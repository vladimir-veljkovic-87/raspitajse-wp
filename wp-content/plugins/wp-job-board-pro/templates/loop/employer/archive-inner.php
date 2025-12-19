<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="employers-listing-wrapper">
	<?php
	/**
	 * wp_job_board_pro_before_employer_archive
	 */
	do_action( 'wp_job_board_pro_before_employer_archive', $employers );
	?>

	<?php
	if ( !empty($employers) && !empty($employers->posts) ) {

		/**
		 * wp_job_board_pro_before_loop_employer
		 */
		do_action( 'wp_job_board_pro_before_loop_employer', $employers );
		?>

		<div class="employers-wrapper">
			<?php while ( $employers->have_posts() ) : $employers->the_post(); ?>
				<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'employers-styles/inner-grid' ); ?>
			<?php endwhile;?>
		</div>

		<?php
		/**
		 * wp_job_board_pro_after_loop_employer
		 */
		do_action( 'wp_job_board_pro_after_loop_employer', $employers );

		WP_Job_Board_Pro_Mixes::custom_pagination( array(
			'max_num_pages' => $employers->max_num_pages,
			'prev_text'          => __( 'Previous page', 'wp-job-board-pro' ),
			'next_text'          => __( 'Next page', 'wp-job-board-pro' ),
			'wp_query' => $employers
		));

		wp_reset_postdata();
	?>

	<?php } else { ?>
		<div class="not-found"><?php esc_html_e('No employer found.', 'wp-job-board-pro'); ?></div>
	<?php } ?>

	<?php
	/**
	 * wp_job_board_pro_after_employer_archive
	 */
	do_action( 'wp_job_board_pro_after_employer_archive', $employers );
	?>
</div>