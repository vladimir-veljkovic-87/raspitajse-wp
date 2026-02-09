<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

if ( $meta_obj->check_post_meta_exist('experience') && ($experience = $meta_obj->get_post_meta( 'experience' )) ) {
?>
    <div id="job-candidate-experience" class="candidate-detail-experience my_resume_eduarea">
        <h4 class="title"><?php esc_html_e('Radno iskustvo', 'wp-job-board-pro'); ?></h4>
        <?php foreach ($experience as $item) { ?>
            <div class="content">
                <div class="circle bgc-thm"></div>
                <p class="mb0">
                    <?php if ( !empty($item['company']) ) { ?>
                        <strong class="edu_center"><?php echo $item['company']; ?></strong>
                    <?php } ?>

                    <?php if ( !empty($item['start_date']) || !empty($item['end_date']) ) { ?>
                        <small class="start_date">
                            <?php if ( !empty($item['start_date']) ) { ?>
                                <?php echo $item['start_date']; ?>
                            <?php } ?>
                            <?php if ( !empty($item['end_date']) ) { ?>
                                - <?php echo $item['end_date']; ?>
                            <?php } ?>
                        </small>
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