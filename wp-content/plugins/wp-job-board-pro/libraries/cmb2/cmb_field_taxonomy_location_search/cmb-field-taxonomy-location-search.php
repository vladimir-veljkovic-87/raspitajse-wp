<?php

/**
 * Class WP_Job_Board_Pro_CMB2_Field_Taxonomy_Location_Search
 */
class WP_Job_Board_Pro_CMB2_Field_Taxonomy_Location_Search {

	/**
	 * Current version number
	 */
	const VERSION = '1.0.0';

	/**
	 * Initialize the plugin by hooking into CMB2
	 */
	public function __construct() {
		add_filter( 'cmb2_render_wpjb_taxonomy_location_search', array( $this, 'render_taxonomy_location' ), 10, 5 );
		add_filter( 'cmb2_sanitize_wpjb_taxonomy_location_search', array( $this, 'sanitize' ), 10, 4 );
		add_filter( 'cmb2_types_esc_wpjb_taxonomy_location_search', array( $this, 'escaped_value' ), 10, 3 );
		add_filter( 'cmb2_repeat_table_row_types', array( $this, 'table_row_class' ), 10, 1 );

	}

	/**
	 * Render select box field
	 */
	public function render_taxonomy_location( $field, $field_escaped_value, $field_object_id, $field_object_type, $field_type_object ) {
		$this->setup_admin_scripts();

		if ( version_compare( CMB2_VERSION, '2.2.2', '>=' ) ) {
			$field_type_object->type = new CMB2_Type_Select( $field_type_object );
		}
		
		$selected_region = $this->options_terms($field_type_object->field);

		$selected_regions = [];
	    if ( !empty($selected_region) ) {
	        if ( !is_array($selected_region) ) {
	            $level = WP_Job_Board_Pro_Abstract_Filter::get_the_level($selected_region, $field_type_object->field->args( 'taxonomy' ));
	            $selected_regions[$level] = $selected_region;
	        } else {
	        	foreach ($selected_region as $term_id) {
	        		$level = WP_Job_Board_Pro_Abstract_Filter::get_the_level($term_id, $field_type_object->field->args( 'taxonomy' ));
	        		
	        		$selected_regions[$level] = $term_id;
	        	}
	        }
	    }

		$nb_fields = apply_filters('wp_job_board_pro_cmb2_field_taxonomy_location_number', 4);
		$parent = 0;
		echo '<div class="field-taxonomy-location-wrapper field-taxonomy-location-wrapper-'.$nb_fields.'">';
		for ($i=1; $i <= $nb_fields; $i++) {
			$default_value = [];
			if ( !empty($selected_regions) && !empty($selected_regions[$i-1]) ) {
				$default_value[] = $selected_regions[$i-1];
			}
			$taxonomy_options = $this->get_taxonomy_options( $field_escaped_value, $field_type_object, $default_value );
			$parent = !empty($taxonomy_options['parent']) ? $taxonomy_options['parent'] : 'no';
			
			$label = '';
			if ( $i == 1 ) {
				$label = esc_html__('Country', 'wp-job-board-pro');
			} elseif ( $i == 2 ) {
				$label = esc_html__('State', 'wp-job-board-pro');
			} elseif ( $i == 3 ) {
				$label = esc_html__('City', 'wp-job-board-pro');
			} elseif ( $i == 4 ) {
				$label = esc_html__('District', 'wp-job-board-pro');
			}

			$field_name = apply_filters('wp_job_board_pro_cmb2_field_taxonomy_location_field_name_'.$i, $label);
			$placeholder = $field->args( 'attributes', 'placeholder' ) ? $field->args( 'attributes', 'placeholder' ) : $field_name;
			$placeholder = sprintf($placeholder, $field_name);

			$a = $field_type_object->parse_args( 'wpjb_taxonomy_location_search', array(
				'style'            => 'width: 99%',
				'class'            => 'wpjb_taxonomy_location_search wpjb_taxonomy_location_search'.$i,
				'name'             => $field_type_object->_name() . '[]',
				'id'               => $field_type_object->_id().$i,
				'desc'             => $field_type_object->_desc( true ),
				'options'          => $taxonomy_options['option'],
				'data-placeholder' => $placeholder,
				'data-next' => ($i + 1),
				'data-prev' => ($i - 1),
				'data-taxonomy' => $field_type_object->field->args( 'taxonomy' ),
				'data-allowclear' => true
			) );

			$attrs = $field_type_object->concat_attrs( $a, array( 'desc', 'options' ) );
			echo sprintf( '<div class="field-taxonomy-location-%d"><select%s>%s</select></div>', $i, $attrs, $a['options'] );
		}
		echo '</div>';
		if ( !empty($a['desc']) ) {
			echo $a['desc'];
		}
	}

	public function get_taxonomy_options( $field_escaped_value, $field_type_object, $default_value ) {
		// $options = (array) $this->get_terms($field_type_object->field->args( 'taxonomy' ));
		$options = [];
		$field_escaped_value = $default_value;
		
		if ( ! empty( $field_escaped_value ) ) {
			if ( !is_array($field_escaped_value) ) {
				$field_escaped_value = array($field_escaped_value);
			}
			$options = (array) $this->get_terms($field_type_object->field->args( 'taxonomy' ), array('include' => $field_escaped_value) );
			// $options = $this->sort_array_by_array( $options, $field_escaped_value );
		}

		$return = array();
		$selected_items = '';
		$other_items = '';

		foreach ( $options as $option_value => $option_label ) {
			// Clone args & modify for just this item
			$option = array(
				'value' => $option_value,
				'label' => $option_label,
			);

			// Split options into those which are selected and the rest
			if ( in_array( $option_value, (array) $field_escaped_value ) ) {
				$return['parent'] = $option_value;
				$option['checked'] = true;
				$selected_items .= $field_type_object->select_option( $option );
			} else {
				$other_items .= $field_type_object->select_option( $option );
			}
		}
		$return['option'] = $selected_items . $other_items;

		return $return;
	}

	public function options_terms($field) {
		if ( empty($field->data_args()['id']) ) {
			return array();
		}
		$object_id = $field->data_args()['id'];
		$terms = get_the_terms( $object_id, $field->args( 'taxonomy' ) );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
			foreach ( $terms as $index => $term ) {
				$terms[ $index ] = $term->term_id;
			}
		}

		return $terms;
	}

	public function sort_array_by_array( array $array, array $orderArray ) {
		$ordered = array();

		foreach ( $orderArray as $key ) {
			if ( array_key_exists( $key, $array ) ) {
				$ordered[ $key ] = $array[ $key ];
				unset( $array[ $key ] );
			}
		}

		return $ordered + $array;
	}

	/**
	 * Handle sanitization for repeatable fields
	 */
	public function sanitize( $check, $meta_value, $object_id, $field_args ) {
		if ( empty($meta_value) || !is_array( $meta_value ) ) {
			return $check;
		}
		if ( $field_args['repeatable'] ) {
			foreach ( $meta_value as $key => $val ) {
				$meta_value[$key] = array_map( 'absint', $val );
				wp_set_object_terms( $object_id, array_map( 'absint', $val ), $field_args['taxonomy'], false );
			}
		} else {
			$meta_value = array_map( 'absint', $meta_value );
			wp_set_object_terms( $object_id, $meta_value, $field_args['taxonomy'], false );
		}

		return $meta_value;
	}


	/**
	 * Handle escaping for repeatable fields
	 */
	public function escaped_value( $check, $meta_value, $field_args ) {
		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return $check;
		}

		foreach ( $meta_value as $key => $val ) {
			$meta_value[$key] = array_map( 'esc_attr', $val );
		}

		return $meta_value;
	}

	/**
	 * Add 'table-layout' class to multi-value select field
	 */
	public function table_row_class( $check ) {
		$check[] = 'wpjb_taxonomy_location_search';

		return $check;
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function setup_admin_scripts() {
		$asset_path = apply_filters( 'wpjb_cmb2_field_select2_asset_path', plugins_url( '', __FILE__  ) );

		wp_enqueue_script( 'wpjb-taxonomy-location-script', $asset_path . '/js/script.js', array( 'cmb2-scripts', 'wpjbp-select2', 'jquery-ui-sortable' ), self::VERSION );
		wp_localize_script( 'wpjb-taxonomy-location-script', 'location_opts', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'ajaxurl_endpoint' => WP_Job_Board_Pro_Ajax::get_endpoint(),
			'ajax_nonce' => wp_create_nonce( 'wpjb-ajax-nonce' )
		));
		wp_enqueue_style( 'wpjb-taxonomy-location-style', $asset_path . '/css/style.css', array( 'wpjbp-select2' ), self::VERSION );
	}

    public function get_terms($taxonomy, $query_args = array()) {
        $return = array();

        $defaults = array(
	        'hide_empty' => false,
	        'orderby' => 'name',
            'order' => 'ASC',
            'hierarchical' => 1,
	    );
	    $args = wp_parse_args( $query_args, $defaults );
	    
	    $terms = get_terms( $taxonomy, $args );
	    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
	        foreach ( $terms as $key => $term ) {
                $return[$term->term_id] = $term->name;
	        }
	    }
		
        return $return;
    }

}
$wpjb_cmb2_field_select2 = new WP_Job_Board_Pro_CMB2_Field_Taxonomy_Location_Search();
