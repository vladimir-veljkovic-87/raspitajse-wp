<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( version_compare(WP_JOB_BOARD_PRO_PLUGIN_VERSION, '1.2.17', '>=') ) {
	$min = WP_Job_Board_Pro_Price::convert_price_exchange($min);
	$max = WP_Job_Board_Pro_Price::convert_price_exchange($max);
}
?>
<div class="clearfix form-group form-group-<?php echo esc_attr($key); ?> <?php echo esc_attr(!empty($field['toggle']) ? 'toggle-field' : ''); ?> <?php echo esc_attr(!empty($field['hide_field_content']) ? 'hide-content' : ''); ?>">
	<?php if ( !isset($field['show_title']) || $field['show_title'] ) { ?>
    	<label for="<?php echo esc_attr($name); ?>" class="heading-label">
    		<?php echo wp_kses_post($field['name']); ?>
    		<?php if ( !empty($field['toggle']) ) { ?>
                <i class="fas fa-angle-down"></i>
            <?php } ?>
    	</label>
    <?php } ?>
    <div class="form-group-inner">
		
		<?php
			$min_val = (!empty( $_GET[$name.'-from'] ) && $_GET[$name.'-from'] >= $min) ? $_GET[$name.'-from'] : $min;
			$max_val = (!empty( $_GET[$name.'-to'] ) && $_GET[$name.'-to'] <= $max) ? $_GET[$name.'-to'] : $max;

	    	if ( version_compare(WP_JOB_BOARD_PRO_PLUGIN_VERSION, '1.2.17', '>=') ) {
		    	$min_val_output = WP_Job_Board_Pro_Price::format_price($min_val, true, true);
		    	$max_val_output = WP_Job_Board_Pro_Price::format_price($max_val, true, true);
		    } else {
		    	$min_val_output = WP_Job_Board_Pro_Price::format_price($min_val, true);
		    	$max_val_output = WP_Job_Board_Pro_Price::format_price($max_val, true);
		    }
	    ?>
	  	<div class="from-to-wrapper">
			<span class="inner">
				<span class="from-text"><?php echo $min_val_output; ?></span>
				<span class="space">-</span>
				<span class="to-text"><?php echo $max_val_output; ?></span>
			</span>
		</div>
		<div class="salary-range-slider" data-max="<?php echo esc_attr($max); ?>" data-min="<?php echo intval($min); ?>"></div>
	  	<input type="hidden" name="<?php echo esc_attr($name.'-from'); ?>" class="filter-from" value="<?php echo esc_attr($min_val); ?>">
	  	<input type="hidden" name="<?php echo esc_attr($name.'-to'); ?>" class="filter-to" value="<?php echo esc_attr($max_val); ?>">
	  </div>
</div><!-- /.form-group -->