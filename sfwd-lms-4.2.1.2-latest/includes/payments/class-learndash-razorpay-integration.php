<?php
/**
 * This class handles Razorpay integration.
 *
 * @since 4.2.0
 *
 * @package LearnDash
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

if ( ! class_exists( 'LearnDash_Razorpay_Integration' ) && class_exists( 'LearnDash_Payment_Gateway_Integration' ) ) {
	/**
	 * Razorpay's integration class.
	 *
	 * @since 4.2.0
	 */
	class LearnDash_Razorpay_Integration extends LearnDash_Payment_Gateway_Integration {
		const AJAX_ACTION_SETUP_OPTIONS = 'learndash_razorpay_setup_options';

		const META_KEY_CUSTOMER_ID_LIVE = 'learndash_razorpay_live_customer_id';
		const META_KEY_CUSTOMER_ID_TEST = 'learndash_razorpay_test_customer_id';

		const META_KEY_PLANS_LIVE = 'learndash_razorpay_live_plans';
		const META_KEY_PLANS_TEST = 'learndash_razorpay_test_plans';

		const PERIOD_HASH = array(
			'D' => 'daily',
			'W' => 'weekly',
			'M' => 'monthly',
			'Y' => 'yearly',
		);

		const TRIAL_PERIOD_DURATION_HASH = array(
			'D' => DAY_IN_SECONDS,
			'W' => WEEK_IN_SECONDS,
			'M' => MONTH_IN_SECONDS,
			'Y' => YEAR_IN_SECONDS,
		);

		const EVENT_ORDER_PAID                 = 'order.paid';
		const EVENT_SUBSCRIPTION_AUTHENTICATED = 'subscription.authenticated';
		const EVENT_SUBSCRIPTION_ACTIVATED     = 'subscription.activated';
		const EVENT_SUBSCRIPTION_COMPLETED     = 'subscription.completed';
		const EVENT_SUBSCRIPTION_PENDING       = 'subscription.pending';
		const EVENT_SUBSCRIPTION_HALTED        = 'subscription.halted';
		const EVENT_SUBSCRIPTION_CANCELLED     = 'subscription.cancelled';
		const EVENT_SUBSCRIPTION_PAUSED        = 'subscription.paused';
		const EVENT_SUBSCRIPTION_RESUMED       = 'subscription.resumed';

		const PAYMENT_PROCESSOR = 'razorpay';

		/**
		 * Settings.
		 *
		 * @since 4.2.0
		 *
		 * @var array
		 */
		protected $settings;

		/**
		 * Secret key.
		 *
		 * @since 4.2.0
		 *
		 * @var string
		 */
		protected $secret_key;

		/**
		 * Publishable key.
		 *
		 * @since 4.2.0
		 *
		 * @var string
		 */
		protected $publishable_key;

		/**
		 * Webhook secret.
		 *
		 * @since 4.2.0
		 *
		 * @var string
		 */
		protected $webhook_secret;

		/**
		 * API client.
		 *
		 * @since 4.2.0
		 *
		 * @var Api
		 */
		protected $api;

		/**
		 * Course/Group we are working with.
		 *
		 * @since 4.2.0
		 *
		 * @var WP_Post
		 */
		protected $post;

		/**
		 * Current user.
		 *
		 * @since 4.2.0
		 *
		 * @var WP_User
		 */
		protected $user;

		/**
		 * Razorpay customer id meta key name.
		 *
		 * @since 4.2.0
		 *
		 * @var string
		 */
		protected $customer_id_meta_key;

		/**
		 * Razorpay plans meta key name.
		 *
		 * @since 4.2.0
		 *
		 * @var string
		 */
		protected $plans_meta_key;

		/**
		 * Current currency code.
		 *
		 * @since 4.2.0
		 *
		 * @var string
		 */
		protected $currency;

		/**
		 * Construction.
		 *
		 * @since 4.2.0
		 */
		public function __construct() {
			$this->configure();

			if ( ! $this->is_ready() ) {
				return;
			}

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_filter( 'learndash_payment_button', array( $this, 'add_payment_button' ), 11, 2 );
			add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION_SETUP_OPTIONS, array( $this, 'setup_options' ) );
			add_action( 'wp_ajax_' . self::AJAX_ACTION_SETUP_OPTIONS, array( $this, 'setup_options' ) );
			add_action( 'wp_loaded', array( $this, 'process_webhook' ) );
		}

		/**
		 * Enqueues scripts.
		 *
		 * @since 4.2.0
		 *
		 * @return void
		 */
		public function enqueue_scripts(): void {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'razorpay', 'https://checkout.razorpay.com/v1/checkout.js', array(), false, true ); // phpcs:ignore
			wp_enqueue_script( 'learndash-payments' );
		}

		/**
		 * Creates an order/subscription in Razorpay.
		 *
		 * @since 4.2.0
		 *
		 * return void
		 */
		public function setup_options(): void {
			if ( empty( $_POST['nonce'] ) || empty( $_POST['post_id'] ) ) {
				wp_send_json_error(
					array(
						'message' => __( 'Cheating?', 'learndash' ),
					)
				);
			}

			$this->post = get_post( absint( $_POST['post_id'] ) ); // phpcs:ignore
			$this->user = wp_get_current_user();

			if (
				! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), $this->get_nonce_name() ) ||
				! is_a( $this->post, WP_Post::class )
			) {
				wp_send_json_error(
					array(
						'message' => __( 'Cheating?', 'learndash' ),
					)
				);
			}

			try {
				$payment_options = $this->map_payment_options();
			} catch ( Exception $e ) {
				wp_send_json_error(
					array(
						'message' => $e->getMessage(),
					)
				);
				wp_die();
			}

			$redirect_url = $this->settings['return_url'] ?? '';

			if ( empty( $redirect_url ) ) {
				if ( learndash_is_course_post( $this->post ) ) {
					$redirect_url = learndash_get_course_enrollment_url( $this->post );
				} elseif ( learndash_is_group_post( $this->post ) ) {
					$redirect_url = learndash_get_group_enrollment_url( $this->post );
				}
			}

			wp_send_json_success(
				array(
					'options'      => $payment_options,
					'redirect_url' => $redirect_url,
				)
			);
		}

		/**
		 * Checks if enabled and all keys are filled in.
		 *
		 * @since 4.2.0
		 *
		 * @return bool
		 */
		protected function is_ready(): bool {
			$enabled = 'yes' === $this->settings['enabled'] ?? '';

			return $enabled && ! empty( $this->secret_key ) && ! empty( $this->publishable_key ) && ! empty( $this->webhook_secret );
		}

		/**
		 * Configures settings.
		 *
		 * @since 4.2.0
		 *
		 * @return void
		 */
		protected function configure(): void {
			$this->settings = LearnDash_Settings_Section::get_section_settings_all( 'LearnDash_Settings_Section_Razorpay' );

			$setting_suffix = $this->is_test_mode() ? 'test' : 'live';

			$this->secret_key           = $this->settings[ "secret_key_$setting_suffix" ];
			$this->publishable_key      = $this->settings[ "publishable_key_$setting_suffix" ];
			$this->webhook_secret       = $this->settings[ "webhook_secret_$setting_suffix" ];
			$this->customer_id_meta_key = $this->is_test_mode() ? self::META_KEY_CUSTOMER_ID_TEST : self::META_KEY_CUSTOMER_ID_LIVE;
			$this->plans_meta_key       = $this->is_test_mode() ? self::META_KEY_PLANS_TEST : self::META_KEY_PLANS_LIVE;
			$this->currency             = mb_strtoupper( learndash_get_currency_code() );

			if ( ! class_exists( 'Razorpay\Api\Api' ) ) {
				require_once LEARNDASH_LMS_LIBRARY_DIR . '/razorpay-php/Razorpay.php';
			}

			if ( ! empty( $this->secret_key ) ) {
				$this->api = new Api( $this->publishable_key, $this->secret_key );
			}
		}

		/**
		 * Checks if it's a test mode.
		 *
		 * @since 4.2.0
		 *
		 * @return bool
		 */
		protected function is_test_mode(): bool {
			return isset( $this->settings['test_mode'] ) && 1 == $this->settings['test_mode'];
		}

		/**
		 * Returns Razorpay options.
		 *
		 * @since 4.2.0
		 *
		 * @throws Exception Throws if not valid arguments passed.
		 *
		 * @return array
		 */
		protected function map_payment_options(): array {
			if ( learndash_is_course_post( $this->post ) ) {
				$formatted_price_info = learndash_get_course_price( $this->post->ID, $this->user->ID );
			} elseif ( learndash_is_group_post( $this->post ) ) {
				$formatted_price_info = learndash_get_group_price( $this->post->ID, $this->user->ID );
			} else {
				throw new Exception( __( 'Cheating?', 'learndash' ) );
			}

			/** This filter is documented in includes/payments/class-learndash-stripe-connect-checkout-integration.php */
			$price = apply_filters(
				'learndash_get_price_by_coupon',
				learndash_get_price_as_float( $formatted_price_info['price'] ),
				$this->post->ID,
				$this->user->ID
			);

			$price = intval( $price * 100 );

			if ( $price < 1 ) {
				throw new Exception( __( 'The minimum Price value is 1.', 'learndash' ) );
			}

			// Create options.

			$customer_id = $this->user->ID > 0 ? $this->find_or_create_customer_id() : null;

			$options = array_filter(
				array(
					'key'         => $this->publishable_key,
					'name'        => get_bloginfo( 'name' ),
					'description' => $this->post->post_title,
					'image'       => get_the_post_thumbnail_url( $this->post->ID, 'medium' ),
					'notes'       => array(
						'post_id' => $this->post->ID,
						'user_id' => $this->user->ID,
					),
				)
			);

			if ( LEARNDASH_PRICE_TYPE_SUBSCRIBE === $formatted_price_info['type'] ) {
				$options['subscription_id'] = $this->create_subscription_id( $price );
			} else {
				if ( ! is_null( $customer_id ) ) {
					$options['customer_id'] = $customer_id;
				}
				$options['order_id'] = $this->create_order_id( $price );
			}

			/**
			 * Filters Razorpay payment options before creation.
			 *
			 * @since 4.2.0
			 *
			 * @param array $options Razorpay payment options.
			 *
			 * @return array Razorpay payment options.
			 */
			return apply_filters( 'learndash_payment_options_razorpay', $options );
		}

		/**
		 * Returns modified payment button.
		 *
		 * @since 4.2.0
		 *
		 * @param string $default_button Learndash default payment button.
		 * @param array  $params         Parameters.
		 *
		 * @return string
		 */
		public function add_payment_button( string $default_button, array $params ): string {
			if ( empty( $params['price'] ) ) {
				return $default_button;
			}

			$this->post = $params['post'] ?? get_post();

			if ( ! is_a( $this->post, WP_Post::class ) ) {
				return $default_button;
			}

			return $default_button . $this->map_payment_button( $default_button );
		}

		/**
		 * Processes a webhook.
		 *
		 * @since 4.2.0
		 *
		 * @return void
		 */
		public function process_webhook(): void {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! isset( $_GET['learndash-integration'] ) || 'razorpay' !== $_GET['learndash-integration'] ) {
				return;
			}

			if ( ! isset( $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ) ) {
				exit();
			}

			$input = @file_get_contents( 'php://input' ); // phpcs:ignore

			if ( json_last_error() !== 0 ) {
				exit();
			}

			if ( empty( $input ) ) {
				exit();
			}

			$event = json_decode( $input, true );

			try {
				$this->api->utility->verifyWebhookSignature(
					$input,
					sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ) ),
					$this->webhook_secret
				);
			} catch ( SignatureVerificationError $e ) {
				exit();
			}

			// Set up a post and a user.

			$entity = $this->get_main_entity_from_event( $event );

			if ( empty( $entity ) || empty( $entity['notes']['is_learndash'] ) || empty( $entity['notes']['post_id'] ) ) {
				exit();
			}

			$this->post = get_post( $entity['notes']['post_id'] );
			$this->user = $this->find_or_create_user( $event );

			if ( is_null( $this->post ) || is_null( $this->user ) ) {
				exit();
			}

			/**
			 * Filters whether to process the Razorpay webhook or not.
			 *
			 * @since 4.2.0
			 *
			 * @param bool  $process To process or not. True by default.
			 * @param array $event   Decoded Razorpay event.
			 *
			 * @return bool
			 */
			if ( ! apply_filters( 'learndash_process_webhook_razorpay', true, $event ) ) {
				exit();
			}

			// Process an event.

			switch ( $event['event'] ) {
				case self::EVENT_ORDER_PAID:
				case self::EVENT_SUBSCRIPTION_AUTHENTICATED:
					$this->add_post_access( $this->post->ID, $this->user->ID );
					$this->record_transaction( $event );
					break;

				case self::EVENT_SUBSCRIPTION_ACTIVATED:
				case self::EVENT_SUBSCRIPTION_RESUMED:
					$this->add_post_access( $this->post->ID, $this->user->ID );
					break;

				case self::EVENT_SUBSCRIPTION_COMPLETED:
				case self::EVENT_SUBSCRIPTION_PENDING:
				case self::EVENT_SUBSCRIPTION_HALTED:
				case self::EVENT_SUBSCRIPTION_CANCELLED:
				case self::EVENT_SUBSCRIPTION_PAUSED:
					$this->remove_post_access( $this->post->ID, $this->user->ID );
					break;

				default:
					exit;
			}
		}

		/**
		 * Finds or creates a user.
		 *
		 * @since 4.2.0
		 *
		 * @param array $event Event.
		 *
		 * @return WP_User|null
		 */
		public function find_or_create_user( array $event ): ?WP_User {
			$entity  = $this->get_main_entity_from_event( $event );
			$user_id = (int) $entity['notes']['user_id'];
			$payment = $this->get_payment_entity_from_event( $event );

			if ( $user_id > 0 ) {
				$user = get_user_by( 'ID', $user_id );
			} elseif ( ! empty( $payment ) ) {
				$user = get_user_by( 'email', $event['payload']['payment']['entity']['email'] );
			} else {
				return null;
			}

			if ( ! is_a( $user, WP_User::class ) ) {
				if ( empty( $payment ) ) {
					return null;
				}

				$user = $this->create_user( $payment['email'] );
			}

			$attached_customer_id = get_post_meta( $user->ID, $this->customer_id_meta_key, true );

			if ( empty( $attached_customer_id ) && ! empty( $entity['customer_id'] ) ) {
				update_user_meta( $user->ID, $this->customer_id_meta_key, $entity['customer_id'] );
			}

			return $user;
		}

		/**
		 * Creates a user.
		 *
		 * @since 4.2.0
		 *
		 * @param string $email Email.
		 *
		 * @return WP_User
		 */
		public function create_user( string $email ): WP_User {
			$username = $email;
			$password = wp_generate_password( 18 );

			if ( username_exists( $username ) ) {
				$username = $username . '-' . uniqid();
			}

			$user_id = wp_create_user( $username, $password, $email );

			global $wp_version;

			if ( version_compare( $wp_version, '4.3.0', '<' ) ) {
				wp_new_user_notification( $user_id, $password ); // phpcs:ignore
			} elseif ( version_compare( $wp_version, '4.3.0', '==' ) ) {
				wp_new_user_notification( $user_id, 'both' ); // phpcs:ignore
			} elseif ( version_compare( $wp_version, '4.3.1', '>=' ) ) {
				wp_new_user_notification( $user_id, null, 'both' );
			}

			/**
			 * Fires after a user is created with Razorpay.
			 *
			 * @since 4.2.0
			 *
			 * @param int $user_id User ID.
			 */
			do_action( 'learndash_user_created_with_razorpay', $user_id );

			return get_user_by( 'ID', $user_id );
		}

		/**
		 * Returns subscription/order entity from the event.
		 *
		 * @since 4.2.0
		 *
		 * @param array $event Event.
		 *
		 * @return array
		 */
		protected function get_main_entity_from_event( array $event ): array {
			$entity_key = $this->event_contains_subscription( $event ) ? 'subscription' : 'order';

			if ( ! isset( $event['payload'][ $entity_key ] ) ) {
				return array();
			}

			return $event['payload'][ $entity_key ]['entity'];
		}

		/**
		 * Returns payment from the event.
		 *
		 * @since 4.2.0
		 *
		 * @param array $event Event.
		 *
		 * @return array
		 */
		protected function get_payment_entity_from_event( array $event ): array {
			if ( ! in_array( 'payment', $event['contains'], true ) ) {
				return array();
			};

			return $event['payload']['payment']['entity'];
		}

		/**
		 * Returns true if it's a subscription event.
		 *
		 * @since 4.2.0
		 *
		 * @param array $event Event.
		 *
		 * @return bool
		 */
		protected function event_contains_subscription( array $event ): bool {
			return in_array( 'subscription', $event['contains'], true );
		}

		/**
		 * Records a payment transaction.
		 *
		 * @since 4.2.0
		 *
		 * @param array $event Event data.
		 */
		public function record_transaction( array $event ): void {
			learndash_transaction_create(
				$this->map_transaction_meta( $event ),
				$this->post,
				$this->user
			);
			learndash_send_purchase_success_email( absint( $this->user->ID ), absint( $this->post->ID ) );
		}

		/**
		 * Maps payment button markup.
		 *
		 * @since 4.2.0
		 *
		 * @param string $default_button Default button.
		 *
		 * @return string
		 */
		protected function map_payment_button( string $default_button ): string {
			$button_label = empty( $default_button )
				? learndash_get_payment_button_label( $this->post )
				: __( 'Use Razorpay', 'learndash' );

			/**
			 * Filters Razorpay payment button label.
			 *
			 * @since 4.2.0
			 *
			 * @param string $button_label Razorpay button label.
			 *
			 * @return string Razorpay button label.
			 */
			$button_label = apply_filters( 'learndash_button_label_razorpay', $button_label );

			ob_start();
			?>
			<div class="learndash_checkout_button">
				<form
					class="learndash-razorpay-form"
					method="post"
					data-action="<?php echo esc_attr( self::AJAX_ACTION_SETUP_OPTIONS ); ?>"
					data-nonce="<?php echo esc_attr( wp_create_nonce( $this->get_nonce_name() ) ); ?>"
					data-post_id="<?php echo esc_attr( $this->post->ID ); ?>"
				>
					<input class="btn-join button" type="submit" value="<?php echo esc_attr( $button_label ); ?>">
				</form>
			</div>
			<?php
			global $learndash_razorpay_loaded;
			$learndash_razorpay_loaded = true;

			return ob_get_clean();
		}

		/**
		 * Returns nonce name.
		 *
		 * @since 4.2.0
		 *
		 * @return string
		 */
		protected function get_nonce_name(): string {
			return 'razorpay-nonce-' . $this->post->ID;
		}

		/**
		 * Gets or creates a customer. Returns customer id.
		 *
		 * @since 4.2.0
		 *
		 * @throws Exception If customer is not found or created.
		 *
		 * @return string
		 */
		protected function find_or_create_customer_id(): string {
			$customer_id = get_user_meta( $this->user->ID, $this->customer_id_meta_key, true );

			if ( ! empty( $customer_id ) ) {
				return $customer_id;
			}

			try {
				$customer = $this->api->customer->create(
					array(
						'name'  => $this->user->display_name,
						'email' => $this->user->user_email,
						'notes' => array(
							'user_id' => $this->user->ID,
						),
					)
				);
			} catch ( Exception $e ) {
				$customer = null;

				$skip  = 0;
				$count = 100;

				while ( is_null( $customer ) ) {
					$customers = $this->api->customer->all(
						array(
							'count' => $count,
							'skip'  => $skip,
						)
					);

					if ( 0 === $customers->count ) {
						break;
					}

					foreach ( $customers->items as $customer_entity ) {
						if (
							$customer_entity->email === $this->user->user_email &&
							$customer_entity->name === $this->user->display_name
						) {
							$customer = $customer_entity;
							break;
						}
					}

					$skip += $count;
				}

				if ( is_null( $customer ) ) {
					throw new Exception(
						__( 'Razorpay customer creation failed. And an existing related Razorpay customer was not found.', 'learndash' )
					);
				}
			}

			update_user_meta( $this->user->ID, $this->customer_id_meta_key, $customer->id );

			return $customer->id;
		}

		/**
		 * Creates an order.
		 *
		 * @since 4.2.0
		 *
		 * @param int $amount Amount.
		 *
		 * @return string
		 */
		protected function create_order_id( int $amount ): string {
			$order = $this->api->order->create(
				array(
					'amount'          => $amount,
					'currency'        => $this->currency,
					'notes'           => array(
						'is_learndash' => true,
						'post_id'      => $this->post->ID,
						'user_id'      => $this->user->ID,
						'pricing'      => wp_json_encode(
							array(
								'currency' => $this->currency,
								'price'    => number_format( $amount / 100, 2, '.', '' ),
							)
						),
					),
					'partial_payment' => false,
				)
			);

			return $order->id;
		}

		/**
		 * Creates a subscription.
		 *
		 * @since 4.2.0
		 *
		 * @throws Exception Exception.
		 *
		 * @param int $price Price.
		 *
		 * @return string
		 */
		protected function create_subscription_id( int $price ): string {
			if ( learndash_is_course_post( $this->post ) ) {
				// Subscription.
				$interval = learndash_get_setting( $this->post->ID, 'course_price_billing_p3' );
				$period   = learndash_get_setting( $this->post->ID, 'course_price_billing_t3' );
				$repeats  = learndash_get_setting( $this->post->ID, 'course_no_of_cycles' );
				// Trial.
				$trial_price    = learndash_get_setting( $this->post->ID, 'course_trial_price' );
				$trial_interval = learndash_get_setting( $this->post->ID, 'course_trial_duration_p1' );
				$trial_period   = learndash_get_setting( $this->post->ID, 'course_trial_duration_t1' );
			} elseif ( learndash_is_group_post( $this->post ) ) {
				// Subscription.
				$interval = learndash_get_setting( $this->post->ID, 'group_price_billing_p3' );
				$period   = learndash_get_setting( $this->post->ID, 'group_price_billing_t3' );
				$repeats  = learndash_get_setting( $this->post->ID, 'post_no_of_cycles' );
				// Trial.
				$trial_price    = learndash_get_setting( $this->post->ID, 'group_trial_price' );
				$trial_interval = learndash_get_setting( $this->post->ID, 'group_trial_duration_p1' );
				$trial_period   = learndash_get_setting( $this->post->ID, 'group_trial_duration_t1' );
			} else {
				throw new Exception( __( 'Cheating?', 'learndash' ) );
			}

			$repeats  = absint( $repeats );
			$interval = absint( $interval );

			if ( 0 === $repeats ) {
				throw new Exception( __( 'The minimum Recurring Times value is 1.', 'learndash' ) );
			} elseif ( empty( $period ) ) {
				throw new Exception( __( 'The Billing Cycle Interval value must be set.', 'learndash' ) );
			} elseif ( 0 === $interval ) {
				throw new Exception( __( 'The minimum Billing Cycle value is 1.', 'learndash' ) );
			} elseif ( 'D' === $period && $interval < 7 ) {
				throw new Exception( __( 'For daily plans, the minimum Billing Cycle value is 7.', 'learndash' ) );
			}

			$subscription_options = array(
				'plan_id'     => $this->find_or_create_plan_id( $price, $interval, $period ),
				'total_count' => $repeats,
				'notes'       => array(
					'is_learndash'   => true,
					'post_id'        => $this->post->ID,
					'user_id'        => $this->user->ID,
					'pricing'        => array(
						'currency'           => $this->currency,
						'price'              => number_format( $price / 100, 2, '.', '' ),
						'pricing_billing_p3' => $interval,
						'pricing_billing_t3' => $period,
						'no_of_cycles'       => $repeats,
					),
					'has_trial'      => false,
					'has_free_trial' => false,
				),
			);

			// Setup trial period.

			$trial_interval = absint( $trial_interval );

			if ( ! empty( $trial_interval ) && ! empty( $trial_period ) ) {
				$subscription_options['start_at'] = time() + $trial_interval * self::TRIAL_PERIOD_DURATION_HASH[ $trial_period ];

				$subscription_options['notes']['has_trial'] = true;

				// Setup trial price.

				$subscription_options['notes']['pricing']['trial_price']       = number_format( learndash_get_price_as_float( $trial_price ), 2, '.', '' );
				$subscription_options['notes']['pricing']['trial_duration_p1'] = $trial_interval;
				$subscription_options['notes']['pricing']['trial_duration_t1'] = $trial_period;

				$trial_price = intval( learndash_get_price_as_float( $trial_price ) * 100 );

				if ( $trial_price >= 1 ) {
					$subscription_options['addons'] = array(
						array(
							'item' => array(
								'name'     => __( 'Trial', 'learndash' ),
								'amount'   => $trial_price,
								'currency' => $this->currency,
							),
						),
					);
				} else {
					$subscription_options['notes']['has_free_trial'] = true;
				}
			}

			$subscription_options['notes']['pricing'] = wp_json_encode( $subscription_options['notes']['pricing'] );

			return $this->api->subscription->create( $subscription_options )->id;
		}

		/**
		 * Creates a plan or returns an existing plan id.
		 *
		 * @since 4.2.0
		 *
		 * @param int    $amount   Amount.
		 * @param int    $interval Interval.
		 * @param string $period   Period.
		 *
		 * @return string Plan ID.
		 */
		protected function find_or_create_plan_id( int $amount, int $interval, string $period ): string {
			$plan_options = array(
				'period'   => self::PERIOD_HASH[ $period ],
				'interval' => $interval,
				'item'     => array(
					'name'     => $this->post->post_title,
					'amount'   => $amount,
					'currency' => $this->currency,
				),
				'notes'    => array(
					'post_id' => $this->post->ID,
				),
			);

			array_multisort( $plan_options );

			$plan_options_hash = md5( wp_json_encode( $plan_options ) );

			$existing_plans = get_post_meta( $this->post->ID, $this->plans_meta_key, true );
			if ( ! is_array( $existing_plans ) ) {
				$existing_plans = array();
			}

			// If we already have an attached plan with the same options hash, we don't need a new plan to be created.
			if ( is_array( $existing_plans ) && isset( $existing_plans[ $plan_options_hash ] ) ) {
				return $existing_plans[ $plan_options_hash ];
			}

			return $this->create_plan( $plan_options, $plan_options_hash, $existing_plans );
		}

		/**
		 * Creates a plan.
		 *
		 * @since 4.2.0
		 *
		 * @param array  $plan_options      Plan options.
		 * @param string $plan_options_hash Plan options hash.
		 * @param array  $existing_plans    Existing plans.
		 *
		 * @return string Plan ID.
		 */
		protected function create_plan( array $plan_options, string $plan_options_hash, array $existing_plans ): string {
			$plan_id = $this->api->plan->create( $plan_options )->id;

			$existing_plans[ $plan_options_hash ] = $plan_id;

			update_post_meta( $this->post->ID, $this->plans_meta_key, $existing_plans );

			return $plan_id;
		}

		/**
		 * Maps transaction meta fields.
		 *
		 * @since 4.2.0
		 *
		 * @param array $event Event.
		 *
		 * @return array
		 */
		protected function map_transaction_meta( array $event ): array {
			$is_subscription_event = $this->event_contains_subscription( $event );
			$entity                = $this->get_main_entity_from_event( $event );
			$payment               = $this->get_payment_entity_from_event( $event );

			$transaction_meta = array(
				'ld_payment_processor' => self::PAYMENT_PROCESSOR,
				'price_type'           => $is_subscription_event ? LEARNDASH_PRICE_TYPE_SUBSCRIBE : LEARNDASH_PRICE_TYPE_PAYNOW,
				'pricing'              => json_decode( $entity['notes']['pricing'], true ),
				'razorpay_event'       => $event,
			);

			if ( $is_subscription_event ) {
				$transaction_meta['has_trial']      = $entity['notes']['has_trial'];
				$transaction_meta['has_free_trial'] = $entity['notes']['has_free_trial'];

				if ( ! $transaction_meta['has_free_trial'] ) {
					$invoices = $this->api->invoice->all(
						array(
							'subscription_id' => $entity['id'],
						)
					);

					if ( $invoices->count > 0 ) {
						$price_key = $entity['notes']['has_trial'] ? 'trial_price' : 'price';

						$transaction_meta['pricing'][ $price_key ] = number_format(
							$invoices->items[0]->amount / 100,
							2,
							'.',
							''
						);
					}
				}
			} elseif ( self::EVENT_ORDER_PAID === $event['event'] ) {
				$transaction_meta['pricing']['price'] = number_format( $payment['amount'] / 100, 2, '.', '' );
			}

			return $transaction_meta;
		}
	}

	new LearnDash_Razorpay_Integration();
}
