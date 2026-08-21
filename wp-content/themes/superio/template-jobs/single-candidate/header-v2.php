<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

?>
<div class="candidate-detail-header v2">
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