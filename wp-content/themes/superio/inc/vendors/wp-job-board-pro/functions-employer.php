<?php

function superio_get_employers( $params = array() ) {
	$params = wp_parse_args( $params, array(
		'limit' => -1,
		'post_status' => 'publish',
		'get_employers_by' => 'recent',
		'orderby' => '',
		'order' => '',
		'post__in' => array(),
		'fields' => null, // ids
		'author' => null,
		'categories' => array(),
		'locations' => array(),
	));
	extract($params);

	$query_args = array(
		'post_type'         => 'employer',
		'posts_per_page'    => $limit,
		'post_status'       => $post_status,
		'orderby'       => $orderby,
		'order'       => $order,
	);

	$meta_query = array();
	switch ($get_employers_by) {
		case 'recent':
			$query_args['orderby'] = 'date';
			$query_args['order'] = 'DESC';
			break;
		case 'featured':
			$meta_query[] = array(
				'key' => WP_JOB_BOARD_PRO_EMPLOYER_PREFIX.'featured',
	           	'value' => 'on',
	           	'compare' => '=',
			);
			break;
	}

	if ( !empty($post__in) ) {
    	$query_args['post__in'] = $post__in;
    }

    if ( !empty($fields) ) {
    	$query_args['fields'] = $fields;
    }

    if ( !empty($author) ) {
    	$query_args['author'] = $author;
    }

    $tax_query = array();
    if ( !empty($categories) ) {
    	$tax_query[] = array(
            'taxonomy'      => 'employer_category',
            'field'         => 'slug',
            'terms'         => $categories,
            'operator'      => 'IN'
        );
    }
    if ( !empty($locations) ) {
    	$tax_query[] = array(
            'taxonomy'      => 'employer_location',
            'field'         => 'slug',
            'terms'         => $locations,
            'operator'      => 'IN'
        );
    }

    if ( !empty($tax_query) ) {
    	$query_args['tax_query'] = $tax_query;
    }
    
    $meta_query[] = array(
		'relation' => 'OR',
		array(
			'key'       => WP_JOB_BOARD_PRO_EMPLOYER_PREFIX.'show_profile',
			'value'     => 'show',
			'compare'   => '==',
		),
		array(
			'key'       => WP_JOB_BOARD_PRO_EMPLOYER_PREFIX.'show_profile',
			'compare' => 'NOT EXISTS',
		),
	);

    if ( !empty($meta_query) ) {
    	$query_args['meta_query'] = $meta_query;
    }

    if ( method_exists('WP_Job_Board_Pro_Employer', 'employer_restrict_listing_query_args') ) {
	    $query_args = WP_Job_Board_Pro_Employer::employer_restrict_listing_query_args($query_args, null);
	}
	
	return new WP_Query( $query_args );
}

if ( !function_exists('superio_employer_content_class') ) {
	function superio_employer_content_class( $class ) {
		$prefix = 'employers';
		if ( is_singular( 'employer' ) ) {
            $prefix = 'employer';
        }
		if ( superio_get_config($prefix.'_fullwidth') ) {
			return 'container-fluid';
		}
		return $class;
	}
}
add_filter( 'superio_employer_content_class', 'superio_employer_content_class', 1, 1  );

if ( !function_exists('superio_get_employers_layout_configs') ) {
	function superio_get_employers_layout_configs() {
		$layout_type = superio_get_employers_layout_sidebar();
		switch ( $layout_type ) {
		 	case 'left-main':
		 		$configs['left'] = array( 'sidebar' => 'employers-filter-sidebar', 'class' => 'col-md-4 col-sm-12 col-xs-12'  );
		 		$configs['main'] = array( 'class' => 'col-md-8 col-sm-12 col-xs-12' );
		 		break;
		 	case 'main-right':
		 	default:
		 		$configs['right'] = array( 'sidebar' => 'employers-filter-sidebar',  'class' => 'col-md-4 col-sm-12 col-xs-12' ); 
		 		$configs['main'] = array( 'class' => 'col-md-8 col-sm-12 col-xs-12' );
		 		break;
	 		case 'main':
	 			$configs['main'] = array( 'class' => 'col-md-12 col-sm-12 col-xs-12' );
	 			break;
		}
		return $configs; 
	}
}

function superio_get_employers_layout_sidebar() {
	global $post;
	if ( is_page() && is_object($post) ) {
		$layout_type = get_post_meta( $post->ID, 'apus_page_layout', true );
	}
	if ( empty($layout_type) ) {
		$layout_type = superio_get_config('employers_layout_sidebar', 'main-right');
	}
	return apply_filters( 'superio_get_employers_layout_sidebar', $layout_type );
}

function superio_get_employers_layout_type() {
	global $post;
	if ( is_page() && is_object($post) ) {
		$layout_type = get_post_meta( $post->ID, 'apus_page_employers_layout_type', true );
	}
	if ( empty($layout_type) ) {
		$layout_type = superio_get_config('employers_layout_type', 'main-right');
	}
	return apply_filters( 'superio_get_employers_layout_type', $layout_type );
}

function superio_get_employers_display_mode() {
	global $post;
	if ( is_page() && is_object($post) ) {
		$columns = get_post_meta( $post->ID, 'apus_page_employers_display_mode', true );
	}
	if ( empty($columns) ) {
		$columns = superio_get_config('employers_display_mode', 3);
	}
	return apply_filters( 'superio_get_employers_columns', $columns );
}

function superio_get_employers_inner_style() {
	global $post;
	$display_mode = superio_get_employers_display_mode();
	if ( $display_mode == 'list' ) {
		if ( is_page() && is_object($post) ) {
			$inner_style = get_post_meta( $post->ID, 'apus_page_employers_inner_list_style', true );
		}
		if ( empty($inner_style) ) {
			$inner_style = superio_get_config('employers_inner_list_style', 'list');
		}
	} else {
		if ( is_page() && is_object($post) ) {
			$inner_style = get_post_meta( $post->ID, 'apus_page_employers_inner_grid_style', true );
		}
		if ( empty($inner_style) ) {
			$inner_style = superio_get_config('employers_inner_grid_style', 'grid');
		}
	}
	return apply_filters( 'superio_get_employers_inner_style', $inner_style );
}

function superio_get_employers_columns() {
	global $post;
	if ( is_page() && is_object($post) ) {
		$columns = get_post_meta( $post->ID, 'apus_page_employers_columns', true );
	}
	if ( empty($columns) ) {
		$columns = superio_get_config('employers_columns', 3);
	}
	return apply_filters( 'superio_get_employers_columns', $columns );
}

function superio_get_employer_layout_type() {
	global $post;
	$layout_type = get_post_meta($post->ID, WP_JOB_BOARD_PRO_EMPLOYER_PREFIX.'layout_type', true);
	
	if ( empty($layout_type) ) {
		$layout_type = superio_get_config('employer_layout_type', 'v1');
	}
	return apply_filters( 'superio_get_employer_layout_type', $layout_type );
}

function superio_get_employers_pagination() {
	global $post;
	if ( is_page() && is_object($post) ) {
		$pagination = get_post_meta( $post->ID, 'apus_page_employers_pagination', true );
	}
	if ( empty($pagination) ) {
		$pagination = superio_get_config('employers_pagination', 'default');
	}
	return apply_filters( 'superio_get_employers_pagination', $pagination );
}



// post per page
add_filter('wp-job-board-pro-employer-filter-query', 'superio_employer_filter_query', 10, 2);
function superio_employer_filter_query( $query, $params) {
	$query_vars = &$query->query_vars;
	$query_vars['posts_per_page'] = superio_employer_get_limit_number();
	$query->query_vars = $query_vars;
	
	return $query;
}

add_filter( 'wp-job-board-pro-employer-query-args', 'superio_employer_filter_query_args', 10, 2 );
function superio_employer_filter_query_args($query_args, $params) {
	$query_args['posts_per_page'] = superio_employer_get_limit_number();
	return $query_args;
}

function superio_employer_get_limit_number() {
	if ( isset( $_REQUEST['employers_ppp'] ) ) {
        $number = intval( $_REQUEST['employers_ppp'] );
    } elseif ( !empty($_COOKIE['employers_per_page']) ) {
        $number = intval( $_COOKIE['employers_per_page'] );
    } else {
        $value = wp_job_board_pro_get_option('number_employers_per_page', 10);
        $number = intval( $value );
    }
    return $number;
}

add_action('init', 'superio_employer_save_ppp');
function superio_employer_save_ppp() {
	if ( !empty( $_REQUEST['employers_ppp'] ) ) {
        $number = intval( $_REQUEST['employers_ppp'] );
        setcookie('employers_per_page', $number, time() + 864000);
        $_COOKIE['employers_per_page'] = $number;
    }
}

function superio_get_employers_show_filter_top() {
	$layout_type = superio_get_employers_layout_type();
	$layout_sidebar = superio_get_employers_layout_sidebar();
	$show_filter_top = false;
	if ( $layout_type !== 'top-map' && $layout_sidebar == 'main' && is_active_sidebar( 'employers-filter-top-sidebar' ) ) {
		$show_filter_top = true;
	}
	return apply_filters( 'superio_get_employers_show_filter_top', $show_filter_top );
}



add_filter('wp-job-board-pro-employer-admin-custom-fields', 'superio_employer_metaboxes_fields', 10);
function superio_employer_metaboxes_fields($fields) {
	$prefix = WP_JOB_BOARD_PRO_EMPLOYER_PREFIX;
	$layout_key = 'tab-heading-employer-layout'.rand(100,1000);
	$fields[$layout_key] = array(
		'id' => $layout_key,
		'icon' => 'dashicons-admin-appearance',
		'title'  => esc_html__( 'Layout Type', 'superio' ),
		'fields' => array(
			array(
				'name'              => esc_html__( 'Layout Type', 'superio' ),
				'id'                => $prefix . 'layout_type',
				'type'              => 'select',
				'options'			=> array(
	                '' => esc_html__('Global Settings', 'superio'),
	                'v1' => esc_html__('Version 1', 'superio'),
	                'v2' => esc_html__('Version 2', 'superio'),
	                'v3' => esc_html__('Version 3', 'superio'),
	            ),
			)
		),
	);

	return $fields;
}


function superio_is_employers_page() {
	if ( is_page() ) {
		$page_name = basename(get_page_template());
		if ( $page_name == 'page-employers.php' ) {
			return true;
		}
	} elseif( is_post_type_archive('employer') || is_tax('employer_category') || is_tax('employer_location') ) {
		return true;
	}
	return false;
}

function superio_employer_placeholder_img_src( $size = 'thumbnail' ) {
	$src               = get_template_directory_uri() . '/images/placeholder.png';
	$placeholder_image = superio_get_config('employer_placeholder_image');
	if ( !empty($placeholder_image['id']) ) {
        if ( is_numeric( $placeholder_image['id'] ) ) {
			$image = wp_get_attachment_image_src( $placeholder_image['id'], $size );

			if ( ! empty( $image[0] ) ) {
				$src = $image[0];
			}
		} else {
			$src = $placeholder_image;
		}
    }

	return apply_filters( 'superio_employer_placeholder_img_src', $src );
}