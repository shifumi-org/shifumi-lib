<?php
/**
 * LearnDash Settings billing functions
 *
 * @since 3.5.0
 * @package LearnDash
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Billing Cycle field html output for courses
 *
 * @since 3.5.0
 *
 * @param int    $post_id   Post ID.
 * @param string $post_type Post type slug.
 *
 * @return string HTML input and selector for billing cycle field.
 */
function learndash_billing_cycle_setting_field_html( $post_id = 0, $post_type = '' ) {
	$post_id = absint( $post_id );
	if ( empty( $post_id ) ) {
		if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_id = absint( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	}

	$post_type = esc_attr( $post_type );
	if ( empty( $post_type ) ) {
		if ( ! empty( $post_id ) ) {
			$post_type = get_post_type( $post_id );
		}
		if ( ( empty( $post_type ) ) && ( isset( $_GET['post_type'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_type = esc_attr( $_GET['post_type'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		}
	}

	$price_billing_p3 = '';
	$price_billing_t3 = '';

	if ( learndash_get_post_type_slug( 'course' ) === $post_type ) {
		$settings_prefix = 'course';
	} elseif ( learndash_get_post_type_slug( 'group' ) === $post_type ) {
		$settings_prefix = 'group';
	} else {
		$settings_prefix = '';
	}

	if ( ! empty( $post_id ) ) {
		$price_billing_t3 = learndash_get_setting( $post_id, $settings_prefix . '_price_billing_t3' );
		$price_billing_p3 = learndash_get_setting( $post_id, $settings_prefix . '_price_billing_p3' );
	}

	$html  = '<input name="' . $settings_prefix . '_price_billing_p3" type="number" value="' . $price_billing_p3 . '" class="small-text" min="0" can_empty="1" />';
	$html .= '<select class="select_course_price_billing_p3" name="' . $settings_prefix . '_price_billing_t3">';
	$html .= '<option value="">' . esc_html__( 'select interval', 'learndash' ) . '</option>';
	$html .= '<option value="D" ' . selected( $price_billing_t3, 'D', false ) . '>' . esc_html__( 'day(s)', 'learndash' ) . '</option>';
	$html .= '<option value="W" ' . selected( $price_billing_t3, 'W', false ) . '>' . esc_html__( 'week(s)', 'learndash' ) . '</option>';
	$html .= '<option value="M" ' . selected( $price_billing_t3, 'M', false ) . '>' . esc_html__( 'month(s)', 'learndash' ) . '</option>';
	$html .= '<option value="Y" ' . selected( $price_billing_t3, 'Y', false ) . '>' . esc_html__( 'year(s)', 'learndash' ) . '</option>';
	$html .= '</select>';

	/**
	 * Filters billing cycle settings field html.
	 *
	 * @since 3.5.0
	 *
	 * @param string $html      HTML content for settings field.
	 * @param int    $post_id   Post ID.
	 * @param string $post_type Post type slug.
	 */
	return apply_filters( 'learndash_billing_cycle_settings_field_html', $html, $post_id, $post_type );
}

/**
 * Trial duration field html output for courses
 *
 * @since 3.6.0
 *
 * @param int    $post_id   Post ID.
 * @param string $post_type Post type slug.
 *
 * @return string HTML input and selector for trial duration field.
 */
function learndash_trial_duration_setting_field_html( $post_id = 0, $post_type = '' ) {
	$post_id = absint( $post_id );
	if ( empty( $post_id ) ) {
		if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_id = absint( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	}

	$post_type = esc_attr( $post_type );
	if ( empty( $post_type ) ) {
		if ( ! empty( $post_id ) ) {
			$post_type = get_post_type( $post_id );
		}
		if ( ( empty( $post_type ) ) && ( isset( $_GET['post_type'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_type = esc_attr( $_GET['post_type'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		}
	}

	$trial_duration_p1 = '';
	$trial_duration_t1 = '';

	if ( learndash_get_post_type_slug( 'course' ) === $post_type ) {
		$settings_prefix = 'course';
	} elseif ( learndash_get_post_type_slug( 'group' ) === $post_type ) {
		$settings_prefix = 'group';
	} else {
		$settings_prefix = '';
	}

	if ( ! empty( $post_id ) ) {
		$trial_duration_t1 = learndash_get_setting( $post_id, $settings_prefix . '_trial_duration_t1' );
		$trial_duration_p1 = learndash_get_setting( $post_id, $settings_prefix . '_trial_duration_p1' );
	}

	$html  = '<input name="' . $settings_prefix . '_trial_duration_p1" type="number" value="' . $trial_duration_p1 . '" class="small-text" min="0" can_empty="1" />';
	$html .= '<select class="select_course_price_billing_p3" name="' . $settings_prefix . '_trial_duration_t1">';
	$html .= '<option value="">' . esc_html__( 'select interval', 'learndash' ) . '</option>';
	$html .= '<option value="D" ' . selected( $trial_duration_t1, 'D', false ) . '>' . esc_html__( 'day(s)', 'learndash' ) . '</option>';
	$html .= '<option value="W" ' . selected( $trial_duration_t1, 'W', false ) . '>' . esc_html__( 'week(s)', 'learndash' ) . '</option>';
	$html .= '<option value="M" ' . selected( $trial_duration_t1, 'M', false ) . '>' . esc_html__( 'month(s)', 'learndash' ) . '</option>';
	$html .= '<option value="Y" ' . selected( $trial_duration_t1, 'Y', false ) . '>' . esc_html__( 'year(s)', 'learndash' ) . '</option>';
	$html .= '</select>';

	/**
	 * Filters trial duration settings field html.
	 *
	 * @since 3.6.0
	 *
	 * @param string $html      HTML content for settings field.
	 * @param int    $post_id   Post ID.
	 * @param string $post_type Post type slug.
	 */
	return apply_filters( 'learndash_trial_duration_settings_field_html', $html, $post_id, $post_type );
}

/**
 * Validate the billing cycle field frequency.
 *
 * @since 3.5.0
 *
 * @param string $price_billing_t3 Billing frequency code. D, W, M, or Y.
 *
 * @return string Valid frequency or empty string.
 */
function learndash_billing_cycle_field_frequency_validate( $price_billing_t3 = '' ) {
	$price_billing_t3 = strtoupper( $price_billing_t3 );

	if ( ! in_array( $price_billing_t3, array( 'D', 'W', 'M', 'Y' ), true ) ) {
		$price_billing_t3 = '';
	}

	return $price_billing_t3;
}

/**
 * Validate the Billing cycle field interval.
 *
 * @since 3.5.0
 *
 * @param int    $price_billing_p3 The Billing field value.
 * @param string $price_billing_t3 The Billing field context. D, M, W, or Y.
 *
 * @return int Valid interval or zero.
 */
function learndash_billing_cycle_field_interval_validate( $price_billing_p3 = 0, $price_billing_t3 = '' ) {

	$price_billing_t3     = learndash_billing_cycle_field_frequency_validate( $price_billing_t3 );
	$price_billing_p3_max = learndash_billing_cycle_field_frequency_max( $price_billing_t3 );

	switch ( $price_billing_t3 ) {
		case 'D':
			if ( $price_billing_p3 < 1 ) {
				$price_billing_p3 = 1;
			} elseif ( $price_billing_p3 > $price_billing_p3_max ) {
				$price_billing_p3 = $price_billing_p3_max;
			}
			break;

		case 'W':
			if ( $price_billing_p3 < 1 ) {
				$price_billing_p3 = 1;
			} elseif ( $price_billing_p3 > $price_billing_p3_max ) {
				$price_billing_p3 = $price_billing_p3_max;
			}
			break;

		case 'M':
			if ( $price_billing_p3 < 1 ) {
				$price_billing_p3 = 1;
			} elseif ( $price_billing_p3 > $price_billing_p3_max ) {
				$price_billing_p3 = $price_billing_p3_max;
			}
			break;

		case 'Y':
			if ( $price_billing_p3 < 1 ) {
				$price_billing_p3 = 1;
			} elseif ( $price_billing_p3 > $price_billing_p3_max ) {
				$price_billing_p3 = $price_billing_p3_max;
			}
			break;

		default:
			$price_billing_p3 = 0;
	}

	return $price_billing_p3;
}

/**
 * Get the billing cycle field max value for frequency.
 *
 * @since 3.5.0
 *
 * @param string $price_billing_t3 The Billing field context. D, M, W, or Y.
 *
 * @return int Valid interval or zero.
 */
function learndash_billing_cycle_field_frequency_max( $price_billing_t3 = '' ) {
	switch ( $price_billing_t3 ) {
		case 'D':
			$price_billing_p3 = 90;
			break;

		case 'W':
			$price_billing_p3 = 52;
			break;

		case 'M':
			$price_billing_p3 = 24;
			break;

		case 'Y':
			$price_billing_p3 = 5;
			break;

		default:
			$price_billing_p3 = 0;
	}

	return $price_billing_p3;
}

// Yes, global var here. This var is set within the payment button processing. The var will contain HTML for a fancy dropdown.
$dropdown_button = ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

/**
 * Generates the LearnDash payment buttons output.
 *
 * @since 2.1.0
 *
 * @uses learndash_get_function()
 * @uses sfwd_lms_has_access()
 *
 * @param int|WP_Post $post Post ID or `WP_Post` object.
 *
 * @return string The payment buttons HTML output.
 */
function learndash_payment_buttons( $post ) {

	if ( is_numeric( $post ) ) {
		$post_id = $post;
		$post    = get_post( $post_id );
	} elseif ( ! empty( $post->ID ) ) {
		$post_id = $post->ID;
	} else {
		return '';
	}

	$user_id = get_current_user_id();

	if ( ( ! $post ) || ( ! is_a( $post, 'WP_Post' ) ) ) {
		return '';
	}

	$post_price_billing_p3 = '';
	$post_price_billing_t3 = '';
	$post_price_trial_a1   = '';
	$post_price_trial_p1   = '';
	$post_price_trial_t1   = '';

	if ( learndash_get_post_type_slug( 'course' ) === $post->post_type ) {
		if ( sfwd_lms_has_access( $post->ID, $user_id ) ) {
			return '';
		}

		$post_label_prefix = 'course';
		$course_price      = learndash_get_course_price( $post_id );

		$meta              = learndash_get_setting( $post_id );
		$post_price_type   = ( isset( $meta[ $post_label_prefix . '_price_type' ] ) ) ? $meta[ $post_label_prefix . '_price_type' ] : '';
		$post_price        = $course_price['price'];
		$post_no_of_cycles = ( isset( $meta['course_no_of_cycles'] ) ) ? $meta['course_no_of_cycles'] : '';
		$post_button_url   = ( isset( $meta['custom_button_url'] ) ) ? $meta['custom_button_url'] : '';
		$post_button_label = ( isset( $meta['custom_button_label'] ) ) ? $meta['custom_button_label'] : '';

		$post_srt = '';
		if ( 'subscribe' === $post_price_type ) {
			$post_price_billing_p3 = get_post_meta( $post_id, $post_label_prefix . '_price_billing_p3', true );
			$post_price_billing_t3 = get_post_meta( $post_id, $post_label_prefix . '_price_billing_t3', true );
			$post_price_trial_a1   = learndash_get_setting( $post_id, 'course_trial_price' );
			$post_price_trial_p1   = learndash_get_setting( $post_id, 'course_trial_duration_p1' );
			$post_price_trial_t1   = learndash_get_setting( $post_id, 'course_trial_duration_t1' );
			$post_srt              = intval( $post_no_of_cycles );
		}

		if ( empty( $post_button_label ) ) {
			$button_text = LearnDash_Custom_Label::get_label( 'button_take_this_course' );
		} else {
			$button_text = esc_attr( $post_button_label );
		}
	} elseif ( learndash_get_post_type_slug( 'group' ) === $post->post_type ) {
		if ( learndash_is_user_in_group( $user_id, $post_id ) ) {
			return '';
		}

		$post_label_prefix = 'group';
		$group_price       = learndash_get_group_price( $post_id );

		$meta              = learndash_get_setting( $post_id );
		$post_price_type   = ( isset( $meta[ $post_label_prefix . '_price_type' ] ) ) ? $meta[ $post_label_prefix . '_price_type' ] : '';
		$post_price        = $group_price['price'];
		$post_no_of_cycles = ( isset( $meta['post_no_of_cycles'] ) ) ? $meta['post_no_of_cycles'] : '';
		$post_button_url   = ( isset( $meta['custom_button_url'] ) ) ? $meta['custom_button_url'] : '';
		$post_button_label = ( isset( $meta['custom_button_label'] ) ) ? $meta['custom_button_label'] : '';

		$post_srt = '';
		if ( 'subscribe' === $post_price_type ) {
			$post_price_billing_p3 = get_post_meta( $post_id, $post_label_prefix . '_price_billing_p3', true );
			$post_price_billing_t3 = get_post_meta( $post_id, $post_label_prefix . '_price_billing_t3', true );
			$post_price_trial_a1   = learndash_get_setting( $post_id, 'group_trial_price' );
			$post_price_trial_p1   = learndash_get_setting( $post_id, 'group_trial_duration_p1' );
			$post_price_trial_t1   = learndash_get_setting( $post_id, 'group_trial_duration_t1' );
			$post_srt              = intval( $post_no_of_cycles );
		}

		if ( empty( $post_button_label ) ) {
			$button_text = LearnDash_Custom_Label::get_label( 'button_take_this_group' );
		} else {
			$button_text = esc_attr( $post_button_label );
		}
	} else {
		return '';
	}

	// format the Course price to be proper XXX.YY no leading dollar signs or other values.
	if ( ( 'paynow' === $post_price_type ) || ( 'subscribe' === $post_price_type ) ) {
		if ( '' !== $post_price ) {
			$post_price = number_format( learndash_get_price_as_float( $post_price ), 2, '.', '' );
		}
		if ( '' !== $post_price_trial_a1 ) {
			$post_price_trial_a1 = number_format( learndash_get_price_as_float( $post_price_trial_a1 ), 2, '.', '' );
		}
	}

	$paypal_settings = LearnDash_Settings_Section::get_section_settings_all( 'LearnDash_Settings_Section_PayPal' );
	if ( ! empty( $paypal_settings ) ) {
		$paypal_settings['paypal_sandbox'] = ( 'yes' === $paypal_settings['paypal_sandbox'] ) ? 1 : 0;
	}

	if ( ! isset( $paypal_settings['enabled'] ) || ! isset( $paypal_settings['paypal_email'] ) || empty( $paypal_settings['paypal_email'] ) ) {
		$paypal_settings['enabled'] = '';
	}

	if ( ( ! empty( $post_price_type ) ) && ( 'closed' === $post_price_type ) ) {

		if ( empty( $post_button_url ) ) {
			$post_button = '';
		} else {
			$post_button_url = trim( $post_button_url );
			/**
			 * If the value does NOT start with [http://, https://, /] we prepend the home URL.
			 */
			if ( ( stripos( $post_button_url, 'http://', 0 ) !== 0 ) && ( stripos( $post_button_url, 'https://', 0 ) !== 0 ) && ( strpos( $post_button_url, '/', 0 ) !== 0 ) ) {
				$post_button_url = get_home_url( null, $post_button_url );
			}
			$post_button = '<a class="btn-join" href="' . esc_url( $post_button_url ) . '" id="btn-join">' . $button_text . '</a>';
		}

		$payment_params = array(
			'custom_button_url' => $post_button_url,
			'post'              => $post,
		);

		/**
		 * Filters the closed course payment button markup.
		 *
		 * @since 2.1.0
		 *
		 * @param string $custom_button  Payment button markup for closed course.
		 * @param array  $payment_params An array of payment parameter details.
		 */
		return apply_filters( 'learndash_payment_closed_button', $post_button, $payment_params );

	} elseif ( ! empty( $post_price ) ) {
		$current_page_id = get_the_ID();

		$ld_registration_page_id = 0;
		if ( ( ! is_multisite() ) && ( 'legacy' !== LearnDash_Theme_Register::get_active_theme_key() ) ) {
			$ld_registration_page_id = (int) LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_Registration_Pages', 'registration' );
		}

		$paypal_button = '';

		if ( ( ! empty( $ld_registration_page_id ) ) && ( $current_page_id !== $ld_registration_page_id ) ) {
			$payment_buttons = '<form action="' . esc_url( get_permalink( $ld_registration_page_id ) ) . '" method="get">';
			if ( empty( get_option( 'permalink_structure' ) ) ) {
				$payment_buttons .= '<input type="hidden" value="' . $ld_registration_page_id . '" name="page_id" />';
			}
			$payment_buttons .= '<input type="hidden" value="' . absint( $post->ID ) . '" name="ld_register_id" />';
			$payment_buttons .= '<input type="submit" class="btn-join" id="btn-join" value="' . $button_text . '" />';
			$payment_buttons .= '</form>';
			return $payment_buttons;

		} elseif ( ( empty( $ld_registration_page_id ) ) || ( $current_page_id === $ld_registration_page_id ) ) {
			if ( ( ! empty( $ld_registration_page_id ) ) && ( ( ! isset( $_GET['ld_register_id'] ) ) || ( empty( $_GET['ld_register_id'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return '';
			}

			include_once LEARNDASH_LMS_LIBRARY_DIR . '/paypal/enhanced-paypal-shortcodes.php';
			if ( ! empty( $paypal_settings['enabled'] ) ) {

				$user_hash = learndash_paypal_init_user_purchase_hash( $user_id, $post_id );
				if ( ! empty( $user_hash ) ) {
					/**
					 * Note on the URL lines below, yes, we are using the notify URL as start. We want to funnel
					 * all PayPal URL through a central URL.
					 */
					$paypal_settings['paypal_returnurl'] = add_query_arg( 'return-success', $user_hash, $paypal_settings['paypal_notifyurl'] );
					$paypal_settings['paypal_cancelurl'] = add_query_arg( 'return-cancel', $user_hash, $paypal_settings['paypal_notifyurl'] );

					// Do the 'paypal_notifyurl' last since it is used as a source on the other URLs.
					$paypal_settings['paypal_notifyurl'] = add_query_arg( 'return-notify', $user_hash, $paypal_settings['paypal_notifyurl'] );
				}

				if ( ( isset( $paypal_settings['paypal_notifyurl'] ) ) && ( ! empty( $paypal_settings['paypal_notifyurl'] ) ) ) {
					$paypal_settings['paypal_notifyurl'] = esc_url_raw( $paypal_settings['paypal_notifyurl'] );
				}
				if ( ( isset( $paypal_settings['paypal_returnurl'] ) ) && ( ! empty( $paypal_settings['paypal_notifyurl'] ) ) ) {
					$paypal_settings['paypal_returnurl'] = esc_url_raw( $paypal_settings['paypal_returnurl'] );
				}
				if ( ( isset( $paypal_settings['paypal_cancelurl'] ) ) && ( ! empty( $paypal_settings['paypal_cancelurl'] ) ) ) {
					$paypal_settings['paypal_cancelurl'] = esc_url_raw( $paypal_settings['paypal_cancelurl'] );
				}

				$post_title = str_replace( array( '[', ']' ), array( '', '' ), $post->post_title );

				/** This filter is documented in includes/payments/class-learndash-stripe-connect-checkout-integration.php */
				$post_price = apply_filters(
					'learndash_get_price_by_coupon',
					learndash_get_price_as_float( $post_price ),
					$post->ID,
					get_current_user_id()
				);

				$post_price = number_format( $post_price, 2, '.', '' );

				$currency_code = learndash_get_currency_code();
				if ( empty( $post_price_type ) || 'paynow' === $post_price_type ) {
					$shortcode_content = do_shortcode( '[paypal type="paynow" amount="' . $post_price . '" sandbox="' . $paypal_settings['paypal_sandbox'] . '" email="' . $paypal_settings['paypal_email'] . '" itemno="' . $post->ID . '" name="' . $post_title . '" noshipping="1" nonote="1" qty="1" currencycode="' . $currency_code . '" rm="2" notifyurl="' . $paypal_settings['paypal_notifyurl'] . '" returnurl="' . $paypal_settings['paypal_returnurl'] . '" cancelurl="' . $paypal_settings['paypal_cancelurl'] . '" imagewidth="100px" pagestyle="paypal" lc="' . $paypal_settings['paypal_country'] . '" cbt="' . esc_html__( 'Complete Your Purchase', 'learndash' ) . '" custom="' . $user_id . '"]' );
					if ( ! empty( $shortcode_content ) ) {
						$paypal_button = wptexturize( '<div class="learndash_checkout_button learndash_paypal_button">' . $shortcode_content . '</div>' );
					}
				} elseif ( 'subscribe' === $post_price_type ) {

					$shortcode_content = do_shortcode( '[paypal type="subscribe" a1="' . $post_price_trial_a1 . '" p1="' . $post_price_trial_p1 . '" t1="' . $post_price_trial_t1 . '" a3="' . $post_price . '" p3="' . $post_price_billing_p3 . '" t3="' . $post_price_billing_t3 . '" sandbox="' . $paypal_settings['paypal_sandbox'] . '" email="' . $paypal_settings['paypal_email'] . '" itemno="' . $post->ID . '" name="' . $post_title . '" noshipping="1" nonote="1" qty="1" currencycode="' . $currency_code . '" rm="2" notifyurl="' . $paypal_settings['paypal_notifyurl'] . '" cancelurl="' . $paypal_settings['paypal_cancelurl'] . '" returnurl="' . $paypal_settings['paypal_returnurl'] . '" imagewidth="100px" pagestyle="paypal" lc="' . $paypal_settings['paypal_country'] . '" cbt="' . esc_html__( 'Complete Your Purchase', 'learndash' ) . '" custom="' . $user_id . '" srt="' . $post_srt . '"]' );

					if ( ! empty( $shortcode_content ) ) {
						$paypal_button = wptexturize( '<div class="learndash_checkout_button learndash_paypal_button">' . $shortcode_content . '</div>' );
					}
				}
			}

			$payment_params = array(
				'price' => $post_price,
				'post'  => $post,
			);

			/**
			 * Filters PayPal payment button markup.
			 *
			 * @since 2.1.0
			 *
			 * @param string $payment_button Payment button markup.
			 * @param array  $payment_params Payment parameters.
			 */
			$payment_buttons = apply_filters( 'learndash_payment_button', $paypal_button, $payment_params );

			global $learndash_stripe_script_loaded;
			global $learndash_stripe_connect_loaded;
			global $learndash_razorpay_loaded;

			$loaded_payment_buttons_number = count(
				array_filter(
					array(
						! empty( $paypal_button ),
						(bool) $learndash_stripe_script_loaded,
						(bool) $learndash_stripe_connect_loaded,
						(bool) $learndash_razorpay_loaded,
					)
				)
			);

			if ( ! empty( $payment_buttons ) ) {
				if ( $payment_buttons === $paypal_button || 1 === $loaded_payment_buttons_number ) {
					return '<div id="learndash_checkout_buttons_course_' . $post->ID . '" class="learndash_checkout_buttons">' . $payment_buttons . '</div>';
				} else {
					$button  = '<div id="learndash_checkout_buttons_course_' . $post->ID . '" class="learndash_checkout_buttons">';
					$button .= '<input id="btn-join-' . $post->ID . '" class="btn-join btn-join-' . $post->ID . ' button learndash_checkout_button" data-jq-dropdown="#jq-dropdown-' . $post->ID . '" type="button" value="' . $button_text . '" />';
					$button .= '</div>';

					global $dropdown_button;
					// phpcs
					//:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
					$dropdown_button .= '<div id="jq-dropdown-' . esc_attr( $post->ID ) . '" class="jq-dropdown jq-dropdown-tip checkout-dropdown-button">';
					$dropdown_button .= '<ul class="jq-dropdown-menu">';
					$dropdown_button .= '<li>';
					$dropdown_button .= str_replace(
						$button_text,
						empty( $paypal_button ) ? esc_html__( 'Use Credit Card', 'learndash' ) : esc_html__( 'Use Paypal', 'learndash' ),
						$payment_buttons
					);
					$dropdown_button .= '</li>';
					$dropdown_button .= '</ul>';
					$dropdown_button .= '</div>';
					// phpcs:enable

					/**
					 * Filters Dropdown payment button markup.
					 *
					 * @param string $button Dropdown payment button markup.
					 */
					return apply_filters( 'learndash_dropdown_payment_button', $button );
				}
			}
		}
	} else {
		$join_button = '<div class="learndash_join_button"><form action="' . get_permalink( $post->ID ) . '" method="post">
							<input type="hidden" value="' . $post->ID . '" name="' . $post_label_prefix . '_id" />
							<input type="hidden" name="' . $post_label_prefix . '_join" value="' . wp_create_nonce( $post_label_prefix . '_join_' . get_current_user_id() . '_' . $post->ID ) . '" />
							<input type="submit" value="' . $button_text . '" class="btn-join" id="btn-join" />
						</form></div>';

		$payment_params = array(
			'price'                            => '0',
			'post'                             => $post,
			$post_label_prefix . '_price_type' => $post_price_type,
		);

		/** This filter is documented in includes/ld-misc-functions.php */
		$payment_buttons = apply_filters( 'learndash_payment_button', $join_button, $payment_params );
		return $payment_buttons;
	}

	return '';
}

