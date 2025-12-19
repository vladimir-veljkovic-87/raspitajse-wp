<?php
/**
 * product type: package
 *
 * @package    wp-job-board-pro-wc-paid-listings
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wp_job_board_pro_wc_paid_listings_register_package_product_type() {
	class WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_Package extends WC_Product_Simple {
		
		public function __construct( $product ) {
			parent::__construct( $product );
		}

		public function get_type() {
	        return 'job_package';
	    }

	    public function is_sold_individually() {
			return apply_filters( 'wp_job_board_pro_wc_paid_listings_job_package_is_sold_individually', true );
		}

		public function is_purchasable() {
			return true;
		}

		public function is_virtual() {
			return true;
		}
	}

	class WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_CV_Package extends WC_Product_Simple {
		
		public function __construct( $product ) {
			parent::__construct( $product );
		}

		public function get_type() {
	        return 'cv_package';
	    }

	    public function is_sold_individually() {
			return apply_filters( 'wp_job_board_pro_wc_paid_listings_cv_package_is_sold_individually', true );
		}

		public function is_purchasable() {
			return true;
		}

		public function is_virtual() {
			return true;
		}
	}

	class WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_Contact_Package extends WC_Product_Simple {
		
		public function __construct( $product ) {
			parent::__construct( $product );
		}

		public function get_type() {
	        return 'contact_package';
	    }

	    public function is_sold_individually() {
			return apply_filters( 'wp_job_board_pro_wc_paid_listings_contact_package_is_sold_individually', true );
		}

		public function is_purchasable() {
			return true;
		}

		public function is_virtual() {
			return true;
		}
	}

	class WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_Candidate_Package extends WC_Product_Simple {
		
		public function __construct( $product ) {
			parent::__construct( $product );
		}

		public function get_type() {
	        return 'candidate_package';
	    }

	    public function is_sold_individually() {
			return apply_filters( 'wp_job_board_pro_wc_paid_listings_candidate_package_is_sold_individually', true );
		}

		public function is_purchasable() {
			return true;
		}

		public function is_virtual() {
			return true;
		}
	}

	class WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_Resume_Package extends WC_Product_Simple {
		
		public function __construct( $product ) {
			parent::__construct( $product );
		}

		public function get_type() {
	        return 'resume_package';
	    }

	    public function is_sold_individually() {
			return apply_filters( 'wp_job_board_pro_wc_paid_listings_resume_package_is_sold_individually', true );
		}

		public function is_purchasable() {
			return true;
		}

		public function is_virtual() {
			return true;
		}
	}

	if ( class_exists( 'WC_Subscriptions' ) ) {
		class WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_Package_Subscription extends WC_Product_Subscription {
		
			public function __construct( $product ) {
				parent::__construct( $product );
			}

			public function get_type() {
		        return 'job_package_subscription';
		    }

		    public function is_type( $type ) {
				return ( 'job_package_subscription' == $type || ( is_array( $type ) && in_array( 'job_package_subscription', $type ) ) ) ? true : parent::is_type( $type );
			}

			public function add_to_cart_url() {
				$url = $this->is_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id ) ) : get_permalink( $this->id );

				return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
			}

		    public function is_sold_individually() {
				return apply_filters( 'wp_job_board_pro_wc_paid_listings_job_package_subscription_is_sold_individually', true );
			}

			public function is_purchasable() {
				return true;
			}

			public function is_virtual() {
				return true;
			}
		}

		class WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_CV_Package_Subscription extends WC_Product_Subscription {
		
			public function __construct( $product ) {
				parent::__construct( $product );
			}

			public function get_type() {
		        return 'cv_package_subscription';
		    }

		    public function is_type( $type ) {
				return ( 'cv_package_subscription' == $type || ( is_array( $type ) && in_array( 'cv_package_subscription', $type ) ) ) ? true : parent::is_type( $type );
			}
			
			public function add_to_cart_url() {
				$url = $this->is_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id ) ) : get_permalink( $this->id );

				return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
			}

		    public function is_sold_individually() {
				return apply_filters( 'wp_job_board_pro_wc_paid_listings_cv_package_subscription_is_sold_individually', true );
			}

			public function is_purchasable() {
				return true;
			}

			public function is_virtual() {
				return true;
			}
		}

		class WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_Contact_Package_Subscription extends WC_Product_Subscription {
		
			public function __construct( $product ) {
				parent::__construct( $product );
			}

			public function get_type() {
		        return 'contact_package_subscription';
		    }

		    public function is_type( $type ) {
				return ( 'contact_package_subscription' == $type || ( is_array( $type ) && in_array( 'contact_package_subscription', $type ) ) ) ? true : parent::is_type( $type );
			}
			
			public function add_to_cart_url() {
				$url = $this->is_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id ) ) : get_permalink( $this->id );

				return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
			}

		    public function is_sold_individually() {
				return apply_filters( 'wp_job_board_pro_wc_paid_listings_contact_package_subscription_is_sold_individually', true );
			}

			public function is_purchasable() {
				return true;
			}

			public function is_virtual() {
				return true;
			}
		}

		class WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_Candidate_Package_Subscription extends WC_Product_Subscription {
		
			public function __construct( $product ) {
				parent::__construct( $product );
			}

			public function get_type() {
		        return 'candidate_package_subscription';
		    }

		    public function is_type( $type ) {
				return ( 'candidate_package_subscription' == $type || ( is_array( $type ) && in_array( 'candidate_package_subscription', $type ) ) ) ? true : parent::is_type( $type );
			}
			
			public function add_to_cart_url() {
				$url = $this->is_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id ) ) : get_permalink( $this->id );

				return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
			}

		    public function is_sold_individually() {
				return apply_filters( 'wp_job_board_pro_wc_paid_listings_candidate_package_subscription_is_sold_individually', true );
			}

			public function is_purchasable() {
				return true;
			}

			public function is_virtual() {
				return true;
			}
		}

		class WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_Resume_Package_Subscription extends WC_Product_Subscription {
		
			public function __construct( $product ) {
				parent::__construct( $product );
			}

			public function get_type() {
		        return 'resume_package_subscription';
		    }

		    public function is_type( $type ) {
				return ( 'resume_package_subscription' == $type || ( is_array( $type ) && in_array( 'resume_package_subscription', $type ) ) ) ? true : parent::is_type( $type );
			}
			
			public function add_to_cart_url() {
				$url = $this->is_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id ) ) : get_permalink( $this->id );

				return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
			}
			
		    public function is_sold_individually() {
				return apply_filters( 'wp_job_board_pro_wc_paid_listings_resume_package_subscription_is_sold_individually', true );
			}

			public function is_purchasable() {
				return true;
			}

			public function is_virtual() {
				return true;
			}
		}
	}
}

add_action( 'init', 'wp_job_board_pro_wc_paid_listings_register_package_product_type' );


function wp_job_board_pro_wc_paid_listings_add_job_package_product( $types ) {
	$types[ 'job_package' ] = __( 'Job Package', 'wp-job-board-pro-wc-paid-listings' );
	$types[ 'cv_package' ] = __( 'CV Package', 'wp-job-board-pro-wc-paid-listings' );
	$types[ 'contact_package' ] = __( 'Contact Package', 'wp-job-board-pro-wc-paid-listings' );
	$types[ 'candidate_package' ] = __( 'Candidate Package', 'wp-job-board-pro-wc-paid-listings' );
	$types[ 'resume_package' ] = __( 'Resume Package', 'wp-job-board-pro-wc-paid-listings' );
	

	if ( class_exists( 'WC_Subscriptions' ) ) {
		$types['job_package_subscription'] = __( 'Job Package Subscription', 'wp-job-board-pro-wc-paid-listings' );
		$types['cv_package_subscription'] = __( 'CV Package Subscription', 'wp-job-board-pro-wc-paid-listings' );
		$types['contact_package_subscription'] = __( 'Contact Package Subscription', 'wp-job-board-pro-wc-paid-listings' );
		$types['candidate_package_subscription'] = __( 'Candidate Package Subscription', 'wp-job-board-pro-wc-paid-listings' );
		$types['resume_package_subscription'] = __( 'Resume Package Subscription', 'wp-job-board-pro-wc-paid-listings' );
	}

	return $types;
}

add_filter( 'product_type_selector', 'wp_job_board_pro_wc_paid_listings_add_job_package_product' );

function wp_job_board_pro_wc_paid_listings_woocommerce_product_class( $classname, $product_type ) {

    if ( $product_type == 'job_package' ) { // notice the checking here.
        $classname = 'WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_Package';
    }

    if ( $product_type == 'cv_package' ) { // notice the checking here.
        $classname = 'WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_CV_Package';
    }

    if ( $product_type == 'contact_package' ) { // notice the checking here.
        $classname = 'WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_Contact_Package';
    }

    if ( $product_type == 'candidate_package' ) { // notice the checking here.
        $classname = 'WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_Candidate_Package';
    }

    if ( $product_type == 'resume_package' ) { // notice the checking here.
        $classname = 'WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_Resume_Package';
    }

    if ( class_exists( 'WC_Subscriptions' ) ) {
	    if ( $product_type == 'job_package_subscription' ) { // notice the checking here.
	        $classname = 'WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_Package_Subscription';
	    }

	    if ( $product_type == 'cv_package_subscription' ) { // notice the checking here.
	        $classname = 'WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_CV_Package_Subscription';
	    }

	    if ( $product_type == 'contact_package_subscription' ) { // notice the checking here.
	        $classname = 'WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_Contact_Package_Subscription';
	    }

	    if ( $product_type == 'candidate_package_subscription' ) { // notice the checking here.
	        $classname = 'WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_Candidate_Package_Subscription';
	    }

	    if ( $product_type == 'resume_package_subscription' ) { // notice the checking here.
	        $classname = 'WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_Resume_Package_Subscription';
	    }
    }
    return $classname;
}

add_filter( 'woocommerce_product_class', 'wp_job_board_pro_wc_paid_listings_woocommerce_product_class', 10, 2 );


/**
 * Show pricing fields for package product.
 */
function wp_job_board_pro_wc_paid_listings_package_custom_js() {

	if ( 'product' != get_post_type() ) {
		return;
	}

	?><script type='text/javascript'>
		jQuery( document ).ready( function() {
			// job package
			jQuery('.product_data_tabs .general_tab').show();
        	jQuery('#general_product_data .pricing').addClass('show_if_job_package').show();
			jQuery('.inventory_options').addClass('show_if_job_package').show();
			jQuery('.inventory_options').addClass('show_if_job_package').show();
            jQuery('#inventory_product_data ._manage_stock_field').addClass('show_if_job_package').show();
            jQuery('#inventory_product_data ._sold_individually_field').parent().addClass('show_if_job_package').show();
            jQuery('#inventory_product_data ._sold_individually_field').addClass('show_if_job_package').show();

            // cv
            jQuery('#general_product_data .pricing').addClass('show_if_cv_package').show();
			jQuery('.inventory_options').addClass('show_if_cv_package').show();
			jQuery('.inventory_options').addClass('show_if_cv_package').show();
            jQuery('#inventory_product_data ._manage_stock_field').addClass('show_if_cv_package').show();
            jQuery('#inventory_product_data ._sold_individually_field').parent().addClass('show_if_cv_package').show();
            jQuery('#inventory_product_data ._sold_individually_field').addClass('show_if_cv_package').show();

            // contact
            jQuery('#general_product_data .pricing').addClass('show_if_contact_package').show();
			jQuery('.inventory_options').addClass('show_if_contact_package').show();
			jQuery('.inventory_options').addClass('show_if_contact_package').show();
            jQuery('#inventory_product_data ._manage_stock_field').addClass('show_if_contact_package').show();
            jQuery('#inventory_product_data ._sold_individually_field').parent().addClass('show_if_contact_package').show();
            jQuery('#inventory_product_data ._sold_individually_field').addClass('show_if_contact_package').show();

            // candidate
            jQuery('#general_product_data .pricing').addClass('show_if_candidate_package').show();
			jQuery('.inventory_options').addClass('show_if_candidate_package').show();
			jQuery('.inventory_options').addClass('show_if_candidate_package').show();
            jQuery('#inventory_product_data ._manage_stock_field').addClass('show_if_candidate_package').show();
            jQuery('#inventory_product_data ._sold_individually_field').parent().addClass('show_if_candidate_package').show();
            jQuery('#inventory_product_data ._sold_individually_field').addClass('show_if_candidate_package').show();

            // resume
            jQuery('#general_product_data .pricing').addClass('show_if_resume_package').show();
			jQuery('.inventory_options').addClass('show_if_resume_package').show();
			jQuery('.inventory_options').addClass('show_if_resume_package').show();
            jQuery('#inventory_product_data ._manage_stock_field').addClass('show_if_resume_package').show();
            jQuery('#inventory_product_data ._sold_individually_field').parent().addClass('show_if_resume_package').show();
            jQuery('#inventory_product_data ._sold_individually_field').addClass('show_if_resume_package').show();
		});
	</script><?php
}
add_action( 'admin_footer', 'wp_job_board_pro_wc_paid_listings_package_custom_js' );

add_filter( 'woocommerce_subscription_product_types', 'wp_job_board_pro_wc_paid_listings_woocommerce_subscription_product_types' );
function wp_job_board_pro_wc_paid_listings_woocommerce_subscription_product_types( $types ) {
	$types[] = 'job_package_subscription';
	$types[] = 'cv_package_subscription';
	$types[] = 'contact_package_subscription';
	$types[] = 'candidate_package_subscription';
	$types[] = 'resume_package_subscription';
	return $types;
}

add_action( 'woocommerce_product_options_general_product_data', 'wp_job_board_pro_wc_paid_listings_package_options_product_tab_content' );

/**
 * Contents of the package options product tab.
 */
function wp_job_board_pro_wc_paid_listings_package_options_product_tab_content() {
	global $post;
	$post_id = $post->ID;
	?>
	<!-- Job Package -->
	<!-- <div id='job_package_options' class='panel woocommerce_options_panel'> -->
	<div class="options_group show_if_job_package show_if_job_package_subscription">
		<?php
			if ( class_exists( 'WC_Subscriptions' ) ) {
				woocommerce_wp_select( array(
					'id' => '_job_package_subscription_type',
					'label' => __( 'Subscription Type', 'wp-job-board-pro-wc-paid-listings' ),
					'description' => __( 'Choose how subscriptions affect this package', 'wp-job-board-pro-wc-paid-listings' ),
					'value' => get_post_meta( $post_id, '_job_package_subscription_type', true ),
					'desc_tip' => true,
					'options' => array(
						'package' => __( 'Link the subscription to the package (renew listing limit every subscription term)', 'wp-job-board-pro-wc-paid-listings' ),
						'listing' => __( 'Link the subscription to posted listings (renew posted listings every subscription term)', 'wp-job-board-pro-wc-paid-listings' )
					),
					'wrapper_class' => 'show_if_job_package_subscription',
				) );
			}
			woocommerce_wp_checkbox( array(
				'id' 		=> '_urgent_jobs',
				'label' 	=> __( 'Urgent Jobs?', 'wp-job-board-pro-wc-paid-listings' ),
				'description'	=> __( 'Urgent this listing - it will be styled differently and sticky.', 'wp-job-board-pro-wc-paid-listings' ),
			) );
			woocommerce_wp_checkbox( array(
				'id' 		=> '_feature_jobs',
				'label' 	=> __( 'Feature Jobs?', 'wp-job-board-pro-wc-paid-listings' ),
				'description'	=> __( 'Feature this listing - it will be styled differently and sticky.', 'wp-job-board-pro-wc-paid-listings' ),
			) );
			woocommerce_wp_text_input( array(
				'id'			=> '_jobs_limit',
				'label'			=> __( 'Jobs Limit', 'wp-job-board-pro-wc-paid-listings' ),
				'desc_tip'		=> true,
				'description'	=> __( 'The number of listings a user can post with this package', 'wp-job-board-pro-wc-paid-listings' ),
				'type' 			=> 'number',
			) );
			woocommerce_wp_text_input( array(
				'id'			=> '_jobs_duration',
				'label'			=> __( 'Jobs Duration (Days)', 'wp-job-board-pro-wc-paid-listings' ),
				'desc_tip'		=> true,
				'description'	=> __( 'The number of days that the listings will be active', 'wp-job-board-pro-wc-paid-listings' ),
				'type' 			=> 'number',
			) );

			do_action('wp_job_board_pro_wc_paid_listings_package_options_product_tab_content');
		?>
	</div>

	<!-- CV Package -->
	<!-- <div id='cv_package_options' class='panel woocommerce_options_panel'> -->
	<div class="options_group show_if_cv_package show_if_cv_package_subscription">
		<?php
			if ( class_exists( 'WC_Subscriptions' ) ) {
				woocommerce_wp_select( array(
					'id' => '_cv_package_subscription_type',
					'label' => __( 'Subscription Type', 'wp-job-board-pro-wc-paid-listings' ),
					'description' => __( 'Choose how subscriptions affect this package', 'wp-job-board-pro-wc-paid-listings' ),
					'value' => get_post_meta( $post_id, '_cv_package_subscription_type', true ),
					'desc_tip' => true,
					'options' => array(
						'package' => __( 'Link the subscription to the package (renew listing limit every subscription term)', 'wp-job-board-pro-wc-paid-listings' ),
						'listing' => __( 'Link the subscription to posted listings (renew posted listings every subscription term)', 'wp-job-board-pro-wc-paid-listings' )
					),
					'wrapper_class' => 'show_if_cv_package_subscription',
				) );
			}

			woocommerce_wp_text_input( array(
				'id'			=> '_cv_package_expiry_time',
				'label'			=> __( 'Package Expiry Time (Days)', 'wp-job-board-pro-wc-paid-listings' ),
				'desc_tip'		=> true,
				'description'	=> __( 'The number of days that the user package active. Leave this field blank for unlimited', 'wp-job-board-pro-wc-paid-listings' ),
				'type' 			=> 'number',
				'default'		=> 30
			) );
			woocommerce_wp_text_input( array(
				'id'			=> '_cv_number_of_cv',
				'label'			=> __( 'Number of CV\'s', 'wp-job-board-pro-wc-paid-listings' ),
				'desc_tip'		=> true,
				'description'	=> __( 'The number of CV to view in this package. Leave this field blank for unlimited', 'wp-job-board-pro-wc-paid-listings' ),
				'type' 			=> 'number',
				'default'		=> 10
			) );

			do_action('wp_job_board_pro_wc_paid_cv_listings_package_options_product_tab_content');
		?>
	</div>

	<!-- Contact Package -->
	<!-- <div id='contact_package_options' class='panel woocommerce_options_panel'> -->
	<div class="options_group show_if_contact_package show_if_contact_package_subscription">
		<?php
			if ( class_exists( 'WC_Subscriptions' ) ) {
				woocommerce_wp_select( array(
					'id' => '_contact_package_subscription_type',
					'label' => __( 'Subscription Type', 'wp-job-board-pro-wc-paid-listings' ),
					'description' => __( 'Choose how subscriptions affect this package', 'wp-job-board-pro-wc-paid-listings' ),
					'value' => get_post_meta( $post_id, '_contact_package_subscription_type', true ),
					'desc_tip' => true,
					'options' => array(
						'package' => __( 'Link the subscription to the package (renew listing limit every subscription term)', 'wp-job-board-pro-wc-paid-listings' ),
						'listing' => __( 'Link the subscription to posted listings (renew posted listings every subscription term)', 'wp-job-board-pro-wc-paid-listings' )
					),
					'wrapper_class' => 'show_if_contact_package_subscription',
				) );
			}

			woocommerce_wp_text_input( array(
				'id'			=> '_contact_package_expiry_time',
				'label'			=> __( 'Package Expiry Time (Days)', 'wp-job-board-pro-wc-paid-listings' ),
				'desc_tip'		=> true,
				'description'	=> __( 'The number of days that the user package active. Leave this field blank for unlimited', 'wp-job-board-pro-wc-paid-listings' ),
				'type' 			=> 'number',
				'default'		=> 30
			) );
			woocommerce_wp_text_input( array(
				'id'			=> '_contact_number_of_cv',
				'label'			=> __( 'Number of CV\'s', 'wp-job-board-pro-wc-paid-listings' ),
				'desc_tip'		=> true,
				'description'	=> __( 'The number of CV to view in this package. Leave this field blank for unlimited', 'wp-job-board-pro-wc-paid-listings' ),
				'type' 			=> 'number',
				'default'		=> 10
			) );

			do_action('wp_job_board_pro_wc_paid_contact_listings_package_options_product_tab_content');
		?>
	</div>

	<!-- Candidate package -->
	<!-- <div id='candidate_package_options' class='panel woocommerce_options_panel'> -->
	<div class="options_group show_if_candidate_package show_if_candidate_package_subscription">
		<?php
			if ( class_exists( 'WC_Subscriptions' ) ) {
				woocommerce_wp_select( array(
					'id' => '_candidate_package_subscription_type',
					'label' => __( 'Subscription Type', 'wp-job-board-pro-wc-paid-listings' ),
					'description' => __( 'Choose how subscriptions affect this package', 'wp-job-board-pro-wc-paid-listings' ),
					'value' => get_post_meta( $post_id, '_candidate_package_subscription_type', true ),
					'desc_tip' => true,
					'options' => array(
						'package' => __( 'Link the subscription to the package (renew listing limit every subscription term)', 'wp-job-board-pro-wc-paid-listings' ),
						'listing' => __( 'Link the subscription to posted listings (renew posted listings every subscription term)', 'wp-job-board-pro-wc-paid-listings' )
					),
					'wrapper_class' => 'show_if_candidate_package_subscription',
				) );
			}

			woocommerce_wp_text_input( array(
				'id'			=> '_candidate_package_expiry_time',
				'label'			=> __( 'Package Expiry Time (Days)', 'wp-job-board-pro-wc-paid-listings' ),
				'desc_tip'		=> true,
				'description'	=> __( 'The number of days that the user package active. Leave this field blank for unlimited', 'wp-job-board-pro-wc-paid-listings' ),
				'type' 			=> 'number',
				'default'		=> 30
			) );
			woocommerce_wp_text_input( array(
				'id'			=> '_candidate_number_of_applications',
				'label'			=> __( 'Number of Applications', 'wp-job-board-pro-wc-paid-listings' ),
				'desc_tip'		=> true,
				'description'	=> __( 'The number of Applications to candidate apply. Leave this field blank for unlimited', 'wp-job-board-pro-wc-paid-listings' ),
				'type' 			=> 'number',
				'default'		=> 10
			) );

			do_action('wp_job_board_pro_wc_paid_candidate_listings_package_options_product_tab_content');
		?>
	</div>

	<!-- Resume package -->
	<!-- <div id='resume_package_options' class='panel woocommerce_options_panel'> -->
	<div class="options_group show_if_resume_package show_if_resume_package_subscription">
		<?php
			if ( class_exists( 'WC_Subscriptions' ) ) {
				woocommerce_wp_select( array(
					'id' => '_resume_package_subscription_type',
					'label' => __( 'Subscription Type', 'wp-job-board-pro-wc-paid-listings' ),
					'description' => __( 'Choose how subscriptions affect this package', 'wp-job-board-pro-wc-paid-listings' ),
					'value' => get_post_meta( $post_id, '_resume_package_subscription_type', true ),
					'desc_tip' => true,
					'options' => array(
						'package' => __( 'Link the subscription to the package (renew listing limit every subscription term)', 'wp-job-board-pro-wc-paid-listings' ),
						'listing' => __( 'Link the subscription to posted listings (renew posted listings every subscription term)', 'wp-job-board-pro-wc-paid-listings' )
					),
					'wrapper_class' => 'show_if_resume_package_subscription',
				) );
			}
			woocommerce_wp_checkbox( array(
				'id' 		=> '_urgent_resumes',
				'label' 	=> __( 'Urgent Resumes?', 'wp-job-board-pro-wc-paid-listings' ),
				'description'	=> __( 'Urgent this listing - it will be styled differently and sticky.', 'wp-job-board-pro-wc-paid-listings' ),
			) );
			woocommerce_wp_checkbox( array(
				'id' 		=> '_featured_resumes',
				'label' 	=> __( 'Featured Resumes?', 'wp-job-board-pro-wc-paid-listings' ),
				'description'	=> __( 'Feature this listing - it will be styled differently and sticky.', 'wp-job-board-pro-wc-paid-listings' ),
			) );
			woocommerce_wp_text_input( array(
				'id'			=> '_resumes_duration',
				'label'			=> __( 'Resume Duration (Days)', 'wp-job-board-pro-wc-paid-listings' ),
				'desc_tip'		=> true,
				'description'	=> __( 'The number of days that the resume will be active', 'wp-job-board-pro-wc-paid-listings' ),
				'type' 			=> 'number',
			) );
			do_action('wp_job_board_pro_wc_paid_resume_listings_package_options_product_tab_content');
		?>
	</div>
	<?php
}
// add_action( 'woocommerce_product_data_panels', 'wp_job_board_pro_wc_paid_listings_package_options_product_tab_content' );

/**
 * Save the Job Package custom fields.
 */
function wp_job_board_pro_wc_paid_listings_save_package_option_field( $post_id ) {
	$urgent_jobs = isset( $_POST['_urgent_jobs'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, '_urgent_jobs', $urgent_jobs );
	
	$feature_jobs = isset( $_POST['_feature_jobs'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, '_feature_jobs', $feature_jobs );
	
	if ( isset( $_POST['_job_package_subscription_type'] ) ) {
		update_post_meta( $post_id, '_job_package_subscription_type', sanitize_text_field( $_POST['_job_package_subscription_type'] ) );
	}

	if ( isset( $_POST['_jobs_limit'] ) ) {
		update_post_meta( $post_id, '_jobs_limit', sanitize_text_field( $_POST['_jobs_limit'] ) );
	}

	if ( isset( $_POST['_jobs_duration'] ) ) {
		update_post_meta( $post_id, '_jobs_duration', sanitize_text_field( $_POST['_jobs_duration'] ) );
	}
}
add_action( 'woocommerce_process_product_meta_job_package', 'wp_job_board_pro_wc_paid_listings_save_package_option_field'  );
add_action( 'woocommerce_process_product_meta_job_package_subscription', 'wp_job_board_pro_wc_paid_listings_save_package_option_field'  );

/**
 * Save the CV Package custom fields.
 */
function wp_job_board_pro_wc_paid_listings_save_cv_package_option_field( $post_id ) {
	if ( isset( $_POST['_cv_package_subscription_type'] ) ) {
		update_post_meta( $post_id, '_cv_package_subscription_type', sanitize_text_field( $_POST['_cv_package_subscription_type'] ) );
	}

	if ( isset( $_POST['_cv_package_expiry_time'] ) ) {
		update_post_meta( $post_id, '_cv_package_expiry_time', sanitize_text_field( $_POST['_cv_package_expiry_time'] ) );
	}

	if ( isset( $_POST['_cv_number_of_cv'] ) ) {
		update_post_meta( $post_id, '_cv_number_of_cv', sanitize_text_field( $_POST['_cv_number_of_cv'] ) );
	}
}
add_action( 'woocommerce_process_product_meta_cv_package', 'wp_job_board_pro_wc_paid_listings_save_cv_package_option_field'  );
add_action( 'woocommerce_process_product_meta_cv_package_subscription', 'wp_job_board_pro_wc_paid_listings_save_cv_package_option_field'  );

/**
 * Save the Contact Package custom fields.
 */
function wp_job_board_pro_wc_paid_listings_save_contact_package_option_field( $post_id ) {
	if ( isset( $_POST['_contact_package_subscription_type'] ) ) {
		update_post_meta( $post_id, '_contact_package_subscription_type', sanitize_text_field( $_POST['_contact_package_subscription_type'] ) );
	}

	if ( isset( $_POST['_contact_package_expiry_time'] ) ) {
		update_post_meta( $post_id, '_contact_package_expiry_time', sanitize_text_field( $_POST['_contact_package_expiry_time'] ) );
	}

	if ( isset( $_POST['_contact_number_of_cv'] ) ) {
		update_post_meta( $post_id, '_contact_number_of_cv', sanitize_text_field( $_POST['_contact_number_of_cv'] ) );
	}
}
add_action( 'woocommerce_process_product_meta_contact_package', 'wp_job_board_pro_wc_paid_listings_save_contact_package_option_field'  );
add_action( 'woocommerce_process_product_meta_contact_package_subscription', 'wp_job_board_pro_wc_paid_listings_save_contact_package_option_field'  );

/**
 * Save the Candidate Package custom fields.
 */
function wp_job_board_pro_wc_paid_listings_save_candidate_package_option_field( $post_id ) {
	if ( isset( $_POST['_candidate_package_subscription_type'] ) ) {
		update_post_meta( $post_id, '_candidate_package_subscription_type', sanitize_text_field( $_POST['_candidate_package_subscription_type'] ) );
	}
	
	if ( isset( $_POST['_candidate_package_expiry_time'] ) ) {
		update_post_meta( $post_id, '_candidate_package_expiry_time', sanitize_text_field( $_POST['_candidate_package_expiry_time'] ) );
	}
	
	if ( isset( $_POST['_candidate_number_of_applications'] ) ) {
		update_post_meta( $post_id, '_candidate_number_of_applications', sanitize_text_field( $_POST['_candidate_number_of_applications'] ) );
	}
}
add_action( 'woocommerce_process_product_meta_candidate_package', 'wp_job_board_pro_wc_paid_listings_save_candidate_package_option_field'  );
add_action( 'woocommerce_process_product_meta_candidate_package_subscription', 'wp_job_board_pro_wc_paid_listings_save_candidate_package_option_field'  );

/**
 * Save the Resume Package custom fields.
 */
function wp_job_board_pro_wc_paid_listings_save_resume_package_option_field( $post_id ) {
	$urgent_resumes = isset( $_POST['_urgent_resumes'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, '_urgent_resumes', $urgent_resumes );
	
	$featured_resumes = isset( $_POST['_featured_resumes'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, '_featured_resumes', $featured_resumes );

	if ( isset( $_POST['_resume_package_subscription_type'] ) ) {
		update_post_meta( $post_id, '_resume_package_subscription_type', sanitize_text_field( $_POST['_resume_package_subscription_type'] ) );
	}
	
	if ( isset( $_POST['_resumes_duration'] ) ) {
		update_post_meta( $post_id, '_resumes_duration', sanitize_text_field( $_POST['_resumes_duration'] ) );
	}
}
add_action( 'woocommerce_process_product_meta_resume_package', 'wp_job_board_pro_wc_paid_listings_save_resume_package_option_field'  );
add_action( 'woocommerce_process_product_meta_resume_package_subscription', 'wp_job_board_pro_wc_paid_listings_save_resume_package_option_field'  );