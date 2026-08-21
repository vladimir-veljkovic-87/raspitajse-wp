<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$author_id = superio_get_post_author($post->ID);
?>
<div class="job-detail-header v2">
    <div class="flex-middle-sm">
        <?php
            superio_job_display_employer_logo($post, true, true);
        ?>
        <div class="info-detail-job">
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
    </div>
</div>