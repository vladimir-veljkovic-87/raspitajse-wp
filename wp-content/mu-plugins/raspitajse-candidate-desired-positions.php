<?php
/**
 * Plugin Name: Raspitajse Candidate Desired Positions
 * Description: Owned candidate preference field backed by canonical job-position terms.
 * Version: 1.0.0
 */

defined( 'ABSPATH' ) || exit;

final class Raspitajse_Candidate_Desired_Positions {

    const TAXONOMY           = 'job_listing_category';
    const META_KEY           = '_raspitajse_candidate_desired_position_ids';
    const PRESENT_KEY        = 'raspitajse_desired_positions_present';
    const MAX_SELECTIONS     = 3;
    const VENDOR_SAVE_HOOK   = 'cmb2_after_init';
    const VENDOR_SAVE_METHOD = 'process_change_profile';

    /** @var array<int,array<int,int>> */
    private static $pending = array();

    public static function boot() {
        add_filter( 'wp-job-board-pro-candidate-fields-front', array( __CLASS__, 'add_profile_field' ), PHP_INT_MAX, 2 );
        add_filter( 'cmb2_override_' . self::META_KEY . '_meta_value', array( __CLASS__, 'override_field_value' ), 10, 4 );
        add_action( self::VENDOR_SAVE_HOOK, array( __CLASS__, 'preflight_profile_save' ), 1 );
        add_action( 'wp-job-board-pro-process-profile-after-change', array( __CLASS__, 'commit_profile_save' ), 10, 2 );
    }

    /**
     * Add one owned field to the existing candidate frontend metabox.
     */
    public static function add_profile_field( $fields, $candidate_id ) {
        if ( ! is_array( $fields ) || ! self::taxonomy_is_available() ) {
            return $fields;
        }

        $fields[] = array(
            'name'       => __( 'Pozicije koje tražiš', 'raspitajse-candidate-profile' ),
            'desc'       => __( 'Možeš izabrati najviše tri pozicije.', 'raspitajse-candidate-profile' ),
            'id'         => self::META_KEY,
            'type'       => 'pw_multiselect',
            'options'    => self::selectable_options(),
            'default'    => self::get_ids( $candidate_id ),
            'save_field' => false,
            'before'     => '<input type="hidden" name="' . esc_attr( self::PRESENT_KEY ) . '" value="1">',
            'attributes' => array(
                'placeholder'                   => __( 'Izaberi pozicije', 'raspitajse-candidate-profile' ),
                'data-maximum-selection-length' => (string) self::MAX_SELECTIONS,
                'data-raspitajse-max-selections' => (string) self::MAX_SELECTIONS,
            ),
        );

        return $fields;
    }

    /**
     * Ensure the form displays only validated stored IDs.
     */
    public static function override_field_value( $override, $object_id, $args, $field ) {
        if ( isset( $args['field_id'] ) && self::META_KEY === $args['field_id'] ) {
            return self::get_ids( $object_id );
        }

        return $override;
    }

    /**
     * Validate before the vendor mutates any candidate profile state.
     * Invalid requests remove only the current request's vendor save callback.
     */
    public static function preflight_profile_save() {
        if (
            ! isset( $_POST['submit-cmb-profile'] )
            || ! self::vendor_profile_available()
            || ! defined( 'WP_JOB_BOARD_PRO_CANDIDATE_PREFIX' )
        ) {
            return;
        }

        $prefix = (string) WP_JOB_BOARD_PRO_CANDIDATE_PREFIX;
        if ( 'candidate' !== (string) ( $_POST[ $prefix . 'post_type' ] ?? '' ) ) {
            return;
        }

        $actor_id    = (int) WP_Job_Board_Pro_User::get_user_id();
        $candidate_id = (int) WP_Job_Board_Pro_User::get_candidate_by_user_id( $actor_id );
        $target_id = self::positive_id( $_POST['object_id'] ?? 0 );
        if ( ! $candidate_id || $target_id !== $candidate_id ) {
            self::reject_vendor_save( 'wrong_profile' );
            return;
        }
        $nonce_valid = self::vendor_nonce_is_valid( $candidate_id, $prefix );
        $present     = isset( $_POST[ self::PRESENT_KEY ] ) && '1' === (string) $_POST[ self::PRESENT_KEY ];
        $raw         = array_key_exists( self::META_KEY, $_POST )
            ? wp_unslash( $_POST[ self::META_KEY ] )
            : array();

        $validation = self::validate_change( $candidate_id, $raw, $actor_id, $nonce_valid, $present );
        if ( ! $validation['valid'] ) {
            self::reject_vendor_save( $validation['code'] );
            return;
        }

        self::$pending[ $candidate_id ] = $validation['ids'];
    }

    /**
     * Save only after the existing profile update and CMB saves succeeded.
     */
    public static function commit_profile_save( $candidate_id, $prefix ) {
        $candidate_id = self::positive_id( $candidate_id );
        if (
            ! $candidate_id
            || ! defined( 'WP_JOB_BOARD_PRO_CANDIDATE_PREFIX' )
            || (string) WP_JOB_BOARD_PRO_CANDIDATE_PREFIX !== (string) $prefix
            || ! array_key_exists( $candidate_id, self::$pending )
        ) {
            return;
        }

        $ids = self::$pending[ $candidate_id ];
        unset( self::$pending[ $candidate_id ] );
        update_post_meta( $candidate_id, self::META_KEY, $ids );
    }

    /**
     * Pure validation contract used by the frontend integration and tests.
     *
     * @return array{valid:bool,ids:array<int,int>,code:string}
     */
    public static function validate_change( $candidate_id, $raw, $actor_id, $nonce_valid, $present ) {
        $candidate_id = self::positive_id( $candidate_id );
        $actor_id     = self::positive_id( $actor_id );

        if ( ! $candidate_id || 'candidate' !== get_post_type( $candidate_id ) ) {
            return self::invalid( 'wrong_profile' );
        }
        if ( ! $actor_id || ! self::can_edit( $candidate_id, $actor_id ) ) {
            return self::invalid( 'unauthorized' );
        }
        if ( true !== $nonce_valid ) {
            return self::invalid( 'invalid_nonce' );
        }
        if ( true !== $present || ! is_array( $raw ) ) {
            return self::invalid( 'tampered_payload' );
        }
        if ( count( $raw ) > self::MAX_SELECTIONS ) {
            return self::invalid( 'too_many' );
        }

        $ids  = array();
        $seen = array();
        foreach ( $raw as $value ) {
            $id = self::strict_submitted_id( $value );
            if ( ! $id ) {
                return self::invalid( 'malformed_value' );
            }
            if ( isset( $seen[ $id ] ) ) {
                return self::invalid( 'duplicate_value' );
            }
            if ( ! self::is_selectable_term_id( $id ) ) {
                return self::invalid( 'invalid_term' );
            }

            $seen[ $id ] = true;
            $ids[]       = $id;
        }

        return array(
            'valid' => true,
            'ids'   => $ids,
            'code'  => '',
        );
    }

    /**
     * Return validated canonical preferences without repairing stored data.
     *
     * @return array<int,int>
     */
    public static function get_ids( $candidate_id ) {
        $candidate_id = self::positive_id( $candidate_id );
        if ( ! $candidate_id || 'candidate' !== get_post_type( $candidate_id ) ) {
            return array();
        }

        $stored = get_post_meta( $candidate_id, self::META_KEY, true );
        if ( ! is_array( $stored ) ) {
            return array();
        }

        $valid = array();
        $seen  = array();
        foreach ( $stored as $value ) {
            $id = self::positive_id( $value );
            if ( ! $id || isset( $seen[ $id ] ) || ! self::is_selectable_term_id( $id ) ) {
                continue;
            }

            $seen[ $id ] = true;
            $valid[]     = $id;
            if ( count( $valid ) === self::MAX_SELECTIONS ) {
                break;
            }
        }

        return $valid;
    }

    /** @return array<int,string> */
    public static function get_labels( $candidate_id ) {
        $labels = array();
        foreach ( self::get_ids( $candidate_id ) as $term_id ) {
            $term = get_term( $term_id, self::TAXONOMY );
            if ( ! is_wp_error( $term ) && is_object( $term ) ) {
                $labels[ $term_id ] = esc_html( self::term_label( $term ) );
            }
        }

        return $labels;
    }

    public static function has_preferences( $candidate_id ) {
        return array() !== self::get_ids( $candidate_id );
    }

    /**
     * A job matches only when it has exactly one valid canonical position and
     * that position overlaps a configured candidate preference.
     */
    public static function job_matches( $candidate_id, $job_id ) {
        $preferences = self::get_ids( $candidate_id );
        if ( array() === $preferences ) {
            return false;
        }

        $job_id = self::positive_id( $job_id );
        if ( ! $job_id || 'job_listing' !== get_post_type( $job_id ) ) {
            return false;
        }

        $job_terms = wp_get_post_terms( $job_id, self::TAXONOMY, array( 'fields' => 'ids' ) );
        if ( is_wp_error( $job_terms ) || ! is_array( $job_terms ) || 1 !== count( $job_terms ) ) {
            return false;
        }

        $position_id = self::positive_id( reset( $job_terms ) );
        return $position_id
            && self::is_selectable_term_id( $position_id )
            && in_array( $position_id, $preferences, true );
    }

    /** @return array<int,string> */
    public static function selectable_options() {
        $options = array();
        foreach ( self::selectable_terms() as $term ) {
            $options[ (int) $term->term_id ] = self::term_label( $term );
        }
        return $options;
    }

    /** @return array<int,object> */
    public static function selectable_terms() {
        if ( ! self::taxonomy_is_available() ) {
            return array();
        }

        $terms = get_terms(
            array(
                'taxonomy'   => self::TAXONOMY,
                'hide_empty' => false,
                'orderby'    => 'none',
            )
        );
        if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
            return array();
        }

        $map = array();
        foreach ( $terms as $term ) {
            if ( self::term_record_is_usable( $term ) ) {
                $map[ (int) $term->term_id ] = $term;
            }
        }

        $children = array();
        foreach ( $map as $term ) {
            $parent_id = self::positive_id( $term->parent );
            if ( $parent_id && isset( $map[ $parent_id ] ) ) {
                $children[] = $term;
            }
        }

        $ordered = false;
        foreach ( $map as $term ) {
            if ( isset( $term->term_order ) && 0 !== (int) $term->term_order ) {
                $ordered = true;
                break;
            }
        }

        usort(
            $children,
            static function ( $left, $right ) use ( $map, $ordered ) {
                $left_path  = self::term_sort_path( $left, $map, $ordered );
                $right_path = self::term_sort_path( $right, $map, $ordered );
                return self::compare_paths( $left_path, $right_path );
            }
        );

        return $children;
    }

    private static function vendor_profile_available() {
        return class_exists( 'WP_Job_Board_Pro_User' )
            && is_callable( array( 'WP_Job_Board_Pro_User', self::VENDOR_SAVE_METHOD ) )
            && function_exists( 'cmb2_get_metabox' );
    }

    private static function vendor_nonce_is_valid( $candidate_id, $prefix ) {
        if ( ! $candidate_id ) {
            return false;
        }

        $cmb = cmb2_get_metabox( $prefix . 'front', $candidate_id );
        if ( ! is_object( $cmb ) || ! is_callable( array( $cmb, 'nonce' ) ) ) {
            return false;
        }

        $nonce_key = (string) $cmb->nonce();
        return '' !== $nonce_key
            && isset( $_POST[ $nonce_key ] )
            && wp_verify_nonce( $_POST[ $nonce_key ], $nonce_key );
    }

    private static function reject_vendor_save( $code ) {
        self::$pending = array();
        remove_action(
            self::VENDOR_SAVE_HOOK,
            array( 'WP_Job_Board_Pro_User', self::VENDOR_SAVE_METHOD ),
            10
        );

        if ( ! isset( $_SESSION['messages'] ) || ! is_array( $_SESSION['messages'] ) ) {
            $_SESSION['messages'] = array();
        }
        $_SESSION['messages'][] = array( 'danger', self::validation_message( $code ) );
    }

    private static function validation_message( $code ) {
        if ( 'too_many' === $code ) {
            return __( 'Možeš izabrati najviše tri pozicije. Profil nije izmenjen.', 'raspitajse-candidate-profile' );
        }
        if ( 'unauthorized' === $code || 'wrong_profile' === $code ) {
            return __( 'Nemaš dozvolu da izmeniš ove pozicije. Profil nije izmenjen.', 'raspitajse-candidate-profile' );
        }

        return __( 'Izbor pozicija nije važeći. Profil nije izmenjen.', 'raspitajse-candidate-profile' );
    }

    private static function can_edit( $candidate_id, $actor_id ) {
        $owner_id = self::positive_id(
            get_post_meta( $candidate_id, self::candidate_prefix() . 'user_id', true )
        );
        if ( $owner_id && $owner_id === $actor_id ) {
            return true;
        }

        return user_can( $actor_id, 'manage_options' )
            && user_can( $actor_id, 'edit_post', $candidate_id );
    }

    private static function candidate_prefix() {
        return defined( 'WP_JOB_BOARD_PRO_CANDIDATE_PREFIX' )
            ? (string) WP_JOB_BOARD_PRO_CANDIDATE_PREFIX
            : '_candidate_';
    }

    private static function taxonomy_is_available() {
        if ( ! taxonomy_exists( self::TAXONOMY ) ) {
            return false;
        }

        $taxonomy = get_taxonomy( self::TAXONOMY );
        return is_object( $taxonomy )
            && isset( $taxonomy->object_type )
            && in_array( 'job_listing', (array) $taxonomy->object_type, true );
    }

    private static function is_selectable_term_id( $term_id ) {
        if ( ! self::taxonomy_is_available() ) {
            return false;
        }

        $term = get_term( $term_id, self::TAXONOMY );
        if ( is_wp_error( $term ) || ! self::term_record_is_usable( $term ) ) {
            return false;
        }

        $parent_id = self::positive_id( $term->parent );
        if ( ! $parent_id ) {
            return false;
        }

        $parent = get_term( $parent_id, self::TAXONOMY );
        return ! is_wp_error( $parent ) && self::term_record_is_usable( $parent );
    }

    private static function term_record_is_usable( $term ) {
        return is_object( $term )
            && self::positive_id( $term->term_id )
            && isset( $term->taxonomy )
            && self::TAXONOMY === (string) $term->taxonomy
            && isset( $term->name )
            && '' !== trim( wp_strip_all_tags( (string) $term->name ) );
    }

    private static function term_label( $term ) {
        $parts = array( trim( wp_strip_all_tags( (string) $term->name ) ) );
        $seen  = array( (int) $term->term_id => true );
        $parent_id = self::positive_id( $term->parent );

        while ( $parent_id && ! isset( $seen[ $parent_id ] ) ) {
            $seen[ $parent_id ] = true;
            $parent = get_term( $parent_id, self::TAXONOMY );
            if ( is_wp_error( $parent ) || ! self::term_record_is_usable( $parent ) ) {
                break;
            }
            array_unshift( $parts, trim( wp_strip_all_tags( (string) $parent->name ) ) );
            $parent_id = self::positive_id( $parent->parent );
        }

        return implode( ' — ', $parts );
    }

    private static function term_sort_path( $term, $map, $ordered ) {
        $path = array();
        $seen = array();
        while ( is_object( $term ) && ! isset( $seen[ (int) $term->term_id ] ) ) {
            $seen[ (int) $term->term_id ] = true;
            array_unshift(
                $path,
                array(
                    'order' => $ordered && isset( $term->term_order ) ? (int) $term->term_order : 0,
                    'name'  => self::collation_key( $term->name ),
                    'id'    => (int) $term->term_id,
                )
            );
            $parent_id = self::positive_id( $term->parent );
            $term      = $parent_id && isset( $map[ $parent_id ] ) ? $map[ $parent_id ] : null;
        }
        return $path;
    }

    private static function compare_paths( $left, $right ) {
        $count = min( count( $left ), count( $right ) );
        for ( $index = 0; $index < $count; $index++ ) {
            foreach ( array( 'order', 'name', 'id' ) as $key ) {
                if ( $left[ $index ][ $key ] === $right[ $index ][ $key ] ) {
                    continue;
                }
                return $left[ $index ][ $key ] < $right[ $index ][ $key ] ? -1 : 1;
            }
        }

        return count( $left ) <=> count( $right );
    }

    private static function collation_key( $value ) {
        $value = remove_accents( wp_strip_all_tags( (string) $value ) );
        return function_exists( 'mb_strtolower' )
            ? mb_strtolower( $value, 'UTF-8' )
            : strtolower( $value );
    }

    private static function strict_submitted_id( $value ) {
        if ( is_int( $value ) ) {
            return $value > 0 ? $value : 0;
        }
        if ( ! is_string( $value ) || 1 !== preg_match( '/^[1-9][0-9]*$/D', $value ) ) {
            return 0;
        }

        $id = (int) $value;
        return (string) $id === $value ? $id : 0;
    }

    private static function positive_id( $value ) {
        if ( is_int( $value ) ) {
            return $value > 0 ? $value : 0;
        }
        if ( ! is_scalar( $value ) || 1 !== preg_match( '/^[1-9][0-9]*$/D', (string) $value ) ) {
            return 0;
        }
        return (int) $value;
    }

    private static function invalid( $code ) {
        return array(
            'valid' => false,
            'ids'   => array(),
            'code'  => (string) $code,
        );
    }
}

Raspitajse_Candidate_Desired_Positions::boot();
