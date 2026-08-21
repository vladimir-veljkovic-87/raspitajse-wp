<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$candidate_layout = superio_get_candidate_layout_type();
$candidate_layout = !empty($candidate_layout) ? $candidate_layout : 'v1';

?>
<section class="<?php echo 'candidate_single_layout'.esc_attr($candidate_layout) ?>">
	<section id="primary" class="content-area inner">
		<div id="main" class="site-main content" role="main">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post();
					global $post;
					if ( $post->post_status == 'expired' ) {
						echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'content-single-candidate-expired' );
					} else {
						if ( method_exists('WP_Job_Board_Pro_Candidate', 'check_view_candidate_detail') && !WP_Job_Board_Pro_Candidate::check_view_candidate_detail() ) {
							?>
							<div class="restrict-wrapper container">
								<!-- list cv package -->
								<?php
									$restrict_detail = wp_job_board_pro_get_option('candidate_restrict_detail', 'all');
									switch ($restrict_detail) {
										case 'register_user':
											?>
											<h2 class="restrict-title"><?php esc_html_e( 'This page is restricted for registered users only.', 'superio' ); ?></h2>
											<div class="restrict-content"><?php esc_html_e( 'Please login to view this page', 'superio' ); ?></div>
											<?php
											break;
										case 'only_applicants':
											?>
											<h2 class="restrict-title"><?php esc_html_e( 'The page is restricted only for employers view his applicants.', 'superio' ); ?></h2>
											<?php
											break;
										case 'register_employer':
											?>
											<h2 class="restrict-title"><?php esc_html_e( 'Please login as employer to view candidate.', 'superio' ); ?></h2>
											<?php
											break;
										default:
											$content = apply_filters('wp-job-board-pro-restrict-candidate-detail-information', '', $post);
											echo trim($content);
											break;
									}
								?>
							</div><!-- /.alert -->

							<?php
						} else {
						?>
							<div class="single-listing-wrapper candidate" <?php superio_candidate_item_map_meta($post); ?>>
								<?php
									if ( $candidate_layout !== 'v1' ) {
										echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'content-single-candidate-'.$candidate_layout );
									} else {
										echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'content-single-candidate' );
									}
								?>
							</div>
						<?php } ?>
					<?php } ?>
				<?php endwhile; ?>

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
<?php get_footer();
