<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email_frequency_default = WP_Job_Board_Pro_Job_Alert::get_email_frequency();
?>
<div class="job-alert-form-btn">
	<a href="javascript:void(0);" class="btn btn-theme btn-job-alert"><?php esc_html_e('Get Jobs Alerts', 'wp-job-board-pro'); ?></a>
</div>
<div class="job-alert-form-wrapper hidden">
	<form method="get" action="" class="job-alert-form">
		<div class="form-group">
		    <label for="job_alert_title"><?php esc_html_e('Title', 'wp-job-board-pro'); ?></label>

		    <input type="text" name="name" class="form-control" id="job_alert_title" placeholder="<?php esc_html_e('Title', 'wp-job-board-pro'); ?>">
		</div><!-- /.form-group -->

		<div class="form-group">
		    <label for="job_alert_email_frequency"><?php esc_html_e('Email Frequency', 'wp-job-board-pro'); ?></label>
		    <div class="wrapper-select">
			    <select name="email_frequency" class="form-control" id="job_alert_email_frequency">
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
			do_action('wp-job-board-pro-add-job-alert-form');

			wp_nonce_field('wp-job-board-pro-add-job-alert-nonce', 'nonce');
		?>

		<div class="form-group">
			<button class="button"><?php esc_html_e('Save Job Alert', 'wp-job-board-pro'); ?></button>
		</div><!-- /.form-group -->

	</form>
</div>