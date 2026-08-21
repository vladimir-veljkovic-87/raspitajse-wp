<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $post;

$rating_avg = WP_Job_Board_Pro_Review::get_ratings_average($post->ID);

?>

<?php do_action( 'wp_job_board_pro_before_candidate_content', $post->ID ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class('map-item candidate-card'); ?> <?php superio_candidate_item_map_meta($post); ?>>
    <div class="candidate-list v2 candidate-archive-layout">
        <div class="flex-middle-sm">
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

                        <?php superio_candidate_display_featured_icon($post,'text'); ?>
                    </div>
                    <div class="clearfix">
                        <?php superio_candidate_display_categories($post); ?>
                        <?php superio_candidate_display_short_location($post, 'icon'); ?>
                    </div>
                </div>
            </div>
            <div class="ali-right">
                <a href="<?php the_permalink(); ?>" class="btn btn-theme-lighten"><?php esc_html_e('View Profile', 'superio'); ?></a>
            </div>
        </div>
    </div>
</article><!-- #post# -->
<?php do_action( 'wp_job_board_pro_after_candidate_content', $post->ID ); ?>