<?php
/**
 * Custom Fields Register
 *
 * @package    wp-job-board-pro
 * @author     Habq
 * @license    GNU General Public License, version 3
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WP_Job_Board_Pro_Custom_Fields_Register {
	
	public static function init() {
        add_filter('wp-job-board-pro-register-employer-fields', array(__CLASS__, 'employer_fields_display'));
		add_action('wp-job-board-pro-register-candidate-fields', array(__CLASS__, 'candidate_fields_display'));
	}

	public static function employer_fields_display($fields) {
        $prefix = WP_JOB_BOARD_PRO_EMPLOYER_PREFIX;
        $custom_fields = WP_Job_Board_Pro_Custom_Fields::get_register_custom_fields(array(), $prefix);
        // echo "<pre>".print_r($custom_fields,1); die;
        return array_merge($fields, $custom_fields);
	}

    public static function candidate_fields_display($fields) {
        $prefix = WP_JOB_BOARD_PRO_CANDIDATE_PREFIX;
        
        $custom_fields = WP_Job_Board_Pro_Custom_Fields::get_register_custom_fields(array(), $prefix);
        
        return array_merge($fields, $custom_fields);
    }

}

WP_Job_Board_Pro_Custom_Fields_Register::init();