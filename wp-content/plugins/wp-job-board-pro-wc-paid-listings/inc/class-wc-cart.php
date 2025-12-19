<?php
/**
 * Cart
 *
 * @package    wp-job-board-pro-wc-paid-listings
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WP_Job_Board_Pro_Wc_Paid_Listings_Cart {

	/**
	 * Init
	 */
	public static function init() {
		add_filter( 'woocommerce_add_to_cart_redirect', array( __CLASS__, 'add_to_cart_redirect' ), 10, 2 );

		add_action( 'woocommerce_job_package_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
		add_action( 'woocommerce_cv_package_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
		add_action( 'woocommerce_contact_package_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
		add_action( 'woocommerce_candidate_package_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
		add_action( 'woocommerce_resume_package_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );

		if ( WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::is_woocommerce_subscriptions_pre( '4.0.0' ) ) {
			add_action( 'woocommerce_job_package_subscription_add_to_cart', 'WC_Subscriptions::subscription_add_to_cart', 30 );
			add_action( 'woocommerce_cv_package_subscription_add_to_cart', 'WC_Subscriptions::subscription_add_to_cart', 30 );
			add_action( 'woocommerce_contact_package_subscription_add_to_cart', 'WC_Subscriptions::subscription_add_to_cart', 30 );
			add_action( 'woocommerce_candidate_package_subscription_add_to_cart', 'WC_Subscriptions::subscription_add_to_cart', 30 );
			add_action( 'woocommerce_resume_package_subscription_add_to_cart', 'WC_Subscriptions::subscription_add_to_cart', 30 );
		} else {
			add_action( 'woocommerce_job_package_subscription_add_to_cart', 'WCS_Template_Loader::get_subscription_add_to_cart', 30 );
			add_action( 'woocommerce_cv_package_subscription_add_to_cart', 'WCS_Template_Loader::get_subscription_add_to_cart', 30 );
			add_action( 'woocommerce_contact_package_subscription_add_to_cart', 'WCS_Template_Loader::get_subscription_add_to_cart', 30 );
			add_action( 'woocommerce_candidate_package_subscription_add_to_cart', 'WCS_Template_Loader::get_subscription_add_to_cart', 30 );
			add_action( 'woocommerce_resume_package_subscription_add_to_cart', 'WCS_Template_Loader::get_subscription_add_to_cart', 30 );
		}


		add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'get_cart_item_from_session' ), 10, 2 );
		
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'order_line_item' ), 20, 4 );
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'get_item_data' ), 10, 2 );

		
		add_filter( 'option_woocommerce_enable_signup_and_login_from_checkout', array( __CLASS__, 'enable_checkout_signup' ) );

		add_filter( 'option_woocommerce_enable_guest_checkout', array( __CLASS__, 'guest_checkout' ) );
	}

	public static function add_to_cart_redirect($url, $_product) {
		if ( is_object($_product) && method_exists($_product, 'is_type') ) {
			if ( $_product->is_type( 'job_package' ) || $_product->is_type( 'cv_package' ) || $_product->is_type( 'contact_package' ) || $_product->is_type( 'candidate_package' ) || $_product->is_type( 'resume_package' ) ) {
				$url = get_permalink( get_option( 'woocommerce_checkout_page_id' ) );
			}
		}
		return $url;
	}

	/**
	 * Check cart contain job_package.
	 */
	public static function check_cart_has_job_package() {
		if ( !is_admin() ) {
			$cart_obj = WC()->cart;
			if ( is_object($cart_obj) ) {
				$cart_items = $cart_obj->get_cart_contents();
				if ( !empty( $cart_items ) ) {
					foreach ( $cart_items as $cart_item_key => $cart_item ) {
						$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
						if ( is_object($_product) && method_exists($_product, 'is_type') ) {
							if ( $_product->is_type( 'job_package' ) && ! $_product->is_type( 'job_package_subscription' ) ) {
								return true;
							}
						}
					}
				}
			}
		}
	}

	/**
	 * force: yes
	 */
	public static function enable_checkout_signup( $value ) {
		remove_filter( 'option_woocommerce_enable_guest_checkout', array( __CLASS__, 'guest_checkout' ) );
		
		$guest_checkout = get_option( 'woocommerce_enable_guest_checkout' );

		add_filter( 'option_woocommerce_enable_guest_checkout', array( __CLASS__, 'guest_checkout' ) );

		if ( $guest_checkout === 'yes' && self::check_cart_has_job_package() ) {
			$value = 'no';
		}

		return $value;
	}

	/**
	 * force: no
	 */
	public static function guest_checkout( $value ) {
		if ( self::check_cart_has_job_package() ) {
			$value = 'no';
		}
		return $value;
	}

	/**
	 * Get the data from the session on page load
	 */
	public static function get_cart_item_from_session( $cart_item, $values ) {
		if ( ! empty( $values['job_id'] ) ) {
			$cart_item['job_id'] = $values['job_id'];
		}
		return $cart_item;
	}

	/**
	 * show listing name in cart
	 */
	public static function get_item_data( $data, $cart_item ) {
		if ( isset( $cart_item['job_id'] ) ) {
			$data[] = array(
				'name'  => __( 'Job Listing', 'wp-job-board-pro-wc-paid-listings' ),
				'value' => get_the_title( intval( $cart_item['job_id'] ) )
			);
		}
		return $data;
	}

	/**
	 * order_item_meta function for storing the meta in the order line items
	 */
	public static function order_line_item( $item, $cart_item_key, $values, $order ) {
		if ( isset( $values['job_id'] ) ) {
			$item->update_meta_data( __( 'Job Listing', 'wp-job-board-pro-wc-paid-listings' ), get_the_title( intval( $values['job_id'] ) ) );
			$item->update_meta_data( '_job_id', $values['job_id'] );
		}
	}
}

WP_Job_Board_Pro_Wc_Paid_Listings_Cart::init();