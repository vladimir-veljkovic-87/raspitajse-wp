<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$orderby_options = apply_filters( 'wp-job-board-pro-jobs-orderby', array(
	'menu_order' => esc_html__('Default', 'wp-job-board-pro'),
	'newest' => esc_html__('Newest', 'wp-job-board-pro'),
	'oldest' => esc_html__('Oldest', 'wp-job-board-pro'),
	'random' => esc_html__('Random', 'wp-job-board-pro'),
));
$orderby = isset( $_GET['filter-orderby'] ) ? wp_unslash( $_GET['filter-orderby'] ) : 'newest';
if ( !WP_Job_Board_Pro_Mixes::is_ajax_request() ) {
	wp_enqueue_script('wpjbp-select2');
	wp_enqueue_style('wpjbp-select2');
}
?>
<div class="jobs-ordering-wrapper">
	<form class="jobs-ordering" method="get" action="<?php echo WP_Job_Board_Pro_Mixes::get_candidates_page_url(); ?>">
		<div class="label"><?php esc_html_e('Sort by:', 'wp-job-board-pro'); ?></div>
		<select name="filter-orderby" class="orderby">
			<?php foreach ( $orderby_options as $id => $name ) : ?>
				<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
			<?php endforeach; ?>
		</select>
		<input type="hidden" name="paged" value="1" />
		<?php WP_Job_Board_Pro_Mixes::query_string_form_fields( null, array( 'filter-orderby', 'submit', 'paged' ) ); ?>
	</form>
</div>
