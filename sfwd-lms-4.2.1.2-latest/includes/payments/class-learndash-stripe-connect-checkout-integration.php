<?php
/**
 * This class handles Stripe Connect integration.
 *
 * @since   4.0.0
 * @package LearnDash
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'LearnDash_Stripe_Connect_Checkout_Integration' ) && class_exists( 'LearnDash_Payment_Gateway_Integration' ) ) {
	/**
	 * Stripe Connect checkout integration class.
	 */
	class LearnDash_Stripe_Connect_Checkout_Integration extends LearnDash_Payment_Gateway_Integration {
		const PAYMENT_PROCESSOR = 'stripe';

		/**
		 * Plugin options.
		 *
		 * @var array
		 */
		protected $options;

		/**
		 * Stripe secret key.
		 *
		 * @var string
		 */
		protected $secret_key;

		/**
		 * Stripe publishable key.
		 *
		 * @var string
		 */
		protected $publishable_key;

		/**
		 * Stripe connected account id.
		 *
		 * @var string
		 */
		protected $account_id;

		/**
		 * Stripe API client.
		 *
		 * @var \Stripe\StripeClient
		 */
		protected $stripe;

		/**
		 * Variable to hold the Course object we are working with.
		 *
		 * @var object
		 */
		protected $course;

		/**
		 * Stripe checkout session id.
		 *
		 * @var string
		 */
		protected $session_id;

		/**
		 * Stripe customer id meta key name.
		 *
		 * @var string
		 */
		protected $stripe_customer_id_meta_key;

		/**
		 * Construction.
		 */
		public function __construct() {
			$this->configure();

			if ( ! $this->is_ready() ) {
				return;
			}

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_filter( 'learndash_payment_button', array( $this, 'payment_button' ), 10, 2 );
			add_action( 'wp_loaded', array( $this, 'process_webhook' ) );
			add_action( 'wp_footer', array( $this, 'output_transaction_message' ) );
			add_action( 'wp_ajax_nopriv_ld_stripe_connect_init_checkout', array( $this, 'ajax_init_checkout' ) );
			add_action( 'wp_ajax_ld_stripe_connect_init_checkout', array( $this, 'ajax_init_checkout' ) );

			$this->hide_plugin_stripe_button();
		}

		/**
		 * Sets Stripe customer id meta key.
		 */
		protected function set_stripe_customer_id_meta_key(): void {
			$this->stripe_customer_id_meta_key = $this->is_test_mode()
				? LearnDash_Settings_Section_Stripe_Connect::STRIPE_CUSTOMER_ID_META_KEY_TEST
				: LearnDash_Settings_Section_Stripe_Connect::STRIPE_CUSTOMER_ID_META_KEY;
		}

		/**
		 * Configs Stripe API key.
		 */
		protected function configure() {
			$this->options = LearnDash_Settings_Section::get_section_settings_all( 'LearnDash_Settings_Section_Stripe_Connect' );

			$this->set_stripe_customer_id_meta_key();

			$this->secret_key      = $this->map_secret_key();
			$this->publishable_key = $this->map_publishable_key();
			$this->account_id      = $this->options['account_id'] ?? '';

			if ( ! class_exists( 'Stripe\Stripe' ) ) {
				require_once LEARNDASH_LMS_LIBRARY_DIR . '/stripe-php/init.php';
			}

			if ( ! empty( $this->secret_key ) ) {
				\Stripe\Stripe::setApiKey( $this->secret_key );
				$this->stripe = new \Stripe\StripeClient( $this->secret_key );
			}

		}

		/**
		 * Checks if it's a test mode.
		 *
		 * @return bool
		 */
		public function is_test_mode(): bool {
			return isset( $this->options['test_mode'] ) && 1 == $this->options['test_mode'];
		}

		/**
		 * Get course button args
		 *
		 * @param int|null $course_id Course ID.
		 *
		 * @return array Course args
		 */
		public function get_course_args( ?int $course_id = null ): array {
			$course = ! isset( $course_id ) ? $this->course : get_post( $course_id );

			if ( ! $course ) {
				return array();
			}

			$user_id    = get_current_user_id();
			$user_email = null;

			if ( 0 != $user_id ) {
				$user       = get_userdata( $user_id );
				$user_email = ( '' != $user->user_email ) ? $user->user_email : '';
			}

			if ( learndash_get_post_type_slug( 'course' ) === $course->post_type ) {
				$course_price                = learndash_get_course_price( $course->ID, $user_id )['price'];
				$course_price_type           = learndash_get_setting( $course->ID, 'course_price_type' );
				$course_plan_id              = 'learndash-course-' . $course->ID;
				$course_interval_count       = get_post_meta( $course->ID, 'course_price_billing_p3', true );
				$course_interval             = get_post_meta( $course->ID, 'course_price_billing_t3', true );
				$course_recurring_times      = learndash_get_setting( $course->ID, 'course_no_of_cycles' );
				$course_trial_price          = learndash_get_setting( $course->ID, 'course_trial_price' );
				$course_trial_interval       = learndash_get_setting( $course->ID, 'course_trial_duration_t1' );
				$course_trial_interval_count = learndash_get_setting( $course->ID, 'course_trial_duration_p1' );
			} elseif ( learndash_get_post_type_slug( 'group' ) === $course->post_type ) {
				$course_price                = learndash_get_group_price( $course->ID, $user_id )['price'];
				$course_price_type           = learndash_get_setting( $course->ID, 'group_price_type' );
				$course_plan_id              = 'learndash-group-' . $course->ID;
				$course_interval_count       = get_post_meta( $course->ID, 'group_price_billing_p3', true );
				$course_interval             = get_post_meta( $course->ID, 'group_price_billing_t3', true );
				$course_recurring_times      = learndash_get_setting( $course->ID, 'post_no_of_cycles' );
				$course_trial_price          = learndash_get_setting( $course->ID, 'group_trial_price' );
				$course_trial_interval       = learndash_get_setting( $course->ID, 'group_trial_duration_t1' );
				$course_trial_interval_count = learndash_get_setting( $course->ID, 'group_trial_duration_p1' );
			} else {
				return array();
			}

			switch ( $course_interval ) {
				case 'D':
					$course_interval = 'day';
					break;

				case 'W':
					$course_interval = 'week';
					break;

				case 'M':
					$course_interval = 'month';
					break;

				case 'Y':
					$course_interval = 'year';
					break;
			}

			switch ( $course_trial_interval ) {
				case 'D':
					$course_trial_interval = 'day';
					break;

				case 'W':
					$course_trial_interval = 'week';
					break;

				case 'M':
					$course_trial_interval = 'month';
					break;

				case 'Y':
					$course_trial_interval = 'year';
					break;
			}

			$currency_code = learndash_get_currency_code();
			$currency      = strtolower( $currency_code );
			$course_image  = get_the_post_thumbnail_url( $course->ID, 'medium' );
			$course_name   = $course->post_title;
			$course_id     = $course->ID;

			$course_price = learndash_get_price_as_float( $course_price );

			if ( ! empty( $course_trial_price ) ) {
				$course_trial_price = learndash_get_price_as_float( $course_trial_price );
			}

			/**
			 * Filters course/group price.
			 *
			 * @since 4.1.0
			 *
			 * @param float    $price   Course/Group Price.
			 * @param int      $post_id Course/Group ID.
			 * @param int|null $user_id User ID.
			 */
			$course_price = apply_filters( 'learndash_get_price_by_coupon', $course_price, $course->ID, $user_id );

			if ( ! $this->is_zero_decimal_currency( $currency_code ) ) {
				$course_price = $course_price * 100;

				if ( ! empty( $course_trial_price ) ) {
					$course_trial_price = $course_trial_price * 100;
				}
			}

			$args = compact(
				'user_id',
				'user_email',
				'course_id',
				'currency',
				'course_image',
				'course_name',
				'course_plan_id',
				'course_price',
				'course_price_type',
				'course_interval',
				'course_interval_count',
				'course_recurring_times',
				'course_trial_price',
				'course_trial_interval',
				'course_trial_interval_count'
			);

			return $args;
		}

		/**
		 * Enqueues scripts.
		 *
		 * @return void
		 */
		public function enqueue_scripts(): void {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'stripe-connect', 'https://js.stripe.com/v3/', array(), false, true ); // phpcs:ignore

			wp_enqueue_script( 'js.cookie', 'https://cdn.jsdelivr.net/npm/js-cookie@3.0.0-rc.1/dist/js.cookie.min.js', array(), false, false ); // phpcs:ignore
			wp_add_inline_script( 'js.cookie', 'var LD_Cookies = Cookies.noConflict();' );
		}

		/**
		 * Checks if enabled and all keys are filled in.
		 *
		 * @return bool
		 */
		private function is_ready(): bool {
			$enabled = 'yes' === $this->options['enabled'] ?? '';

			return $enabled &&
				! empty( $this->account_id ) &&
				! empty( $this->secret_key ) &&
				! empty( $this->publishable_key );
		}

		/**
		 * Output modified payment button
		 *
		 * @param string     $default_button Learndash default payment button.
		 * @param array|null $params Button parameters.
		 *
		 * @return string Modified button.
		 */
		public function payment_button( string $default_button, ?array $params = null ): string {
			if ( empty( $params['price'] ) ) {
				return $default_button;
			}

			$this->course = $params['post'] ?? get_post( get_the_ID() );

			$stripe_button = $this->stripe_button( $default_button );

			if ( empty( $stripe_button ) ) {
				return $default_button;
			}

			return $default_button . $stripe_button;
		}

		/**
		 * Stripe payment button
		 *
		 * @param string $default_button Default button.
		 *
		 * @return string Payment button.
		 */
		public function stripe_button( $default_button ) {
			extract( $this->get_course_args() ); // phpcs:ignore

			ob_start();

			$stripe_button_text = empty( $default_button )
				? learndash_get_payment_button_label( $course_id )
				: __( 'Use Credit Card', 'learndash' );

			/**
			 * Filters Stripe payment button text.
			 *
			 * @since 4.0.0
			 *
			 * @param string $stripe_button_text Stripe button text.
			 *
			 * @return string Stripe button text.
			 */
			$stripe_button_text = apply_filters( 'learndash_stripe_purchase_button_text', $stripe_button_text );

			$stripe_button  = '<div class="learndash_checkout_button learndash_stripe_button">';
			$stripe_button .= '<form class="learndash-stripe-checkout" name="" action="" method="post">';
			$stripe_button .= '<input type="hidden" name="action" value="ld_stripe_connect_init_checkout" />';
			$stripe_button .= '<input type="hidden" name="stripe_email" value="' . esc_attr( $user_email ) . '" />';
			$stripe_button .= '<input type="hidden" name="stripe_user_id" value="' . esc_attr( $user_id ) . '" />';
			$stripe_button .= '<input type="hidden" name="stripe_course_id" value="' . esc_attr( $course_id ) . '" />';
			$stripe_button .= '<input type="hidden" name="stripe_plan_id" value="' . esc_attr( $course_plan_id ) . '" />';
			$stripe_button .= '<input type="hidden" name="stripe_name" value="' . esc_attr( $course_name ) . '" />';
			$stripe_button .= '<input type="hidden" name="stripe_currency" value="' . esc_attr( $currency ) . '" />';
			$stripe_button .= '<input type="hidden" name="stripe_price" value="' . esc_attr( $course_price ) . '" />';
			$stripe_button .= '<input type="hidden" name="stripe_price_type" value="' . esc_attr( $course_price_type ) . '" />';

			if ( LEARNDASH_PRICE_TYPE_SUBSCRIBE == $course_price_type ) {
				$stripe_button .= '<input type="hidden" name="pricing_billing_p3" value="' . esc_attr( $course_interval_count ) . '" />';
				$stripe_button .= '<input type="hidden" name="pricing_billing_t3" value="' . esc_attr( $course_interval ) . '" />';

				// Trial subscription.
				$stripe_button .= '<input type="hidden" name="no_of_cycles" value="' . esc_attr( $course_recurring_times ) . '" />';
				$stripe_button .= '<input type="hidden" name="trial_price" value="' . esc_attr( $course_trial_price ) . '" />';
				$stripe_button .= '<input type="hidden" name="trial_duration_t1" value="' . esc_attr( $course_trial_interval ) . '" />';
				$stripe_button .= '<input type="hidden" name="trial_duration_p1" value="' . esc_attr( $course_trial_interval_count ) . '" />';
			}

			$stripe_button_nonce = wp_create_nonce( 'stripe-connect-nonce-' . $course_id . $course_price_type );
			$stripe_button      .= '<input type="hidden" name="stripe_connect_nonce" value="' . esc_attr( $stripe_button_nonce ) . '" />';

			$stripe_button .= '<input class="learndash-stripe-checkout-button btn-join button" type="submit" value="' . esc_attr( $stripe_button_text ) . '">';
			$stripe_button .= '</form>';
			$stripe_button .= '</div>';

			global $learndash_stripe_connect_loaded;
			if ( ! $learndash_stripe_connect_loaded ) {
				$this->button_scripts();
				$learndash_stripe_connect_loaded = true;
			}

			$stripe_button .= ob_get_clean();

			return $stripe_button;
		}

		/**
		 * Integration button scripts
		 *
		 * @return void
		 */
		public function button_scripts() {
			?>
			<script>
				"use strict";

				function ownKeys(object, enumerableOnly) {
					var keys = Object.keys(object);
					if (Object.getOwnPropertySymbols) {
						var symbols = Object.getOwnPropertySymbols(object);
						if (enumerableOnly) symbols = symbols.filter(function (sym) {
							return Object.getOwnPropertyDescriptor(object, sym).enumerable;
						});
						keys.push.apply(keys, symbols);
					}
					return keys;
				}

				function _objectSpread(target) {
					for (var i = 1; i < arguments.length; i++) {
						var source = arguments[i] != null ? arguments[i] : {};
						if (i % 2) {
							ownKeys(Object(source), true).forEach(function (key) {
								_defineProperty(target, key, source[key]);
							});
						} else if (Object.getOwnPropertyDescriptors) {
							Object.defineProperties(target, Object.getOwnPropertyDescriptors(source));
						} else {
							ownKeys(Object(source)).forEach(function (key) {
								Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key));
							});
						}
					}
					return target;
				}

				function _defineProperty(obj, key, value) {
					if (key in obj) {
						Object.defineProperty(obj, key, {
							value: value,
							enumerable: true,
							configurable: true,
							writable: true
						});
					} else {
						obj[key] = value;
					}
					return obj;
				}

				jQuery(document).ready(function ($) {
					var stripe = Stripe( '<?php echo esc_attr( $this->publishable_key ); ?>', {
						'stripeAccount': '<?php echo esc_attr( $this->account_id ); ?>'
					} );

					$(document).on('submit', '.learndash-stripe-checkout', function (e) {
						e.preventDefault();
						var inputs = $(this).serializeArray();
						inputs = inputs.reduce(function (new_inputs, value, index, inputs) {
							new_inputs[value.name] = value.value;
							return new_inputs;
						}, {});

						var ld_stripe_session_id = LD_Cookies.get('ld_stripe_connect_session_id_' + inputs.stripe_course_id);

						if (typeof ld_stripe_session_id != 'undefined') {
							stripe.redirectToCheckout({
								sessionId: ld_stripe_session_id
							}).then(function (result) {
								if (result.error.length > 0) {
									alert(result.error);
								}
							});
						} else {
							$('.checkout-dropdown-button').hide();
							$(this).closest('.learndash_checkout_buttons').addClass('ld-loading');
							$('head').append('<style class="ld-stripe-css">' + '.ld-loading::after { background: none !important; }' + '.ld-loading::before { width: 30px !important; height: 30px !important; left: 53% !important; top: 62% !important; }' + '</style>');
							$('.learndash_checkout_buttons .learndash_checkout_button').css({
								backgroundColor: 'rgba(182, 182, 182, 0.1)'
							});

							// Set Stripe session
							$.ajax({
								url: <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
								type: 'POST',
								dataType: 'json',
								data: _objectSpread({}, inputs)
							}).done(function (response) {
								if (response.status === 'success') {
									LD_Cookies.set('ld_stripe_connect_session_id_' + inputs.stripe_course_id, response.session_id); // If session is created

									stripe.redirectToCheckout({
										sessionId: response.session_id
									}).then(function (result) {
										if (result.error.length > 0) {
											alert(result.error);
										}
									});
								} else {
									alert(response.payload);
								}

								$('.learndash_checkout_buttons').removeClass('ld-loading');
								$('style.ld-stripe-css').remove();
								$('.learndash_checkout_buttons .learndash_checkout_button').css({
									backgroundColor: ''
								});
							});
						}
					});
				});
			</script>
			<?php
		}

		/**
		 * Process Stripe new checkout
		 *
		 * @return void
		 * @throws \Stripe\Exception\ApiErrorException API Error.
		 */
		public function process_webhook(): void {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! isset( $_GET['learndash-integration'] ) || 'stripe-connect' !== $_GET['learndash-integration'] ) {
				return;
			}

			$payload = @file_get_contents( 'php://input' ); // phpcs:ignore

			if ( empty( $payload ) ) {
				exit();
			}

			$decoded_payload = json_decode( $payload );

			/**
			 * Filters whether to process the Stripe webhook or not.
			 *
			 * True by default.
			 *
			 * @since 4.0.0
			 *
			 * @param boolean $process         To process or not.
			 * @param object  $decoded_payload Stripe decoded json payload.
			 *
			 * @return boolean True to process this Stripe webhook, otherwise false.
			 */
			if ( ! apply_filters( 'learndash_stripe_process_webhook', true, $decoded_payload ) ) {
				return;
			}

			// Prevent webhooks being processed at the same time to prevent webhook error.
			$this->check_webhook_process();

			global $learndash_stripe_webhook_running;
			$learndash_stripe_webhook_running = true;

			// Configure Stripe.
			$this->configure();

			try {
				$event = $this->stripe->events->retrieve( $decoded_payload->id );
			} catch ( Exception $e ) {
				if ( true === WP_DEBUG ) {
					error_log( 'Stripe Webhook Error: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}
				wp_die(
					esc_html( $e->getMessage() ),
					esc_html__( 'Error', 'learndash' ),
					intval( $e->getCode() )
				);
				exit();
			}

			/**
			 * Stripe checkout session.
			 *
			 * @var \Stripe\Checkout\Session $session Session.
			 */
			$session = $event->data->object;

			try {
				if ( ! empty( $session->customer ) ) {
					$customer = $this->stripe->customers->retrieve( $session->customer );
				} else {
					http_response_code( 200 );
					exit();
				}
			} catch ( \Exception $e ) {
				if ( true === WP_DEBUG ) {
					error_log( 'Stripe Webhook Error: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}
				wp_die(
					esc_html( $e->getMessage() ),
					esc_html__( 'Error', 'learndash' ),
					intval( $e->getCode() )
				);
				exit();
			}

			$user_id = $this->get_user_id(
				$customer->email,
				$customer->id,
				isset( $session->metadata->user_id )
					? intval( $session->metadata->user_id )
					: null
			);

			if ( 'checkout.session.completed' === $event->type ) {
				// Associate course with user.
				$course_id = $session->metadata->course_id ?? null;
				$this->add_post_access( $course_id, $user_id );
				$this->record_transaction( $session, $course_id, $user_id, $customer->email );
				learndash_send_purchase_success_email( $user_id, $course_id );
			} elseif ( 'invoice.payment_succeeded' === $event->type ) {
				foreach ( $session->lines->data as $item ) {
					$plan_id = $item->plan->id ?? null;

					if ( $plan_id ) {
						$course_id = $item->metadata->course_id ?? $this->get_course_id_by_plan_id( $plan_id );
						if ( ! empty( $course_id ) ) {
							$this->add_post_access( $course_id, $user_id );

							// Cancel user subscription if recurring limit is set.
							$course_args            = $this->get_course_args( $course_id );
							$course_recurring_times = $course_args['course_recurring_times'];

							if ( ! empty( $session->subscription ) && ! empty( $course_recurring_times ) ) {
								$invoices = $this->stripe->invoices->all(
									array(
										'status'       => 'paid',
										'customer'     => $session->customer,
										'subscription' => $session->subscription,
									)
								);

								$subscription = \Stripe\Subscription::retrieve( $session->subscription );

								$payments_count = count( $invoices->data );
								if ( isset( $subscription->metadata->has_trial ) && $subscription->metadata->has_trial ) {
									$payments_count--;
								}

								if ( $course_recurring_times == $payments_count ) {
									$this->stripe->subscriptions->update(
										$session->subscription,
										array(
											'cancel_at_period_end' => true,
										)
									);
								}
							}
						}
					}
				}
			} elseif ( 'invoice.payment_failed' === $event->type ) {
				foreach ( $session->lines->data as $item ) {
					$plan_id   = $item->plan->id;
					$course_id = $item->metadata->course_id ?? $this->get_course_id_by_plan_id( $plan_id );
					if ( ! empty( $course_id ) ) {
						$this->remove_post_access( $course_id, $user_id );
					}
				}
			} elseif ( 'customer.subscription.deleted' === $event->type ) {
				foreach ( $session->items->data as $item ) {
					$plan_id   = $item->plan->id;
					$course_id = $item->metadata->course_id ?? $this->get_course_id_by_plan_id( $plan_id );

					if ( ! empty( $course_id ) ) {
						/**
						 * Filters whether to remove a user's course if a recurring limit is applied.
						 *
						 * False by default.
						 *
						 * @since 4.0.0
						 *
						 * @param boolean $remove    To remove a course or not.
						 * @param int     $course_id Course Id.
						 * @param int     $user_id   USer Id.
						 *
						 * @return boolean True to remove, otherwise false.
						 */
						$remove_user_course_access_on_recurring_limit = apply_filters( 'learndash_stripe_remove_user_course_access_on_recurring_limit', false, $course_id, $user_id );

						if ( isset( $session->metadata->has_recurring_limit ) && $session->metadata->has_recurring_limit ) {
							if ( true === $remove_user_course_access_on_recurring_limit ) {
								$this->remove_post_access( $course_id, $user_id );
							}
						} else {
							$this->remove_post_access( $course_id, $user_id );
						}
					}
				}
			}

			$learndash_stripe_webhook_running = false;

			http_response_code( 200 );
			exit();
		}

		/**
		 * Get LearnDash course ID by Stripe plan ID.
		 *
		 * @param string $plan_id Stripe plan ID.
		 *
		 * @return string|null LearnDash course ID or null.
		 */
		public function get_course_id_by_plan_id( string $plan_id ): ?string {
			global $wpdb;

			return $wpdb->get_var( // phpcs:ignore
				$wpdb->prepare( "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'stripe_plan_id' AND meta_value = %s", $plan_id )
			);
		}

		/**
		 * Outputs transaction message.
		 *
		 * @return void
		 */
		public function output_transaction_message(): void {
			if ( empty( $_GET['ld_stripe_connect'] ) ) { // phpcs:ignore
				return;
			}

			switch ( $_GET['ld_stripe_connect'] ) { // phpcs:ignore
				case 'success':
					if ( is_user_logged_in() ) {
						$message = __( 'Your transaction was successful.', 'learndash' );
					} else {
						$message = __( 'Your transaction was successful. Please log in to access your content.', 'learndash' );
					}
					break;
				default:
					$message = '';
					break;
			}

			if ( empty( $message ) ) {
				return;
			}
			?>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					alert('<?php echo esc_html( $message ); ?>');
				});
			</script>
			<?php
		}

		/**
		 * Get user ID of the customer
		 *
		 * @param null|string $email User email address.
		 * @param string      $customer_id Stripe customer ID.
		 * @param null|int    $user_id WP user ID.
		 *
		 * @return int WP_User ID
		 */
		public function get_user_id( ?string $email, string $customer_id, ?int $user_id = null ): int {
			$user = ! empty( $user_id ) && is_numeric( $user_id )
				? get_user_by( 'ID', $user_id )
				: ( ! empty( $email ) ? get_user_by( 'email', $email ) : null );

			if ( $user ) {
				update_user_meta( $user->ID, $this->stripe_customer_id_meta_key, $customer_id );

				return $user->ID;
			}

			$password = wp_generate_password( 18 );
			$user_id  = $this->create_user( $email, $password, $email );

			if ( ! is_wp_error( $user_id ) ) {
				update_user_meta( $user_id, $this->stripe_customer_id_meta_key, $customer_id );

				global $wp_version;

				if ( version_compare( $wp_version, '4.3.0', '<' ) ) {
					wp_new_user_notification( $user_id, $password ); // phpcs:ignore
				} elseif ( version_compare( $wp_version, '4.3.0', '==' ) ) {
					wp_new_user_notification( $user_id, 'both' ); // phpcs:ignore
				} elseif ( version_compare( $wp_version, '4.3.1', '>=' ) ) {
					wp_new_user_notification( $user_id, null, 'both' );
				}
			}

			return $user_id;
		}

		/**
		 * Creates a user if does not exist.
		 *
		 * @param string $email Email.
		 * @param string $password Password.
		 * @param string $username Username.
		 *
		 * @return int Newly created user ID
		 */
		public function create_user( string $email, string $password, string $username ): int {
			/**
			 * Filters whether to shorten a username from an email or keep an email.
			 *
			 * False by default.
			 *
			 * @since 4.0.0
			 *
			 * @param boolean $shorten To shorten or not.
			 *
			 * @return boolean True to shorten a username from an email. Anything else to keep an email.
			 */
			if ( true === apply_filters( 'learndash_stripe_create_short_username', false ) ) {
				$username = preg_replace( '/(.*)\@(.*)/', '$1', $email );
			}

			if ( username_exists( $username ) ) {
				$random_chars = str_shuffle( substr( md5( time() ), 0, 5 ) );
				$username     = $username . '-' . $random_chars;
			}

			$user_id = wp_create_user( $username, $password, $email );

			do_action( 'learndash_stripe_after_create_user', $user_id );

			return $user_id;
		}

		/**
		 * Records a payment transaction.
		 *
		 * @param object $session    Transaction data passed through $_POST.
		 * @param int    $post_id    Post ID.
		 * @param int    $user_id    User ID.
		 * @param string $user_email Email of the user.
		 */
		public function record_transaction( $session, int $post_id, int $user_id, string $user_email ) {
			$currency        = $session->currency;
			$amount          = $session->amount_total;
			$course_args     = $this->get_course_args( $post_id );
			$subscribe_price = 'subscription' === $session->mode ? $session->display_items[1]['amount'] : '';

			$transaction_meta = array(
				'stripe_session_id'     => $session->id,
				'stripe_metadata'       => $session->metadata,
				'stripe_customer'       => $session->customer,
				'stripe_payment_intent' => $session->payment_intent,
				'customer_email'        => $user_email,
				'stripe_price'          => ! $this->is_zero_decimal_currency( $currency ) && $amount > 0
					? number_format( $amount / 100, 2, '.', '' )
					: $amount,
				'stripe_price_type'     => 'payment' === $session->mode ? LEARNDASH_PRICE_TYPE_PAYNOW : LEARNDASH_PRICE_TYPE_SUBSCRIBE,
				'stripe_currency'       => $currency,
				'stripe_name'           => get_the_title( $post_id ),
				'subscription'          => $session->subscription,
				'pricing_billing_p3'    => $course_args['course_interval_count'],
				'pricing_billing_t3'    => $course_args['course_interval'],
				'no_of_cycles'          => $course_args['course_recurring_times'],
				'trial_price'           => ! $this->is_zero_decimal_currency( $currency ) && $course_args['course_trial_price'] > 0 ? number_format( $course_args['course_trial_price'] / 100, 2, '.', '' ) : $course_args['course_trial_price'],
				'trial_duration_p1'     => $course_args['course_trial_interval_count'],
				'trial_duration_t1'     => $course_args['course_trial_interval'],
				'subscribe_price'       => $subscribe_price,
				'ld_payment_processor'  => 'stripe',
			);

			learndash_transaction_create(
				$transaction_meta,
				get_post( $post_id ),
				get_user_by( 'ID', $user_id )
			);
		}

		/**
		 * Maps the secret key.
		 *
		 * @return string
		 */
		protected function map_secret_key(): string {
			if ( $this->is_test_mode() && ! empty( $this->options['secret_key_test'] ) ) {
				return $this->options['secret_key_test'];
			}

			if ( ! $this->is_test_mode() && ! empty( $this->options['secret_key_live'] ) ) {
				return $this->options['secret_key_live'];
			}

			return '';
		}

		/**
		 * Maps the publishable key.
		 *
		 * @return string
		 */
		protected function map_publishable_key(): string {
			if ( $this->is_test_mode() && ! empty( $this->options['publishable_key_test'] ) ) {
				return $this->options['publishable_key_test'];
			}

			if ( ! $this->is_test_mode() && ! empty( $this->options['publishable_key_live'] ) ) {
				return $this->options['publishable_key_live'];
			}

			return '';
		}

		/**
		 * Returns enabled payment methods.
		 *
		 * @return array
		 */
		protected function get_payment_methods(): array {
			$enabled_payment_methods = isset( $this->options['payment_methods'] ) && ( ! empty( $this->options['payment_methods'] ) ) ? $this->options['payment_methods'] : array( 'card' );
			/**
			 * Filters enabled Stripe payment methods.
			 *
			 * @since 4.0.0
			 *
			 * @param array $enabled_payment_methods Enabled Stripe payment methods.
			 *
			 * @return array Stripe payment methods.
			 */
			return apply_filters( 'learndash_stripe_payment_method_types', $enabled_payment_methods );
		}

		/**
		 * Check if Stripe currency ISO code is zero decimal currency
		 *
		 * @param string $currency Stripe currency ISO code.
		 *
		 * @return bool
		 */
		protected function is_zero_decimal_currency( string $currency = '' ): bool {
			$zero_decimal_currencies = array(
				'BIF',
				'CLP',
				'DJF',
				'GNF',
				'JPY',
				'KMF',
				'KRW',
				'MGA',
				'PYG',
				'RWF',
				'VND',
				'VUV',
				'XAF',
				'XOF',
				'XPF',
			);

			return in_array( strtoupper( $currency ), $zero_decimal_currencies, true );
		}

		/**
		 * Generates random string.
		 *
		 * @param integer $length Length.
		 *
		 * @return string
		 */
		protected function generate_random_string( $length = 5 ): string {
			return substr( md5( microtime() ), 0, $length );
		}

		/**
		 * Checks if learndash Stripe Connect webhook is running.
		 *
		 * @return bool
		 */
		protected function check_webhook_process(): bool {
			global $learndash_stripe_webhook_running;

			if ( $learndash_stripe_webhook_running ) {
				sleep( 1 );
				$this->check_webhook_process();
			}

			return false;
		}

		/**
		 * Sets Stripe checkout session on a course page.
		 *
		 * @param int|null $course_id Course ID.
		 *
		 * @return string|array
		 * @throws \Stripe\Exception\ApiErrorException API Error.
		 */
		protected function set_session( ?int $course_id = null ) {
			$args = $this->get_course_args( $course_id );

			extract( $args ); // phpcs:ignore

			$this->configure();

			$stripe_customer_id = get_user_meta( get_current_user_id(), $this->stripe_customer_id_meta_key, true );

			try {
				$stripe_customer = ! empty( $stripe_customer_id )
					? $this->stripe->customers->retrieve( $stripe_customer_id )
					: null;
			} catch ( Exception $e ) {
				return array(
					'error' => $e->getMessage(),
				);
			}

			$customer   = ! empty( $stripe_customer->id ) && empty( $stripe_customer->deleted ) && ! empty( $stripe_customer_id )
				? $stripe_customer_id
				: null;
			$user_id    = is_user_logged_in()
				? get_current_user_id()
				: null;
			$user_email = is_user_logged_in()
				? wp_get_current_user()->user_email
				: null;

			$course_page_url = get_permalink( $course_id );
			$success_url     = ! empty( $this->options['return_url'] ) ? $this->options['return_url'] : $course_page_url;
			$success_url     = add_query_arg(
				array(
					'ld_stripe_connect' => 'success',
					'session_id'        => '{CHECKOUT_SESSION_ID}',
				),
				$success_url
			);
			$course_images   = ! empty( $course_image ) ? array( $course_image ) : null;
			$metadata        = array(
				'course_id' => $course_id,
				'user_id'   => $user_id,
			);

			$line_items = array(
				array(
					'name'     => $course_name,
					'amount'   => $course_price,
					'currency' => $currency,
					'quantity' => 1,
				),
			);
			if ( ! empty( $course_images ) ) {
				$line_items[0]['images'] = $course_images;
			}

			$payment_intent_data = null;
			if ( 'paynow' === $course_price_type ) {
				$payment_intent_data = array(
					'metadata' => $metadata,
				);

				if ( $user_email ) {
					$payment_intent_data['receipt_email'] = $user_email;
				}
			}

			$subscription_data = null;
			if ( 'subscribe' === $course_price_type ) {
				if ( empty( $course_interval ) || empty( $course_interval_count ) || empty( $course_price ) ) {
					return '';
				}

				$plan_id = get_post_meta( $course_id, 'stripe_plan_id', false );
				$plan_id = end( $plan_id );

				if ( ! empty( $plan_id ) ) {
					try {
						$plan = $this->stripe->plans->retrieve(
							$plan_id,
							array(
								'expand' => array( 'product' ),
							)
						);

						if (
							( isset( $plan ) && is_object( $plan ) ) &&
							$plan->amount != $course_price ||
							strtolower( $currency ) != $plan->currency ||
							$plan->id != $plan_id ||
							$plan->interval != $course_interval ||
							htmlspecialchars_decode( $plan->product->name ) != stripslashes( sanitize_text_field( $course_name ) ) ||
							$plan->interval_count != $course_interval_count
						) {
							// Don't delete the old plan as old subscription may still be attached to it.

							// Create a new plan.
							$plan = $this->stripe->plans->create(
								array(
									// Required.
									'amount'         => esc_attr( $course_price ),
									'currency'       => strtolower( $currency ),
									'id'             => $course_plan_id . '-' . $this->generate_random_string(),
									'interval'       => $course_interval,
									'product'        => array(
										'name' => stripslashes( sanitize_text_field( $course_name ) ),
									),
									// Optional.
									'interval_count' => esc_attr( $course_interval_count ),
								)
							);

							$plan_id = $plan->id;

							add_post_meta( $course_id, 'stripe_plan_id', $plan_id, false );
						}
					} catch ( Exception $e ) {
						// Create a new plan.
						$plan = $this->stripe->plans->create(
							array(
								// Required.
								'amount'         => esc_attr( $course_price ),
								'currency'       => strtolower( $currency ),
								'id'             => $course_plan_id . '-' . $this->generate_random_string(),
								'interval'       => $course_interval,
								'product'        => array(
									'name' => stripslashes( sanitize_text_field( $course_name ) ),
								),
								// Optional.
								'interval_count' => esc_attr( $course_interval_count ),
							)
						);

						$plan_id = $plan->id;

						add_post_meta( $course_id, 'stripe_plan_id', $plan_id );
					}
				} else {
					// Create a new plan.
					$plan = $this->stripe->plans->create(
						array(
							// Required.
							'amount'         => esc_attr( $course_price ),
							'currency'       => strtolower( $currency ),
							'id'             => $course_plan_id,
							'interval'       => $course_interval,
							'product'        => array(
								'name' => stripslashes( sanitize_text_field( $course_name ) ),
							),
							// Optional.
							'interval_count' => esc_attr( $course_interval_count ),
						)
					);

					$plan_id = $plan->id;

					add_post_meta( $course_id, 'stripe_plan_id', $plan_id );
				}

				$trial_period_days = null;
				if ( ! empty( $course_trial_interval_count ) && ! empty( $course_trial_interval ) ) {
					switch ( $course_trial_interval ) {
						case 'day':
							$trial_period_days = $course_trial_interval_count * 1;
							break;

						case 'week':
							$trial_period_days = $course_trial_interval_count * 7;
							break;

						case 'month':
							$trial_period_days = $course_trial_interval_count * 30;
							break;

						case 'year':
							$trial_period_days = $course_trial_interval_count * 365;
							break;
					}
				}

				if ( ! empty( $trial_period_days ) ) {
					if ( ! empty( $course_trial_price ) ) {
						$line_items = array(
							array(
								'name'     => sprintf(
									// Translators: number of days.
									_n( '%d Day Trial', '%d Days Trial', $trial_period_days, 'learndash' ),
									$trial_period_days
								),
								'amount'   => $course_trial_price,
								'currency' => $currency,
								'quantity' => 1,
							),
						);
					} else {
						$line_items = null;
					}

					$metadata['has_trial'] = true;
				} else {
					$line_items = null;
				}

				if ( ! empty( $course_recurring_times ) ) {
					$metadata['has_recurring_limit'] = true;
				}

				$subscription_data = array(
					'metadata'          => $metadata,
					'items'             => array(
						array(
							'plan' => $plan_id,
						),
					),
					'trial_period_days' => $trial_period_days,
				);
			}

			$session_args = array(
				'allow_promotion_codes' => true,
				'payment_method_types'  => $this->get_payment_methods(),
				'line_items'            => $line_items,
				'metadata'              => $metadata,
				'success_url'           => $success_url,
				'cancel_url'            => $course_page_url,
				'payment_intent_data'   => $payment_intent_data,
				'subscription_data'     => is_array( $subscription_data ) ? array_filter( $subscription_data ) : null,
				'customer'              => $customer,
			);
			$session_args = array_filter( $session_args );

			/**
			 * Filters Stripe session arguments before creation.
			 *
			 * @since 4.0.0
			 *
			 * @param array $session_args Stripe session arguments.
			 *
			 * @return array Stripe session arguments.
			 */
			$session_args = apply_filters( 'learndash_stripe_session_args', $session_args );

			try {
				$session = $this->stripe->checkout->sessions->create( $session_args );

				$this->session_id = $session->id;
				setcookie( 'ld_stripe_connect_session_id_' . $course_id, $this->session_id, time() + DAY_IN_SECONDS );

				return $this->session_id;
			} catch ( Exception $e ) {
				return array(
					'error' => $e->getMessage(),
				);
			}
		}

		/**
		 * AJAX function handler for init checkout.
		 *
		 * @throws \Stripe\Exception\ApiErrorException Stripe Api Error.
		 *
		 * @uses ld_stripe_connect_init_checkout WP AJAX action string.
		 *
		 * @return void
		 */
		public function ajax_init_checkout(): void {
			$action = 'stripe-connect-nonce-' . $_POST['stripe_course_id'] . $_POST['stripe_price_type']; // phpcs:ignore

			if ( empty( $_POST['stripe_course_id'] ) || ! wp_verify_nonce( $_POST['stripe_connect_nonce'], $action ) ) { // phpcs:ignore
				wp_die( __( 'Cheatin\' huh?', 'learndash' ) ); // phpcs:ignore
			}

			$session_id = $this->set_session(
				intval( $_POST['stripe_course_id'] )
			);

			if ( is_array( $session_id ) && isset( $session_id['error'] ) ) {
				echo wp_json_encode(
					array(
						'status'  => 'error',
						'payload' => $session_id['error'],
					)
				);
				wp_die();
			}

			echo wp_json_encode(
				array(
					'status'     => 'success',
					'session_id' => $session_id,
				)
			);
			wp_die();
		}

		/**
		 * Hides buttons from Stripe plugin to avoid 2 Stripe buttons.
		 */
		private function hide_plugin_stripe_button(): void {
			global $ld_stripe_checkout;
			global $ld_stripe_legacy_checkout;

			if ( ! is_null( $ld_stripe_checkout ) ) {
				remove_filter( 'learndash_payment_button', array( $ld_stripe_checkout, 'payment_button' ) );
			} elseif ( ! is_null( $ld_stripe_legacy_checkout ) ) {
				remove_filter( 'learndash_payment_button', array( $ld_stripe_legacy_checkout, 'payment_button' ) );
			}
		}
	}

	new LearnDash_Stripe_Connect_Checkout_Integration();
}
