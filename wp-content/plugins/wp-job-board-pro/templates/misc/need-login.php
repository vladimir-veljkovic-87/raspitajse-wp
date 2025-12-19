<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_id = wp_job_board_pro_get_option('login_register_page_id');
$page_url = get_permalink($page_id);
?>

<div class="alert alert-warning not-allow-wrapper">
	<p class="account-sign-in"><?php esc_html_e( 'You need to be signed in to access this page.', 'wp-job-board-pro' ); ?> <a class="button" href="<?php echo esc_url( $page_url ); ?>"><?php esc_html_e( 'Sign in', 'wp-job-board-pro' ); ?></a></p>
</div><!-- /.alert -->
