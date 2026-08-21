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
<div class="job-detail-header v7" <?php echo trim($style); ?>>
    <div class="container">
        <div class="row flex-middle-sm">
            <div class="col-md-8 col-sm-7 col-xs-12">
                <div class="flex-middle-sm">
                    <?php
                        superio_job_display_employer_logo($post, true, true);
                    ?>
                    <div class="inner">
                        <?php the_title( '<h1 class="job-detail-title">', '</h1>' ); ?>
                        <div class="info">
                            <?php superio_job_display_employer_title($post,'no-icon','full'); ?>
                            <?php superio_job_display_job_category($post,'no-title','full'); ?>
                        </div>
                    </div>
                    <div class="ali-right action-top">
                        <?php superio_job_display_featured_icon($post,'icon'); ?>
                        <?php superio_job_display_urgent_icon($post,'icon'); ?>
                        <?php WP_Job_Board_Pro_Job_Listing::display_shortlist_btn($post->ID); ?>
                    </div>
                </div>
                <div class="job-metas-detail-bottom">
                    <?php superio_job_display_job_type($post); ?>
                    <?php superio_job_display_short_location($post); ?>
                    <?php superio_job_display_postdate($post); ?>
                    <?php superio_job_display_salary($post); ?>
                </div>
            </div>
            <div class="col-md-1 hidden-xs hidden-sm"></div>
            <div class="job-detail-buttons col-md-3 col-sm-5 col-xs-12">
                <div class="action">
                    <?php WP_Job_Board_Pro_Job_Listing::display_apply_job_btn($post->ID); ?>
                </div>
            </div>
        </div>
    </div>
</div>