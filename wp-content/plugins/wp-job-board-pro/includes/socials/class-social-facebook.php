<?php
/**
 * Social: Facebook
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

/*
 * Import the Facebook SDK and load all the classes
 */
require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/facebook-sdk/autoload.php';

use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Exceptions\FacebookResponseException;


class WP_Job_Board_Pro_Social_Facebook {
	
	private $app_id;

    private $app_secret;

    private $callback_url;

    private $access_token;

    private $facebook_user_datas;

    private $redirect_url;

	private static $_instance = null;

	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {

        $this->app_id = wp_job_board_pro_get_option( 'facebook_api_app_id' );
        $this->app_secret = wp_job_board_pro_get_option( 'facebook_api_app_secret' );

        $this->callback_url = admin_url('admin-ajax.php?action=wp_job_board_pro_facebook_login');
        if ( ($this->is_facebook_login_enabled() || $this->is_facebook_apply_enabled()) && !is_user_logged_in() ) {

            if (!session_id()) {
                session_start();
            }
            // Ajax endpoints.
            add_action( 'wjbp_ajax_wp_job_board_pro_facebook_login', array( $this, 'process_facebook_login' ) );
            
            // compatible handlers.
            add_action( 'wp_ajax_wp_job_board_pro_facebook_login', array( $this, 'process_facebook_login' ) );
            add_action( 'wp_ajax_nopriv_wp_job_board_pro_facebook_login', array( $this, 'process_facebook_login' ) );
            
            add_action( 'wp_job_board_pro_after_facebook_login', array( $this, 'process_apply_job'), 10, 1 );

            add_action( 'login_form', array( $this, 'display_login_btn') );
            if ( $this->is_facebook_apply_enabled() ) {
                add_action( 'wp_job_board_pro_social_apply_btn', array( $this, 'display_apply_btn') );
            }
        }
	}
    
    public function is_facebook_login_enabled() {
        if ( wp_job_board_pro_get_option('enable_facebook_login') && ! empty( $this->app_id ) && ! empty( $this->app_secret ) ) {
            return true;
        }

        return false;
    }

    public function is_facebook_apply_enabled() {
        if ( wp_job_board_pro_get_option('enable_facebook_apply') && ! empty( $this->app_id ) && ! empty( $this->app_secret ) ) {
            return true;
        }

        return false;
    }

	public function process_facebook_login() {
        $fb = $this->init_api();

        $this->access_token = $this->get_token($fb);

        $this->facebook_user_datas = $this->get_user_details($fb);

        // Login the user first
        $this->login_user();

        // Create a new account
        $this->create_user();

        if ( empty($this->redirect_url) ) {
        	$user_dashboard_page_id = wp_job_board_pro_get_option('user_dashboard_page_id');
        	$this->redirect_url = $user_dashboard_page_id > 0 ? get_permalink($user_dashboard_page_id) : home_url('/');
        }

        // Redirect the user
        WP_Job_Board_Pro_Mixes::redirect($this->redirect_url);
    }

    private function init_api() {
        $facebook = new Facebook([
            'app_id' => $this->app_id,
            'app_secret' => $this->app_secret,
            'default_graph_version' => 'v2.10',
            'persistent_data_handler' => 'session'
        ]);

        return $facebook;
    }

    public function get_login_url() {
        $fb = $this->init_api();
        $helper = $fb->getRedirectLoginHelper();

        // Optional permissions
        $permissions = ['email'];

        $url = $helper->getLoginUrl( $this->callback_url, $permissions );

        return esc_url($url);
    }
    
    private function get_token($fb) {
        $msg = '';
        // Assign the Session variable for Facebook
        $_SESSION['FBRLH_state'] = $_GET['state'];

        // Load the Facebook SDK helper
        $helper = $fb->getRedirectLoginHelper();

        // Try to get an access token
        $url = admin_url('admin-ajax.php?action=wp_job_board_pro_facebook_login');
        try {
            $accessToken = $helper->getAccessToken($url);
        } catch (FacebookResponseException $e) {
            $msg = sprintf(__('Graph returned an error: %s', 'wp-job-board-pro'), $e->getMessage());
        } catch (FacebookSDKException $e) {
            $msg = sprintf(__('Facebook SDK returned an error: %s', 'wp-job-board-pro'), $e->getMessage());
        }

        // Check Error
        if (!isset($accessToken)) {
            // Errors Message
            set_transient('wp_job_board_pro_facebook_message', $msg, 60 * 60 * 24 * 30);

            // Redirect
            WP_Job_Board_Pro_Mixes::redirect($this->redirect_url);
        }

        return $accessToken->getValue();
    }

    /**
     * Get user details through the Facebook API
     *
     * @link https://developers.facebook.com/docs/facebook-login/permissions#reference-public_profile
     * @param $fb Facebook
     * @return \Facebook\GraphNodes\GraphUser
     */
    private function get_user_details($fb) {
        $msg = '';
        try {
            $response = $fb->get('/me?fields=id,name,first_name,last_name,email,link', $this->access_token);
        } catch (FacebookResponseException $e) {
            $msg = sprintf(__('Graph returned an error: %s', 'wp-job-board-pro'), $e->getMessage());
        } catch (FacebookSDKException $e) {
            $msg = sprintf(__('Facebook SDK returned an error: %s', 'wp-job-board-pro'), $e->getMessage());
        }

        // If we caught an error
        if (!empty($msg)) {
            // Report our errors
            set_transient('wp_job_board_pro_facebook_message', $msg, 60 * 60 * 24 * 30);

            // Redirect
            WP_Job_Board_Pro_Mixes::redirect($this->redirect_url);
        }

        return $response->getGraphUser();
    }

    private function login_user() {
        $wp_users = get_users(array(
            'number' => 1,
            'count_total' => false,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'wp_job_board_pro_facebook_id',
                    'value' =>  $this->facebook_user_datas['id'],
                    'compare' => "=",
                )
            )
        ));

        if (empty($wp_users[0])) {
            return false;
        }

        $user_login_auth = WP_Job_Board_Pro_User::get_user_status($wp_users[0]);
        if ( $user_login_auth == 'pending' ) {
            $user_data = get_userdata($wp_users[0]);
            $jsondata = array(
                'error' => false,
                'msg' => WP_Job_Board_Pro_User::register_msg($user_data),
            );
            $_SESSION['register_msg'] = $jsondata;
            $login_register_page_id = wp_job_board_pro_get_option('login_register_page_id');
            $redirect_url = $login_register_page_id > 0 ? get_permalink($login_register_page_id) : home_url('/');
            $this->redirect_url = add_query_arg( 'register_msg', $wp_users[0]->ID, $redirect_url );
            // $this->redirect_url = $login_register_page_id > 0 ? get_permalink($login_register_page_id) : home_url('/');
        } elseif ( $user_login_auth == 'denied' ) {
            $jsondata = array(
                'status' => false,
                'msg' => __('Your account is denied', 'wp-job-board-pro')
            );
            $_SESSION['register_msg'] = $jsondata;
            $login_register_page_id = wp_job_board_pro_get_option('login_register_page_id');
            $redirect_url = $login_register_page_id > 0 ? get_permalink($login_register_page_id) : home_url('/');
            $this->redirect_url = add_query_arg( 'register_msg', $wp_users[0]->ID, $redirect_url );
            // $this->redirect_url = $login_register_page_id > 0 ? get_permalink($login_register_page_id) : home_url('/');
        } else {
            wp_set_auth_cookie($wp_users[0]);

            do_action('wp_job_board_pro_after_facebook_login', $wp_users[0]);
        }
        
        WP_Job_Board_Pro_Mixes::redirect($this->redirect_url);
    }

    private function create_user() {
        $facebook_user_data = $this->facebook_user_datas;

        $wp_user = get_user_by('email', $facebook_user_data['email']);
        if ( !empty($wp_user->ID) ) {
            update_user_meta($wp_user->ID, 'wp_job_board_pro_facebook_id', $facebook_user_data['id']);
            $this->login_user();
        }

        // Username
        $username = sanitize_user(str_replace(' ', '_', strtolower($this->facebook_user_datas['name'])));

        if (username_exists($username)) {
            $username .= '_' . rand(10000, 99999);
        }

        // New user data
        $userdata = array(
	        'user_login' => sanitize_user( $username ),
	        'user_email' => sanitize_email( $facebook_user_data['email'] ),
	        'user_pass' => wp_generate_password(),
	        'role' => 'wp_job_board_pro_candidate',
        );
        $userdata = apply_filters('wp-job-board-pro-facebook-login-userdata', $userdata, $facebook_user_data);

        $_POST['role'] = 'wp_job_board_pro_candidate';
        $_POST['action'] = 'wp_job_board_pro_ajax_register';
        
        global $wp_job_board_pro_socials_register;
        $wp_job_board_pro_socials_register = $facebook_user_data['id'];

        $user_id = wp_insert_user( $userdata );
        if ( !is_wp_error( $user_id ) ) {
            update_user_meta($user_id, 'first_name', $facebook_user_data['first_name']);
            update_user_meta($user_id, 'last_name', $facebook_user_data['last_name']);
            update_user_meta($user_id, 'user_url', $facebook_user_data['link']);
            update_user_meta($user_id, 'wp_job_board_pro_facebook_id', $facebook_user_data['id']);
            
            $user_picture_obj = isset($facebook_user_data['picture']) ? $facebook_user_data['picture'] : '';
            $user_picture = '';
            if (isset($user_picture_obj['url'])) {
                $candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
                WP_Job_Board_Pro_Image::upload_attach_with_external_url($user_picture_obj['url'], $candidate_id);
            }
            
        	do_action('wp_job_board_pro_after_facebook_login', $user_id);

			wp_set_auth_cookie($user_id);
            
        } else {
	        set_transient('wp_job_board_pro_facebook_message', $user_id->get_error_message(), 60 * 60 * 24 * 30);
            echo $user_id->get_error_message();
            die;
	    }
    }

    public function process_apply_job($user_id) {
        if ( isset( $_COOKIE['wp_job_board_pro_facebook_job_id'] ) && $_COOKIE['wp_job_board_pro_facebook_job_id'] > 0 ) {
            $job_id = $_COOKIE['wp_job_board_pro_facebook_job_id'];
            $job = get_post($job_id);
            
            if ( WP_Job_Board_Pro_User::is_candidate( $user_id ) ) {
                WP_Job_Board_Pro_Candidate::insert_applicant($user_id, $job);
            }

            $this->redirect_url = get_permalink($job_id);

            unset($_COOKIE['wp_job_board_pro_facebook_job_id']);
            setcookie('wp_job_board_pro_facebook_job_id', null, -1, '/');
        }
    }

    public function display_message() {
        if ( get_transient('wp_job_board_pro_facebook_message') ) {
            $message = get_transient('wp_job_board_pro_facebook_message');
            echo '<div class="alert alert-danger facebook-message">' . $message . '</div>';
            delete_transient('wp_job_board_pro_facebook_message');
        }
    }

    public function display_login_btn() {
        if ( is_user_logged_in() ) {
            return;
        }
    	ob_start();
        $this->display_message();
    	?>
    	<div class="facebook-login-btn-wrapper">
    		<a class="facebook-login-btn" href="<?php echo esc_url($this->get_login_url()); ?>"><i class="fab fa-facebook-f"></i> <?php esc_html_e('Facebook', 'wp-job-board-pro'); ?></a>
    	</div>
    	<?php
    	$output = ob_get_clean();
    	echo apply_filters('wp-job-board-pro-facebook-login-btn', $output, $this);
    }

	public function display_apply_btn($job) {
        if ( !WP_Job_Board_Pro_Job_Listing::check_can_apply_social($job->ID) ) {
            return;
        }
    	ob_start();
        $this->display_message();
    	?>
    	<div class="facebook-apply-btn-wrapper">
    		<a class="facebook-apply-btn" href="<?php echo esc_url($this->get_login_url()); ?>" data-job_id="<?php echo esc_attr($job->ID); ?>"><i class="fab fa-facebook-f"></i> <?php esc_html_e('Facebook', 'wp-job-board-pro'); ?></a>
    	</div>
    	<?php
    	$output = ob_get_clean();
    	echo apply_filters('wp-job-board-pro-facebook-apply-btn', $output, $this, $job);
    }

}

function wp_job_board_pro_social_facebook() {
    WP_Job_Board_Pro_Social_Facebook::get_instance();
}
add_action( 'init', 'wp_job_board_pro_social_facebook' );