<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="candidate-rss-btn margin-left-15">
	<a class="rss-feed-url" href="<?php echo esc_url(WP_Job_Board_Pro_Candidate::candidate_feed_url(null, array('submit', 'paged'), '', '', true )); ?>" target="_blank">
		<i class="fas fa-rss-square"></i>
		<?php esc_html_e('RSS Feed', 'wp-job-board-pro'); ?>
	</a>
</div>