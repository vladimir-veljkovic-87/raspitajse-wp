<?php
/**
 * Stuctured Data
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Structured_Data {
	
	public static function init() {
		add_action( 'wp_footer', array( __CLASS__, 'output_structured_data' ) );
	}

	public static function output_structured_data() {
		if ( ! is_single() ) {
			return;
		}
		global $post;
		if ( ! self::output_job_listing_structured_data($post->ID) ) {
			return;
		}

		$structured_data = self::get_job_listing_structured_data($post->ID);
		if ( ! empty( $structured_data ) ) {
			echo '<!-- WP Job Board Pro Structured Data -->' . "\r\n";
			echo '<script type="application/ld+json">' . self::esc_json( wp_json_encode( $structured_data ), true ) . '</script>';
		}
	}

	public static function output_job_listing_structured_data( $post = null ) {
		$post = get_post( $post );

		if ( ! $post || 'job_listing' !== $post->post_type ) {
			return false;
		}
		$filled = WP_Job_Board_Pro_Job_Listing::get_post_meta($post->ID, 'filled', true);
		$output_structured_data = !$filled && 'publish' === $post->post_status;

		return apply_filters( 'wp_job_board_pro_output_job_listing_structured_data', $output_structured_data, $post );
	}

	/**
	 * Gets the structured data for the job listing.
	 * @see https://developers.google.com/search/docs/data-types/job-postings
	 *
	 */
	public static function get_job_listing_structured_data( $post = null ) {
		$post = get_post( $post );

		if ( ! $post || 'job_listing' !== $post->post_type ) {
			return false;
		}

		$data               = array();
		$data['@context']   = 'http://schema.org/';
		$data['@type']      = 'JobPosting';
		$data['datePosted'] = get_post_time( 'c', false, $post );

		$job_expires = get_post_meta( $post->ID, '_job_expires', true );
		if ( ! empty( $job_expires ) ) {
			$data['validThrough'] = date( 'c', strtotime( $job_expires ) );
		}

		$data['title']       = wp_strip_all_tags( get_the_title( $post ) );
		$data['description'] = wp_strip_all_tags(apply_filters( 'the_content', $post->post_content ));

		$employment_types = self::get_job_employment_types($post);
		if ( ! empty( $employment_types ) ) {
			$data['employmentType'] = $employment_types;
		}

		$data['hiringOrganization']          = array();
		$data['hiringOrganization']['@type'] = 'Organization';
		$data['hiringOrganization']['name']  = self::get_company_name( $post );

		$company_website = self::get_company_website( $post );
		if ( $company_website ) {
			$data['hiringOrganization']['sameAs'] = $company_website;
			$data['hiringOrganization']['url']    = $company_website;
		}

		$company_logo = self::get_company_logo( $post, 'full' );
		if ( $company_logo ) {
			$data['hiringOrganization']['logo'] = $company_logo;
		}

		$data['identifier']          = array();
		$data['identifier']['@type'] = 'PropertyValue';
		$data['identifier']['name']  = self::get_company_name( $post );
		$data['identifier']['value'] = get_the_guid( $post );

		$meta_obj = WP_Job_Board_Pro_Job_Listing_Meta::get_instance($post->ID);
		if ( $meta_obj->check_post_meta_exist('location') ) {
			$locations = get_the_terms( $post->ID, 'job_listing_location' );
			$locations_list = array();
			if ( !empty( $locations ) && !is_wp_error( $locations ) ) {
				foreach ( $locations as $term ) {
					$locations_list[] = $term->name;
				}

				$data['jobLocation']            = array();
				$data['jobLocation']['@type']   = 'Place';
				$data['jobLocation']['address'] = implode(', ', $locations_list);
			}
		}

		$location = self::get_job_location( $post );
		
		if ( ! empty( $location ) ) {
			$data['jobLocation']            = array();
			$data['jobLocation']['@type']   = 'Place';
			$data['jobLocation']['address'] = self::get_job_listing_location_structured_data( $post );
			if ( empty($data['jobLocation']['address']) ) {
				$data['jobLocation']['address'] = $location;
			}
		}

		$min_salary = WP_Job_Board_Pro_Job_Listing::get_post_meta( $post->ID, 'salary', true );
		$max_salary = WP_Job_Board_Pro_Job_Listing::get_post_meta( $post->ID, 'max_salary', true );

		$symbol = wp_job_board_pro_get_option('custom_symbol');
		if ( empty($symbol) ) {
			$currency = wp_job_board_pro_get_option('currency', 'USD');
			$symbol = WP_Job_Board_Pro_Price::currency_symbol($currency);
		}
		
		$salary_type = WP_Job_Board_Pro_Job_Listing::get_post_meta( $post->ID, 'salary_type', true );
		$unitText = '';
		switch ($salary_type) {
			case 'yearly':
				$unitText = 'YEAR';
				break;
			case 'monthly':
				$unitText = 'MONTH';
				break;
			case 'weekly':
				$unitText = 'WEEK';
				break;
			case 'hourly':
				$unitText = 'HOUR';
				break;
		}
		$data['baseSalary']['@type'] = 'MonetaryAmount';
		$data['baseSalary']['currency'] = !empty($symbol) ? $symbol : 'USD';
		$data['baseSalary']['value']['@type'] = 'QuantitativeValue';
		$data['baseSalary']['value']['minValue'] = $min_salary;
		$data['baseSalary']['value']['maxValue'] = $max_salary;
		$data['baseSalary']['value']['value'] = $min_salary;
		$data['baseSalary']['value']['unitText'] = $unitText;
		
		// expired
		$expiry_date = WP_Job_Board_Pro_Job_Listing::get_post_meta( $post->ID, 'expiry_date', true );
		if ( $expiry_date ) {
			$data['validThrough'] = $expiry_date;
		}
		
		return apply_filters( 'wp_job_board_pro_get_job_listing_structured_data', $data, $post );
	}

	public static function get_job_listing_location_structured_data( $post ) {
		
		$mapping                    = array();
		$mapping['streetAddress']   = array( 'house_number', 'road' );
		$mapping['addressLocality'] = 'city';
		$mapping['addressRegion']   = 'state';
		$mapping['postalCode']      = 'postcode';
		$mapping['addressCountry']  = 'country_code';

		
		$address          = array();
		$address['@type'] = 'PostalAddress';
		$properties = WP_Job_Board_Pro_Job_Listing::get_post_meta( $post->ID, 'map_location_properties', true );
		if ( !empty($properties) && is_array($properties) ) {
			foreach ( $mapping as $schema_key => $geolocation_key ) {
				if ( is_array( $geolocation_key ) ) {
					$values = array();
					foreach ( $geolocation_key as $sub_geo_key ) {
						if ( ! empty( $properties[$sub_geo_key] ) ) {
							$values[] = $properties[$sub_geo_key];
						}
					}
					$value = implode( ' ', $values );
				} else {
					$value = ! empty( $properties[$geolocation_key] ) ?  $properties[$geolocation_key] : '';
				}
				if ( ! empty( $value ) ) {
					$address[ $schema_key ] = $value;
				}
			}
		}
		
		if ( 1 === count( $address ) ) {
			$address = false;
		}

		return apply_filters( 'wp_job_board_pro_get_job_listing_location_structured_data', $address, $post );
	}

	public static function esc_json( $json, $html = false ) {
		return _wp_specialchars(
			$json,
			$html ? ENT_NOQUOTES : ENT_QUOTES,
			'UTF-8',
			true
		);
	}

	public static function get_company_name($post) {
		$author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($post->ID);
		$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);
		if ( $employer_id ) {
			return wp_strip_all_tags( get_the_title($employer_id) );
		} else {
			return '';
		}
	}

	public static function get_company_website( $post = null ) {
		$author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($post->ID);
		$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);
		if ( $employer_id ) {

			$website = WP_Job_Board_Pro_Employer::get_post_meta($employer_id, 'website', true);

			if ( $website && ! strstr( $website, 'http:' ) && ! strstr( $website, 'https:' ) ) {
				$website = 'http://' . $website;
			}

			return apply_filters( 'wp_job_board_pro_the_company_website', $website, $post );
		}
		return '';
	}

	public static function get_company_logo( $post, $size = 'thumbnail' ) {
		$author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($post->ID);
		$employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);

		if ( $employer_id ) {
			if ( has_post_thumbnail( $employer_id ) ) {
				$src = wp_get_attachment_image_src( get_post_thumbnail_id( $employer_id ), $size );
				return $src ? $src[0] : '';
			} elseif ( ! empty( has_post_thumbnail( $post->ID ) ) ) {
				$src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $size );
				return $src ? $src[0] : '';
			}
		}

		return '';
	}

	public static function get_job_location( $post ) {
		$location = WP_Job_Board_Pro_Job_Listing::get_post_meta($post->ID, 'address', true);
		if ( empty($location) ) {
			$location = WP_Job_Board_Pro_Job_Listing::get_post_meta( $post->ID, 'map_location_address', true );
		}

		return $location;
	}

	public static function get_job_employment_types( $post ) {
		$employment_types = array();
		$job_types        = self::get_the_job_types( $post );

		if ( ! empty( $job_types ) ) {
			foreach ( $job_types as $job_type ) {
				$employment_type = get_term_meta( $job_type->term_id, '_employment_type', true );
				if ( ! empty( $employment_type ) ) {
					$employment_types[] = $employment_type;
				}
			}
		}

		return apply_filters( 'wp_job_board_pro_get_job_employment_types', array_unique( $employment_types ), $post );
	}

	public static function get_the_job_types( $post ) {
		
		$types = get_the_terms( $post->ID, 'job_listing_type' );

		if ( empty( $types ) || is_wp_error( $types ) ) {
			$types = array();
		}

		return apply_filters( 'wp_job_board_pro_the_job_types', $types, $post );
	}
}

WP_Job_Board_Pro_Structured_Data::init();