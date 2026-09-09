<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$limit = apply_filters('wp_job_board_pro_employer_limit_open_jobs', 3);

$user_id = WP_Job_Board_Pro_User::get_user_by_employer_id($post->ID);
$args = array(
    'post_type' => 'job_listing',
    'post_status' => 'publish',
    'posts_per_page' => $limit,
    'author' => $user_id
);
$jobs = new WP_Query( $args );

if( $jobs->have_posts() ):
    $jobs_url = WP_Job_Board_Pro_Mixes::get_jobs_page_url();
    $jobs_url = add_query_arg( 'filter-author', $user_id, remove_query_arg( 'filter-author', $jobs_url ) );
?>
    <div class="widget-open-jobs">
        <div class="top-info-widget flex-middle">
            <h4 class="title">
                <?php esc_html_e( 'Open Position', 'superio' ); ?>
            </h4>
            <div class="ali-right">
                <a href="<?php echo esc_url($jobs_url); ?>" class="text-theme view_all">
                    <?php esc_html_e('Browse Full List', 'superio'); ?> <i class="ti-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="widget-content">
            <?php
                while ( $jobs->have_posts() ) : $jobs->the_post();
                    ?>
                    <?php echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'jobs-styles/inner-list' ); ?>
                    <?php
                endwhile;
            ?>
            <?php wp_reset_postdata(); ?>
        </div>
    </div>
<?php endif; ?>