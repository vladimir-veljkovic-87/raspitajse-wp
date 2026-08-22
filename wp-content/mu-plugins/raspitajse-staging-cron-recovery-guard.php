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
