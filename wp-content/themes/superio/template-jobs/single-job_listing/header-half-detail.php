<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$author_id = superio_get_post_author($post->ID);
?>
<div class="job-detail-header v-half-detail">
    <div class="flex">
        <?php
            superio_job_display_employer_logo($post, true, true);
        ?>
        <div class="middle">
            <?php the_title( sprintf( '<h2 class="job-detail-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
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
    <div class="flex-middle-sm half-detail-detail-bottom">
        <div class="flex-middle">
            <?php superio_job_display_job_type($post); ?>
            <?php superio_job_display_short_location($post); ?>
        </div>
        <div class="ali-right">
            <?php WP_Job_Board_Pro_Job_Listing::display_apply_job_btn($post->ID); ?>
        </div>
    </div>
</div>