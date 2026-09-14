<?php
/**
 * Free-launch public UI and route policy.
 *
 * Historical commerce data and authorized wp-admin views remain available while
 * candidate and employer visitors use only the free job-board journey.
 *
 * @package Raspitajse_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Raspitajse_Free_Launch_UI_Policy {
    const PRICING_PAGE_ID = 36;
    const PACKAGES_PAGE_ID = 1699;
    const CART_PAGE_ID = 8606;
    const CHECKOUT_PAGE_ID = 8607;
    const PACKAGES_MENU_ITEM_ID = 1712;
    const SUBMIT_PAGE_ID = 1694;
    const DASHBOARD_PAGE_ID = 1653;

    private static $booted = false;
    private static $activated = false;

    public static function boot() {
        if ( self::$booted ) {
            return;
        }
        self::$booted = true;
        add_action( 'plugins_loaded', array( __CLASS__, 'activate' ), PHP_INT_MAX );
    }

    public static function activate() {
        if ( self::$activated || ! self::is_enabled() ) {
            return;
        }
        self::$activated = true;
        self::detach_owned_paid_callbacks();

        add_action( 'after_setup_theme', array( __CLASS__, 'hide_theme_cart' ), PHP_INT_MAX );
        add_action( 'wp_loaded', array( __CLASS__, 'deny_add_to_cart_request' ), PHP_INT_MIN );
        add_action( 'template_redirect', array( __CLASS__, 'protect_paid_routes' ), PHP_INT_MIN );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'dequeue_paid_assets' ), PHP_INT_MAX );
        add_filter( 'wp_nav_menu_objects', array( __CLASS__, 'filter_menu_items' ), PHP_INT_MAX );
        add_filter( 'wp_get_nav_menu_items', array( __CLASS__, 'filter_menu_items' ), PHP_INT_MAX );
        add_filter( 'elementor/widget/render_content', array( __CLASS__, 'filter_elementor_widget' ), PHP_INT_MAX, 2 );
        add_filter( 'woocommerce_account_menu_items', array( __CLASS__, 'filter_account_menu_items' ), PHP_INT_MAX );
        add_filter( 'woocommerce_get_cart_url', array( __CLASS__, 'filter_paid_url' ), PHP_INT_MAX );
        add_filter( 'woocommerce_get_checkout_url', array( __CLASS__, 'filter_paid_url' ), PHP_INT_MAX );
        add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'deny_public_purchase' ), PHP_INT_MIN );
        add_filter( 'woocommerce_is_purchasable', array( __CLASS__, 'filter_product_purchasable' ), PHP_INT_MAX, 2 );
    }

    private static function detach_owned_paid_callbacks() {
        remove_action( 'wp_footer', array( 'Raspitajse_Commerce', 'render_checkout_prefill' ), 10 );
        remove_action( 'wp_enqueue_scripts', array( 'Raspitajse_Commerce', 'enqueue_package_purchase_transport' ), 10 );
        remove_filter( 'woocommerce_checkout_get_value', array( 'Raspitajse_Commerce', 'filter_checkout_country' ), 10 );
        remove_action( 'woocommerce_checkout_create_order', array( 'Raspitajse_Commerce', 'persist_company_order_data' ), 20 );

        remove_action( 'woocommerce_checkout_before_customer_details', array( 'Raspitajse_Commerce_Checkout_Policy', 'render_legal_entity_notice' ), 10 );
        remove_filter( 'woocommerce_checkout_fields', array( 'Raspitajse_Commerce_Checkout_Policy', 'filter_checkout_fields' ), 10 );
        remove_action( 'wp_footer', array( 'Raspitajse_Commerce_Checkout_Policy', 'render_currency_preview' ), 10 );
        remove_action( 'woocommerce_checkout_create_order', array( 'Raspitajse_Commerce_Checkout_Policy', 'convert_order_to_rsd' ), 30 );
        remove_action( 'wp_head', array( 'Raspitajse_Commerce_Checkout_Policy', 'hide_standard_bank_details' ), 10 );
        remove_action( 'woocommerce_thankyou', array( 'Raspitajse_Commerce_Checkout_Policy', 'render_payment_instructions' ), 1 );
        remove_filter( 'wpo_wcpdf_attach_invoice', array( 'Raspitajse_Commerce_Checkout_Policy', 'filter_invoice_attachment' ), 10 );
    }

    public static function is_enabled() {
        if ( class_exists( 'Raspitajse_Free_Job_Access_Policy' ) ) {
            return Raspitajse_Free_Job_Access_Policy::is_enabled();
        }
        return (bool) apply_filters( 'raspitajse_free_launch_enabled', true );
    }

    public static function is_exempt_context() {
        $exempt = is_admin()
            || wp_doing_ajax()
            || ( defined( 'REST_REQUEST' ) && REST_REQUEST )
            || ( defined( 'WP_CLI' ) && WP_CLI )
            || ( defined( 'DOING_CRON' ) && DOING_CRON );

        return (bool) apply_filters( 'raspitajse_free_launch_exempt_context', $exempt );
    }

    public static function hide_theme_cart() {
        if ( self::is_exempt_context() ) {
            return;
        }
        global $superio_options;
        if ( ! is_array( $superio_options ) ) {
            $superio_options = array();
        }
        $superio_options['show_cartbtn'] = false;
    }

    public static function filter_menu_items( $items ) {
        if ( self::is_exempt_context() || ! is_array( $items ) ) {
            return $items;
        }
        return array_values(
            array_filter(
                $items,
                static function ( $item ) {
                    if ( ! is_object( $item ) ) {
                        return true;
                    }
                    if ( self::PACKAGES_MENU_ITEM_ID === absint( isset( $item->ID ) ? $item->ID : 0 ) ) {
                        return false;
                    }
                    $object_id = absint( isset( $item->object_id ) ? $item->object_id : 0 );
                    if ( in_array( $object_id, self::paid_page_ids(), true ) ) {
                        return false;
                    }
                    return ! self::url_targets_paid_page( isset( $item->url ) ? $item->url : '' );
                }
            )
        );
    }

    public static function filter_elementor_widget( $content, $widget ) {
        if ( self::is_exempt_context() || ! is_object( $widget ) || ! method_exists( $widget, 'get_name' ) ) {
            return $content;
        }
        $paid_widgets = array(
            'apus_element_jobs_single_package',
            'apus_element_jobs_packages',
            'apus_element_jobs_user_packages',
        );
        return in_array( (string) $widget->get_name(), $paid_widgets, true ) ? '' : $content;
    }

    public static function filter_account_menu_items( $items ) {
        if ( self::is_exempt_context() || current_user_can( 'manage_options' ) || ! is_array( $items ) ) {
            return $items;
        }
        unset( $items['orders'], $items['downloads'], $items['payment-methods'] );
        return $items;
    }

    public static function dequeue_paid_assets() {
        if ( self::is_exempt_context() ) {
            return;
        }
        wp_dequeue_script( 'raspitajse-commerce-package-purchase' );
        wp_deregister_script( 'raspitajse-commerce-package-purchase' );
    }

    public static function deny_public_purchase() {
        return self::is_exempt_context() || current_user_can( 'manage_options' );
    }

    public static function filter_product_purchasable( $purchasable, $product ) {
        if ( self::is_exempt_context() || current_user_can( 'manage_options' ) ) {
            return $purchasable;
        }
        return false;
    }

    public static function filter_paid_url( $url ) {
        if ( self::is_exempt_context() || current_user_can( 'manage_options' ) ) {
            return $url;
        }
        return self::destination_url( self::is_employer_user() );
    }

    public static function deny_add_to_cart_request() {
        if ( self::is_exempt_context() || current_user_can( 'manage_options' ) || ! isset( $_REQUEST['add-to-cart'] ) ) {
            return;
        }
        self::respond_to_paid_request( self::is_employer_user() );
    }

    public static function protect_paid_routes() {
        if ( self::is_exempt_context() || current_user_can( 'manage_options' ) || ! self::is_paid_route() ) {
            return;
        }
        self::respond_to_paid_request( self::is_employer_user() );
    }

    public static function route_decision( $method, $employer ) {
        $method = strtoupper( (string) $method );
        if ( 'GET' !== $method && 'HEAD' !== $method ) {
            return array( 'action' => 'deny', 'status' => 405, 'target' => '' );
        }
        return array(
            'action' => 'redirect',
            'status' => 302,
            'target' => self::destination_url( (bool) $employer ),
        );
    }

    private static function respond_to_paid_request( $employer ) {
        $method = isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $decision = self::route_decision( $method, $employer );
        if ( 'redirect' === $decision['action'] ) {
            wp_safe_redirect( $decision['target'], $decision['status'], 'Raspitajse-Free-Launch' );
            exit;
        }
        status_header( $decision['status'] );
        nocache_headers();
        wp_die(
            esc_html__( 'Ova kupovna ruta nije dostupna tokom besplatnog pristupa.', 'raspitajse-commerce' ),
            esc_html__( 'Kupovina nije dostupna', 'raspitajse-commerce' ),
            array( 'response' => $decision['status'] )
        );
    }

    private static function is_paid_route() {
        $page_id = absint( get_queried_object_id() );
        if ( in_array( $page_id, self::paid_page_ids(), true ) ) {
            return true;
        }
        if ( function_exists( 'is_wc_endpoint_url' ) ) {
            foreach ( array( 'order-pay', 'add-payment-method' ) as $endpoint ) {
                if ( is_wc_endpoint_url( $endpoint ) ) {
                    return true;
                }
            }
        }
        return function_exists( 'is_product' ) && is_product();
    }

    private static function destination_url( $employer ) {
        $page_id = 0;
        if ( function_exists( 'wp_job_board_pro_get_option' ) ) {
            $page_id = absint(
                wp_job_board_pro_get_option(
                    $employer ? 'submit_job_form_page_id' : 'user_dashboard_page_id'
                )
            );
        }
        if ( ! $page_id ) {
            $page_id = $employer ? self::SUBMIT_PAGE_ID : self::DASHBOARD_PAGE_ID;
        }
        $url = get_permalink( $page_id );
        return $url ? $url : home_url( '/' );
    }

    private static function is_employer_user() {
        if ( ! is_user_logged_in() || ! class_exists( 'WP_Job_Board_Pro_User' ) ) {
            return false;
        }
        return WP_Job_Board_Pro_User::is_employer()
            || ( method_exists( 'WP_Job_Board_Pro_User', 'is_employee' ) && WP_Job_Board_Pro_User::is_employee() );
    }

    private static function paid_page_ids() {
        return array(
            self::PRICING_PAGE_ID,
            self::PACKAGES_PAGE_ID,
            self::CART_PAGE_ID,
            self::CHECKOUT_PAGE_ID,
        );
    }

    private static function url_targets_paid_page( $url ) {
        $path = wp_parse_url( (string) $url, PHP_URL_PATH );
        if ( ! is_string( $path ) ) {
            return false;
        }
        foreach ( self::paid_page_ids() as $page_id ) {
            $paid_path = wp_parse_url( get_permalink( $page_id ), PHP_URL_PATH );
            if ( is_string( $paid_path ) && untrailingslashit( $paid_path ) === untrailingslashit( $path ) ) {
                return true;
            }
        }
        return false;
    }
}
