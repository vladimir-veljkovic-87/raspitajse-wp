<?php
/**
 * Employer
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Employer {
	
	public static function init() {
		// Ajax endpoints.
		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_employer_add_employee', array( __CLASS__, 'add_employee' ) );
		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_employer_remove_employee', array( __CLASS__, 'remove_employee' ) );

		// add_job_shortlist
		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_add_candidate_shortlist',  array(__CLASS__,'process_add_candidate_shortlist') );

		// remove job shortlist
		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_remove_candidate_shortlist',  array(__CLASS__,'process_remove_candidate_shortlist') );

		// invite candidate
		add_action( 'wjbp_ajax_wp_job_board_pro_ajax_invite_candidate',  array(__CLASS__, 'process_invite_candidate') );
		

		add_action( 'wp_job_board_pro_before_employer_archive', array( __CLASS__, 'display_employers_results_count_orderby_start' ), 5 );
		add_action( 'wp_job_board_pro_before_employer_archive', array( __CLASS__, 'display_employers_count_results' ), 10 );
		add_action( 'wp_job_board_pro_before_employer_archive', array( __CLASS__, 'display_employers_orderby' ), 15 );
		add_action( 'wp_job_board_pro_before_employer_archive', array( __CLASS__, 'display_employers_results_count_orderby_end' ), 100 );

		// restrict
		add_filter( 'wp-job-board-pro-employer-query-args', array( __CLASS__, 'employer_restrict_listing_query_args'), 100, 2 );
		add_filter( 'wp-job-board-pro-employer-filter-query', array( __CLASS__, 'employer_restrict_listing_query'), 100, 2 );

		add_action( 'wp_job_board_pro_after_employer_archive', array( __CLASS__, 'restrict_employer_listing_information' ), 10 );

		add_action( 'template_redirect', array( __CLASS__, 'track_job_view' ), 20 );
	}

	public static function get_post_meta($post_id, $key, $single = true) {
		return get_post_meta($post_id, WP_JOB_BOARD_PRO_EMPLOYER_PREFIX.$key, $single);
	}

	public static function update_post_meta($post_id, $key, $data) {
		return update_post_meta($post_id, WP_JOB_BOARD_PRO_EMPLOYER_PREFIX.$key, $data);
	}

	public static function track_job_view() {
	    if ( ! is_singular( 'employer' ) ) {
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

	public static function get_ajax_employees() {
		$query_args = array(
			'paged'         	=> 1,
			'number'    	=> 20,
			'orderby' => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
				'ID'         => 'DESC',
			),
			'order' => 'DESC',
			'role__in' => array('wp_job_board_pro_employee'),
			'search_columns' => array( 'user_login', 'user_email' )
		);
		if ( !empty($_REQUEST['q']) ) {
			$query_args['search'] = '*'.$_REQUEST['q'].'*';
		}
		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
		$employees = self::get_post_meta($employer_id, 'employees', false);
		if ( !empty($employees) ) {
			$query_args['exclude'] = $employees;
		}

		$users = get_users( $query_args );
		$return = array();
		if ( !empty($users) ) {
			foreach ($users as $user) {
				$return[] = array(
					'value' => $user->ID,
					'label' => $user->display_name,
					'img' => get_avatar($user->ID),
				);
			}
		}
		echo json_encode($return);
		exit();
	}

	public static function add_employee() {
		global $reg_errors;
		$return = array();
		if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp-job-board-pro-employer-add-employee-nonce' ) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Your nonce did not verify.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}

		// add employee user
		WP_Job_Board_Pro_User::registration_validation( $_POST['email'], $_POST['password'], $_POST['confirmpassword'], false, false );
		
		$username = !empty($_POST['username']) ? $_POST['username'] : '';
		if ( 4 > strlen( $username ) ) {
		    $reg_errors->add( 'username_length', esc_html__( 'Username too short. At least 4 characters is required', 'wp-job-board-pro' ) );
		}

		if ( username_exists( $username ) ) {
	    	$reg_errors->add('user_name', esc_html__( 'The username already exists', 'wp-job-board-pro' ) );
		}

		if ( ! validate_username( $username ) ) {
		    $reg_errors->add( 'username_invalid', esc_html__( 'The username you entered is not valid', 'wp-job-board-pro' ) );
		}

        if ( 1 > count( $reg_errors->get_error_messages() ) ) {

	 		$userdata = array(
		        'user_login' => sanitize_user( $_POST['username'] ),
		        'user_email' => sanitize_email( $_POST['email'] ),
		        'user_pass' => $_POST['password'],
		        'role' => 'wp_job_board_pro_employee'
	        );
	        $_POST['role'] = 'wp_job_board_pro_employee';
	        
	        $employee_id = wp_insert_user( $userdata );
	        if ( is_wp_error( $employee_id ) ) {
		        $return = array('status' => false, 'msg' => esc_html__( 'Register user error!', 'wp-job-board-pro' ) );
		        echo wp_json_encode($return);
			   	exit;
		    }
	    } else {
	    	$return = array('status' => false, 'msg' => implode('<br>', $reg_errors->get_error_messages()) );
	    	echo wp_json_encode($return);
		   	exit;
	    }

	    // add employee to employer
		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);

		update_user_meta($employee_id, 'employee_employer_id', $employer_id);

		$employees = self::get_post_meta($employer_id, 'employees', false);
		$html = '';
		if ( !empty($employees) ) {
			add_post_meta($employer_id, WP_JOB_BOARD_PRO_EMPLOYER_PREFIX.'employees', $employee_id);
            
            $userdata = get_userdata($employee_id);
            $employee_style = apply_filters('wp-job-board-pro-employee-inner-list-team', 'inner-list-team');
            $html = WP_Job_Board_Pro_Template_Loader::get_template_part( 'employees-styles/'.$employee_style, array('userdata' => $userdata) );
            
			$return = array( 'status' => true, 'msg' => esc_html__('Add employee to team successful', 'wp-job-board-pro'), 'html' => $html );
			echo wp_json_encode($return);
		   	exit;
		} else {
			add_post_meta($employer_id, WP_JOB_BOARD_PRO_EMPLOYER_PREFIX.'employees', $employee_id);

			$userdata = get_userdata($employee_id);
			$employee_style = apply_filters('wp-job-board-pro-employee-inner-list-team', 'inner-list-team');
            $html = WP_Job_Board_Pro_Template_Loader::get_template_part( 'employees-styles/'.$employee_style, array('userdata' => $userdata) );

			$return = array( 'status' => true, 'msg' => esc_html__('Add employee to team successful', 'wp-job-board-pro'), 'html' => $html );
			echo wp_json_encode($return);
		   	exit;
		}
	}

	public static function remove_employee() {
		$return = array();
		if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp-job-board-pro-employer-remove-employee-nonce' ) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Your nonce did not verify.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$employee_id = !empty($_POST['employee_id']) ? $_POST['employee_id'] : '';
		if ( empty($employee_id) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Employee not found', 'wp-job-board-pro') );
			echo wp_json_encode($return);
		   	exit;
		}

		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
		$employees = self::get_post_meta($employer_id, 'employees', false);
		if ( !empty($employees) && is_array($employees) ) {

			require_once( ABSPATH . 'wp-admin/includes/user.php' );
			
			wp_delete_user($employee_id);
		    delete_post_meta($employer_id, WP_JOB_BOARD_PRO_EMPLOYER_PREFIX.'employees', $employee_id);
			$return = array( 'status' => true, 'msg' => esc_html__('Remove employee from team successful', 'wp-job-board-pro') );
			echo wp_json_encode($return);
		   	exit;

		} else {
			$return = array( 'status' => false, 'msg' => esc_html__('Employee not found', 'wp-job-board-pro') );
			echo wp_json_encode($return);
		   	exit;
		}
	}

	public static function process_add_candidate_shortlist() {
		$return = array();
		if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp-job-board-pro-add-candidate-shortlist-nonce' )  ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Your nonce did not verify.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		if ( !is_user_logged_in() || !WP_Job_Board_Pro_User::is_employer() ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Please login as "Employer" to add shortlist.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$candidate_id = !empty($_POST['candidate_id']) ? $_POST['candidate_id'] : '';
		$post = get_post($candidate_id);

		if ( !$post || empty($post->ID) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Candidate doesn\'t exist', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}

		do_action('wp-job-board-pro-process-add-candidate-shortlist', $_POST);

		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);

		$shortlist = self::get_post_meta($employer_id, 'shortlist', true);
		if ( !empty($shortlist) && is_array($shortlist) ) {
			if ( !in_array($candidate_id, $shortlist) ) {
				$shortlist[] = $candidate_id;
			}
		} else {
			$shortlist = array( $candidate_id );
		}

		$result = self::update_post_meta( $employer_id, 'shortlist', $shortlist );

		if ( $result ) {
			
			do_action('wp-job-board-pro-after-add-candidate-shortlist', $employer_id, $shortlist, $post );

	        $return = array( 'status' => true, 'msg' => esc_html__('Candidate has been added to the shortlist successfully', 'wp-job-board-pro'), 'html' => WP_Job_Board_Pro_Candidate::display_shortlist_btn($candidate_id, false) );
		   	echo wp_json_encode($return);
		   	exit;
	    } else {
			$return = array( 'status' => false, 'msg' => esc_html__('Error accord when add candidate to the shortlist', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
	}

	public static function process_remove_candidate_shortlist() {
		$return = array();
		if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp-job-board-pro-remove-candidate-shortlist-nonce' )  ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Your nonce did not verify.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		if ( !is_user_logged_in() ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Please login to remove shortlist.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$candidate_id = !empty($_POST['candidate_id']) ? $_POST['candidate_id'] : '';

		if ( empty($candidate_id) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Candidate doesn\'t exist', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}

		do_action('wp-job-board-pro-process-remove-candidate-shortlist', $_POST);
		
		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);

		$result = true;
		$shortlist = self::get_post_meta($employer_id, 'shortlist', true);
		if ( !empty($shortlist) && is_array($shortlist) ) {
			if ( in_array($candidate_id, $shortlist) ) {
				$key = array_search( $candidate_id, $shortlist );
				unset($shortlist[$key]);
				$result = self::update_post_meta( $employer_id, 'shortlist', $shortlist );
			}
		}

		if ( $result ) {
	        $return = array( 'status' => true, 'msg' => esc_html__('Candidate has been removed from the shortlist successfully', 'wp-job-board-pro'), 'html' => WP_Job_Board_Pro_Candidate::display_shortlist_btn($candidate_id, false) );
		   	echo wp_json_encode($return);
		   	exit;
	    } else {
			$return = array( 'status' => false, 'msg' => esc_html__('Error accord when remove candidate from the shortlist', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
	}
	
	public static function process_invite_candidate() {
		if ( !is_user_logged_in() || !WP_Job_Board_Pro_User::is_employer() ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Please login as "Employer" to invite Candidate.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}
		$candidate_id = !empty($_POST['candidate_id']) ? $_POST['candidate_id'] : '';
		$post = get_post($candidate_id);

		if ( !$post || empty($post->ID) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Candidate doesn\'t exist', 'wp-job-board-pro') );
		   	wp_send_json($return);
		}

		$job_ids = !empty($_POST['job_ids']) ? $_POST['job_ids'] : '';
		if ( empty($job_ids) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Please select jobs first', 'wp-job-board-pro') );
		   	wp_send_json($return);
		}

		$clean_job_ids = array();
		foreach ($job_ids as $job_id) {
			$job_invited_list = get_post_meta($job_id, '_job_invited_candidate_apply', true);
            $job_invited_list = !empty($job_invited_list) ? $job_invited_list : array();
            if (!in_array($candidate_id, $job_invited_list)) {
                $clean_job_ids[] = $job_id;
            }
		}

		if (empty($clean_job_ids)) {
            $return = array( 'status' => false, 'msg' => esc_html__('You already invited this user for these jobs.', 'wp-job-board-pro') );
		   	wp_send_json($return);
        } else {

        	$user_id = WP_Job_Board_Pro_User::get_user_id();
			$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);

			$notify_args = array(
				'post_type' => 'candidate',
				'user_post_id' => $candidate_id,
	            'type' => 'invite_candidate_apply',
	            'employer_id' => $employer_id
			);

			$email_jobs_list = '<ul>';
            foreach ($clean_job_ids as $job_id) {
            	$email_jobs_list .= '<li><a href="'.get_permalink($job_id).'">'.get_the_title($job_id).'</a></li>';

                $job_invited_list = get_post_meta($job_id, '_job_invited_candidate_apply', true);
                $job_invited_list = !empty($job_invited_list) ? $job_invited_list : array();
                
                $job_invited_list[] = $candidate_id;
                update_post_meta($job_id, '_job_invited_candidate_apply', $job_invited_list);
            }
        	$email_jobs_list .= '</ul>';

        	// notify candidate
			$notify_args['job_ids'] = $clean_job_ids;
			WP_Job_Board_Pro_User_Notification::add_notification($notify_args);

            // Email
            $email_subject = WP_Job_Board_Pro_Email::render_email_vars( array('job_title' => $post->post_title, 'candidate_name' => get_the_title($candidate_id)), 'invite_candidate_notice', 'subject');
	        $email_content_args = array(
	        	'candidate_name' => get_the_title($candidate_id),
	        	'employer_name' => get_the_title($employer_id),
	        	'list_jobs' => $email_jobs_list,
	        );
	        $email_content = WP_Job_Board_Pro_Email::render_email_vars( $email_content_args, 'invite_candidate_notice', 'content');
			
	        $headers = sprintf( "From: %s <%s>\r\n Content-type: text/html", get_bloginfo('name'), get_option( 'admin_email', false ) );
	        
	        $candidate_email = get_post_meta( $candidate_id, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'email', true);
			if ( empty($candidate_email) ) {
				$candidate_user_id = WP_Job_Board_Pro_User::get_user_by_candidate_id($candidate_id);
				$candidate_email = get_the_author_meta( 'user_email', $candidate_user_id );
			}

			$result = WP_Job_Board_Pro_Email::wp_mail( $candidate_email, $email_subject, $email_content, $headers );

            do_action('wp-job-board-pro-invite-apply-to-candidate', $candidate_id, $clean_job_ids, $user_id );

            if ( $result ) {
	            $return = array( 'status' => true, 'msg' => esc_html__('Invited successfully.', 'wp-job-board-pro') );
			   	wp_send_json($return);
		   	} else {
		   		$return = array( 'status' => false, 'msg' => esc_html__('Send a email error', 'wp-job-board-pro') );
			   	wp_send_json($return);
		   	}
        }

	}

	public static function employer_only_applicants($post) {
		$return = false;
		if ( is_user_logged_in() ) {
			$user_id = WP_Job_Board_Pro_User::get_user_id();
			if ( WP_Job_Board_Pro_User::is_candidate($user_id) ) {
				$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
				$query_vars = array(
				    'post_type' => 'job_applicant',
				    'posts_per_page'    => -1,
				    'paged'    			=> 1,
				    'post_status' => 'publish',
				    'fields' => 'ids',
				    'meta_query' => array(
				    	array(
					    	'key' => WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'candidate_id',
					    	'value' => $candidate_id,
					    	'compare' => '=',
					    )
					)
				);
				
				$applicants = WP_Job_Board_Pro_Query::get_posts($query_vars);
				if ( !empty($applicants) && !empty($applicants->posts) ) {
					$employer_id = $post->ID;
					$employer_user_id = WP_Job_Board_Pro_User::get_user_by_employer_id($employer_id);
					foreach ($applicants->posts as $applicant_id) {
						$job_id = get_post_meta($applicant_id, WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'job_id', true);
						$post_author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($job_id);

						if ( $post_author_id == $employer_user_id ) {
							$return = true;
							break;
						}
					}
				}
			}
		}
		return $return;
	}

	// check view
	public static function check_view_employer_detail() {
		global $post;
		$restrict_type = wp_job_board_pro_get_option('employer_restrict_type', '');
		$view = wp_job_board_pro_get_option('employer_restrict_detail', 'all');
		
		$return = true;
		if ( $restrict_type == 'view' ) {
			$author_id = WP_Job_Board_Pro_User::get_user_by_employer_id($post->ID);
			$user_id = WP_Job_Board_Pro_User::get_user_id();
			if ( $user_id == $author_id ) {
				$return = true;
			} else {
				switch ($view) {
					case 'always_hidden':
						$return = false;
						break;
					case 'register_user':
						$return = false;
						if ( is_user_logged_in() ) {
							$show_profile = self::get_post_meta($post->ID, 'show_profile');
							if ( empty($show_profile) || $show_profile == 'show' ) {
								$return = true;
							}
						}
						break;
					case 'register_candidate':
						$return = false;
						if ( is_user_logged_in() ) {
							if ( WP_Job_Board_Pro_User::is_candidate($user_id) ) {
								$show_profile = self::get_post_meta($post->ID, 'show_profile');
								if ( empty($show_profile) || $show_profile == 'show' ) {
									$return = true;
								}
							}
						}
						break;
					case 'only_applicants':
						$return = self::employer_only_applicants($post);
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
		}
		return apply_filters('wp-job-board-pro-check-view-employer-detail', $return, $post);
	}

	public static function employer_restrict_listing_query($query, $filter_params) {
		$query_vars = $query->query_vars;
		$query_vars = self::employer_restrict_listing_query_args($query_vars, $filter_params);
		$query->query_vars = $query_vars;
		
		return apply_filters('wp-job-board-pro-check-view-employer-listing-query', $query);
	}

	public static function employer_restrict_listing_query_args($query_args, $filter_params) {
		$restrict_type = wp_job_board_pro_get_option('employer_restrict_type', '');

		if ( $restrict_type == 'view' ) {
			$view = wp_job_board_pro_get_option('employer_restrict_listing', 'all');
			
			$user_id = WP_Job_Board_Pro_User::get_user_id();
			switch ($view) {
				case 'always_hidden':
					$meta_query = !empty($query_args['meta_query']) ? $query_args['meta_query'] : array();
					$meta_query[] = array(
						'key'       => 'employer_restrict_listing',
						'value'     => 'always_hidden',
						'compare'   => '==',
					);
					$query_args['meta_query'] = $meta_query;
					break;
				case 'register_user':
					if ( !is_user_logged_in() ) {
						$meta_query = !empty($query_args['meta_query']) ? $query_args['meta_query'] : array();
						$meta_query[] = array(
							'key'       => 'employer_restrict_listing',
							'value'     => 'register_user',
							'compare'   => '==',
						);
						$query_args['meta_query'] = $meta_query;
					}
					break;
				case 'register_candidate':
					$return = false;
					if ( is_user_logged_in() ) {
						if ( WP_Job_Board_Pro_User::is_candidate($user_id) ) {
							$return = true;
						}
					}
					if ( !$return ) {
						$meta_query = !empty($query_args['meta_query']) ? $query_args['meta_query'] : array();
						$meta_query[] = array(
							'key'       => 'employer_restrict_listing',
							'value'     => 'register_candidate',
							'compare'   => '==',
						);
						$query_args['meta_query'] = $meta_query;
					}
					break;
				case 'only_applicants':

					$ids = array(0);
					if ( is_user_logged_in() ) {
						$applicants = WP_Job_Board_Pro_Applicant::get_all_applicants_by_candidate($user_id);
						foreach ($applicants as $applicant_id) {
							$job_id = get_post_meta($applicant_id, WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'job_id', true);
							$post_author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($job_id);
							$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($post_author_id);
							if ( $employer_id ) {
								$return[] = $employer_id;
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
					'key'       => WP_JOB_BOARD_PRO_EMPLOYER_PREFIX.'show_profile',
					'value'     => 'hide',
					'compare'   => '!=',
				),
				array(
					'key'       => WP_JOB_BOARD_PRO_EMPLOYER_PREFIX.'show_profile',
					'compare' => 'NOT EXISTS',
				),
			)
		);

		$query_args['meta_query'] = $meta_query;

		return apply_filters('wp-job-board-pro-check-view-employer-listing-query-args', $query_args);
	}

	public static function check_restrict_view_contact_info($post) {
		$return = true;
		$restrict_type = wp_job_board_pro_get_option('employer_restrict_type', '');
		if ( $restrict_type == 'view_contact_info' ) {
			$view = wp_job_board_pro_get_option('employer_restrict_contact_info', 'all');

			$user_id = WP_Job_Board_Pro_User::get_user_id();

			$author_id = WP_Job_Board_Pro_User::get_user_by_employer_id($post->ID);
			if ( $user_id == $author_id ) {

				$return = true;
			} else {
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
					case 'register_candidate':
						$return = false;
						if ( is_user_logged_in() ) {
							if ( WP_Job_Board_Pro_User::is_candidate($user_id) ) {
								$return = true;
							}
						}
						break;
					case 'only_applicants':
						$return = self::employer_only_applicants($post);
						break;
					default:
						$return = true;
						break;
				}
			}
		}
		return apply_filters('wp-job-board-pro-check-view-employer-contact-info', $return, $post);
	}

	public static function check_restrict_review($post) {
		$return = true;
		
		$user_id = WP_Job_Board_Pro_User::get_user_id();

		$view = wp_job_board_pro_get_option('employers_restrict_review', 'all');
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
			case 'register_candidate':
				$return = false;
				if ( is_user_logged_in() ) {
					if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
						$return = true;
					}
				}
				break;
			case 'only_applicants':
				$return = self::employer_only_applicants($post);
				break;
			default:
				$return = true;
				break;
		}
		return apply_filters('wp-job-board-pro-check-restrict-employer-review', $return, $post);
	}

	public static function display_follow_btn($employer_id = null) {
		if ( WP_Job_Board_Pro_Candidate::check_following($employer_id) ) {
			$classes = 'btn-unfollow-employer';
			$nonce = wp_create_nonce( 'wp-job-board-pro-unfollow-employer-nonce' );
			$text = esc_html__('Following', 'wp-job-board-pro');
		} else {
			$classes = 'btn-follow-employer';
			$nonce = wp_create_nonce( 'wp-job-board-pro-follow-employer-nonce' );
			$text = esc_html__('Follow us', 'wp-job-board-pro');
		}
		?>
		<a href="javascript:void(0)" class="btn button btn-block btn-theme btn-icon <?php echo esc_attr($classes); ?>" data-employer_id="<?php echo esc_attr($employer_id); ?>" data-nonce="<?php echo esc_attr($nonce); ?>"><i class="flaticon-alarm pre"></i> <span><?php echo esc_html($text); ?></span></a>
		<?php
	}

	public static function check_added_shortlist($candidate_id) {
		if ( empty($candidate_id) || !is_user_logged_in() || !WP_Job_Board_Pro_User::is_employer() ) {
			return false;
		}

		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);

		$shortlist = self::get_post_meta($employer_id, 'shortlist', true);
		
		if ( !empty($shortlist) && is_array($shortlist) && in_array($candidate_id, $shortlist) ) {
			return true;
		} else {
			return false;
		}
	}

	public static function display_employers_count_results($wp_query) {
		$total = $wp_query->found_posts;
		$per_page = $wp_query->query_vars['posts_per_page'];
		$current = max( 1, $wp_query->get( 'paged', 1 ) );
		$args = array(
			'total' => $total,
			'per_page' => $per_page,
			'current' => $current,
		);
		echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/employer/results-count', $args);
	}

	public static function display_employers_orderby() {
		echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/employer/orderby');
	}

	public static function display_employers_results_count_orderby_start() {
		echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/employer/results-count-orderby-start');
	}

	public static function display_employers_results_count_orderby_end() {
		echo WP_Job_Board_Pro_Template_Loader::get_template_part('loop/employer/results-count-orderby-end');
	}

	public static function restrict_employer_listing_information($query) {
		$restrict_type = wp_job_board_pro_get_option('employer_restrict_type', '');
		if ( $restrict_type == 'view' ) {
			$user_id = WP_Job_Board_Pro_User::get_user_id();
			$view =  wp_job_board_pro_get_option('employer_restrict_listing', 'all');
			$output = '';
			switch ($view) {
				case 'always_hidden':
						$output = '
						<div class="employer-listing-info">
							<h2 class="restrict-title">'.__( 'The page is restricted. You can not view this page', 'wp-job-board-pro' ).'</h2>
						</div>';
					break;
				case 'register_user':
					if ( !is_user_logged_in() ) {
						$output = '
						<div class="employer-listing-info">
							<h2 class="restrict-title">'.__( 'The page is restricted only for register user.', 'wp-job-board-pro' ).'</h2>
							<div class="restrict-content">'.__( 'You need login to view this page', 'wp-job-board-pro' ).'</div>
						</div>';
					}
					break;
				case 'register_candidate':
					$return = false;
					if ( is_user_logged_in() ) {
						if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
							$return = true;
						}
					}
					if ( !$return ) {
						$output = '<div class="employer-listing-info"><h2 class="restrict-title">'.__( 'The page is restricted only for candidates.', 'wp-job-board-pro' ).'</h2></div>';
					}
					break;
				case 'only_applicants':
					$return = array();
					if ( is_user_logged_in() ) {
						$applicants = WP_Job_Board_Pro_Applicant::get_all_applicants_by_candidate($user_id);
						if ( !empty($applicants) ) {
							foreach ($applicants as $applicant_id) {
								$job_id = get_post_meta($applicant_id, WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'job_id', true);
								$post_author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($job_id);

								$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($post_author_id);
								if ( $employer_id ) {
									$return[] = $employer_id;
								}
							}
						}
					}
					if ( empty($return) ) {
						$output = '<div class="employer-listing-info"><h2 class="restrict-title">'.__( 'The page is restricted only for candidates view his applicants.', 'wp-job-board-pro' ).'</h2></div>';
					}
					break;
				default:
					$output = apply_filters('wp-job-board-pro-restrict-employer-listing-default-information', '', $query);
					break;
			}

			echo apply_filters('wp-job-board-pro-restrict-employer-listing-information', $output, $query);
		}
	}

	public static function get_display_email($post) {
		if ( is_object($post) ) {
			$post_id = $post->ID;
		} else {
			$post_id = $post;
			$post = get_post($post_id);
		}
		$email = '';
		if ( self::check_restrict_view_contact_info($post) || wp_job_board_pro_get_option('restrict_contact_employer_email', 'on') !== 'on' ) {
			$email = self::get_post_meta( $post_id, 'email', true );
		}
		return apply_filters('wp-job-board-pro-get-display-employer-email', $email, $post_id);
	}

	public static function get_display_phone($post) {
		if ( is_object($post) ) {
			$post_id = $post->ID;
		} else {
			$post_id = $post;
			$post = get_post($post_id);
		}
		$phone = '';
		if ( self::check_restrict_view_contact_info($post) || wp_job_board_pro_get_option('restrict_contact_employer_phone', 'on') !== 'on' ) {
			$phone = self::get_post_meta( $post_id, 'phone', true );
		}
		return apply_filters('wp-job-board-pro-get-display-employer-phone', $phone, $post_id);
	}
}

WP_Job_Board_Pro_Employer::init();