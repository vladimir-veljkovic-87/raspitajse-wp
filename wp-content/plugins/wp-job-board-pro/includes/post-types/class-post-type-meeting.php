<?php
/**
 * Post Type: Meeting
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Post_Type_Meeting {
	
	public static $prefix = WP_JOB_BOARD_PRO_MEETING_PREFIX;

	public static function init() {
	  	add_action( 'init', array( __CLASS__, 'register_post_type' ) );
	}

	public static function register_post_type() {
		$singular = __( 'Meeting', 'wp-job-board-pro' );
		$plural   = __( 'Meetings', 'wp-job-board-pro' );

		$labels = array(
			'name'                  => $plural,
			'singular_name'         => $singular,
			'add_new'               => sprintf(__( 'Add New %s', 'wp-job-board-pro' ), $singular),
			'add_new_item'          => sprintf(__( 'Add New %s', 'wp-job-board-pro' ), $singular),
			'edit_item'             => sprintf(__( 'Edit %s', 'wp-job-board-pro' ), $singular),
			'new_item'              => sprintf(__( 'New %s', 'wp-job-board-pro' ), $singular),
			'all_items'             => $plural,
			'view_item'             => sprintf(__( 'View %s', 'wp-job-board-pro' ), $singular),
			'search_items'          => sprintf(__( 'Search %s', 'wp-job-board-pro' ), $singular),
			'not_found'             => sprintf(__( 'No %s found', 'wp-job-board-pro' ), $plural),
			'not_found_in_trash'    => sprintf(__( 'No %s found in Trash', 'wp-job-board-pro' ), $plural),
			'parent_item_colon'     => '',
			'menu_name'             => $plural,
		);

		register_post_type( 'job_meeting',
			array(
				'labels'            => $labels,
				'supports'          => array( 'title' ),
				'public'            => false,
		        'has_archive'       => false,
		        'publicly_queryable' => false,
				'show_in_rest'		=> false,
				'exclude_from_search' => true,
            	'hierarchical' => false,

	            'show_ui' => false,
	            'show_in_menu' => false,
	            'query_var' => false,
			)
		);
	}

}
WP_Job_Board_Pro_Post_Type_Meeting::init();