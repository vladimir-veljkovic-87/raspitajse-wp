<?php
/**
 * Resume Package
 *
 * @package    wp-job-board-pro-wc-paid-listings
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WP_Job_Board_Pro_Wc_Paid_Listings_Resume_Package {
	public static function init() {
		add_filter('wp-job-board-pro-calculate-candidate-expiry', array( __CLASS__, 'calculate_resume_expiry' ), 10, 2 );

		add_action( 'wp-job-board-pro-resume-form-status', array( __CLASS__, 'packages' ), 10, 2 );

		add_filter('wp-job-board-pro-create-candidate-post-args', array( __CLASS__, 'create_candidate_args' ), 10, 1);
		add_filter('wp-job-board-pro-approve-user-post-status', array( __CLASS__, 'approve_user' ), 10, 2);
	}

	public static function calculate_resume_expiry($duration, $candidate_id) {
		if ( metadata_exists( 'post', $candidate_id, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'package_duration' ) ) {
			$duration = get_post_meta( $candidate_id, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'package_duration', true );
		}

		return $duration;
	}

	public static function packages($post_status, $post_id) {
		if ( $post_status == 'expired' || $post_status == 'pending_payment' ) {
			$packages = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_resume_package_products();
			echo WP_Job_Board_Pro_Wc_Paid_Listings_Template_Loader::get_template_part('resume-packages', array('packages' => $packages) );
		} elseif ( $post_status == 'pending' || $post_status == 'pending_approve' ) {
			$user_package_id = get_post_meta( $post_id, '_user_package_id', true );
			if ( empty($user_package_id) ) {
				$packages = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_resume_package_products();
				echo WP_Job_Board_Pro_Wc_Paid_Listings_Template_Loader::get_template_part('resume-packages', array('packages' => $packages) );
			}
		}
	}

	public static function create_candidate_args($post_args) {

		$packages = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_resume_package_products();
		if ( !empty($packages) ) {
			$post_args['post_status'] = 'pending_payment';
		}
		return $post_args;
	}

	public static function approve_user($post_status, $user) {

		$packages = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_resume_package_products();
		if ( !empty($packages) ) {
			$post_status = 'pending_payment';
		}
		return $post_status;
	}
}

WP_Job_Board_Pro_Wc_Paid_Listings_Resume_Package::init();