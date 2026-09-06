<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="submission-form-wrapper">
	<?php
		do_action( 'wp_job_board_pro_job_submit_done_content_after', sanitize_title( $job->post_status ), $job );

		switch ( $job->post_status ) :
			case 'publish' :
				echo wp_kses_post(sprintf(__( 'Job listed successfully. To view your listing <a href="%s">click here</a>.', 'wp-job-board-pro' ), get_permalink( $job->ID ) ));
			break;
			case 'pending' :
				echo wp_kses_post(sprintf(esc_html__( 'Job submitted successfully. Your listing will be visible once approved.', 'wp-job-board-pro' ), get_permalink( $job->ID )));
			break;
			default :
				do_action( 'wp_job_board_pro_job_submit_done_content_' . str_replace( '-', '_', sanitize_title( $job->post_status ) ), $job );
			break;
		endswitch;

		do_action( 'wp_job_board_pro_job_submit_done_content_after', sanitize_title( $job->post_status ), $job );
	?>
</div>
