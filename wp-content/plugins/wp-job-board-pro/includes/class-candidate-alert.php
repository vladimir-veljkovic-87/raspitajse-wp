<?php
/**
 * Job Alert
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Candidate_Alert {
	public static function init() {
		add_action( 'wp_job_board_pro_email_daily_notices', array( __CLASS__, 'send_candidate_alert_notice' ) );

		// Ajax endpoints.
		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_add_candidate_alert',  array(__CLASS__,'process_add_candidate_alert') );
		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_remove_candidate_alert',  array(__CLASS__,'process_remove_candidate_alert') );

		// compatible handlers.
		add_action( 'wp_ajax_wp_job_board_pro_ajax_add_candidate_alert',  array(__CLASS__,'process_add_candidate_alert') );
		add_action( 'wp_ajax_nopriv_wp_job_board_pro_ajax_add_candidate_alert',  array(__CLASS__,'process_add_candidate_alert') );

		add_action( 'wp_ajax_wp_job_board_pro_ajax_remove_candidate_alert',  array(__CLASS__,'process_remove_candidate_alert') );
		add_action( 'wp_ajax_nopriv_wp_job_board_pro_ajax_remove_candidate_alert',  array(__CLASS__,'process_remove_candidate_alert') );
	}

	public static function send_candidate_alert_notice() {
		$email_frequency_default = WP_Job_Board_Pro_Job_Alert::get_email_frequency();
		if ( $email_frequency_default ) {
			foreach ($email_frequency_default as $key => $value) {
				if ( !empty($value['days']) ) {
					$meta_query = array(
						'relation' => 'OR',
						array(
							'key' => WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX.'send_email_time',
							'compare' => 'NOT EXISTS',
						)
					);
					$current_time = apply_filters( 'wp-job-board-pro-candidate-alert-current-'.$key.'-time', date( 'Y-m-d', strtotime( '-'.intval($value['days']).' days', current_time( 'timestamp' ) ) ) );
					$meta_query[] = array(
						'relation' => 'AND',
						array(
							'key' => WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX.'send_email_time',
							'value' => $current_time,
							'compare' => '<=',
						),
						array(
							'key' => WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX.'email_frequency',
							'value' => $key,
							'compare' => '=',
						),
					);

					$query_args = apply_filters( 'wp-job-board-pro-candidate-alert-query-args', array(
						'post_type' => 'candidate_alert',
						'post_per_page' => -1,
						'post_status' => 'publish',
						'fields' => 'ids',
						'meta_query' => $meta_query
					));

					$candidate_alerts = new WP_Query($query_args);
					if ( !empty($candidate_alerts->posts) ) {
						foreach ($candidate_alerts->posts as $post_id) {
							$author_id = get_post_field('post_author', $post_id);
							$alert_query = get_post_meta($post_id, WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX . 'alert_query', true);
							
							$params = $alert_query;
							if ( !empty($alert_query) && !is_array($alert_query) ) {
								$params = json_decode($alert_query, true);
							}

							$query_args = array(
								'post_type' => 'candidate',
							    'post_status' => 'publish',
							    'post_per_page' => 1,
							    'fields' => 'ids',
							    'view_user_id' => $author_id
							);
							$candidates = WP_Job_Board_Pro_Query::get_posts($query_args, $params);
							$count_candidates = $candidates->found_posts;
							$candidate_alert_title = get_the_title($post_id);
							// send email action
							$email_from = get_option( 'admin_email', false );
							
							$headers = sprintf( "From: %s <%s>\r\n Content-type: text/html", get_bloginfo('name'), $email_from );
							
							$author_id = get_post_field( 'post_author', $post_id );
							$email_to = get_the_author_meta('user_email', $author_id);

							$subject = WP_Job_Board_Pro_Email::render_email_vars(array('alert_title' => $candidate_alert_title), 'candidate_alert_notice', 'subject');

							$email_frequency = get_post_meta($post_id, WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX.'email_frequency', true);
							if ( !empty($email_frequency_default[$email_frequency]['label']) ) {
								$email_frequency = $email_frequency_default[$email_frequency]['label'];
							}
							$candidates_alert_url = WP_Job_Board_Pro_Mixes::get_candidates_page_url();
							if ( !empty($params) ) {
								foreach ($params as $key => $value) {
									if ( is_array($value) ) {
										$candidates_alert_url = remove_query_arg( $key.'[]', $candidates_alert_url );
										foreach ($value as $val) {
											$candidates_alert_url = add_query_arg( $key.'[]', $val, $candidates_alert_url );
										}
									} else {
										$candidates_alert_url = add_query_arg( $key, $value, remove_query_arg( $key, $candidates_alert_url ) );
									}
								}
							}
							$content_args = apply_filters( 'wp-job-board-pro-candidate-alert-email-content-args', array(
								'alert_title' => $candidate_alert_title,
								'candidates_found' => $count_candidates,
								'email_frequency_type' => $email_frequency,
								'candidates_alert_url' => $candidates_alert_url
							));
							$content = WP_Job_Board_Pro_Email::render_email_vars($content_args, 'candidate_alert_notice', 'content');
										
							WP_Job_Board_Pro_Email::wp_mail( $email_to, $subject, $content, $headers );
							$current_time = date( 'Y-m-d', current_time( 'timestamp' ) );
							delete_post_meta($post_id, WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX.'send_email_time');
							add_post_meta($post_id, WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX.'send_email_time', $current_time);
						}
					}
				}
			}
		}
		
	}

	public static function process_add_candidate_alert() {
		$return = array();
		
		if ( !is_user_logged_in() || !WP_Job_Board_Pro_User::is_employer() ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Please login as "Employer" to add candidate alert.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);

		$errors = self::validate_add_candidate_alert();
		if ( !empty($errors) && sizeof($errors) > 0 ) {
			$return = array( 'status' => false, 'msg' => implode(', ', $errors) );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$name = !empty($_POST['name']) ? $_POST['name'] : '';
		
		$post_args = array(
            'post_title' => $name,
            'post_type' => 'candidate_alert',
            'post_content' => '',
            'post_status' => 'publish',
            'user_id' => $user_id
        );
		$post_args = apply_filters('wp-job-board-pro-add-candidate-alert-data', $post_args);
		
		do_action('wp-job-board-pro-before-add-candidate-alert');

        // Insert the post into the database
        $alert_id = wp_insert_post($post_args);
        if ( $alert_id ) {
	        update_post_meta($alert_id, WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX . 'candidate_id', $candidate_id);
	        $email_frequency = !empty($_POST['email_frequency']) ? $_POST['email_frequency'] : '';
	        update_post_meta($alert_id, WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX . 'email_frequency', $email_frequency);

	        $alert_query = array();
			if ( ! empty( $_POST ) && is_array( $_POST ) ) {
				foreach ( $_POST as $key => $value ) {
					if ( strrpos( $key, 'filter-', -strlen( $key ) ) !== false ) {
						$alert_query[$key] = $value;
					}
				}
			}
	        if ( !empty($alert_query) ) {
	        	// $alert_query = json_encode($alert_query);
	        	update_post_meta($alert_id, WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX . 'alert_query', $alert_query);	
	        }
	        
	        do_action('wp-job-board-pro-after-add-candidate-alert', $alert_id);

	        $return = array( 'status' => true, 'msg' => esc_html__('Add candidate alert successfully.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
	    } else {
			$return = array( 'status' => false, 'msg' => esc_html__('Add candidate alert error.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
	}

	public static function validate_add_candidate_alert() {
		$return = array();
		$name = !empty($_POST['name']) ? $_POST['name'] : '';
		if ( empty($name) ) {
			$return[] = esc_html__('Name is required.', 'wp-job-board-pro');
		}
		$email_frequency = !empty($_POST['email_frequency']) ? $_POST['email_frequency'] : '';
		if ( empty($email_frequency) ) {
			$return[] = esc_html__('Email frequency is required.', 'wp-job-board-pro');
		}
		return $return;
	}

	public static function process_remove_candidate_alert() {
		$return = array();

		if ( !is_user_logged_in() ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Please login to remove candidate alert.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$alert_id = !empty($_POST['alert_id']) ? $_POST['alert_id'] : '';

		if ( empty($alert_id) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Candidate did not exists.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$is_allowed = WP_Job_Board_Pro_Mixes::is_allowed_to_remove( $user_id, $alert_id );

		if ( ! $is_allowed ) {
	        $return = array( 'status' => false, 'msg' => esc_html__('You can not remove this candidate alert.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}

		if ( wp_delete_post( $alert_id ) ) {
	        $return = array( 'status' => true, 'msg' => esc_html__('Remove candidate alert successfully.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
	    } else {
			$return = array( 'status' => false, 'msg' => esc_html__('Remove candidate alert error.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
	}
}

WP_Job_Board_Pro_Candidate_Alert::init();