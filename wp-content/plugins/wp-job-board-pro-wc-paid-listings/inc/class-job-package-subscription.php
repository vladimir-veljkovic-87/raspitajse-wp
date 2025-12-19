<?php
/**
 * Job Package Subscription
 *
 * @package    wp-job-board-pro-wc-paid-listings
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WP_Job_Board_Pro_Wc_Paid_Listings_Job_Package_Subscription {
	public static function init() {
		if ( class_exists( 'WC_Subscriptions_Synchroniser' ) && method_exists( 'WC_Subscriptions_Synchroniser', 'save_subscription_meta' ) ) {
			add_action( 'woocommerce_process_product_meta_job_package_subscription', array('WC_Subscriptions_Synchroniser', 'save_subscription_meta'), 10 );
			add_action( 'woocommerce_process_product_meta_cv_package_subscription', array('WC_Subscriptions_Synchroniser', 'save_subscription_meta'), 10 );
			add_action( 'woocommerce_process_product_meta_contact_package_subscription', array('WC_Subscriptions_Synchroniser', 'save_subscription_meta'), 10 );
			add_action( 'woocommerce_process_product_meta_candidate_package_subscription', array('WC_Subscriptions_Synchroniser', 'save_subscription_meta'), 10 );
			add_action( 'woocommerce_process_product_meta_resume_package_subscription', array('WC_Subscriptions_Synchroniser', 'save_subscription_meta'), 10 );
		}

		add_action( 'added_post_meta', array( __CLASS__, 'updated_post_meta' ), 10, 4 );
		add_action( 'updated_post_meta', array( __CLASS__, 'updated_post_meta' ), 10, 4 );

		add_filter( 'woocommerce_is_subscription', array( __CLASS__, 'woocommerce_is_subscription' ), 10, 2 );

		add_action( 'wp_trash_post', array( __CLASS__, 'wp_trash_post' ) );
		add_action( 'untrash_post', array( __CLASS__, 'untrash_post' ) );

		add_action( 'publish_to_expired', array( __CLASS__, 'check_expired_listing' ) );


		// Subscription is paused
		add_action( 'woocommerce_subscription_status_on-hold', array( __CLASS__, 'subscription_paused' ) ); // When a subscription is put on hold

		// Subscription is ended
		add_action( 'woocommerce_scheduled_subscription_expiration', array( __CLASS__, 'subscription_ended' ) ); // When a subscription expires
		add_action( 'woocommerce_scheduled_subscription_end_of_prepaid_term', array( __CLASS__, 'subscription_ended' ) ); // When a subscription ends after remaining unpaid
		add_action( 'woocommerce_subscription_status_cancelled', array( __CLASS__, 'subscription_ended' ) ); // When the subscription status changes to cancelled

		// Subscription starts
		add_action( 'woocommerce_subscription_status_active', array( __CLASS__, 'subscription_activated' ) ); // When the subscription status changes to active

		// On renewal
		add_action( 'woocommerce_subscription_renewal_payment_complete', array( __CLASS__, 'subscription_renewed' ) ); // When the subscription is renewed

		// Subscription is switched
		add_action( 'woocommerce_subscriptions_switched_item', array( __CLASS__, 'subscription_switched' ), 10, 3 ); // When the subscription is switched and a new subscription is created
		add_action( 'woocommerce_subscription_item_switched', array( __CLASS__, 'subscription_item_switched' ), 10, 4 ); // When the subscription is switched and only the item is changed
	}

	public static function updated_post_meta($meta_id, $object_id, $meta_key, $meta_value) {
		$post_type = get_post_type( $object_id );
		if ( $post_type === 'job_listing') {
			$prefix = WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX;
			if ( $meta_value !== '' && $prefix.'expiry_date' === $meta_key ) {
				$package_id = get_post_meta( $object_id, $prefix.'package_id', true );
				$package = wc_get_product( $package_id );
				$subscription_type = get_post_meta($package_id, '_job_package_subscription_type', true);

				if ( $package && 'listing' === $subscription_type ) {
					update_post_meta( $object_id, $prefix.'expiry_date', '' ); // Never expire automatically
				}
			}
		} elseif ( $post_type == 'candidate' ) {
			$prefix = WP_JOB_BOARD_PRO_CANDIDATE_PREFIX;
			if ( $meta_value !== '' && $prefix.'expiry_date' === $meta_key ) {
				$package_id = get_post_meta( $object_id, $prefix.'package_id', true );
				$package = wc_get_product( $package_id );
				$subscription_type = get_post_meta($package_id, '_resume_package_subscription_type', true);

				if ( $package && 'listing' === $subscription_type ) {
					update_post_meta( $object_id, $prefix.'expiry_date', '' ); // Never expire automatically
				}
			}
		}
	}

	public static function woocommerce_is_subscription( $is_subscription, $product_id ) {
		$product = wc_get_product( $product_id );
		if ( $product && $product->is_type( array( 'job_package_subscription', 'cv_package_subscription', 'contact_package_subscription', 'candidate_package_subscription', 'resume_package_subscription' ) ) ) {
			$is_subscription = true;
		}
		return $is_subscription;
	}

	public static function get_package_subscription_type( $product_id ) {
		$subscription_type = get_post_meta( $product_id, '_package_subscription_type', true );
		return empty( $subscription_type ) ? 'package' : $subscription_type;
	}

	public static function wp_trash_post( $id ) {
		if ( $id > 0 ) {
			$post_type = get_post_type( $id );

			if ( $post_type === 'job_listing' ) {
				$prefix = WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX;
				$package_product_id = get_post_meta( $id, $prefix.'package_id', true );
				$user_package_id = get_post_meta( $id, $prefix.'user_package_id', true );

				if ( $package_product_id ) {
					$subscription_type = self::get_package_subscription_type( $package_product_id );

					if ( 'listing' === $subscription_type ) {
						$new_count = get_post_meta($user_package_id, WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX.'package_count', true);
						$new_count --;

						update_post_meta($user_package_id, WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX.'package_count', $new_count);
					}
				}
			}
		}
	}

	/**
	 * If a listing gets restored, the pack may need it's listing count changing
	 */
	public static function untrash_post( $id ) {
		if ( $id > 0 ) {
			$post_type = get_post_type( $id );

			if ( 'job_listing' === $post_type ) {
				$prefix = WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX;
				$package_product_id = get_post_meta( $id, $prefix.'package_id', true );
				$user_package_id = get_post_meta( $id, $prefix.'user_package_id', true );

				if ( $package_product_id ) {
					$subscription_type = self::get_package_subscription_type( $package_product_id );

					if ( 'listing' === $subscription_type ) {
						$new_count = get_post_meta($user_package_id, WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX.'package_count', true);
						$new_count++;
						$job_limit = get_post_meta($user_package_id, WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX.'job_limit', true);
						$new_count = min( $job_limit, $new_count );

						update_post_meta($user_package_id, WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX.'package_count', $new_count);
					}
				}
			}
		}
	}

	public static function check_expired_listing( $post ) {
		if ( 'job_listing' === $post->post_type ) {
			$prefix = WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX;
			$package_product_id = get_post_meta( $post->ID, $prefix.'package_id', true );
			$user_package_id = get_post_meta( $post->ID, $prefix.'user_package_id', true );
			

			if ( $package_product_id ) {
				$subscription_type = self::get_package_subscription_type( $package_product_id );

				if ( 'listing' === $subscription_type ) {
					$new_count = get_post_meta($user_package_id, WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX.'package_count', true);
					$new_count --;
					$new_count = max( 0, $new_count );

					update_post_meta($user_package_id, WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX.'package_count', $new_count);

					// Remove package meta after adjustment
					delete_post_meta( $post->ID, $prefix.'package_id' );
					delete_post_meta( $post->ID, $prefix.'user_package_id' );
				}
			}
		}
	}

	public static function subscription_paused( $subscription ) {
		self::subscription_ended( $subscription );
	}

	public static function subscription_ended( $subscription ) {
		$prefix = WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX;
		if ( is_object($subscription) && method_exists($subscription, 'get_parent') ) {
			$legacy_id = !empty($subscription->get_parent()->get_id()) ? $subscription->get_parent()->get_id() : $subscription->get_id();
		} else {
			if ( is_object($subscription) && method_exists($subscription, 'get_id') ) {
				$legacy_id = $subscription->get_id();
			} else {
				$legacy_id = 0;
			}
		}
		if ( !is_object($subscription) || !method_exists($subscription, 'get_items') ) {
			return;
		}
		foreach ( $subscription->get_items() as $item ) {
			

			$user_packages = get_posts(array(
				'post_type' => array('job_package'),
				'fields' => 'ids',
				'meta_query' => array(
					array(
						'key'     => $prefix.'order_id',
						'value'   => array($legacy_id, $subscription->get_id()),
						'compare' => 'IN'
					),
					array(
						'key'     => $prefix.'product_id',
						'value'   => $item['product_id'],
						'compare' => '='
					),
				)
			));

			if ( $user_packages ) {
				foreach ($user_packages as $user_package_id) {
					$package_type = get_post_meta( $user_package_id, $prefix.'package_type', true );
					$subscription_type = get_post_meta( $user_package_id, $prefix.'subscription_type', true );
					if ( $package_type == 'job_package' ) {
						// Expire listings posted with package

						if ( 'listing' === $subscription_type ) {
							$listing_ids = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_listings_for_package( $user_package_id, 'job_listing' );
							foreach ( $listing_ids as $listing_id ) {
								$listing = array( 'ID' => $listing_id, 'post_status' => 'expired' );
								wp_update_post( $listing );

								// Make a record of the subscription ID in case of re-activation
								update_post_meta( $listing_id, '_expired_subscription_id', $subscription->get_id() );
							}
						}
					} elseif ( $package_type == 'resume_package' ) {
						// Expire listings posted with package
						if ( 'listing' === $subscription_type ) {
							$listing_ids = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_listings_for_package( $user_package_id, 'candidate' );

							foreach ( $listing_ids as $listing_id ) {
								$listing = array( 'ID' => $listing_id, 'post_status' => 'expired' );
								wp_update_post( $listing );

								// Make a record of the subscription ID in case of re-activation
								update_post_meta( $listing_id, '_expired_subscription_id', $subscription->get_id() );
							}
						}
					}

					// Delete the package
					wp_delete_post($user_package_id);
				}
				
			}
		}

		delete_post_meta( $subscription->get_id(), 'wp_job_board_pro_wc_paid_listings_packages_processed' );
	}

	public static function subscription_activated( $subscription ) {
		global $wpdb;
		$prefix = WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX;

		if ( get_post_meta( $subscription->get_id(), 'wp_job_board_pro_wc_paid_listings_packages_processed', true ) ) {
			return;
		}

		// Remove any old packages for this subscription
		if ( is_object($subscription) && method_exists($subscription, 'get_parent') ) {
			$legacy_id = !empty($subscription->get_parent()->get_id()) ? $subscription->get_parent()->get_id() : $subscription->get_id();
		} else {
			if ( is_object($subscription) && method_exists($subscription, 'get_id') ) {
				$legacy_id = $subscription->get_id();
			} else {
				$legacy_id = 0;
			}
		}
		if ( !is_object($subscription) || !method_exists($subscription, 'get_items') ) {
			return;
		}

		foreach ( $subscription->get_items() as $item ) {
			$user_packages = get_posts(array(
				'post_type' => array('job_package'),
				'fields' => 'ids',
				'meta_query' => array(
					array(
						'key'     => $prefix.'order_id',
						'value'   => array($legacy_id, $subscription->get_id()),
						'compare' => 'IN'
					),
					array(
						'key'     => $prefix.'product_id',
						'value'   => $item['product_id'],
						'compare' => '='
					),
				)
			));

			if ( $user_packages ) {
				foreach ($user_packages as $user_package_id) {
					wp_delete_post($user_package_id);
				}
			}

			$product           = wc_get_product( $item['product_id'] );
			$subscription_type = self::get_package_subscription_type( $item['product_id'] );

			// Give user packages for this subscription
			if ( $product->is_type( array( 'job_package_subscription' ) ) && $subscription->get_user_id() && ! isset( $item['switched_subscription_item_id'] ) ) {

				// Give packages to user
				for ( $i = 0; $i < $item['qty']; $i ++ ) {
					$user_package_id = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::create_user_package( $subscription->get_user_id(), $product->get_id(), $subscription->get_id() );
				}

				/**
				 * If the subscription is associated with listings, see if any
				 * already match this ID and approve them (useful on
				 * re-activation of a sub).
				 */
				if ( 'listing' === $subscription_type ) {
					$listing_ids = (array) $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key=%s AND meta_value=%s", '_expired_subscription_id', $subscription->get_id() ) );
				} else {
					$listing_ids = array();
				}

				$listing_ids[] = isset( $item['job_id'] ) ? $item['job_id'] : '';
				$listing_ids   = array_unique( array_filter( array_map( 'absint', $listing_ids ) ) );

				foreach ( $listing_ids as $listing_id ) {
					if ( in_array( get_post_status( $listing_id ), array( 'pending_payment', 'expired' ) ) ) {
						WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::approve_job_with_package( $listing_id, $subscription->get_user_id(), $user_package_id );
						delete_post_meta( $listing_id, '_expired_subscription_id' );
					}
				}
			} elseif ( $product->is_type( array( 'resume_package_subscription' ) ) && $subscription->get_user_id() && ! isset( $item['switched_subscription_item_id'] ) ) {
				// Give packages to user
				for ( $i = 0; $i < $item['qty']; $i ++ ) {
					$user_package_id = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::create_user_resume_package( $subscription->get_user_id(), $product->get_id(), $subscription->get_id() );
				}

				WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::increase_expiry_with_package( $subscription->get_user_id(), $user_package_id );
				$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($subscription->get_user_id());
				if ( $candidate_id ) {
					update_post_meta( $candidate_id, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'package_id', $product->get_id() );
				}

			} elseif ( $product->is_type( array( 'cv_package_subscription' ) ) && $subscription->get_user_id() && ! isset( $item['switched_subscription_item_id'] ) ) {
				// Give packages to user
				for ( $i = 0; $i < $item['qty']; $i ++ ) {
					$user_package_id = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::create_user_cv_package( $subscription->get_user_id(), $product->get_id(), $subscription->get_id() );
				}
			} elseif ( $product->is_type( array( 'contact_package_subscription' ) ) && $subscription->get_user_id() && ! isset( $item['switched_subscription_item_id'] ) ) {
				// Give packages to user
				for ( $i = 0; $i < $item['qty']; $i ++ ) {
					$user_package_id = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::create_user_contact_package( $subscription->get_user_id(), $product->get_id(), $subscription->get_id() );
				}
			} elseif ( $product->is_type( array( 'candidate_package_subscription' ) ) && $subscription->get_user_id() && ! isset( $item['switched_subscription_item_id'] ) ) {
				// Give packages to user
				for ( $i = 0; $i < $item['qty']; $i ++ ) {
					$user_package_id = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::create_user_candidate_package( $subscription->get_user_id(), $product->get_id(), $subscription->get_id() );
				}
			}
		}

		update_post_meta( $subscription->get_id(), 'wp_job_board_pro_wc_paid_listings_packages_processed', true );
	}

	public static function subscription_renewed( $subscription ) {
		global $wpdb;
		$prefix = WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX;

		foreach ( $subscription->get_items() as $item ) {
			$product           = wc_get_product( $item['product_id'] );
			$subscription_type = self::get_package_subscription_type( $item['product_id'] );
			if ( is_object($subscription) && method_exists($subscription, 'get_parent') ) {
				$legacy_id = !empty($subscription->get_parent()->get_id()) ? $subscription->get_parent()->get_id() : $subscription->get_id();
			} else {
				if ( is_object($subscription) && method_exists($subscription, 'get_id') ) {
					$legacy_id = $subscription->get_id();
				} else {
					$legacy_id = 0;
				}
			}
			if ( !is_object($subscription) || !method_exists($subscription, 'get_items') ) {
				return;
			}

			// Renew packages which refresh every term
			$user_packages = get_posts(array(
				'post_type' => array('job_package'),
				'fields' => 'ids',
				'meta_query' => array(
					array(
						'key'     => $prefix.'order_id',
						'value'   => array($legacy_id, $subscription->get_id()),
						'compare' => 'IN'
					),
					array(
						'key'     => $prefix.'product_id',
						'value'   => $item['product_id'],
						'compare' => '='
					),
				)
			));
			if ( 'package' === $subscription_type ) {
				if ( $user_packages ) {
					foreach ($user_packages as $user_package_id) {
						$package_type = get_post_meta( $user_package_id, $prefix.'package_type', true );
						if ( $package_type == 'job_package' ) {
							update_post_meta($user_package_id, $prefix.'package_count', 0);
						} elseif ( $package_type == 'cv_package' ) {
							update_post_meta($user_package_id, $prefix.'cv_viewed_count', '');
							update_post_meta($user_package_id, $prefix.'cv_viewed_count_nb', 0);
						} elseif ( $package_type == 'contact_package' ) {
							update_post_meta($user_package_id, $prefix.'contact_viewed_count', '');
							update_post_meta($user_package_id, $prefix.'contact_viewed_count_nb', 0);
						} elseif ( $package_type == 'candidate_package' ) {
							update_post_meta($user_package_id, $prefix.'candidate_applied_count', '');
							update_post_meta($user_package_id, $prefix.'candidate_applied_count_nb', 0);
						}
					}
				} else {
					if ( $product->get_type() == 'job_package_subscription' ) {
						WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::create_user_package( $subscription->get_user_id(), $item['product_id'], $subscription->get_id() );
					} elseif ( $product->get_type() == 'cv_package_subscription' ) {
						WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::create_user_cv_package( $subscription->get_user_id(), $item['product_id'], $subscription->get_id() );
					} elseif ( $product->get_type() == 'contact_package_subscription' ) {
						WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::create_user_contact_package( $subscription->get_user_id(), $item['product_id'], $subscription->get_id() );
					} elseif ( $product->get_type() == 'candidate_package_subscription' ) {
						WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::create_user_candidate_package( $subscription->get_user_id(), $item['product_id'], $subscription->get_id() );
					} elseif ( $product->get_type() == 'resume_package_subscription' ) {
						WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::create_user_resume_package( $subscription->get_user_id(), $item['product_id'], $subscription->get_id() );
					}
				}

			// Otherwise the listings stay active, but we can ensure they are synced in terms of featured status etc
			} else {
				if ( $user_packages ) {
					foreach ( $user_packages as $user_package_id ) {
						$package_type = get_post_meta( $user_package_id, $prefix.'package_type', true );
						if ( $package_type == 'job_package' ) {
							$urgent_jobs = get_post_meta($user_package_id, $prefix.'urgent_jobs', true );
							$urgent = $urgent_jobs === 'yes' ? 1 : 0;
							$feature_jobs = get_post_meta($user_package_id, $prefix.'feature_jobs', true );
							$featured = $feature_jobs === 'yes' ? 1 : 0;
							if ( $listing_ids = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_listings_for_package( $user_package_id, 'job_listing' ) ) {
								foreach ( $listing_ids as $listing_id ) {
									// Featured | Urgent or not
									update_post_meta( $listing_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX. 'urgent', $urgent );
									update_post_meta( $listing_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX. 'featured', $featured );
								}
							}
						} elseif ( $package_type == 'resume_package' ) {
							$urgent_resumes = get_post_meta($user_package_id, $prefix.'urgent_resumes', true );
							$urgent = $urgent_resumes === 'yes' ? 1 : 0;
							$featured_resumes = get_post_meta($user_package_id, $prefix.'featured_resumes', true );
							$featured = $featured_resumes === 'yes' ? 1 : 0;

							$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($subscription->get_user_id());
							if ( $candidate_id ) {
								update_post_meta( $candidate_id, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'urgent', $urgent );
								update_post_meta( $candidate_id, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX.'featured', $featured );
							}
						} elseif ( $package_type == 'cv_package' ) {
							update_post_meta($user_package_id, $prefix.'cv_viewed_count', '');
							update_post_meta($user_package_id, $prefix.'cv_viewed_count_nb', 0);
						} elseif ( $package_type == 'contact_package' ) {
							update_post_meta($user_package_id, $prefix.'contact_viewed_count', '');
							update_post_meta($user_package_id, $prefix.'contact_viewed_count_nb', 0);
						} elseif ( $package_type == 'candidate_package' ) {
							update_post_meta($user_package_id, $prefix.'candidate_applied_count', '');
							update_post_meta($user_package_id, $prefix.'candidate_applied_count_nb', 0);
						}
					}
				}
			}
		}
	}

	public static function subscription_switched( $subscription, $new_order_item, $old_order_item ) {
		global $wpdb;

		$new_subscription = (object) array(
			'id'         => $subscription->get_id(),
			'product_id' => $new_order_item['product_id'],
			'product'    => wc_get_product( $new_order_item['product_id'] ),
			'type'       => self::get_package_subscription_type( $new_order_item['product_id'] )
		);

		$old_subscription = (object) array(
			'id'         => $wpdb->get_var( $wpdb->prepare( "SELECT order_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_id = %d ", $new_order_item['switched_subscription_item_id'] ) ),
			'product_id' => $old_order_item['product_id'],
			'product'    => wc_get_product( $old_order_item['product_id'] ),
			'type'       => self::get_package_subscription_type( $old_order_item['product_id'] )
		);

		self::switch_package( $subscription->get_user_id(), $new_subscription, $old_subscription );
	}

	public static function subscription_item_switched( $order, $subscription, $new_order_item_id, $old_order_item_id ) {
		global $wpdb;

		$new_order_item = WC_Subscriptions_Order::get_item_by_id( $new_order_item_id );
		$old_order_item = WC_Subscriptions_Order::get_item_by_id( $old_order_item_id );

		$new_subscription = (object) array(
			'id'           => $subscription->get_id(),
			'subscription' => $subscription,
			'product_id'   => $new_order_item['product_id'],
			'product'      => wc_get_product( $new_order_item['product_id'] ),
			'type'         => self::get_package_subscription_type( $new_order_item['product_id'] )
		);

		$old_subscription = (object) array(
			'id'           => $subscription->get_id(),
			'subscription' => $subscription,
			'product_id'   => $old_order_item['product_id'],
			'product'      => wc_get_product( $old_order_item['product_id'] ),
			'type'         => self::get_package_subscription_type( $old_order_item['product_id'] )
		);

		self::switch_package( $subscription->get_user_id(), $new_subscription, $old_subscription );
	}

	public static function switch_package( $user_id, $new_subscription, $old_subscription ) {
		$prefix = WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX;
		// Get the user package
		$user_packages = get_posts(array(
			'post_type' => array('job_package'),
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key'     => $prefix.'order_id',
					'value'   => $old_subscription->id,
					'compare' => '='
				),
				array(
					'key'     => $prefix.'product_id',
					'value'   => $item['product_id'],
					'compare' => '='
				),
			)
		));
		if ( $user_packages ) {

			// If invalid, abort
			if ( ! $new_subscription->product->is_type( array( 'job_package_subscription', 'resume_package_subscription' ) ) ) {
				return false;
			}

			foreach ($user_packages as $user_package_id) {
				$package_type = get_post_meta( $user_package_id, $prefix.'package_type', true );
				if ( $package_type == 'job_package' ) {
					// Give new package to user
					$switching_to_package_id = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::create_user_package( $user_id, $new_subscription->product_id, $new_subscription->id );

					// Upgrade?
					$package_count = get_post_meta($user_package_id, $prefix.'package_count', true);
					$limit_jobs = get_post_meta($new_subscription->product_id, '_jobs_limit', true );
					$is_upgrade = ( 0 === $limit_jobs || $limit_jobs >= $package_count );

					// Delete the old package
					wp_delete_post($user_package_id);

					// Update old listings
					if ( 'listing' === $new_subscription->type && $switching_to_package_id ) {
						$listing_ids = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_listings_for_package( $user_package_id, 'job_listing' );

						$urgent_jobs = get_post_meta($switching_to_package_id, $prefix.'urgent_jobs', true );
						$urgent = $urgent_jobs === 'yes' ? 1 : 0;
						$feature_jobs = get_post_meta($switching_to_package_id, $prefix.'feature_jobs', true );
						$featured = $feature_jobs === 'yes' ? 1 : 0;

						foreach ( $listing_ids as $listing_id ) {
							// If we are not upgrading, expire the old listing
							if ( ! $is_upgrade ) {
								$listing = array( 'ID' => $listing_id, 'post_status' => 'expired' );
								wp_update_post( $listing );
							} else {
								WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::increase_package_count( $user_id, $switching_to_package_id );
								// Change the user package ID and package ID
								update_post_meta( $listing_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'user_package_id', $switching_to_package_id );
								update_post_meta( $listing_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'package_id', $new_subscription->product_id );
							}

							// Featured | Urgent or not
							update_post_meta( $listing_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'urgent', $urgent );
							update_post_meta( $listing_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'featured', $featured );
							// Fire action
							do_action( 'wc_paid_listings_switched_subscription', $listing_id, $user_package_id );
						}
					}
				} elseif( $package_type == 'resume_package' ) {
					// Give new package to user
					$switching_to_package_id = WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::create_user_resume_package( $user_id, $new_subscription->product_id, $new_subscription->id );
					// Delete the old package
					wp_delete_post($user_package_id);

					// Update old listings
					if ( 'listing' === $new_subscription->type && $switching_to_package_id ) {
						$urgent_resumes = get_post_meta($switching_to_package_id, $prefix.'urgent_resumes', true );
						$urgent = $urgent_resumes === 'yes' ? 1 : 0;
						$featured_resumes = get_post_meta($switching_to_package_id, $prefix.'featured_resumes', true );
						$featured = $featured_resumes === 'yes' ? 1 : 0;

						$candidate_id = WP_Job_Board_Pro_User::get_candidate_by_user_id($subscription->get_user_id());
						if ( $candidate_id ) {
							// Featured | Urgent or not
							update_post_meta( $candidate_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'urgent', $urgent );
							update_post_meta( $candidate_id, WP_JOB_BOARD_PRO_JOB_LISTING_PREFIX.'featured', $featured );
							// Fire action
							do_action( 'wc_paid_listings_switched_subscription', $candidate_id, $user_package_id );
						}
					}
				}
			}
		}
	}


}

WP_Job_Board_Pro_Wc_Paid_Listings_Job_Package_Subscription::init();