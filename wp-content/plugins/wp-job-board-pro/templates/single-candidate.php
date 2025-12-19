<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main content" role="main">
			<?php if ( have_posts() ) : ?>
				<?php
				while ( have_posts() ) : the_post();
					global $post;
					if ( !WP_Job_Board_Pro_Candidate::check_view_candidate_detail() ) {
						?>
						<div class="restrict-wrapper">
							<!-- list cv package -->
							<?php
								$restrict_detail = wp_job_board_pro_get_option('candidate_restrict_detail', 'all');
								switch ($restrict_detail) {
									case 'register_user':
										?>
										<h2 class="restrict-title"><?php echo __( 'The page is restricted only for register user.', 'wp-job-board-pro' ); ?></h2>
										<div class="restrict-content"><?php echo __( 'You need login to view this page', 'wp-job-board-pro' ); ?></div>
										<?php
										break;
									case 'only_applicants':
										?>
										<h2 class="restrict-title"><?php echo __( 'The page is restricted only for employers view his applicants.', 'wp-job-board-pro' ); ?></h2>
										<?php
										break;
									case 'register_employer':
										?>
										<h2 class="restrict-title"><?php echo __( 'Please login as employer to view candidate.', 'wp-job-board-pro' ); ?></h2>
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
						echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'content-single-candidate' );
					}
				endwhile;
				?>

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
