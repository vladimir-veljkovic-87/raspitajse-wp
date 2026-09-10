<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;
?>
<?php if ( superio_get_config('show_employer_description', true) && $post->post_content ) { ?>
	<div class="employer-detail-description">
		<h3 class="title"><?php esc_html_e('About Company', 'superio'); ?></h3>
		<div class="inner">
			<?php the_content(); ?>

			<?php do_action('wp-job-board-pro-single-employer-description', $post); ?>
		</div>
	</div>
<?php } ?>