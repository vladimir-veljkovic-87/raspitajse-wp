<?php
global $post;
// get our custom meta
$author_email = WP_Job_Board_Pro_Employer::get_post_meta( $post->ID, 'email', true );
$email = $phone = '';
if ( is_user_logged_in() ) {
	$user_id = WP_Job_Board_Pro_User::get_user_id();
	$userdata = get_userdata( $user_id );
	$email = $userdata->user_email;
	if ( WP_Job_Board_Pro_User::is_employer() ) {
		$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
		$phone = WP_Job_Board_Pro_Employer::get_post_meta($employer_id, 'phone', true);
	} elseif( WP_Job_Board_Pro_User::is_candidate() ) {
		$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
		$phone = WP_Job_Board_Pro_Candidate::get_post_meta($candidate_id, 'phone', true);
	}
}
?>


<div class="contact-form widget">
	<h2 class="widget-title">
		<span><?php echo sprintf( esc_html__('Contact %s', 'wp-job-board-pro'), get_the_title() ); ?></span>
	</h2>
	
	<?php if ( ! empty( $author_email ) ) : ?>
	    <form method="post" action="?" class="contact-form-wrapper">
	    	<div class="row">
		        <div class="col-sm-12">
			        <div class="form-group">
			            <input type="text" class="form-control style2" name="subject" placeholder="<?php esc_attr_e( 'Subject', 'wp-job-board-pro' ); ?>" required="required">
			        </div><!-- /.form-group -->
			    </div>
			    <div class="col-sm-12">
			        <div class="form-group">
			            <input type="email" class="form-control style2" name="email" placeholder="<?php esc_attr_e( 'E-mail', 'wp-job-board-pro' ); ?>" required="required" value="<?php echo esc_attr($email); ?>">
			        </div><!-- /.form-group -->
			    </div>
			    <div class="col-sm-12">
			        <div class="form-group">
			            <input type="text" class="form-control style2" name="phone" placeholder="<?php esc_attr_e( 'Phone', 'wp-job-board-pro' ); ?>" required="required" value="<?php echo esc_attr($phone); ?>">
			        </div><!-- /.form-group -->
			    </div>
	        </div>
	        <div class="form-group space-30">
	            <textarea class="form-control style2" name="message" placeholder="<?php esc_attr_e( 'Message', 'wp-job-board-pro' ); ?>" required="required"></textarea>
	        </div><!-- /.form-group -->

	        <?php if ( WP_Job_Board_Pro_Recaptcha::is_recaptcha_enabled() ) { ?>
                <div id="recaptcha-contact-form" class="ga-recaptcha" data-sitekey="<?php echo esc_attr(wp_job_board_pro_get_option( 'recaptcha_site_key' )); ?>"></div>
          	<?php } ?>

          	<input type="hidden" name="post_id" value="<?php echo esc_attr($post->ID); ?>">
	        <button class="button btn btn-theme btn-block" name="contact-form"><?php echo esc_html__( 'Send Message', 'wp-job-board-pro' ); ?></button>
	    </form>
	<?php endif; ?>
</div>
