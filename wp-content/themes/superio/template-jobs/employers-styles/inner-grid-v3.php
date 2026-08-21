<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

?>

<?php do_action( 'wp_job_board_pro_before_employer_content', $post->ID ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class('map-item employer-card'); ?> <?php superio_employer_item_map_meta($post); ?>>
    <div class="employer-grid v3 layout-employer">
        <?php superio_employer_display_follow_btn($post->ID); ?>
        <?php superio_employer_display_logo($post); ?>
        
        <?php
        $title = superio_employer_name($post);
        ?>
        <h2 class="employer-title">
            <a href="<?php the_permalink(); ?>" rel="bookmark">
                <?php echo trim($title); ?>
            </a>
        </h2>
    </div>
</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_employer_content', $post->ID ); ?>