<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$cv_attachments = WP_Job_Board_Pro_Candidate::get_post_meta($post->ID, 'cv_attachment', true );

if ( !empty($cv_attachments) ) {
?>
    <div class="candidate-detail-cv-attachments">
        
        <a href="<?php echo esc_url($url); ?>"><?php esc_html_e('Download CV', 'wp-job-board-pro'); ?></a>
        
    </div>
<?php }