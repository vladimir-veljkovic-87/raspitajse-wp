<?php
/**
 * All Import
 *
 * @package    wp-job-board
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_All_Import {
	
	public static function init() {
		add_action( 'pmxi_saved_post', array(__CLASS__, 'pmxi_saved_post' ), 10, 2 );
	}

	public static function pmxi_saved_post( $post_id, $rootNodes ) {

		$post_type = get_post_type( $post_id );
		if ( 'job_listing' === $post_type ) {
			$location = get_post_meta( $post_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'address', true );
			if ( $location ) {
				WP_Job_Board_Geocode::generate_location_data( $post_id, $location );
			}
		} elseif ( in_array($post_type, array('employer', 'candidate')) ) {
			update_post_meta($post_id, $prefix . '_'.$post_type.'_show_profile', 'show');
			
			$user_email = '';
			if ( !empty($rootNodes->useremail) ) {
				$user_email = (string)$rootNodes->useremail;
			}
			WP_Job_Board_Pro_User::generate_user_by_post($post_id, $user_email);
		}
	}

}

WP_Job_Board_All_Import::init();