<?php

if ( ! function_exists( 'superio_body_classes' ) ) {
	function superio_body_classes( $classes ) {
		global $post;
		$show_footer_mobile = superio_get_config('show_footer_mobile', true);

		if ( is_page() && is_object($post) ) {
			$class = get_post_meta( $post->ID, 'apus_page_extra_class', true );
			if ( !empty($class) ) {
				$classes[] = trim($class);
			}
			if(get_post_meta( $post->ID, 'apus_page_header_transparent',true) && get_post_meta( $post->ID, 'apus_page_header_transparent',true) == 'yes' ){
				$classes[] = 'header_transparent';
			}

			// breadcrumbs
			if( superio_is_wp_job_board_pro_activated() ) {
				if ( superio_is_jobs_page() || superio_is_employers_page() || superio_is_candidates_page() ) {
					$show = get_post_meta( $post->ID, 'apus_page_show_breadcrumb', true );
					if ( $show == 'no' ) {
						$classes[] = 'no-breadcrumbs';
					}
				}
			}

		}
		if ( superio_get_config('preload', true) ) {
			$classes[] = 'apus-body-loading';
		}
		if ( superio_get_config('image_lazy_loading') ) {
			$classes[] = 'image-lazy-loading';
		}
		if ( $show_footer_mobile ) {
			$classes[] = 'body-footer-mobile';
		}

		if( superio_is_wp_job_board_pro_activated() ) {
			$layout_type = '';
			if ( superio_is_jobs_page() ) {
				$layout_type = superio_get_jobs_layout_type();
				$show = superio_get_config('show_job_breadcrumbs', true);
				if ( !is_page() && !$show ) {
					$classes[] = 'no-breadcrumbs';
				}
			} elseif ( superio_is_employers_page() ) {
				$layout_type = superio_get_employers_layout_type();
				$show = superio_get_config('show_employer_breadcrumbs', true);
				if ( !is_page() && !$show ) {
					$classes[] = 'no-breadcrumbs';
				}
			} elseif ( superio_is_candidates_page() ) {
				$layout_type = superio_get_candidates_layout_type();
				$show = superio_get_config('show_candidate_breadcrumbs', true);
				if ( !is_page() && !$show ) {
					$classes[] = 'no-breadcrumbs';
				}
			}

			if ( $layout_type == 'half-map' ) {
				$classes[] = 'no-footer';
				$classes[] = 'fix-header';
			} elseif ( $layout_type == 'fullwidth' ) {
				$classes[] = 'fix-header';
			}
		}
		if ( superio_get_config('keep_header') ) {
			$classes[] = 'has-header-sticky';
		}
		



		return $classes;
	}
	add_filter( 'body_class', 'superio_body_classes' );
}

if ( !function_exists('superio_get_header_layouts') ) {
	function superio_get_header_layouts() {
		$headers = array();
		$args = array(
			'posts_per_page'   => -1,
			'offset'           => 0,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'post_type'        => 'apus_header',
			'post_status'      => 'publish',
			'suppress_filters' => true 
		);
		$posts = get_posts( $args );
		foreach ( $posts as $post ) {
			$headers[$post->post_name] = $post->post_title;
		}
		return $headers;
	}
}

if ( !function_exists('superio_get_header_layout') ) {
	function superio_get_header_layout() {
		global $post;
		if ( is_page() && is_object($post) && isset($post->ID) ) {
			global $post;
			$header = get_post_meta( $post->ID, 'apus_page_header_type', true );
			if ( empty($header) || $header == 'global' ) {
				return superio_get_config('header_type');
			}
			return $header;
		}
		return superio_get_config('header_type');
	}
	add_filter( 'superio_get_header_layout', 'superio_get_header_layout' );
}

function superio_display_header_builder($header_slug) {
	$args = array(
		'name'        => $header_slug,
		'post_type'   => 'apus_header',
		'post_status' => 'publish',
		'numberposts' => 1,
		'fields' => 'ids'
	);
	$post_ids = get_posts($args);
	foreach ( $post_ids as $post_id ) {
		$post_id = superio_get_lang_post_id($post_id, 'apus_header');
		$post = get_post($post_id);

		if ( superio_get_config('keep_header') ) {
			$classes = array('apus-header');
		}else{
			$classes = array('apus-header no_keep_header');
		}
		$classes[] = $post->post_name.'-'.$post->ID;

		if ( superio_get_config('separate_header_mobile', true) ) {
			$classes[] = 'visible-lg';
		}

		echo '<div id="apus-header" class="'.esc_attr(implode(' ', $classes)).'">';
		if ( superio_get_config('keep_header') ) {
	        echo '<div class="main-sticky-header">';
	    }
			echo apply_filters( 'superio_generate_post_builder', do_shortcode( $post->post_content ), $post, $post->ID);
		if ( superio_get_config('keep_header') ) {
			echo '</div>';
	    }
		echo '</div>';
	}
}

if ( !function_exists('superio_get_footer_layouts') ) {
	function superio_get_footer_layouts() {
		$footers = array();
		$args = array(
			'posts_per_page'   => -1,
			'offset'           => 0,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'post_type'        => 'apus_footer',
			'post_status'      => 'publish',
			'suppress_filters' => true 
		);
		$posts = get_posts( $args );
		foreach ( $posts as $post ) {
			$footers[$post->post_name] = $post->post_title;
		}
		return $footers;
	}
}

if ( !function_exists('superio_get_footer_layout') ) {
	function superio_get_footer_layout() {
		if ( is_page() ) {
			global $post;
			$footer = '';
			if ( is_object($post) && isset($post->ID) ) {
				$footer = get_post_meta( $post->ID, 'apus_page_footer_type', true );
				if ( empty($footer) || $footer == 'global' ) {
					return superio_get_config('footer_type', '');
				}
			}
			return $footer;
		}
		return superio_get_config('footer_type', '');
	}
	add_filter('superio_get_footer_layout', 'superio_get_footer_layout');
}

function superio_display_footer_builder($footer_slug) {
	$args = array(
		'name'        => $footer_slug,
		'post_type'   => 'apus_footer',
		'post_status' => 'publish',
		'numberposts' => 1,
		'fields' => 'ids'
	);
	$post_ids = get_posts($args);

	foreach ( $post_ids as $post_id ) {
		$post_id = superio_get_lang_post_id($post_id, 'apus_footer');
		$post = get_post($post_id);

		$classes = array('apus-footer footer-builder-wrapper');
		$classes[] = $post->post_name;

		echo '<div id="apus-footer" class="'.esc_attr(implode(' ', $classes)).'">';
		echo '<div class="apus-footer-inner">';
		echo apply_filters( 'superio_generate_post_builder', do_shortcode( $post->post_content ), $post, $post->ID);
		echo '</div>';
		echo '</div>';
	}
}

if ( !function_exists('superio_blog_content_class') ) {
	function superio_blog_content_class( $class ) {
		$page = 'archive';
		if ( is_singular( 'post' ) ) {
            $page = 'single';
        }
		if ( superio_get_config('blog_'.$page.'_fullwidth') ) {
			return 'container-fluid';
		}
		return $class;
	}
}
add_filter( 'superio_blog_content_class', 'superio_blog_content_class', 1 , 1  );

if ( !function_exists('superio_get_blog_layout_configs') ) {
	function superio_get_blog_layout_configs() {
		$page = 'archive';
		if ( is_singular( 'post' ) ) {
            $page = 'single';
        }
		$left = superio_get_config('blog_'.$page.'_left_sidebar');
		$right = superio_get_config('blog_'.$page.'_right_sidebar');

		switch ( superio_get_config('blog_'.$page.'_layout') ) {
		 	case 'left-main':
			 	if ( is_active_sidebar( $left ) ) {
			 		$configs['left'] = array( 'sidebar' => $left, 'class' => 'col-md-4 col-sm-12 col-xs-12'  );
			 		$configs['main'] = array( 'class' => 'col-md-8 col-sm-12 col-xs-12 pull-right' );
			 	}
		 		break;
		 	case 'main-right':
		 		if ( is_active_sidebar( $right ) ) {
			 		$configs['right'] = array( 'sidebar' => $right,  'class' => 'col-md-4 col-sm-12 col-xs-12 pull-right' ); 
			 		$configs['main'] = array( 'class' => 'col-md-8 col-sm-12 col-xs-12' );
			 	}
		 		break;
	 		case 'main':
	 			$configs['main'] = array( 'class' => 'col-md-12 col-sm-12 col-xs-12' );
	 			break;
		 	default:
		 		if ( is_active_sidebar( 'sidebar-default' ) ) {
			 		$configs['right'] = array( 'sidebar' => 'sidebar-default',  'class' => 'col-md-4 col-xs-12' ); 
			 		$configs['main'] = array( 'class' => 'col-md-8 col-xs-12' );
			 	} else {
			 		$configs['main'] = array( 'class' => 'col-md-12 col-sm-12 col-xs-12' );
			 	}
		 		break;
		}
		if ( empty($configs) ) {
			if ( is_active_sidebar( 'sidebar-default' ) ) {
		 		$configs['right'] = array( 'sidebar' => 'sidebar-default',  'class' => 'col-md-4 col-xs-12' ); 
		 		$configs['main'] = array( 'class' => 'col-md-8 col-xs-12' );
		 	} else {
		 		$configs['main'] = array( 'class' => 'col-md-12 col-sm-12 col-xs-12' );
		 	}
		}
		return $configs; 
	}
}

if ( !function_exists('superio_page_content_class') ) {
	function superio_page_content_class( $class ) {
		global $post;
		if (is_object($post)) {
			$fullwidth = get_post_meta( $post->ID, 'apus_page_fullwidth', true );
			if ( !$fullwidth || $fullwidth == 'no' ) {
				return $class;
			}
		}
		return 'container-fluid';
	}
}
add_filter( 'superio_page_content_class', 'superio_page_content_class', 1 , 1  );

if ( !function_exists('superio_get_page_layout_configs') ) {
	function superio_get_page_layout_configs() {
		global $post;
		if ( is_object($post) ) {
			$left = get_post_meta( $post->ID, 'apus_page_left_sidebar', true );
			$right = get_post_meta( $post->ID, 'apus_page_right_sidebar', true );

			switch ( get_post_meta( $post->ID, 'apus_page_layout', true ) ) {
			 	case 'left-main':
			 		if ( is_active_sidebar( $left ) ) {
				 		$configs['left'] = array( 'sidebar' => $left, 'class' => ' col-md-4 col-sm-12 col-xs-12'  );
				 		$configs['main'] = array( 'class' => 'col-md-8 col-sm-12 col-xs-12' );
				 	}
			 		break;
			 	case 'main-right':
			 		if ( is_active_sidebar( $right ) ) {
				 		$configs['right'] = array( 'sidebar' => $right,  'class' => ' col-md-4 col-sm-12 col-xs-12' ); 
				 		$configs['main'] = array( 'class' => 'col-md-8 col-sm-12 col-xs-12' );
				 	}
			 		break;
		 		case 'main':
		 			$configs['main'] = array( 'class' => 'col-xs-12 clearfix' );
		 			break;
			 	default:
			 		if ( superio_is_woocommerce_activated() && (is_cart() || is_checkout()) ) {
			 			$configs['main'] = array( 'class' => 'col-xs-12 clearfix' );
			 		} elseif ( is_active_sidebar( 'sidebar-default' ) ) {
				 		$configs['right'] = array( 'sidebar' => 'sidebar-default',  'class' => ' col-md-4 col-sm-12 col-xs-12' ); 
				 		$configs['main'] = array( 'class' => 'col-md-8 col-sm-12 col-xs-12' );
				 	} else {
				 		$configs['main'] = array( 'class' => 'col-xs-12 clearfix' );
				 	}
			 		break;
			}

			if ( empty($configs) ) {
				if ( is_active_sidebar( 'sidebar-default' ) ) {
			 		$configs['right'] = array( 'sidebar' => 'sidebar-default',  'class' => 'col-md-3 col-sm-12 col-xs-12' ); 
			 		$configs['main'] = array( 'class' => 'col-md-9 col-sm-12 col-xs-12' );
			 	} else {
			 		$configs['main'] = array( 'class' => 'col-xs-12 clearfix' );
			 	}
			}
		} else {
			$configs['main'] = array( 'class' => 'col-xs-12' );
		}
		return $configs; 
	}
}

if ( !function_exists( 'superio_random_key' ) ) {
    function superio_random_key($length = 5) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $return = '';
        for ($i = 0; $i < $length; $i++) {
            $return .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $return;
    }
}

if ( !function_exists('superio_substring') ) {
    function superio_substring($string, $limit, $afterlimit = '[...]') {
        if ( empty($string) ) {
        	return $string;
        }
       	$string = explode(' ', strip_tags( $string ), $limit);

        if (count($string) >= $limit) {
            array_pop($string);
            $string = implode(" ", $string) .' '. $afterlimit;
        } else {
            $string = implode(" ", $string);
        }
        $string = preg_replace('`[[^]]*]`','',$string);
        return strip_shortcodes( $string );
    }
}

function superio_hex2rgb( $color ) {
	$color = trim( $color, '#' );

	if ( strlen( $color ) == 3 ) {
		$r = hexdec( substr( $color, 0, 1 ).substr( $color, 0, 1 ) );
		$g = hexdec( substr( $color, 1, 1 ).substr( $color, 1, 1 ) );
		$b = hexdec( substr( $color, 2, 1 ).substr( $color, 2, 1 ) );
	} else if ( strlen( $color ) == 6 ) {
		$r = hexdec( substr( $color, 0, 2 ) );
		$g = hexdec( substr( $color, 2, 2 ) );
		$b = hexdec( substr( $color, 4, 2 ) );
	} else {
		return array();
	}

	return array( 'r' => $r, 'g' => $g, 'b' => $b );
}

function superio_generate_rgba( $rgb, $opacity ) {
	$output = 'rgba('.$rgb['r'].', '.$rgb['g'].', '.$rgb['b'].', '.$opacity.');';

	return $output;
}
/**
 * Increases or decreases the brightness of a color by a percentage of the current brightness.
 *
 * @param   string  $hexCode        Supported formats: `#FFF`, `#FFFFFF`, `FFF`, `FFFFFF`
 * @param   float   $adjustPercent  A number between -1 and 1. E.g. 0.3 = 30% lighter; -0.4 = 40% darker.
 *
 * @return  string
 *
 * @author  maliayas
 */
function superio_adjust_brightness($hexCode, $adjustPercent) {
    $hexCode = ltrim($hexCode, '#');

    if (strlen($hexCode) == 3) {
        $hexCode = $hexCode[0] . $hexCode[0] . $hexCode[1] . $hexCode[1] . $hexCode[2] . $hexCode[2];
    }

    $hexCode = array_map('hexdec', str_split($hexCode, 2));

    foreach ($hexCode as & $color) {
        $adjustableLimit = $adjustPercent < 0 ? $color : 255 - $color;
        $adjustAmount = ceil($adjustableLimit * $adjustPercent);

        $color = str_pad(dechex($color + $adjustAmount), 2, '0', STR_PAD_LEFT);
    }

    return '#' . implode($hexCode);
}

function superio_is_apus_framework_activated() {
	return defined('APUS_FRAMEWORK_VERSION') ? true : false;
}

function superio_is_cmb2_activated() {
	return defined('CMB2_LOADED') ? true : false;
}

function superio_is_woocommerce_activated() {
	return class_exists( 'woocommerce' ) ? true : false;
}

function superio_is_revslider_activated() {
	return class_exists( 'RevSlider' ) ? true : false;
}

function superio_is_mailchimp_activated() {
	return class_exists( 'MC4WP_Form_Manager' ) ? true : false;
}

function superio_is_wp_job_board_pro_activated() {
	return class_exists( 'WP_Job_Board_Pro' ) ? true : false;
}

function superio_is_wp_job_board_pro_wc_paid_listings_activated() {
	return class_exists( 'WP_Job_Board_Pro_Wc_Paid_Listings' ) ? true : false;
}

function superio_is_wp_private_message() {
	return class_exists( 'WP_Private_Message' ) ? true : false;
}


function superio_header_footer_templates( $template ) {
	$post_type = get_post_type();
	if ( $post_type ) {
		$custom_post_types = array( 'apus_footer', 'apus_header', 'apus_megamenu', 'elementor_library' );
		if ( in_array( $post_type, $custom_post_types ) ) {
			if ( is_single() ) {
				$post_type = str_replace('_', '-', $post_type);
				return get_template_directory() . '/single-apus-elementor.php';
			}
		}
	}

	return $template;
}
add_filter( 'template_include', 'superio_header_footer_templates' );



function superio_get_shortcode_atts($post_content, $shortcode_key) {
	$result = array();
	//get shortcode regex pattern wordpress function
	$pattern = get_shortcode_regex();

	if (   preg_match_all( '/'. $pattern .'/s', $post_content, $matches ) )
	{
	    $keys = array();
	    $result = array();
	    foreach( $matches[0] as $key => $value) {
	    	if ( has_shortcode( $value, $shortcode_key ) ) {
	    		// $matches[3] return the shortcode attribute as string
		        // replace space with '&' for parse_str() function
		        $get = str_replace(" ", "&" , $matches[3][$key] );
		        parse_str($get, $output);

		        //get all shortcode attribute keys
		        $keys = array_unique( array_merge(  $keys, array_keys($output)) );
		        $result[] = $output;
	    	}
	    }
	    if( $keys && $result ) {
	        // Loop the result array and add the missing shortcode attribute key
	        foreach ($result as $key => $value) {
	            // Loop the shortcode attribute key
	            foreach ($keys as $attr_key) {
	                $result[$key][$attr_key] = isset( $result[$key][$attr_key] ) ? $result[$key][$attr_key] : NULL;
	            }
	            //sort the array key
	            ksort( $result[$key]);              
	        }
	    }
	}

	return $result;
}

function superio_get_lang_post_id($post_id, $post_type = 'page') {
    return apply_filters( 'wp-job-board-pro-post-id', $post_id, $post_type);
}


add_action( 'apus_megamenu_item_config' , 'superio_add_extra_fields_menu_config', 11, 5 );
function superio_add_extra_fields_menu_config($item_id, $item, $depth, $args, $id) {
	global $wp_roles;

    $all_roles = $wp_roles->roles;
	$val = ( array ) get_post_meta( $item_id, 'apus_user_role', true );
    ?>
        <p class="field-addclass description description-wide">
            <label for="edit-menu-item-apus_user_role-<?php echo esc_attr($item_id); ?>">
                <?php  esc_html_e( 'User Role', 'superio' ); ?><br />
                <select name="menu-item-apus_user_role[<?php echo esc_attr($item_id); ?>][]" multiple="multiple">
                  	<option value=""><?php esc_html_e('All Users', 'superio'); ?></option>
                  	<?php if ( $all_roles ) {
                  		foreach ($all_roles as $key => $value) {
                  			$selected = '';
                  			if ( in_array($key, $val) ) {
                  				$selected = 'selected="selected"';
                  			}
                  			?>
                  			<option value="<?php echo esc_attr($key); ?>" <?php echo trim($selected); ?>><?php echo trim($value['name']); ?></option>
                  			<?php
                  		}
                  	}
                  	?>
                </select>
            </label>
        </p>
    <?php
}

add_action( 'wp_update_nav_menu_item', 'superio_custom_nav_update',11, 3);
function superio_custom_nav_update($menu_id, $menu_item_db_id, $args ) {
	$fields = array('apus_user_role');
	foreach ( $fields as $field ) {
		if ( isset( $_POST['menu-item-'.$field][$menu_item_db_id] ) ) {
			$custom_value = $_POST['menu-item-'.$field][$menu_item_db_id];
			update_post_meta( $menu_item_db_id, $field, $custom_value );
		}
	}
}