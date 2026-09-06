<?php
/**
 * Post Type: Job Applicant
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Post_Type_Job_Applicant {
	
	public static $prefix = WP_JOB_BOARD_PRO_APPLICANT_PREFIX;

	public static function init() {
	  	add_action( 'init', array( __CLASS__, 'register_post_type' ) );

	  	add_filter( 'cmb2_meta_boxes', array( __CLASS__, 'fields' ) );

	  	add_filter( 'manage_edit-job_applicant_columns', array( __CLASS__, 'custom_columns' ) );
		add_action( 'manage_job_applicant_posts_custom_column', array( __CLASS__, 'custom_columns_manage' ) );
	}

	public static function register_post_type() {
		$singular = __( 'Job Applicant', 'wp-job-board-pro' );
		$plural   = __( 'Job Applicants', 'wp-job-board-pro' );

		$labels = array(
			'name'                  => $plural,
			'singular_name'         => $singular,
			'add_new'               => sprintf(__( 'Add New %s', 'wp-job-board-pro' ), $singular),
			'add_new_item'          => sprintf(__( 'Add New %s', 'wp-job-board-pro' ), $singular),
			'edit_item'             => sprintf(__( 'Edit %s', 'wp-job-board-pro' ), $singular),
			'new_item'              => sprintf(__( 'New %s', 'wp-job-board-pro' ), $singular),
			'all_items'             => $plural,
			'view_item'             => sprintf(__( 'View %s', 'wp-job-board-pro' ), $singular),
			'search_items'          => sprintf(__( 'Search %s', 'wp-job-board-pro' ), $singular),
			'not_found'             => sprintf(__( 'No %s found', 'wp-job-board-pro' ), $plural),
			'not_found_in_trash'    => sprintf(__( 'No %s found in Trash', 'wp-job-board-pro' ), $plural),
			'parent_item_colon'     => '',
			'menu_name'             => $plural,
		);

		register_post_type( 'job_applicant',
			array(
				'labels'            => $labels,
				'supports'          => array( 'title' ),
				'public'            => true,
		        'has_archive'       => false,
		        'publicly_queryable' => false,
				'show_in_rest'		=> true,
				'show_in_menu'		=> 'edit.php?post_type=job_listing',
				'capabilities' => array(
				    'create_posts' => false,
				),
				'map_meta_cap' => true,
			)
		);
	}

	/**
	 * Defines custom fields
	 *
	 * @access public
	 * @param array $metaboxes
	 * @return array
	 */
	public static function fields( array $metaboxes ) {
		

		$metaboxes[ self::$prefix . 'general' ] = array(
			'id'                        => self::$prefix . 'general',
			'title'                     => __( 'General Options', 'wp-job-board-pro' ),
			'object_types'              => array( 'job_applicant' ),
			'context'                   => 'normal',
			'priority'                  => 'high',
			'show_names'                => true,
			'show_in_rest'				=> true,
			'fields'                    => array(
				array(
					'name'              => __( 'Status', 'wp-job-board-pro' ),
					'id'                => self::$prefix . 'app_status',
					'type'              => 'select',
					'options'			=> array(
						'' => esc_html__('Pending', 'wp-job-board-pro'),
						'approved' => esc_html__('Approved', 'wp-job-board-pro'),
						'rejected' => esc_html__('Rejected', 'wp-job-board-pro'),
					)
				),
				array(
					'name'              => __( 'Job ID', 'wp-job-board-pro' ),
					'id'                => self::$prefix . 'job_id',
					'type'              => 'text',
				),
				array(
					'name'              => __( 'Job Name', 'wp-job-board-pro' ),
					'id'                => self::$prefix . 'job_name',
					'type'              => 'text',
				),
				array(
					'name'              => __( 'Message', 'wp-job-board-pro' ),
					'id'                => self::$prefix . 'message',
					'type'              => 'textarea',
				),
				array(
					'name'              => __( 'CV ID', 'wp-job-board-pro' ),
					'id'                => self::$prefix . 'cv_file_id',
					'type'              => 'text',
				),
			),
		);


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
			'title' 			=> __( 'Title', 'wp-job-board-pro' ),
			'job_title' 		=> __( 'Job Title', 'wp-job-board-pro' ),
			'candidate' 		=> __( 'View Profile', 'wp-job-board-pro' ),
			'author' 			=> __( 'Author', 'wp-job-board-pro' ),
			'status' 			=> __( 'Status', 'wp-job-board-pro' ),
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
				$candidate_id = get_post_meta( get_the_ID(), WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'candidate_id', true );
				if ( has_post_thumbnail($candidate_id) ) {
					echo get_the_post_thumbnail( $candidate_id, 'thumbnail', array(
						'class' => 'attachment-thumbnail attachment-thumbnail-small logo-thumnail',
					) );
				} else {
					echo '-';
				}
				break;
			case 'job_title':
				$job_id = get_post_meta( get_the_ID(), WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'job_id', true );
				?>
				<a href="<?php echo esc_url(get_permalink($job_id)); ?>" target="_blank"><?php echo get_the_title($job_id); ?></a>
				<?php
				break;
			case 'candidate':
				$candidate_id = get_post_meta( get_the_ID(), WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'candidate_id', true );
				?>
				<a href="<?php echo esc_url(get_permalink($candidate_id)); ?>" target="_blank"><?php esc_html_e('View profile', 'wp-job-board-pro'); ?></a>
				<?php
				break;
			case 'status':
				$app_status = WP_Job_Board_Pro_Applicant::get_post_meta(get_the_ID(), 'app_status', true);

                if ( $app_status == 'approved' ) {
                    echo '<div class="application-status-label approved" style="background: #007cba;color: #fff;border-radius: 3px;padding: 5px 10px; display: inline-block;">'.esc_html__('Approved', 'wp-job-board-pro').'</div>';
                } elseif ( $app_status == 'rejected' ) {
                    echo '<div class="application-status-label rejected" style="background: #ca4a1f;color: #fff;border-radius: 3px;padding: 5px 10px;display: inline-block;">'.esc_html__('Rejected', 'wp-job-board-pro').'</div>';
                } else {
                    echo '<div class="application-status-label pending" style="background: #39b54a;color: #fff;border-radius: 3px;padding: 5px 10px;display: inline-block;">'.esc_html__('Pending', 'wp-job-board-pro').'</div>';
                }
				break;

		}
	}

}
WP_Job_Board_Pro_Post_Type_Job_Applicant::init();