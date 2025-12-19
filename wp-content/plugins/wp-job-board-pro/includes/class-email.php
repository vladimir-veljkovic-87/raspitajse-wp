<?php
/**
 * Email
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Email {
	
	public static $emails_vars;

	public static function init() {
		// Ajax endpoints.
		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_contact_form',  array(__CLASS__,'process_send_contact') );

		
		// compatible handlers.
		add_action( 'wp_ajax_wp_job_board_pro_ajax_contact_form',  array(__CLASS__,'process_send_contact') );
		add_action( 'wp_ajax_nopriv_wp_job_board_pro_ajax_contact_form',  array(__CLASS__,'process_send_contact') );
	}

	public static function wp_mail( $author_email, $subject, $content, $headers, $attachments = null) {
		if ( !preg_match( '%<html[>\s].*</html>%is', $content ) ) {
			$header = apply_filters( 'wp-job-board-pro-mail-html-header',
				'<!doctype html>
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset='.get_bloginfo( 'charset' ).'" />
			<title>' . esc_html( $subject ) . '</title>
			</head>
			<body>
			', $subject );

			$footer = apply_filters( 'wp-job-board-pro-mail-html-footer',
						'</body>
			</html>' );

			$content = $header . wpautop( $content ) . $footer;
		}
		
		return wp_mail( $author_email, $subject, $content, $headers, $attachments );
	}

	public static function process_send_contact() {
		$is_form_filled = ! empty( $_POST['email'] ) && ! empty( $_POST['subject'] ) && ! empty( $_POST['message'] ) && ! empty( $_POST['post_id'] );

		if ( WP_Job_Board_Pro_Recaptcha::is_recaptcha_enabled() ) {
			$is_recaptcha_valid = array_key_exists( 'g-recaptcha-response', $_POST ) ? WP_Job_Board_Pro_Recaptcha::is_recaptcha_valid( sanitize_text_field( $_POST['g-recaptcha-response'] ) ) : false;
			if ( !$is_recaptcha_valid ) {
				$is_form_filled = false;
			}
		}
		
		$post_type = get_post_type( $_POST['post_id'] );
		if ( $post_type == 'employer' ) {
			$author_email = get_post_meta( $_POST['post_id'], WP_JOB_BOARD_PRO_EMPLOYER_PREFIX.'email', true );
		} elseif ( $post_type == 'candidate' ) {
			$author_email = get_post_meta( $_POST['post_id'], WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'email', true );
		}
		
		if ($is_form_filled && !empty($author_email)) {
			$post = get_post($_POST['post_id']);
			if ($post->post_type == 'candidate' && !WP_Job_Board_Pro_Candidate::check_restrict_view_contact_info($post)) {
				$return = array(
					'status' => false,
					'msg' => esc_html__('You have no package.', 'wp-job-board-pro')
				);
				echo wp_json_encode($return);
				exit;
			}
			// Contact email check
			do_action('wp-job-board-pro-before-process-send-contact', $post_type, $_POST);
		
			// Sanitize input fields
			$email = sanitize_text_field($_POST['email']);
			$phone = sanitize_text_field($_POST['phone']);
			$t_subject = sanitize_text_field($_POST['subject']);
			$message = sanitize_textarea_field($_POST['message']);
		
			// Prepare email subject and content
			$subject = str_replace('{{subject}}', $t_subject, wp_job_board_pro_get_option('contact_form_notice_subject'));
			
			$content = wp_job_board_pro_get_option('contact_form_notice_content');
			// Replace placeholders with actual data
			$content = str_replace('{{subject}}', $t_subject, $content);
			$content = str_replace('{{website_url}}', home_url(), $content);
			$content = str_replace('{{website_name}}', get_bloginfo('name'), $content);
			$content = str_replace('{{email}}', $email, $content);
			$content = str_replace('{{phone}}', $phone, $content);
			$content = str_replace('{{message}}', $message, $content);
		
			// Additional job data placeholders
			$job_title = $job_url = $job_publish_date = $job_expiry_date = $location = $salary = $employer_name = $job_data = '';
		
			if (!empty($_POST['job_id'])) {
				$job_id = $_POST['job_id'];
				$job_title = get_the_title($job_id);
				$job_url = get_permalink($job_id);
		
				// Fetch additional meta data
				$job_publish_date = get_the_date('Y-m-d', $job_id); // Publish date from post date
				$job_expiry_date = get_post_meta($job_id, '_job_expiry_date', true);
				$location = get_post_meta($job_id, '_job_address', true);
				$salary = get_post_meta($job_id, '_job_salary', true);
		
				// Fetch employer name using employer ID from meta
				$employer_id = get_post_meta($job_id, '_job_employer_posted_by', true);
				$employer_name = get_the_author_meta('display_name', $employer_id);
			}
		
			// Set headers and send email
			$headers = sprintf("From: %s <%s>\r\nContent-type: text/html", $email, $email);
			
			$result = self::wp_mail($author_email, $subject, $content, $headers);
			if ($result) {
				$return = array('status' => true, 'msg' => esc_html__('Your message has been successfully sent.', 'wp-job-board-pro'));
		
				do_action('wp-job-board-pro-after-process-send-contact', $post_type, $_POST);
			} else {
				$return = array('status' => false, 'msg' => esc_html__('An error occurred when sending an email.', 'wp-job-board-pro'));
			}
		} else {
			$return = array('status' => false, 'msg' => esc_html__('Form has not been filled correctly.', 'wp-job-board-pro'));
		}
		
		echo wp_json_encode($return);
		exit;
		
	}

	public static function emails_vars() {
		self::$emails_vars = apply_filters( 'wp-job-board-pro-emails-vars', array(
			'admin_notice_add_new_listing' => array(
				'subject' => array( 'job_title' ),
				'content' => array( 'job_title', 'job_type', 'job_category', 'job_publish_date', 'job_expiry_date', 'job_featured', 'job_status', 'job_url', 'author', 'website_url', 'website_name' )
			),
			'admin_notice_updated_listing' => array(
				'subject' => array( 'job_title' ),
				'content' => array( 'job_title', 'job_type', 'job_category', 'job_publish_date', 'job_expiry_date', 'job_featured', 'job_status', 'job_url', 'author', 'website_url', 'website_name' )
			),
			'admin_notice_expiring_listing' => array(
				'subject' => array( 'job_title' ),
				'content' => array( 'job_title', 'job_type', 'job_category', 'job_publish_date', 'job_expiry_date', 'job_featured', 'job_status', 'job_url', 'website_url', 'website_name', 'job_admin_edit_url' )
			),
			'employer_notice_expiring_listing' => array(
				'subject' => array( 'job_title' ),
				'content' => array( 'job_title', 'job_type', 'job_category', 'job_publish_date', 'job_expiry_date', 'job_featured', 'job_status', 'job_url', 'website_url', 'website_name', 'dashboard_url', 'my_jobs' )
			),
			'email_apply_job_notice' => array(
				'subject' => array( 'job_title' ),
				'content' => array( 'job_title', 'job_url', 'fullname', 'email', 'phone', 'message', 'cv_file_url', 'website_name', 'website_url' )
			),
			'internal_apply_job_notice' => array(
				'subject' => array( 'job_title' ),
				'content' => array( 'job_title', 'job_url', 'candidate_name', 'email', 'phone', 'resume_url', 'cv_file_url', 'message', 'website_name', 'website_url' )
			),
			'applied_job_thanks_notice' => array(
				'subject' => array( 'job_title' ),
				'content' => array( 'job_title', 'job_url', 'candidate_name', 'website_name', 'website_url' )
			),
			'job_alert_notice' => array(
				'subject' => array( 'alert_title', 'location', 'job_title'),
				'content' => array(
				'job_data',				
				'alert_title', 'website_url', 'website_name', 'email_frequency_type', 'jobs_alert_url',  
				'newest_employer_name', 'newest_job_title', 'newest_job_url', 'newest_job_publish_date', 'newest_job_expiry_date', 'newest_job_apply_email', 'newest_location', 'newest_salary', 'newest_alert_title'  )
			),
			'candidate_alert_notice' => array(
				'subject' => array( 'alert_title', 'location', 'candidate_title' ),
				'content' => array( 
					'candidate_data',
					'alert_title', 'candidates_found', 'website_url', 'website_name', 'email_frequency_type',
					'best_candidate_title', 'best_candidate_url', 'best_candidate_job_title', 'best_candidate_qualification', 'best_candidate_experience_time', 'best_candidate_birth_date', 'best_location', 'best_alert_title' )
			),
			'contact_form_notice' => array(
				'subject' => array( 'subject' ),
				'content' => array( 'subject', 'message', 'email', 'phone', 'job_title', 'job_url', 'website_url', 'website_name' )
			),
			'reject_interview_notice' => array(
				'subject' => array( 'job_title' ),
				'content' => array( 'candidate_name', 'employer_name', 'job_title', 'job_url', 'website_url', 'website_name' )
			),
			'undo_reject_interview_notice' => array(
				'subject' => array( 'job_title' ),
				'content' => array( 'candidate_name', 'employer_name', 'job_title', 'job_url', 'website_url', 'website_name' )
			),
			'approve_interview_notice' => array(
				'subject' => array( 'job_title' ),
				'content' => array( 'candidate_name', 'employer_name', 'job_title', 'job_url', 'website_url', 'website_name' )
			),
			'undo_approve_interview_notice' => array(
				'subject' => array( 'job_title' ),
				'content' => array( 'candidate_name', 'employer_name', 'job_title', 'job_url', 'website_url', 'website_name' )
			),

			'user_register_auto_approve' => array(
				'subject' => array( 'user_name' ),
				'content' => array( 'user_name', 'user_email', 'login_url', 'website_url', 'website_name' )
			),
			'user_register_need_approve' => array(
				'subject' => array( 'user_name' ),
				'content' => array( 'user_name', 'user_email', 'approve_url', 'website_url', 'website_name' )
			),
			'user_register_approved' => array(
				'subject' => array( 'user_name' ),
				'content' => array( 'user_name', 'user_email', 'login_url', 'dashboard_url', 'website_url', 'website_name' )
			),
			'user_register_denied' => array(
				'subject' => array( 'user_name' ),
				'content' => array( 'user_name', 'user_email', 'website_url', 'website_name' )
			),
			'user_reset_password' => array(
				'subject' => array( 'user_name' ),
				'content' => array( 'user_name', 'user_email', 'new_password', 'website_url', 'website_name' )
			),

			'meeting_create' => array(
				'subject' => array( 'user_name', 'date', 'time', 'time_duration' ),
				'content' => array( 'employer_name', 'job_title', 'user_name', 'date', 'time', 'time_duration', 'message', 'zoom_meeting_url', 'website_url', 'website_name' )
			),

			'meeting_reschedule' => array(
				'subject' => array( 'user_name', 'date', 'time', 'time_duration' ),
				'content' => array( 'job_title', 'user_name', 'date', 'time', 'time_duration', 'message', 'website_url', 'website_name' )
			),

			'invite_candidate_notice' => array(
				'subject' => array( 'candidate_name' ),
				'content' => array( 'list_jobs', 'candidate_name' , 'employer_name', 'website_url', 'website_name' )
			),
		));
		return self::$emails_vars;
	}

	public static function display_email_vars($key, $type = 'subject') {
		self::emails_vars();
		$output = '';
		if ( !empty(self::$emails_vars[$key][$type]) ) {
			$i = 1;
			foreach (self::$emails_vars[$key][$type] as $value) {
				$output .= '{{'.$value.'}}'.($i < count(self::$emails_vars[$key][$type]) ? ', ' : '');
				$i++;
			}
		}
		return $output;
	}

	public static function render_email_vars($args, $key, $type = 'subject') {
		self::emails_vars();
		$output = wp_job_board_pro_get_option($key.'_'.$type);
		if ( empty($output) && $type == 'content' ) {
			$output = self::get_email_default_content($key);
		}
		if ( !empty(self::$emails_vars[$key][$type]) ) {
			$vars = self::$emails_vars[$key][$type];
			foreach ($vars as $var) {
				if ( strpos($output, '{{'.$var.'}}') !== false ) {
					if ( isset($args[$var]) ) {
						$value = $args[$var];
					} elseif ( is_callable( array('WP_Job_Board_Pro_Email', $var) ) ) {
						$value = call_user_func( array('WP_Job_Board_Pro_Email', $var), $args );
					} else {
						$value = apply_filters('wp-job-board-pro-render-email-var-'.$var, '', $args);
					}
					$output = str_replace('{{'.$var.'}}', $value, $output);
				}
			}
		}
		return apply_filters( 'wp-job-board-pro-render-emails-vars', $output, $args, $key, $type );
	}

	public static function get_email_default_content($key) {
		$return = '';
		if ( !empty($key) ) {
			$name = 'html-'.str_replace('_', '-', $key);
            if ( file_exists( WP_JOB_BOARD_PRO_PLUGIN_DIR . "includes/email-templates-default/{$name}.php" ) ) {
				ob_start();
	                load_template(WP_JOB_BOARD_PRO_PLUGIN_DIR . "includes/email-templates-default/{$name}.php", false);
	            $return = ob_get_clean();
			}
		}
		return trim($return);
	}

	public static function job_title($args) {
		$output = '';
		if ( isset($args['job']) && !empty($args['job']->post_title) ) {
			$output = $args['job']->post_title;
		}
		return $output;
	}

	public static function job_type($args) {
		$output = '';
		if ( isset($args['job']) && !empty($args['job']->ID) ) {
			$terms = get_the_terms( $args['job']->ID, 'job_listing_type' );
			if ( $terms ) {
				$k = count( $terms );
				foreach ($terms as $term) {
					$k -= 1;
					if ( $k == 0 ) {
						$output .= '<a class="type-job" href="'.get_term_link($term).'" >'.esc_html($term->name).'</a>';
					} else {
						$output .= '<a class="type-job" href="'.get_term_link($term).'" >'.esc_html($term->name).'</a>, ';
					}
		    	}
			}
		}
		return $output;
	}

	public static function job_category($args) {
		$output = '';
		if ( isset($args['job']) && !empty($args['job']->ID) ) {
			$terms = get_the_terms( $args['job']->ID, 'job_listing_category' );
			if ( $terms ) {
				$k = count( $terms );
				foreach ($terms as $term) {
					$k -= 1;
					if ( $k == 0 ) {
						$output .= '<a class="type-job" href="'.get_term_link($term).'" >'.esc_html($term->name).'</a>';
					} else {
						$output .= '<a class="type-job" href="'.get_term_link($term).'" >'.esc_html($term->name).'</a>, ';
					}
		    	}
			}
		}
		return $output;
	}

	public static function job_publish_date($args) {
		$output = '';
		if ( isset($args['job']) && !empty($args['job']->ID) ) {
			$output = get_the_date(get_option('date_format'), $args['job']->ID);
		}
		return $output;
	}

	public static function job_expiry_date($args) {
		$output = '';
		if ( isset($args['job']) && !empty($args['job']->ID) ) {
			$meta_obj = WP_Job_Board_Pro_Job_Listing_Meta::get_instance($args['job']->ID);

			$expiry_date = $meta_obj->get_post_meta( 'expiry_date' );
			if ( $expiry_date ) {
				$expiry_date = strtotime($expiry_date);
				$output = date_i18n(get_option('date_format'), $expiry_date);
			}
		}
		return $output;
	}

	public static function job_featured($args) {
		$output = '';
		if ( isset($args['job']) && !empty($args['job']->ID) ) {
			$meta_obj = WP_Job_Board_Pro_Job_Listing_Meta::get_instance($args['job']->ID);

			$featured = $meta_obj->get_post_meta( 'featured' );
			if ( $featured ) {
				$output = esc_html__('Yes', 'wp-job-board-pro');
			} else {
				$output = esc_html__('No', 'wp-job-board-pro');
			}
		}
		return $output;
	}

	public static function job_urgent($args) {
		$output = '';
		if ( isset($args['job']) && !empty($args['job']->ID) ) {
			$meta_obj = WP_Job_Board_Pro_Job_Listing_Meta::get_instance($args['job']->ID);

			$urgent = $meta_obj->get_post_meta( 'urgent' );
			if ( $urgent ) {
				$output = esc_html__('Yes', 'wp-job-board-pro');
			} else {
				$output = esc_html__('No', 'wp-job-board-pro');
			}
		}
		return $output;
	}

	public static function job_status($args) {
		$output = '';
		if ( isset($args['job']) && !empty($args['job']->post_status) ) {
			$post_status = get_post_status_object( $args['job']->post_status );
			if ( !empty($post_status->label) ) {
				$output = $post_status->label;
			} else {
				$output = $post_status->post_status;
			}
		}
		return $output;
	}

	public static function job_url($args) {
		$output = '';
		if ( !empty($args['job']) ) {
			$output = get_permalink($args['job']);
		}
		return $output;
	}

	public static function website_url($args) {
		$output = home_url();
		
		return $output;
	}

	public static function website_name($args) {
		$output = get_bloginfo( 'name' );
		
		return $output;
	}

	public static function dashboard_url($args) {
		$output = '';
		$dashboard_page_id = wp_job_board_pro_get_option('user_dashboard_page_id');
		$output = get_permalink($dashboard_page_id);
		return $output;
	}

	public static function my_jobs($args) {
		$output = '';
		$my_jobs_page_id = wp_job_board_pro_get_option('my_jobs_page_id');
		$output = get_permalink($my_jobs_page_id);
		return $output;
	}

	public static function job_admin_edit_url($args) {
		$output = '';
		if ( !empty($args['job']) ) {
			$output = admin_url( sprintf( 'post.php?post=%d&amp;action=edit', $args['job']->ID ) );
		}
		return $output;
	}

	public static function author($job) {
		$output = '';
		if ( !empty($args['job']) && !empty($args['job']->ID) ) {
			$author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($args['job']->ID);
			if ( WP_Job_Board_Pro_User::is_employer($author_id) ) {
				$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);
				$output = get_the_title($employer_id);
			} else {
				$output = get_the_author_meta( 'display_name', $author_id );
			}
		}
		return $output;
	}

	public static function candidate_name($job) {
		$output = '';
		if ( isset($args['candidate']) && !empty($args['candidate']->post_title) ) {
			$output = $args['candidate']->post_title;
		}
		return $output;
	}

	public static function employer_name($job) {
		$output = '';
		if ( isset($args['employer']) && !empty($args['employer']->post_title) ) {
			$output = $args['employer']->post_title;
		}
		return $output;
	}

	public static function login_url($args) {
		$output = '';
		$login_page_id = wp_job_board_pro_get_option('login_register_page_id');
		$output = get_permalink($login_page_id);
		return $output;
	}

	public static function user_name($args) {
		$output = '';
		if ( isset($args['user_obj']) && !empty($args['user_obj']->data->display_name) ) {
			$output = $args['user_obj']->data->display_name;
		}
		return $output;
	}

	public static function user_email($args) {
		$output = '';
		if ( isset($args['user_obj']) && !empty($args['user_obj']->data->user_email) ) {
			$output = $args['user_obj']->data->user_email;
		}
		return $output;
	}

	public static function approve_url($args) {
		$output = '';
		if ( isset($args['user_obj']) && !empty($args['user_obj']->ID) ) {
			$approve_user_page_id = wp_job_board_pro_get_option('approve_user_page_id');
			$admin_url = get_permalink($approve_user_page_id);

			$user_id = $args['user_obj']->ID;
            $code = get_user_meta($user_id, 'account_approve_key', true);
			$output = add_query_arg(array('action' => 'wp_job_board_pro_approve_user', 'user_id' => $user_id, 'approve-key' => $code), $admin_url);
		}
		return $output;
	}
}

WP_Job_Board_Pro_Email::init();