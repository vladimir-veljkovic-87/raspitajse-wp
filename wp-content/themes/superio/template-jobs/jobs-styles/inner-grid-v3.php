<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $post;

?>

<?php do_action( 'wp_job_board_pro_before_job_content', $post->ID ); ?>

<article <?php post_class('map-item layout-job job-grid-v3'); ?> <?php superio_job_item_map_meta($post); ?>>
    <div class="flex-middle">
        <?php the_title( sprintf( '<h2 class="job-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
        <div class="ali-right">
            <?php WP_Job_Board_Pro_Job_Listing::display_shortlist_btn($post->ID); ?>
        </div>
    </div>
    <div class="job-metas">
        <?php superio_job_display_short_location($post, 'icon'); ?>
        <?php superio_job_display_job_type($post,'icon'); ?>
    </div>
    <?php superio_job_display_salary($post, 'icon'); ?>
    <div class="info-bottom flex-middle">
        <div class="clearfix">
            <div class="flex-middle">
                <div class="flex-shrink-0">
                    <?php superio_job_display_employer_logo($post); ?>
                </div>
                <div class="inner-left flex-grow-1">
                    <?php superio_job_display_employer_title($post); ?>
                    <?php superio_job_display_postdate($post); ?>
                </div>
            </div>
        </div>
        <div class="ali-right">
            <a class="btn" href="<?php the_permalink(); ?>"><?php esc_html_e('Apply Now','superio'); ?></a>
        </div>
    </div>
</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_job_content', $post->ID ); ?>