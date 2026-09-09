<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$meta_obj = WP_Job_Board_Pro_Job_Listing_Meta::get_instance($post->ID);

if ( $meta_obj->check_post_meta_exist('photos') && ($photos = $meta_obj->get_post_meta( 'photos' )) ) {
?>
    <div id="job-job-portfolio" class="job-detail-portfolio portfolio">
    	<h4 class="title"><?php esc_html_e('Photos', 'superio'); ?></h4>
    	<div class="content-bottom">
            <div class="row row-responsive row-portfolio">
                <?php $i=1; foreach ($photos as $attach_id => $img_url) {
                    $additional_class = '';
                    if ( $i > 4 ) {
                        $additional_class = 'hidden';
                    }
                    $more_image_class = $more_image_html = '';
                    if ( $i == 4 && count($photos) > 4 ) {
                        $more_image_html = '<div class="view-more-gallery">+'.(count($photos) - 4).'</div>';
                        $more_image_class = 'view-more-image';
                    }
                ?>
                    <div class="col-xs-3 item <?php echo esc_attr($additional_class); ?>">
                        <div class="education-item portfolio-item">
                            <div class="p-relative">
                                <a href="<?php echo esc_url($img_url); ?>" data-elementor-lightbox-slideshow="superio-gallery" class="popup-image-gallery <?php echo esc_attr($more_image_class); ?>">
                                    <?php echo superio_get_attachment_thumbnail($attach_id, '200x200'); ?>
                                </a>
                                <?php echo trim($more_image_html); ?>
                            </div>
                        </div>
                    </div>
                <?php $i++; } ?>
            </div>
        </div>
    </div>
<?php }