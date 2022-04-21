<?php
/**
 * Plugin Name: LearnDash LMS - WooCommerce Integration
 * Plugin URI: http://www.learndash.com/work/woocommerce/
 * Description: LearnDash LMS addon plugin to integrate LearnDash LMS with WooCommerce.
 * Version: 1.9.4.1
 * Author: LearnDash
 * Author URI: http://www.learndash.com
 * Domain Path: /languages/
 * Text Domain: learndash-woocommerce
 * WC requires at least: 3.0.0
 * WC tested up to: 5.6.0
 */

class Learndash_WooCommerce {
	public $debug = false;

	public function __construct() {
		self::setup_constants();

		self::check_dependency();

		add_action( 'plugins_loaded', function() {
		    if ( LearnDash_Dependency_Check_LD_WooCommerce::get_instance()->check_dependency_results() ) {
		        self::includes();
		        self::hooks();
		    }
		} );
	}

	public static function hooks()
	{
		// Setup translation
		add_action( 'plugins_loaded', array( __CLASS__, 'load_translation' ) );

		// Meta box
		add_filter( 'product_type_selector', array( __CLASS__, 'add_product_type' ), 10, 1 );
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'render_course_selector' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ), 1 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'deregister_admin_scripts' ), 20 );
		add_action( 'save_post', array( __CLASS__, 'store_related_courses' ), 10, 2 );

		// Product variation hooks
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'render_variation_course_selector' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'store_variation_related_courses' ), 10, 2 );

		// Order hook
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'add_course_access' ), 10, 1 );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'add_course_access' ), 10, 1 );
		add_action( 'woocommerce_payment_complete', array( __CLASS__, 'add_course_access' ), 10, 1 );
		add_action( 'woocommerce_order_status_refunded', array( __CLASS__, 'remove_course_access' ), 10, 1 );
		add_action( 'woocommerce_order_status_cancelled', array( __CLASS__, 'remove_course_access' ), 10, 1 );
		add_action( 'woocommerce_order_status_failed', array( __CLASS__, 'remove_course_access' ), 10, 1 );

		add_action( 'wp_trash_post', [ __CLASS__, 'delete_order' ], 10, 1 );
		add_action( 'before_delete_post', [ __CLASS__, 'delete_order' ], 10, 1 );
		add_action( 'untrashed_post', [ __CLASS__, 'restore_order' ], 30, 1 );

		add_action( 'woocommerce_process_shop_order_meta', [ __CLASS__, 'update_order_meta' ] );
		add_action( 'woocommerce_before_subscription_object_save', [ __CLASS__, 'update_subscription_meta' ] );

		add_action( 'woocommerce_new_order_item', [ __CLASS__, 'process_new_order_item' ], 10, 3 );

		add_filter( 'woocommerce_order_get_items', array( __CLASS__, 'filter_subscription_products_out' ), 10, 3 );

		// New hooks for WC subscription
		add_action( 'woocommerce_subscription_status_cancelled', array( __CLASS__, 'remove_subscription_course_access' ) );
		// add_action( 'woocommerce_subscription_status_on-hold', array( __CLASS__, 'remove_subscription_course_access' ) );
		
		if ( 'no' === get_option( 'learndash_woocommerce_disable_access_removal_on_expiration', 'no' ) ) {
			add_action( 'woocommerce_subscription_status_expired', array( __CLASS__, 'remove_subscription_course_access' ) );
		}

		add_action( 'woocommerce_subscription_status_active', array( __CLASS__, 'add_subscription_course_access' ) );

		add_filter( 'woocommerce_subscription_settings', array( __CLASS__, 'add_subscription_settings' ), 20, 1 );

		// add_filter( 'ld_woocommerce_remove_subscription_course_access', array( __CLASS__, 'check_subscription_course_access_removal' ), 10, 3 );
		add_filter( 'ld_woocommerce_add_subscription_course_access', array( __CLASS__, 'check_subscription_course_access_addition' ), 10, 3 );

		add_action( 'woocommerce_subscription_renewal_payment_complete', array( __CLASS__, 'remove_course_access_on_billing_cycle_completion' ), 10, 2 );

		// WC Subscription switcher
		add_action( 'woocommerce_subscription_checkout_switch_order_processed', array( __CLASS__, 'switch_subscription' ), 10, 2 );

		// Silent background course enrollment process
		add_action( 'learndash_woocommerce_cron', array( __CLASS__, 'process_silent_course_enrollment' ) );

		// Force user to log in or create account if there is LD course in WC cart
		add_action( 'woocommerce_checkout_init', array( __CLASS__, 'enable_signup_on_checkout' ), 10, 1 );
		add_action( 'woocommerce_after_checkout_validation', [ __CLASS__, 'validate_checkout' ], 10, 2 );

		// Auto complete course transaction
		add_action( 'woocommerce_payment_complete', array( __CLASS__, 'auto_complete_transaction' ) );
		add_action( 'woocommerce_thankyou', array( __CLASS__, 'auto_complete_transaction' ) );

		// Remove course increment record if a course unenrolled manually
		add_action( 'learndash_delete_user_data', array( __CLASS__, 'remove_access_increment_count' ) );
	}

	public static function setup_constants() {
		if ( ! defined( 'LEARNDASH_WOOCOMMERCE_VERSION' ) ) {
			define( 'LEARNDASH_WOOCOMMERCE_VERSION', '1.9.4.1' );
		}

		// Plugin file
		if ( ! defined( 'LEARNDASH_WOOCOMMERCE_FILE' ) ) {
			define( 'LEARNDASH_WOOCOMMERCE_FILE', __FILE__ );
		}		

		// Plugin folder path
		if ( ! defined( 'LEARNDASH_WOOCOMMERCE_PLUGIN_PATH' ) ) {
			define( 'LEARNDASH_WOOCOMMERCE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
		}

		// Plugin folder URL
		if ( ! defined( 'LEARNDASH_WOOCOMMERCE_PLUGIN_URL' ) ) {
			define( 'LEARNDASH_WOOCOMMERCE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	/**
	 * Check and set dependencies
	 * 
	 * @return void
	 */
	public static function check_dependency()
	{
	    include LEARNDASH_WOOCOMMERCE_PLUGIN_PATH . 'includes/class-dependency-check.php';

	    LearnDash_Dependency_Check_LD_WooCommerce::get_instance()->set_dependencies(
	        array(
	            'sfwd-lms/sfwd_lms.php' => array(
	                'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
	                'class'       => 'SFWD_LMS',
	                'min_version' => '3.0.0',
	            ),
	            'woocommerce/woocommerce.php' => array(
	                'label'       => '<a href="https://woocommerce.com/">WooCommerce</a>',
	                'class'       => 'WooCommerce',
	                'min_version' => '4.5.0',
	            ),
	        )
	    );

	    LearnDash_Dependency_Check_LD_WooCommerce::get_instance()->set_message(
	        __( 'LearnDash LMS - WooCommerce Integration Add-on requires the following plugin(s) to be active:', 'learndash-woocommerce' )
	    );
	}

	public static function includes() {
		include LEARNDASH_WOOCOMMERCE_PLUGIN_PATH . 'includes/class-upgrade.php';
		include LEARNDASH_WOOCOMMERCE_PLUGIN_PATH . 'includes/class-cron.php';
		include LEARNDASH_WOOCOMMERCE_PLUGIN_PATH . 'includes/class-tools.php';
	}

	public static function load_translation()
	{
		global $wp_version;
		// Set filter for plugin language directory
		$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$lang_dir = apply_filters( 'ld_woocommerce_languages_directory', $lang_dir );

		$get_locale = get_locale();

		if ( $wp_version >= '4.7' ) {
			$get_locale = get_user_locale();
		}

		$mofile = sprintf( '%s-%s.mo', 'learndash-woocommerce', $get_locale );
		$mofile = WP_LANG_DIR . 'plugins/' . $mofile;

		if ( file_exists( $mofile ) ) {			
			load_textdomain( 'learndash-woocommerce', $mofile );
		} else {
			load_plugin_textdomain( 'learndash-woocommerce', $deprecated = false, $lang_dir );
		}

		// include translations/update class
		include LEARNDASH_WOOCOMMERCE_PLUGIN_PATH . 'includes/class-translations-ld-woocommerce.php';
	}

	public static function add_product_type( $types ) {
		$types['course'] = learndash_get_custom_label( 'course' );
		return $types;
	}

	public static function enqueue_admin_scripts() {
		$screen = get_current_screen();

		if ( $screen->id == 'product' && $screen->base == 'post' ) {
			wp_enqueue_style( 'learndash-woocommerce-select2', LEARNDASH_WOOCOMMERCE_PLUGIN_URL . 'lib/select2/select2.min.css', [], '4.0.13', 'screen' );
			wp_enqueue_script( 'learndash-woocommerce-select2', LEARNDASH_WOOCOMMERCE_PLUGIN_URL . 'lib/select2/select2.min.js', [ 'jquery' ], '4.0.13', false );

			wp_enqueue_style( 'learndash-woocommerce-product', LEARNDASH_WOOCOMMERCE_PLUGIN_URL . 'assets/css/product.min.css', [], LEARNDASH_WOOCOMMERCE_VERSION, 'screen' );
			wp_enqueue_script( 'learndash-woocommerce-product', LEARNDASH_WOOCOMMERCE_PLUGIN_URL . 'assets/js/product.min.js', [ 'jquery' ], LEARNDASH_WOOCOMMERCE_VERSION, true );
		}
	}

	public static function deregister_admin_scripts()
	{
		$screen = get_current_screen();

		if ( $screen->id == 'product' && $screen->base == 'post' ) {
			wp_deregister_script( 'learndash-select2-jquery-script' );
			wp_deregister_style( 'learndash-select2-jquery-style' );
		}
	}

	public static function add_front_scripts() {
		wp_enqueue_script( 'ld_wc_front', plugins_url( '/front.js', __FILE__ ), array( 'jquery' ), LEARNDASH_WOOCOMMERCE_VERSION );
	}

	public static function render_course_selector() {
		global $post;

		$courses_options = self::list_courses();
		$groups_options  = wp_list_pluck( learndash_get_groups(), 'post_title', 'ID' );

		/**
		 * Filter for course selector class names
		 *
		 * @param string   	   Default class names
		 * @param object $post WP_Post object
		 * @var string New modified class names
		 */
		$class = apply_filters( 'learndash_woocommerce_course_selector_class', 'options_group show_if_course show_if_simple', $post );
		
		echo '<div class="' . $class . '">';

		wp_nonce_field( 'save_post', 'ld_wc_nonce' );

		$values = (array) get_post_meta( $post->ID, '_related_course', true );
		if ( ! $values ) {
			$values = array();
		}

		$groups_values = (array) get_post_meta( $post->ID, '_related_group', true );
		if ( ! $groups_values ) {
			$groups_values = array();
		}

		?>
		<p><?php printf( __( '<strong>Important:</strong> When customers checkout with 5 or more associated courses and groups in a single cart, course enrollment process is done in the background and you will need to set up a cron job. To set up a cron job please follow <a href="%s" target="_blank">these steps</a>.', 'learndash-woocommerce' ), 'https://www.learndash.com/support/docs/faqs/email-notifications-send-time/#create-cron-job-in-cpanel' ); ?></p>
		<?php

		self::woocommerce_wp_select_multiple( array(
			'id'          => '_related_course[]',
			'class'		  => 'select2 regular-width select short ld_related_courses',
			'label'       => sprintf( _x( 'LearnDash %s', 'LearnDash Courses', 'learndash-woocommerce' ), learndash_get_custom_label( 'courses' ) ),
			'options'     => $courses_options,
			'desc_tip'    => true,
			'value' => $values,
		) );

		self::woocommerce_wp_select_multiple( array(
			'id'          => '_related_group[]',
			'class'		  => 'select2 regular-width select short ld_related_groups',
			'label'       => sprintf( _x( 'LearnDash %s', 'LearnDash Groups', 'learndash-woocommerce' ), learndash_get_custom_label( 'groups' ) ),
			'options'     => $groups_options,
			'desc_tip'    => true,
			'value' => $groups_values,
		) );

		echo '</div>';
	}

	public static function store_related_courses( $id, $post ) {
		if ( ! isset( $_POST['ld_wc_nonce'] ) || ! wp_verify_nonce( $_POST['ld_wc_nonce'], 'save_post' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( ! $post->post_type === 'product' ) {
			return;
		}

		// Delete the meta and bail if product is variable type
		$product = wc_get_product( $id );
		if ( false !== $product ) {
			if ( in_array( $product->get_type(), [ 'variable', 'variable-subscription' ] ) ) {
				delete_post_meta( $id, '_related_course' );
				delete_post_meta( $id, '_related_group' );
	
				return;
			}
		}

		if ( isset( $_POST['_related_course'] ) && ! empty( $_POST['_related_course'] ) ) {
			$related_courses = array_map( 'intval', $_POST['_related_course'] );
			update_post_meta( $id, '_related_course', $related_courses );
		} else {
			delete_post_meta( $id, '_related_course' );
		}

		if ( isset( $_POST['_related_group'] ) && ! empty( $_POST['_related_group'] ) ) {
			$related_groups = array_map( 'intval', $_POST['_related_group'] );
			update_post_meta( $id, '_related_group', $related_groups );
		} else {
			delete_post_meta( $id, '_related_group' );
		}
	}

	public static function render_variation_course_selector( $loop, $data, $variation )
	{
		$courses_options = self::list_courses();
		$groups_options  = wp_list_pluck( learndash_get_groups(), 'post_title', 'ID' );
		
		echo '<div class="form-field form-row form-row-full">';

		wp_nonce_field( 'save_post', 'ld_wc_nonce' );

		$values = (array) get_post_meta( $variation->ID, '_related_course', true );
		if ( ! $values ) {
			$values = array();
		}

		$groups_values = (array) get_post_meta( $variation->ID, '_related_group', true );
		if ( ! $groups_values ) {
			$groups_values = array();
		}

		?>
		<p><?php printf( __( '<strong>Important:</strong> When customers checkout with 5 or more associated courses and groups in a single cart, course enrollment process is done in the background and you will need to set up a cron job. To set up a cron job please follow <a href="%s" target="_blank">these steps</a>.', 'learndash-woocommerce' ), 'https://www.learndash.com/support/docs/faqs/email-notifications-send-time/#create-cron-job-in-cpanel' ); ?></p>
		<?php

		self::woocommerce_wp_select_multiple( array(
			'id'          => '_related_course['. $loop . '][]',
			'class'		  => 'select2 full-width select short ld_related_courses_variation',
			'label'       => sprintf( _x( 'LearnDash %s', 'LearnDash Courses', 'learndash-woocommerce' ), learndash_get_custom_label( 'courses' ) ),
			'options'     => $courses_options,
			'desc_tip'    => true,
			'value' => $values,
		) );

		self::woocommerce_wp_select_multiple( array(
			'id'          => '_related_group['. $loop . '][]',
			'class'		  => 'select2 full-width select short ld_related_groups_variation',
			'label'       => sprintf( _x( 'LearnDash %s', 'LearnDash Groups', 'learndash-woocommerce' ), learndash_get_custom_label( 'groups' ) ),
			'options'     => $groups_options,
			'desc_tip'    => true,
			'value' => $groups_values,
		) );

		echo '</div>';
	}

	public static function store_variation_related_courses( $variation_id, $loop )
	{
		if ( ! isset( $_POST['ld_wc_nonce'] ) || ! wp_verify_nonce( $_POST['ld_wc_nonce'], 'save_post' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( ! $post->post_type === 'product' ) {
			return;
		}

		if ( isset( $_POST['_related_course'] ) && ! empty( $_POST['_related_course'] ) ) {
			$related_courses = array();
			foreach ( $_POST['_related_course'] as $key => $value ) {
				if ( isset( $value ) && ! empty( $value ) ) {
					$related_courses[ $key ] = array_map( 'intval', $value );
				} else {
					$related_courses[ $key ] = array();
				}

				update_post_meta( $variation_id, '_related_course', $related_courses[ $loop ] );
			}
		} else {
			delete_post_meta( $variation_id, '_related_course' );
		}

		if ( isset( $_POST['_related_group'] ) && ! empty( $_POST['_related_group'] ) ) {
			$related_groups = array();
			foreach ( $_POST['_related_group'] as $key => $value ) {
				if ( isset( $value ) && ! empty( $value ) ) {
					$related_groups[ $key ] = array_map( 'intval', $value );
				} else {
					$related_groups[ $key ] = array();
				}

				update_post_meta( $variation_id, '_related_group', $related_groups[ $loop ] );
			}
		} else {
			delete_post_meta( $variation_id, '_related_group' );
		}
	}

	/**
	 * Remove course when order is refunded
	 * @param  int    $order_id    Order ID
	 * @param  int    $customer_id Customer ID (optional)
	 */
	public static function remove_course_access( $order_id, $customer_id = null )
	{
		$order = wc_get_order( $order_id );

		if ( $order !== false && is_a( $order, 'WC_Order' ) ) {
			/**
			 * Only get items for non-subscription products
			 *
			 * The $learndash_woocommerce_get_items_filter_out_subscriptions variable is required to be "true" for the filter to work
			 *
			 * @see Learndash_WooCommerce::filter_subscription_products_out() Filter subscription products out when getting items for course access update
			 * @var array
			 */
			global $learndash_woocommerce_get_items_filter_out_subscriptions;
			$learndash_woocommerce_get_items_filter_out_subscriptions = true;
			$products = $order->get_items();

			$customer_id = ! empty( $customer_id ) && is_numeric( $customer_id ) ? $customer_id : $order->get_user_id();

			foreach ( $products as $product ) {
				if ( ! empty( $product->get_variation_id() ) ) {
					$courses_id = (array) get_post_meta( $product->get_variation_id(), '_related_course', true );
					$groups_id  = (array) get_post_meta( $product->get_variation_id(), '_related_group', true );
				} else {
					$courses_id = (array) get_post_meta( $product['product_id'], '_related_course', true );
					$groups_id = (array) get_post_meta( $product['product_id'], '_related_group', true );
				}

				if ( $courses_id && is_array( $courses_id ) ) {
					foreach ( $courses_id as $course_id ) {
						self::update_remove_course_access( $course_id, $customer_id, $order_id );
					}
				}

				if ( $groups_id && is_array( $groups_id ) ) {
					foreach ( $groups_id as $group_id ) {
						self::update_remove_group_access( $group_id, $customer_id, $order_id );
					}
				}
			}
		}
	}

	/**
	 * Enroll customer into order's associated courses and groups
	 *
	 * @param int $order_id		WC_Order ID
	 * @param int $customer_id	Customer ID (optional)
	 * @return void
	 */
	public static function add_course_access( $order_id, $customer_id = null ) 
	{
		$order = wc_get_order( $order_id );

		if ( $order !== false && is_a( $order, 'WC_Order' ) ) {
			/**
			 * Only get items for non-subscription products
			 *
			 * @see Learndash_WooCommerce::filter_subscription_products_out() Filter subscription products out when getting items for course access update
			 * @var array
			 */
			global $learndash_woocommerce_get_items_filter_out_subscriptions;
			$learndash_woocommerce_get_items_filter_out_subscriptions = true;
			$products = $order->get_items();

			$customer_id = ! empty( $customer_id ) && is_numeric( $customer_id ) ? $customer_id : $order->get_user_id();

			$courses_count = 0;
			$groups_count  = 0;
			array_walk( $products, function( $product ) use ( &$courses_count, &$groups_count ) {
				if ( ! empty( $product->get_variation_id() ) ) {
					$courses = (array) get_post_meta( $product->get_variation_id(), '_related_course', true );
					$groups  = (array) get_post_meta( $product->get_variation_id(), '_related_group', true );
				} else {
					$courses = (array) get_post_meta( $product['product_id'], '_related_course', true );
					$groups  = (array) get_post_meta( $product['product_id'], '_related_group', true );
				}

				$courses_count += count( $courses );
				$groups_count  += count( $groups );
			} );

			if ( ( $courses_count + $groups_count ) >= self::get_products_count_for_silent_course_enrollment() && current_filter() !== 'learndash_woocommerce_cron' && current_filter() !== 'wp_ajax_ld_wc_retroactive_access' ) {
				self::enqueue_silent_course_enrollment( array( 'order_id' => $order_id ) );
			} else {
				foreach ( $products as $product ) {
					if ( ! empty( $product->get_variation_id() ) ) {
						$courses_id = (array) get_post_meta( $product->get_variation_id(), '_related_course', true );
						$groups_id  = (array) get_post_meta( $product->get_variation_id(), '_related_group', true );
					} else {
						$courses_id = (array) get_post_meta( $product['product_id'], '_related_course', true );
						$groups_id  = (array) get_post_meta( $product['product_id'], '_related_group', true );
					}

					if ( $courses_id && is_array( $courses_id ) ) {
						foreach ( $courses_id as $course_id ) {
							self::update_add_course_access( $course_id, $customer_id, $order_id );
						}
					}

					if ( $groups_id && is_array( $groups_id ) ) {
						foreach ( $groups_id as $group_id ) {
							self::update_add_group_access( $group_id, $customer_id, $order_id );
						}
					}
				}
			}
		}
	}

	/**
	 * Handler when an order is deleted, hooked to 
	 * wp_trash_post and before_delete_post
	 *
	 * @param int $post_id
	 * @return void
	 */
	public static function delete_order( $post_id )
	{
		$post_type = get_post_type( $post_id );

		if ( 'shop_order' == $post_type ) {
			self::remove_course_access( $post_id );
		} elseif ( 'shop_subscription' == $post_type ) {
			if ( function_exists( 'wcs_get_subscription' ) ) {
				$subscription = wcs_get_subscription( $post_id );

				if ( $subscription ) {
					self::remove_subscription_course_access( $subscription );
				}
			}

		}
	}

	/**
	 * Handler when an order is restored/untrashed, hooked to
	 * untrash_post
	 *
	 * @param int $post_id
	 * @return void
	 */
	public static function restore_order( $post_id )
	{
		$post_type = get_post_type( $post_id );

		if ( 'shop_order' == $post_type ) {
			$order = wc_get_order( $post_id );

			if ( in_array( $order->get_status(), [ 'processing', 'completed' ] ) ) {
				self::add_course_access( $post_id );
			}
		} elseif ( 'shop_subscription' == $post_type ) {
			if ( function_exists( 'wcs_get_subscription' ) ) {
				$subscription = wcs_get_subscription( $post_id );
	
				if ( in_array( $subscription->get_status(), [ 'active' ] ) ) {
					self::add_subscription_course_access( $subscription );
				}
			}
		}
	}

	/**
	 * Handler when an order is updated, hooked to
	 * woocommerce_process_shop_order_meta
	 * 
	 * This method is used to handle course access update
	 * in some scenarios such as:
	 * 1. Order's customer change
	 *
	 * @param int $order_id
	 * @return void
	 */
	public static function update_order_meta( $order_id )
	{
		$order = wc_get_order( $order_id );

		$old_customer = $order->get_customer_id();
		$new_customer = intval( $_POST['customer_user'] );

		if ( $old_customer != $new_customer ) {
			if ( in_array( $order->get_status(), [ 'processing', 'completed' ] ) ) {
				self::remove_course_access( $order_id, $old_customer );
				self::add_course_access( $order_id, $new_customer );
			}
		}
	}

	/**
	 * Handler when an subscription is updated, hooked to
	 * woocommerce_process_shop_subscription_meta
	 * 
	 * This method is used to handle course access update
	 * in some scenarios such as:
	 * 1. Order's customer change
	 *
	 * @param int $subscription_id
	 * @return void
	 */
	public static function update_subscription_meta( $subscription_id )
	{
		$subscription = wcs_get_subscription( $subscription_id );

		$old_customer = $subscription->get_customer_id();
		$new_customer = intval( $_POST['customer_user'] );

		if ( $old_customer != $new_customer ) {
			if ( in_array( $subscription->get_status(), [ 'active' ] ) ) {
				self::remove_subscription_course_access( $subscription, [], $old_customer );
				
				self::add_subscription_course_access( $subscription, [], $new_customer );
			}
		}
	}

	/**
	 * Process new order item added to existing order
	 */
	public static function process_new_order_item( $item_id, $item, $order_id )
	{
		$order = wc_get_order( $order_id );

		if ( ! $order || ! is_a( $order, 'WC_Order' ) )  {
			return;
		}

		if ( in_array( $order->get_status(), [ 'processing', 'completed' ] ) ) {
			self::add_course_access( $order_id );
		}
	}

	/**
	 * Filter only subscription products out from order processing methods
	 * 
	 * @param  array  $items Original items
	 * @param  array  $order Order object
	 * @param  array  $types Item typs
	 * @return array         Modified $items
	 */
	public static function filter_subscription_products_out( $items, $order, $types ) {
		global $learndash_woocommerce_get_items_filter_out_subscriptions;
		if ( $learndash_woocommerce_get_items_filter_out_subscriptions ) {
			$learndash_woocommerce_get_items_filter_out_subscriptions = false;

			$items = array_filter( $items, function( $item ) {
				$product = $item->get_product();

				if ( $product && is_a( $product, 'WC_Product' ) ) {
					return $product->get_type() != 'subscription';
				} else {
					return true;
				}
			} );
		}

		return $items;
	}

	public static function debug( $msg ) {
		$original_log_errors = ini_get( 'log_errors' );
		$original_error_log  = ini_get( 'error_log' );
		ini_set( 'log_errors', true );
		ini_set( 'error_log', dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'debug.log' );

		global $ld_sf_processing_id;
		if ( empty( $ld_sf_processing_id ) ) {
			$ld_sf_processing_id = time();
		}

		if ( isset( $_GET['debug'] ) || self::debug ) {
			error_log( "[$ld_sf_processing_id] " . print_r( $msg, true ) );
		} //Comment This line to stop logging debug messages.

		ini_set( 'log_errors', $original_log_errors );
		ini_set( 'error_log', $original_error_log );
	}

	public static function list_courses() {
		global $post;
		$postid = $post->ID;
		query_posts( array( 'post_type' => 'sfwd-courses', 'posts_per_page' => - 1, 'suppress_filters' => true ) );
		$courses = array();
		while ( have_posts() ) {
			the_post();
			$courses[ get_the_ID() ] = get_the_title();
		}
		wp_reset_query();
		$post = get_post( $postid );

		return $courses;
	}

	/**
	 * Handle subscription status change to remove LD course access
	 * @param object 			$subscription 	WC_Subscription object
	 * @param WC_Order_Item[] 	$products 		WC order items
	 * @param int 				$customer_id	Customer ID (optional)
	 * @return void
	 */
	public static function remove_subscription_course_access( $subscription, $products = [], $customer_id = null )
	{
		if ( ! apply_filters( 'ld_woocommerce_remove_subscription_course_access', true, $subscription, current_filter() ) ) {
			return;
		}

		if ( empty( $products ) ) {
			// Get products related to this order
			$products = $subscription->get_items();
		}

		$customer_id = ! empty( $customer_id ) && is_numeric( $customer_id ) ? $customer_id : $subscription->get_user_id();

		foreach ( $products as $product ) {
			if ( ! empty( $product->get_variation_id() ) ) {
				$courses_id = (array) get_post_meta( $product->get_variation_id(), '_related_course', true );
				$groups_id  = (array) get_post_meta( $product->get_variation_id(), '_related_group', true );
			} else {
				$courses_id = (array) get_post_meta( $product['product_id'], '_related_course', true );
				$groups_id  = (array) get_post_meta( $product['product_id'], '_related_group', true );
			}

			if ( $courses_id && is_array( $courses_id ) ) {
				foreach ( $courses_id as $course_id ) {
					self::update_remove_course_access( $course_id, $customer_id, $subscription->get_id() );

					foreach ( $subscription->get_related_orders() as $order_id ) {
						self::update_remove_course_access( $course_id, $customer_id, $order_id );
					}
				}
			}

			if ( $groups_id && is_array( $groups_id ) ) {
				foreach ( $groups_id as $group_id ) {
					self::update_remove_group_access( $group_id, $customer_id, $subscription->get_id() );

					foreach ( $subscription->get_related_orders() as $order_id ) {
						self::update_remove_group_access( $group_id, $customer_id, $order_id );
					}
				}
			}
		}
	}

	/**
	 * Handle subscription status change to add LD course access
	 * @param object 			$subscription 	WC_Subscription object
	 * @param WC_Order_Item[] 	$products 		WC order items
	 * @param int 				$customer_id 	WC Customer ID (optional)
	 * @return void
	 */
	public static function add_subscription_course_access( $subscription, $products = [], $customer_id = null )
	{
		if ( false === $subscription || ! is_a( $subscription, 'WC_Subscription' ) ) {
			return;
		}

		if ( ! apply_filters( 'ld_woocommerce_add_subscription_course_access', true, $subscription, current_filter() ) ) {
			return;
		}

		if ( empty( $products ) ) {
			// Get products related to this order
			$products = $subscription->get_items();
		}

		$customer_id = ! empty( $customer_id ) && is_numeric( $customer_id ) ? $customer_id : $subscription->get_user_id();

		$start_date  = $subscription->get_date( 'date_created' );

		$courses_count = 0;
		$groups_count  = 0;
		array_walk( $products, function( $product ) use ( &$courses_count, &$groups_count ) {
			if ( ! empty( $product->get_variation_id() ) ) {
				$courses = (array) get_post_meta( $product->get_variation_id(), '_related_course', true );	
				$groups  = (array) get_post_meta( $product->get_variation_id(), '_related_group', true );	
			} else {
				$courses = (array) get_post_meta( $product['product_id'], '_related_course', true );
				$groups = (array) get_post_meta( $product['product_id'], '_related_group', true );
			}

			$courses_count += count( $courses );
			$groups_count  += count( $groups );
		} );

		if ( ( $courses_count + $groups_count ) >= self::get_products_count_for_silent_course_enrollment() && current_filter() !== 'learndash_woocommerce_cron' && current_filter() !== 'wp_ajax_ld_wc_retroactive_access' ) {
			self::enqueue_silent_course_enrollment( array( 'subscription_id' => $subscription->get_id() ) );
		} else {
			foreach ( $products as $product ) {
				if ( ! empty( $product->get_variation_id() ) ) {
					$courses_id = (array) get_post_meta( $product->get_variation_id(), '_related_course', true );
					$groups_id  = (array) get_post_meta( $product->get_variation_id(), '_related_group', true );
				} else {
					$courses_id = (array) get_post_meta( $product['product_id'], '_related_course', true );
					$groups_id  = (array) get_post_meta( $product['product_id'], '_related_group', true );
				}

				// Update access to the courses
				if ( $courses_id && is_array( $courses_id ) ) {
					foreach ( $courses_id as $course_id ) {
						self::update_add_course_access( $course_id, $customer_id, $subscription->get_id() );

						// Replace start date to keep the drip feeding working
						if ( apply_filters( 'learndash_woocommerce_reset_subscription_course_access_from', true, $course_id, $subscription ) ) {
							update_user_meta( $customer_id, 'course_' . $course_id . '_access_from', strtotime( $start_date ) );
						}
					}
				}

				if ( $groups_id && is_array( $groups_id ) ) {
					foreach ( $groups_id as $group_id ) {
						self::update_add_group_access( $group_id, $customer_id, $subscription->get_id() );

						// Replace start date to keep the drip feeding working
						if ( apply_filters( 'learndash_woocommerce_reset_subscription_group_access_from', true, $group_id, $subscription ) ) {
							update_user_meta( $customer_id, 'learndash_group_enrolled_' . $group_id, strtotime( $start_date ) );
						}
					}
				}
			}
		}			
	}

	/**
	 * Add subscription settings related to LearnDash
	 *
	 * @param array $settings Original settings array
	 */
	public static function add_subscription_settings( $settings )
	{
		return array_merge( $settings, [
			array(
				'name'          => 'LearnDash',
				'type'          => 'title',
				'desc'          => __( 'WooCommerce subscription settings related to LearnDash.', 'learndash-woocommerce' ),
				'id'            => 'learndash_woocommerce_section',
			),
			array(
				'name'          => __( 'Access Removal on Expiration', 'learndash-woocommerce' ),
				'desc'          => __( 'Disable', 'learndash-woocommerce' ),
				'desc_tip'		=> __( 'Check the box to disable course access removal on subscription expiration. By default, course access will be revoked when a subscription expires.', 'learndash-woocommerce' ),
				'id'            => 'learndash_woocommerce_disable_access_removal_on_expiration',
				'css'           => '',
				'type'          => 'checkbox',
				'default'		=> 'no',
			),
			array( 'type' => 'sectionend', 'id' => 'learndash_woocommerce_section' ),
		] );
	}

	/**
	 * Filter hook function to check subscription course access removal
	 * @param  bool   $remove       True to remove|false otherwise
	 * @param  object $subscription WC_Subscription object
	 * @param  string $filter       Current filter hook
	 * @return bool                 Returned $remove value
	 */
	public static function check_subscription_course_access_removal( $remove, $subscription, $filter )
	{
		$backtrace = wp_debug_backtrace_summary();

		if ( 
			$filter === 'woocommerce_subscription_status_on-hold' 
			&& (
				strpos( $backtrace, 'process_renewal' ) !== false 
				|| strpos( $backtrace, 'Renewal_Order' ) !== false 
			) 
		) {
			return false;
		}

		return $remove;
	}

	/**
	 * Filter hook function to check subscription course access addition
	 * @param  bool   $add          True to remove|false otherwise
	 * @param  object $subscription WC_Subscription object
	 * @param  string $filter       Current filter hook
	 * @return bool                 Returned $add value
	 */
	public static function check_subscription_course_access_addition( $add, $subscription, $filter )
	{
		$backtrace = wp_debug_backtrace_summary();

		if ( 
			$filter === 'woocommerce_subscription_status_active' 
			&& (
				strpos( $backtrace, 'process_renewal' ) !== false 
				|| strpos( $backtrace, 'Renewal_Order' ) !== false 
			) 
		) {
			return false;
		}

		return $add;
	}

	/**
	 * Remove course access when user completes billing cycle
	 * 
	 * @param  object $subscription WC_Subscription object
	 * @param  array  $last_order   Last order details
	 */
	public static function remove_course_access_on_billing_cycle_completion( $subscription, $last_order )
	{
		if ( self::is_course_access_removed_on_subscription_billing_cycle_completion( $subscription ) ) {
			
			$next_payment_date = $subscription->calculate_date( 'next_payment' );

			// Check if there's no next payment date
			// See calculate_date() in class-wc-subscriptions.php
			if ( 0 == $next_payment_date ) {
				self::remove_subscription_course_access( $subscription );
			}
		}
	}

	/**
	 * Handle course access when subscription switching happens
	 * @param  Automattic\WooCommerce\Admin\Overrides\Order $order WC order object
	 * @param  array    $data  WC order data
	 * @return void
	 */
	public static function switch_subscription( $order, $data )
	{
		foreach ( $data as $subscription_id => $subscription_data ) {
			foreach ( $subscription_data['switches'] as $switch_item_id => $switch_data ) {
				$subscriptions = wcs_get_subscriptions_for_switch_order( $order );

				if ( ! empty( $switch_data['remove_line_item'] ) ) {
					$old_order = wc_get_order( wc_get_order_id_by_order_item_id( $switch_data['remove_line_item'] ) );
					if ( $old_order && is_a( $old_order, 'WC_Order' ) ) {
						foreach ( $subscriptions as $subscription ) {
							self::remove_subscription_course_access( $subscription, $old_order->get_items() );
						}
					}
				}

				if ( ! empty( $switch_data['add_line_item'] ) ) {
					self::add_subscription_course_access( $subscription, $order->get_items() );
				}
			}
		}
	}

	/**
	 * Enqueue course enrollment in database for product with many courses
	 * 
	 * @param  array  $args Order/subscription arg in this format: 
	 *                      array( 'order_id' => $order_id ) OR 
	 *                      array( 'subscription_id' => $subscription_id )
	 * @return void
	 */
	public static function enqueue_silent_course_enrollment( $args ) {
		$queue = get_option( 'learndash_woocommerce_silent_course_enrollment_queue', array() );

		if ( ! empty( $args['order_id'] ) ) {
			$queue[ $args['order_id'] ] = $args;
		} elseif( ! empty( $args['subscription_id'] ) ) {
			$queue[ $args['subscription_id'] ] = $args;
		}

		update_option( 'learndash_woocommerce_silent_course_enrollment_queue', $queue, false );
	}

	/**
	 * Process silent background course enrollment using cron
	 * 
	 * @return void
	 */
	public static function process_silent_course_enrollment() {
		$queue = get_option( 'learndash_woocommerce_silent_course_enrollment_queue', array() );

		$queue_count = apply_filters( 'learndash_woocommerce_process_silent_course_enrollment_queue_count', 1 );

		$processed_queue = array_slice( $queue, 0, $queue_count, true );

		foreach ( $processed_queue as $id => $args ) {
			if ( ! empty( $args['order_id'] ) ) {
				self::add_course_access( $args['order_id'] );
			} elseif ( ! empty( $args['subscription_id'] ) ) {
				self::add_subscription_course_access( wcs_get_subscription( $args['subscription_id'] ) );
			}

			unset( $queue[ $id ] );
		}

		update_option( 'learndash_woocommerce_silent_course_enrollment_queue', $queue, false );
	}

	/**
	 * Force user to login when there is a LD course in cart
	 * 
	 * @param  object $checkout Checkout object
	 */
	public static function enable_signup_on_checkout( $checkout )
	{	
		$wc_cart = WC()->cart;
		if ( is_a( $wc_cart, 'WC_Cart' ) ) {
			$cart_items = $wc_cart->cart_contents;
			foreach ( $cart_items as $key => $item ) {
				$courses = (array) get_post_meta( $item['data']->get_id(), '_related_course', true );
				$courses = maybe_unserialize( $courses );
				
				$groups = (array) get_post_meta( $item['data']->get_id(), '_related_group', true );
				$groups = maybe_unserialize( $groups );
				
				if ( isset( $courses ) && is_array( $courses ) ) {
					foreach ( $courses as $course ) {
						if ( $course != 0 ) {
							self::add_front_scripts();
							break 2;
						}
					}
				}

				if ( isset( $groups ) && is_array( $groups ) ) {
					foreach ( $groups as $group ) {
						if ( $group != 0 ) {
							self::add_front_scripts();
							break 2;
						}
					}
				}
			}

		}
	}

	/**
	 * Validate checkout
	 * 
	 * @param array 	$data 	Checkout data
	 * @param WP_Error 	$errors Validation errors
	 */
	public static function validate_checkout( $data, $errors )
	{
		$wc_cart = WC()->cart;
		if ( is_a( $wc_cart, 'WC_Cart' ) ) {
			$cart_items = $wc_cart->cart_contents;
			$ld_object_found = false;
			foreach ( $cart_items as $key => $item ) {
				$courses = (array) get_post_meta( $item['data']->get_id(), '_related_course', true );
				$courses = maybe_unserialize( $courses );
				
				$groups = (array) get_post_meta( $item['data']->get_id(), '_related_group', true );
				$groups = maybe_unserialize( $groups );

				// Filter out invalid courses and groups first
				$courses = array_filter( $courses, function( $course_id ) {
					$course = get_post( $course_id );

					return $course && $course->post_status == 'publish' && $course->post_type == 'sfwd-courses';
				} );

				$groups = array_filter( $groups, function( $group_id ) {
					$group = get_post( $group_id );

					return $group && $group->post_status == 'publish' && $group->post_type == 'groups';
				} );

				if ( ! empty( $courses ) ) {
					$ld_object_found = true;
				}

				if ( ! empty( $groups ) ) {
					$ld_object_found = true;
				}
			}

			if ( $ld_object_found && ! is_user_logged_in() && ! WC()->checkout()->is_registration_enabled() ) {
				$errors->add( 'require_login', __( 'Your cart contains at least 1 product associated with a course. Please sign up or log in first to continue checkout. Please contact our support if you need assistance.', 'learndash-woocomerce' ) );
			}
		}
	}

	/**
	 * Autocomplete transaction if all cart items are course items
	 * @param  int    $order_id
	 */
	public static function auto_complete_transaction( $order_id )
	{
		if ( ! apply_filters( 'learndash_woocommerce_auto_complete_order', true, $order_id ) ) {
			return;
		}

		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order || ! is_a( $order, 'WC_Order' ) )  {
			return;
		}

		if ( ! $order->is_paid() ) {
			return;
		}

		if ( 'completed' == $order->get_status() ) {
			return;
		}

		$items = $order->get_items();
		$payment_method = $order->get_payment_method();

		$manual_payment_methods = apply_filters( 'learndash_woocommerce_manual_payment_methods', array(
			'bacs', 'cheque', 'cod'
		) );

		// If using manual payment, bail
		if ( in_array( $payment_method, $manual_payment_methods ) ) {
			return;
		}

		$found = array();
		foreach ( $items as $item ) {
			// If variation product
			if ( $item->get_variation_id() > 0 ) {
				$item_id = $item->get_variation_id();
				$courses = (array) get_post_meta( $item->get_variation_id(), '_related_course', true );
				$groups  = (array) get_post_meta( $item->get_variation_id(), '_related_group', true );
			} 
			// Else if normal product
			elseif ( $item->get_product_id() > 0 ) {
				$item_id = $item->get_product_id();
				$courses = (array) get_post_meta( $item->get_product_id(), '_related_course', true );
				$groups  = (array) get_post_meta( $item->get_product_id(), '_related_group', true );
			}

			// Filter out invalid courses and groups first
			$courses = array_map( function( $course_id ) {
				$course = get_post( $course_id );

				return $course && $course->post_status == 'publish' && $course->post_type == 'sfwd-courses';
			}, $courses );

			$groups = array_map( function( $group_id ) {
				$group = get_post( $group_id );

				return $group && $group->post_status == 'publish' && $group->post_type == 'groups';
			}, $groups );

			if ( 
				( is_array( $courses ) && ! empty( $courses ) && ! in_array( 0, $courses ) ) 
				|| ( 
					is_array( $courses ) && count( $courses ) > 1 && in_array( 0, $courses ) 
					|| ( $item->is_type( 'virtual' ) || $item->is_type( 'downloadable' ) ) 
				)
				|| ( is_array( $groups ) && ! empty( $groups ) && ! in_array( 0, $groups ) ) 
				|| ( 
					is_array( $groups ) && count( $groups ) > 1 && in_array( 0, $groups ) 
					|| ( $item->is_type( 'virtual' ) || $item->is_type( 'downloadable' ) ) 
				)
			) {
				$found[] = $item_id;
			}
		}

		// Autocomplete transaction if all items are course
		if ( count( $found ) == count( $items ) ) {
			$order->update_status( 'completed' );
		}	
	}

	/**
	 * Remove course access count if user data is removed
	 * 
	 * @param  int    $user_id     
	 */
	public static function remove_access_increment_count( $user_id ) {
		delete_user_meta( $user_id, '_learndash_woocommerce_enrolled_courses_access_counter' );
	}

	/**
	 * Add course access
	 * 
	 * @param int $course_id ID of a course
	 * @param int $user_id   ID of a user
	 */
	private static function update_add_course_access( $course_id, $user_id, $order_id )
	{
		self::increment_course_access_counter( $course_id, $user_id, $order_id );

		// check if user already enrolled
		if ( ! self::is_user_enrolled_to_course( $user_id, $course_id ) ) {
			ld_update_course_access( $user_id, $course_id );
		} elseif ( self::is_user_enrolled_to_course( $user_id, $course_id ) && ld_course_access_expired( $course_id, $user_id ) ) {
			
			// Remove access first
			// @todo: only remove access counter from old WC orders
			self::reset_course_access_counter( $course_id, $user_id );
			ld_update_course_access( $user_id, $course_id, $remove = true );

			// Re-enroll to get new access from value
			self::increment_course_access_counter( $course_id, $user_id, $order_id );
			ld_update_course_access( $user_id, $course_id );
		}
	}

	/**
	 * Remove course access
	 * 
	 * @param int $course_id ID of a course
	 * @param int $user_id   ID of a user
	 * @param int $order_id  ID of an order
	 */
	private static function update_remove_course_access( $course_id, $user_id, $order_id )
	{
		$courses = self::decrement_course_access_counter( $course_id, $user_id, $order_id );

		if ( ! isset( $courses[ $course_id ] ) || empty( $courses[ $course_id ] ) ) {
			ld_update_course_access( $user_id, $course_id, $remove = true );
		}
	}

	/**
	 * Add group access
	 * 
	 * @param  int    $group_id LearnDash group ID
	 * @param  int    $user_id  WP_User ID
	 * @param  int    $order_id WC order ID
	 * @return void
	 */
	private static function update_add_group_access( $group_id, $user_id, $order_id )
	{
		self::increment_course_access_counter( $group_id, $user_id, $order_id );

		if ( ! learndash_is_user_in_group( $user_id, $group_id ) ) {
			ld_update_group_access( $user_id, $group_id );
		}
	}

	/**
	 * Remove group acess
	 *
	 * @param  int    $group_id LearnDash group ID
	 * @param  int    $user_id  WP_User ID
	 * @param  int    $order_id WC order ID
	 * @return void
	 */
	private static function update_remove_group_access( $group_id, $user_id, $order_id )
	{
		$access = self::decrement_course_access_counter( $group_id, $user_id, $order_id );

		if ( ! isset( $access[ $group_id ] ) || empty( $access[ $group_id ] ) ) {
			ld_update_group_access( $user_id, $group_id, $remove = true );
		}
	}

	/**
	 * Check if a user is already enrolled to a course
	 * 
	 * @param  integer $user_id   User ID
	 * @param  integer $course_id Course ID
	 * @return boolean            True if enrolled|false otherwise
	 */
	private static function is_user_enrolled_to_course( $user_id = 0, $course_id = 0 ) {
		$enrolled_courses = learndash_user_get_enrolled_courses( $user_id );

		if ( is_array( $enrolled_courses ) && in_array( $course_id, $enrolled_courses ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get all LearnDash courses
	 * 
	 * @return object LearnDash course
	 */
	private static function get_learndash_courses()
	{
		global $wpdb;
		$query = "SELECT posts.* FROM $wpdb->posts posts WHERE posts.post_type = 'sfwd-courses' AND posts.post_status = 'publish' ORDER BY posts.post_title";

		return $wpdb->get_results( $query, OBJECT );
	}

	/**
	 * Add enrolled course record to a user
	 *
	 * @param int $course_id ID of a course
	 * @param int $user_id   ID of a user
	 * @param int $order_id  ID of an order
	 */
	private static function increment_course_access_counter( $course_id, $user_id, $order_id )
	{
		$courses = self::get_courses_access_counter( $user_id );

		if ( isset( $courses[ $course_id ] ) && ! is_array( $courses[ $course_id ] ) ) {
			$courses[ $course_id ] = array();
		}

		if ( ! isset( $courses[ $course_id ] ) || ( isset( $courses[ $course_id] ) && array_search( $order_id, $courses[ $course_id ] ) === false ) ) {
			// Add order ID to course access counter
			$courses[ $course_id ][] = $order_id;
		}

		update_user_meta( $user_id, '_learndash_woocommerce_enrolled_courses_access_counter', $courses );

		return $courses;
	}

	/**
	 * Delete enrolled course record from a user
	 * 
	 * @param int $course_id ID of a course
	 * @param int $user_id   ID of a user
	 * @param int $order_id  ID of an order
	 */
	private static function decrement_course_access_counter( $course_id, $user_id, $order_id )
	{
		$courses = self::get_courses_access_counter( $user_id );
		
		if ( isset( $courses[ $course_id ] ) && ! is_array( $courses[ $course_id ] ) ) {
			$courses[ $course_id ] = array();
		}

		if ( isset( $courses[ $course_id ] ) ) {
			$keys = array_keys( $courses[ $course_id ], $order_id );
			if ( is_array( $keys ) ) {
				foreach ( $keys as $key ) {
					unset( $courses[ $course_id ][ $key ] );
				}
			}
		}

		update_user_meta( $user_id, '_learndash_woocommerce_enrolled_courses_access_counter', $courses );

		return $courses;
	}

	/**
	 * Reset course access counter
	 * 
	 * @param  int 	  $course_id Course ID
	 * @param  int 	  $user_id   User ID
	 * @return void
	 */
	private static function reset_course_access_counter( $course_id, $user_id ) {
		$courses = self::get_courses_access_counter( $user_id );
		
		if ( isset( $courses[ $course_id ] ) ) {
			unset( $courses[ $course_id ] );
		}

		update_user_meta( $user_id, '_learndash_woocommerce_enrolled_courses_access_counter', $courses );
	}

	/**
	 * Get user enrolled course access counter
	 * 
	 * @param  int $user_id ID of a user
	 * @return array        Course access counter array
	 */
	private static function get_courses_access_counter( $user_id )
	{
		$courses = get_user_meta( $user_id, '_learndash_woocommerce_enrolled_courses_access_counter', true );

		if ( ! empty( $courses ) ) {
			$courses = maybe_unserialize( $courses );
		} else {
			$courses = array();
		}
		
		return $courses;
	}

	/**
	 * Get setting if course access should be removed when user completeng subscription payment billing cycle
	 *
	 * @param  object $subscription WC_Subscription object
	 * @return boolean
	 */
	public static function is_course_access_removed_on_subscription_billing_cycle_completion( $subscription )
	{
		return apply_filters( 'learndash_woocommerce_remove_course_access_on_subscription_billing_cycle_completion', false, $subscription );
	}

	/**
	 * Output a select input box.
	 *
	 * @param array $field
	 */
	public static function woocommerce_wp_select_multiple( $field ) {
		global $thepostid, $post;
		?>

		<script type="text/javascript">
			jQuery( document ).ready( function( $ ) {
				$( '.select2.regular-width' ).show().select2({
				   closeOnSelect: false,
				   allowClear: true,
				   scrollAfterSelect: false,
				   placeholder: ''
			   });
		   
			   $( '.select2.full-width' ).show().select2({
				   width: '100%',
				   closeOnSelect: false,
				   allowClear: true,
				   scrollAfterSelect: false,
				   placeholder: ''
			   });
			});
		</script>

		<?php

		$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
		$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
		$field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : false;

		// Custom attribute handling
		$custom_attributes = array();

		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

			foreach ( $field['custom_attributes'] as $attribute => $value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
			}
		}

		echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
			<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';

		if ( ! empty( $field['description'] ) && false !== $field['desc_tip'] ) {
			echo wc_help_tip( $field['description'] );
		}

		echo '<select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" ' . implode( ' ', $custom_attributes ) . ' multiple="multiple">';

		foreach ( $field['options'] as $key => $value ) {
			$selected = in_array( $key, $field['value'] ) ? 'selected="selected"' : '';
			echo '<option value="' . esc_attr( $key ) . '" ' . $selected . '>' . esc_html( $value ) . '</option>';
		}

		echo '</select> ';

		if ( ! empty( $field['description'] ) && false === $field['desc_tip'] ) {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}

		echo '</p>';
	}

	public static function get_products_count_for_silent_course_enrollment() {
		return apply_filters( 'learndash_woocommerce_products_count_for_silent_course_enrollment', 5 );
	}

	/**
	 * Output a custom error log file
	 * @param  mixed  $message Message
	 */
	public static function log( $message = '' ) {
		$file = LEARNDASH_WOOCOMMERCE_PLUGIN_PATH . 'error.log';

		if ( ! file_exists( $file ) ) {
			$handle = fopen( $file, 'a+' );
			fclose( $handle );
		}

		error_log( print_r( $message, true ), 3, $file );
	}
}
new Learndash_WooCommerce();


add_action( 'init', 'learndash_woocommerce_set_course_as_virtual' );
/**
 * Establish the Course Product type that is virtual, and sold individually
 */
function learndash_woocommerce_set_course_as_virtual() {
	if (class_exists('WC_Product')) {
		class WC_Product_Course extends WC_Product {

			/**
			 * Initialize course product.
			 *
			 * @param mixed $product
			 */
			public function __construct( $product ) {
				parent::__construct( $product );

				$this->supports = array(
					'ajax_add_to_cart',
				);
				$this->set_virtual( true );
				$this->set_sold_individually( true );
			}

			public function get_type()
			{
				return 'course';
			}


			/**
			 * Get the add to cart button text
			 *
			 * @return string
			 */
			public function add_to_cart_text() {
				$text = $this->is_purchasable() ? __( 'Add to cart', 'learndash-woocommerce' ) : __( 'Read More', 'learndash-woocommerce' );
				return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
			}

			/**
			 * Set the add to cart button URL used on the /shop/ page
			 *
			 * @return string
			 * @since 1.3.1
			 */
			public function add_to_cart_url() {
				// Code copied from WP Simple Product function of same name
				$url = $this->is_purchasable() && $this->is_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->get_id() ) ) : get_permalink( $this->get_id() );
				return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
			}
		}
	}
}


/**
 * Add To Cart template, use the simple template
 */
add_action( 'woocommerce_course_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
