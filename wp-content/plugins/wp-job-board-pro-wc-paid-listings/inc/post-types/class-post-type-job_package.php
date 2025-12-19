<?php
/**
 * Package
 *
 * @package    wp-job-board-pro-wc-paid-listings
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */
 
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class WP_Job_Board_Pro_Wc_Paid_Listings_Post_Type_Packages {

  	public static function init() {
    	add_action( 'init', array( __CLASS__, 'register_post_type' ) );

    	add_action( 'cmb2_meta_boxes', array( __CLASS__, 'fields' ) );

    	add_filter( 'manage_edit-job_package_columns', array( __CLASS__, 'custom_columns' ) );
		add_action( 'manage_job_package_posts_custom_column', array( __CLASS__, 'custom_columns_manage' ) );

		add_action('restrict_manage_posts', array( __CLASS__, 'filter_job_package_by_type' ));
  	}

  	public static function register_post_type() {
	    $labels = array(
			'name'                  => esc_html__( 'User Package', 'wp-job-board-pro-wc-paid-listings' ),
			'singular_name'         => esc_html__( 'User Package', 'wp-job-board-pro-wc-paid-listings' ),
			'add_new'               => esc_html__( 'Add New Package', 'wp-job-board-pro-wc-paid-listings' ),
			'add_new_item'          => esc_html__( 'Add New Package', 'wp-job-board-pro-wc-paid-listings' ),
			'edit_item'             => esc_html__( 'Edit Package', 'wp-job-board-pro-wc-paid-listings' ),
			'new_item'              => esc_html__( 'New Package', 'wp-job-board-pro-wc-paid-listings' ),
			'all_items'             => esc_html__( 'User Packages', 'wp-job-board-pro-wc-paid-listings' ),
			'view_item'             => esc_html__( 'View Package', 'wp-job-board-pro-wc-paid-listings' ),
			'search_items'          => esc_html__( 'Search Package', 'wp-job-board-pro-wc-paid-listings' ),
			'not_found'             => esc_html__( 'No Packages found', 'wp-job-board-pro-wc-paid-listings' ),
			'not_found_in_trash'    => esc_html__( 'No Packages found in Trash', 'wp-job-board-pro-wc-paid-listings' ),
			'parent_item_colon'     => '',
			'menu_name'             => esc_html__( 'User Packages', 'wp-job-board-pro-wc-paid-listings' ),
	    );

	    register_post_type( 'job_package',
	      	array(
		        'labels'            => apply_filters( 'wp_job_board_pro_wc_paid_listings_postype_package_fields_labels' , $labels ),
		        'supports'          => array( 'title' ),
		        'public'            => true,
		        'has_archive'       => false,
		        'publicly_queryable' => false,
		        'show_in_menu'		=> 'edit.php?post_type=job_listing',
	      	)
	    );
  	}
	
	public static function package_types() {
		return apply_filters('wp-job-board-pro-wc-paid-listings-package-types', array(
			'job_package' => __('Job Package', 'wp-job-board-pro-wc-paid-listings'),
			'cv_package' => __('CV Package', 'wp-job-board-pro-wc-paid-listings'),
			'contact_package' => __('Contact Package', 'wp-job-board-pro-wc-paid-listings'),
			'candidate_package' => __('Candidate Package', 'wp-job-board-pro-wc-paid-listings'),
			'resume_package' => __('Resume Package', 'wp-job-board-pro-wc-paid-listings'),
		));
	}

	public static function get_packages($type = 'job_package') {
		$packages = array( '' => __('Choose a package', 'wp-job-board-pro-wc-paid-listings') );
		$product_packages = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_job_package_products($type);
		if ( !empty($product_packages) ) {
			foreach ($product_packages as $product) {
				$packages[$product->ID] = $product->post_title;
			}
		}
		return $packages;
	}

  	public static function fields( array $metaboxes ) {
		$prefix = WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX;


		$package_types = array_merge(array('' => __('Choose package type', 'wp-job-board-pro-wc-paid-listings')), self::package_types());
		$metaboxes[ $prefix . 'general' ] = array(
			'id'                        => $prefix . 'general',
			'title'                     => __( 'General Options', 'wp-job-board-pro-wc-paid-listings' ),
			'object_types'              => array( 'job_package' ),
			'context'                   => 'normal',
			'priority'                  => 'high',
			'show_names'                => true,
			'show_in_rest'				=> true,
			'fields'                    => array(
				array(
					'name'              => __( 'Order Id', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'order_id',
					'type'              => 'text',
				),
				array(
					'name'              => __( 'Employer/Candidate user id', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'user_id',
					'type'              => 'text',
				),
				array(
					'name'              => __( 'Package Type', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'package_type',
					'type'              => 'select',
					'options'			=> $package_types
				),
			),
		);

		$packages = self::get_packages(array('job_package', 'job_package_subscription'));
		$metaboxes[ $prefix . 'job_package' ] = array(
			'id'                        => $prefix . 'job_package',
			'title'                     => __( 'Job Package Options', 'wp-job-board-pro-wc-paid-listings' ),
			'object_types'              => array( 'job_package' ),
			'context'                   => 'normal',
			'priority'                  => 'high',
			'show_names'                => true,
			'show_in_rest'				=> true,
			'fields'                    => array(
				array(
					'name'              => __( 'Package', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'product_id',
					'type'              => 'select',
					'options'			=> $packages
				),
				array(
					'name'              => __( 'Package Count', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'package_count',
					'type'              => 'text',
					'attributes' 	    => array(
						'type' 				=> 'number',
						'min'				=> 0,
						'pattern' 			=> '\d*',
					)
				),
				array(
					'name'              => __( 'Urgent Jobs', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'urgent_jobs',
					'type'              => 'checkbox',
					'desc'				=> __( 'Urgent this listing - it will be styled differently and sticky.', 'wp-job-board-pro-wc-paid-listings' ),
				),
				array(
					'name'              => __( 'Featured Jobs', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'feature_jobs',
					'type'              => 'checkbox',
					'desc'				=> __( 'Feature this listing - it will be styled differently and sticky.', 'wp-job-board-pro-wc-paid-listings' ),
				),
				array(
					'name'              => __( 'Job duration', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'job_duration',
					'type'              => 'text',
					'attributes' 	    => array(
						'type' 				=> 'number',
						'min'				=> 0,
						'pattern' 			=> '\d*',
					),
					'desc'				=> __( 'The number of days that the jobs will be active', 'wp-job-board-pro-wc-paid-listings' ),
				),
				array(
					'name'              => __( 'Jobs limit', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'job_limit',
					'type'              => 'text',
					'attributes' 	    => array(
						'type' 				=> 'number',
						'min'				=> 0,
						'pattern' 			=> '\d*',
					),
					'desc'				=> __( 'The number of jobs a user can post with this package', 'wp-job-board-pro-wc-paid-listings' ),
				),
			),
		);

		$packages = self::get_packages(array('cv_package', 'cv_package_subscription'));
		$metaboxes[ $prefix . 'cv_package' ] = array(
			'id'                        => $prefix . 'cv_package',
			'title'                     => __( 'CV Package Options', 'wp-job-board-pro-wc-paid-listings' ),
			'object_types'              => array( 'job_package' ),
			'context'                   => 'normal',
			'priority'                  => 'high',
			'show_names'                => true,
			'show_in_rest'				=> true,
			'fields'                    => array(
				array(
					'name'              => __( 'Package', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'cv_product_id',
					'type'              => 'select',
					'options'			=> $packages
				),
				array(
					'name'              => __( 'CV viewed', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'cv_viewed_count',
					'type'              => 'text',
					'desc' 				=> __( 'Enter candidate ids separate by ","', 'wp-job-board-pro-wc-paid-listings' ),
				),
				array(
					'name'              => __( 'Package Expiry Time (Days)', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'cv_package_expiry_time',
					'type'              => 'text',
					'attributes' 	    => array(
						'type' 				=> 'number',
						'min'				=> 0,
						'pattern' 			=> '\d*',
					),
					'desc'				=> __( 'The number of days that the user package active. Leave this field blank for unlimited', 'wp-job-board-pro-wc-paid-listings' ),
				),
				array(
					'name'              => __( 'Number of CV\'s', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'cv_number_of_cv',
					'type'              => 'text',
					'attributes' 	    => array(
						'type' 				=> 'number',
						'min'				=> 0,
						'pattern' 			=> '\d*',
					),
					'desc'				=> __( 'The number of CV to view in this package. Leave this field blank for unlimited', 'wp-job-board-pro-wc-paid-listings' ),
				),
			),
		);

		$packages = self::get_packages(array('contact_package', 'contact_package_subscription'));
		$metaboxes[ $prefix . 'contact_package' ] = array(
			'id'                        => $prefix . 'contact_package',
			'title'                     => __( 'Contact Package Options', 'wp-job-board-pro-wc-paid-listings' ),
			'object_types'              => array( 'job_package' ),
			'context'                   => 'normal',
			'priority'                  => 'high',
			'show_names'                => true,
			'show_in_rest'				=> true,
			'fields'                    => array(
				array(
					'name'              => __( 'Package', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'contact_product_id',
					'type'              => 'select',
					'options'			=> $packages
				),
				array(
					'name'              => __( 'CV Contacts Sent', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'contact_viewed_count',
					'type'              => 'text',
					'desc' 				=> __( 'Enter candidate ids separate by ","', 'wp-job-board-pro-wc-paid-listings' ),
				),
				array(
					'name'              => __( 'Package Expiry Time (Days)', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'contact_package_expiry_time',
					'type'              => 'text',
					'attributes' 	    => array(
						'type' 				=> 'number',
						'min'				=> 0,
						'pattern' 			=> '\d*',
					),
					'desc'				=> __( 'The number of days that the user package active. Leave this field blank for unlimited', 'wp-job-board-pro-wc-paid-listings' ),
				),
				array(
					'name'              => __( 'Number of CV\'s', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'contact_number_of_cv',
					'type'              => 'text',
					'attributes' 	    => array(
						'type' 				=> 'number',
						'min'				=> 0,
						'pattern' 			=> '\d*',
					),
					'desc'				=> __( 'The number of CV to view in this package. Leave this field blank for unlimited', 'wp-job-board-pro-wc-paid-listings' ),
				),
			),
		);

		$packages = self::get_packages(array('candidate_package', 'candidate_package_subscription'));
		$metaboxes[ $prefix . 'candidate_package' ] = array(
			'id'                        => $prefix . 'candidate_package',
			'title'                     => __( 'Candidate Package Options', 'wp-job-board-pro-wc-paid-listings' ),
			'object_types'              => array( 'job_package' ),
			'context'                   => 'normal',
			'priority'                  => 'high',
			'show_names'                => true,
			'show_in_rest'				=> true,
			'fields'                    => array(
				array(
					'name'              => __( 'Package', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'candidate_product_id',
					'type'              => 'select',
					'options'			=> $packages
				),
				array(
					'name'              => __( 'Candidate applications', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'candidate_applied_count',
					'type'              => 'text',
					'desc' 				=> __( 'Enter applications ids separate by ","', 'wp-job-board-pro-wc-paid-listings' ),
				),
				array(
					'name'              => __( 'Package Expiry Time (Days)', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'candidate_package_expiry_time',
					'type'              => 'text',
					'attributes' 	    => array(
						'type' 				=> 'number',
						'min'				=> 0,
						'pattern' 			=> '\d*',
					),
					'desc'				=> __( 'The number of days that the user package active. Leave this field blank for unlimited', 'wp-job-board-pro-wc-paid-listings' ),
				),
				array(
					'name'              => __( 'Number of applications', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'candidate_number_of_applications',
					'type'              => 'text',
					'attributes' 	    => array(
						'type' 				=> 'number',
						'min'				=> 0,
						'pattern' 			=> '\d*',
					),
					'desc'				=> __( 'The number of applications to candidate apply. Leave this field blank for unlimited', 'wp-job-board-pro-wc-paid-listings' ),
				),
			),
		);

		$packages = self::get_packages(array('resume_package', 'resume_package_subscription'));
		$metaboxes[ $prefix . 'resume_package' ] = array(
			'id'                        => $prefix . 'resume_package',
			'title'                     => __( 'Resume Package Options', 'wp-job-board-pro-wc-paid-listings' ),
			'object_types'              => array( 'job_package' ),
			'context'                   => 'normal',
			'priority'                  => 'high',
			'show_names'                => true,
			'show_in_rest'				=> true,
			'fields'                    => array(
				array(
					'name'              => __( 'Package', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'resume_product_id',
					'type'              => 'select',
					'options'			=> $packages
				),
				array(
					'name'              => __( 'Urgent Resumes', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'urgent_resumes',
					'type'              => 'checkbox',
					'desc'				=> __( 'Urgent this listing - it will be styled differently and sticky.', 'wp-job-board-pro-wc-paid-listings' ),
				),
				array(
					'name'              => __( 'Featured Resumes', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'featured_resumes',
					'type'              => 'checkbox',
					'desc'				=> __( 'Feature this listing - it will be styled differently and sticky.', 'wp-job-board-pro-wc-paid-listings' ),
				),
				array(
					'name'              => __( 'Resume duration', 'wp-job-board-pro-wc-paid-listings' ),
					'id'                => $prefix . 'resumes_duration',
					'type'              => 'text',
					'attributes' 	    => array(
						'type' 				=> 'number',
						'min'				=> 0,
						'pattern' 			=> '\d*',
					),
					'desc'				=> __( 'The number of days that the jobs will be active', 'wp-job-board-pro-wc-paid-listings' ),
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
	public static function custom_columns() {
		$fields = array(
			'cb' 				=> '<input type="checkbox" />',
			'title' 			=> __( 'Title', 'wp-job-board-pro' ),
			'package_type' 		=> __( 'Package Type', 'wp-job-board-pro' ),
			'user_author' 			=> __( 'User', 'wp-job-board-pro' ),
			'date' 				=> __( 'Date', 'wp-job-board-pro' ),
		);
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
		$prefix = WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX;
		switch ( $column ) {
			case 'package_type':
				$package_type = get_post_meta($post->ID, $prefix.'package_type', true );
				$package_types = self::package_types();
				if ( !empty($package_types[$package_type]) ) {
					echo $package_types[$package_type];
				} else {
					echo '-';
				}
				break;
			case 'user_author':
				$user_id = get_post_meta($post->ID, $prefix.'user_id', true );
				if ( class_exists('WP_Job_Board_Pro_User') ) {
					if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
						$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
						echo get_the_title($employer_id);
					} elseif ( WP_Job_Board_Pro_User::is_candidate($user_id) ) {
						$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
						echo get_the_title($candidate_id);
					} else {
						echo '-';
					}
				} else {
					echo '-';
				}
				break;
		}
	}

	public static function filter_job_package_by_type() {
		global $typenow;
		if ( $typenow == 'job_package') {
			// categories
			$selected = isset($_GET['package_type']) ? $_GET['package_type'] : '';
			$package_types = self::package_types();
			if ( ! empty( $package_types ) ){
				?>
				<select name="package_type">
					<option value=""><?php esc_html_e('All package types', 'wp-job-board-pro'); ?></option>
					<?php
					foreach ($package_types as $key => $title) {
						?>
						<option value="<?php echo esc_attr($key); ?>" <?php selected($selected, $key); ?>><?php echo esc_html($title); ?></option>
						<?php
					}
				?>
				</select>
				<?php
			}
		}
	}

}

WP_Job_Board_Pro_Wc_Paid_Listings_Post_Type_Packages::init();