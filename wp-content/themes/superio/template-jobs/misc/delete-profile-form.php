<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty($user_id) ) {
	return;
}
?>
<div class="box-dashboard-wrapper">
	<h3 class="widget-title"><?php echo esc_html__('Obriši profil','superio') ?></h3>
	<div class="inner-list">
		<div class="widget-delete">
			<div class="conf-messages"><?php esc_html_e('Da li ste sigurni da želite da obrišete svoj profil?', 'superio'); ?></div>
			<div class="undone-messages"><?php esc_html_e('Brisanje profila se ne može opozvati!', 'superio'); ?></div>

			<form method="post" action="" class="delete-profile-form">

				<div class="form-group">
					<div class="conf-deleted"><?php esc_html_e( 'Unesite svoju lozinku da biste potvrdili:', 'superio' ); ?></div>
					<input id="delete-profile-password" class="form-control" type="password" name="password" required="required" placeholder="<?php esc_attr_e('Lozinka', 'superio'); ?>">
				</div><!-- /.form-control -->

				<?php
					do_action('wp-job-board-pro-delete-profile-form-fields');
					wp_nonce_field('wp-job-board-pro-delete-profile-nonce', 'nonce');
				?>

				<button type="submit" class="btn btn-danger delete-profile-btn"><?php esc_html_e( 'Obriši profil', 'superio' ); ?></button>
			</form>
		</div>
	</div>
</div>