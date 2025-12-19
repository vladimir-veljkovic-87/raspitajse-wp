<?php
/**
 * Price
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Price {
	
	public static function init() {
	    add_action( 'init', array( __CLASS__, 'process_currency' ) );
	}

	/**
	 * Formats price
	 *
	 * @access public
	 * @param $price
	 * @return bool|string
	 */
	public static function format_price111( $price, $show_null = true ) {
		if ( empty( $price ) || ! is_numeric( $price ) ) {
			if ( !$show_null ) {
				return false;
			}
			$price = 0;
		}

		$price = WP_Job_Board_Pro_Mixes::format_number( $price );

		$symbol = wp_job_board_pro_get_option('custom_symbol');
		if ( empty($symbol) ) {
			$currency = wp_job_board_pro_get_option('currency', 'USD');
			$symbol = self::currency_symbol($currency);
		}
		$currency_position = wp_job_board_pro_get_option('currency_position', 'before');

		$currency_symbol = ! empty( $symbol ) ? '<span class="suffix">'.$symbol.'</span>' : '<span class="suffix">$</span>';

		switch ($currency_position) {
			case 'before':
				$price = $currency_symbol . '<span class="price-text">'.$price.'</span>';
				break;
			case 'after':
				$price = '<span class="price-text">'.$price.'</span>' . $currency_symbol;
				break;
			case 'before_space':
				$price = $currency_symbol . ' <span class="price-text">'.$price.'</span>';
				break;
			case 'after_space':
				$price = '<span class="price-text">'.$price.'</span> ' . $currency_symbol;
				break;
		}

		return $price;
	}

	public static function format_price( $price, $show_null = false, $without_rate_exchange = false ) {
		if ( empty( $price ) || ! is_numeric( $price ) ) {
			if ( !$show_null ) {
				return false;
			}
			$price = 0;
		}
		$decimals = false;
		$money_decimals = wp_job_board_pro_get_option('money_decimals');

		if ( wp_job_board_pro_get_option('enable_multi_currencies') === 'yes' ) {
			$current_currency = self::get_current_currency();
			$multi_currencies = self::get_currencies_settings();

			if ( !empty($multi_currencies) && !empty($multi_currencies[$current_currency]) ) {
				$currency_args = $multi_currencies[$current_currency];
			}

			if ( !empty($currency_args) ) {
				if ( !empty($currency_args['custom_symbol']) ) {
					$symbol = $currency_args['custom_symbol'];
				} else {
					$currency = $currency_args['currency'];
					$symbol = WP_Job_Board_Pro_Price::currency_symbol($currency);
				}
				$currency_position = !empty($currency_args['currency_position']) ? $currency_args['currency_position'] : 'before';
				$rate_exchange_fee = !empty($currency_args['rate_exchange_fee']) ? $currency_args['rate_exchange_fee'] : 1;
				$decimals = true;
				$money_decimals = !empty($currency_args['money_decimals']) ? $currency_args['money_decimals'] : 1;
				if ( !$without_rate_exchange ) {
					$price = $price*$rate_exchange_fee;
				}
			} else {
				$symbol = wp_job_board_pro_get_option('custom_symbol', '');
				if ( empty($symbol) ) {
					$currency = wp_job_board_pro_get_option('currency', 'USD');
					$symbol = WP_Job_Board_Pro_Price::currency_symbol($currency);
				}
				$currency_position = wp_job_board_pro_get_option('currency_position', 'before');
			}
		} else {
			$symbol = wp_job_board_pro_get_option('custom_symbol', '');
			if ( empty($symbol) ) {
				$currency = wp_job_board_pro_get_option('currency', 'USD');
				$symbol = WP_Job_Board_Pro_Price::currency_symbol($currency);
			}
			$currency_position = wp_job_board_pro_get_option('currency_position', 'before');
		}

		$currency_symbol = ! empty( $symbol ) ? '<span class="suffix">'.$symbol.'</span>' : '<span class="suffix">$</span>';

		$price = WP_Job_Board_Pro_Mixes::format_number( $price, $decimals, $money_decimals );

		switch ($currency_position) {
			case 'before':
				$price = $currency_symbol . '<span class="price-text">'.$price.'</span>';
				break;
			case 'after':
				$price = '<span class="price-text">'.$price.'</span>' . $currency_symbol;
				break;
			case 'before_space':
				$price = $currency_symbol . ' <span class="price-text">'.$price.'</span>';
				break;
			case 'after_space':
				$price = '<span class="price-text">'.$price.'</span> ' . $currency_symbol;
				break;
		}

		return $price;
	}

	public static function format_price_without_html111( $price, $show_null = false ) {
		if ( empty( $price ) || ! is_numeric( $price ) ) {
			if ( !$show_null ) {
				return false;
			}
			$price = 0;
		}

		$price = WP_Job_Board_Pro_Mixes::format_number( $price );

		$symbol = wp_job_board_pro_get_option('custom_symbol');
		if ( empty($symbol) ) {
			$currency = wp_job_board_pro_get_option('currency', 'USD');
			$symbol = self::currency_symbol($currency);
		}
		$currency_position = wp_job_board_pro_get_option('currency_position', 'before');

		$currency_symbol = ! empty( $symbol ) ? $symbol : '$';

		switch ($currency_position) {
			case 'before':
				$price = $currency_symbol . $price;
				break;
			case 'after':
				$price = $price . $currency_symbol;
				break;
			case 'before_space':
				$price = $currency_symbol.' '.$price;
				break;
			case 'after_space':
				$price = $price.' '.$currency_symbol;
				break;
		}

		return $price;
	}

	public static function format_price_without_html( $price, $show_null = false, $without_rate_exchange = false ) {
		if ( empty( $price ) || ! is_numeric( $price ) ) {
			if ( !$show_null ) {
				return false;
			}
			$price = 0;
		}
		$decimals = false;
		$money_decimals = wp_job_board_pro_get_option('money_decimals');

		if ( wp_job_board_pro_get_option('enable_multi_currencies') === 'yes' ) {
			$current_currency = self::get_current_currency();
			$multi_currencies = self::get_currencies_settings();

			if ( !empty($multi_currencies) && !empty($multi_currencies[$current_currency]) ) {
				$currency_args = $multi_currencies[$current_currency];
			}

			if ( !empty($currency_args) ) {
				if ( !empty($currency_args['custom_symbol']) ) {
					$symbol = $currency_args['custom_symbol'];
				} else {
					$currency = $currency_args['currency'];
					$symbol = WP_Job_Board_Pro_Price::currency_symbol($currency);
				}
				$currency_position = !empty($currency_args['currency_position']) ? $currency_args['currency_position'] : 'before';
				$rate_exchange_fee = !empty($currency_args['rate_exchange_fee']) ? $currency_args['rate_exchange_fee'] : 1;
				$decimals = true;
				$money_decimals = !empty($currency_args['money_decimals']) ? $currency_args['money_decimals'] : 1;
				if ( !$without_rate_exchange ) {
					$price = $price*$rate_exchange_fee;
				}
			} else {
				$symbol = wp_job_board_pro_get_option('custom_symbol', '$');
				if ( empty($symbol) ) {
					$currency = wp_job_board_pro_get_option('currency', 'USD');
					$symbol = WP_Job_Board_Pro_Price::currency_symbol($currency);
				}
				$currency_position = wp_job_board_pro_get_option('currency_position', 'before');
			}
		} else {
			$symbol = wp_job_board_pro_get_option('custom_symbol', '$');
			if ( empty($symbol) ) {
				$currency = wp_job_board_pro_get_option('currency', 'USD');
				$symbol = WP_Job_Board_Pro_Price::currency_symbol($currency);
			}
			$currency_position = wp_job_board_pro_get_option('currency_position', 'before');
		}

		$price = WP_Job_Board_Pro_Mixes::format_number( $price, $decimals, $money_decimals );

		$currency_symbol = ! empty( $symbol ) ? $symbol : '$';

		switch ($currency_position) {
			case 'before':
				$price = $currency_symbol . $price;
				break;
			case 'after':
				$price = $price . $currency_symbol;
				break;
			case 'before_space':
				$price = $currency_symbol.' '.$price;
				break;
			case 'after_space':
				$price = $price.' '.$currency_symbol;
				break;
		}

		return $price;
	}

	public static function convert_price_exchange( $price ) {
		if ( empty( $price ) || ! is_numeric( $price ) ) {
			$price = 0;
		}
		if ( wp_job_board_pro_get_option('enable_multi_currencies') === 'yes' ) {
			$current_currency = self::get_current_currency();
			$multi_currencies = self::get_currencies_settings();

			if ( !empty($multi_currencies) && !empty($multi_currencies[$current_currency]) ) {
				$currency_args = $multi_currencies[$current_currency];
			}

			if ( !empty($currency_args) ) {
				$rate_exchange_fee = !empty($currency_args['rate_exchange_fee']) ? $currency_args['rate_exchange_fee'] : 1;
				$price = $price*$rate_exchange_fee;
			}
		}

		return $price;
	}
	
	public static function convert_current_currency_to_default( $price ) {
		if ( empty( $price ) || ! is_numeric( $price ) ) {
			$price = 0;
		}
		if ( wp_job_board_pro_get_option('enable_multi_currencies') === 'yes' ) {
			$current_currency = self::get_current_currency();
			$multi_currencies = self::get_currencies_settings();

			if ( !empty($multi_currencies) && !empty($multi_currencies[$current_currency]) ) {
				$currency_args = $multi_currencies[$current_currency];
			}

			if ( !empty($currency_args) ) {
				$rate_exchange_fee = !empty($currency_args['rate_exchange_fee']) ? $currency_args['rate_exchange_fee'] : 1;

				$price = $price*(1/$rate_exchange_fee);
			}
		}

		return $price;
	}

	public static function get_current_currency() {
		if ( wp_job_board_pro_get_option('enable_multi_currencies') === 'yes' ) {
			$current_currency = !empty($_COOKIE['wp_job_board_pro_currency']) ? $_COOKIE['wp_job_board_pro_currency'] : wp_job_board_pro_get_option('currency', 'USD');
		} else {
			$current_currency = wp_job_board_pro_get_option('currency', 'USD');
		}
		return $current_currency;
	}

	/**
	 * Get full list of currency codes.
	 *
	 * Currency symbols and names should follow the Unicode CLDR recommendation (http://cldr.unicode.org/translation/currency-names)
	 *
	 * @return array
	 */
	public static function get_currencies() {
		$currencies = array_unique(
			apply_filters(
				'wp-job-board-pro-currencies',
				array(
					'AED' => __( 'United Arab Emirates dirham', 'wp-job-board-pro' ),
					'AFN' => __( 'Afghan afghani', 'wp-job-board-pro' ),
					'ALL' => __( 'Albanian lek', 'wp-job-board-pro' ),
					'AMD' => __( 'Armenian dram', 'wp-job-board-pro' ),
					'ANG' => __( 'Netherlands Antillean guilder', 'wp-job-board-pro' ),
					'AOA' => __( 'Angolan kwanza', 'wp-job-board-pro' ),
					'ARS' => __( 'Argentine peso', 'wp-job-board-pro' ),
					'AUD' => __( 'Australian dollar', 'wp-job-board-pro' ),
					'AWG' => __( 'Aruban florin', 'wp-job-board-pro' ),
					'AZN' => __( 'Azerbaijani manat', 'wp-job-board-pro' ),
					'BAM' => __( 'Bosnia and Herzegovina convertible mark', 'wp-job-board-pro' ),
					'BBD' => __( 'Barbadian dollar', 'wp-job-board-pro' ),
					'BDT' => __( 'Bangladeshi taka', 'wp-job-board-pro' ),
					'BGN' => __( 'Bulgarian lev', 'wp-job-board-pro' ),
					'BHD' => __( 'Bahraini dinar', 'wp-job-board-pro' ),
					'BIF' => __( 'Burundian franc', 'wp-job-board-pro' ),
					'BMD' => __( 'Bermudian dollar', 'wp-job-board-pro' ),
					'BND' => __( 'Brunei dollar', 'wp-job-board-pro' ),
					'BOB' => __( 'Bolivian boliviano', 'wp-job-board-pro' ),
					'BRL' => __( 'Brazilian real', 'wp-job-board-pro' ),
					'BSD' => __( 'Bahamian dollar', 'wp-job-board-pro' ),
					'BTC' => __( 'Bitcoin', 'wp-job-board-pro' ),
					'BTN' => __( 'Bhutanese ngultrum', 'wp-job-board-pro' ),
					'BWP' => __( 'Botswana pula', 'wp-job-board-pro' ),
					'BYR' => __( 'Belarusian ruble (old)', 'wp-job-board-pro' ),
					'BYN' => __( 'Belarusian ruble', 'wp-job-board-pro' ),
					'BZD' => __( 'Belize dollar', 'wp-job-board-pro' ),
					'CAD' => __( 'Canadian dollar', 'wp-job-board-pro' ),
					'CDF' => __( 'Congolese franc', 'wp-job-board-pro' ),
					'CHF' => __( 'Swiss franc', 'wp-job-board-pro' ),
					'CLP' => __( 'Chilean peso', 'wp-job-board-pro' ),
					'CNY' => __( 'Chinese yuan', 'wp-job-board-pro' ),
					'COP' => __( 'Colombian peso', 'wp-job-board-pro' ),
					'CRC' => __( 'Costa Rican col&oacute;n', 'wp-job-board-pro' ),
					'CUC' => __( 'Cuban convertible peso', 'wp-job-board-pro' ),
					'CUP' => __( 'Cuban peso', 'wp-job-board-pro' ),
					'CVE' => __( 'Cape Verdean escudo', 'wp-job-board-pro' ),
					'CZK' => __( 'Czech koruna', 'wp-job-board-pro' ),
					'DJF' => __( 'Djiboutian franc', 'wp-job-board-pro' ),
					'DKK' => __( 'Danish krone', 'wp-job-board-pro' ),
					'DOP' => __( 'Dominican peso', 'wp-job-board-pro' ),
					'DZD' => __( 'Algerian dinar', 'wp-job-board-pro' ),
					'EGP' => __( 'Egyptian pound', 'wp-job-board-pro' ),
					'ERN' => __( 'Eritrean nakfa', 'wp-job-board-pro' ),
					'ETB' => __( 'Ethiopian birr', 'wp-job-board-pro' ),
					'EUR' => __( 'Euro', 'wp-job-board-pro' ),
					'FJD' => __( 'Fijian dollar', 'wp-job-board-pro' ),
					'FKP' => __( 'Falkland Islands pound', 'wp-job-board-pro' ),
					'GBP' => __( 'Pound sterling', 'wp-job-board-pro' ),
					'GEL' => __( 'Georgian lari', 'wp-job-board-pro' ),
					'GGP' => __( 'Guernsey pound', 'wp-job-board-pro' ),
					'GHS' => __( 'Ghana cedi', 'wp-job-board-pro' ),
					'GIP' => __( 'Gibraltar pound', 'wp-job-board-pro' ),
					'GMD' => __( 'Gambian dalasi', 'wp-job-board-pro' ),
					'GNF' => __( 'Guinean franc', 'wp-job-board-pro' ),
					'GTQ' => __( 'Guatemalan quetzal', 'wp-job-board-pro' ),
					'GYD' => __( 'Guyanese dollar', 'wp-job-board-pro' ),
					'HKD' => __( 'Hong Kong dollar', 'wp-job-board-pro' ),
					'HNL' => __( 'Honduran lempira', 'wp-job-board-pro' ),
					'HRK' => __( 'Croatian kuna', 'wp-job-board-pro' ),
					'HTG' => __( 'Haitian gourde', 'wp-job-board-pro' ),
					'HUF' => __( 'Hungarian forint', 'wp-job-board-pro' ),
					'IDR' => __( 'Indonesian rupiah', 'wp-job-board-pro' ),
					'ILS' => __( 'Israeli new shekel', 'wp-job-board-pro' ),
					'IMP' => __( 'Manx pound', 'wp-job-board-pro' ),
					'INR' => __( 'Indian rupee', 'wp-job-board-pro' ),
					'IQD' => __( 'Iraqi dinar', 'wp-job-board-pro' ),
					'IRR' => __( 'Iranian rial', 'wp-job-board-pro' ),
					'IRT' => __( 'Iranian toman', 'wp-job-board-pro' ),
					'ISK' => __( 'Icelandic kr&oacute;na', 'wp-job-board-pro' ),
					'JEP' => __( 'Jersey pound', 'wp-job-board-pro' ),
					'JMD' => __( 'Jamaican dollar', 'wp-job-board-pro' ),
					'JOD' => __( 'Jordanian dinar', 'wp-job-board-pro' ),
					'JPY' => __( 'Japanese yen', 'wp-job-board-pro' ),
					'KES' => __( 'Kenyan shilling', 'wp-job-board-pro' ),
					'KGS' => __( 'Kyrgyzstani som', 'wp-job-board-pro' ),
					'KHR' => __( 'Cambodian riel', 'wp-job-board-pro' ),
					'KMF' => __( 'Comorian franc', 'wp-job-board-pro' ),
					'KPW' => __( 'North Korean won', 'wp-job-board-pro' ),
					'KRW' => __( 'South Korean won', 'wp-job-board-pro' ),
					'KWD' => __( 'Kuwaiti dinar', 'wp-job-board-pro' ),
					'KYD' => __( 'Cayman Islands dollar', 'wp-job-board-pro' ),
					'KZT' => __( 'Kazakhstani tenge', 'wp-job-board-pro' ),
					'LAK' => __( 'Lao kip', 'wp-job-board-pro' ),
					'LBP' => __( 'Lebanese pound', 'wp-job-board-pro' ),
					'LKR' => __( 'Sri Lankan rupee', 'wp-job-board-pro' ),
					'LRD' => __( 'Liberian dollar', 'wp-job-board-pro' ),
					'LSL' => __( 'Lesotho loti', 'wp-job-board-pro' ),
					'LYD' => __( 'Libyan dinar', 'wp-job-board-pro' ),
					'MAD' => __( 'Moroccan dirham', 'wp-job-board-pro' ),
					'MDL' => __( 'Moldovan leu', 'wp-job-board-pro' ),
					'MGA' => __( 'Malagasy ariary', 'wp-job-board-pro' ),
					'MKD' => __( 'Macedonian denar', 'wp-job-board-pro' ),
					'MMK' => __( 'Burmese kyat', 'wp-job-board-pro' ),
					'MNT' => __( 'Mongolian t&ouml;gr&ouml;g', 'wp-job-board-pro' ),
					'MOP' => __( 'Macanese pataca', 'wp-job-board-pro' ),
					'MRU' => __( 'Mauritanian ouguiya', 'wp-job-board-pro' ),
					'MUR' => __( 'Mauritian rupee', 'wp-job-board-pro' ),
					'MVR' => __( 'Maldivian rufiyaa', 'wp-job-board-pro' ),
					'MWK' => __( 'Malawian kwacha', 'wp-job-board-pro' ),
					'MXN' => __( 'Mexican peso', 'wp-job-board-pro' ),
					'MYR' => __( 'Malaysian ringgit', 'wp-job-board-pro' ),
					'MZN' => __( 'Mozambican metical', 'wp-job-board-pro' ),
					'NAD' => __( 'Namibian dollar', 'wp-job-board-pro' ),
					'NGN' => __( 'Nigerian naira', 'wp-job-board-pro' ),
					'NIO' => __( 'Nicaraguan c&oacute;rdoba', 'wp-job-board-pro' ),
					'NOK' => __( 'Norwegian krone', 'wp-job-board-pro' ),
					'NPR' => __( 'Nepalese rupee', 'wp-job-board-pro' ),
					'NZD' => __( 'New Zealand dollar', 'wp-job-board-pro' ),
					'OMR' => __( 'Omani rial', 'wp-job-board-pro' ),
					'PAB' => __( 'Panamanian balboa', 'wp-job-board-pro' ),
					'PEN' => __( 'Sol', 'wp-job-board-pro' ),
					'PGK' => __( 'Papua New Guinean kina', 'wp-job-board-pro' ),
					'PHP' => __( 'Philippine peso', 'wp-job-board-pro' ),
					'PKR' => __( 'Pakistani rupee', 'wp-job-board-pro' ),
					'PLN' => __( 'Polish z&#x142;oty', 'wp-job-board-pro' ),
					'PRB' => __( 'Transnistrian ruble', 'wp-job-board-pro' ),
					'PYG' => __( 'Paraguayan guaran&iacute;', 'wp-job-board-pro' ),
					'QAR' => __( 'Qatari riyal', 'wp-job-board-pro' ),
					'RON' => __( 'Romanian leu', 'wp-job-board-pro' ),
					'RSD' => __( 'Serbian dinar', 'wp-job-board-pro' ),
					'RUB' => __( 'Russian ruble', 'wp-job-board-pro' ),
					'RWF' => __( 'Rwandan franc', 'wp-job-board-pro' ),
					'SAR' => __( 'Saudi riyal', 'wp-job-board-pro' ),
					'SBD' => __( 'Solomon Islands dollar', 'wp-job-board-pro' ),
					'SCR' => __( 'Seychellois rupee', 'wp-job-board-pro' ),
					'SDG' => __( 'Sudanese pound', 'wp-job-board-pro' ),
					'SEK' => __( 'Swedish krona', 'wp-job-board-pro' ),
					'SGD' => __( 'Singapore dollar', 'wp-job-board-pro' ),
					'SHP' => __( 'Saint Helena pound', 'wp-job-board-pro' ),
					'SLL' => __( 'Sierra Leonean leone', 'wp-job-board-pro' ),
					'SOS' => __( 'Somali shilling', 'wp-job-board-pro' ),
					'SRD' => __( 'Surinamese dollar', 'wp-job-board-pro' ),
					'SSP' => __( 'South Sudanese pound', 'wp-job-board-pro' ),
					'STN' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'wp-job-board-pro' ),
					'SYP' => __( 'Syrian pound', 'wp-job-board-pro' ),
					'SZL' => __( 'Swazi lilangeni', 'wp-job-board-pro' ),
					'THB' => __( 'Thai baht', 'wp-job-board-pro' ),
					'TJS' => __( 'Tajikistani somoni', 'wp-job-board-pro' ),
					'TMT' => __( 'Turkmenistan manat', 'wp-job-board-pro' ),
					'TND' => __( 'Tunisian dinar', 'wp-job-board-pro' ),
					'TOP' => __( 'Tongan pa&#x2bb;anga', 'wp-job-board-pro' ),
					'TRY' => __( 'Turkish lira', 'wp-job-board-pro' ),
					'TTD' => __( 'Trinidad and Tobago dollar', 'wp-job-board-pro' ),
					'TWD' => __( 'New Taiwan dollar', 'wp-job-board-pro' ),
					'TZS' => __( 'Tanzanian shilling', 'wp-job-board-pro' ),
					'UAH' => __( 'Ukrainian hryvnia', 'wp-job-board-pro' ),
					'UGX' => __( 'Ugandan shilling', 'wp-job-board-pro' ),
					'USD' => __( 'United States (US) dollar', 'wp-job-board-pro' ),
					'UYU' => __( 'Uruguayan peso', 'wp-job-board-pro' ),
					'UZS' => __( 'Uzbekistani som', 'wp-job-board-pro' ),
					'VEF' => __( 'Venezuelan bol&iacute;var', 'wp-job-board-pro' ),
					'VES' => __( 'Bol&iacute;var soberano', 'wp-job-board-pro' ),
					'VND' => __( 'Vietnamese &#x111;&#x1ed3;ng', 'wp-job-board-pro' ),
					'VUV' => __( 'Vanuatu vatu', 'wp-job-board-pro' ),
					'WST' => __( 'Samoan t&#x101;l&#x101;', 'wp-job-board-pro' ),
					'XAF' => __( 'Central African CFA franc', 'wp-job-board-pro' ),
					'XCD' => __( 'East Caribbean dollar', 'wp-job-board-pro' ),
					'XOF' => __( 'West African CFA franc', 'wp-job-board-pro' ),
					'XPF' => __( 'CFP franc', 'wp-job-board-pro' ),
					'YER' => __( 'Yemeni rial', 'wp-job-board-pro' ),
					'ZAR' => __( 'South African rand', 'wp-job-board-pro' ),
					'ZMW' => __( 'Zambian kwacha', 'wp-job-board-pro' ),
				)
			)
		);

		return $currencies;
	}

	/**
	 * Get all available Currency symbols.
	 *
	 * Currency symbols and names should follow the Unicode CLDR recommendation (http://cldr.unicode.org/translation/currency-names)
	 *
	 * @since 4.1.0
	 * @return array
	 */
	public static function get_currency_symbols() {

		$symbols = apply_filters(
			'wp-job-board-pro-currency-symbols',
			array(
				'AED' => '&#x62f;.&#x625;',
				'AFN' => '&#x60b;',
				'ALL' => 'L',
				'AMD' => 'AMD',
				'ANG' => '&fnof;',
				'AOA' => 'Kz',
				'ARS' => '&#36;',
				'AUD' => '&#36;',
				'AWG' => 'Afl.',
				'AZN' => 'AZN',
				'BAM' => 'KM',
				'BBD' => '&#36;',
				'BDT' => '&#2547;&nbsp;',
				'BGN' => '&#1083;&#1074;.',
				'BHD' => '.&#x62f;.&#x628;',
				'BIF' => 'Fr',
				'BMD' => '&#36;',
				'BND' => '&#36;',
				'BOB' => 'Bs.',
				'BRL' => '&#82;&#36;',
				'BSD' => '&#36;',
				'BTC' => '&#8383;',
				'BTN' => 'Nu.',
				'BWP' => 'P',
				'BYR' => 'Br',
				'BYN' => 'Br',
				'BZD' => '&#36;',
				'CAD' => '&#36;',
				'CDF' => 'Fr',
				'CHF' => '&#67;&#72;&#70;',
				'CLP' => '&#36;',
				'CNY' => '&yen;',
				'COP' => '&#36;',
				'CRC' => '&#x20a1;',
				'CUC' => '&#36;',
				'CUP' => '&#36;',
				'CVE' => '&#36;',
				'CZK' => '&#75;&#269;',
				'DJF' => 'Fr',
				'DKK' => 'DKK',
				'DOP' => 'RD&#36;',
				'DZD' => '&#x62f;.&#x62c;',
				'EGP' => 'EGP',
				'ERN' => 'Nfk',
				'ETB' => 'Br',
				'EUR' => '&euro;',
				'FJD' => '&#36;',
				'FKP' => '&pound;',
				'GBP' => '&pound;',
				'GEL' => '&#x20be;',
				'GGP' => '&pound;',
				'GHS' => '&#x20b5;',
				'GIP' => '&pound;',
				'GMD' => 'D',
				'GNF' => 'Fr',
				'GTQ' => 'Q',
				'GYD' => '&#36;',
				'HKD' => '&#36;',
				'HNL' => 'L',
				'HRK' => 'kn',
				'HTG' => 'G',
				'HUF' => '&#70;&#116;',
				'IDR' => 'Rp',
				'ILS' => '&#8362;',
				'IMP' => '&pound;',
				'INR' => '&#8377;',
				'IQD' => '&#x639;.&#x62f;',
				'IRR' => '&#xfdfc;',
				'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
				'ISK' => 'kr.',
				'JEP' => '&pound;',
				'JMD' => '&#36;',
				'JOD' => '&#x62f;.&#x627;',
				'JPY' => '&yen;',
				'KES' => 'KSh',
				'KGS' => '&#x441;&#x43e;&#x43c;',
				'KHR' => '&#x17db;',
				'KMF' => 'Fr',
				'KPW' => '&#x20a9;',
				'KRW' => '&#8361;',
				'KWD' => '&#x62f;.&#x643;',
				'KYD' => '&#36;',
				'KZT' => '&#8376;',
				'LAK' => '&#8365;',
				'LBP' => '&#x644;.&#x644;',
				'LKR' => '&#xdbb;&#xdd4;',
				'LRD' => '&#36;',
				'LSL' => 'L',
				'LYD' => '&#x644;.&#x62f;',
				'MAD' => '&#x62f;.&#x645;.',
				'MDL' => 'MDL',
				'MGA' => 'Ar',
				'MKD' => '&#x434;&#x435;&#x43d;',
				'MMK' => 'Ks',
				'MNT' => '&#x20ae;',
				'MOP' => 'P',
				'MRU' => 'UM',
				'MUR' => '&#x20a8;',
				'MVR' => '.&#x783;',
				'MWK' => 'MK',
				'MXN' => '&#36;',
				'MYR' => '&#82;&#77;',
				'MZN' => 'MT',
				'NAD' => 'N&#36;',
				'NGN' => '&#8358;',
				'NIO' => 'C&#36;',
				'NOK' => '&#107;&#114;',
				'NPR' => '&#8360;',
				'NZD' => '&#36;',
				'OMR' => '&#x631;.&#x639;.',
				'PAB' => 'B/.',
				'PEN' => 'S/',
				'PGK' => 'K',
				'PHP' => '&#8369;',
				'PKR' => '&#8360;',
				'PLN' => '&#122;&#322;',
				'PRB' => '&#x440;.',
				'PYG' => '&#8370;',
				'QAR' => '&#x631;.&#x642;',
				'RMB' => '&yen;',
				'RON' => 'lei',
				'RSD' => '&#1088;&#1089;&#1076;',
				'RUB' => '&#8381;',
				'RWF' => 'Fr',
				'SAR' => '&#x631;.&#x633;',
				'SBD' => '&#36;',
				'SCR' => '&#x20a8;',
				'SDG' => '&#x62c;.&#x633;.',
				'SEK' => '&#107;&#114;',
				'SGD' => '&#36;',
				'SHP' => '&pound;',
				'SLL' => 'Le',
				'SOS' => 'Sh',
				'SRD' => '&#36;',
				'SSP' => '&pound;',
				'STN' => 'Db',
				'SYP' => '&#x644;.&#x633;',
				'SZL' => 'L',
				'THB' => '&#3647;',
				'TJS' => '&#x405;&#x41c;',
				'TMT' => 'm',
				'TND' => '&#x62f;.&#x62a;',
				'TOP' => 'T&#36;',
				'TRY' => '&#8378;',
				'TTD' => '&#36;',
				'TWD' => '&#78;&#84;&#36;',
				'TZS' => 'Sh',
				'UAH' => '&#8372;',
				'UGX' => 'UGX',
				'USD' => '&#36;',
				'UYU' => '&#36;',
				'UZS' => 'UZS',
				'VEF' => 'Bs F',
				'VES' => 'Bs.S',
				'VND' => '&#8363;',
				'VUV' => 'Vt',
				'WST' => 'T',
				'XAF' => 'CFA',
				'XCD' => '&#36;',
				'XOF' => 'CFA',
				'XPF' => 'Fr',
				'YER' => '&#xfdfc;',
				'ZAR' => '&#82;',
				'ZMW' => 'ZK',
			)
		);

		return $symbols;
	}

	/**
	 * Get Currency symbol.
	 *
	 * Currency symbols and names should follow the Unicode CLDR recommendation (http://cldr.unicode.org/translation/currency-names)
	 *
	 * @param string $currency Currency. (default: '').
	 * @return string
	 */
	public static function currency_symbol( $currency = '' ) {
		if ( ! $currency ) {
			$currency = wp_job_board_pro_get_option('currency', 'USD');
		}

		$symbols = self::get_currency_symbols();

		$currency_symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : '';

		return apply_filters( 'wp-job-board-pro-currency-symbol', $currency_symbol, $currency );
	}
	
	public static function get_currencies_settings() {
		$currency = wp_job_board_pro_get_option('currency', 'USD');
		$return = array(
			$currency => array(
				'currency' => $currency,
				'currency_position' => wp_job_board_pro_get_option('currency_position', 'before'),
				'money_decimals' => wp_job_board_pro_get_option('money_decimals', ''),
				'rate_exchange_fee' => 1,
				'custom_symbol' => wp_job_board_pro_get_option('custom_symbol', ''),
			)
		);
		$multi_currencies = wp_job_board_pro_get_option('multi_currencies');
		if ( !empty($multi_currencies) ) {
			foreach ($multi_currencies as $multi_currency) {
				if ( !empty($multi_currency['currency']) && $multi_currency['currency'] != $currency) {
					$return[$multi_currency['currency']] = $multi_currency;
				}
			}
		}

		return $return;
	}

	public static function process_currency() {
		if ( !empty($_GET['currency']) && wp_job_board_pro_get_option('enable_multi_currencies') === 'yes' ) {
			setcookie('wp_job_board_pro_currency', sanitize_text_field($_GET['currency']), time() + (86400 * 30), "/" );
			$_COOKIE['wp_job_board_pro_currency'] = sanitize_text_field($_GET['currency']);
		}		
	}
}

WP_Job_Board_Pro_Price::init();