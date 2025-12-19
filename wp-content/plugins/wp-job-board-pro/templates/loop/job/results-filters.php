<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !empty($filters) ) {
	?>
	<div class="results-filter-wrapper">
		<h3 class="title"><?php esc_html_e('Vaši odabrani filteri', 'wp-job-board-pro'); ?></h3>
		<div class="inner">
			<ul class="results-filter">
				<?php foreach ($filters as $key => $value) { ?>
					<?php WP_Job_Board_Pro_Job_Filter::display_filter_value($key, $value, $filters); ?>
				<?php } ?>
			</ul>
			<a href="<?php echo esc_url(WP_Job_Board_Pro_Mixes::get_jobs_page_url()); ?>"><?php esc_html_e('Resetuj filtere', 'wp-job-board-pro'); ?></a>
		</div>
	</div>
<?php }