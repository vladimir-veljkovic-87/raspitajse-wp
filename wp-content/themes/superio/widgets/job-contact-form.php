<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
extract( $args );

global $post, $job_preview;
if ( $job_preview ) {
    $post = $job_preview;
}
if ( empty($post->post_type) || $post->post_type != 'job_listing' ) {
    return;
}


$user_id = superio_get_post_author($post->ID);
$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
if ( empty($employer_id) ) {
    return;
}
$emp_post = get_post($employer_id);

$author_email = WP_Job_Board_Pro_Employer::get_display_email( $emp_post );

if ( ! empty( $author_email ) ) :
	extract( $args );
	extract( $instance );

	echo trim($before_widget);
	$title = !empty($instance['title']) ? sprintf($instance['title'], $emp_post->post_title) : '';
	$title = apply_filters('widget_title', $title);

	if ( $title ) {
	    echo trim($before_title)  . trim( $title ) . $after_title;
	}

	$email = $phone = '';
	if ( is_user_logged_in() ) {
		$current_user_id = get_current_user_id();

		if ( WP_Job_Board_Pro_User::is_employer() ) {
			$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($current_user_id);
			$phone = WP_Job_Board_Pro_Employer::get_display_phone( $employer_id );
			$email = WP_Job_Board_Pro_Employer::get_post_meta($employer_id, 'email', true);
		} elseif ( method_exists('WP_Job_Board_Pro_User', 'is_employee') && WP_Job_Board_Pro_User::is_employee($user_id) ) {
			$employee_user_id = WP_Job_Board_Pro_User::get_user_id();
			$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($employee_user_id);
			$phone = WP_Job_Board_Pro_Employer::get_post_meta($employer_id, 'phone', true);
			$email = WP_Job_Board_Pro_Employer::get_post_meta($employer_id, 'email', true);
		} elseif( WP_Job_Board_Pro_User::is_candidate() ) {
			$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($current_user_id);
			$phone = WP_Job_Board_Pro_Candidate::get_display_phone($candidate_id);
			$email = WP_Job_Board_Pro_Candidate::get_post_meta($candidate_id, 'email', true);
		}

		if ( !empty($email) ) {
			$userdata = get_userdata( $current_user_id );
			$email = $userdata->user_email;
		}
	}
?>


	<div class="contact-form">
		
	    <form method="post" action="?" class="contact-form-employer in-sidebar contact-form-wrapper">
	    	<div class="row">
		        <div class="col-sm-12">
			        <div class="form-group">
			            <input type="text" class="form-control style2" name="subject" placeholder="<?php esc_attr_e( 'Subject', 'superio' ); ?>" required="required">
			        </div><!-- /.form-group -->
			    </div>
			    <div class="col-sm-12">
			        <div class="form-group">
			            <input type="email" class="form-control style2" name="email" placeholder="<?php esc_attr_e( 'E-mail', 'superio' ); ?>" required="required" value="<?php echo esc_attr($email); ?>">
			        </div><!-- /.form-group -->
			    </div>
			    <div class="col-sm-12">
			        <div class="form-group">
			            <input type="text" class="form-control style2" name="phone" placeholder="<?php esc_attr_e( 'Phone', 'superio' ); ?>" required="required" value="<?php echo esc_attr($phone); ?>">
			        </div><!-- /.form-group -->
			    </div>
	        </div>
	        <div class="form-group space-30">
	            <textarea class="form-control style2" name="message" placeholder="<?php esc_attr_e( 'Message', 'superio' ); ?>" required="required"></textarea>
	        </div><!-- /.form-group -->

	        <?php if ( WP_Job_Board_Pro_Recaptcha::is_recaptcha_enabled() ) { ?>
	            <div id="recaptcha-contact-form" class="ga-recaptcha" data-sitekey="<?php echo esc_attr(wp_job_board_pro_get_option( 'recaptcha_site_key' )); ?>"></div>
	      	<?php } ?>

	      	<input type="hidden" name="post_id" value="<?php echo esc_attr($employer_id); ?>">
	      	<input type="hidden" name="job_id" value="<?php echo esc_attr($post->ID); ?>">
	        <button class="button btn btn-theme btn-block" name="contact-form"><?php echo esc_html__( 'Send Message', 'superio' ); ?></button>

	    </form>

	    <?php do_action('superio_after_contact_form', $emp_post, $user_id); ?>
		
	</div>
<?php echo trim($after_widget);
endif;