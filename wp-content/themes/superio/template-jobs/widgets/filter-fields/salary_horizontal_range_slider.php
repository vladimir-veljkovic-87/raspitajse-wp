<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="clearfix form-group form-group-<?php echo esc_attr($key); ?> <?php echo esc_attr(!empty($field['toggle']) ? 'toggle-field' : ''); ?> <?php echo esc_attr(!empty($field['hide_field_content']) ? 'hide-content' : ''); ?>">
	<?php if ( !isset($field['show_title']) || $field['show_title'] ) { ?>
    	<label for="<?php echo esc_attr($name); ?>" class="heading-label">
    		<?php echo trim($field['label']); ?>
    		<?php if ( !empty($field['toggle']) ) { ?>
                <i class="fa fa-angle-down" aria-hidden="true"></i>
            <?php } ?>
    	</label>
    <?php } ?>
    <div class="form-group-inner">
		<?php
		$salary_types = WP_Job_Board_Pro_Mixes::get_default_salary_types();
		if ( !empty($salary_types) ) {
			$selected = !empty( $_GET[$name.'-type'] ) ? $_GET[$name.'-type'] : '';
			?>
			<div class="salary_types-wrapper">
				<select name="filter-salary-type">
					<option value=""><?php echo esc_html__('Filter by salary type', 'superio'); ?></option>
					<?php foreach ($salary_types as $salary_key => $text) { ?>
						<option value="<?php echo esc_attr($salary_key); ?>" <?php selected($selected, $salary_key); ?>><?php echo esc_html($text); ?></option>
					<?php } ?>
				</select>
			</div>
			<?php
		}
		?>
		<?php
			$min_val = ! empty( $_GET[$name.'-from'] ) ? esc_attr( $_GET[$name.'-from'] ) : $min;
			$max_val = ! empty( $_GET[$name.'-to'] ) ? esc_attr( $_GET[$name.'-to'] ) : $max;
		?>
	  	<div class="from-to-wrapper">
			<span class="inner">
				<span class="from-text"><?php echo WP_Job_Board_Pro_Price::format_price($min_val); ?></span>
				<span class="space">-</span>
				<span class="to-text"><?php echo WP_Job_Board_Pro_Price::format_price($max_val); ?></span>
			</span>
		</div>
		<div class="salary-range-slider" data-max="<?php echo esc_attr($max); ?>" data-min="<?php echo intval($min); ?>"></div>
	  	<input type="hidden" name="<?php echo esc_attr($name.'-from'); ?>" class="filter-from" value="<?php echo esc_attr($min_val); ?>">
	  	<input type="hidden" name="<?php echo esc_attr($name.'-to'); ?>" class="filter-to" value="<?php echo esc_attr($max_val); ?>">
	  </div>
</div><!-- /.form-group -->