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
				'transaction_type' => array(
					'type'                   => 'early',
					'show_all_value'         => '',
					'show_all_label'         => esc_html__( 'Show All Transactions Types', 'learndash' ),
					'options'                => array(
						'paypal' => esc_html__( 'PayPal', 'learndash' ),
						'stripe' => esc_html__( 'Stripe', 'learndash' ),
					),
					'listing_query_function' => array( $this, 'listing_filter_by_transaction_type' ),
				),
				'course_id'        => array(
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
				'group_id'         => array(
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
				'transaction_type' => array(
					'label'   => esc_html__( 'Transaction Type', 'learndash' ),
					'after'   => 'date',
					'display' => array( $this, 'show_column_transaction_type' ),
				),
				'course_group_id'  => array(
					'label'   => sprintf(
						// translators: placeholder: Course, Group.
						esc_html_x( 'Enrolled %1$s / %2$s', 'placeholder: Course, Group', 'learndash' ),
						LearnDash_Custom_Label::get_label( 'course' ),
						LearnDash_Custom_Label::get_label( 'group' )
					),
					'after'   => 'transaction_type',
					'display' => array( $this, 'show_column_transaction_course_group_id' ),
				),
				'user_id'          => array(
					'label'   => esc_html__( 'User', 'learndash' ),
					'after'   => 'course_group_id',
					'display' => array( $this, 'show_column_transaction_user_id' ),
				),
			);

			parent::listing_init();

			$this->listing_init_done = true;
		}

		/**
		 * Filter the main query listing by the transaction_type
		 *
		 * @since 3.2.3
		 *
		 * @param object $q_vars   Query vars used for the table listing
		 * @param array  $selector Selector array.
		 *
		 * @return object $q_vars.
		 */
		protected function listing_filter_by_transaction_type( $q_vars, $selector = array() ) {
			if ( ( isset( $selector['selected'] ) ) && ( ! empty( $selector['selected'] ) ) ) {
				if ( ! isset( $q_vars['meta_query'] ) ) {
					$q_vars['meta_query'] = array();
				}

				if ( 'paypal' === $selector['selected'] ) {
					$q_vars['meta_query'][] = array(
						'key'     => 'ipn_track_id',
						'compare' => 'EXISTS',
					);
				} elseif ( 'stripe' === $selector['selected'] ) {
					$q_vars['meta_query'][] = array(
						'key'     => 'stripe_nonce',
						'compare' => 'EXISTS',
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
		 * @param object $q_vars   Query vars used for the table listing
		 * @param array  $selector Selector array.
		 *
		 * @return object $q_vars.
		 */
		protected function listing_filter_by_transaction_course_id( $q_vars, $selector = array() ) {
			if ( ( isset( $selector['selected'] ) ) && ( ! empty( $selector['selected'] ) ) ) {
				if ( ! isset( $q_vars['meta_query'] ) ) {
					$q_vars['meta_query'] = array();
				}

				$q_vars['meta_query'][] = array(
					'key'   => 'course_id',
					'value' => absint( $selector['selected'] ),
				);
			}

			return $q_vars;
		}

		/**
		 * Filter the main query listing by the group_id
		 *
		 * @since 3.2.3
		 *
		 * @param object $q_vars   Query vars used for the table listing
		 * @param array  $selector Selector array.
		 *
		 * @return object $q_vars
		 */
		protected function listing_filter_by_transaction_group_id( $q_vars, $selector = array() ) {
			if ( ( isset( $selector['selected'] ) ) && ( ! empty( $selector['selected'] ) ) ) {
				if ( ! isset( $q_vars['meta_query'] ) ) {
					$q_vars['meta_query'] = array();
				}

				$q_vars['meta_query'][] = array(
					'key'   => 'group_id',
					'value' => absint( $selector['selected'] ),
				);
			}

			return $q_vars;
		}

		/**
		 * Output the Transaction Type column.
		 *
		 * @since 3.2.3
		 *
		 * @param int $post_id Transaction Post ID.
		 */
		protected function show_column_transaction_type( $post_id = 0 ) {
			$post_id = absint( $post_id );

			$ipn_track_id = get_post_meta( $post_id, 'ipn_track_id', true );
			$stripe_nonce = get_post_meta( $post_id, 'stripe_nonce', true );
			if ( ! empty( $ipn_track_id ) ) {
				$payment_amount = get_post_meta( $post_id, 'mc_gross', true );
				if ( '' === $payment_amount ) {
					$payment_amount = '0.00';
				}
				$payment_amount   = number_format_i18n( $payment_amount, 2 );
				$payment_currency = get_post_meta( $post_id, 'mc_currency', true );
				echo sprintf(
					// translators: placeholder: PayPal Purchase price, Stripe Currency.
					esc_html_x( 'PayPal: %1$s %2$s', 'placeholder: PayPal Purchase price, Stripe Currency', 'learndash' ),
					esc_attr( $payment_amount ),
					esc_attr( strtoupper( $payment_currency ) )
				);
			} elseif ( ! empty( $stripe_nonce ) ) {
				$payment_amount = get_post_meta( $post_id, 'stripe_price', true );
				if ( '' === $payment_amount ) {
					$payment_amount = '0.00';
				}
				$payment_amount   = number_format_i18n( $payment_amount, 2 );
				$payment_currency = get_post_meta( $post_id, 'stripe_currency', true );
				echo sprintf(
					// translators: placeholder: Stripe Purchase price, Stripe Currency.
					esc_html_x( 'Stripe: %1$s %2$s', 'placeholder: Stripe Purchase price, Stripe Currency', 'learndash' ),
					esc_attr( $payment_amount ),
					esc_attr( strtoupper( $payment_currency ) )
				);
			}
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

			$course_id = get_post_meta( $post_id, 'course_id', true );
			$course_id = absint( $course_id );
			if ( ! empty( $course_id ) ) {
				$row_actions = array();
				echo sprintf(
					// translators: placeholder: Course.
					esc_html_x( '%s : ', 'placeholder: Course', 'learndash' ),
					LearnDash_Custom_Label::get_label( 'course' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Method escapes output
				);

				$filter_url = add_query_arg( 'course_id', $course_id, $this->get_clean_filter_url() );
				echo '<a href="' . esc_url( $filter_url ) . '">' . wp_kses_post( get_the_title( $course_id ) ) . '</a>';
				$row_actions['ld-post-filter'] = '<a href="' . esc_url( $filter_url ) . '">' . esc_html__( 'filter', 'learndash' ) . '</a>';

				if ( current_user_can( 'edit_post', $course_id ) ) {
					$row_actions['ld-post-edit'] = '<a href="' . esc_url( get_edit_post_link( $course_id ) ) . '">' . esc_html__( 'edit', 'learndash' ) . '</a>';
				}

				if ( is_post_type_viewable( get_post_type( $course_id ) ) ) {
					$row_actions['ld-post-view'] = '<a href="' . esc_url( get_permalink( $course_id ) ) . '">' . esc_html__( 'view', 'learndash' ) . '</a>';
				}
				echo $this->list_table_row_actions( $row_actions ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Need to output HTML
			} else {
				$group_id = get_post_meta( $post_id, 'group_id', true );
				$group_id = absint( $group_id );
				if ( ! empty( $group_id ) ) {
					$row_actions = array();

					echo sprintf(
						// translators: placeholder: Group.
						esc_html_x( '%s : ', 'placeholder: Group', 'learndash' ),
						LearnDash_Custom_Label::get_label( 'group' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Method escapes output
					);

					$filter_url = add_query_arg( 'group_id', $group_id, $this->get_clean_filter_url() );
					echo '<a href="' . esc_url( $filter_url ) . '">' . wp_kses_post( get_the_title( $group_id ) ) . '</a>';
					$row_actions['ld-post-filter'] = '<a href="' . esc_url( $filter_url ) . '">' . esc_html__( 'filter', 'learndash' ) . '</a>';

					if ( current_user_can( 'edit_post', $group_id ) ) {
						$row_actions['ld-post-edit'] = '<a href="' . esc_url( get_edit_post_link( $group_id ) ) . '">' . esc_html__( 'edit', 'learndash' ) . '</a>';
					}

					if ( is_post_type_viewable( get_post_type( $group_id ) ) ) {
						$row_actions['ld-post-view'] = '<a href="' . esc_url( get_permalink( $group_id ) ) . '">' . esc_html__( 'view', 'learndash' ) . '</a>';
					}

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

			$ipn_track_id = get_post_meta( $post_id, 'ipn_track_id', true );
			$stripe_nonce = get_post_meta( $post_id, 'stripe_nonce', true );
			if ( ! empty( $ipn_track_id ) ) {
				$email = get_post_meta( $post_id, 'payer_email', true );
				if ( ! empty( $email ) ) {
					$user = get_user_by( 'email', $email );
				}
			} elseif ( ! empty( $stripe_nonce ) ) {
				$user_id = get_post_meta( $post_id, 'user_id', true );
				if ( ! empty( $user_id ) ) {
					$user = get_user_by( 'ID', $user_id );
				}
			}

			if ( ( ! empty( $user ) ) && ( is_a( $user, 'WP_User' ) ) ) {
				$display_name = $user->display_name . ' (' . $user->user_email . ')';
				if ( current_user_can( 'edit_users' ) ) {
					$edit_url = get_edit_user_link( $user->ID );
					echo '<a href="' . esc_url( $edit_url ) . '">' . esc_html( $display_name ) . '</a>';
					$row_actions['edit'] = '<a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'edit', 'learndash' ) . '</a>';
				} else {
					echo esc_html( $display_name );
				}
				echo $this->list_table_row_actions( $row_actions ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Need to output HTML
			}
		}

		// End of functions.
	}
}
new Learndash_Admin_Transactions_Listing();
