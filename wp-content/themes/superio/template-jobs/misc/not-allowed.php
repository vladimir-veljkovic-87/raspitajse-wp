<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="box-dashboard-wrapper">
	<div class="alert alert-warning not-allow-wrapper">
		<?php
		if ( empty($need_role) ) {
			echo esc_html__( 'You are not allowed to access this page.', 'superio' );
		} else {
			switch ($need_role) {
				case 'employer':
					$need_role = esc_html__( 'employer', 'superio' );
					break;
				default:
					$need_role = esc_html__( 'candidate', 'superio' );
					break;
			}
			echo sprintf(esc_html__( 'You need to login with %s account to access this page.', 'superio' ), $need_role);
		}

		?>
	</div><!-- /.alert -->
</div>