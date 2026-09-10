<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$orderby_options = apply_filters( 'wp-job-board-pro-jobs-orderby', array(
	'menu_order' => esc_html__('Sort by (Default)', 'superio'),
	'newest' => esc_html__('Newest', 'superio'),
	'oldest' => esc_html__('Oldest', 'superio'),
	'random' => esc_html__('Random', 'superio'),
));
$orderby = isset( $_GET['filter-orderby'] ) ? wp_unslash( $_GET['filter-orderby'] ) : 'menu_order';
if ( !WP_Job_Board_Pro_Mixes::is_ajax_request() ) {
	superio_load_select2();
}
?>
<div class="jobs-ordering-wrapper">
	<form class="jobs-ordering" method="get" action="<?php echo WP_Job_Board_Pro_Mixes::get_jobs_page_url(); ?>">
		<select name="filter-orderby" class="orderby" data-placeholder="<?php esc_attr_e('Sort by', 'superio'); ?>">
			<?php foreach ( $orderby_options as $id => $name ) : ?>
				<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
			<?php endforeach; ?>
		</select>
		<input type="hidden" name="paged" value="1" />
		<?php WP_Job_Board_Pro_Mixes::query_string_form_fields( null, array( 'filter-orderby', 'submit', 'paged' ) ); ?>
	</form>
</div>
