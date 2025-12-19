<?php

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}
global $post;
// get our custom meta

$author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($post->ID);
$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);

$phone = '';
$meta_obj = WP_Job_Board_Pro_Job_Listing_Meta::get_instance($post->ID);
if ( $meta_obj->check_post_meta_exist('phone') ) {
	$phone = $meta_obj->get_post_meta('phone');
}

if ( !$phone ) {
	$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($employer_id);
	if ( $meta_obj->check_post_meta_exist('phone') ) {
		$phone = $meta_obj->get_post_meta('phone');
	}
}

?>

<?php if ( ! empty( $phone ) ) : ?>
	<div id="job-apply-call-form-wrapper-<?php echo esc_attr($post->ID); ?>" class="job-apply-email-form-wrapper mfp-hide">
		<div class="inner">
			<h2 class="widget-title">
				<span><?php echo __('Call employer', 'wp-job-board-pro'); ?></span>
			</h2>
			<div class="widget-content">
				<div class="des">
					<?php echo sprintf(__('Call %s to apply', 'wp-job-board-pro'), get_the_title($employer_id)); ?>
				</div>
				<div class="phone">
					<a href="tel: <?php echo esc_attr($phone); ?>">
						<i class="flaticon-phone"></i> <span class="phont-text"><?php echo esc_html($phone); ?></span>
					</a>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>