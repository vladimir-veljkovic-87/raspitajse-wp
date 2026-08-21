<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;
?>
<?php if ( superio_get_config('show_candidate_description', true) ) { ?>
	<div id="job-candidate-description" class="job-detail-description box-detail">
		<h3 class="title"><?php esc_html_e('About Candidate', 'superio'); ?></h3>
		<div class="inner">
			<?php the_content(); ?>

			<?php do_action('wp-job-board-pro-single-candidate-description', $post); ?>
		</div>
	</div>
<?php } ?>