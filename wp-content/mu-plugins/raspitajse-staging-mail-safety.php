<?php
/**
 * Plugin Name: Raspitajse Staging Mail Safety
 * Description: Prevents staging email from reaching real recipients by forcing all wp_mail traffic to a configured test inbox.
 * Version: 1.0.1
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
 * Prefix a subject exactly once.
 */
function raspitajse_staging_mail_safety_subject( $subject ) {
    $subject = (string) $subject;

    return 0 === strpos( $subject, '[STAGING]' ) ? $subject : '[STAGING] ' . $subject;
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
 * Keep a small LIFO context stack so nested wp_mail calls remain safe.
 */
function raspitajse_staging_mail_safety_push_context( $atts ) {
    if ( ! isset( $GLOBALS['raspitajse_staging_mail_safety_stack'] ) || ! is_array( $GLOBALS['raspitajse_staging_mail_safety_stack'] ) ) {
        $GLOBALS['raspitajse_staging_mail_safety_stack'] = array();
    }

    $GLOBALS['raspitajse_staging_mail_safety_stack'][] = is_array( $atts ) ? $atts : array();
}

function raspitajse_staging_mail_safety_current_context() {
    if ( empty( $GLOBALS['raspitajse_staging_mail_safety_stack'] ) || ! is_array( $GLOBALS['raspitajse_staging_mail_safety_stack'] ) ) {
        return array();
    }

    $context = end( $GLOBALS['raspitajse_staging_mail_safety_stack'] );

    return is_array( $context ) ? $context : array();
}

function raspitajse_staging_mail_safety_pop_context() {
    if ( ! empty( $GLOBALS['raspitajse_staging_mail_safety_stack'] ) && is_array( $GLOBALS['raspitajse_staging_mail_safety_stack'] ) ) {
        array_pop( $GLOBALS['raspitajse_staging_mail_safety_stack'] );
    }
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
 * Capture the original wp_mail payload before legacy filters can mutate it.
 */
add_filter(
    'wp_mail',
    function ( $atts ) {
        if ( raspitajse_staging_mail_safety_is_staging() ) {
            raspitajse_staging_mail_safety_push_context( $atts );
        }

        return $atts;
    },
    PHP_INT_MIN
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

        $original = raspitajse_staging_mail_safety_current_context();

        // Recover the original payload if a legacy wp_mail callback returned a non-array value.
        if ( ! is_array( $atts ) ) {
            $atts = $original;
        }

        // Preserve core fields if an intermediate filter removed them.
        foreach ( array( 'message', 'attachments' ) as $key ) {
            if ( ! array_key_exists( $key, $atts ) && array_key_exists( $key, $original ) ) {
                $atts[ $key ] = $original[ $key ];
            }
        }

        $original_to      = isset( $original['to'] ) ? $original['to'] : ( isset( $atts['to'] ) ? $atts['to'] : array() );
        $original_headers = isset( $original['headers'] ) ? $original['headers'] : ( isset( $atts['headers'] ) ? $atts['headers'] : array() );
        $original_subject = isset( $original['subject'] ) ? (string) $original['subject'] : ( isset( $atts['subject'] ) ? (string) $atts['subject'] : '' );

        error_log(
            '[Raspitajse staging mail safety] Redirecting email. Original recipients: ' .
            wp_json_encode(
                array(
                    'to'      => $original_to,
                    'headers' => $original_headers,
                ),
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            )
        );

        $atts['to']      = $recipient;
        $atts['subject'] = raspitajse_staging_mail_safety_subject( $original_subject );

        $headers         = isset( $atts['headers'] ) ? $atts['headers'] : $original_headers;
        $atts['headers'] = raspitajse_staging_mail_safety_strip_recipient_headers( $headers );

        return $atts;
    },
    PHP_INT_MAX
);

/**
 * Final recipient and subject guard after WordPress has parsed headers into PHPMailer.
 * This prevents custom To/Cc/Bcc headers or later subject mutations from bypassing safety.
 */
add_action(
    'phpmailer_init',
    function ( $phpmailer ) {
        if ( ! raspitajse_staging_mail_safety_is_staging() ) {
            return;
        }

        $recipient = raspitajse_staging_mail_safety_recipient();
        $original  = raspitajse_staging_mail_safety_current_context();
        $subject   = isset( $original['subject'] ) ? $original['subject'] : $phpmailer->Subject;

        $phpmailer->clearAddresses();
        $phpmailer->clearCCs();
        $phpmailer->clearBCCs();
        $phpmailer->clearReplyTos();
        $phpmailer->Subject = raspitajse_staging_mail_safety_subject( $subject );

        if ( $recipient ) {
            $phpmailer->addAddress( $recipient );
        }

        raspitajse_staging_mail_safety_pop_context();
    },
    PHP_INT_MAX
);
