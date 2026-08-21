<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$orderby_options = apply_filters( 'wp-job-board-pro-jobs-orderby', array(
	'menu_order' => esc_html__('Sortiraj (Podrazumevano)', 'superio'),
	'newest' => esc_html__('Najnoviji', 'superio'),
	'oldest' => esc_html__('Najstariji', 'superio'),
	'random' => esc_html__('Nasumično', 'superio'),
));
$orderby = isset( $_GET['filter-orderby'] ) ? wp_unslash( $_GET['filter-orderby'] ) : 'menu_order';
if ( !WP_Job_Board_Pro_Mixes::is_ajax_request() ) {
	superio_load_select2();
}
?>
<div class="jobs-ordering-wrapper">
	<form class="jobs-ordering" method="get" action="<?php echo WP_Job_Board_Pro_Mixes::get_candidates_page_url(); ?>">
		<select name="filter-orderby" class="orderby">
			<?php foreach ( $orderby_options as $id => $name ) : ?>
				<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
			<?php endforeach; ?>
		</select>
		<input type="hidden" name="paged" value="1" />
		<?php WP_Job_Board_Pro_Mixes::query_string_form_fields( null, array( 'filter-orderby', 'submit', 'paged' ) ); ?>
	</form>
</div>
