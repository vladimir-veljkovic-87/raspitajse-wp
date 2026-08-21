<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
extract( $args );

global $post;
if ( empty($post->post_type) || $post->post_type != 'candidate' ) {
    return;
}

if ( !WP_Job_Board_Pro_Candidate::check_restrict_view_contact_info($post) && wp_job_board_pro_get_option('restrict_contact_candidate_social', 'on') == 'on' ) {
    return;
}
$meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

if ( $meta_obj->check_post_meta_exist('socials') && ($socials = $meta_obj->get_post_meta( 'socials' )) ) {
    $all_socials = WP_Job_Board_Pro_Mixes::get_socials_network();
    extract( $args );
    extract( $instance );

    echo trim($before_widget);
    $title = apply_filters('widget_title', $instance['title']);

    if ( $title ) {
        echo trim($before_title)  . trim( $title ) . $after_title;
    }
    ?>

    <div class="job-detail-detail in-sidebar">
        <?php if ( $socials ) { ?>
        <div class="flex-middle">
            <div class="social-title"><?php echo esc_html__('Social Profiles:','superio'); ?> </div>
            <div class="apus-social-share ali-right">
                <?php foreach ($socials as $social) { ?>
                    <?php if ( !empty($social['url']) && !empty($social['network']) ) {
                        $icon_class = !empty( $all_socials[$social['network']]['icon'] ) ? $all_socials[$social['network']]['icon'] : '';
                    ?>
                        <a href="<?php echo esc_html($social['url']); ?>">
                            <i class="<?php echo esc_attr($icon_class); ?>"></i>
                        </a>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>

<?php echo trim($after_widget);
}