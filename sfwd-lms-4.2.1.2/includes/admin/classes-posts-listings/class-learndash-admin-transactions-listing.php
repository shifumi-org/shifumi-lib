<?php
/**
 * LearnDash Transactions (sfwd-transactions) Posts Listing.
 *
 * @since 3.2.0
 * @package LearnDash\Transactions\Listing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( class_exists( 'Learndash_Admin_Posts_Listing' ) ) && ( ! class_exists( 'Learndash_Admin_Transactions_Listing' ) ) ) {

	/**
	 * Class LearnDash Transactions (sfwd-transactions) Posts Listing.
	 *
	 * @since 3.2.0
	 * @uses Learndash_Admin_Posts_Listing
	 */
	class Learndash_Admin_Transactions_Listing extends Learndash_Admin_Posts_Listing {

		/**
		 * Public constructor for class
		 *
		 * @since 3.2.0
		 */
		public function __construct() {
			$this->post_type = learndash_get_post_type_slug( 'transaction' );

			parent::__construct();
		}

		/**
		 * Called via the WordPress init action hook.
		 *
		 * @since 3.2.3
		 */
		public function listing_init() {
			if ( $this->listing_init_done ) {
				return;
			}

			$this->selectors = array(
				'payment_processors' => array(
					'type'                   => 'early',
					'show_all_value'         => '',
					'show_all_label'         => esc_html__( 'Show All Payment Processors', 'learndash' ),
					'options'                => array(
						'paypal'   => esc_html__( 'PayPal', 'learndash' ),
						'stripe'   => esc_html__( 'Stripe', 'learndash' ),
						'razorpay' => esc_html__( 'Razorpay', 'learndash' ),
					),
					'listing_query_function' => array( $this, 'listing_filter_by_payment_processor' ),
					'select2'                => true,
					'select2_fetch'          => false,
				),
				'transaction_type'   => array(
					'type'                   => 'early',
					'show_all_value'         => '',
					'show_all_label'         => esc_html__( 'Show All Transactions Types', 'learndash' ),
					'options'                => array(
						'return-success'                => esc_html__( 'PayPal Purchase Pending', 'learndash' ),
						'web_accept'                    => esc_html__( 'PayPal Purchase Complete', 'learndash' ),
						'subscr_cancel'                 => esc_html__( 'PayPal Subscription Canceled', 'learndash' ),
						'subscr_eot'                    => esc_html__( 'PayPal Subscription Expired', 'learndash' ),
						'subscr_failed'                 => esc_html__( 'PayPal Subscription Payment Failed', 'learndash' ),
						'subscr_payment'                => esc_html__( 'PayPal Subscription Payment Success', 'learndash' ),
						'subscr_signup'                 => esc_html__( 'PayPal Subscription Signup', 'learndash' ),
						'stripe_paynow'                 => esc_html__( 'Stripe Purchase', 'learndash' ),
						'stripe_subscribe'              => esc_html__( 'Stripe Subscription', 'learndash' ),
						'razorpay_paynow'               => esc_html__( 'Razorpay Purchase', 'learndash' ),
						'razorpay_subscribe'            => esc_html__( 'Razorpay Subscription (no trial)', 'learndash' ),
						'razorpay_subscribe_paid_trial' => esc_html__( 'Razorpay Subscription (paid trial)', 'learndash' ),
						'razorpay_subscribe_free_trial' => esc_html__( 'Razorpay Subscription (free trial)', 'learndash' ),
					),
					'listing_query_function' => array( $this, 'listing_filter_by_transaction_type' ),
					'select2'                => true,
					'select2_fetch'          => false,
				),
				'course_id'          => array(
					'type'                    => 'post_type',
					'post_type'               => learndash_get_post_type_slug( 'course' ),
					'show_all_value'          => '',
					'show_all_label'          => sprintf(
						// translators: placeholder: Courses.
						esc_html_x( 'All %s', 'placeholder: Courses', 'learndash' ),
						LearnDash_Custom_Label::get_label( 'courses' )
					),
					'listing_query_function'  => array( $this, 'listing_filter_by_transaction_course_id' ),
					'selector_value_function' => array( $this, 'selector_value_for_course' ),
				),
				'group_id'           => array(
					'type'                    => 'post_type',
					'post_type'               => learndash_get_post_type_slug( 'group' ),
					'show_all_value'          => '',
					'show_all_label'          => sprintf(
						// translators: placeholder: Groups.
						esc_html_x( 'All %s', 'placeholder: Groups', 'learndash' ),
						LearnDash_Custom_Label::get_label( 'groups' )
					),
					'listing_query_function'  => array( $this, 'listing_filter_by_transaction_group_id' ),
					'selector_value_function' => array( $this, 'selector_value_for_group' ),
				),
			);

			$this->columns = array(
				'payment_processor' => array(
					'label'   => esc_html__( 'Payment Processor', 'learndash' ),
					'after'   => 'date',
					'display' => array( $this, 'show_column_payment_processor' ),
				),
				'transaction_type'  => array(
					'label'   => esc_html__( 'Transaction Type', 'learndash' ),
					'after'   => 'payment_processor',
					'display' => array( $this, 'show_column_transaction_type' ),
				),
				'coupon'            => array(
					'label'   => esc_html__( 'Coupon', 'learndash' ),
					'after'   => 'transaction_type',
					'display' => array( $this, 'show_column_coupon' ),
				),
				'access_status'     => array(
					'label'   => esc_html__( 'Access Status', 'learndash' ),
					'after'   => 'coupon',
					'display' => array( $this, 'show_column_access_status' ),
				),
				'course_group_id'   => array(
					'label'   => sprintf(
						// translators: placeholder: Course, Group.
						esc_html_x( 'Enrolled %1$s / %2$s', 'placeholder: Course, Group', 'learndash' ),
						LearnDash_Custom_Label::get_label( 'course' ),
						LearnDash_Custom_Label::get_label( 'group' )
					),
					'after'   => 'access_status',
					'display' => array( $this, 'show_column_transaction_course_group_id' ),
				),
				'user_id'           => array(
					'label'   => esc_html__( 'User', 'learndash' ),
					'after'   => 'course_group_id',
					'display' => array( $this, 'show_column_transaction_user_id' ),
				),
			);

			parent::listing_init();

			$this->listing_init_done = true;
		}

		/**
		 * Call via the WordPress load sequence for admin pages.
		 *
		 * @since 3.6.0
		 */
		public function on_load_listing() {
			if ( $this->post_type_check() ) {

				parent::on_load_listing();

				add_action( 'admin_footer', array( $this, 'transactions_bulk_actions' ), 30 );

				$this->transactions_bulk_actions_remove_access();
			}
		}

		/**
		 * Filter the main query listing by the transaction_type
		 *
		 * @since 3.6.0
		 *
		 * @param object $q_vars   Query vars used for the table listing.
		 * @param array  $selector Selector array.
		 *
		 * @return object $q_vars.
		 */
		protected function listing_filter_by_payment_processor( $q_vars, $selector = array() ) {
			if ( empty( $selector['selected'] ) ) {
				return $q_vars;
			}

			if ( ! isset( $q_vars['meta_query'] ) ) {
				$q_vars['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			}

			if ( 'paypal' === $selector['selected'] ) {
				$q_vars['meta_query']['relation'] = 'OR';
				$q_vars['meta_query'][]           = array(
					'key'     => 'ld_payment_processor',
					'compare' => '=',
					'value'   => $selector['selected'],
				);
				$q_vars['meta_query'][]           = array(
					'key'     => 'ipn_track_id',
					'compare' => 'EXISTS',
				);
			} elseif ( 'stripe' === $selector['selected'] ) {
				$q_vars['meta_query']['relation'] = 'OR';
				$q_vars['meta_query'][]           = array(
					'key'     => 'ld_payment_processor',
					'compare' => '=',
					'value'   => $selector['selected'],
				);
				$q_vars['meta_query'][]           = array(
					'key'     => 'stripe_session_id',
					'compare' => 'EXISTS',
				);
			} elseif ( 'razorpay' === $selector['selected'] ) {
				// phpcs:ignore
				$q_vars['meta_query'][] = array(
					'key'     => 'ld_payment_processor',
					'compare' => '=',
					'value'   => $selector['selected'],
				);
			}

			return $q_vars;
		}

		/**
		 * Filter the main query listing by the transaction_type
		 *
		 * @since 3.6.0
		 *
		 * @param object $q_vars   Query vars used for the table listing.
		 * @param array  $selector Selector array.
		 *
		 * @return object $q_vars.
		 */
		protected function listing_filter_by_transaction_type( $q_vars, $selector = array() ) {
			if ( ( isset( $selector['selected'] ) ) && ( ! empty( $selector['selected'] ) ) ) {

				if ( ! isset( $q_vars['meta_query'] ) ) {
					$q_vars['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				}

				if ( 'web_accept' === $selector['selected'] ) {
					$q_vars['meta_query'][] = array(
						'key'   => 'txn_type',
						'value' => 'web_accept',
					);
				} elseif ( 'subscr_cancel' === $selector['selected'] ) {
					$q_vars['meta_query'][] = array(
						'key'   => 'txn_type',
						'value' => 'subscr_cancel',
					);
				} elseif ( 'subscr_eot' === $selector['selected'] ) {
					$q_vars['meta_query'][] = array(
						'key'   => 'txn_type',
						'value' => 'subscr_eot',
					);
				} elseif ( 'subscr_failed' === $selector['selected'] ) {
					$q_vars['meta_query'][] = array(
						'key'   => 'txn_type',
						'value' => 'subscr_failed',
					);
				} elseif ( 'subscr_payment' === $selector['selected'] ) {
					$q_vars['meta_query'][] = array(
						'key'   => 'txn_type',
						'value' => 'subscr_payment',
					);
				} elseif ( 'subscr_signup' === $selector['selected'] ) {
					$q_vars['meta_query'][] = array(
						'key'   => 'txn_type',
						'value' => 'subscr_signup',
					);
				} elseif ( 'stripe_paynow' === $selector['selected'] ) {
					$q_vars['meta_query'][] = array(
						'key'   => 'stripe_price_type',
						'value' => LEARNDASH_PRICE_TYPE_PAYNOW,
					);
				} elseif ( 'stripe_subscribe' === $selector['selected'] ) {
					$q_vars['meta_query'][] = array(
						'key'   => 'stripe_price_type',
						'value' => LEARNDASH_PRICE_TYPE_SUBSCRIBE,
					);
				} elseif ( 'razorpay_paynow' === $selector['selected'] ) {
					$q_vars['meta_query'][] = array(
						array(
							'key'   => 'ld_payment_processor',
							'value' => 'razorpay',
						),
						array(
							'key'   => 'price_type',
							'value' => LEARNDASH_PRICE_TYPE_PAYNOW,
						),
					);
				} elseif ( 'razorpay_subscribe' === $selector['selected'] ) {
					$q_vars['meta_query'][] = array(
						array(
							'key'   => 'ld_payment_processor',
							'value' => 'razorpay',
						),
						array(
							'key'   => 'price_type',
							'value' => LEARNDASH_PRICE_TYPE_SUBSCRIBE,
						),
						array(
							'key'     => 'has_trial',
							'compare' => '!=',
							'value'   => 1,
						),
					);
				} elseif ( 'razorpay_subscribe_paid_trial' === $selector['selected'] ) {
					$q_vars['meta_query'][] = array(
						array(
							'key'   => 'ld_payment_processor',
							'value' => 'razorpay',
						),
						array(
							'key'   => 'has_trial',
							'value' => 1,
						),
						array(
							'key'   => 'has_free_trial',
							'value' => 0,
						),
					);
				} elseif ( 'razorpay_subscribe_free_trial' === $selector['selected'] ) {
					$q_vars['meta_query'][] = array(
						array(
							'key'   => 'ld_payment_processor',
							'value' => 'razorpay',
						),
						array(
							'key'   => 'has_free_trial',
							'value' => 1,
						),
					);
				}
			}

			return $q_vars;
		}

		/**
		 * Filter the main query listing by the course_id
		 *
		 * @since 3.2.3
		 *
		 * @param object $q_vars   Query vars used for the table listing.
		 * @param array  $selector Selector array.
		 *
		 * @return object $q_vars.
		 */
		protected function listing_filter_by_transaction_course_id( $q_vars, $selector = array() ) {
			if ( empty( $selector['selected'] ) ) {
				return $q_vars;
			}

			if ( ! isset( $q_vars['meta_query'] ) ) {
				$q_vars['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			}

			$q_vars['meta_query']['relation'] = 'OR';
			$q_vars['meta_query'][]           = array(
				'key'   => 'course_id',
				'value' => absint( $selector['selected'] ),
			);
			$q_vars['meta_query'][]           = array(
				'key'   => 'post_id',
				'value' => absint( $selector['selected'] ),
			);

			return $q_vars;
		}

		/**
		 * Filter the main query listing by the group_id
		 *
		 * @since 3.2.3
		 *
		 * @param object $q_vars   Query vars used for the table listing.
		 * @param array  $selector Selector array.
		 *
		 * @return object $q_vars
		 */
		protected function listing_filter_by_transaction_group_id( $q_vars, $selector = array() ) {
			if ( empty( $selector['selected'] ) ) {
				return $q_vars;
			}

			if ( ! isset( $q_vars['meta_query'] ) ) {
				$q_vars['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			}

			$q_vars['meta_query']['relation'] = 'OR';
			$q_vars['meta_query'][]           = array(
				'key'   => 'group_id',
				'value' => absint( $selector['selected'] ),
			);
			$q_vars['meta_query'][]           = array(
				'key'   => 'post_id',
				'value' => absint( $selector['selected'] ),
			);

			return $q_vars;
		}

		/**
		 * Output the Transaction Type column.
		 *
		 * @since 3.2.3
		 *
		 * @param int $post_id Transaction Post ID.
		 */
		protected function show_column_payment_processor( $post_id = 0 ) {
			$post_id = absint( $post_id );

			$is_zero_price = (bool) get_post_meta( $post_id, 'is_zero_price', true );

			if ( $is_zero_price ) {
				esc_html_e( 'No', 'learndash' );
				return;
			}

			$payment_processor = $this->get_transaction_processor_type( $post_id );

			switch ( $payment_processor ) {
				case 'paypal':
					esc_html_e( 'PayPal', 'learndash' );
					break;
				case 'stripe':
					esc_html_e( 'Stripe', 'learndash' );
					break;
				case 'razorpay':
					esc_html_e( 'Razorpay', 'learndash' );
					break;
				default:
					esc_html_e( 'Unknown', 'learndash' );
			}
		}

		/**
		 * Output the Transaction Type column.
		 *
		 * @since 3.2.3
		 *
		 * @param int $post_id Transaction Post ID.
		 */
		protected function show_column_transaction_type( $post_id = 0 ) {
			$post_id       = absint( $post_id );
			$is_zero_price = (bool) get_post_meta( $post_id, 'is_zero_price', true );

			if ( $is_zero_price ) {
				esc_html_e( 'Zero price', 'learndash' );
				return;
			}

			$payment_processor = $this->get_transaction_processor_type( $post_id );

			$payment_label    = '';
			$payment_amount   = '';
			$payment_currency = '';

			if ( 'paypal' === $payment_processor ) {
				$ipn_transaction_type = get_post_meta( $post_id, 'txn_type', true );
				if ( 'return-success' === $ipn_transaction_type ) {
					$ipn_track_id = get_post_meta( $post_id, 'txn_type', true );
					if ( ! empty( $ipn_track_id ) ) {
						$ipn_transaction_type = 'web_accept';
						update_post_meta( $post_id, 'txn_type', $ipn_transaction_type );
						update_post_meta( $post_id, 'ld_ipn_action', $ipn_transaction_type );
					}
				}

				if ( ! empty( $ipn_transaction_type ) ) {
					if ( isset( $this->selectors['transaction_type']['options'][ $ipn_transaction_type ] ) ) {
						$payment_label = $this->selectors['transaction_type']['options'][ $ipn_transaction_type ];
						if ( in_array( $ipn_transaction_type, array( 'web_accept', 'subscr_payment' ), true ) ) {
							$payment_amount = get_post_meta( $post_id, 'mc_gross', true );
							if ( '' === $payment_amount ) {
								$payment_amount = '0.00';
							}
							$payment_amount   = number_format_i18n( $payment_amount, 2 );
							$payment_currency = get_post_meta( $post_id, 'mc_currency', true );
						}
					} else {
						$payment_label = sprintf(
							// translators: placeholder: PayPal txn_type value.
							esc_html_x( 'PayPal %s', 'placeholder: PayPal txn_type value', 'learndash' ),
							$ipn_transaction_type
						);
					}
				}
			} elseif ( 'stripe' === $payment_processor ) {
				$stripe_price_type = 'stripe_' . get_post_meta( $post_id, 'stripe_price_type', true );

				if ( in_array( $stripe_price_type, array( 'stripe_paynow', 'stripe_subscribe' ), true ) ) {
					$payment_label  = $this->selectors['transaction_type']['options'][ $stripe_price_type ];
					$payment_amount = get_post_meta( $post_id, 'stripe_price', true );

					if ( 'stripe_subscribe' === $stripe_price_type && ( 0 == $payment_amount || '' === $payment_amount ) ) {
						$payment_label .= ' ' . __( 'Signup', 'learndash' );
						$payment_amount = null;
					} else {
						if ( '' === $payment_amount ) {
							$payment_amount = '0.00';
						}
						$payment_amount   = number_format_i18n( $payment_amount, 2 );
						$payment_currency = get_post_meta( $post_id, 'stripe_currency', true );
					}
				} else {
					$payment_label = sprintf(
						// translators: placeholder: Stripe stripe_price_type value.
						esc_html_x( 'stripe %s', 'Stripe stripe_price_type value', 'learndash' ),
						$stripe_price_type
					);
				}
			} elseif ( 'razorpay' === $payment_processor ) {
				$price_type     = get_post_meta( $post_id, 'price_type', true );
				$pricing        = get_post_meta( $post_id, 'pricing', true );
				$has_trial      = get_post_meta( $post_id, 'has_trial', true );
				$has_free_trial = get_post_meta( $post_id, 'has_free_trial', true );

				$payment_label = $has_trial
					? $this->selectors['transaction_type']['options'][ 'razorpay_' . $price_type . '_' . ( $has_free_trial ? 'free' : 'paid' ) . '_trial' ]
					: $this->selectors['transaction_type']['options'][ 'razorpay_' . $price_type ];

				if ( ! $has_free_trial ) {
					$payment_amount = ! empty( $pricing['trial_price'] ) ? $pricing['trial_price'] : $pricing['price'];
					$payment_amount = number_format_i18n( $payment_amount, 2 );

					$payment_currency = $pricing['currency'];
				}
			}

			if ( ! empty( $payment_label ) ) {
				if ( ! empty( $payment_amount ) ) {
					echo sprintf(
						// translators: placeholder: Payment Action Label, Payment price, Payment Currency.
						esc_html_x( '%1$s: %2$s %3$s', 'placeholder: Payment Action Label, Payment price, Payment Currency', 'learndash' ),
						esc_html( $payment_label ),
						esc_attr( $payment_amount ),
						esc_attr( strtoupper( $payment_currency ) )
					);
				} else {
					echo esc_html( $payment_label );
				}
			}
		}

		/**
		 * Output the Coupon column.
		 *
		 * @since 4.1.0
		 *
		 * @param int $post_id Transaction Post ID.
		 */
		protected function show_column_coupon( int $post_id ) {
			$coupon_data = get_post_meta( $post_id, LEARNDASH_TRANSACTION_COUPON_META_KEY, true );

			if ( empty( $coupon_data ) ) {
				echo esc_html__( 'No coupon', 'learndash' );
				return;
			}

			$code = $coupon_data[ LEARNDASH_COUPON_META_KEY_CODE ];

			echo esc_html( $code );
		}

		/**
		 * Output the Transaction Course or Group.
		 *
		 * @since 3.2.3
		 *
		 * @param int $post_id Transaction Post ID.
		 */
		protected function show_column_transaction_course_group_id( $post_id = 0 ) {
			$post_id = absint( $post_id );

			$filter_label = '';
			$filter_url   = '';
			$filter_link  = '';
			$row_actions  = array();

			$course_id = get_post_meta( $post_id, 'course_id', true );
			if ( empty( $course_id ) ) {
				$course_id = get_post_meta( $post_id, 'post_id', true );
			}
			$course_id = absint( $course_id );

			if ( ! empty( $course_id ) ) {
				$filter_label = LearnDash_Custom_Label::get_label( 'course' );
				$filter_url   = add_query_arg( 'course_id', $course_id, $this->get_clean_filter_url() );

				$row_actions['ld-post-filter'] = '<a href="' . esc_url( $filter_url ) . '">' . esc_html__( 'filter', 'learndash' ) . '</a>';

				if ( current_user_can( 'edit_post', $course_id ) ) {
					$row_actions['ld-post-edit'] = '<a href="' . esc_url( get_edit_post_link( $course_id ) ) . '">' . esc_html__( 'edit', 'learndash' ) . '</a>';
				}

				if ( is_post_type_viewable( get_post_type( $course_id ) ) ) {
					$row_actions['ld-post-view'] = '<a href="' . esc_url( get_permalink( $course_id ) ) . '">' . esc_html__( 'view', 'learndash' ) . '</a>';
				}

				$filter_link = '<a href="' . esc_url( $filter_url ) . '">' . wp_kses_post( get_the_title( $course_id ) ) . '</a>';
			} else {
				$group_id = get_post_meta( $post_id, 'group_id', true );
				$group_id = absint( $group_id );
				if ( ! empty( $group_id ) ) {
					$filter_label = LearnDash_Custom_Label::get_label( 'group' );
					$filter_url   = add_query_arg( 'group_id', $group_id, $this->get_clean_filter_url() );

					$row_actions['ld-post-filter'] = '<a href="' . esc_url( $filter_url ) . '">' . esc_html__( 'filter', 'learndash' ) . '</a>';

					if ( current_user_can( 'edit_post', $group_id ) ) {
						$row_actions['ld-post-edit'] = '<a href="' . esc_url( get_edit_post_link( $group_id ) ) . '">' . esc_html__( 'edit', 'learndash' ) . '</a>';
					}

					if ( is_post_type_viewable( get_post_type( $group_id ) ) ) {
						$row_actions['ld-post-view'] = '<a href="' . esc_url( get_permalink( $group_id ) ) . '">' . esc_html__( 'view', 'learndash' ) . '</a>';
					}

					$filter_link = '<a href="' . esc_url( $filter_url ) . '">' . wp_kses_post( get_the_title( $group_id ) ) . '</a>';
				}
			}

			if ( ( ! empty( $filter_label ) ) && ( ! empty( $filter_link ) ) ) {
				echo sprintf(
					// translators: placeholder: Post type label (Course/Group), Link to Course/Group.
					esc_html_x( '%1$s: %2$s', 'placeholder: Post type label (Course/Group), Link to Course/Group', 'learndash' ),
					esc_html( $filter_label ),
					wp_kses_post( $filter_link )
				);

				if ( ! empty( $row_actions ) ) {
					echo $this->list_table_row_actions( $row_actions ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Need to output HTML
				}
			}
		}

		/**
		 * Show Transaction User ID.
		 *
		 * @since 3.2.3
		 *
		 * @param int $post_id Transaction Post ID.
		 */
		protected function show_column_transaction_user_id( $post_id = 0 ) {
			$post_id = absint( $post_id );

			$user = null;

			$user_id = get_post_meta( $post_id, 'user_id', true );
			if ( ! empty( $user_id ) ) {
				$user = get_user_by( 'ID', $user_id );
			}

			if ( ! $user ) {
				$ld_payment_processor = $this->get_transaction_processor_type( $post_id );
				if ( 'paypal' === $ld_payment_processor ) {
					$email = get_post_meta( $post_id, 'payer_email', true );
					if ( ! empty( $email ) ) {
						$user = get_user_by( 'email', $email );
					}
				} elseif ( 'stripe' === $ld_payment_processor ) {
					$user_id = get_post_meta( $post_id, 'user_id', true );
					if ( ! empty( $user_id ) ) {
						$user = get_user_by( 'ID', $user_id );
					}
				}
			}

			if ( ( ! empty( $user ) ) && ( is_a( $user, 'WP_User' ) ) ) {
				$row_actions  = array();
				$display_name = $user->display_name . ' (' . $user->user_email . ')';

				if ( current_user_can( 'edit_users' ) ) {
					$edit_url = get_edit_user_link( $user->ID );
					echo '<a href="' . esc_url( $edit_url ) . '">' . esc_html( $display_name ) . '</a>';
					$row_actions['edit'] = '<a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'edit', 'learndash' ) . '</a>';
				} else {
					echo esc_html( $display_name );
				}
				if ( ! empty( $row_actions ) ) {
					echo $this->list_table_row_actions( $row_actions ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Need to output HTML
				}
			}
		}

		/**
		 * Adds a 'Remove Access' option next to certain selects on transaction edit screen in admin.
		 *
		 * Fires on `admin_footer` hook.
		 *
		 * @since 3.6.0
		 *
		 * @global WP_Post $post Post object.
		 */
		public function transactions_bulk_actions() {
			global $post;

			if ( ( ! empty( $post->post_type ) ) && ( learndash_get_post_type_slug( 'transaction' ) === $post->post_type ) ) {
				$remove_access_text = esc_html__( 'Remove access', 'learndash' );
				?>
					<script type="text/javascript">
						jQuery( function() {
							jQuery('<option>').val('remove_access').text('<?php echo esc_attr( $remove_access_text ); ?>').appendTo("select[name='action']");
							jQuery('<option>').val('remove_access').text('<?php echo esc_attr( $remove_access_text ); ?>').appendTo("select[name='action2']");
						});
					</script>
				<?php
			}
		}

		/**
		 * Handles the access removal of courses and groups in bulk.
		 *
		 * Fires on `load-edit.php` hook.
		 *
		 * @since 3.6.0
		 */
		protected function transactions_bulk_actions_remove_access() {
			if ( ( ! isset( $_REQUEST['ld-listing-nonce'] ) ) || ( empty( $_REQUEST['ld-listing-nonce'] ) ) || ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['ld-listing-nonce'] ) ), get_called_class() ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				return;
			}

			if ( ( ! isset( $_REQUEST['post'] ) ) || ( empty( $_REQUEST['post'] ) ) || ( ! is_array( $_REQUEST['post'] ) ) ) {
				return;
			}

			if ( ( ! isset( $_REQUEST['post_type'] ) ) || ( learndash_get_post_type_slug( 'transaction' ) !== $_REQUEST['post_type'] ) ) {
				return;
			}

			$action = '';
			if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] ) {
				$action = esc_attr( wp_unslash( $_REQUEST['action'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			} elseif ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] ) {
				$action = esc_attr( wp_unslash( $_REQUEST['action2'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			} elseif ( ( isset( $_REQUEST['ld_action'] ) ) && ( 'remove_access' === $_REQUEST['ld_action'] ) ) {
				$action = 'remove_access';
			}

			if ( 'remove_access' === $action ) {
				if ( ( isset( $_REQUEST['post'] ) ) && ( ! empty( $_REQUEST['post'] ) ) ) {

					$transactions = array( wp_unslash( $_REQUEST['post'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

					if ( ! is_array( $transactions ) ) {
						$transactions = array( $transactions );
					}
					$transactions = array_map( 'absint', $transactions );

					foreach ( $transactions as $transaction_id ) {
						$transaction_post = get_post( $transaction_id );
						if ( ( ! empty( $transaction_post ) ) && ( is_a( $transaction_post, 'WP_Post' ) ) && ( learndash_get_post_type_slug( 'transaction' ) === $transaction_post->post_type ) ) {
							$post_id = (int) get_post_meta( $transaction_id, 'post_id', true );
							$user_id = (int) get_post_meta( $transaction_id, 'user_id', true );

							if ( ( empty( $post_id ) ) || ( empty( $user_id ) ) ) {
								return false;
							}

							$post = get_post( $post_id );
							if ( empty( $post ) || ( ! is_a( $post, 'WP_Post' ) ) ) {
								return false;
							}

							if ( ! in_array( $post->post_type, array( learndash_get_post_type_slug( 'course' ), learndash_get_post_type_slug( 'group' ) ), true ) ) {
								return false;
							}

							$user = get_userdata( $user_id );
							if ( empty( $user ) || ! is_a( $user, 'WP_User' ) ) {
								return false;
							}
							$user_has_access  = sfwd_lms_has_access( $post_id, $user_id );
							$txn_type         = get_post_meta( $transaction_id, 'txn_type', true );
							$removal_statuses = array( 'subscr_failed', 'subscr_cancel', 'subscr_eot' );
							/**
							 * Filter the PayPal Subscription removal statuses
							 *
							 * @param array  $removal_statuses Array of PayPal IPN subscription statuses
							 * @param object $post             Course or Group Object
							 * @param object $user             WP_User
							 */
							$removal_statuses = apply_filters( 'learndash_paypal_subscription_removal_statuses', $removal_statuses, $post, $user );

							if ( in_array( $txn_type, $removal_statuses, true ) && $user_has_access ) {
								if ( learndash_get_post_type_slug( 'course' ) === $post->post_type ) {
									ld_update_course_access( $user_id, $post->ID, true );
								} elseif ( learndash_get_post_type_slug( 'group' ) === $post->post_type ) {
									ld_update_group_access( $user_id, $post->ID, true );
								}
							}
						}
					}
				}
			}
		}

		/**
		 * Show the Course/Group Removal Status.
		 *
		 * @since 3.6.0
		 *
		 * @param int $transaction_id Transaction Post ID.
		 */
		protected function show_column_access_status( $transaction_id = 0 ) {
			$transaction_id = absint( $transaction_id );
			if ( ! empty( $transaction_id ) ) {
				$post_id = (int) get_post_meta( $transaction_id, 'post_id', true );
				$user_id = (int) get_post_meta( $transaction_id, 'user_id', true );

				if ( ( empty( $post_id ) ) || ( empty( $user_id ) ) ) {
					return false;
				}

				$post = get_post( $post_id );
				if ( empty( $post ) || ( ! is_a( $post, 'WP_Post' ) ) ) {
					return false;
				}

				if ( ! in_array( $post->post_type, array( learndash_get_post_type_slug( 'course' ), learndash_get_post_type_slug( 'group' ) ), true ) ) {
					return false;
				}

				$user = get_userdata( $user_id );
				if ( empty( $user ) || ! is_a( $user, 'WP_User' ) ) {
					return false;
				}

				$user_has_access = sfwd_lms_has_access( $post_id, $user_id );
				?>
				<div class="ld-access-status"><?php $user_has_access ? esc_html_e( 'Access', 'learndash' ) : esc_html_e( 'No access', 'learndash' ); ?> </div>
				<?php
				$txn_type         = get_post_meta( $transaction_id, 'txn_type', true );
				$removal_statuses = array( 'return-success', 'subscr_failed', 'subscr_cancel', 'subscr_eot' );
				/**
				 * Filter the PayPal Subscription removal statuses
				 *
				 * @param array  $removal_statuses Array of PayPal IPN subscription statuses
				 * @param object $post             Course or Group Object
				 * @param object $user             WP_User
				 */
				$removal_statuses = apply_filters( 'learndash_paypal_subscription_removal_statuses', $removal_statuses, $post, $user );

				if ( in_array( $txn_type, $removal_statuses, true ) && $user_has_access ) {
					?>
				<div class="ld-removal-action">
				<button id="ld_remove_access_<?php echo absint( $transaction_id ); ?>" class="small ld_remove_access_single"><?php esc_html_e( 'Remove access', 'learndash' ); ?></button>
				</div>
					<?php
				}
			}
		}

		/**
		 * Get transaction processor type.
		 *
		 * @since 3.6.0
		 *
		 * @param int $post_id Transaction Post ID.
		 */
		protected function get_transaction_processor_type( $post_id = 0 ) {
			$ld_payment_processor = '';

			$post_id = absint( $post_id );

			if ( ! empty( $post_id ) ) {
				$ld_payment_processor = get_post_meta( $post_id, 'ld_payment_processor', true );

				if ( empty( $ld_payment_processor ) ) {
					$ipn_track_id = get_post_meta( $post_id, 'ipn_track_id', true );

					if ( ! empty( $ipn_track_id ) ) {
						$ld_payment_processor = 'paypal';
						update_post_meta( $post_id, 'ld_payment_processor', $ld_payment_processor );
					}

					$stripe_session_id = get_post_meta( $post_id, 'stripe_session_id', true );
					$stripe_price      = get_post_meta( $post_id, 'stripe_price', true );

					if ( ! empty( $stripe_session_id ) || ! empty( $stripe_price ) ) {
						$ld_payment_processor = 'stripe';
						update_post_meta( $post_id, 'ld_payment_processor', $ld_payment_processor );
					}
				}
			}

			return $ld_payment_processor;
		}

		// End of functions.
	}
}
new Learndash_Admin_Transactions_Listing();
