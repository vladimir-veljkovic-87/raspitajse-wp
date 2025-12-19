<?php
/**
 * Rest API
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Rest_API {
	
	public static function init() {
		add_action( 'rest_api_init', array(__CLASS__,'register_meta_fields'));
	}
	
	public static function register_meta_fields(){

		register_rest_field( 'job_listing', 'metas', array(
		 	'get_callback' => array(__CLASS__, 'get_job_metas_for_api'),
		 	'schema' => null,
		));

		register_rest_field( 'candidate', 'metas', array(
		 	'get_callback' => array(__CLASS__, 'get_candidate_metas_for_api'),
		 	'schema' => null,
		));

		register_rest_field( 'employer', 'metas', array(
		 	'get_callback' => array(__CLASS__, 'get_employer_metas_for_api'),
		 	'schema' => null,
		));
	}

	public static function get_job_metas_for_api($object) {
		$prefix = WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX;

		$post_id = $object['id'];

		$meta_obj = WP_Job_Board_Pro_Job_Listing_Meta::get_instance($post_id);
		$fields = $meta_obj->get_post_metas();
		if ( isset($fields[$prefix.'title']) ) {
			unset($fields[$prefix.'title']);
		}
		if ( isset($fields[$prefix.'description']) ) {
			unset($fields[$prefix.'description']);
		}
		if ( isset($fields[$prefix.'max_salary']) ) {
			unset($fields[$prefix.'max_salary']);
		}
		$return = array();
		foreach ($fields as $key => $field) {
			if ( $field['type'] != 'title' ) {
				switch ($key) {
					case $prefix.'category':
						$terms = get_the_terms( $post_id, 'job_listing_category' );
						$values = [];
						if ( $terms ) {
							foreach ($terms as $term) {
								$values[$term->term_id] = $term->name;
							}
						}
						$return[$key] = $values;
						break;
					case $prefix.'type':
						$terms = get_the_terms( $post_id, 'job_listing_type' );
						$values = [];
						if ( $terms ) {
							foreach ($terms as $term) {
								$values[$term->term_id] = $term->name;
							}
						}
						$return[$key] = $values;
						break;
					case $prefix.'tag':
						$terms = get_the_terms( $post_id, 'job_listing_tag' );
						$values = [];
						if ( $terms ) {
							foreach ($terms as $term) {
								$values[$term->term_id] = $term->name;
							}
						}
						$return[$key] = $values;
						break;
					case $prefix.'location':
						$terms = get_the_terms( $post_id, 'job_listing_location' );
						$values = [];
						if ( $terms ) {
							foreach ($terms as $term) {
								$values[$term->term_id] = $term->name;
							}
						}
						$return[$key] = $values;
						break;
					case $prefix.'salary':
						$return[$key] = WP_Job_Board_Pro_Job_Listing::get_salary_html($post_id, false);
						break;
					case $prefix.'map_location':
						$values = [];
						$values['address'] = WP_Job_Board_Pro_Job_Listing::get_post_meta($post_id, 'map_location_address');
						$values['latitude'] = WP_Job_Board_Pro_Job_Listing::get_post_meta($post_id, 'map_location_latitude');
						$values['longitude'] = WP_Job_Board_Pro_Job_Listing::get_post_meta($post_id, 'map_location_longitude');
						$return[$key] = $values;
						break;
					default:
						$return[$key] = $meta_obj->get_custom_post_meta($key);
						break;
				}
			}
		}

		$author_id = get_post_field('post_author', $post_id);
		if ( WP_Job_Board_Pro_User::is_employer($author_id) ) {
			$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);
			if ( empty($return[$prefix.'logo']) ) {
				if ( has_post_thumbnail($employer_id) ) {
					$return[$prefix.'logo'] = get_the_post_thumbnail_url($employer_id, 'thumbnail');
				}
			}
			$return[$prefix.'employer_name'] = get_the_title($employer_id);
			$return[$prefix.'employer_url'] = get_permalink($employer_id);
		}
		

		return $return;
	}

	public static function get_candidate_metas_for_api($object) {
		$prefix = WP_JOB_BOARD_PRO_CANDIDATE_PREFIX;

		$post_id = $object['id'];

		$meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($post_id);
		$fields = $meta_obj->get_post_metas();
		if ( isset($fields[$prefix.'title']) ) {
			unset($fields[$prefix.'title']);
		}
		if ( isset($fields[$prefix.'description']) ) {
			unset($fields[$prefix.'description']);
		}
		if ( isset($fields[$prefix.'featured_image']) ) {
			unset($fields[$prefix.'featured_image']);
		}
		$return = array();
		foreach ($fields as $key => $field) {
			if ( $field['type'] != 'title' ) {
				switch ($key) {
					case $prefix.'category':
						$terms = get_the_terms( $post_id, 'candidate_category' );
						$values = [];
						if ( $terms ) {
							foreach ($terms as $term) {
								$values[$term->term_id] = $term->name;
							}
						}
						$return[$key] = $values;
						break;
					case $prefix.'tag':
						$terms = get_the_terms( $post_id, 'candidate_tag' );
						$values = [];
						if ( $terms ) {
							foreach ($terms as $term) {
								$values[$term->term_id] = $term->name;
							}
						}
						$return[$key] = $values;
						break;
					case $prefix.'location':
						$terms = get_the_terms( $post_id, 'candidate_location' );
						$values = [];
						if ( $terms ) {
							foreach ($terms as $term) {
								$values[$term->term_id] = $term->name;
							}
						}
						$return[$key] = $values;
						break;
					case $prefix.'salary':
						$return[$key] = WP_Job_Board_Pro_Candidate::get_salary_html($post_id, false);
						break;
					case $prefix.'map_location':
						$values = [];
						$values['address'] = WP_Job_Board_Pro_Candidate::get_post_meta($post_id, 'map_location_address');
						$values['latitude'] = WP_Job_Board_Pro_Candidate::get_post_meta($post_id, 'map_location_latitude');
						$values['longitude'] = WP_Job_Board_Pro_Candidate::get_post_meta($post_id, 'map_location_longitude');
						$return[$key] = $values;
						break;
					default:
						$return[$key] = $meta_obj->get_custom_post_meta($key);
						break;
				}
			}
		}
		if ( has_post_thumbnail($post_id) ) {
			$return[$prefix.'logo'] = get_the_post_thumbnail_url($post_id, 'thumbnail');
		}
		return $return;
	}

	public static function get_employer_metas_for_api($object) {
		$prefix = WP_JOB_BOARD_PRO_EMPLOYER_PREFIX;

		$post_id = $object['id'];

		$meta_obj = WP_Job_Board_Pro_Employer_Meta::get_instance($post_id);
		$fields = $meta_obj->get_post_metas();
		if ( isset($fields[$prefix.'title']) ) {
			unset($fields[$prefix.'title']);
		}
		if ( isset($fields[$prefix.'description']) ) {
			unset($fields[$prefix.'description']);
		}
		$return = array();
		foreach ($fields as $key => $field) {
			if ( $field['type'] != 'title' ) {
				switch ($key) {
					case $prefix.'category':
						$terms = get_the_terms( $post_id, 'employer_category' );
						$values = [];
						if ( $terms ) {
							foreach ($terms as $term) {
								$values[$term->term_id] = $term->name;
							}
						}
						$return[$key] = $values;
						break;
					case $prefix.'location':
						$terms = get_the_terms( $post_id, 'employer_location' );
						$values = [];
						if ( $terms ) {
							foreach ($terms as $term) {
								$values[$term->term_id] = $term->name;
							}
						}
						$return[$key] = $values;
						break;
					case $prefix.'map_location':
						$values = [];
						$values['address'] = WP_Job_Board_Pro_Employer::get_post_meta($post_id, 'map_location_address');
						$values['latitude'] = WP_Job_Board_Pro_Employer::get_post_meta($post_id, 'map_location_latitude');
						$values['longitude'] = WP_Job_Board_Pro_Employer::get_post_meta($post_id, 'map_location_longitude');
						$return[$key] = $values;
						break;
					default:
						$return[$key] = $meta_obj->get_custom_post_meta($key);
						break;
				}
			}
		}

		if ( has_post_thumbnail($post_id) ) {
			$return[$prefix.'logo'] = get_the_post_thumbnail_url($post_id, 'thumbnail');
		}

		return $return;
	}
}

WP_Job_Board_Pro_Rest_API::init();