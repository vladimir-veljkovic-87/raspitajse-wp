<?php
/**
 * Careerjet Jobs API
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Job_Board_Pro_Careerjet_API {

    public static function get_jobs($args) {
        
        $api_key = wp_job_board_pro_get_option('careerjet_job_import_api');
        
        $default_args = array(
            'affid' => $api_key,
            'keywords' => '',
            'location' => '',
            'page' => 1,
        );

        $args = wp_parse_args( $args, $default_args );
        
        require_once 'Careerjet_API.php';
        
        $api = new Careerjet_API('en_GB') ;
        
        $jobs = array();
        $result = $api->search($args);

        if ( !empty($result->type) && $result->type == 'JOBS' ) {
            $jobs = $result->jobs;
        }

        return $jobs;
    }

}