<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;
?>
<div class="job-detail-header v6">
    <div class="flex-middle-sm">
        <?php
            superio_job_display_employer_logo($post, true, true);
        ?>
        <div class="inner">
            <?php the_title( '<h1 class="job-detail-title">', '</h1>' ); ?>
            <div class="info">
                <?php superio_job_display_employer_title($post,'no-icon','full'); ?>
                <?php superio_job_display_job_category($post,'no-title','full'); ?>
            </div>
        </div>
        <div class="ali-right action-top">
            <?php superio_job_display_featured_icon($post,'icon'); ?>
            <?php superio_job_display_urgent_icon($post,'icon'); ?>
            <?php WP_Job_Board_Pro_Job_Listing::display_shortlist_btn($post->ID); ?>
        </div>
    </div>
    <div class="job-metas-detail-bottom">
        <?php superio_job_display_job_type($post); ?>
        <?php superio_job_display_short_location($post); ?>
        <?php superio_job_display_postdate($post); ?>
        <?php superio_job_display_salary($post); ?>
    </div>
</div>