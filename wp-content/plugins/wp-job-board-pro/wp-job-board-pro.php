<?php
/**
 * Plugin Name: WP Job Board Pro
 * Plugin URI: http://apusthemes.com/wp-job-board-pro/
 * Description: Powerful plugin to create a Job Board on your website.
 * Version: 1.2.73
 * Author: Habq
 * Author URI: http://apusthemes.com/
 * Requires at least: 3.8
 * Tested up to: 6.0
 *
 * Text Domain: wp-job-board-pro
 * Domain Path: /languages/
 *
 * @package wp-job-board-pro
 * @category Plugins
 * @author Habq
 */
if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

if ( !class_exists("WP_Job_Board_Pro") ) {
	
	final class WP_Job_Board_Pro {

		private static $instance;

		public static function getInstance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WP_Job_Board_Pro ) ) {
				self::$instance = new WP_Job_Board_Pro;
				self::$instance->setup_constants();

				self::$instance->load_textdomain();
				self::$instance->plugin_update();


				add_action( 'activated_plugin', array( self::$instance, 'plugin_order' ) );
				add_action( 'tgmpa_register', array( self::$instance, 'register_plugins' ) );
				add_action( 'widgets_init', array( self::$instance, 'register_widgets' ) );

				self::$instance->libraries();
				self::$instance->includes();
			}

			return self::$instance;
		}

		/**
		 *
		 */
		public function setup_constants(){
			define( 'WP_JOB_BOARD_PRO_PLUGIN_VERSION', '1.2.73' );

			define( 'WP_JOB_BOARD_PRO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			define( 'WP_JOB_BOARD_PRO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

			define('WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX', '_job_');
			define('WP_JOB_BOARD_PRO_EMPLOYER_PREFIX', '_employer_');
			define('WP_JOB_BOARD_PRO_CANDIDATE_PREFIX', '_candidate_');
			define('WP_JOB_BOARD_PRO_APPLICANT_PREFIX', '_applicant_');
			define('WP_JOB_BOARD_PRO_JOB_CUSTOM_FIELD_PREFIX', '_job_cfield_');
			define('WP_JOB_BOARD_PRO_EMPLOYER_CUSTOM_FIELD_PREFIX', '_employer_cfield_');
			define('WP_JOB_BOARD_PRO_CANDIDATE_CUSTOM_FIELD_PREFIX', '_candidate_cfield_');
			define('WP_JOB_BOARD_PRO_JOB_ALERT_PREFIX', '_job_alert_');
			define('WP_JOB_BOARD_PRO_CANDIDATE_ALERT_PREFIX', '_candidate_alert_');
			define('WP_JOB_BOARD_PRO_MEETING_PREFIX', '_meeting_');
		}

		public function includes() {
			global $wp_job_board_pro_options;
			// Admin Settings
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/admin/class-settings.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/admin/class-permalink-settings.php';

			$wp_job_board_pro_options = wp_job_board_pro_get_settings();
			
			// post type
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/post-types/class-post-type-job_listing.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/post-types/class-post-type-employer.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/post-types/class-post-type-candidate.php';

			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/post-types/class-post-type-job-applicant.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/post-types/class-post-type-job-alert.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/post-types/class-post-type-candidate-alert.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/post-types/class-post-type-meeting.php';
			
			// custom fields
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/custom-fields/class-fields-manager.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/custom-fields/class-custom-fields-html.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/custom-fields/class-custom-fields.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/custom-fields/class-custom-fields-display.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/custom-fields/class-custom-fields-register.php';

			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-job-meta.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-employer-meta.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-candidate-meta.php';

			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-abstract-register-form.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-employer-register-form.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-candidate-register-form.php';
			
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-candidate-register-apply-form.php';


			// taxonomies
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/taxonomies/class-taxonomy-job-type.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/taxonomies/class-taxonomy-job-category.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/taxonomies/class-taxonomy-job-location.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/taxonomies/class-taxonomy-job-tag.php';
			
			// employer taxonomies
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/taxonomies/class-taxonomy-employer-category.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/taxonomies/class-taxonomy-employer-location.php';
			// candidate taxonomies
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/taxonomies/class-taxonomy-candidate-category.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/taxonomies/class-taxonomy-candidate-location.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/taxonomies/class-taxonomy-candidate-tag.php';

			//
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-scripts.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-template-loader.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-job_listing.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-employer.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-candidate.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-applicant.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-job-rss-feed.php';
			
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-price.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-query.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-shortcodes.php';

			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-abstract-form.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-submit-form.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-edit-form.php';
			
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-user.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-image.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-recaptcha.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-email.php';
			
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-abstract-filter.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-job-filter.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-employer-filter.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-candidate-filter.php';

			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-review.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-job-alert.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-candidate-alert.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-geocode.php';
			
			// meeting
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/meetings/class-meeting.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/meetings/class-meeting-zoom.php';

			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-user-notification.php';


			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-ajax.php';

			// social login
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/socials/class-social-facebook.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/socials/class-social-google.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/socials/class-social-linkedin.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/socials/class-social-twitter.php';

			// import indeed jobs
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/import-jobs-integration/class-import-jobs-integration.php';

			// mpdf
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-mpdf.php';

			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-mixes.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-cache-helper.php';

			// 3rd-party
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/3rd-party/class-wpml.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/3rd-party/class-polylang.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/3rd-party/class-all-in-one-seo-pack.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/3rd-party/class-jetpack.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/3rd-party/class-yoast.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/3rd-party/class-all-import.php';

			// google structured data
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-structured-data.php';

			//
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-social.php';

			//
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/class-rest-api.php';

			add_action('init', array( __CLASS__, 'register_post_statuses' ) );
		}

		public function plugin_update() {
	        require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/plugin-update-checker/plugin-update-checker.php';
	        Puc_v4_Factory::buildUpdateChecker(
	            'https://www.apusthemes.com/themeplugins/wp-job-board-pro.json',
	            __FILE__,
	            'wp-job-board-pro'
	        );
	    }

		public static function register_post_statuses() {
			register_post_status(
				'expired',
				array(
					'label'                     => _x( 'Expired', 'post status', 'wp-job-board-pro' ),
					'public'                    => true,
					'protected'                 => true,
					'exclude_from_search'       => true,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'wp-job-board-pro' ),
				)
			);
			register_post_status(
				'pending_approve',
				array(
					'label'                     => _x( 'Pending Approve', 'post status', 'wp-job-board-pro' ),
					'public'                    => false,
					'protected'                 => true,
					'exclude_from_search'       => true,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Pending Approve <span class="count">(%s)</span>', 'Pending Approve <span class="count">(%s)</span>', 'wp-job-board-pro' ),
				)
			);
			register_post_status(
				'preview',
				array(
					'label'                     => _x( 'Preview', 'post status', 'wp-job-board-pro' ),
					'public'                    => false,
					'exclude_from_search'       => true,
					'show_in_admin_all_list'    => false,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Preview <span class="count">(%s)</span>', 'Preview <span class="count">(%s)</span>', 'wp-job-board-pro' ),
				)
			);
			register_post_status(
				'pending_payment',
				array(
					'label'                     => _x( 'Pending Payment', 'post status', 'wp-job-board-pro' ),
					'public'                    => false,
					'exclude_from_search'       => true,
					'show_in_admin_all_list'    => false,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Pending Payment <span class="count">(%s)</span>', 'Pending Payment <span class="count">(%s)</span>', 'wp-job-board-pro' ),
				)
			);
			register_post_status(
				'denied',
				array(
					'label'                     => _x( 'Denied', 'post status', 'wp-job-board-pro' ),
					'public'                    => false,
					'exclude_from_search'       => true,
					'show_in_admin_all_list'    => false,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Denied <span class="count">(%s)</span>', 'Denied <span class="count">(%s)</span>', 'wp-job-board-pro' ),
				)
			);
		}
		public static function register_widgets() {
			// widgets
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/widgets/class-widget-job-filter.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/widgets/class-widget-employer-filter.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/widgets/class-widget-candidate-filter.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/widgets/class-widget-job-alert-form.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'includes/widgets/class-widget-candidate-alert-form.php';
		}
		/**
		 * Loads third party libraries
		 *
		 * @access public
		 * @return void
		 */
		public static function libraries() {
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/cmb2/cmb2_field_map/cmb-field-map.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/cmb2/cmb2_field_tags/cmb2-field-type-tags.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/cmb2/cmb2_field_file/cmb2-field-type-file.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/cmb2/cmb2_field_attached_user/cmb2-field-type-attached_user.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/cmb2/cmb2_field_profile_url/cmb2-field-type-profile_url.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/cmb2/cmb2_field_image_select/cmb2-field-type-image-select.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/cmb2/cmb_field_select2/cmb-field-select2.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/cmb2/cmb_field_taxonomy_select2/cmb-field-taxonomy-select2.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/cmb2/cmb_field_taxonomy_select2_search/cmb-field-taxonomy-select2-search.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/cmb2/cmb2_field_ajax_search/cmb2-field-ajax-search.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/cmb2/cmb_field_taxonomy_location/cmb-field-taxonomy-location.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/cmb2/cmb_field_taxonomy_location_search/cmb-field-taxonomy-location-search.php';
			
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/cmb2/cmb2-hide-show-password-field/cmb2-hide-show-password.php';
			
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/cmb2/cmb2_field_rate_exchange/cmb2-field-type-rate_exchange.php';

			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/cmb2/cmb2_field_datepicker/cmb2-field-type-datepicker.php';
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/cmb2/cmb2_field_datepicker2/cmb2-field-type-datepicker2.php';
			
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/cmb2/cmb2-tabs/plugin.php';
			
			require_once WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/class-tgm-plugin-activation.php';
		}

		/**
	     * Loads this plugin first
	     *
	     * @access public
	     * @return void
	     */
	    public static function plugin_order() {
		    $wp_path_to_this_file = preg_replace( '/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR.'/$2', __FILE__ );
		    $this_plugin = plugin_basename( trim( $wp_path_to_this_file ) );
		    $active_plugins = get_option( 'active_plugins' );
		    $this_plugin_key = array_search( $this_plugin, $active_plugins );
			if ( $this_plugin_key ) {
				array_splice( $active_plugins, $this_plugin_key, 1 );
				array_unshift( $active_plugins, $this_plugin );
			    update_option( 'active_plugins', $active_plugins );
		    }
	    }

		/**
		 * Install plugins
		 *
		 * @access public
		 * @return void
		 */
		public static function register_plugins() {
			$plugins = array(
	            array(
		            'name'      => 'CMB2',
		            'slug'      => 'cmb2',
		            'required'  => true,
	            )
			);

			tgmpa( $plugins );
		}

		public static function maybe_schedule_cron_jobs() {
			if ( ! wp_next_scheduled( 'wp_job_board_pro_check_for_expired_jobs' ) ) {
				wp_schedule_event( time(), 'hourly', 'wp_job_board_pro_check_for_expired_jobs' );
			}
			if ( ! wp_next_scheduled( 'wp_job_board_pro_delete_old_previews' ) ) {
				wp_schedule_event( time(), 'daily', 'wp_job_board_pro_delete_old_previews' );
			}
			if ( ! wp_next_scheduled( 'wp_job_board_pro_email_daily_notices' ) ) {
				wp_schedule_event( time(), 'daily', 'wp_job_board_pro_email_daily_notices' );
			}
		}

		/**
		 * Unschedule cron jobs. This is run on plugin deactivation.
		 */
		public static function unschedule_cron_jobs() {
			wp_clear_scheduled_hook( 'wp_job_board_pro_check_for_expired_jobs' );
			wp_clear_scheduled_hook( 'wp_job_board_pro_delete_old_previews' );
			wp_clear_scheduled_hook( 'wp_job_board_pro_email_daily_notices' );
		}

		/**
		 *
		 */
		public function load_textdomain() {
			// Set filter for WP_Job_Board_Pro's languages directory
			$lang_dir = WP_JOB_BOARD_PRO_PLUGIN_DIR . 'languages/';
			$lang_dir = apply_filters( 'wp_job_board_pro_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'wp-job-board-pro' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'wp-job-board-pro', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/wp-job-board-pro/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/wp-job-board-pro folder
				load_textdomain( 'wp-job-board-pro', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/wp-job-board-pro/languages/ folder
				load_textdomain( 'wp-job-board-pro', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'wp-job-board-pro', false, $lang_dir );
			}
		}
	}
}

register_activation_hook( __FILE__, array( 'WP_Job_Board_Pro', 'maybe_schedule_cron_jobs' ) );
register_deactivation_hook( __FILE__, array( 'WP_Job_Board_Pro', 'unschedule_cron_jobs' ) );

function WP_Job_Board_Pro() {
	return WP_Job_Board_Pro::getInstance();
}

add_action( 'plugins_loaded', 'WP_Job_Board_Pro' );
