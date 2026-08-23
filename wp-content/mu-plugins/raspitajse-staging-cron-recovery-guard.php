<?php
/**
 * Plugin Name: Raspitajse Staging Cron Recovery Guard
 * Description: Temporarily prevents traffic-triggered WP-Cron spawning on staging while the overdue cron backlog is recovered manually.
 * Version: 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if (
    function_exists( 'wp_get_environment_type' )
    && 'staging' === wp_get_environment_type()
    && ! defined( 'DISABLE_WP_CRON' )
) {
    define( 'DISABLE_WP_CRON', true );
}

/**
 * Exposes a narrow runtime check for staging recovery validation.
 *
 * This guard prevents normal traffic-triggered spawning. It does not execute,
 * delete, reschedule, or otherwise mutate any cron event by itself.
 */
function raspitajse_staging_cron_recovery_guard_is_active() {
    return function_exists( 'wp_get_environment_type' )
        && 'staging' === wp_get_environment_type()
        && defined( 'DISABLE_WP_CRON' )
        && true === DISABLE_WP_CRON;
}
/**
 * AIOSEO AI is intentionally disabled/DROP on staging.
 *
 * Normal AIOSEO SEO, metadata, sitemap and head services remain loaded. Only
 * AI authentication, credit scheduling, AI REST routes and AI transports are
 * disabled.
 */
function raspitajse_staging_aioseo_ai_is_disabled() {
    return function_exists( 'wp_get_environment_type' )
        && 'staging' === wp_get_environment_type();
}

/**
 * Remove only the AIOSEO AI lifecycle callbacks registered by its AI service.
 */
function raspitajse_staging_disable_aioseo_ai_callbacks() {
    if ( ! raspitajse_staging_aioseo_ai_is_disabled() ) {
        return;
    }

    global $wp_filter;

    $targets = [
        'init'                     => [ 'getAccessToken', 'scheduleCreditFetchAction' ],
        'aioseo_ai_update_credits' => [ 'updateCredits' ],
    ];

    foreach ( $targets as $hook => $methods ) {
        if ( empty( $wp_filter[ $hook ] ) ) {
            continue;
        }

        foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
            foreach ( $callbacks as $entry ) {
                $callback = $entry['function'];
                if (
                    is_array( $callback )
                    && is_object( $callback[0] )
                    && 'AIOSEO\\Plugin\\Common\\Ai\\Ai' === get_class( $callback[0] )
                    && in_array( $callback[1], $methods, true )
                ) {
                    remove_action( $hook, $callback, $priority );
                }
            }
        }
    }
}
add_action( 'plugins_loaded', 'raspitajse_staging_disable_aioseo_ai_callbacks', PHP_INT_MAX );
add_action( 'init', 'raspitajse_staging_disable_aioseo_ai_callbacks', PHP_INT_MIN );

/**
 * Remove only AIOSEO's AI REST routes; preserve all normal AIOSEO endpoints.
 */
function raspitajse_staging_disable_aioseo_ai_rest_routes( $endpoints ) {
    if ( ! raspitajse_staging_aioseo_ai_is_disabled() ) {
        return $endpoints;
    }

    foreach ( array_keys( $endpoints ) as $route ) {
        if ( 0 === strpos( $route, '/aioseo/v1/ai/' ) ) {
            unset( $endpoints[ $route ] );
        }
    }

    return $endpoints;
}
add_filter( 'rest_endpoints', 'raspitajse_staging_disable_aioseo_ai_rest_routes', PHP_INT_MAX );

/**
 * Fail closed before transport for AIOSEO AI licensing/generator requests.
 */
function raspitajse_staging_block_aioseo_ai_http( $preempt, $args, $url ) {
    if ( ! raspitajse_staging_aioseo_ai_is_disabled() ) {
        return $preempt;
    }

    foreach ( (array) ( $args['headers'] ?? [] ) as $name => $value ) {
        if ( 'x-aioseo-ai-token' === strtolower( (string) $name ) ) {
            return new WP_Error( 'raspitajse_staging_aioseo_ai_disabled', 'AIOSEO AI is disabled on staging.' );
        }
    }

    $parts = wp_parse_url( $url );
    $host  = strtolower( (string) ( $parts['host'] ?? '' ) );
    $path  = (string) ( $parts['path'] ?? '' );

    if (
        ( 'licensing.aioseo.com' === $host && 0 === strpos( $path, '/v1/ai/' ) )
        || ( 'ai-generator.aioseo.com' === $host && 0 === strpos( $path, '/v1/' ) )
    ) {
        return new WP_Error( 'raspitajse_staging_aioseo_ai_disabled', 'AIOSEO AI is disabled on staging.' );
    }

    return $preempt;
}
add_filter( 'pre_http_request', 'raspitajse_staging_block_aioseo_ai_http', PHP_INT_MIN, 3 );
/**
 * Staging report-email and telemetry behavior classified as DROP.
 *
 * The list is deliberately narrow and does not affect normal SEO, form
 * processing, analytics collection, commerce, or any other scheduler hook.
 */
function raspitajse_staging_reporting_telemetry_is_disabled() {
    return function_exists( 'wp_get_environment_type' )
        && 'staging' === wp_get_environment_type();
}

function raspitajse_staging_disable_aioseo_report_summary( $enabled ) {
    return raspitajse_staging_reporting_telemetry_is_disabled() ? false : $enabled;
}
add_filter( 'aioseo_report_summary_enable', 'raspitajse_staging_disable_aioseo_report_summary', PHP_INT_MIN );

function raspitajse_staging_disable_wpforms_email_summaries( $disabled ) {
    return raspitajse_staging_reporting_telemetry_is_disabled() ? true : $disabled;
}
add_filter( 'wpforms_emails_summaries_is_disabled', 'raspitajse_staging_disable_wpforms_email_summaries', PHP_INT_MIN );

/**
 * Remove only proven DROP callbacks and their exact WP-Cron schedules.
 */
function raspitajse_staging_disable_reporting_telemetry_callbacks() {
    if ( ! raspitajse_staging_reporting_telemetry_is_disabled() ) {
        return;
    }

    global $wp_filter;

    $targets = [
        'admin_init' => [
            'AIOSEO\\Plugin\\Common\\EmailReports\\Summary\\Summary' => [ 'maybeSchedule' ],
            'WPForms\\Admin\\Addons\\AddonsCache'                    => [ 'schedule_update_cache' ],
            'WPForms\\Admin\\Builder\\HelpCache'                     => [ 'schedule_update_cache' ],
            'WPForms\\Admin\\Builder\\TemplatesCache'                => [ 'schedule_update_cache' ],
        ],
        'aioseo_report_summary' => [
            'AIOSEO\\Plugin\\Common\\EmailReports\\Summary\\Summary' => [ 'cronTrigger' ],
        ],
        'elementor/tracker/send_event' => [
            'Elementor\\Tracker' => [ 'send_tracking_data' ],
        ],
        'monsterinsights_feature_feedback_checkin' => [
            'MonsterInsights_Feature_Feedback' => [ 'feature_feedback_checkin' ],
        ],
        'wpforms_email_summaries_cron' => [
            'WPForms\\Lite\\Emails\\Summaries' => [ 'cron' ],
            'WPForms\\Emails\\Summaries' => [ 'cron' ],
        ],
        'wpforms_admin_addons_cache_update' => [
            'WPForms\\Admin\\Addons\\AddonsCache' => [ 'update' ],
        ],
        'wpforms_admin_builder_templates_cache_update' => [
            'WPForms\\Admin\\Builder\\TemplatesCache' => [ 'update' ],
        ],
        'wpforms_builder_help_cache_update' => [
            'WPForms\\Admin\\Builder\\HelpCache' => [ 'update' ],
        ],
        'wpforms_email_summaries_fetch_info_blocks' => [
            'WPForms\\Emails\\Tasks\\FetchInfoBlocksTask' => [ 'process' ],
        ],
    ];

    foreach ( $targets as $hook => $classes ) {
        if ( empty( $wp_filter[ $hook ] ) ) {
            continue;
        }

        foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
            foreach ( $callbacks as $entry ) {
                $callback = $entry['function'];
                if ( ! is_array( $callback ) || ! isset( $callback[0], $callback[1] ) ) {
                    continue;
                }

                $class = is_object( $callback[0] ) ? get_class( $callback[0] ) : (string) $callback[0];
                if ( isset( $classes[ $class ] ) && in_array( $callback[1], $classes[ $class ], true ) ) {
                    remove_action( $hook, $callback, $priority );
                }
            }
        }
    }

    foreach ( [
        'elementor/tracker/send_event',
        'monsterinsights_feature_feedback_checkin',
        'wpforms_email_summaries_cron',
    ] as $hook ) {
        if ( wp_next_scheduled( $hook ) ) {
            wp_clear_scheduled_hook( $hook );
        }
    }
}
add_action( 'plugins_loaded', 'raspitajse_staging_disable_reporting_telemetry_callbacks', PHP_INT_MAX );
add_action( 'init', 'raspitajse_staging_disable_reporting_telemetry_callbacks', PHP_INT_MAX );

/**
 * Disable the unsupported WPForms spam-purge task on staging.
 *
 * The loaded WPForms edition exposes no entry subsystem, so no supported
 * spam-entry fixture or purge operation exists in this environment.
 */
function raspitajse_staging_disable_wpforms_spam_purge_interval( $interval ) {
    return raspitajse_staging_reporting_telemetry_is_disabled() ? 0 : $interval;
}
add_filter( 'wpforms_tasks_actions_purge_spam_task_interval', 'raspitajse_staging_disable_wpforms_spam_purge_interval', PHP_INT_MIN );
