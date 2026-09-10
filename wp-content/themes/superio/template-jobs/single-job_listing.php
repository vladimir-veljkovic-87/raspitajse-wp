<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$job_layout = superio_get_job_layout_type();
$job_layout = !empty($job_layout) ? $job_layout : 'v1';


?>
<section class="detail-version-<?php echo esc_attr($job_layout); ?>">
	
	<section id="primary" class="content-area inner">
		<div id="main" class="site-main content" role="main">
			<?php if ( have_posts() ) : ?>

				<?php while ( have_posts() ) : the_post();
					global $post;
					if ( $post->post_status == 'expired' ) {
						echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'content-single-job_listing-expired' );
					} else {
						if ( method_exists('WP_Job_Board_Pro_Job_Listing', 'check_view_job_detail') && !WP_Job_Board_Pro_Job_Listing::check_view_job_detail() ) {
						?>
							<div class="restrict-wrapper container">
								<?php
									$restrict_detail = wp_job_board_pro_get_option('job_restrict_detail', 'all');
									switch ($restrict_detail) {
										case 'register_user':
											?>
											<h2 class="restrict-title"><?php esc_html_e( 'This page is restricted for registered users only.', 'superio' ); ?></h2>
											<div class="restrict-content"><?php esc_html_e( 'Please login to view this page', 'superio' ); ?></div>
											<?php
											break;
										case 'register_candidate':
											?>
											<h2 class="restrict-title"><?php esc_html_e( 'Please login as candidate to view job.', 'superio' ); ?></h2>
											<?php
											break;
										default:
											$content = apply_filters('wp-job-board-pro-restrict-job-detail-information', '', $post);
											echo trim($content);
											break;
									}
								?>
							</div><!-- /.alert -->

							<?php
						} else {
						?>
							<div class="single-listing-wrapper job_listing" <?php superio_job_item_map_meta($post); ?>>
								<?php

										if ( $job_layout !== 'v1' ) {
											echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'content-single-job_listing-'.$job_layout );
										} else {
											echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'content-single-job_listing' );
										}
								?>
							</div>
					<?php
						}
					}
				endwhile; ?>

				<?php the_posts_pagination( array(
					'prev_text'          => esc_html__( 'Previous page', 'superio' ),
					'next_text'          => esc_html__( 'Next page', 'superio' ),
					'before_page_number' => '<span class="meta-nav screen-reader-text">' . esc_html__( 'Page', 'superio' ) . ' </span>',
				) ); ?>
			<?php else : ?>
				<?php get_template_part( 'content', 'none' ); ?>
			<?php endif; ?>
		</div><!-- .site-main -->
	</section><!-- .content-area -->
</section>
<?php get_footer(); ?>
