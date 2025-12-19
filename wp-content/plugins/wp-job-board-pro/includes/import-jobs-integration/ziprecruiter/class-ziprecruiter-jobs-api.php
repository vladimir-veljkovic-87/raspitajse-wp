<?php
/**
 * Ziprecruiter Jobs API
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Job_Board_Pro_Ziprecruiter_API {
    
    public static function get_jobs($args) {
        
        $api_key = wp_job_board_pro_get_option('ziprecruiter_job_import_api');

        $default_args = array(
            'api_key' => $api_key,
            'search' => '',
            'location' => '',
            'jobs_per_page' => 10,
            'page' => 1,
            'radius_miles' => 20,
            'days_ago' => ''
        );
        $args = wp_parse_args($args, $default_args);
        
        
        $results = array();
        $result_data = wp_remote_get('https://api.ziprecruiter.com/jobs/v1/?' . http_build_query($args, '', '&'), array('timeout' => 10));

        if (!is_wp_error($result_data) && !empty($result_data['body'])) {
            $results = json_decode($result_data['body']);
            if (!$results || empty($results->success)) {
                return array();
            }
        } else {
            return array();
        }

        $jobs = array();
        foreach ($results->jobs as $result) {
            $jobs[] = array(
                'title' => sanitize_text_field($result->name),
                'company' => sanitize_text_field($result->hiring_company->name),
                'tagline' => sanitize_text_field($result->snippet),
                'url' => esc_url_raw($result->url),
                'location' => sanitize_text_field($result->location),
                'latitude' => '',
                'longitude' => '',
                'type' => '',
                'type_slug' => '',
                'timestamp' => strtotime($result->posted_time),
                'link_attributes' => array(),
                'logo' => ''
            );
        }

        return $jobs;
    }
}