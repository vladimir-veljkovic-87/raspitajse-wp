<?php
/**
 * Candidate-to-job alert delivery foundation.
 *
 * This file intentionally registers no cron event and replaces no callback.
 * The service is dormant until a bounded owned caller invokes it.
 *
 * @package raspitajse-communications
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Calendar-window and configuration policy.
 */
final class Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy {

    const POLICY_VERSION = 1;

    const FREQUENCY_DAILY       = 'daily';
    const FREQUENCY_WEEKLY      = 'weekly';
    const FREQUENCY_FORTNIGHTLY = 'fortnightly';
    const FREQUENCY_MONTHLY     = 'monthly';
    const FREQUENCY_BIANNUALLY  = 'biannually';
    const FREQUENCY_ANNUALLY    = 'annually';

    const BIWEEKLY_ANCHOR = '1970-01-05 00:00:00';

    /**
     * Validate a frequency without inheriting legacy elapsed-day semantics.
     *
     * @return string|WP_Error
     */
    public static function normalize_frequency( $frequency, $allow_legacy = false ) {
        if ( ! is_scalar( $frequency ) ) {
            return self::error( 'invalid_frequency' );
        }

        $frequency = sanitize_key( (string) $frequency );
        $current   = array(
            self::FREQUENCY_DAILY,
            self::FREQUENCY_WEEKLY,
            self::FREQUENCY_FORTNIGHTLY,
            self::FREQUENCY_MONTHLY,
        );
        $legacy    = array(
            self::FREQUENCY_BIANNUALLY,
            self::FREQUENCY_ANNUALLY,
        );

        if (
            ! in_array( $frequency, $current, true )
            && ( ! $allow_legacy || ! in_array( $frequency, $legacy, true ) )
        ) {
            return self::error( 'invalid_frequency' );
        }

        return $frequency;
    }

    /**
     * Normalize a saved filter structure for validation and hashing.
     *
     * Root filter keys are sorted. Sequential filter values are treated as
     * unordered sets because WPJBP filter selection order has no semantics.
     *
     * @return array|WP_Error
     */
    public static function normalize_saved_query( $query ) {
        if ( '' === $query || null === $query ) {
            return array();
        }

        if ( is_string( $query ) ) {
            $decoded = json_decode( $query, true );
            if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
                return self::error( 'malformed_saved_query' );
            }
            $query = $decoded;
        }

        if ( ! is_array( $query ) ) {
            return self::error( 'malformed_saved_query' );
        }

        $normalized = array();
        foreach ( $query as $key => $value ) {
            if (
                ! is_string( $key )
                || 1 !== preg_match( '/^filter-[a-z0-9_-]+$/D', $key )
            ) {
                return self::error( 'malformed_saved_query' );
            }

            $value = self::normalize_query_value( $value, 0 );
            if ( is_wp_error( $value ) ) {
                return $value;
            }

            $normalized[ $key ] = $value;
        }

        ksort( $normalized, SORT_STRING );

        return $normalized;
    }

    /**
     * Build a non-PII deterministic revision hash.
     *
     * @return string|WP_Error
     */
    public static function config_hash( $frequency, $query, $allow_legacy = false ) {
        $frequency = self::normalize_frequency( $frequency, $allow_legacy );
        if ( is_wp_error( $frequency ) ) {
            return $frequency;
        }

        $query = self::normalize_saved_query( $query );
        if ( is_wp_error( $query ) ) {
            return $query;
        }

        return hash(
            'sha256',
            wp_json_encode(
                array(
                    'policy_version' => self::POLICY_VERSION,
                    'frequency'      => $frequency,
                    'query'          => $query,
                )
            )
        );
    }

    /**
     * Create the approved immediate first-send window.
     *
     * @return array|WP_Error
     */
    public static function initial_window( $alert_id, $config_hash, $cutoff_gmt, $upper_gmt ) {
        $cutoff = self::parse_gmt( $cutoff_gmt );
        $upper  = self::parse_gmt( $upper_gmt );

        if ( is_wp_error( $cutoff ) || is_wp_error( $upper ) || $upper < $cutoff ) {
            return self::error( 'invalid_initial_window' );
        }

        $period_key = 'I:' . $cutoff->format( 'Ymd\THis\Z' );

        return self::window(
            $alert_id,
            $config_hash,
            $period_key,
            $cutoff,
            $upper
        );
    }

    /**
     * Return the most recently closed site-local calendar period.
     *
     * @return array|WP_Error
     */
    public static function closed_window( $alert_id, $config_hash, $frequency, $at_gmt, $timezone = null ) {
        $frequency = self::normalize_frequency( $frequency, true );
        $at         = self::parse_gmt( $at_gmt );
        $timezone   = self::resolve_timezone( $timezone );

        if ( is_wp_error( $frequency ) || is_wp_error( $at ) || is_wp_error( $timezone ) ) {
            return self::error( 'invalid_calendar_window' );
        }

        $local = $at->setTimezone( $timezone );
        $end   = self::current_boundary( $frequency, $local, $timezone );
        if ( is_wp_error( $end ) ) {
            return $end;
        }

        $start = self::previous_boundary( $frequency, $end );
        $key   = self::period_key( $frequency, $start );

        return self::window(
            $alert_id,
            $config_hash,
            $key,
            $start->setTimezone( new DateTimeZone( 'UTC' ) ),
            $end->setTimezone( new DateTimeZone( 'UTC' ) )
        );
    }

    /**
     * Return the next site-local calendar boundary as UTC.
     *
     * @return string|WP_Error
     */
    public static function next_due_gmt( $frequency, $after_gmt, $timezone = null ) {
        $frequency = self::normalize_frequency( $frequency, true );
        $after      = self::parse_gmt( $after_gmt );
        $timezone   = self::resolve_timezone( $timezone );

        if ( is_wp_error( $frequency ) || is_wp_error( $after ) || is_wp_error( $timezone ) ) {
            return self::error( 'invalid_next_due' );
        }

        $local    = $after->setTimezone( $timezone );
        $boundary = self::current_boundary( $frequency, $local, $timezone );
        if ( is_wp_error( $boundary ) ) {
            return $boundary;
        }

        $next = self::next_boundary( $frequency, $boundary );

        return self::format_gmt( $next->setTimezone( new DateTimeZone( 'UTC' ) ) );
    }

    /**
     * Strictly parse a stored UTC machine timestamp.
     *
     * @return DateTimeImmutable|WP_Error
     */
    public static function parse_gmt( $value ) {
        if ( ! is_string( $value ) ) {
            return self::error( 'invalid_gmt_timestamp' );
        }

        $timezone = new DateTimeZone( 'UTC' );
        $parsed   = DateTimeImmutable::createFromFormat( '!Y-m-d H:i:s', $value, $timezone );
        $errors   = DateTimeImmutable::getLastErrors();

        if (
            false === $parsed
            || ( is_array( $errors ) && ( $errors['warning_count'] || $errors['error_count'] ) )
            || $parsed->format( 'Y-m-d H:i:s' ) !== $value
        ) {
            return self::error( 'invalid_gmt_timestamp' );
        }

        return $parsed;
    }

    public static function format_gmt( DateTimeImmutable $value ) {
        return $value->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' );
    }

    /**
     * Return current UTC without relying on the PHP process timezone.
     */
    public static function now_gmt() {
        return current_time( 'mysql', true );
    }

    /**
     * Resolve the WordPress site timezone or a bounded synthetic test timezone.
     *
     * @return DateTimeZone|WP_Error
     */
    public static function resolve_timezone( $timezone = null ) {
        if ( $timezone instanceof DateTimeZone ) {
            return $timezone;
        }

        if ( null === $timezone ) {
            return wp_timezone();
        }

        if ( ! is_string( $timezone ) || '' === $timezone ) {
            return self::error( 'invalid_timezone' );
        }

        try {
            return new DateTimeZone( $timezone );
        } catch ( Exception $exception ) {
            return self::error( 'invalid_timezone' );
        }
    }

    private static function normalize_query_value( $value, $depth ) {
        if ( $depth > 4 ) {
            return self::error( 'malformed_saved_query' );
        }

        if ( is_array( $value ) ) {
            $normalized = array();
            foreach ( $value as $key => $item ) {
                if ( ! is_int( $key ) && ! is_string( $key ) ) {
                    return self::error( 'malformed_saved_query' );
                }

                $item = self::normalize_query_value( $item, $depth + 1 );
                if ( is_wp_error( $item ) ) {
                    return $item;
                }
                $normalized[ $key ] = $item;
            }

            if ( array_is_list( $normalized ) ) {
                usort(
                    $normalized,
                    static function ( $left, $right ) {
                        return strcmp( wp_json_encode( $left ), wp_json_encode( $right ) );
                    }
                );
            } else {
                ksort( $normalized, SORT_STRING );
            }

            return $normalized;
        }

        if ( is_bool( $value ) ) {
            return $value ? '1' : '0';
        }

        if ( ! is_scalar( $value ) ) {
            return self::error( 'malformed_saved_query' );
        }

        return sanitize_text_field( (string) $value );
    }

    private static function current_boundary( $frequency, DateTimeImmutable $local, DateTimeZone $timezone ) {
        switch ( $frequency ) {
            case self::FREQUENCY_DAILY:
                return $local->setTime( 0, 0, 0 );

            case self::FREQUENCY_WEEKLY:
                return $local->modify( 'monday this week' )->setTime( 0, 0, 0 );

            case self::FREQUENCY_FORTNIGHTLY:
                $monday = $local->modify( 'monday this week' )->setTime( 0, 0, 0 );
                $anchor = new DateTimeImmutable( self::BIWEEKLY_ANCHOR, $timezone );
                $days   = (int) $anchor->diff( $monday )->format( '%r%a' );
                $block  = (int) floor( $days / 14 );
                return $anchor->modify( '+' . ( $block * 14 ) . ' days' );

            case self::FREQUENCY_MONTHLY:
                return $local->modify( 'first day of this month' )->setTime( 0, 0, 0 );

            case self::FREQUENCY_BIANNUALLY:
                $month = (int) $local->format( 'n' );
                $date  = $month <= 6
                    ? $local->format( 'Y' ) . '-01-01 00:00:00'
                    : $local->format( 'Y' ) . '-07-01 00:00:00';
                return new DateTimeImmutable( $date, $timezone );

            case self::FREQUENCY_ANNUALLY:
                return new DateTimeImmutable( $local->format( 'Y' ) . '-01-01 00:00:00', $timezone );
        }

        return self::error( 'invalid_frequency' );
    }

    private static function previous_boundary( $frequency, DateTimeImmutable $boundary ) {
        switch ( $frequency ) {
            case self::FREQUENCY_DAILY:
                return $boundary->modify( '-1 day' );
            case self::FREQUENCY_WEEKLY:
                return $boundary->modify( '-1 week' );
            case self::FREQUENCY_FORTNIGHTLY:
                return $boundary->modify( '-2 weeks' );
            case self::FREQUENCY_MONTHLY:
                return $boundary->modify( '-1 month' );
            case self::FREQUENCY_BIANNUALLY:
                return $boundary->modify( '-6 months' );
            case self::FREQUENCY_ANNUALLY:
                return $boundary->modify( '-1 year' );
        }

        return $boundary;
    }

    private static function next_boundary( $frequency, DateTimeImmutable $boundary ) {
        switch ( $frequency ) {
            case self::FREQUENCY_DAILY:
                return $boundary->modify( '+1 day' );
            case self::FREQUENCY_WEEKLY:
                return $boundary->modify( '+1 week' );
            case self::FREQUENCY_FORTNIGHTLY:
                return $boundary->modify( '+2 weeks' );
            case self::FREQUENCY_MONTHLY:
                return $boundary->modify( '+1 month' );
            case self::FREQUENCY_BIANNUALLY:
                return $boundary->modify( '+6 months' );
            case self::FREQUENCY_ANNUALLY:
                return $boundary->modify( '+1 year' );
        }

        return $boundary;
    }

    private static function period_key( $frequency, DateTimeImmutable $start ) {
        switch ( $frequency ) {
            case self::FREQUENCY_DAILY:
                return 'D:' . $start->format( 'Y-m-d' );
            case self::FREQUENCY_WEEKLY:
                return 'W:' . $start->format( 'o-\WW' );
            case self::FREQUENCY_FORTNIGHTLY:
                return 'B:' . $start->format( 'Y-m-d' );
            case self::FREQUENCY_MONTHLY:
                return 'M:' . $start->format( 'Y-m' );
            case self::FREQUENCY_BIANNUALLY:
                return 'H:' . $start->format( 'Y' ) . ( (int) $start->format( 'n' ) <= 6 ? '-H1' : '-H2' );
            case self::FREQUENCY_ANNUALLY:
                return 'Y:' . $start->format( 'Y' );
        }

        return '';
    }

    private static function window( $alert_id, $config_hash, $period_key, DateTimeImmutable $start, DateTimeImmutable $end ) {
        $alert_id = absint( $alert_id );
        if (
            ! $alert_id
            || ! is_string( $config_hash )
            || 1 !== preg_match( '/^[a-f0-9]{64}$/D', $config_hash )
            || ! is_string( $period_key )
            || '' === $period_key
        ) {
            return self::error( 'invalid_calendar_window' );
        }

        return array(
            'key'        => 'alert:' . $alert_id . ':config:' . $config_hash . ':period:' . $period_key,
            'period_key' => $period_key,
            'start_gmt'  => self::format_gmt( $start ),
            'end_gmt'    => self::format_gmt( $end ),
        );
    }

    private static function error( $code ) {
        return new WP_Error(
            'raspitajse_cja_' . sanitize_key( $code ),
            'Candidate job alert schedule policy rejected the request.'
        );
    }
}

/**
 * Alert-owned state, job ledger, and atomic option claim.
 */
final class Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store {

    const SCHEMA_VERSION = 1;
    const CLAIM_TTL      = 600;

    const META_NEXT_DUE     = '_raspitajse_cja_next_due_gmt';
    const META_STATE        = '_raspitajse_cja_delivery_state';
    const META_DELIVERED_JOB = '_raspitajse_cja_delivered_job';
    const CLAIM_PREFIX      = 'raspitajse_cja_claim_';

    /**
     * @return array|null|WP_Error
     */
    public static function read_state( $alert_id ) {
        $value = get_post_meta( absint( $alert_id ), self::META_STATE, true );
        if ( '' === $value ) {
            return null;
        }

        if ( ! is_array( $value ) ) {
            return self::error( 'invalid_state' );
        }

        return $value;
    }

    /**
     * Persist and verify the exact non-PII state structure.
     *
     * @return true|WP_Error
     */
    public static function save_state( $alert_id, $state ) {
        $alert_id = absint( $alert_id );
        if ( ! $alert_id || ! is_array( $state ) ) {
            return self::error( 'invalid_state' );
        }

        update_post_meta( $alert_id, self::META_STATE, $state );
        if ( get_post_meta( $alert_id, self::META_STATE, true ) !== $state ) {
            return self::error( 'state_write_failed' );
        }

        return true;
    }

    /**
     * @return string|WP_Error
     */
    public static function read_next_due( $alert_id ) {
        $value = get_post_meta( absint( $alert_id ), self::META_NEXT_DUE, true );
        if ( ! is_string( $value ) || is_wp_error( Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt( $value ) ) ) {
            return self::error( 'invalid_next_due' );
        }

        return $value;
    }

    /**
     * @return true|WP_Error
     */
    public static function save_next_due( $alert_id, $value ) {
        if ( is_wp_error( Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt( $value ) ) ) {
            return self::error( 'invalid_next_due' );
        }

        update_post_meta( absint( $alert_id ), self::META_NEXT_DUE, $value );
        if ( (string) get_post_meta( absint( $alert_id ), self::META_NEXT_DUE, true ) !== $value ) {
            return self::error( 'next_due_write_failed' );
        }

        return true;
    }

    public static function was_job_delivered( $alert_id, $job_id ) {
        $job_id = absint( $job_id );
        if ( ! $job_id ) {
            return false;
        }

        $values = array_map(
            'absint',
            get_post_meta( absint( $alert_id ), self::META_DELIVERED_JOB, false )
        );

        return in_array( $job_id, $values, true );
    }

    /**
     * Append once semantically. The per-alert claim serializes owned writers.
     *
     * @return true|WP_Error
     */
    public static function append_delivered_job( $alert_id, $job_id ) {
        $alert_id = absint( $alert_id );
        $job_id   = absint( $job_id );
        if ( ! $alert_id || ! $job_id ) {
            return self::error( 'invalid_job_ledger_entry' );
        }

        if ( self::was_job_delivered( $alert_id, $job_id ) ) {
            return true;
        }

        add_post_meta( $alert_id, self::META_DELIVERED_JOB, $job_id );

        return self::was_job_delivered( $alert_id, $job_id )
            ? true
            : self::error( 'job_ledger_write_failed' );
    }

    /**
     * @return true|WP_Error
     */
    public static function reconcile_delivered_jobs( $alert_id, $job_ids ) {
        if ( ! is_array( $job_ids ) ) {
            return self::error( 'invalid_job_ledger_entry' );
        }

        foreach ( array_values( array_unique( array_map( 'absint', $job_ids ) ) ) as $job_id ) {
            if ( ! $job_id ) {
                return self::error( 'invalid_job_ledger_entry' );
            }

            $result = self::append_delivered_job( $alert_id, $job_id );
            if ( is_wp_error( $result ) ) {
                return $result;
            }
        }

        return true;
    }

    public static function delivered_job_ids( $alert_id ) {
        $values = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'absint',
                        get_post_meta( absint( $alert_id ), self::META_DELIVERED_JOB, false )
                    )
                )
            )
        );
        sort( $values, SORT_NUMERIC );

        return $values;
    }

    /**
     * Acquire an atomic, non-autoloaded option claim.
     *
     * @return array|WP_Error
     */
    public static function acquire_claim( $alert_id, $window_key = '', $now_gmt = null, $token = null ) {
        $alert_id = absint( $alert_id );
        $now      = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt(
            null === $now_gmt
                ? Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::now_gmt()
                : $now_gmt
        );

        if ( ! $alert_id || is_wp_error( $now ) || ! is_string( $window_key ) ) {
            return self::error( 'invalid_claim' );
        }

        if ( null === $token ) {
            try {
                $token = bin2hex( random_bytes( 16 ) );
            } catch ( Exception $exception ) {
                $token = str_replace( '-', '', wp_generate_uuid4() );
            }
        }

        if ( ! is_string( $token ) || 1 !== preg_match( '/^[a-zA-Z0-9_-]{16,128}$/D', $token ) ) {
            return self::error( 'invalid_claim_token' );
        }

        $claim = array(
            'schema_version' => self::SCHEMA_VERSION,
            'token'          => $token,
            'alert_id'       => $alert_id,
            'window_key'     => $window_key,
            'acquired_at_gmt'=> Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::format_gmt( $now ),
            'expires_at_gmt' => Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::format_gmt(
                $now->modify( '+' . self::CLAIM_TTL . ' seconds' )
            ),
        );
        $key   = self::claim_key( $alert_id );

        if ( add_option( $key, $claim, '', 'no' ) ) {
            return $claim;
        }

        $snapshot = self::read_claim_snapshot( $alert_id );
        if ( is_wp_error( $snapshot ) ) {
            return $snapshot;
        }

        if ( null === $snapshot ) {
            return add_option( $key, $claim, '', 'no' )
                ? $claim
                : self::error( 'already_claimed' );
        }

        $expires = isset( $snapshot['claim']['expires_at_gmt'] )
            ? Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt( $snapshot['claim']['expires_at_gmt'] )
            : self::error( 'invalid_claim' );

        if ( is_wp_error( $expires ) ) {
            return self::error( 'invalid_claim' );
        }

        if ( $expires > $now ) {
            return self::error( 'already_claimed' );
        }

        if ( ! self::compare_delete_raw_claim( $alert_id, $snapshot['raw'] ) ) {
            return self::error( 'already_claimed' );
        }

        return add_option( $key, $claim, '', 'no' )
            ? $claim
            : self::error( 'already_claimed' );
    }

    /**
     * Release only the currently stored exact ownership token.
     *
     * @return true|WP_Error
     */
    public static function release_claim( $alert_id, $token ) {
        $snapshot = self::read_claim_snapshot( $alert_id );
        if ( is_wp_error( $snapshot ) ) {
            return $snapshot;
        }

        if ( null === $snapshot ) {
            return self::error( 'claim_not_found' );
        }

        $current_token = isset( $snapshot['claim']['token'] )
            ? (string) $snapshot['claim']['token']
            : '';

        if (
            ! is_string( $token )
            || '' === $current_token
            || ! hash_equals( $current_token, $token )
        ) {
            return self::error( 'claim_ownership_mismatch' );
        }

        return self::compare_delete_raw_claim( $alert_id, $snapshot['raw'] )
            ? true
            : self::error( 'claim_changed' );
    }

    /**
     * Testable CAS primitive: delete only an exact previously read value.
     */
    public static function compare_delete_claim( $alert_id, $expected_claim ) {
        if ( ! is_array( $expected_claim ) ) {
            return false;
        }

        return self::compare_delete_raw_claim(
            $alert_id,
            maybe_serialize( $expected_claim )
        );
    }

    /**
     * @return array|null|WP_Error
     */
    public static function read_claim_snapshot( $alert_id ) {
        global $wpdb;

        $key = self::claim_key( $alert_id );
        $raw = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
                $key
            )
        );

        if ( null === $raw ) {
            return null;
        }

        $claim = maybe_unserialize( $raw );
        if (
            ! is_array( $claim )
            || self::SCHEMA_VERSION !== (int) ( $claim['schema_version'] ?? 0 )
            || absint( $alert_id ) !== (int) ( $claim['alert_id'] ?? 0 )
            || empty( $claim['token'] )
        ) {
            return self::error( 'invalid_claim' );
        }

        return array(
            'raw'   => $raw,
            'claim' => $claim,
        );
    }

    private static function compare_delete_raw_claim( $alert_id, $raw ) {
        global $wpdb;

        if ( ! is_string( $raw ) ) {
            return false;
        }

        $key     = self::claim_key( $alert_id );
        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s",
                $key,
                $raw
            )
        );

        if ( 1 === (int) $deleted ) {
            wp_cache_delete( $key, 'options' );
            wp_cache_delete( 'alloptions', 'options' );
            wp_cache_delete( 'notoptions', 'options' );
            return true;
        }

        return false;
    }

    private static function claim_key( $alert_id ) {
        return self::CLAIM_PREFIX . absint( $alert_id );
    }

    private static function error( $code ) {
        return new WP_Error(
            'raspitajse_cja_' . sanitize_key( $code ),
            'Candidate job alert delivery store rejected the operation.'
        );
    }
}

/**
 * Dormant, callable delivery-state service.
 */
final class Raspitajse_Communications_Candidate_Job_Alert_Delivery_Service {

    const MAX_RESULTS  = 5;
    const MAX_ATTEMPTS = 3;

    const OUTCOME_READY          = 'ready';
    const OUTCOME_SNAPSHOT       = 'snapshot';
    const OUTCOME_RETRYABLE      = 'retryable_failed';
    const OUTCOME_MAIL_ACCEPTED  = 'mail_accepted';
    const OUTCOME_DELIVERED      = 'delivered';
    const OUTCOME_EMPTY          = 'empty';
    const OUTCOME_TERMINAL       = 'terminal_failed';

    /**
     * Process one exact alert through injected query and mail seams.
     *
     * No hook or scheduler calls this method in 1.88.
     *
     * @return array|WP_Error
     */
    public static function process( $alert_id, $eligible_adapter, $mail_adapter, $options = array() ) {
        $alert_id = absint( $alert_id );
        if ( ! $alert_id || ! is_callable( $eligible_adapter ) || ! is_callable( $mail_adapter ) ) {
            return self::error( 'invalid_service_request' );
        }

        $options  = is_array( $options ) ? $options : array();
        $now_gmt  = isset( $options['now_gmt'] )
            ? $options['now_gmt']
            : Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::now_gmt();
        $timezone = isset( $options['timezone'] ) ? $options['timezone'] : null;
        $claim     = Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::acquire_claim(
            $alert_id,
            '',
            $now_gmt,
            $options['claim_token'] ?? null
        );

        if ( is_wp_error( $claim ) ) {
            return $claim;
        }

        try {
            $result = self::process_claimed(
                $alert_id,
                $eligible_adapter,
                $mail_adapter,
                $now_gmt,
                $timezone,
                $options
            );
        } catch ( Throwable $throwable ) {
            $result = self::error( 'service_exception' );
        }

        $released = Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::release_claim(
            $alert_id,
            $claim['token']
        );

        if ( is_wp_error( $released ) && ! is_wp_error( $result ) ) {
            return self::error( 'claim_release_failed' );
        }

        return $result;
    }

    private static function process_claimed( $alert_id, $eligible_adapter, $mail_adapter, $now_gmt, $timezone, $options ) {
        $now = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt( $now_gmt );
        if ( is_wp_error( $now ) ) {
            return self::error( 'invalid_now' );
        }

        $aggregate = self::validate_aggregate( $alert_id );
        if ( is_wp_error( $aggregate ) ) {
            return $aggregate;
        }

        $config = self::load_config( $alert_id );
        if ( is_wp_error( $config ) ) {
            return $config;
        }

        $state = Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::read_state( $alert_id );
        if ( is_wp_error( $state ) ) {
            return $state;
        }

        if ( is_array( $state ) ) {
            $valid = self::validate_state( $state );
            if ( is_wp_error( $valid ) ) {
                return $valid;
            }
        }

        if (
            is_array( $state )
            && self::OUTCOME_MAIL_ACCEPTED === $state['outcome']
        ) {
            return self::recover_accepted( $alert_id, $state, $now_gmt, $timezone, $options );
        }

        $initialized = false;
        if ( null === $state ) {
            $state = self::initialize_state( $alert_id, $aggregate, $config, $now_gmt, $timezone );
            if ( is_wp_error( $state ) ) {
                return $state;
            }
            $initialized = true;
        } elseif ( ! hash_equals( $state['config_hash'], $config['hash'] ) ) {
            $state = self::new_state( $config, $now_gmt, true );
            $saved = self::save_state_and_due( $alert_id, $state, $now_gmt );
            if ( is_wp_error( $saved ) ) {
                return $saved;
            }

            // Detection time is the config activation cutoff. Wait for the
            // next bounded invocation so an initial window can have a real
            // upper bound and cannot be consumed empty at the same instant.
            return self::result(
                'config_reinitialized',
                $state,
                array( 'initialized' => true )
            );
        }

        $next_due = Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::read_next_due( $alert_id );
        if ( is_wp_error( $next_due ) ) {
            return $next_due;
        }

        if ( self::OUTCOME_RETRYABLE === $state['outcome'] ) {
            $retry = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt( $state['next_retry_gmt'] );
            if ( is_wp_error( $retry ) ) {
                return self::error( 'invalid_retry_state' );
            }
            if ( $now < $retry ) {
                return self::result( 'retry_not_due', $state );
            }

            $window = self::window_from_state( $state );
            if ( is_wp_error( $window ) ) {
                return $window;
            }
            $selected = self::resolve_selected(
                $alert_id,
                $eligible_adapter,
                $config,
                $state,
                $window,
                $state['selected_job_ids']
            );
            if ( is_wp_error( $selected ) ) {
                return $selected;
            }
            if ( ! $selected ) {
                $state['selected_job_ids'] = array();
                return self::complete( $alert_id, $state, self::OUTCOME_EMPTY, $now_gmt, $timezone );
            }

            return self::attempt(
                $alert_id,
                $selected,
                $window,
                $state,
                $mail_adapter,
                $now_gmt,
                $timezone,
                $options,
                false
            );
        }

        if ( self::OUTCOME_SNAPSHOT === $state['outcome'] ) {
            $window = self::window_from_state( $state );
            if ( is_wp_error( $window ) ) {
                return $window;
            }
            $selected = self::resolve_selected(
                $alert_id,
                $eligible_adapter,
                $config,
                $state,
                $window,
                $state['selected_job_ids']
            );
            if ( is_wp_error( $selected ) ) {
                return $selected;
            }
            if ( ! $selected ) {
                $state['selected_job_ids'] = array();
                return self::complete( $alert_id, $state, self::OUTCOME_EMPTY, $now_gmt, $timezone );
            }

            return self::attempt(
                $alert_id,
                $selected,
                $window,
                $state,
                $mail_adapter,
                $now_gmt,
                $timezone,
                $options,
                true
            );
        }

        if ( $state['initial_pending'] ) {
            $window = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::initial_window(
                $alert_id,
                $config['hash'],
                $state['activation_cutoff_gmt'],
                $now_gmt
            );
        } else {
            $due = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt( $next_due );
            if ( is_wp_error( $due ) ) {
                return self::error( 'invalid_next_due' );
            }
            if ( $now < $due ) {
                return self::result(
                    $state['last_evaluated_window'] ? 'already_completed' : 'not_due',
                    $state,
                    array( 'initialized' => $initialized )
                );
            }

            $window = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::closed_window(
                $alert_id,
                $config['hash'],
                $config['frequency'],
                $now_gmt,
                $timezone
            );
        }

        if ( is_wp_error( $window ) ) {
            return $window;
        }

        $selected = self::resolve_selected(
            $alert_id,
            $eligible_adapter,
            $config,
            $state,
            $window
        );
        if ( is_wp_error( $selected ) ) {
            return $selected;
        }

        $state['current_window_key'] = $window['key'];
        $state['window_start_gmt']   = $window['start_gmt'];
        $state['window_end_gmt']     = $window['end_gmt'];
        $state['initial_pending']    = false;
        $state['selected_job_ids']   = array();
        $state['attempt_count']      = 0;
        $state['next_retry_gmt']     = '';
        $state['outcome']            = self::OUTCOME_READY;
        $state['failure_category'] = '';

        if ( ! $selected ) {
            return self::complete( $alert_id, $state, self::OUTCOME_EMPTY, $now_gmt, $timezone );
        }

        return self::attempt(
            $alert_id,
            $selected,
            $window,
            $state,
            $mail_adapter,
            $now_gmt,
            $timezone,
            $options,
            false
        );
    }

    private static function initialize_state( $alert_id, $aggregate, $config, $now_gmt, $timezone ) {
        $legacy_key = defined( 'WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX' )
            ? WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX . 'send_email_time'
            : '_job_alert_send_email_time';
        $attempted  = metadata_exists( 'post', $alert_id, $legacy_key );

        if ( $attempted ) {
            $legacy_date = get_post_meta( $alert_id, $legacy_key, true );
            $valid_date  = is_string( $legacy_date )
                && 1 === preg_match( '/^\d{4}-\d{2}-\d{2}$/D', $legacy_date )
                && $legacy_date === gmdate( 'Y-m-d', strtotime( $legacy_date . ' 00:00:00 UTC' ) );
            if ( ! $valid_date ) {
                return self::error( 'malformed_legacy_attempt_date' );
            }

            $state    = self::new_state( $config, $now_gmt, false );
            $next_due = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::next_due_gmt(
                $config['frequency'],
                $now_gmt,
                $timezone
            );
        } else {
            $created = (string) $aggregate['post']->post_date_gmt;
            if ( is_wp_error( Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt( $created ) ) ) {
                return self::error( 'invalid_alert_creation_time' );
            }
            $state                         = self::new_state( $config, $created, true );
            $state['activation_cutoff_gmt'] = $created;
            $next_due                     = $now_gmt;
        }

        if ( is_wp_error( $next_due ) ) {
            return $next_due;
        }

        $saved = self::save_state_and_due( $alert_id, $state, $next_due );
        if ( is_wp_error( $saved ) ) {
            return $saved;
        }

        return $state;
    }

    private static function new_state( $config, $activation_gmt, $initial_pending ) {
        return array(
            'schema_version'             => Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::SCHEMA_VERSION,
            'policy_version'             => Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::POLICY_VERSION,
            'config_hash'                => $config['hash'],
            'frequency'                  => $config['frequency'],
            'activation_cutoff_gmt'      => $activation_gmt,
            'initial_pending'            => (bool) $initial_pending,
            'current_window_key'         => '',
            'window_start_gmt'           => '',
            'window_end_gmt'             => '',
            'selected_job_ids'           => array(),
            'attempt_count'              => 0,
            'next_retry_gmt'             => '',
            'outcome'                    => self::OUTCOME_READY,
            'last_evaluated_window'      => '',
            'last_evaluated_outcome'     => '',
            'last_evaluated_at_gmt'      => '',
            'mail_accepted_window'       => '',
            'mail_accepted_at_gmt'       => '',
            'failure_category'  => '',
        );
    }

    private static function validate_aggregate( $alert_id ) {
        $post = get_post( $alert_id );
        if ( ! $post ) {
            return self::error( 'alert_missing' );
        }
        if ( 'job_alert' !== $post->post_type ) {
            return self::error( 'wrong_post_type' );
        }
        if ( 'publish' !== $post->post_status ) {
            return self::error( 'alert_inactive' );
        }

        $owner_id = (int) $post->post_author;
        $user     = $owner_id ? get_user_by( 'ID', $owner_id ) : false;
        if ( ! $user ) {
            return self::error( 'owner_missing' );
        }
        if (
            ! in_array( 'wp_job_board_pro_candidate', (array) $user->roles, true )
            || ! class_exists( 'WP_Job_Board_Pro_User' )
            || ! WP_Job_Board_Pro_User::is_candidate( $owner_id )
            || 'approved' !== WP_Job_Board_Pro_User::get_user_status( $owner_id )
        ) {
            return self::error( 'owner_ineligible' );
        }

        $profile_id = absint( WP_Job_Board_Pro_User::get_candidate_by_user_id( $owner_id ) );
        if (
            ! $profile_id
            || 'candidate' !== get_post_type( $profile_id )
            || 'publish' !== get_post_status( $profile_id )
            || $owner_id !== (int) WP_Job_Board_Pro_User::get_user_by_candidate_id( $profile_id )
        ) {
            return self::error( 'canonical_profile_missing' );
        }

        $candidate_key = defined( 'WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX' )
            ? WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX . 'candidate_id'
            : '_job_alert_candidate_id';
        $compat_id     = absint( get_post_meta( $alert_id, $candidate_key, true ) );
        if ( $compat_id && $compat_id !== $profile_id ) {
            return self::error( 'candidate_profile_mismatch' );
        }

        return array(
            'post'       => $post,
            'owner_id'   => $owner_id,
            'profile_id' => $profile_id,
        );
    }

    private static function load_config( $alert_id ) {
        $prefix    = defined( 'WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX' )
            ? WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX
            : '_job_alert_';
        $frequency = get_post_meta( $alert_id, $prefix . 'email_frequency', true );
        $query     = get_post_meta( $alert_id, $prefix . 'alert_query', true );
        $frequency = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::normalize_frequency(
            $frequency,
            true
        );
        if ( is_wp_error( $frequency ) ) {
            return $frequency;
        }

        $query = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::normalize_saved_query( $query );
        if ( is_wp_error( $query ) ) {
            return $query;
        }

        $hash = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::config_hash(
            $frequency,
            $query,
            true
        );
        if ( is_wp_error( $hash ) ) {
            return $hash;
        }

        return array(
            'frequency' => $frequency,
            'query'     => $query,
            'hash'      => $hash,
        );
    }

    private static function validate_state( $state ) {
        $required = array(
            'schema_version',
            'policy_version',
            'config_hash',
            'frequency',
            'activation_cutoff_gmt',
            'initial_pending',
            'current_window_key',
            'window_start_gmt',
            'window_end_gmt',
            'selected_job_ids',
            'attempt_count',
            'next_retry_gmt',
            'outcome',
            'last_evaluated_window',
            'last_evaluated_outcome',
            'last_evaluated_at_gmt',
            'mail_accepted_window',
            'mail_accepted_at_gmt',
            'failure_category',
        );

        foreach ( $required as $key ) {
            if ( ! array_key_exists( $key, $state ) ) {
                return self::error( 'invalid_state' );
            }
        }
        if ( count( $state ) !== count( $required ) ) {
            return self::error( 'invalid_state' );
        }

        $outcomes = array(
            self::OUTCOME_READY,
            self::OUTCOME_SNAPSHOT,
            self::OUTCOME_RETRYABLE,
            self::OUTCOME_MAIL_ACCEPTED,
            self::OUTCOME_DELIVERED,
            self::OUTCOME_EMPTY,
            self::OUTCOME_TERMINAL,
        );
        if ( ! is_array( $state['selected_job_ids'] ) ) {
            return self::error( 'invalid_state' );
        }

        $frequency = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::normalize_frequency(
            $state['frequency'],
            true
        );
        $selected  = array_values( array_unique( array_map( 'absint', $state['selected_job_ids'] ) ) );

        if (
            Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::SCHEMA_VERSION !== (int) $state['schema_version']
            || Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::POLICY_VERSION !== (int) $state['policy_version']
            || is_wp_error( $frequency )
            || ! is_string( $state['config_hash'] )
            || 1 !== preg_match( '/^[a-f0-9]{64}$/D', $state['config_hash'] )
            || ! is_array( $state['selected_job_ids'] )
            || count( $state['selected_job_ids'] ) > self::MAX_RESULTS
            || count( $selected ) !== count( $state['selected_job_ids'] )
            || (int) $state['attempt_count'] < 0
            || (int) $state['attempt_count'] > self::MAX_ATTEMPTS
            || ! in_array( $state['outcome'], $outcomes, true )
            || is_wp_error( Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt( $state['activation_cutoff_gmt'] ) )
        ) {
            return self::error( 'invalid_state' );
        }

        $in_flight = in_array(
            $state['outcome'],
            array( self::OUTCOME_SNAPSHOT, self::OUTCOME_RETRYABLE, self::OUTCOME_MAIL_ACCEPTED ),
            true
        );
        if (
            $in_flight
            && (
                ! $selected
                || (int) $state['attempt_count'] < 1
                || '' === $state['current_window_key']
                || is_wp_error( Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt( $state['window_start_gmt'] ) )
                || is_wp_error( Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt( $state['window_end_gmt'] ) )
            )
        ) {
            return self::error( 'invalid_state_transition' );
        }

        if (
            self::OUTCOME_RETRYABLE === $state['outcome']
            && is_wp_error( Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt( $state['next_retry_gmt'] ) )
        ) {
            return self::error( 'invalid_state_transition' );
        }

        if (
            self::OUTCOME_MAIL_ACCEPTED === $state['outcome']
            && (
                $state['mail_accepted_window'] !== $state['current_window_key']
                || is_wp_error( Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt( $state['mail_accepted_at_gmt'] ) )
            )
        ) {
            return self::error( 'invalid_state_transition' );
        }

        return true;
    }

    private static function resolve_selected( $alert_id, $adapter, $config, $state, $window, $restrict_to = null ) {
        $ids = call_user_func(
            $adapter,
            array(
                'alert_id'    => $alert_id,
                'config_hash' => $config['hash'],
                'frequency'   => $config['frequency'],
                'query'       => $config['query'],
                'window'      => $window,
            )
        );

        if ( ! is_array( $ids ) ) {
            return self::error( 'eligible_adapter_failed' );
        }

        $restrict = null;
        if ( is_array( $restrict_to ) ) {
            $restrict = array_values( array_unique( array_map( 'absint', $restrict_to ) ) );
        }

        $cutoff = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt(
            $state['activation_cutoff_gmt']
        );
        $end    = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt(
            $window['end_gmt']
        );
        if ( is_wp_error( $cutoff ) || is_wp_error( $end ) ) {
            return self::error( 'invalid_window_state' );
        }

        $eligible = array();
        foreach ( array_values( array_unique( array_map( 'absint', $ids ) ) ) as $job_id ) {
            if (
                ! $job_id
                || ( null !== $restrict && ! in_array( $job_id, $restrict, true ) )
                || 'job_listing' !== get_post_type( $job_id )
                || 'publish' !== get_post_status( $job_id )
                || Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::was_job_delivered( $alert_id, $job_id )
            ) {
                continue;
            }

            $published = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt(
                (string) get_post_field( 'post_date_gmt', $job_id )
            );
            if ( is_wp_error( $published ) || $published < $cutoff || $published >= $end ) {
                continue;
            }

            $eligible[] = array(
                'id'        => $job_id,
                'published' => $published->format( 'Y-m-d H:i:s' ),
            );
        }

        usort(
            $eligible,
            static function ( $left, $right ) {
                $date = strcmp( $left['published'], $right['published'] );
                return 0 !== $date ? $date : $left['id'] <=> $right['id'];
            }
        );

        return array_slice(
            array_map(
                static function ( $row ) {
                    return $row['id'];
                },
                $eligible
            ),
            0,
            self::MAX_RESULTS
        );
    }

    private static function attempt( $alert_id, $selected, $window, $state, $mail_adapter, $now_gmt, $timezone, $options, $reuse_snapshot ) {
        if ( ! $reuse_snapshot ) {
            $state['attempt_count']++;
        }

        if ( $state['attempt_count'] < 1 || $state['attempt_count'] > self::MAX_ATTEMPTS ) {
            return self::error( 'invalid_attempt_state' );
        }

        $state['current_window_key']        = $window['key'];
        $state['window_start_gmt']          = $window['start_gmt'];
        $state['window_end_gmt']            = $window['end_gmt'];
        $state['selected_job_ids']          = array_values( $selected );
        $state['next_retry_gmt']            = '';
        $state['outcome']                   = self::OUTCOME_SNAPSHOT;
        $state['failure_category'] = '';

        $saved = Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::save_state( $alert_id, $state );
        if ( is_wp_error( $saved ) ) {
            return $saved;
        }

        if ( self::checkpoint( $options, 'snapshot_persisted', $state ) ) {
            return self::result( 'checkpoint_snapshot_persisted', $state );
        }

        try {
            $mail_result = Raspitajse_Communications_Transport::with_sender_channel(
                Raspitajse_Communications_Sender_Policy::CHANNEL_CANDIDATE_ALERTS,
                static function () use ( $mail_adapter, $alert_id, $selected, $window, $state ) {
                    return call_user_func(
                        $mail_adapter,
                        array(
                            'alert_id'     => $alert_id,
                            'job_ids'      => array_values( $selected ),
                            'window_key'   => $window['key'],
                            'attempt'      => (int) $state['attempt_count'],
                            'channel'      => Raspitajse_Communications_Sender_Policy::CHANNEL_CANDIDATE_ALERTS,
                        )
                    );
                }
            );
        } catch ( Throwable $throwable ) {
            $mail_result = self::error( 'transport_exception' );
        }

        if ( true === $mail_result ) {
            $state['outcome']              = self::OUTCOME_MAIL_ACCEPTED;
            $state['mail_accepted_window'] = $window['key'];
            $state['mail_accepted_at_gmt'] = $now_gmt;

            $saved = Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::save_state( $alert_id, $state );
            if ( is_wp_error( $saved ) ) {
                return $saved;
            }

            if ( self::checkpoint( $options, 'mail_accepted', $state ) ) {
                return self::result( 'checkpoint_mail_accepted', $state );
            }

            $ledger = Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::reconcile_delivered_jobs(
                $alert_id,
                $selected
            );
            if ( is_wp_error( $ledger ) ) {
                return $ledger;
            }

            if ( self::checkpoint( $options, 'ledger_reconciled', $state ) ) {
                return self::result( 'checkpoint_ledger_reconciled', $state );
            }

            return self::complete( $alert_id, $state, self::OUTCOME_DELIVERED, $now_gmt, $timezone );
        }

        $category = is_wp_error( $mail_result )
            ? sanitize_key( $mail_result->get_error_code() )
            : 'transport_false';
        $category = $category ?: 'transport_failure';

        if ( $state['attempt_count'] >= self::MAX_ATTEMPTS ) {
            $state['failure_category'] = $category;
            return self::complete( $alert_id, $state, self::OUTCOME_TERMINAL, $now_gmt, $timezone );
        }

        $hours                   = 1 === (int) $state['attempt_count'] ? 1 : 6;
        $now                     = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt( $now_gmt );
        $state['outcome']        = self::OUTCOME_RETRYABLE;
        $state['next_retry_gmt'] = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::format_gmt(
            $now->modify( '+' . $hours . ' hours' )
        );
        $state['failure_category'] = $category;

        $saved = Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::save_state( $alert_id, $state );
        if ( is_wp_error( $saved ) ) {
            return $saved;
        }

        return self::result( self::OUTCOME_RETRYABLE, $state );
    }

    private static function recover_accepted( $alert_id, $state, $now_gmt, $timezone, $options ) {
        if (
            '' === $state['mail_accepted_window']
            || $state['mail_accepted_window'] !== $state['current_window_key']
            || ! $state['selected_job_ids']
        ) {
            return self::error( 'invalid_accepted_state' );
        }

        $ledger = Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::reconcile_delivered_jobs(
            $alert_id,
            $state['selected_job_ids']
        );
        if ( is_wp_error( $ledger ) ) {
            return $ledger;
        }

        if ( self::checkpoint( $options, 'recovery_ledger_reconciled', $state ) ) {
            return self::result( 'checkpoint_recovery_ledger_reconciled', $state );
        }

        return self::complete(
            $alert_id,
            $state,
            self::OUTCOME_DELIVERED,
            $now_gmt,
            $timezone,
            array( 'recovered' => true )
        );
    }

    private static function complete( $alert_id, $state, $outcome, $now_gmt, $timezone, $extra = array() ) {
        if (
            ! in_array( $outcome, array( self::OUTCOME_DELIVERED, self::OUTCOME_EMPTY, self::OUTCOME_TERMINAL ), true )
            || '' === $state['current_window_key']
        ) {
            return self::error( 'invalid_completion' );
        }

        $next_due = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::next_due_gmt(
            $state['frequency'],
            $now_gmt,
            $timezone
        );
        if ( is_wp_error( $next_due ) ) {
            return $next_due;
        }

        $state['outcome']                = $outcome;
        $state['initial_pending']        = false;
        $state['next_retry_gmt']         = '';
        $state['last_evaluated_window']  = $state['current_window_key'];
        $state['last_evaluated_outcome'] = $outcome;
        $state['last_evaluated_at_gmt']  = $now_gmt;

        if ( self::OUTCOME_TERMINAL !== $outcome ) {
            $state['failure_category'] = '';
        }

        $saved = self::save_state_and_due( $alert_id, $state, $next_due );
        if ( is_wp_error( $saved ) ) {
            return $saved;
        }

        return self::result( $outcome, $state, array_merge( $extra, array( 'next_due_gmt' => $next_due ) ) );
    }

    private static function save_state_and_due( $alert_id, $state, $next_due ) {
        $saved = Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::save_state( $alert_id, $state );
        if ( is_wp_error( $saved ) ) {
            return $saved;
        }

        return Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::save_next_due(
            $alert_id,
            $next_due
        );
    }

    private static function window_from_state( $state ) {
        if (
            '' === $state['current_window_key']
            || is_wp_error( Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt( $state['window_start_gmt'] ) )
            || is_wp_error( Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt( $state['window_end_gmt'] ) )
        ) {
            return self::error( 'invalid_window_state' );
        }

        return array(
            'key'       => $state['current_window_key'],
            'start_gmt' => $state['window_start_gmt'],
            'end_gmt'   => $state['window_end_gmt'],
        );
    }

    private static function checkpoint( $options, $name, $state ) {
        return isset( $options['checkpoint'] )
            && is_callable( $options['checkpoint'] )
            && true === call_user_func( $options['checkpoint'], $name, $state );
    }

    private static function result( $status, $state, $extra = array() ) {
        return array_merge(
            array(
                'status'      => $status,
                'outcome'     => $state['outcome'],
                'window_key'  => $state['current_window_key'],
                'attempts'    => (int) $state['attempt_count'],
                'selected_ids'=> array_values( array_map( 'absint', $state['selected_job_ids'] ) ),
            ),
            $extra
        );
    }

    private static function error( $code ) {
        return new WP_Error(
            'raspitajse_cja_' . sanitize_key( $code ),
            'Candidate job alert delivery service failed closed.'
        );
    }
}
