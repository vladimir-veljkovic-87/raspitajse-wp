<?php
/**
 * Contact Package
 *
 * @package    wp-job-board-pro-wc-paid-listings
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WP_Job_Board_Pro_Wc_Paid_Listings_Contact_Package {
	public static function init() {
		add_filter( 'wp_job_board_pro_settings_candidate_settings', array( __CLASS__, 'restrict_candidate_settings_fields' ), 11, 2 );
		add_filter( 'wp-job-board-pro-restrict-candidate-view-contact', array( __CLASS__, 'restrict_candidate_settings' ), 11 );

		add_filter( 'wp-job-board-pro-check-view-candidate-contact-info', array( __CLASS__, 'process_restrict_candidate_contact' ), 11, 2 );

		add_action( 'wp_job_board_pro_before_job_detail', array( __CLASS__, 'process_viewed_candidate' ), 10 );
	}

	public static function restrict_candidate_settings_fields($fields, $pages) {
		$fields[] = array(
			'name'    => __( 'Contact packages Page', 'wp-job-board-pro-wc-paid-listings' ),
			'desc'    => __( 'Select Contact Packages Page. It will redirect employers at selected page to buy package.', 'wp-job-board-pro-wc-paid-listings' ),
			'id'      => 'contact_package_page_id',
			'type'    => 'select',
			'options' => $pages,
		);
		return apply_filters('wp-job-board-pro-wc-paid-listings-restrict-candidate-settings-fields', $fields);
	}

	public static function restrict_candidate_settings($fields) {
		if ( !isset($fields['register_employer_contact_with_package']) ) {
			$fields['register_employer_contact_with_package'] = __( 'All users can view candidate, but only employers with package can see contact info (Users who purchased <strong class="highlight">Contact Package</strong> can see contact info.)', 'wp-job-board-pro-wc-paid-listings' );
		}
		return apply_filters('wp-job-board-pro-wc-paid-listings-restrict-candidate-settings', $fields);
	}

	public static function process_restrict_candidate_contact($return, $post) {
		$restrict_type = wp_job_board_pro_get_option('candidate_restrict_type', '');
		if ( $restrict_type == 'view_contact_info' ) {
			$view = wp_job_board_pro_get_option('candidate_restrict_contact_info', 'all');
			if ( $view == 'register_employer_contact_with_package' ) {
				$return = false;
				if ( is_user_logged_in() ) {
					$user_id = get_current_user_id();
					if ( class_exists('WP_Job_Board_Pro_User') && WP_Job_Board_Pro_User::is_employer($user_id) ) {
						
						$packages = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_contact_packages_by_user($user_id, true, $post->ID);
						if ( !empty($packages) ) {
							$return = true;
						} else {
							$return = WP_Job_Board_Pro_Candidate::candidate_only_applicants($post);
						}
					}
				}
			}
		}
		return apply_filters('wp-job-board-pro-wc-paid-listings-process-restrict-candidate-contact', $return, $post);
	}


	public static function process_viewed_candidate($candidate_id) {
		$restrict_type = wp_job_board_pro_get_option('candidate_restrict_type', '');
		if ( $restrict_type == 'view_contact_info' ) {
			$view = wp_job_board_pro_get_option('candidate_restrict_contact_info', 'all');
			if ( $view == 'register_employer_contact_with_package' && is_user_logged_in() ) {
				$user_id = get_current_user_id();
				if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
					$packages = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_contact_packages_by_user($user_id, true, $candidate_id);
					if ( !empty($packages) && !empty($packages[0]) ) {
						$package = $packages[0];
						WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::increase_contact_package_viewed_count($candidate_id, $user_id, $package->ID);
					}
				}
			}
		}
	}

	public static function check_user_can_contact_candidate($post) {
		$return = true;
		$restrict_type = wp_job_board_pro_get_option('candidate_restrict_type', '');
		if ( $restrict_type == 'view_contact_info' ) {
			$view = wp_job_board_pro_get_option('candidate_restrict_contact_info', 'all');
			if ( $view == 'register_employer_contact_with_package' ) {
				$return = false;
				if ( is_user_logged_in() ) {
					$user_id = get_current_user_id();
					if ( class_exists('WP_Job_Board_Pro_User') && WP_Job_Board_Pro_User::is_employer($user_id) ) {
						$packages = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_contact_packages_by_user($user_id, true, $post->ID);
						if ( !empty($packages) ) {
							$return = true;
						} else {
							$return = WP_Job_Board_Pro_Candidate::candidate_only_applicants($post);
						}
					}
				}
			}
		}

		return apply_filters('wp-job-board-pro-wc-paid-listings-check-user-can-contact-candidate', $return, $post);
	}
}

WP_Job_Board_Pro_Wc_Paid_Listings_Contact_Package::init();