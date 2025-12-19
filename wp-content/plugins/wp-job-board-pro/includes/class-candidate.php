<?php
/**
 * Candidate
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Candidate {
	
	public static function init() {

		// Ajax endpoints.
		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_apply_email',  array(__CLASS__,'process_apply_email') );

		// apply job internal
		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_apply_internal',  array(__CLASS__,'process_apply_internal') );

		// removed job internal
		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_remove_applied',  array(__CLASS__,'process_remove_applied') );

		// add_job_shortlist
		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_add_job_shortlist',  array(__CLASS__,'process_add_job_shortlist') );

		// remove job shortlist
		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_remove_job_shortlist',  array(__CLASS__,'process_remove_job_shortlist') );

		// wp_job_board_pro_ajax_follow_employer
		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_follow_employer',  array(__CLASS__,'process_follow_employer') );

		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_unfollow_employer',  array(__CLASS__,'process_unfollow_employer') );

		// download cv
		add_action('wjbp_ajax_wp_job_board_pro_ajax_download_file', array( __CLASS__, 'process_download_file' ) );

		// download cv
		add_action('wjbp_ajax_wp_job_board_pro_ajax_download_cv', array( __CLASS__, 'process_download_cv' ) );
		
		// download resume cv
		add_action('wjbp_ajax_wp_job_board_pro_ajax_download_resume_cv', array( __CLASS__, 'process_download_resume_cv' ) );

		// loop
		add_action( 'wp_job_board_pro_before_candidate_archive', array( __CLASS__, 'display_candidates_results_filters' ), 5 );
		add_action( 'wp_job_board_pro_before_candidate_archive', array( __CLASS__, 'display_candidates_count_results' ), 10 );

		add_action( 'wp_job_board_pro_before_candidate_archive', array( __CLASS__, 'display_candidates_alert_orderby_start' ), 15 );
		add_action( 'wp_job_board_pro_before_candidate_archive', array( __CLASS__, 'display_candidates_alert_form' ), 20 );
		add_action( 'wp_job_board_pro_before_candidate_archive', array( __CLASS__, 'display_candidates_orderby' ), 25 );
		add_action( 'wp_job_board_pro_before_candidate_archive', array( __CLASS__, 'display_candidates_alert_orderby_end' ), 100 );

		// restrict
		add_filter( 'wp-job-board-pro-candidate-query-args', array( __CLASS__, 'candidate_restrict_listing_query_args'), 100, 2 );
		add_filter( 'wp-job-board-pro-candidate-filter-query', array( __CLASS__, 'candidate_restrict_listing_query'), 100, 2 );

		add_action( 'wp_job_board_pro_after_candidate_archive', array( __CLASS__, 'restrict_candidate_listing_information' ), 10 );

		add_action( 'template_redirect', array( __CLASS__, 'track_job_view' ), 20 );
	}
	
	public static function track_job_view() {
	    if ( ! is_singular( 'candidate' ) ) {
	        return;
	    }

	    global $post;

	    // views count
	    $viewed_count = intval(get_post_meta($post->ID, '_viewed_count', true));
	    $viewed_count++;
	    update_post_meta($post->ID, '_viewed_count', $viewed_count);

	    // view days
	    $today = date('Y-m-d', time());
	    $views_by_date = get_post_meta($post->ID, '_views_by_date', true);

	    if( $views_by_date != '' || is_array($views_by_date) ) {
	        if (!isset($views_by_date[$today])) {
	            if ( count($views_by_date) > 60 ) {
	                array_shift($views_by_date);
	            }
	            $views_by_date[$today] = 1;
	        } else {
	            $views_by_date[$today] = intval($views_by_date[$today]) + 1;
	        }
	    } else {
	        $views_by_date = array();
	        $views_by_date[$today] = 1;
	    }
	    update_post_meta($post->ID, '_views_by_date', $views_by_date);
	    update_post_meta($post->ID, '_recently_viewed', $today);
	}

	public static function send_admin_expiring_notice() {
		global $wpdb;

		if ( !wp_job_board_pro_get_option('admin_notice_expiring_candidate') ) {
			return;
		}
		$days_notice = wp_job_board_pro_get_option('admin_notice_expiring_candidate_days');

		$candidate_ids = self::get_expiring_candidates($days_notice);

		if ( $candidate_ids ) {
			foreach ( $candidate_ids as $candidate_id ) {
				// send email here.
				$candidate = get_post($candidate_id);
				$email_from = get_option( 'admin_email', false );
				
				$headers = sprintf( "From: %s <%s>\r\n Content-type: text/html", get_bloginfo('name'), $email_from );
				$email_to = get_option( 'admin_email', false );
				$subject = WP_Job_Board_Pro_Email::render_email_vars(array('candidate' => $candidate), 'admin_notice_expiring_candidate', 'subject');
				$content = WP_Job_Board_Pro_Email::render_email_vars(array('candidate' => $candidate), 'admin_notice_expiring_candidate', 'content');
				
				WP_Job_Board_Pro_Email::wp_mail( $email_to, $subject, $content, $headers );
			}
		}
	}

	public static function send_candidate_expiring_notice() {
		global $wpdb;

		if ( !wp_job_board_pro_get_option('candidate_notice_expiring_candidate') ) {
			return;
		}
		$days_notice = wp_job_board_pro_get_option('candidate_notice_expiring_candidate_days');

		$candidate_ids = self::get_expiring_candidates($days_notice);

		if ( $candidate_ids ) {
			foreach ( $candidate_ids as $candidate_id ) {
				// send email here.
				$candidate = get_post($candidate_id);
				$email_from = get_option( 'admin_email', false );
				
				$headers = sprintf( "From: %s <%s>\r\n Content-type: text/html", get_bloginfo('name'), $email_from );
				$email_to = get_the_author_meta( 'user_email', $candidate->post_author );
				$subject = WP_Job_Board_Pro_Email::render_email_vars(array('candidate' => $candidate), 'candidate_notice_expiring_listing', 'subject');
				$content = WP_Job_Board_Pro_Email::render_email_vars(array('candidate' => $candidate), 'candidate_notice_expiring_listing', 'content');
				
				WP_Job_Board_Pro_Email::wp_mail( $email_to, $subject, $content, $headers );
				
			}
		}
	}

	public static function get_expiring_candidates($days_notice) {
		$prefix = WP_JOB_BOARD_PRO_CANDIDATE_PREFIX;

		$notice_before_ts = current_time( 'timestamp' ) + ( DAY_IN_SECONDS * $days_notice );
		$candidate_ids          = $wpdb->get_col( $wpdb->prepare(
			"
			SELECT postmeta.post_id FROM {$wpdb->postmeta} as postmeta
			LEFT JOIN {$wpdb->posts} as posts ON postmeta.post_id = posts.ID
			WHERE postmeta.meta_key = %s
			AND postmeta.meta_value = %s
			AND posts.post_status = 'publish'
			AND posts.post_type = 'candidate'
			",
			$prefix.'expiry_date',
			date( 'Y-m-d', $notice_before_ts )
		) );

		return $candidate_ids;
	}

	public static function check_for_expired_candidates() {
		global $wpdb;

		$prefix = WP_JOB_BOARD_PRO_CANDIDATE_PREFIX;
		
		// Change status to expired.
		$candidate_ids = $wpdb->get_col(
			$wpdb->prepare( "
				SELECT postmeta.post_id FROM {$wpdb->postmeta} as postmeta
				LEFT JOIN {$wpdb->posts} as posts ON postmeta.post_id = posts.ID
				WHERE postmeta.meta_key = %s
				AND postmeta.meta_value > 0
				AND postmeta.meta_value < %s
				AND posts.post_status = 'publish'
				AND posts.post_type = 'candidate'",
				$prefix.'expiry_date',
				date( 'Y-m-d', current_time( 'timestamp' ) )
			)
		);

		if ( $candidate_ids ) {
			foreach ( $candidate_ids as $job_id ) {
				$job_data                = array();
				$job_data['ID']          = $job_id;
				$job_data['post_status'] = 'expired';
				wp_update_post( $job_data );
			}
		}

		// Delete old expired jobs.
		if ( apply_filters( 'wp_job_board_pro_delete_expired_candidates', false ) ) {
			$candidate_ids = $wpdb->get_col(
				$wpdb->prepare( "
					SELECT posts.ID FROM {$wpdb->posts} as posts
					WHERE posts.post_type = 'candidate'
					AND posts.post_modified < %s
					AND posts.post_status = 'expired'",
					date( 'Y-m-d', strtotime( '-' . apply_filters( 'wp_job_board_pro_delete_expired_candidates_days', 30 ) . ' days', current_time( 'timestamp' ) ) )
				)
			);

			if ( $candidate_ids ) {
				foreach ( $candidate_ids as $job_id ) {
					wp_trash_post( $job_id );
				}
			}
		}
	}

	public static function is_candidate_status_changing( $from_status, $to_status ) {
		return isset( $_POST['post_status'] ) && isset( $_POST['original_post_status'] ) && $_POST['original_post_status'] !== $_POST['post_status'] && ( null === $from_status || $from_status === $_POST['original_post_status'] ) && $to_status === $_POST['post_status'];
	}

	public static function calculate_candidate_expiry( $candidate_id ) {
		$duration = absint( wp_job_board_pro_get_option( 'resume_duration' ) );
		$duration = apply_filters( 'wp-job-board-pro-calculate-candidate-expiry', $duration, $candidate_id);

		if ( $duration ) {
			return date( 'Y-m-d', strtotime( "+{$duration} days", current_time( 'timestamp' ) ) );
		}

		return '';
	}

	public static function get_post_meta($post_id, $key, $single = true) {
		return get_post_meta($post_id, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.$key, $single);
	}

	public static function update_post_meta($post_id, $key, $data) {
		return update_post_meta($post_id, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.$key, $data);
	}

	public static function get_salary_html( $post_id = null, $html = true ) {
		$min_salary = self::get_min_salary_html($post_id, $html);
		$price_html = '';
		if ( $min_salary ) {
			$price_html = $min_salary;
		}
		if ( $price_html ) {
			$salary_type = self::get_post_meta( $post_id, 'salary_type' );

			$salary_type_html = '';
			switch ($salary_type) {
				case 'yearly':
					$salary_type_html = esc_html__(' per year', 'wp-job-board-pro');
					break;
				case 'monthly':
					$salary_type_html = esc_html__(' per month', 'wp-job-board-pro');
					break;
				case 'weekly':
					$salary_type_html = esc_html__(' per week', 'wp-job-board-pro');
					break;
				case 'daily':
					$salary_type_html = esc_html__(' per day', 'wp-job-board-pro');
					break;
				case 'hourly':
					$salary_type_html = esc_html__(' per hour', 'wp-job-board-pro');
					break;
			}
			$salary_type_html = apply_filters( 'wp-job-board-pro-get-salary-type-html', $salary_type_html, $salary_type, $post_id );
			$price_html = $price_html.$salary_type_html;
		}
		return apply_filters( 'wp-job-board-pro-get-salary-html', $price_html, $post_id );
	}

	public static function get_min_salary_html( $post_id = null, $html = true  ) {
		if ( null == $post_id ) {
			$post_id = get_the_ID();
		}
		$meta_obj = WP_Job_Board_Pro_Candidate_Meta::get_instance($post_id);
		if ( !$meta_obj->check_post_meta_exist('salary') ) {
			return false;
		}
		$price = $meta_obj->get_post_meta( 'salary' );

		if ( $price == '0' ) {
			$price = 0;
		} elseif ( empty( $price ) || ! is_numeric( $price ) ) {
			return false;
		}

		if ( !$html ) {
			$price = WP_Job_Board_Pro_Price::format_price_without_html( $price );
		} else {
			$price = WP_Job_Board_Pro_Price::format_price( $price );
		}

		return apply_filters( 'wp-job-board-pro-get-candidate-min-salary-html', $price, $post_id );
	}
	
	public static function is_featured( $post_id = null ) {
		if ( null == $post_id ) {
			$post_id = get_the_ID();
		}
		$featured = self::get_post_meta( $post_id, 'featured' );
		$return = $featured ? true : false;
		return apply_filters( 'wp-job-board-pro-job-listing-is-featured', $return, $post_id );
	}

	public static function is_urgent( $post_id = null ) {
		if ( null == $post_id ) {
			$post_id = get_the_ID();
		}
		$urgent = self::get_post_meta( $post_id, 'urgent' );
		$return = $urgent ? true : false;
		return apply_filters( 'wp-job-board-pro-job-listing-is-urgent', $return, $post_id );
	}

	public static function is_filled( $post_id = null ) {
		if ( null == $post_id ) {
			$post_id = get_the_ID();
		}
		$filled = self::get_post_meta( $post_id, 'filled' );
		$return = $filled ? true : false;
		return apply_filters( 'wp-job-board-pro-job-listing-is-filled', $return, $post_id );
	}
	
	public static function display_shortlist_btn( $post_id = null, $echo = true ) {
		if ( null == $post_id ) {
			$post_id = get_the_ID();
		}

		if ( WP_Job_Board_Pro_Employer::check_added_shortlist($post_id) ) {
			$classes = 'btn btn-block btn-added-candidate-shortlist';
			$nonce = wp_create_nonce( 'wp-job-board-pro-remove-candidate-shortlist-nonce' );
			$text = esc_html__('Shortlisted', 'wp-job-board-pro');
		} else {
			$classes = 'btn btn-block btn-add-candidate-shortlist';
			$nonce = wp_create_nonce( 'wp-job-board-pro-add-candidate-shortlist-nonce' );
			$text = esc_html__('Shortlist', 'wp-job-board-pro');
		}
		ob_start();
		?>
		<a href="javascript:void(0);" class="<?php echo esc_attr($classes); ?>" data-candidate_id="<?php echo esc_attr($post_id); ?>" data-nonce="<?php echo esc_attr($nonce); ?>"><?php echo esc_html($text); ?></a>
		<?php
		$html = ob_get_clean();

		$return = apply_filters('wp-job-board-pro-candidate-display-shortlist-btn', $html, $post_id);
		if ( $echo ) {
			echo trim($return );
		} else {
			return $return ;
		}
	}
	
	public static function display_shortlist_link( $post_id = null, $echo = true ) {
		if ( null == $post_id ) {
			$post_id = get_the_ID();
		}

		if ( WP_Job_Board_Pro_Employer::check_added_shortlist($post_id) ) {
			$classes = 'btn-added-candidate-shortlist';
			$nonce = wp_create_nonce( 'wp-job-board-pro-remove-candidate-shortlist-nonce' );
			$text = esc_html__('Shortlisted', 'wp-job-board-pro');
		} else {
			$classes = 'btn-add-candidate-shortlist';
			$nonce = wp_create_nonce( 'wp-job-board-pro-add-candidate-shortlist-nonce' );
			$text = esc_html__('Shortlist', 'wp-job-board-pro');
		}
		ob_start();
		?>
		<a href="javascript:void(0);" class="<?php echo esc_attr($classes); ?>" data-candidate_id="<?php echo esc_attr($post_id); ?>" data-nonce="<?php echo esc_attr($nonce); ?>"><?php echo esc_html($text); ?></a>
		<?php
		$html = ob_get_clean();

		$return = apply_filters('wp-job-board-pro-candidate-display-shortlist-link', $html, $post_id);
		if ( $echo ) {
			echo trim($return );
		} else {
			return $return ;
		}
	}

	public static function display_download_cv_btn( $post_id = null, $classes = 'btn btn-download-cv', $echo = true ) {
		if ( null == $post_id ) {
			$post_id = get_the_ID();
		}
		$download_base_url = WP_Job_Board_Pro_Ajax::get_endpoint('wp_job_board_pro_ajax_download_resume_cv');
		$download_url = add_query_arg(array('post_id' => $post_id), $download_base_url);

		$check_can_download = true;
		if ( !is_user_logged_in() ) {
			$check_can_download = false;
		} else {
			$user = wp_get_current_user();
			$user_id = WP_Job_Board_Pro_User::get_user_id();
			if ( !WP_Job_Board_Pro_User::is_employer($user_id) && !in_array('administrator', $user->roles) ) {
				$check_can_download = false;
				
				if( WP_Job_Board_Pro_User::is_candidate($user_id) ) {
					$candidate_post_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
					if ( $post_id == $candidate_post_id ) {
						$check_can_download = true;
					}
				}
			}
		}
		$msg = '';
		$additional_class = $classes;
		if ( !$check_can_download ) {
			$additional_class .= ' cannot-download-cv-btn ';
			$msg = esc_html__('Please login as "Employer" to download CV.', 'wp-job-board-pro');
		}

		ob_start();
		?>
		<a href="<?php echo esc_url($download_url); ?>" class="<?php echo esc_attr($additional_class); ?>" data-msg="<?php echo esc_attr($msg); ?>"><?php esc_html_e('Download CV', 'wp-job-board-pro'); ?></a>
		<?php
		$html = ob_get_clean();

		$return = apply_filters('wp-job-board-pro-candidate-display-download-cv-btn', $html, $post_id, $classes);
		if ( $echo ) {
			echo trim($return );
		} else {
			return $return ;
		}
	}

	public static function display_invite_btn( $post_id = null, $classes = 'btn btn-invite-candidate btn-theme', $echo = true ) {
		if ( null == $post_id ) {
			$post_id = get_the_ID();
		}

		$check_can_invite = true;
		if ( !is_user_logged_in() ) {
			$check_can_invite = false;
		} else {
			$user = wp_get_current_user();
			if ( !WP_Job_Board_Pro_User::is_employer() && !in_array('administrator', $user->roles) ) {
				$check_can_invite = false;
			}
		}
		$invite_url = '#invite-candidate-form-wrapper-'.$post_id;
		$msg = '';
		$additional_class = $classes;
		if ( !$check_can_invite ) {
			$additional_class .= ' cannot-download-cv-btn ';
			$msg = esc_html__('Please login as "Employer" to download CV.', 'wp-job-board-pro');
			$invite_url = 'javascript:void(0);';
		}
		$invite_text = apply_filters('wp-job-board-pro-candidate-display-invite-candidate-text', esc_html__('Invite', 'wp-job-board-pro') );



		ob_start();
		?>
		<a href="<?php echo esc_attr($invite_url); ?>" class="<?php echo esc_attr($additional_class); ?>" data-msg="<?php echo esc_attr($msg); ?>"><?php echo $invite_text; ?></a>
		<?php
		if ( $check_can_invite ) {
			echo WP_Job_Board_Pro_Template_Loader::get_template_part('single-candidate/invite-candidate-form', array('candidate_id' => $post_id));
		}
		$html = ob_get_clean();


		$return = apply_filters('wp-job-board-pro-candidate-display-invite-candidate-btn', $html, $post_id, $classes);
		if ( $echo ) {
			echo trim($return );
		} else {
			return $return ;
		}
	}
	
	public static function process_apply_email() {
		$return = array();
		if (  !isset( $_POST['wp-job-board-pro-apply-email-nonce'] ) || ! wp_verify_nonce( $_POST['wp-job-board-pro-apply-email-nonce'], 'wp-job-board-pro-apply-email' )  ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Your nonce did not verify.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		if ( WP_Job_Board_Pro_Recaptcha::is_recaptcha_enabled() ) {
			$is_recaptcha_valid = array_key_exists( 'g-recaptcha-response', $_POST ) ? WP_Job_Board_Pro_Recaptcha::is_recaptcha_valid( sanitize_text_field( $_POST['g-recaptcha-response'] ) ) : false;
			if ( !$is_recaptcha_valid ) {
				$return = array( 'status' => false, 'msg' => esc_html__('Your recaptcha did not verify.', 'wp-job-board-pro') );
			   	echo wp_json_encode($return);
			   	exit;
			}
		}

		$fullname = !empty($_POST['fullname']) ? $_POST['fullname'] : '';
		$email = !empty($_POST['email']) ? $_POST['email'] : '';
		$phone = !empty($_POST['phone']) ? $_POST['phone'] : '';
		$message = !empty($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
		$job_id = !empty($_POST['job_id']) ? $_POST['job_id'] : '';

		if ( empty($fullname) || empty($email) || empty($message) || empty($job_id) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Form has been not filled correctly.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$post = get_post($job_id);
		if ( !$post || empty($post->ID) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Job doesn\'t exist', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		
		$filled = WP_Job_Board_Pro_Job_Listing::get_post_meta($post->ID, 'filled', true);
		if ( $filled ) {
			$return = array( 'status' => false, 'msg' => esc_html__('This job is filled and no longer accepting applications.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		
		do_action('wp-job-board-pro-process-apply-email', $_POST);

		// cv file
        $cv_file_url = array();
        $files_path = array();
        if ( !empty($_FILES['cv_file']) && !empty($_FILES['cv_file']['name']) ) {

			$files = $_FILES['cv_file'];
	    	if ( is_array($files['name']) ) {
	    		$has_file = false;
	    		$return = array();
			    foreach ($files['name'] as $key => $value) {            
		            if ($files['name'][$key]) {
		            	$has_file = true;
		                $file = array( 
		                    'name' => $files['name'][$key],
		                    'type' => $files['type'][$key], 
		                    'tmp_name' => $files['tmp_name'][$key], 
		                    'error' => $files['error'][$key],
		                    'size' => $files['size'][$key]
		                ); 
		                $_FILES = array( 'cv_file' => $file ); 
		                foreach ($_FILES as $file => $array) {              
		                    $file_data = WP_Job_Board_Pro_Image::upload_cv_file($_FILES['cv_file']);
		                    if ( $file_data && !empty($file_data->url) ) {
				            	$attach_id = WP_Job_Board_Pro_Image::create_attachment( $file_data->url, 0 );
				            	
				            	if ( !empty($attach_id) ) {
					            	$download_url = WP_Job_Board_Pro_Ajax::get_endpoint('wp_job_board_pro_ajax_download_cv');
					            	$download_key = base64_encode('download-file-'.$attach_id);
					        		$cv_file_url[] = add_query_arg(array('file_id' => $attach_id, 'download_key' => $download_key), $download_url);

					        		$files_path[] = get_attached_file($attach_id);
					        	}
				            }
		                }
		            } 
		        }

		        if ( $has_file && empty($cv_file_url) ) {
					$return = array( 'status' => false, 'msg' => esc_html__('Can not upload file.', 'wp-job-board-pro') );
				   	echo wp_json_encode($return);
				   	exit;
				}
	        } else {
	        	$file_data = WP_Job_Board_Pro_Image::upload_cv_file($_FILES['cv_file']);
	            if ( $file_data && !empty($file_data->url) ) {
	            	$attach_id = WP_Job_Board_Pro_Image::create_attachment( $file_data->url, 0 );
	            	
	            	if ( !empty($attach_id) ) {
		            	$download_url = WP_Job_Board_Pro_Ajax::get_endpoint('wp_job_board_pro_ajax_download_cv');
		            	$download_key = base64_encode('download-file-'.$attach_id);
		        		$cv_file_url[] = add_query_arg(array('file_id' => $attach_id, 'download_key' => $download_key), $download_url);

		        		$files_path[] = get_attached_file($attach_id);
		        	}
	            }

	            if ( empty($cv_file_url) ) {
					$return = array( 'status' => false, 'msg' => esc_html__('Can not upload file.', 'wp-job-board-pro') );
				   	echo wp_json_encode($return);
				   	exit;
				}
	        }
        }

        if ( empty($cv_file_url) ) {
        	$apply_cv_id = !empty($_POST['apply_cv_id']) ? $_POST['apply_cv_id'] : '';
        	if ( !empty($apply_cv_id) ) {
        		if ( is_array($apply_cv_id) ) {
        			foreach ($apply_cv_id as $attach_id) {
        				$download_url = WP_Job_Board_Pro_Ajax::get_endpoint('wp_job_board_pro_ajax_download_cv');
			        	$download_key = base64_encode('download-file-'.$attach_id);
			    		$cv_file_url[] = add_query_arg(array('file_id' => $attach_id, 'download_key' => $download_key), $download_url);

			    		$files_path[] = get_attached_file($attach_id);
        			}
        		} else {
		        	$download_url = WP_Job_Board_Pro_Ajax::get_endpoint('wp_job_board_pro_ajax_download_cv');
		        	$download_key = base64_encode('download-file-'.$apply_cv_id);
		    		$cv_file_url[] = add_query_arg(array('file_id' => $apply_cv_id, 'download_key' => $download_key), $download_url);

		    		$files_path[] = get_attached_file($apply_cv_id);
		    	}
	    	}
        }

        $email_subject = WP_Job_Board_Pro_Email::render_email_vars( array('job_title' => $post->post_title), 'email_apply_job_notice', 'subject');
        $email_content_args = array(
        	'job' => $post,
        	'job_title' => $post->post_title,
        	'message' => $message,
        	'fullname' => $fullname,
        	'email' => $email,
        	'phone' => $phone,
        	'cv_file_url' => $cv_file_url ? implode(', ', $cv_file_url) : '',
        );
        $email_content_args = apply_filters('wp-job-board-pro-email-content-args-email-apply', $email_content_args);

        $email_content = WP_Job_Board_Pro_Email::render_email_vars( $email_content_args, 'email_apply_job_notice', 'content');
		
        $headers = sprintf( "From: %s <%s>\r\n Content-type: text/html", $fullname, $email );
        
        $author_email = get_post_meta( $post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'apply_email', true);
		if ( empty($author_email) ) {
			$author_email = get_post_meta( $post->ID, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'email', true);
		}
		if ( empty($author_email) ) {
			$author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($post->ID);
			$author_email = get_the_author_meta( 'user_email', $author_id );
		}

		$result = WP_Job_Board_Pro_Email::wp_mail( $author_email, $email_subject, $email_content, $headers, $files_path );
		if ( $result ) {
			// thanks email
			$email_subject = WP_Job_Board_Pro_Email::render_email_vars( array('job_title' => $post->post_title), 'applied_job_thanks_notice', 'subject');
			$email_content_args = array(
	        	'job' => $post,
	        	'job_title' => $post->post_title,
	        	'candidate_name' => $fullname,
	        );
	        $email_content_args = apply_filters('wp-job-board-pro-email-content-args-applied-thanks', $email_content_args);
	        $email_content = WP_Job_Board_Pro_Email::render_email_vars( $email_content_args, 'applied_job_thanks_notice', 'content');
	        $headers = sprintf( "From: %s <%s>\r\n Content-type: text/html", get_bloginfo('name'), get_option( 'admin_email', false ));
			WP_Job_Board_Pro_Email::wp_mail( $email, $email_subject, $email_content, $headers );

			// notification
			$author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($post->ID);
			$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);
			$notify_args = array(
				'post_type' => 'employer',
				'user_post_id' => $employer_id,
	            'type' => 'email_apply',
	            'job_id' => $job_id,
			);
			WP_Job_Board_Pro_User_Notification::add_notification($notify_args);

			$return = array( 'status' => true, 'msg' => esc_html__('You have successfully applied to the job', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		} else {
			$return = array( 'status' => false, 'msg' => esc_html__('Error accord when applying for the job', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
	}

	public static function process_apply_internal() {
		$return = array();
		if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp-job-board-pro-apply-internal-nonce' )  ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Your nonce did not verify.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		if ( WP_Job_Board_Pro_Recaptcha::is_recaptcha_enabled() ) {
			$is_recaptcha_valid = array_key_exists( 'g-recaptcha-response', $_POST ) ? WP_Job_Board_Pro_Recaptcha::is_recaptcha_valid( sanitize_text_field( $_POST['g-recaptcha-response'] ) ) : false;
			if ( !$is_recaptcha_valid ) {
				$return = array( 'status' => false, 'msg' => esc_html__('Your recaptcha did not verify.', 'wp-job-board-pro') );
			   	echo wp_json_encode($return);
			   	exit;
			}
		}

		if ( !is_user_logged_in() || !WP_Job_Board_Pro_User::is_candidate() ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Please login as "Candidate" to apply.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$job_id = !empty($_POST['job_id']) ? $_POST['job_id'] : '';
		$job = get_post($job_id);

		if ( !$job || empty($job->ID) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Job doesn\'t exist', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$filled = WP_Job_Board_Pro_Job_Listing::get_post_meta($job->ID, 'filled', true);
		if ( $filled ) {
			$return = array( 'status' => false, 'msg' => esc_html__('This job is filled and no longer accepting applications.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}

		$user_id = WP_Job_Board_Pro_User::get_user_id();

		$check_applied = WP_Job_Board_Pro_Candidate::check_applied($user_id, $job_id);
		if ( $check_applied ) {
			$return = array(
				'status' => false,
				'msg' => __('You have applied this job.', 'wp-job-board-pro')
			);
		   	echo wp_json_encode($return);
		   	exit;
		}
		$free_apply = self::check_candidate_can_apply();
		if ( !$free_apply ) {
			$candidate_package_page_id = wp_job_board_pro_get_option('candidate_package_page_id', true);
			$package_page_url = $candidate_package_page_id ? get_permalink($candidate_package_page_id) : home_url('/');
			$return = array(
				'status' => false,
				'msg' => sprintf(__('You have no package. <a href="%s" class="text-theme">Click here</a> to subscribe a package.', 'wp-job-board-pro'), $package_page_url)
			);
		   	echo wp_json_encode($return);
		   	exit;
		}

		
		$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);

		// apply_job_with_percent_resume
		$min_percent_resume = wp_job_board_pro_get_option('apply_job_with_percent_resume', 70);
		if ( !empty($min_percent_resume) && $min_percent_resume > 0 ) {
			$profile_percents = WP_Job_Board_Pro_User::compute_profile_percent($candidate_id);
			$percent = isset($profile_percents['percent']) ? $profile_percents['percent'] : 0;
			$profile_percent = !empty($percent) ? $percent*100 : 0;
			if ( $min_percent_resume > 100 ) {
				$min_percent_resume = 100;
			}
			
			if ( $profile_percent < $min_percent_resume ) {
				$return = array( 'status' => false, 'msg' => esc_html__('You need to complete your resume before you can apply for a job.', 'wp-job-board-pro') );
			   	echo wp_json_encode($return);
			   	exit;
			}
		}

		do_action('wp-job-board-pro-process-apply-internal', $_POST);

		// cv file
        $cv_file_ids = array();
        if ( !empty($_FILES['cv_file']) && !empty($_FILES['cv_file']['name']) ) {

			$files = $_FILES['cv_file'];
	    	if ( is_array($files['name']) ) {
	    		$has_file = false;
	    		$return = array();
			    foreach ($files['name'] as $key => $value) {            
		            if ($files['name'][$key]) { 
		            	$has_file = true;
		                $file = array( 
		                    'name' => $files['name'][$key],
		                    'type' => $files['type'][$key], 
		                    'tmp_name' => $files['tmp_name'][$key], 
		                    'error' => $files['error'][$key],
		                    'size' => $files['size'][$key]
		                ); 
		                $_FILES = array( 'cv_file' => $file ); 
		                foreach ($_FILES as $file => $array) {              
		                    $file_data = WP_Job_Board_Pro_Image::upload_cv_file($_FILES['cv_file']);
		                    if ( $file_data && !empty($file_data->url) ) {
				            	$cv_file_id = WP_Job_Board_Pro_Image::create_attachment( $file_data->url, 0 );

				            	if ( !empty($cv_file_id) ) {
				            		$cv_file_ids[] = $cv_file_id;
					            	$cv_attachments = self::get_post_meta($candidate_id, 'cv_attachment');
					            	if ( !empty($cv_attachments) ) {
					            		$cv_attachments[$cv_file_id] = $file_data->url;
					            	} else {
					            		$cv_attachments = array($cv_file_id => $file_data->url);
					            	}
					            	self::update_post_meta($candidate_id, 'cv_attachment', $cv_attachments);
					            }
				            }
		                }
		            } 
		        }

		        if ( $has_file && empty($cv_file_ids) ) {
					$return = array( 'status' => false, 'msg' => esc_html__('Can not upload file.', 'wp-job-board-pro') );
				   	echo wp_json_encode($return);
				   	exit;
				}
				
	        } else {
	        	$file_data = WP_Job_Board_Pro_Image::upload_cv_file($_FILES['cv_file']);
	            if ( $file_data && !empty($file_data->url) ) {
	            	$cv_file_id = WP_Job_Board_Pro_Image::create_attachment( $file_data->url, 0 );

	            	if ( !empty($cv_file_id) ) {
	            		$cv_file_ids[] = $cv_file_id;
		            	$cv_attachments = self::get_post_meta($candidate_id, 'cv_attachment');
		            	if ( !empty($cv_attachments) ) {
		            		$cv_attachments[$cv_file_id] = $file_data->url;
		            	} else {
		            		$cv_attachments = array($cv_file_id => $file_data->url);
		            	}
		            	self::update_post_meta($candidate_id, 'cv_attachment', $cv_attachments);
		            }
	            }

	            if ( empty($cv_file_ids) ) {
					$return = array( 'status' => false, 'msg' => esc_html__('Can not upload file.', 'wp-job-board-pro') );
				   	echo wp_json_encode($return);
				   	exit;
				}
	        }

	        
        }

        if ( empty($cv_file_ids) ) {
        	$apply_cv_id = !empty($_POST['apply_cv_id']) ? $_POST['apply_cv_id'] : '';
        	if ( !empty($apply_cv_id) ) {
        		if ( is_array($apply_cv_id) ) {
        			$cv_file_ids = $apply_cv_id;
        		} else {
		        	$cv_file_ids[] = $apply_cv_id;
		    	}
	    	}
        }

		$applicant_id = self::insert_applicant($user_id, $job, $cv_file_ids);
		
        if ( $applicant_id ) {
	        $return = array( 'status' => true, 'msg' => esc_html__('You have successfully applied to the job', 'wp-job-board-pro'), 'text' => esc_html__('Applied', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
	    } else {
			$return = array( 'status' => false, 'msg' => esc_html__('Error accord when applying for the job', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
	}

	public static function insert_applicant($user_id, $job, $cv_file_ids = 0) {
		$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
		$job_id = $job->ID;

		$post_args = array(
            'post_title' => get_the_title($candidate_id),
            'post_type' => 'job_applicant',
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => $user_id,
        );
		$post_args = apply_filters('wp-job-board-pro-add-job-applicant-data', $post_args);
		do_action('wp-job-board-pro-before-add-job-applicant');

        // Insert the post into the database
        $applicant_id = wp_insert_post($post_args);
        if ( $applicant_id ) {
	        update_post_meta($applicant_id, WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'candidate_id', $candidate_id);
	        update_post_meta($applicant_id, WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'job_id', $job_id);
	        update_post_meta($applicant_id, WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'job_name', $job->post_title);
	        
	        do_action('wp-job-board-pro-before-after-job-applicant-send-email', $applicant_id, $job_id, $candidate_id, $user_id);
	        
	        $message = !empty($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
    		WP_Job_Board_Pro_Applicant::update_post_meta($applicant_id, 'message', $message);
    		$cv_file_url = array();
    		$files_path = array();
        	if ( $cv_file_ids ) {
        		if ( is_array($cv_file_ids) ) {
        			WP_Job_Board_Pro_Applicant::update_post_meta($applicant_id, 'cv_file_id', $cv_file_ids);
        			foreach ($cv_file_ids as $cv_file_id) {
        				
		        		$download_url = WP_Job_Board_Pro_Ajax::get_endpoint('wp_job_board_pro_ajax_download_cv');
		        		$download_key = base64_encode('download-file-'.$cv_file_id);
		        		$cv_file_url[] = add_query_arg(array('file_id' => $cv_file_id, 'download_key' => $download_key), $download_url);

		        		$files_path[] = get_attached_file($cv_file_id);
        			}
        		} else {
        			$cv_file_id = $cv_file_ids;
	        		WP_Job_Board_Pro_Applicant::update_post_meta($applicant_id, 'cv_file_id', $cv_file_id);

	        		$download_url = WP_Job_Board_Pro_Ajax::get_endpoint('wp_job_board_pro_ajax_download_cv');
	        		$download_key = base64_encode('download-file-'.$cv_file_id);
	        		$cv_file_url[] = add_query_arg(array('file_id' => $cv_file_id, 'download_key' => $download_key), $download_url);

	        		$files_path[] = get_attached_file($cv_file_id);
	        	}
        	}

	        // send email
	        $email = self::get_post_meta($candidate_id, 'email', true);
	        $phone = self::get_post_meta($candidate_id, 'phone', true);
	        $candidate_name = get_post_field('post_title', $candidate_id);

	        $email_subject = WP_Job_Board_Pro_Email::render_email_vars( array('job_title' => $job->post_title), 'internal_apply_job_notice', 'subject');
	        $email_content_args = array(
	        	'job' => $job,
	        	'job_title' => $job->post_title,
	        	'candidate_name' => $candidate_name,
	        	'email' => $email,
	        	'phone' => $phone,
	        	'resume_url' => get_permalink($candidate_id),
	        	'cv_file_url' => $cv_file_url ? implode(', ', $cv_file_url) : '',
	        	'message' => $message,
	        );
	        $email_content_args = apply_filters('wp-job-board-pro-email-content-args-internal-apply', $email_content_args, $applicant_id);

	        $email_content = WP_Job_Board_Pro_Email::render_email_vars( $email_content_args, 'internal_apply_job_notice', 'content');

	        $headers = sprintf( "From: %s <%s>\r\n Content-type: text/html", get_bloginfo('name'), get_option( 'admin_email', false ) );
	        
			if ( empty($author_email) ) {
				$author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($job->ID);
				if ( WP_Job_Board_Pro_User::is_employer($author_id) ) {
					$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);
					$author_email = WP_Job_Board_Pro_Employer::get_post_meta($employer_id, 'email');
				}
				if ( empty($author_email) ) {
					$author_email = get_the_author_meta( 'user_email', $author_id );
				}
			}

			$result = WP_Job_Board_Pro_Email::wp_mail( $author_email, $email_subject, $email_content, $headers, $files_path );
			// end send email

			// thanks email
			$email_subject = WP_Job_Board_Pro_Email::render_email_vars( array('job_title' => $job->post_title), 'applied_job_thanks_notice', 'subject');
			$email_content_args = array(
	        	'job' => $job,
	        	'job_title' => $job->post_title,
	        	'candidate_name' => $candidate_name,
	        );
	        $email_content_args = apply_filters('wp-job-board-pro-email-content-args-applied-thanks', $email_content_args);
	        $email_content = WP_Job_Board_Pro_Email::render_email_vars( $email_content_args, 'applied_job_thanks_notice', 'content');
	        $headers = sprintf( "From: %s <%s>\r\n Content-type: text/html", get_bloginfo('name'), get_option( 'admin_email', false ) );
			WP_Job_Board_Pro_Email::wp_mail( $email, $email_subject, $email_content, $headers );

			
			// notification
			$author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($job_id);
			$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);
			$notify_args = array(
				'post_type' => 'employer',
				'user_post_id' => $employer_id,
	            'type' => 'internal_apply',
	            'job_id' => $job_id,
	            'candidate_id' => $candidate_id,
	            'applicant_id' => $applicant_id,
			);
			WP_Job_Board_Pro_User_Notification::add_notification($notify_args);

	        do_action('wp-job-board-pro-before-after-job-applicant', $applicant_id, $job_id, $candidate_id, $user_id);
	    }

	    return $applicant_id;
	}

	public static function check_candidate_can_apply() {
		$free_apply = wp_job_board_pro_get_option('candidate_free_job_apply', 'on');
		$return = true;
		if ( $free_apply == 'off' ) {
			$return = false;
		}
		return apply_filters('wp-job-board-pro-check-candidate-can-apply', $return);
	}
	
	public static function check_applied( $user_id, $job_id ) {
		if ( !is_user_logged_in() || !WP_Job_Board_Pro_User::is_candidate() ) {
			return false;
		}
		$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
		$posts = get_posts(array(
			'post_type' => 'job_applicant',
			'post_status' => 'publish',
			'meta_query' => array(
				array(
					'key' => WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'candidate_id',
			    	'value' => $candidate_id,
			    	'compare' => '=',
				),
				array(
					'key' => WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'job_id',
			    	'value' => $job_id,
			    	'compare' => '=',
				)
			)
		));
		if ( $posts && is_array($posts) ) {
			return true;
		}
		
		return false;
	}

	public static function process_remove_applied() {
		$return = array();
		if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp-job-board-pro-remove-applied-nonce' )  ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Your nonce did not verify.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		if ( !is_user_logged_in() ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Please login to remove shortlist.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$applicant_id = !empty($_POST['applicant_id']) ? $_POST['applicant_id'] : '';

		if ( empty($applicant_id) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Applicant doesn\'t exist', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$is_allowed = WP_Job_Board_Pro_Mixes::is_allowed_to_remove( $user_id, $applicant_id );
		$job_id = get_post_meta($applicant_id, WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'job_id', true);
		$is_allowed_job = WP_Job_Board_Pro_Mixes::is_allowed_to_remove( $user_id, $job_id );

		if ( !$is_allowed && !$is_allowed_job ) {
	        $return = array( 'status' => false, 'msg' => esc_html__('You can not remove this applied.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}

		$candidate_id = get_post_meta($applicant_id, WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'candidate_id', true);
		$candidate_package_id = get_post_meta($applicant_id, WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'candidate_package_id', true);

		do_action('wp-job-board-pro-process-remove-applied', $_POST);

		if ( wp_delete_post( $applicant_id ) ) {

			$author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($job_id);
			$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);
			$notify_args = array(
				'post_type' => 'candidate',
				'user_post_id' => $candidate_id,
	            'type' => 'remove_apply',
	            'job_id' => $job_id,
	            'employer_id' => $employer_id,
	            'applicant_id' => $applicant_id,
			);
			WP_Job_Board_Pro_User_Notification::add_notification($notify_args);

	        $return = array( 'status' => true, 'msg' => esc_html__('Application removed successful', 'wp-job-board-pro') );

	        do_action('wp-job-board-pro-after-remove-applied', $applicant_id, $job_id, $candidate_id, $candidate_package_id, $_POST);
		   	echo wp_json_encode($return);
		   	exit;
	    } else {
			$return = array( 'status' => false, 'msg' => esc_html__('Remove applied error.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
	}

	public static function process_add_job_shortlist() {
		$return = array();
		if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp-job-board-pro-add-job-shortlist-nonce' )  ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Your nonce did not verify.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		if ( !is_user_logged_in() || !WP_Job_Board_Pro_User::is_candidate() ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Please login as "Candidate" to add shortlist.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$job_id = !empty($_POST['job_id']) ? $_POST['job_id'] : '';
		$post = get_post($job_id);

		if ( !$post || empty($post->ID) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Job doesn\'t exist', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}

		do_action('wp-job-board-pro-process-add-job-shortlist', $_POST);

		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);

		$shortlist = self::get_post_meta($candidate_id, 'shortlist', true);

		if ( !empty($shortlist) && is_array($shortlist) ) {
			if ( !in_array($job_id, $shortlist) ) {
				$shortlist[] = $job_id;
			}
		} else {
			$shortlist = array( $job_id );
		}
		$result = self::update_post_meta( $candidate_id, 'shortlist', $shortlist );

		if ( $result ) {
	        $return = array( 'status' => true, 'msg' => esc_html__('Job has been added to the shortlist successfully', 'wp-job-board-pro'), 'html' => WP_Job_Board_Pro_Job_Listing::display_shortlist_btn($job_id, false) );
		   	echo wp_json_encode($return);
		   	exit;
	    } else {
			$return = array( 'status' => false, 'msg' => esc_html__('Error accord when add job to the shortlist', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
	}

	public static function check_added_shortlist($job_id) {
		if ( empty($job_id) || !is_user_logged_in() || !WP_Job_Board_Pro_User::is_candidate() ) {
			return false;
		}

		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);

		$shortlist = self::get_post_meta($candidate_id, 'shortlist', true);

		if ( !empty($shortlist) && is_array($shortlist) && in_array($job_id, $shortlist) ) {
			return true;
		} else {
			return false;
		}
	}

	public static function process_remove_job_shortlist() {
		$return = array();
		if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp-job-board-pro-remove-job-shortlist-nonce' )  ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Your nonce did not verify.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		if ( !is_user_logged_in() ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Please login to remove shortlist.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$job_id = !empty($_POST['job_id']) ? $_POST['job_id'] : '';

		if ( empty($job_id) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Job doesn\'t exist', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}

		do_action('wp-job-board-pro-process-remove-job-shortlist', $_POST);

		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);

		$result = true;
		$shortlist = self::get_post_meta($candidate_id, 'shortlist', true);
		if ( !empty($shortlist) && is_array($shortlist) ) {
			if ( in_array($job_id, $shortlist) ) {
				$key = array_search( $job_id, $shortlist );
				unset($shortlist[$key]);
				$result = self::update_post_meta( $candidate_id, 'shortlist', $shortlist );
			}
		}

		if ( $result ) {
	        $return = array( 'status' => true, 'msg' => esc_html__('Job has been removed from the shortlist successfully', 'wp-job-board-pro'), 'html' => WP_Job_Board_Pro_Job_Listing::display_shortlist_btn($job_id, false) );
		   	echo wp_json_encode($return);
		   	exit;
	    } else {
			$return = array( 'status' => false, 'msg' => esc_html__('Error accord when remove job from the shortlist', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
	}

	public static function process_follow_employer() {
		$return = array();
		if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp-job-board-pro-follow-employer-nonce' )  ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Your nonce did not verify.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		if ( !is_user_logged_in() || !WP_Job_Board_Pro_User::is_candidate() ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Please login as "Candidate" to follow employer.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$employer_id = !empty($_POST['employer_id']) ? $_POST['employer_id'] : '';

		if ( empty($employer_id) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Employer did not exists.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}

		do_action('wp-job-board-pro-process-follow-employer', $_POST);

		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$following = get_user_meta($user_id, '_following_employer', true);
		if ( !empty($following) && is_array($following) ) {
			if ( !in_array($employer_id, $following) ) {
				$following[] = $employer_id;
			}
		} else {
			$following = array($employer_id);
		}
		$result = update_user_meta($user_id, '_following_employer', $following);
		if ( $result ) {
	        $return = array( 'status' => true, 'nonce' => wp_create_nonce( 'wp-job-board-pro-unfollow-employer-nonce' ), 'text' => esc_html__('Following', 'wp-job-board-pro'), 'msg' => sprintf(__('You are now successfully following "%s"', 'wp-job-board-pro'), get_post_field('post_title', $employer_id)) );
		   	echo wp_json_encode($return);
		   	exit;
	    } else {
			$return = array( 'status' => false, 'msg' => esc_html__('Follow employer error.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
	}

	public static function process_unfollow_employer() {
		$return = array();
		if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp-job-board-pro-unfollow-employer-nonce' )  ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Your nonce did not verify.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		if ( !is_user_logged_in() ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Please login to unfollow employer.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$employer_id = !empty($_POST['employer_id']) ? $_POST['employer_id'] : '';

		if ( empty($employer_id) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Employer did not exists.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}

		do_action('wp-job-board-pro-process-unfollow-employer', $_POST);

		$result = true;
		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$following = get_user_meta($user_id, '_following_employer', true);
		if ( !empty($following) && is_array($following) ) {
			foreach ($following as $key => $id) {
				if ( $employer_id == $id ) {
					unset($following[$key]);
				}
			}
			$result = update_user_meta($user_id, '_following_employer', $following);
		}

		if ( $result ) {
	        $return = array( 'status' => true, 'nonce' => wp_create_nonce( 'wp-job-board-pro-follow-employer-nonce' ), 'text' => esc_html__('Follow us', 'wp-job-board-pro'), 'msg' => sprintf(__('You have successfully unfollowed "%s"', 'wp-job-board-pro'), get_post_field('post_title', $employer_id)) );
		   	echo wp_json_encode($return);
		   	exit;
	    } else {
			$return = array( 'status' => false, 'msg' => esc_html__('Unfollow employer error.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
	}

	public static function check_following($employer_id) {
		if ( empty($employer_id) || !is_user_logged_in() || !WP_Job_Board_Pro_User::is_candidate() ) {
			return false;
		}

		$user_id = WP_Job_Board_Pro_User::get_user_id();

		$following = get_user_meta($user_id, '_following_employer', true);

		if ( !empty($following) && is_array($following) && in_array($employer_id, $following) ) {
			return true;
		} else {
			return false;
		}
	}

	public static function process_download_file() {
	    $attachment_id = isset($_GET['file_id']) ? $_GET['file_id'] : '';
	    $attachment_id = absint($attachment_id);

	    $error_page_url = home_url('/404-error');

	    if ( $attachment_id > 0 ) {

	        $file_post = get_post($attachment_id);
	        $file_path = get_attached_file($attachment_id);

	        if ( !$file_post || !$file_path || !file_exists($file_path) ) {
	            wp_redirect($error_page_url);
	        } else {
	            
	            header('Content-Description: File Transfer');
	            header("Expires: 0");
				header("Cache-Control: no-cache, no-store, must-revalidate"); 
				header('Cache-Control: pre-check=0, post-check=0, max-age=0', false); 
				header("Pragma: no-cache");	
				header("Content-type: " . $file_post->post_mime_type);
				header('Content-Disposition:attachment; filename="'. basename($file_path) .'"');
				header("Content-Type: application/force-download");
				header('Content-Length: ' . @filesize($file_path));

	            @readfile($file_path);
	            exit;
	        }
	    } else {
	        wp_redirect($error_page_url);
	    }

	    die;
	}

	public static function check_user_can_download_cv($attachment_id) {
		if ( $attachment_id > 0 && is_user_logged_in() ) {

	        $file_post = get_post($attachment_id);
	        $file_path = get_attached_file($attachment_id);

	        if ( $file_post && $file_path && file_exists($file_path) ) {

	            $attch_parnt = get_post_ancestors($attachment_id);
	            if (isset($attch_parnt[0])) {
	                $attch_parnt = $attch_parnt[0];
	            }
	            
	            $error = true;

	            $user_id = WP_Job_Board_Pro_User::get_user_id();
	            $cur_user_obj = wp_get_current_user();

	            if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
	                $error = false;
	            }

	            if ( WP_Job_Board_Pro_User::is_candidate($user_id) ) {
	                $user_cand_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
	                if ($user_cand_id == $attch_parnt) {
	                    $error = false;
	                }
	            }

	            if ( in_array('administrator', (array) $cur_user_obj->roles) ) {
	                $error = false;
	            }

	            if ( !$error ) {
	                return true;
	            }
	            
	        }
	    }
	    return false;
	}

	public static function process_download_cv() {
	    $attachment_id = isset($_GET['file_id']) ? $_GET['file_id'] : '';
	    $attachment_id = absint($attachment_id);

	    $error_page_url = home_url('/404-error');

	    if ( $attachment_id > 0 ) {

	        $file_post = get_post($attachment_id);
	        $file_path = get_attached_file($attachment_id);

	        if ( !$file_post || !$file_path || !file_exists($file_path) ) {
	            wp_redirect($error_page_url);
	        } else {

	            $attch_parnt = get_post_ancestors($attachment_id);
	            if (isset($attch_parnt[0])) {
	                $attch_parnt = $attch_parnt[0];
	            }
	            
	            $error = true;

	            $download_key = isset($_GET['download_key']) ? $_GET['download_key'] : '';

	            if ( !empty($download_key) && $download_key == base64_encode('download-file-'.$attachment_id) ) {
	            	$error = false;
	            } else {
		            if (!is_user_logged_in() && !apply_filters('wp-job-board-pro-loggedin-user-download', false)) {
		                wp_redirect($error_page_url);
		                exit;
		            }
		            $user_id = WP_Job_Board_Pro_User::get_user_id();
		            $cur_user_obj = wp_get_current_user();

		            if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
		                $error = false;
		            }

		            if ( WP_Job_Board_Pro_User::is_candidate($user_id) ) {
		                $user_cand_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
		                if ($user_cand_id == $attch_parnt) {
		                    $error = false;
		                }
		            }

		            if ( in_array('administrator', (array) $cur_user_obj->roles) ) {
		                $error = false;
		            }

		            $error = apply_filters('wp-job-board-pro-download-cv-check', $error, $file_post);
	            }

	            if ( $error ) {
	                wp_redirect($error_page_url);
	                exit;
	            }
	            
	            header('Content-Description: File Transfer');
	            header("Expires: 0");
				header("Cache-Control: no-cache, no-store, must-revalidate"); 
				header('Cache-Control: pre-check=0, post-check=0, max-age=0', false); 
				header("Pragma: no-cache");	
				header("Content-type: " . $file_post->post_mime_type);
				header('Content-Disposition:attachment; filename="'. basename($file_path) .'"');
				header("Content-Type: application/force-download");
				header('Content-Length: ' . @filesize($file_path));

	            @readfile($file_path);
	            exit;
	        }
	    } else {
	        wp_redirect($error_page_url);
	    }

	    die;
	}

	public static function process_download_resume_cv() {
		$post_id = isset($_GET['post_id']) ? $_GET['post_id'] : '';
	    $post_id = absint($post_id);

	    $error_page_url = home_url('/404-error');

	    if ( $post_id > 0 ) {

	        $resume_post = get_post($post_id);

	        if ( !$resume_post ) {
	            wp_redirect($error_page_url);
	        } else {

	            $error = true;
	            if (!is_user_logged_in() && !apply_filters('wp-job-board-pro-loggedin-user-download', false)) {
	                wp_redirect($error_page_url);
	                exit;
	            }
	            $user_id = WP_Job_Board_Pro_User::get_user_id();
	            $cur_user_obj = wp_get_current_user();

	            if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
	                $error = false;
	            } elseif ( WP_Job_Board_Pro_User::is_candidate($user_id) ) {
					$candidate_post_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
					if ( $post_id == $candidate_post_id ) {
						$error = false;
					}
	            }

	            if ( in_array('administrator', (array) $cur_user_obj->roles) ) {
	                $error = false;
	            }

	            $error = apply_filters('wp-job-board-pro-download-resume-cv-check', $error, $resume_post);

	            $file_path = WP_Job_Board_Pro_Mpdf::mpdf_exec($resume_post);
	            if ( empty($file_path) ) {
	            	$error = false;
	            }

	            if ( $error ) {
	                wp_redirect($error_page_url);
	                exit;
	            }

	            header('Content-Description: File Transfer');
	            header("Expires: 0");
				header("Cache-Control: no-cache, no-store, must-revalidate"); 
				header('Cache-Control: pre-check=0, post-check=0, max-age=0', false); 
				header("Pragma: no-cache");	
				header("Content-type: application/pdf");
				header('Content-Disposition:attachment; filename="'. basename($file_path) .'"');
				header("Content-Type: application/force-download");
				header('Content-Length: ' . @filesize($file_path));

	            @readfile($file_path);
	            exit;
	        }
	    } else {
	        wp_redirect($error_page_url);
	    }

	    die;
	}

	public static function candidate_only_applicants($post) {
		$return = false;
		if ( is_user_logged_in() ) {
			$user_id = WP_Job_Board_Pro_User::get_user_id();
			if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
				$query_vars = array(
					'post_type'     => 'job_listing',
					'post_status'   => 'publish',
					'paged'         => 1,
					'author'        => $user_id,
					'fields' => 'ids',
					'posts_per_page'    => -1,
				);
				$jobs = WP_Job_Board_Pro_Query::get_posts($query_vars);
				if ( !empty($jobs) && !empty($jobs->posts) ) {
					$query_vars = array(
					    'post_type' => 'job_applicant',
					    'posts_per_page'    => -1,
					    'paged'    			=> 1,
					    'post_status' => 'publish',
					    'fields' => 'ids',
					    'meta_query' => array(
					    	array(
						    	'key' => WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'candidate_id',
						    	'value' => $post->ID,
						    	'compare' => '=',
						    ),
						    array(
						    	'key' => WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'job_id',
						    	'value' => $jobs->posts,
						    	'compare' => 'IN',
						    ),
						)
					);

					$applicants = WP_Job_Board_Pro_Query::get_posts($query_vars);
					if ( !empty($applicants) && !empty($applicants->posts) ) {
						$return = true;
					}
				}
			}
		}
		return $return;
	}

	// check view
	public static function check_view_candidate_detail() {
		global $post;
		$restrict_type = wp_job_board_pro_get_option('candidate_restrict_type', '');
		$view = wp_job_board_pro_get_option('candidate_restrict_detail', 'all');
		
		$return = true;
		if ( $restrict_type == 'view' ) {
			$author_id = WP_Job_Board_Pro_User::get_user_by_candidate_id($post->ID);
			if ( get_current_user_id() == $author_id ) {
				$return = true;
			} else {
				switch ($view) {
					case 'register_user':
						$return = false;
						if ( is_user_logged_in() ) {
							$show_profile = self::get_post_meta($post->ID, 'show_profile');
							if ( empty($show_profile) || $show_profile == 'show' ) {
								$return = true;
							}
						}
						break;
					case 'register_employer':
						$return = false;
						if ( is_user_logged_in() ) {
							$user_id = WP_Job_Board_Pro_User::get_user_id();
							if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
								$show_profile = self::get_post_meta($post->ID, 'show_profile');
								if ( empty($show_profile) || $show_profile == 'show' ) {
									$return = true;
								}
							}
						}
						break;
					case 'only_applicants':
						$return = self::candidate_only_applicants($post);
						break;
					default:
						$return = false;
						$show_profile = self::get_post_meta($post->ID, 'show_profile');
						if ( empty($show_profile) || $show_profile == 'show' ) {
							$return = true;
						}
						break;
				}
			}

		} else {
			$return = self::candidate_only_applicants($post);
			if ( !$return ) {
				$show_profile = self::get_post_meta($post->ID, 'show_profile');
				if ( empty($show_profile) || $show_profile == 'show' ) {
					$return = true;
				}
			}
		}

		return apply_filters('wp-job-board-pro-check-view-candidate-detail', $return, $post);
	}

	public static function candidate_restrict_listing_query($query, $filter_params) {
		$query_vars = $query->query_vars;
		$query_vars = self::candidate_restrict_listing_query_args($query_vars, $filter_params);
		$query->query_vars = $query_vars;
		
		return apply_filters('wp-job-board-pro-check-view-candidate-listing-query', $query);
	}

	public static function candidate_restrict_listing_query_args($query_args, $filter_params) {
		$view_user_id = !empty($query_args['view_user_id']) ? $query_args['view_user_id'] : 0;

		$restrict_type = wp_job_board_pro_get_option('candidate_restrict_type', '');
		if ( $restrict_type == 'view' ) {
			$view = wp_job_board_pro_get_option('candidate_restrict_listing', 'all');
			
			switch ($view) {
				case 'register_user':
					if ( !$view_user_id && !is_user_logged_in() ) {
						$meta_query = !empty($query_args['meta_query']) ? $query_args['meta_query'] : array();
						$meta_query[] = array(
							'key'       => 'candidate_restrict_listing',
							'value'     => 'register_user',
							'compare'   => '==',
						);
						$query_args['meta_query'] = $meta_query;
					}
					break;
				case 'register_employer':
					$return = false;
					if ( $view_user_id ) {
						$user_id = $view_user_id;
					} elseif ( is_user_logged_in() ) {
						$user_id = WP_Job_Board_Pro_User::get_user_id();
					}

					if ( !empty($user_id) && WP_Job_Board_Pro_User::is_employer($user_id) ) {
						$return = true;
					}

					if ( !$return ) {
						$meta_query = !empty($query_args['meta_query']) ? $query_args['meta_query'] : array();
						$meta_query[] = array(
							'key'       => 'candidate_restrict_listing',
							'value'     => 'register_employer',
							'compare'   => '==',
						);
						$query_args['meta_query'] = $meta_query;
					}
					break;
				case 'only_applicants':
					$ids = array(0);
					if ( $view_user_id ) {
						$user_id = $view_user_id;
					} elseif ( is_user_logged_in() ) {
						$user_id = WP_Job_Board_Pro_User::get_user_id();
					}
					if ( !empty($user_id) ) {
						$applicants = WP_Job_Board_Pro_Applicant::get_all_applicants_by_employer($user_id);
						foreach ($applicants as $applicant_id) {
							$candidate_id = get_post_meta($applicant_id, WP_JOB_BOARD_PRO_APPLICANT_PREFIX.'candidate_id', true );
							if ( $candidate_id ) {
								$return[] = $candidate_id;
							}
						}
					}
					if ( !empty($return) ) {
						$post__in = !empty($query_args['post__in']) ? $query_args['post__in'] : array();
						if ( !empty($post__in) ) {
							$ids = array_intersect($return, $post__in);
						} else {
							$ids = $return;
						}
						$ids[] = 0;
					}
					$query_args['post__in'] = $ids;
					break;
			}
		}

		// show/hide profile
		$meta_query = !empty($query_args['meta_query']) ? $query_args['meta_query'] : array();
		
		$meta_query[] = array(
			array(
				'relation' => 'OR',
				array(
					'key'       => WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'show_profile',
					'value'     => 'hide',
					'compare'   => '!=',
				),
				array(
					'key'       => WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'show_profile',
					'compare' => 'NOT EXISTS',
				),
			)
		);
		
		$query_args['meta_query'] = $meta_query;
		
		return apply_filters('wp-job-board-pro-check-view-candidate-listing-query-args', $query_args);
	}

	public static function check_restrict_view_contact_info($post) {
		$return = true;
		$restrict_type = wp_job_board_pro_get_option('candidate_restrict_type', '');
		if ( $restrict_type == 'view_contact_info' ) {
			$view = wp_job_board_pro_get_option('candidate_restrict_contact_info', 'all');

			$author_id = WP_Job_Board_Pro_User::get_user_by_candidate_id($post->ID);
			if ( get_current_user_id() == $author_id ) {
				$return = true;
			} else {
				switch ($view) {
					case 'register_user':
						$return = false;
						if ( is_user_logged_in() ) {
							$return = true;
						}
						break;
					case 'register_employer':
						$return = false;
						if ( is_user_logged_in() ) {
							$user_id = WP_Job_Board_Pro_User::get_user_id();
							if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
								$return = true;
							}
						}
						break;
					case 'only_applicants':
						$return = self::candidate_only_applicants($post);
						break;
					default:
						$return = true;
						break;
				}
			}
		}
		return apply_filters('wp-job-board-pro-check-view-candidate-contact-info', $return, $post);
	}

	public static function check_restrict_review($post) {
		$return = true;
		
		$view = wp_job_board_pro_get_option('candidates_restrict_review', 'all');
		
		switch ($view) {
			case 'always_hidden':
				$return = false;
				break;
			case 'register_user':
				$return = false;
				if ( is_user_logged_in() ) {
					$return = true;
				}
				break;
			case 'register_employer':
				$return = false;
				if ( is_user_logged_in() ) {
					$user_id = WP_Job_Board_Pro_User::get_user_id();
					if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
						$return = true;
					}
				}
				break;
			case 'only_applicants':
				$return = self::candidate_only_applicants($post);
				break;
			default:
				$return = true;
				break;
		}

		return apply_filters('wp-job-board-pro-check-restrict-candidate-review', $return, $post);
	}

	public static function display_candidates_results_filters() {
		$filters = WP_Job_Board_Pro_Abstract_Filter::get_filters();

		echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/candidate/results-filters', array('filters' => $filters));
	}

	public static function display_candidates_count_results($wp_query) {
		$total = $wp_query->found_posts;
		$per_page = $wp_query->query_vars['posts_per_page'];
		$current = max( 1, $wp_query->get( 'paged', 1 ) );
		$args = array(
			'total' => $total,
			'per_page' => $per_page,
			'current' => $current,
		);
		echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/candidate/results-count', $args);
	}

	public static function display_candidates_alert_form() {
		echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/candidate/candidates-alert-form');
	}

	public static function display_candidates_orderby() {
		echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/candidate/orderby');
	}

	public static function display_candidates_alert_orderby_start() {
		echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/candidate/alert-orderby-start');
	}

	public static function display_candidates_alert_orderby_end() {
		echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/candidate/alert-orderby-end');
	}

	public static function get_display_email($post) {
		if ( is_object($post) ) {
			$post_id = $post->ID;
		} else {
			$post_id = $post;
			$post = get_post($post_id);
		}
		$email = '';
		if ( self::check_restrict_view_contact_info($post) || wp_job_board_pro_get_option('restrict_contact_candidate_email', 'on') !== 'on' ) {
			$email = self::get_post_meta( $post_id, 'email', true );
		}
		return apply_filters('wp-job-board-pro-get-display-candidate-email', $email, $post_id);
	}

	public static function get_display_phone($post) {
		if ( is_object($post) ) {
			$post_id = $post->ID;
		} else {
			$post_id = $post;
			$post = get_post($post_id);
		}
		$phone = '';
		if ( self::check_restrict_view_contact_info($post) || wp_job_board_pro_get_option('restrict_contact_candidate_phone', 'on') !== 'on' ) {
			$phone = self::get_post_meta( $post_id, 'phone', true );
		}
		return apply_filters('wp-job-board-pro-get-display-candidate-phone', $phone, $post_id);
	}

	public static function get_display_cv_download($post) {
		if ( is_object($post) ) {
			$post_id = $post->ID;
		} else {
			$post_id = $post;
			$post = get_post($post_id);
		}
		$cv_attachment = '';
		if ( self::check_restrict_view_contact_info($post) || wp_job_board_pro_get_option('restrict_contact_candidate_download_cv', 'on') !== 'on' ) {
			$cv_attachment = self::get_post_meta( $post_id, 'cv_attachment', true );
		}
		return apply_filters('wp-job-board-pro-get-display-candidate-cv_attachment', $cv_attachment, $post_id);
	}

	public static function restrict_candidate_listing_information($query) {
		$restrict_type = wp_job_board_pro_get_option('candidate_restrict_type', '');
		if ( $restrict_type == 'view' ) {
			$view =  wp_job_board_pro_get_option('candidate_restrict_listing', 'all');
			$output = '';
			switch ($view) {
				case 'register_user':
					if ( !is_user_logged_in() ) {
						$output = '
						<div class="candidate-listing-info">
							<h2 class="restrict-title">'.__( 'The page is restricted only for register user.', 'wp-job-board-pro' ).'</h2>
							<div class="restrict-content">'.__( 'You need login to view this page', 'wp-job-board-pro' ).'</div>
						</div>';
					}
					break;
				case 'register_employer':
					$return = false;
					if ( is_user_logged_in() ) {
						$user_id = WP_Job_Board_Pro_User::get_user_id();
						if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
							$return = true;
						}
					}
					if ( !$return ) {
						$output = '<div class="candidate-listing-info"><h2 class="restrict-title">'.__( 'The page is restricted only for employers.', 'wp-job-board-pro' ).'</h2></div>';
					}
					break;
				case 'only_applicants':
					$return = array();
					if ( is_user_logged_in() ) {
						$user_id = WP_Job_Board_Pro_User::get_user_id();

						$applicants = WP_Job_Board_Pro_Applicant::get_all_applicants_by_employer($user_id);
						if ( !empty($applicants) ) {
							foreach ($applicants as $applicant_id) {
								$candidate_id = get_post_meta($applicant_id, WP_JOB_BOARD_PRO_APPLICANT_PREFIX.'candidate_id', true );
								if ( $candidate_id ) {
									$return[] = $candidate_id;
								}
							}
						}
					}
					if ( empty($return) ) {
						$output = '<div class="candidate-listing-info"><h2 class="restrict-title">'.__( 'The page is restricted only for employers view his applicants.', 'wp-job-board-pro' ).'</h2></div>';
					}
					break;
				default:
					$output = apply_filters('wp-job-board-pro-restrict-candidate-listing-default-information', '', $query);
					break;
			}

			echo apply_filters('wp-job-board-pro-restrict-candidate-listing-information', $output, $query);
		}
	}

	public static function candidate_feed_url($values = null, $exclude = array(), $current_key = '', $page_rss_url = '', $return = false) {
		if ( empty($page_rss_url) ) {
			$page_rss_url = home_url('/') . '?feed=candidate_listing_feed';
		}
		if ( is_null( $values ) ) {
			$values = $_GET; // WPCS: input var ok, CSRF ok.
		} elseif ( is_string( $values ) ) {
			$url_parts = wp_parse_url( $values );
			$values    = array();

			if ( ! empty( $url_parts['query'] ) ) {
				parse_str( $url_parts['query'], $values );
			}
		}
		foreach ( $values as $key => $value ) {
			if ( in_array( $key, $exclude, true ) ) {
				continue;
			}
			if ( $current_key ) {
				$key = $current_key . '[' . $key . ']';
			}
			if ( is_array( $value ) ) {
				$page_rss_url = self::candidate_feed_url( $value, $exclude, $key, $page_rss_url, true );
			} else {
				$page_rss_url = add_query_arg($key, wp_unslash( $value ), $page_rss_url);
			}
		}

		if ( $return ) {
			return $page_rss_url;
		}

		echo $page_rss_url;
	}

	public static function display_candidate_feed(){
		echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/candidate/candidates-rss-btn');
	}
}

WP_Job_Board_Pro_Candidate::init();