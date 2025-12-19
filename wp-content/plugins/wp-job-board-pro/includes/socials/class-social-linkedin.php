<?php
/**
 * Social: LinkedIn
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Job_Board_Pro_Social_Linkedin {
    
    public $linkedin_api_key;

    public $linkedin_secret_key;

    public $access_token;

    public $oauth;

    private $redirect_url = '';

    private $linkedin_user_datas;

    private $linkedin_user_email_datas;

    public $linkedin_options;

    private static $_instance = null;

    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->linkedin_api_key = wp_job_board_pro_get_option( 'linkedin_api_client_id' );
        $this->linkedin_secret_key = wp_job_board_pro_get_option( 'linkedin_api_client_secret' );

        if ( ($this->is_linkedin_login_enabled() || $this->is_linkedin_apply_enabled()) && !is_user_logged_in() ) {

            if (!session_id()) {
                session_start();
            }
            $this->linkedin_options = array(
                'li_cancel_redirect_url' => '',
                'li_redirect_url' => '',
                'li_auto_profile_update' => '',
                'li_registration_redirect_url' => '',
                'li_logged_in_message' => '',
            );

            require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/linkedin/linkedin_oauth2.class.php';

            // Create new Oauth client
            $this->oauth = new WP_Job_Board_Pro_OAuth2Client($this->linkedin_api_key, $this->linkedin_secret_key);

            // Set Oauth URLs
            $this->oauth->redirect_uri = home_url('/') . '?action=wp_job_board_pro_linkedin_login';
            $this->oauth->authorize_url = 'https://www.linkedin.com/uas/oauth2/authorization';
            $this->oauth->token_url = 'https://www.linkedin.com/uas/oauth2/accessToken';
            $this->oauth->api_base_url = 'https://api.linkedin.com/v2/me';

            // Set user token if user is logged in
            if (get_current_user_id()) {
                $this->oauth->access_token = get_user_meta(get_current_user_id(), 'wp_job_board_pro_access_token', true);
            }

            $this->process_linkedin_login();

            if ( $this->is_linkedin_login_enabled() ) {
                add_action( 'login_form', array( $this, 'display_login_btn') );
            }
            if ( $this->is_linkedin_apply_enabled() ) {
                add_action( 'wp_job_board_pro_social_apply_btn', array( $this, 'display_apply_btn') );
            }
            
            // Start session
            if (!session_id()) {
                session_start();
            }
        }
    }

    public function is_linkedin_login_enabled() {
        if ( wp_job_board_pro_get_option('enable_linkedin_login') && ! empty( $this->linkedin_api_key ) && ! empty( $this->linkedin_secret_key ) ) {
            return true;
        }

        return false;
    }

    public function is_linkedin_apply_enabled() {
        if ( wp_job_board_pro_get_option('enable_linkedin_apply') && ! empty( $this->linkedin_api_key ) && ! empty( $this->linkedin_secret_key ) ) {
            return true;
        }

        return false;
    }

    public function process_linkedin_login() {
        // If this is not a linkedin sign-in request, do nothing
        if (!$this->is_linkedin_signin()) {
            return;
        }

        // If this is a user sign-in request, but the user denied granting access, redirect to login URL
        if (isset($_REQUEST['error']) && $_REQUEST['error'] == 'access_denied') {

            // Get our cancel redirect URL
            $cancel_redirect_url = $this->linkedin_options['li_cancel_redirect_url'];

            // Redirect to login URL if left blank
            if (empty($cancel_redirect_url)) {
                wp_redirect(home_url('/'));
            }

            // Redirect to our given URL
            wp_safe_redirect($cancel_redirect_url);
        }

        $scope_type = wp_job_board_pro_get_option('linkedin_login_type', '');

        // Another error occurred, create an error log entry
        if (isset($_REQUEST['error'])) {
            $msg = sprintf(__('LinkedIn login error: %s %s', 'wp-job-board-pro'), $_REQUEST['error'], $_REQUEST['error_description']);

            set_transient('wp_job_board_pro_linkedin_message', $msg, 60 * 60 * 24 * 30);
        }

        $xml = $this->get_linkedin_profile();
        $xml = json_decode($xml, true);

        if ($scope_type == 'openid') {
            if (!is_array($xml) || !isset($xml['given_name'])) {
                return false;
            }
        } else {
            if (!is_array($xml) || !isset($xml['id'])) {
                return false;
            }
        }

        $this->linkedin_user_datas = $xml;

        
        $email_xml = $this->get_linkedin_profile_email();
        $email_xml = json_decode($email_xml, true);

        $this->linkedin_user_email_datas = $email_xml;

        // Login the user first
        $this->login_user();

        // Create a new account
        $this->create_user();
        
        if ( empty($this->redirect_url) ) {
            $user_dashboard_page_id = wp_job_board_pro_get_option('user_dashboard_page_id');
            $this->redirect_url = $user_dashboard_page_id > 0 ? get_permalink($user_dashboard_page_id) : home_url('/');
        }

        WP_Job_Board_Pro_Mixes::redirect($this->redirect_url);
    }

    private function is_linkedin_signin() {

        // If no action is requested or the action is not ours
        if (!isset($_REQUEST['action']) || ($_REQUEST['action'] != "wp_job_board_pro_linkedin_login")) {
            return false;
        }

        // If a code is not returned, and no error as well, then OAuth did not proceed properly
        if (!isset($_REQUEST['code']) && !isset($_REQUEST['error'])) {
            return false;
        }

        // This is a LinkedIn signing-request - unset state and return true
        unset($_SESSION['li_api_state']);

        return true;
    }

    private function get_linkedin_profile() {

        global $wp_job_board_pro_openid_userprofile_data;

        $scope_type = wp_job_board_pro_get_option('linkedin_login_type', '');
        // Use GET method since POST isn't working
        $this->oauth->curl_authenticate_method = 'GET';

        // Request access token
        $response = $this->oauth->authenticate($_REQUEST['code']);
        if ($response) {
            $this->access_token = $response->{'access_token'};
        }

        if ($scope_type == 'openid') {
            $firstLastName = wp_remote_get('https://api.linkedin.com/v2/userinfo', array(
                    'method' => 'GET',
                    'timeout' => 15,
                    'headers' => array('Authorization' => "Bearer ".$response->access_token),
                )
            );
            if(!is_wp_error($firstLastName) && isset($firstLastName['response']['code']) && 200 === $firstLastName['response']['code']){
                $xml = wp_remote_retrieve_body($firstLastName);
                $wp_job_board_pro_openid_userprofile_data = $xml;
            }
        } else {
            // Get first name, last name and email address, and load 
            // response into XML object
            $xml = ($this->oauth->get('https://api.linkedin.com/v2/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))'));
        }

        return $xml;
    }

    private function get_linkedin_profile_email() {

        global $wp_job_board_pro_openid_userprofile_data;
        $scope_type = wp_job_board_pro_get_option('linkedin_login_type', '');

        // Use GET method since POST isn't working
        $this->oauth->curl_authenticate_method = 'GET';

        // Get first name, last name and email address, and load 
        // response into XML object

        if ($scope_type == 'openid') {
            $xml = $wp_job_board_pro_openid_userprofile_data;
        } else {
            $xml = ($this->oauth->get('https://api.linkedin.com/v2/clientAwareMemberHandles?q=members&projection=(elements*(primary,type,handle~))'));
        }

        return $xml;
    }

    private function login_user() {
        $scope_type = wp_job_board_pro_get_option('linkedin_login_type', '');

        $linkedin_user_data = $this->linkedin_user_datas;
        if ($scope_type == 'openid') {
            $user_id = isset($linkedin_user_data['email']) ? $linkedin_user_data['email'] : '';
        } else {
            $user_id = isset($linkedin_user_data['id']) ? $linkedin_user_data['id'] : '';
        }

        // We look for the `eo_linkedin_id` to see if there is any match
        $wp_users = get_users(array(
            'number' => 1,
            'count_total' => false,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'wp_job_board_pro_linkedin_id',
                    'value' => $user_id,
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

            // apply job
            $this->process_apply_job($wp_users[0]);

            do_action('wp_job_board_pro_after_linkedin_login', $wp_users[0]);
        }

        WP_Job_Board_Pro_Mixes::redirect($this->redirect_url);
    }

    private function create_user() {
        $scope_type = wp_job_board_pro_get_option('linkedin_login_type', '');

        $linkedin_user_data = $this->linkedin_user_datas;
        $linkedin_user_email = $this->linkedin_user_email_datas;
        
        $pitcure_url    = '';

        if ($scope_type == 'openid') {
            $pitcure_url = isset($linkedin_user_data['picture']) ? $linkedin_user_data['picture'] : '';
        } else {
            $img_array = isset($linkedin_user_email['profilePicture']['displayImage~']['elements']) ? $linkedin_user_email['profilePicture']['displayImage~']['elements'] : '';
            $pitcure_url = isset($img_array[3]['identifiers'][0]['identifier']) ? $img_array[3]['identifiers'][0]['identifier'] : '';
            if ($pitcure_url == '') {
                $pitcure_url = isset($img_array[2]['identifiers'][0]['identifier']) ? $img_array[3]['identifiers'][0]['identifier'] : '';
            }
            if ($pitcure_url == '') {
                $pitcure_url = isset($img_array[1]['identifiers'][0]['identifier']) ? $img_array[3]['identifiers'][0]['identifier'] : '';
            }
            if ($pitcure_url == '') {
                $pitcure_url = isset($img_array[0]['identifiers'][0]['identifier']) ? $img_array[3]['identifiers'][0]['identifier'] : '';
            }
        }

        if (!empty($pitcure_url)) {
            $pitcure_url = esc_url_raw($pitcure_url);

            $is_valid_domain = $this->is_valid_domain($pitcure_url);
            if (!$is_valid_domain) {
                $pitcure_url = '';
            }            
        }

        if ($scope_type == 'openid') {
            $linkedin_user_id = isset($linkedin_user_data['email']) ? $linkedin_user_data['email'] : '';
        } else {
            $linkedin_user_id = isset($linkedin_user_data['id']) ? $linkedin_user_data['id'] : '';
        }

        $first_name = $last_name = '';
        if ($scope_type == 'openid') {
            $first_name = isset($linkedin_user_data['given_name']) ? $linkedin_user_data['given_name'] : '';
            $last_name = isset($linkedin_user_data['family_name']) ? $linkedin_user_data['family_name'] : '';
            $email = isset($linkedin_user_data['email']) ? $linkedin_user_data['email'] : '';
        } else {
            if (!empty($linkedin_user_data['firstName']['localized'])) {
                foreach ($linkedin_user_data['firstName']['localized'] as $value) {
                    $first_name = $value;
                }
            }
            if (!empty($linkedin_user_data['lastName']['localized'])) {
                foreach ($linkedin_user_data['lastName']['localized'] as $value) {
                    $last_name = $value;
                }
            }

            $email = isset($linkedin_user_email['elements'][0]['handle~']['emailAddress']) ? $linkedin_user_email['elements'][0]['handle~']['emailAddress'] : '';
        }

        $wp_user = get_user_by('email', $email);
        if ( !empty($wp_user->ID) ) {
            update_user_meta($wp_user->ID, 'wp_job_board_pro_linkedin_id', $linkedin_user_id);
            $this->login_user();
        }

        if ( !empty($first_name) && !empty($last_name) ) {
            $name = $first_name . '_' . $last_name;
            $name = str_replace(array(' '), array('_'), $name);
            $username = sanitize_user(str_replace(' ', '_', strtolower($name)));
        } else {
            $username = $email;
        }

        if ( username_exists($username) ) {
            $username .= '_' . rand(100000, 999999);
        }

        // New user data
        $userdata = array(
            'user_login' => sanitize_user( $username ),
            'user_email' => sanitize_email( $email ),
            'user_pass' => wp_generate_password(),
            'role' => 'wp_job_board_pro_candidate',
        );
        $userdata = apply_filters('wp-job-board-pro-linkedin-login-userdata', $userdata, $linkedin_user_data);

        $_POST['role'] = 'wp_job_board_pro_candidate';
        $_POST['action'] = 'wp_job_board_pro_ajax_register';

        global $wp_job_board_pro_socials_register;
        $wp_job_board_pro_socials_register = $linkedin_user_id;

        $user_id = wp_insert_user( $userdata );
        if ( !is_wp_error( $user_id ) ) {
            
            update_user_meta($user_id, 'first_name', $first_name);
            update_user_meta($user_id, 'last_name', $last_name);
            update_user_meta($user_id, 'wp_job_board_pro_linkedin_id', $linkedin_user_id);
            update_user_meta($user_id, 'wp_job_board_pro_access_token', $this->access_token, true);
            
            if ($pitcure_url != '') {
                $candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($user_id);
                WP_Job_Board_Pro_Image::upload_attach_with_external_url($pitcure_url, $candidate_id);
            }

            // apply job
            $this->process_apply_job($user_id);

            do_action('wp_job_board_pro_after_linkedin_login', $user_id);

            wp_set_auth_cookie($user_id);
            
        } else {
            set_transient('wp_job_board_pro_linkedin_message', $user_id->get_error_message(), 60 * 60 * 24 * 30);
            echo $user_id->get_error_message();
            die;
        }
    }

    public function is_valid_domain($url) {
        $url = (false === strpos($url, '://')) ? 'http://' . $url : $url;    
        $headers = @get_headers($url);    
        return $headers && isset($headers[0]) && strpos($headers[0], '200') !== false;
    }

    public function process_apply_job($user_id) {
        if ( isset( $_COOKIE['wp_job_board_pro_linkedin_job_id'] ) && $_COOKIE['wp_job_board_pro_linkedin_job_id'] > 0 ) {
            $job_id = $_COOKIE['wp_job_board_pro_linkedin_job_id'];
            $job = get_post($job_id);

            if ( WP_Job_Board_Pro_User::is_candidate( $user_id ) ) {
                WP_Job_Board_Pro_Candidate::insert_applicant($user_id, $job);
            }

            $this->redirect_url = get_permalink($job_id);

            unset($_COOKIE['wp_job_board_pro_linkedin_job_id']);
            setcookie('wp_job_board_pro_linkedin_job_id', null, -1, '/');
        }
    }

    public function get_login_url($redirect = false) {
        $state = wp_generate_password(12, false);
        $scope_type = wp_job_board_pro_get_option('linkedin_login_type', '');

        $scope_str = 'r_liteprofile r_emailaddress';
        if ($scope_type == 'openid') {
            $scope_str = 'openid,profile,email';
        }

        $authorize_url = $this->oauth->authorizeUrl(array('scope' => $scope_str, 'state' => $state));

        if (!isset($_SESSION['li_api_state'])) {
            $_SESSION['li_api_state'] = $state;
        }

        $_SESSION['li_api_redirect'] = $redirect;

        return $authorize_url;
    }

    public function display_message() {
        if ( get_transient('wp_job_board_pro_linkedin_message') ) {
            $message = get_transient('wp_job_board_pro_linkedin_message');
            echo '<div class="alert alert-danger linkedin-message">' . $message . '</div>';
            delete_transient('wp_job_board_pro_linkedin_message');
        }
    }

    public function display_login_btn() {
        if ( is_user_logged_in() ) {
            return;
        }
        ob_start();
        $this->display_message();
        ?>
        <div class="linkedin-login-btn-wrapper">
            <a class="linkedin-login-btn" href="<?php echo esc_url($this->get_login_url()); ?>"><i class="fab fa-linkedin-in"></i> <?php esc_html_e('LinkedIn', 'wp-job-board-pro'); ?></a>
        </div>
        <?php
        $output = ob_get_clean();
        echo apply_filters('wp-job-board-pro-linkedin-login-btn', $output, $this);
    }

    public function display_apply_btn($job) {
        if ( !WP_Job_Board_Pro_Job_Listing::check_can_apply_social($job->ID) ) {
            return;
        }
        ob_start();
        $this->display_message();
        ?>
        <div class="linkedin-apply-btn-wrapper">
            <a class="linkedin-apply-btn" href="<?php echo esc_url($this->get_login_url()); ?>" data-job_id="<?php echo esc_attr($job->ID); ?>"><i class="fab fa-linkedin-in"></i> <?php esc_html_e('LinkedIn', 'wp-job-board-pro'); ?></a>
        </div>
        <?php
        $output = ob_get_clean();
        echo apply_filters('wp-job-board-pro-linkedin-apply-btn', $output, $this, $job);
    }

}


function wp_job_board_pro_social_linkedin() {
    WP_Job_Board_Pro_Social_Linkedin::get_instance();
}
add_action( 'init', 'wp_job_board_pro_social_linkedin' );