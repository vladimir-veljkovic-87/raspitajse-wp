<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


ob_start();
if ( !empty($options) ) {
    $i = 1;
    foreach ($options as $option) {
        if ( $option['value'] ) {
            $selected_attr = '';
            if ( !empty($selected) && is_array($selected) ) {
                $selected_attr = in_array($option['value'], $selected) ? 'selected="selected"' : '';
            } else {
                $selected_attr = selected($selected, $option['value'], false);
            }
            ?>
            <option value="<?php echo esc_attr($option['value']); ?>" <?php echo trim($selected_attr); ?>>
                <?php echo esc_attr($option['text']); ?>
            </option>
            <?php
            $i++;
        }
    }
}
$output = ob_get_clean();

if ( !empty($output) ) {
    $placeholder = !empty($field['placeholder']) ? $field['placeholder'] : $field['name'];
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
        <div class="form-group-inner inner <?php echo (!empty($field['icon']))?'has-icon':'' ?>">
            <?php if ( !empty($field['icon']) ) { ?>
                <i class="<?php echo esc_attr( $field['icon'] ); ?>"></i>
            <?php } ?>
            <select multiple name="<?php echo esc_attr($name); ?>[]" class="form-control" id="<?php echo esc_attr( $args['widget_id'] ); ?>_<?php echo esc_attr($key); ?>" <?php if ( !empty($placeholder) ) { ?>
                    data-placeholder="<?php echo esc_attr($placeholder); ?>"
                    <?php } ?>>
                <?php echo $output; ?>
            </select>
        </div>
    </div><!-- /.form-group -->
<?php }