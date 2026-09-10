<?php
/**
 * Plugin Name: Raspitajse Commerce
 * Description: Raspitajse-owned employer checkout, order data and job-package policy integration.
 * Version: 0.4.0
 * Author: Raspitajse.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Raspitajse_Commerce {

    const ORDER_META_PIB          = '_billing_pib';
    const ORDER_META_MB           = '_billing_mb';
    const ORDER_META_HOUSE_NUMBER = '_billing_house_number';

    /**
     * Register the owned checkout integration.
     */
    public static function boot() {
        add_action( 'before_woocommerce_init', array( __CLASS__, 'declare_hpos_compatibility' ) );
        add_action( 'wp_footer', array( __CLASS__, 'render_checkout_prefill' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_package_purchase_transport' ) );
        add_filter( 'woocommerce_checkout_get_value', array( __CLASS__, 'filter_checkout_country' ), 10, 2 );
        add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'persist_company_order_data' ), 20, 2 );
        add_action(
            'woocommerce_admin_order_data_after_billing_address',
            array( __CLASS__, 'render_admin_company_data' )
        );
    }

    /**
     * Declare compatibility because every order read/write uses Woo CRUD.
     */
    public static function declare_hpos_compatibility() {
        if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'custom_order_tables',
                __FILE__,
                true
            );
        }
    }

    /**
     * Enqueue the owned transport for standalone package purchases.
     *
     * Job-submission package selection deliberately remains on Paid Listings'
     * native server flow. The script is further scoped to the standalone form.
     */
    public static function enqueue_package_purchase_transport() {
        if (
            is_admin()
            || wp_doing_ajax()
            || ! function_exists( 'is_page_template' )
            || ! is_page_template( 'page-dashboard.php' )
            || ! class_exists( 'WC_AJAX' )
            || ! function_exists( 'wc_get_checkout_url' )
        ) {
            return;
        }

        $handle     = 'raspitajse-commerce-package-purchase';
        $asset_path = __DIR__ . '/assets/js/package-purchase.js';

        if ( ! is_readable( $asset_path ) ) {
            return;
        }

        wp_enqueue_script(
            $handle,
            plugins_url( 'assets/js/package-purchase.js', __FILE__ ),
            array( 'jquery' ),
            (string) filemtime( $asset_path ),
            true
        );

        $settings = array(
            'addToCartUrl'  => esc_url_raw( WC_AJAX::get_endpoint( 'add_to_cart' ) ),
            'checkoutUrl'   => esc_url_raw( wc_get_checkout_url() ),
            'pendingMessage' => __( 'Preusmeravanje…', 'raspitajse-commerce' ),
            'failureMessage' => __(
                'Paket trenutno nije moguće dodati. Pokušajte ponovo.',
                'raspitajse-commerce'
            ),
        );
        $settings_json = wp_json_encode(
            $settings,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );

        if ( false === $settings_json ) {
            return;
        }

        wp_add_inline_script(
            $handle,
            'window.RaspitajseCommerce = window.RaspitajseCommerce || {};'
                . 'window.RaspitajseCommerce.packagePurchase = Object.freeze('
                . $settings_json
                . ');',
            'before'
        );
    }

    /**
     * Resolve the employer through WP Job Board Pro's canonical employer_id link.
     *
     * @param int $user_id WordPress user ID.
     * @return int
     */
    public static function get_employer_id_by_user( $user_id ) {
        $user_id = absint( $user_id );

        if ( ! $user_id || ! class_exists( 'WP_Job_Board_Pro_User' ) ) {
            return 0;
        }

        $employer_id = absint(
            WP_Job_Board_Pro_User::get_employer_by_user_id( $user_id )
        );

        if ( ! $employer_id || 'employer' !== get_post_type( $employer_id ) ) {
            return 0;
        }

        return $employer_id;
    }

    /**
     * Read the employer profile snapshot used to prefill classic checkout.
     *
     * This method is intentionally read-only.
     *
     * @param int $user_id WordPress user ID.
     * @return array
     */
    public static function get_employer_checkout_data( $user_id ) {
        $employer_id = self::get_employer_id_by_user( $user_id );

        if ( ! $employer_id ) {
            return array();
        }

        $company = get_the_title( $employer_id );
        if ( '' === trim( (string) $company ) ) {
            $company = get_post_meta( $employer_id, '_employer_title', true );
        }

        return array(
            'company'      => self::clean_profile_value( $company ),
            'mb'           => self::clean_profile_value(
                get_post_meta( $employer_id, 'custom-text-2726709', true )
            ),
            'pib'          => self::clean_profile_value(
                get_post_meta( $employer_id, 'custom-text-2842853', true )
            ),
            'email'        => self::clean_profile_value(
                get_post_meta( $employer_id, '_employer_email', true )
            ),
            'phone'        => self::clean_profile_value(
                get_post_meta( $employer_id, '_employer_phone', true )
            ),
            'street'       => self::clean_profile_value(
                get_post_meta( $employer_id, 'custom-text-36619838', true )
            ),
            'house_number' => self::clean_profile_value(
                get_post_meta( $employer_id, 'custom-number-37930732', true )
            ),
            'postcode'     => self::clean_profile_value(
                get_post_meta( $employer_id, 'custom-number-38584023', true )
            ),
            'city'         => self::clean_profile_value(
                get_post_meta( $employer_id, 'custom-text-35868429', true )
            ),
            'country'      => self::clean_profile_value(
                get_post_meta( $employer_id, 'custom-select-40692190', true )
            ),
        );
    }

    /**
     * Output the existing classic-checkout prefill behavior without persistence.
     */
    public static function render_checkout_prefill() {
        if (
            wp_doing_ajax()
            || ( defined( 'REST_REQUEST' ) && REST_REQUEST )
            || ! function_exists( 'is_checkout' )
            || ! is_checkout()
            || ! is_user_logged_in()
        ) {
            return;
        }

        $data = self::get_employer_checkout_data( get_current_user_id() );
        if ( empty( $data ) ) {
            return;
        }
        ?>
        <script>
            jQuery(function ($) {
                const employer = <?php echo wp_json_encode(
                    $data,
                    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                ); ?>;

                function fillEmployerCheckoutFields() {
                    const fields = {
                        company: '#billing_company',
                        pib: '#billing_pib',
                        mb: '#billing_mb',
                        street: '#billing_address_1',
                        house_number: '#billing_house_number',
                        postcode: '#billing_postcode',
                        city: '#billing_city',
                        email: '#billing_email',
                        phone: '#billing_phone'
                    };

                    Object.entries(fields).forEach(([key, selector]) => {
                        if (employer[key]) {
                            $(selector).val(employer[key]).trigger('change');
                        }
                    });

                    if (employer.country) {
                        const country = $('#billing_country');
                        if (country.length && country.val() !== employer.country) {
                            country.val(employer.country).trigger('change.select2');
                        }
                    }
                }

                fillEmployerCheckoutFields();
                $(document.body).on('updated_checkout', fillEmployerCheckoutFields);
            });
        </script>
        <?php
    }

    /**
     * Prefill billing country from employer data without changing user meta.
     *
     * @param mixed  $value Current checkout value.
     * @param string $input Checkout field name.
     * @return mixed
     */
    public static function filter_checkout_country( $value, $input ) {
        if ( 'billing_country' !== $input || ! is_user_logged_in() ) {
            return $value;
        }

        $data = self::get_employer_checkout_data( get_current_user_id() );

        return ! empty( $data['country'] ) ? $data['country'] : $value;
    }

    /**
     * Persist company identifiers through the WC_Order object before Woo saves.
     *
     * @param mixed $order Order object supplied by WooCommerce.
     * @param mixed $data  Sanitized classic-checkout data.
     */
    public static function persist_company_order_data( $order, $data ) {
        if ( ! $order instanceof WC_Order || ! is_array( $data ) ) {
            return;
        }

        $pib          = self::clean_order_field( $data, 'billing_pib' );
        $mb           = self::clean_order_field( $data, 'billing_mb' );
        $street       = self::clean_order_field( $data, 'billing_address_1' );
        $house_number = self::clean_order_field( $data, 'billing_house_number' );

        if ( '' !== $pib ) {
            $order->update_meta_data( self::ORDER_META_PIB, $pib );
        }

        if ( '' !== $mb ) {
            $order->update_meta_data( self::ORDER_META_MB, $mb );
        }

        if ( '' !== $house_number ) {
            $order->update_meta_data( self::ORDER_META_HOUSE_NUMBER, $house_number );
        }

        if ( '' !== $street && '' !== $house_number ) {
            $order->set_billing_address_1( trim( $street . ' ' . $house_number ) );
        }
    }

    /**
     * Return company identifiers from a Woo order.
     *
     * @param mixed $order Woo order.
     * @return array
     */
    public static function get_order_company_data( $order ) {
        if ( ! $order instanceof WC_Order ) {
            return array(
                'mb'           => '',
                'pib'          => '',
                'house_number' => '',
            );
        }

        return array(
            'mb'           => (string) $order->get_meta( self::ORDER_META_MB, true ),
            'pib'          => (string) $order->get_meta( self::ORDER_META_PIB, true ),
            'house_number' => (string) $order->get_meta(
                self::ORDER_META_HOUSE_NUMBER,
                true
            ),
        );
    }

    /**
     * Display escaped company data on classic and HPOS order admin screens.
     *
     * @param mixed $order Woo order supplied by the admin hook.
     */
    public static function render_admin_company_data( $order ) {
        $company_data = self::get_order_company_data( $order );

        if ( ! array_filter( $company_data, 'strlen' ) ) {
            return;
        }

        echo '<div class="raspitajse-order-company-data">';
        echo '<p><strong>' . esc_html__( 'Podaci o kompaniji', 'raspitajse-commerce' ) . '</strong></p>';

        if ( '' !== $company_data['mb'] ) {
            echo '<p>' . esc_html__( 'Matični broj:', 'raspitajse-commerce' ) . ' '
                . esc_html( $company_data['mb'] ) . '</p>';
        }

        if ( '' !== $company_data['pib'] ) {
            echo '<p>' . esc_html__( 'PIB:', 'raspitajse-commerce' ) . ' '
                . esc_html( $company_data['pib'] ) . '</p>';
        }

        if ( '' !== $company_data['house_number'] ) {
            echo '<p>' . esc_html__( 'Broj:', 'raspitajse-commerce' ) . ' '
                . esc_html( $company_data['house_number'] ) . '</p>';
        }

        echo '</div>';
    }

    /**
     * Normalize scalar employer meta for safe checkout output.
     *
     * @param mixed $value Profile value.
     * @return string
     */
    private static function clean_profile_value( $value ) {
        if ( ! is_scalar( $value ) ) {
            return '';
        }

        return sanitize_text_field( (string) $value );
    }

    /**
     * Read one scalar field from Woo checkout data.
     *
     * @param array  $data Checkout data.
     * @param string $key  Field key.
     * @return string
     */
    private static function clean_order_field( $data, $key ) {
        if ( ! array_key_exists( $key, $data ) || ! is_scalar( $data[ $key ] ) ) {
            return '';
        }

        $value = (string) $data[ $key ];

        return function_exists( 'wc_clean' )
            ? (string) wc_clean( $value )
            : sanitize_text_field( $value );
    }
}

final class Raspitajse_Commerce_Checkout_Policy {

    const RSD_GATEWAY_ID = 'bank_transfer_1';
    const RATE_TRANSIENT = 'raspitajse_nbs_eur_rsd_rate';
    const FALLBACK_RATE  = 117.5;

    /**
     * Register one owned callback for each custom checkout/payment behavior.
     */
    public static function boot() {
        add_action(
            'woocommerce_checkout_before_customer_details',
            array( __CLASS__, 'render_legal_entity_notice' )
        );
        add_filter(
            'woocommerce_checkout_fields',
            array( __CLASS__, 'filter_checkout_fields' )
        );
        add_action(
            'wp_footer',
            array( __CLASS__, 'render_currency_preview' )
        );
        add_action(
            'woocommerce_checkout_create_order',
            array( __CLASS__, 'convert_order_to_rsd' ),
            30,
            2
        );
        add_action(
            'wp_head',
            array( __CLASS__, 'hide_standard_bank_details' )
        );
        add_action(
            'woocommerce_thankyou',
            array( __CLASS__, 'render_payment_instructions' ),
            1
        );
        add_filter(
            'wpo_wcpdf_attach_invoice',
            array( __CLASS__, 'filter_invoice_attachment' ),
            10,
            3
        );
    }

    /**
     * Explain the legal-entity-only checkout contract.
     */
    public static function render_legal_entity_notice() {
        echo '<div class="legal-entity-notice" style="margin-bottom:20px;padding:15px;background:#f5f9ff;border-left:4px solid #2a90cc;">';
        echo '<strong>' . esc_html__( 'Važno obaveštenje', 'raspitajse-commerce' ) . '</strong><br>';
        echo wp_kses_post(
            __(
                'Molimo vas da uplatu izvršite <strong>isključivo sa računa pravnog lica</strong>. Podaci koje unesete biće korišćeni za izdavanje fakture i moraju biti tačni i potpuni.',
                'raspitajse-commerce'
            )
        );
        echo '</div>';
    }

    /**
     * Own the classic legal-entity checkout field schema.
     *
     * @param array $fields WooCommerce checkout fields.
     * @return array
     */
    public static function filter_checkout_fields( $fields ) {
        if ( ! isset( $fields['billing'] ) || ! is_array( $fields['billing'] ) ) {
            return $fields;
        }

        unset(
            $fields['billing']['billing_state'],
            $fields['billing']['billing_address_2'],
            $fields['billing']['billing_first_name'],
            $fields['billing']['billing_last_name']
        );

        $fields['billing']['billing_company'] = array_merge(
            isset( $fields['billing']['billing_company'] )
                ? $fields['billing']['billing_company']
                : array(),
            array(
                'label'       => __( 'Naziv kompanije', 'raspitajse-commerce' ),
                'required'    => true,
                'class'       => array( 'form-row-wide' ),
                'priority'    => 30,
                'placeholder' => __( 'npr. Dots Agencija', 'raspitajse-commerce' ),
            )
        );

        $fields['billing']['billing_pib'] = array(
            'label'    => __( 'Poreski Identifikacioni Broj (PIB)', 'raspitajse-commerce' ),
            'required' => true,
            'class'    => array( 'form-row-first' ),
            'priority' => 15,
        );
        $fields['billing']['billing_mb'] = array(
            'label'    => __( 'Matični broj', 'raspitajse-commerce' ),
            'required' => true,
            'class'    => array( 'form-row-last' ),
            'priority' => 16,
        );

        if ( isset( $fields['billing']['billing_address_1'] ) ) {
            $fields['billing']['billing_address_1'] = array_merge(
                $fields['billing']['billing_address_1'],
                array(
                    'label'       => __( 'Ulica', 'raspitajse-commerce' ),
                    'placeholder' => __( 'npr. Nemanjina', 'raspitajse-commerce' ),
                    'required'    => true,
                    'class'       => array( 'form-row-first' ),
                    'priority'    => 30,
                )
            );
        }

        $fields['billing']['billing_house_number'] = array(
            'label'       => __( 'Broj', 'raspitajse-commerce' ),
            'placeholder' => __( 'npr. 76/11', 'raspitajse-commerce' ),
            'required'    => true,
            'class'       => array( 'form-row-last' ),
            'priority'    => 31,
        );

        foreach (
            array(
                'billing_postcode' => array( 'Poštanski broj', 'form-row-first', 40 ),
                'billing_city'     => array( 'Grad', 'form-row-last', 41 ),
                'billing_country'  => array( 'Država', 'form-row-wide', 50 ),
                'billing_email'    => array( 'Email adresa', 'form-row-first', 60 ),
                'billing_phone'    => array( 'Telefon', 'form-row-last', 61 ),
            ) as $key => $definition
        ) {
            if ( ! isset( $fields['billing'][ $key ] ) ) {
                continue;
            }

            $fields['billing'][ $key ] = array_merge(
                $fields['billing'][ $key ],
                array(
                    'label'    => __( $definition[0], 'raspitajse-commerce' ),
                    'required' => true,
                    'class'    => array( $definition[1] ),
                    'priority' => $definition[2],
                )
            );
        }

        if ( isset( $fields['order']['order_comments'] ) ) {
            $fields['order']['order_comments']['label'] = __(
                'Napomena uz porudžbinu',
                'raspitajse-commerce'
            );
            $fields['order']['order_comments']['priority'] = 90;
        }

        return $fields;
    }

    /**
     * Render a presentation-only RSD checkout preview from owned code.
     */
    public static function render_currency_preview() {
        if (
            is_admin()
            || wp_doing_ajax()
            || ! function_exists( 'is_checkout' )
            || ! is_checkout()
        ) {
            return;
        }

        $rate = self::get_eur_to_rsd_rate();
        if ( $rate <= 0 ) {
            return;
        }
        ?>
        <script>
        (function ($, window) {
            'use strict';

            const gatewayId = <?php echo wp_json_encode( self::RSD_GATEWAY_ID ); ?>;
            const rate = <?php echo wp_json_encode( $rate ); ?>;

            function readEur($element) {
                const stored = $element.data('raspitajse-eur');
                if (typeof stored === 'number' && Number.isFinite(stored)) {
                    return stored;
                }

                const raw = $element.text()
                    .replace(/\s/g, '')
                    .replace('€', '')
                    .replace(/\./g, '')
                    .replace(',', '.');
                const value = Number.parseFloat(raw);

                if (!Number.isFinite(value)) {
                    return null;
                }

                $element.data('raspitajse-eur', value);
                return value;
            }

            function refreshPreview() {
                const selected = $('input[name="payment_method"]:checked').val();

                $('.woocommerce-checkout-review-order .woocommerce-Price-amount bdi')
                    .each(function () {
                        const $amount = $(this);
                        const eur = readEur($amount);

                        if (eur === null) {
                            return;
                        }

                        $amount.text(
                            selected === gatewayId
                                ? Math.round(eur * rate).toLocaleString('sr-RS') + ' RSD'
                                : eur.toFixed(2).replace('.', ',') + ' €'
                        );
                    });
            }

            $(document).on(
                'change.raspitajseCommerceCurrency',
                'input[name="payment_method"]',
                function () {
                    window.setTimeout(refreshPreview, 50);
                }
            );
            $(document.body).on('updated_checkout', function () {
                window.setTimeout(refreshPreview, 50);
            });
            window.setTimeout(refreshPreview, 100);
        })(jQuery, window);
        </script>
        <?php
    }
    /**
     * Return the exchange rate through one owned provider.
     *
     * The filter is the deterministic no-network acceptance seam.
     *
     * @return float
     */
    public static function get_eur_to_rsd_rate() {
        $override = apply_filters(
            'raspitajse_commerce_eur_to_rsd_rate',
            null
        );

        if ( is_numeric( $override ) && (float) $override > 0 ) {
            return (float) $override;
        }

        $cached = get_transient( self::RATE_TRANSIENT );
        if ( is_numeric( $cached ) && (float) $cached > 0 ) {
            return (float) $cached;
        }

        $response = wp_safe_remote_get(
            'https://www.nbs.rs/kursnaListaModul/zaDevize.faces?lang=lat',
            array(
                'timeout'     => 5,
                'redirection' => 0,
            )
        );

        if ( ! is_wp_error( $response ) ) {
            $body = wp_remote_retrieve_body( $response );

            if (
                preg_match(
                    '/<td>EUR<\/td>.*?<td>([\d,]+)<\/td>/s',
                    $body,
                    $matches
                )
            ) {
                $rate = (float) str_replace( ',', '.', $matches[1] );

                if ( $rate > 0 ) {
                    set_transient( self::RATE_TRANSIENT, $rate, DAY_IN_SECONDS );
                    return $rate;
                }
            }
        }

        return (float) apply_filters(
            'raspitajse_commerce_eur_to_rsd_fallback_rate',
            self::FALLBACK_RATE
        );
    }

    /**
     * Convert the package order once when the RSD bank-transfer gateway is used.
     *
     * @param mixed $order WooCommerce order.
     * @param mixed $data  Sanitized checkout data.
     */
    public static function convert_order_to_rsd( $order, $data = array() ) {
        if (
            ! $order instanceof WC_Order
            || self::RSD_GATEWAY_ID !== $order->get_payment_method()
            || $order->get_meta( '_converted_to_rsd', true )
        ) {
            return;
        }

        $rate = self::get_eur_to_rsd_rate();
        if ( $rate <= 0 ) {
            return;
        }

        $new_total = 0.0;

        foreach ( $order->get_items() as $item ) {
            if ( ! $item instanceof WC_Order_Item_Product ) {
                continue;
            }

            $eur_total = (float) $item->get_total();
            $rsd_total = round( $eur_total * $rate );

            $item->add_meta_data( '_total_eur', $eur_total, true );
            $item->set_subtotal( $rsd_total );
            $item->set_total( $rsd_total );
            $new_total += $rsd_total;
        }

        $order->update_meta_data( '_original_currency', 'EUR' );
        $order->update_meta_data( '_eur_to_rsd_rate', $rate );
        $order->update_meta_data( '_converted_to_rsd', 1 );
        $order->set_currency( 'RSD' );
        $order->set_total( $new_total );
    }

    /**
     * Keep the custom instruction block as the single bank-detail renderer.
     */
    public static function hide_standard_bank_details() {
        if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
            echo '<style>.woocommerce-bacs-bank-details{display:none!important;}</style>';
        }
    }

    /**
     * Render owned, local bank-payment instructions without a third-party QR.
     *
     * @param int $order_id WooCommerce order ID.
     */
    public static function render_payment_instructions( $order_id ) {
        $order = function_exists( 'wc_get_order' )
            ? wc_get_order( absint( $order_id ) )
            : false;

        if ( ! $order instanceof WC_Order ) {
            return;
        }

        $payment_method = $order->get_payment_method();
        if ( ! in_array( $payment_method, array( 'bacs', self::RSD_GATEWAY_ID ), true ) ) {
            return;
        }

        $currency = strtoupper( (string) $order->get_currency() );
        if ( ! in_array( $currency, array( 'EUR', 'RSD' ), true ) ) {
            return;
        }

        $payer = trim( (string) $order->get_billing_company() );
        if ( '' === $payer ) {
            $payer = trim( (string) $order->get_formatted_billing_full_name() );
        }

        $amount  = number_format( (float) $order->get_total(), 2, ',', '' );
        $purpose = sprintf(
            /* translators: %s is the WooCommerce order number. */
            __( 'KUPOVINA PAKETA BR %s', 'raspitajse-commerce' ),
            $order->get_order_number()
        );

        echo '<section class="raspitajse-payment-slip" style="margin:30px 0;padding:20px;border:2px solid #F5F7FC;border-radius:12px;background:#fafffb;">';
        echo '<h4>' . esc_html__( 'Nalog za uplatu', 'raspitajse-commerce' ) . '</h4>';
        echo '<dl class="raspitajse-payment-details">';
        self::render_detail( __( 'Platilac', 'raspitajse-commerce' ), $payer );
        self::render_detail( __( 'Šifra plaćanja', 'raspitajse-commerce' ), '221' );
        self::render_detail( __( 'Valuta', 'raspitajse-commerce' ), $currency );
        self::render_detail( __( 'Iznos', 'raspitajse-commerce' ), $amount );
        self::render_detail( __( 'Svrha plaćanja', 'raspitajse-commerce' ), $purpose );
        self::render_detail( __( 'Primalac', 'raspitajse-commerce' ), 'VLADIMIR VELJKOVIĆ PR DOTS' );

        if ( 'RSD' === $currency ) {
            self::render_detail(
                __( 'Račun primaoca', 'raspitajse-commerce' ),
                '265-6660310001092-13'
            );
        } else {
            self::render_detail(
                __( 'IBAN primaoca', 'raspitajse-commerce' ),
                'RS35265100000003681027'
            );
            self::render_detail(
                __( 'BIC / SWIFT', 'raspitajse-commerce' ),
                'RZBSRSBG'
            );
        }

        echo '</dl>';
        echo '<p>' . esc_html__(
            'Uplatu možete izvršiti putem e-banking servisa ili popunjavanjem naloga za uplatu.',
            'raspitajse-commerce'
        ) . '</p>';
        echo '</section>';
    }

    /**
     * Keep paid customer invoice attachment policy in the commerce owner.
     *
     * @param bool  $attach   Current attachment decision.
     * @param mixed $order    WooCommerce order.
     * @param string $email_id WooCommerce email ID.
     * @return bool
     */
    public static function filter_invoice_attachment(
        $attach,
        $order,
        $email_id = ''
    ) {
        if ( ! $order instanceof WC_Order || (float) $order->get_total() <= 0 ) {
            return false;
        }

        if (
            '' !== $email_id
            && ! in_array(
                $email_id,
                array(
                    'customer_completed_order',
                    'customer_processing_order',
                ),
                true
            )
        ) {
            return false;
        }

        return (bool) $attach;
    }

    /**
     * Render one escaped payment detail.
     *
     * @param string $label Detail label.
     * @param string $value Detail value.
     */
    private static function render_detail( $label, $value ) {
        echo '<div><dt>' . esc_html( $label ) . '</dt><dd>'
            . esc_html( $value ) . '</dd></div>';
    }
}


require_once __DIR__ . '/includes/class-raspitajse-commerce-job-package-policy.php';

Raspitajse_Commerce::boot();
Raspitajse_Commerce_Checkout_Policy::boot();
Raspitajse_Commerce_Job_Package_Policy::boot();
