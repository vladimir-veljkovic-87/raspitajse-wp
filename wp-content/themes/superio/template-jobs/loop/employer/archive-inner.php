<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$layout_type = superio_get_employers_layout_type();
if ( $layout_type == 'half-map' ) {
	remove_action( 'wp_job_board_pro_before_employer_archive', array( 'WP_Job_Board_Pro_Employer', 'display_employers_count_results' ), 10 );
}
add_action( 'wp_job_board_pro_before_employer_archive', 'superio_employer_display_filter_btn', 10 );
add_action( 'wp_job_board_pro_before_employer_archive', 'superio_employer_display_per_page_form', 26 );

$display_mode = superio_get_employers_display_mode();
$inner_style = superio_get_employers_inner_style();
$columns = superio_get_employers_columns();
$columns = !empty($columns) ? $columns : 3;
$bcol = $columns ? 12/$columns : 4;
?>
<div class="employers-listing-wrapper main-items-wrapper layout-type-<?php echo esc_attr($display_mode); ?>" data-display_mode="<?php echo esc_attr($display_mode); ?>">
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

		<div class="employers-wrapper items-wrapper">
			<?php $i = 0; ?>
			<div class="row">
				<?php while ( $employers->have_posts() ) : $employers->the_post(); ?>
					<div class="<?php echo esc_attr($columns > 1 ? 'col-sm-6' : 'col-sm-12'); ?> col-md-<?php echo esc_attr($bcol); ?> col-xs-12 <?php echo esc_attr($i%$columns == 0 ? 'md-clearfix lg-clearfix':''); ?> <?php echo esc_attr(($columns > 1 && $i%2 == 0) ? 'sm-clearfix':''); ?>">
						<?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'employers-styles/inner-'.$inner_style ); ?>
					</div>
				<?php $i++; endwhile; ?>
			</div>
		</div>

		<?php
		/**
		 * wp_job_board_pro_after_loop_employer
		 */
		do_action( 'wp_job_board_pro_after_loop_employer', $employers );

		wp_reset_postdata();
	?>

	<?php } else { ?>
		<div class="not-found"><?php esc_html_e('No employer found.', 'superio'); ?></div>
	<?php } ?>

	<?php
	/**
	 * wp_job_board_pro_after_employer_archive
	 */
	do_action( 'wp_job_board_pro_after_employer_archive', $employers );
	?>
</div>