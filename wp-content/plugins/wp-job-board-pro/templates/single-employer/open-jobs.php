<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$limit = apply_filters('wp_job_board_pro_employer_limit_open_jobs', 3);

$user_id = WP_Job_Board_Pro_User::get_user_by_employer_id($post->ID);
$args = array(
    'post_type' => 'job_listing',
    'posts_per_page' => $limit,
    'author' => $user_id
);
$jobs = new WP_Query( $args );
if( $jobs->have_posts() ):
?>
    <div class="widget">
        <h4 class="widget-title">
            <span><?php esc_html_e( 'Open Position', 'wp-job-board-pro' ); ?></span>
        </h4>
        <div class="widget-content">
            <?php
                while ( $jobs->have_posts() ) : $jobs->the_post();
                    echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'jobs-styles/inner-list' );
                endwhile;
            ?>
            <?php wp_reset_postdata(); ?>
        </div>
    </div>
<?php endif; ?>