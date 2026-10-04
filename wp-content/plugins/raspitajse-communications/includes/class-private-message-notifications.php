<?php
/**
 * Raspitajse-owned private-message notifications.
 *
 * @package raspitajse-communications
 */

defined( 'ABSPATH' ) || exit;

final class Raspitajse_Communications_Private_Message_Notifications {

    const INITIAL_HOOK = 'wp-private-message-after-add-message';
    const REPLY_HOOK   = 'wp-private-message-after-reply-message';

    /**
     * The vendor currently gates both immediately following mail calls with
     * this option. Matching that observed execution gate keeps the owned
     * attempt and its one-call suppression atomic.
     */
    const VENDOR_NOTIFICATION_OPTION = 'user_notice_add_new_message';

    /** @var array<string,bool> */
    private static $processed_events = array();

    /** @var array<string,int|string>|null */
    private static $legacy_suppression = null;

    public static function boot() {
        add_action( self::INITIAL_HOOK, array( __CLASS__, 'handle_initial' ), 10, 3 );
        add_action( self::REPLY_HOOK, array( __CLASS__, 'handle_reply' ), 10, 3 );
        add_filter( 'pre_wp_mail', array( __CLASS__, 'suppress_legacy_vendor_mail' ), PHP_INT_MIN, 2 );
        add_action( 'shutdown', array( __CLASS__, 'reset_request_state' ), PHP_INT_MAX );
    }

    /**
     * Notify the authoritative saved recipient of a new conversation.
     */
    public static function handle_initial( $message_id, $recipient_id, $sender_id ) {
        if ( ! self::vendor_notifications_enabled() ) {
            return;
        }

        $message_id   = self::positive_id( $message_id );
        $recipient_id = self::positive_id( $recipient_id );
        $sender_id    = self::positive_id( $sender_id );

        if ( ! $message_id || ! $recipient_id || ! $sender_id || $recipient_id === $sender_id ) {
            return;
        }

        $message = get_post( $message_id );
        if (
            ! is_object( $message )
            || 'private_message' !== (string) $message->post_type
            || $sender_id !== self::positive_id( $message->post_author )
        ) {
            return;
        }

        $saved_sender    = self::positive_id( get_post_meta( $message_id, '_sender', true ) );
        $saved_recipient = self::positive_id( get_post_meta( $message_id, '_recipient', true ) );
        if ( $sender_id !== $saved_sender || $recipient_id !== $saved_recipient ) {
            return;
        }

        self::attempt_notification(
            'initial',
            $message_id,
            $message,
            $saved_sender,
            $saved_recipient,
            $message_id
        );
    }

    /**
     * Notify only the opposite participant of a new saved reply.
     */
    public static function handle_reply( $reply_id, $parent_id, $current_user_id ) {
        if ( ! self::vendor_notifications_enabled() ) {
            return;
        }

        $reply_id        = self::positive_id( $reply_id );
        $parent_id       = self::positive_id( $parent_id );
        $current_user_id = self::positive_id( $current_user_id );
        if ( ! $reply_id || ! $parent_id || ! $current_user_id ) {
            return;
        }

        $reply  = get_post( $reply_id );
        $parent = get_post( $parent_id );
        if (
            ! is_object( $reply )
            || 'private_message' !== (string) $reply->post_type
            || $parent_id !== self::positive_id( $reply->post_parent )
            || $current_user_id !== self::positive_id( $reply->post_author )
            || ! is_object( $parent )
            || 'private_message' !== (string) $parent->post_type
        ) {
            return;
        }

        $sender_id    = self::positive_id( get_post_meta( $parent_id, '_sender', true ) );
        $recipient_id = self::positive_id( get_post_meta( $parent_id, '_recipient', true ) );
        if (
            ! $sender_id
            || ! $recipient_id
            || $sender_id === $recipient_id
            || $sender_id !== self::positive_id( $parent->post_author )
            || ( $current_user_id !== $sender_id && $current_user_id !== $recipient_id )
        ) {
            return;
        }

        $notify_user_id = $current_user_id === $sender_id ? $recipient_id : $sender_id;
        self::attempt_notification(
            'reply',
            $reply_id,
            $parent,
            $current_user_id,
            $notify_user_id,
            $parent_id
        );
    }

    /**
     * Suppress only the first exact legacy recipient/subject call after an
     * accepted owned attempt. A mismatch passes through and consumes state.
     *
     * @param null|bool $pre  Existing short-circuit value.
     * @param array     $atts wp_mail attributes.
     * @return null|bool
     */
    public static function suppress_legacy_vendor_mail( $pre, $atts ) {
        if ( null === self::$legacy_suppression ) {
            return $pre;
        }

        $expected                 = self::$legacy_suppression;
        self::$legacy_suppression = null;

        if ( 1 !== $expected['budget'] || ! is_array( $atts ) ) {
            return $pre;
        }

        $recipient = self::single_recipient(
            isset( $atts['to'] ) ? $atts['to'] : ''
        );
        $subject = isset( $atts['subject'] ) && is_scalar( $atts['subject'] )
            ? (string) $atts['subject']
            : '';

        $actual = self::legacy_fingerprint( $expected['kind'], $recipient, $subject );
        if ( '' !== $recipient && hash_equals( $expected['fingerprint'], $actual ) ) {
            if ( is_callable( array( 'Raspitajse_Communications_Transport', 'finish_short_circuited_mail_context' ) ) ) {
                Raspitajse_Communications_Transport::finish_short_circuited_mail_context();
            }
            self::observe( $expected['kind'], 'legacy_vendor_mail_suppressed' );
            return false;
        }

        self::observe( $expected['kind'], 'legacy_vendor_mail_mismatch' );
        return $pre;
    }

    /**
     * Clear all request-local state, including at request shutdown.
     */
    public static function reset_request_state() {
        self::$legacy_suppression = null;
        self::$processed_events   = array();
    }

    private static function attempt_notification( $kind, $event_id, $conversation, $actor_id, $recipient_id, $link_id ) {
        $actor     = get_userdata( $actor_id );
        $recipient = get_userdata( $recipient_id );
        if (
            ! self::valid_user( $actor, $actor_id )
            || ! self::valid_user( $recipient, $recipient_id )
            || $actor_id === $recipient_id
        ) {
            return;
        }

        $event_key = $kind . ':' . $event_id . ':' . $actor_id . ':' . $recipient_id;
        if ( isset( self::$processed_events[ $event_key ] ) ) {
            return;
        }
        self::$processed_events[ $event_key ] = true;

        $vendor_subject_key = 'initial' === $kind
            ? 'user_notice_add_new_message_subject'
            : 'user_notice_replied_message_subject';
        $vendor_subject = wp_private_message_get_option( $vendor_subject_key );
        $vendor_subject = is_scalar( $vendor_subject ) ? (string) $vendor_subject : '';

        $result = self::send_owned_notification(
            $kind,
            $conversation,
            $actor,
            $recipient,
            $link_id
        );

        self::$legacy_suppression = array(
            'budget'      => 1,
            'fingerprint' => self::legacy_fingerprint( $kind, $recipient->user_email, $vendor_subject ),
            'kind'        => $kind,
        );

        if ( is_wp_error( $result ) || true !== $result ) {
            self::observe( $kind, 'owned_delivery_failed' );
            return;
        }

        self::observe( $kind, 'owned_delivery_sent' );
    }

    /**
     * Build and send body-free notification content through owned policy.
     *
     * @return bool|WP_Error
     */
    private static function send_owned_notification( $kind, $conversation, $actor, $recipient, $link_id ) {
        $link = self::conversation_link( $link_id );
        if ( is_wp_error( $link ) ) {
            return $link;
        }

        $blocked_emails = array( (string) $actor->user_email, (string) $recipient->user_email );
        $display_name   = self::safe_public_text( $actor->display_name, $blocked_emails, 'Raspitajse korisnik' );
        $topic          = self::safe_public_text( $conversation->post_title, $blocked_emails, 'Privatna poruka' );

        if ( 'initial' === $kind ) {
            $subject = sprintf( 'Nova privatna poruka: %s', $topic );
            $message = sprintf(
                "%s vam je poslao/la privatnu poruku na Raspitajse.com.\n\nTema: %s\n\nOtvorite razgovor: %s",
                $display_name,
                $topic,
                $link
            );
        } else {
            $subject = sprintf( 'Novi odgovor na privatnu poruku: %s', $topic );
            $message = sprintf(
                "%s je odgovorio/la na privatnu poruku na Raspitajse.com.\n\nTema: %s\n\nOtvorite razgovor: %s",
                $display_name,
                $topic,
                $link
            );
        }

        if ( self::contains_blocked_email( $subject . "\n" . $message, $blocked_emails ) ) {
            return new WP_Error(
                'raspitajse_private_message_unsafe_content',
                'Private-message notification content was rejected.'
            );
        }

        return Raspitajse_Communications_Transport::send(
            Raspitajse_Communications_Sender_Policy::CHANNEL_PRIVATE_MESSAGES,
            $recipient->user_email,
            $subject,
            $message,
            array( 'Content-Type: text/plain; charset=UTF-8' )
        );
    }

    /** @return string|WP_Error */
    private static function conversation_link( $conversation_id ) {
        if ( ! function_exists( 'wp_private_message_get_option' ) ) {
            return new WP_Error(
                'raspitajse_private_message_vendor_unavailable',
                'Private-message integration is unavailable.'
            );
        }

        $dashboard_id = self::positive_id(
            wp_private_message_get_option( 'message_dashboard_page_id' )
        );
        if ( ! $dashboard_id ) {
            return new WP_Error(
                'raspitajse_private_message_dashboard_unavailable',
                'Private-message dashboard is unavailable.'
            );
        }

        $dashboard_link = get_permalink( $dashboard_id );
        if ( ! is_string( $dashboard_link ) || '' === trim( $dashboard_link ) ) {
            return new WP_Error(
                'raspitajse_private_message_dashboard_unavailable',
                'Private-message dashboard is unavailable.'
            );
        }

        $link = add_query_arg(
            'id',
            self::positive_id( $conversation_id ),
            remove_query_arg( 'id', $dashboard_link )
        );
        $link = esc_url_raw( $link );

        if ( '' === $link ) {
            return new WP_Error(
                'raspitajse_private_message_link_unavailable',
                'Private-message conversation link is unavailable.'
            );
        }

        return $link;
    }

    private static function vendor_notifications_enabled() {
        return function_exists( 'wp_private_message_get_option' )
            && (bool) wp_private_message_get_option( self::VENDOR_NOTIFICATION_OPTION );
    }

    private static function valid_user( $user, $expected_id ) {
        return is_object( $user )
            && $expected_id === self::positive_id( $user->ID )
            && isset( $user->user_email )
            && is_email( $user->user_email );
    }

    private static function positive_id( $value ) {
        if ( ! is_scalar( $value ) ) {
            return 0;
        }

        $value = (string) $value;
        if ( 1 !== preg_match( '/^[1-9][0-9]*$/D', $value ) ) {
            return 0;
        }

        return (int) $value;
    }

    private static function safe_public_text( $value, $blocked_emails, $fallback ) {
        $value = is_scalar( $value ) ? (string) $value : '';
        $value = sanitize_text_field( wp_strip_all_tags( $value ) );

        foreach ( $blocked_emails as $email ) {
            if ( '' !== $email ) {
                $value = str_ireplace( $email, '[redigovano]', $value );
            }
        }

        $value = preg_replace(
            '/[a-z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)+/i',
            '[redigovano]',
            $value
        );
        $value = trim( (string) $value );

        return '' === $value ? $fallback : $value;
    }

    private static function contains_blocked_email( $value, $blocked_emails ) {
        foreach ( $blocked_emails as $email ) {
            if ( '' !== $email && false !== stripos( $value, $email ) ) {
                return true;
            }
        }

        return false;
    }

    private static function single_recipient( $to ) {
        if ( is_array( $to ) ) {
            if ( 1 !== count( $to ) ) {
                return '';
            }
            $to = reset( $to );
        }

        return is_scalar( $to ) ? strtolower( trim( (string) $to ) ) : '';
    }

    private static function legacy_fingerprint( $kind, $recipient, $subject ) {
        return hash(
            'sha256',
            (string) $kind . "\0" . strtolower( trim( (string) $recipient ) ) . "\0" . (string) $subject
        );
    }

    private static function observe( $kind, $result ) {
        do_action(
            'raspitajse_private_message_notification_observation',
            array(
                'kind'   => (string) $kind,
                'result' => (string) $result,
            )
        );
    }
}
