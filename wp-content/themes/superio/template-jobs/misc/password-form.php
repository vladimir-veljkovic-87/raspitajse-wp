<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'dashicons' );
?>
<div class="box-dashboard-wrapper">
	<h3 class="widget-title"><?php echo esc_html__('Change Password','superio') ?></h3>
	<div class="inner-list">
		<form method="post" action="" class="change-password-form">
			<div class="form-group">
				<label for="change-password-form-old-password"><?php echo esc_html__( 'Old password', 'superio' ); ?></label>
				<span class="show_hide_password">
					<input id="change-password-form-old-password" class="form-control" type="password" name="old_password" required="required">
					<a class="toggle-password" title="<?php esc_attr_e('Show', 'superio'); ?>"><span class="dashicons dashicons-hidden"></span></a>
				</span>
			</div><!-- /.form-control -->

			<div class="form-group">
				<label for="change-password-form-new-password"><?php echo esc_html__( 'New password', 'superio' ); ?></label>
				<span class="show_hide_password">
					<input id="change-password-form-new-password" class="form-control" type="password" name="new_password" required="required" minlength="8">
					<a class="toggle-password" title="<?php esc_attr_e('Show', 'superio'); ?>"><span class="dashicons dashicons-hidden"></span></a>
				</span>
			</div><!-- /.form-control -->

			<div class="form-group">
				<label for="change-password-form-retype-password"><?php echo esc_html__( 'Retype password', 'superio' ); ?></label>
				<span class="show_hide_password">
					<input id="change-password-form-retype-password" class="form-control" type="password" name="retype_password" required="required" minlength="8">
					<a class="toggle-password" title="<?php esc_attr_e('Show', 'superio'); ?>"><span class="dashicons dashicons-hidden"></span></a>
				</span>
			</div><!-- /.form-control -->

			<button type="submit" name="change_password_form" class="button btn btn-theme"><?php echo esc_html__( 'Change Password', 'superio' ); ?></button>
		</form>
	</div>
</div>