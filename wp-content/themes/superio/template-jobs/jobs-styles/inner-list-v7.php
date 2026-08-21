<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $post;

?>
<?php do_action( 'wp_job_board_pro_before_job_content', $post->ID ); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class('map-item layout-job job-list v7'); ?> <?php superio_job_item_map_meta($post); ?>>
    <div class="flex-middle top-info">
        <?php superio_job_display_employer_logo($post); ?>
        <div class="ali-right action-top">
            <?php superio_job_display_featured_icon($post,'icon'); ?>
            <?php superio_job_display_urgent_icon($post,'icon'); ?>
            <?php WP_Job_Board_Pro_Job_Listing::display_shortlist_btn($post->ID); ?>
        </div>
    </div>
    <div class="inner-left">
        <div class="job-list-content">
            <?php the_title( sprintf( '<h2 class="job-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
            <div class="info">
                <?php superio_job_display_employer_title($post,'no-icon','full'); ?>
                <?php superio_job_display_job_category($post,'no-title','full'); ?>
            </div>
        </div>
    </div>
    <div class="job-metas">
        <?php superio_job_display_job_type($post); ?>
        <?php superio_job_display_short_location($post); ?>
        <?php superio_job_display_salary($post); ?>
    </div>
    <?php superio_job_display_deadline($post, 'title'); ?>
</article><!-- #post-## -->
<?php do_action( 'wp_job_board_pro_after_job_content', $post->ID ); ?>