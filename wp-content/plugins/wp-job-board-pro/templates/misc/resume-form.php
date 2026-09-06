<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="resume-form-wrapper widget box-employer">
	<h1 class="widget-title"><?php _e( 'Edit Resume', 'wp-job-board-pro' ) ; ?></h1>

	<?php
	$post_status =  get_post_status($post_id);
	if ( $post_status == 'pending' || $post_status == 'pending_approve' ) {
		?>
		<div class="alert alert-danger"><?php esc_html_e('Your resume has to be confirmed by an administrator before publish.', 'wp-job-board-pro'); ?></div>
		<?php
		do_action('wp-job-board-pro-resume-form-status-pending', $post_status, $post_id);
	} elseif ( $post_status == 'expired' ) {
		?>
		<div class="alert alert-danger"><?php esc_html_e('Your resume has expired.', 'wp-job-board-pro'); ?></div>
		<?php
		do_action('wp-job-board-pro-resume-form-status-expired', $post_status, $post_id);
	}

	do_action('wp-job-board-pro-resume-form-status', $post_status, $post_id);

	if ( ! empty( $_SESSION['messages'] ) ) : ?>

		<ul class="messages">
			<?php foreach ( $_SESSION['messages'] as $message ) { ?>
				<?php
				$status = !empty( $message[0] ) ? $message[0] : 'success';
				if ( !empty( $message[1] ) ) {
				?>
				<li class="message_line <?php echo esc_attr( $status ) ?>">
					<?php echo wp_kses_post( $message[1] ); ?>
				</li>
			<?php
				}
			}
			unset( $_SESSION['messages'] );
			?>
		</ul>

	<?php endif; ?>

	<?php
		echo cmb2_get_metabox_form( $metaboxes_form, $post_id, array(
			'form_format' => '<form action="//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '" class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<input type="submit" name="submit-cmb-resume" value="%4$s" class="button-primary"></form>',
			'save_button' => __( 'Save Resume', 'wp-job-board-pro' ),
		) );
	?>
</div>
