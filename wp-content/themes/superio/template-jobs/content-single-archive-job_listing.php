<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $post;
?>

<?php do_action( 'wp_job_board_pro_before_job_detail', $post->ID ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class('job-single-v1'); ?>>

	<!-- heading -->
	<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-job_listing/header-half-detail' ); ?>

	<div class="job-content-area">
		<!-- Main content -->
		<div class="content-job-detail">
			<div class="list-content-job">

				<?php do_action( 'wp_job_board_pro_before_job_content', $post->ID ); ?>
				
				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-job_listing/detail' ); ?>

				<!-- job description -->
				<?php
				if ( superio_get_config('show_job_description', true) ) {
					echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-job_listing/description' );
				}
				?>

				<!-- job social -->
				<?php if ( superio_get_config('show_job_social_share', true) ) { ?>
					<div class="sharebox-job">
	        			<?php get_template_part( 'template-parts/sharebox' );  ?>
	        		</div>
	            <?php } ?>
				
				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-job_listing/tags' ); ?>

				<?php do_action( 'wp_job_board_pro_after_job_content', $post->ID ); ?>
			</div>
			
		</div>
	</div>

</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_job_detail', $post->ID ); ?>