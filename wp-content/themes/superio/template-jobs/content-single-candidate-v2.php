<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $post;
?>

<?php do_action( 'wp_job_board_pro_before_job_detail', get_the_ID() ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class('candidate-single-v2'); ?>>
	
	

	<div class="candidate-content-area <?php echo apply_filters('superio_candidate_content_class', 'container');?>">
		<!-- Main content -->
		<div class="row content-single-candidate">
			<div class="col-xs-12 list-content-candidate col-md-<?php echo esc_attr( is_active_sidebar( 'candidate-single-sidebar' ) ? 8 : 12); ?>">

				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/header-v2' ); ?>

				<?php do_action( 'wp_job_board_pro_before_job_content', get_the_ID() ); ?>
				<!-- job description -->
				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/description' ); ?>
				
				<!-- job social -->
				<?php if ( superio_get_config('show_candidate_social_share', false) ) { ?>
					<div class="sharebox-job">
	        			<?php get_template_part( 'template-parts/sharebox-job' );  ?>
	        		</div>
	            <?php } ?>
	            
				<?php if ( superio_get_config('show_candidate_map_location', true) ) { ?>
					<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'single-candidate/map-location' ); ?>
				<?php } ?>

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
			
			<div class="col-xs-12 col-md-4 sidebar-job">
				<div class="candidate-detail-buttons flex-middle">
	                <?php
                 	if ( WP_Job_Board_Pro_Candidate::check_restrict_view_contact_info($post) || wp_job_board_pro_get_option('restrict_contact_candidate_download_cv', 'on') !== 'on' ) {
	                	WP_Job_Board_Pro_Candidate::display_download_cv_btn($post->ID);
	                }
	                ?>
                    <?php superio_candidate_show_invite($post->ID); ?>
	                <?php WP_Job_Board_Pro_Candidate::display_shortlist_btn($post->ID); ?>
	            </div>
	            <?php if ( is_active_sidebar( 'candidate-single-sidebar' ) ): ?>
		   			<?php dynamic_sidebar( 'candidate-single-sidebar' ); ?>
		   		<?php endif; ?>
		   	</div>
		   	
		</div>
	</div>
</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_job_detail', get_the_ID() ); ?>