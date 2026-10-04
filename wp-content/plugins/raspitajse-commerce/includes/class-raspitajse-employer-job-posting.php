<?php
/**
 * Employer job-form fields, validation and read models for the free launch.
 *
 * @package Raspitajse_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Raspitajse_Employer_Job_Posting {
    const TAXONOMY       = 'job_listing_category';
    const POSITION_FIELD = '_job_category';
    const WORKERS_META   = '_raspitajse_workers_needed';
    const QUOTA_FIELD    = '_raspitajse_active_job_quota';
    const WORKERS_MAX    = 1000;

    const REASON_POSITION = 'invalid_job_position';
    const REASON_WORKERS  = 'invalid_workers_needed';

    public static function boot() {
        add_filter( 'wp-job-board-pro-job_listing-fields', array( __CLASS__, 'normalize_front_fields' ), PHP_INT_MAX, 2 );
        add_filter( 'cmb2_override_' . self::POSITION_FIELD . '_meta_value', array( __CLASS__, 'override_position_value' ), 10, 4 );
        add_action( 'wp-job-board-pro-process-submission-after-save', array( __CLASS__, 'persist_frontend_fields' ), 10, 1 );
    }

    public static function normalize_front_fields( $fields, $job_id ) {
        if ( ! is_array( $fields ) || ! self::taxonomy_is_available() ) {
            return $fields;
        }

        $normalized = array();
        $inserted   = false;
        foreach ( $fields as $field ) {
            $field_id = is_array( $field ) && isset( $field['id'] ) ? (string) $field['id'] : '';
            $field_name = is_array( $field ) && isset( $field['name'] )
                ? trim( wp_strip_all_tags( (string) $field['name'] ) )
                : '';
            if ( in_array( $field_id, array( self::WORKERS_META, self::QUOTA_FIELD ), true ) || 'Potreban broj radnika' === $field_name ) {
                continue;
            }
            if ( self::POSITION_FIELD === $field_id ) {
                if ( ! $inserted ) {
                    $normalized = array_merge( $normalized, self::form_fields( $job_id, $field ) );
                    $inserted   = true;
                }
                continue;
            }
            if ( ! $inserted && '_job_post_type' === $field_id ) {
                $normalized = array_merge( $normalized, self::form_fields( $job_id ) );
                $inserted   = true;
            }
            $normalized[] = $field;
        }
        if ( ! $inserted ) {
            $normalized = array_merge( $normalized, self::form_fields( $job_id ) );
        }
        return $normalized;
    }

    /**
     * Always preselect the one canonical term, never stale taxonomy-field meta.
     */
    public static function override_position_value( $override, $object_id, $args, $field ) {
        if ( ! isset( $args['field_id'] ) || self::POSITION_FIELD !== $args['field_id'] ) {
            return $override;
        }

        $requirements = self::get_job_requirements( $object_id );
        return is_wp_error( $requirements ) ? 0 : $requirements['position_id'];
    }

    private static function form_fields( $job_id, $original = array() ) {
        $job_id = self::positive_id( $job_id );
        $position_default = self::submitted_default( self::POSITION_FIELD );
        if ( null === $position_default ) {
            $requirements = self::get_job_requirements( $job_id );
            $position_default = is_wp_error( $requirements ) ? 0 : $requirements['position_id'];
        }

        $workers_default = self::submitted_default( self::WORKERS_META );
        if ( null === $workers_default ) {
            $stored_workers  = self::get_workers_needed( $job_id );
            $workers_default = $stored_workers ? $stored_workers : 1;
        }

        $position = array(
            'name'       => __( 'Pozicija', 'raspitajse-commerce' ),
            'desc'       => __( 'Izaberite jednu konkretnu poziciju.', 'raspitajse-commerce' ),
            'id'         => self::POSITION_FIELD,
            'type'       => 'pw_select',
            'options'    => self::selectable_options(),
            'default'    => $position_default,
            'save_field' => false,
            'attributes' => array(
                'required'        => 'required',
                'data-allowclear' => 'true',
                'data-width'      => '100%',
                'placeholder'     => __( 'Izaberite poziciju', 'raspitajse-commerce' ),
            ),
        );
        if ( is_array( $original ) && isset( $original['priority'] ) ) {
            $position['priority'] = $original['priority'];
        }

        return array(
            $position,
            array(
                'name'       => __( 'Potreban broj radnika', 'raspitajse-commerce' ),
                'desc'       => sprintf(
                    /* translators: %d: maximum supported worker count. */
                    __( 'Unesite ceo broj od 1 do %d.', 'raspitajse-commerce' ),
                    self::WORKERS_MAX
                ),
                'id'         => self::WORKERS_META,
                'type'       => 'text',
                'default'    => $workers_default,
                'save_field' => false,
                'attributes' => array(
                    'type'      => 'number',
                    'min'       => '1',
                    'max'       => (string) self::WORKERS_MAX,
                    'step'      => '1',
                    'required'  => 'required',
                    'inputmode' => 'numeric',
                ),
            ),
            self::quota_field( $job_id ),
        );
    }

    private static function quota_field( $job_id ) {
        $title = __( 'Aktivni oglasi: stanje nije dostupno', 'raspitajse-commerce' );
        $desc  = __( 'Objavljivanje će biti provereno bezbednosnim pravilom pre promene statusa.', 'raspitajse-commerce' );
        $state = self::get_quota_presentation( get_current_user_id(), $job_id );

        if ( ! is_wp_error( $state ) ) {
            $title = sprintf(
                /* translators: 1: active jobs, 2: active-job limit. */
                __( 'Aktivni oglasi: %1$d od %2$d', 'raspitajse-commerce' ),
                $state['used'],
                $state['limit']
            );
            if ( 0 === $state['remaining'] && ! $state['current_consumes_slot'] ) {
                $desc = __( 'Limit je dostignut. Oglas možete sačuvati kao nacrt; za objavu prvo zatvorite jedan aktivan oglas.', 'raspitajse-commerce' );
            } elseif ( $state['current_consumes_slot'] ) {
                $desc = __( 'Ovaj oglas već koristi jedno mesto i njegova izmena ne troši dodatno mesto.', 'raspitajse-commerce' );
            } else {
                $desc = sprintf(
                    /* translators: %d: remaining active-job slots. */
                    _n( 'Možete objaviti još %d aktivan oglas.', 'Možete objaviti još %d aktivna oglasa.', $state['remaining'], 'raspitajse-commerce' ),
                    $state['remaining']
                );
            }
        }

        return array(
            'name'       => $title,
            'desc'       => $desc,
            'id'         => self::QUOTA_FIELD,
            'type'       => 'title',
            'save_field' => false,
        );
    }

    public static function persist_frontend_fields( $job_id ) {
        $job_id = self::positive_id( $job_id );
        if (
            ! $job_id
            || 'job_listing' !== get_post_type( $job_id )
            || ! isset( $_POST['submit-cmb-job_listing'] )
            || ! class_exists( 'Raspitajse_Free_Job_Access_Policy' )
            || ! Raspitajse_Free_Job_Access_Policy::employer_owns_job( get_current_user_id(), $job_id )
        ) {
            return;
        }

        $position_raw = array_key_exists( self::POSITION_FIELD, $_POST ) ? wp_unslash( $_POST[ self::POSITION_FIELD ] ) : null;
        $workers_raw  = array_key_exists( self::WORKERS_META, $_POST ) ? wp_unslash( $_POST[ self::WORKERS_META ] ) : null;
        $position     = self::validate_position( $position_raw );
        $workers      = self::validate_workers( $workers_raw );

        if ( $position['valid'] ) {
            wp_set_object_terms( $job_id, array( $position['id'] ), self::TAXONOMY, false );
        }
        if ( $workers['valid'] ) {
            update_post_meta( $job_id, self::WORKERS_META, $workers['value'] );
        }

        if ( $position['valid'] && $workers['valid'] ) {
            $reason = (string) get_post_meta( $job_id, Raspitajse_Free_Job_Access_Policy::META_REASON, true );
            if ( in_array( $reason, array( self::REASON_POSITION, self::REASON_WORKERS ), true ) ) {
                delete_post_meta( $job_id, Raspitajse_Free_Job_Access_Policy::META_REASON );
            }
        }
    }

    /**
     * Validate form, admin, REST and programmatic requests at the existing
     * authoritative public-transition gate.
     *
     * @return true|WP_Error
     */
    public static function validate_publication_request( $post_id, $postarr, $unsanitized_postarr = array() ) {
        $position = self::validate_position( self::requested_position( $post_id, $postarr, $unsanitized_postarr ) );
        if ( ! $position['valid'] ) {
            return new WP_Error( self::REASON_POSITION, self::validation_message( self::REASON_POSITION ) );
        }

        $workers = self::validate_workers( self::requested_workers( $post_id, $postarr, $unsanitized_postarr ) );
        if ( ! $workers['valid'] ) {
            return new WP_Error( self::REASON_WORKERS, self::validation_message( self::REASON_WORKERS ) );
        }
        return true;
    }

    /** @return array{valid:bool,id:int,code:string} */
    public static function validate_position( $raw ) {
        if ( is_scalar( $raw ) ) {
            $raw = array( $raw );
        }
        if ( ! is_array( $raw ) || 1 !== count( $raw ) ) {
            return array( 'valid' => false, 'id' => 0, 'code' => 'exactly_one_required' );
        }
        $id = self::strict_positive_integer( reset( $raw ) );
        if ( ! $id || ! self::is_selectable_term_id( $id ) ) {
            return array( 'valid' => false, 'id' => 0, 'code' => 'invalid_term' );
        }
        return array( 'valid' => true, 'id' => $id, 'code' => '' );
    }

    /** @return array{valid:bool,value:int,code:string} */
    public static function validate_workers( $raw ) {
        $value = self::strict_positive_integer( $raw );
        if ( ! $value ) {
            return array( 'valid' => false, 'value' => 0, 'code' => 'positive_integer_required' );
        }
        if ( self::WORKERS_MAX < $value ) {
            return array( 'valid' => false, 'value' => 0, 'code' => 'above_upper_bound' );
        }
        return array( 'valid' => true, 'value' => $value, 'code' => '' );
    }

    /**
     * Read-only form view of the authoritative quota resolver/query.
     *
     * @return array{used:int,limit:int,remaining:int,current_consumes_slot:bool}|WP_Error
     */
    public static function get_quota_presentation( $user_id, $job_id = 0 ) {
        if ( ! class_exists( 'Raspitajse_Free_Job_Access_Policy' ) ) {
            return new WP_Error( 'quota_service_unavailable' );
        }
        $context = Raspitajse_Free_Job_Access_Policy::get_employer_context( $user_id );
        if ( is_wp_error( $context ) ) {
            return $context;
        }

        $job_id = self::positive_id( $job_id );
        if ( $job_id && ! Raspitajse_Free_Job_Access_Policy::employer_owns_job( $context['user_id'], $job_id ) ) {
            return new WP_Error( Raspitajse_Free_Job_Access_Policy::REASON_CROSS_EMPLOYER );
        }

        $all = Raspitajse_Free_Job_Access_Policy::get_quota_state( $context['user_id'] );
        if ( is_wp_error( $all ) ) {
            return $all;
        }

        $consumes = false;
        if ( $job_id ) {
            $without = Raspitajse_Free_Job_Access_Policy::get_quota_state( $context['user_id'], $job_id );
            if ( is_wp_error( $without ) ) {
                return $without;
            }
            $consumes = (int) $all['used'] === (int) $without['used'] + 1;
        }

        return array(
            'used'                  => (int) $all['used'],
            'limit'                 => (int) $all['limit'],
            'remaining'             => (int) $all['remaining'],
            'current_consumes_slot' => $consumes,
        );
    }

    /**
     * Later presentation/application work can read normalized requirements
     * without silently repairing historical records.
     */
    public static function get_job_requirements( $job_id ) {
        $job_id = self::positive_id( $job_id );
        if ( ! $job_id || 'job_listing' !== get_post_type( $job_id ) ) {
            return new WP_Error( 'invalid_job' );
        }

        $terms    = wp_get_post_terms( $job_id, self::TAXONOMY, array( 'fields' => 'ids' ) );
        $position = is_wp_error( $terms ) ? self::validate_position( null ) : self::validate_position( $terms );
        $workers  = self::validate_workers( get_post_meta( $job_id, self::WORKERS_META, true ) );

        return array(
            'position_id'    => $position['valid'] ? $position['id'] : 0,
            'workers_needed' => $workers['valid'] ? $workers['value'] : 0,
            'repair_required' => ! $position['valid'] || ! $workers['valid'],
            'position_code'  => $position['code'],
            'workers_code'   => $workers['code'],
        );
    }

    public static function get_workers_needed( $job_id ) {
        $requirements = self::get_job_requirements( $job_id );
        return is_wp_error( $requirements ) ? 0 : $requirements['workers_needed'];
    }

    /** @return array<int,string> */
    public static function selectable_options() {
        $options = array();
        foreach ( self::selectable_terms() as $term ) {
            $parent      = get_term( (int) $term->parent, self::TAXONOMY );
            $parent_name = ! is_wp_error( $parent ) && is_object( $parent ) ? trim( wp_strip_all_tags( (string) $parent->name ) ) : '';
            $name        = trim( wp_strip_all_tags( (string) $term->name ) );
            $options[ (int) $term->term_id ] = $parent_name ? $parent_name . ' — ' . $name : $name;
        }
        return $options;
    }

    private static function selectable_terms() {
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

        $valid = array();
        foreach ( $terms as $term ) {
            if ( self::is_selectable_term_id( is_object( $term ) ? $term->term_id : 0 ) ) {
                $valid[] = $term;
            }
        }
        usort(
            $valid,
            static function ( $left, $right ) {
                $result = strcasecmp(
                    remove_accents( wp_strip_all_tags( (string) $left->name ) ),
                    remove_accents( wp_strip_all_tags( (string) $right->name ) )
                );
                return 0 !== $result ? $result : (int) $left->term_id <=> (int) $right->term_id;
            }
        );
        return $valid;
    }

    private static function requested_position( $post_id, $postarr, $unsanitized_postarr ) {
        foreach ( array( $unsanitized_postarr, $postarr ) as $source ) {
            if ( ! is_array( $source ) ) {
                continue;
            }
            if ( array_key_exists( self::POSITION_FIELD, $source ) ) {
                return $source[ self::POSITION_FIELD ];
            }
            if ( isset( $source['tax_input'] ) && is_array( $source['tax_input'] ) && array_key_exists( self::TAXONOMY, $source['tax_input'] ) ) {
                return $source['tax_input'][ self::TAXONOMY ];
            }
            if ( array_key_exists( self::TAXONOMY, $source ) ) {
                return $source[ self::TAXONOMY ];
            }
        }
        if ( array_key_exists( self::POSITION_FIELD, $_POST ) ) {
            return wp_unslash( $_POST[ self::POSITION_FIELD ] );
        }
        return $post_id ? wp_get_post_terms( $post_id, self::TAXONOMY, array( 'fields' => 'ids' ) ) : null;
    }

    private static function requested_workers( $post_id, $postarr, $unsanitized_postarr ) {
        foreach ( array( $unsanitized_postarr, $postarr ) as $source ) {
            if ( ! is_array( $source ) ) {
                continue;
            }
            foreach ( array( 'meta_input', 'meta' ) as $container ) {
                if ( isset( $source[ $container ] ) && is_array( $source[ $container ] ) && array_key_exists( self::WORKERS_META, $source[ $container ] ) ) {
                    return $source[ $container ][ self::WORKERS_META ];
                }
            }
            if ( array_key_exists( self::WORKERS_META, $source ) ) {
                return $source[ self::WORKERS_META ];
            }
        }
        if ( array_key_exists( self::WORKERS_META, $_POST ) ) {
            return wp_unslash( $_POST[ self::WORKERS_META ] );
        }
        return $post_id ? get_post_meta( $post_id, self::WORKERS_META, true ) : null;
    }

    private static function submitted_default( $key ) {
        if ( ! array_key_exists( $key, $_POST ) ) {
            return null;
        }
        $value = wp_unslash( $_POST[ $key ] );
        if ( is_array( $value ) ) {
            return 1 === count( $value ) && is_scalar( reset( $value ) ) ? reset( $value ) : '';
        }
        return is_scalar( $value ) ? $value : '';
    }

    private static function validation_message( $reason ) {
        if ( self::REASON_POSITION === $reason ) {
            return __( 'Izaberite tačno jednu važeću poziciju. Oglas je sačuvan kao nacrt.', 'raspitajse-commerce' );
        }
        return sprintf(
            /* translators: %d: maximum supported worker count. */
            __( 'Polje „Potreban broj radnika” mora biti ceo broj između 1 i %d. Oglas je sačuvan kao nacrt.', 'raspitajse-commerce' ),
            self::WORKERS_MAX
        );
    }

    private static function taxonomy_is_available() {
        if ( ! function_exists( 'taxonomy_exists' ) || ! taxonomy_exists( self::TAXONOMY ) ) {
            return false;
        }
        $taxonomy = get_taxonomy( self::TAXONOMY );
        return is_object( $taxonomy )
            && isset( $taxonomy->object_type )
            && in_array( 'job_listing', (array) $taxonomy->object_type, true );
    }

    private static function is_selectable_term_id( $term_id ) {
        $term_id = self::strict_positive_integer( is_int( $term_id ) ? $term_id : (string) $term_id );
        if ( ! $term_id || ! self::taxonomy_is_available() ) {
            return false;
        }
        $term = get_term( $term_id, self::TAXONOMY );
        if ( is_wp_error( $term ) || ! self::usable_term( $term ) ) {
            return false;
        }
        $parent_id = self::strict_positive_integer( is_int( $term->parent ) ? $term->parent : (string) $term->parent );
        if ( ! $parent_id ) {
            return false;
        }
        $parent = get_term( $parent_id, self::TAXONOMY );
        return ! is_wp_error( $parent ) && self::usable_term( $parent );
    }

    private static function usable_term( $term ) {
        return is_object( $term )
            && self::positive_id( $term->term_id )
            && isset( $term->taxonomy )
            && self::TAXONOMY === (string) $term->taxonomy
            && isset( $term->name )
            && '' !== trim( wp_strip_all_tags( (string) $term->name ) );
    }

    private static function strict_positive_integer( $value ) {
        if ( is_int( $value ) ) {
            return 0 < $value ? $value : 0;
        }
        if ( ! is_string( $value ) || 1 !== preg_match( '/^[1-9][0-9]*$/D', $value ) ) {
            return 0;
        }
        $integer = (int) $value;
        return (string) $integer === $value ? $integer : 0;
    }

    private static function positive_id( $value ) {
        if ( is_int( $value ) ) {
            return 0 < $value ? $value : 0;
        }
        if ( ! is_scalar( $value ) || 1 !== preg_match( '/^[1-9][0-9]*$/D', (string) $value ) ) {
            return 0;
        }
        return (int) $value;
    }
}
