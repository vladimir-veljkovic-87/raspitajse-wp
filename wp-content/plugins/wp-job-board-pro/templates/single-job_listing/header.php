<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($post->ID);
$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);

$address = WP_Job_Board_Pro_Job_Listing::get_post_meta( $post->ID, 'address', true );
$salary = WP_Job_Board_Pro_Job_Listing::get_salary_html($post->ID);

?>
<div class="job-detail-header">
    <?php if ( has_post_thumbnail($employer_id) ) { ?>
        <div class="employer-thumbnail">
            <a href="<?php echo esc_url(get_permalink($employer_id)); ?>">
                <?php echo get_the_post_thumbnail( $employer_id, 'thumbnail' ); ?>
            </a>
        </div>
    <?php } ?>
    <div class="job-information">
        <?php WP_Job_Board_Pro_Job_Listing::get_job_types_html($post->ID); ?>
        <?php the_title( '<h1 class="entry-title job-title">', '</h1>' ); ?>
        <div class="job-date-author">
            <?php echo sprintf(__(' posted %s ago', 'wp-job-board-pro'), human_time_diff(get_the_time('U'), current_time('timestamp')) ); ?> 
            <?php
            if ( $employer_id ) {
                echo sprintf(__('by %s', 'wp-job-board-pro'), get_the_title($employer_id));
            }
            ?>
        </div>
        <div class="job-metas">
            <?php if ( $address ) { ?>
                <div class="job-location"><?php echo wp_kses_post($address); ?></div>
            <?php } ?>
            <?php if ( $salary ) { ?>
                <div class="job-salary"><?php echo wp_kses_post($salary); ?></div>
            <?php } ?>
        </div>
    </div>

    <div class="job-detail-buttons">
        <?php WP_Job_Board_Pro_Job_Listing::display_apply_job_btn($post->ID); ?>
        <?php WP_Job_Board_Pro_Job_Listing::display_shortlist_btn($post->ID); ?>
    </div>
</div>