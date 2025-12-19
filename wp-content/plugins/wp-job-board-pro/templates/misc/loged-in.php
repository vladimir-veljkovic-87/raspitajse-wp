<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>


<div class="logged-in-wrapper">
	<?php
		$user = wp_get_current_user();
		printf( wp_kses_post( __( 'You are currently signed in as <strong>%s</strong>.', 'wp-job-board-pro' ) ), esc_html( $user->user_login ) );
	?>

	<a class="button" href="<?php echo esc_url( wp_logout_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Sign out', 'wp-job-board-pro' ); ?></a>
</div>