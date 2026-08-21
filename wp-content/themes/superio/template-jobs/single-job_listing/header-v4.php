<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$author_id = superio_get_post_author($post->ID);
?>
<?php 
    if(has_post_thumbnail()){
        $img_bg_src = wp_get_attachment_image_url( get_post_thumbnail_id( $post->ID ), 'full' );
        $style = 'style="background-image:url('.esc_url($img_bg_src).')"';
    }else{
        $style = '';
    }
?>
<div class="job-detail-header v4" <?php echo trim($style); ?>>
    <div class="container">
        <div class="clearfix">
            <?php
                superio_job_display_employer_logo($post, true, true);
            ?>
            <div class="info-detail-job-v4">
                <div class="title-wrapper">
                    <?php the_title( '<h1 class="job-detail-title">', '</h1>' ); ?>
                    <?php superio_job_display_featured_icon($post); ?>
                    <?php superio_job_display_filled_label($post); ?>
                </div>
                <div class="job-metas-detail">
                    <?php superio_job_display_job_category($post, 'icon'); ?>
                    <?php superio_job_display_short_location($post, 'icon'); ?>
                    <?php superio_job_display_postdate($post, 'icon'); ?>
                    <?php superio_job_display_salary($post, 'icon'); ?>
                </div>
                <div class="job-metas-detail-bottom">
                    <?php superio_job_display_job_type($post); ?>
                    <?php superio_job_display_urgent_icon($post); ?>
                    
                </div>
            </div>
        </div>
        <div class="job-detail-buttons">
            <div class="action">
                <?php WP_Job_Board_Pro_Job_Listing::display_apply_job_btn($post->ID); ?>
                <?php WP_Job_Board_Pro_Job_Listing::display_shortlist_btn($post->ID); ?>
            </div>
        </div>
    </div>
</div>