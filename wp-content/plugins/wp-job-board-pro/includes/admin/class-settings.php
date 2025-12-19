<?php
/**
 * Settings
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Settings {

	/**
	 * Option key, and option page slug
	 * @var string
	 */
	private $key = 'wp_job_board_pro_settings';

	/**
	 * Array of metaboxes/fields
	 * @var array
	 */
	protected $option_metabox = array();

	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = '';

	/**
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Constructor
	 * @since 1.0
	 */
	public function __construct() {
	
		add_action( 'admin_menu', array( $this, 'admin_menu' ) , 10 );

		add_action( 'admin_init', array( $this, 'init' ) );

		//Custom CMB2 Settings Fields
		add_action( 'cmb2_render_wp_job_board_pro_title', 'wp_job_board_pro_title_callback', 10, 5 );
		add_action( 'cmb2_render_wp_job_board_pro_hidden', 'wp_job_board_pro_hidden_callback', 10, 5 );

		add_action( "cmb2_save_options-page_fields", array( $this, 'settings_notices' ), 10, 3 );


		add_action( 'cmb2_render_api_keys', 'wp_job_board_pro_api_keys_callback', 10, 5 );

		// Include CMB CSS in the head to avoid FOUC
		add_action( "admin_print_styles-wp_job_board_pro_properties_page_job_listing-settings", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
	}

	public function admin_menu() {
		//Settings
	 	$wp_job_board_pro_settings_page = add_submenu_page( 'edit.php?post_type=job_listing', __( 'Settings', 'wp-job-board-pro' ), __( 'Settings', 'wp-job-board-pro' ), 'manage_options', 'job_listing-settings',
	 		array( $this, 'admin_page_display' ) );
	}

	/**
	 * Register our setting to WP
	 * @since  1.0
	 */
	public function init() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Retrieve settings tabs
	 *
	 * @since 1.0
	 * @return array $tabs
	 */
	public function wp_job_board_pro_get_settings_tabs() {
		$tabs             	  = array();
		$tabs['general']  	  = __( 'General', 'wp-job-board-pro' );
		$tabs['job_submission']   = __( 'Job Submission', 'wp-job-board-pro' );
		$tabs['pages']   = __( 'Pages', 'wp-job-board-pro' );
		$tabs['jobs_settings']   = __( 'Jobs Settings', 'wp-job-board-pro' );
		$tabs['employer_settings']   = __( 'Employer Settings', 'wp-job-board-pro' );
		$tabs['employee_settings']   = __( 'Employee Settings', 'wp-job-board-pro' );
		$tabs['candidate_settings']   = __( 'Candidate Settings', 'wp-job-board-pro' );
	 	$tabs['api_settings'] = __( 'Socials API', 'wp-job-board-pro' );
	 	$tabs['recaptcha_api_settings'] = __( 'ReCaptcha API', 'wp-job-board-pro' );
	 	$tabs['email_notification'] = __( 'Email Notification', 'wp-job-board-pro' );
	 	$tabs['import_job_integrations'] = __( 'Import Job Integrations', 'wp-job-board-pro' );

		return apply_filters( 'wp_job_board_pro_settings_tabs', $tabs );
	}

	/**
	 * Admin page markup. Mostly handled by CMB2
	 * @since  1.0
	 */
	public function admin_page_display() {

		$active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $this->wp_job_board_pro_get_settings_tabs() ) ? $_GET['tab'] : 'general';

		?>

		<div class="wrap wp_job_board_pro_settings_page cmb2_options_page <?php echo $this->key; ?>">

			<h2><?php esc_html_e( 'Settings', 'wp-job-board-pro' ); ?></h2>
			
			<div class="nav-tab-wrapper">
				<?php
				foreach ( $this->wp_job_board_pro_get_settings_tabs() as $tab_id => $tab_name ) {

					$tab_url = esc_url( add_query_arg( array(
						'settings-updated' => false,
						'tab'              => $tab_id
					) ) );

					$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

					echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );

					echo '</a>';
				}
				?>
			</div>
			
			<?php cmb2_metabox_form( $this->wp_job_board_pro_settings( $active_tab ), $this->key ); ?>

		</div><!-- .wrap -->

		<?php
	}

	/**
	 * Define General Settings Metabox and field configurations.
	 *
	 * Filters are provided for each settings section to allow add-ons and other plugins to add their own settings
	 *
	 * @param $active_tab active tab settings; null returns full array
	 *
	 * @return array
	 */
	public function wp_job_board_pro_settings( $active_tab ) {

		$pages = wp_job_board_pro_cmb2_get_post_options( array(
			'post_type'   => 'page',
			'numberposts' => - 1
		) );
		$cv_mime_types = array();
		$mime_types = WP_Job_Board_Pro_Mixes::get_cv_mime_types();
		foreach($mime_types as $key => $mine_type) {
			$cv_mime_types[$key] = $key;
		}

		$images_file_types = array();
		$mime_types = WP_Job_Board_Pro_Mixes::get_image_mime_types();
		foreach($mime_types as $key => $mine_type) {
			$images_file_types[$key] = $key;
		}

		$wp_job_board_pro_settings = array();

		$countries = array( '' => __('All Countries', 'wp-job-board-pro') );
		$countries = array_merge( $countries, WP_Job_Board_Pro_Indeed_API::indeed_api_countries() );

		// currency
		$currencies = WP_Job_Board_Pro_Price::get_currencies();
		$currencies_opts = [];
		foreach ($currencies as $k => $currency) {
			$currencies_opts[$k] = $k.' - '.$currency.'('.WP_Job_Board_Pro_Price::currency_symbol($k).')';
		}

		// General
		$wp_job_board_pro_settings['general'] = array(
			'id'         => 'options_page',
			'wp_job_board_pro_title' => __( 'General Settings', 'wp-job-board-pro' ),
			'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
			'fields'     => apply_filters( 'wp_job_board_pro_settings_general', array(
					
					
					array(
						'name' => __( 'Currency Settings', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_general_settings_2',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),

					// new
					array(
						'name'    => __( 'Enable Multiple Currencies', 'wp-job-board-pro' ),
						'id'      => 'enable_multi_currencies',
						'type'    => 'select',
						'options' => array(
							'yes' 	=> __( 'Yes', 'wp-job-board-pro' ),
							'no'   => __( 'No', 'wp-job-board-pro' ),
						),
						'default' => 'no',
					),
					array(
						'name'            => __( 'Exchangerate API Key', 'wp-job-board-pro' ),
						'id'              => 'exchangerate_api_key',
						'type'            => 'text',
						'desc' => sprintf(__( 'Acquire an API key from the <a href="%s" target="_blank">Exchange Rate API</a>', 'wp-job-board-pro' ), 'https://www.exchangerate-api.com/docs/php-currency-api'),
					),
					array(
						'name'    => __( 'Currency', 'wp-job-board-pro' ),
						'desc'    => __( 'Choose a currency.', 'wp-job-board-pro' ),
						'id'      => 'currency',
						'type'    => 'pw_select',
						'options' => $currencies_opts,
						'default' => 'USD',
						'attributes'        => array(
		                    'data-allowclear' => 'false',
		                    'data-width'		=> '25em'
		                ),
					),
					array(
						'name'            => __( 'Custom symbol', 'wp-job-board-pro' ),
						'id'              => 'custom_symbol',
						'type'            => 'text_small',
						'attributes'        => array(
		                    'placeholder' => __( 'eg: CAD $', 'wp-job-board-pro' ),
		                ),
					),
					array(
						'name'    => __( 'Currency Position', 'wp-job-board-pro' ),
						'desc'    => 'Choose the position of the currency sign.',
						'id'      => 'currency_position',
						'type'    => 'pw_select',
						'options' => array(
							'before' => __( 'Before - $10', 'wp-job-board-pro' ),
							'after'  => __( 'After - 10$', 'wp-job-board-pro' ),
							'before_space' => __( 'Before with space - $ 10', 'wp-job-board-pro' ),
							'after_space'  => __( 'After with space - 10 $', 'wp-job-board-pro' ),
						),
						'default' => 'before',
						'attributes'        => array(
		                    'data-allowclear' => 'false',
		                    'data-width'		=> '25em'
		                ),
					),
					array(
						'name'    => __( 'Number of decimals', 'wp-job-board-pro' ),
						'desc'    => __( 'This sets the number of decimal points shown in displayed prices.', 'wp-job-board-pro' ),
						'id'      => 'money_decimals',
						'type'    => 'text_small',
						'attributes' 	    => array(
							'type' 				=> 'number',
							'min'				=> 0,
							'pattern' 			=> '\d*',
						)
					),
					array(
						'name'            => __( 'Decimal Separator', 'wp-job-board-pro' ),
						'desc'            => __( 'The symbol (usually , or .) to separate decimal points', 'wp-job-board-pro' ),
						'id'              => 'money_dec_point',
						'type'            => 'text_small',
						'default' 		=> '.',
					),
					array(
						'name'    => __( 'Thousands Separator', 'wp-job-board-pro' ),
						'desc'    => __( 'If you need space, enter &nbsp;', 'wp-job-board-pro' ),
						'id'      => 'money_thousands_separator',
						'type'    => 'text_small',
					),
					/////
					array(
						'name'              => __( 'More Currencies', 'wp-job-board-pro' ),
						'id'                => 'multi_currencies',
						'type'              => 'group',
						'options'     		=> array(
							'group_title'       => __( 'Currency', 'wp-job-board-pro' ),
							'add_button'        => __( 'Add Another Currency', 'wp-job-board-pro' ),
							'remove_button'     => __( 'Remove Currency', 'wp-job-board-pro' ),
							'sortable'          => true,
							'closed'         => true,
							'remove_confirm' => __( 'Do you want to remove this currency', 'wp-job-board-pro' ),
						),
						'fields'			=> array(
							array(
								'name'    => __( 'Currency', 'wp-job-board-pro' ),
								'desc'    => __( 'Choose a currency.', 'wp-job-board-pro' ),
								'id'      => 'currency',
								'type'    => 'pw_select',
								'options' => $currencies_opts,
								'attributes'        => array(
				                    'data-allowclear' => 'false',
				                    'data-width'		=> '25em'
				                ),
				                'classes' => 'multi-currency-select'
							),
							array(
								'name'    => __( 'Currency Position', 'wp-job-board-pro' ),
								'desc'    => 'Choose the position of the currency sign.',
								'id'      => 'currency_position',
								'type'    => 'pw_select',
								'options' => array(
									'before' => __( 'Before - $10', 'wp-job-board-pro' ),
									'after'  => __( 'After - 10$', 'wp-job-board-pro' ),
									'before_space' => __( 'Before with space - $ 10', 'wp-job-board-pro' ),
									'after_space'  => __( 'After with space - 10 $', 'wp-job-board-pro' ),
								),
								'default' => 'before',
								'attributes'        => array(
				                    'data-allowclear' => 'false',
				                    'data-width'		=> '25em'
				                ),
							),
							array(
								'name'    => __( 'Number of decimals', 'wp-job-board-pro' ),
								'desc'    => __( 'This sets the number of decimal points shown in displayed prices.', 'wp-job-board-pro' ),
								'id'      => 'money_decimals',
								'type'    => 'text_small',
								'attributes' 	    => array(
									'type' 				=> 'number',
									'min'				=> 0,
									'pattern' 			=> '\d*',
								)
							),
							array(
								'name'            => __( 'Rate + Exchange Fee', 'wp-job-board-pro' ),
								'id'              => 'rate_exchange_fee',
								'type'            => 'wp_job_board_pro_rate_exchange',
								'default' => 1
							),
							array(
								'name'            => __( 'Custom symbol', 'wp-job-board-pro' ),
								'id'              => 'custom_symbol',
								'type'            => 'text_small',
								'attributes'        => array(
				                    'placeholder' => __( 'eg: CAD $', 'wp-job-board-pro' ),
				                ),
							),
						),
					),


					array(
						'name' => __( 'File Types', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_general_settings_3',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Images File Types', 'wp-job-board-pro' ),
						'id'      => 'image_file_types',
						'type'    => 'multicheck_inline',
						'options' => $images_file_types,
						'default' => array('jpg', 'jpeg', 'jpe', 'png')
					),
					array(
						'name'    => __( 'CV File Types', 'wp-job-board-pro' ),
						'id'      => 'cv_file_types',
						'type'    => 'multicheck_inline',
						'options' => $cv_mime_types,
						'default' => array('doc', 'pdf', 'docx')
					),
					array(
						'name' => __( 'Map API Settings', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_general_settings_4',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Map Service', 'wp-job-board-pro' ),
						'id'      => 'map_service',
						'type'    => 'select',
						'options' => array(
							'mapbox' => __('Mapbox', 'wp-job-board-pro'),
							'google-map' => __('Google Maps', 'wp-job-board-pro'),
							'here' => __('Here Maps', 'wp-job-board-pro'),
							'openstreetmap' => __('OpenStreetMap', 'wp-job-board-pro'),
						),
					),
					array(
						'name'    => __( 'Google Map API', 'wp-job-board-pro' ),
						'desc'    => __( 'Google requires an API key to retrieve location information for job listings. Acquire an API key from the <a href="https://developers.google.com/maps/documentation/geocoding/get-api-key">Google Maps API developer site.</a>', 'wp-job-board-pro' ),
						'id'      => 'google_map_api_keys',
						'type'    => 'text',
						'default' => '',
					),
					array(
						'name'    => __( 'Google Maps Style', 'wp-job-board-pro' ),
						'desc' 	  => wp_kses(__('<a href="//snazzymaps.com/">Get custom style</a> and paste it below. If there is nothing added, we will fallback to the Google Maps service.', 'wp-job-board-pro'), array('a' => array('href' => array()))),
						'id'      => 'google_map_style',
						'type'    => 'textarea',
						'default' => '',
					),
					array(
						'name'    => __( 'Mapbox Token', 'wp-job-board-pro' ),
						'desc' => wp_kses(__('<a href="//www.mapbox.com/help/create-api-access-token/">Get a FREE token</a> and paste it below. If there is nothing added, we will fallback to the Google Maps service.', 'wp-job-board-pro'), array('a' => array('href' => array()))),
						'id'      => 'mapbox_token',
						'type'    => 'text',
						'default' => '',
					),
					array(
						'name'    => __( 'Mapbox Style', 'wp-job-board-pro' ),
						'id'      => 'mapbox_style',
						'type'    => 'wp_job_board_pro_image_select',
						'options' => array(
							'streets-v11' => array(
		                        'alt' => esc_html__('streets', 'wp-job-board-pro'),
		                        'img' => WP_JOB_BOARD_PRO_PLUGIN_URL . '/assets/images/streets.png'
		                    ),
		                    'light-v10' => array(
		                        'alt' => esc_html__('light', 'wp-job-board-pro'),
		                        'img' => WP_JOB_BOARD_PRO_PLUGIN_URL . '/assets/images/light.png'
		                    ),
		                    'dark-v10' => array(
		                        'alt' => esc_html__('dark', 'wp-job-board-pro'),
		                        'img' => WP_JOB_BOARD_PRO_PLUGIN_URL . '/assets/images/dark.png'
		                    ),
		                    'outdoors-v11' => array(
		                        'alt' => esc_html__('outdoors', 'wp-job-board-pro'),
		                        'img' => WP_JOB_BOARD_PRO_PLUGIN_URL . '/assets/images/outdoors.png'
		                    ),
		                    'satellite-v9' => array(
		                        'alt' => esc_html__('satellite', 'wp-job-board-pro'),
		                        'img' => WP_JOB_BOARD_PRO_PLUGIN_URL . '/assets/images/satellite.png'
		                    ),
		                ),
		                'default' => 'streets-v11',
					),
					array(
						'name'    => __( 'Here Maps API Key', 'wp-job-board-pro' ),
						'desc' => wp_kses(__('<a href="https://developer.here.com/tutorials/getting-here-credentials/">Get a API key</a> and paste it below. If there is nothing added, we will fallback to the Google Maps service.', 'wp-job-board-pro'), array('a' => array('href' => array()))),
						'id'      => 'here_map_api_key',
						'type'    => 'text',
						'default' => '',
					),
					array(
						'name'    => __( 'Here Maps Style', 'wp-job-board-pro' ),
						'id'      => 'here_map_style',
						'type'    => 'select',
						'options' => array(
							'normal.day' => esc_html__('Normal Day', 'wp-job-board-pro'),
							'normal.day.grey' => esc_html__('Normal Day Grey', 'wp-job-board-pro'),
							'normal.day.transit' => esc_html__('Normal Day Transit', 'wp-job-board-pro'),
							'normal.night' => esc_html__('Normal Night', 'wp-job-board-pro'),
							'reduced.day' => esc_html__('Reduced Day', 'wp-job-board-pro'),
							'reduced.night' => esc_html__('Reduced Night', 'wp-job-board-pro'),
							'pedestrian.day' => esc_html__('Pedestrian Day', 'wp-job-board-pro'),
						)
					),
					array(
						'name'    => __( 'Geocoder Country', 'wp-job-board-pro' ),
						'id'      => 'geocoder_country',
						'type'    => 'select',
						'options' => $countries
					),
					array(
						'name' => __( 'Default maps location', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_general_settings_default_maps_location',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Latitude', 'wp-job-board-pro' ),
						'desc'    => __( 'Enter your latitude', 'wp-job-board-pro' ),
						'id'      => 'default_maps_location_latitude',
						'type'    => 'text_small',
						'default' => '43.6568',
					),
					array(
						'name'    => __( 'Longitude', 'wp-job-board-pro' ),
						'desc'    => __( 'Enter your longitude', 'wp-job-board-pro' ),
						'id'      => 'default_maps_location_longitude',
						'type'    => 'text_small',
						'default' => '-79.4512',
					),
					array(
						'name'    => esc_html__( 'Map Pin', 'wp-job-board-pro' ),
						'desc'    => esc_html__( 'Enter your map pin', 'wp-job-board-pro' ),
						'id'      => 'default_maps_pin',
						'type'    => 'file',
						'options' => array(
							'url' => true,
						),
						'query_args' => array(
							'type' => array(
								'image/gif',
								'image/jpeg',
								'image/png',
							),
						),
					),
					array(
						'name' => __( 'Distance Settings', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_general_settings_distance',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Distance unit', 'wp-job-board-pro' ),
						'id'      => 'distance_unit',
						'type'    => 'select',
						'options' => array(
							'km' => __('Kilometers', 'wp-job-board-pro'),
							'miles' => __('Miles', 'wp-job-board-pro'),
						),
						'default' => 'miles',
					),
					array(
						'name' => __( 'Location Settings', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_general_settings_location',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Location Multiple Fields', 'wp-job-board-pro' ),
						'id'      => 'location_multiple_fields',
						'type'    => 'select',
						'options' => array(
							'yes' 	=> __( 'Yes', 'wp-job-board-pro' ),
							'no'   => __( 'No', 'wp-job-board-pro' ),
						),
						'default' => 'yes',
						'desc'    => __( 'You can set 4 fields for regions like: Country, State, City, District', 'wp-job-board-pro' ),
					),
					array(
						'name'    => __( 'Number Fields', 'wp-job-board-pro' ),
						'id'      => 'location_nb_fields',
						'type'    => 'select',
						'options' => array(
							'1' => __('1 Field', 'wp-job-board-pro'),
							'2' => __('2 Fields', 'wp-job-board-pro'),
							'3' => __('3 Fields', 'wp-job-board-pro'),
							'4' => __('4 Fields', 'wp-job-board-pro'),
						),
						'default' => '1',
						'desc'    => __( 'You can set 4 fields for regions like: Country, State, City, District', 'wp-job-board-pro' ),
					),
					array(
						'name'    => __( 'First Field Label', 'wp-job-board-pro' ),
						'desc'    => __( 'Empty for translate multiple languages', 'wp-job-board-pro' ),
						'id'      => 'location_1_field_label',
						'type'    => 'text',
						'attributes' 	    => array(
							'placeholder'  => 'Country'
						)
					),
					array(
						'name'    => __( 'Second Field Label', 'wp-job-board-pro' ),
						'desc'    => __( 'Empty for translate multiple languages', 'wp-job-board-pro' ),
						'id'      => 'location_2_field_label',
						'type'    => 'text',
						'attributes' 	    => array(
							'placeholder'  => 'State'
						)
					),
					array(
						'name'    => __( 'Third Field Label', 'wp-job-board-pro' ),
						'desc'    => __( 'Empty for translate multiple languages', 'wp-job-board-pro' ),
						'id'      => 'location_3_field_label',
						'type'    => 'text',
						'attributes' 	    => array(
							'placeholder'  => 'City'
						)
					),
					array(
						'name'    => __( 'Fourth Field Label', 'wp-job-board-pro' ),
						'desc'    => __( 'Empty for translate multiple languages', 'wp-job-board-pro' ),
						'id'      => 'location_4_field_label',
						'type'    => 'text',
						'attributes' 	    => array(
							'placeholder'  => 'District'
						)
					),
				)
			)		 
		);

		// Job Submission
		$wp_job_board_pro_settings['job_submission'] = array(
			'id'         => 'options_page',
			'wp_job_board_pro_title' => __( 'Job Submission', 'wp-job-board-pro' ),
			'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
			'fields'     => apply_filters( 'wp_job_board_pro_settings_job_submission', array(
					array(
						'name' => __( 'Job Submission', 'wp-job-board-pro' ),
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_job_submission_settings_1',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Submit Job Form Page', 'wp-job-board-pro' ),
						'desc'    => __( 'This is page to display form for submit job. The <code>[wp_job_board_pro_submission]</code> shortcode should be on this page.', 'wp-job-board-pro' ),
						'id'      => 'submit_job_form_page_id',
						'type'    => 'select',
						'options' => $pages,
						'page-type' => 'page'
					),
					array(
						'name'    => __( 'Moderate New Listings', 'wp-job-board-pro' ),
						'desc'    => __( 'Require admin approval of all new listing submissions', 'wp-job-board-pro' ),
						'id'      => 'submission_requires_approval',
						'type'    => 'select',
						'options' => array(
							'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
							'off'   => __( 'Disable', 'wp-job-board-pro' ),
						),
						'default' => 'on',
					),
					array(
						'name'    => __( 'Allow Published Edits', 'wp-job-board-pro' ),
						'desc'    => __( 'Choose whether published job listings can be edited and if edits require admin approval. When moderation is required, the original job listings will be unpublished while edits await admin approval.', 'wp-job-board-pro' ),
						'id'      => 'user_edit_published_submission',
						'type'    => 'select',
						'options' => array(
							'no' 	=> __( 'Users cannot edit', 'wp-job-board-pro' ),
							'yes'   => __( 'Users can edit without admin approval', 'wp-job-board-pro' ),
							'yes_moderated'   => __( 'Users can edit, but edits require admin approval', 'wp-job-board-pro' ),
						),
						'default' => 'yes',
					),
					array(
						'name'            => __( 'Listing Duration', 'wp-job-board-pro' ),
						'desc'            => __( 'Listings will display for the set number of days, then expire. Enter this field "0" if you don\'t want listings to have an expiration date.', 'wp-job-board-pro' ),
						'id'              => 'submission_duration',
						'type'            => 'text_small',
						'default'         => 30,
					),
					array(
						'name' => __( 'Job Slug', 'wp-job-board-pro' ),
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_job_submission_job_slug',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Employer Name', 'wp-job-board-pro' ),
						'desc'    => __( 'Use employer name in job slug', 'wp-job-board-pro' ),
						'id'      => 'submission_job_slug_employer',
						'type'    => 'select',
						'options' => array(
							'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
							'off'   => __( 'Disable', 'wp-job-board-pro' ),
						),
						'default' => 'on',
					),
					array(
						'name'    => __( 'Location', 'wp-job-board-pro' ),
						'desc'    => __( 'Use location in job slug', 'wp-job-board-pro' ),
						'id'      => 'submission_job_slug_location',
						'type'    => 'select',
						'options' => array(
							'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
							'off'   => __( 'Disable', 'wp-job-board-pro' ),
						),
						'default' => 'on',
					),
					array(
						'name'    => __( 'Job Type', 'wp-job-board-pro' ),
						'desc'    => __( 'Use job type in job slug', 'wp-job-board-pro' ),
						'id'      => 'submission_job_slug_type',
						'type'    => 'select',
						'options' => array(
							'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
							'off'   => __( 'Disable', 'wp-job-board-pro' ),
						),
						'default' => 'on',
					),
				), $pages
			)		 
		);

		// Pages
		$wp_job_board_pro_settings['pages'] = array(
			'id'         => 'options_page',
			'wp_job_board_pro_title' => __( 'Pages', 'wp-job-board-pro' ),
			'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
			'fields'     => apply_filters( 'wp_job_board_pro_settings_pages', array(
					array(
						'name'    => __( 'Jobs Page', 'wp-job-board-pro' ),
						'desc'    => __( 'This lets the plugin know the location of the jobs listing page. The <code>[wp_job_board_pro_jobs]</code> shortcode should be on this page.', 'wp-job-board-pro' ),
						'id'      => 'jobs_page_id',
						'type'    => 'select',
						'options' => $pages,
						'page-type' => 'page'
					),
					
					
					array(
						'name'    => __( 'Login/Register Page', 'wp-job-board-pro' ),
						'desc'    => __( 'This lets the plugin know the location of the job listings page. The <code>[wp_job_board_pro_login]</code> <code>[wp_job_board_pro_register]</code> shortcode should be on this page.', 'wp-job-board-pro' ),
						'id'      => 'login_register_page_id',
						'type'    => 'select',
						'options' => $pages,
						'page-type' => 'page'
					),
					array(
						'name'    => __( 'After Login Page (Employer)', 'wp-job-board-pro' ),
						'desc'    => __( 'This lets the plugin know the page after employer login.', 'wp-job-board-pro' ),
						'id'      => 'after_login_page_id',
						'type'    => 'select',
						'options' => $pages,
						'page-type' => 'page'
					),
					array(
						'name'    => __( 'After Login Page (Candidate)', 'wp-job-board-pro' ),
						'desc'    => __( 'This lets the plugin know the page after candidate login.', 'wp-job-board-pro' ),
						'id'      => 'after_login_page_id_candidate',
						'type'    => 'select',
						'options' => $pages,
						'page-type' => 'page'
					),
					array(
						'name'    => __( 'After Register Page (Employer)', 'wp-job-board-pro' ),
						'desc'    => __( 'This lets the plugin know the page after employer register.', 'wp-job-board-pro' ),
						'id'      => 'after_register_page_id',
						'type'    => 'select',
						'options' => $pages,
						'page-type' => 'page'
					),
					array(
						'name'    => __( 'After Register Page (Candidate)', 'wp-job-board-pro' ),
						'desc'    => __( 'This lets the plugin know the page after candidate register.', 'wp-job-board-pro' ),
						'id'      => 'after_register_page_id_candidate',
						'type'    => 'select',
						'options' => $pages,
						'page-type' => 'page'
					),
					array(
						'name'    => __( 'Approve User Page', 'wp-job-board-pro' ),
						'desc'    => __( 'This lets the plugin know the location of the job listings page. The <code>[wp_job_board_pro_approve_user]</code> shortcode should be on this page.', 'wp-job-board-pro' ),
						'id'      => 'approve_user_page_id',
						'type'    => 'select',
						'options' => $pages,
						'page-type' => 'page'
					),
					array(
						'name'    => __( 'User Dashboard Page', 'wp-job-board-pro' ),
						'desc'    => __( 'This lets the plugin know the location of the user dashboard. The <code>[wp_job_board_pro_user_dashboard]</code> shortcode should be on this page.', 'wp-job-board-pro' ),
						'id'      => 'user_dashboard_page_id',
						'type'    => 'select',
						'options' => $pages,
						'page-type' => 'page'
					),
					array(
						'name'    => __( 'Edit Profile Page', 'wp-job-board-pro' ),
						'desc'    => __( 'This lets the plugin know the location of the user edit profile. The <code>[wp_job_board_pro_change_profile]</code> shortcode should be on this page.', 'wp-job-board-pro' ),
						'id'      => 'edit_profile_page_id',
						'type'    => 'select',
						'options' => $pages,
						'page-type' => 'page'
					),
					array(
						'name'    => __( 'Change Password Page', 'wp-job-board-pro' ),
						'desc'    => __( 'This lets the plugin know the location of the user edit profile. The <code>[wp_job_board_pro_change_password]</code> shortcode should be on this page.', 'wp-job-board-pro' ),
						'id'      => 'change_password_page_id',
						'type'    => 'select',
						'options' => $pages,
						'page-type' => 'page'
					),
					array(
						'name'    => __( 'My Jobs Page', 'wp-job-board-pro' ),
						'desc'    => __( 'This lets the plugin know the location of the job listings page. The <code>[wp_job_board_pro_my_jobs]</code> shortcode should be on this page.', 'wp-job-board-pro' ),
						'id'      => 'my_jobs_page_id',
						'type'    => 'select',
						'options' => $pages,
						'page-type' => 'page'
					),
					array(
						'name'    => __( 'My Resume', 'wp-job-board-pro' ),
						'desc'    => __( 'This lets the plugin know the location of the candidate resume page. The <code>[wp_job_board_pro_change_resume]</code> shortcode should be on this page.', 'wp-job-board-pro' ),
						'id'      => 'my_resume_page_id',
						'type'    => 'select',
						'options' => $pages,
						'page-type' => 'page'
					),
					array(
						'name'    => __( 'Terms and Conditions Page', 'wp-job-board-pro' ),
						'desc'    => __( 'This lets the plugin know the Terms and Conditions page.', 'wp-job-board-pro' ),
						'id'      => 'terms_conditions_page_id',
						'type'    => 'select',
						'options' => $pages,
						'page-type' => 'page'
					),
				), $pages
			)		 
		);

		// Jobs Settings
		$wp_job_board_pro_settings['jobs_settings'] = array(
			'id'         => 'options_page',
			'wp_job_board_pro_title' => __( 'Jobs Settings', 'wp-job-board-pro' ),
			'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
			'fields'     => apply_filters( 'wp_job_board_pro_settings_jobs_settings', array(
				
				array(
					'name' => __( 'General Settings', 'wp-job-board-pro' ),
					'type' => 'wp_job_board_pro_title',
					'id'   => 'wp_job_board_pro_title_general_settings_1',
					'before_row' => '<hr>',
					'after_row'  => '<hr>'
				),
				array(
					'name'    => __( 'Number jobs per page', 'wp-job-board-pro' ),
					'desc'    => __( 'Number of jobs to display per page.', 'wp-job-board-pro' ),
					'id'      => 'number_jobs_per_page',
					'type'    => 'text',
					'default' => '10',
				),
				

				array(
					'name' => __( 'Restrict Job settings', 'wp-job-board-pro' ),
					'type' => 'wp_job_board_pro_title',
					'id'   => 'wp_job_board_pro_title_jobs_settings_restrict_job',
					'before_row' => '<hr>',
					'after_row'  => '<hr>'
				),
				array(
					'name'    => __( 'Restrict Job Type', 'wp-job-board-pro' ),
					'desc'    => __( 'Select a restrict type for restrict job', 'wp-job-board-pro' ),
					'id'      => 'job_restrict_type',
					'type'    => 'select',
					'options' => array(
						'' => __( 'None', 'wp-job-board-pro' ),
						'view' => __( 'View Job', 'wp-job-board-pro' ),
					),
					'default' => ''
				),
				array(
					'name'    => __( 'Restrict Job Detail', 'wp-job-board-pro' ),
					'desc'    => __( 'Restrict Jobs detail page for all users except jobs.', 'wp-job-board-pro' ),
					'id'      => 'job_restrict_detail',
					'type'    => 'radio',
					'options' => apply_filters( 'wp-job-board-pro-restrict-job-detail', array(
						'all' => __( 'All (Users, Guests)', 'wp-job-board-pro' ),
						'register_user' => __( 'All Register Users', 'wp-job-board-pro' ),
						'register_candidate' => __( 'Register Candidates (All registered candidates can view jobs.)', 'wp-job-board-pro' ),
						'always_hidden' => __( 'Always Hidden', 'wp-job-board-pro' ),
					)),
					'default' => 'all',
				),
				array(
					'name'    => __( 'Restrict Job Listing', 'wp-job-board-pro' ),
					'desc'    => __( 'Restrict Jobs Listing page for all users except jobs.', 'wp-job-board-pro' ),
					'id'      => 'job_restrict_listing',
					'type'    => 'radio',
					'options' => apply_filters( 'wp-job-board-pro-restrict-job-listing', array(
						'all' => __( 'All Users (Users, Guests)', 'wp-job-board-pro' ),
						'register_user' => __( 'All Register Users', 'wp-job-board-pro' ),
						'register_candidate' => __( 'Register Candidates (All registered candidates can view jobs.)', 'wp-job-board-pro' ),
						'always_hidden' => __( 'Always Hidden', 'wp-job-board-pro' ),
					)),
					'default' => 'all',
				),

			), $pages )
		);
		// Employer Settings
		$wp_job_board_pro_settings['employer_settings'] = array(
			'id'         => 'options_page',
			'wp_job_board_pro_title' => __( 'Employer Settings', 'wp-job-board-pro' ),
			'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
			'fields'     => apply_filters( 'wp_job_board_pro_settings_employer_settings', array(
				array(
					'name'    => __( 'Moderate New Employer', 'wp-job-board-pro' ),
					'desc'    => __( 'Require admin approval of all new employers', 'wp-job-board-pro' ),
					'id'      => 'employers_requires_approval',
					'type'    => 'select',
					'options' => array(
						'auto' 	=> __( 'Auto Approve', 'wp-job-board-pro' ),
						'email_approve' => __( 'Email Approve', 'wp-job-board-pro' ),
						'admin_approve' => __( 'Administrator Approve', 'wp-job-board-pro' ),
					),
					'default' => 'auto',
				),
				array(
					'name'    => __( 'Employers Page', 'wp-job-board-pro' ),
					'desc'    => __( 'This lets the plugin know the location of the employers listing page. The <code>[wp_job_board_pro_employers]</code> shortcode should be on this page.', 'wp-job-board-pro' ),
					'id'      => 'employers_page_id',
					'type'    => 'select',
					'options' => $pages,
					'page-type' => 'page'
				),
				array(
					'name'    => __( 'Number employers per page', 'wp-job-board-pro' ),
					'desc'    => __( 'Number of employers to display per page.', 'wp-job-board-pro' ),
					'id'      => 'number_employers_per_page',
					'type'    => 'text',
					'default' => '10',
				),

				array(
					'name' => __( 'Employer Slug', 'wp-job-board-pro' ),
					'type' => 'wp_job_board_pro_title',
					'id'   => 'wp_job_board_pro_title_employer_submission_job_slug',
					'before_row' => '<hr>',
					'after_row'  => '<hr>'
				),
				array(
					'name'    => __( 'Employer Slug', 'wp-job-board-pro' ),
					'id'      => 'employer_slug_employer',
					'type'    => 'select',
					'options' => array(
						'' 	=> __( 'Default (Employer name)', 'wp-job-board-pro' ),
						'number' 	=> __( 'Random Number', 'wp-job-board-pro' ),
					),
					'default' => '',
				),

				array(
					'name' => __( 'Restrict Employer settings', 'wp-job-board-pro' ),
					'type' => 'wp_job_board_pro_title',
					'id'   => 'wp_job_board_pro_title_employer_settings_restrict_employer',
					'before_row' => '<hr>',
					'after_row'  => '<hr>'
				),
				array(
					'name'    => __( 'Restrict Type', 'wp-job-board-pro' ),
					'desc'    => __( 'Select a restrict type for restrict employer', 'wp-job-board-pro' ),
					'id'      => 'employer_restrict_type',
					'type'    => 'select',
					'options' => array(
						'' => __( 'None', 'wp-job-board-pro' ),
						'view' => __( 'View Employer', 'wp-job-board-pro' ),
						'view_contact_info' => __( 'View Employer Contact Info', 'wp-job-board-pro' ),
					),
					'default' => ''
				),
				array(
					'name'    => __( 'Restrict Employer Detail', 'wp-job-board-pro' ),
					'desc'    => __( 'Restrict Employers detail page for all users except employers.', 'wp-job-board-pro' ),
					'id'      => 'employer_restrict_detail',
					'type'    => 'radio',
					'options' => apply_filters( 'wp-job-board-pro-restrict-employer-detail', array(
						'all' => __( 'All (Users, Guests)', 'wp-job-board-pro' ),
						'register_user' => __( 'All Register Users', 'wp-job-board-pro' ),
						'only_applicants' => __( 'Only Applicants (Candidate can view only their own applicants employers.)', 'wp-job-board-pro' ),
						'register_candidate' => __( 'Register Candidates (All registered candidates can view employers.)', 'wp-job-board-pro' ),
						'always_hidden' => __( 'Always Hidden', 'wp-job-board-pro' ),
					)),
					'default' => 'all',
				),
				array(
					'name'    => __( 'Restrict Employer Listing', 'wp-job-board-pro' ),
					'desc'    => __( 'Restrict Employers Listing page for all users except employers.', 'wp-job-board-pro' ),
					'id'      => 'employer_restrict_listing',
					'type'    => 'radio',
					'options' => apply_filters( 'wp-job-board-pro-restrict-employer-listing', array(
						'all' => __( 'All Users (Users, Guests)', 'wp-job-board-pro' ),
						'register_user' => __( 'All Register Users', 'wp-job-board-pro' ),
						'only_applicants' => __( 'Only Applicants (Candidate can view only their own applicants employers.)', 'wp-job-board-pro' ),
						'register_candidate' => __( 'Register Candidates (All registered candidates can view employers.)', 'wp-job-board-pro' ),
						'always_hidden' => __( 'Always Hidden', 'wp-job-board-pro' ),
					)),
					'default' => 'all',
				),

				// restrict contact
				array(
					'name'    => __( 'Restrict View Contact Employer', 'wp-job-board-pro' ),
					'desc'    => __( 'Restrict View Contact Employers detail page for all users except employers.', 'wp-job-board-pro' ),
					'id'      => 'employer_restrict_contact_info',
					'type'    => 'radio',
					'options' => apply_filters( 'wp-job-board-pro-restrict-employer-view-contact', array(
						'all' => __( 'All (Users, Guests)', 'wp-job-board-pro' ),
						'register_user' => __( 'All Register Users', 'wp-job-board-pro' ),
						'only_applicants' => __( 'Only Applicants (Candidate can see contact info only their own applicants employers.)', 'wp-job-board-pro' ),
						'register_candidate' => __( 'Register Candidates (All registered employers can see contact info employers.)', 'wp-job-board-pro' ),
						'always_hidden' => __( 'Always Hidden', 'wp-job-board-pro' ),
					)),
					'default' => 'all',
				),

				array(
					'name'    => __( 'Hide Employer Name', 'wp-job-board-pro' ),
					'id'      => 'restrict_contact_employer_name',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),
				array(
					'name'    => __( 'Hide Employer Email', 'wp-job-board-pro' ),
					'id'      => 'restrict_contact_employer_email',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),
				array(
					'name'    => __( 'Hide Employer Phone', 'wp-job-board-pro' ),
					'id'      => 'restrict_contact_employer_phone',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),
				array(
					'name'    => __( 'Hide Employer Website', 'wp-job-board-pro' ),
					'id'      => 'restrict_contact_employer_website',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),
				array(
					'name'    => __( 'Hide Employer Socials Media', 'wp-job-board-pro' ),
					'id'      => 'restrict_contact_employer_social',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),

				array(
					'name' => __( 'Employer Review settings', 'wp-job-board-pro' ),
					'type' => 'wp_job_board_pro_title',
					'id'   => 'wp_job_board_pro_title_employer_settings_employer_review',
					'before_row' => '<hr>',
					'after_row'  => '<hr>'
				),
				array(
					'name'    => __( 'Restrict Review', 'wp-job-board-pro' ),
					'id'      => 'employers_restrict_review',
					'type'    => 'radio',
					'options' => apply_filters( 'wp-job-board-pro-restrict-employer-review', array(
						'all' => __( 'All (Users, Guests)', 'wp-job-board-pro' ),
						'register_user' => __( 'All Register Users', 'wp-job-board-pro' ),
						'only_applicants' => __( 'Only Applicants (Candidate can see contact info only their own applicants employers.)', 'wp-job-board-pro' ),
						'register_candidate' => __( 'Register Candidates (All registered employers can see contact info employers.)', 'wp-job-board-pro' ),
						'always_hidden' => __( 'Always Hidden', 'wp-job-board-pro' ),
					)),
					'default' => 'all',
				),
			), $pages )
		);
		// Employee Settings
		$wp_job_board_pro_settings['employee_settings'] = array(
			'id'         => 'options_page',
			'wp_job_board_pro_title' => __( 'Employee Settings', 'wp-job-board-pro' ),
			'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
			'fields'     => apply_filters( 'wp_job_board_pro_settings_employee_settings', array(
				
				array(
					'name'    => __( 'Employee View Dashboard', 'wp-job-board-pro' ),
					'id'      => 'employee_view_dashboard',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),
				array(
					'name'    => __( 'Employee Submit Job', 'wp-job-board-pro' ),
					'id'      => 'employee_submit_job',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),
				array(
					'name'    => __( 'Employee Edit Job', 'wp-job-board-pro' ),
					'id'      => 'employee_edit_job',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),
				array(
					'name'    => __( 'Employee Edit Employer Profile', 'wp-job-board-pro' ),
					'id'      => 'employee_edit_employer_profile',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),
				array(
					'name'    => __( 'Employee View My Jobs', 'wp-job-board-pro' ),
					'id'      => 'employee_view_my_jobs',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),
				array(
					'name'    => __( 'Employee View Applications', 'wp-job-board-pro' ),
					'id'      => 'employee_view_applications',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),
				array(
					'name'    => __( 'Employee View Shortlist Candidate', 'wp-job-board-pro' ),
					'id'      => 'employee_view_shortlist',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),
				array(
					'name'    => __( 'Employee View Candidate Alerts', 'wp-job-board-pro' ),
					'id'      => 'employee_view_candidate_alert',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),
			), $pages )
		);
		// Candidate Settings
		$wp_job_board_pro_settings['candidate_settings'] = array(
			'id'         => 'options_page',
			'wp_job_board_pro_title' => __( 'Candidate Settings', 'wp-job-board-pro' ),
			'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
			'fields'     => apply_filters( 'wp_job_board_pro_settings_candidate_settings', array(
				array(
					'name'    => __( 'Candidates Page', 'wp-job-board-pro' ),
					'desc'    => __( 'This lets the plugin know the location of the candidates listing page. The <code>[wp_job_board_pro_candidates]</code> shortcode should be on this page.', 'wp-job-board-pro' ),
					'id'      => 'candidates_page_id',
					'type'    => 'select',
					'options' => $pages,
					'page-type' => 'page'
				),
				array(
					'name'    => __( 'Number candidates per page', 'wp-job-board-pro' ),
					'desc'    => __( 'Number of candidates to display per page.', 'wp-job-board-pro' ),
					'id'      => 'number_candidates_per_page',
					'type'    => 'text',
					'default' => '10',
				),
				array(
					'name' => __( 'Register Candidate settings', 'wp-job-board-pro' ),
					'type' => 'wp_job_board_pro_title',
					'id'   => 'wp_job_board_pro_title_candidate_settings_register_candidate',
					'before_row' => '<hr>',
					'after_row'  => '<hr>'
				),
				array(
					'name'    => __( 'Moderate New Candidate', 'wp-job-board-pro' ),
					'desc'    => __( 'Require admin approval of all new candidates', 'wp-job-board-pro' ),
					'id'      => 'candidates_requires_approval',
					'type'    => 'select',
					'options' => array(
						'auto' 	=> __( 'Auto Approve', 'wp-job-board-pro' ),
						'email_approve' => __( 'Email Approve', 'wp-job-board-pro' ),
						'admin_approve' => __( 'Administrator Approve', 'wp-job-board-pro' ),
					),
					'default' => 'auto',
				),
				array(
					'name'    => __( 'Moderate New Resume', 'wp-job-board-pro' ),
					'desc'    => __( 'Require admin approval of all new resume', 'wp-job-board-pro' ),
					'id'      => 'resumes_requires_approval',
					'type'    => 'select',
					'options' => array(
						'auto' 	=> __( 'Auto Approve', 'wp-job-board-pro' ),
						'admin_approve' => __( 'Administrator Approve', 'wp-job-board-pro' ),
					),
					'default' => 'auto',
				),
				array(
					'name'            => __( 'Resume Duration', 'wp-job-board-pro' ),
					'desc'            => __( 'Resumes will display for the set number of days, then expire. Enter this field "0" if you don\'t want resumes to have an expiration date.', 'wp-job-board-pro' ),
					'id'              => 'resume_duration',
					'type'            => 'text_small',
					'default'         => 30,
				),
				array(
					'name' => __( 'Candidate Slug', 'wp-job-board-pro' ),
					'type' => 'wp_job_board_pro_title',
					'id'   => 'wp_job_board_pro_title_candidate_submission_job_slug',
					'before_row' => '<hr>',
					'after_row'  => '<hr>'
				),
				array(
					'name'    => __( 'Candidate Slug', 'wp-job-board-pro' ),
					'id'      => 'candidate_slug_candidate',
					'type'    => 'select',
					'options' => array(
						'' 	=> __( 'Default (Candidate name)', 'wp-job-board-pro' ),
						'number' 	=> __( 'Random Number', 'wp-job-board-pro' ),
					),
					'default' => '',
				),
				array(
					'name' => __( 'Candidate Apply settings', 'wp-job-board-pro' ),
					'type' => 'wp_job_board_pro_title',
					'id'   => 'wp_job_board_pro_title_candidate_settings_candidate_appy',
					'before_row' => '<hr>',
					'after_row'  => '<hr>'
				),
				array(
					'name'    => __( 'Free Job Apply', 'wp-job-board-pro' ),
					'desc'    => __( 'Allow candidates to apply jobs absolutely package free.', 'wp-job-board-pro' ),
					'id'      => 'candidate_free_job_apply',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),
				array(
					'name'    => __( 'Candidate packages Page', 'wp-job-board-pro' ),
					'desc'    => __( 'Select Candidate Packages Page. It will redirect candidates at selected page to buy package.', 'wp-job-board-pro' ),
					'id'      => 'candidate_package_page_id',
					'type'    => 'select',
					'options' => $pages,
				),
				array(
					'name'            => __( 'Apply Job With Complete Resume', 'wp-job-board-pro' ),
					'desc'            => __( '% Candidate can apply job with percent number resume complete.', 'wp-job-board-pro' ),
					'id'              => 'apply_job_with_percent_resume',
					'type'            => 'text_small',
					'default'         => 70,
					'attributes' 	    => array(
						'type' 				=> 'number',
						'min'				=> 0,
						'max'				=> 100,
						'pattern' 			=> '\d*',
					)
				),
				array(
					'name'    => __( 'Apply job without login', 'wp-job-board-pro' ),
					'desc'    => __( 'Allow candidates to apply jobs without login.', 'wp-job-board-pro' ),
					'id'      => 'candidate_apply_job_without_login',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'off',
				),
				array(
					'name'    => __( 'CV required ?', 'wp-job-board-pro' ),
					'desc'    => __( 'Required candidate choose a CV.', 'wp-job-board-pro' ),
					'id'      => 'candidate_apply_job_cv_required',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),

				array(
					'name' => __( 'Restrict Candidate settings', 'wp-job-board-pro' ),
					'type' => 'wp_job_board_pro_title',
					'id'   => 'wp_job_board_pro_title_candidate_settings_restrict_candidate',
					'before_row' => '<hr>',
					'after_row'  => '<hr>'
				),
				array(
					'name'    => __( 'Restrict Type', 'wp-job-board-pro' ),
					'desc'    => __( 'Select a restrict type for restrict candidate', 'wp-job-board-pro' ),
					'id'      => 'candidate_restrict_type',
					'type'    => 'select',
					'options' => array(
						'' => __( 'None', 'wp-job-board-pro' ),
						'view' => __( 'View Candidate', 'wp-job-board-pro' ),
						'view_contact_info' => __( 'View Candidate Contact Info', 'wp-job-board-pro' ),
					),
					'default' => ''
				),
				array(
					'name'    => __( 'Restrict Candidate Detail', 'wp-job-board-pro' ),
					'desc'    => __( 'Restrict Candidates detail page for all users except employers.', 'wp-job-board-pro' ),
					'id'      => 'candidate_restrict_detail',
					'type'    => 'radio',
					'options' => apply_filters( 'wp-job-board-pro-restrict-candidate-detail', array(
						'all' => __( 'All (Users, Guests)', 'wp-job-board-pro' ),
						'register_user' => __( 'All Register Users', 'wp-job-board-pro' ),
						'only_applicants' => __( 'Only Applicants (Employer can view only their own applicants candidates.)', 'wp-job-board-pro' ),
						'register_employer' => __( 'Register Employers (All registered employers can view candidates.)', 'wp-job-board-pro' ),
					)),
					'default' => 'all',
				),
				array(
					'name'    => __( 'Restrict Candidate Listing', 'wp-job-board-pro' ),
					'desc'    => __( 'Restrict Candidates Listing page for all users except employers.', 'wp-job-board-pro' ),
					'id'      => 'candidate_restrict_listing',
					'type'    => 'radio',
					'options' => apply_filters( 'wp-job-board-pro-restrict-candidate-listing', array(
						'all' => __( 'All Users (Users, Guests)', 'wp-job-board-pro' ),
						'register_user' => __( 'All Register Users', 'wp-job-board-pro' ),
						'only_applicants' => __( 'Only Applicants (Employer can view only their own applicants candidates.)', 'wp-job-board-pro' ),
						'register_employer' => __( 'Register Employers (All registered employers can view candidates.)', 'wp-job-board-pro' ),
					)),
					'default' => 'all',
				),

				// restrict contact
				array(
					'name'    => __( 'Restrict View Contact Candidate', 'wp-job-board-pro' ),
					'desc'    => __( 'Restrict View Contact Candidates detail page for all users except employers.', 'wp-job-board-pro' ),
					'id'      => 'candidate_restrict_contact_info',
					'type'    => 'radio',
					'options' => apply_filters( 'wp-job-board-pro-restrict-candidate-view-contact', array(
						'all' => __( 'All (Users, Guests)', 'wp-job-board-pro' ),
						'register_user' => __( 'All Register Users', 'wp-job-board-pro' ),
						'only_applicants' => __( 'Only Applicants (Employer can see contact info only their own applicants candidates.)', 'wp-job-board-pro' ),
						'register_employer' => __( 'Register Employers (All registered employers can see contact info candidates.)', 'wp-job-board-pro' ),
					)),
					'default' => 'all',
				),

				array(
					'name'    => __( 'Hide Candidate Name', 'wp-job-board-pro' ),
					'id'      => 'restrict_contact_candidate_name',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),
				array(
					'name'    => __( 'Hide Candidate Email', 'wp-job-board-pro' ),
					'id'      => 'restrict_contact_candidate_email',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),
				array(
					'name'    => __( 'Hide Candidate Phone', 'wp-job-board-pro' ),
					'id'      => 'restrict_contact_candidate_phone',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),
				array(
					'name'    => __( 'Hide Candidate Socials Media', 'wp-job-board-pro' ),
					'id'      => 'restrict_contact_candidate_social',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),

				array(
					'name'    => __( 'Hide Candidate Download CV', 'wp-job-board-pro' ),
					'id'      => 'restrict_contact_candidate_download_cv',
					'type'    => 'select',
					'options' => array(
						'on' 	=> __( 'Enable', 'wp-job-board-pro' ),
						'off'   => __( 'Disable', 'wp-job-board-pro' ),
					),
					'default' => 'on',
				),

				array(
					'name' => __( 'Candidate Review settings', 'wp-job-board-pro' ),
					'type' => 'wp_job_board_pro_title',
					'id'   => 'wp_job_board_pro_title_candidate_settings_candidate_review',
					'before_row' => '<hr>',
					'after_row'  => '<hr>'
				),
				array(
					'name'    => __( 'Restrict Review', 'wp-job-board-pro' ),
					'id'      => 'candidates_restrict_review',
					'type'    => 'radio',
					'options' => apply_filters( 'wp-job-board-pro-restrict-candidate-review', array(
						'all' => __( 'All (Users, Guests)', 'wp-job-board-pro' ),
						'register_user' => __( 'All Register Users', 'wp-job-board-pro' ),
						'only_applicants' => __( 'Only Applicants (Employer can see contact info only their own applicants candidates.)', 'wp-job-board-pro' ),
						'register_employer' => __( 'Register Employers (All registered employers can see contact info candidates.)', 'wp-job-board-pro' ),
						'always_hidden' => __( 'Always Hidden', 'wp-job-board-pro' ),
					)),
					'default' => 'all',
				),

			), $pages )
		);
		
		// ReCaaptcha
		$wp_job_board_pro_settings['api_settings'] = array(
			'id'         => 'options_page',
			'wp_job_board_pro_title' => __( 'Social API', 'wp-job-board-pro' ),
			'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
			'fields'     => apply_filters( 'wp_job_board_pro_settings_api_settings', array(
					// Facebook
					array(
						'name' => __( 'Facebook API settings', 'wp-job-board-pro' ),
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_api_settings_facebook_title',
						'before_row' => '<hr>',
						'after_row'  => '<hr>',
						'desc' => sprintf(__('Callback URL is: %s', 'wp-job-board-pro'), admin_url('admin-ajax.php?action=wp_job_board_pro_facebook_login')),
					),
					array(
						'name'            => __( 'App ID', 'wp-job-board-pro' ),
						'desc'            => __( 'Please enter App ID of your Facebook account.', 'wp-job-board-pro' ),
						'id'              => 'facebook_api_app_id',
						'type'            => 'text',
					),
					array(
						'name'            => __( 'App Secret', 'wp-job-board-pro' ),
						'desc'            => __( 'Please enter App Secret of your Facebook account.', 'wp-job-board-pro' ),
						'id'              => 'facebook_api_app_secret',
						'type'            => 'text',
					),
					array(
						'name'    => __( 'Enable Facebook Login', 'wp-job-board-pro' ),
						'id'      => 'enable_facebook_login',
						'type'    => 'checkbox',
					),
					array(
						'name'    => __( 'Enable Facebook Apply', 'wp-job-board-pro' ),
						'id'      => 'enable_facebook_apply',
						'type'    => 'checkbox',
					),

					// Linkedin
					array(
						'name' => __( 'Linkedin API settings', 'wp-job-board-pro' ),
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_api_settings_linkedin_title',
						'before_row' => '<hr>',
						'after_row'  => '<hr>',
						'desc' => sprintf(__('Callback URL is: %s', 'wp-job-board-pro'), home_url('/')),
					),
					array(
						'name'    => __( 'Linkedin Login Type', 'wp-job-board-pro' ),
						'id'      => 'linkedin_login_type',
						'type'    => 'radio',
						'options' => array(
							'' => 'OAuth',
							'openid' => 'OpenID',
						),
						'default' => '',
					),
					array(
						'name'            => __( 'Client ID', 'wp-job-board-pro' ),
						'desc'            => __( 'Please enter Client ID of your linkedin app.', 'wp-job-board-pro' ),
						'id'              => 'linkedin_api_client_id',
						'type'            => 'text',
					),
					array(
						'name'            => __( 'Client Secret', 'wp-job-board-pro' ),
						'desc'            => __( 'Please enter Client Secret of your linkedin app.', 'wp-job-board-pro' ),
						'id'              => 'linkedin_api_client_secret',
						'type'            => 'text',
					),
					array(
						'name'    => __( 'Enable Linkedin Login', 'wp-job-board-pro' ),
						'id'      => 'enable_linkedin_login',
						'type'    => 'checkbox',
					),
					array(
						'name'    => __( 'Enable Linkedin Apply', 'wp-job-board-pro' ),
						'id'      => 'enable_linkedin_apply',
						'type'    => 'checkbox',
					),

					// Twitter
					array(
						'name' => __( 'Twitter API settings', 'wp-job-board-pro' ),
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_api_settings_twitter_title',
						'before_row' => '<hr>',
						'after_row'  => '<hr>',
						'desc' => sprintf(__('Callback URL is: %s', 'wp-job-board-pro'), home_url('/')),
					),
					array(
						'name'            => __( 'Consumer Key', 'wp-job-board-pro' ),
						'desc'            => __( 'Set Consumer Key for twitter.', 'wp-job-board-pro' ),
						'id'              => 'twitter_api_consumer_key',
						'type'            => 'text',
					),
					array(
						'name'            => __( 'Consumer Secret', 'wp-job-board-pro' ),
						'desc'            => __( 'Set Consumer Secret for twitter.', 'wp-job-board-pro' ),
						'id'              => 'twitter_api_consumer_secret',
						'type'            => 'text',
					),
					array(
						'name'            => __( 'Access Token', 'wp-job-board-pro' ),
						'desc'            => __( 'Set Access Token for twitter.', 'wp-job-board-pro' ),
						'id'              => 'twitter_api_access_token',
						'type'            => 'text',
					),
					array(
						'name'            => __( 'Token Secret', 'wp-job-board-pro' ),
						'desc'            => __( 'Set Token Secret for twitter.', 'wp-job-board-pro' ),
						'id'              => 'twitter_api_token_secret',
						'type'            => 'text',
					),
					array(
						'name'    => __( 'Enable Twitter Login', 'wp-job-board-pro' ),
						'id'      => 'enable_twitter_login',
						'type'    => 'checkbox',
					),
					array(
						'name'    => __( 'Enable Twitter Apply', 'wp-job-board-pro' ),
						'id'      => 'enable_twitter_apply',
						'type'    => 'checkbox',
					),

					// Google API
					array(
						'name' => __( 'Google API settings', 'wp-job-board-pro' ),
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_api_settings_google_title',
						'before_row' => '<hr>',
						'after_row'  => '<hr>',
						'desc' => sprintf(__('Callback URL is: %s', 'wp-job-board-pro'), home_url('/')),
					),
					array(
						'name'            => __( 'Client ID', 'wp-job-board-pro' ),
						'desc'            => __( 'Please enter Client ID of your Google account.', 'wp-job-board-pro' ),
						'id'              => 'google_api_client_id',
						'type'            => 'text',
					),
					array(
						'name'            => __( 'Client Secret', 'wp-job-board-pro' ),
						'desc'            => __( 'Please enter Client secret of your Google account.', 'wp-job-board-pro' ),
						'id'              => 'google_api_client_secret',
						'type'            => 'text',
					),
					array(
						'name'    => __( 'Enable Google Login', 'wp-job-board-pro' ),
						'id'      => 'enable_google_login',
						'type'    => 'checkbox',
					),
					array(
						'name'    => __( 'Enable Google Apply', 'wp-job-board-pro' ),
						'id'      => 'enable_google_apply',
						'type'    => 'checkbox',
					),
				)
			)		 
		);

		// ReCaaptcha
		$wp_job_board_pro_settings['recaptcha_api_settings'] = array(
			'id'         => 'options_page',
			'wp_job_board_pro_title' => __( 'reCAPTCHA API', 'wp-job-board-pro' ),
			'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
			'fields'     => apply_filters( 'wp_job_board_pro_settings_recaptcha_api_settings', array(
					
					// Google Recaptcha
					array(
						'name' => __( 'Google reCAPTCHA API (V2) Settings', 'wp-job-board-pro' ),
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_api_settings_google_recaptcha',
						'before_row' => '<hr>',
						'after_row'  => '<hr>',
						'desc' => __('The plugin use ReCaptcha v2', 'wp-job-board-pro'),
					),
					array(
						'name'            => __( 'Site Key', 'wp-job-board-pro' ),
						'desc'            => __( 'You can retrieve your site key from <a href="https://www.google.com/recaptcha/admin#list">Google\'s reCAPTCHA admin dashboard.</a>', 'wp-job-board-pro' ),
						'id'              => 'recaptcha_site_key',
						'type'            => 'text',
					),
					array(
						'name'            => __( 'Secret Key', 'wp-job-board-pro' ),
						'desc'            => __( 'You can retrieve your secret key from <a href="https://www.google.com/recaptcha/admin#list">Google\'s reCAPTCHA admin dashboard.</a>', 'wp-job-board-pro' ),
						'id'              => 'recaptcha_secret_key',
						'type'            => 'text',
					),
				)
			)		 
		);

		// Email Notification
		$wp_job_board_pro_settings['email_notification'] = array(
			'id'         => 'options_page',
			'wp_job_board_pro_title' => __( 'Email Notification', 'wp-job-board-pro' ),
			'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
			'fields'     => apply_filters( 'wp_job_board_pro_settings_email_notification', array(
					
					array(
						'name'    => __( 'Admin Notice of New Listing', 'wp-job-board-pro' ),
						'id'      => 'admin_notice_add_new_listing',
						'type'    => 'checkbox',
						'desc' 	=> __( 'Send a notice to the site administrator when a new job is submitted on the frontend.', 'wp-job-board-pro' ),
					),
					array(
						'name'    => __( 'Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('admin_notice_add_new_listing', 'subject') ),
						'id'      => 'admin_notice_add_new_listing_subject',
						'type'    => 'text',
						'default' => 'New Job Found',
					),
					array(
						'name'    => __( 'Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('admin_notice_add_new_listing', 'content') ),
						'id'      => 'admin_notice_add_new_listing_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('admin_notice_add_new_listing'),
					),

					
					array(
						'name'    => __( 'Admin Notice of Updated Listing', 'wp-job-board-pro' ),
						'id'      => 'admin_notice_updated_listing',
						'type'    => 'checkbox',
						'desc' 	=> __( 'Send a notice to the site administrator when a job is updated on the frontend.', 'wp-job-board-pro' ),
					),
					array(
						'name'    => __( 'Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('admin_notice_updated_listing', 'subject') ),
						'id'      => 'admin_notice_updated_listing_subject',
						'type'    => 'text',
						'default' => 'A Job Updated',
					),
					array(
						'name'    => __( 'Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('admin_notice_updated_listing', 'content') ),
						'id'      => 'admin_notice_updated_listing_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('admin_notice_updated_listing'),
					),

					
					array(
						'name'    => __( 'Admin Notice of Expiring Job Listings', 'wp-job-board-pro' ),
						'id'      => 'admin_notice_expiring_listing',
						'type'    => 'checkbox',
						'desc' 	=> __( 'Send notices to the site administrator before a job listing expires.', 'wp-job-board-pro' ),
					),
					array(
						'name'    => __( 'Notice Period', 'wp-job-board-pro' ),
						'desc'    => __( 'days', 'wp-job-board-pro' ),
						'id'      => 'admin_notice_expiring_listing_days',
						'type'    => 'text_small',
						'default' => '1',
					),
					array(
						'name'    => __( 'Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('admin_notice_expiring_listing', 'subject') ),
						'id'      => 'admin_notice_expiring_listing_subject',
						'type'    => 'text',
						'default' => 'Job Listing Expiring: {{job_title}}',
					),
					array(
						'name'    => __( 'Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('admin_notice_expiring_listing', 'content') ),
						'id'      => 'admin_notice_expiring_listing_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('admin_notice_expiring_listing'),
					),

					
					array(
						'name'    => __( 'Employer Notice of Expiring Job Listings', 'wp-job-board-pro' ),
						'id'      => 'employer_notice_expiring_listing',
						'type'    => 'checkbox',
						'desc' 	=> __( 'Send notices to employers before a job listing expires.', 'wp-job-board-pro' ),
					),
					array(
						'name'    => __( 'Notice Period', 'wp-job-board-pro' ),
						'desc'    => __( 'days', 'wp-job-board-pro' ),
						'id'      => 'employer_notice_expiring_listing_days',
						'type'    => 'text_small',
						'default' => '1',
					),
					array(
						'name'    => __( 'Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('employer_notice_expiring_listing', 'subject') ),
						'id'      => 'employer_notice_expiring_listing_subject',
						'type'    => 'text',
						'default' => 'Job Listing Expiring: {{job_title}}',
					),
					array(
						'name'    => __( 'Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('employer_notice_expiring_listing', 'content') ),
						'id'      => 'employer_notice_expiring_listing_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('employer_notice_expiring_listing'),
					),


					
					array(
						'name' => __( 'Job Alert', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_job_alert',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Job Alert Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('job_alert_notice', 'subject') ),
						'id'      => 'job_alert_notice_subject',
						'type'    => 'text',
						'default' => sprintf(__( 'Job Alert: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('job_alert_notice', 'subject') ),
					),
					array(
						'name'    => __( 'Job Alert Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('job_alert_notice', 'content') ),
						'id'      => 'job_alert_notice_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('job_alert_notice'),
					),

					array(
						'name' => __( 'Candidate Alert', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_candidate_alert',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Candidate Alert Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('candidate_alert_notice', 'subject') ),
						'id'      => 'candidate_alert_notice_subject',
						'type'    => 'text',
						'default' => sprintf(__( 'Candidate Alert: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('candidate_alert_notice', 'subject') ),
					),
					array(
						'name'    => __( 'Candidate Alert Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('candidate_alert_notice', 'content') ),
						'id'      => 'candidate_alert_notice_content',
						'type'    => 'wysiwyg',
						'default' =>  WP_Job_Board_Pro_Email::get_email_default_content('candidate_alert_notice'),
					),

					// Email Apply
					array(
						'name' => __( 'Email Apply Template', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_email_apply',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Email Apply Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('email_apply_job_notice', 'subject') ),
						'id'      => 'email_apply_job_notice_subject',
						'type'    => 'text',
						'default' => sprintf(__( 'Apply Job: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('email_apply_job_notice', 'subject') ),
					),
					array(
						'name'    => __( 'Email Apply Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('email_apply_job_notice', 'content') ),
						'id'      => 'email_apply_job_notice_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('email_apply_job_notice'),
					),

					// Internal Apply
					array(
						'name' => __( 'Internal Apply Template', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_internal_apply',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Internal Apply Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('internal_apply_job_notice', 'subject') ),
						'id'      => 'internal_apply_job_notice_subject',
						'type'    => 'text',
						'default' => sprintf(__( 'Apply Job: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('internal_apply_job_notice', 'subject') ),
					),
					array(
						'name'    => __( 'Internal Apply Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('internal_apply_job_notice', 'content') ),
						'id'      => 'internal_apply_job_notice_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('internal_apply_job_notice'),
					),

					// Applied Thanks
					array(
						'name' => __( 'Thanks For Applying Job Template', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_applied_thanks',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Thanks For Applying Job Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('applied_job_thanks_notice', 'subject') ),
						'id'      => 'applied_job_thanks_notice_subject',
						'type'    => 'text',
						'default' => sprintf(__( 'Thanks for applying the job: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('applied_job_thanks_notice', 'subject') ),
					),
					array(
						'name'    => __( 'Thanks For Applying Job Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('applied_job_thanks_notice', 'content') ),
						'id'      => 'applied_job_thanks_notice_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('applied_job_thanks_notice'),
					),

					// Create Meeting
					array(
						'name' => __( 'Create Meeting Template', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_create_meeting',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Create Meeting Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('meeting_create', 'subject') ),
						'id'      => 'meeting_create_subject',
						'type'    => 'text',
						'default' => sprintf(__( 'Apply Job: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('meeting_create', 'subject') ),
					),
					array(
						'name'    => __( 'Create Meeting Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('meeting_create', 'content') ),
						'id'      => 'meeting_create_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('meeting_create'),
					),

					// Re-schedule Meeting
					array(
						'name' => __( 'Re-schedule Meeting Template', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_reschedule_meeting',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Re-schedule Meeting Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('meeting_reschedule', 'subject') ),
						'id'      => 'meeting_reschedule_subject',
						'type'    => 'text',
						'default' => sprintf(__( 'Apply Job: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('meeting_reschedule', 'subject') ),
					),
					array(
						'name'    => __( 'Re-schedule Meeting Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('meeting_reschedule', 'content') ),
						'id'      => 'meeting_reschedule_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('meeting_reschedule'),
					),

					// Invite Candidate
					array(
						'name' => __( 'Invite Candidate Template', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_invite_candidate',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Invite Candidate Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('invite_candidate_notice', 'subject') ),
						'id'      => 'invite_candidate_notice_subject',
						'type'    => 'text',
						'default' => sprintf(__( 'Invite Candidate: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('invite_candidate_notice', 'subject') ),
					),
					array(
						'name'    => __( 'Invite Candidate Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('invite_candidate_notice', 'content') ),
						'id'      => 'invite_candidate_notice_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('invite_candidate_notice'),
					),

					// contact form
					array(
						'name' => __( 'Contact Form', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_contact_form',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Contact Form Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('contact_form_notice', 'subject') ),
						'id'      => 'contact_form_notice_subject',
						'type'    => 'text',
						'default' => sprintf(__( 'Contact Form: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('contact_form_notice', 'subject') ),
					),
					array(
						'name'    => __( 'Contact Form Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('contact_form_notice', 'content') ),
						'id'      => 'contact_form_notice_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('contact_form_notice'),
					),

					// Reject interview
					array(
						'name' => __( 'Reject interview', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_reject_interview',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Reject interview Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('reject_interview_notice', 'subject') ),
						'id'      => 'reject_interview_notice_subject',
						'type'    => 'text',
						'default' => sprintf(__( 'Reject interview: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('reject_interview_notice', 'subject') ),
					),
					array(
						'name'    => __( 'Reject interview Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('reject_interview_notice', 'content') ),
						'id'      => 'reject_interview_notice_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('reject_interview_notice'),
					),

					// Undo Reject interview
					array(
						'name' => __( 'Undo Reject interview', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_undo_reject_interview',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Undo Reject interview Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('undo_reject_interview_notice', 'subject') ),
						'id'      => 'undo_reject_interview_notice_subject',
						'type'    => 'text',
						'default' => sprintf(__( 'Reject interview: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('undo_reject_interview_notice', 'subject') ),
					),
					array(
						'name'    => __( 'Undo Reject interview Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('undo_reject_interview_notice', 'content') ),
						'id'      => 'undo_reject_interview_notice_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('undo_reject_interview_notice'),
					),

					// Approve interview
					array(
						'name' => __( 'Approve interview', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_approve_interview',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Approve interview Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('approve_interview_notice', 'subject') ),
						'id'      => 'approve_interview_notice_subject',
						'type'    => 'text',
						'default' => sprintf(__( 'Approve interview: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('approve_interview_notice', 'subject') ),
					),
					array(
						'name'    => __( 'Approve interview Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('approve_interview_notice', 'content') ),
						'id'      => 'approve_interview_notice_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('approve_interview_notice'),
					),

					// Undo Approve interview
					array(
						'name' => __( 'Undo Approve interview', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_undo_approve_interview',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Undo Approve interview Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('undo_approve_interview_notice', 'subject') ),
						'id'      => 'undo_approve_interview_notice_subject',
						'type'    => 'text',
						'default' => sprintf(__( 'Approve interview: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('undo_approve_interview_notice', 'subject') ),
					),
					array(
						'name'    => __( 'Undo Approve interview Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('undo_approve_interview_notice', 'content') ),
						'id'      => 'undo_approve_interview_notice_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('undo_approve_interview_notice'),
					),

					// Approve new user register
					array(
						'name' => __( 'New user register (auto approve)', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_user_register_auto_approve',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'New user register Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('user_register_auto_approve', 'subject') ),
						'id'      => 'user_register_auto_approve_subject',
						'type'    => 'text',
						'default' => sprintf(__( 'New user register: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('user_register_auto_approve', 'subject') ),
					),
					array(
						'name'    => __( 'New user register Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('user_register_auto_approve', 'content') ),
						'id'      => 'user_register_auto_approve_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('user_register_auto_approve'),
					),
					// Approve new user register
					array(
						'name' => __( 'Approve new user register', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_user_register_need_approve',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Approve new user register Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('user_register_need_approve', 'subject') ),
						'id'      => 'user_register_need_approve_subject',
						'type'    => 'text',
						'default' => sprintf(__( 'Approve new user register: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('user_register_need_approve', 'subject') ),
					),
					array(
						'name'    => __( 'Approve new user register Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('user_register_need_approve', 'content') ),
						'id'      => 'user_register_need_approve_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('user_register_need_approve'),
					),
					// Approved user register
					array(
						'name' => __( 'Approved user', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_user_register_approved',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Approved user Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('user_register_approved', 'subject') ),
						'id'      => 'user_register_approved_subject',
						'type'    => 'text',
						'default' => sprintf(__( 'Approve new user register: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('user_register_approved', 'subject') ),
					),
					array(
						'name'    => __( 'Approved user Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('user_register_approved', 'content') ),
						'id'      => 'user_register_approved_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('user_register_approved'),
					),
					// Denied user register
					array(
						'name' => __( 'Denied user', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_user_register_denied',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Denied user Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('user_register_denied', 'subject') ),
						'id'      => 'user_register_denied_subject',
						'type'    => 'text',
						'default' => sprintf(__( 'Approve new user register: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('user_register_denied', 'subject') ),
					),
					array(
						'name'    => __( 'Denied user Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('user_register_denied', 'content') ),
						'id'      => 'user_register_denied_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('user_register_denied'),
					),
					// Reset Password
					array(
						'name' => __( 'Reset Password', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_user_reset_password',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name'    => __( 'Reset Password Subject', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email subject. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('user_reset_password', 'subject') ),
						'id'      => 'user_reset_password_subject',
						'type'    => 'text',
						'default' => 'Your new password',
					),
					array(
						'name'    => __( 'Reset Password Content', 'wp-job-board-pro' ),
						'desc'    => sprintf(__( 'Enter email content. You can add variables: %s', 'wp-job-board-pro' ), WP_Job_Board_Pro_Email::display_email_vars('user_reset_password', 'content') ),
						'id'      => 'user_reset_password_content',
						'type'    => 'wysiwyg',
						'default' => WP_Job_Board_Pro_Email::get_email_default_content('user_reset_password'),
					),
				)
			)		 
		);

		// Indeed Jobs Import
		$wp_job_board_pro_settings['import_job_integrations'] = array(
			'id'         => 'options_page',
			'wp_job_board_pro_title' => __( 'Import Job Integrations', 'wp-job-board-pro' ),
			'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
			'fields'     => apply_filters( 'wp_job_board_pro_settings_import_job_integrations', array(
					array(
						'name' => __( 'Indeed Jobs Import', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_indeed_job_import',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name' => __( 'Enable Indeed Jobs Import', 'wp-job-board-pro' ),
						'id'   => 'indeed_job_import_enable',
						'type' => 'checkbox',
					),
					array(
                        'name'    => __( 'Publisher Number', 'wp-job-board-pro' ),
                        'id'      => 'indeed_job_import_number',
                        'type'    => 'text',
                        'desc' => wp_kses(__('Acquire an publisher ID from the <a href="https://www.indeed.com/publisher" target="_blank">https://www.indeed.com/publisher</a>', 'wp-job-board-pro'), array('a' => array('href' => array(), 'target' => array())))
                    ),

					array(
						'name' => __( 'Ziprecruiter Jobs Import', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_ziprecruiter_job_import',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name' => __( 'Enable Ziprecruiter Jobs import', 'wp-job-board-pro' ),
						'id'   => 'ziprecruiter_job_import_enable',
						'type' => 'checkbox',
					),
					array(
                        'name'    => __( 'Ziprecruiter API Key', 'wp-job-board-pro' ),
                        'id'      => 'ziprecruiter_job_import_api',
                        'type'    => 'text',
                        'desc' => wp_kses(__('Acquire an API key from the <a href="https://www.ziprecruiter.com/zipsearch" target="_blank">https://www.ziprecruiter.com/zipsearch</a>', 'wp-job-board-pro'), array('a' => array('href' => array(), 'target' => array())))
                    ),

					array(
						'name' => __( 'CareerJet Jobs Import', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_careerjet_job_import',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name' => __( 'Enable CareerJet Jobs import', 'wp-job-board-pro' ),
						'id'   => 'careerjet_job_import_enable',
						'type' => 'checkbox',
					),
					array(
                        'name'    => __( 'CareerJet AFFID', 'wp-job-board-pro' ),
                        'id'      => 'careerjet_job_import_api',
                        'type'    => 'text',
                        'desc' => wp_kses(__('Acquire an AFFID from the <a href="https://www.careerjet.com/contact-us" target="_blank">https://www.careerjet.com/contact-us</a>', 'wp-job-board-pro'), array('a' => array('href' => array(), 'target' => array())))
                    ),

					array(
						'name' => __( 'CareerBuilder Jobs Import', 'wp-job-board-pro' ),
						'desc' => '',
						'type' => 'wp_job_board_pro_title',
						'id'   => 'wp_job_board_pro_title_careerbuilder_job_import',
						'before_row' => '<hr>',
						'after_row'  => '<hr>'
					),
					array(
						'name' => __( 'Enable CareerBuilder Jobs import', 'wp-job-board-pro' ),
						'id'   => 'careerbuilder_job_import_enable',
						'type' => 'checkbox',
					),
					array(
                        'name'    => __( 'CareerBuilder API Key', 'wp-job-board-pro' ),
                        'id'      => 'careerbuilder_job_import_api',
                        'type'    => 'text',
                        'desc' => wp_kses(__('Acquire an AFFID from the <a href="https://developer.careerbuilder.com/" target="_blank">https://developer.careerbuilder.com/</a>', 'wp-job-board-pro'), array('a' => array('href' => array(), 'target' => array())))
                    ),
				)
			)		 
		);
		//Return all settings array if necessary

		if ( $active_tab === null   ) {  
			return apply_filters( 'wp_job_board_pro_registered_settings', $wp_job_board_pro_settings );
		}

		// Add other tabs and settings fields as needed
		return apply_filters( 'wp_job_board_pro_registered_'.$active_tab.'_settings', isset($wp_job_board_pro_settings[ $active_tab ])?$wp_job_board_pro_settings[ $active_tab ]:array() );

	}

	/**
	 * Show Settings Notices
	 *
	 * @param $object_id
	 * @param $updated
	 * @param $cmb
	 */
	public function settings_notices( $object_id, $updated, $cmb ) {

		//Sanity check
		if ( $object_id !== $this->key ) {
			return;
		}

		if ( did_action( 'cmb2_save_options-page_fields' ) === 1 ) {
			settings_errors( 'wp_job_board_pro-notices' );
		}

		add_settings_error( 'wp_job_board_pro-notices', 'global-settings-updated', __( 'Settings updated.', 'wp-job-board-pro' ), 'updated' );

	}


	/**
	 * Public getter method for retrieving protected/private variables
	 *
	 * @since  1.0
	 *
	 * @param  string $field Field to retrieve
	 *
	 * @return mixed          Field value or exception is thrown
	 */
	public function __get( $field ) {

		// Allowed fields to retrieve
		if ( in_array( $field, array( 'key', 'fields', 'wp_job_board_pro_title', 'options_page' ), true ) ) {
			return $this->{$field};
		}
		if ( 'option_metabox' === $field ) {
			return $this->option_metabox();
		}

		throw new Exception( 'Invalid property: ' . $field );
	}


}

// Get it started
$WP_Job_Board_Pro_Settings = new WP_Job_Board_Pro_Settings();

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 *
 * @param  string $key Options array key
 *
 * @return mixed        Option value
 */
function wp_job_board_pro_get_option( $key = '', $default = false ) {
	global $wp_job_board_pro_options;

	$wp_job_board_pro_options = wp_job_board_pro_get_settings();
	
	$value = isset( $wp_job_board_pro_options[ $key ] ) ? $wp_job_board_pro_options[ $key ] : $default;
	$value = apply_filters( 'wp_job_board_pro_get_option', $value, $key, $default );

	return apply_filters( 'wp_job_board_pro_get_option_' . $key, $value, $key, $default );
}



/**
 * Get Settings
 *
 * Retrieves all WP_Job_Board_Pro plugin settings
 *
 * @since 1.0
 * @return array WP_Job_Board_Pro settings
 */
function wp_job_board_pro_get_settings() {
	return apply_filters( 'wp_job_board_pro_get_settings', get_option( 'wp_job_board_pro_settings' ) );
}


/**
 * WP_Job_Board_Pro Title
 *
 * Renders custom section titles output; Really only an <hr> because CMB2's output is a bit funky
 *
 * @since 1.0
 *
 * @param       $field_object , $escaped_value, $object_id, $object_type, $field_type_object
 *
 * @return void
 */
function wp_job_board_pro_title_callback( $field_object, $escaped_value, $object_id, $object_type, $field_type_object ) {

	$id                = $field_type_object->field->args['id'];
	$title             = $field_type_object->field->args['name'];
	$field_description = $field_type_object->field->args['desc'];
	if ( $field_description ) {
		echo '<div class="desc">'.$field_description.'</div>';
	}
}

function wp_job_board_pro_hidden_callback( $field_object, $escaped_value, $object_id, $object_type, $field_type_object ) {
	$id                = $field_type_object->field->args['id'];
	$title             = $field_type_object->field->args['name'];
	$field_description = $field_type_object->field->args['desc'];
	echo '<input type="hidden" name="'.$id.'" value="'.$escaped_value.'">';
	if ( $field_type_object->field->args['human_value'] ) {
		echo '<strong>'.$field_type_object->field->args['human_value'].'</strong>';
	}
	if ( $field_description ) {
		echo '<div class="desc">'.$field_description.'</div>';
	}
}

/**
 * Gets a number of posts and displays them as options
 *
 * @param  array $query_args Optional. Overrides defaults.
 * @param  bool  $force      Force the pages to be loaded even if not on settings
 *
 * @see: https://github.com/WebDevStudios/CMB2/wiki/Adding-your-own-field-types
 * @return array An array of options that matches the CMB2 options array
 */
function wp_job_board_pro_cmb2_get_post_options( $query_args, $force = false ) {

	$post_options = array( '' => '' ); // Blank option

	if ( ( ! isset( $_GET['page'] ) || 'job_listing-settings' != $_GET['page'] ) && ! $force ) {
		return $post_options;
	}

	$args = wp_parse_args( $query_args, array(
		'post_type'   => 'page',
		'numberposts' => 10,
	) );

	$posts = get_posts( $args );

	if ( $posts ) {
		foreach ( $posts as $post ) {

			$post_options[ $post->ID ] = $post->post_title;

		}
	}

	return $post_options;
}


/**
 * Modify CMB2 Default Form Output
 *
 * @param string @args
 *
 * @since 1.0
 */

add_filter( 'cmb2_get_metabox_form_format', 'wp_job_board_pro_modify_cmb2_form_output', 10, 3 );

function wp_job_board_pro_modify_cmb2_form_output( $form_format, $object_id, $cmb ) {

	//only modify the wp_job_board_pro settings form
	if ( 'wp_job_board_pro_settings' == $object_id && 'options_page' == $cmb->cmb_id ) {

		return '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<div class="wp_job_board_pro-submit-wrap"><input type="submit" name="submit-cmb" value="' . __( 'Save Settings', 'wp-job-board-pro' ) . '" class="button-primary"></div></form>';
	}

	return $form_format;

}
