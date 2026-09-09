<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;
?>
<div class="job-detail-description">
	<h3 class="title"><?php esc_html_e('Job Description', 'superio'); ?></h3>
	<?php the_content(); ?>

	<?php do_action('wp-job-board-pro-single-job-description', $post); ?>
</div>