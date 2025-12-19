<?php
/**
 * Geocode
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Job_Board_Geocode {

	const GOOGLE_MAPS_GEOCODE_API_URL = 'https://maps.googleapis.com/maps/api/geocode/json';

	public static function init() {
		add_filter( 'wp-job-board-pro-geolocation-endpoint', [ __CLASS__, 'add_geolocation_endpoint_query_args' ], 0, 2 );
		add_filter( 'wp-job-board-pro-geolocation-api-key', [ __CLASS__, 'get_google_maps_api_key' ], 0 );
	}

	public static function generate_location_data( $job_id, $location ) {
		$address_data = self::get_location_data( $location );
		self::save_location_data( $job_id, $address_data );
	}

	public static function save_location_data( $job_id, $address_data ) {
		if ( ! is_wp_error( $address_data ) && $address_data ) {
			$maps_address = [
				'latitude' => '',
				'longitude' => '',
				'address' => '',
			];
			if ( $address_data['latitude'] ) {
				$maps_address['latitude'] = $address_data['latitude'];
			}
			if ( $address_data['longitude'] ) {
				$maps_address['longitude'] = $address_data['longitude'];
			}
			if ( $address_data['formatted_address'] ) {
				$maps_address['address'] = $address_data['formatted_address'];
			}
			WP_Job_Board_Pro_Job_Listing::update_post_meta( $post->ID, 'map_location_latitude', $maps_address['latitude'] );
			WP_Job_Board_Pro_Job_Listing::update_post_meta( $post->ID, 'map_location_longitude', $maps_address['longitude'] );
			WP_Job_Board_Pro_Job_Listing::update_post_meta( $post->ID, 'map_location_address', $maps_address['address'] );
			WP_Job_Board_Pro_Job_Listing::update_post_meta( $post->ID, 'map_location', $maps_address );

			WP_Job_Board_Pro_Job_Listing::update_post_meta( $post->ID, 'map_location_properties', $address_data );
		}
	}

	public static function get_google_maps_api_key( $key ) {
		if ( wp_job_board_pro_get_option('map_service') == 'google-map' ) {
			return wp_job_board_pro_get_option('google_map_api_keys');
		}
		return '';
	}

	/**
	 * Adds the necessary query arguments for a Google Maps Geocode API request.
	 *
	 * @param  string $geocode_endpoint_url
	 * @param  string $raw_address
	 * @return string|bool
	 */
	public static function add_geolocation_endpoint_query_args( $geocode_endpoint_url, $raw_address ) {
		// Add an API key if available.
		$api_key = apply_filters( 'wp-job-board-pro-geolocation-api-key', '', $raw_address );

		if ( '' !== $api_key ) {
			$geocode_endpoint_url = add_query_arg( 'key', rawurlencode( $api_key ), $geocode_endpoint_url );
		}

		$geocode_endpoint_url = add_query_arg( 'address', rawurlencode( $raw_address ), $geocode_endpoint_url );

		$locale = get_locale();
		if ( $locale ) {
			$geocode_endpoint_url = add_query_arg( 'language', substr( $locale, 0, 2 ), $geocode_endpoint_url );
		}

		$region = apply_filters( 'wp-job-board-pro-geolocation-region-cctld', '', $raw_address );
		if ( '' !== $region ) {
			$geocode_endpoint_url = add_query_arg( 'region', rawurlencode( $region ), $geocode_endpoint_url );
		}

		return $geocode_endpoint_url;
	}

	/**
	 * Gets Location Data from Google.
	 *
	 * Based on code by Eyal Fitoussi.
	 *
	 * @param string $raw_address
	 * @return array|bool location data.
	 * @throws Exception After geocoding error.
	 */
	public static function get_location_data( $raw_address ) {
		$invalid_chars = [
			' ' => '+',
			',' => '',
			'?' => '',
			'&' => '',
			'=' => '',
			'#' => '',
		];
		$raw_address   = trim( strtolower( str_replace( array_keys( $invalid_chars ), array_values( $invalid_chars ), $raw_address ) ) );

		if ( empty( $raw_address ) ) {
			return false;
		}

		$transient_name              = 'wjbp_geocode_' . md5( $raw_address );
		$geocoded_address            = get_transient( $transient_name );
		$wjbp_geocode_over_query_limit = get_transient( 'wjbp_geocode_over_query_limit' );

		// Query limit reached - don't geocode for a while.
		if ( $wjbp_geocode_over_query_limit && false === $geocoded_address ) {
			return false;
		}

		$geocode_api_url = apply_filters( 'wp-job-board-pro-geolocation-endpoint', self::GOOGLE_MAPS_GEOCODE_API_URL, $raw_address );
		if ( false === $geocode_api_url ) {
			return false;
		}

		try {
			if ( false === $geocoded_address || empty( $geocoded_address->results[0] ) ) {
				$result           = wp_remote_get(
					$geocode_api_url,
					[
						'timeout'     => 5,
						'redirection' => 1,
						'httpversion' => '1.1',
						'user-agent'  => 'WordPress/WP-Job-Board-Pro-' . WP_JOB_BOARD_PRO_PLUGIN_VERSION . '; ' . get_bloginfo( 'url' ),
						'sslverify'   => false,
					]
				);
				$result           = wp_remote_retrieve_body( $result );
				$geocoded_address = json_decode( $result );

				if ( isset( $geocoded_address->status ) ) {
					if ( 'ZERO_RESULTS' === $geocoded_address->status ) {
						throw new Exception( __( 'No results found', 'wp-job-board-pro' ) );
					} elseif ( 'OVER_QUERY_LIMIT' === $geocoded_address->status ) {
						set_transient( 'wjbp_geocode_over_query_limit', 1, HOUR_IN_SECONDS );
						throw new Exception( __( 'Query limit reached', 'wp-job-board-pro' ) );
					} elseif ( 'OK' === $geocoded_address->status && ! empty( $geocoded_address->results[0] ) ) {
						set_transient( $transient_name, $geocoded_address, DAY_IN_SECONDS * 7 );
					} else {
						throw new Exception( __( 'Geocoding error', 'wp-job-board-pro' ) );
					}
				} else {
					throw new Exception( __( 'Geocoding error', 'wp-job-board-pro' ) );
				}
			}
		} catch ( Exception $e ) {
			return new WP_Error( 'error', $e->getMessage() );
		}

		$address                      = [];
		$address['latitude']               = sanitize_text_field( $geocoded_address->results[0]->geometry->location->lat );
		$address['longitude']              = sanitize_text_field( $geocoded_address->results[0]->geometry->location->lng );
		$address['formatted_address'] = sanitize_text_field( $geocoded_address->results[0]->formatted_address );

		if ( ! empty( $geocoded_address->results[0]->address_components ) ) {
			$address_data             = $geocoded_address->results[0]->address_components;
			$address['house_number'] 	= false;
			$address['road']        	= false;
			$address['city']          	= false;
			$address['state']   		= false;
			$address['state_long']    = false;
			$address['postcode']      = false;
			$address['country_code'] = false;
			$address['country_long']  = false;

			foreach ( $address_data as $data ) {
				switch ( $data->types[0] ) {
					case 'house_number':
						$address['house_number'] = sanitize_text_field( $data->long_name );
						break;
					case 'route':
						$address['road'] = sanitize_text_field( $data->long_name );
						break;
					case 'sublocality_level_1':
					case 'locality':
					case 'postal_town':
						$address['city'] = sanitize_text_field( $data->long_name );
						break;
					case 'administrative_area_level_1':
					case 'administrative_area_level_2':
						$address['state'] = sanitize_text_field( $data->short_name );
						$address['state_long']  = sanitize_text_field( $data->long_name );
						break;
					case 'postal_code':
						$address['postcode'] = sanitize_text_field( $data->long_name );
						break;
					case 'country':
						$address['country_code'] = sanitize_text_field( $data->short_name );
						$address['country_long']  = sanitize_text_field( $data->long_name );
						break;
				}
			}
		}

		return apply_filters( 'wp-job-board-pro-geolocation-get-location-data', $address, $geocoded_address );
	}
}

WP_Job_Board_Geocode::init();
