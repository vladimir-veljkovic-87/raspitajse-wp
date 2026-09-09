<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_candidate = superio_get_config('register_form_enable_candidate', true);
$show_employer = superio_get_config('register_form_enable_employer', true);
if ( !$show_candidate && !$show_employer ) {
	return;
}
$popup = isset($popup) ? $popup : false;

$rand = superio_random_key();
?>

<div class="register-form register-form-wrapper">

	<?php if ( $show_candidate && $show_employer ) { ?>
	    <ul class="role-tabs nav nav-tabs">
	        <li class="active"><a data-toggle="tab" href="#apus_register_form_candidate_<?php echo esc_attr($rand); ?>"><i class="flaticon-user"></i><?php esc_html_e('Candidate', 'superio'); ?></a></li>
	        <li><a data-toggle="tab" href="#apus_register_form_employer_<?php echo esc_attr($rand); ?>"><i class="flaticon-briefcase"></i><?php esc_html_e('Employer', 'superio'); ?></a></li>
	    </ul>
	<?php } ?>

	<div class="tab-content clearfix">
		<?php if ( $show_candidate ) { ?>
		    <div class="tab-pane active in" id="apus_register_form_candidate_<?php echo esc_attr($rand); ?>">
		        <form name="registerForm" method="post" class="register-form register-form-candidate">
		          		
					<?php do_action('register_candidate_form_fields_before'); ?>

					<input type="hidden" name="role" value="wp_job_board_pro_candidate">

					<div class="form-group">
						<label><?php esc_attr_e('Email *','superio'); ?></label>
						<input type="text" class="form-control" name="email" placeholder="<?php esc_attr_e('Email *','superio'); ?>" required>
					</div>
					<div class="form-group">
						<label><?php esc_attr_e('Password *','superio'); ?></label>
						<span class="show_hide_password">
							<input type="password" class="form-control" name="password" placeholder="<?php esc_attr_e('Password *','superio'); ?>" required>
							<a class="toggle-password" title="<?php esc_attr_e('Show', 'superio'); ?>"><span class="dashicons dashicons-hidden"></span></a>
						</span>
					</div>
					<div class="form-group">
						<label><?php esc_attr_e('Confirm Password *','superio'); ?></label>
						<span class="show_hide_password">
							<input type="password" class="form-control" name="confirmpassword" placeholder="<?php esc_attr_e('Confirm Password *','superio'); ?>" required>
							<a class="toggle-password" title="<?php esc_attr_e('Show', 'superio'); ?>"><span class="dashicons dashicons-hidden"></span></a>
						</span>
					</div>

					<?php do_action('register_candidate_form_fields_after'); ?>


					<?php
						wp_nonce_field('ajax-register-candidate-nonce', 'security_register_candidate');
					?>

					<?php if ( WP_Job_Board_Pro_Recaptcha::is_recaptcha_enabled() ) { ?>
			            <div id="recaptcha-register-candidate-form" class="ga-recaptcha" data-sitekey="<?php echo esc_attr(wp_job_board_pro_get_option( 'recaptcha_site_key' )); ?>"></div>
			      	<?php } ?>
			      	
			      	<?php
					$page_id = wp_job_board_pro_get_option('terms_conditions_page_id');
					if ( !empty($page_id) ) {
						$page_url = $page_id ? get_permalink($page_id) : home_url('/');
					?>
						<div class="form-group">
							<label for="candidate-register-terms-and-conditions">
								<input type="checkbox" name="terms_and_conditions" value="on" id="candidate-register-terms-and-conditions" required>
								<?php
									$allowed_html_array = array( 'a' => array('href' => array(), 'target' => array()) );
									echo sprintf(wp_kses(__('You accept our <a href="%s" target="_blank">Terms and Conditions and Privacy Policy</a>', 'superio'), $allowed_html_array), esc_url($page_url));
								?>
							</label>
						</div>
					<?php } ?>

					<div class="form-group text-center">
						<button type="submit" class="btn btn-theme btn-block" name="submitRegister">
							<?php echo esc_html__('Register now', 'superio'); ?>
						</button>
					</div>

					<?php do_action('register_form'); ?>
		      	</form>
		    </div>
		<?php } ?>
		<?php if ( $show_employer ) { ?>
		    <div class="tab-pane <?php echo esc_attr( !$show_candidate ? 'active in' : '' ); ?>" id="apus_register_form_employer_<?php echo esc_attr($rand); ?>">
		        <form name="registerForm" method="post" class="register-form register-form-employer">
		          		
					<?php do_action('register_employer_form_fields_before'); ?>

					<input type="hidden" name="role" value="wp_job_board_pro_employer">
					
					<div class="form-group">
						<label><?php esc_attr_e('Email *','superio'); ?></label>
						<input type="text" class="form-control" name="email" placeholder="<?php esc_attr_e('Email *','superio'); ?>" required>
					</div>
					<div class="form-group">
						<label><?php esc_attr_e('Password *','superio'); ?></label>
						<span class="show_hide_password">
							<input type="password" class="form-control" name="password" placeholder="<?php esc_attr_e('Password *','superio'); ?>" required>
							<a class="toggle-password" title="<?php esc_attr_e('Show', 'superio'); ?>"><span class="dashicons dashicons-hidden"></span></a>
						</span>
					</div>
					<div class="form-group">
						<label><?php esc_attr_e('Confirm Password *','superio'); ?></label>
						<span class="show_hide_password">
							<input type="password" class="form-control" name="confirmpassword" placeholder="<?php esc_attr_e('Confirm Password *','superio'); ?>" required>
							<a class="toggle-password" title="<?php esc_attr_e('Show', 'superio'); ?>"><span class="dashicons dashicons-hidden"></span></a>
						</span>
					</div>

					<?php do_action('register_employer_form_fields_after'); ?>


					<?php wp_nonce_field('ajax-register-employer-nonce', 'security_register_employer'); ?>

					<?php if ( WP_Job_Board_Pro_Recaptcha::is_recaptcha_enabled() ) { ?>
			            <div id="recaptcha-register-employer-form" class="ga-recaptcha" data-sitekey="<?php echo esc_attr(wp_job_board_pro_get_option( 'recaptcha_site_key' )); ?>"></div>
			      	<?php } ?>
			      	
			      	<?php
					$page_id = wp_job_board_pro_get_option('terms_conditions_page_id');
					if ( !empty($page_id) ) {
						$page_url = $page_id ? get_permalink($page_id) : home_url('/');
					?>
						<div class="form-group">
							<label for="employer-register-terms-and-conditions">
								<input type="checkbox" name="terms_and_conditions" value="on" id="employer-register-terms-and-conditions" required>
								<?php
									$allowed_html_array = array( 'a' => array('href' => array(), 'target' => array()) );
									echo sprintf(wp_kses(__('You accept our <a href="%s" target="_blank">Terms and Conditions and Privacy Policy</a>', 'superio'), $allowed_html_array), esc_url($page_url));
								?>
							</label>
						</div>
					<?php } ?>

					<div class="form-group text-center">
						<button type="submit" class="btn btn-theme btn-block" name="submitRegister">
							<?php echo esc_html__('Register now', 'superio'); ?>
						</button>
					</div>

					<?php do_action('register_form'); ?>
		      	</form>
		    </div>
		<?php } ?>

		<?php if ( $popup ) { ?>
			<div class="login-info">
				<?php esc_html_e('Already have an account?', 'superio'); ?>
				<a class="apus-user-login" href="#apus_login_forgot_form">
	                <?php esc_html_e('Login', 'superio'); ?>
	            </a>
	        </div>
	    <?php } ?>
	</div>
</div>