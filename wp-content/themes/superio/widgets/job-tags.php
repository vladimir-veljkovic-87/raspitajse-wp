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

$tags = superio_job_display_tags($post, 'no-title', false);

if ( empty($tags) ) {
	return;
}

extract( $args );
extract( $instance );

echo trim($before_widget);
$title = apply_filters('widget_title', $instance['title']);

if ( $title ) {
    echo trim($before_title)  . trim( $title ) . $after_title;
}
$style = (!empty($style)) ? $style : '' ;
?>
<div class="job-detail-tags <?php echo esc_attr($style); ?>">
    <?php echo trim($tags); ?>
</div>

<?php echo trim($after_widget);