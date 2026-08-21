<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php do_action( 'wp_job_board_pro_before_job_detail', get_the_ID() ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class('candidate-single-v1'); ?>>
	<!-- heading -->
	<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/header-pdf' ); ?>

	<!-- Main content -->
	<div class="row">
		<div class="col-sm-12">

			<?php do_action( 'wp_job_board_pro_before_job_content', get_the_ID() ); ?>
			
			
			<!-- job description -->
			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/description' ); ?>
			
			<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/detail' ); ?>

			<?php if ( superio_get_config('show_candidate_education', true) ) { ?>
				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/education', array('source_pdf' => true) ); ?>
			<?php } ?>

			<?php if ( superio_get_config('show_candidate_experience', true) ) { ?>
				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/experience', array('source_pdf' => true) ); ?>
			<?php } ?>

			<?php if ( superio_get_config('show_candidate_portfolios', true) ) { ?>
				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/portfolios-pdf' ); ?>
			<?php } ?>

			<?php if ( superio_get_config('show_candidate_skill', true) ) { ?>
				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/skill' ); ?>
			<?php } ?>

			<?php if ( superio_get_config('show_candidate_award', true) ) { ?>
				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/award', array('source_pdf' => true) ); ?>
			<?php } ?>
			
			<?php do_action( 'wp_job_board_pro_after_job_content', get_the_ID() ); ?>
		</div>
	</div>

</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_job_detail', get_the_ID() ); ?>