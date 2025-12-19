<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

if ( $meta_obj->check_post_meta_exist('award') && ($award = $meta_obj->get_post_meta( 'award' )) ) {
?>
    <div id="job-candidate-award" class="my_resume_eduarea">
        <h4 class="title"><?php esc_html_e('Awards', 'wp-job-board-pro'); ?></h4>
        <?php foreach ($award as $item) { ?>

            <div class="content">
                <div class="circle"></div>
                <?php if ( !empty($item['year']) ) { ?>
                    <div class="edu_center"><span class="year"><?php echo $item['year']; ?></span></div>
                <?php } ?>
                <?php if ( !empty($item['title']) ) { ?>
                    <h4 class="edu_stats"><?php echo $item['title']; ?></h4>
                <?php } ?>
                <?php if ( !empty($item['description']) ) { ?>
                    <div class="mb0"><?php echo $item['description']; ?></div>
                <?php } ?>
            </div>

        <?php } ?>
    </div>
<?php }