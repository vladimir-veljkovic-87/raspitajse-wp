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
<div class="employer-detail-header candidate-detail-header v1" <?php echo trim($style); ?>>
    <div class="container">
        <div class="flex-middle-sm row">
            <div class="col-xs-12 col-sm-8">  
                <div class="candidate-top-wrapper flex-middle-sm">
                    <?php if ( has_post_thumbnail() ) { ?>
                        <div class="candidate-thumbnail">
                            <?php superio_employer_display_logo($post, false); ?>
                        </div>
                    <?php } ?>

                    <div class="candidate-information">
                        <div class="title-wrapper">

                            <?php
                            $title = superio_employer_name($post);
                            ?>
                            <h1 class="candidate-title">
                                <?php echo trim($title); ?>
                            </h1>
                            <?php superio_employer_display_featured_icon($post,'text'); ?>
                        </div>

                        <div class="candidate-metas">
                            <?php superio_employer_display_category($post->ID, 'icon'); ?>
                            <?php superio_employer_display_short_location($post, 'icon'); ?>
                            <?php superio_employer_display_phone($post->ID, 'flaticon-phone'); ?>
                            <?php superio_employer_display_email($post->ID, 'icon'); ?>
                        </div>

                        <div class="candidate-metas-bottom">
                            <?php superio_employer_display_nb_jobs($post); ?>
                        </div>
                        
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="candidate-detail-buttons flex-middle justify-content-end-sm">
                    <?php
                        if ( superio_is_wp_private_message() ) {
                            $user_id = WP_Job_Board_Pro_User::get_user_by_employer_id($post->ID);
                            superio_private_message_form($post, $user_id);
                        }
                    ?>
                    <?php superio_employer_display_follow_btn($post->ID); ?>
                </div>
            </div>
        </div>
    </div>
</div>