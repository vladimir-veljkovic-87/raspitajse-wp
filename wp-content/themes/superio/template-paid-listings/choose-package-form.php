<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$form      = WP_Job_Board_Pro_Submit_Form::get_instance();
$job_id    = $form->get_job_id();
$step      = $form->get_step();
$form_name = $form->get_form_name();

$user_id = get_current_user_id();
$user_packages = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_packages_by_user($user_id, true);
$packages = WP_Job_Board_Pro_Wc_Paid_Listings_Submit_Form::get_products();

?>
<div class="box-dashboard-wrapper">
	<form method="post" id="job_package_selection">
		<?php if ( WP_Job_Board_Pro_User::is_employer_can_add_submission($user_id) || WP_Job_Board_Pro_User::is_employer_can_edit_job( $job_id ) ) { ?>
			<div class="job_job_packages_title">
				<input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />

				<input type="hidden" name="<?php echo esc_attr($form_name); ?>" value="<?php echo esc_attr($form_name); ?>">
				<input type="hidden" name="submit_step" value="<?php echo esc_attr($step); ?>">
				<input type="hidden" name="object_id" value="<?php echo esc_attr($job_id); ?>">

				<?php wp_nonce_field('wp-job-board-pro-job-submit-package-nonce', 'security-job-submit-package'); ?>

				<h2 class="title_package_heading hidden"><?php esc_html_e( 'Choose a package', 'superio' ); ?></h2>
			</div>
			<div class="job_listing_types">
				<?php echo WP_Job_Board_Pro_Wc_Paid_Listings_Template_Loader::get_template_part('user-packages', array('user_packages' => $user_packages) ); ?>
				<?php echo WP_Job_Board_Pro_Wc_Paid_Listings_Template_Loader::get_template_part('packages', array('packages' => $packages) ); ?>
			</div>
		<?php } else { ?>
			<div class="text-warning">
				<?php esc_html_e('Sorry, you can\'t post a listing.', 'superio'); ?>
			</div>
		<?php } ?>
	</form>
</div>