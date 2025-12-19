<?php
/**
 * Mixes
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Mixes {
	
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'track_post_views' ) );

		add_action( 'login_form', array( __CLASS__, 'social_login_before' ), 1 );
		add_action( 'login_form', array( __CLASS__, 'social_login_after' ), 30 );

		add_filter( 'wp_job_board_pro_filter_distance_type', array( __CLASS__, 'set_distance_type' ), 10 );

		add_filter( 'wp_job_board_pro_cmb2_field_taxonomy_location_number', array( __CLASS__, 'set_location_number' ), 10 );
		add_filter( 'wp_job_board_pro_cmb2_field_taxonomy_location_field_name_1', array( __CLASS__, 'set_first_location_label' ), 10 );
		add_filter( 'wp_job_board_pro_cmb2_field_taxonomy_location_field_name_2', array( __CLASS__, 'set_second_location_label' ), 10 );
		add_filter( 'wp_job_board_pro_cmb2_field_taxonomy_location_field_name_3', array( __CLASS__, 'set_third_location_label' ), 10 );
		add_filter( 'wp_job_board_pro_cmb2_field_taxonomy_location_field_name_4', array( __CLASS__, 'set_fourth_location_label' ), 10 );
	}

	public static function set_post_views($post_id, $prefix) {
	    $count_key = $prefix.'views_count';
	    $count = get_post_meta($post_id, $count_key, true);
	    if ( $count == '' ) {
	        $count = 0;
	        delete_post_meta($post_id, $count_key);
	        add_post_meta($post_id, $count_key, '0');
	    } else {
	        $count++;
	        update_post_meta($post_id, $count_key, $count);
	    }
	}

	public static function track_post_views() {
	    if ( is_singular('job_listing') || is_singular('employer') || is_singular('candidate') ) {
	        global $post;
	        $post_id = $post->ID;
	        $prefix = WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX;
	        if ( is_singular('employer') ) {
	        	$prefix = WP_JOB_BOARD_PRO_EMPLOYER_PREFIX;
	        } elseif ( is_singular('candidate') ) {
	        	$prefix = WP_JOB_BOARD_PRO_CANDIDATE_PREFIX;
	        }
		    self::set_post_views($post_id, $prefix);
		}
	}
	
	/**
	 * Formats number by currency settings
	 *
	 * @access public
	 * @param $price
	 * @return bool|string
	 */
	public static function format_number( $price, $decimals = false, $money_decimals = 0  ) {
		if ( empty( $price ) || ! is_numeric( $price ) ) {
			return 0;
		}
		if ( !$decimals ) {
            $money_decimals = wp_job_board_pro_get_option('money_decimals');
        }
		$money_thousands_separator = wp_job_board_pro_get_option('money_thousands_separator');
		$money_dec_point = wp_job_board_pro_get_option('money_dec_point');

		$price_parts_dot = explode( '.', $price );
		$price_parts_col = explode( ',', $price );

		if ( count( $price_parts_dot ) > 1 || count( $price_parts_col ) > 1 ) {
			$decimals = ! empty( $money_decimals ) ? $money_decimals : '0';
		} else {
			$decimals = 0;
		}

		$dec_point = ! empty( $money_dec_point ) ? $money_dec_point : '.';
		$thousands_separator = ! empty( $money_thousands_separator ) ? $money_thousands_separator : '';

		$price = number_format( $price, $decimals, $dec_point, $thousands_separator );

		return $price;
	}

	public static function is_allowed_to_remove( $user_id, $item_id ) {
		$item = get_post( $item_id );
		$author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($item_id);
		
		if ( ! empty( $author_id ) ) {
			return $author_id == $user_id ;
		}

		return false;
	}
	
	public static function redirect($redirect_url) {
		if ( ! $redirect_url ) {
			$redirect_url = home_url( '/' );
		}

		wp_redirect( $redirect_url );
		exit();
	}

	public static function sort_array_by_priority( $a, $b ) {
		if ( $a['priority'] == $b['priority'] ) {
			return 0;
		}

		return ( $a['priority'] < $b['priority'] ) ? - 1 : 1;
	}
	
	public static function get_the_level($id, $type = 'property_location') {
	  	return count( get_ancestors($id, $type) );
	}

	public static function get_default_salary_types() {
		return apply_filters( 'wp-job-board-pro-get-default-salary-types', array(
			'monthly' => __( 'Mesečno', 'wp-job-board-pro' ),
			'weekly' => __( 'Nedeljno', 'wp-job-board-pro' ),
			'daily' => __( 'Dnevno', 'wp-job-board-pro' ),
			'hourly' => __( 'Po satu', 'wp-job-board-pro' ),
			'yearly' => __( 'Godišnje', 'wp-job-board-pro' ),
		));
	}

	public static function get_default_apply_types() {
		return apply_filters( 'wp-job-board-pro-get-default-apply-types', array(
			'internal' => __( 'Internal', 'wp-job-board-pro' ),
            'external' => __( 'External URL', 'wp-job-board-pro' ),
            'with_email' => __( 'By Email', 'wp-job-board-pro' ),
            'call' => __( 'Call To Apply', 'wp-job-board-pro' ),
		));
	}

	public static function get_image_mime_types() {
		return apply_filters( 'wp-job-board-pro-get-image-mime-types', array(
			'jpg'         => 'image/jpeg',
			'jpeg'        => 'image/jpeg',
			'jpe'         => 'image/jpeg',
			'gif'         => 'image/gif',
			'png'         => 'image/png',
			'bmp'         => 'image/bmp',
			'tif|tiff'    => 'image/tiff',
			'ico'         => 'image/x-icon',
		));
	}

	public static function get_cv_mime_types() {
		return apply_filters( 'wp-job-board-pro-get-cv-mime-types', array(
			'txt'         => 'text/plain',
			'doc'         => 'application/msword',
			'docx'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'xlsx'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'xls'         => 'application/vnd.ms-excel',
			'pdf'         => 'application/pdf',
		));
	}

	public static function get_socials_network() {
		return apply_filters( 'wp-job-board-pro-get-socials-network', array(
			'facebook' => array(
				'title' => esc_html__('Facebook', 'wp-job-board-pro'),
				'icon' => 'fab fa-facebook-f',
			),
			'twitter' => array(
				'title' => esc_html__('Twitter', 'wp-job-board-pro'),
				'icon' => 'fab fa-x-twitter',
			),
			'linkedin' => array(
				'title' => esc_html__('Linkedin', 'wp-job-board-pro'),
				'icon' => 'fab fa-linkedin-in',
			),
			'dribbble' => array(
				'title' => esc_html__('Dribbble', 'wp-job-board-pro'),
				'icon' => 'fab fa-dribbble',
			),
			'tumblr' => array(
				'title' => esc_html__('Tumblr', 'wp-job-board-pro'),
				'icon' => 'fab fa-tumblr',
			),
			'pinterest' => array(
				'title' => esc_html__('Pinterest', 'wp-job-board-pro'),
				'icon' => 'fab fa-pinterest',
			),
			'instagram' => array(
				'title' => esc_html__('Instagram', 'wp-job-board-pro'),
				'icon' => 'fab fa-instagram',
			),
			'youtube' => array(
				'title' => esc_html__('Youtube', 'wp-job-board-pro'),
				'icon' => 'fab fa-youtube',
			),
			'tiktok' => array(
				'title' => esc_html__('Tiktok', 'wp-job-board-pro'),
				'icon' => 'fab fa-tiktok',
			),
			'telegram' => array(
				'title' => esc_html__('Telegram', 'wp-job-board-pro'),
				'icon' => 'fab fa-telegram',
			),
			'discord' => array(
				'title' => esc_html__('Discord', 'wp-job-board-pro'),
				'icon' => 'fab fa-discord',
			),
		));
	}

	public static function get_jobs_page_url() {
		if ( is_post_type_archive('job_listing') ) {
			$url = get_post_type_archive_link( 'job_listing' );
		} elseif (is_tax()) {
			$url = '';
			$taxs = ['type', 'category', 'location', 'tag'];
			foreach ($taxs as $tax) {
				if ( is_tax('job_listing_'.$tax) ) {
					global $wp_query;
					$term = $wp_query->queried_object;
					if ( isset( $term->slug) ) {
						$url = get_term_link($term, 'job_listing_'.$tax);
					}
				}
			}
		} else {
			global $post;
			if ( is_page() && is_object($post) && basename( get_page_template() ) == 'page-jobs.php' ) {
				$url = get_permalink($post->ID);
			} else {
				$jobs_page_id = wp_job_board_pro_get_option('jobs_page_id');
				$jobs_page_id = self::get_lang_post_id( $jobs_page_id, 'page');
				if ( $jobs_page_id ) {
					$url = get_permalink($jobs_page_id);
				} else {
					$url = get_post_type_archive_link( 'job_listing' );
				}
			}
		}
		return apply_filters( 'wp-job-board-pro-get-jobs-page-url', $url );
	}

	public static function get_employers_page_url() {
		if ( is_post_type_archive('employer') ) {
			$url = get_post_type_archive_link( 'employer' );
		} elseif (is_tax()) {
			$url = '';
			$taxs = ['category', 'location'];
			foreach ($taxs as $tax) {
				if ( is_tax('employer_'.$tax) ) {
					global $wp_query;
					$term = $wp_query->queried_object;
					if ( isset( $term->slug) ) {
						$url = get_term_link($term, 'employer_'.$tax);
					}
				}
			}
		} else {
			global $post;
			if ( is_page() && is_object($post) && basename( get_page_template() ) == 'page-employers.php' ) {
				$url = get_permalink($post->ID);
			} else {
				$employers_page_id = wp_job_board_pro_get_option('employers_page_id');
				$employers_page_id = self::get_lang_post_id( $employers_page_id, 'page');
				if ( $employers_page_id ) {
					$url = get_permalink($employers_page_id);
				} else {
					$url = get_post_type_archive_link( 'employer' );
				}
			}
		}
		return apply_filters( 'wp-job-board-pro-get-employers-page-url', $url );
	}

	public static function get_candidates_page_url() {
		if ( is_post_type_archive('candidate') ) {
			$url = get_post_type_archive_link( 'candidate' );
		} elseif (is_tax()) {
			$url = '';
			$taxs = ['category', 'location', 'tag'];
			foreach ($taxs as $tax) {
				if ( is_tax('candidate_'.$tax) ) {
					global $wp_query;
					$term = $wp_query->queried_object;
					if ( isset( $term->slug) ) {
						$url = get_term_link($term, 'candidate_'.$tax);
					}
				}
			}
		} else {
			global $post;
			if ( is_page() && is_object($post) && basename( get_page_template() ) == 'page-candidates.php' ) {
				$url = get_permalink($post->ID);
			} else {
				$candidates_page_id = wp_job_board_pro_get_option('candidates_page_id');
				$candidates_page_id = self::get_lang_post_id( $candidates_page_id, 'page');
				if ( $candidates_page_id ) {
					$url = get_permalink($candidates_page_id);
				} else {
					$url = get_post_type_archive_link( 'candidate' );
				}
			}
		}
		return apply_filters( 'wp-job-board-pro-get-candidates-page-url', $url );
	}

	public static function get_lang_post_id($post_id, $post_type = 'page') {
	    return apply_filters( 'wp-job-board-pro-post-id', $post_id, $post_type);
	}

	public static function custom_pagination( $args = array() ) {
    	global $wp_rewrite;
        
        $args = wp_parse_args( $args, array(
			'prev_text' => '<i class="flaticon-left-arrow"></i>'.esc_html__('Prev', 'wp-job-board-pro'),
			'next_text' => esc_html__('Next','wp-job-board-pro').'<i class="flaticon-right-arrow"></i>',
			'max_num_pages' => 10,
			'echo' => true,
			'class' => '',
		));

        if ( !empty($args['wp_query']) ) {
        	$wp_query = $args['wp_query'];
        } else {
        	global $wp_query;
        }

        if ( $wp_query->max_num_pages < 2 ) {
			return;
		}

    	$pages = $args['max_num_pages'];

    	$current = !empty($wp_query->query_vars['paged']) && $wp_query->query_vars['paged'] > 1 ? $wp_query->query_vars['paged'] : 1;
        if ( empty($pages) ) {
            global $wp_query;
            $pages = $wp_query->max_num_pages;
            if ( !$pages ) {
                $pages = 1;
            }
        }
        $pagination = array(
            'base' => @add_query_arg('paged','%#%'),
            'format' => '',
            'total' => $pages,
            'current' => $current,
            'prev_text' => $args['prev_text'],
            'next_text' => $args['next_text'],
            'type' => 'array'
        );

		$pagenum_link = html_entity_decode( get_pagenum_link() );
		$query_args   = array();
		$url_parts    = explode( '?', $pagenum_link );

		if ( isset( $url_parts[1] ) ) {
			wp_parse_str( $url_parts[1], $query_args );
		}

		$pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
		$pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

		$format  = $wp_rewrite->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
		$format .= $wp_rewrite->using_permalinks() ? user_trailingslashit( $wp_rewrite->pagination_base . '/%#%', 'paged' ) : '?paged=%#%';

		$add_args = array();
		if ( !empty($query_args) ) {
			foreach ($query_args as $key => $value) {
				if ( is_array($value) ) {
					$add_args[$key] = array_map( 'urlencode', $value );
				} else {
					$add_args[$key] = $value;
				}
			}
		}

		$pagination['base'] = $pagenum_link;
		$pagination['format'] = $format;
		$pagination['add_args'] = $add_args;
        

        $sq = '';
        if ( isset($_GET['s']) ) {
            $cq = $_GET['s'];
            $sq = str_replace(" ", "+", $cq);
        }
        
        if ( !empty($wp_query->query_vars['s']) ) {
            $pagination['add_args'] = array( 's' => $sq);
        }
        $pagination = apply_filters( 'wp-job-board-pro-custom-pagination', $pagination );

        $paginations = paginate_links( $pagination );
        $output = '';
        if ( !empty($paginations) ) {
            $output .= '<ul class="pagination '.esc_attr( $args["class"] ).'">';
                foreach ($paginations as $key => $pg) {
                    $output .= '<li>'. $pg .'</li>';
                }
            $output .= '</ul>';
        }
    	
        if ( $args["echo"] ) {
        	echo wp_kses_post($output);
        } else {
        	return $output;
        }
    }

    public static function custom_pagination2( $args = array() ) {
    	global $wp_rewrite;
        
        $args = wp_parse_args( $args, array(
			'prev_text' => '<i class="flaticon-left-arrow"></i>'.esc_html__('Prev', 'wp-job-board-pro'),
			'next_text' => esc_html__('Next','wp-job-board-pro').'<i class="flaticon-right-arrow"></i>',
			'echo' => true,
			'class' => '',
			'per_page' => '',
			'max_num_pages' => '',
			'current' => '',
		));

        if ( $args['max_num_pages'] < 2 ) {
			return;
		}

    	$pages = $args['max_num_pages'];

    	$current = !empty($args['current']) && $args['current'] > 1 ? $args['current'] : 1;
        
        $pagination = array(
            'base' => @add_query_arg('paged','%#%'),
            'format' => '',
            'total' => $pages,
            'current' => $current,
            'prev_text' => $args['prev_text'],
            'next_text' => $args['next_text'],
            'type' => 'array'
        );

		$pagenum_link = html_entity_decode( get_pagenum_link() );
		$query_args   = array();
		$url_parts    = explode( '?', $pagenum_link );

		if ( isset( $url_parts[1] ) ) {
			wp_parse_str( $url_parts[1], $query_args );
		}

		$pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
		$pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

		$format  = $wp_rewrite->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
		$format .= $wp_rewrite->using_permalinks() ? user_trailingslashit( $wp_rewrite->pagination_base . '/%#%', 'paged' ) : '?paged=%#%';

		$add_args = array();
		if ( !empty($query_args) ) {
			foreach ($query_args as $key => $value) {
				if ( is_array($value) ) {
					$add_args[$key] = array_map( 'urlencode', $value );
				} else {
					$add_args[$key] = $value;
				}
			}
		}

		$pagination['base'] = $pagenum_link;
		$pagination['format'] = $format;
		$pagination['add_args'] = $add_args;
        
        $sq = '';
        if ( isset($_GET['s']) ) {
            $cq = $_GET['s'];
            $sq = str_replace(" ", "+", $cq);
        }
        
        $pagination = apply_filters( 'wp-job-board-pro-custom-pagination2', $pagination );

        $paginations = paginate_links( $pagination );
        $output = '';
        if ( !empty($paginations) ) {
            $output .= '<ul class="pagination '.esc_attr( $args["class"] ).'">';
                foreach ($paginations as $key => $pg) {
                    $output .= '<li>'. $pg .'</li>';
                }
            $output .= '</ul>';
        }
    	
        if ( $args["echo"] ) {
        	echo wp_kses_post($output);
        } else {
        	return $output;
        }
    }

    public static function query_string_form_fields( $values = null, $exclude = array(), $current_key = '', $return = false ) {
		if ( is_null( $values ) ) {
			$values = $_GET; // WPCS: input var ok, CSRF ok.
		} elseif ( is_string( $values ) ) {
			$url_parts = wp_parse_url( $values );
			$values    = array();

			if ( ! empty( $url_parts['query'] ) ) {
				parse_str( $url_parts['query'], $values );
			}
		}
		$html = '';

		foreach ( $values as $key => $value ) {
			if ( in_array( $key, $exclude, true ) ) {
				continue;
			}
			if ( $current_key ) {
				$key = $current_key . '[' . $key . ']';
			}
			if ( is_array( $value ) ) {
				$html .= self::query_string_form_fields( $value, $exclude, $key, true );
			} else {
				$html .= '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( wp_unslash( $value ) ) . '" />';
			}
		}

		if ( $return ) {
			return $html;
		}

		echo $html; // WPCS: XSS ok.
	}

	public static function is_ajax_request() {
	    if ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) {
	        return true;
	    }
	    return false;
	}

	public static function get_full_current_url() {
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
		    $link = "https"; 
		} else {
		    $link = "http"; 
		}
		  
		// Here append the common URL characters. 
		$link .= "://"; 
		  
		// Append the host(domain name, ip) to the URL. 
		$link .= $_SERVER['HTTP_HOST']; 
		  
		// Append the requested resource location to the URL 
		$link .= $_SERVER['REQUEST_URI']; 
		      
		// Print the link 
		return $link; 
	}

	public static function check_social_login_enable() {
		$facebook = WP_Job_Board_Pro_Social_Facebook::get_instance();
		$google = WP_Job_Board_Pro_Social_Google::get_instance();
		$linkedin = WP_Job_Board_Pro_Social_Linkedin::get_instance();
		$twitter = WP_Job_Board_Pro_Social_Twitter::get_instance();
		if ( $facebook->is_facebook_login_enabled() || $google->is_google_login_enabled() || $linkedin->is_linkedin_login_enabled() || $twitter->is_twitter_login_enabled() ) {
			return true;
		}
		return false;
	}

	public static function social_login_before(){
		if ( self::check_social_login_enable() ) {
	        echo '<div class="wrapper-social-login"><div class="line-header"><span>'.esc_html__('or', 'wp-job-board-pro').'</span></div><div class="inner-social">';
	    }
    }
	
	public static function social_login_after(){
		if ( self::check_social_login_enable() ) {
	        echo '</div></div>';
	    }
    }

    public static function set_distance_type($distance_unit) {
    	$unit = wp_job_board_pro_get_option('distance_unit', 'miles');
    	if ( in_array($unit, array('miles', 'km')) ) {
    		$distance_unit = $unit;
    	}
    	return $distance_unit;
    }

    public static function random_key($length = 5) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $return = '';
        for ($i = 0; $i < $length; $i++) {
            $return .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $return;
    }

    public static function set_location_number($nb) {
    	$nb_fields = wp_job_board_pro_get_option('location_nb_fields', 1);
    	return $nb_fields;
    }

    public static function set_first_location_label($label) {
    	return wp_job_board_pro_get_option('location_1_field_label', $label);
    }

    public static function set_second_location_label($label) {
    	return wp_job_board_pro_get_option('location_2_field_label', $label);
    }

    public static function set_third_location_label($label) {
    	return wp_job_board_pro_get_option('location_3_field_label', $label);
    }

    public static function set_fourth_location_label($label) {
    	return wp_job_board_pro_get_option('location_4_field_label', $label);
    }

    public static function get_post_id_by_meta_value($meta_key, $meta_value) {
	    global $wpdb;
	    if ($meta_key != '' && $meta_value != '') {
	        $post_query = "SELECT postmeta.meta_id FROM $wpdb->postmeta AS postmeta";
	        $post_query .= " WHERE meta_key='{$meta_key}' AND meta_value='{$meta_value}'";
	        $post_query .= " LIMIT 1";
	        $results = $wpdb->get_col($post_query);
	        if (isset($results[0])) {
	            return $results[0];
	        }
	    }
	    return 0;
	}

	public static function required_add_label($obj) {
        if ( !empty($obj['name']) ) {
            return $obj['name'].' <span class="required">*</span>';
        }
        return '';
    }
}

WP_Job_Board_Pro_Mixes::init();
