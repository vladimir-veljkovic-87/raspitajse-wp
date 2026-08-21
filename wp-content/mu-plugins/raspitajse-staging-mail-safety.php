<?php
/**
 * Plugin Name: Raspitajse Staging Mail Safety
 * Description: Prevents staging email from reaching real recipients by forcing all wp_mail traffic to a configured test inbox.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return true only when WordPress explicitly identifies this installation as staging.
 */
function raspitajse_staging_mail_safety_is_staging() {
    if ( function_exists( 'wp_get_environment_type' ) ) {
        return 'staging' === wp_get_environment_type();
    }

    return defined( 'WP_ENVIRONMENT_TYPE' ) && 'staging' === WP_ENVIRONMENT_TYPE;
}

/**
 * Return the configured staging recipient, or an empty string when configuration is invalid.
 */
function raspitajse_staging_mail_safety_recipient() {
    if ( ! defined( 'RASPITAJSE_STAGING_MAIL_TO' ) ) {
        return '';
    }

    $recipient = sanitize_email( (string) RASPITAJSE_STAGING_MAIL_TO );

    return is_email( $recipient ) ? $recipient : '';
}

/**
 * Remove recipient-bearing headers so Cc/Bcc/To cannot bypass the staging recipient override.
 */
function raspitajse_staging_mail_safety_strip_recipient_headers( $headers ) {
    if ( empty( $headers ) ) {
        return $headers;
    }

    $was_string = is_string( $headers );
    $lines      = $was_string ? preg_split( '/\r\n|\r|\n/', $headers ) : (array) $headers;
    $filtered   = array();

    foreach ( $lines as $line ) {
        if ( preg_match( '/^\s*(to|cc|bcc)\s*:/i', (string) $line ) ) {
            continue;
        }

        $filtered[] = $line;
    }

    return $was_string ? implode( "\n", $filtered ) : $filtered;
}

/**
 * Fail closed on staging when the test inbox is missing or invalid.
 */
add_filter(
    'pre_wp_mail',
    function ( $return, $atts ) {
        if ( ! raspitajse_staging_mail_safety_is_staging() ) {
            return $return;
        }

        if ( raspitajse_staging_mail_safety_recipient() ) {
            return $return;
        }

        error_log( '[Raspitajse staging mail safety] Email blocked: RASPITAJSE_STAGING_MAIL_TO is missing or invalid.' );

        return false;
    },
    PHP_INT_MAX,
    2
);

/**
 * Rewrite every staging wp_mail call to the single configured test inbox.
 */
add_filter(
    'wp_mail',
    function ( $atts ) {
        if ( ! raspitajse_staging_mail_safety_is_staging() ) {
            return $atts;
        }

        $recipient = raspitajse_staging_mail_safety_recipient();
        if ( ! $recipient ) {
            return $atts;
        }

        $original = array(
            'to'      => isset( $atts['to'] ) ? $atts['to'] : array(),
            'headers' => isset( $atts['headers'] ) ? $atts['headers'] : array(),
        );

        error_log(
            '[Raspitajse staging mail safety] Redirecting email. Original recipients: ' .
            wp_json_encode( $original, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
        );

        $atts['to'] = $recipient;

        if ( isset( $atts['headers'] ) ) {
            $atts['headers'] = raspitajse_staging_mail_safety_strip_recipient_headers( $atts['headers'] );
        }

        $subject = isset( $atts['subject'] ) ? (string) $atts['subject'] : '';
        if ( 0 !== strpos( $subject, '[STAGING]' ) ) {
            $atts['subject'] = '[STAGING] ' . $subject;
        }

        return $atts;
    },
    PHP_INT_MAX
);

/**
 * Final recipient guard after WordPress has parsed headers into PHPMailer.
 * This prevents custom To/Cc/Bcc headers from surviving the wp_mail layer.
 */
add_action(
    'phpmailer_init',
    function ( $phpmailer ) {
        if ( ! raspitajse_staging_mail_safety_is_staging() ) {
            return;
        }

        $recipient = raspitajse_staging_mail_safety_recipient();

        $phpmailer->clearAddresses();
        $phpmailer->clearCCs();
        $phpmailer->clearBCCs();
        $phpmailer->clearReplyTos();

        if ( ! $recipient ) {
            // pre_wp_mail should already have blocked wp_mail(). Keep PHPMailer recipient-free as a second fail-closed guard.
            return;
        }

        $phpmailer->addAddress( $recipient );
    },
    PHP_INT_MAX
);
