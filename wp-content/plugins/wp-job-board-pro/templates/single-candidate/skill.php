<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

if ( $meta_obj->check_post_meta_exist('skill') && ($skill = $meta_obj->get_post_meta( 'skill' )) ) {
?>
    <div id="job-candidate-skill" class="candidate-detail-skill candidate_resume_skill">
        <h4 class="title"><?php esc_html_e('Skills', 'wp-job-board-pro'); ?></h4>
        <div class="progress-levels">
            <?php $i=1; foreach ($skill as $item) {
                $delay = $i*100;
            ?>
                <div class="progress-box wow animated" data-wow-delay="<?php echo esc_attr($delay); ?>ms" data-wow-duration="1500ms">

                    <?php if ( !empty($item['title']) ) { ?>
                        <h5 class="box-title"><?php echo $item['title']; ?></h5>
                    <?php } ?>
                    
                    <?php if ( !empty($item['percentage']) ) { ?>
                        <div class="inner">
                            <div class="bar">
                                <div class="bar-innner"><div class="bar-fill ulockd-bgthm" data-percent="<?php echo esc_attr($item['percentage']); ?>" style="width: <?php echo trim($item['percentage']); ?>%;"><div class="percent"><?php echo esc_html($item['percentage']); ?>%</div></div></div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php $i++; } ?>
        </div>
    </div>
<?php }