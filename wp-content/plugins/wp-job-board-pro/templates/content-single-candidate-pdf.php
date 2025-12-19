<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php do_action( 'wp_job_board_pro_before_job_detail', get_the_ID() ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class('candidate-single-v1'); ?>>
	<!-- heading -->
	<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/header' ); ?>

	<!-- Main content -->
	<div class="row">
		<div class="col-sm-12">

			<?php do_action( 'wp_job_board_pro_before_job_content', get_the_ID() ); ?>
			
			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/detail' ); ?>

			<!-- job description -->
			<div class="job-detail-description">
				<h3><?php esc_html_e('About Me', 'wp-job-board-pro'); ?></h3>
				<div class="inner">
					<?php the_content(); ?>
				</div>
			</div>

			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/education' ); ?>

			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/experience' ); ?>

			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/portfolios' ); ?>

			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/skill' ); ?>

			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/award' ); ?>

			<?php if ( comments_open() || get_comments_number() ) : ?>
				<!-- Review -->
				<?php comments_template(); ?>
			<?php endif; ?>

			<?php do_action( 'wp_job_board_pro_after_job_content', get_the_ID() ); ?>
		</div>
	</div>

</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_job_detail', get_the_ID() ); ?>