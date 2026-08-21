<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
extract( $args );

global $post, $job_preview;
if ( $job_preview ) {
    $post = $job_preview;
}
if ( empty($post->post_type) || $post->post_type != 'job_listing' ) {
    return;
}
extract( $args );
extract( $instance );

echo trim($before_widget);
$title = apply_filters('widget_title', $instance['title']);

if ( $title ) {
    echo trim($before_title)  . trim( $title ) . $after_title;
}

$author_id = superio_get_post_author($post->ID);

$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);
if ( empty($employer_id) ) {
    return;
}

$emp_post = get_post($employer_id);
$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($employer_id);


$style = (!empty($style)) ? $style : '' ;
?>
<div class="job-detail-employer-info">
    <div class="job-employer-header">
        <?php if ( has_post_thumbnail($employer_id) ) { ?>
            <div class="employer-thumbnail">
                <?php echo get_the_post_thumbnail( $employer_id, 'thumbnail' ); ?>
            </div>
        <?php } ?>

        <div class="employer-links">
            <?php superio_job_display_employer_title($post); ?>
            <a class="more-link" href="<?php echo get_permalink($employer_id); ?>"><?php esc_html_e('View Company Profile', 'superio'); ?></a>
        </div>
    </div>
    <?php if($style == 'style1') { ?>
        <?php superio_employer_display_category($employer_id, 'title'); ?>
        <?php superio_employer_display_meta($emp_post, 'founded_date', '', true, '', true); ?>
        <?php superio_employer_display_short_location($emp_post, 'title', true); ?>
        <?php superio_employer_display_phone($emp_post, '', true); ?>
        <?php superio_employer_display_email($emp_post, 'title'); ?>

        <?php
        if ( $meta_obj->check_post_meta_exist('socials') && (WP_Job_Board_Pro_Employer::check_restrict_view_contact_info($post) || wp_job_board_pro_get_option('restrict_contact_employer_social', 'on') !== 'on') ) {
            $socials = $meta_obj->get_post_meta('socials');
            if ( $socials ) {
                $all_socials = WP_Job_Board_Pro_Mixes::get_socials_network();
            ?>  
                <div class="social-employer">
                    <h3 class="title"><?php echo trim($meta_obj->get_post_meta_title('socials')); ?>:</h3>
                    <div class="value">
                        <?php foreach ($socials as $social) {
                            if ( !empty($social['network']) && !empty($social['url']) ) {
                                $icon_class = !empty( $all_socials[$social['network']]['icon'] ) ? $all_socials[$social['network']]['icon'] : '';
                        ?>
                            <a href="<?php echo esc_url($social['url']); ?>"><i class="<?php echo esc_attr($icon_class); ?>"></i></a>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>

        <?php do_action('wp-job-board-pro-single-job-employer-info', $post); ?>

        <?php
        if ( WP_Job_Board_Pro_Employer::check_restrict_view_contact_info($emp_post) || wp_job_board_pro_get_option('restrict_contact_employer_website', 'on') !== 'on' ) {
            $website = $meta_obj->get_post_meta('website');
            if ( $website ) { ?>
                <div class="employer-website">
                    <a href="<?php echo esc_url($website); ?>" class="btn btn-theme-light btn-block"><?php echo trim($website); ?></a>
                </div>
            <?php } ?>
        <?php } ?>
    <?php } else { ?>
        <?php do_action('wp-job-board-pro-single-job-employer-info', $post); ?>
    <?php } ?>
</div>
<?php echo trim($after_widget);