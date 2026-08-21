<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

?>
<div class="job-detail-map-location">
	<h4 class="title"><?php esc_html_e('Lokacija', 'superio'); ?></h4>
    <div id="jobs-google-maps" class="single-job-map"></div>
</div>