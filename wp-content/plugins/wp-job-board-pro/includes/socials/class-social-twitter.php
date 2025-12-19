<?php
/**
 * Social: Twitter
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Job_Board_Pro_Social_Twitter {
    
    private $consumer_key;
    private $consumer_secret;
    private $access_token;
    private $token_secret;
    private $redirect_url;
    
    private $twitter_user_datas;

    private static $_instance = null;

    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {

        $this->consumer_key = wp_job_board_pro_get_option( 'twitter_api_consumer_key' );
        $this->consumer_secret = wp_job_board_pro_get_option( 'twitter_api_consumer_secret' );
        $this->access_token = wp_job_board_pro_get_option( 'twitter_api_access_token' );
        $this->token_secret = wp_job_board_pro_get_option( 'twitter_api_token_secret' );

        if ( $this->is_twitter_login_enabled() || $this->is_twitter_apply_enabled() ) {
            $user_dashboard_page_id = wp_job_board_pro_get_option('user_dashboard_page_id');
            $this->redirect_url = $user_dashboard_page_id > 0 ? get_permalink($user_dashboard_page_id) : home_url('/');

            // Ajax endpoints.
            add_action('wjbp_ajax_wp_job_board_pro_twitter', array($this, 'twitter_connect'));

            // compatible handlers.
            add_action('wp_ajax_wp_job_board_pro_twitter', array($this, 'twitter_connect'));
            add_action('wp_ajax_nopriv_wp_job_board_pro_twitter', array($this, 'twitter_connect'));

            add_action( 'wp_job_board_pro_after_twitter_login', array( $this, 'process_apply_job'), 10, 1 );

            add_action( 'login_form', array( $this, 'display_login_btn') );

            if ( $this->is_twitter_apply_enabled() ) {
                add_action( 'wp_job_board_pro_social_apply_btn', array( $this, 'display_apply_btn') );
            }
            
            if ( isset($_GET['oauth_token']) && $_GET['oauth_token'] != '' ) {
                $this->process_twitter_login();
            }
        }
    }

    public function is_twitter_login_enabled() {
        if ( wp_job_board_pro_get_option('enable_twitter_login') && ! empty( $this->consumer_key ) && ! empty( $this->consumer_secret ) ) {
            return true;
        }

        return false;
    }

    public function is_twitter_apply_enabled() {
        if ( wp_job_board_pro_get_option('enable_twitter_apply') && ! empty( $this->consumer_key ) && ! empty( $this->consumer_secret ) ) {
            return true;
        }

        return false;
    }

    public function twitter_connect() {
        if ( !class_exists('TwitterOAuth') ) {
            require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/twitter/twitteroauth.php';
        }
        $consumer_key = $this->consumer_key;
        $consumer_secret = $this->consumer_secret;
        $twitter_oath_callback = home_url('/');
        if ( !empty($consumer_key) && !empty($consumer_secret) ) {

            $connection = new TwitterOAuth($consumer_key, $consumer_secret);
            $request_token = $connection->getRequestToken($twitter_oath_callback);

            if (!empty($request_token)) {
                set_transient('wp_job_board_pro_oauth_token', $request_token['oauth_token'], (60 * 60 * 24));
                set_transient('wp_job_board_pro_oauth_token_secret', $request_token['oauth_token_secret'], (60 * 60 * 24));
                $token = $request_token['oauth_token'];
            }

            switch ($connection->http_code) {
                case 200:
                    wp_redirect($connection->getAuthorizeURL($token));
                    break;
                default:
                    echo sprintf(__('%s There is problem while connecting to twitter', 'wp-job-board-pro'), $connection->http_code);
            }
            exit();
        }
        wp_die();
    }

    public function process_twitter_login() {
        if (!class_exists('TwitterOAuth')) {
            require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/twitter/twitteroauth.php';
        }
        $consumer_key = $this->consumer_key;
        $consumer_secret = $this->consumer_secret;

        $oauth_token = get_transient('wp_job_board_pro_oauth_token');
        $oauth_token_secret = get_transient('wp_job_board_pro_oauth_token_secret');
        if (!empty($oauth_token) && !empty($oauth_token_secret)) {
            $connection = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
            $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
            set_transient('wp_job_board_pro_access_token', $access_token, (3600 * 60) * 24);
            delete_transient('wp_job_board_pro_oauth_token');
            delete_transient('wp_job_board_pro_oauth_token_secret');
        }

        if ($connection->http_code != 200) {
            set_transient('wp_job_board_pro_twitter_message', __('There is problem while connecting to twitter', 'wp-job-board-pro'), 60 * 60 * 24 * 30);

            $login_register_page_id = wp_job_board_pro_get_option('login_register_page_id');
            $this->redirect_url = $login_register_page_id > 0 ? get_permalink($login_register_page_id) : home_url('/');

        } else {
            $params = array('include_email' => 'true', 'include_entities' => 'false', 'skip_status' => 'true');
            
            $user = $connection->get('account/verify_credentials',$params);
            
            $this->twitter_user_datas = $user;

            // Login the user first
            $this->login_user();

            // Create a new account
            $this->create_user();
        }
        WP_Job_Board_Pro_Mixes::redirect($this->redirect_url);
        wp_die();
    }

    private function login_user() {
        $wp_users = get_users(array(
            'number' => 1,
            'count_total' => false,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'wp_job_board_pro_twitter_id',
                    'value' => $this->twitter_user_datas->id,
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
            $this->redirect_url = $login_register_page_id > 0 ? get_permalink($login_register_page_id) : home_url('/');
        } elseif ( $user_login_auth == 'denied' ) {
            $jsondata = array(
                'status' => false,
                'msg' => __('Your account is denied', 'wp-job-board-pro')
            );
            $_SESSION['register_msg'] = $jsondata;
            $login_register_page_id = wp_job_board_pro_get_option('login_register_page_id');
            $this->redirect_url = $login_register_page_id > 0 ? get_permalink($login_register_page_id) : home_url('/');
        } else {
            wp_set_auth_cookie($wp_users[0]);
            do_action('wp_job_board_pro_after_twitter_login', $wp_users[0]);
        }

        WP_Job_Board_Pro_Mixes::redirect($this->redirect_url);
    }

    private function create_user() {

        $twitter_user_data = $this->twitter_user_datas;
        $twitter_user_id = $twitter_user_data->id;
        
        $site_url = parse_url(site_url());
        $user_email = 'twitter_' . md5($twitter_user_data->id) . '@' . $site_url['host'];

        if (isset($twitter_user_data->email)) {
            $user_email = $twitter_user_data->email;

            $wp_user = get_user_by('email', $user_email);
            if ( !empty($wp_user->ID) ) {
                update_user_meta($wp_user->ID, 'wp_job_board_pro_twitter_id', $twitter_user_id);
                $this->login_user();
            }
        }

        // Username
        $username = sanitize_user(str_replace(' ', '_', strtolower($twitter_user_data->name)));

        if (username_exists($username)) {
            $username .= '_' . rand(10000, 99999);
        }

        // New user data
        $userdata = array(
            'user_login' => sanitize_user( $username ),
            'user_email' => sanitize_email( $email ),
            'user_pass' => wp_generate_password(),
            'role' => 'wp_job_board_pro_candidate',
        );
        $userdata = apply_filters('wp-job-board-pro-twitter-login-userdata', $userdata, $twitter_user_data);

        $_POST['role'] = 'wp_job_board_pro_candidate';
        $_POST['action'] = 'wp_job_board_pro_ajax_register';
        global $wp_job_board_pro_socials_register;
        $wp_job_board_pro_socials_register = $twitter_user_id;

        $user_id = wp_insert_user( $userdata );
        if ( ! is_wp_error( $user_id ) ) {
            
            $first_name = isset($twitter_user_data->first_name) ? $twitter_user_data->first_name : '';
            $last_name = isset($twitter_user_data->last_name) ? $twitter_user_data->last_name : '';

            update_user_meta($user_id, 'first_name', $first_name);
            update_user_meta($user_id, 'last_name', $last_name);
            update_user_meta($user_id, 'wp_job_board_pro_twitter_id', $twitter_user_id);
            
            do_action('wp_job_board_pro_after_twitter_login', $user_id);

            wp_set_auth_cookie($user_id);
            
        } else {
            set_transient('wp_job_board_pro_twitter_message', $user_id->get_error_message(), 60 * 60 * 24 * 30);
            echo $user_id->get_error_message();
            die;
        }
    }


    public function process_apply_job($user_id) {
        if ( isset( $_COOKIE['wp_job_board_pro_twitter_job_id'] ) && $_COOKIE['wp_job_board_pro_twitter_job_id'] > 0 ) {
            $job_id = $_COOKIE['wp_job_board_pro_twitter_job_id'];
            $job = get_post($job_id);

            if ( WP_Job_Board_Pro_User::is_candidate( $user_id ) ) {
                WP_Job_Board_Pro_Candidate::insert_applicant($user_id, $job);
            }

            $this->redirect_url = get_permalink($job_id);

            unset($_COOKIE['wp_job_board_pro_twitter_job_id']);
            setcookie('wp_job_board_pro_twitter_job_id', null, -1, '/');
        }
    }

    public static function get_login_url() {
        return admin_url('admin-ajax.php?action=wp_job_board_pro_twitter');
    }

    public function display_message() {
        if ( get_transient('wp_job_board_pro_twitter_message') ) {
            $message = get_transient('wp_job_board_pro_twitter_message');
            echo '<div class="alert alert-danger twitter-message">' . $message . '</div>';
            delete_transient('wp_job_board_pro_twitter_message');
        }
    }

    public function display_login_btn() {
        if ( is_user_logged_in() ) {
            return;
        }
        ob_start();
        $this->display_message();
        ?>
        <div class="twitter-login-btn-wrapper">
            <a class="twitter-login-btn" href="<?php echo esc_url($this->get_login_url()); ?>"><i class="fab fa-x-twitter"></i> <?php esc_html_e('Twitter', 'wp-job-board-pro'); ?></a>
        </div>
        <?php
        $output = ob_get_clean();
        echo apply_filters('wp-job-board-pro-twitter-login-btn', $output, $this);
    }

    public function display_apply_btn($job) {
        if ( !WP_Job_Board_Pro_Job_Listing::check_can_apply_social($job->ID) ) {
            return;
        }
        ob_start();
        $this->display_message();
        ?>
        <div class="twitter-apply-btn-wrapper">
            <a class="twitter-apply-btn" href="<?php echo esc_url($this->get_login_url()); ?>" data-job_id="<?php echo esc_attr($job->ID); ?>"><i class="fab fa-twitter"></i> <?php esc_html_e('Twitter', 'wp-job-board-pro'); ?></a>
        </div>
        <?php
        $output = ob_get_clean();
        echo apply_filters('wp-job-board-pro-twitter-apply-btn', $output, $this);
    }

}

function wp_job_board_pro_social_twitter() {
    WP_Job_Board_Pro_Social_Twitter::get_instance();
}
add_action( 'init', 'wp_job_board_pro_social_twitter' );