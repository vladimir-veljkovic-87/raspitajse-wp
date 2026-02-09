<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

if ( $meta_obj->check_post_meta_exist('education') && ($education = $meta_obj->get_post_meta( 'education' )) ) {
?>
    <div id="job-candidate-education" class="candidate-detail-education my_resume_eduarea">
        <h4 class="title"><?php esc_html_e('Obrazovanje', 'wp-job-board-pro'); ?></h4>
        <?php foreach ($education as $item) { ?>

            <div class="content">
                <div class="circle bgc-thm"></div>
                <p class="edu_center">
                    <?php if ( !empty($item['academy']) ) { ?>
                        <span class="university"><?php echo $item['academy']; ?></span>
                    <?php } ?>
                    <?php if ( !empty($item['year']) ) { ?>
                        <small class="year"><?php echo $item['year']; ?></small>
                    <?php } ?>
                </p>
                <?php if ( !empty($item['title']) ) { ?>
                    <h4 class="edu_stats"><?php echo $item['title']; ?></h4>
                <?php } ?>
                <?php if ( !empty($item['description']) ) { ?>
                    <p class="mb0"><?php echo $item['description']; ?></p>
                <?php } ?>
            </div>
            
        <?php } ?>
    </div>
<?php }