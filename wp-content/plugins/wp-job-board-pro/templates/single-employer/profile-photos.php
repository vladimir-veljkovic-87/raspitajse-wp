<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($post->ID);

if ( $meta_obj->check_post_meta_exist('profile_photos') && ($profile_photos = $meta_obj->get_post_meta( 'profile_photos' )) ) {
?>
    <div id="job-employer-portfolio" class="employer-detail-portfolio">
    	<h4 class="title"><?php esc_html_e('Office Photos', 'wp-job-board-pro'); ?></h4>
        <?php foreach ($profile_photos as $attach_id => $img_url) { ?>
            <div class="photo-item">
            	<a href="<?php echo esc_url($img_url); ?>" class="popup-image">
                	<img src="<?php echo esc_url($img_url); ?>" alt="">
                </a>
            </div>
        <?php } ?>
    </div>
<?php }