<?php
/**
 * Plugin Name: Raspitajse Commerce
 * Description: Raspitajse-owned employer checkout, order data and job-package policy integration.
 * Version: 0.2.0
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

require_once __DIR__ . '/includes/class-raspitajse-commerce-job-package-policy.php';

Raspitajse_Commerce::boot();
Raspitajse_Commerce_Job_Package_Policy::boot();
