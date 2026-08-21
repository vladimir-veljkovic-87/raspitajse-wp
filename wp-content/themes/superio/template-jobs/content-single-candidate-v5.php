<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $post;
?>
<?php do_action( 'wp_job_board_pro_before_job_detail', get_the_ID() ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class('candidate-single-v5'); ?>>
	
	<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/header-v5' ); ?>

	<div class="candidate-content-area <?php echo apply_filters('superio_candidate_content_class', 'container');?>">
		<!-- Main content -->
		<div class="row content-single-candidate">
			<div class="col-xs-12 list-content-candidate col-md-<?php echo esc_attr( is_active_sidebar( 'candidate-single-sidebar' ) ? 8 : 12); ?>">

				<?php do_action( 'wp_job_board_pro_before_job_content', get_the_ID() ); ?>
				
				<!-- job description -->
				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/description' ); ?>

				<?php if ( superio_get_config('show_candidate_education', true) ) { ?>
					<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/education' ); ?>
				<?php } ?>

				<?php if ( superio_get_config('show_candidate_experience', true) ) { ?>
					<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/experience' ); ?>
				<?php } ?>

				<?php if ( superio_get_config('show_candidate_portfolios', true) ) { ?>
					<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/portfolios' ); ?>
				<?php } ?>

				<?php if ( superio_get_config('show_candidate_skill', true) ) { ?>
					<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/skill' ); ?>
				<?php } ?>

				<?php if ( superio_get_config('show_candidate_award', true) ) { ?>
					<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/award' ); ?>
				<?php } ?>

				<?php if ( superio_get_config('show_candidate_video', true) ) { ?>
					<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/video' ); ?>
				<?php } ?>

				<?php
					if ( superio_get_config('candidate_releated_show', true) ) {
						echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/releated' );
					}
				?>

				<?php if ( superio_check_employer_candidate_review($post) ) : ?>
					<!-- Review -->
					<?php comments_template(); ?>
				<?php endif; ?>

				<?php do_action( 'wp_job_board_pro_after_job_content', get_the_ID() ); ?>
			</div>
			<?php if ( is_active_sidebar( 'candidate-single3-sidebar' ) ): ?>
				<div class="col-xs-12 col-md-4">
					<div class="p-l-lg-20 sidebar-job2">
			   			<?php dynamic_sidebar( 'candidate-single3-sidebar' ); ?>
			   		</div>
			   	</div>
		   	<?php endif; ?>

		</div>
	</div>
</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_job_detail', get_the_ID() ); ?>