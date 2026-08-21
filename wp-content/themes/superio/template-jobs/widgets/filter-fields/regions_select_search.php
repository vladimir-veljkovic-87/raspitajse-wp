<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$taxonomy = $field['taxonomy'];

$first_regions = get_terms($taxonomy, array(
    'orderby' => 'count',
    'hide_empty' => 0,
    'parent' => 0,
    'number' => 1,
    'orderby' => 'name',
    'order'   => 'ASC',
));

if ( !empty( $first_regions ) && !is_wp_error( $first_regions ) ) {

    $selected_region = '';
    if ( ! empty( $selected ) ) {
        $selected_region = $selected;
    } elseif ( is_tax($taxonomy) ) {
        global $wp_query;
        $term = $wp_query->queried_object;
        if ( isset( $term->term_id) ) {
            $selected_region = $term->term_id;
        }
    }

    $selected_regions = [];

    $parent = $parent_second = $parent_third = 0;
    $first_select = $second_select = $third_select = $fourth_select = 0;

    if ( !empty($selected_region) ) {
        if ( !is_array($selected_region) ) {
            $level = WP_Job_Board_Pro_Abstract_Filter::get_the_level($selected_region);
            if ( $level == 0 ) {
                $parent = $selected_region;
                $selected_regions[] = $selected_region;
            } elseif ( $level == 1 ) {
                $term = get_term($selected_region);
                if ( $term ) {
                    $parent_term = get_term($term->parent);
                    $parent = $parent_term->term_id;
                    $parent_second = $term->term_id;
                    $selected_regions[] = $parent_term->term_id;
                    $selected_regions[] = $term->term_id;
                }
            } elseif ( $level == 2 ) {
                $term = get_term($selected_region);
                if ( $term ) {
                    // second
                    $second_parent_term = get_term($term->parent);
                    $parent_second = $second_parent_term->term_id;
                    
                    // first
                    $first_parent_term = get_term($second_parent_term->parent);
                    $parent = $first_parent_term->term_id;


                    $selected_regions[] = $term->term_id;
                    $selected_regions[] = $second_parent_term->term_id;
                    $selected_regions[] = $first_parent_term->term_id;
                }
            } elseif ( $level == 3 ) {
                $term = get_term($selected_region);
                if ( $term ) {
                    $fourth_select = $term->term_id;
                    // third
                    $third_parent_term = get_term($term->parent);
                    $parent_third = $third_parent_term->term_id;
                    // second
                    $second_parent_term = get_term($third_parent_term->parent);
                    $parent_second = $second_parent_term->term_id;
                    // first
                    $first_parent_term = get_term($second_parent_term->parent);
                    $parent = $first_parent_term->term_id;

                    $selected_regions[] = $term->term_id;
                    $selected_regions[] = $third_parent_term->term_id;
                    $selected_regions[] = $second_parent_term->term_id;
                    $selected_regions[] = $first_parent_term->term_id;
                }
            }
        } else {
            $selected_regions = $selected_region;
        }
    }

    $nb_fields = apply_filters('wp_job_board_pro_cmb2_field_taxonomy_location_number', 4);

    $region1_text = apply_filters('wp_job_board_pro_cmb2_field_taxonomy_location_field_name_1', esc_html__('Country', 'superio'));


    $placeholder = !empty($field['placeholder']) ? $field['placeholder'] : __('Filter by %s', 'superio');
    $placeholder1 = sprintf($placeholder, $region1_text);
?>
    <div class="form-group form-group-<?php echo esc_attr($key); ?> tax-select-field">

        <?php if ( !isset($field['show_title']) || $field['show_title'] ) { ?>
            <label for="<?php echo esc_attr( $args['widget_id'] ); ?>_<?php echo esc_attr($key); ?>" class="heading-label">
                <?php echo wp_kses_post($field['name']); ?>
            </label>
        <?php } ?>

        <div class="form-group-inner group-wrapper-filter <?php echo (!empty($field['icon']))?'has-icon':'' ?>">
            
            <div class="field-region field-region1">
                <div class="form-group-inner <?php echo (!empty($field['icon']))?'has-icon':'' ?>">
                    <?php if ( !empty($field['icon']) ) { ?>
                        <i class="<?php echo esc_attr( $field['icon'] ); ?>"></i>
                    <?php } ?>
                    <select class="select-field-region-search select-field-region-search1 form-control" data-prev="0" data-next="2" autocomplete="off" name="<?php echo esc_attr($name); ?>[]" data-placeholder="<?php echo esc_attr($placeholder1); ?>" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                        <option value=""><?php echo esc_attr($placeholder1); ?></option>
                        <?php
                        if ( ! empty( $selected_regions ) ) {
                            $first_regions = get_terms($taxonomy, array(
                                'orderby' => 'count',
                                'hide_empty' => 0,
                                'parent' => 0,
                                'orderby' => 'name',
                                'order'   => 'ASC',
                                'include' => $selected_regions
                            ));
                            if ( ! empty( $first_regions ) && ! is_wp_error( $first_regions ) ) {
                                foreach ($first_regions as $region) {
                                    ?>
                                    <option value="<?php echo esc_attr($region->term_id); ?>" selected="selected"><?php echo esc_html($region->name); ?></option>
                                    <?php  
                                }
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
    

            <?php if ( $nb_fields == '2' || $nb_fields == '3' || $nb_fields == '4' ) {
                $region2_text = apply_filters('wp_job_board_pro_cmb2_field_taxonomy_location_field_name_2', esc_html__('State', 'superio'));
                $placeholder2 = sprintf($placeholder, $region2_text);
            ?>

                <div class="field-region field-region2">
                    <div class="form-group-inner <?php echo (!empty($field['icon']))?'has-icon':'' ?>">
                        <?php if ( !empty($field['icon']) ) { ?>
                            <i class="<?php echo esc_attr( $field['icon'] ); ?>"></i>
                        <?php } ?>
                        <select class="select-field-region-search select-field-region-search2 form-control" data-prev="1" data-next="3" autocomplete="off" name="<?php echo esc_attr($name); ?>[]" data-placeholder="<?php echo esc_attr($placeholder2); ?>"  data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                            <option value=""><?php echo esc_attr($placeholder2); ?></option>
                            <?php
                            if ( !empty($parent) && ! empty( $selected_regions ) ) {
                                $second_regions = get_terms($taxonomy, array(
                                    'orderby' => 'count',
                                    'hide_empty' => 0,
                                    'parent' => $parent,
                                    'orderby' => 'name',
                                    'order'   => 'ASC',
                                    'include'   => $selected_regions,
                                ));
                                if ( ! empty( $second_regions ) && ! is_wp_error( $second_regions ) ) {
                                    foreach ($second_regions as $region) {
                                        ?>
                                        <option value="<?php echo esc_attr($region->term_id); ?>" selected="selected"><?php echo esc_html($region->name); ?></option>
                                        <?php  
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

            <?php } ?>
            <?php if ( $nb_fields == '3' || $nb_fields == '4' ) {
                $region3_text = apply_filters('wp_job_board_pro_cmb2_field_taxonomy_location_field_name_3', esc_html__('City', 'superio'));
                $placeholder3 = sprintf($placeholder, $region3_text);
            ?>

                <div class="field-region field-region3">
                    <div class="form-group-inner <?php echo (!empty($field['icon']))?'has-icon':'' ?>">
                        <?php if ( !empty($field['icon']) ) { ?>
                            <i class="<?php echo esc_attr( $field['icon'] ); ?>"></i>
                        <?php } ?>
                        <select class="select-field-region-search select-field-region-search3 form-control" data-prev="2" data-next="4" autocomplete="off" name="<?php echo esc_attr($name); ?>[]" data-placeholder="<?php echo esc_attr($placeholder3); ?>"  data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                            <option value=""><?php echo esc_attr($placeholder3); ?></option>
                            <?php
                            if ( !empty($parent_second) && ! empty( $selected_regions ) ) {
                                $third_regions = get_terms($taxonomy, array(
                                    'orderby' => 'count',
                                    'hide_empty' => 0,
                                    'parent' => $parent_second,
                                    'orderby' => 'name',
                                    'order'   => 'ASC',
                                    'include'   => $selected_regions,
                                ));
                                if ( ! empty( $third_regions ) && ! is_wp_error( $third_regions ) ) {
                                    foreach ($third_regions as $region) {
                                        ?>
                                        <option value="<?php echo esc_attr($region->term_id); ?>" selected="selected"><?php echo esc_html($region->name); ?></option>
                                        <?php  
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

            <?php } ?>
            <?php if ( $nb_fields == '4' ) {
                $region4_text = apply_filters('wp_job_board_pro_cmb2_field_taxonomy_location_field_name_4', esc_html__('District', 'superio'));
                $placeholder4 = sprintf($placeholder, $region4_text);
            ?>
                <div class="field-region field-region4">
                    <div class="form-group-inner <?php echo (!empty($field['icon']))?'has-icon':'' ?>">
                        <?php if ( !empty($field['icon']) ) { ?>
                            <i class="<?php echo esc_attr( $field['icon'] ); ?>"></i>
                        <?php } ?>
                        <select class="select-field-region-search select-field-region-search4 form-control" data-prev="3" data-next="5" autocomplete="off" name="<?php echo esc_attr($name); ?>[]" data-placeholder="<?php echo esc_attr($placeholder4); ?>" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                            <option value=""><?php echo esc_attr($placeholder4); ?></option>
                            <?php
                            if ( !empty($parent_third) && ! empty( $selected_regions ) ) {
                                $fourth_regions = get_terms($taxonomy, array(
                                    'orderby' => 'count',
                                    'hide_empty' => 0,
                                    'parent' => $parent_third,
                                    'orderby' => 'name',
                                    'order'   => 'ASC',
                                    'include'   => $selected_regions,
                                ));
                                if ( ! empty( $fourth_regions ) && ! is_wp_error( $fourth_regions ) ) {
                                    foreach ($fourth_regions as $region) {
                                        ?>
                                        <option value="<?php echo esc_attr($region->term_id); ?>" selected="selected"><?php echo esc_html($region->name); ?></option>
                                        <?php  
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

            <?php } ?>
        </div>

    </div><!-- /.form-group -->
<?php }