<?php
/**
 * Meeting Zoom
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Meeting_Zoom {

	public static function init() {
        add_action( 'wjbp_ajax_wp_job_board_pro_ajax_zoom_settings',  array(__CLASS__, 'process_zoom_settings') );
        
        add_action('wp', array(__CLASS__, 'get_access_token_zoom'));
        
        add_action('wp', array(__CLASS__, 'user_reset_zoom_access_token'), 20);
    }
    
    public static function process_zoom_settings() {
		if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp-job-board-pro-zoom-meeting-nonce' )  ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Your nonce did not verify.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}

		if ( !is_user_logged_in() || !WP_Job_Board_Pro_User::is_employer() ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Please login to set zoom meeting API.', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}

		$email = sanitize_text_field(!empty($_POST['email']) ? $_POST['email'] : '');
		$client_id = sanitize_text_field(!empty($_POST['client_id']) ? $_POST['client_id'] : '');
		$client_secret = sanitize_text_field(!empty($_POST['client_secret']) ? $_POST['client_secret'] : '');

		if ( empty($email) || empty($client_id) || empty($client_secret) ) {
			$return = array( 'status' => false, 'msg' => esc_html__('Fill all fields', 'wp-job-board-pro') );
		   	echo wp_json_encode($return);
		   	exit;
		}

		$user_id = WP_Job_Board_Pro_User::get_user_id();
		$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);

		do_action('wp-job-board-pro-before-set-zoom-meeting-api');

        // update
        WP_Job_Board_Pro_Employer::update_post_meta($employer_id, 'zoom_email', $email);
        WP_Job_Board_Pro_Employer::update_post_meta($employer_id, 'zoom_client_id', $client_id);
        WP_Job_Board_Pro_Employer::update_post_meta($employer_id, 'zoom_client_secret', $client_secret);

        $state = base64_encode('zoom_auth_state');
        $redirect_uri = home_url('/');
        
        $html = '';
        if ($client_id != '') {
            ob_start();
            ?>
            <script>
                var zoom_auth_win = window.open('https://zoom.us/oauth/authorize?response_type=code&state=<?php echo esc_attr($state); ?>&client_id=<?php echo esc_attr($client_id); ?>&redirect_uri=<?php echo ($redirect_uri) ?>',
                        '', 'scrollbars=no,menubar=no,resizable=yes,toolbar=no,status=no,width=800, height=400');
                var auth_window_timer = setInterval(function () {
                    if (jQuery.isEmptyObject( zoom_auth_win) && zoom_auth_win.closed) {
                        clearInterval(auth_window_timer);
                        window.location.reload();
                    }
                }, 500);
            </script>
            <?php
            $html = ob_get_clean();
        }
        
        wp_send_json(array('html' => $html));


	}

    public static function access_token_code_curl($code) {

        $user_id = WP_Job_Board_Pro_User::get_user_id();
        $employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
        
        $client_id = WP_Job_Board_Pro_Employer::get_post_meta($employer_id, 'zoom_client_id', true);
        $client_secret = WP_Job_Board_Pro_Employer::get_post_meta($employer_id, 'zoom_client_secret', true);

        $data = array(
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => home_url('/'),
        );
        $data_str = http_build_query($data);

        $url = 'https://zoom.us/oauth/token';
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, 1);
        // make sure we are POSTing
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_str);
        // allow us to use the returned data from the request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //we are sending json
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret),
        ));

        $result_token = curl_exec($ch);
        curl_close($ch);

        return $result_token;
    }

    public static function refresh_token_code_curl($refresh_token) {

        $user_id = WP_Job_Board_Pro_User::get_user_id();
        $employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
        
        $client_id = WP_Job_Board_Pro_Employer::get_post_meta($employer_id, 'zoom_client_id', true);
        $client_secret = WP_Job_Board_Pro_Employer::get_post_meta($employer_id, 'zoom_client_secret', true);

        $data = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
            'redirect_uri' => home_url('/'),
        );
        $data_str = http_build_query($data);

        $url = 'https://zoom.us/oauth/token';
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, 1);
        // make sure we are POSTing
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_str);
        // allow us to use the returned data from the request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //we are sending json
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret),
        ));

        $result_token = curl_exec($ch);
        curl_close($ch);

        return $result_token;
    }
    
    public static function get_access_token_zoom() {
        $state = base64_encode('zoom_auth_state');
        if (isset($_GET['state']) && $_GET['state'] == $state && isset($_GET['code']) && $_GET['code'] != '') {

            $user_id = WP_Job_Board_Pro_User::get_user_id();
            $employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);

            $code = $_GET['code'];

            $result_token = self::access_token_code_curl($code);

            $result_token = json_decode($result_token, true);

            if (isset($result_token['access_token']) && $result_token['access_token'] != '') {
                $refresh_token = isset($result_token['refresh_token']) ? $result_token['refresh_token'] : '';
                WP_Job_Board_Pro_Employer::update_post_meta($employer_id, 'zoom_refresh_token', $refresh_token);
                $access_token = $result_token['access_token'];
                set_transient('wjbp_zoom_access_token_' . $user_id, $access_token, 900);
                echo '<script>window.close();</script>';
                die;
            }
        }
    }
    
    public static function user_zoom_access_token($user_id) {

        $check_transient = get_transient('wjbp_zoom_access_token_' . $user_id);
        if (!empty($check_transient)) {
            $access_token = $check_transient;
            return $access_token;
        }
    }
    
    public static function user_reset_zoom_access_token() {
        if (is_user_logged_in()) {
            $user_id = WP_Job_Board_Pro_User::get_user_id();
            $employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
            
            if ($employer_id > 0) {
                $user_refresh_token = WP_Job_Board_Pro_Employer::get_post_meta($employer_id, 'zoom_refresh_token', true);
                if ($user_refresh_token != '') {
                    $result_token = self::refresh_token_code_curl($user_refresh_token);
                    $result_token = json_decode($result_token, true);
                    if (isset($result_token['access_token']) && $result_token['access_token'] != '') {
                        $refresh_token = isset($result_token['refresh_token']) ? $result_token['refresh_token'] : '';
                        WP_Job_Board_Pro_Employer::update_post_meta($employer_id, 'zoom_refresh_token', $refresh_token);
                        $access_token = $result_token['access_token'];
                        set_transient('wjbp_zoom_access_token_' . $user_id, $access_token, 900);
                        return $access_token;
                    } else {
                        WP_Job_Board_Pro_Employer::update_post_meta($employer_id, 'zoom_refresh_token', '');
                    }
                }
            }
        }
    }
    
    public static function reset_zoom_access_token_byid($user_id) {
        
        $employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($user_id);
        
        $access_token = self::user_zoom_access_token($user_id);
        if (!$access_token) {

            if ($employer_id > 0) {
                $user_refresh_token = WP_Job_Board_Pro_Employer::get_post_meta($employer_id, 'zoom_refresh_token', true);
                if ($user_refresh_token != '') {
                    $result_token = self::refresh_token_code_curl($user_refresh_token);
                    $result_token = json_decode($result_token, true);
                    if (isset($result_token['access_token']) && $result_token['access_token'] != '') {
                        $refresh_token = isset($result_token['refresh_token']) ? $result_token['refresh_token'] : '';
                        WP_Job_Board_Pro_Employer::update_post_meta($employer_id, 'zoom_refresh_token', $refresh_token);
                        $access_token = $result_token['access_token'];
                        set_transient('wjbp_zoom_access_token_' . $user_id, $access_token, 900);
                        return $access_token;
                    } else {
                        WP_Job_Board_Pro_Employer::update_post_meta($employer_id, 'zoom_refresh_token', '');
                    }
                }
            }
        }
        
    }
}

WP_Job_Board_Pro_Meeting_Zoom::init();