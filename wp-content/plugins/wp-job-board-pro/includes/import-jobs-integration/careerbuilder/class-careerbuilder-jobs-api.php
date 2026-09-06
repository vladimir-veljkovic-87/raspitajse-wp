<?php
/**
 * Careerbuilder Jobs API
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Job_Board_Pro_Careerbuilder_API {
    
    public static function get_jobs($args) {
        
        $api_key = wp_job_board_pro_get_option('careerbuilder_job_import_api');

        $default_args = array(
            'DID' => $api_key,
            'keyword' => '',
            'location' => '',
            'jobs_per_page' => 10,
            'page' => 1,
            'outputjson' => true,
        );

        $args = wp_parse_args($args, $default_args);
        
        
        $jobs = array();
        $result = wp_remote_get('https://api.careerbuilder.com/v3/job?' . http_build_query($args, '', '&'), array('timeout' => 10));

        if (!is_wp_error($result) && !empty($result['body'])) {
            $jobs = json_decode($result['body']);
        }


        return $jobs;
    }
}