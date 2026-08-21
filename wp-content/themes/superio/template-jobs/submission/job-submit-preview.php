<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $post, $job_preview;
$job_preview = $post;
$job_layout = superio_get_job_layout_type();
$job_layout = !empty($job_layout) ? $job_layout : 'v1';
?>
<div class="job-submission-preview-form-wrapper">
	<?php if ( sizeof($form_obj->errors) ) : ?>
		<ul class="messages">
			<?php foreach ( $form_obj->errors as $message ) { ?>
				<li class="message_line danger">
					<?php echo trim( $message ); ?>
				</li>
			<?php
			}
			?>
		</ul>
	<?php endif; ?>
	<form action="<?php echo esc_url($form_obj->get_form_action());?>" class="cmb-form" method="post" enctype="multipart/form-data" encoding="multipart/form-data">
		<input type="hidden" name="<?php echo esc_attr($form_obj->get_form_name()); ?>" value="<?php echo esc_attr($form_obj->get_form_name()); ?>">
		<input type="hidden" name="job_id" value="<?php echo esc_attr($job_id); ?>">
		<input type="hidden" name="submit_step" value="<?php echo esc_attr($step); ?>">
		<input type="hidden" name="object_id" value="<?php echo esc_attr($job_id); ?>">
		<?php wp_nonce_field('wp-job-board-pro-job-submit-preview-nonce', 'security-job-submit-preview'); ?>

		<div class="action-preview">
			<button class="button btn btn-success btn-lg" name="continue-submit-job"><?php esc_html_e('Submit Job', 'superio'); ?></button>
			<button class="button btn btn-theme btn-lg" name="continue-edit-job"><?php esc_html_e('Edit Job', 'superio'); ?></button>
		</div>
		
	</form>
	
	<div class="single-listing-wrapper job_listing" <?php superio_job_item_map_meta($post); ?>>
		<?php
			if ( $job_layout !== 'v1' ) {
				echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'content-single-job_listing-'.$job_layout );
			} else {
				echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'content-single-job_listing' );
			}
		?>
	</div>
</div>