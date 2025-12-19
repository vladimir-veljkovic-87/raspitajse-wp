<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $post;
?>

<?php do_action( 'wp_job_board_pro_before_job_detail', $post->ID ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<!-- heading -->
	<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-job_listing/header' ); ?>

	<!-- Main content -->
	<div class="row">
		<div class="col-sm-9">

			<?php do_action( 'wp_job_board_pro_before_job_content', $post->ID ); ?>
			<!-- job description -->
			<div class="job-detail-description">
				<h3><?php esc_html_e('Job Description', 'wp-job-board-pro'); ?></h3>
				<div class="inner">
					<?php the_content(); ?>
				</div>
			</div>

			<!-- job releated -->
			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-job_listing/releated' ); ?>

			<?php do_action( 'wp_job_board_pro_after_job_content', $post->ID ); ?>
		</div>
		<div class="col-sm-3">
			<?php do_action( 'wp_job_board_pro_before_job_sidebar', $post->ID ); ?>
			<!-- job detail employer -->
			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-job_listing/employer-detail' ); ?>
			<!-- job detail -->
			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-job_listing/detail' ); ?>
			<!-- job detail -->
			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-job_listing/map-location' ); ?>

			<?php do_action( 'wp_job_board_pro_after_job_sidebar', $post->ID ); ?>
		</div>
	</div>

</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_job_detail', $post->ID ); ?>