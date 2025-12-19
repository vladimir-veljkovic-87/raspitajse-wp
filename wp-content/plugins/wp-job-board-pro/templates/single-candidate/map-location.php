<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

?>
<div class="candidate-detail-map-location">
	<h4 class="title"><?php esc_html_e('Location', 'wp-job-board-pro'); ?></h4>
    <div id="jobs-google-maps" class="single-job-map"></div>
</div>