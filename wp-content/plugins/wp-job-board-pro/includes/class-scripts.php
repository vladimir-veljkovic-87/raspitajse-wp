<?php
/**
 * Scripts
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Scripts {
	/**
	 * Initialize scripts
	 *
	 * @access public
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_backend' ) );

		add_action( 'login_enqueue_scripts', array( __CLASS__, 'login_enqueue_styles' ), 10 );
	}

	/**
	 * Loads front files
	 *
	 * @access public
	 * @return void
	 */
	public static function enqueue_frontend() {
		if ( is_user_logged_in() ) {
			wp_register_script( 'jquery-iframe-transport', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/jquery-fileupload/jquery.iframe-transport.js', array( 'jquery' ), '1.8.3', true );
			wp_register_script( 'jquery-fileupload', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/jquery-fileupload/jquery.fileupload.js', array( 'jquery', 'jquery-iframe-transport', 'jquery-ui-widget' ), '9.11.2', true );
			wp_register_script( 'wp-job-board-pro-ajax-file-upload', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/ajax-file-upload.js', array( 'jquery', 'jquery-fileupload' ), WP_JOB_BOARD_PRO_PLUGIN_VERSION, true );

			$js_field_html_img = WP_Job_Board_Pro_Template_Loader::get_template_part('misc/uploaded-file-html', array( 'input_name'  => '', 'value' => '', 'extension' => 'jpg' ));
			$js_field_html = WP_Job_Board_Pro_Template_Loader::get_template_part('misc/uploaded-file-html', array( 'input_name'  => '', 'value' => '', 'extension' => 'zip' ));

			wp_localize_script(
				'wp-job-board-pro-ajax-file-upload',
				'wp_job_board_pro_file_upload',
				array(
					'ajax_url'               => admin_url( 'admin-ajax.php' ),
					'ajax_url_endpoint'      => WP_Job_Board_Pro_Ajax::get_endpoint(),
					'js_field_html_img'      => esc_js( str_replace( "\n", '', $js_field_html_img ) ),
					'js_field_html'          => esc_js( str_replace( "\n", '', $js_field_html ) ),
					'i18n_invalid_file_type' => __( 'Invalid file type. Accepted types:', 'wp-job-board-pro' ),
					'i18n_invalid_file_size' => __( 'Invalid file size. Accepted size %sMB:', 'wp-job-board-pro' ),
				)
			);
		}

		wp_dequeue_script('select2');
		$select2_args = array( 'width' => '100%' );
		if ( is_rtl() ) {
			$select2_args['dir'] = 'rtl';
		}
		$select2_args['language'] = get_bloginfo("language");
		$select2_args['language_result'] = esc_html__('No Results Found', 'wp-job-board-pro');
		$select2_args['formatInputTooShort_text'] = esc_html__('Please enter 2 or more characters', 'wp-job-board-pro');

		wp_register_script( 'wpjbp-select2', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/select2/select2.min.js', array( 'jquery'  ), '4.0.5', true );
		wp_localize_script( 'wpjbp-select2', 'wp_job_board_pro_select2_opts', $select2_args);
		wp_register_style( 'wpjbp-select2', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/select2/select2.min.css', array(), '4.0.5' );

		wp_enqueue_style( 'magnific', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/magnific/magnific-popup.css', array(), '1.1.0' );
		wp_enqueue_script( 'magnific', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/magnific/jquery.magnific-popup.min.js', array( 'jquery' ), '1.1.0', true );

		wp_register_script( 'jquery-ui-touch-punch', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/jquery.ui.touch-punch.min.js', array( 'jquery' ), '20150330', true );

		$browser_key = wp_job_board_pro_get_option('google_map_api_keys');
		$key = empty( $browser_key ) ? '' : 'key='. $browser_key . '&';
		wp_register_script( 'google-maps', '//maps.googleapis.com/maps/api/js?'. $key .'libraries=geometry,places', '6.0', true );

		if ( wp_job_board_pro_get_option('map_service') == 'google-map' ) {
			wp_enqueue_script( 'google-maps' );
			wp_register_script( 'leaflet-GoogleMutant', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/leaflet/Leaflet.GoogleMutant.js', array( 'jquery' ), '1.5.1', true );
		}

		wp_register_style( 'leaflet', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/leaflet/leaflet.css', array(), '1.5.1' );

		wp_register_script( 'jquery-highlight', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/jquery.highlight.js', array( 'jquery' ), '5', true );
	    wp_register_script( 'leaflet', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/leaflet/leaflet.js', array( 'jquery' ), '1.5.1', true );
	    wp_register_script( 'control-geocoder', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/leaflet/Control.Geocoder.js', array( 'jquery', 'leaflet' ), '1.5.1', true );
	    wp_register_script( 'esri-leaflet', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/leaflet/esri-leaflet.js', array( 'jquery', 'leaflet' ), '1.5.1', true );
	    wp_register_script( 'esri-leaflet-geocoder', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/leaflet/esri-leaflet-geocoder.js', array( 'jquery', 'leaflet' ), '1.5.1', true );
	    wp_register_script( 'leaflet-markercluster', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/leaflet/leaflet.markercluster.js', array( 'jquery', 'leaflet' ), '1.5.1', true );
	    wp_register_script( 'leaflet-HtmlIcon', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/leaflet/LeafletHtmlIcon.js', array( 'jquery', 'leaflet' ), '1.5.1', true );


		
		if ( wp_job_board_pro_get_option('after_login_page_id') ) {
			$page_id = wp_job_board_pro_get_option('after_login_page_id');
			$page_id = WP_Job_Board_Pro_Mixes::get_lang_post_id($page_id, 'page');
			$after_login_page_url = get_permalink($page_id);
		} else {
			$after_login_page_url = get_permalink( wp_job_board_pro_get_option('user_dashboard_page_id') );
		}
		if ( wp_job_board_pro_get_option('after_login_page_id_candidate') ) {
			$page_id = wp_job_board_pro_get_option('after_login_page_id_candidate');
			$page_id = WP_Job_Board_Pro_Mixes::get_lang_post_id($page_id, 'page');
			$after_login_page_candidate_url = get_permalink($page_id);
		} else {
			$after_login_page_candidate_url = $after_login_page_url;
		}

		if ( wp_job_board_pro_get_option('after_register_page_id') ) {
			$page_id = wp_job_board_pro_get_option('after_register_page_id');
			$page_id = WP_Job_Board_Pro_Mixes::get_lang_post_id($page_id, 'page');
			$after_register_page_url = get_permalink($page_id);
		} else {
			$page_id = wp_job_board_pro_get_option('user_dashboard_page_id');
			$page_id = WP_Job_Board_Pro_Mixes::get_lang_post_id($page_id, 'page');
			$after_register_page_url = get_permalink($page_id);
		}
		if ( wp_job_board_pro_get_option('after_register_page_id_candidate') ) {
			$page_id = wp_job_board_pro_get_option('after_register_page_id_candidate');
			$page_id = WP_Job_Board_Pro_Mixes::get_lang_post_id($page_id, 'page');
			$after_register_page_candidate_url = get_permalink($page_id);
		} else {
			$after_register_page_candidate_url = $after_register_page_url;
		}

		$page_id = wp_job_board_pro_get_option('user_dashboard_page_id');
		$page_id = WP_Job_Board_Pro_Mixes::get_lang_post_id($page_id, 'page');
		$dashboard_page_url = get_permalink($page_id);
		
		$page_id = wp_job_board_pro_get_option('login_register_page_id');
		$page_id = WP_Job_Board_Pro_Mixes::get_lang_post_id($page_id, 'page');
		$login_register_url = get_permalink($page_id);

		$cv_file_type_keys = wp_job_board_pro_get_option('cv_file_types', array('doc', 'docx', 'pdf'));
		$all_cv_file_types = WP_Job_Board_Pro_Mixes::get_cv_mime_types();
		$cv_file_types = array();
		foreach ($cv_file_type_keys as $mime) {
			if ( !empty($all_cv_file_types[$mime]) ) {
				$cv_file_types[] = $all_cv_file_types[$mime];
			}
		}
		if ( !empty($cv_file_types) ) {
			$cv_file_types = array_unique($cv_file_types);
		} else {
	        $cv_file_types = array(
	            'application/msword',
	            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	            'application/pdf',
	        );
	    }
        $cv_files_types_json = json_encode($cv_file_types);
        $suitable_files_str = '';
        if ( !empty($cv_file_type_keys) ) {
	        $suitable_files_str = implode(', ', $cv_file_type_keys);
	    }

		wp_register_script( 'wp-job-board-pro-main', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/main.js', array( 'jquery', 'jquery-ui-slider', 'jquery-ui-touch-punch' ), '20131022', true );
		wp_localize_script( 'wp-job-board-pro-main', 'wp_job_board_pro_opts', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'ajaxurl_endpoint' => WP_Job_Board_Pro_Ajax::get_endpoint(),
			'dashboard_url' => esc_url( $dashboard_page_url ),
			'after_login_page_url' => esc_url( $after_login_page_url ),
			'after_login_page_candidate_url' => esc_url( $after_login_page_candidate_url ),
			'after_register_page_url' => esc_url( $after_register_page_url ),
			'after_register_page_candidate_url' => esc_url( $after_register_page_candidate_url ),
			'login_register_url' => esc_url( $login_register_url ),
			'home_url' => esc_url( home_url( '/' ) ),

			'cv_file_size_allow' => wp_max_upload_size(),
            'cv_file_size_error' => esc_js(sprintf(__('File size should not greater than %s.', 'wp-job-board-pro'), wp_max_upload_size())),
            'cv_file_types_error' => esc_js(sprintf(__('Suitable files are %s.', 'wp-job-board-pro'), $suitable_files_str)),
            'cv_file_types' => esc_js($cv_files_types_json),

            'money_decimals' => wp_job_board_pro_get_option('money_decimals', 0),
			'money_dec_point' => wp_job_board_pro_get_option('money_dec_point', '.'),
			'money_thousands_separator' => wp_job_board_pro_get_option('money_thousands_separator') ? wp_job_board_pro_get_option('money_thousands_separator') : '',

			'show_more' => esc_html__('Show more', 'wp-job-board-pro'),
			'show_more_icon' => '',
			'show_less' => esc_html__('Show less', 'wp-job-board-pro'),
			'show_less_icon' => '',

			'geocoder_country' => wp_job_board_pro_get_option('geocoder_country', ''),
			'rm_item_txt' => esc_html__('Are you sure?', 'wp-job-board-pro'),
			'recaptcha_enable' => WP_Job_Board_Pro_Recaptcha::is_recaptcha_enabled(),
			'map_service' => wp_job_board_pro_get_option('map_service'),
			'not_allow' => esc_html__('Not Allow', 'wp-job-board-pro'),
			'choose_a_cv' => esc_html__('Choose a cv', 'wp-job-board-pro'),
			'job_filled' => esc_html__('This job is filled and no longer accepting applications.', 'wp-job-board-pro'),

			'cv_required' => wp_job_board_pro_get_option('candidate_apply_job_cv_required', 'on'),

			'ajax_nonce' => wp_create_nonce( 'wpjb-ajax-nonce' ),
		));
		wp_enqueue_script( 'wp-job-board-pro-main' );
	}

	/**
	 * Loads backend files
	 *
	 * @access public
	 * @return void
	 */
	public static function enqueue_backend() {
		
		$browser_key = wp_job_board_pro_get_option('google_map_api_keys');
		$key = empty( $browser_key ) ? '' : 'key='. $browser_key . '&';
		wp_register_script( 'google-maps', '//maps.googleapis.com/maps/api/js?'. $key .'libraries=geometry,places' );

		if ( wp_job_board_pro_get_option('map_service') == 'google-map' ) {
			wp_enqueue_script( 'google-maps' );
			wp_register_script( 'leaflet-GoogleMutant', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/leaflet/Leaflet.GoogleMutant.js', array( 'jquery' ), '1.5.1', true );
		}


		wp_register_style( 'leaflet', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/leaflet/leaflet.css', array(), '1.5.1' );
		wp_register_script( 'jquery-highlight', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/jquery.highlight.js', array( 'jquery' ), '5', true );
	    wp_register_script( 'leaflet', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/leaflet/leaflet.js', array( 'jquery' ), '1.5.1', true );
	    
	    wp_register_script( 'control-geocoder', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/leaflet/Control.Geocoder.js', array( 'jquery', 'leaflet' ), '1.5.1', true );
	    wp_register_script( 'esri-leaflet', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/leaflet/esri-leaflet.js', array( 'jquery', 'leaflet' ), '1.5.1', true );
	    wp_register_script( 'esri-leaflet-geocoder', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/leaflet/esri-leaflet-geocoder.js', array( 'jquery', 'leaflet' ), '1.5.1', true );
	    wp_register_script( 'leaflet-markercluster', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/leaflet/leaflet.markercluster.js', array( 'jquery', 'leaflet' ), '1.5.1', true );
	    wp_register_script( 'leaflet-HtmlIcon', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/leaflet/LeafletHtmlIcon.js', array( 'jquery', 'leaflet' ), '1.5.1', true );

	    $select2_args = array( 'width' => '100%' );
	    $select2_args['dir'] = 'ltr';
	    $select2_args['formatInputTooShort_text'] = esc_html__('Please enter 2 or more characters', 'wp-job-board-pro');
		if ( is_rtl() ) {
			$select2_args['dir'] = 'rtl';
		}
		$select2_args['language_result'] = esc_html__('No Results Found', 'wp-job-board-pro');
		
		wp_register_script( 'wpjbp-select2', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/select2/select2.min.js', array( 'jquery'  ), '4.0.5', true );
		wp_localize_script( 'wpjbp-select2', 'wp_job_board_pro_select2_opts', $select2_args);
		wp_register_style( 'wpjbp-select2', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/select2/select2.min.css', array(), '4.0.5' );
		
		wp_enqueue_style( 'wp-job-board-pro-style-admin', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/css/style-admin.css' );
		wp_enqueue_style( 'font-awesome', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/css/font-awesome.css', array(), '4.5.0', false );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-job-board-pro-admin-main', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/js/admin-main.js', array( 'jquery', 'wp-color-picker' ), '1.0.0', true );
	}

	public static function login_enqueue_styles() {
		wp_enqueue_style( 'font-awesome', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/css/font-awesome.css', array(), '4.5.0' );
		wp_enqueue_style( 'wp-job-board-pro-login-style', WP_JOB_BOARD_PRO_PLUGIN_URL . 'assets/css/login-style.css', array(), '1.0' );
	}
	
}

WP_Job_Board_Pro_Scripts::init();
