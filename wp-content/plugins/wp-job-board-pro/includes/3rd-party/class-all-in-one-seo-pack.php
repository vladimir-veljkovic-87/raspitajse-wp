<?php
/**
 * All_In_One_Seo_Pack
 *
 * @package    wp-job-board
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_All_In_One_Seo_Pack {
	
	public static function init() {
		
		add_action( 'aiosp_sitemap_post_filter', array(__CLASS__, 'sitemap_filter_filled_jobs'), 10, 3 );
	}

	public static function sitemap_filter_filled_jobs($posts) {
		foreach ( $posts as $index => $post ) {
			if ( $post instanceof WP_Post && 'job_listing' !== $post->post_type && 'candidate' !== $post->post_type ) {
				continue;
			}
			
			if ( $post->post_type == 'job_listing' ) {
				if ( $post->post_status == 'expired' || WP_Job_Board_Pro_Job_Listing::is_filled( $post->ID ) ) {
					unset( $posts[ $index ] );
				}
			} elseif ( $post->post_type == 'candidate' ) {
				$meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($post->ID);
				if ( $post->post_status == 'expired' || ($meta_obj->check_post_meta_exist('show_profile') && $meta_obj->get_post_meta('show_profile') == 'hide') ) {
					unset( $posts[ $index ] );
				}
			}  elseif ( $post->post_type == 'employer' ) {
				$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($post->ID);
				if ( $post->post_status == 'expired' || ($meta_obj->check_post_meta_exist('show_profile') && $meta_obj->get_post_meta('show_profile') == 'hide') ) {
					unset( $posts[ $index ] );
				}
			}
		}
		return $posts;
	}

}

WP_Job_Board_Pro_All_In_One_Seo_Pack::init();