<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'datetimepicker', get_template_directory_uri() . '/css/jquery.datetimepicker.min.css' );
wp_enqueue_script( 'datetimepicker', get_template_directory_uri() . '/js/jquery.datetimepicker.full.min.js' );

?>
<div class="form-group form-group-<?php echo esc_attr($key); ?> <?php echo esc_attr(!empty($field['toggle']) ? 'toggle-field' : ''); ?> <?php echo esc_attr(!empty($field['hide_field_content']) ? 'hide-content' : ''); ?>">
	<?php if ( !isset($field['show_title']) || $field['show_title'] ) { ?>
    	<label for="<?php echo esc_attr( $args['widget_id'] ); ?>_<?php echo esc_attr($key); ?>" class="heading-label">
    		<?php echo wp_kses_post($field['name']); ?>
    		<?php if ( !empty($field['toggle']) ) { ?>
                <i class="fas fa-angle-down"></i>
            <?php } ?>
    	</label>
    <?php } ?>
    <div class="form-group-inner inner">
	    
       <?php
			$min_val = ! empty( $_GET[$name]['form'] ) ? esc_attr( $_GET[$name]['form'] ) : '';
			$max_val = ! empty( $_GET[$name]['to'] ) ? esc_attr( $_GET[$name]['to'] ) : '';
		?>
		
	  	<input type="text" name="<?php echo esc_attr($name); ?>[form]" class="field-datetimepicker filter-from form-control <?php echo esc_attr(!empty($field['add_class']) ? $field['add_class'] : '');?>" value="<?php echo esc_attr($min_val); ?>" placeholder="<?php echo esc_attr(!empty($field['placeholder']) ? $field['placeholder'] : esc_attr__('Min Date', 'superio')); ?>">
	  	<input type="text" name="<?php echo esc_attr($name); ?>[to]" class="field-datetimepicker filter-to form-control margin-top-10 <?php echo esc_attr(!empty($field['add_class']) ? $field['add_class'] : '');?>" value="<?php echo esc_attr($max_val); ?>" placeholder="<?php echo esc_attr(!empty($field['placeholder']) ? $field['placeholder'] : esc_attr__('Max Date', 'superio')); ?>">

	</div>
</div><!-- /.form-group -->
