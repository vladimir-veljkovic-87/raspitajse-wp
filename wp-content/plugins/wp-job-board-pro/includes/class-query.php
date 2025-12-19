<?php
/**
 * Price
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Query {
	
	public static function get_posts( $params = array(), $filter_params = null ) {
		$params = wp_parse_args( $params, array(
			'post_type' => 'job_listing',
			'post_per_page' => -1,
			'paged' => 1,
			'post_status' => 'publish',
			'post__in' => array(),
			'fields' => null, // ids
			'author' => null,
			'meta_query' => null,
			'tax_query' => null,
			'orderby' => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
				'ID'         => 'DESC',
			),
			'order' => 'DESC',
			's' => '',
			'view_user_id' => ''
		));
		extract($params);

		$query_args = array(
			'post_type'         => $post_type,
			'paged'         	=> $paged,
			'posts_per_page'    => $post_per_page,
			'post_status'       => $post_status,
			'orderby'       	=> $orderby,
			'order'       		=> $order,
			'view_user_id'      => $view_user_id,
		);

		if ( !empty($post__in) ) {
	    	$query_args['post__in'] = $post__in;
	    }
	    
	    if ( !empty($s) ) {
	    	$query_args['s'] = $s;
	    }

	    if ( !empty($fields) ) {
	    	$query_args['fields'] = $fields;
	    }

	    if ( !empty($author) ) {
	    	$query_args['author'] = $author;
	    }

	    if ( !empty($meta_query) ) {
	    	$query_args['meta_query'] = $meta_query;
	    }

	    if ( !empty($tax_query) ) {
	    	$query_args['tax_query'] = $tax_query;
	    }
	    
	    $query_args = WP_Job_Board_Pro_Abstract_Filter::orderby($query_args, $filter_params);
	    
	    if ( $filter_params != null ) {
			// TODO: apply filter params
			switch ($post_type) {
				case 'job_listing':
					$query_args = WP_Job_Board_Pro_Job_Filter::get_query_var_filter($query_args, $filter_params);					

					// Meta query
					$meta_query = WP_Job_Board_Pro_Job_Filter::get_meta_filter($filter_params);
					if ( $meta_query ) {
						$query_args['meta_query'] = $meta_query;
					}

					// Tax query
					$tax_query = WP_Job_Board_Pro_Job_Filter::get_tax_filter($filter_params);
					if ( $tax_query ) {
						$query_args['tax_query'] = $tax_query;
					}

					break;
				case 'employer':
					$query_args = WP_Job_Board_Pro_Employer_Filter::get_query_var_filter($query_args, $filter_params);

					// Meta query
					$meta_query = WP_Job_Board_Pro_Employer_Filter::get_meta_filter($filter_params);
					if ( $meta_query ) {
						$query_args['meta_query'] = $meta_query;
					}

					// Tax query
					$tax_query = WP_Job_Board_Pro_Employer_Filter::get_tax_filter($filter_params);
					if ( $tax_query ) {
						$query_args['tax_query'] = $tax_query;
					}

					break;
				case 'candidate':
					$query_args = WP_Job_Board_Pro_Candidate_Filter::get_query_var_filter($query_args, $filter_params);

					// Meta query
					$meta_query = WP_Job_Board_Pro_Candidate_Filter::get_meta_filter($filter_params);
					if ( $meta_query ) {
						$query_args['meta_query'] = $meta_query;
					}
					// Tax query
					$tax_query = WP_Job_Board_Pro_Candidate_Filter::get_tax_filter($filter_params);
					if ( $tax_query ) {
						$query_args['tax_query'] = $tax_query;
					}
					break;
			}

		}
		
		$query_args = apply_filters('wp-job-board-pro-'.$post_type.'-query-args', $query_args, $filter_params);

		$query = new WP_Query( $query_args );

		return $query;
	}

	public static function get_employers( $params = array() ) {
		$params = wp_parse_args( $params, array(
			'post_per_page' => -1,
			'post_status' => 'publish',
			'ids' => array()
		));
		extract($params);

		$query_args = array(
			'post_type'         => 'employer',
			'posts_per_page'    => $post_per_page,
			'post_status'       => $post_status,
		);

		if ( !empty($ids) ) {
	    	$query_args['post__in'] = $ids;
	    }

		return new WP_Query( $query_args );
	}

	public static function get_candidates( $params = array() ) {
		$params = wp_parse_args( $params, array(
			'post_per_page' => -1,
			'post_status' => 'publish',
			'ids' => array()
		));
		extract($params);

		$query_args = array(
			'post_type'         => 'candidate',
			'posts_per_page'    => $post_per_page,
			'post_status'       => $post_status,
		);

		if ( !empty($ids) ) {
	    	$query_args['post__in'] = $ids;
	    }
	    
		return new WP_Query( $query_args );
	}

	public static function get_job_location_name( $post_id = null, $separator = ',' ) {
		static $job_locations;

		if ( null == $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! empty( $job_locations[ $post_id ] ) ) {
			return $job_locations[ $post_id ];
		}

		$locations = wp_get_post_terms( $post_id, 'job_listing_location', array(
	        'orderby'   => 'parent',
	        'order'     => 'DESC',
		) );

		if ( is_array( $locations ) && count( $locations ) > 0 ) {
			$output = '';

			$locations = array_reverse( $locations );

			foreach ( $locations as $key => $location ) {
				$output .= '<a href="' . get_term_link( $location, 'job_listing_location' ). '">' . $location->name . '</a>';

				if ( array_key_exists( $key + 1, $locations ) ) {
					$output .= ' <span class="separator">' . $separator . '</span> ';
				}
			}

			$job_locations[ $post_id ] = $output;
			return $output;
		}

		return false;
	}
	
	public static function get_min_max_meta_value( $key, $post_type = 'job_listing', $meta_condition = array() ){
	    global $wpdb;
	    $cash_key = md5($key.'_'.$post_type.json_encode($meta_condition));
	    $results = wp_cache_get($cash_key);

	    if ($results === false) {
	    	$sql  = "SELECT min( CAST( postmeta.meta_value AS UNSIGNED ) ) as min, max( CAST( postmeta.meta_value AS UNSIGNED ) ) as max FROM {$wpdb->posts} ";
			$sql .= " LEFT JOIN {$wpdb->postmeta} as postmeta ON {$wpdb->posts}.ID = postmeta.post_id ";
			$sql .= " 	WHERE {$wpdb->posts}.post_type = %s
						AND {$wpdb->posts}.post_status = 'publish'
						AND postmeta.meta_key='%s' ";
			if ( !empty($meta_condition) ) {
				$sql .= " AND {$wpdb->posts}.ID IN (
						SELECT {$wpdb->posts}.ID
						FROM {$wpdb->posts}
						LEFT JOIN {$wpdb->postmeta} as pmeta ON {$wpdb->posts}.ID = pmeta.post_id
						WHERE {$wpdb->posts}.post_type = '%s'
								AND {$wpdb->posts}.post_status = 'publish'
								AND pmeta.meta_key='%s' AND pmeta.meta_value='%s'
					) ";
				$query = $wpdb->prepare( $sql, $post_type, $key, $post_type, $meta_condition['key'], $meta_condition['value'] );
				
			} else {
		        $query = $wpdb->prepare( $sql, $post_type, $key);
		    }

	        $results = $wpdb->get_row( $query );
	        wp_cache_set( $cash_key, $results, '', DAY_IN_SECONDS );
	    }

	    return $results;
	}

	public static function get_employer_employees( $employer_id = null, $args = array() ) {
		if ( null == $employer_id ) {
			$employer_id = get_the_ID();
		}

		$args = wp_parse_args( $args, array(
			'post_per_page' => -1,
			'paged' => 1,
			'orderby' => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
				'ID'         => 'DESC',
			),
			'order' => 'DESC',
			'fields' => ''
		));
		extract($args);

		$employees = WP_Job_Board_Pro_Employer::get_post_meta($employer_id, 'employees', false);
		
		if ( $employees ) {
			$query_args = array(
				'number'    		=> $post_per_page,
				'paged'    			=> $paged,
				'orderby'    		=> $orderby,
				'order'    			=> $order,
				'role'    			=> 'wp_job_board_pro_employee',
				'include'        	=> $employees
			);
			if ( !empty($fields) ) {
				$query_args['fields'] = $fields;
			}
			return get_users( $query_args );
		}
		return false;
	}

	public static function get_employee_users( $employer_id, $args = array() ) {
		
		$args = wp_parse_args( $args, array(
			'post_per_page' => -1,
			'paged' => 1,
			'orderby' => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
				'ID'         => 'DESC',
			),
			'order' => 'DESC',
			'fields' => ''
		));
		extract($args);

		if ( $employer_id ) {
			$query_args = array(
				'number'    		=> $post_per_page,
				'paged'    			=> $paged,
				'orderby'    		=> $orderby,
				'order'    			=> $order,
				'role'    			=> 'wp_job_board_pro_employee',
				'meta_query'		=> array(
					array(
						'key'       => 'employee_employer_id',
						'value'     => $employer_id,
					),
				)
			);
			if ( !empty($fields) ) {
				$query_args['fields'] = $fields;
			}
			return get_users( $query_args );
		}
		return false;
	}

	public static function get_employee_employers( $employee_id, $args = array() ) {
		
		$args = wp_parse_args( $args, array(
			'post_per_page' => -1,
			'paged' => 1,
			'orderby' => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
				'ID'         => 'DESC',
			),
			'order' => 'DESC',
			'fields' => 'ids'
		));
		extract($args);

		$query_args = array(
			'post_type'         => 'employer',
			'post_per_page'    	=> $post_per_page,
			'paged'    			=> $paged,
			'orderby'    		=> $orderby,
			'order'    			=> $order,
			'meta_query' => array(
				array(
					'key'       => WP_JOB_BOARD_PRO_EMPLOYER_PREFIX . 'employees',
					'value'     => $employee_id,
					'compare'   => '==',
				)
			)
		);
		if ( !empty($fields) ) {
			$query_args['fields'] = $fields;
		}
		$loop = new WP_Query( $query_args );
		$return = array();
		if ( isset($loop->posts) ) {
			$return = $loop->posts;
		}
		return $return;
	}
}
