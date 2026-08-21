<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
extract( $args );

global $post;
if ( empty($post->post_type) || $post->post_type != 'candidate' ) {
    return;
}

extract( $args );
extract( $instance );

echo trim($before_widget);

$title = apply_filters('widget_title', $instance['title']);

if ( $title ) {
    echo trim($before_title)  . trim( $title ) . $after_title;
}

?>
<div id="candidate-buttons" class="candidate-buttons">
    <?php
    
    if ( $show_cv_button ) {
        if ( WP_Job_Board_Pro_Candidate::check_restrict_view_contact_info($post) || wp_job_board_pro_get_option('restrict_contact_candidate_download_cv', 'on') !== 'on' ) {
            WP_Job_Board_Pro_Candidate::display_download_cv_btn($post->ID, 'btn btn-download-cv');
        }
    }
    if ( $show_invite_button ) {
        WP_Job_Board_Pro_Candidate::display_invite_btn($post->ID);
    }
    if ( $show_shortlist_button ) {
        WP_Job_Board_Pro_Candidate::display_shortlist_btn($post->ID);
    }
    ?>
</div>
<?php echo trim($after_widget);