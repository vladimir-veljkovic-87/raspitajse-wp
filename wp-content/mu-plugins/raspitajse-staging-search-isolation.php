<?php
/**
 * Plugin Name: Raspitajse Staging Search Isolation
 * Description: Enforces deterministic search-engine isolation for staging responses.
 * Version: 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if (
	! function_exists( 'wp_get_environment_type' )
	|| 'staging' !== wp_get_environment_type()
) {
	return;
}

/**
 * Add a fail-closed robots header to public WordPress responses.
 *
 * @param array<string,string> $headers Response headers.
 * @return array<string,string>
 */
function raspitajse_staging_search_isolation_headers( $headers ) {
	$headers['X-Robots-Tag'] = 'noindex, nofollow';

	return $headers;
}
add_filter( 'wp_headers', 'raspitajse_staging_search_isolation_headers', PHP_INT_MAX );

/**
 * Force the WordPress core robots metadata contract.
 *
 * @param array<string,bool|string> $robots Robots directives.
 * @return array<string,bool|string>
 */
function raspitajse_staging_search_isolation_wp_robots( $robots ) {
	unset( $robots['index'], $robots['follow'] );
	$robots['noindex']  = true;
	$robots['nofollow'] = true;

	return $robots;
}
add_filter( 'wp_robots', 'raspitajse_staging_search_isolation_wp_robots', PHP_INT_MAX );

/**
 * Preserve the same contract when AIOSEO owns the rendered robots tag.
 *
 * @param array<string,mixed> $robots Robots directives.
 * @return array<string,mixed>
 */
function raspitajse_staging_search_isolation_aioseo_robots( $robots ) {
	if ( ! is_array( $robots ) ) {
		$robots = array();
	}

	unset( $robots['index'], $robots['follow'] );
	$robots['noindex']  = 'noindex';
	$robots['nofollow'] = 'nofollow';

	return $robots;
}
add_filter( 'aioseo_robots_meta', 'raspitajse_staging_search_isolation_aioseo_robots', PHP_INT_MAX );

/**
 * Replace the dynamic robots response with a global crawl denial.
 *
 * @param string $output Existing robots output.
 * @param bool   $public Whether WordPress is configured as public.
 * @return string
 */
function raspitajse_staging_search_isolation_robots_txt( $output, $public ) {
	unset( $output, $public );

	return "User-agent: *\nDisallow: /\n";
}
add_filter( 'robots_txt', 'raspitajse_staging_search_isolation_robots_txt', PHP_INT_MAX, 2 );
