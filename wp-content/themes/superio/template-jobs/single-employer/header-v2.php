<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($post->ID);
$cover_photo_url = $meta_obj->get_post_meta('cover_photo');
$style = '';
if ( !empty($cover_photo_url) ) {
    $style = 'style="background-image:url('.$cover_photo_url.');"';
}
?>
<div class="employer-detail-header v2 candidate-detail-header" <?php echo trim($style); ?>>
    <div class="container">
        <?php if ( has_post_thumbnail() ) { ?>
            <div class="candidate-thumbnail">
                <?php superio_employer_display_logo($post, false); ?>
            </div>
        <?php } ?>

        <div class="top-information">
            <div class="title-wrapper">
                <?php
                $title = superio_employer_name($post);
                ?>
                <h1 class="candidate-title">
                    <?php echo trim($title); ?>
                </h1>
                <?php superio_employer_display_featured_icon($post,'text'); ?>
            </div>

            <div class="employer-metas-bottom">
                <?php superio_employer_display_nb_jobs($post); ?>
            </div>
        </div>
    </div>
</div>