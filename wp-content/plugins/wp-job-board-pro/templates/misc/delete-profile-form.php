<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty($user_id) ) {
	return;
}
?>

<div class="conf-messages"><?php esc_html_e('Are you sure! You want to delete your profile.', 'wp-job-board-pro'); ?></div>
<div class="undone-messages"><?php esc_html_e('This can\'t be undone!', 'wp-job-board-pro'); ?></div>

<form method="post" action="" class="delete-profile-form">

	<div class="form-group">
		<label for="delete-profile-password"><?php esc_html_e( 'Please enter your login Password to confirm:', 'wp-job-board-pro' ); ?></label>
		<input id="delete-profile-password" class="form-control" type="password" name="password" required="required">
	</div><!-- /.form-control -->

	<?php
		do_action('wp-job-board-pro-delete-profile-form-fields');
		wp_nonce_field('wp-job-board-pro-delete-profile-nonce', 'nonce');
	?>

	<button type="submit" class="btn btn-danger delete-profile-btn"><?php esc_html_e( 'Delete Profile', 'wp-job-board-pro' ); ?></button>
</form>
