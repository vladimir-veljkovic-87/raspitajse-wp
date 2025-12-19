<?php
/**
 * Job RSS Feed
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Job_RSS_Feed {

	public static function init() {
		 add_action( 'init', array( __CLASS__, 'custom_rss' ) );
	}

	public static function custom_rss() {
        add_feed( 'job_listing_feed', array( __CLASS__, 'custom_feed_template' ) );
        add_feed( 'candidate_listing_feed', array( __CLASS__, 'custom_candidate_feed_template' ) );
    }

    public static function custom_feed_template() {
        echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'rss-feed-jobs' );
    }

    public static function custom_candidate_feed_template() {
        echo WP_Job_Board_Pro_Template_Loader::get_template_part( 'rss-feed-candidates' );
    }
}

WP_Job_Board_Pro_Job_RSS_Feed::init();