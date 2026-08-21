<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);

if ( $meta_obj->check_post_meta_exist('experience') && ($experience = $meta_obj->get_post_meta( 'experience' )) ) {
    $source_pdf = isset($source_pdf) ? $source_pdf : false;
?>
    <div id="job-candidate-experience" class="candidate-detail-experience my_resume_eduarea color-blue">
        <h4 class="title"><?php esc_html_e('Work &amp; Experience', 'superio'); ?></h4>
        <?php foreach ($experience as $item) { ?>
            <div class="content">
                <div class="circle">
                    <?php if ( !empty($item['title']) ) {
                        echo mb_substr(trim($item['title']), 0, 1);
                    } ?>
                </div>
                <div class="top-info">
                    <?php if ( !empty($item['title']) ) { ?>
                        <span class="edu_stats"><?php echo esc_html($item['title']); ?></span>
                    <?php } ?>
                    
                    <?php
                    if ( version_compare(WP_JOB_BOARD_PRO_PLUGIN_VERSION, '1.2.48', '>=') ) {
                        if ( !empty($item['start_date']) || !empty($item['end_date']) ) { ?>
                            <span class="year">
                                <?php if ( !empty($item['start_date']) ) { ?>
                                    <?php echo trim($item['start_date']); ?>
                                <?php } ?>
                                <?php if ( !empty($item['end_date']) ) { ?>
                                    - <?php echo trim($item['end_date']); ?>
                                <?php } ?>
                            </span>
                        <?php }
                    } else {
                        if ( !empty($item['start_date']) || !empty($item['end_date']) ) { ?>
                            <span class="year">
                                <?php if ( !empty($item['start_date']) ) {
                                    $date = strtotime($item['start_date']);
                                ?>
                                    <?php echo date_i18n(get_option('date_format'), $date); ?>
                                <?php } ?>
                                <?php if ( !empty($item['end_date']) ) {
                                    $date = strtotime($item['end_date']);
                                ?>
                                    - <?php echo date_i18n(get_option('date_format'), $date); ?>
                                <?php } ?>
                            </span>
                        <?php } ?>
                    <?php } ?>

                </div>
                <div class="edu_center">
                    <?php if ( !empty($item['company']) ) { ?>
                        <span class="university"><?php echo esc_html($item['company']); ?></span>
                    <?php } ?>
                </div>
                <?php if ( !empty($item['description']) ) { ?>
                    <p class="mb0"><?php echo esc_html($item['description']); ?></p>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
<?php }