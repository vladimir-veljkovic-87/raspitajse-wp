<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $post;
?>

<?php do_action( 'wp_job_board_pro_before_job_detail', $post->ID ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class('job-single-v5'); ?>>

	<!-- heading -->
	<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-job_listing/header-v4' ); ?>

	<div class="job-content-area container">
		<div class="content-job-detail">
			<!-- Main content -->
			<div class="list-content-job">
				<?php do_action( 'wp_job_board_pro_before_job_content', $post->ID ); ?>
				
				<!-- overview -->
				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-job_listing/detail' ); ?>

				<!-- job description -->
				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-job_listing/description' ); ?>

				<!-- job social -->
				<?php if ( superio_get_config('show_job_social_share', false) ) { ?>
					<div class="sharebox-job">
	        			<?php get_template_part( 'template-parts/sharebox-job' );  ?>
	        		</div>
	            <?php } ?>

				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-job_listing/photos' ); ?>

				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-job_listing/video' ); ?>
				
	            <!-- job releated -->
	            <?php
	            	if ( superio_get_config('job_releated_show', true) ) {
		            	echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-job_listing/releated' );
		            }
	            ?>

				<?php do_action( 'wp_job_board_pro_after_job_content', $post->ID ); ?>
			</div>
		</div>
	</div>

</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_job_detail', $post->ID ); ?>