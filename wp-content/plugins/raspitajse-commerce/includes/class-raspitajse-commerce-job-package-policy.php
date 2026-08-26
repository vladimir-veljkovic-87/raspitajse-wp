<?php
/**
 * Canonical employer job-package policy and Paid Listings lifecycle adapter.
 *
 * @package Raspitajse_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Raspitajse_Commerce_Job_Package_Policy {

    const META_ACTIVATED_AT = '_raspitajse_job_package_activated_at';
    const META_VALID_UNTIL  = '_raspitajse_job_package_valid_until';

    const ORDER_META_PROCESSED = 'wp_job_board_pro_wc_paid_listings_packages_processed';
    const ORDER_META_CANCELLED = 'wp_job_board_pro_wc_paid_listings_packages_cancelled';

    const STATUS_AVAILABLE   = 'available';
    const STATUS_EXHAUSTED   = 'exhausted';
    const STATUS_EXPIRED     = 'expired';
    const STATUS_REVOKED     = 'revoked';
    const STATUS_UNAVAILABLE = 'unavailable';

    const VALIDITY_DAYS = 30;

    /**
     * First-activation timestamps for the current order-status callback.
     *
     * @var array
     */
    private static $activation_context = array();

    /**
     * Prevent recursive metadata-bridge calls.
     *
     * @var bool
     */
    private static $metadata_bridge_active = false;

    /**
     * Register lifecycle, authorization and HPOS compatibility hooks.
     */
    public static function boot() {
        add_filter(
            'get_post_metadata',
            array( __CLASS__, 'read_order_marker_through_crud' ),
            10,
            5
        );
        add_filter(
            'update_post_metadata',
            array( __CLASS__, 'write_order_marker_through_crud' ),
            10,
            5
        );

        add_action(
            'woocommerce_order_status_processing',
            array( __CLASS__, 'begin_order_activation' ),
            1
        );
        add_action(
            'woocommerce_order_status_completed',
            array( __CLASS__, 'begin_order_activation' ),
            1
        );
        add_action(
            'wp_job_board_pro_wc_paid_listings_create_user_package_meta',
            array( __CLASS__, 'stamp_created_entitlement' ),
            10,
            4
        );
        add_action(
            'woocommerce_order_status_processing',
            array( __CLASS__, 'end_order_activation' ),
            PHP_INT_MAX
        );
        add_action(
            'woocommerce_order_status_completed',
            array( __CLASS__, 'end_order_activation' ),
            PHP_INT_MAX
        );

        add_action(
            'woocommerce_order_status_failed',
            array( __CLASS__, 'revoke_failed_order' ),
            10
        );

        add_filter(
            'wp_job_board_pro_submit_job_steps',
            array( __CLASS__, 'protect_submit_handlers' ),
            50
        );
    }

    /**
     * Capture first successful activation before Paid Listings runs.
     *
     * @param int $order_id Woo order ID.
     */
    public static function begin_order_activation( $order_id ) {
        $order_id = absint( $order_id );
        $order    = $order_id && function_exists( 'wc_get_order' )
            ? wc_get_order( $order_id )
            : false;

        if (
            ! $order instanceof WC_Order
            || $order->get_meta( self::ORDER_META_PROCESSED, true )
        ) {
            return;
        }

        self::$activation_context[ $order_id ] = current_datetime()->getTimestamp();
    }

    /**
     * Clear request-local activation state.
     *
     * @param int $order_id Woo order ID.
     */
    public static function end_order_activation( $order_id ) {
        unset( self::$activation_context[ absint( $order_id ) ] );
    }

    /**
     * Store an immutable canonical 30-day window on a new entitlement.
     *
     * The filter is the explicit deterministic disposable-fixture seam.
     *
     * @param int $entitlement_id User-package post ID.
     * @param int $user_id        Owning WordPress user ID.
     * @param int $product_id     Woo product ID.
     * @param int $order_id       Woo order ID.
     */
    public static function stamp_created_entitlement(
        $entitlement_id,
        $user_id,
        $product_id,
        $order_id
    ) {
        $entitlement_id = absint( $entitlement_id );
        $order_id       = absint( $order_id );

        if (
            ! $entitlement_id
            || ! isset( self::$activation_context[ $order_id ] )
            || 'job_package' !== get_post_type( $entitlement_id )
            || metadata_exists( 'post', $entitlement_id, self::META_ACTIVATED_AT )
            || metadata_exists( 'post', $entitlement_id, self::META_VALID_UNTIL )
        ) {
            return;
        }

        $activated_at = absint( self::$activation_context[ $order_id ] );
        $valid_until  = self::calculate_valid_until( $activated_at );

        $window = apply_filters(
            'raspitajse_commerce_job_package_activation_window',
            array(
                'activated_at' => $activated_at,
                'valid_until'  => $valid_until,
            ),
            $entitlement_id,
            absint( $user_id ),
            absint( $product_id ),
            $order_id
        );

        if ( ! is_array( $window ) ) {
            return;
        }

        $activated_at = isset( $window['activated_at'] )
            ? absint( $window['activated_at'] )
            : 0;
        $valid_until = isset( $window['valid_until'] )
            ? absint( $window['valid_until'] )
            : 0;

        if ( ! $activated_at || $valid_until <= $activated_at ) {
            return;
        }

        $activation_added = add_post_meta(
            $entitlement_id,
            self::META_ACTIVATED_AT,
            $activated_at,
            true
        );

        if ( ! $activation_added ) {
            return;
        }

        $valid_until_added = add_post_meta(
            $entitlement_id,
            self::META_VALID_UNTIL,
            $valid_until,
            true
        );

        if ( ! $valid_until_added ) {
            delete_post_meta(
                $entitlement_id,
                self::META_ACTIVATED_AT,
                $activated_at
            );
        }
    }

    /**
     * Return one canonical employer entitlement state.
     *
     * Runtime callers omit the optional exact timestamp. It exists only so the
     * boundary rule can be tested without waiting or changing the system clock.
     *
     * @param int      $user_id        WordPress user ID.
     * @param int      $entitlement_id User-package post ID.
     * @param int|null $now_timestamp  Optional exact current timestamp.
     * @return string
     */
    public static function get_status(
        $user_id,
        $entitlement_id,
        $now_timestamp = null
    ) {
        $user_id        = self::normalize_user_id( $user_id );
        $entitlement_id = absint( $entitlement_id );
        $post           = $entitlement_id ? get_post( $entitlement_id ) : null;

        if ( ! $user_id || ! $post || 'job_package' !== $post->post_type ) {
            return self::STATUS_UNAVAILABLE;
        }

        $prefix       = self::paid_listings_prefix();
        $package_type = (string) get_post_meta(
            $entitlement_id,
            $prefix . 'package_type',
            true
        );
        $owner_id = absint(
            get_post_meta( $entitlement_id, $prefix . 'user_id', true )
        );

        if ( 'job_package' !== $package_type || $owner_id !== $user_id ) {
            return self::STATUS_UNAVAILABLE;
        }

        if ( 'cancelled' === $post->post_status ) {
            return self::STATUS_REVOKED;
        }

        if ( 'publish' !== $post->post_status ) {
            return self::STATUS_UNAVAILABLE;
        }

        $activated_at = absint(
            get_post_meta( $entitlement_id, self::META_ACTIVATED_AT, true )
        );
        $valid_until = absint(
            get_post_meta( $entitlement_id, self::META_VALID_UNTIL, true )
        );

        if ( ! $activated_at || $valid_until <= $activated_at ) {
            return self::STATUS_UNAVAILABLE;
        }

        $now_timestamp = null === $now_timestamp
            ? current_datetime()->getTimestamp()
            : absint( $now_timestamp );

        if ( $now_timestamp >= $valid_until ) {
            return self::STATUS_EXPIRED;
        }

        $package_count = absint(
            get_post_meta( $entitlement_id, $prefix . 'package_count', true )
        );
        $job_limit = absint(
            get_post_meta( $entitlement_id, $prefix . 'job_limit', true )
        );

        if ( 0 !== $job_limit && $package_count >= $job_limit ) {
            return self::STATUS_EXHAUSTED;
        }

        return self::STATUS_AVAILABLE;
    }

    /**
     * Whether the entitlement can authorize a new job now.
     *
     * @param int      $user_id        WordPress user ID.
     * @param int      $entitlement_id User-package post ID.
     * @param int|null $now_timestamp  Optional exact current timestamp.
     * @return bool
     */
    public static function is_usable(
        $user_id,
        $entitlement_id,
        $now_timestamp = null
    ) {
        return self::STATUS_AVAILABLE === self::get_status(
            $user_id,
            $entitlement_id,
            $now_timestamp
        );
    }

    /**
     * Read the canonical expiry timestamp for presentation.
     *
     * @param int $entitlement_id User-package post ID.
     * @return int
     */
    public static function get_valid_until( $entitlement_id ) {
        return absint(
            get_post_meta(
                absint( $entitlement_id ),
                self::META_VALID_UNTIL,
                true
            )
        );
    }

    /**
     * Return employer job entitlements, including revoked history for UI.
     *
     * @param int  $user_id        WordPress user ID.
     * @param bool $include_revoked Include cancelled entitlements.
     * @return array
     */
    public static function get_packages_by_user(
        $user_id,
        $include_revoked = true
    ) {
        $user_id = self::normalize_user_id( $user_id );

        if ( ! $user_id ) {
            return array();
        }

        $prefix = self::paid_listings_prefix();

        return get_posts(
            array(
                'post_type'      => 'job_package',
                'post_status'    => $include_revoked
                    ? array( 'publish', 'cancelled' )
                    : 'publish',
                'posts_per_page' => -1,
                'order'          => 'ASC',
                'orderby'        => 'date ID',
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => $prefix . 'user_id',
                        'value'   => $user_id,
                        'compare' => '=',
                    ),
                    array(
                        'key'     => $prefix . 'package_type',
                        'value'   => 'job_package',
                        'compare' => '=',
                    ),
                ),
            )
        );
    }

    /**
     * Replace submit handlers with policy wrappers, leaving vendor views intact.
     *
     * @param array $steps WP Job Board Pro submit steps.
     * @return array
     */
    public static function protect_submit_handlers( $steps ) {
        if ( isset( $steps['wjbp-choose-packages'] ) ) {
            $steps['wjbp-choose-packages']['handler'] = array(
                __CLASS__,
                'choose_package_handler',
            );
        }

        if ( isset( $steps['wjbp-process-packages'] ) ) {
            $steps['wjbp-process-packages']['handler'] = array(
                __CLASS__,
                'process_package_handler',
            );
        }

        return $steps;
    }

    /**
     * Reject unavailable existing entitlements before vendor selection.
     *
     * @return mixed
     */
    public static function choose_package_handler() {
        if ( ! self::selected_existing_package_is_usable() ) {
            return self::reject_selected_package();
        }

        return WP_Job_Board_Pro_Wc_Paid_Listings_Submit_Form::choose_package_handler();
    }

    /**
     * Re-check immediately before job mutation and quota consumption.
     *
     * @return mixed
     */
    public static function process_package_handler() {
        if ( ! self::selected_existing_package_is_usable() ) {
            return self::reject_selected_package();
        }

        return WP_Job_Board_Pro_Wc_Paid_Listings_Submit_Form::process_package_handler();
    }

    /**
     * Revoke on failed only when the order had already activated packages.
     *
     * @param int $order_id Woo order ID.
     */
    public static function revoke_failed_order( $order_id ) {
        $order = function_exists( 'wc_get_order' )
            ? wc_get_order( absint( $order_id ) )
            : false;

        if (
            ! $order instanceof WC_Order
            || ! $order->get_meta( self::ORDER_META_PROCESSED, true )
            || ! class_exists( 'WP_Job_Board_Pro_Wc_Paid_Listings_Order' )
        ) {
            return;
        }

        WP_Job_Board_Pro_Wc_Paid_Listings_Order::order_cancelled(
            $order->get_id()
        );
    }

    /**
     * Route the vendor's marker reads through WC_Order under HPOS.
     *
     * @param mixed  $value     Existing short-circuit value.
     * @param int    $object_id Object ID.
     * @param string $meta_key  Meta key.
     * @param bool   $single    Whether one value was requested.
     * @param string $meta_type Metadata type.
     * @return mixed
     */
    public static function read_order_marker_through_crud(
        $value,
        $object_id,
        $meta_key,
        $single,
        $meta_type
    ) {
        if (
            null !== $value
            || self::$metadata_bridge_active
            || 'post' !== $meta_type
            || ! self::is_order_marker( $meta_key )
            || ! self::hpos_is_authoritative()
            || ! function_exists( 'wc_get_order' )
        ) {
            return $value;
        }

        self::$metadata_bridge_active = true;
        $order                        = wc_get_order( absint( $object_id ) );

        if ( $order instanceof WC_Order ) {
            $order->read_meta_data( true );
        }
        $stored                       = $order instanceof WC_Order
            ? $order->get_meta( $meta_key, true, 'edit' )
            : null;
        self::$metadata_bridge_active = false;

        if ( ! $order instanceof WC_Order ) {
            return $value;
        }

        return $single
            ? $stored
            : ( '' === $stored ? array() : array( $stored ) );
    }

    /**
     * Route the vendor's marker writes through WC_Order under HPOS.
     *
     * @param mixed  $check      Existing short-circuit value.
     * @param int    $object_id  Object ID.
     * @param string $meta_key   Meta key.
     * @param mixed  $meta_value Meta value.
     * @param mixed  $prev_value Previous-value constraint.
     * @return mixed
     */
    public static function write_order_marker_through_crud(
        $check,
        $object_id,
        $meta_key,
        $meta_value,
        $prev_value
    ) {
        if (
            null !== $check
            || self::$metadata_bridge_active
            || ! self::is_order_marker( $meta_key )
            || ! self::hpos_is_authoritative()
            || ! function_exists( 'wc_get_order' )
        ) {
            return $check;
        }

        self::$metadata_bridge_active = true;
        $order                        = wc_get_order( absint( $object_id ) );

        if ( $order instanceof WC_Order ) {
            $order->read_meta_data( true );
        }

        if ( $order instanceof WC_Order ) {
            $order->update_meta_data( $meta_key, $meta_value );
            $order->save_meta_data();
        }

        self::$metadata_bridge_active = false;

        return $order instanceof WC_Order ? true : $check;
    }

    /**
     * Calculate 30 calendar days in the configured WordPress timezone.
     *
     * @param int $activated_at Activation timestamp.
     * @return int
     */
    private static function calculate_valid_until( $activated_at ) {
        $activated = ( new DateTimeImmutable( '@' . absint( $activated_at ) ) )
            ->setTimezone( wp_timezone() );

        return $activated
            ->modify( '+' . self::VALIDITY_DAYS . ' days' )
            ->getTimestamp();
    }

    /**
     * Whether the selected existing package passes canonical policy.
     *
     * New-product purchase selection remains the vendor's responsibility.
     *
     * @return bool
     */
    private static function selected_existing_package_is_usable() {
        $selected = absint(
            WP_Job_Board_Pro_Wc_Paid_Listings_Submit_Form::$listing_user_package
        );

        return ! $selected
            || self::is_usable( get_current_user_id(), $selected );
    }

    /**
     * Add a form error and return to package selection.
     *
     * @return false
     */
    private static function reject_selected_package() {
        $form  = WP_Job_Board_Pro_Submit_Form::get_instance();
        $steps = $form->get_steps();
        $index = array_search(
            'wjbp-choose-packages',
            array_keys( $steps ),
            true
        );

        $form->add_error(
            esc_html__(
                'Izabrani paket više nije dostupan.',
                'raspitajse-commerce'
            )
        );

        if ( false !== $index ) {
            $form->set_step( $index );
        }

        return false;
    }

    /**
     * Normalize a user/employer identifier through Paid Listings.
     *
     * @param int $user_id User or employer identifier.
     * @return int
     */
    private static function normalize_user_id( $user_id ) {
        $user_id = absint( $user_id );

        if (
            $user_id
            && class_exists( 'WP_Job_Board_Pro_Wc_Paid_Listings_Mixes' )
        ) {
            $user_id = absint(
                WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_user_id(
                    $user_id
                )
            );
        }

        return $user_id;
    }

    /**
     * Return the active Paid Listings meta prefix.
     *
     * @return string
     */
    private static function paid_listings_prefix() {
        return defined( 'WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX' )
            ? WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX
            : '_wjbpwpl_';
    }

    /**
     * Whether the key is an existing vendor order marker contract.
     *
     * @param string $meta_key Meta key.
     * @return bool
     */
    private static function is_order_marker( $meta_key ) {
        return in_array(
            $meta_key,
            array(
                self::ORDER_META_PROCESSED,
                self::ORDER_META_CANCELLED,
            ),
            true
        );
    }

    /**
     * Whether WooCommerce HPOS is authoritative.
     *
     * @return bool
     */
    private static function hpos_is_authoritative() {
        $class = '\\Automattic\\WooCommerce\\Utilities\\OrderUtil';

        return class_exists( $class )
            && $class::custom_orders_table_usage_is_enabled();
    }
}
