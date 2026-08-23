<?php
/**
 * Final-process rollback support for guarded WP-CLI tasks.
 *
 * Repository tooling only; not loaded by normal WordPress requests.
 */
if ( class_exists( 'Raspitajse_Codex_Shutdown_Rollback', false ) ) {
	return;
}

final class Raspitajse_Codex_Shutdown_Rollback {
	private static $restorers = [];
	private static $registered = false;

	public static function arm( $key, callable $restorer ) {
		if ( ! is_string( $key ) || '' === $key ) {
			throw new InvalidArgumentException( 'Rollback key must be non-empty.' );
		}
		self::$restorers[ $key ] = $restorer;
		if ( ! self::$registered ) {
			self::$registered = true;
			// Registered after WordPress boot, so this runs after shutdown_action_hook().
			register_shutdown_function( [ __CLASS__, 'restore' ] );
		}
	}

	public static function disarm( $key ) {
		unset( self::$restorers[ $key ] );
	}

	public static function restore() {
		foreach ( array_reverse( self::$restorers, true ) as $restorer ) {
			$restorer();
		}
		self::$restorers = [];
	}
}

/**
 * Snapshot one existing WP option into process memory and arm silent restoration.
 *
 * The value is never returned, printed, logged, or written to a task file.
 */
function raspitajse_codex_arm_wp_option_shutdown_rollback( $option_name ) {
	if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
		throw new RuntimeException( 'WP option rollback requires WP-CLI.' );
	}
	if ( ! is_string( $option_name ) || ! preg_match( '/^[A-Za-z0-9_.:-]+$/', $option_name ) ) {
		throw new InvalidArgumentException( 'Invalid option name.' );
	}
	$missing = new stdClass();
	$original = get_option( $option_name, $missing );
	if ( $missing === $original ) {
		throw new RuntimeException( 'Cannot arm rollback for a missing option.' );
	}
	$key = 'wp-option:' . $option_name;
	Raspitajse_Codex_Shutdown_Rollback::arm( $key, static function () use ( $option_name, $original ) {
		update_option( $option_name, $original );
		if ( function_exists( 'wp_cache_delete' ) ) {
			wp_cache_delete( $option_name, 'options' );
		}
	} );
	return $key;
}
