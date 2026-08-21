<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;

$tags = superio_job_display_tags($post, 'no-title', false);

if ( !empty($tags) ) {
?>
    <div id="job-job-tags" class="job-detail-tags">
        <h4 class="title"><?php esc_html_e('Tags', 'superio'); ?></h4>
        <div class="content-bottom">
            <?php echo trim($tags); ?>
        </div>
    </div>
<?php }