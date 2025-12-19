<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !empty($args['before_widget']) ) {
	echo wp_kses_post( $args['before_widget'] );
}

if ( ! empty( $instance['title'] ) ) {
	echo wp_kses_post( $args['before_title'] );
	echo esc_attr( $instance['title'] );
	echo wp_kses_post( $args['after_title'] );
}


/* OVDЕ UBACUJEMO SUBTITLE */
if ( ! empty( $instance['subtitle'] ) ) {
    echo '<p class="candidate-alert-subtitle">' . esc_html( $instance['subtitle'] ) . '</p>';
}


$email_frequency_default = WP_Job_Board_Pro_Job_Alert::get_email_frequency();

?>

<form method="get" action="" class="candidate-alert-form">
	<div class="form-group">
	    <label for="<?php echo esc_attr( $args['widget_id'] ); ?>_title"><?php esc_html_e('Naziv obaveštenja', 'wp-job-board-pro'); ?></label>

	    <input type="text" name="name" class="form-control" id="<?php echo esc_attr( $args['widget_id'] ); ?>_title">
	</div><!-- /.form-group -->

	<div class="form-group">
	    <label for="<?php echo esc_attr( $args['widget_id'] ); ?>_email_frequency"><?php esc_html_e('Učestalost E-pošte', 'wp-job-board-pro'); ?></label>
	    <select name="email_frequency" class="form-control" id="<?php echo esc_attr( $args['widget_id'] ); ?>_email_frequency">
	        <?php if ( !empty($email_frequency_default) ) { ?>
	            <?php foreach ($email_frequency_default as $key => $value) {
	                if ( !empty($value['label']) && !empty($value['days']) ) {
	            ?>
	                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_attr($value['label']); ?></option>

	                <?php } ?>
	            <?php } ?>
	        <?php } ?>
	    </select>
	</div><!-- /.form-group -->

	<?php
		do_action('wp-job-board-pro-add-candidate-alert-form');

		wp_nonce_field('wp-job-board-pro-add-candidate-alert-nonce', 'nonce');
	?>
	<?php if ( ! empty( $instance['button_text'] ) ) : ?>
		<div class="form-group">
			<button class="button"><?php echo esc_attr( $instance['button_text'] ); ?></button>
		</div><!-- /.form-group -->
	<?php endif; ?>
</form>

<?php
if ( !empty($args['after_widget']) ) {
	echo wp_kses_post( $args['after_widget'] );
}
?>

