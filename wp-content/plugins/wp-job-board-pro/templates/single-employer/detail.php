<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;
$views = WP_Job_Board_Pro_Employer::get_post_meta($post->ID, 'views_count', true );
$user_id = WP_Job_Board_Pro_User::get_user_by_employer_id($post->ID);
$jobs = WP_Job_Board_Pro_Query::get_posts(array(
    'post_type' => 'job_listing',
    'post_status' => 'publish',
    'author' => $user_id,
    'fields' => 'ids'
));
$count_jobs = $jobs->post_count;

$address = WP_Job_Board_Pro_Employer::get_post_meta($post->ID, 'address', true );
$categories = get_the_terms( $post->ID, 'employer_category' );
$founded_date = WP_Job_Board_Pro_Employer::get_post_meta($post->ID, 'founded_date', true );

?>
<div class="employer-detail-detail">
    <h4><?php esc_html_e('Informacije o kompaniji', 'wp-job-board-pro'); ?></h4>
    <ul class="list">
        
        <?php if ( $views ) { ?>
            <li>
                <div class="icon">
                    <i class="flaticon-eye"></i>
                </div>
                <div class="details">
                    <div class="text"><?php esc_html_e('Pregleda', 'wp-job-board-pro'); ?></div>
                    <div class="value"><?php echo wp_kses_post($views); ?></div>
                </div>
            </li>
        <?php } ?>

        <?php if ( $count_jobs ) { ?>
            <li>
                <div class="icon">
                    <i class="flaticon-label"></i>
                </div>
                <div class="details">
                    <div class="text"><?php esc_html_e('Objavljenih poslova', 'wp-job-board-pro'); ?></div>
                    <div class="value"><?php echo wp_kses_post($count_jobs); ?></div>
                </div>
            </li>
        <?php } ?>

        <?php if ( $address ) { ?>
            <li>
                <div class="icon">
                    <i class="flaticon-paper-plane"></i>
                </div>
                <div class="details">
                    <div class="text"><?php esc_html_e('Lokacija', 'wp-job-board-pro'); ?></div>
                    <div class="value"><?php echo wp_kses_post($address); ?></div>
                </div>
            </li>
        <?php } ?>

        <?php if ( $categories ) { ?>
            <li>
                <div class="icon">
                    <i class="flaticon-2-squares"></i>
                </div>
                <div class="details">
                    <div class="text"><?php esc_html_e('Kategorije', 'wp-job-board-pro'); ?></div>
                    <div class="value">
                        <?php foreach ($categories as $term) { ?>
                            <a href="<?php echo get_term_link($term); ?>"><?php echo esc_html($term->name); ?></a>
                        <?php } ?>
                    </div>
                </div>
            </li>
        <?php } ?>

        <?php if ( $founded_date ) { ?>
            <li>
                <div class="icon">
                    <i class="flaticon-timeline"></i>
                </div>
                <div class="details">
                    <div class="text"><?php esc_html_e('Osnovano', 'wp-job-board-pro'); ?></div>
                    <div class="value"><?php echo wp_kses_post($founded_date); ?></div>
                </div>
            </li>
        <?php } ?>
    </ul>
</div>
