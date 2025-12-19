<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$categories = get_the_terms( $post->ID, 'employer_category' );
$website = WP_Job_Board_Pro_Employer::get_post_meta( $post->ID, 'website', true );
$phone = WP_Job_Board_Pro_Employer::get_post_meta( $post->ID, 'phone', true );
$email = WP_Job_Board_Pro_Employer::get_post_meta( $post->ID, 'email', true );

$author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($post->ID);
$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);

?>
<div class="employer-detail-header">
    <?php if ( has_post_thumbnail() ) { ?>
        <div class="employer-thumbnail">
            <?php echo get_the_post_thumbnail( $post->ID, 'thumbnail' ); ?>
        </div>
    <?php } ?>
    <div class="employer-information">
        <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ){ ?>
            <?php foreach ($categories as $term) { ?>
                <a href="<?php echo get_term_link($term); ?>"><?php echo $term->name; ?></a>
            <?php } ?>
        <?php } ?>
        <?php the_title( '<h1 class="entry-title employer-title">', '</h1>' ); ?>
        <div class="employer-date-author">
            <?php echo sprintf( __(' posted %s ago', 'wp-job-board-pro'), human_time_diff(get_the_time('U'), current_time('timestamp')) ); ?> 
            <?php
            if ( $employer_id ) {
                echo sprintf(__('by %s', 'wp-job-board-pro'), get_the_title($employer_id));
            }
            ?>
        </div>
        <div class="employer-metas">
            <?php if ( $website ) { ?>
                <div class="employer-website"><?php echo $website; ?></div>
            <?php } ?>
            <?php if ( $phone ) { ?>
                <div class="employer-phone"><?php echo $phone; ?></div>
            <?php } ?>
            <?php if ( $email ) { ?>
                <div class="employer-email"><?php echo $email; ?></div>
            <?php } ?>
        </div>
    </div>

    <div class="employer-detail-buttons">
        <a href="javascript:void(0)" class="btn button follow-us-btn"><?php esc_html_e('Follow us', 'wp-job-board-pro'); ?></a>
        <a href="#review_form_wrapper" class="btn button add-a-review"><?php esc_html_e('Add a review', 'wp-job-board-pro'); ?></a>
    </div>
</div>