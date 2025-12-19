<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


?>
<div class="jobs-listing-wrapper">
	<?php
	/**
	 * wp_job_board_pro_before_job_archive
	 */
	do_action( 'wp_job_board_pro_before_job_archive', $jobs );
	?>

	<?php if ( $jobs->have_posts() ) : ?>
		<?php
		/**
		 * wp_job_board_pro_before_loop_job
		 */
		do_action( 'wp_job_board_pro_before_loop_job', $jobs );
		?>

		<div class="jobs-wrapper">
			<?php while ( $jobs->have_posts() ) : $jobs->the_post(); ?>
				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'jobs-styles/inner-list' ); ?>
			<?php endwhile; ?>
		</div>

		<?php
		/**
		 * wp_job_board_pro_after_loop_job
		 */
		do_action( 'wp_job_board_pro_after_loop_job', $jobs );

		WP_Job_Board_Pro_Mixes::custom_pagination( array(
			'max_num_pages' => $jobs->max_num_pages,
			'prev_text'     => esc_html__( 'Previous page', 'wp-job-board-pro' ),
			'next_text'     => esc_html__( 'Next page', 'wp-job-board-pro' ),
			'wp_query' => $jobs
		));
		?>

	<?php else : ?>
		<div class="not-found"><?php esc_html_e('No job found.', 'wp-job-board-pro'); ?></div>
	<?php endif; ?>

	<?php
	/**
	 * wp_job_board_pro_before_job_archive
	 */
	do_action( 'wp_job_board_pro_before_job_archive', $jobs );
	?>
</div>