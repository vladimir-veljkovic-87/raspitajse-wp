<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="register-form-wrapper">
  	<div class="container-form">

		<ul class="role-tabs">
			<li class="active"><?php esc_html_e('Candidate', 'wp-job-board-pro'); ?></li>
			<li><?php esc_html_e('Employer', 'wp-job-board-pro'); ?></li>
		</ul>

      	<form name="registerForm" method="post" class="register-form register-form-candidate">
			<?php do_action('register_candidate_form_fields_before'); ?>

			<input type="radio" name="role" value="wp_job_board_pro_candidate" checked="checked" class="hidden">

			<div class="form-group">
				<label for="register-username"><?php esc_html_e('Username', 'wp-job-board-pro'); ?></label>
				<sup class="required-field">*</sup>
				<input type="text" class="form-control" name="username" id="register-username" placeholder="<?php esc_attr_e('Enter Username','wp-job-board-pro'); ?>">
			</div>
			<div class="form-group">
				<label for="register-email"><?php esc_html_e('Email', 'wp-job-board-pro'); ?></label>
				<sup class="required-field">*</sup>
				<input type="text" class="form-control" name="email" id="register-email" placeholder="<?php esc_attr_e('Enter Email','wp-job-board-pro'); ?>">
			</div>
			<div class="form-group">
				<label for="password"><?php esc_html_e('Password', 'wp-job-board-pro'); ?></label>
				<sup class="required-field">*</sup>
				<input type="password" class="form-control" name="password" id="password" placeholder="<?php esc_attr_e('Enter Password','wp-job-board-pro'); ?>">
			</div>
			<div class="form-group">
				<label for="confirmpassword"><?php esc_html_e('Confirm Password', 'wp-job-board-pro'); ?></label>
				<sup class="required-field">*</sup>
				<input type="password" class="form-control" name="confirmpassword" id="confirmpassword" placeholder="<?php esc_attr_e('Enter Password','wp-job-board-pro'); ?>">
			</div>


			<?php do_action('register_candidate_form_fields_after'); ?>


			<?php wp_nonce_field('ajax-register-nonce', 'security_register'); ?>

			<?php if ( WP_Job_Board_Pro_Recaptcha::is_recaptcha_enabled() ) { ?>
	            <div id="recaptcha-contact-form" class="ga-recaptcha" data-sitekey="<?php echo esc_attr(wp_job_board_pro_get_option( 'recaptcha_site_key' )); ?>"></div>
	      	<?php } ?>
	      	
			<?php
			$page_id = wp_job_board_pro_get_option('terms_conditions_page_id');
			if ( !empty($page_id) ) {
				$page_url = $page_id ? get_permalink($page_id) : home_url('/');
			?>
				<div class="form-group">
					<label for="register-terms-and-conditions">
						<input type="checkbox" name="terms_and_conditions" value="on" id="register-terms-and-conditions" required>
						<?php
							echo sprintf(__('You accept our <a href="%s">Terms and Conditions and Privacy Policy</a>', 'wp-job-board-pro'), esc_url($page_url));
						?>
					</label>
				</div>
			<?php } ?>

			<div class="form-group">
				<button type="submit" class="btn btn-second btn-block" name="submitRegister">
					<?php echo esc_html__('Register now', 'wp-job-board-pro'); ?>
				</button>
			</div>

			<?php do_action('register_form'); ?>
      	</form>

      	<form name="registerForm" method="post" class="register-form register-form-employer">
			<?php do_action('register_employer_form_fields_before'); ?>

			<input type="radio" name="role" value="wp_job_board_pro_employer" class="hidden">

			<div class="form-group">
				<label for="register-username"><?php esc_html_e('Username', 'wp-job-board-pro'); ?></label>
				<sup class="required-field">*</sup>
				<input type="text" class="form-control" name="username" id="register-username" placeholder="<?php esc_attr_e('Enter Username','wp-job-board-pro'); ?>">
			</div>
			<div class="form-group">
				<label for="register-email"><?php esc_html_e('Email', 'wp-job-board-pro'); ?></label>
				<sup class="required-field">*</sup>
				<input type="text" class="form-control" name="email" id="register-email" placeholder="<?php esc_attr_e('Enter Email','wp-job-board-pro'); ?>">
			</div>
			<div class="form-group">
				<label for="password"><?php esc_html_e('Password', 'wp-job-board-pro'); ?></label>
				<sup class="required-field">*</sup>
				<input type="password" class="form-control" name="password" id="password" placeholder="<?php esc_attr_e('Enter Password','wp-job-board-pro'); ?>">
			</div>
			<div class="form-group">
				<label for="confirmpassword"><?php esc_html_e('Confirm Password', 'wp-job-board-pro'); ?></label>
				<sup class="required-field">*</sup>
				<input type="password" class="form-control" name="confirmpassword" id="confirmpassword" placeholder="<?php esc_attr_e('Enter Password','wp-job-board-pro'); ?>">
			</div>


			<?php do_action('register_employer_form_fields_after'); ?>


			<?php wp_nonce_field('ajax-register-nonce', 'security_register'); ?>

			<?php if ( WP_Job_Board_Pro_Recaptcha::is_recaptcha_enabled() ) { ?>
	            <div id="recaptcha-contact-form" class="ga-recaptcha" data-sitekey="<?php echo esc_attr(wp_job_board_pro_get_option( 'recaptcha_site_key' )); ?>"></div>
	      	<?php } ?>
	      	
			<?php
			$page_id = wp_job_board_pro_get_option('terms_conditions_page_id');
			if ( !empty($page_id) ) {
				$page_url = $page_id ? get_permalink($page_id) : home_url('/');
			?>
				<div class="form-group">
					<label for="register-terms-and-conditions">
						<input type="checkbox" name="terms_and_conditions" value="on" id="register-terms-and-conditions" required>
						<?php
							echo sprintf(__('You accept our <a href="%s">Terms and Conditions and Privacy Policy</a>', 'wp-job-board-pro'), esc_url($page_url));
						?>
					</label>
				</div>
			<?php } ?>

			<div class="form-group">
				<button type="submit" class="btn btn-second btn-block" name="submitRegister">
					<?php echo esc_html__('Register now', 'wp-job-board-pro'); ?>
				</button>
			</div>

			<?php do_action('register_form'); ?>
      	</form>
    </div>

</div>
