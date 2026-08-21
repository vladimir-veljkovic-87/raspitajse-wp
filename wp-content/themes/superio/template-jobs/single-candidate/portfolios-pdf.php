<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

if ( $meta_obj->check_post_meta_exist('portfolio_photos') && ($portfolio_photos = $meta_obj->get_post_meta( 'portfolio_photos' )) ) {
?>
    <div id="job-candidate-portfolio" class="candidate-detail-portfolio portfolio">
    	<h4 class="title"><?php esc_html_e('Portfolio', 'superio'); ?></h4>
    	<div class="content-bottom">
	    	<div class="row row-responsive row-portfolio">
		        <?php foreach ($portfolio_photos as $attach_id => $img_url) {
	        	?>
		            <div class="col-xs-3 item">
		            	<div class="education-item portfolio-item">
	            			<?php echo wp_get_attachment_image($attach_id, 'thumbnail'); ?>
		                </div>
		            </div>
		        <?php } ?>
	        </div>
        </div>
    </div>
<?php }