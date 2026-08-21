<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

if ( $meta_obj->check_post_meta_exist('video_url') && ($video_url = $meta_obj->get_post_meta( 'video_url' )) ) {
?>
    <div id="job-candidate-video" class="candidate-detail-video">
    	<h4 class="title"><?php esc_html_e('Video', 'superio'); ?></h4>
    	<div class="content-bottom">
	    	<?php
				if ( strpos($video_url, 'www.aparat.com') !== false ) {
				    $path = parse_url($video_url, PHP_URL_PATH);
					$matches = preg_split("/\/v\//", $path);
					
					if ( !empty($matches[1]) ) {
					    $output = '<iframe src="http://www.aparat.com/video/video/embed/videohash/'. $matches[1] . '/vt/frame"
					                allowFullScreen="true"
					                webkitallowfullscreen="true"
					                mozallowfullscreen="true"
					                height="720"
					                width="1280" >
					                </iframe>';

					    echo trim($output);
					}
			   	} else {
					echo apply_filters( 'the_content', '[embed width="1280" height="720"]' . esc_attr( $video_url ) . '[/embed]' );
				}
			?>
        </div>
    </div>
<?php }