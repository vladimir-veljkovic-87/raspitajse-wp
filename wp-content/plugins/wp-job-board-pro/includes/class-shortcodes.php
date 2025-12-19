<?php
/**
 * Shortcodes
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Shortcodes {
	/**
	 * Initialize shortcodes
	 *
	 * @access public
	 * @return void
	 */
	public static function init() {
	    add_action( 'wp', array( __CLASS__, 'check_logout' ) );

	    // login | register
		add_shortcode( 'wp_job_board_pro_logout', array( __CLASS__, 'logout' ) );
	    add_shortcode( 'wp_job_board_pro_login', array( __CLASS__, 'login' ) );
	    add_shortcode( 'wp_job_board_pro_register', array( __CLASS__, 'register' ) );
	    add_shortcode( 'wp_job_board_pro_register_candidate', array( __CLASS__, 'register_candidate' ) );
	    add_shortcode( 'wp_job_board_pro_register_employer', array( __CLASS__, 'register_employer' ) );

	    // profile
	    add_shortcode( 'wp_job_board_pro_user_dashboard', array( __CLASS__, 'user_dashboard' ) );
	    add_shortcode( 'wp_job_board_pro_change_password', array( __CLASS__, 'change_password' ) );
	    add_shortcode( 'wp_job_board_pro_change_profile', array( __CLASS__, 'change_profile' ) );
	    add_shortcode( 'wp_job_board_pro_change_resume', array( __CLASS__, 'change_resume' ) );
	    add_shortcode( 'wp_job_board_pro_delete_profile', array( __CLASS__, 'delete_profile' ) );
	    add_shortcode( 'wp_job_board_pro_approve_user', array( __CLASS__, 'approve_user' ) );
    	
    	// employer
		add_shortcode( 'wp_job_board_pro_submission', array( __CLASS__, 'submission' ) );
	    add_shortcode( 'wp_job_board_pro_my_jobs', array( __CLASS__, 'my_jobs' ) );

	    add_shortcode( 'wp_job_board_pro_job_applicants', array( __CLASS__, 'job_applicants' ) );
	    add_shortcode( 'wp_job_board_pro_my_candidates_shortlist', array( __CLASS__, 'my_candidates_shortlist' ) );
	    add_shortcode( 'wp_job_board_pro_my_candidates_alerts', array( __CLASS__, 'my_candidates_alerts' ) );

	    add_shortcode( 'wp_job_board_pro_employer_employees', array( __CLASS__, 'employer_employees' ) );

	    add_shortcode( 'wp_job_board_pro_employer_meetings', array( __CLASS__, 'employer_meetings' ) );

	    // candidate
	    add_shortcode( 'wp_job_board_pro_my_jobs_shortlist', array( __CLASS__, 'my_jobs_shortlist' ) );
	    add_shortcode( 'wp_job_board_pro_my_applied', array( __CLASS__, 'my_applied' ) );
	    add_shortcode( 'wp_job_board_pro_my_jobs_alerts', array( __CLASS__, 'my_jobs_alerts' ) );
	    add_shortcode( 'wp_job_board_pro_my_following_employers', array( __CLASS__, 'my_following_employers' ) );

	    add_shortcode( 'wp_job_board_pro_candidate_meetings', array( __CLASS__, 'candidate_meetings' ) );

	    add_shortcode( 'wp_job_board_pro_jobs', array( __CLASS__, 'jobs' ) );
	    add_shortcode( 'wp_job_board_pro_employers', array( __CLASS__, 'employers' ) );
	    add_shortcode( 'wp_job_board_pro_candidates', array( __CLASS__, 'candidates' ) );

	    // currency
	    add_shortcode( 'wp_job_board_pro_currencies', array( __CLASS__, 'currencies' ) );
	}

	/**
	 * Logout checker
	 *
	 * @access public
	 * @param $wp
	 * @return void
	 */
	public static function check_logout( $wp ) {
		$post = get_post();

		if ( is_page() ) {
			if ( has_shortcode( $post->post_content, 'wp_job_board_pro_logout' ) ) {
				wp_safe_redirect( str_replace( '&amp;', '&', wp_logout_url( home_url( '/' ) ) ) );
				exit();
			} elseif ( has_shortcode( $post->post_content, 'wp_job_board_pro_my_jobs' ) ) {
				self::my_jobs_hanlder();
			}
		}

		if ( !empty($_GET['register_msg']) && ($user_data = get_userdata($_GET['register_msg'])) ) {
			$user_login_auth = WP_Job_Board_Pro_User::get_user_status($user_data);
        	if ( $user_login_auth == 'pending' ) {
				$jsondata = array(
	                'error' => false,
	                'msg' => WP_Job_Board_Pro_User::register_msg($user_data),
	            );
	            $_SESSION['register_msg'] = $jsondata;
			} elseif ( $user_login_auth == 'denied' ) {
	            $jsondata = array(
	                'status' => false,
	                'msg' => __('Your account is denied', 'wp-job-board-pro')
	            );
	            $_SESSION['register_msg'] = $jsondata;
	        }
		}
	}

	/**
	 * Logout
	 *
	 * @access public
	 * @return void
	 */
	public static function logout( $atts ) {}

	/**
	 * Login
	 *
	 * @access public
	 * @return string
	 */
	public static function login( $atts ) {
		if ( is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/loged-in' );
	    }
		return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/login', $atts );
	}

	/**
	 * Register
	 *
	 * @access public
	 * @return string
	 */
	public static function register( $atts ) {
		if ( is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/loged-in' );
	    }
		return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/register', $atts );
	}

	/**
	 * Register Candidate
	 *
	 * @access public
	 * @return string
	 */
	public static function register_candidate( $atts ) {
		if ( is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/loged-in' );
	    }
	    $form = WP_Job_Board_Pro_Candidate_Register_Form::get_instance();

		return $form->form_output();
	}

	/**
	 * Register Employer
	 *
	 * @access public
	 * @return string
	 */
	public static function register_employer( $atts ) {
		if ( is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/loged-in' );
	    }
	    $form = WP_Job_Board_Pro_Employer_Register_Form::get_instance();

		return $form->form_output();
	}

	/**
	 * Submission index
	 *
	 * @access public
	 * @return string|void
	 */
	public static function submission( $atts ) {
	    if ( ! is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
	    } else {
	    	$user_id = get_current_user_id();
	    	if ( WP_Job_Board_Pro_User::is_employee($user_id) ) {
	    		if ( !WP_Job_Board_Pro_User::is_employee_can_add_submission($user_id) ) {
	    			return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'employer') );
	    		}
	    	} elseif ( !WP_Job_Board_Pro_User::is_employer($user_id) ) {
				return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'employer') );
			}
	    }
	    
		$form = WP_Job_Board_Pro_Submit_Form::get_instance();

		return $form->output();
	}

	public static function edit_form( $atts ) {
		if ( ! is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
	    } elseif ( WP_Job_Board_Pro_User::is_employer() || (WP_Job_Board_Pro_User::is_employee() && wp_job_board_pro_get_option('employee_edit_job') == 'on')  ) {
	    	$user_id = WP_Job_Board_Pro_User::get_user_id();
	    	if ( empty($user_id) ) {
				return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'employer') );
			}
			$form = WP_Job_Board_Pro_Edit_Form::get_instance();

			return $form->output();
		}

		return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'employer') );
	}
	
	public static function my_jobs_hanlder() {
		$action = !empty($_REQUEST['action']) ? sanitize_title( $_REQUEST['action'] ) : '';
		$job_id = isset( $_REQUEST['job_id'] ) ? absint( $_REQUEST['job_id'] ) : 0;

		if ( $action == 'relist' || $action == 'continue' ) {
			$submit_form_page_id = wp_job_board_pro_get_option('submit_job_form_page_id');
			if ( $submit_form_page_id ) {
				$submit_page_url = get_permalink($submit_form_page_id);
				wp_safe_redirect( add_query_arg( array( 'job_id' => absint( $job_id ), 'action' => $action ), $submit_page_url ) );
				exit;
			}
			
		}
	}

	public static function my_jobs( $atts ) {
		if ( ! is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
	    } elseif ( WP_Job_Board_Pro_User::is_employer() || (WP_Job_Board_Pro_User::is_employee() && wp_job_board_pro_get_option('employee_view_my_jobs') == 'on') ) {
	    	$user_id = WP_Job_Board_Pro_User::get_user_id();
	    	if ( empty($user_id) ) {
				return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'employer') );
			}
			if ( ! empty( $_REQUEST['action'] ) ) {
				$action = sanitize_title( $_REQUEST['action'] );
				
				if ( $action == 'edit' ) {
					return self::edit_form($atts);
				}
			}
			return WP_Job_Board_Pro_Template_Loader::get_template_part( 'submission/my-jobs' );
		}
		return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'employer') );
	}
	
	/**
	 * Employer dashboard
	 *
	 * @access public
	 * @param $atts
	 * @return string
	 */
	public static function user_dashboard( $atts ) {
		if ( ! is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
	    } else {
			$user_id = get_current_user_id();
			$user = wp_get_current_user();
        	$roles = ( array ) $user->roles;
			if ( !empty($roles) && in_array('administrator', $roles) ) {
				$user_id = !empty($_GET['user_id']) ? $_GET['user_id'] : $user_id;
			}
		    if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
				$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
				return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/employer-dashboard', array( 'user_id' => $user_id, 'employer_id' => $employer_id ) );
			} elseif ( WP_Job_Board_Pro_User::is_candidate($user_id) ) {
				$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
				return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/candidate-dashboard', array( 'user_id' => $user_id, 'candidate_id' => $candidate_id ) );
			} elseif ( WP_Job_Board_Pro_User::is_employee($user_id) && wp_job_board_pro_get_option('employee_view_dashboard') == 'on' ) {
				$user_id = WP_Job_Board_Pro_User::get_user_id($user_id);
				if ( empty($user_id) ) {
					return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'employer') );
				}
				$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
				return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/employer-dashboard', array( 'user_id' => $user_id, 'employer_id' => $employer_id ) );
			}
	    }

    	return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed' );
	}

	/**
	 * Change password
	 *
	 * @access public
	 * @param $atts
	 * @return string
	 */
	public static function change_password( $atts ) {
		if ( ! is_user_logged_in() ) {
			return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
		}

		return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/password-form' );
	}

	/**
	 * Change profile
	 *
	 * @access public
	 * @param $atts
	 * @return void
	 */
	public static function change_profile( $atts ) {
		if ( ! is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
	    }
	    
	    $metaboxes = apply_filters( 'cmb2_meta_boxes', array() );
	    $metaboxes_form = array();
	    $user_id = get_current_user_id();
	    if ( WP_Job_Board_Pro_User::is_employer($user_id) ) {
	    	if ( ! isset( $metaboxes[ WP_JOB_BOARD_PRO_EMPLOYER_PREFIX . 'front' ] ) ) {
				return __( 'A metabox with the specified \'metabox_id\' doesn\'t exist.', 'wp-job-board-pro' );
			}
			$metaboxes_form = $metaboxes[ WP_JOB_BOARD_PRO_EMPLOYER_PREFIX . 'front' ];
			$post_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
	    } elseif( WP_Job_Board_Pro_User::is_candidate($user_id) ) {
	    	if ( ! isset( $metaboxes[ WP_JOB_BOARD_PRO_CANDIDATE_PREFIX . 'front' ] ) ) {
				return __( 'A metabox with the specified \'metabox_id\' doesn\'t exist.', 'wp-job-board-pro' );
			}
			$metaboxes_form = $metaboxes[ WP_JOB_BOARD_PRO_CANDIDATE_PREFIX . 'front' ];
			$post_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
	    } elseif ( WP_Job_Board_Pro_User::is_employee($user_id) && wp_job_board_pro_get_option('employee_edit_employer_profile') == 'on' ) {
	    	$user_id = WP_Job_Board_Pro_User::get_user_id($user_id);
	    	if ( empty($user_id) ) {
				return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'employer') );
			}

	    	if ( ! isset( $metaboxes[ WP_JOB_BOARD_PRO_EMPLOYER_PREFIX . 'front' ] ) ) {
				return __( 'A metabox with the specified \'metabox_id\' doesn\'t exist.', 'wp-job-board-pro' );
			}
			$metaboxes_form = $metaboxes[ WP_JOB_BOARD_PRO_EMPLOYER_PREFIX . 'front' ];
			$post_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
	    } else {
	    	return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed' );
	    }

		if ( !$post_id ) {
			return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed' );
		}
		if ( wp_job_board_pro_get_option('map_service') == 'google-map' ) {
			wp_enqueue_script('google-maps');
		}
		wp_enqueue_script('wpjbp-select2');
		wp_enqueue_style('wpjbp-select2');
		
		return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/profile-form', array('post_id' => $post_id, 'metaboxes_form' => $metaboxes_form ) );
	}

	public static function change_resume( $atts ) {
		if ( ! is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
	    } elseif ( !WP_Job_Board_Pro_User::is_candidate() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'candidate') );
	    }
	    
	    $metaboxes = apply_filters( 'cmb2_meta_boxes', array() );
	    $metaboxes_form = array();
	    $user_id = WP_Job_Board_Pro_User::get_user_id();
	    
    	if ( ! isset( $metaboxes[ WP_JOB_BOARD_PRO_CANDIDATE_PREFIX . 'resume_front' ] ) ) {
			return __( 'A metabox with the specified \'metabox_id\' doesn\'t exist.', 'wp-job-board-pro' );
		}
		$metaboxes_form = $metaboxes[ WP_JOB_BOARD_PRO_CANDIDATE_PREFIX . 'resume_front' ];
		$post_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
		
		if ( !$post_id ) {
			return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'candidate') );
		}
		if ( wp_job_board_pro_get_option('map_service') == 'google-map' ) {
			wp_enqueue_script('google-maps');
		}
		wp_enqueue_script('wpjbp-select2');
		wp_enqueue_style('wpjbp-select2');

		return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/resume-form', array('post_id' => $post_id, 'metaboxes_form' => $metaboxes_form ) );
	}

	public static function delete_profile($atts) {
		if ( ! is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
	    } elseif ( WP_Job_Board_Pro_User::is_employee() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed' );
	    }
	    $user_id = get_current_user_id();
	    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/delete-profile-form', array('user_id' => $user_id) );
	}

	public static function approve_user($atts) {
	    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/approve-user' );
	}

	public static function job_applicants( $atts ) {
		if ( ! is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
	    } elseif ( WP_Job_Board_Pro_User::is_employer() || (WP_Job_Board_Pro_User::is_employee() && wp_job_board_pro_get_option('employee_view_applications') == 'on') ) {
		   
		    $user_id = WP_Job_Board_Pro_User::get_user_id();
		    if ( empty($user_id) ) {
				return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'employer') );
			}

			$jobs_loop = new WP_Query( array(
				'post_type' => 'job_listing',
				'fields' => 'ids',
				'author' => $user_id,
				'orderby' => 'date',
				'order' => 'DESC',
				'posts_per_page' => -1,
			));

			$job_ids = array();
			if ( !empty($jobs_loop) && !empty($jobs_loop->posts) ) {
				$job_ids = $jobs_loop->posts;
			}

			return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/job-applicants', array( 'job_ids' => $job_ids ) );

	    }
	    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'employer') );
	}

	public static function my_candidates_shortlist( $atts ) {
		if ( ! is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
	    } elseif ( WP_Job_Board_Pro_User::is_employer() || (WP_Job_Board_Pro_User::is_employee() && wp_job_board_pro_get_option('employee_view_shortlist') == 'on') ) {
		    
		    $candidate_ids_list = array();

		    $user_id = WP_Job_Board_Pro_User::get_user_id();
		    if ( empty($user_id) ) {
				return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'employer') );
			}
			$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
			$employer_ids = apply_filters( 'wp-job-board-pro-translations-post-ids', $employer_id );
			if ( !is_array($employer_ids) ) {
				$employer_ids = array($employer_ids);
			}

			foreach ($employer_ids as $employer_id) {
				$candidate_ids = get_post_meta( $employer_id, WP_JOB_BOARD_PRO_EMPLOYER_PREFIX.'shortlist', true );
				if ( !empty($candidate_ids) ) {
					foreach ($candidate_ids as $candidate_id) {
						$ids = apply_filters( 'wp-job-board-pro-translations-post-ids', $candidate_id );
						if ( !is_array($ids) ) {
							$ids = array($ids);
						}

						$candidate_ids_list = array_merge($candidate_ids_list, $ids);
					}
				}
			}
			
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/candidates-shortlist', array( 'candidate_ids' => $candidate_ids_list ) );
		}

		return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'employer') );
	}

	public static function my_candidates_alerts( $atts ) {
		if ( ! is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
	    } elseif ( WP_Job_Board_Pro_User::is_employer() || (WP_Job_Board_Pro_User::is_employee() && wp_job_board_pro_get_option('employee_view_candidate_alert') == 'on') ) {
		    
		    $user_id = WP_Job_Board_Pro_User::get_user_id();
		    if ( empty($user_id) ) {
				return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'employer') );
			}
		    if ( get_query_var( 'paged' ) ) {
			    $paged = get_query_var( 'paged' );
			} elseif ( get_query_var( 'page' ) ) {
			    $paged = get_query_var( 'page' );
			} else {
			    $paged = 1;
			}
			$query_vars = array(
			    'post_type' => 'candidate_alert',
			    'posts_per_page'    => get_option('posts_per_page'),
			    'paged'    			=> $paged,
			    'post_status' => 'publish',
			    'fields' => 'ids',
			    'author' => $user_id,
			);
			if ( isset($_GET['search']) ) {
				$query_vars['s'] = $_GET['search'];
			}
			if ( isset($_GET['orderby']) ) {
				switch ($_GET['orderby']) {
					case 'menu_order':
						$query_vars['orderby'] = array(
							'menu_order' => 'ASC',
							'date'       => 'DESC',
							'ID'         => 'DESC',
						);
						break;
					case 'newest':
						$query_vars['orderby'] = 'date';
						$query_vars['order'] = 'DESC';
						break;
					case 'oldest':
						$query_vars['orderby'] = 'date';
						$query_vars['order'] = 'ASC';
						break;
				}
			}

			$alerts = WP_Job_Board_Pro_Query::get_posts($query_vars);

			return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/my-candidates-alerts', array( 'alerts' => $alerts ) );
		}
		return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'employer') );
	}

	public static function employer_employees( $atts ) {
		if ( ! is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
	    } elseif ( !WP_Job_Board_Pro_User::is_employer() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'employer') );
	    }

	    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/employer-employees' );
	}

	public static function employer_meetings($atts) {
		if ( ! is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
	    } elseif ( !WP_Job_Board_Pro_User::is_employer() || (WP_Job_Board_Pro_User::is_employee() && wp_job_board_pro_get_option('employee_view_applications') == 'on') ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'employer') );
	    }

	    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/employer-meetings' );
	}

	public static function my_jobs_shortlist( $atts ) {
		if ( ! is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
	    } elseif ( !WP_Job_Board_Pro_User::is_candidate() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'candidate') );
	    }
	    $user_id = WP_Job_Board_Pro_User::get_user_id();
		$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);

		$job_ids = get_post_meta( $candidate_id, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'shortlist', true );

	    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/jobs-shortlist', array( 'job_ids' => $job_ids ) );
	}

	public static function my_applied( $atts ) {
		if ( ! is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
	    } elseif ( !WP_Job_Board_Pro_User::is_candidate() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'candidate') );
	    }

	    $user_id = WP_Job_Board_Pro_User::get_user_id();
		$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);

		if ( get_query_var( 'paged' ) ) {
		    $paged = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
		    $paged = get_query_var( 'page' );
		} else {
		    $paged = 1;
		}
		
		$candidate_ids = apply_filters( 'wp-job-board-pro-translations-post-ids', $candidate_id );
		if ( !is_array($candidate_ids) ) {
			$candidate_ids = array($candidate_ids);
		}
		$candidate_ids = array_merge(array(0), $candidate_ids);
		$query_vars = array(
		    'post_type' => 'job_applicant',
		    'posts_per_page'    => get_option('posts_per_page'),
		    'paged'    			=> $paged,
		    'post_status' => 'publish',
		    'fields' => 'ids',
		    'meta_query' => array(
		    	array(
			    	'key' => WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'candidate_id',
			    	'value' => $candidate_ids,
			    	'compare' => 'IN',
			    ),
			    
			),
			'suppress_filters' => true
		);
		if ( isset($_GET['search']) ) {
			$meta_query = $query_vars['meta_query'];
			$meta_query[] = array(
		    	'key' => WP_JOB_BOARD_PRO_APPLICANT_PREFIX . 'job_name',
		    	'value' => $_GET['search'],
		    	'compare' => 'LIKE',
		    );
			$query_vars['meta_query'] = $meta_query;
		}
		if ( isset($_GET['orderby']) ) {
			switch ($_GET['orderby']) {
				case 'menu_order':
					$query_vars['orderby'] = array(
						'menu_order' => 'ASC',
						'date'       => 'DESC',
						'ID'         => 'DESC',
					);
					break;
				case 'newest':
					$query_vars['orderby'] = 'date';
					$query_vars['order'] = 'DESC';
					break;
				case 'oldest':
					$query_vars['orderby'] = 'date';
					$query_vars['order'] = 'ASC';
					break;
			}
		}
		
		$applicants = new WP_Query($query_vars);
		return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/jobs-applied', array( 'applicants' => $applicants ) );
	}

	public static function my_jobs_alerts( $atts ) {
		if ( ! is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
	    } elseif ( !WP_Job_Board_Pro_User::is_candidate() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'candidate') );
	    }

	    $user_id = WP_Job_Board_Pro_User::get_user_id();
	    if ( get_query_var( 'paged' ) ) {
		    $paged = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
		    $paged = get_query_var( 'page' );
		} else {
		    $paged = 1;
		}

		$query_vars = array(
		    'post_type' => 'job_alert',
		    'posts_per_page'    => get_option('posts_per_page'),
		    'paged'    			=> $paged,
		    'post_status' => 'publish',
		    'fields' => 'ids',
		    'author' => $user_id,
		);
		if ( isset($_GET['search']) ) {
			$query_vars['s'] = $_GET['search'];
		}
		if ( isset($_GET['orderby']) ) {
			switch ($_GET['orderby']) {
				case 'menu_order':
					$query_vars['orderby'] = array(
						'menu_order' => 'ASC',
						'date'       => 'DESC',
						'ID'         => 'DESC',
					);
					break;
				case 'newest':
					$query_vars['orderby'] = 'date';
					$query_vars['order'] = 'DESC';
					break;
				case 'oldest':
					$query_vars['orderby'] = 'date';
					$query_vars['order'] = 'ASC';
					break;
			}
		}
		$alerts = WP_Job_Board_Pro_Query::get_posts($query_vars);

		return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/my-jobs-alerts', array( 'alerts' => $alerts ) );
	}

	public static function my_following_employers( $atts ) {
		if ( ! is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
	    } elseif ( !WP_Job_Board_Pro_User::is_candidate() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'candidate') );
	    }

	    $user_id = WP_Job_Board_Pro_User::get_user_id();
	    $ids = get_user_meta($user_id, '_following_employer', true);
	    $employers = array();
	    if ( !empty($ids) && is_array($ids) ) {
		    if ( get_query_var( 'paged' ) ) {
			    $paged = get_query_var( 'paged' );
			} elseif ( get_query_var( 'page' ) ) {
			    $paged = get_query_var( 'page' );
			} else {
			    $paged = 1;
			}
			$query_vars = array(
			    'post_type' => 'employer',
			    'posts_per_page'    => get_option('posts_per_page'),
			    'paged'    			=> $paged,
			    'post_status' => 'publish',
			    'post__in' => $ids,
			);
			if ( isset($_GET['search']) ) {
				$query_vars['s'] = $_GET['search'];
			}
			if ( isset($_GET['orderby']) ) {
				switch ($_GET['orderby']) {
					case 'menu_order':
						$query_vars['orderby'] = array(
							'menu_order' => 'ASC',
							'date'       => 'DESC',
							'ID'         => 'DESC',
						);
						break;
					case 'newest':
						$query_vars['orderby'] = 'date';
						$query_vars['order'] = 'DESC';
						break;
					case 'oldest':
						$query_vars['orderby'] = 'date';
						$query_vars['order'] = 'ASC';
						break;
				}
			}
			$employers = WP_Job_Board_Pro_Query::get_posts($query_vars);
		}
		return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/my-following-employers', array( 'employers' => $employers ) );
	}

	public static function candidate_meetings($atts) {
		if ( ! is_user_logged_in() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/need-login' );
	    } elseif ( !WP_Job_Board_Pro_User::is_candidate() ) {
		    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/not-allowed', array('need_role' => 'candidate') );
	    }

	    return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/candidate-meetings' );
	}

	public static function jobs( $atts ) {
		$atts = wp_parse_args( $atts, array(
			'limit' => wp_job_board_pro_get_option('number_jobs_per_page', 10),
			'post__in' => array(),
			'categories' => array(),
			'types' => array(),
			'locations' => array(),
		));

		if ( get_query_var( 'paged' ) ) {
		    $paged = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
		    $paged = get_query_var( 'page' );
		} else {
		    $paged = 1;
		}

		$query_args = array(
			'post_type' => 'job_listing',
		    'post_status' => 'publish',
		    'post_per_page' => $atts['limit'],
		    'paged' => $paged,
		);

		$params = array();
		if (WP_Job_Board_Pro_Abstract_Filter::has_filter($atts)) {
			$params = $atts;
		}
		if ( WP_Job_Board_Pro_Job_Filter::has_filter() ) {
			$params = array_merge($params, $_GET);
		}

		$jobs = WP_Job_Board_Pro_Query::get_posts($query_args, $params);
		// echo "<pre>".print_r($jobs,1); die;
		return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/jobs', array( 'jobs' => $jobs, 'atts' => $atts ) );
	}

	public static function employers( $atts ) {
		$atts = wp_parse_args( $atts, array(
			'limit' => wp_job_board_pro_get_option('number_employers_per_page', 10),
			'post__in' => array(),
			'categories' => array(),
			'types' => array(),
			'locations' => array(),
		));

		if ( get_query_var( 'paged' ) ) {
		    $paged = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
		    $paged = get_query_var( 'page' );
		} else {
		    $paged = 1;
		}

		$query_args = array(
			'post_type' => 'employer',
		    'post_status' => 'publish',
		    'post_per_page' => $atts['limit'],
		    'paged' => $paged,
		);

		$params = array();
		if (WP_Job_Board_Pro_Abstract_Filter::has_filter($atts)) {
			$params = $atts;
		}
		if ( WP_Job_Board_Pro_Employer_Filter::has_filter() ) {
			$params = array_merge($params, $_GET);
		}

		$employers = WP_Job_Board_Pro_Query::get_posts($query_args, $params);
		
		return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/employers', array( 'employers' => $employers, 'atts' => $atts ) );
	}

	public static function candidates( $atts ) {
		$atts = wp_parse_args( $atts, array(
			'limit' => wp_job_board_pro_get_option('number_candidates_per_page', 10),
			'post__in' => array(),
			'categories' => array(),
			'types' => array(),
			'locations' => array(),
		));

		if ( get_query_var( 'paged' ) ) {
		    $paged = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
		    $paged = get_query_var( 'page' );
		} else {
		    $paged = 1;
		}

		$query_args = array(
			'post_type' => 'candidate',
		    'post_status' => 'publish',
		    'post_per_page' => $atts['limit'],
		    'paged' => $paged,
		);
		$params = array();
		if (WP_Job_Board_Pro_Abstract_Filter::has_filter($atts)) {
			$params = $atts;
		}
		if ( WP_Job_Board_Pro_Candidate_Filter::has_filter() ) {
			$params = array_merge($params, $_GET);
		}

		$candidates = WP_Job_Board_Pro_Query::get_posts($query_args, $params);
		return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/candidates', array( 'candidates' => $candidates, 'atts' => $atts ) );
	}
	
	public static function currencies() {
		return WP_Job_Board_Pro_Template_Loader::get_template_part( 'misc/currencies' );
	}
}

WP_Job_Board_Pro_Shortcodes::init();
