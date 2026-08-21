<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !empty($args['before_widget']) ) {
	echo trim( $args['before_widget'] );
}

if ( ! empty( $instance['title'] ) ) {
	echo trim( $args['before_title'] );
	echo esc_attr( $instance['title'] );
	echo trim( $args['after_title'] );
}

?>

<form method="get" action="<?php echo WP_Job_Board_Pro_Mixes::get_candidates_page_url(); ?>" class="filter-candidate-form filter-listing-form vertical filter-listing-form-wrapper">
	
	<?php $fields = WP_Job_Board_Pro_Candidate_Filter::get_fields(); ?>
	<?php if ( ! empty( $instance['sort'] ) ) : ?>
		<?php
			$keys = explode( ',', $instance['sort'] );
			$filtered_keys = array_filter( $keys );
			$fields = array_merge( array_flip( $filtered_keys ), $fields );
		?>
	<?php endif; ?>

	<?php foreach ( $fields as $key => $field ) : ?>
		<?php
			if ( empty( $instance['hide_'.$key] ) && !empty($field['field_call_back']) && is_callable($field['field_call_back']) ) {
				call_user_func( $field['field_call_back'], $instance, $args, $key, $field );
			}
		?>
	<?php endforeach; ?>

	<?php if ( ! empty( $instance['button_text'] ) ) : ?>
		<div class="form-group">
			<button class="button btn btn-theme"><?php echo esc_attr( $instance['button_text'] ); ?></button>
		</div><!-- /.form-group -->
	<?php endif; ?>
</form>

<?php
if ( !empty($args['after_widget']) ) {
	echo trim( $args['after_widget'] );
}
?>

