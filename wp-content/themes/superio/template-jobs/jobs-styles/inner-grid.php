<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

?>

<?php do_action( 'wp_job_board_pro_before_job_content', $post->ID ); ?>

<article <?php post_class('map-item job-grid'); ?> <?php superio_job_item_map_meta($post); ?>>
    <div class="top-left">
        <?php superio_job_display_featured_icon($post, 'text'); ?>
        <?php superio_job_display_urgent_icon($post); ?>
    </div>
    <?php superio_job_display_job_type($post); ?>
    <?php superio_job_display_employer_logo($post); ?>
    <div class="job-information">
        
        <?php superio_job_display_employer_title($post); ?>

        <?php the_title( sprintf( '<h2 class="job-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

        <?php superio_job_display_short_location($post, 'icon'); ?>

	</div>
</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_job_content', $post->ID ); ?>