<?php
/**
 * Post Type: Candidate
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Post_Type_Candidate {

	public static $prefix = WP_JOB_BOARD_PRO_CANDIDATE_PREFIX;

	public static function init() {
	  	add_action( 'init', array( __CLASS__, 'register_post_type' ) );
	  	add_action( 'admin_menu', array( __CLASS__, 'add_pending_count_to_menu' ) );
	  	
	  	add_filter( 'cmb2_admin_init', array( __CLASS__, 'metaboxes' ) );
	  	add_filter( 'cmb2_meta_boxes', array( __CLASS__, 'fields_front' ) );
	  	add_filter( 'cmb2_meta_boxes', array( __CLASS__, 'fields_resume_front' ) );
	  	add_filter( 'manage_edit-candidate_columns', array( __CLASS__, 'custom_columns' ) );
		add_action( 'manage_candidate_posts_custom_column', array( __CLASS__, 'custom_columns_manage' ) );

		add_action('restrict_manage_posts', array( __CLASS__, 'filter_candidate_by_type' ));

		add_action('save_post', array( __CLASS__, 'save_post' ), 10, 2 );

		add_action( 'pending_to_publish', array( __CLASS__, 'set_expiry_date' ) );
		add_action( 'pending_approve_to_publish', array( __CLASS__, 'set_expiry_date' ) );
		add_action( 'preview_to_publish', array( __CLASS__, 'set_expiry_date' ) );
		add_action( 'draft_to_publish', array( __CLASS__, 'set_expiry_date' ) );
		add_action( 'auto-draft_to_publish', array( __CLASS__, 'set_expiry_date' ) );
		add_action( 'expired_to_publish', array( __CLASS__, 'set_expiry_date' ) );

		add_action( 'pending_to_publish', array( __CLASS__, 'set_candidate_slug' ) );
		add_action( 'pending_approve_to_publish', array( __CLASS__, 'set_candidate_slug' ) );


		add_action( 'wp_job_board_pro_check_for_expired_jobs', array('WP_Job_Board_Pro_Candidate', 'check_for_expired_candidates') );

		add_action( 'wp_job_board_pro_email_daily_notices', array( 'WP_Job_Board_Pro_Candidate', 'send_admin_expiring_notice' ) );
		add_action( 'wp_job_board_pro_email_daily_notices', array( 'WP_Job_Board_Pro_Candidate', 'send_candidate_expiring_notice' ) );


		add_action( "cmb2_save_field_".self::$prefix."expiry_date", array( __CLASS__, 'save_expiry_date' ), 10, 3 );

		add_action( 'denied_to_publish', array( __CLASS__, 'process_denied_to_publish' ) );
		add_action( 'pending_to_publish', array( __CLASS__, 'process_pending_to_publish' ) );
	}

	public static function register_post_type() {
		$singular = __( 'Candidate', 'wp-job-board-pro' );
		$plural   = __( 'Candidates', 'wp-job-board-pro' );

		$labels = array(
			'name'                  => $plural,
			'singular_name'         => $singular,
			'add_new'               => sprintf(__( 'Add New %s', 'wp-job-board-pro' ), $singular),
			'add_new_item'          => sprintf(__( 'Add New %s', 'wp-job-board-pro' ), $singular),
			'edit_item'             => sprintf(__( 'Edit %s', 'wp-job-board-pro' ), $singular),
			'new_item'              => sprintf(__( 'New %s', 'wp-job-board-pro' ), $singular),
			'all_items'             => sprintf(__( 'All %s', 'wp-job-board-pro' ), $plural),
			'view_item'             => sprintf(__( 'View %s', 'wp-job-board-pro' ), $singular),
			'search_items'          => sprintf(__( 'Search %s', 'wp-job-board-pro' ), $singular),
			'not_found'             => sprintf(__( 'No %s found', 'wp-job-board-pro' ), $plural),
			'not_found_in_trash'    => sprintf(__( 'No %s found in Trash', 'wp-job-board-pro' ), $plural),
			'parent_item_colon'     => '',
			'menu_name'             => $plural,
		);
		$has_archive = true;
		$candidate_archive = get_option('wp_job_board_pro_candidate_archive_slug');
		if ( $candidate_archive ) {
			$has_archive = $candidate_archive;
		}
		$rewrite_slug = get_option('wp_job_board_pro_candidate_base_slug');
		if ( empty($rewrite_slug) ) {
			$rewrite_slug = _x( 'candidate', 'Candidate slug - resave permalinks after changing this', 'wp-job-board-pro' );
		}
		$rewrite = array(
			'slug'       => $rewrite_slug,
			'with_front' => false
		);
		register_post_type( 'candidate',
			array(
				'labels'            => $labels,
				'supports'          => array( 'title', 'editor', 'thumbnail', 'comments' ),
				'public'            => true,
				'has_archive'       => $has_archive,
				'rewrite'           => $rewrite,
				'menu_position'     => 51,
				'categories'        => array(),
				'menu_icon'         => 'dashicons-admin-post',
				'show_in_rest'		=> true,
				'capabilities' => array(
				    'create_posts' => false,
				),
				'map_meta_cap' => true,
			)
		);
	}

	/**
	 * Adds pending count to WP admin menu label
	 *
	 * @access public
	 * @return void
	 */
	public static function add_pending_count_to_menu() {
		global $menu;
		$menu_item_index = null;

		foreach( $menu as $index => $menu_item ) {
			if ( ! empty( $menu_item[5] ) && $menu_item[5] == 'menu-posts-candidate' ) {
				$menu_item_index = $index;
				break;
			}
		}

		if ( $menu_item_index ) {
			$count_candidate = wp_count_posts( 'candidate' );
			
			$pending_approve = $count_candidate->pending_approve;
			$pending = $count_candidate->pending;
			$count = $pending_approve + $pending;

			if ( $count > 0 ) {
				$menu_title = $menu[ $menu_item_index ][0];
				$menu_title = sprintf('%s <span class="awaiting-mod"><span class="pending-count">%d</span></span>', $menu_title, $count );
				$menu[ $menu_item_index ][0] = $menu_title;
			}
		}
	}

	public static function save_expiry_date($updated, $action, $obj) {
		if ( $action != 'disabled' ) {
			$key = self::$prefix.'expiry_date';
			$data_to_save = $obj->data_to_save;
			$post_id = !empty($data_to_save['post_ID']) ? $data_to_save['post_ID'] : '';
			$expiry_date = isset($data_to_save[$key]) ? $data_to_save[$key] : '';
			if ( empty( $expiry_date ) ) {
				if ( wp_job_board_pro_get_option( 'resume_duration' ) ) {
					$expires = WP_Job_Board_Pro_Candidate::calculate_candidate_expiry( $post_id );
					update_post_meta( $post_id, $key, $expires );
				} else {
					delete_post_meta( $post_id, $key );
				}
			} else {
				update_post_meta( $post->ID, self::$prefix.'expiry_date', date( 'Y-m-d', strtotime( sanitize_text_field( $expiry_date ) ) ) );
			}

		}
	}

	public static function save_post($post_id, $post) {
		if ( $post->post_type === 'candidate' ) {
			$post_args = array( 'ID' => $post_id );
			
			if ( !empty($_POST[self::$prefix . 'urgent']) ) {
				$post_args['menu_order'] = -2;
			} elseif ( !empty($_POST[self::$prefix . 'featured']) ) {
				$post_args['menu_order'] = -1;
			} else {
				$post_args['menu_order'] = 0;
			}

			$expiry_date = get_post_meta( $post_id, self::$prefix.'expiry_date', true );
			$today_date = date( 'Y-m-d', current_time( 'timestamp' ) );
			$is_candidate_listing_expired = $expiry_date && $today_date > $expiry_date;

			if ( $is_candidate_listing_expired && ! WP_Job_Board_Pro_Candidate::is_candidate_status_changing( null, 'draft' ) ) {

				if ( !empty($_POST) ) {
					if ( WP_Job_Board_Pro_Candidate::is_candidate_status_changing( 'expired', 'publish' ) ) {
						if ( empty($_POST[self::$prefix.'expiry_date']) || strtotime( $_POST[self::$prefix.'expiry_date'] ) < current_time( 'timestamp' ) ) {
							$expires = WP_Job_Board_Pro_Candidate::calculate_candidate_expiry( $post_id );
							update_post_meta( $post_id, self::$prefix.'expiry_date', WP_Job_Board_Pro_Candidate::calculate_candidate_expiry( $post_id ) );
							if ( isset( $_POST[self::$prefix.'expiry_date'] ) ) {
								$_POST[self::$prefix.'expiry_date'] = $expires;
							}
						}
					} else {
						$post_args['post_status'] = 'expired';
					}
				}
			}

			WP_Job_Board_Pro_Mpdf::mpdf_delete_file($post);

			$profile_percents = WP_Job_Board_Pro_User::compute_profile_percent($post_id);
			if ( isset($profile_percents['percent']) ) {
				update_post_meta($post_id, self::$prefix .'profile_percent', $profile_percents['percent']);
			}

			remove_action('save_post', array( __CLASS__, 'save_post' ), 10, 2 );
			wp_update_post( $post_args );
			add_action('save_post', array( __CLASS__, 'save_post' ), 10, 2 );

			delete_transient( 'wp_job_board_pro_filter_counts' );
			
			clean_post_cache( $post_id );
		}
	}

	public static function set_expiry_date( $post ) {

		if ( $post->post_type === 'candidate' ) {

			// See if it is already set.
			if ( metadata_exists( 'post', $post->ID, self::$prefix.'expiry_date' ) ) {
				$expires = get_post_meta( $post->ID, self::$prefix.'expiry_date', true );

				// if ( $expires && strtotime( $expires ) < current_time( 'timestamp' ) ) {
				// 	update_post_meta( $post->ID, self::$prefix.'expiry_date', '' );
				// }
			}

			// See if the user has set the expiry manually.
			if ( ! empty( $_POST[self::$prefix.'expiry_date'] ) ) {
				update_post_meta( $post->ID, self::$prefix.'expiry_date', date( 'Y-m-d', strtotime( sanitize_text_field( $_POST[self::$prefix.'expiry_date'] ) ) ) );
			} elseif ( ! isset( $expires ) ) {
				// No manual setting? Lets generate a date if there isn't already one.
				$expires = WP_Job_Board_Pro_Candidate::calculate_candidate_expiry( $post->ID );
				update_post_meta( $post->ID, self::$prefix.'expiry_date', $expires );

				// In case we are saving a post, ensure post data is updated so the field is not overridden.
				if ( isset( $_POST[self::$prefix.'expiry_date'] ) ) {
					$_POST[self::$prefix.'expiry_date'] = $expires;
				}
			}
		}
	}

	public static function set_candidate_slug( $post ) {

		if ( $post->post_type === 'candidate' ) {
			if ( wp_job_board_pro_get_option('candidate_slug_candidate') == 'number' ) {
				$rand_num = mt_rand(100000000000,999999999999);
				$post_args = array(
	                'post_name' => $rand_num,
	                'ID' => $post->ID,
	            );

	            $post_id = wp_update_post( $post_args, true );
            }
		}
	}

	public static function process_denied_to_publish($post) {
		if ( $post->post_type === 'candidate' ) {
			$user_id = WP_Job_Board_Pro_User::get_user_by_candidate_id($post->ID);
			remove_action('denied_to_publish', array( __CLASS__, 'process_denied_to_publish' ) );
			do_action( 'wp_job_board_pro_new_user_approve_approve_user', $user_id );
			add_action( 'denied_to_publish', array( __CLASS__, 'process_denied_to_publish' ) );
		}
	}
	
	public static function process_pending_to_publish($post) {
		if ( $post->post_type === 'candidate' ) {
			$user_id = WP_Job_Board_Pro_User::get_user_by_candidate_id($post->ID);
			remove_action('pending_to_publish', array( __CLASS__, 'process_pending_to_publish' ) );
			do_action( 'wp_job_board_pro_new_user_approve_approve_user', $user_id );
			add_action( 'pending_to_publish', array( __CLASS__, 'process_pending_to_publish' ) );
		}
	}

	public static function metaboxes() {
		global $pagenow;
		if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) {
			do_action('wp-job-board-pro-candidate-fields-admin');
		}
	}

	public static function fields_front( array $metaboxes ) {
		if ( ! is_admin() ) {
			$user_id = WP_Job_Board_Pro_User::get_user_id();
			$post_id = 0;
			$tags_default = '';
			if ( WP_Job_Board_Pro_User::is_candidate($user_id) ) {
				$post_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
				if ( !empty($post_id) ) {
					$post = get_post( $post_id );
					$featured_image = get_post_thumbnail_id( $post_id );
					$tags_default = implode( ', ', wp_get_object_terms( $post_id, 'candidate_tag', array( 'fields' => 'names' ) ) );
				}
			}
			
			$init_fields = apply_filters( 'wp-job-board-pro-candidate-fields-front', array(), $post_id );

			// uasort( $init_fields, array( 'WP_Job_Board_Pro_Mixes', 'sort_array_by_priority') );

			$fields = array();
			$i = 1;
			$heading_count = 0;
			$index = 0;
			foreach ($init_fields as $field) {
				$rfield = $field;
				if ( $i == 1 ) {
					if ( $field['type'] !== 'title' ) {
						$fields[] = array(
							'name' => esc_html__('General', 'wp-job-board-pro'),
							'type' => 'title',
							'id'   => self::$prefix.'heading_general_title',
							'priority' => 0,
							'before_row' => '<div id="heading-'.self::$prefix.'heading_general_title" class="before-group-row before-group-row-'.$heading_count.' active"><div class="before-group-row-inner">',
						);
						$heading_count = 1;
						$index = 0;
					}
				}
				
				if ( $rfield['id'] == self::$prefix . 'title' ) {
					$rfield['default'] = !empty( $post ) ? $post->post_title : '';
				} elseif ( $rfield['id'] == self::$prefix . 'description' ) {
					$rfield['default'] = !empty( $post ) ? $post->post_content : '';
				} elseif ( $rfield['id'] == self::$prefix . 'featured_image' ) {
					$rfield['default'] = !empty( $featured_image ) ? $featured_image : '';
				} elseif ( $rfield['id'] == self::$prefix . 'tag' ) {
					$rfield['default'] = $tags_default;
				}
				if ( $rfield['type'] == 'title' ) {
					$before_row = '';
					if ( $i > 1 ) {
						$before_row .= '</div></div>';
					}
					$classes = '';
					if ( !empty($rfield['number_columns']) ) {
						$classes = 'columns-'.$rfield['number_columns'];
					}
					$before_row .= '<div id="heading-'.$rfield['id'].'" class="before-group-row before-group-row-'.$heading_count.' '.($heading_count == 0 ? 'active' : '').' '.$classes.'"><div class="before-group-row-inner">';

					$rfield['before_row'] = $before_row;

					$heading_count++;
					$index++;
				}

				if ( $i == count($init_fields) ) {
					if ( $rfield['type'] == 'group' ){
						$rfield['after_group'] = '</div></div>';
					} else {
						$rfield['after_row'] = '</div></div>';
					}
				}

				$fields[] = $rfield;

				$i++;
			}

			$fields[] = array(
				'id'                => self::$prefix . 'post_type',
				'type'              => 'hidden',
				'default'           => 'candidate',
				'priority'          => 100,
			);

			$fields = apply_filters( 'wp-job-board-pro-candidate-fields', $fields, $post_id );

			$metaboxes[ self::$prefix . 'front' ] = array(
				'id'                        => self::$prefix . 'front',
				'title'                     => __( 'General Options', 'wp-job-board-pro' ),
				'object_types'              => array( 'candidate' ),
				'context'                   => 'normal',
				'priority'                  => 'high',
				'show_names'                => true,
				'fields'                    => $fields
			);
		}
		return $metaboxes;
	}

	public static function fields_resume_front( array $metaboxes ) {
		if ( ! is_admin() ) {
			$user_id = WP_Job_Board_Pro_User::get_user_id();
			$post_id = 0;
			$tags_default = '';
			if ( WP_Job_Board_Pro_User::is_candidate($user_id) ) {
				$post_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
				if ( !empty($post_id) ) {
					$post = get_post( $post_id );
					$featured_image = get_post_thumbnail_id( $post_id );
					$tags_default = implode( ', ', wp_get_object_terms( $post_id, 'candidate_tag', array( 'fields' => 'names' ) ) );
				}
			}

			$init_fields = apply_filters( 'wp-job-board-pro-candidate-fields-resume-front', array(), $post_id );

			// uasort( $init_fields, array( 'WP_Job_Board_Pro_Mixes', 'sort_array_by_priority') );

			$fields = array();
			$i = 1;
			$heading_count = 0;
			$index = 0;
			foreach ($init_fields as $field) {
				$rfield = $field;
				if ( $i == 1 ) {
					if ( $field['type'] !== 'title' ) {
						$fields[] = array(
							'name' => esc_html__('General', 'wp-job-board-pro'),
							'type' => 'title',
							'id'   => self::$prefix.'heading_general_title',
							'priority' => 0,
							'before_row' => '<div id="heading-'.self::$prefix.'heading_general_title" class="before-group-row before-group-row-'.$heading_count.' active"><div class="before-group-row-inner">',
						);
						$heading_count = 1;
						$index = 0;
					}
				}
				
				if ( $rfield['id'] == self::$prefix . 'title' ) {
					$rfield['default'] = !empty( $post ) ? $post->post_title : '';
				} elseif ( $rfield['id'] == self::$prefix . 'description' ) {
					$rfield['default'] = !empty( $post ) ? $post->post_content : '';
				} elseif ( $rfield['id'] == self::$prefix . 'featured_image' ) {
					$rfield['default'] = !empty( $featured_image ) ? $featured_image : '';
				} elseif ( $rfield['id'] == self::$prefix . 'tag' ) {
					$rfield['default'] = $tags_default;
				}

				if ( $rfield['type'] == 'title' ) {
					$before_row = '';
					if ( $i > 1 ) {
						$before_row .= '</div></div>';
					}
					$classes = '';
					if ( !empty($rfield['number_columns']) ) {
						$classes = 'columns-'.$rfield['number_columns'];
					}
					$before_row .= '<div id="heading-'.$rfield['id'].'" class="before-group-row before-group-row-'.$heading_count.' '.($heading_count == 0 ? 'active' : '').' '.$classes.'"><div class="before-group-row-inner">';

					$rfield['before_row'] = $before_row;

					$heading_count++;
					$index++;
				}

				if ( $i == count($init_fields) ) {
					if ( $rfield['type'] == 'group' ){
						$rfield['after_group'] = '</div></div>';
					} else {
						$rfield['after_row'] = '</div></div>';
					}
				}

				$fields[] = $rfield;

				$i++;
			}

			$fields[] = array(
				'id'                => self::$prefix . 'post_type',
				'type'              => 'hidden',
				'default'           => 'candidate',
				'priority'          => 100,
			);

			$fields = apply_filters( 'wp-job-board-pro-candidate-fields-resume', $fields, $post_id );
			
			$metaboxes[ self::$prefix . 'resume_front' ] = array(
				'id'                        => self::$prefix . 'resume_front',
				'title'                     => __( 'General Options', 'wp-job-board-pro' ),
				'object_types'              => array( 'candidate' ),
				'context'                   => 'normal',
				'priority'                  => 'high',
				'show_names'                => true,
				'fields'                    => $fields
			);
		}
		return $metaboxes;
	}
	/**
	 * Custom admin columns for post type
	 *
	 * @access public
	 * @return array
	 */
	public static function custom_columns($columns) {
		if ( isset($columns['comments']) ) {
			unset($columns['comments']);
		}
		if ( isset($columns['date']) ) {
			unset($columns['date']);
		}
		$fields = array_merge($columns, array(
			'thumbnail' 		=> __( 'Thumbnail', 'wp-job-board-pro' ),
			'posted' 			=> __( 'Posted', 'wp-job-board-pro' ),
			'attached-user' 	=> __( 'Attached User', 'wp-job-board-pro' ),
			'expires' 			=> __( 'Expires', 'wp-job-board-pro' ),
			'category' 			=> __( 'Category', 'wp-job-board-pro' ),
			'location' 			=> __( 'Location', 'wp-job-board-pro' ),
			'urgent' 			=> __( 'Urgent', 'wp-job-board-pro' ),
			'featured' 			=> __( 'Featured', 'wp-job-board-pro' ),
			'candidate_status'  => __( 'Status', 'wp-job-board-pro' ),
		));
		return $fields;
	}

	/**
	 * Custom admin columns implementation
	 *
	 * @access public
	 * @param string $column
	 * @return array
	 */
	public static function custom_columns_manage( $column ) {
		global $post;
		switch ( $column ) {
			case 'thumbnail':
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'thumbnail', array(
						'class' => 'attachment-thumbnail attachment-thumbnail-small logo-thumnail',
					) );
				} else {
					echo '-';
				}
				break;
			case 'posted':
				echo '<strong>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ) ) . '</strong><span><br>';
				echo ( empty( $post->post_author ) ? esc_html__( 'by a guest', 'wp-job-board-pro' ) : sprintf( esc_html__( 'by %s', 'wp-job-board-pro' ), '<a href="' . esc_url( add_query_arg( 'author', $post->post_author ) ) . '">' . esc_html( get_the_author() ) . '</a>' ) ) . '</span>';
				break;
			case 'expires':
				$expires = get_post_meta( $post->ID, self::$prefix.'expiry_date', true);
				if ( $expires ) {
					echo '<strong>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $expires ) ) ) . '</strong>';
				} else {
					echo '&ndash;';
				}
				break;
			case 'category':
				$terms = get_the_terms( get_the_ID(), 'candidate_category' );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$category = array_shift( $terms );
					echo sprintf( '<a href="?post_type=candidate&candidate_category=%s">%s</a>', $category->slug, $category->name );
				} else {
					echo '-';
				}
				break;
			case 'location':
				$terms = get_the_terms( get_the_ID(), 'candidate_location' );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$location = array_shift( $terms );
					echo sprintf( '<a href="?post_type=candidate&candidate_location=%s">%s</a>', $location->slug, $location->name );
				} else {
					echo '-';
				}
				break;
			case 'urgent':
				$urgent = get_post_meta( get_the_ID(), self::$prefix . 'urgent', true );

				if ( ! empty( $urgent ) ) {
					echo '&#10004;';
				} else {
					echo '&ndash;';
				}
				break;
			case 'featured':
				$featured = get_post_meta( get_the_ID(), self::$prefix . 'featured', true );

				if ( ! empty( $featured ) ) {
					echo '<div class="dashicons dashicons-star-filled"></div>';
				} else {
					echo '<div class="dashicons dashicons-star-empty"></div>';
				}
				break;
			case 'candidate_status':
				$status   = $post->post_status;
				$statuses = WP_Job_Board_Pro_Job_Listing::job_statuses();

				$status_text = $status;
				if ( !empty($statuses[$status]) ) {
					$status_text = $statuses[$status];
				}
				echo sprintf( '<a href="?post_type=candidate&post_status=%s">%s</a>', esc_attr( $post->post_status ), '<span class="status-' . esc_attr( $post->post_status ) . '">' . esc_html( $status_text ) . '</span>' );
				break;
			case 'attached-user':
				$user_id = get_post_meta( get_the_ID(), self::$prefix . 'user_id', true );
				$display_name = get_post_meta( get_the_ID(), self::$prefix . 'display_name', true );
				$email = get_post_meta( get_the_ID(), self::$prefix . 'email', true );
				if ( $user_id && ($user = get_user_by( 'ID', $user_id)) ) {
					$html = '<div><strong>'.$display_name.'</strong></div>';
					$html .= $email;
				} else {
					$html = '-';
				}
				if ( !empty($html) ) {
					echo wp_kses_post($html);
				}

				break;
		}
	}

	public static function filter_candidate_by_type() {
		global $typenow;
		if ($typenow == 'candidate') {
			// categories
			$selected = isset($_GET['candidate_category']) ? $_GET['candidate_category'] : '';
			$terms = get_terms( 'candidate_category', array('hide_empty' => false) );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
				?>
				<select name="candidate_category">
					<option value=""><?php esc_html_e('All categories', 'wp-job-board-pro'); ?></option>
				<?php
				foreach ($terms as $term) {
					?>
					<option value="<?php echo esc_attr($term->slug); ?>" <?php echo trim($term->slug == $selected ? ' selected="selected"' : '') ; ?>><?php echo esc_html($term->name); ?></option>
					<?php
				}
				?>
				</select>
				<?php
			}
		}
	}
	
}
WP_Job_Board_Pro_Post_Type_Candidate::init();