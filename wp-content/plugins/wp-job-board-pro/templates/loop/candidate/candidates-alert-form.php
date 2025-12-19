<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email_frequency_default = WP_Job_Board_Pro_Job_Alert::get_email_frequency();
?>
<div class="candidate-alert-form-btn">
	<a href="javascript:void(0);" class="btn btn-theme btn-candidate-alert"><?php esc_html_e('Get Candidate Alerts', 'wp-job-board-pro'); ?></a>
</div>
<div class="candidate-alert-form-wrapper hidden">
	<form method="get" action="" class="candidate-alert-form">
		<div class="form-group">
		    <label for="candidate_alert_title"><?php esc_html_e('Naziv', 'wp-job-board-pro'); ?></label>

		    <input type="text" name="name" class="form-control" id="candidate_alert_title">
		</div><!-- /.form-group -->

		<div class="form-group">
		    <label for="candidate_alert_email_frequency"><?php esc_html_e('Učestalost E-pošte', 'wp-job-board-pro'); ?></label>
		    <div class="wrapper-select">
			    <select name="email_frequency" class="form-control" id="candidate_alert_email_frequency">
			        <?php if ( !empty($email_frequency_default) ) { ?>
			            <?php foreach ($email_frequency_default as $key => $value) {
			                if ( !empty($value['label']) && !empty($value['days']) ) {
			            ?>
			                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_attr($value['label']); ?></option>

			                <?php } ?>
			            <?php } ?>
			        <?php } ?>
			    </select>
		    </div>
		</div><!-- /.form-group -->

		<?php
			do_action('wp-job-board-pro-add-candidate-alert-form');

			wp_nonce_field('wp-job-board-pro-add-candidate-alert-nonce', 'nonce');
		?>

		<div class="form-group">
			<button class="button"><?php esc_html_e('Sačuvaj obaveštenja za kandidate', 'wp-job-board-pro'); ?></button>
		</div><!-- /.form-group -->

	</form>
</div>