<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $post;
?>

<?php do_action( 'wp_job_board_pro_before_job_detail', get_the_ID() ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<!-- heading -->
	<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-employer/header' ); ?>

	<!-- Main content -->
	<div class="row">
		<div class="col-sm-9">

			<?php do_action( 'wp_job_board_pro_before_job_content', get_the_ID() ); ?>
			<!-- job description -->
			<div class="job-detail-description">
				<h3><?php esc_html_e('Opis Posla', 'wp-job-board-pro'); ?></h3>
				<div class="inner">
					<?php the_content(); ?>
				</div>
			</div>
			<!-- profile photos -->
			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-employer/profile-photos' ); ?>

			<!-- team member -->
			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-employer/members' ); ?>

			<!-- Socials -->
			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-employer/socials' ); ?>

			<!-- job releated -->
			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-employer/open-jobs' ); ?>

			<?php if ( (comments_open() || get_comments_number()) && WP_Job_Board_Pro_Employer::check_restrict_review($post) ) : ?>
				<!-- Review -->
				<?php comments_template(); ?>
			<?php endif; ?>

			<?php do_action( 'wp_job_board_pro_after_job_content', get_the_ID() ); ?>
		</div>
		<div class="col-sm-3">
			<?php do_action( 'wp_job_board_pro_before_job_sidebar', get_the_ID() ); ?>
			<!-- job detail -->
			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-employer/detail' ); ?>
			<!-- job detail -->
			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-employer/map-location' ); ?>
			
			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-employer/contact-form' ); ?>

			<?php do_action( 'wp_job_board_pro_after_job_sidebar', get_the_ID() ); ?>
		</div>
	</div>

</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_job_detail', get_the_ID() ); ?>