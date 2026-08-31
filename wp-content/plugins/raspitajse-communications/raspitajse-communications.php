<?php
/**
 * Plugin Name: Raspitajse Communications
 * Description: Raspitajse-owned email transport and communication infrastructure.
 * Version: 0.3.0
 * Author: Raspitajse.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Raspitajse_Communications_Transport {

    const ENABLE_FLAG   = 'RASPITAJSE_COMMUNICATIONS_TRANSPORT_ENABLED';
    const ENABLE_OPTION = 'raspitajse_communications_transport_enabled';

    /**
     * Headers captured from the current wp_mail call.
     *
     * @var string
     */
    private static $mail_headers = '';

    /**
     * Original wp_mail payloads, kept as a stack so nested mail calls remain safe.
     *
     * @var array
     */
    private static $mail_context_stack = array();

    /**
     * Register transport hooks only after an explicit migration flag is enabled.
     */
    public static function boot() {
        if ( ! self::is_enabled() ) {
            return;
        }

        add_filter( 'wp_mail', array( __CLASS__, 'capture_mail_args' ), PHP_INT_MIN );

        // Restore a payload corrupted by legacy callbacks before the staging
        // mail-safety MU plugin applies its final redirect/prefix at PHP_INT_MAX.
        add_filter( 'wp_mail', array( __CLASS__, 'restore_mail_args' ), PHP_INT_MAX - 100 );

        add_filter( 'wp_mail_from', array( __CLASS__, 'filter_from' ), PHP_INT_MAX );
        add_filter( 'wp_mail_from_name', array( __CLASS__, 'filter_from_name' ), PHP_INT_MAX );
        add_action( 'phpmailer_init', array( __CLASS__, 'configure_phpmailer' ), 20 );
    }

    /**
     * Whether the new communications transport is explicitly enabled.
     *
     * Production remains fail-closed unless the wp-config constant is set.
     * Staging can use a reversible WordPress option during migration testing.
     */
    public static function is_enabled() {
        if ( defined( self::ENABLE_FLAG ) ) {
            return true === constant( self::ENABLE_FLAG );
        }

        if ( ! self::is_staging() ) {
            return false;
        }

        return '1' === (string) get_option( self::ENABLE_OPTION, '0' );
    }

    /**
     * Capture the original mail payload without mutating it.
     *
     * The legacy child-theme callback was registered on wp_mail without
     * returning $args. Because wp_mail is a filter hook, that callback can
     * replace the payload with null. Keep an original copy so we can restore
     * it before later filters run.
     */
    public static function capture_mail_args( $args ) {
        $context = is_array( $args ) ? $args : array();

        self::$mail_context_stack[] = $context;
        self::$mail_headers         = self::headers_to_string(
            isset( $context['headers'] ) ? $context['headers'] : array()
        );

        return $args;
    }

    /**
     * Restore required wp_mail fields if a legacy callback corrupted them.
     */
    public static function restore_mail_args( $args ) {
        if ( empty( self::$mail_context_stack ) ) {
            return $args;
        }

        $original = end( self::$mail_context_stack );

        if ( ! is_array( $args ) ) {
            $args = $original;
        } else {
            foreach ( array( 'to', 'subject', 'message', 'headers', 'attachments' ) as $key ) {
                if ( ! array_key_exists( $key, $args ) && array_key_exists( $key, $original ) ) {
                    $args[ $key ] = $original[ $key ];
                }
            }
        }

        // Some legacy callers pass null as the optional attachments argument.
        // WordPress expects an array or string and emits a PHP deprecation when
        // null reaches its attachment normalization logic on newer PHP versions.
        if ( array_key_exists( 'attachments', $args ) && null === $args['attachments'] ) {
            $args['attachments'] = array();
        }

        return $args;
    }

    /**
     * Configure Amazon SES SMTP using constants already provided by wp-config.
     */
    public static function configure_phpmailer( $phpmailer ) {
        $required = array( 'SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASS' );

        foreach ( $required as $constant ) {
            if ( ! defined( $constant ) ) {
                error_log( '[Raspitajse Communications] SMTP transport skipped: missing ' . $constant . '.' );
                self::finish_mail_context();
                return;
            }
        }

        $phpmailer->isSMTP();
        $phpmailer->Host       = SMTP_HOST;
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Port       = SMTP_PORT;
        $phpmailer->Username   = SMTP_USER;
        $phpmailer->Password   = SMTP_PASS;
        $phpmailer->SMTPSecure = 'tls';
        $phpmailer->isHTML( true );

        // Preserve the current staging behavior while sender policy is migrated.
        if (
            self::is_staging()
            && ( empty( $phpmailer->From ) || false === strpos( $phpmailer->From, '@stage.raspitajse.com' ) )
        ) {
            $phpmailer->From     = 'noreply@stage.raspitajse.com';
            $phpmailer->FromName = self::default_from_name();
        }

        self::finish_mail_context();
    }

    /**
     * Respect per-message From headers and provide the current staging fallback.
     */
    public static function filter_from( $email ) {
        if ( self::has_custom_from_header() ) {
            return $email;
        }

        if ( self::is_staging() ) {
            return 'noreply-system@stage.raspitajse.com';
        }

        // Do not invent production sender policy during the staging refactor.
        if ( defined( 'SMTP_FROM' ) && is_email( SMTP_FROM ) ) {
            return SMTP_FROM;
        }

        return $email;
    }

    /**
     * Respect per-message sender names and preserve the existing brand fallback.
     */
    public static function filter_from_name( $name ) {
        if ( self::has_custom_from_header() ) {
            return $name;
        }

        if ( defined( 'SMTP_FROM_NAME' ) && '' !== trim( (string) SMTP_FROM_NAME ) ) {
            return (string) SMTP_FROM_NAME;
        }

        return self::default_from_name();
    }

    /**
     * Whether the current mail call explicitly supplied a From header.
     */
    private static function has_custom_from_header() {
        return '' !== self::$mail_headers
            && false !== stripos( self::$mail_headers, 'From:' );
    }

    /**
     * Convert wp_mail headers to the string format used by sender detection.
     */
    private static function headers_to_string( $headers ) {
        if ( is_array( $headers ) ) {
            return implode( "\n", $headers );
        }

        return (string) $headers;
    }

    /**
     * Close the current mail context and restore the previous nested context.
     */
    private static function finish_mail_context() {
        if ( ! empty( self::$mail_context_stack ) ) {
            array_pop( self::$mail_context_stack );
        }

        if ( empty( self::$mail_context_stack ) ) {
            self::$mail_headers = '';
            return;
        }

        $previous = end( self::$mail_context_stack );
        self::$mail_headers = self::headers_to_string(
            isset( $previous['headers'] ) ? $previous['headers'] : array()
        );
    }

    /**
     * Keep environment checks in one place.
     */
    private static function is_staging() {
        return function_exists( 'wp_get_environment_type' )
            && 'staging' === wp_get_environment_type();
    }

    private static function default_from_name() {
        return 'Raspitajse.com - Vaš pouzdan AI model';
    }
}

final class Raspitajse_Communications_Alert_Security {

    public static function boot() {
        add_filter( 'register_post_type_args', array( __CLASS__, 'restrict_alert_rest' ), 10, 2 );
        add_action( 'plugins_loaded', array( __CLASS__, 'replace_vendor_callbacks' ), 100 );
    }

    /**
     * Saved alert searches are private application state, not public REST data.
     */
    public static function restrict_alert_rest( $args, $post_type ) {
        if ( in_array( $post_type, array( 'job_alert', 'candidate_alert' ), true ) ) {
            $args['show_in_rest'] = false;
        }

        return $args;
    }

    /**
     * Replace all active vendor mutations, including the anonymous routes, with
     * one owned handler per operation.
     */
    public static function replace_vendor_callbacks() {
        $routes = array(
            array(
                'vendor_class'  => 'WP_Job_Board_Pro_Job_Alert',
                'vendor_method' => 'process_add_job_alert',
                'owned_method'  => 'process_add_job_alert',
                'hooks'         => array(
                    'wjbp_ajax_wp_job_board_pro_ajax_add_job_alert',
                    'wp_ajax_wp_job_board_pro_ajax_add_job_alert',
                    'wp_ajax_nopriv_wp_job_board_pro_ajax_add_job_alert',
                ),
            ),
            array(
                'vendor_class'  => 'WP_Job_Board_Pro_Job_Alert',
                'vendor_method' => 'process_remove_job_alert',
                'owned_method'  => 'process_remove_job_alert',
                'hooks'         => array(
                    'wjbp_ajax_wp_job_board_pro_ajax_remove_job_alert',
                    'wp_ajax_wp_job_board_pro_ajax_remove_job_alert',
                    'wp_ajax_nopriv_wp_job_board_pro_ajax_remove_job_alert',
                ),
            ),
            array(
                'vendor_class'  => 'WP_Job_Board_Pro_Candidate_Alert',
                'vendor_method' => 'process_add_candidate_alert',
                'owned_method'  => 'process_add_candidate_alert',
                'hooks'         => array(
                    'wjbp_ajax_wp_job_board_pro_ajax_add_candidate_alert',
                    'wp_ajax_wp_job_board_pro_ajax_add_candidate_alert',
                    'wp_ajax_nopriv_wp_job_board_pro_ajax_add_candidate_alert',
                ),
            ),
            array(
                'vendor_class'  => 'WP_Job_Board_Pro_Candidate_Alert',
                'vendor_method' => 'process_remove_candidate_alert',
                'owned_method'  => 'process_remove_candidate_alert',
                'hooks'         => array(
                    'wjbp_ajax_wp_job_board_pro_ajax_remove_candidate_alert',
                    'wp_ajax_wp_job_board_pro_ajax_remove_candidate_alert',
                    'wp_ajax_nopriv_wp_job_board_pro_ajax_remove_candidate_alert',
                ),
            ),
        );

        foreach ( $routes as $route ) {
            if ( ! class_exists( $route['vendor_class'] ) ) {
                continue;
            }

            foreach ( $route['hooks'] as $hook ) {
                remove_action( $hook, array( $route['vendor_class'], $route['vendor_method'] ), 10 );
                add_action( $hook, array( __CLASS__, $route['owned_method'] ), 10 );
            }
        }
    }

    public static function process_add_job_alert() {
        self::send_response( self::create_alert( 'job_alert', $_POST ) );
    }

    public static function process_remove_job_alert() {
        self::send_response( self::delete_alert( 'job_alert', $_POST ) );
    }

    public static function process_add_candidate_alert() {
        self::send_response( self::create_alert( 'candidate_alert', $_POST ) );
    }

    public static function process_remove_candidate_alert() {
        self::send_response( self::delete_alert( 'candidate_alert', $_POST ) );
    }

    /**
     * Validate and create an alert without trusting client identity or filters.
     * This application service is also used by the bounded staging fixture.
     */
    public static function create_alert( $post_type, $request ) {
        $config = self::get_config( $post_type );
        if ( ! $config ) {
            return self::failure( __( 'Nevažeći tip obaveštenja.', 'raspitajse-communications' ) );
        }

        $failure = self::authorize( $config );
        if ( $failure ) {
            return $failure;
        }

        if ( ! is_array( $request ) || ! self::verify_nonce( $request, $config['add_nonce'] ) ) {
            return self::failure( __( 'Sigurnosna provjera nije uspjela.', 'raspitajse-communications' ) );
        }

        $allowed_filters = self::get_allowed_filter_keys( $post_type );
        $allowed_keys    = array_fill_keys(
            array_merge(
                array( 'action', 'nonce', '_wp_http_referer', 'name', 'email_frequency' ),
                $allowed_filters
            ),
            true
        );

        // Read only allowlisted keys; benign URL extras are never persisted.
        if ( ! self::has_no_unknown_structures( $request, $allowed_keys ) ) {
            return self::failure( __( 'Zahtjev sadrži nedozvoljenu strukturu.', 'raspitajse-communications' ) );
        }

        if (
            ! self::has_expected_action( $request, $config['add_request_action'] )
            || ! self::has_scalar_referrer( $request )
        ) {
            return self::failure( __( 'Zahtjev sadrži nevažeća kontrolna polja.', 'raspitajse-communications' ) );
        }

        if ( ! isset( $request['name'] ) || ! is_scalar( $request['name'] ) ) {
            return self::failure( __( 'Naziv je obavezan.', 'wp-job-board-pro' ) );
        }

        $name = sanitize_text_field( wp_unslash( (string) $request['name'] ) );
        if ( '' === $name ) {
            return self::failure( __( 'Naziv je obavezan.', 'wp-job-board-pro' ) );
        }

        if ( ! isset( $request['email_frequency'] ) || ! is_scalar( $request['email_frequency'] ) ) {
            return self::failure( __( 'Učestalost e-pošte je obavezna.', 'wp-job-board-pro' ) );
        }

        $frequency   = sanitize_key( wp_unslash( (string) $request['email_frequency'] ) );
        $frequencies = call_user_func( array( $config['vendor_class'], 'get_email_frequency' ) );
        if ( ! is_array( $frequencies ) || ! array_key_exists( $frequency, $frequencies ) ) {
            return self::failure( __( 'Učestalost e-pošte nije dozvoljena.', 'raspitajse-communications' ) );
        }

        $alert_query = array();
        foreach ( $allowed_filters as $filter_key ) {
            if ( ! array_key_exists( $filter_key, $request ) ) {
                continue;
            }

            $valid           = true;
            $sanitized_value = self::sanitize_filter_value( $request[ $filter_key ], $valid );
            if ( ! $valid ) {
                return self::failure( __( 'Filter sadrži nedozvoljenu strukturu.', 'raspitajse-communications' ) );
            }

            if ( '' !== $sanitized_value && array() !== $sanitized_value ) {
                $alert_query[ $filter_key ] = $sanitized_value;
            }
        }

        $user_id = get_current_user_id();
        if ( self::owner_has_alert( $post_type, $user_id ) ) {
            return self::failure( $config['limit_message'] );
        }

        $profile_id = 0;
        if ( 'job_alert' === $post_type ) {
            $profile_id = absint( WP_Job_Board_Pro_User::get_candidate_by_user_id( $user_id ) );
            if ( ! $profile_id || 'candidate' !== get_post_type( $profile_id ) ) {
                return self::failure( $config['auth_message'] );
            }
        }

        $post_args = array(
            'post_title'   => $name,
            'post_type'    => $post_type,
            'post_content' => '',
            'post_status'  => 'publish',
            'post_author'  => $user_id,
        );

        do_action( $config['before_add_action'] );

        $alert_id = wp_insert_post( $post_args, true );
        if ( is_wp_error( $alert_id ) || ! $alert_id ) {
            return self::failure( $config['create_error'] );
        }

        $meta_ok = self::write_meta( $alert_id, $config['prefix'] . 'email_frequency', $frequency );
        if ( $meta_ok && $alert_query ) {
            $meta_ok = self::write_meta( $alert_id, $config['prefix'] . 'alert_query', $alert_query );
        }
        if ( $meta_ok && $profile_id ) {
            $meta_ok = self::write_meta( $alert_id, $config['prefix'] . 'candidate_id', $profile_id );
        }

        if ( ! $meta_ok ) {
            wp_delete_post( $alert_id, true );
            return self::failure( $config['create_error'] );
        }

        do_action( $config['after_add_action'], $alert_id );

        return array(
            'status' => true,
            'msg'    => $config['create_success'],
        );
    }

    /**
     * Delete only an exact alert type owned by the authenticated role/profile.
     */
    public static function delete_alert( $post_type, $request ) {
        $config = self::get_config( $post_type );
        if ( ! $config ) {
            return self::failure( __( 'Nevažeći tip obaveštenja.', 'raspitajse-communications' ) );
        }

        $failure = self::authorize( $config );
        if ( $failure ) {
            return $failure;
        }

        if ( ! is_array( $request ) || ! self::verify_nonce( $request, $config['remove_nonce'] ) ) {
            return self::failure( __( 'Sigurnosna provjera nije uspjela.', 'raspitajse-communications' ) );
        }

        $allowed_keys = array_fill_keys( array( 'action', 'nonce', '_wp_http_referer', 'alert_id' ), true );
        if (
            ! self::has_only_allowed_keys( $request, $allowed_keys )
            || ! self::has_expected_action( $request, $config['remove_request_action'] )
            || ! self::has_scalar_referrer( $request )
        ) {
            return self::failure( __( 'Zahtjev sadrži nedozvoljena polja.', 'raspitajse-communications' ) );
        }

        if ( ! isset( $request['alert_id'] ) || ! self::is_positive_integer( $request['alert_id'] ) ) {
            return self::failure( $config['remove_denied'] );
        }

        $alert_id = (int) $request['alert_id'];
        $post     = get_post( $alert_id );
        if (
            ! $post
            || $post_type !== $post->post_type
            || get_current_user_id() !== (int) $post->post_author
        ) {
            return self::failure( $config['remove_denied'] );
        }

        if ( $config['before_remove_action'] ) {
            do_action( $config['before_remove_action'], $alert_id );
        }

        if ( ! wp_delete_post( $alert_id ) ) {
            return self::failure( $config['remove_error'] );
        }

        return array(
            'status' => true,
            'msg'    => $config['remove_success'],
        );
    }

    private static function authorize( $config ) {
        if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) {
            return self::failure( $config['auth_message'] );
        }

        $user = wp_get_current_user();
        if (
            ! in_array( $config['role'], (array) $user->roles, true )
            || ! call_user_func( array( 'WP_Job_Board_Pro_User', $config['role_helper'] ), $user->ID )
        ) {
            return self::failure( $config['auth_message'] );
        }

        return false;
    }

    private static function verify_nonce( $request, $action ) {
        return isset( $request['nonce'] )
            && is_scalar( $request['nonce'] )
            && (bool) wp_verify_nonce(
                sanitize_text_field( wp_unslash( (string) $request['nonce'] ) ),
                $action
            );
    }

    private static function has_only_allowed_keys( $request, $allowed_keys ) {
        foreach ( array_keys( $request ) as $key ) {
            if ( ! is_string( $key ) || ! isset( $allowed_keys[ $key ] ) ) {
                return false;
            }
        }

        return true;
    }

    private static function has_no_unknown_structures( $request, $allowed_keys ) {
        foreach ( $request as $key => $value ) {
            if ( ! is_string( $key ) ) {
                return false;
            }

            if ( ! isset( $allowed_keys[ $key ] ) && ! is_scalar( $value ) ) {
                return false;
            }
        }

        return true;
    }

    private static function has_expected_action( $request, $expected ) {
        return isset( $request['action'] )
            && is_scalar( $request['action'] )
            && $expected === sanitize_key( wp_unslash( (string) $request['action'] ) );
    }

    private static function has_scalar_referrer( $request ) {
        return ! isset( $request['_wp_http_referer'] ) || is_scalar( $request['_wp_http_referer'] );
    }

    private static function owner_has_alert( $post_type, $user_id ) {
        $query = new WP_Query(
            array(
                'post_type'              => $post_type,
                'post_status'            => 'any',
                'author'                 => $user_id,
                'posts_per_page'         => 1,
                'fields'                 => 'ids',
                'no_found_rows'          => true,
                'orderby'                => 'none',
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            )
        );

        return ! empty( $query->posts );
    }

    /**
     * Build the allowlist from the filter layer active at runtime, adding only
     * companion keys that the same layer consumes.
     */
    private static function get_allowed_filter_keys( $post_type ) {
        $class  = 'job_alert' === $post_type
            ? 'WP_Job_Board_Pro_Job_Filter'
            : 'WP_Job_Board_Pro_Candidate_Filter';
        $fields = class_exists( $class ) ? call_user_func( array( $class, 'get_fields' ) ) : array();
        $keys   = array();

        foreach ( array_keys( is_array( $fields ) ? $fields : array() ) as $field_key ) {
            if ( is_string( $field_key ) && '' !== $field_key ) {
                $keys[] = 'filter-' . $field_key;
            }
        }

        if ( isset( $fields['center-location'] ) ) {
            $keys[] = 'filter-center-latitude';
            $keys[] = 'filter-center-longitude';
            $keys[] = 'filter-distance';
        }

        if ( isset( $fields['salary'] ) ) {
            $keys[] = 'filter-salary-from';
            $keys[] = 'filter-salary-to';
        }

        $keys[] = 'filter-orderby';
        if ( 'candidate_alert' === $post_type ) {
            $keys[] = 'filter-tag';
        }

        return array_values( array_unique( $keys ) );
    }

    /**
     * Accept a scalar or one-dimensional list of scalars. Nested structures,
     * objects and oversized lists are rejected rather than serialized.
     */
    private static function sanitize_filter_value( $value, &$valid ) {
        if ( is_array( $value ) ) {
            if ( count( $value ) > 100 ) {
                $valid = false;
                return array();
            }

            $sanitized = array();
            foreach ( $value as $item ) {
                if ( ! is_scalar( $item ) ) {
                    $valid = false;
                    return array();
                }

                $item = sanitize_text_field( wp_unslash( (string) $item ) );
                if ( '' !== $item ) {
                    $sanitized[] = $item;
                }
            }

            return $sanitized;
        }

        if ( ! is_scalar( $value ) ) {
            $valid = false;
            return '';
        }

        return sanitize_text_field( wp_unslash( (string) $value ) );
    }

    private static function is_positive_integer( $value ) {
        if ( is_int( $value ) ) {
            return $value > 0;
        }

        return is_string( $value ) && 1 === preg_match( '/^[1-9][0-9]*$/D', $value );
    }

    private static function write_meta( $post_id, $key, $value ) {
        update_post_meta( $post_id, $key, $value );
        $stored = get_post_meta( $post_id, $key, true );

        if ( is_array( $value ) ) {
            return $value === $stored;
        }

        return (string) $value === (string) $stored;
    }

    private static function send_response( $response ) {
        echo wp_json_encode( $response );
        exit;
    }

    private static function failure( $message ) {
        return array(
            'status' => false,
            'msg'    => $message,
        );
    }

    private static function get_config( $post_type ) {
        if (
            ! class_exists( 'WP_Job_Board_Pro_User' )
            || ! class_exists( 'WP_Job_Board_Pro_Job_Alert' )
            || ! class_exists( 'WP_Job_Board_Pro_Candidate_Alert' )
        ) {
            return false;
        }

        if ( 'job_alert' === $post_type ) {
            return array(
                'vendor_class'          => 'WP_Job_Board_Pro_Job_Alert',
                'role'                  => 'wp_job_board_pro_candidate',
                'role_helper'           => 'is_candidate',
                'prefix'                => WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX,
                'add_nonce'             => 'wp-job-board-pro-add-job-alert-nonce',
                'remove_nonce'          => 'wp-job-board-pro-remove-job-alert-nonce',
                'add_request_action'    => 'wp_job_board_pro_ajax_add_job_alert',
                'remove_request_action' => 'wp_job_board_pro_ajax_remove_job_alert',
                'before_add_action'     => 'wp-job-board-pro-before-add-job-alert',
                'after_add_action'      => 'wp-job-board-pro-after-add-job-alert',
                'before_remove_action'  => 'wp-job-board-pro-before-remove-job-alert',
                'auth_message'          => __( 'Prijavite se kao „Kandidat“ da biste upravljali obaveštenjima o poslovima.', 'wp-job-board-pro' ),
                'limit_message'         => __( 'Možete imati najviše jedno obaveštenje o poslovima.', 'wp-job-board-pro' ),
                'create_success'        => __( 'Obaveštenje o poslovima je uspešno dodato.', 'wp-job-board-pro' ),
                'create_error'          => __( 'Greška pri dodavanju obaveštenja o poslovima.', 'wp-job-board-pro' ),
                'remove_denied'         => __( 'Ne možete ukloniti ovo obaveštenje o poslovima.', 'wp-job-board-pro' ),
                'remove_success'        => __( 'Obaveštenje o poslovima je uspešno uklonjeno.', 'wp-job-board-pro' ),
                'remove_error'          => __( 'Greška pri uklanjanju obaveštenja o poslovima.', 'wp-job-board-pro' ),
            );
        }

        if ( 'candidate_alert' === $post_type ) {
            return array(
                'vendor_class'          => 'WP_Job_Board_Pro_Candidate_Alert',
                'role'                  => 'wp_job_board_pro_employer',
                'role_helper'           => 'is_employer',
                'prefix'                => WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX,
                'add_nonce'             => 'wp-job-board-pro-add-candidate-alert-nonce',
                'remove_nonce'          => 'wp-job-board-pro-remove-candidate-alert-nonce',
                'add_request_action'    => 'wp_job_board_pro_ajax_add_candidate_alert',
                'remove_request_action' => 'wp_job_board_pro_ajax_remove_candidate_alert',
                'before_add_action'     => 'wp-job-board-pro-before-add-candidate-alert',
                'after_add_action'      => 'wp-job-board-pro-after-add-candidate-alert',
                'before_remove_action'  => '',
                'auth_message'          => __( 'Prijavite se kao „Poslodavac“ da biste upravljali obaveštenjima o kandidatima.', 'wp-job-board-pro' ),
                'limit_message'         => __( 'Možete imati jedno sačuvano obaveštenje o kandidatima.', 'wp-job-board-pro' ),
                'create_success'        => __( 'Uspešno ste dodali obaveštenje o kandidatima.', 'wp-job-board-pro' ),
                'create_error'          => __( 'Došlo je do greške prilikom dodavanja obaveštenja o kandidatima.', 'wp-job-board-pro' ),
                'remove_denied'         => __( 'Ne možete ukloniti ovo obaveštenje o kandidatu.', 'wp-job-board-pro' ),
                'remove_success'        => __( 'Uspešno ste uklonili obaveštenje o kandidatima.', 'wp-job-board-pro' ),
                'remove_error'          => __( 'Došlo je do greške prilikom uklanjanja obaveštenja o kandidatima.', 'wp-job-board-pro' ),
            );
        }

        return false;
    }
}

Raspitajse_Communications_Transport::boot();
Raspitajse_Communications_Alert_Security::boot();
