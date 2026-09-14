<?php
/**
 * Free-launch access policy and canonical employer active-job quota.
 *
 * @package Raspitajse_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Raspitajse_Free_Job_Access_Policy {
    const ACTIVE_JOB_LIMIT = 3;
    const LOCK_TIMEOUT = 3;
    const META_REASON = '_raspitajse_job_publication_block_reason';
    const REASON_QUOTA = 'active_job_limit';
    const REASON_IDENTITY = 'invalid_employer_identity';
    const REASON_CROSS_EMPLOYER = 'cross_employer_ownership';
    const REASON_EXPIRY = 'invalid_expiry_date';
    const REASON_LOCK = 'quota_lock_unavailable';

    private static $booted = false;
    private static $activated = false;
    private static $frames = array();
    private static $held_locks = array();
    private static $last_reason = '';
    private static $last_blocked_post_id = 0;

    public static function boot() {
        if ( self::$booted ) {
            return;
        }

        self::$booted = true;
        add_action( 'plugins_loaded', array( __CLASS__, 'activate' ), PHP_INT_MAX );
    }

    public static function activate() {
        if ( self::$activated || ! self::is_enabled() ) {
            return;
        }

        self::$activated = true;
        self::detach_paid_submission_callbacks();
        self::detach_paid_candidate_callbacks();
        self::detach_paid_purchase_callbacks();

        add_filter( 'wp_job_board_pro_submit_job_steps', array( __CLASS__, 'remove_paid_submit_steps' ), PHP_INT_MAX );
        add_filter( 'wp_job_board_pro_submit_job_post_status', array( __CLASS__, 'normalize_free_submission_status' ), PHP_INT_MAX, 2 );
        add_filter( 'wp_job_board_pro_get_option_candidate_free_job_apply', array( __CLASS__, 'force_candidate_application_free' ), PHP_INT_MAX, 3 );
        add_filter( 'wp_job_board_pro_get_option_candidate_restrict_detail', array( __CLASS__, 'normalize_candidate_privacy_option' ), PHP_INT_MAX, 3 );
        add_filter( 'wp_job_board_pro_get_option_candidate_restrict_listing', array( __CLASS__, 'normalize_candidate_privacy_option' ), PHP_INT_MAX, 3 );
        add_filter( 'wp_job_board_pro_get_option_candidate_restrict_contact_info', array( __CLASS__, 'normalize_candidate_privacy_option' ), PHP_INT_MAX, 3 );
        add_filter( 'wp_insert_post_data', array( __CLASS__, 'enforce_publication_quota' ), 9999, 4 );
        add_action( 'wp_after_insert_post', array( __CLASS__, 'after_insert_post' ), PHP_INT_MAX, 4 );
        add_action( 'shutdown', array( __CLASS__, 'release_all_locks' ), 0 );
        add_action( 'init', array( __CLASS__, 'register_reason_meta' ), 20 );
        add_action( 'wp_job_board_pro_job_submit_done', array( __CLASS__, 'render_submission_reason' ), 0 );
        add_filter( 'redirect_post_location', array( __CLASS__, 'add_admin_reason_query_arg' ), PHP_INT_MAX, 2 );
        add_action( 'admin_notices', array( __CLASS__, 'render_admin_reason' ) );
    }

    public static function is_enabled() {
        return (bool) apply_filters( 'raspitajse_free_launch_enabled', true );
    }

    private static function detach_paid_submission_callbacks() {
        $class = 'WP_Job_Board_Pro_Wc_Paid_Listings_Submit_Form';

        remove_filter( 'wp_job_board_pro_submit_job_steps', array( $class, 'submit_job_steps' ), 5 );
        remove_filter( 'wp-job-board-pro-get-listing-package-id', array( $class, 'get_package_id_post' ), 10 );
        remove_action( 'wp-job-board-pro-before-preview-job', array( $class, 'before_preview_job' ), 10 );
        remove_action( 'wp_loaded', array( $class, 'after_wp_loaded' ), 10 );
        remove_action( 'wp_job_board_pro_submit_job_construct', array( $class, 'before_view_package' ), 10 );
        remove_filter( 'wp_job_board_pro_submit_job_steps', array( 'Raspitajse_Commerce_Job_Package_Policy', 'protect_submit_handlers' ), 50 );
    }

    private static function detach_paid_candidate_callbacks() {
        $candidate = 'WP_Job_Board_Pro_Wc_Paid_Listings_Candidate_Package';
        $cv = 'WP_Job_Board_Pro_Wc_Paid_Listings_CV_Package';
        $contact = 'WP_Job_Board_Pro_Wc_Paid_Listings_Contact_Package';
        $resume = 'WP_Job_Board_Pro_Wc_Paid_Listings_Resume_Package';

        remove_filter( 'wp-job-board-pro-check-candidate-can-apply', array( $candidate, 'process_candidate_can_apply' ), 10 );
        remove_action( 'wp-job-board-pro-before-after-job-applicant', array( $candidate, 'process_added_applicant' ), 10 );
        remove_action( 'wp-job-board-pro-after-remove-applied', array( $candidate, 'process_removed_applicant' ), 10 );

        remove_filter( 'wp-job-board-pro-check-view-candidate-detail', array( $cv, 'process_restrict_candidate_detail' ), 10 );
        remove_filter( 'wp-job-board-pro-check-view-candidate-listing-query-args', array( $cv, 'process_restrict_candidate_listing' ), 10 );
        remove_action( 'wp_job_board_pro_before_job_detail', array( $cv, 'process_viewed_candidate' ), 10 );
        remove_filter( 'wp-job-board-pro-restrict-candidate-detail-information', array( $cv, 'restrict_candidate_information' ), 10 );
        remove_action( 'wp-job-board-pro-restrict-candidate-listing-default-information', array( $cv, 'restrict_candidate_listing_information' ), 10 );

        remove_filter( 'wp-job-board-pro-check-view-candidate-contact-info', array( $contact, 'process_restrict_candidate_contact' ), 11 );
        remove_action( 'wp_job_board_pro_before_job_detail', array( $contact, 'process_viewed_candidate' ), 10 );

        remove_filter( 'wp-job-board-pro-calculate-candidate-expiry', array( $resume, 'calculate_resume_expiry' ), 10 );
        remove_action( 'wp-job-board-pro-resume-form-status', array( $resume, 'packages' ), 10 );
        remove_filter( 'wp-job-board-pro-create-candidate-post-args', array( $resume, 'create_candidate_args' ), 10 );
        remove_filter( 'wp-job-board-pro-approve-user-post-status', array( $resume, 'approve_user' ), 10 );
    }

    private static function detach_paid_purchase_callbacks() {
        $cart = 'WP_Job_Board_Pro_Wc_Paid_Listings_Cart';
        $order = 'WP_Job_Board_Pro_Wc_Paid_Listings_Order';

        remove_filter( 'woocommerce_add_to_cart_redirect', array( $cart, 'add_to_cart_redirect' ), 10 );
        foreach ( array( 'job', 'cv', 'contact', 'candidate', 'resume' ) as $type ) {
            remove_action( 'woocommerce_' . $type . '_package_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
        }
        remove_filter( 'woocommerce_get_cart_item_from_session', array( $cart, 'get_cart_item_from_session' ), 10 );
        remove_action( 'woocommerce_checkout_create_order_line_item', array( $cart, 'order_line_item' ), 20 );
        remove_filter( 'woocommerce_get_item_data', array( $cart, 'get_item_data' ), 10 );
        remove_filter( 'option_woocommerce_enable_signup_and_login_from_checkout', array( $cart, 'enable_checkout_signup' ), 10 );
        remove_filter( 'option_woocommerce_enable_guest_checkout', array( $cart, 'guest_checkout' ), 10 );

        remove_action( 'woocommerce_thankyou', array( $order, 'woocommerce_thankyou' ), 5 );
        remove_action( 'woocommerce_order_status_processing', array( $order, 'order_paid' ), 10 );
        remove_action( 'woocommerce_order_status_completed', array( $order, 'order_paid' ), 10 );
        remove_action( 'woocommerce_order_status_cancelled', array( $order, 'order_cancelled' ), 10 );
        remove_action( 'woocommerce_order_status_refunded', array( $order, 'order_cancelled' ), 10 );
        remove_action( 'delete_user', array( $order, 'delete_user_packages' ), 10 );

        remove_action( 'woocommerce_order_status_processing', array( 'Raspitajse_Commerce_Job_Package_Policy', 'begin_order_activation' ), 1 );
        remove_action( 'woocommerce_order_status_completed', array( 'Raspitajse_Commerce_Job_Package_Policy', 'begin_order_activation' ), 1 );
        remove_action( 'wp_job_board_pro_wc_paid_listings_create_user_package_meta', array( 'Raspitajse_Commerce_Job_Package_Policy', 'stamp_created_entitlement' ), 10 );
        remove_action( 'woocommerce_order_status_processing', array( 'Raspitajse_Commerce_Job_Package_Policy', 'end_order_activation' ), PHP_INT_MAX );
        remove_action( 'woocommerce_order_status_completed', array( 'Raspitajse_Commerce_Job_Package_Policy', 'end_order_activation' ), PHP_INT_MAX );
        remove_action( 'woocommerce_order_status_failed', array( 'Raspitajse_Commerce_Job_Package_Policy', 'revoke_failed_order' ), 10 );
    }

    public static function remove_paid_submit_steps( $steps ) {
        if ( is_array( $steps ) ) {
            unset( $steps['wjbp-choose-packages'], $steps['wjbp-process-packages'] );
        }

        return $steps;
    }

    public static function normalize_free_submission_status( $status, $job = null ) {
        if ( 'pending_payment' !== $status ) {
            return $status;
        }

        return 'on' === wp_job_board_pro_get_option( 'submission_requires_approval' )
            ? 'pending'
            : 'publish';
    }

    public static function force_candidate_application_free() {
        return 'on';
    }

    public static function normalize_candidate_privacy_option( $value ) {
        if ( 'register_employer_with_package' === $value || 'register_employer_contact_with_package' === $value ) {
            return 'register_employer';
        }

        return $value;
    }

    public static function enforce_publication_quota( $data, $postarr, $unsanitized_postarr = array(), $update = false ) {
        if ( ! self::is_enabled() || ! is_array( $data ) || 'job_listing' !== ( isset( $data['post_type'] ) ? $data['post_type'] : '' ) ) {
            return $data;
        }

        self::$frames[] = array( 'lock' => '', 'reason' => '' );
        $frame_index = count( self::$frames ) - 1;

        if ( 'publish' !== ( isset( $data['post_status'] ) ? $data['post_status'] : '' ) || current_user_can( 'manage_options' ) ) {
            return $data;
        }

        $post_id = isset( $postarr['ID'] ) ? absint( $postarr['ID'] ) : 0;
        $identity = self::resolve_publication_identity( $data, $postarr, $unsanitized_postarr, $post_id );
        if ( is_wp_error( $identity ) ) {
            return self::block_publication( $data, $frame_index, $identity->get_error_code() );
        }

        $expiry = self::get_requested_expiry( $postarr, $unsanitized_postarr, $post_id );
        if ( ! self::is_valid_expiry_value( $expiry ) ) {
            return self::block_publication( $data, $frame_index, self::REASON_EXPIRY );
        }

        $lock_name = self::lock_name( $identity['user_id'] );
        if ( ! self::acquire_lock( $lock_name ) ) {
            return self::block_publication( $data, $frame_index, self::REASON_LOCK );
        }
        self::$frames[ $frame_index ]['lock'] = $lock_name;

        $state = self::authoritative_state( $identity['user_id'], $identity['employer_id'], $post_id );
        if ( ! empty( $state['invalid_expiry_ids'] ) ) {
            return self::block_publication( $data, $frame_index, self::REASON_EXPIRY );
        }
        if ( self::ACTIVE_JOB_LIMIT <= $state['used'] ) {
            return self::block_publication( $data, $frame_index, self::REASON_QUOTA );
        }

        return $data;
    }

    private static function resolve_publication_identity( $data, $postarr, $unsanitized_postarr, $post_id ) {
        $stored_profile = $post_id ? absint( get_post_meta( $post_id, '_job_employer_posted_by', true ) ) : 0;
        $input_profile = self::read_input_profile( $postarr, $unsanitized_postarr );

        if ( $stored_profile && $input_profile && $stored_profile !== $input_profile ) {
            return new WP_Error( self::REASON_CROSS_EMPLOYER );
        }

        $profile_id = $stored_profile ? $stored_profile : $input_profile;
        $profile_owner = $profile_id ? self::user_for_valid_employer_profile( $profile_id ) : 0;
        if ( $profile_id && ! $profile_owner ) {
            return new WP_Error( self::REASON_IDENTITY );
        }

        $author_id = isset( $data['post_author'] ) ? absint( $data['post_author'] ) : 0;
        $author_owner = self::canonical_employer_user( $author_id );
        if ( $author_id && ! $author_owner ) {
            return new WP_Error( self::REASON_IDENTITY );
        }
        if ( $profile_owner && $author_owner && $profile_owner !== $author_owner ) {
            return new WP_Error( self::REASON_CROSS_EMPLOYER );
        }

        $owner_id = $profile_owner ? $profile_owner : $author_owner;
        if ( ! $owner_id ) {
            return new WP_Error( self::REASON_IDENTITY );
        }

        $employer_id = absint( WP_Job_Board_Pro_User::get_employer_by_user_id( $owner_id ) );
        if ( ! $employer_id || 'employer' !== get_post_type( $employer_id ) || $owner_id !== absint( WP_Job_Board_Pro_User::get_user_by_employer_id( $employer_id ) ) ) {
            return new WP_Error( self::REASON_IDENTITY );
        }

        $current_owner = self::canonical_employer_user( get_current_user_id() );
        if ( ! $current_owner || $current_owner !== $owner_id ) {
            return new WP_Error( self::REASON_CROSS_EMPLOYER );
        }

        return array( 'user_id' => $owner_id, 'employer_id' => $employer_id );
    }

    private static function canonical_employer_user( $user_id ) {
        $user_id = absint( $user_id );
        if ( ! $user_id || ! class_exists( 'WP_Job_Board_Pro_User' ) ) {
            return 0;
        }

        $owner_id = absint( WP_Job_Board_Pro_User::get_user_id( $user_id ) );
        if ( ! $owner_id ) {
            return 0;
        }

        $profile_id = absint( WP_Job_Board_Pro_User::get_employer_by_user_id( $owner_id ) );

        return $profile_id
            && 'employer' === get_post_type( $profile_id )
            && $owner_id === absint( WP_Job_Board_Pro_User::get_user_by_employer_id( $profile_id ) )
            ? $owner_id
            : 0;
    }

    private static function user_for_valid_employer_profile( $profile_id ) {
        if ( ! class_exists( 'WP_Job_Board_Pro_User' ) || 'employer' !== get_post_type( $profile_id ) ) {
            return 0;
        }

        $user_id = absint( WP_Job_Board_Pro_User::get_user_by_employer_id( $profile_id ) );

        return $user_id && self::canonical_employer_user( $user_id ) === $user_id ? $user_id : 0;
    }

    private static function read_input_profile( $postarr, $unsanitized_postarr ) {
        $key = '_job_employer_posted_by';

        foreach ( array( $unsanitized_postarr, $postarr ) as $source ) {
            if ( isset( $source['meta_input'] ) && is_array( $source['meta_input'] ) && array_key_exists( $key, $source['meta_input'] ) ) {
                return absint( $source['meta_input'][ $key ] );
            }
        }

        if ( isset( $_POST[ $key ] ) && is_scalar( $_POST[ $key ] ) ) {
            return absint( wp_unslash( $_POST[ $key ] ) );
        }

        return 0;
    }

    private static function get_requested_expiry( $postarr, $unsanitized_postarr, $post_id ) {
        $key = '_job_expiry_date';

        foreach ( array( $unsanitized_postarr, $postarr ) as $source ) {
            if ( isset( $source['meta_input'] ) && is_array( $source['meta_input'] ) && array_key_exists( $key, $source['meta_input'] ) ) {
                return is_scalar( $source['meta_input'][ $key ] )
                    ? trim( (string) $source['meta_input'][ $key ] )
                    : '__invalid__';
            }
        }

        if ( isset( $_POST[ $key ] ) ) {
            return is_scalar( $_POST[ $key ] )
                ? trim( (string) wp_unslash( $_POST[ $key ] ) )
                : '__invalid__';
        }

        return $post_id ? trim( (string) get_post_meta( $post_id, $key, true ) ) : '';
    }

    private static function is_valid_expiry_value( $value ) {
        if ( '' === $value ) {
            return true;
        }

        $date = DateTimeImmutable::createFromFormat( '!Y-m-d', $value, wp_timezone() );
        $errors = DateTimeImmutable::getLastErrors();

        return false !== $date
            && ( false === $errors || ( 0 === $errors['warning_count'] && 0 === $errors['error_count'] ) )
            && $date->format( 'Y-m-d' ) === $value;
    }

    private static function authoritative_state( $user_id, $employer_id, $exclude_id = 0 ) {
        global $wpdb;

        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT p.ID
                 FROM {$wpdb->posts} p
                 LEFT JOIN {$wpdb->postmeta} owner_meta
                   ON owner_meta.post_id = p.ID
                  AND owner_meta.meta_key = %s
                 WHERE p.post_type = %s
                   AND p.post_status = %s
                   AND p.ID <> %d
                   AND (
                       CAST(owner_meta.meta_value AS UNSIGNED) = %d
                       OR (owner_meta.meta_id IS NULL AND p.post_author = %d)
                   )
                 ORDER BY p.ID",
                '_job_employer_posted_by',
                'job_listing',
                'publish',
                absint( $exclude_id ),
                absint( $employer_id ),
                absint( $user_id )
            )
        );

        $today = current_datetime()->format( 'Y-m-d' );
        $used = 0;
        $invalid_ids = array();

        foreach ( array_map( 'absint', $ids ) as $job_id ) {
            $values = array_values(
                array_unique(
                    array_map( 'strval', get_post_meta( $job_id, '_job_expiry_date', false ) )
                )
            );
            if ( 1 < count( $values ) ) {
                $invalid_ids[] = $job_id;
                continue;
            }

            $expiry = isset( $values[0] ) ? trim( $values[0] ) : '';
            if ( ! self::is_valid_expiry_value( $expiry ) ) {
                $invalid_ids[] = $job_id;
                continue;
            }
            if ( '' === $expiry || $expiry >= $today ) {
                ++$used;
            }
        }

        return array(
            'used' => $used,
            'remaining' => max( 0, self::ACTIVE_JOB_LIMIT - $used ),
            'invalid_expiry_ids' => $invalid_ids,
        );
    }

    public static function get_quota_state( $user_id, $exclude_id = 0 ) {
        $owner_id = self::canonical_employer_user( $user_id );
        if ( ! $owner_id ) {
            return new WP_Error( self::REASON_IDENTITY, self::reason_message( self::REASON_IDENTITY ) );
        }

        $employer_id = absint( WP_Job_Board_Pro_User::get_employer_by_user_id( $owner_id ) );
        $state = self::authoritative_state( $owner_id, $employer_id, $exclude_id );
        $state['limit'] = self::ACTIVE_JOB_LIMIT;

        return $state;
    }

    private static function lock_name( $user_id ) {
        return 'raspitajse_job_quota_' . substr(
            hash( 'sha256', get_current_blog_id() . '|' . absint( $user_id ) ),
            0,
            40
        );
    }

    private static function acquire_lock( $lock_name ) {
        global $wpdb;

        if ( isset( self::$held_locks[ $lock_name ] ) ) {
            ++self::$held_locks[ $lock_name ];
            return true;
        }

        $acquired = $wpdb->get_var(
            $wpdb->prepare( 'SELECT GET_LOCK(%s, %d)', $lock_name, self::LOCK_TIMEOUT )
        );
        if ( 1 !== (int) $acquired ) {
            return false;
        }

        self::$held_locks[ $lock_name ] = 1;
        do_action( 'raspitajse_free_job_quota_lock_event', 'acquired', $lock_name );

        return true;
    }

    private static function release_lock( $lock_name ) {
        global $wpdb;

        if ( ! isset( self::$held_locks[ $lock_name ] ) ) {
            return;
        }

        --self::$held_locks[ $lock_name ];
        if ( 0 < self::$held_locks[ $lock_name ] ) {
            return;
        }

        unset( self::$held_locks[ $lock_name ] );
        $released = $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) );
        do_action(
            'raspitajse_free_job_quota_lock_event',
            1 === (int) $released ? 'released' : 'release_failed',
            $lock_name
        );
    }

    private static function block_publication( $data, $frame_index, $reason ) {
        $data['post_status'] = 'draft';
        self::$frames[ $frame_index ]['reason'] = $reason;
        self::$last_reason = $reason;
        unset( $_COOKIE['job_add_new_update'] );

        return $data;
    }

    public static function after_insert_post( $post_id, $post, $update, $post_before ) {
        if ( ! $post instanceof WP_Post || 'job_listing' !== $post->post_type ) {
            return;
        }

        $frame = array_pop( self::$frames );
        if ( ! is_array( $frame ) ) {
            return;
        }

        if ( ! empty( $frame['reason'] ) ) {
            update_post_meta( $post_id, self::META_REASON, $frame['reason'] );
            self::$last_blocked_post_id = absint( $post_id );
        } elseif ( 'publish' === $post->post_status ) {
            delete_post_meta( $post_id, self::META_REASON );
        }

        if ( ! empty( $frame['lock'] ) ) {
            self::release_lock( $frame['lock'] );
        }
    }

    public static function release_all_locks() {
        foreach ( array_keys( self::$held_locks ) as $lock_name ) {
            while ( isset( self::$held_locks[ $lock_name ] ) ) {
                self::release_lock( $lock_name );
            }
        }
    }

    public static function register_reason_meta() {
        register_post_meta(
            'job_listing',
            self::META_REASON,
            array(
                'type' => 'string',
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => array( __CLASS__, 'sanitize_reason' ),
                'auth_callback' => static function () {
                    return current_user_can( 'edit_posts' );
                },
            )
        );
    }

    public static function sanitize_reason( $reason ) {
        $reason = sanitize_key( $reason );

        return array_key_exists( $reason, self::reason_messages() ) ? $reason : '';
    }

    public static function reason_message( $reason ) {
        $messages = self::reason_messages();

        return isset( $messages[ $reason ] )
            ? $messages[ $reason ]
            : __( 'The job could not be published safely.', 'raspitajse-commerce' );
    }

    private static function reason_messages() {
        return array(
            self::REASON_QUOTA => __( 'You can have at most three active job listings. Save this job as a draft or close an active listing before publishing.', 'raspitajse-commerce' ),
            self::REASON_IDENTITY => __( 'The employer identity could not be verified. The job was kept as a draft.', 'raspitajse-commerce' ),
            self::REASON_CROSS_EMPLOYER => __( 'This job does not belong to the current employer. It was not published.', 'raspitajse-commerce' ),
            self::REASON_EXPIRY => __( 'The job expiry date must be empty or a valid date in YYYY-MM-DD format.', 'raspitajse-commerce' ),
            self::REASON_LOCK => __( 'The publication quota is busy. The job was kept as a draft; please try again.', 'raspitajse-commerce' ),
        );
    }

    public static function render_submission_reason( $post_id ) {
        $reason = (string) get_post_meta( absint( $post_id ), self::META_REASON, true );
        if ( '' === $reason ) {
            return;
        }

        echo '<div class="alert alert-warning raspitajse-job-quota-notice">'
            . esc_html( self::reason_message( $reason ) )
            . '</div>';
    }

    public static function add_admin_reason_query_arg( $location, $post_id ) {
        if ( absint( $post_id ) !== self::$last_blocked_post_id ) {
            return $location;
        }

        return add_query_arg( 'raspitajse_job_publication_block', self::$last_reason, $location );
    }

    public static function render_admin_reason() {
        if ( empty( $_GET['raspitajse_job_publication_block'] ) || ! current_user_can( 'edit_posts' ) ) {
            return;
        }

        $reason = self::sanitize_reason( wp_unslash( $_GET['raspitajse_job_publication_block'] ) );
        if ( '' === $reason ) {
            return;
        }

        echo '<div class="notice notice-warning"><p>'
            . esc_html( self::reason_message( $reason ) )
            . '</p></div>';
    }
}
