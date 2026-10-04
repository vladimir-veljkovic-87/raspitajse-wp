<?php
/**
 * Deterministic offline regression harness for owned private-message notices.
 */

declare( strict_types=1 );

define( 'ABSPATH', __DIR__ . '/' );

$GLOBALS['test_hooks']        = array();
$GLOBALS['test_posts']        = array();
$GLOBALS['test_meta']         = array();
$GLOBALS['test_users']        = array();
$GLOBALS['test_options']      = array();
$GLOBALS['test_sent_mail']    = array();
$GLOBALS['test_observations'] = array();
$GLOBALS['test_fail_next']    = false;
$GLOBALS['test_environment']  = 'staging';

final class WP_Error {
    private $code;
    private $message;

    public function __construct( $code = '', $message = '' ) {
        $this->code    = (string) $code;
        $this->message = (string) $message;
    }

    public function get_error_code() {
        return $this->code;
    }

    public function get_error_message() {
        return $this->message;
    }
}

function is_wp_error( $value ) {
    return $value instanceof WP_Error;
}

function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
    $GLOBALS['test_hooks'][ $hook ][ (int) $priority ][] = array( $callback, (int) $accepted_args );
    return true;
}

function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
    return add_filter( $hook, $callback, $priority, $accepted_args );
}

function apply_filters( $hook, $value, ...$args ) {
    if ( empty( $GLOBALS['test_hooks'][ $hook ] ) ) {
        return $value;
    }

    $callbacks = $GLOBALS['test_hooks'][ $hook ];
    ksort( $callbacks, SORT_NUMERIC );
    foreach ( $callbacks as $priority_callbacks ) {
        foreach ( $priority_callbacks as $entry ) {
            $params = array_merge( array( $value ), $args );
            $value  = call_user_func_array( $entry[0], array_slice( $params, 0, $entry[1] ) );
        }
    }

    return $value;
}

function do_action( $hook, ...$args ) {
    if ( 'raspitajse_private_message_notification_observation' === $hook ) {
        $GLOBALS['test_observations'][] = $args[0];
    }

    if ( empty( $GLOBALS['test_hooks'][ $hook ] ) ) {
        return;
    }

    $callbacks = $GLOBALS['test_hooks'][ $hook ];
    ksort( $callbacks, SORT_NUMERIC );
    foreach ( $callbacks as $priority_callbacks ) {
        foreach ( $priority_callbacks as $entry ) {
            call_user_func_array( $entry[0], array_slice( $args, 0, $entry[1] ) );
        }
    }
}

function register_activation_hook() {}
function register_deactivation_hook() {}

function wp_get_environment_type() {
    return $GLOBALS['test_environment'];
}

function get_option( $key, $default = false ) {
    return array_key_exists( $key, $GLOBALS['test_options'] )
        ? $GLOBALS['test_options'][ $key ]
        : $default;
}

function wp_private_message_get_option( $key ) {
    return array_key_exists( $key, $GLOBALS['test_options'] )
        ? $GLOBALS['test_options'][ $key ]
        : false;
}

function get_post( $post_id ) {
    return isset( $GLOBALS['test_posts'][ (int) $post_id ] )
        ? $GLOBALS['test_posts'][ (int) $post_id ]
        : null;
}

function get_post_meta( $post_id, $key, $single = false ) {
    return isset( $GLOBALS['test_meta'][ (int) $post_id ][ $key ] )
        ? $GLOBALS['test_meta'][ (int) $post_id ][ $key ]
        : '';
}

function get_userdata( $user_id ) {
    return isset( $GLOBALS['test_users'][ (int) $user_id ] )
        ? $GLOBALS['test_users'][ (int) $user_id ]
        : false;
}

function is_email( $email ) {
    return false !== filter_var( $email, FILTER_VALIDATE_EMAIL );
}

function wp_strip_all_tags( $value ) {
    return strip_tags( (string) $value );
}

function sanitize_text_field( $value ) {
    $value = strip_tags( (string) $value );
    $value = preg_replace( '/[\r\n\t ]+/', ' ', $value );
    return trim( (string) $value );
}

function get_permalink( $post_id ) {
    return 77 === (int) $post_id ? 'https://stage.example.test/poruke/?view=inbox' : '';
}

function remove_query_arg( $key, $url ) {
    $parts = parse_url( (string) $url );
    parse_str( isset( $parts['query'] ) ? $parts['query'] : '', $query );
    unset( $query[ $key ] );
    $rebuilt = $parts['scheme'] . '://' . $parts['host'] . $parts['path'];
    return empty( $query ) ? $rebuilt : $rebuilt . '?' . http_build_query( $query );
}

function add_query_arg( $key, $value, $url ) {
    $separator = false === strpos( $url, '?' ) ? '?' : '&';
    return $url . $separator . rawurlencode( (string) $key ) . '=' . rawurlencode( (string) $value );
}

function esc_url_raw( $url ) {
    return filter_var( $url, FILTER_VALIDATE_URL ) ? (string) $url : '';
}

function wp_mail( $to, $subject, $message, $headers = array(), $attachments = array() ) {
    $atts = array(
        'to'          => $to,
        'subject'     => $subject,
        'message'     => $message,
        'headers'     => $headers,
        'attachments' => $attachments,
    );
    $atts = apply_filters( 'wp_mail', $atts );
    $pre  = apply_filters( 'pre_wp_mail', null, $atts );
    if ( null !== $pre ) {
        return $pre;
    }

    if ( $GLOBALS['test_fail_next'] ) {
        $GLOBALS['test_fail_next'] = false;
        return false;
    }

    $GLOBALS['test_sent_mail'][] = $atts;
    return true;
}

require dirname( __DIR__ ) . '/raspitajse-communications.php';

function test_fail( $message ) {
    fwrite( STDERR, 'FAIL: ' . $message . PHP_EOL );
    exit( 1 );
}

function test_assert( $condition, $message ) {
    if ( ! $condition ) {
        test_fail( $message );
    }
}

function test_same( $expected, $actual, $message ) {
    if ( $expected !== $actual ) {
        test_fail( $message . ' expected=' . var_export( $expected, true ) . ' actual=' . var_export( $actual, true ) );
    }
}

function test_headers( $headers ) {
    return is_array( $headers ) ? implode( "\n", $headers ) : (string) $headers;
}

function test_post( $id, $title, $author, $parent = 0, $content = 'BODY_MUST_NOT_LEAK' ) {
    return (object) array(
        'ID'           => $id,
        'post_type'    => 'private_message',
        'post_title'   => $title,
        'post_author'  => $author,
        'post_parent'  => $parent,
        'post_content' => $content,
    );
}

function test_reset() {
    Raspitajse_Communications_Private_Message_Notifications::reset_request_state();
    $GLOBALS['test_posts'] = array();
    $GLOBALS['test_meta']  = array();
    $GLOBALS['test_users'] = array(
        11 => (object) array(
            'ID'           => 11,
            'display_name' => 'Kandidat Test',
            'user_email'   => 'candidate@example.test',
        ),
        22 => (object) array(
            'ID'           => 22,
            'display_name' => 'Poslodavac Test',
            'user_email'   => 'employer@example.test',
        ),
        33 => (object) array(
            'ID'           => 33,
            'display_name' => 'Treća strana',
            'user_email'   => 'outsider@example.test',
        ),
    );
    $GLOBALS['test_options'] = array(
        'user_notice_add_new_message'       => true,
        'user_notice_add_new_message_subject' => 'Vendor initial subject',
        'user_notice_replied_message_subject' => 'Vendor reply subject',
        'message_dashboard_page_id'         => 77,
    );
    $GLOBALS['test_sent_mail']    = array();
    $GLOBALS['test_observations'] = array();
    $GLOBALS['test_fail_next']    = false;
    $GLOBALS['test_environment']  = 'staging';
}

function test_seed_initial( $id, $sender_id, $recipient_id, $title = 'Tema razgovora' ) {
    $GLOBALS['test_posts'][ $id ] = test_post( $id, $title, $sender_id );
    $GLOBALS['test_meta'][ $id ]  = array(
        '_sender'    => $sender_id,
        '_recipient' => $recipient_id,
    );
}

function test_seed_reply( $parent_id, $reply_id, $actor_id ) {
    test_seed_initial( $parent_id, 11, 22 );
    $GLOBALS['test_posts'][ $reply_id ] = test_post( $reply_id, 'RE: Tema razgovora', $actor_id, $parent_id );
}

function test_assert_safe_owned_mail( $mail, $recipient_email, $conversation_id, $topic ) {
    test_same( $recipient_email, $mail['to'], 'authoritative recipient must be used' );
    test_assert( false !== strpos( $mail['subject'], $topic ), 'message subject must be present' );
    test_assert( false !== strpos( $mail['message'], 'id=' . $conversation_id ), 'conversation link must target saved conversation' );
    test_assert( false !== strpos( $mail['message'], 'https://stage.example.test/poruke/' ), 'link must use staging permalink' );
    test_assert( false === strpos( $mail['message'], 'BODY_MUST_NOT_LEAK' ), 'message body must not be rendered' );

    $headers = test_headers( $mail['headers'] );
    test_assert( false !== strpos( $headers, 'From: ' ), 'owned From header is required' );
    test_assert( false !== strpos( $headers, '<noreply-system@stage.raspitajse.com>' ), 'approved staging From is required' );
    test_assert( false !== strpos( $headers, 'Reply-To: no-reply@stage.raspitajse.com' ), 'approved staging Reply-To is required' );

    foreach ( array( 'candidate@example.test', 'employer@example.test', 'outsider@example.test' ) as $email ) {
        test_assert( false === stripos( $headers, $email ), 'user email must not appear in transport identity' );
        test_assert( false === stripos( $mail['subject'] . $mail['message'], $email ), 'user email must not appear in rendered content' );
    }
}

function test_legacy_mail( $recipient, $subject ) {
    return wp_mail(
        $recipient,
        $subject,
        'Legacy BODY_MUST_NOT_LEAK',
        'From: unsafe-user@example.test'
    );
}

// Sender policy and inactive-vendor loading.
test_assert( class_exists( 'Raspitajse_Communications_Private_Message_Notifications' ), 'integration must load' );
test_assert( ! class_exists( 'WP_Private_Message_Message' ), 'harness must prove vendor-symbol-independent loading' );
$sender = Raspitajse_Communications_Sender_Policy::resolve(
    Raspitajse_Communications_Sender_Policy::CHANNEL_PRIVATE_MESSAGES
);
test_assert( is_array( $sender ), 'staging private-message sender must resolve' );
test_same( 'noreply-system@stage.raspitajse.com', $sender['from_email'], 'staging sender must be approved' );
$GLOBALS['test_environment'] = 'production';
test_assert(
    is_wp_error( Raspitajse_Communications_Sender_Policy::resolve( Raspitajse_Communications_Sender_Policy::CHANNEL_PRIVATE_MESSAGES ) ),
    'production sender must fail closed without configuration'
);
$GLOBALS['test_environment'] = 'staging';

// Candidate -> employer initial message.
test_reset();
test_seed_initial( 101, 11, 22 );
do_action( 'wp-private-message-after-add-message', 101, 22, 11 );
test_same( 1, count( $GLOBALS['test_sent_mail'] ), 'candidate initial must send exactly once' );
test_assert_safe_owned_mail( $GLOBALS['test_sent_mail'][0], 'employer@example.test', 101, 'Tema razgovora' );
test_same( false, test_legacy_mail( 'employer@example.test', 'Vendor initial subject' ), 'matched initial vendor mail must be suppressed' );
test_same( 1, count( $GLOBALS['test_sent_mail'] ), 'suppressed vendor initial must not be delivered' );

// Employer -> candidate initial message.
test_reset();
test_seed_initial( 102, 22, 11 );
do_action( 'wp-private-message-after-add-message', 102, 11, 22 );
test_same( 1, count( $GLOBALS['test_sent_mail'] ), 'employer initial must send exactly once' );
test_assert_safe_owned_mail( $GLOBALS['test_sent_mail'][0], 'candidate@example.test', 102, 'Tema razgovora' );
test_same( false, test_legacy_mail( 'candidate@example.test', 'Vendor initial subject' ), 'reverse initial vendor mail must be suppressed' );

// Candidate -> employer reply.
test_reset();
test_seed_reply( 201, 202, 11 );
do_action( 'wp-private-message-after-reply-message', 202, 201, 11 );
test_same( 1, count( $GLOBALS['test_sent_mail'] ), 'candidate reply must send exactly once' );
test_assert_safe_owned_mail( $GLOBALS['test_sent_mail'][0], 'employer@example.test', 201, 'Tema razgovora' );
test_same( false, test_legacy_mail( 'employer@example.test', 'Vendor reply subject' ), 'matched candidate reply vendor mail must be suppressed' );

// Employer -> candidate reply.
test_reset();
test_seed_reply( 203, 204, 22 );
do_action( 'wp-private-message-after-reply-message', 204, 203, 22 );
test_same( 1, count( $GLOBALS['test_sent_mail'] ), 'employer reply must send exactly once' );
test_assert_safe_owned_mail( $GLOBALS['test_sent_mail'][0], 'candidate@example.test', 203, 'Tema razgovora' );
test_same( false, test_legacy_mail( 'candidate@example.test', 'Vendor reply subject' ), 'matched employer reply vendor mail must be suppressed' );

// Unrelated mail before and after an exact match must pass.
test_reset();
test_same( true, wp_mail( 'system@example.test', 'Before', 'Before' ), 'unrelated mail before hook must pass' );
test_seed_initial( 301, 11, 22 );
do_action( 'wp-private-message-after-add-message', 301, 22, 11 );
test_same( false, test_legacy_mail( 'employer@example.test', 'Vendor initial subject' ), 'single matched legacy call must be suppressed' );
test_same( true, wp_mail( 'system@example.test', 'After', 'After' ), 'unrelated mail after match must pass' );
test_same( 3, count( $GLOBALS['test_sent_mail'] ), 'only one exact legacy call may be removed' );

// Fingerprint mismatch passes and clears suppression.
test_reset();
test_seed_initial( 302, 11, 22 );
do_action( 'wp-private-message-after-add-message', 302, 22, 11 );
test_same( true, test_legacy_mail( 'employer@example.test', 'Different subject' ), 'fingerprint mismatch must pass' );
test_same( true, test_legacy_mail( 'employer@example.test', 'Vendor initial subject' ), 'state must clear after mismatch' );
test_same( 3, count( $GLOBALS['test_sent_mail'] ), 'mismatch and later mail must both pass' );

// Shutdown clears pending suppression.
test_reset();
test_seed_initial( 303, 11, 22 );
do_action( 'wp-private-message-after-add-message', 303, 22, 11 );
do_action( 'shutdown' );
test_same( true, test_legacy_mail( 'employer@example.test', 'Vendor initial subject' ), 'shutdown must clear suppression state' );

// Disabled vendor option means no owned attempt and no suppression state.
test_reset();
$GLOBALS['test_options']['user_notice_add_new_message'] = false;
test_seed_initial( 304, 11, 22 );
do_action( 'wp-private-message-after-add-message', 304, 22, 11 );
test_same( 0, count( $GLOBALS['test_sent_mail'] ), 'disabled notification option must produce zero owned mail' );
test_same( true, test_legacy_mail( 'employer@example.test', 'Vendor initial subject' ), 'disabled option must leave no suppression state' );

// Duplicate hook is idempotent within one request.
test_reset();
test_seed_initial( 305, 11, 22 );
do_action( 'wp-private-message-after-add-message', 305, 22, 11 );
do_action( 'wp-private-message-after-add-message', 305, 22, 11 );
test_same( 1, count( $GLOBALS['test_sent_mail'] ), 'duplicate saved event must send once' );
test_same( false, test_legacy_mail( 'employer@example.test', 'Vendor initial subject' ), 'duplicate hook must retain one suppression budget' );

// Invalid initial states fail closed.
test_reset();
test_seed_initial( 401, 11, 11 );
do_action( 'wp-private-message-after-add-message', 401, 11, 11 );
test_seed_initial( 402, 11, 99 );
do_action( 'wp-private-message-after-add-message', 402, 99, 11 );
test_seed_initial( 403, 11, 33 );
do_action( 'wp-private-message-after-add-message', 403, 22, 11 );
test_same( 0, count( $GLOBALS['test_sent_mail'] ), 'self, deleted user and metadata mismatch must produce zero mail' );

// Outsider and malformed reply parent fail closed.
test_reset();
test_seed_reply( 501, 502, 33 );
do_action( 'wp-private-message-after-reply-message', 502, 501, 33 );
test_seed_reply( 503, 504, 11 );
$GLOBALS['test_posts'][503]->post_type = 'post';
do_action( 'wp-private-message-after-reply-message', 504, 503, 11 );
test_seed_reply( 505, 506, 11 );
$GLOBALS['test_meta'][505]['_recipient'] = '';
do_action( 'wp-private-message-after-reply-message', 506, 505, 11 );
test_same( 0, count( $GLOBALS['test_sent_mail'] ), 'outsider and malformed parent must produce zero mail' );

// Unsafe display/subject emails are redacted rather than rendered.
test_reset();
$GLOBALS['test_users'][11]->display_name = 'candidate@example.test';
test_seed_initial( 601, 11, 22, 'Kontakt candidate@example.test' );
do_action( 'wp-private-message-after-add-message', 601, 22, 11 );
test_same( 1, count( $GLOBALS['test_sent_mail'] ), 'redacted content must remain deliverable' );
test_assert_safe_owned_mail( $GLOBALS['test_sent_mail'][0], 'employer@example.test', 601, '[redigovano]' );
test_same( false, test_legacy_mail( 'employer@example.test', 'Vendor initial subject' ), 'redacted notification still suppresses vendor mail' );

// Owned delivery failure still suppresses unsafe fallback and preserves saved state.
test_reset();
test_seed_initial( 701, 11, 22 );
$GLOBALS['test_fail_next'] = true;
do_action( 'wp-private-message-after-add-message', 701, 22, 11 );
test_same( 0, count( $GLOBALS['test_sent_mail'] ), 'failed owned delivery must not be recorded as sent' );
test_same( false, test_legacy_mail( 'employer@example.test', 'Vendor initial subject' ), 'failed owned delivery must suppress unsafe fallback' );
test_assert( isset( $GLOBALS['test_posts'][701] ), 'saved message must remain after delivery failure' );
test_same( 11, $GLOBALS['test_meta'][701]['_sender'], 'saved sender metadata must remain' );
test_same( 22, $GLOBALS['test_meta'][701]['_recipient'], 'saved recipient metadata must remain' );
$diagnostics = json_encode( $GLOBALS['test_observations'] );
test_assert( false !== strpos( $diagnostics, 'owned_delivery_failed' ), 'sanitized failure signal is required' );
foreach ( array( 'candidate@example.test', 'employer@example.test', 'BODY_MUST_NOT_LEAK' ) as $forbidden ) {
    test_assert( false === strpos( $diagnostics, $forbidden ), 'diagnostics must not contain email or body content' );
}

echo "PASS: OWNED_PRIVATE_MESSAGE_NOTIFICATIONS_READY\n";
