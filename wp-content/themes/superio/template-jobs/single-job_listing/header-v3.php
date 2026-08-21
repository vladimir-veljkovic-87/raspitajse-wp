<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;
?>
<div class="job-detail-header v3">
    
    <div class="title-wrapper flex-middle-sm">
        <?php the_title( '<h1 class="job-detail-title">', '</h1>' ); ?>
        <?php superio_job_display_featured_icon($post); ?>
        <?php superio_job_display_filled_label($post); ?>
    </div>
    <div class="job-metas-detail">
        <?php superio_job_display_job_category($post, 'icon'); ?>
        <?php superio_job_display_short_location($post, 'icon'); ?>
        <?php superio_job_display_postdate($post, 'icon'); ?>
        <?php superio_job_display_salary($post, 'icon'); ?>
    </div>
    <div class="job-metas-detail-bottom">
        <?php superio_job_display_job_type($post); ?>
        <?php superio_job_display_urgent_icon($post); ?>
    </div>

</div>