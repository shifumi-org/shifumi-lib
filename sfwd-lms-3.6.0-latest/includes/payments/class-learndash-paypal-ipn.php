<?php
/**
 * PHP-PayPal-IPN Handler
 *
 * This class handles inbound processing of the PayPal IPN post-purchase
 * transactions data.
 *
 * @since 3.2.3
 * @package LearnDash
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'LEARNDASH_VERSION' ) ) {
	exit;
}

if ( ! class_exists( 'LearnDash_PayPal_IPN' ) ) {
	/**
	 * Class to create the instance.
	 */
	class LearnDash_PayPal_IPN {

		/**
		 * IPN Transaction log.
		 *
		 * @since 3.2.3
		 *
		 * @var string $ipn_transaction_log String containing processing
		 * message. Data will be written to post_meta as part of the
		 * transaction post.
		 */
		private static $ipn_transaction_log;

		/**
		 * IPN Transaction data.
		 *
		 * @since 3.2.3
		 *
		 * @var array $ipn_transaction_data Array of IPN POST data from PayPal.
		 */
		private static $ipn_transaction_data;

		/**
		 * IPN Transaction post ID.
		 *
		 * @since 3.2.3
		 *
		 * @var int $ipn_transaction_post_id Will be set to the Transaction
		 * 'sfwd-transactions' Post ID.
		 */
		private static $ipn_transaction_post_id;

		/**
		 * PayPal Settings.
		 *
		 * @since 3.2.3
		 *
		 * @var array $ld_paypal_settings Array of the current general PayPal setting.
		 */
		private static $ld_paypal_settings;

		/**
		 * LD Debug Processing enabled.
		 *
		 * @since 3.2.3
		 *
		 * @var bool $ld_debug_enabled If debug processing is enabled.
		 */
		private static $ld_debug_enabled = false;

		/**
		 * Process hash action
		 *
		 * @since 3.6.0
		 *
		 * @var string $hash_action.
		 */
		private static $hash_action = null;

		/**
		 * Process hash nonce
		 *
		 * @since 3.6.0
		 *
		 * @var string $hash_nonce.
		 */
		private static $hash_nonce = null;

		/**
		 * Process hash User ID
		 *
		 * @since 3.6.0
		 *
		 * @var int $hash_user_id.
		 */
		private static $hash_user_id = null;

		/**
		 * Process hash meta key
		 *
		 * @since 3.6.0
		 *
		 * @var string $hash_user_meta_key.
		 */
		private static $hash_user_meta_key = null;

		/**
		 * Process hash meta values
		 *
		 * @since 3.6.0
		 *
		 * @var string $hash_user_meta_values.
		 */
		private static $hash_user_meta_values = null;

		/**
		 * Public constructor for class
		 *
		 * @since 3.2.2
		 */
		private function __construct() {
		}

		/**
		 * Static function to initialize the class variables.
		 *
		 * @since 3.2.3
		 */
		protected static function init() {
			self::$ipn_transaction_log     = '';
			self::$ipn_transaction_data    = array();
			self::$ipn_transaction_post_id = 0;

			self::$ld_paypal_settings = array();
		}

		/**
		 * Entry point for IPN processing
		 *
		 * @since 3.2.2
		 */
		public static function ipn_process() {
			self::hash_init_action();
			self::ipn_debug( '---' );

			self::ipn_init_settings();
			self::ipn_debug( '---' );

			self::hash_process_action();
			self::ipn_debug( '---' );

			// Create our initial Transaction.
			self::ipn_init_transaction();
			self::ipn_debug( '---' );

			self::ipn_init_post_data();
			self::ipn_debug( '---' );

			self::ipn_init_listener();
			self::ipn_debug( '---' );

			self::ipn_validate_post_data();
			self::ipn_debug( '---' );

			self::ipn_process_post_data();
			self::ipn_debug( '---' );

			self::ipn_process_user_data();
			self::ipn_debug( '---' );

			self::ipn_complete_transaction();
			self::ipn_debug( '---' );

			self::ipn_debug( 'IPN Processing Completed Successfully.' );
			self::ipn_exit();
			// we're done here.
		}

		/**
		 * Initialize the `$ipn_transaction_data` from the IPN POST data.
		 *
		 * @since 3.2.3
		 */
		public static function ipn_init_post_data() {
			self::$ipn_transaction_data = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			self::$ipn_transaction_data = array_map( 'trim', self::$ipn_transaction_data );
			self::$ipn_transaction_data = array_map( 'esc_attr', self::$ipn_transaction_data );

			if ( learndash_is_admin_user() ) {
				if ( ( isset( self::$ipn_transaction_data['ld-debug-nonce'] ) ) && ( ! empty( self::$ipn_transaction_data['ld-debug-nonce'] ) ) && wp_verify_nonce( self::$ipn_transaction_data['ld-debug-nonce'], 'ld-paypal-debug-' . self::$ld_paypal_settings['paypal_email'] . '-' . get_home_url() ) ) {
					self::$ld_debug_enabled = true;
					self::ipn_debug( 'Debug Processing ENABLED' );
				} else {
					self::$ld_debug_enabled = false;
				}
			}

			// First log our incoming vars.
			self::ipn_debug( 'IPN Post vars<pre>' . print_r( self::$ipn_transaction_data, true ) . '</pre>' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

			self::ipn_debug( 'IPN Get vars<pre>' . print_r( $_GET, true ) . '</pre>' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}

		/**
		 * Init the hash action.
		 *
		 * @since @ 3.6.0
		 */
		public static function hash_init_action() {
			if ( is_null( self::$hash_action ) ) {

				if ( ( isset( $_GET['return-success'] ) ) && ( ! empty( $_GET['return-success'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					self::$hash_action = 'return-success';

					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					self::$hash_nonce = sanitize_text_field( wp_unslash( $_GET['return-success'] ) );

				} elseif ( ( isset( $_GET['return-cancel'] ) ) && ( ! empty( $_GET['return-cancel'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					self::$hash_action = 'return-cancel';

					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					self::$hash_nonce = sanitize_text_field( wp_unslash( $_GET['return-cancel'] ) );

				} elseif ( ( isset( $_GET['return-notify'] ) ) && ( ! empty( $_GET['return-notify'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					self::$hash_action = 'return-notify';

					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					self::$hash_nonce = sanitize_text_field( wp_unslash( $_GET['return-notify'] ) );
				} else {
					self::ipn_exit();
				}

				if ( ! self::hash_verify_nonce() ) {
					self::ipn_debug( 'DEBUG: hash verify nonce failed.' );
					self::ipn_exit();
				}
			}
		}

		/**
		 * Process the PayPal hash action.
		 *
		 * @since 3.6.0
		 */
		public static function hash_process_action() {

			switch ( self::$hash_action ) {
				case 'return-success':
					self::ipn_debug( 'Starting Processing action: ' . self::$hash_action );

					$transaction_post_id = self::ipn_init_transaction();

					/**
					 * If success we set the 'return-success' timestamp. This
					 * will be used to check for the 'return-notify' action.
					 */
					if ( ! isset( self::$hash_user_meta_values['return-success'] ) ) {
						self::$hash_user_meta_values['return-success'] = time();
					}

					// Set the reference to the transaction post ID.
					if ( ( ! isset( self::$hash_user_meta_values['transaction_id'] ) ) || ( absint( self::$hash_user_meta_values['transaction_id'] ) !== absint( $transaction_post_id ) ) ) {
						self::$hash_user_meta_values['transaction_id'] = $transaction_post_id;
					}
					self::hash_update_user_meta_values();

					$product_id                                  = 0;
					self::$ipn_transaction_data['ld_ipn_action'] = self::$hash_action;
					self::$ipn_transaction_data['txn_type']      = self::$hash_action;
					self::$ipn_transaction_data['ld_ipn_hash']   = self::$hash_user_meta_values['nonce'];
					self::$ipn_transaction_data['user_id']       = 0;
					self::$ipn_transaction_data['post_id']       = 0;
					self::$ipn_transaction_data['post_type']     = '';

					if ( isset( self::$hash_user_meta_values['product_id'] ) ) {
						$product_id = absint( self::$hash_user_meta_values['product_id'] );

						self::$ipn_transaction_data['post_id']   = $product_id;
						self::$ipn_transaction_data['post_type'] = get_post_type( $product_id );
						if ( learndash_get_post_type_slug( 'course' ) === self::$ipn_transaction_data['post_type'] ) {
							self::$ipn_transaction_data['course_id'] = $product_id;
						} elseif ( learndash_get_post_type_slug( 'group' ) === self::$ipn_transaction_data['post_type'] ) {
							self::$ipn_transaction_data['group_id'] = $product_id;
						}
					}

					if ( ! empty( self::$hash_user_id ) ) {
						self::$ipn_transaction_data['user_id'] = self::$hash_user_id;
					}
					self::ipn_grant_access();

					self::$ipn_transaction_data[ self::$hash_user_meta_key ] = self::$hash_user_meta_values;

					self::$ipn_transaction_data['ld_payment_processor'] = 'paypal';

					self::ipn_update_transaction_post_meta();

					$redirect_url = learndash_paypal_get_purchase_success_redirect_url( $product_id );

					self::ipn_exit( $redirect_url );
					break;

				case 'return-cancel':
					// If cancelled then we can just remove the user meta.
					if ( ! isset( self::$hash_user_meta_values['nonce'] ) ) {
						self::hash_delete_user_meta_values();
					}

					$product_id = 0;
					if ( isset( self::$hash_user_meta_values['product_id'] ) ) {
						$product_id = absint( self::$hash_user_meta_values['product_id'] );
					}

					$redirect_url = learndash_paypal_get_purchase_cancel_redirect_url( $product_id );
					if ( ! empty( $redirect_url ) ) {
						learndash_safe_redirect( $redirect_url );
					}

					self::ipn_exit();
					break;

				case 'return-notify':
					// If notify we can remove the user meta.
					if ( ! isset( self::$hash_user_meta_values['nonce'] ) ) {
						self::hash_delete_user_meta_values();
					}
					break;

				default:
					self::ipn_debug( 'ERROR: Unknown hash action[' . self::$hash_action . '].' );
					self::ipn_exit();

			}
		}

		/**
		 * Get hash user ID.
		 *
		 * @since 3.6.0
		 */
		public static function hash_get_user_id() {
			if ( ( is_null( self::$hash_user_id ) ) && ( ! empty( self::$hash_nonce ) ) ) {
				$user_query = new WP_User_Query(
					array(
						'number'       => 1,
						'meta_key'     => self::hash_get_user_meta_key(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key.
						'meta_compare' => 'EXISTS',
					)
				);

				if ( ( $user_query ) && ( is_a( $user_query, 'WP_User_Query' ) ) ) {
					if ( ( $user_query->total_users > 0 ) && ( isset( $user_query->results[0] ) ) ) {
						self::$hash_user_id = $user_query->results[0]->ID;
					}
				}
			}

			return self::$hash_user_id;
		}

		/**
		 * Get hash meta key.
		 *
		 * @since 3.6.0
		 */
		public static function hash_get_user_meta_key() {
			if ( is_null( self::$hash_user_meta_key ) ) {
				if ( ! empty( self::$hash_nonce ) ) {
					self::$hash_user_meta_key = 'ld_purchase_nonce_' . self::$hash_nonce;
				}
			}

			return self::$hash_user_meta_key;
		}

		/**
		 * Get user hash meta values.
		 *
		 * @since 3.6.0
		 */
		public static function hash_get_user_meta_values() {
			if ( is_null( self::$hash_user_meta_values ) ) {
				$user_id = self::hash_get_user_id();
				if ( ! empty( $user_id ) ) {
					$user_meta = get_user_meta( $user_id, self::hash_get_user_meta_key(), true );
					if ( ( ! is_null( $user_meta ) ) && ( is_array( $user_meta ) ) ) {
						self::$hash_user_meta_values = $user_meta;
					}
				}
			}
			return self::$hash_user_meta_values;
		}

		/**
		 * Update user hash meta values.
		 *
		 * @since 3.6.0
		 */
		public static function hash_update_user_meta_values() {
			if ( ! is_null( self::$hash_user_meta_values ) ) {
				$user_id = self::hash_get_user_id();
				if ( ! empty( $user_id ) ) {
					return update_user_meta( $user_id, self::hash_get_user_meta_key(), self::$hash_user_meta_values );
				}
			}
		}

		/**
		 * Delete user hash meta values.
		 *
		 * @since 3.6.0
		 */
		public static function hash_delete_user_meta_values() {
			if ( ! is_null( self::$hash_user_meta_values ) ) {
				$user_id = self::hash_get_user_id();
				if ( ! empty( $user_id ) ) {
					return delete_user_meta( $user_id, self::hash_get_user_meta_key() );
				}
			}
		}

		/**
		 * Verify user hash nonce.
		 *
		 * @since 3.6.0
		 */
		public static function hash_verify_nonce() {
			if ( ! empty( self::$hash_nonce ) ) {
				$user_id = self::hash_get_user_id();

				$user_meta_values = self::hash_get_user_meta_values();
				/**
				 * Note we can't use wp_nonce_verify() here becuase it uses the user_id as
				 * part of the calculation logic. So we stored the nonce in the user_meta and
				 * can only compare it here.
				 */

				if ( ( isset( $user_meta_values['nonce'] ) ) && ( ! empty( $user_meta_values['nonce'] ) ) && ( $user_meta_values['nonce'] === self::$hash_nonce ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Load LearnDash general PayPal Settings.
		 *
		 * @since 3.2.3
		 */
		public static function ipn_init_settings() {
			self::$ld_paypal_settings = LearnDash_Settings_Section::get_section_settings_all( 'LearnDash_Settings_Section_PayPal' );

			if ( ! isset( self::$ld_paypal_settings['paypal_sandbox'] ) ) {
				self::$ld_paypal_settings['paypal_sandbox'] = '';
			}
			self::$ld_paypal_settings['paypal_sandbox'] = ( 'yes' === self::$ld_paypal_settings['paypal_sandbox'] ) ? 1 : 0;

			// Then log the PayPal settings.
			self::ipn_debug( 'LearnDash Paypal Settings<pre>' . print_r( self::$ld_paypal_settings, true ) . '</pre>' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

			if ( ( ! isset( self::$ld_paypal_settings['paypal_email'] ) ) || ( empty( self::$ld_paypal_settings['paypal_email'] ) ) ) {
				self::ipn_debug( "ERROR: LD PayPal settings 'paypal_email' is empty. [" . self::$ld_paypal_settings['paypal_email'] . ']' );
				self::ipn_exit();
			}
			self::$ld_paypal_settings['paypal_email'] = sanitize_email( self::$ld_paypal_settings['paypal_email'] );
			self::$ld_paypal_settings['paypal_email'] = strtolower( self::$ld_paypal_settings['paypal_email'] );

			if ( ! is_email( self::$ld_paypal_settings['paypal_email'] ) ) {
				self::ipn_debug( "ERROR: LD PayPal settings 'paypal_email' is invalid. [" . self::$ld_paypal_settings['paypal_email'] . ']' );
				self::ipn_exit();
			}
		}

		/**
		 * Initialize the PayPal IPN Listener.
		 *
		 * @since 3.2.3
		 */
		public static function ipn_init_listener() {
			if ( true !== self::$ld_debug_enabled ) {
				self::ipn_debug( 'IPN Listener Loading...' );
				if ( ! file_exists( LEARNDASH_LMS_LIBRARY_DIR . '/paypal/ipnlistener.php' ) ) {
					self::ipn_debug( 'ERROR: Required file not found ' . LEARNDASH_LMS_LIBRARY_DIR . '/paypal/ipnlistener.php' );
					self::ipn_exit();
				}
				require LEARNDASH_LMS_LIBRARY_DIR . '/paypal/ipnlistener.php';
				$learndash_paypal_ipn_listener = new IpnListener();

				/**
				 * Fires after instansiating a ipnlistener object to allow override of public attributes.
				 *
				 * @since 2.2.1.2
				 *
				 * @param Object  $learndash_paypal_ipn_listener An instance of IpnListener Class.
				 */
				do_action_ref_array( 'learndash_ipnlistener_init', array( &$learndash_paypal_ipn_listener ) );

				self::ipn_debug( 'IPN Listener Loaded' );

				if ( ! empty( self::$ld_paypal_settings['paypal_sandbox'] ) ) {
					self::ipn_debug( 'PayPal Sandbox Enabled.' );
					$learndash_paypal_ipn_listener->use_sandbox = true;
				} else {
					self::ipn_debug( 'PayPal Live Enabled.' );
					$learndash_paypal_ipn_listener->use_sandbox = false;
				}

				try {
					self::ipn_debug( 'Checking IPN Post Method.' );
					$learndash_paypal_ipn_listener->requirePostMethod();
					$learndash_paypal_ipn_verified = $learndash_paypal_ipn_listener->processIpn();
					self::ipn_debug( 'IPN Post method check completed.' );
					if ( ! $learndash_paypal_ipn_verified ) {
						/**
						 * An Invalid IPN *may* be caused by a fraudulent transaction
						 * attempt. It's a good idea to have a developer or sys admin
						 * manually investigate any invalid IPN.
						 */
						self::ipn_debug( 'ERROR: Invalid IPN. Shutting Down Processing.' );
						self::ipn_exit();
					}
				} catch ( Exception $e ) {
					self::ipn_debug( 'IPN Post method error: <pre>' . print_r( $e->getMessage(), true ) . '</pre>' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
					self::ipn_debug( 'Found Exception. Ending Script.' );
					self::ipn_exit();
				}
			}
		}

		/**
		 * Vaidate the IPN POST data.
		 *
		 * @since 3.2.3
		 */
		protected static function ipn_validate_post_data() {

			self::ipn_validate_payment_type();

			if ( ( ! isset( self::$ipn_transaction_data['notify_version'] ) ) || ( empty( self::$ipn_transaction_data['notify_version'] ) ) ) {
				self::ipn_debug( 'ERROR: PayPal POST param "notify_version" missing or empty. Aborting.' );
				self::ipn_exit();
			}

			if ( in_array( self::$ipn_transaction_data['txn_type'], array( 'web_accept', 'subscr_payment' ), true ) ) {

				self::ipn_validate_payment_status();

				if ( ( ! isset( self::$ipn_transaction_data['mc_gross'] ) ) || ( empty( self::$ipn_transaction_data['mc_gross'] ) ) ) {
					self::ipn_debug( "ERROR: Missing or empty 'mc_gross' in IPN data." );
					self::ipn_exit();
				}
				self::ipn_debug( "Valid IPN 'mc_gross' : " . self::$ipn_transaction_data['mc_gross'] );
			}

			if ( ( ! isset( self::$ipn_transaction_data['item_number'] ) ) || ( empty( self::$ipn_transaction_data['item_number'] ) ) ) {
				self::ipn_debug( "ERROR: Invalid or missing 'item_number' in IPN data" );
				self::ipn_exit();
			}

			self::ipn_validate_customer_data();
			self::ipn_validate_receiver_data();
		}

		/**
		 * Validate the IPN Customer data.
		 *
		 * @since 3.6.0
		 */
		protected static function ipn_validate_customer_data() {
			if ( ! isset( self::$ipn_transaction_data['payer_email'] ) ) {
				self::ipn_debug( "ERROR: Missing transaction 'payer_email' in IPN data" );
				self::ipn_exit();
			}

			self::$ipn_transaction_data['payer_email'] = sanitize_email( self::$ipn_transaction_data['payer_email'] );
			self::$ipn_transaction_data['payer_email'] = strtolower( self::$ipn_transaction_data['payer_email'] );

			if ( ! is_email( self::$ipn_transaction_data['payer_email'] ) ) {
				self::ipn_debug( "ERROR: Invalid 'payer_email' in IPN data. [" . self::$ipn_transaction_data['payer_email'] . ']' );
				self::ipn_exit();
			}
			self::ipn_debug( "Valid IPN 'payer_email' : " . self::$ipn_transaction_data['payer_email'] );

			if ( isset( self::$ipn_transaction_data['first_name'] ) ) {
				self::$ipn_transaction_data['first_name'] = esc_attr( self::$ipn_transaction_data['first_name'] );
			} else {
				self::$ipn_transaction_data['first_name'] = '';
			}

			if ( isset( self::$ipn_transaction_data['last_name'] ) ) {
				self::$ipn_transaction_data['last_name'] = esc_attr( self::$ipn_transaction_data['last_name'] );
			} else {
				self::$ipn_transaction_data['last_name'] = '';
			}
		}

		/**
		 * Validate the IPN Receiver data.
		 *
		 * @since 3.6.0
		 */
		protected static function ipn_validate_receiver_data() {
			$valid_ipn_email = false;

			if ( isset( self::$ipn_transaction_data['receiver_email'] ) ) {
				self::$ipn_transaction_data['receiver_email'] = sanitize_email( self::$ipn_transaction_data['receiver_email'] );
				self::$ipn_transaction_data['receiver_email'] = strtolower( self::$ipn_transaction_data['receiver_email'] );

				if ( self::$ipn_transaction_data['receiver_email'] === self::$ld_paypal_settings['paypal_email'] ) {
					$valid_ipn_email = true;
				}
				self::ipn_debug( 'Receiver Email: ' . self::$ipn_transaction_data['receiver_email'] . ' Valid Receiver Email? :' . ( true === $valid_ipn_email ? 'YES' : 'NO' ) );
			}

			if ( isset( self::$ipn_transaction_data['business'] ) ) {
				self::$ipn_transaction_data['business'] = sanitize_email( self::$ipn_transaction_data['business'] );
				self::$ipn_transaction_data['business'] = strtolower( self::$ipn_transaction_data['business'] );

				if ( self::$ipn_transaction_data['business'] === self::$ld_paypal_settings['paypal_email'] ) {
					$valid_ipn_email = true;
				}
				self::ipn_debug( 'Business Email: ' . self::$ipn_transaction_data['business'] . ' Valid Business Email? :' . ( true === $valid_ipn_email ? 'YES' : 'NO' ) );
			}

			if ( true !== $valid_ipn_email ) {
				self::ipn_debug( 'Error: IPN with invalid receiver/business email!' );
				self::ipn_exit();
			}
		}

		/**
		 * Vaidate the IPN POST Payment Type.
		 *
		 * @since 3.6.0
		 */
		protected static function ipn_validate_payment_type() {

			if ( ! isset( self::$ipn_transaction_data['txn_type'] ) ) {
				self::ipn_debug( "ERROR: Missing transaction 'txn_type' in IPN data" );
				self::ipn_exit();
			}

			switch ( self::$ipn_transaction_data['txn_type'] ) {
				case 'web_accept':
				case 'subscr_signup':
				case 'subscr_payment':
				case 'subscr_cancel':
				case 'subscr_failed':
				case 'subscr_eot':
					self::ipn_debug( "Valid IPN 'txn_type' : " . self::$ipn_transaction_data['txn_type'] );

					break;

				default:
					self::ipn_debug( "ERROR: Unsupported transaction 'txn_type' : " . self::$ipn_transaction_data['txn_type'] );
					self::ipn_exit();
					break;
			}
		}

		/**
		 * Vaidate the IPN POST Payment Status.
		 *
		 * @since 3.6.0
		 */
		public static function ipn_validate_payment_status() {
			switch ( self::$ipn_transaction_data['txn_type'] ) {
				case 'web_accept':
				case 'subscr_payment':
					if ( ! isset( self::$ipn_transaction_data['payment_status'] ) ) {
						self::ipn_debug( "ERROR: Missing 'payment_status' in IPN data" );
						self::ipn_exit();
					}

					if ( 'completed' !== strtolower( self::$ipn_transaction_data['payment_status'] ) ) {
						self::ipn_debug( "ERROR: 'payment_status' not 'completed' in IPN data" );
						self::ipn_exit();
					}

					self::ipn_debug( "Valid IPN 'payment_status': " . self::$ipn_transaction_data['payment_status'] );

					break;

				case 'subscr_signup':
				case 'subscr_cancel':
				case 'subscr_failed':
				case 'subscr_eot':
					break;

				default:
					break;
			}
		}

		/**
		 * Process the IPN POST data.
		 *
		 * @since 3.2.3
		 */
		public static function ipn_process_post_data() {
			self::$ipn_transaction_data['post_id']          = 0;
			self::$ipn_transaction_data['post_type']        = '';
			self::$ipn_transaction_data['post_type_prefix'] = '';

			self::$ipn_transaction_data['post_id']   = absint( self::$ipn_transaction_data['item_number'] );
			self::$ipn_transaction_data['post_type'] = get_post_type( self::$ipn_transaction_data['post_id'] );

			if ( learndash_get_post_type_slug( 'course' ) === self::$ipn_transaction_data['post_type'] ) {
				self::$ipn_transaction_data['post_type_prefix'] = 'course';
				self::$ipn_transaction_data['course_id']        = absint( self::$ipn_transaction_data['post_id'] );
				self::ipn_debug( 'Purchased Course access [' . self::$ipn_transaction_data['post_id'] . ']' );
			} elseif ( learndash_get_post_type_slug( 'group' ) === self::$ipn_transaction_data['post_type'] ) {
				self::$ipn_transaction_data['post_type_prefix'] = 'group';
				self::$ipn_transaction_data['group_id']         = absint( self::$ipn_transaction_data['post_id'] );
				self::ipn_debug( 'Purchased Group access [' . self::$ipn_transaction_data['post_id'] . ']' );
			} else {
				self::$ipn_transaction_data['post_id']          = '';
				self::$ipn_transaction_data['post_type']        = '';
				self::$ipn_transaction_data['post_type_prefix'] = '';
			}

			if ( empty( self::$ipn_transaction_data['post_id'] ) ) {
				self::ipn_debug( "ERROR: Invalid 'post_id' in IPN data. Unable to determine related Course/Group post." );
				self::ipn_exit();
			}

			if ( empty( self::$ipn_transaction_data['post_type'] ) ) {
				self::ipn_debug( "ERROR: Invalid 'post_id' in IPN data. Unable to determine related Course/Group post." );
				self::ipn_exit();
			}

			$post_type_prefix = self::$ipn_transaction_data['post_type_prefix'];

			if ( in_array( self::$ipn_transaction_data['txn_type'], array( 'web_accept', 'subscr_payment' ), true ) ) {
				$post_settings = learndash_get_setting( self::$ipn_transaction_data['post_id'] );

				if ( ( ! isset( $post_settings[ $post_type_prefix . '_price_type' ] ) ) || ( empty( $post_settings[ $post_type_prefix . '_price_type' ] ) ) ) {
					self::ipn_debug( 'ERROR: Price Type: ' . $post_settings[ $post_type_prefix . '_price_type' ] . ' not set. Aborting' );
					self::ipn_exit();
				}

				if ( ( ( 'web_accept' !== self::$ipn_transaction_data['txn_type'] ) && ( 'paynow' === $post_settings[ $post_type_prefix . '_price_type' ] ) ) || ( ( 'subscr_payment' !== self::$ipn_transaction_data['txn_type'] ) && ( 'subscribe' === $post_settings[ $post_type_prefix . '_price_type' ] ) ) ) {
					self::ipn_debug( "ERROR: Transaction type mismatch: IPN 'txn_type' " . esc_attr( self::$ipn_transaction_data['txn_type'] ) . ' Post Price Type: ' . esc_attr( $post_settings[ $post_type_prefix . '_price_type' ] ) . '. Aborting' );
					self::ipn_exit();
				}

				if ( ( ! isset( $post_settings[ $post_type_prefix . '_price' ] ) ) || ( empty( $post_settings[ $post_type_prefix . '_price' ] ) ) ) {
					self::ipn_debug( 'ERROR: ' . ucfirst( self::$ipn_transaction_data['post_type_prefix'] ) . ' Price setting not set or empty. Aborting' );
					self::ipn_exit();
				}

				$server_course_price = '0.00';
				if ( isset( $post_settings[ $post_type_prefix . '_price' ] ) ) {
					$server_course_price = preg_replace( '/[^0-9.]/', '', $post_settings[ $post_type_prefix . '_price' ] );
					$server_course_price = number_format( floatval( $server_course_price ), 2, '.', '' );
				}
				self::ipn_debug( ucfirst( $post_type_prefix ) . ' Price [' . $server_course_price . ']' );

				$server_course_trial_price = '0.00';
				if ( isset( $post_settings[ $post_type_prefix . '_trial_price' ] ) ) {
					$server_course_trial_price = preg_replace( '/[^0-9.]/', '', $post_settings[ $post_type_prefix . '_trial_price' ] );
					$server_course_trial_price = number_format( floatval( $server_course_trial_price ), 2, '.', '' );
				}
				self::ipn_debug( ucfirst( $post_type_prefix ) . ' Trial Price [' . $server_course_trial_price . ']' );

				$ipn_course_price = preg_replace( '/[^0-9.]/', '', self::$ipn_transaction_data['mc_gross'] );
				$ipn_course_price = floatval( $ipn_course_price );
				self::ipn_debug( 'IPN Gross Price [' . $ipn_course_price . ']' );

				if ( isset( self::$ipn_transaction_data['tax'] ) ) {
					$ipn_tax_price = preg_replace( '/[^0-9.]/', '', self::$ipn_transaction_data['tax'] );
				} else {
					$ipn_tax_price = 0;
				}
				$ipn_tax_price = floatval( $ipn_tax_price );
				self::ipn_debug( 'IPN Tax [' . $ipn_tax_price . ']' );

				$ipn_course_price = $ipn_course_price - $ipn_tax_price;
				$ipn_course_price = number_format( floatval( $ipn_course_price ), 2, '.', '' );
				self::ipn_debug( 'IPN Gross - Tax (result) [' . $ipn_course_price . ']' );

				if ( ( ! empty( $server_course_trial_price ) ) && ( $server_course_trial_price == $ipn_course_price ) ) {
					self::ipn_debug( 'IPN Price match: IPN Price [' . $ipn_course_price . '] ' . ucfirst( $post_type_prefix ) . ' Trial Price [' . $server_course_trial_price . ']' );
				} elseif ( $server_course_price == $ipn_course_price ) {
					self::ipn_debug( 'IPN Price match: IPN Price [' . $ipn_course_price . '] ' . ucfirst( $post_type_prefix ) . ' Price [' . $server_course_price . ']' );
				} else {
					self::ipn_debug( 'Error: IPN Price mismatch: IPN Price [' . $ipn_course_price . '] ' . ucfirst( $post_type_prefix ) . ' Price [' . $server_course_price . ']' );
					self::ipn_exit();
				}
			}
		}

		/**
		 * Process the User data.
		 *
		 * This is where the use is created in the WordPress system if needed.
		 *
		 * @since 3.2.3
		 */
		public static function ipn_process_user_data() {
			// get / add user.
			$user = '';
			if ( ! empty( self::$ipn_transaction_data['custom'] ) ) {
				$user = get_user_by( 'id', absint( self::$ipn_transaction_data['custom'] ) );
				if ( ( $user ) && ( is_a( $user, 'WP_User' ) ) ) {
					self::ipn_debug( "Valid 'custom' in IPN data: [" . absint( self::$ipn_transaction_data['custom'] ) . ']. Matched to User ID [' . $user->ID . ']' );
					self::$ipn_transaction_data['user_id'] = $user->ID;
				} else {
					$user = '';
					self::ipn_debug( "Error: Unknown User ID 'custom' in IPN data: " . absint( self::$ipn_transaction_data['custom'] ) );
					self::ipn_debug( "Continue processing to create new user from IPN 'payer_email'." );
				}
			}

			if ( empty( $user ) ) {
				self::$ipn_transaction_data['user_id'] = email_exists( self::$ipn_transaction_data['payer_email'] );
				if ( ! empty( self::$ipn_transaction_data['user_id'] ) ) {
					self::ipn_debug( "IPN 'payer_email' matched to existing user. User Id: " . self::$ipn_transaction_data['user_id'] );
					$user = get_user_by( 'id', self::$ipn_transaction_data['user_id'] );
				} else {
					self::ipn_debug( 'User email does not exists. Checking available username...' );
					$username = self::$ipn_transaction_data['payer_email'];

					self::$ipn_transaction_data['user_id'] = username_exists( $username );
					if ( ! empty( self::$ipn_transaction_data['user_id'] ) ) {

						self::ipn_debug( 'Username matching email found, cannot use.' );
						$count = 1;

						do {
							$new_username = $count . '_' . $username;
							$count++;
						} while ( username_exists( $new_username ) );

						$username = $new_username;
						self::ipn_debug( 'Accepting user with $username as :' . $new_username );
					}

					$random_password = wp_generate_password( 12, false );
					self::ipn_debug( 'Creating User with username: ' . $username . ' email: ' . self::$ipn_transaction_data['payer_email'] );
					self::$ipn_transaction_data['user_id'] = wp_create_user( $username, $random_password, self::$ipn_transaction_data['payer_email'] );
					self::ipn_debug( 'User created with user_id: ' . self::$ipn_transaction_data['user_id'] );

					wp_new_user_notification( self::$ipn_transaction_data['user_id'], null, 'both' );

					self::ipn_debug( 'New User Notification Sent.' );

					$user = get_user_by( 'id', self::$ipn_transaction_data['user_id'] );

					if ( ( ! empty( self::$ipn_transaction_data['first_name'] ) ) && ( ! empty( self::$ipn_transaction_data['first_name'] ) ) ) {
						self::ipn_debug( 'Updating User: ' . self::$ipn_transaction_data['user_id'] . ' first_name: ' . self::$ipn_transaction_data['first_name'] . ' last_name: ' . self::$ipn_transaction_data['last_name'] );
						wp_update_user(
							array(
								'ID'         => self::$ipn_transaction_data['user_id'],
								'first_name' => self::$ipn_transaction_data['first_name'],
								'last_name'  => self::$ipn_transaction_data['last_name'],
							)
						);
					}
				}
			}

			if ( in_array( self::$ipn_transaction_data['txn_type'], array( 'web_accept', 'subscr_payment' ), true ) ) {
				self::ipn_grant_access();
			}
		}

		/**
		 * Logs the message to the IPN Processing log.
		 *
		 * @since 3.2.2
		 *
		 * @param string $msg Debug message.
		 */
		public static function ipn_debug( $msg = '' ) {
			if ( ! empty( $msg ) ) {
				if ( '---' === $msg ) {
					$dattime = '';
					$msg     = "\r\n" . $msg;
				} else {
					$dattime = learndash_adjust_date_time_display( time(), 'Y-m-d H:i:s' );
				}
				self::$ipn_transaction_log .= $dattime . ' ' . $msg . "\r\n";

				if ( true === self::$ld_debug_enabled ) {
					echo $dattime . ' ' . $msg . '<br />'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}

		/**
		 * IPN Processing exit.
		 *
		 * This is a general cleanup function called at the end of
		 * processing on an abort. This function will finish out the
		 * processing log before exit.
		 *
		 * @param string $redirect_url redirect on exit. @since 3.6.0.
		 */
		public static function ipn_exit( $redirect_url = '' ) {

			if ( ! empty( self::$ipn_transaction_log ) ) {
				$transaction_post_id = self::ipn_init_transaction();
				if ( ! empty( $transaction_post_id ) ) {
					/**
					 * Filters if we save the PayPal processing log to transaction.
					 *
					 * @since 3.3.0
					 *
					 * @param boolean $save_log True to save processing log.
					 */
					if ( apply_filters( 'learndash_paypal_save_processing_log', true ) ) {
						$process_log = get_post_meta( $transaction_post_id, 'processing_log', true );
						if ( ! is_string( $process_log ) ) {
							$process_log = '';
						}
						$process_log .= self::$ipn_transaction_log;
						update_post_meta( $transaction_post_id, 'processing_log', $process_log );
					}
				}
			}

			if ( ! empty( $redirect_url ) ) {
				learndash_safe_redirect( $redirect_url );
			}

			exit();
		}

		/**
		 * Initialize the Transaction Post instance.
		 *
		 * @since 3.2.3
		 */
		public static function ipn_init_transaction() {
			if ( empty( self::$ipn_transaction_post_id ) ) {
				if ( ( isset( self::$hash_user_meta_values['transaction_id'] ) ) && ( ! empty( self::$hash_user_meta_values['transaction_id'] ) ) ) {
					$transaction_post = get_post( self::$hash_user_meta_values['transaction_id'] );
					if ( ( $transaction_post ) && ( is_a( $transaction_post, 'WP_Post' ) ) && ( learndash_get_post_type_slug( 'transaction' ) === $transaction_post->post_type ) ) {
						self::$ipn_transaction_post_id = absint( self::$hash_user_meta_values['transaction_id'] );
						self::ipn_debug( 'Resuming Transaction. Post Id: ' . self::$ipn_transaction_post_id );
					}
				}

				if ( empty( self::$ipn_transaction_post_id ) ) {
					self::$ipn_transaction_post_id = wp_insert_post(
						array(
							'post_title'  => 'PayPal IPN Transaction',
							'post_type'   => 'sfwd-transactions',
							'post_status' => 'draft',
							'post_author' => 0,
						)
					);

					self::ipn_debug( 'Starting Transaction. Post Id: ' . self::$ipn_transaction_post_id );

					if ( ( ! isset( self::$hash_user_meta_values['transaction_id'] ) ) || ( absint( self::$hash_user_meta_values['transaction_id'] ) !== absint( self::$ipn_transaction_post_id ) ) ) {
						self::$hash_user_meta_values['transaction_id'] = absint( self::$ipn_transaction_post_id );
						self::hash_update_user_meta_values();
					}
				}
			}

			return self::$ipn_transaction_post_id;
		}

		/**
		 * Complete the IPN Transaction Processing.
		 *
		 * @since 3.2.3
		 */
		public static function ipn_complete_transaction() {

			$transaction_post_id = self::ipn_init_transaction();

			if ( ! empty( $transaction_post_id ) ) {
				self::ipn_debug( 'Completing Transaction: Post Id: ' . $transaction_post_id );

				$post_id = wp_insert_post(
					array(
						'ID'          => $transaction_post_id,
						'post_title'  => self::ipn_get_transaction_title(),
						'post_type'   => 'sfwd-transactions',
						'post_status' => 'publish',
						'post_author' => self::$ipn_transaction_data['user_id'],
					)
				);
				if ( absint( $post_id ) !== absint( $transaction_post_id ) ) {
					$transaction_post_id = absint( $post_id );
				}
				self::ipn_update_transaction_post_meta();
			}
		}

		/**
		 * Update the IPN Transaction post meta.
		 *
		 * @since 3.6.0
		 */
		public static function ipn_update_transaction_post_meta() {
			$transaction_post_id = self::ipn_init_transaction();
			if ( ! empty( $transaction_post_id ) ) {
				foreach ( self::$ipn_transaction_data as $k => $v ) {
					if ( 'post' !== $k ) {
						update_post_meta( $transaction_post_id, $k, $v );
					}
				}
			}
		}

		/**
		 * Get the Transaction title.
		 *
		 * @since 3.2.3
		 */
		public static function ipn_get_transaction_title() {
			$transaction_post_label = self::get_transaction_post_label();
			$transaction_post_title = self::get_transaction_post_title();
			$transaction_type_label = '';

			$transaction_post_title = sprintf(
				// translators: placeholders: Course/Group Label, Course/Group Post title, Transaction Label, User name.
				esc_html_x( '%1$s %2$s %3$s By %4$s', 'placeholders: Course/Group Label, Course/Group Post title, Transaction Label, User name', 'learndash' ),
				$transaction_post_label,
				$transaction_post_title,
				$transaction_type_label,
				self::$ipn_transaction_data['payer_email']
			);

			return $transaction_post_title;
		}

		/**
		 * Get Transaction Post Title
		 *
		 * @since 3.6.0
		 */
		protected static function get_transaction_post_title() {
			$transaction_post_title = '';

			if ( ! empty( self::$ipn_transaction_data['post_id'] ) ) {
				$transaction_post_title = get_the_title( self::$ipn_transaction_data['post_id'] );
			}

			return $transaction_post_title;
		}

		/**
		 * Get Transaction Post Label
		 *
		 * @since 3.6.0
		 */
		protected static function get_transaction_post_label() {
			$transaction_post_label = '';

			if ( ! empty( self::$ipn_transaction_data['post_id'] ) ) {
				if ( learndash_get_post_type_slug( 'course' ) === self::$ipn_transaction_data['post_type'] ) {
					$transaction_post_label = learndash_get_custom_label( 'course' );
				} elseif ( learndash_get_post_type_slug( 'group' ) === self::$ipn_transaction_data['post_type'] ) {
					$transaction_post_label = learndash_get_custom_label( 'group' );
				}
			}

			if ( empty( $transaction_post_label ) ) {
				$transaction_post_label = 'Unknown';
			}

			return $transaction_post_label;
		}

		/**
		 * Get IPN Transaction label
		 *
		 * @since 3.6.0
		 */
		protected static function get_transaction_type_label() {
			$transaction_type_label = '';

			if ( 'web_accept' === self::$ipn_transaction_data['txn_type'] ) {
				$transaction_type_label = esc_html__( 'Purchased', 'learndash' );
			} elseif ( 'subscr_signup' === self::$ipn_transaction_data['txn_type'] ) {
				$transaction_type_label = esc_html__( 'Subscription Signup', 'learndash' );
			} elseif ( 'subscr_payment' === self::$ipn_transaction_data['txn_type'] ) {
				$transaction_type_label = esc_html__( 'Subscription Payment', 'learndash' );
			} elseif ( 'subscr_cancel' === self::$ipn_transaction_data['txn_type'] ) {
				$transaction_type_label = esc_html__( 'Subscription Cancel', 'learndash' );
			} elseif ( 'subscr_failed' === self::$ipn_transaction_data['txn_type'] ) {
				$transaction_type_label = esc_html__( 'Subscription Failed', 'learndash' );
			} elseif ( 'subscr_eot' === self::$ipn_transaction_data['txn_type'] ) {
				$transaction_type_label = esc_html__( 'Subscription EOT', 'learndash' );
			}

			return $transaction_type_label;
		}

		/**
		 * Grant user access.
		 *
		 * This function will enroll the user in the Course/Group.
		 *
		 * @since 3.2.3
		 */
		public static function ipn_grant_access() {
			if ( ( ! empty( self::$ipn_transaction_data['user_id'] ) ) && ( ! empty( self::$ipn_transaction_data['post_id'] ) ) ) {
				if ( learndash_get_post_type_slug( 'course' ) === get_post_type( self::$ipn_transaction_data['post_id'] ) ) {
					self::ipn_debug( 'Starting to give Course access: User ID[' . absint( self::$ipn_transaction_data['user_id'] ) . '] Course[' . self::$ipn_transaction_data['post_id'] . ']' );
					if ( ! sfwd_lms_has_access( self::$ipn_transaction_data['post_id'], self::$ipn_transaction_data['user_id'] ) ) {
						// record in course.
						ld_update_course_access( absint( self::$ipn_transaction_data['user_id'] ), self::$ipn_transaction_data['post_id'] );
						learndash_send_purchase_success_email( absint( self::$ipn_transaction_data['user_id'] ), self::$ipn_transaction_data['post_id'] );
						self::ipn_debug( 'User enrolled in Course success.' );

					} else {
						self::ipn_debug( 'User previously enrolled in Course.' );
					}
				} elseif ( learndash_get_post_type_slug( 'group' ) === get_post_type( self::$ipn_transaction_data['post_id'] ) ) {
					self::ipn_debug( 'Starting to give Group access: User ID[' . absint( self::$ipn_transaction_data['user_id'] ) . '] Group[' . self::$ipn_transaction_data['post_id'] . ']' );

					$user_enrolled = get_user_meta( self::$ipn_transaction_data['user_id'], 'learndash_group_users_' . self::$ipn_transaction_data['post_id'], true );
					if ( ! $user_enrolled ) {
						// record in group.
						ld_update_group_access( absint( self::$ipn_transaction_data['user_id'] ), self::$ipn_transaction_data['post_id'] );
						learndash_send_purchase_success_email( absint( self::$ipn_transaction_data['user_id'] ), self::$ipn_transaction_data['post_id'] );
						self::ipn_debug( 'User enrolled in Group success.' );
					} else {
						self::ipn_debug( 'User previously enrolled Group.' );
					}
				}
			}
		}

		// End of functions.
	}
}
LearnDash_PayPal_IPN::ipn_process();
