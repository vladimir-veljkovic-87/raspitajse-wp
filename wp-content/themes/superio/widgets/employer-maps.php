<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
extract( $args );

global $post;
if ( empty($post->post_type) || $post->post_type != 'employer' ) {
    return;
}
extract( $args );
extract( $instance );

echo trim($before_widget);
$title = apply_filters('widget_title', $instance['title']);

if ( $title ) {
    echo trim($before_title)  . trim( $title ) . $after_title;
}


?>
<div class="employer-detail-map-location job_maps_sidebar">
    <div id="jobs-google-maps" class="single-job-map"></div>
</div>
<?php echo trim($after_widget);