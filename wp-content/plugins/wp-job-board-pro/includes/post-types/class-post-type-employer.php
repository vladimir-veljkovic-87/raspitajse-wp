<?php
/**
 * Post Type: Employer
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Post_Type_Employer {

	public static $prefix = WP_JOB_BOARD_PRO_EMPLOYER_PREFIX;

	public static function init() {
	  	add_action( 'init', array( __CLASS__, 'register_post_type' ) );

	  	add_filter( 'cmb2_meta_boxes', array( __CLASS__, 'fields_front' ) );

	  	add_filter( 'cmb2_admin_init', array( __CLASS__, 'metaboxes' ) );

	  	add_filter( 'manage_edit-employer_columns', array( __CLASS__, 'custom_columns' ) );
		add_action( 'manage_employer_posts_custom_column', array( __CLASS__, 'custom_columns_manage' ) );

		add_action( 'restrict_manage_posts', array( __CLASS__, 'filter_employer_by_type' ) );

		add_action( 'save_post', array( __CLASS__, 'save_post' ), 10, 2 );

		add_action( 'denied_to_publish', array( __CLASS__, 'process_denied_to_publish' ) );
		add_action( 'pending_to_publish', array( __CLASS__, 'process_pending_to_publish' ) );

		add_action( 'cmb2_save_field_'.self::$prefix . 'employees', array( __CLASS__, 'save_field_employees' ), 10, 3 );
	}

	public static function register_post_type() {
		$singular = __( 'Employer', 'wp-job-board-pro' );
		$plural   = __( 'Employers', 'wp-job-board-pro' );

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
		$employer_archive = get_option('wp_job_board_pro_employer_archive_slug');
		if ( $employer_archive ) {
			$has_archive = $employer_archive;
		}
		$rewrite_slug = get_option('wp_job_board_pro_employer_base_slug');
		if ( empty($rewrite_slug) ) {
			$rewrite_slug = _x( 'employer', 'Employer slug - resave permalinks after changing this', 'wp-job-board-pro' );
		}
		$rewrite = array(
			'slug'       => $rewrite_slug,
			'with_front' => false
		);
		register_post_type( 'employer',
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

	public static function save_post($post_id, $post) {
		if ( $post->post_type === 'employer' ) {
			$arg = array( 'ID' => $post_id );
			if ( !empty($_POST[self::$prefix . 'featured']) ) {
				$arg['menu_order'] = -1;
			} else {
				$arg['menu_order'] = 0;
			}
			
			remove_action('save_post', array( __CLASS__, 'save_post' ), 10, 2 );
			wp_update_post( $arg );
			add_action('save_post', array( __CLASS__, 'save_post' ), 10, 2 );

			delete_transient( 'wp_job_board_pro_filter_counts' );
			
			clean_post_cache( $post_id );
		}
	}

	public static function process_denied_to_publish($post) {
		if ( $post->post_type === 'employer' ) {
			$user_id = WP_Job_Board_Pro_User::get_user_by_employer_id($post->ID);
			remove_action('denied_to_publish', array( __CLASS__, 'process_denied_to_publish' ) );
			do_action( 'wp_job_board_pro_new_user_approve_approve_user', $user_id );
			add_action( 'denied_to_publish', array( __CLASS__, 'process_denied_to_publish' ) );
		}
	}

	public static function process_pending_to_publish($post) {
		if ( $post->post_type === 'employer' ) {
			$user_id = WP_Job_Board_Pro_User::get_user_by_employer_id($post->ID);
			remove_action('pending_to_publish', array( __CLASS__, 'process_pending_to_publish' ) );
			do_action( 'wp_job_board_pro_new_user_approve_approve_user', $user_id );
			add_action( 'pending_to_publish', array( __CLASS__, 'process_pending_to_publish' ) );
		}
	}

	public static function save_field_employees($updated, $action, $obj) {
		if ( $action != 'disabled' ) {
			$key = self::$prefix.'employees';
			$data_to_save = $obj->data_to_save;
			$post_id = !empty($data_to_save['post_ID']) ? $data_to_save['post_ID'] : '';
			$employees = isset($data_to_save[$key]) ? $data_to_save[$key] : '';

			// remove employee employer
			$employee_users = WP_Job_Board_Pro_Query::get_employee_users($post_id, array('fields' => 'ids'));
			if ( !empty($employee_users) ) {
				foreach ($employee_users as $employee_user_id) {
					delete_user_meta($employee_user_id, 'employee_employer_id');
				}
			}
			if ( !empty( $employees ) ) {
				if ( is_array($employees) ) {
					foreach ($employees as $employee_id) {
						update_user_meta($employee_id, 'employee_employer_id', $post_id);
					}
				}
			}

		}
	}
	
	public static function metaboxes() {
		global $pagenow;
		if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) {
			do_action('wp-job-board-pro-employer-fields-admin');
		}
	}

	public static function fields_front( array $metaboxes ) {
		if ( ! is_admin() ) {
			$post_id = 0;
			$user_id = WP_Job_Board_Pro_User::get_user_id();
			if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
				$post_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
				if ( !empty($post_id) ) {
					$post = get_post( $post_id );
					$featured_image = get_post_thumbnail_id( $post_id );
				}
			}
			
			$init_fields = apply_filters( 'wp-job-board-pro-employer-fields-front', array(), $post_id );
			
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
				'default'           => 'employer',
				'priority'          => 100,
			);

			$fields = apply_filters( 'wp-job-board-pro-employer-fields', $fields, $post_id );
			
			$metaboxes[ self::$prefix . 'front' ] = array(
				'id'                        => self::$prefix . 'front',
				'title'                     => __( 'General Options', 'wp-job-board-pro' ),
				'object_types'              => array( 'employer' ),
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
			'title' 			=> __( 'Title', 'wp-job-board-pro' ),
			'thumbnail' 		=> __( 'Thumbnail', 'wp-job-board-pro' ),
			'attached-user' 	=> __( 'Attached User', 'wp-job-board-pro' ),
			'category' 			=> __( 'Category', 'wp-job-board-pro' ),
			'location' 			=> __( 'Location', 'wp-job-board-pro' ),
			'featured' 			=> __( 'Featured', 'wp-job-board-pro' ),
			'date' 				=> __( 'Date', 'wp-job-board-pro' ),
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
		switch ( $column ) {
			case 'thumbnail':
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'thumbnail', array(
						'class' => 'attachment-thumbnail attachment-thumbnail-small ',
					) );
				} else {
					echo '-';
				}
				break;
			case 'category':
				$terms = get_the_terms( get_the_ID(), 'employer_category' );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$category = array_shift( $terms );
					echo sprintf( '<a href="?post_type=employer&employer_category=%s">%s</a>', $category->slug, $category->name );
				} else {
					echo '-';
				}
				break;
			case 'location':
				$terms = get_the_terms( get_the_ID(), 'employer_location' );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$location = array_shift( $terms );
					echo sprintf( '<a href="?post_type=employer&employer_location=%s">%s</a>', $location->slug, $location->name );
				} else {
					echo '-';
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

	public static function filter_employer_by_type() {
		global $typenow;
		if ($typenow == 'employer') {
			// categories
			$selected = isset($_GET['employer_category']) ? $_GET['employer_category'] : '';
			$terms = get_terms( 'employer_category', array('hide_empty' => false,) );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
				?>
				<select name="employer_category">
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
WP_Job_Board_Pro_Post_Type_Employer::init();