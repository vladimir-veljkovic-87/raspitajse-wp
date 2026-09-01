<?php
/**
 * Candidate-to-job new-alert frequency UI.
 *
 * @package raspitajse-communications
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Scope the canonical new-alert allowlist to the active WPJBP job-alert widget.
 */
final class Raspitajse_Communications_Candidate_Job_Alert_Frequency_UI {

    /** @var int */
    private static $create_form_depth = 0;

    public static function boot() {
        add_filter(
            'wp-job-board-pro-job-alert-email-frequency',
            array( __CLASS__, 'filter_create_form_frequencies' ),
            PHP_INT_MAX
        );
        add_action( 'widgets_init', array( __CLASS__, 'replace_job_alert_widget' ), PHP_INT_MAX );
    }

    /**
     * Render one create form inside an exception-safe request-local context.
     *
     * @return mixed
     */
    public static function with_create_form_frequencies( $callback ) {
        if ( ! is_callable( $callback ) ) {
            return null;
        }

        self::$create_form_depth++;
        try {
            return call_user_func( $callback );
        } finally {
            self::$create_form_depth--;
        }
    }

    /**
     * Keep vendor labels but expose only backend-approved values while creating.
     */
    public static function filter_create_form_frequencies( $frequencies ) {
        if ( self::$create_form_depth < 1 || ! is_array( $frequencies ) ) {
            return $frequencies;
        }

        $scoped = array();
        foreach ( Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::current_frequencies() as $key ) {
            if ( isset( $frequencies[ $key ] ) && is_array( $frequencies[ $key ] ) ) {
                $scoped[ $key ] = $frequencies[ $key ];
            }
        }

        return $scoped;
    }

    /**
     * Preserve the vendor widget ID/settings while owning only its render scope.
     */
    public static function replace_job_alert_widget() {
        if ( ! class_exists( 'WP_Job_Board_Pro_Widget_Job_Alert_Form' ) ) {
            return;
        }

        require_once __DIR__ . '/class-candidate-job-alert-frequency-widget.php';

        unregister_widget( 'WP_Job_Board_Pro_Widget_Job_Alert_Form' );
        register_widget( 'Raspitajse_Communications_Candidate_Job_Alert_Frequency_Widget' );
    }
}
