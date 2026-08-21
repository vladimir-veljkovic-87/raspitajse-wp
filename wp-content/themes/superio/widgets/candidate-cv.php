<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
extract( $args );

global $post;
if ( empty($post->post_type) || $post->post_type != 'candidate' ) {
    return;
}

if ( !WP_Job_Board_Pro_Candidate::check_restrict_view_contact_info($post) && wp_job_board_pro_get_option('restrict_contact_candidate_download_cv', 'on') == 'on' ) {
    return;
}

$cv_attachment = WP_Job_Board_Pro_Candidate::get_display_cv_download( $post );
if ( empty($cv_attachment) ) {
    return;
}

extract( $args );
extract( $instance );

echo trim($before_widget);
$title = apply_filters('widget_title', $instance['title']);

if ( $title ) {
    echo trim($before_title)  . trim( $title ) . $after_title;
}

$download_base_url = WP_Job_Board_Pro_Ajax::get_endpoint('wp_job_board_pro_ajax_download_cv');
if ( is_array($cv_attachment) ) { ?>
    <div id="candidate-cv" class="candidate-cv">
    <?php foreach ($cv_attachment as $id => $cv_url) {
        $file_info = pathinfo($cv_url);
        if ( $file_info ) {
            $check_download = WP_Job_Board_Pro_Candidate::check_user_can_download_cv($id);
            $classes = 'cannot-download-cv-btn';
            $download_url = 'javascript:void(0);';
            if ( $check_download ) {
                $download_url = add_query_arg(array('file_id' => $id), $download_base_url);
                $classes = '';
            }
        ?>
            <a href="<?php echo trim($download_url); ?>" class="candidate-detail-cv <?php echo esc_attr($classes); ?>" data-msg="<?php esc_attr_e('Please login as employer account to download CV', 'superio'); ?>">
                <span class="icon_type">
                    <i class="flaticon-file"></i>
                </span>
                <?php if ( !empty($file_info['filename']) ) { ?>
                    <div class="filename"><?php echo esc_html($file_info['filename']); ?></div>
                <?php } ?>
                <?php if ( !empty($file_info['extension']) ) { ?>
                    <div class="extension"><?php echo esc_html($file_info['extension']); ?></div>
                <?php } ?>
            </a>
        <?php }
    }?>
    </div>
<?php 
}

echo trim($after_widget);