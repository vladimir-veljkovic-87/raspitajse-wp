<?php
/**
 * CV Package
 *
 * @package    wp-job-board-pro-wc-paid-listings
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WP_Job_Board_Pro_Wc_Paid_Listings_CV_Package {
	public static function init() {
		add_filter( 'wp-job-board-pro-restrict-candidate-detail', array( __CLASS__, 'restrict_candidate' ), 10 );
		add_filter( 'wp-job-board-pro-restrict-candidate-listing', array( __CLASS__, 'restrict_candidate' ), 10 );

		add_filter( 'wp-job-board-pro-check-view-candidate-detail', array( __CLASS__, 'process_restrict_candidate_detail' ), 10, 2 );
		add_filter( 'wp-job-board-pro-check-view-candidate-listing-query-args', array( __CLASS__, 'process_restrict_candidate_listing' ), 10, 2 );

		add_action( 'wp_job_board_pro_before_job_detail', array( __CLASS__, 'process_viewed_candidate' ), 10 );

		add_filter( 'wp-job-board-pro-restrict-candidate-detail-information', array( __CLASS__, 'restrict_candidate_information' ), 10, 2 );
		add_action( 'wp-job-board-pro-restrict-candidate-listing-default-information', array( __CLASS__, 'restrict_candidate_listing_information' ), 10, 2 );
	}

	public static function restrict_candidate($fields) {
		if ( !isset($fields['register_employer_with_package']) ) {
			$fields['register_employer_with_package'] = __( 'Register Employers with package (Registered employers who purchased <strong class="highlight">CV Package</strong> can view candidates.)', 'wp-job-board-pro-wc-paid-listings' );
		}
		return apply_filters('wp-job-board-pro-wc-paid-listings-restrict-candidate', $fields);
	}

	public static function process_restrict_candidate_detail($return, $post) {
		$restrict_type = wp_job_board_pro_get_option('candidate_restrict_type', '');
		if ( $restrict_type == 'view' ) {
			$view = wp_job_board_pro_get_option('candidate_restrict_detail', 'all');
			if ( $view == 'register_employer_with_package' ) {
				$return = false;
				if ( is_user_logged_in() ) {
					$user_id = get_current_user_id();
					$author_id = WP_Job_Board_Pro_User::get_user_by_candidate_id($post->ID);
					if ( $user_id == $author_id ) {
						$return = true;
					} elseif ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
						$packages = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_cv_packages_by_user($user_id, true, $post->ID);
						if ( !empty($packages) ) {
							$return = true;
						}
					}
				}
			}
		}

		return apply_filters('wp-job-board-pro-wc-paid-listings-process-restrict-candidate-detail', $return, $post);
	}

	public static function process_restrict_candidate_listing($query_args) {

		$view_user_id = !empty($query_args['view_user_id']) ? $query_args['view_user_id'] : 0;
		$restrict_type = wp_job_board_pro_get_option('candidate_restrict_type', '');
		if ( $restrict_type == 'view' ) {
			$view = wp_job_board_pro_get_option('candidate_restrict_listing', 'all');
			if ( $view == 'register_employer_with_package' ) {
				$return = false;
				if ( $view_user_id ) {
					$user_id = $view_user_id;
				} elseif ( is_user_logged_in() ) {
					$user_id = WP_Job_Board_Pro_User::get_user_id();
				}

				if ( !empty($user_id) ) {
					if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
						$packages = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_cv_packages_by_user($user_id);
						if ( !empty($packages) ) {
							$return = true;
						}
					}
				}

				if ( !$return ) {
					$meta_query = !empty($query_args['meta_query']) ? $query_args['meta_query'] : array();
					$meta_query[] = array(
						'key'       => 'candidate_restrict_listing',
						'value'     => 'register_employer_with_package',
						'compare'   => '==',
					);
					$query_args['meta_query'] = $meta_query;
				}
			}
		}

		return apply_filters( 'wp-job-board-pro-wc-paid-listings-process-restrict-candidate-listing', $query_args );
	}

	public static function process_viewed_candidate($candidate_id) {
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
				$packages = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_cv_packages_by_user($user_id, true, $candidate_id);
				if ( !empty($packages) && !empty($packages[0]) ) {
					$package = $packages[0];
					WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::increase_cv_package_viewed_count($candidate_id, $user_id, $package->ID);
				}
			}
		}
	}
	
	public static function restrict_candidate_information($content, $post) {
		$view = wp_job_board_pro_get_option('candidate_restrict_detail', 'all');
		if ( $view == 'register_employer_with_package' ) {
			$packages = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_cv_package_products();
			$content = apply_filters( 'wp-job-board-pro-wc-paid-listings-restrict-candidate-information', '<div class="restrict-cv-package-info">'.
					'<h2 class="restrict-title">'.__( 'The page is restricted only for subscribed employers.', 'wp-job-board-pro-wc-paid-listings' ).'</h2>'.
					'<div class="restrict-inner">'.
						WP_Job_Board_Pro_Wc_Paid_Listings_Template_Loader::get_template_part('cv-packages', array('packages' => $packages) ).
					'</div>'.
				'</div>');
		}
		return $content;
	}

	public static function restrict_candidate_listing_information($content, $query) {
		
		$view = wp_job_board_pro_get_option('candidate_restrict_listing', 'all');
		if ( $view == 'register_employer_with_package' ) {
			$return = false;
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
				if ( class_exists('WP_Job_Board_Pro_User') && WP_Job_Board_Pro_User::is_employer($user_id) ) {
					$packages = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_cv_packages_by_user($user_id);
					if ( !empty($packages) ) {
						$return = true;
					}
				}
			}
			if ( !$return ) {
				$packages = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_cv_package_products();
				$content = apply_filters( 'wp-job-board-pro-wc-paid-listings-restrict-candidate-listing-information', '<div class="restrict-cv-package-info">'.
						'<h2 class="restrict-title">'.__( 'The page is restricted only for subscribed employers.', 'wp-job-board-pro-wc-paid-listings' ).'</h2>'.
						'<div class="restrict-inner">'.
							WP_Job_Board_Pro_Wc_Paid_Listings_Template_Loader::get_template_part('cv-packages', array('packages' => $packages) ).
						'</div>'.
					'</div>');
			}
		}
		return $content;
	}
}

WP_Job_Board_Pro_Wc_Paid_Listings_CV_Package::init();