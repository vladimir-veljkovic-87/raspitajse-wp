<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
extract( $args );

global $post, $job_preview;
if ( $job_preview ) {
    $post = $job_preview;
}
if ( empty($post->post_type) || $post->post_type != 'job_listing' ) {
    return;
}
$latitude = WP_Job_Board_Pro_Job_Listing::get_post_meta( $post->ID, 'map_location_latitude', true );
$longitude = WP_Job_Board_Pro_Job_Listing::get_post_meta( $post->ID, 'map_location_longitude', true );

if ( !empty($latitude) && !empty($longitude) ) {
	extract( $args );
	extract( $instance );

	echo trim($before_widget);
	$title = apply_filters('widget_title', $instance['title']);

	if ( $title ) {
	    echo trim($before_title)  . trim( $title ) . $after_title;
	}

	?>
	<div class="job-detail-map-location job_maps_sidebar">
	    <div id="jobs-google-maps" class="single-job-map"></div>
	</div>
	<?php echo trim($after_widget);
}