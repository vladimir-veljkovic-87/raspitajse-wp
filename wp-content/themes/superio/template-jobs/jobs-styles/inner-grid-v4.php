<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $post;

?>

<?php do_action( 'wp_job_board_pro_before_job_content', $post->ID ); ?>

<article <?php post_class('map-item layout-job job-grid-v4'); ?> <?php superio_job_item_map_meta($post); ?>>
    <div class="flex-middle top-info">
        <?php superio_job_display_job_first_category($post); ?>
        <div class="ali-right">
            <?php WP_Job_Board_Pro_Job_Listing::display_shortlist_btn($post->ID); ?>
        </div>
    </div>
    <?php the_title( sprintf( '<h2 class="job-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
    <div class="job-metas">
        <?php superio_job_display_short_location($post, 'icon'); ?>
        <?php superio_job_display_job_type($post,'icon'); ?>
    </div>
    <div class="info-bottom">
        <div class="flex-middle">
            <div class="flex-shrink-0">
                <?php superio_job_display_employer_logo($post); ?>
            </div>
            <div class="inner-right flex-grow-1">
                <?php superio_job_display_employer_title($post); ?>
                <?php superio_job_display_postdate($post); ?>
            </div>
        </div>
    </div>
</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_job_content', $post->ID ); ?>