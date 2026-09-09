<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

remove_action('wp_job_board_pro_before_job_archive', array( 'WP_Job_Board_Pro_Job_Listing', 'display_jobs_count_results'), 10 );
$layout_type = superio_get_jobs_layout_type();
if ( $layout_type !== 'half-map' ) {
	add_action('wp_job_board_pro_before_job_archive', array( 'WP_Job_Board_Pro_Job_Listing', 'display_jobs_count_results'), 20 );
}

remove_action( 'wp_job_board_pro_before_job_archive', array( 'WP_Job_Board_Pro_Job_Listing', 'display_job_feed' ), 22 );
remove_action( 'wp_job_board_pro_before_job_archive', array( 'WP_Job_Board_Pro_Job_Listing', 'display_jobs_alert_form' ), 20 );

add_action( 'wp_job_board_pro_before_job_archive', 'superio_job_display_filter_btn', 19 );
add_action( 'wp_job_board_pro_before_job_archive', 'superio_job_display_per_page_form', 26 );

superio_job_display_topbar_sidebar();
?>
<div class="jobs-listing-wrapper main-items-wrapper" data-display_mode="<?php echo esc_attr($jobs_display_mode); ?>">
	<?php
	/**
	 * wp_job_board_pro_before_job_archive
	 */
	do_action( 'wp_job_board_pro_before_job_archive', $jobs );
	?>

	<?php if ( !empty($jobs) && !empty($jobs->posts) ) : ?>
		<?php
		if ( $layout_type !== 'half-job-details' ) {
			/**
			 * wp_job_board_pro_before_loop_job
			 */
			do_action( 'wp_job_board_pro_before_loop_job', $jobs );
			?>
			
			<div class="jobs-wrapper items-wrapper">
				<?php 
					$columns = superio_get_jobs_columns();
					$columns = !empty($columns) ? $columns : 3;
					$bcol = $columns ? 12/$columns : 4;
					$i = 0;
				?>
				<div class="row items-wrapper-<?php echo esc_attr($job_inner_style); ?>">
					<?php while ( $jobs->have_posts() ) : $jobs->the_post(); ?>
						<div class="item-job <?php echo esc_attr($columns > 1 ? 'col-sm-6' : 'col-sm-12'); ?> col-md-<?php echo esc_attr($bcol); ?> col-xs-12 <?php echo esc_attr(($i%$columns == 0)?'lg-clearfix md-clearfix':'') ?> <?php echo esc_attr($columns > 1 && ($i%2 == 0)?'sm-clearfix':'') ?>">
							<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'jobs-styles/inner-'.$job_inner_style ); ?>
						</div>
					<?php $i++; endwhile; ?>
				</div>
			</div>

			<?php
			/**
			 * wp_job_board_pro_after_loop_job
			 */
			do_action( 'wp_job_board_pro_after_loop_job', $jobs );
			
			wp_reset_postdata();
		} else {
			?>
			<div class="row flex">
				<div class="col-xs-12 col-md-5 col-lg-4">
					<div class="topfilter-half-job-detail flex-middle">
						<?php WP_Job_Board_Pro_Job_Listing::display_jobs_count_results($jobs); ?>
						<div class="ali-right">
							<?php superio_job_display_per_page_form($jobs); ?>
						</div>
					</div>
					<?php
					/**
					 * wp_job_board_pro_before_loop_job
					 */
					do_action( 'wp_job_board_pro_before_loop_job', $jobs );
					?>
					
					<div class="jobs-wrapper items-wrapper">
						<?php 
							$columns = superio_get_jobs_columns();
							$bcol = $columns ? 12/$columns : 4;
						?>
						<div class="row row-items items-wrapper-<?php echo esc_attr($job_inner_style); ?>">
							<?php $i=0; while ( $jobs->have_posts() ) : $jobs->the_post();
								global $post;
								$classes = '';
								if ( $i == 0 ) {
									$classes = 'active';
								}
							?>
								<div class="item-job <?php echo esc_attr($classes); ?> col-xs-12" data-job_id="<?php echo esc_attr($post->ID); ?>">
									<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'jobs-styles/inner-'.$job_inner_style ); ?>
								</div>
							<?php $i++; endwhile; ?>
						</div>
					</div>

					<?php
					/**
					 * wp_job_board_pro_after_loop_job
					 */
					do_action( 'wp_job_board_pro_after_loop_job', $jobs );
					
					wp_reset_postdata();

					echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/job/pagination', array('jobs' => $jobs));
					?>

				</div><!-- .content-area -->
				<div class="col-xs-12 col-md-7 col-lg-8 hidden-xs hidden-sm">
					<div id="job-details-wrapper" class="sticky-top">
						<?php
							$job = $jobs->posts[0];
							setup_postdata( $GLOBALS['post'] =& $job );

							echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'content-single-archive-job_listing' );

							wp_reset_postdata();
						?>
					</div>
				</div>
			</div>
			<?php
		}
		?>

	<?php else : ?>
		<div class="not-found"><?php esc_html_e('No job found.', 'superio'); ?></div>
	<?php endif; ?>

	<?php
	/**
	 * wp_job_board_pro_after_job_archive
	 */
	do_action( 'wp_job_board_pro_after_job_archive', $jobs );
	?>
</div>