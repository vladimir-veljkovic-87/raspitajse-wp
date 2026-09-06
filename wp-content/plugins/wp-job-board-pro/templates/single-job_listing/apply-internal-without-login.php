<?php

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}
global $post;


?>

<div id="job-apply-internal-without-login-form-wrapper-<?php echo esc_attr($post->ID); ?>" class="job-apply-email-form-wrapper mfp-hide">
	<div class="inner">
		<h2 class="widget-title">
			<span><?php echo __('Apply for this job', 'wp-job-board-pro'); ?></span>
		</h2>

	    <?php
	    	$form = WP_Job_Board_Pro_Candidate_Register_Apply_Form::get_instance();
			echo $form->form_output();
	    ?>
	</div>
</div>