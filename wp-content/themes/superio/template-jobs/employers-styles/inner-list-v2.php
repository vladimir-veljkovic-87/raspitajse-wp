<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

?>

<?php do_action( 'wp_job_board_pro_before_employer_content', $post->ID ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class('map-item employer-card'); ?> <?php superio_employer_item_map_meta($post); ?>>
    <div class="employer-list layout-employer v2">
        <?php superio_employer_display_follow_btn($post->ID); ?>
        <div class="flex-bottom-sm">
            <div class="inner-left">
                <?php if ( has_post_thumbnail() ) { ?>
                    <?php superio_employer_display_logo($post); ?>
                <?php } ?>
                <div class="info-employer">
                    <div class="title-wrapper">
                        
                        <?php
                        $title = superio_employer_name($post);
                        ?>
                        <h2 class="employer-title">
                            <a href="<?php the_permalink(); ?>" rel="bookmark">
                                <?php echo trim($title); ?>
                            </a>
                        </h2>
                        <?php superio_employer_display_featured_icon($post, 'text'); ?>
                    </div>
                    <div class="employer-metas employer-map-metas">
                        <?php superio_employer_display_short_location($post,'icon'); ?>
                        <?php superio_employer_display_category($post->ID,'icon'); ?>
                    </div>
                </div>
            </div>
            <div class="ali-right">
                
                <?php superio_employer_display_open_position($post, true); ?>
            </div>
        </div>
    </div>
</article><!-- #post-## -->
<?php do_action( 'wp_job_board_pro_after_employer_content', $post->ID ); ?>