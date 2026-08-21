<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $post;
$title = superio_employer_name($post);
?>

<?php do_action( 'wp_job_board_pro_before_employer_content', $post->ID ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class('map-item employer-card'); ?> <?php superio_employer_item_map_meta($post); ?>>
    <div class="employer-grid-v4 layout-employer">
        <div class="flex-middle">
            <?php superio_employer_display_logo($post); ?>
            <div class="inner flex-grow-1">
                <h2 class="employer-title">
                    <a href="<?php the_permalink(); ?>" rel="bookmark">
                        <?php echo trim($title); ?>
                    </a>
                </h2>
                <?php superio_employer_display_open_position($post, true); ?>
            </div>
        </div>
        <div class="employer-metas">
            <?php superio_employer_display_category($post,'icon'); ?>
            <?php superio_employer_display_short_location($post, 'icon'); ?>
            <?php superio_employer_display_meta($post, 'company_size', 'flaticon-user', false, '', true); ?>
        </div>
        <?php if ( !empty( get_the_content() ) ){  ?>
            <div class="des">
                <?php echo trim(superio_substring( get_the_content(),16, '' )); ?>
            </div>
        <?php } ?>
        <?php superio_employer_display_follow_btn($post->ID); ?>
    </div>
</article><!-- #post-## -->
<?php do_action( 'wp_job_board_pro_after_employer_content', $post->ID ); ?>