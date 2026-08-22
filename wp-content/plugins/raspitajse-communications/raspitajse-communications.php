<?php
/**
 * Plugin Name: Raspitajse Communications
 * Description: Raspitajse-owned email transport and communication infrastructure.
 * Version: 0.2.1
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

Raspitajse_Communications_Transport::boot();
