<?php
/**
 * Plugin Name: Raspitajse Communications
 * Description: Raspitajse-owned email transport and communication infrastructure.
 * Version: 0.1.0
 * Author: Raspitajse.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Raspitajse_Communications_Transport {

    const ENABLE_FLAG = 'RASPITAJSE_COMMUNICATIONS_TRANSPORT_ENABLED';

    /**
     * Headers captured from the current wp_mail call.
     *
     * @var string
     */
    private static $mail_headers = '';

    /**
     * Register transport hooks only after an explicit migration flag is enabled.
     */
    public static function boot() {
        if ( ! defined( self::ENABLE_FLAG ) || true !== constant( self::ENABLE_FLAG ) ) {
            return;
        }

        add_filter( 'wp_mail', array( __CLASS__, 'capture_mail_args' ), PHP_INT_MIN );
        add_filter( 'wp_mail_from', array( __CLASS__, 'filter_from' ), PHP_INT_MAX );
        add_filter( 'wp_mail_from_name', array( __CLASS__, 'filter_from_name' ), PHP_INT_MAX );
        add_action( 'phpmailer_init', array( __CLASS__, 'configure_phpmailer' ), 20 );
    }

    /**
     * Capture mail headers without mutating the wp_mail payload.
     *
     * The legacy child-theme callback was registered on wp_mail without
     * returning $args. Because wp_mail is a filter hook, that can corrupt
     * the payload for callbacks that execute afterwards.
     */
    public static function capture_mail_args( $args ) {
        $headers = isset( $args['headers'] ) ? $args['headers'] : array();

        if ( is_array( $headers ) ) {
            self::$mail_headers = implode( "\n", $headers );
        } else {
            self::$mail_headers = (string) $headers;
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

        // Preserve explicitly supplied From headers. On staging, keep the
        // current fallback identity until sender policy is migrated fully.
        if (
            self::is_staging()
            && ( empty( $phpmailer->From ) || false === strpos( $phpmailer->From, '@stage.raspitajse.com' ) )
        ) {
            $phpmailer->From     = 'noreply@stage.raspitajse.com';
            $phpmailer->FromName = self::default_from_name();
        }
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

Raspitajse_Communications_Transport::boot();
