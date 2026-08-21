<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $post;
?>
<?php do_action( 'wp_job_board_pro_before_job_detail', $post->ID ); ?>

<article id="post-<?php echo esc_attr($post->ID); ?>" <?php post_class('employer-single-v3'); ?>>
	<!-- heading -->
	<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-employer/header-v2' ); ?>

	<div class="employer-content-area <?php echo apply_filters('superio_employer_content_class', 'container');?>">
		<!-- Main content -->
		<div class="row content-single-candidate">

			<?php if ( is_active_sidebar( 'employer-single-sidebar2' ) ): ?>
				<div class="col-md-4 col-xs-12 sidebar-job">
			   		<?php dynamic_sidebar( 'employer-single-sidebar' ); ?>
			   	</div>
		   	<?php endif; ?>

			<div class="list-content-candidate col-xs-12 col-md-<?php echo esc_attr( is_active_sidebar( 'employer-single-sidebar2' ) ? 8 : 12); ?>">

				<?php do_action( 'wp_job_board_pro_before_employer_content', $post->ID ); ?>

				<!-- employer description -->
				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-employer/description' ); ?>
				
				<?php if ( superio_get_config('show_employer_socials', true) ) { ?>
					<!-- Socials -->
					<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-employer/socials' ); ?>
				<?php } ?>

				<?php if ( superio_get_config('show_employer_photos', true) ) { ?>
					<!-- profile photos -->
					<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-employer/profile-photos' ); ?>
				<?php } ?>

				<?php if ( superio_get_config('show_employer_video', true) ) { ?>
					<!-- Video -->
					<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-employer/video' ); ?>
				<?php } ?>

				<?php if ( superio_get_config('show_employer_members', true) ) { ?>
					<!-- team member -->
					<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-employer/members' ); ?>
				<?php } ?>
				
				<?php if ( superio_get_config('show_employer_open_jobs', true) ) { ?>
					<!-- employer releated -->
					<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-employer/open-jobs' ); ?>
				<?php } ?>

				<?php if ( superio_check_employer_candidate_review($post) ) : ?>
					<!-- Review -->
					<?php comments_template(); ?>
				<?php endif; ?>

				<?php do_action( 'wp_job_board_pro_after_employer_content', $post->ID ); ?>
			</div>
			
		</div>
	</div>

</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_job_detail', $post->ID ); ?>