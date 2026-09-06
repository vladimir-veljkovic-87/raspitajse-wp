<?php
/**
 * User Notification
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_User_Notification {
	
	public static function init() {

		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_remove_notify',  array(__CLASS__, 'process_remove_notification') );

		// for private message
		add_action('wp-private-message-after-add-message', array( __CLASS__, 'add_private_message_nnotify'), 10, 3 );
	}

	public static function add_notification($args) {
		
		$args = wp_parse_args( $args, array(
			'post_type' => 'employer',
			'user_post_id' => 0,
			'unique_id' => uniqid(),
			'viewed' => 0,
            'time' => current_time('timestamp'),
            'type' => '',
            'application_id' => 0,
            'employer_id' => 0,
            'job_id' => 0,
		));

		extract( $args );

		if ( empty($user_post_id) || empty($post_type) ) {
			return;
		}

		$prefix = WP_JOB_BOARD_PRO_CANDIDATE_PREFIX;
		if ( !empty($post_type) && $post_type == 'employer' ) {
			$prefix = WP_JOB_BOARD_PRO_EMPLOYER_PREFIX;
		}
		$notifications = get_post_meta($user_post_id, $prefix . 'notifications', true);;
        $notifications = !empty($notifications) ? $notifications : array();

        $new_notifications = array_merge( array($unique_id => $args), $notifications );
		update_post_meta($user_post_id, $prefix . 'notifications', $new_notifications);
	}

	public static function process_remove_notification() {
		$return = array();
		if (  !isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp-job-board-pro-remove-notify-nonce' )  ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Your nonce did not verify.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}

		$unique_id = !empty($_POST['unique_id']) ? $_POST['unique_id'] : '';

		$user_id = WP_Job_Board_Pro_User::get_user_id();
		if ( WP_Job_Board_Pro_User::is_employer() ) {
			$user_post_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
			$post_type = 'employer';
			$prefix = WP_JOB_BOARD_PRO_EMPLOYER_PREFIX;
		} elseif ( WP_Job_Board_Pro_User::is_candidate() ) {
			$user_post_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
			$post_type = 'candidate';
			$prefix = WP_JOB_BOARD_PRO_CANDIDATE_PREFIX;
		} else {
			$return = array( 'status' => false, 'msg' => esc_html__('You can not remove the notification', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}


		$notifications = self::get_notifications($user_post_id, $post_type);
		if ( !empty($notifications[$unique_id]) ) {
			unset($notifications[$unique_id]);
			update_post_meta($user_post_id, $prefix . 'notifications', $notifications);

			$return = array( 'status' => true, 'msg' => esc_html__('The notification was successfully removed', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		} else {
			$return = array( 'status' => false, 'msg' => esc_html__('The notification dosen\'t exist', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		
	}

	public static function get_notifications($user_post_id, $post_type = 'employer') {
		
		if ( empty($user_post_id) || empty($post_type) ) {
			return;
		}

		$prefix = WP_JOB_BOARD_PRO_CANDIDATE_PREFIX;
		if ( !empty($post_type) && $post_type == 'employer' ) {
			$prefix = WP_JOB_BOARD_PRO_EMPLOYER_PREFIX;
		}
		$notifications = get_post_meta($user_post_id, $prefix . 'notifications', true);;
        $notifications = !empty($notifications) ? $notifications : array();

        return $notifications;
	}

	public static function get_not_seen_notifications($user_post_id, $post_type = 'employer') {
		$notifications = self::get_notifications($user_post_id, $post_type);
		if ( empty($notifications) ) {
			return;
		}
		$return = [];
		foreach ( $notifications as $key => $notify ) {
			if ( isset($notify['viewed']) ) {
				$return[] = $notify;
			}
		}
        return $return;
	}
	
	public static function remove_notification($user_post_id, $post_type = 'employer', $unique_id = '') {
		$notifications = self::get_notifications($user_post_id, $post_type);
		if ( empty($notifications) ) {
			return true;
		}
		if ( !empty($notifications[$unique_id]) ) {
			unset($notifications[$unique_id]);
		} else {
			return false;
		}

		$prefix = WP_JOB_BOARD_PRO_CANDIDATE_PREFIX;
		if ( !empty($post_type) && $post_type == 'employer' ) {
			$prefix = WP_JOB_BOARD_PRO_EMPLOYER_PREFIX;
		}

		update_post_meta($user_post_id, $prefix . 'notifications', $notifications);

        return true;
	}
	
	public static function add_private_message_nnotify($message_id, $recipient, $user_id) {
		if ( WP_Job_Board_Pro_User::is_employer($recipient) ) {
			$post_type = 'employer';
			$user_post_id = WP_Job_Board_Pro_User::get_employer_by_user_id($recipient);
		} elseif ( WP_Job_Board_Pro_User::is_candidate($recipient) ) {
			$post_type = 'candidate';
			$user_post_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($recipient);
		}
		
		if ( $post_type == 'employer' || $post_type == 'candidate' ) {
			$notify_args = array(
				'post_type' => $post_type,
				'user_post_id' => $user_post_id,
	            'type' => 'new_private_message',
	            'user_id' => $user_id,
	            'message_id' => $message_id,
			);
			WP_Job_Board_Pro_User_Notification::add_notification($notify_args);
		}
	}

	public static function display_notify($args) {
		$type = !empty($args['type']) ? $args['type'] : '';
		switch ($type) {
			case 'email_apply':
				$job_id = !empty($args['job_id']) ? $args['job_id'] : '';
				$html = sprintf(__('A new application is submitted on your job <a href="%s">%s</a>', 'wp-job-board-pro'), get_permalink($job_id), get_the_title($job_id));
				break;
			case 'internal_apply':
				$job_id = !empty($args['job_id']) ? $args['job_id'] : '';
				$candidate_id = !empty($args['candidate_id']) ? $args['candidate_id'] : '';
				$html = sprintf(__('A new application is submitted on your job <a href="%s">%s</a> by <a href="%s">%s</a>.', 'wp-job-board-pro'), get_permalink($job_id), get_the_title($job_id), get_permalink($candidate_id), get_the_title($candidate_id) );
				break;
			case 'remove_apply':
				$employer_id = !empty($args['employer_id']) ? $args['employer_id'] : '';
				$job_id = !empty($args['job_id']) ? $args['job_id'] : '';

				$html = sprintf(__('The application is removed on your job <a href="%s">%s</a> by <a href="%s">%s</a>.', 'wp-job-board-pro'), get_permalink($job_id), get_the_title($job_id), get_permalink($employer_id), get_the_title($employer_id) );
				break;
			case 'create_meeting':
				$employer_id = !empty($args['employer_id']) ? $args['employer_id'] : '';
				$job_id = !empty($args['job_id']) ? $args['job_id'] : '';

				$html = sprintf(__('A new meeting is created on the job <a href="%s">%s</a> by <a href="%s">%s</a>.', 'wp-job-board-pro'), get_permalink($job_id), get_the_title($job_id), get_permalink($employer_id), get_the_title($employer_id) );
				break;
			case 'reschedule_meeting':
				$reschedule_user_id = !empty($args['reschedule_user_id']) ? $args['reschedule_user_id'] : '';
				$meeting_id = !empty($args['meeting_id']) ? $args['meeting_id'] : '';
				$application_id = WP_Job_Board_Pro_Meeting::get_post_meta($meeting_id, 'application_id');
				$job_id = WP_Job_Board_Pro_Applicant::get_post_meta($application_id, 'job_id');

				$html = sprintf(__('A meeting is re-schedule on the job <a href="%s">%s</a> by <a href="%s">%s</a>.', 'wp-job-board-pro'), get_permalink($job_id), get_the_title($job_id), get_permalink($employer_id), get_the_title($employer_id) );
				break;
			case 'remove_meeting':
				$application_id = !empty($args['application_id']) ? $args['application_id'] : '';
				$employer_id = !empty($args['employer_id']) ? $args['employer_id'] : '';
				$job_id = WP_Job_Board_Pro_Applicant::get_post_meta($application_id, 'job_id');

				$html = sprintf(__('A meeting is removed on the job <a href="%s">%s</a> by <a href="%s">%s</a>.', 'wp-job-board-pro'), get_permalink($job_id), get_the_title($job_id), get_permalink($employer_id), get_the_title($employer_id) );
				break;
			case 'cancel_meeting':
				$application_id = !empty($args['application_id']) ? $args['application_id'] : '';
				$candidate_id = !empty($args['candidate_id']) ? $args['candidate_id'] : '';
				$job_id = WP_Job_Board_Pro_Applicant::get_post_meta($application_id, 'job_id');

				$html = sprintf(__('A meeting is canceled on your job <a href="%s">%s</a> by <a href="%s">%s</a>.', 'wp-job-board-pro'), get_permalink($job_id), get_the_title($job_id), get_permalink($candidate_id), get_the_title($candidate_id) );
				break;
			case 'reject_applied':
				$employer_id = !empty($args['employer_id']) ? $args['employer_id'] : '';
				$job_id = !empty($args['job_id']) ? $args['job_id'] : '';

				$html = sprintf(__('The application is rejected on your job <a href="%s">%s</a> by <a href="%s">%s</a>.', 'wp-job-board-pro'), get_permalink($job_id), get_the_title($job_id), get_permalink($employer_id), get_the_title($employer_id) );
				break;
			case 'undo_reject_applied':
				$employer_id = !empty($args['employer_id']) ? $args['employer_id'] : '';
				$job_id = !empty($args['job_id']) ? $args['job_id'] : '';

				$html = sprintf(__('The application is undo rejected on your job <a href="%s">%s</a> by <a href="%s">%s</a>.', 'wp-job-board-pro'), get_permalink($job_id), get_the_title($job_id), get_permalink($employer_id), get_the_title($employer_id) );
				break;
			case 'approve_applied':
				$employer_id = !empty($args['employer_id']) ? $args['employer_id'] : '';
				$job_id = !empty($args['job_id']) ? $args['job_id'] : '';

				$html = sprintf(__('The application is approved on your job <a href="%s">%s</a> by <a href="%s">%s</a>.', 'wp-job-board-pro'), get_permalink($job_id), get_the_title($job_id), get_permalink($employer_id), get_the_title($employer_id) );
				break;
			case 'undo_approve_applied':
				$employer_id = !empty($args['employer_id']) ? $args['employer_id'] : '';
				$job_id = !empty($args['job_id']) ? $args['job_id'] : '';

				$html = sprintf(__('The application is undo approved on your job <a href="%s">%s</a> by <a href="%s">%s</a>.', 'wp-job-board-pro'), get_permalink($job_id), get_the_title($job_id), get_permalink($employer_id), get_the_title($employer_id) );
				break;
			case 'new_private_message':
				$user_id = !empty($args['user_id']) ? $args['user_id'] : '';
				if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
					$user_post_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
				} elseif ( WP_Job_Board_Pro_User::is_candidate($user_id) ) {
					$user_post_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
				}
				$message_id = !empty($args['message_id']) ? $args['message_id'] : '';
				if ( !empty($user_post_id) ) {
					$html = sprintf(__('A new private message from <a href="%s">%s</a>.', 'wp-job-board-pro'), get_permalink($user_post_id), get_the_title($user_post_id) );
				} else {
					$user = get_userdata( $user_id );
					if ( is_object($user) ) {
						$html = sprintf(__('A new private message from %s.', 'wp-job-board-pro'), $user->display_name );
					}
				}
				break;
			case 'invite_candidate_apply':
				$candidate_id = !empty($args['candidate_id']) ? $args['candidate_id'] : '';
				$employer_id = !empty($args['employer_id']) ? $args['employer_id'] : '';
				$job_ids = !empty($args['job_ids']) ? $args['job_ids'] : '';
				
				if ( !empty($job_ids) && count($job_ids) == 1 ) {
					$html = sprintf(__('You are invited to apply job <a href="%s">%s</a>.', 'wp-job-board-pro'), get_permalink($job_ids[0]), get_the_title($job_ids[0]) );
				} elseif( !empty($job_ids) ) {
					$jobs_html = '';
					$count = 1;
					foreach ($job_ids as $job_id) {
						$jobs_html .= '<a href="'.get_permalink($job_id).'">'.get_the_title($job_id).'</a>'.($count < count($job_ids) ? ', ' : '');
						$count++;
					}
					$html = sprintf(__('You are invited to apply jobs %s.', 'wp-job-board-pro'), $jobs_html );
				}
				break;
			default:
				$html = '';
				break;
		}

		return apply_filters( 'wp-job-board-pro-display-notify', $html, $args);
	}
	
}

WP_Job_Board_Pro_User_Notification::init();