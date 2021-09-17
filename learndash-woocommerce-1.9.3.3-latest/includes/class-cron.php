<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
* Cron class
*/
class Learndash_WooCommerce_Cron {
	
	/**
	 * Hook functions
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array( $this, 'add_cron_schedule' ) );
		add_action( 'admin_init', array( $this, 'register_cron' ) );
		register_deactivation_hook( LEARNDASH_WOOCOMMERCE_FILE, array( $this, 'deregister_hook' ) );
	}

	/**
	 * Add cron schedule
	 * 
	 * @param array $schedules Cron schedules
	 */
	public function add_cron_schedule( $schedules ) {
		$schedules['per_minute'] = array(
			'interval' => MINUTE_IN_SECONDS,
			'display'  => __( 'Once per Minute', 'learndash-woocommerce' ),
		);

		return $schedules;
	}

	/**
	 * Register cron hook
	 */
	public function register_cron() {
		if ( ! wp_next_scheduled( 'learndash_woocommerce_cron' ) ) {
			wp_schedule_event( time(), 'per_minute', 'learndash_woocommerce_cron' );
		}
	}

	public function deregister_hook() {
		wp_clear_scheduled_hook( 'learndash_woocommerce_cron' );
	}
}

new Learndash_WooCommerce_Cron();