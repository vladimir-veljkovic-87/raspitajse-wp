<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="alert alert-warning not-allow-wrapper">
	<?php
	if ( empty($need_role) ) {
		echo __( 'You are not allowed to access this page.', 'wp-job-board-pro' );
	} else {
		switch ($need_role) {
			case 'employer':
				$need_role = __( 'employer', 'wp-job-board-pro' );
				break;
			default:
				$need_role = __( 'candidate', 'wp-job-board-pro' );
				break;
		}
		echo sprintf(__( 'You need login with %s account to access this page.', 'wp-job-board-pro' ), $need_role);
	}

	?>
</div><!-- /.alert -->
