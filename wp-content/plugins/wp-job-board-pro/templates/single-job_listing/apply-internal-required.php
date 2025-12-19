<?php

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

if ( !is_user_logged_in() || !WP_Job_Board_Pro_User::is_candidate() ) {
?>
	<div class="job-apply-internal-required-wrapper" style="display: none;">
		<div class="msg-inner"><?php esc_html_e('Please login as "Candidate" to apply', 'wp-job-board-pro'); ?></div>
	</div>
<?php }