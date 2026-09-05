<?php
/**
 * Runtime guard and sanitized snapshot provider for the staging owned-cron runner.
 *
 * Loaded only by WP-CLI through --require. It is deliberately not a WordPress
 * plugin and has no scheduler-facing interface of its own.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    return;
}

if ( ! defined( 'WP_HTTP_BLOCK_EXTERNAL' ) ) {
    define( 'WP_HTTP_BLOCK_EXTERNAL', true );
}

const RASPITAJSE_STAGING_CRON_ROOT = '/home/u601262303/domains/raspitajse.com/public_html/public_html_stage';
if ( 'selective_runner_v1' === getenv( 'RASPITAJSE_STAGING_CRON_CONTEXT' ) && ! defined( 'RASPITAJSE_STAGING_SELECTIVE_RUNNER' ) ) {
    define( 'RASPITAJSE_STAGING_SELECTIVE_RUNNER', true );
}


/**
 * The complete executable allowlist. Keep this map literal and caller-independent.
 *
 * @return array<string,array<string,mixed>>
 */
function raspitajse_staging_owned_cron_contracts() {
    return array(
        'raspitajse_job_listing_expiry_evaluator' => array(
            'callback'      => 'Raspitajse_Communications_Job_Listing_Expiry::run',
            'priority'      => 10,
            'accepted_args' => 1,
        ),
        'raspitajse_employer_job_expiry_notice_evaluator' => array(
            'callback'      => 'Raspitajse_Communications_Employer_Job_Expiry_Notification::run',
            'priority'      => 10,
            'accepted_args' => 1,
        ),
        'raspitajse_candidate_job_alert_evaluator' => array(
            'callback'      => 'Raspitajse_Communications_Candidate_Job_Alert_Evaluator::run',
            'priority'      => 10,
            'accepted_args' => 2,
        ),
    );
}

function raspitajse_staging_owned_cron_state_file() {
    $path = getenv( 'RASPITAJSE_STAGING_CRON_STATE_FILE' );
    if ( ! is_string( $path ) || 1 !== preg_match( '#^/tmp/raspitajse-staging-owned-cron\.[A-Za-z0-9]+/guard\.json$#D', $path ) ) {
        return '';
    }

    return $path;
}

function raspitajse_staging_owned_cron_increment( $key ) {
    $allowed = array( 'wp_http_api', 'http_intercepted', 'http_unexpected', 'wp_mail', 'phpmailer', 'smtp', 'payment' );
    if ( ! in_array( $key, $allowed, true ) ) {
        return;
    }

    $path = raspitajse_staging_owned_cron_state_file();
    if ( '' === $path ) {
        return;
    }

    $handle = @fopen( $path, 'c+' );
    if ( false === $handle ) {
        throw new RuntimeException( 'guard_state_unavailable' );
    }

    try {
        if ( ! flock( $handle, LOCK_EX ) ) {
            throw new RuntimeException( 'guard_state_lock_failed' );
        }
        rewind( $handle );
        $raw   = stream_get_contents( $handle );
        $state = json_decode( is_string( $raw ) ? $raw : '', true );
        if ( ! is_array( $state ) ) {
            $state = array();
        }
        $state[ $key ] = isset( $state[ $key ] ) ? (int) $state[ $key ] + 1 : 1;
        rewind( $handle );
        if ( ! ftruncate( $handle, 0 ) || false === fwrite( $handle, wp_json_encode( $state ) ) ) {
            throw new RuntimeException( 'guard_state_write_failed' );
        }
        fflush( $handle );
        flock( $handle, LOCK_UN );
    } finally {
        fclose( $handle );
    }
}

function raspitajse_staging_owned_cron_record_progress( $hook, $summary ) {
    $allowed_hooks = array_keys( raspitajse_staging_owned_cron_contracts() );
    if ( ! in_array( $hook, $allowed_hooks, true ) || ! is_array( $summary ) ) {
        return;
    }

    $row = array();
    foreach ( array( 'selected', 'processed', 'succeeded', 'failed' ) as $key ) {
        if ( isset( $summary[ $key ] ) && is_numeric( $summary[ $key ] ) ) {
            $row[ $key ] = max( 0, (int) $summary[ $key ] );
        }
    }
    if ( array_key_exists( 'has_more', $summary ) ) {
        $row['has_more'] = (bool) $summary['has_more'];
    }

    $path = raspitajse_staging_owned_cron_state_file();
    if ( '' === $path ) {
        return;
    }
    $handle = @fopen( $path, 'c+' );
    if ( false === $handle ) {
        throw new RuntimeException( 'guard_state_unavailable' );
    }
    try {
        if ( ! flock( $handle, LOCK_EX ) ) {
            throw new RuntimeException( 'guard_state_lock_failed' );
        }
        rewind( $handle );
        $state = json_decode( (string) stream_get_contents( $handle ), true );
        $state = is_array( $state ) ? $state : array();
        $state['progress'] = isset( $state['progress'] ) && is_array( $state['progress'] ) ? $state['progress'] : array();
        $state['progress'][ $hook ] = $row;
        rewind( $handle );
        if ( ! ftruncate( $handle, 0 ) || false === fwrite( $handle, wp_json_encode( $state ) ) ) {
            throw new RuntimeException( 'guard_state_write_failed' );
        }
        fflush( $handle );
        flock( $handle, LOCK_UN );
    } finally {
        fclose( $handle );
    }
}

function raspitajse_staging_owned_cron_job_expiry_observation( $summary ) {
    $selected = isset( $summary['selected'] ) ? (int) $summary['selected'] : 0;
    $failed   = isset( $summary['failed'] ) ? (int) $summary['failed'] : 0;
    raspitajse_staging_owned_cron_record_progress( 'raspitajse_job_listing_expiry_evaluator', array( 'selected' => $selected, 'processed' => (int) ( ( $summary['expired'] ?? 0 ) + ( $summary['skipped'] ?? 0 ) + $failed ), 'succeeded' => (int) ( $summary['expired'] ?? 0 ), 'failed' => $failed ) );
}

function raspitajse_staging_owned_cron_employer_expiry_observation( $summary ) {
    $selected = isset( $summary['selected'] ) ? (int) $summary['selected'] : 0;
    $failed   = isset( $summary['failed'] ) ? (int) $summary['failed'] : 0;
    raspitajse_staging_owned_cron_record_progress( 'raspitajse_employer_job_expiry_notice_evaluator', array( 'selected' => $selected, 'processed' => (int) ( ( $summary['delivered'] ?? 0 ) + ( $summary['retryable_failed'] ?? 0 ) + ( $summary['invalid_recipient'] ?? 0 ) + ( $summary['terminal'] ?? 0 ) + ( $summary['skipped'] ?? 0 ) + $failed ), 'succeeded' => (int) ( $summary['delivered'] ?? 0 ), 'failed' => $failed ) );
}

function raspitajse_staging_owned_cron_candidate_alert_observation( $summary ) {
    raspitajse_staging_owned_cron_record_progress(
        'raspitajse_candidate_job_alert_evaluator',
        array(
            'processed' => isset( $summary['processed'] ) ? (int) $summary['processed'] : 0,
            'failed'    => isset( $summary['errors'] ) ? (int) $summary['errors'] : 0,
            'has_more'  => ! empty( $summary['has_more'] ),
        )
    );
}

function raspitajse_staging_owned_cron_http_guard( $preempt, $args, $url ) {
    raspitajse_staging_owned_cron_increment( 'wp_http_api' );
    raspitajse_staging_owned_cron_increment( 'http_intercepted' );

    $known = false;
    foreach ( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 18 ) as $frame ) {
        $class = isset( $frame['class'] ) ? (string) $frame['class'] : '';
        if ( 0 === strpos( $class, 'LiteSpeed\\' ) ) {
            $known = true;
            break;
        }
    }
    if ( ! $known ) {
        raspitajse_staging_owned_cron_increment( 'http_unexpected' );
    }

    return new WP_Error( 'raspitajse_staging_owned_cron_http_blocked', 'External HTTP is blocked for this staging runner.' );
}

function raspitajse_staging_owned_cron_mail_guard( $return, $atts ) {
    raspitajse_staging_owned_cron_increment( 'wp_mail' );
    return $return;
}

function raspitajse_staging_owned_cron_phpmailer_guard( $phpmailer ) {
    raspitajse_staging_owned_cron_increment( 'phpmailer' );
    if ( is_object( $phpmailer ) && isset( $phpmailer->Mailer ) && 'smtp' === strtolower( (string) $phpmailer->Mailer ) ) {
        raspitajse_staging_owned_cron_increment( 'smtp' );
    }
}

function raspitajse_staging_owned_cron_callback_name( $callback ) {
    if ( is_array( $callback ) && isset( $callback[0], $callback[1] ) ) {
        $owner = is_object( $callback[0] ) ? get_class( $callback[0] ) : (string) $callback[0];
        return $owner . '::' . (string) $callback[1];
    }
    if ( is_string( $callback ) ) {
        return $callback;
    }

    return 'unsupported';
}

/**
 * @return array<int,array<string,mixed>>
 */
function raspitajse_staging_owned_cron_callbacks( $hook ) {
    global $wp_filter;

    $rows = array();
    if ( empty( $wp_filter[ $hook ] ) || ! isset( $wp_filter[ $hook ]->callbacks ) ) {
        return $rows;
    }

    foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
        foreach ( $callbacks as $entry ) {
            $rows[] = array(
                'callback'      => raspitajse_staging_owned_cron_callback_name( $entry['function'] ),
                'priority'      => (int) $priority,
                'accepted_args' => isset( $entry['accepted_args'] ) ? (int) $entry['accepted_args'] : 1,
            );
        }
    }
    usort(
        $rows,
        static function ( $left, $right ) {
            return strcmp( wp_json_encode( $left ), wp_json_encode( $right ) );
        }
    );

    return $rows;
}

/**
 * @return array<int,array<string,mixed>>
 */
function raspitajse_staging_owned_cron_events( $hook ) {
    $rows = array();
    foreach ( (array) _get_cron_array() as $timestamp => $hooks ) {
        if ( empty( $hooks[ $hook ] ) || ! is_array( $hooks[ $hook ] ) ) {
            continue;
        }
        foreach ( $hooks[ $hook ] as $event ) {
            $args   = isset( $event['args'] ) && is_array( $event['args'] ) ? $event['args'] : array();
            $rows[] = array(
                'timestamp' => (int) $timestamp,
                'schedule'  => isset( $event['schedule'] ) ? (string) $event['schedule'] : '',
                'interval'  => isset( $event['interval'] ) ? (int) $event['interval'] : 0,
                'args_empty'=> array() === $args,
                'args_hash' => hash( 'sha256', maybe_serialize( $args ) ),
            );
        }
    }
    usort(
        $rows,
        static function ( $left, $right ) {
            return $left['timestamp'] <=> $right['timestamp'];
        }
    );

    return $rows;
}

function raspitajse_staging_owned_cron_fingerprint( $exclude_allowlist ) {
    $allow = array_keys( raspitajse_staging_owned_cron_contracts() );
    $rows  = array();
    foreach ( (array) _get_cron_array() as $timestamp => $hooks ) {
        foreach ( (array) $hooks as $hook => $events ) {
            if ( $exclude_allowlist && in_array( $hook, $allow, true ) ) {
                continue;
            }
            foreach ( (array) $events as $event ) {
                $args   = isset( $event['args'] ) && is_array( $event['args'] ) ? $event['args'] : array();
                $rows[] = array(
                    'timestamp' => (int) $timestamp,
                    'hook'      => (string) $hook,
                    'schedule'  => isset( $event['schedule'] ) ? (string) $event['schedule'] : '',
                    'interval'  => isset( $event['interval'] ) ? (int) $event['interval'] : 0,
                    'args_hash' => hash( 'sha256', maybe_serialize( $args ) ),
                );
            }
        }
    }
    usort(
        $rows,
        static function ( $left, $right ) {
            return strcmp( wp_json_encode( $left ), wp_json_encode( $right ) );
        }
    );

    return hash( 'sha256', wp_json_encode( $rows ) );
}

function raspitajse_staging_owned_cron_post_snapshot( $post_types, $meta_keys = array() ) {
    global $wpdb;

    $post_types   = array_values( array_map( 'sanitize_key', (array) $post_types ) );
    $placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
    $sql          = $wpdb->prepare(
        "SELECT ID, post_type, post_status, post_author, post_date_gmt, post_modified_gmt
         FROM {$wpdb->posts}
         WHERE post_type IN ({$placeholders})
         ORDER BY ID ASC",
        $post_types
    );
    $rows = $wpdb->get_results( $sql, ARRAY_A );
    $rows = is_array( $rows ) ? $rows : array();

    foreach ( $rows as &$row ) {
        $meta = array();
        foreach ( $meta_keys as $meta_key ) {
            $values            = get_post_meta( (int) $row['ID'], $meta_key, false );
            $meta[ $meta_key ] = hash( 'sha256', maybe_serialize( $values ) );
        }
        $row['meta'] = $meta;
    }
    unset( $row );

    $statuses = array();
    foreach ( $rows as $row ) {
        $key              = (string) $row['post_status'];
        $statuses[ $key ] = isset( $statuses[ $key ] ) ? $statuses[ $key ] + 1 : 1;
    }
    ksort( $statuses );

    return array(
        'count'       => count( $rows ),
        'statuses'    => $statuses,
        'fingerprint' => hash( 'sha256', wp_json_encode( $rows ) ),
    );
}

function raspitajse_staging_owned_cron_employer_users_snapshot() {
    global $wpdb;

    $like = '%' . $wpdb->esc_like( '"wp_job_board_pro_employer"' ) . '%';
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT u.ID, u.user_registered, u.user_status
             FROM {$wpdb->users} u
             INNER JOIN {$wpdb->usermeta} m ON m.user_id = u.ID
             WHERE m.meta_key = %s AND m.meta_value LIKE %s
             ORDER BY u.ID ASC",
            $wpdb->prefix . 'capabilities',
            $like
        ),
        ARRAY_A
    );
    $rows = is_array( $rows ) ? $rows : array();
    return array( 'count' => count( $rows ), 'fingerprint' => hash( 'sha256', wp_json_encode( $rows ) ) );
}

function raspitajse_staging_owned_cron_action_scheduler_snapshot() {
    global $wpdb;

    $table = $wpdb->prefix . 'actionscheduler_actions';
    $found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
    if ( $found !== $table ) {
        return array( 'ok' => false, 'reason' => 'action_scheduler_table_missing' );
    }

    $rows = $wpdb->get_results(
        "SELECT action_id, hook, status, scheduled_date_gmt, group_id, attempts
         FROM {$table}
         WHERE status = 'pending'
         ORDER BY action_id ASC",
        ARRAY_A
    );
    $rows = is_array( $rows ) ? $rows : array();
    $protected = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT status, attempts FROM {$table} WHERE action_id = %d LIMIT 1",
            32733
        ),
        ARRAY_A
    );

    return array(
        'ok'                    => is_array( $protected ) && 'pending' === $protected['status'] && 0 === (int) $protected['attempts'],
        'reason'                => is_array( $protected ) ? '' : 'protected_action_missing',
        'pending_count'         => count( $rows ),
        'pending_fingerprint'   => hash( 'sha256', wp_json_encode( $rows ) ),
        'protected_32733_status'=> is_array( $protected ) ? (string) $protected['status'] : 'missing',
        'protected_32733_attempts'=> is_array( $protected ) ? (int) $protected['attempts'] : -1,
    );
}

function raspitajse_staging_owned_cron_claim_counts() {
    global $wpdb;

    $exact = (int) $wpdb->get_var(
        $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name = %s", 'raspitajse_job_listing_expiry_claim' )
    );
    $notice = (int) $wpdb->get_var(
        $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( 'raspitajse_job_expiry_notice_claim_' ) . '%' )
    );
    $alerts = (int) $wpdb->get_var(
        $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( 'raspitajse_cja_claim_' ) . '%' )
    );

    return array( 'job_expiry' => $exact, 'employer_notice' => $notice, 'candidate_alert' => $alerts );
}

function raspitajse_staging_owned_cron_due_work() {
    global $wpdb;

    $today    = wp_date( 'Y-m-d', null, wp_timezone() );
    $tomorrow = current_datetime()->modify( '+1 day' )->format( 'Y-m-d' );
    $now_gmt  = gmdate( 'Y-m-d H:i:s' );
    $expired  = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key=%s
             WHERE p.post_type='job_listing' AND p.post_status='publish'
             AND m.meta_value REGEXP %s AND m.meta_value < %s",
            '_job_expiry_date',
            '^[0-9]{4}-[0-9]{2}-[0-9]{2}$',
            $today
        )
    );
    $tomorrow_jobs = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key=%s
             WHERE p.post_type='job_listing' AND p.post_status='publish' AND m.meta_value=%s",
            '_job_expiry_date',
            $tomorrow
        )
    );
    $due_alerts = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key=%s
             WHERE p.post_type='job_alert' AND p.post_status='publish'
             AND (m.meta_value IS NULL OR m.meta_value='' OR m.meta_value<=%s)",
            '_raspitajse_cja_next_due_gmt',
            $now_gmt
        )
    );

    return array(
        'expired_jobs'  => $expired,
        'tomorrow_jobs' => $tomorrow_jobs,
        'due_job_alerts'=> $due_alerts,
        'total'         => $expired + $tomorrow_jobs + $due_alerts,
    );
}

function raspitajse_staging_owned_cron_monitor_hook( $hook ) {
    $expected = getenv( 'RASPITAJSE_STAGING_CRON_TARGET' );
    $expected = is_string( $expected ) ? $expected : '';
    $allow    = array_keys( raspitajse_staging_owned_cron_contracts() );

    $forbidden = array(
        'action_scheduler_run_queue',
        'raspitajse_candidate_job_alert_evaluator_continue',
        'wp_job_board_pro_email_daily_notices',
        'wp_job_board_pro_check_for_expired_jobs',
    );
    $payment_prefixes = array(
        'woocommerce_payment_complete',
        'woocommerce_order_status_',
        'woocommerce_refund_',
        'woocommerce_order_refunded',
        'woocommerce_create_refund',
    );
    foreach ( $payment_prefixes as $prefix ) {
        if ( 0 === strpos( (string) $hook, $prefix ) ) {
            raspitajse_staging_owned_cron_increment( 'payment' );
            throw new RuntimeException( 'payment_path_blocked' );
        }
    }
    if ( in_array( $hook, $forbidden, true ) ) {
        throw new RuntimeException( 'forbidden_scheduler_hook' );
    }
    if ( in_array( $hook, $allow, true ) && $hook !== $expected ) {
        throw new RuntimeException( 'unexpected_owned_hook' );
    }

    if ( ! empty( $GLOBALS['raspitajse_staging_owned_cron_scheduled_hooks'][ $hook ] ) && $hook !== $expected ) {
        throw new RuntimeException( 'non_allowlisted_cron_hook' );
    }
}

function raspitajse_staging_owned_cron_bootstrap() {
    $scheduled = array();
    foreach ( (array) _get_cron_array() as $hooks ) {
        foreach ( array_keys( (array) $hooks ) as $hook ) {
            $scheduled[ $hook ] = true;
        }
    }
    $GLOBALS['raspitajse_staging_owned_cron_scheduled_hooks'] = $scheduled;

    add_filter( 'pre_http_request', 'raspitajse_staging_owned_cron_http_guard', PHP_INT_MIN, 3 );
    add_filter( 'pre_wp_mail', 'raspitajse_staging_owned_cron_mail_guard', PHP_INT_MIN, 2 );
    add_action( 'phpmailer_init', 'raspitajse_staging_owned_cron_phpmailer_guard', PHP_INT_MAX, 1 );
    add_action( 'all', 'raspitajse_staging_owned_cron_monitor_hook', PHP_INT_MIN, 1 );
    add_action( 'raspitajse_job_listing_expiry_evaluator_observation', 'raspitajse_staging_owned_cron_job_expiry_observation', 10, 1 );
    add_action( 'raspitajse_employer_job_expiry_notice_evaluator_observation', 'raspitajse_staging_owned_cron_employer_expiry_observation', 10, 1 );
    add_action( 'raspitajse_candidate_job_alert_evaluator_observation', 'raspitajse_staging_owned_cron_candidate_alert_observation', 10, 1 );
}
WP_CLI::add_hook( 'after_wp_load', 'raspitajse_staging_owned_cron_bootstrap' );

/**
 * Return a complete sanitized gate snapshot. No PII or raw meta/cron args leave it.
 *
 * @return array<string,mixed>
 */
/**
 * Return the bounded normal-cycle snapshot: only safety, owned contracts/events,
 * continuation absence, and due timestamps. Deep state remains in snapshot().
 *
 * @return array<string,mixed>
 */
function raspitajse_staging_owned_cron_light_snapshot() {
    $reasons        = array();
    $contracts      = raspitajse_staging_owned_cron_contracts();
    $owned          = array();
    $contract_basis = array();
    $now            = time();
    $root           = realpath( ABSPATH );

    if ( 'staging' !== wp_get_environment_type() ) {
        $reasons[] = 'environment_not_staging';
    }
    if ( false === $root || realpath( RASPITAJSE_STAGING_CRON_ROOT ) !== $root ) {
        $reasons[] = 'staging_root_mismatch';
    }
    if ( ! defined( 'DISABLE_WP_CRON' ) || true !== DISABLE_WP_CRON ) {
        $reasons[] = 'wp_cron_not_disabled';
    }
    if ( ! defined( 'WP_HTTP_BLOCK_EXTERNAL' ) || true !== WP_HTTP_BLOCK_EXTERNAL ) {
        $reasons[] = 'external_http_not_blocked';
    }
    if ( ! function_exists( 'raspitajse_staging_mail_safety_is_staging' ) || ! raspitajse_staging_mail_safety_is_staging() ) {
        $reasons[] = 'mail_safety_missing';
    }
    if ( ! function_exists( 'raspitajse_staging_mail_safety_recipient' ) || '' === raspitajse_staging_mail_safety_recipient() ) {
        $reasons[] = 'mail_safety_recipient_invalid';
    }

    foreach ( $contracts as $hook => $expected ) {
        $callbacks = raspitajse_staging_owned_cron_callbacks( $hook );
        $events    = raspitajse_staging_owned_cron_events( $hook );
        $valid     = 1 === count( $callbacks ) && $expected === $callbacks[0]
            && 1 === count( $events )
            && 'hourly' === $events[0]['schedule']
            && 3600 === $events[0]['interval']
            && true === $events[0]['args_empty'];
        if ( ! $valid ) {
            $reasons[] = 'owned_contract_mismatch_' . $hook;
        }
        $owned[ $hook ] = array(
            'valid'               => $valid,
            'callback_count'      => count( $callbacks ),
            'callback_fingerprint'=> hash( 'sha256', wp_json_encode( $callbacks ) ),
            'event_count'         => count( $events ),
            'event_fingerprint'   => hash( 'sha256', wp_json_encode( $events ) ),
            'timestamp'           => 1 === count( $events ) ? (int) $events[0]['timestamp'] : 0,
            'due'                 => 1 === count( $events ) && (int) $events[0]['timestamp'] <= $now,
        );
        $event_shape = array_map(
            static function ( $event ) {
                unset( $event['timestamp'] );
                return $event;
            },
            $events
        );
        $contract_basis[ $hook ] = array( 'callbacks' => $callbacks, 'event_shape' => $event_shape );
    }

    $continuation_events = count( raspitajse_staging_owned_cron_events( 'raspitajse_candidate_job_alert_evaluator_continue' ) );
    if ( 0 !== $continuation_events ) {
        $reasons[] = 'continuation_event_present';
    }
    if ( false !== get_transient( 'doing_cron' ) ) {
        $reasons[] = 'doing_cron_not_clear';
    }

    return array(
        'ok'                   => empty( $reasons ),
        'reason_codes'         => $reasons,
        'environment'          => wp_get_environment_type(),
        'doing_cron_clear'      => false === get_transient( 'doing_cron' ),
        'continuation_events'   => $continuation_events,
        'owned'                => $owned,
        'contract_fingerprint' => hash( 'sha256', wp_json_encode( $contract_basis ) ),
    );
}

function raspitajse_staging_owned_cron_emit_light_snapshot() {
    $json = wp_json_encode( raspitajse_staging_owned_cron_light_snapshot(), JSON_UNESCAPED_SLASHES );
    echo 'RASPITAJSE_OWNED_CRON_SNAPSHOT=' . base64_encode( $json ) . PHP_EOL;
}

function raspitajse_staging_owned_cron_snapshot() {
    $reasons   = array();
    $contracts = raspitajse_staging_owned_cron_contracts();
    $owned     = array();
    $contract_basis = array();
    $now       = time();

    $root = realpath( ABSPATH );
    if ( 'staging' !== wp_get_environment_type() ) {
        $reasons[] = 'environment_not_staging';
    }
    if ( false === $root || realpath( RASPITAJSE_STAGING_CRON_ROOT ) !== $root ) {
        $reasons[] = 'staging_root_mismatch';
    }
    if ( ! defined( 'DISABLE_WP_CRON' ) || true !== DISABLE_WP_CRON ) {
        $reasons[] = 'wp_cron_not_disabled';
    }
    if ( ! defined( 'WP_HTTP_BLOCK_EXTERNAL' ) || true !== WP_HTTP_BLOCK_EXTERNAL ) {
        $reasons[] = 'external_http_not_blocked';
    }
    if ( ! function_exists( 'raspitajse_staging_mail_safety_is_staging' ) || ! raspitajse_staging_mail_safety_is_staging() ) {
        $reasons[] = 'mail_safety_missing';
    }
    if ( ! function_exists( 'raspitajse_staging_mail_safety_recipient' ) || '' === raspitajse_staging_mail_safety_recipient() ) {
        $reasons[] = 'mail_safety_recipient_invalid';
    }

    foreach ( $contracts as $hook => $expected ) {
        $callbacks = raspitajse_staging_owned_cron_callbacks( $hook );
        $events    = raspitajse_staging_owned_cron_events( $hook );
        $valid     = 1 === count( $callbacks ) && $expected === $callbacks[0]
            && 1 === count( $events )
            && 'hourly' === $events[0]['schedule']
            && 3600 === $events[0]['interval']
            && true === $events[0]['args_empty'];
        if ( ! $valid ) {
            $reasons[] = 'owned_contract_mismatch_' . $hook;
        }
        $owned[ $hook ] = array(
            'valid'             => $valid,
            'callback_count'    => count( $callbacks ),
            'callback_fingerprint'=> hash( 'sha256', wp_json_encode( $callbacks ) ),
            'event_count'       => count( $events ),
            'event_fingerprint' => hash( 'sha256', wp_json_encode( $events ) ),
            'timestamp'         => 1 === count( $events ) ? (int) $events[0]['timestamp'] : 0,
            'due'               => 1 === count( $events ) && (int) $events[0]['timestamp'] <= $now,
        );
        $event_shape = array_map(
            static function ( $event ) {
                unset( $event['timestamp'] );
                return $event;
            },
            $events
        );
        $contract_basis[ $hook ] = array(
            'callbacks'   => $callbacks,
            'event_shape' => $event_shape,
        );
    }

    $daily_callbacks = raspitajse_staging_owned_cron_callbacks( 'wp_job_board_pro_email_daily_notices' );
    $daily_events    = raspitajse_staging_owned_cron_events( 'wp_job_board_pro_email_daily_notices' );
    $shared_callbacks= raspitajse_staging_owned_cron_callbacks( 'wp_job_board_pro_check_for_expired_jobs' );
    $shared_events   = raspitajse_staging_owned_cron_events( 'wp_job_board_pro_check_for_expired_jobs' );
    $continuations   = raspitajse_staging_owned_cron_events( 'raspitajse_candidate_job_alert_evaluator_continue' );
    $vendor_job      = array_filter( $daily_callbacks, static function ( $row ) { return 'WP_Job_Board_Pro_Job_Alert::send_job_alert_notice' === $row['callback']; } );
    $vendor_candidate= array_filter( $daily_callbacks, static function ( $row ) { return 'WP_Job_Board_Pro_Candidate_Alert::send_candidate_alert_notice' === $row['callback']; } );
    $expiry_filters  = raspitajse_staging_owned_cron_callbacks( 'wp-job-board-pro-calculate-candidate-expiry' );
    $expiry_expected = array(
        'callback'      => 'Raspitajse_Communications_Job_Listing_Expiry::disable_candidate_expiry',
        'priority'      => PHP_INT_MAX,
        'accepted_args' => 2,
    );
    $expiry_owned = array_values( array_filter( $expiry_filters, static function ( $row ) use ( $expiry_expected ) { return $row === $expiry_expected; } ) );

    $negative = array(
        'legacy_daily_callbacks'       => count( $daily_callbacks ),
        'legacy_daily_events'          => count( $daily_events ),
        'legacy_shared_callbacks'      => count( $shared_callbacks ),
        'legacy_shared_events'         => count( $shared_events ),
        'continuation_events'          => count( $continuations ),
        'vendor_job_sender_callbacks'  => count( $vendor_job ),
        'vendor_candidate_sender_callbacks'=> count( $vendor_candidate ),
        'candidate_expiry_filter_count'=> count( $expiry_owned ),
        'candidate_expiry_effective'   => (int) apply_filters( 'wp-job-board-pro-calculate-candidate-expiry', 30, 0 ),
    );
    foreach ( $negative as $key => $value ) {
        $expected = 'candidate_expiry_filter_count' === $key ? 1 : 0;
        if ( $value !== $expected ) {
            $reasons[] = 'negative_contract_mismatch_' . $key;
        }
    }

    $as = raspitajse_staging_owned_cron_action_scheduler_snapshot();
    if ( empty( $as['ok'] ) ) {
        $reasons[] = isset( $as['reason'] ) && $as['reason'] ? $as['reason'] : 'protected_action_mismatch';
    }

    $claims = raspitajse_staging_owned_cron_claim_counts();
    if ( 0 !== array_sum( $claims ) ) {
        $reasons[] = 'owned_claim_active';
    }
    if ( false !== get_transient( 'doing_cron' ) ) {
        $reasons[] = 'doing_cron_not_clear';
    }

    $business = array(
        'candidates'       => raspitajse_staging_owned_cron_post_snapshot( array( 'candidate' ), array( '_candidate_expiry_date' ) ),
        'candidate_alerts' => raspitajse_staging_owned_cron_post_snapshot( array( 'candidate_alert' ) ),
        'job_alerts'       => raspitajse_staging_owned_cron_post_snapshot( array( 'job_alert' ), array( '_raspitajse_cja_next_due_gmt', '_raspitajse_cja_delivery_state', '_raspitajse_cja_delivered_job' ) ),
        'jobs'             => raspitajse_staging_owned_cron_post_snapshot( array( 'job_listing' ), array( '_job_expiry_date', '_job_featured', '_job_urgent', '_raspitajse_job_expiry_notice_state' ) ),
        'employers'        => raspitajse_staging_owned_cron_post_snapshot( array( 'employer' ) ),
        'employer_users'   => raspitajse_staging_owned_cron_employer_users_snapshot(),
        'packages'         => raspitajse_staging_owned_cron_post_snapshot( array( 'job_package' ) ),
        'orders'           => raspitajse_staging_owned_cron_post_snapshot( array( 'shop_order' ) ),
        'refunds'          => raspitajse_staging_owned_cron_post_snapshot( array( 'shop_order_refund' ) ),
    );
    $candidate_expiry_footprint = (int) $GLOBALS['wpdb']->get_var(
        $GLOBALS['wpdb']->prepare(
            "SELECT COUNT(*) FROM {$GLOBALS['wpdb']->postmeta} WHERE meta_key=%s AND meta_value<>''",
            '_candidate_expiry_date'
        )
    );
    $business['candidate_expiry_meta_footprint'] = $candidate_expiry_footprint;
    $business['fingerprint'] = hash( 'sha256', wp_json_encode( $business ) );

    return array(
        'ok'                       => empty( $reasons ),
        'reason_codes'             => $reasons,
        'environment'              => wp_get_environment_type(),
        'site_timezone'            => wp_timezone_string(),
        'disable_wp_cron'          => defined( 'DISABLE_WP_CRON' ) && true === DISABLE_WP_CRON,
        'mail_safety_loaded'        => function_exists( 'raspitajse_staging_mail_safety_is_staging' ),
        'doing_cron_clear'          => false === get_transient( 'doing_cron' ),
        'now_epoch'                => $now,
        'owned'                    => $owned,
        'negative_contract'        => $negative,
        'continuation_events'      => (int) $negative['continuation_events'],
        'contract_fingerprint'     => hash( 'sha256', wp_json_encode( array( $contract_basis, $negative ) ) ),
        'full_cron_fingerprint'    => raspitajse_staging_owned_cron_fingerprint( false ),
        'nonallow_cron_fingerprint'=> raspitajse_staging_owned_cron_fingerprint( true ),
        'action_scheduler'         => $as,
        'claims'                   => $claims,
        'due_business'             => raspitajse_staging_owned_cron_due_work(),
        'business'                 => $business,
    );
}

function raspitajse_staging_owned_cron_emit_snapshot() {
    $json = wp_json_encode( raspitajse_staging_owned_cron_snapshot(), JSON_UNESCAPED_SLASHES );
    echo 'RASPITAJSE_OWNED_CRON_SNAPSHOT=' . base64_encode( $json ) . PHP_EOL;
}
