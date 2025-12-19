<?php
/**
 * Jetpack
 *
 * @package    wp-job-board
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Jetpack {
	
	public static function init() {
		add_action( 'jetpack_sitemap_skip_post', array(__CLASS__, 'skip_filled_job_listings'), 10, 2 );

		add_filter( 'jetpack_sitemap_post_types', array(__CLASS__, 'add_post_type') );
	}

	public static function skip_filled_job_listings($skip_post, $post) {
		
		if ( 'job_listing' !== $post->post_type && 'candidate' !== $post->post_type ) {
			return $skip_post;
		}

		if ( $post->post_type == 'job_listing' ) {
			if ( $post->post_status == 'expired' || WP_Job_Board_Pro_Job_Listing::is_filled( $post->ID ) ) {
				return false;
			}
		} elseif ( $post->post_type == 'candidate' ) {
			$meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);
			if ( $post->post_status == 'expired' || ($meta_obj->check_post_meta_exist('show_profile') && $meta_obj->get_post_meta('show_profile') == 'hide') ) {
				return false;
			}
		} elseif ( $post->post_type == 'employer' ) {
			$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($post->ID);
			if ( $post->post_status == 'expired' || ($meta_obj->check_post_meta_exist('show_profile') && $meta_obj->get_post_meta('show_profile') == 'hide') ) {
				return false;
			}
		}

		return $skip_post;
	}

	public static function add_post_type($post_types) {
		$post_types[] = 'job_listing';
		return $post_types;
	}

}

WP_Job_Board_Pro_Jetpack::init();