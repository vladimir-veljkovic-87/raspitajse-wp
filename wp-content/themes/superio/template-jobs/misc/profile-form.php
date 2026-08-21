<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_enqueue_style( 'dashicons' );

?>
<div class="profile-form-wrapper box-dashboard-wrapper dashboard-wrapper-v2">
	<h1 class="title"><?php esc_html_e( 'Uredi Profil', 'superio' ) ; ?></h1>
	<div class="inner-list">
		<?php if ( ! empty( $_SESSION['messages'] ) ) : ?>

			<ul class="messages">
				<?php foreach ( $_SESSION['messages'] as $message ) { ?>
					<?php
					$status = !empty( $message[0] ) ? $message[0] : 'success';
					if ( !empty( $message[1] ) ) {
					?>
					<li class="message_line <?php echo esc_attr( $status ) ?>">
						<?php echo trim( $message[1] ); ?>
					</li>
				<?php
					}
				}
				unset( $_SESSION['messages'] );
				?>
			</ul>

		<?php endif; ?>

		<?php
		$form_html = cmb2_get_metabox_form( $metaboxes_form, $post_id, array(
			'form_format' => '<form action="form_url_replace_html" class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<input type="submit" name="submit-cmb-profile" value="%4$s" class="button-primary"></form>',
			'save_button' => esc_html__( 'Sačuvaj Profil', 'superio' ),
		) );

		$current_url = WP_Job_Board_Pro_Mixes::get_full_current_url();

		echo str_replace('form_url_replace_html', $current_url, $form_html);
	?>
	</div>
</div>
