<?php
/**
 * Owned candidate application tracking and employer status workflow.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Raspitajse_Communications_Candidate_Application_Tracking {

    const POST_TYPE = 'job_applicant';
    const STATUS_META = '_raspitajse_application_status';
    const CHANGED_META = '_raspitajse_application_status_changed_at';
    const NOTICE_META_PREFIX = '_raspitajse_application_notice_';
    const AJAX_ACTION = 'raspitajse_application_status_update';

    private static $lock_name = '';

    public static function boot() {
        add_action( 'wp-job-board-pro-process-apply-internal', array( __CLASS__, 'guard_application_request' ), 5, 1 );
        add_filter( 'wp-job-board-pro-add-job-applicant-data', array( __CLASS__, 'guard_insert_data' ), 5, 1 );
        add_action( 'wp-job-board-pro-before-after-job-applicant', array( __CLASS__, 'application_created' ), 5, 4 );
        add_action( 'shutdown', array( __CLASS__, 'release_application_lock' ), PHP_INT_MAX );
        add_action( 'wp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'ajax_change_status' ) );
        add_action( 'wjbp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'ajax_change_status' ) );
        add_action( 'wp_job_board_pro_after_applicant_content', array( __CLASS__, 'render_employer_controls' ), 20, 1 );
        add_action( 'wp_job_board_pro_after_job_content', array( __CLASS__, 'render_candidate_status' ), 20, 1 );
        add_action( 'init', array( __CLASS__, 'retire_legacy_status_actions' ), 100 );
    }

    public static function statuses() {
        return array(
            'submitted' => array(
                'label' => __( 'Prijava poslata', 'raspitajse-communications' ),
                'terminal' => false,
                'next' => array( 'under_review', 'shortlisted', 'interview', 'hired', 'rejected' ),
            ),
            'under_review' => array(
                'label' => __( 'U razmatranju', 'raspitajse-communications' ),
                'terminal' => false,
                'next' => array( 'shortlisted', 'interview', 'hired', 'rejected' ),
            ),
            'shortlisted' => array(
                'label' => __( 'U užem izboru', 'raspitajse-communications' ),
                'terminal' => false,
                'next' => array( 'interview', 'hired', 'rejected' ),
            ),
            'interview' => array(
                'label' => __( 'Poziv na razgovor', 'raspitajse-communications' ),
                'terminal' => false,
                'next' => array( 'hired', 'rejected' ),
            ),
            'hired' => array(
                'label' => __( 'Primljen/a', 'raspitajse-communications' ),
                'terminal' => true,
                'next' => array(),
            ),
            'rejected' => array(
                'label' => __( 'Nije izabran/a', 'raspitajse-communications' ),
                'terminal' => true,
                'next' => array(),
            ),
        );
    }

    public static function applicant_prefix() {
        return defined( 'WP_JOB_BOARD_PRO_APPLICANT_PREFIX' )
            ? WP_JOB_BOARD_PRO_APPLICANT_PREFIX
            : '_applicant_';
    }

    public static function read_status( $application_id ) {
        $owned = get_post_meta( $application_id, self::STATUS_META, true );
        if ( '' !== (string) $owned ) {
            return isset( self::statuses()[ $owned ] ) ? (string) $owned : '';
        }

        $legacy = get_post_meta( $application_id, self::applicant_prefix() . 'app_status', true );
        if ( '' === (string) $legacy ) {
            return 'submitted';
        }
        if ( 'approved' === $legacy ) {
            return 'shortlisted';
        }
        if ( 'rejected' === $legacy ) {
            return 'rejected';
        }
        return '';
    }

    public static function application_context( $application_id ) {
        $application = get_post( $application_id );
        if ( ! $application || self::POST_TYPE !== $application->post_type || 'trash' === $application->post_status ) {
            return self::denied();
        }

        $prefix = self::applicant_prefix();
        $candidate_id = absint( get_post_meta( $application_id, $prefix . 'candidate_id', true ) );
        $job_id = absint( get_post_meta( $application_id, $prefix . 'job_id', true ) );
        $candidate = get_post( $candidate_id );
        $job = get_post( $job_id );
        if ( ! $candidate || 'candidate' !== $candidate->post_type || ! $job || 'job_listing' !== $job->post_type ) {
            return self::denied();
        }

        return array(
            'application' => $application,
            'candidate_id' => $candidate_id,
            'job_id' => $job_id,
            'job' => $job,
        );
    }

    private static function denied() {
        return new WP_Error(
            'raspitajse_application_denied',
            __( 'Zahtev nije dozvoljen.', 'raspitajse-communications' )
        );
    }

    public static function candidate_can_view( $application_id, $user_id ) {
        if ( ! class_exists( 'WP_Job_Board_Pro_User' ) ) {
            return false;
        }
        $context = self::application_context( $application_id );
        if ( is_wp_error( $context ) ) {
            return false;
        }
        $candidate_id = absint( WP_Job_Board_Pro_User::get_candidate_by_user_id( absint( $user_id ) ) );
        return $candidate_id > 0 && $candidate_id === $context['candidate_id'];
    }

    public static function actor_can_manage( $actor_id, $application_id, $context = null ) {
        if ( ! class_exists( 'WP_Job_Board_Pro_User' ) || ! class_exists( 'WP_Job_Board_Pro_Job_Listing' ) ) {
            return false;
        }
        if ( null === $context ) {
            $context = self::application_context( $application_id );
        }
        if ( is_wp_error( $context ) ) {
            return false;
        }

        if ( user_can( $actor_id, 'manage_options' ) && user_can( $actor_id, 'edit_post', $application_id ) ) {
            return true;
        }

        $canonical_actor = absint( WP_Job_Board_Pro_User::get_user_id( absint( $actor_id ) ) );
        $job_owner = absint( WP_Job_Board_Pro_Job_Listing::get_author_id( $context['job_id'] ) );
        if ( ! $canonical_actor || $canonical_actor !== $job_owner ) {
            return false;
        }
        if ( absint( $actor_id ) === $canonical_actor ) {
            return WP_Job_Board_Pro_User::is_employer( $canonical_actor );
        }
        return WP_Job_Board_Pro_User::is_employee( $actor_id )
            && WP_Job_Board_Pro_User::is_employee_can_edit_job( $context['job_id'], $actor_id );
    }

    public static function change_status( $application_id, $requested, $expected, $actor_id ) {
        $application_id = absint( $application_id );
        $requested = sanitize_key( (string) $requested );
        $expected = sanitize_key( (string) $expected );
        $registry = self::statuses();
        $context = self::application_context( $application_id );

        if ( is_wp_error( $context ) || ! self::actor_can_manage( $actor_id, $application_id, $context ) ) {
            return self::denied();
        }
        if ( ! isset( $registry[ $requested ] ) || ! isset( $registry[ $expected ] ) ) {
            return new WP_Error( 'raspitajse_application_invalid_status', __( 'Status nije dostupan.', 'raspitajse-communications' ) );
        }

        $current = self::read_status( $application_id );
        if ( '' === $current || $expected !== $current ) {
            return new WP_Error( 'raspitajse_application_stale', __( 'Status je u međuvremenu promenjen.', 'raspitajse-communications' ) );
        }
        if ( $requested === $current ) {
            return array( 'changed' => false, 'status' => $current );
        }
        if ( $registry[ $current ]['terminal'] || ! in_array( $requested, $registry[ $current ]['next'], true ) ) {
            return new WP_Error( 'raspitajse_application_transition_denied', __( 'Promena statusa nije dozvoljena.', 'raspitajse-communications' ) );
        }

        $stored = get_post_meta( $application_id, self::STATUS_META, true );
        if ( '' === (string) $stored ) {
            if ( ! add_post_meta( $application_id, self::STATUS_META, $current, true ) ) {
                return new WP_Error( 'raspitajse_application_stale', __( 'Status je u međuvremenu promenjen.', 'raspitajse-communications' ) );
            }
        } elseif ( $stored !== $current ) {
            return new WP_Error( 'raspitajse_application_stale', __( 'Status je u međuvremenu promenjen.', 'raspitajse-communications' ) );
        }

        if ( ! update_post_meta( $application_id, self::STATUS_META, $requested, $current ) ) {
            return new WP_Error( 'raspitajse_application_stale', __( 'Status je u međuvremenu promenjen.', 'raspitajse-communications' ) );
        }
        $changed_at = gmdate( 'Y-m-d H:i:s' );
        update_post_meta( $application_id, self::CHANGED_META, $changed_at );

        $notice = self::notify_candidate( $application_id, $current, $requested, $changed_at, $context );
        if ( is_wp_error( $notice ) || false === $notice ) {
            do_action(
                'raspitajse_application_status_notification_observation',
                array( 'application_id' => $application_id, 'result' => 'failed' )
            );
        }

        return array( 'changed' => true, 'status' => $requested, 'notification_sent' => true === $notice );
    }

    public static function initialize_application( $application_id, $job_id, $candidate_id ) {
        $context = self::application_context( $application_id );
        if ( is_wp_error( $context ) || absint( $job_id ) !== $context['job_id'] || absint( $candidate_id ) !== $context['candidate_id'] ) {
            return false;
        }
        if ( '' !== (string) get_post_meta( $application_id, self::STATUS_META, true ) ) {
            return 'submitted' === self::read_status( $application_id );
        }
        $created = add_post_meta( $application_id, self::STATUS_META, 'submitted', true );
        if ( $created ) {
            update_post_meta( $application_id, self::CHANGED_META, gmdate( 'Y-m-d H:i:s' ) );
        }
        return (bool) $created;
    }

    public static function application_created( $application_id, $job_id, $candidate_id, $user_id ) {
        self::initialize_application( $application_id, $job_id, $candidate_id );
        self::release_application_lock();
    }

    public static function find_application( $candidate_id, $job_id ) {
        $prefix = self::applicant_prefix();
        $ids = get_posts(
            array(
                'post_type' => self::POST_TYPE,
                'post_status' => 'any',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'no_found_rows' => true,
                'meta_query' => array(
                    'relation' => 'AND',
                    array( 'key' => $prefix . 'candidate_id', 'value' => absint( $candidate_id ), 'compare' => '=' ),
                    array( 'key' => $prefix . 'job_id', 'value' => absint( $job_id ), 'compare' => '=' ),
                ),
            )
        );
        return empty( $ids ) ? 0 : absint( $ids[0] );
    }

    public static function acquire_application_lock( $candidate_id, $job_id ) {
        global $wpdb;
        if ( self::$lock_name ) {
            return false;
        }
        $blog_id = function_exists( 'get_current_blog_id' ) ? get_current_blog_id() : 1;
        $name = 'raspapp:' . substr( hash( 'sha256', $blog_id . ':' . absint( $candidate_id ) . ':' . absint( $job_id ) ), 0, 56 );
        $acquired = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 0)', $name ) );
        if ( '1' !== (string) $acquired ) {
            return false;
        }
        self::$lock_name = $name;
        return true;
    }

    public static function release_application_lock() {
        global $wpdb;
        if ( '' === self::$lock_name ) {
            return;
        }
        $name = self::$lock_name;
        self::$lock_name = '';
        $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $name ) );
    }

    /**
     * Cover current direct insert_applicant callers (register and social apply).
     * The vendor filter provides post_author; the job is resolved only from its
     * fixed submission fields/cookies and then validated as a saved job.
     */
    public static function guard_insert_data( $post_args ) {
        if ( self::POST_TYPE !== ( $post_args['post_type'] ?? '' ) || self::$lock_name ) {
            return $post_args;
        }
        if ( ! class_exists( 'WP_Job_Board_Pro_User' ) ) {
            self::send_json_failure( __( 'Prijava trenutno nije dostupna.', 'raspitajse-communications' ) );
        }
        $user_id = absint( $post_args['post_author'] ?? 0 );
        $candidate_id = absint( WP_Job_Board_Pro_User::get_candidate_by_user_id( $user_id ) );
        $job_id = self::request_job_id();
        $job = get_post( $job_id );
        if ( ! $candidate_id || ! $job || 'job_listing' !== $job->post_type ) {
            self::send_json_failure( __( 'Prijava trenutno nije dostupna.', 'raspitajse-communications' ) );
        }
        if ( ! self::acquire_application_lock( $candidate_id, $job_id ) ) {
            self::send_json_failure( __( 'Prijava je već u obradi. Pokušajte ponovo.', 'raspitajse-communications' ) );
        }
        if ( self::find_application( $candidate_id, $job_id ) ) {
            self::release_application_lock();
            self::send_json_failure( __( 'Već ste se prijavili na ovaj oglas.', 'raspitajse-communications' ) );
        }
        return $post_args;
    }

    private static function request_job_id() {
        if ( isset( $_POST['job_id'] ) ) {
            return absint( wp_unslash( $_POST['job_id'] ) );
        }
        foreach ( array( 'linkedin', 'facebook', 'twitter', 'google' ) as $provider ) {
            $key = 'wp_job_board_pro_' . $provider . '_job_id';
            if ( isset( $_COOKIE[ $key ] ) ) {
                return absint( wp_unslash( $_COOKIE[ $key ] ) );
            }
        }
        return 0;
    }

    public static function guard_application_request( $request ) {
        if ( ! class_exists( 'WP_Job_Board_Pro_User' ) || ! is_user_logged_in() ) {
            return;
        }
        $candidate_id = absint( WP_Job_Board_Pro_User::get_candidate_by_user_id( get_current_user_id() ) );
        $job_id = isset( $request['job_id'] ) ? absint( $request['job_id'] ) : 0;
        $job = get_post( $job_id );
        if ( ! $candidate_id || ! $job || 'job_listing' !== $job->post_type ) {
            return;
        }
        if ( ! self::acquire_application_lock( $candidate_id, $job_id ) ) {
            self::send_json_failure( __( 'Prijava je već u obradi. Pokušajte ponovo.', 'raspitajse-communications' ) );
        }
        if ( self::find_application( $candidate_id, $job_id ) ) {
            self::release_application_lock();
            self::send_json_failure( __( 'Već ste se prijavili na ovaj oglas.', 'raspitajse-communications' ) );
        }
    }

    public static function ajax_change_status() {
        $application_id = isset( $_POST['application_id'] ) ? absint( wp_unslash( $_POST['application_id'] ) ) : 0;
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! is_user_logged_in() || ! wp_verify_nonce( $nonce, 'raspitajse-application-status-' . $application_id ) ) {
            self::send_json_failure( __( 'Zahtev nije dozvoljen.', 'raspitajse-communications' ) );
        }
        $result = self::change_status(
            $application_id,
            isset( $_POST['status'] ) ? wp_unslash( $_POST['status'] ) : '',
            isset( $_POST['expected_status'] ) ? wp_unslash( $_POST['expected_status'] ) : '',
            get_current_user_id()
        );
        if ( is_wp_error( $result ) ) {
            self::send_json_failure( $result->get_error_message() );
        }
        wp_send_json_success( $result );
    }

    private static function send_json_failure( $message ) {
        wp_send_json_error( array( 'message' => $message ) );
    }

    public static function candidate_view_model( $application_id, $user_id ) {
        if ( ! self::candidate_can_view( $application_id, $user_id ) ) {
            return self::denied();
        }
        self::enqueue_assets();
        $status = self::read_status( $application_id );
        $registry = self::statuses();
        return array(
            'status' => $status,
            'label' => isset( $registry[ $status ] ) ? $registry[ $status ]['label'] : __( 'Status nije dostupan', 'raspitajse-communications' ),
            'changed_at' => (string) get_post_meta( $application_id, self::CHANGED_META, true ),
        );
    }

    public static function render_candidate_status( $job_id ) {
        if ( ! class_exists( 'WP_Job_Board_Pro_User' ) || ! is_user_logged_in() ) {
            return;
        }
        $candidate_id = absint( WP_Job_Board_Pro_User::get_candidate_by_user_id( get_current_user_id() ) );
        $application_id = $candidate_id ? self::find_application( $candidate_id, $job_id ) : 0;
        if ( ! $application_id ) {
            return;
        }
        $model = self::candidate_view_model( $application_id, get_current_user_id() );
        if ( is_wp_error( $model ) ) {
            return;
        }
        $date = '';
        if ( $model['changed_at'] ) {
            $timestamp = strtotime( $model['changed_at'] . ' UTC' );
            $date = $timestamp ? wp_date( get_option( 'date_format' ), $timestamp ) : '';
        }
        echo '<div class="raspitajse-application-status" data-application-id="' . esc_attr( $application_id ) . '">';
        echo '<span class="raspitajse-application-status__label">' . esc_html( $model['label'] ) . '</span>';
        if ( $date ) {
            echo ' <time datetime="' . esc_attr( $model['changed_at'] ) . '">' . esc_html( $date ) . '</time>';
        }
        echo '</div>';
    }

    public static function render_employer_controls( $application_id ) {
        if ( ! is_user_logged_in() || ! self::actor_can_manage( get_current_user_id(), $application_id ) ) {
            return;
        }
        self::enqueue_assets();
        $status = self::read_status( $application_id );
        $registry = self::statuses();
        if ( ! isset( $registry[ $status ] ) ) {
            echo '<p class="raspitajse-application-status-unavailable">' . esc_html__( 'Status nije dostupan', 'raspitajse-communications' ) . '</p>';
            return;
        }
        echo '<form class="raspitajse-application-status-form">';
        echo '<input type="hidden" name="application_id" value="' . esc_attr( $application_id ) . '">';
        echo '<input type="hidden" name="expected_status" value="' . esc_attr( $status ) . '">';
        echo '<input type="hidden" name="nonce" value="' . esc_attr( wp_create_nonce( 'raspitajse-application-status-' . $application_id ) ) . '">';
        echo '<label>' . esc_html__( 'Status prijave', 'raspitajse-communications' ) . ' <select name="status">';
        echo '<option value="' . esc_attr( $status ) . '">' . esc_html( $registry[ $status ]['label'] ) . '</option>';
        foreach ( $registry[ $status ]['next'] as $next ) {
            echo '<option value="' . esc_attr( $next ) . '">' . esc_html( $registry[ $next ]['label'] ) . '</option>';
        }
        echo '</select></label> <button type="submit">' . esc_html__( 'Sačuvaj status', 'raspitajse-communications' ) . '</button>';
        echo '<span class="raspitajse-application-status-form__message" aria-live="polite"></span></form>';
    }

    public static function enqueue_assets() {
        wp_enqueue_script(
            'raspitajse-application-tracking',
            plugins_url( '../assets/application-tracking.js', __FILE__ ),
            array(),
            '1.0.0',
            true
        );
        wp_localize_script(
            'raspitajse-application-tracking',
            'raspitajseApplicationTracking',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'action' => self::AJAX_ACTION,
                'failure' => __( 'Promena statusa nije uspela.', 'raspitajse-communications' ),
            )
        );
    }

    public static function retire_legacy_status_actions() {
        if ( ! class_exists( 'WP_Job_Board_Pro_Applicant' ) ) {
            return;
        }
        $actions = array(
            'wp_job_board_pro_ajax_reject_applied' => 'process_applicant_reject',
            'wp_job_board_pro_ajax_undo_reject_applied' => 'process_undo_applicant_reject',
            'wp_job_board_pro_ajax_approve_applied' => 'process_applicant_approve',
            'wp_job_board_pro_ajax_undo_approve_applied' => 'process_undo_applicant_approve',
        );
        foreach ( $actions as $action => $method ) {
            remove_action( 'wjbp_ajax_' . $action, array( 'WP_Job_Board_Pro_Applicant', $method ) );
        }
    }

    public static function notify_candidate( $application_id, $old, $new, $changed_at, $context = null ) {
        $notice_key = self::NOTICE_META_PREFIX . substr( hash( 'sha256', $old . '>' . $new ), 0, 20 );
        if ( ! add_post_meta( $application_id, $notice_key, $changed_at, true ) ) {
            return true;
        }
        if ( null === $context ) {
            $context = self::application_context( $application_id );
        }
        if ( is_wp_error( $context ) || ! class_exists( 'WP_Job_Board_Pro_User' ) ) {
            return new WP_Error( 'raspitajse_application_recipient_unavailable', 'Recipient unavailable.' );
        }
        $user_id = absint( WP_Job_Board_Pro_User::get_user_by_candidate_id( $context['candidate_id'] ) );
        $user = $user_id ? get_userdata( $user_id ) : false;
        if ( ! $user || ! is_email( $user->user_email ) ) {
            return new WP_Error( 'raspitajse_application_recipient_unavailable', 'Recipient unavailable.' );
        }
        $registry = self::statuses();
        $dashboard_id = absint( wp_job_board_pro_get_option( 'user_dashboard_page_id' ) );
        $dashboard_url = $dashboard_id ? get_permalink( $dashboard_id ) : home_url( '/' );
        $dashboard_url = add_query_arg( 'application', $application_id, $dashboard_url );
        $subject = sprintf( __( 'Status prijave: %s', 'raspitajse-communications' ), $context['job']->post_title );
        $message = sprintf(
            __( 'Status vaše prijave za „%1$s” je sada: %2$s.' . "\n\n" . 'Pregled prijava: %3$s', 'raspitajse-communications' ),
            $context['job']->post_title,
            $registry[ $new ]['label'],
            esc_url_raw( $dashboard_url )
        );
        $payload = array( 'to' => $user->user_email, 'subject' => $subject, 'message' => $message );
        $intercepted = apply_filters( 'raspitajse_application_status_notification_transport', null, $payload );
        if ( null !== $intercepted ) {
            return $intercepted;
        }
        return Raspitajse_Communications_Transport::send(
            Raspitajse_Communications_Sender_Policy::CHANNEL_APPLICATION_STATUS,
            $user->user_email,
            $subject,
            $message
        );
    }
}
