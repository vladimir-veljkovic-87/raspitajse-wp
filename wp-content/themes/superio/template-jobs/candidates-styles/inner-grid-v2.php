<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $post;

$rating_avg = WP_Job_Board_Pro_Review::get_ratings_average($post->ID);

?>

<?php do_action( 'wp_job_board_pro_before_candidate_content', $post->ID ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class('map-item candidate-card'); ?> <?php superio_candidate_item_map_meta($post); ?>>
    <div class="candidate-list v4 candidate-archive-layout">
        <div class="flex-middle">
            <?php superio_candidate_display_logo($post); ?>
            <div class="candidate-info-content">
                <div class="title-wrapper">
                    <?php
                    $title = superio_candidate_name($post);
                    ?>
                    <h2 class="candidate-title">
                        <a href="<?php the_permalink(); ?>" rel="bookmark">
                            <?php echo trim($title) ?>
                        </a>
                    </h2>

                    <?php superio_candidate_display_featured_icon($post,'icon'); ?>
                </div>
                <div class="info-top">
                    <?php superio_candidate_display_categories($post); ?>
                </div>
            </div>
        </div>
        <div class="info-middle">
            <?php superio_candidate_display_short_location($post, 'icon'); ?>
            <?php superio_candidate_display_salary($post, 'icon'); ?>
        </div>
        <?php if ( !empty( get_the_content() ) ){  ?>
            <div class="des hidden-xs hidden-sm">
                <?php echo trim(superio_substring( get_the_content(),16, '' )); ?>
            </div>
        <?php } ?>
        <?php superio_candidate_display_tags_version2($post); ?>
        <a href="<?php the_permalink(); ?>" class="btn">
            <?php esc_html_e('View Profile', 'superio'); ?>
            <svg class="next" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" style="fill: transparent;"><path d="M15.8335 2.9165L4.16683 14.5832" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15.8335 11.4748V2.9165H7.27516" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path></svg>
        </a>
    </div>
</article><!-- #post# -->
<?php do_action( 'wp_job_board_pro_after_candidate_content', $post->ID ); ?>