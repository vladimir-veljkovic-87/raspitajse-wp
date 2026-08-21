<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

?>
<div class="candidate-detail-header v1">
    <div class="container">
        <div class="flex-middle-sm row">
            <div class="col-xs-12 col-sm-8">
                <div class="candidate-top-wrapper flex-middle-sm">
                    <?php if ( has_post_thumbnail() ) { ?>
                        <div class="candidate-thumbnail">
                            <?php superio_candidate_display_logo($post, false); ?>
                        </div>
                    <?php } ?>
                    <div class="candidate-information">
                        <div class="title-wrapper">
                            <?php
                            $title = superio_candidate_name($post);
                            ?>
                            <h1 class="candidate-title">
                                <?php echo trim($title) ?>
                            </h1>

                            <?php superio_candidate_display_featured_icon($post,'text'); ?>
                        </div>
                        <div class="candidate-metas">
                            <?php superio_candidate_display_job_title($post); ?>
                            <?php superio_candidate_display_short_location($post, 'icon'); ?>
                            <?php superio_candidate_display_salary($post, 'icon'); ?>
                            <?php superio_candidate_display_birthday($post, 'icon'); ?>
                        </div>
                        <div class="candidate-metas-bottom">
                            <?php superio_candidate_display_tags($post); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="candidate-detail-buttons flex-middle justify-content-end-sm">
                    <?php
                    if ( WP_Job_Board_Pro_Candidate::check_restrict_view_contact_info($post) || wp_job_board_pro_get_option('restrict_contact_candidate_download_cv', 'on') !== 'on' ) {
                        WP_Job_Board_Pro_Candidate::display_download_cv_btn($post->ID);
                    }
                    ?>
                    <?php superio_candidate_show_invite($post->ID); ?>
                    <?php WP_Job_Board_Pro_Candidate::display_shortlist_btn($post->ID); ?>
                </div>
            </div>
        </div>
    </div>
</div>