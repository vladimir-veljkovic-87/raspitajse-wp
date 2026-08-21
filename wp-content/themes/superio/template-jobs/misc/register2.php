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
		        <?php echo do_shortcode( '[wp_job_board_pro_register_candidate]' ); ?>
		    </div>
		<?php } ?>
		<?php if ( $show_employer ) { ?>
		    <div class="tab-pane <?php echo esc_attr( !$show_candidate ? 'active in' : '' ); ?>" id="apus_register_form_employer_<?php echo esc_attr($rand); ?>">
		        <?php echo do_shortcode( '[wp_job_board_pro_register_employer]' ); ?>
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