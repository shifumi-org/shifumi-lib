<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

use Automattic\WooCommerce\Admin\Overrides\OrderRefund;

/**
* Tools class
*/
class Learndash_WooCommerce_Tools {
	
	/**
	 * Hook functions
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_ajax_ld_wc_retroactive_access', array( $this, 'ajax_retroactive_access' ) );

		add_filter( 'woocommerce_debug_tools', array( $this, 'course_retroactive_access_tool' ) );
	}

	/**
	 * Enqueue admin scripts
	 * @return void
	 */
	public function enqueue_admin_scripts() {
		$screen = get_current_screen();

		$prefix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( $screen->id == 'woocommerce_page_wc-status' && isset( $_GET['tab'] ) && $_GET['tab'] == 'tools' ) {
			wp_enqueue_style( 'ld-wc-tools', LEARNDASH_WOOCOMMERCE_PLUGIN_URL . 'assets/css/tools' . $prefix . '.css', array(), LEARNDASH_WOOCOMMERCE_VERSION );

			wp_enqueue_script( 'ld-wc-tools', LEARNDASH_WOOCOMMERCE_PLUGIN_URL . 'assets/js/tools' . $prefix . '.js', array( 'jquery' ), LEARNDASH_WOOCOMMERCE_VERSION, true );

			wp_localize_script( 'ld-wc-tools', 'LD_WC_Tools_Params', array(
				'text' => array(
					'status' => __( 'Status', 'learndash-woocommerce' ),
					'complete' => __( 'Complete', 'learndash-woocommerce' ),
					'keep_page_open' => __( 'Please keep this page open until the process is complete.', 'learndash-woocommerce' ),
					'retroactive_button' => __( 'Check LearnDash course access', 'learndash-woocommerce' ),
				),
				'nonce' => array(
					'retroactive_access' => wp_create_nonce( 'ld_wc_retroactive_access' ),
				),
				'wc_version' => WC_VERSION,
			) );
		}
	}

	/**
	 * AJAX hook for processing retroactive tool
	 * @return void
	 */
	public function ajax_retroactive_access() {
		if ( ! isset( $_POST['nonce'] ) ) {
			wp_die();
		}

		if ( ! wp_verify_nonce( $_POST['nonce'], 'ld_wc_retroactive_access' ) ) {
			wp_die();
		}

		$step               = intval( $_POST['step'] );
		$per_batch          = apply_filters( 'learndash_woocommerce_retroactive_tool_per_batch', 10 );
		$offset             = ( $step - 1 ) * $per_batch;
		$order_total        = (array) wp_count_posts( 'shop_order' );
		$order_total        = array_sum( $order_total );
		$subscription_total = (array) wp_count_posts( 'shop_subscription' );
		$subscription_total = array_sum( $subscription_total );
		$total              = $order_total + $subscription_total;

		// Process orders
		$orders = wc_get_orders( array(
			'limit'  => $per_batch,
			'offset' => $offset,
			'order'  => 'ASC',
		) );

		// Foreach orders
		foreach ( $orders as $order ) {
			$status = $order->get_status();
			$id     = $order->get_id();

			// skip order that is part of subscription
			if ( function_exists( 'wcs_order_contains_subscription' ) ) {
				// Workaround for WC_Order_Refund because wcs_order_contains_subscription() only accepts WC_Order object or ID
				if ( ( 'refunded' == $status && is_a( $order, 'WC_Order' ) ) || is_a( $order, 'WC_Order_Refund' ) || is_a( $order, OrderRefund::class ) ) {
					Learndash_WooCommerce::remove_course_access( $id );
					continue;
				}

				if (  wcs_order_contains_subscription( $order, 'any' ) ) {
					continue;
				}
			}

			switch ( $status ) {
				case 'completed':
				case 'processing':
					Learndash_WooCommerce::add_course_access( $id );
					break;
				
				case 'pending':
				case 'on-hold':
				case 'cancelled':
				case 'refunded':
				case 'failed':
					Learndash_WooCommerce::remove_course_access( $id );
					break;
			}
		}

		// Process subscriptions only after orders process is complete
		$subscriptions = array();
		if ( empty( $orders ) ) {
			// Process subscriptions
			if ( function_exists( 'wcs_get_subscriptions' ) ) {
				if ( ! empty( $_POST['subscription_step'] ) && ! empty( $_POST['last_order_step'] ) ) {
					$last_order_step   = $_POST['last_order_step'];
					$subscription_step = $_POST['subscription_step'];
				} else {
					$last_order_step   = $step - 1;
					$subscription_step = 1;
				}

				$subscription_offset = ( $subscription_step - 1 ) * $per_batch;

				// Get subscriptions
				$subscriptions = wcs_get_subscriptions( array(
					'subscriptions_per_page' => $per_batch,
					'offset'                 => $subscription_offset,
					'order'                  => 'ASC',
				) );

				foreach ( $subscriptions as $subscription ) {
					$status = $subscription->get_status();
					$id     = $subscription->get_id();

					switch ( $status ) {
						case 'active':
						case 'pending-cancel':
							Learndash_WooCommerce::add_subscription_course_access( $subscription );
							break;
						
						case 'cancelled':
						case 'on-hold':
							Learndash_WooCommerce::remove_subscription_course_access( $subscription );
							break;	

						case 'expired':
							if ( 'no' === get_option( 'learndash_woocommerce_disable_access_removal_on_expiration', 'no' ) ) {
								Learndash_WooCommerce::remove_subscription_course_access( $subscription );
							} else {
								Learndash_WooCommerce::add_subscription_course_access( $subscription );
							}
							break;
					}
				}
			}	
		}

		if ( ! empty( $orders ) || ! empty( $subscriptions ) ) {
			$last_order_step    = isset( $last_order_step ) && ! empty( $last_order_step ) ? $last_order_step : 0;
			$subscription_step    = isset( $subscription_step ) && ! empty( $subscription_step ) ? $subscription_step : 0;
			
			$order_offset        = empty( $last_order_step ) ? ( $step - 1 ) * $per_batch : ( $last_order_step - 1 ) * $per_batch;
			$subscription_offset = ! empty( $subscription_step ) ? ( $subscription_step - 1 ) * $per_batch : 0;
			$offset              = $order_offset + $subscription_offset;

			$percentage 		= number_format( ( ( $offset + $per_batch ) / $total ) * 100, 0 );
			$percentage			= $percentage > 100 ? 100 : $percentage;

			$returned_subscription_step = $subscription_step > 0 ? $subscription_step + 1 : 0;

			$return = array(
				'step'              => intval( $step + 1 ),
				'last_order_step'   => intval( $last_order_step ),
				'subscription_step' => intval( $returned_subscription_step ),
				'percentage'        => intval( $percentage ),
			);
		} else {
			// done
			$return = array(
				'step' => 'complete',
			);
		}

		echo json_encode( $return );

		wp_die();
	}

	/**
	 * Get the latest option
	 * 
	 * @return array Addon settings
	 */
	public function get_options() {
		wp_cache_delete( 'learndash_woocommerce_settings', 'options' );
		$options = get_option( 'learndash_woocommerce_settings', array() );

		return $options;
	}

	/**
	 * Add tools button for LD WooCommerce
	 * 
	 * @param  array  $tools Existing tools
	 * @return array         New tools
	 */
	public function course_retroactive_access_tool( $tools ) {
		$tools['learndash_retroactive_access'] = array(
			'name' => __( 'LearnDash retroactive course access', 'learndash-woocommerce' ),
			'button' => __( 'Check LearnDash course access', 'learndash-woocommerce' ),
			'desc' => __( 'Check LearnDash course access of WooCommerce integration. Enroll and unenroll users according to WooCommerce purchase/subscription data.', 'learndash-woocommerce' ),
			// 'callback' => array( $this, 'execute_course_retroactive_access' ),
			'callback' => function() {}
		);

		return $tools;
	}
}

new Learndash_WooCommerce_Tools();