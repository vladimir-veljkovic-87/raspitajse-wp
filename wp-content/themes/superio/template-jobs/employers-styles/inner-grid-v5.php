<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

?>

<?php do_action( 'wp_job_board_pro_before_employer_content', $post->ID ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class('map-item employer-card'); ?> <?php superio_employer_item_map_meta($post); ?>>
    <div class="employer-grid v5 layout-employer">
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
        <div class="employer-metas">
            <?php superio_employer_display_short_location($post, 'icon'); ?>
            <?php superio_employer_display_meta($post, 'company_size', 'flaticon-user', false, '', true); ?>
        </div>
        <?php if ( !empty( get_the_content() ) ){  ?>
            <div class="des">
                <?php echo trim(superio_substring( get_the_content(),8, '' )); ?>
            </div>
        <?php } ?>
        <?php superio_employer_display_open_position($post, true); ?>
    </div>
</article><!-- #post-## -->

<?php do_action( 'wp_job_board_pro_after_employer_content', $post->ID ); ?>