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
				// Prikaz ako je oglas objavljen
				echo wp_kses_post(sprintf(__( 'Oglas je uspešno objavljen. Da biste ga pogledali, <a href="%s">kliknite ovde</a>.', 'wp-job-board-pro' ), get_permalink( $job->ID ) ));
			break;
			case 'pending' :
				// Prikaz ako oglas čeka odobrenje
				echo wp_kses_post(sprintf(esc_html__( 'Oglas je uspešno poslat. Biće vidljiv nakon odobrenja.', 'wp-job-board-pro' ), get_permalink( $job->ID )));
			break;
			default :
				// Za druge statuse koristi poseban hook
				do_action( 'wp_job_board_pro_job_submit_done_content_' . str_replace( '-', '_', sanitize_title( $job->post_status ) ), $job );
			break;
		endswitch;

		do_action( 'wp_job_board_pro_job_submit_done_content_after', sanitize_title( $job->post_status ), $job );
	?>
</div>

