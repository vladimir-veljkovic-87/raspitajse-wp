<?php
/**
 * Plugin Name: Raspitajse Staging Action Scheduler DROP Guard
 * Description: Prevents six explicitly classified staging-only DROP actions from being scheduled again.
 * Version: 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * The guard is activated only after the exact-ID remediation transaction.
 */
function raspitajse_staging_as_drop_guard_is_active() {
    return function_exists( 'wp_get_environment_type' )
        && 'staging' === wp_get_environment_type()
        && '1' === (string) get_option( 'raspitajse_staging_as_drop_guard_enabled', '0' );
}

/**
 * Return the only Action Scheduler hook/group pairs classified as DROP.
 */
function raspitajse_staging_as_drop_guard_targets() {
    return [
        'wpforms_process_forms_locator_scan' => 'wpforms',
        'aioseo_addons_refresh'              => 'aioseo',
        'aioseo_features_refresh'            => 'aioseo',
        'aioseo_admin_notifications_update'  => 'aioseo',
        'wpforms_send_usage_data'            => 'wpforms',
        'wpforms_analytics_aggregate'         => 'wpforms',
    ];
}

/**
 * Match a scheduling attempt only when both its hook and group are approved.
 */
function raspitajse_staging_as_drop_guard_should_block( $hook, $group ) {
    if ( ! raspitajse_staging_as_drop_guard_is_active() ) {
        return false;
    }

    $targets = raspitajse_staging_as_drop_guard_targets();

    return isset( $targets[ $hook ] ) && $targets[ $hook ] === $group;
}

function raspitajse_staging_as_drop_guard_pre_async( $pre, $hook, $args, $group, $priority, $unique ) {
    return raspitajse_staging_as_drop_guard_should_block( $hook, $group ) ? 0 : $pre;
}
add_filter( 'pre_as_enqueue_async_action', 'raspitajse_staging_as_drop_guard_pre_async', PHP_INT_MAX, 6 );

function raspitajse_staging_as_drop_guard_pre_single( $pre, $timestamp, $hook, $args, $group, $priority ) {
    return raspitajse_staging_as_drop_guard_should_block( $hook, $group ) ? 0 : $pre;
}
add_filter( 'pre_as_schedule_single_action', 'raspitajse_staging_as_drop_guard_pre_single', PHP_INT_MAX, 6 );

function raspitajse_staging_as_drop_guard_pre_recurring( $pre, $timestamp, $interval, $hook, $args, $group, $priority ) {
    return raspitajse_staging_as_drop_guard_should_block( $hook, $group ) ? 0 : $pre;
}
add_filter( 'pre_as_schedule_recurring_action', 'raspitajse_staging_as_drop_guard_pre_recurring', PHP_INT_MAX, 7 );

function raspitajse_staging_as_drop_guard_pre_cron( $pre, $timestamp, $schedule, $hook, $args, $group, $priority ) {
    return raspitajse_staging_as_drop_guard_should_block( $hook, $group ) ? 0 : $pre;
}
add_filter( 'pre_as_schedule_cron_action', 'raspitajse_staging_as_drop_guard_pre_cron', PHP_INT_MAX, 7 );

/**
 * Stop WPForms at its scheduling sources to avoid orphan task-meta rows.
 */
function raspitajse_staging_as_drop_guard_zero_interval( $interval ) {
    return raspitajse_staging_as_drop_guard_is_active() ? 0 : $interval;
}
add_filter( 'wpforms_tasks_actions_forms_locator_scan_task_interval', 'raspitajse_staging_as_drop_guard_zero_interval', PHP_INT_MAX );
add_filter( 'wpforms_tasks_actions_analytics_aggregation_task_init_interval', 'raspitajse_staging_as_drop_guard_zero_interval', PHP_INT_MAX );

function raspitajse_staging_as_drop_guard_disable_usage_tracking( $enabled ) {
    return raspitajse_staging_as_drop_guard_is_active() ? false : $enabled;
}
add_filter( 'wpforms_usagetracking_is_allowed', 'raspitajse_staging_as_drop_guard_disable_usage_tracking', PHP_INT_MAX );
add_filter( 'wpforms_integrations_usagetracking_is_enabled', 'raspitajse_staging_as_drop_guard_disable_usage_tracking', PHP_INT_MAX );
