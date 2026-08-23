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
 * Prevent normal request shutdown from broadly dispatching the mixed-risk
 * Action Scheduler backlog while exact-ID recovery is active.
 */
function raspitajse_staging_disable_action_scheduler_async_dispatch() {
    if ( ! raspitajse_staging_cron_recovery_guard_is_active() ) {
        return;
    }

    global $wp_filter;

    if ( empty( $wp_filter['shutdown'] ) ) {
        return;
    }

    foreach ( $wp_filter['shutdown']->callbacks as $priority => $callbacks ) {
        foreach ( $callbacks as $entry ) {
            $callback = $entry['function'];
            if (
                is_array( $callback )
                && is_object( $callback[0] )
                && $callback[0] instanceof ActionScheduler_QueueRunner
                && 'maybe_dispatch_async_request' === $callback[1]
            ) {
                remove_action( 'shutdown', $callback, $priority );
            }
        }
    }
}
add_action( 'plugins_loaded', 'raspitajse_staging_disable_action_scheduler_async_dispatch', PHP_INT_MAX );
add_action( 'init', 'raspitajse_staging_disable_action_scheduler_async_dispatch', PHP_INT_MIN );
