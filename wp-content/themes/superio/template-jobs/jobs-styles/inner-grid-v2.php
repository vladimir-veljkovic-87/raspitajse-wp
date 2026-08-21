<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $post;

?>

<?php do_action( 'wp_job_board_pro_before_job_content', $post->ID ); ?>

<article <?php post_class('map-item job-grid-v2'); ?> <?php superio_job_item_map_meta($post); ?>>
    <?php superio_job_display_job_first_category($post, 'icon'); ?>
    <?php the_title( sprintf( '<h2 class="job-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
    <div class="job-information">
        <?php superio_job_display_short_location($post, 'icon'); ?>
        <?php superio_job_display_job_type($post); ?>
    </div>
    <div class="info-bottom flex-middle">
        <div class="inner-left">
            <div class="date">
                <?php the_time(get_option('date_format')); ?>
            </div>
            <?php superio_job_display_employer_title($post); ?>
        </div>
        <div class="ali-right">
            <?php superio_job_display_employer_logo($post); ?>
        </div>
    </div>
</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_job_content', $post->ID ); ?>