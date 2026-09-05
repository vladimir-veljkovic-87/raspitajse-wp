<?php
/**
 * Active candidate-to-job alert evaluation and WPJBP integration.
 *
 * @package raspitajse-communications
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Reuse WPJBP matching while enforcing owned delivery eligibility/order.
 */
final class Raspitajse_Communications_Candidate_Job_Alert_Query_Adapter {

    const PAGE_SIZE = 50;
    const MAX_PAGES = 10;

    /**
     * Return up to five undelivered matching IDs in publication order.
     *
     * @return int[]
     */
    public static function eligible_jobs( $context ) {
        if (
            ! is_array( $context )
            || empty( $context['alert_id'] )
            || ! isset( $context['query'], $context['window']['end_gmt'] )
            || ! class_exists( 'WP_Job_Board_Pro_Query' )
        ) {
            return null;
        }

        $alert_id = absint( $context['alert_id'] );
        $query    = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::normalize_saved_query(
            $context['query']
        );
        $state    = Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::read_state( $alert_id );

        if (
            ! $alert_id
            || is_wp_error( $query )
            || ! is_array( $state )
            || empty( $state['activation_cutoff_gmt'] )
        ) {
            return null;
        }

        $cutoff = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt(
            $state['activation_cutoff_gmt']
        );
        $end    = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt(
            $context['window']['end_gmt']
        );
        if ( is_wp_error( $cutoff ) || is_wp_error( $end ) || $end <= $cutoff ) {
            return null;
        }

        $owner_id = (int) get_post_field( 'post_author', $alert_id );
        if ( ! $owner_id ) {
            return null;
        }

        $selected = array();
        for ( $page = 1; $page <= self::MAX_PAGES; $page++ ) {
            $ids = self::query_page(
                $query,
                $owner_id,
                $cutoff->format( 'Y-m-d H:i:s' ),
                $end->format( 'Y-m-d H:i:s' ),
                $page
            );
            if ( ! is_array( $ids ) ) {
                return null;
            }

            foreach ( $ids as $job_id ) {
                $job_id = absint( $job_id );
                if (
                    ! $job_id
                    || Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::was_job_delivered( $alert_id, $job_id )
                    || ! self::is_active_job( $job_id )
                ) {
                    continue;
                }

                $selected[] = $job_id;
                if ( count( $selected ) >= Raspitajse_Communications_Candidate_Job_Alert_Delivery_Service::MAX_RESULTS ) {
                    return $selected;
                }
            }

            if ( count( $ids ) < self::PAGE_SIZE ) {
                break;
            }
        }

        return $selected;
    }

    private static function query_page( $query, $owner_id, $cutoff_gmt, $end_gmt, $page ) {
        global $wpdb;

        $args_filter = static function ( $args ) use ( $page ) {
            $args['posts_per_page'] = Raspitajse_Communications_Candidate_Job_Alert_Query_Adapter::PAGE_SIZE;
            $args['paged']          = $page;
            $args['fields']         = 'ids';
            $args['post_status']    = 'publish';
            $args['orderby']        = array(
                'date' => 'ASC',
                'ID'   => 'ASC',
            );
            $args['order']                 = 'ASC';
            $args['raspitajse_cja_query']  = 1;
            $args['ignore_sticky_posts']   = true;
            $args['update_post_meta_cache'] = false;
            $args['update_post_term_cache'] = false;
            return $args;
        };
        $where_filter = static function ( $where, $wp_query ) use ( $wpdb, $cutoff_gmt, $end_gmt ) {
            if ( ! $wp_query->get( 'raspitajse_cja_query' ) ) {
                return $where;
            }
            return $where . $wpdb->prepare(
                " AND {$wpdb->posts}.post_date_gmt >= %s AND {$wpdb->posts}.post_date_gmt < %s",
                $cutoff_gmt,
                $end_gmt
            );
        };
        $orderby_filter = static function ( $orderby, $wp_query ) use ( $wpdb ) {
            if ( ! $wp_query->get( 'raspitajse_cja_query' ) ) {
                return $orderby;
            }
            return "{$wpdb->posts}.post_date_gmt ASC, {$wpdb->posts}.ID ASC";
        };

        add_filter( 'wp-job-board-pro-job_listing-query-args', $args_filter, PHP_INT_MAX, 2 );
        add_filter( 'posts_where', $where_filter, PHP_INT_MAX, 2 );
        add_filter( 'posts_orderby', $orderby_filter, PHP_INT_MAX, 2 );

        try {
            $result = WP_Job_Board_Pro_Query::get_posts(
                array(
                    'post_type'     => 'job_listing',
                    'post_status'   => 'publish',
                    'post_per_page' => self::PAGE_SIZE,
                    'paged'         => $page,
                    'fields'        => 'ids',
                    'view_user_id'  => $owner_id,
                ),
                $query
            );
            $ids = $result instanceof WP_Query ? array_values( array_map( 'absint', $result->posts ) ) : array();
        } catch ( Throwable $throwable ) {
            $ids = null;
        } finally {
            remove_filter( 'wp-job-board-pro-job_listing-query-args', $args_filter, PHP_INT_MAX );
            remove_filter( 'posts_where', $where_filter, PHP_INT_MAX );
            remove_filter( 'posts_orderby', $orderby_filter, PHP_INT_MAX );
        }

        return $ids;
    }

    private static function is_active_job( $job_id ) {
        if ( 'job_listing' !== get_post_type( $job_id ) || 'publish' !== get_post_status( $job_id ) ) {
            return false;
        }

        if (
            class_exists( 'WP_Job_Board_Pro_Job_Listing' )
            && method_exists( 'WP_Job_Board_Pro_Job_Listing', 'is_filled' )
            && WP_Job_Board_Pro_Job_Listing::is_filled( $job_id )
        ) {
            return false;
        }

        $expiry = get_post_meta( $job_id, '_job_expiry_date', true );
        if ( '' === $expiry ) {
            return true;
        }
        if ( ! is_string( $expiry ) || 1 !== preg_match( '/^\d{4}-\d{2}-\d{2}$/D', $expiry ) ) {
            return false;
        }

        return $expiry >= wp_date( 'Y-m-d', null, wp_timezone() );
    }
}

/**
 * Render the existing WPJBP alert UX and hand it to the already-scoped channel.
 */
final class Raspitajse_Communications_Candidate_Job_Alert_Mailer {

    /**
     * @return bool|WP_Error
     */
    public static function send( $context ) {
        if (
            ! is_array( $context )
            || empty( $context['alert_id'] )
            || empty( $context['job_ids'] )
            || Raspitajse_Communications_Sender_Policy::CHANNEL_CANDIDATE_ALERTS !== ( $context['channel'] ?? '' )
            || ! class_exists( 'WP_Job_Board_Pro_Email' )
        ) {
            return self::error( 'invalid_mail_context' );
        }

        $alert_id = absint( $context['alert_id'] );
        $alert    = get_post( $alert_id );
        $job_ids  = array_values( array_unique( array_map( 'absint', (array) $context['job_ids'] ) ) );
        if (
            ! $alert
            || 'job_alert' !== $alert->post_type
            || 'publish' !== $alert->post_status
            || ! $job_ids
            || count( $job_ids ) > Raspitajse_Communications_Candidate_Job_Alert_Delivery_Service::MAX_RESULTS
        ) {
            return self::error( 'invalid_mail_aggregate' );
        }

        $owner = get_userdata( (int) $alert->post_author );
        if ( ! $owner || ! is_email( $owner->user_email ) ) {
            return self::error( 'invalid_mail_recipient' );
        }

        $query = get_post_meta( $alert_id, '_job_alert_alert_query', true );
        $query = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::normalize_saved_query( $query );
        if ( is_wp_error( $query ) ) {
            return self::error( 'invalid_mail_query' );
        }

        $jobs_url = WP_Job_Board_Pro_Mixes::get_jobs_page_url();
        foreach ( $query as $key => $value ) {
            if ( is_array( $value ) ) {
                foreach ( $value as $item ) {
                    $jobs_url = add_query_arg( $key . '[]', $item, $jobs_url );
                }
            } else {
                $jobs_url = add_query_arg( $key, $value, $jobs_url );
            }
        }

        $rows = array();
        foreach ( $job_ids as $job_id ) {
            if ( 'job_listing' !== get_post_type( $job_id ) || 'publish' !== get_post_status( $job_id ) ) {
                return self::error( 'invalid_mail_job' );
            }
            $rows[] = self::job_row( $job_id, get_the_title( $alert_id ), $jobs_url );
        }

        $newest = end( $rows );
        $older  = array_slice( $rows, 0, -1 );
        $entry_template = (string) get_option( 'job_entry_template', '' );
        if ( $older && '' === trim( $entry_template ) ) {
            return self::error( 'missing_job_entry_template' );
        }

        $job_content = '';
        foreach ( $older as $row ) {
            $entry = self::render_entry( $entry_template, $row );
            if ( is_wp_error( $entry ) ) {
                return $entry;
            }
            $job_content .= $entry . "\n";
        }

        $frequency = get_post_meta( $alert_id, '_job_alert_email_frequency', true );
        $frequency_label = (string) $frequency;
        if ( class_exists( 'WP_Job_Board_Pro_Job_Alert' ) ) {
            $frequencies = WP_Job_Board_Pro_Job_Alert::get_email_frequency();
            if ( isset( $frequencies[ $frequency ]['label'] ) ) {
                $frequency_label = (string) $frequencies[ $frequency ]['label'];
            }
        }

        $subject = WP_Job_Board_Pro_Email::render_email_vars(
            array(
                'alert_title' => esc_html( get_the_title( $alert_id ) ),
                'location'    => $newest['location'],
                'job_title'   => $newest['job_title'],
            ),
            'job_alert_notice',
            'subject'
        );
        $content = WP_Job_Board_Pro_Email::render_email_vars(
            apply_filters(
                'wp-job-board-pro-job-alert-email-content-args',
                array(
                    'job_data'                    => $job_content,
                    'alert_title'                 => esc_html( get_the_title( $alert_id ) ),
                    'email_frequency_type'        => esc_html( $frequency_label ),
                    'jobs_alert_url'              => esc_url( $jobs_url ),
                    'newest_employer_name'        => $newest['employer_name'],
                    'newest_job_title'            => $newest['job_title'],
                    'newest_job_url'              => $newest['job_url'],
                    'newest_job_publish_date'     => $newest['job_publish_date'],
                    'newest_job_expiry_date'      => $newest['job_expiry_date'],
                    'newest_job_apply_email'      => $newest['job_apply_email'],
                    'newest_location'             => $newest['location'],
                    'newest_salary'               => $newest['salary'],
                    'newest_alert_title'          => $newest['alert_title'],
                )
            ),
            'job_alert_notice',
            'content'
        );

        if (
            '' === trim( (string) $subject )
            || '' === trim( (string) $content )
            || 1 === preg_match( '/\{\{[a-z0-9_]+\}\}/i', $subject . $content )
        ) {
            return self::error( 'invalid_rendered_mail' );
        }
        foreach ( $rows as $row ) {
            if ( false === strpos( $content, $row['job_title'] ) ) {
                return self::error( 'incomplete_rendered_mail' );
            }
        }

        return WP_Job_Board_Pro_Email::wp_mail(
            $owner->user_email,
            $subject,
            $content,
            "Content-Type: text/html; charset=UTF-8\r\n"
        );
    }

    private static function job_row( $job_id, $alert_title, $jobs_url ) {
        $employer_id = absint( get_post_meta( $job_id, '_job_employer_posted_by', true ) );
        return array(
            'job_id'               => $job_id,
            'job_title'            => esc_html( get_the_title( $job_id ) ),
            'job_url'              => esc_url( get_permalink( $job_id ) ),
            'job_publish_date'     => esc_html( get_the_date( 'Y-m-d', $job_id ) ),
            'job_expiry_date'      => esc_html( get_post_meta( $job_id, '_job_expiry_date', true ) ),
            'job_apply_email'      => esc_html( get_post_meta( $job_id, '_job_apply_email', true ) ),
            'location'             => esc_html( get_post_meta( $job_id, '_job_address', true ) ),
            'salary'               => esc_html( get_post_meta( $job_id, '_job_salary', true ) ),
            'employer_name'        => esc_html( $employer_id ? get_the_title( $employer_id ) : '' ),
            'alert_title'          => esc_html( $alert_title ),
            'jobs_alert_url'       => esc_url( $jobs_url ),
        );
    }

    private static function render_entry( $template, $row ) {
        $replace = array(
            '{{job_title}}'        => $row['job_title'],
            '{{job_url}}'          => $row['job_url'],
            '{{employer_name}}'    => $row['employer_name'],
            '{{location}}'         => $row['location'],
            '{{job_publish_date}}' => $row['job_publish_date'],
            '{{job_expiry_date}}'  => $row['job_expiry_date'],
            '{{job_apply_email}}'  => $row['job_apply_email'],
            '{{salary}}'           => $row['salary'],
        );
        $entry = str_replace( array_keys( $replace ), array_values( $replace ), $template );
        if ( 1 === preg_match( '/\{\{[a-z0-9_]+\}\}/i', $entry ) ) {
            return self::error( 'unresolved_job_entry_placeholder' );
        }
        return $entry;
    }

    private static function error( $code ) {
        return new WP_Error(
            'raspitajse_cja_' . sanitize_key( $code ),
            'Candidate job alert mail adapter rejected the operation.'
        );
    }
}

/**
 * Bounded hourly evaluator and active daily-hook cutover.
 */
final class Raspitajse_Communications_Candidate_Job_Alert_Evaluator {

    const HOOK              = 'raspitajse_candidate_job_alert_evaluator';
    const CONTINUATION_HOOK = 'raspitajse_candidate_job_alert_evaluator_continue';
    const BATCH_SIZE        = 20;
    const CONTINUATION_DELAY = 300;
    private const LEGACY_DAILY_HOOK     = 'wp_job_board_pro_email_daily_notices';
    private const LEGACY_DAILY_PRIORITY = 10;

    public static function boot() {
        add_action( 'plugins_loaded', array( __CLASS__, 'cutover_daily_hook' ), 101 );
        add_action( 'init', array( __CLASS__, 'ensure_schedule' ), 20 );
        add_action( self::HOOK, array( __CLASS__, 'run' ), 10, 2 );
        add_action( self::CONTINUATION_HOOK, array( __CLASS__, 'run' ), 10, 2 );
    }

    public static function activate() {
        self::ensure_schedule();
    }

    public static function deactivate() {
        wp_clear_scheduled_hook( self::HOOK );
        wp_clear_scheduled_hook( self::CONTINUATION_HOOK );
    }

    public static function cutover_daily_hook() {
        remove_action(
            self::LEGACY_DAILY_HOOK,
            array( 'WP_Job_Board_Pro_Job_Alert', 'send_job_alert_notice' ),
            self::LEGACY_DAILY_PRIORITY
        );
    }

    public static function ensure_schedule() {
        if ( ! self::has_scheduled_event( self::HOOK ) ) {
            wp_schedule_event( time() + self::CONTINUATION_DELAY, 'hourly', self::HOOK );
        }
    }

    /**
     * Cron callback. Public return is used only by bounded direct fixtures.
     */
    public static function run( $cursor_due = '', $cursor_id = 0 ) {
        return self::evaluate(
            array(
                'cursor_due' => is_string( $cursor_due ) ? $cursor_due : '',
                'cursor_id'  => absint( $cursor_id ),
            )
        );
    }

    /**
     * Evaluate due alerts with keyset continuation and per-alert isolation.
     *
     * @return array
     */
    public static function evaluate( $options = array() ) {
        $options = is_array( $options ) ? $options : array();
        $now_gmt = isset( $options['now_gmt'] )
            ? (string) $options['now_gmt']
            : Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::now_gmt();
        if ( is_wp_error( Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt( $now_gmt ) ) ) {
            return array( 'processed' => 0, 'errors' => 1, 'continued' => false, 'has_more' => false, 'status' => 'invalid_now' );
        }

        $cursor_due = isset( $options['cursor_due'] ) && is_string( $options['cursor_due'] )
            ? $options['cursor_due']
            : '';
        $cursor_id = isset( $options['cursor_id'] ) ? absint( $options['cursor_id'] ) : 0;
        $started   = microtime( true );
        $budget    = self::runtime_budget();
        $processed = 0;
        $errors    = 0;
        $outcomes  = array();
        $has_more  = false;

        while ( $processed < self::BATCH_SIZE && ( microtime( true ) - $started ) < $budget ) {
            $rows = self::due_rows( $now_gmt, $cursor_due, $cursor_id, self::BATCH_SIZE + 1 );
            if ( ! $rows ) {
                break;
            }

            foreach ( $rows as $row ) {
                if ( $processed >= self::BATCH_SIZE || ( microtime( true ) - $started ) >= $budget ) {
                    $has_more = true;
                    break 2;
                }

                $cursor_due = $row['due_sort'];
                $cursor_id  = $row['alert_id'];
                if ( ! self::is_exactly_due( $cursor_id, $now_gmt ) ) {
                    continue;
                }

                try {
                    $result = Raspitajse_Communications_Candidate_Job_Alert_Delivery_Service::process(
                        $cursor_id,
                        array( 'Raspitajse_Communications_Candidate_Job_Alert_Query_Adapter', 'eligible_jobs' ),
                        array( 'Raspitajse_Communications_Candidate_Job_Alert_Mailer', 'send' ),
                        array( 'now_gmt' => $now_gmt )
                    );
                } catch ( Throwable $throwable ) {
                    $result = new WP_Error( 'raspitajse_cja_evaluator_exception', 'Candidate alert evaluator isolated an exception.' );
                }

                $processed++;
                if ( is_wp_error( $result ) ) {
                    $errors++;
                    $outcomes[] = array( 'alert_id' => $cursor_id, 'outcome' => sanitize_key( $result->get_error_code() ) );
                } else {
                    $status = isset( $result['status'] ) ? sanitize_key( $result['status'] ) : 'unknown';
                    $outcomes[] = array( 'alert_id' => $cursor_id, 'outcome' => $status );
                }
            }

            if ( count( $rows ) <= self::BATCH_SIZE ) {
                break;
            }
        }

        if ( ! $has_more ) {
            $has_more = (bool) self::due_rows( $now_gmt, $cursor_due, $cursor_id, 1 );
        }
        $runner_context = defined( 'RASPITAJSE_STAGING_SELECTIVE_RUNNER' ) && true === RASPITAJSE_STAGING_SELECTIVE_RUNNER;
        $continued      = $has_more && ! $runner_context ? self::schedule_continuation( $cursor_due, $cursor_id ) : false;

        $summary = array(
            'processed' => $processed,
            'errors'    => $errors,
            'continued' => $continued,
            'has_more'  => $has_more,
            'status'    => 'complete',
            'outcomes'  => $outcomes,
        );
        do_action( 'raspitajse_candidate_job_alert_evaluator_observation', $summary );
        return $summary;
    }

    private static function due_rows( $now_gmt, $cursor_due, $cursor_id, $limit ) {
        global $wpdb;

        $meta_key = Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::META_NEXT_DUE;
        $empty_due = '0000-00-00 00:00:00';
        $cursor_due = $cursor_due ?: $empty_due;
        $limit = max( 1, min( self::BATCH_SIZE + 1, absint( $limit ) ) );
        $sql = $wpdb->prepare(
            "SELECT p.ID AS alert_id, COALESCE(NULLIF(d.meta_value, ''), %s) AS due_sort
             FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} d ON d.post_id = p.ID AND d.meta_key = %s
             WHERE p.post_type = 'job_alert'
               AND p.post_status = 'publish'
               AND (d.meta_value IS NULL OR d.meta_value = '' OR d.meta_value <= %s)
               AND (
                    COALESCE(NULLIF(d.meta_value, ''), %s) > %s
                    OR (COALESCE(NULLIF(d.meta_value, ''), %s) = %s AND p.ID > %d)
               )
             ORDER BY due_sort ASC, p.ID ASC
             LIMIT %d",
            $empty_due,
            $meta_key,
            $now_gmt,
            $empty_due,
            $cursor_due,
            $empty_due,
            $cursor_due,
            $cursor_id,
            $limit
        );
        $rows = $wpdb->get_results( $sql, ARRAY_A );
        return array_map(
            static function ( $row ) {
                return array( 'alert_id' => (int) $row['alert_id'], 'due_sort' => (string) $row['due_sort'] );
            },
            is_array( $rows ) ? $rows : array()
        );
    }

    private static function is_exactly_due( $alert_id, $now_gmt ) {
        $state = Raspitajse_Communications_Candidate_Job_Alert_Delivery_Store::read_state( $alert_id );
        if ( is_wp_error( $state ) || null === $state ) {
            return true;
        }
        if (
            is_array( $state )
            && Raspitajse_Communications_Candidate_Job_Alert_Delivery_Service::OUTCOME_RETRYABLE === ( $state['outcome'] ?? '' )
        ) {
            $retry = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt(
                $state['next_retry_gmt'] ?? ''
            );
            $now = Raspitajse_Communications_Candidate_Job_Alert_Schedule_Policy::parse_gmt( $now_gmt );
            return ! is_wp_error( $retry ) && ! is_wp_error( $now ) && $now >= $retry;
        }
        return true;
    }

    private static function runtime_budget() {
        $maximum = (int) ini_get( 'max_execution_time' );
        if ( $maximum <= 0 ) {
            return 20.0;
        }
        return (float) max( 1, min( 20, floor( $maximum * 0.5 ) ) );
    }

    private static function schedule_continuation( $cursor_due, $cursor_id ) {
        if ( self::has_scheduled_event( self::CONTINUATION_HOOK ) ) {
            return false;
        }
        $scheduled = wp_schedule_single_event(
            time() + self::CONTINUATION_DELAY,
            self::CONTINUATION_HOOK,
            array( (string) $cursor_due, (int) $cursor_id ),
            true
        );
        return true === $scheduled;
    }

    private static function has_scheduled_event( $hook ) {
        $cron = _get_cron_array();
        foreach ( is_array( $cron ) ? $cron : array() as $hooks ) {
            if ( ! empty( $hooks[ $hook ] ) ) {
                return true;
            }
        }
        return false;
    }
}
