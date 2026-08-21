<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $post;
?>

<?php do_action( 'wp_job_board_pro_before_job_detail', $post->ID ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class('job-single-v3'); ?>>

	<div class="job-content-area <?php echo apply_filters('superio_job_content_class', 'container');?>">
		<!-- Main content -->
		<div class="row content-job-detail">

			<div class="list-content-job col-xs-12 col-md-<?php echo esc_attr( is_active_sidebar( 'job-single-sidebar' ) ? 8 : 12); ?>">

				<!-- heading -->
				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-job_listing/header-v3' ); ?>

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
				
				<?php
					if ( superio_get_config('job_releated_show', true) ) {
		            	echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-job_listing/releated' );
		            }
				?>
	            
				<?php do_action( 'wp_job_board_pro_after_job_content', $post->ID ); ?>
			</div>
			
			<?php if ( is_active_sidebar( 'job-single3-sidebar' ) ): ?>
				<div class="col-md-4 col-xs-12 sidebar-job">
			   		<?php dynamic_sidebar( 'job-single3-sidebar' ); ?>
			   	</div>
		   	<?php endif; ?>
		   	
		</div>
	</div>

</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_job_detail', $post->ID ); ?>