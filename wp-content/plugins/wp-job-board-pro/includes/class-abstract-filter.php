<?php
/**
 * Job Filter
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Abstract_Filter {

	public static function has_filter($params = null) {
		if ( empty($params) ) {
			$params = $_GET;
		}
		if ( ! empty( $params ) && is_array( $params ) ) {
			foreach ( $params as $key => $value ) {
				if ( strrpos( $key, 'filter-', -strlen( $key ) ) !== false ) {
					return true;
				}
			}
		}
		return false;
	}

	public static function get_filters($params = null) {
		$filters = array();
		if ( empty($params) ) {
			if ( ! empty( $_GET ) && is_array( $_GET ) ) {
				$params = $_GET;
			}
		}
		if ( ! empty( $params ) && is_array( $params ) ) {
			foreach ( $params as $key => $value ) {
				if ( strrpos( $key, 'filter-', -strlen( $key ) ) !== false && !empty($value) ) {
					$filters[$key] = $value;
				}
			}
			if ( isset($filters['filter-salary-from']) && isset($filters['filter-salary-to']) ) {
				$filters['filter-salary'] = array($filters['filter-salary-from'], $filters['filter-salary-to'] );
				unset($filters['filter-salary-from']);
				unset($filters['filter-salary-to']);
			}
			if ( isset($filters['filter-center-latitude']) ) {
				unset($filters['filter-center-latitude']);
			}
			if ( isset($filters['filter-center-longitude']) ) {
				unset($filters['filter-center-longitude']);
			}
			if ( !empty($filters['filter-distance']) && !isset($filters['filter-center-location']) ) {
				unset($filters['filter-distance']);
			}
		}
		return $filters;
	}

	public static function date_posted_options() {
		return apply_filters( 'wp-job-board-pro-date-posted-options', array(
			array(
				'value' => '1hour',
				'text'	=> __('Last Hour', 'wp-job-board-pro'),
			),
			array(
				'value' => '24hours',
				'text'	=> __('Last 24 hours', 'wp-job-board-pro'),
			),
			array(
				'value' => '7days',
				'text'	=> __('Last 7 days', 'wp-job-board-pro'),
			),
			array(
				'value' => '14days',
				'text'	=> __('Last 14 days', 'wp-job-board-pro'),
			),
			array(
				'value' => '30days',
				'text'	=> __('Last 30 days', 'wp-job-board-pro'),
			),
			array(
				'value' => 'all',
				'text'	=> __('All', 'wp-job-board-pro'),
			),
		) );
	}

	public static function orderby($query_vars, $params) {
		// Order
		if ( ! empty( $params['filter-orderby'] ) ) {
			switch ( $params['filter-orderby'] ) {
				case 'newest':
					$query_vars['orderby'] = 'date';
					$query_vars['order'] = 'DESC';
					break;
				case 'oldest':
					$query_vars['orderby'] = 'date';
					$query_vars['order'] = 'ASC';
					break;
				case 'random':
					$query_vars['orderby'] = 'rand';
					break;
				case 'title':
					$query_vars['orderby'] = 'title';
					$query_vars['order'] = 'ASC';
					break;
				case 'published':
					$query_vars['orderby'] = 'date';
					break;
				case 'price':
					$query_vars['meta_key'] = WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX . 'price';
					$query_vars['orderby'] = 'meta_value_num';
					break;
			}
		} else {
			$query_vars['order'] = 'DESC';
			$query_vars['orderby'] = array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
				'ID'         => 'DESC',
			);
		}

		return apply_filters('wp-job-board-pro-get-orderby', $query_vars, $params);
	}

	public static function build_post_ids( $haystack, array $ids ) {
		if ( ! is_array( $haystack ) ) {
			$haystack = array();
		}

		if ( is_array( $haystack ) && count( $haystack ) > 0 ) {
			return array_intersect( $haystack, $ids );
		} else {
			$haystack = $ids;
		}

		return $haystack;
	}
	
	public static function filter_by_date_posted($params) {
		if ( ! empty( $params['filter-date-posted'] ) ) {
            switch ($params['filter-date-posted']) {
            	case '1hour':
            		$lastdate = '-1 hour';
            		break;
            	case '24hours':
            		$lastdate = '-24 hours';
            		break;
            	case '7days':
            		$lastdate = '-7 days';
            		break;
        		case '14days':
        			$lastdate = '-14 days';
            		break;
        		case '30days':
        			$lastdate = '-30 days';
            		break;
            }

            if ( !empty($lastdate) ) {
            	return array(
                        'after'     => $lastdate,  
         				'inclusive' => true,
                    );
            }
	    }
	    return null;
	}

	public static function filter_by_distance($params, $post_stype) {
		$distance_ids = array();
		if ( ! empty( $params['filter-center-location'] ) && ! empty( $params['filter-center-latitude'] ) && ! empty( $params['filter-center-longitude'] ) ) {
			$filter_distance = 50;
			if ( ! empty( $params['filter-distance'] ) ) {
				$filter_distance = $params['filter-distance'];
			}
		    $post_ids = self::get_posts_by_distance( $params['filter-center-latitude'], $params['filter-center-longitude'], $filter_distance, $post_stype );

		    if ( $post_ids ) {
			    foreach ( $post_ids as $post ) {
					$distance_ids[] = $post->ID;
			    }
			}
			if ( empty( $distance_ids ) || ! $distance_ids ) {
	            $distance_ids = array(0);
			}
	    }
	    
	    return $distance_ids;
	}

	public static function get_posts_by_distance($latitude, $longitude, $distance, $post_stype = 'job_listing') {
		global $wpdb;
		$distance_type = apply_filters( 'wp_job_board_pro_filter_distance_type', 'miles' );
		$earth_distance = $distance_type == 'miles' ? 3959 : 6371;

		$prefix = WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX;
		switch ($post_stype) {
			case 'candidate':
				$prefix = WP_JOB_BOARD_PRO_CANDIDATE_PREFIX;
				break;
			case 'employer':
				$prefix = WP_JOB_BOARD_PRO_EMPLOYER_PREFIX;
				break;
			case 'job_listing':
			default:
				$prefix = WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX;
				break;
		}
		$post_ids = false;
		$sql = $wpdb->prepare( "
			SELECT $wpdb->posts.ID, 
				( %s * acos( cos( radians(%s) ) * cos( radians( latmeta.meta_value ) ) * cos( radians( longmeta.meta_value ) - radians(%s) ) + sin( radians(%s) ) * sin( radians( latmeta.meta_value ) ) ) ) AS distance, latmeta.meta_value AS latitude, longmeta.meta_value AS longitude
			FROM $wpdb->posts
			INNER JOIN $wpdb->postmeta AS latmeta ON $wpdb->posts.ID = latmeta.post_id
			INNER JOIN $wpdb->postmeta AS longmeta ON $wpdb->posts.ID = longmeta.post_id
			WHERE $wpdb->posts.post_type = %s AND $wpdb->posts.post_status = 'publish' AND latmeta.meta_key=%s AND longmeta.meta_key=%s
			HAVING distance < %s
			ORDER BY $wpdb->posts.menu_order ASC, distance ASC",
			$earth_distance,
			$latitude,
			$longitude,
			$latitude,
			$post_stype,
			$prefix.'map_location_latitude',
			$prefix.'map_location_longitude',
			$distance
		);

		if ( apply_filters( 'wp_job_board_pro_get_job_listings_cache_results', false ) ) {
			$to_hash         = json_encode( array($earth_distance, $latitude, $longitude, $latitude, $distance, $post_stype) );
			$query_args_hash = 'wp_job_board_pro_' . md5( $to_hash . WP_JOB_BOARD_PRO_PLUGIN_VERSION );

			$post_ids = get_transient( $query_args_hash );
		}

		if ( ! $post_ids ) {
			$post_ids = $wpdb->get_results( $sql, OBJECT_K );
			if ( !empty($query_args_hash) ) {
				set_transient( $query_args_hash, $post_ids, DAY_IN_SECONDS );
			}
		}

		return $post_ids;
	}

	public static function filter_count($name, $term_id, $field) {
		$args = array(
			'post_type' => !empty($field['for_post_type']) ? $field['for_post_type'] : 'job_listing',
			'post_per_page' => 1,
			'fields' => 'ids'
		);
		$params = array();
		if ( WP_Job_Board_Pro_Abstract_Filter::has_filter() ) {
			$params = $_GET;
		}
		$params['filter-counter'] = array($name => $term_id);
		if ( !empty($params[$name]) ) {
			$values = $params[$name];
			if ( is_array($values) ) {
				$values[] = $term_id;
			} else {
				$values = $term_id;
			}
			$params[$name] = $values;
		} else {
			$params[$name] = $term_id;
		}

		$query_hash = md5( json_encode($args) ) .'-'. md5( json_encode($params) );

		$cached_counts = (array) get_transient( 'wp_job_board_pro_filter_counts' );
		if ( ! isset( $cached_counts[ $query_hash ] ) ) {
			$loop = WP_Job_Board_Pro_Query::get_posts($args, $params);
			$cached_counts[ $query_hash ] = $loop->found_posts;
			set_transient( 'wp_job_board_pro_filter_counts', $cached_counts, DAY_IN_SECONDS );
		}

		return $cached_counts[ $query_hash ];
	}
	
	public static function get_term_name($term_id, $tax) {
		$term = get_term($term_id, $tax);
		if ( $term ) {
			return $term->name;
		}
		return '';
	}

	public static function render_filter_tax($key, $value, $tax, $url) {
		if ( is_array($value) ) {
			foreach ($value as $val) {
				$name = self::get_term_name($val, $tax);
				$rm_url = self::remove_url_var($key . '[]=' . $val, $url);
				self::render_filter_result_item($name, $rm_url);
			}
		} else {
			$name = self::get_term_name($value, $tax);
			$rm_url = self::remove_url_var($key . '=' . $value, $url);
			self::render_filter_result_item($name, $rm_url);
		}
	}

	public static function remove_url_var($url_var, $url) {
		$str = "?" . $url_var;
		if ( strpos($url, $str) !== false ) {
		    $rm_url = str_replace($url_var, "", $url);
		    $rm_url = str_replace('?&', "?", $rm_url);
		} else {
			$rm_url = str_replace("&" . $url_var, "", $url);
		}
		return $rm_url;
	}

	public static function render_filter_result_item($value, $rm_url) {
		if ( $value ) {
		?>
			<li><a href="<?php echo esc_url($rm_url); ?>" ><span class="close-value">x</span><?php echo trim($value); ?></a></li>
			<?php
		}
	}

	public static function render_filter_tax_simple($key, $value, $tax, $label) {
		if ( is_array($value) ) {
			foreach ($value as $val) {
				$name = self::get_term_name($val, $tax);
				self::render_filter_result_item_simple($name, $label);
			}
		} else {
			$name = self::get_term_name($value, $tax);
			self::render_filter_result_item_simple($name, $label);
		}
	}

	public static function render_filter_result_item_simple($value, $label) {
		if ( $value ) {
		?>
			<li><strong class="text"><?php echo esc_html($label); ?>:</strong> <span class="value"><?php echo trim($value); ?></span></li>
			<?php
		}
	}


	// filter function
	public static function filter_get_name($key, $field) {
		$prefix = 'filter';
		if ( !empty($field['filter-name-prefix']) ) {
			$prefix = $field['filter-name-prefix'];
		}
		$name = $prefix.'-'.$key;
		return apply_filters('wp-job-board-pro-filter-get-name', $name, $key, $field);
	}

	public static function filter_field_input($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/text' );
	}

	public static function filter_field_input_location($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/text_location' );
	}

	public static function filter_field_input_distance($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : apply_filters( 'wp_job_board_pro_filter_distance_default', 50 );

		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/distance' );
	}

	public static function filter_field_input_date_posted($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : 'all';
		$options = WP_Job_Board_Pro_Abstract_Filter::date_posted_options();
		
		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/radios' );
	}

	public static function filter_field_found_date_range_slider($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';
		$min_max = WP_Job_Board_Pro_Query::get_min_max_meta_value(WP_JOB_BOARD_PRO_EMPLOYER_PREFIX.'founded_date', 'employer');
		if ( empty($min_max) ) {
			return;
		}
		$min    = floor( $min_max->min );
		$max    = ceil( $min_max->max );

		if ( $min == $max ) {
			return;
		}
		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/range_slider' );
	}

	public static function filter_date_field_input($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/text_date' );
	}

	public static function filter_field_job_salary($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		$condition = array();
		if ( !empty($_GET['filter-salary_type']) ) {
			$condition = array(
				'key' => WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'salary_type',
				'value' => $_GET['filter-salary_type']
			);
		}

		$salary_min = WP_Job_Board_Pro_Query::get_min_max_meta_value(WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'salary', 'job_listing', $condition);
		$salary_max = WP_Job_Board_Pro_Query::get_min_max_meta_value(WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'max_salary', 'job_listing', $condition);
		if ( empty($salary_min) && empty($salary_max) ) {
			return;
		}
		$min = $max = 0;
		// $min = $salary_min->min < $salary_max->min ? $salary_min->min : $salary_max->min;
		$max = $salary_min->max > $salary_max->max ? $salary_min->max : $salary_max->max;
		
		if ( $min >= $max ) {
			return;
		}
		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/salary_range_slider' );
	}

	public static function filter_field_candidate_salary($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		$condition = array();
		if ( !empty($_GET['filter-salary-type']) ) {
			$condition = array(
				'key' => WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'salary_type',
				'value' => $_GET['filter-salary-type']
			);
		}

		$min_max = WP_Job_Board_Pro_Query::get_min_max_meta_value(WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'salary', 'candidate', $condition);
		
		if ( empty($min_max) ) {
			return;
		}
		$min    = 0;
		$max    = ceil( $min_max->max );
		
		if ( $min >= $max ) {
			return;
		}
		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/salary_range_slider' );
	}

	public static function filter_field_checkbox($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/checkbox' );
	}

	public static function filter_field_select($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		$options = array();

		if ( !empty($field['options']) ) {
			foreach ($field['options'] as $key => $value) {
				$options[] = array(
					'value' => $key,
					'text' => $value,
				);
			}
		}

		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/select' );
	}

	public static function filter_field_multiselect($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		$options = array();

		if ( !empty($field['options']) ) {
			foreach ($field['options'] as $key => $value) {
				$options[] = array(
					'value' => $key,
					'text' => $value,
				);
			}
		}

		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/multiselect' );
	}

	public static function filter_field_radio_list($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		$options = array();

		if ( !empty($field['options']) ) {
			foreach ($field['options'] as $key => $value) {
				$options[] = array(
					'value' => $key,
					'text' => $value,
				);
			}
		}

		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/radios' );
	}

	public static function filter_field_check_list($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		$options = array();

		if ( !empty($field['options']) ) {
			foreach ($field['options'] as $key => $value) {
				$options[] = array(
					'value' => $key,
					'text' => $value,
				);
			}
		}

		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/check_list' );
	}

	public static function filter_field_taxonomy_radio_list($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		$options = array();
		$query_args = apply_filters('wp-job-board-pro-filter-field-taxonomy-radio-list-query-args', array( 'hierarchical' => 1, 'hide_empty' => true, 'orderby' => 'title'  ), $field);
		$terms = get_terms($field['taxonomy'], $query_args);

		if ( ! is_wp_error( $terms ) && ! empty ( $terms ) ) {
			foreach ($terms as $term) {
				$options[] = array(
					'value' => $term->term_id,
					'text' => $term->name,
				);
			}
		}
		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/radios' );
	}

	public static function filter_field_taxonomy_check_list($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		$options = array();
		$query_args = apply_filters('wp-job-board-pro-filter-field-taxonomy-check-list-query-args', array( 'hierarchical' => 1, 'hide_empty' => true, 'orderby' => 'title'  ), $field);
		$terms = get_terms($field['taxonomy'], $query_args);

		if ( ! is_wp_error( $terms ) && ! empty ( $terms ) ) {
			foreach ($terms as $term) {
				$options[] = array(
					'value' => $term->term_id,
					'text' => $term->name
				);
			}
		}
		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/check_list' );
	}

	public static function filter_field_taxonomy_select($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		$options = array();
		$query_args = apply_filters('wp-job-board-pro-filter-field-taxonomy-taxonomy-select-query-args', array( 'hierarchical' => 1, 'hide_empty' => true, 'orderby' => 'title'  ), $field);
		$terms = get_terms($field['taxonomy'], $query_args);

		if ( ! is_wp_error( $terms ) && ! empty ( $terms ) ) {
			foreach ($terms as $term) {
				$options[] = array(
					'value' => $term->term_id,
					'text' => $term->name
				);
			}
		}
		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/select' );
	}

	public static function filter_field_taxonomy_hierarchical_radio_list($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/tax_radios' );
	}

	public static function filter_field_taxonomy_hierarchical_check_list($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/tax_check_list' );
	}

	public static function filter_field_taxonomy_hierarchical_select($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/tax_select' );
	}

	public static function filter_field_taxonomy_select_search($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/tax_select_search' );
	}

	public static function filter_field_location_select($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
	    $selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

	    $location_type = wp_job_board_pro_get_option('location_multiple_fields', 'yes');
	    
	    if ( $location_type === 'no' ) {
	    	include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/tax_select' );
	    } else {
	    	include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/regions_select' );
	    }
	}

	public static function filter_field_location_select_search($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
	    $selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

	    $location_type = wp_job_board_pro_get_option('location_multiple_fields', 'yes');
	    
	    if ( $location_type === 'no' ) {
	    	include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/tax_select_search' );
	    } else {
	    	include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/regions_select_search' );
	    }
	}

	public static function hierarchical_tax_tree($catId, $depth, $input_name, $key, $field, $selected, $input_type = 'checkbox'){
	    $output = $return = '';
	    $next_depth = $depth + 1;

	    if ( empty($field['taxonomy']) ) {
	    	return;
	    }

	    $query_args = apply_filters('wp-job-board-pro-filter-field-taxonomy-tax-tree-query-args', array( 'hierarchical' => 1, 'hide_empty' => true, 'orderby' => 'title'  ), $field);
	    $query_args['parent'] = $catId;
		$terms = get_terms($field['taxonomy'], $query_args);

	    if ( ! is_wp_error( $terms ) && ! empty ( $terms ) ) {
	        foreach ($terms as $term) {
	            $checked = '';
	        	if ( !empty($selected) ) {
		            if ( is_array($selected) ) {
		                if ( in_array($term->term_id, $selected) ) {
		                    $checked = ' checked="checked"';
		                }
		            } elseif ( $term->term_id == $selected ) {
		                $checked = ' checked="checked"';
		            }
		        }
		        
		        $output .= '<li class="list-item">';
		        	$output .= '<div class="list-item-inner">';
		        	if ( $input_type == 'checkbox' ) {
			        	$output .= '<input id="'.esc_attr($input_name.'-'.$term->slug).'" type="checkbox" name="'.esc_attr($input_name).'[]" value="'.esc_attr($term->term_id).'" '.$checked.'>';
			        } else {
			        	$output .= '<input id="'.esc_attr($input_name.'-'.$term->slug).'" type="radio" name="'.esc_attr($input_name).'" value="'.esc_attr($term->term_id).'" '.$checked.'>';
			        }
		        	$output .= '<label for="'.esc_attr($input_name.'-'.$term->slug).'">'.wp_kses_post($term->name).'</label>';

		        	
		        	$child_output = self::hierarchical_tax_tree($term->term_id, $next_depth, $input_name, $key, $field, $selected, $input_type);
		        	if ( $child_output ) {
		        		$output .= '<span class="caret-wrapper"><span class="caret"></span></span>';
		        	}
		        	$output .= '</div>';

	            	$output .= $child_output;

	            $output .= '</li>';
	        }
	        if ( $output ) {
	        	$return = '<ul class="terms-list circle-check level-'.$depth.'">'.$output.'</ul>';
	        }
	    }

	    return $return;
	}

	public static function hierarchical_tax_option_tree($catId, $depth, $input_name, $key, $field, $selected ){
	    $output = $show_depth = '';
	    $next_depth = $depth + 1;
	    for ($i = 1; $i <= $depth; $i++) {
		    $show_depth .= '-';
		}
	    if ( empty($field['taxonomy']) ) {
	    	return;
	    }

	    $query_args = apply_filters('wp-job-board-pro-filter-field-taxonomy-option-tree-query-args', array( 'hierarchical' => 1, 'hide_empty' => true, 'orderby' => 'title'  ), $field);
	    $query_args['parent'] = $catId;
		$terms = get_terms($field['taxonomy'], $query_args);
 
	    if ( ! is_wp_error( $terms ) && ! empty ( $terms ) ) {
	        foreach ($terms as $term) {
	            $checked = '';
	        	if ( !empty($selected) ) {
		            if ( is_array($selected) ) {
		                if ( in_array($term->term_id, $selected) ) {
		                    $checked = ' checked="checked"';
		                }
		            } elseif ( $term->term_id == $selected ) {
		                $checked = ' checked="checked"';
		            }
		        }

		        $output .= '<option value="'.esc_attr($term->term_id).'" '.selected($selected, $term->term_id, false).'>';
		        	
		        	$output .= $show_depth.' '.wp_kses_post($term->name);
		        	
	            $output .= '</option>';

	            $output .= self::hierarchical_tax_option_tree($term->term_id, $next_depth, $input_name, $key, $field, $selected);
	        }
	    }

	    return $output;
	}

	public static function filter_field_employers($instance, $args, $key, $field) {
		$name = self::filter_get_name($key, $field);
		$selected = !empty( $_GET[$name] ) ? $_GET[$name] : '';

		$options = array();
		$employer_ids = WP_Job_Board_Pro_User::get_author_employers();

		if ( ! empty ( $employer_ids ) ) {
			foreach ($employer_ids as $user_id) {
				$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);

				if ( $employer_id ) {
					$options[] = array(
						'value' => $user_id,
						'text' => get_the_title($employer_id)
					);
				}
			}
		}
		include WP_Job_Board_Pro_Template_Loader::locate( 'widgets/filter-fields/check_list' );
	}
	
	public static function filter_custom_field_meta($meta_query, $params, $filter_fields) {
		if ( empty($params) || !is_array($params) ) {
			return $meta_query;
		}
		
		$cfielddate = [];
    	foreach ( $params as $key => $value ) {
    		if ( !empty($value) && strrpos( $key, 'filter-cfielddate-', -strlen( $key ) ) !== false ) {
    			$cfielddate[$key] = $value;
    		}
			if ( !empty($value) && strrpos( $key, 'filter-cfield-', -strlen( $key ) ) !== false ) {
				$custom_key = str_replace( 'filter-cfield-', '', $key );

		        if ( !empty($filter_fields[$custom_key]) ) {
		            $fielddata = $filter_fields[$custom_key];

		            $field_type = $fielddata['type'];
		            $meta_key = $custom_key;

		            switch ($field_type) {
		            	
		            	case 'text':
		            	case 'textarea':
		            	case 'wysiwyg':
		            	case 'number':
		            	case 'url':
		            	case 'email':
		            		$meta_query[] = array(
								'key'       => $meta_key,
								'value'     => $value,
								'compare'   => 'LIKE',
							);
		            		break;
	            		case 'radio':
	            		case 'select':
	            		case 'pw_select':
		            		$meta_query[] = array(
								'key'       => $meta_key,
								'value'     => $value,
								'compare'   => '=',
							);
		            		break;
	            		case 'checkbox':
	            			$meta_query[] = array(
								'key'       => $meta_key,
								'value'     => 'on',
								'compare'   => '=',
							);
							break;
	            		case 'pw_multiselect':
	            		case 'multiselect':
	            		case 'multicheck':
	            			if ( is_array($value) ) {
	            				$multi_meta = array( 'relation' => 'OR' );
	            				foreach ($value as $val) {
	            					$multi_meta[] = array(
	            						'key'       => $meta_key,
										'value'     => '"'.$val.'"',
										'compare'   => 'LIKE',
	            					);
	            				}
	            				$meta_query[] = $multi_meta;
	            			} else {
	            				$meta_query[] = array(
									'key'       => $meta_key,
									'value'     => '"'.$value.'"',
									'compare'   => 'LIKE',
								);
	            			}
	            			break;
		            }
		        }
			}
		}
		if ( !empty($cfielddate) ) {
			
			foreach ( $cfielddate as $key => $values ) {
				if ( !empty($values) && is_array($values) && count($values) == 2 ) {
					$custom_key = str_replace( 'filter-cfielddate-', '', $key );

			        if ( !empty($filter_fields[$custom_key]) ) {
			            $fielddata = $filter_fields[$custom_key];

			            $field_type = $fielddata['type'];
			            $meta_key = $custom_key;

			            
						if ( !empty($values['from']) && !empty($values['to']) ) {
							$meta_query[] = array(
					           	'key' => $meta_key,
					           	'value' => array(sanitize_text_field($values['from']), sanitize_text_field($values['to'])),
					           	'compare'   => 'BETWEEN',
								'type' 		=> 'DATE',
							);
						} elseif ( !empty($values['from']) && empty($values['to']) ) {
							$meta_query[] = array(
					           	'key' => $meta_key,
					           	'value' => sanitize_text_field($values['from']),
					           	'compare'   => '>=',
								'type' 		=> 'DATE',
					       	);
						} elseif (empty($values['from']) && !empty($values['to']) ) {
							$meta_query[] = array(
					           	'key' => $meta_key,
					           	'value' => sanitize_text_field($values['to']),
					           	'compare'   => '<=',
								'type' 		=> 'DATE',
					       	);
						}

			        }
				}
			}
		}

		return $meta_query;
	}

	public static function get_the_level($id, $type = 'job_listing_location') {
	  	return count( get_ancestors($id, $type) );
	}
}
