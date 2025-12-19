<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

if ( $meta_obj->check_post_meta_exist('portfolio_photos') && ($portfolio_photos = $meta_obj->get_post_meta( 'portfolio_photos' )) ) {
?>
    <div id="job-candidate-portfolio" class="candidate-detail-portfolio">
    	<h4 class="title"><?php esc_html_e('Portfolio', 'wp-job-board-pro'); ?></h4>
        <?php foreach ($portfolio_photos as $attach_id => $img_url) { ?>
            <div class="education-item">
            	<a href="<?php echo esc_url($img_url); ?>" class="popup-image">
	                <img src="<?php echo esc_url($img_url); ?>" alt="">
	            </a>
            </div>
        <?php } ?>
    </div>
<?php }