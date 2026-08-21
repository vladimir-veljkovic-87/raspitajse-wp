<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'dashicons' );
$popup = isset($popup) ? $popup : false;
$rand = rand(0000, 9999);
?>
<div class="login-form-wrapper">
	<div id="login-form-wrapper<?php echo esc_attr($rand); ?>" class="form-container">			
		<?php if ( defined('SUPERIO_DEMO_MODE') && SUPERIO_DEMO_MODE ) { ?>
			<div class="sign-in-demo-notice">
				Username: <strong>candidate</strong> or <strong>employer</strong><br>
				Password: <strong>demo</strong>
			</div>
		<?php } ?>
		
		<form class="login-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="post">
			<?php if ( isset($_SESSION['register_msg']) ) { ?>
				<div class="alert <?php echo esc_attr($_SESSION['register_msg']['error'] ? 'alert-warning' : 'alert-info'); ?>">
					<?php echo trim($_SESSION['register_msg']['msg']); ?>
				</div>
			<?php
				unset($_SESSION['register_msg']);
			}
			?>
			<div class="form-group">
				<label><?php esc_attr_e('Email','superio'); ?></label>
				<input autocomplete="off" type="text" name="username" class="form-control" id="username_or_email" placeholder="<?php esc_attr_e('Email','superio'); ?>">
			</div>
			<div class="form-group">
				<label><?php esc_attr_e('Lozinka','superio'); ?></label>
				<span class="show_hide_password">
					<input name="password" type="password" class="password required form-control" id="login_password" placeholder="<?php esc_attr_e('Lozinka','superio'); ?>">
					<a class="toggle-password" title="<?php esc_attr_e('Show', 'superio'); ?>"><span class="dashicons dashicons-hidden"></span></a>
				</span>
			</div>

			<div class="row form-group info">
				<div class="col-xs-6">
					<label for="user-remember-field<?php echo esc_attr($rand); ?>" class="remember label-ostavi">
						<input type="checkbox" class="ostavi-me" name="remember" id="user-remember-field<?php echo esc_attr($rand); ?>" value="true"> <?php echo esc_html__('Ostavi me prijavljenog','superio'); ?>
					</label>
				</div>
				<div class="col-xs-6 link-right">
					<a class="back-link" href="#forgot-password-form-wrapper<?php echo esc_attr($rand); ?>" title="<?php esc_attr_e('Forgotten password','superio'); ?>"><?php echo esc_html__("Zaboravili ste lozinku?",'superio'); ?></a>
				</div>
			</div>
			
			<?php if ( version_compare(WP_JOB_BOARD_PRO_PLUGIN_VERSION, '1.2.32', '>=') && WP_Job_Board_Pro_Recaptcha::is_recaptcha_enabled() ) { ?>
				<div class="form-group info">
					<div id="recaptcha-login-form" class="ga-recaptcha" data-sitekey="<?php echo esc_attr(wp_job_board_pro_get_option( 'recaptcha_site_key' )); ?>"></div>
				</div>
			<?php } ?>
			
			<div class="form-group">
				<input type="submit" class="btn btn-theme btn-block" name="submit" value="<?php esc_attr_e('Uloguj se','superio'); ?>"/>
			</div>
			<?php
				do_action('login_form');
				wp_nonce_field('ajax-login-nonce', 'security_login');
			?>
			<?php if ( $popup ) { ?>
				<div class="register-info">
					<?php esc_html_e('Još uvek nemate nalog?', 'superio'); ?>
					<span>
						<a class="btn-readmore" href="https://raspitajse.com/registracija-kandidata/">Registruj se kao kandidat</a>					
					</span>
					<span  style="margin-left:5px;margin-right:5px;">
						|
					</span>
					<span>
						<a class="btn-readmore" href="https://raspitajse.com/registracija-poslodavca/">Registruj se kao poslodavac</a>	
					</span>
	            </div>
	        <?php } ?>
		</form>
	</div>
	<!-- reset form -->
	<div id="forgot-password-form-wrapper<?php echo esc_attr($rand); ?>" class="form-container forgotpassword-form-wrapper">
		<div class="top-info-user text-center1">
			<h3 class="title"><?php echo esc_html__('Resetuj lozinku', 'superio'); ?></h3>
			<div class="des"><?php echo esc_html__('Unesite korisničko ime ili email adresu','superio') ?></div>
		</div>
		<form name="forgotpasswordform" class="forgotpassword-form" action="<?php echo esc_url( site_url('wp-login.php?action=lostpassword', 'login_post') ); ?>" method="post">
			<div class="lostpassword-fields">
				<div class="form-group">
					<input type="text" name="user_login" class="user_login form-control" id="lostpassword_username" placeholder="<?php esc_attr_e('Korisničko ime ili E-mail','superio'); ?>">
				</div>
				<?php
					do_action('lostpassword_form');
					wp_nonce_field('ajax-lostpassword-nonce', 'security_lostpassword');
				?>

	            <div id="recaptcha-forgotpasswordform" class="ga-recaptcha" data-sitekey="<?php echo esc_attr(wp_job_board_pro_get_option( 'recaptcha_site_key' )); ?>"></div>
		      	
				<div class="form-group">
					<div class="row">
						<div class="col-xs-6"><input type="submit" class="btn btn-theme btn-sm btn-block" name="wp-submit" value="<?php esc_attr_e('Zatraži novu lozinku', 'superio'); ?>" tabindex="100" /></div>
					</div>
				</div>
			</div>
			<div class="lostpassword-link"><a href="#login-form-wrapper<?php echo esc_attr($rand); ?>" class="back-link"><?php echo esc_html__('Povratak na prijavu', 'superio'); ?></a></div>
		</form>
	</div>
</div>
