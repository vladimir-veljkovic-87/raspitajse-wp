<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="employers-pagination-wrapper">
	<?php
		WP_Job_Board_Pro_Mixes::custom_pagination( array(
			'max_num_pages' => $employers->max_num_pages,
			'prev_text'     => esc_html__( 'Previous page', 'wp-job-board-pro' ),
			'next_text'     => esc_html__( 'Next page', 'wp-job-board-pro' ),
			'wp_query' => $employers
		));
	?>
</div>
