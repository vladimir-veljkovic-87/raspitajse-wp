<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$r = [
    'orderby'         => 'name',
    'order'           => 'ASC',
    'show_count'      => 0,
    'hide_empty'      => 0,
    'parent'          => '',
    'child_of'        => 0,
    'exclude'         => '',
    'selected'        => $selected,
    'hierarchical'    => 1,
    'name'            => $name,
    'depth'           => 0,
    'taxonomy'        => $field['taxonomy'],
    'value'           => 'id',
    'multiple'        => true,
];

$r['lang'] = apply_filters( 'wp-job-board-pro-current-lang', null );

$categories_hash = 'wjbp_cats_' . md5( wp_json_encode( $r ) . WP_Job_Board_Pro_Cache_Helper::get_transient_version('wjbp_get_' . $r['taxonomy']) );
$categories      = get_transient( $categories_hash );

if ( empty( $categories ) ) {
    $cat_args = [
        'taxonomy'     => $r['taxonomy'],
        'orderby'      => $r['orderby'],
        'order'        => $r['order'],
        'hide_empty'   => $r['hide_empty'],
        'parent'       => $r['parent'],
        'child_of'     => $r['child_of'],
        'exclude'      => $r['exclude'],
        'hierarchical' => $r['hierarchical'],
    ];

    $categories = get_terms( $cat_args );

    set_transient( $categories_hash, $categories, DAY_IN_SECONDS * 7 );
}

$output = '';
if ( ! empty( $categories ) ) {
    include_once WP_JOB_BOARD_PRO_PLUGIN_DIR . '/includes/walkers/class-category-select-walker.php';

    $walker = new WP_Job_Board_Pro_Category_Select_Walker();

    if ( $r['hierarchical'] ) {
        $depth = $r['depth'];  // Walk the full depth.
    } else {
        $depth = -1; // Flat.
    }

    $output .= $walker->walk( $categories, $depth, $r );
}

// $output = WP_Job_Board_Pro_Abstract_Filter::hierarchical_tax_option_tree(0, 0, $name, $key, $field, $selected );

if ( !empty($output) ) {
    $placeholder = !empty($field['placeholder']) ? $field['placeholder'] : $field['name'];
?>
    <div class="form-group form-group-<?php echo esc_attr($key); ?> <?php echo esc_attr(!empty($field['toggle']) ? 'toggle-field' : ''); ?> <?php echo esc_attr(!empty($field['hide_field_content']) ? 'hide-content' : ''); ?> tax-select-field">
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
            <select name="<?php echo esc_attr($name); ?>" class="form-control" id="<?php echo esc_attr( $args['widget_id'] ); ?>_<?php echo esc_attr($key); ?>" <?php if ( !empty($placeholder) ) { ?>
                    data-placeholder="<?php echo esc_attr($placeholder); ?>"
                    <?php } ?>>

                    <option value=""><?php echo esc_attr($placeholder); ?></option>
                    
                <?php echo $output; ?>
            </select>
        </div>
    </div><!-- /.form-group -->
<?php }