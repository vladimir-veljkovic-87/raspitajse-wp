<?php
// Process-order self-test. Uses fake values only.
if ( 2 !== $argc || 0 !== strpos( $argv[1], '/tmp/raspitajse-' ) ) {
	exit( 2 );
}
$result_file = $argv[1];
require dirname( __DIR__ ) . '/codex-wp-option-shutdown-rollback.php';
$original = 'fake-original-token';
$state = $original;
file_put_contents( $result_file, json_encode( [
	'before' => $state,
	'temporary_change' => false,
	'shutdown_hooks_ran' => false,
	'final_rollback' => false,
] ) );

// Simulates AIOSEO's shutdown save; it is registered before the rollback.
register_shutdown_function( static function () use ( &$state, $result_file ) {
	$state = 'fake-temporary-cleared-token';
	file_put_contents( $result_file, json_encode( [
		'before' => 'fake-original-token',
		'temporary_change' => 'fake-temporary-cleared-token' === $state,
		'shutdown_hooks_ran' => true,
		'final_rollback' => false,
	] ) );
} );

Raspitajse_Codex_Shutdown_Rollback::arm( 'fake-option', static function () use ( &$state, $original ) {
	$state = $original;
} );

// Registered last, so it verifies state after the rollback dispatcher.
register_shutdown_function( static function () use ( &$state, $original, $result_file ) {
	$result = json_decode( file_get_contents( $result_file ), true );
	$result['final_rollback'] = hash_equals( $original, $state );
	$result['after'] = $state;
	$result['pass'] = $result['temporary_change'] && $result['shutdown_hooks_ran'] && $result['final_rollback'];
	file_put_contents( $result_file, json_encode( $result ) );
} );
