<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main content" role="main">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post();
					global $post;
					if ( !WP_Job_Board_Pro_Job_Listing::check_view_job_detail() ) {
					?>
						<div class="restrict-wrapper">
							<?php
								$restrict_detail = wp_job_board_pro_get_option('job_restrict_detail', 'all');
								switch ($restrict_detail) {
									case 'register_user':
										?>
										<h2 class="restrict-title"><?php echo __( 'The page is restricted only for register user.', 'wp-job-board-pro' ); ?></h2>
										<div class="restrict-content"><?php echo __( 'You need login to view this page', 'wp-job-board-pro' ); ?></div>
										<?php
										break;
									case 'only_applicants':
										?>
										<h2 class="restrict-title"><?php echo __( 'The page is restricted only for candidates view his applicants.', 'wp-job-board-pro' ); ?></h2>
										<?php
										break;
									case 'register_candidate':
										?>
										<h2 class="restrict-title"><?php echo __( 'The page is restricted only for candidates.', 'wp-job-board-pro' ); ?></h2>
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
						<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'content-single-job_listing' ); ?>
				<?php
					}
				
				endwhile; ?>

				<?php the_posts_pagination( array(
					'prev_text'          => __( 'Previous page', 'wp-job-board-pro' ),
					'next_text'          => __( 'Next page', 'wp-job-board-pro' ),
					'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'wp-job-board-pro' ) . ' </span>',
				) ); ?>
			<?php else : ?>
				<?php get_template_part( 'content', 'none' ); ?>
			<?php endif; ?>
		</main><!-- .site-main -->
	</section><!-- .content-area -->

<?php get_footer(); ?>
