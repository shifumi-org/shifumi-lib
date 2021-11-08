<?php
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
		if ( isset( $_GET['post'] ) ) {
			$post_id = absint( $_GET['post'] );
		}
	}

	$post_type = esc_attr( $post_type );
	if ( empty( $post_type ) ) {
		if ( ! empty( $post_id ) ) {
			$post_type = get_post_type( $post_id );
		}
		if ( ( empty( $post_type ) ) && ( isset( $_GET['post_type'] ) ) ) {
			$post_type = esc_attr( $_GET['post_type'] );
		}
	}

	$price_billing_p3 = '';
	$price_billing_t3 = '';

	if ( learndash_get_post_type_slug( 'course' ) === $post_type ) {
		$settings_prefix = 'course';
	} elseif ( learndash_get_post_type_slug( 'group' ) === $post_type ) {
		$settings_prefix = 'group';
	} else {
		return '';
	}

	if ( ! empty( $post_id ) ) {
		$price_billing_t3 = learndash_get_setting( $post_id, $settings_prefix . '_price_billing_t3' );
		$price_billing_p3 = learndash_get_setting( $post_id, $settings_prefix . '_price_billing_p3' );
	}

	$html  = '<input name="' . $settings_prefix . '_price_billing_p3" type="number" value="' . $price_billing_p3 . '" class="small-text" min="0" />';
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

// Yes, global var here. This var is set within the payment button processing. The var will contain HTML for a fancy dropdown
$dropdown_button = ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

/**
 * Generates the LearnDash payment buttons output.
 *
 * @since 2.1.0
 *
 * @uses learndash_get_function()
 * @uses sfwd_lms_has_access()
 *
 * @param int|WP_Post $course course ID or `WP_Post` course object.
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

	if ( learndash_get_post_type_slug( 'course' ) === $post->post_type ) {
		if ( sfwd_lms_has_access( $post->ID, $user_id ) ) {
			return '';
		}

		$post_label_prefix = 'course';

		$meta              = learndash_get_setting( $post_id );
		$post_price_type   = ( isset( $meta[ $post_label_prefix . '_price_type' ] ) ) ? $meta[ $post_label_prefix . '_price_type' ] : '';
		$post_price        = ( isset( $meta[ $post_label_prefix . '_price' ] ) ) ? $meta[ $post_label_prefix . '_price' ] : '';
		$post_no_of_cycles = ( isset( $meta['course_no_of_cycles'] ) ) ? $meta['course_no_of_cycles'] : '';
		$post_button_url   = ( isset( $meta['custom_button_url'] ) ) ? $meta['custom_button_url'] : '';
		$post_button_label = ( isset( $meta['custom_button_label'] ) ) ? $meta['custom_button_label'] : '';

		$post_srt = '';
		if ( 'subscribe' === $post_price_type ) {
			$post_price_billing_p3 = get_post_meta( $post_id, $post_label_prefix . '_price_billing_p3', true );
			$post_price_billing_t3 = get_post_meta( $post_id, $post_label_prefix . '_price_billing_t3', true );
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

		$meta              = learndash_get_setting( $post_id );
		$post_price_type   = ( isset( $meta[ $post_label_prefix . '_price_type' ] ) ) ? $meta[ $post_label_prefix . '_price_type' ] : '';
		$post_price        = ( isset( $meta[ $post_label_prefix . '_price' ] ) ) ? $meta[ $post_label_prefix . '_price' ] : '';
		$post_no_of_cycles = ( isset( $meta[ $post_label_prefix . '_no_of_cycles' ] ) ) ? $meta[ $post_label_prefix . '_no_of_cycles' ] : '';
		$post_button_url   = ( isset( $meta['custom_button_url'] ) ) ? $meta['custom_button_url'] : '';
		$post_button_label = ( isset( $meta['custom_button_label'] ) ) ? $meta['custom_button_label'] : '';

		$post_srt = '';
		if ( 'subscribe' === $post_price_type ) {
			$post_price_billing_p3 = learndash_get_setting( $post_id, 'course_price_billing_p3' );
			$post_price_billing_t3 = learndash_get_setting( $post_id, 'course_price_billing_t3' );
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
			$post_price = preg_replace( '/[^0-9.]/', '', $post_price );
			$post_price = number_format( floatval( $post_price ), 2, '.', '' );
		}
	}

	$paypal_settings = LearnDash_Settings_Section::get_section_settings_all( 'LearnDash_Settings_Section_PayPal' );
	if ( ! empty( $paypal_settings ) ) {
		$paypal_settings['paypal_sandbox'] = ( 'yes' === $paypal_settings['paypal_sandbox'] ) ? 1 : 0;
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
		 * @param array  $payment_params An array of payment paramter details.
		 */
		return apply_filters( 'learndash_payment_closed_button', $post_button, $payment_params );

	} elseif ( ! empty( $post_price ) ) {
		include_once LEARNDASH_LMS_LIBRARY_DIR . '/paypal/enhanced-paypal-shortcodes.php';

		$paypal_button = '';

		if ( ! empty( $paypal_settings['paypal_email'] ) ) {

			$post_title = str_replace( array( '[', ']' ), array( '', '' ), $post->post_title );

			if ( empty( $post_price_type ) || 'paynow' === $post_price_type ) {
				$shortcode_content = do_shortcode( '[paypal type="paynow" amount="' . $post_price . '" sandbox="' . $paypal_settings['paypal_sandbox'] . '" email="' . $paypal_settings['paypal_email'] . '" itemno="' . $post->ID . '" name="' . $post_title . '" noshipping="1" nonote="1" qty="1" currencycode="' . $paypal_settings['paypal_currency'] . '" rm="2" notifyurl="' . $paypal_settings['paypal_notifyurl'] . '" returnurl="' . $paypal_settings['paypal_returnurl'] . '" cancelurl="' . $paypal_settings['paypal_cancelurl'] . '" imagewidth="100px" pagestyle="paypal" lc="' . $paypal_settings['paypal_country'] . '" cbt="' . esc_html__( 'Complete Your Purchase', 'learndash' ) . '" custom="' . $user_id . '"]' );
				if ( ! empty( $shortcode_content ) ) {
					$paypal_button = wptexturize( '<div class="learndash_checkout_button learndash_paypal_button">' . $shortcode_content . '</div>' );
				}
			} elseif ( 'subscribe' === $post_price_type ) {

				$shortcode_content = do_shortcode( '[paypal type="subscribe" a3="' . $post_price . '" p3="' . $post_price_billing_p3 . '" t3="' . $post_price_billing_t3 . '" sandbox="' . $paypal_settings['paypal_sandbox'] . '" email="' . $paypal_settings['paypal_email'] . '" itemno="' . $post->ID . '" name="' . $post_title . '" noshipping="1" nonote="1" qty="1" currencycode="' . $paypal_settings['paypal_currency'] . '" rm="2" notifyurl="' . $paypal_settings['paypal_notifyurl'] . '" cancelurl="' . $paypal_settings['paypal_cancelurl'] . '" returnurl="' . $paypal_settings['paypal_returnurl'] . '" imagewidth="100px" pagestyle="paypal" lc="' . $paypal_settings['paypal_country'] . '" cbt="' . esc_html__( 'Complete Your Purchase', 'learndash' ) . '" custom="' . $user_id . '" srt="' . $post_srt . '"]' );

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
		 * @param array  $payment_params An array of payment paramter details.
		 */
		$payment_buttons = apply_filters( 'learndash_payment_button', $paypal_button, $payment_params );

		if ( ! empty( $payment_buttons ) ) {

			if ( ( ! empty( $paypal_button ) ) && ( $payment_buttons != $paypal_button ) ) {

				$button  = '';
				$button .= '<div id="learndash_checkout_buttons_course_' . $post->ID . '" class="learndash_checkout_buttons">';
				$button .= '<input id="btn-join-' . $post->ID . '" class="btn-join btn-join-' . $post->ID . ' button learndash_checkout_button" data-jq-dropdown="#jq-dropdown-' . $post->ID . '" type="button" value="' . $button_text . '" />';
				$button .= '</div>';

				global $dropdown_button;
				// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				$dropdown_button .= '<div id="jq-dropdown-' . esc_attr( $post->ID ) . '" class="jq-dropdown jq-dropdown-tip checkout-dropdown-button">';
				$dropdown_button .= '<ul class="jq-dropdown-menu">';
				$dropdown_button .= '<li>';
				$dropdown_button .= str_replace( $button_text, esc_html__( 'Use Paypal', 'learndash' ), $payment_buttons );
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

			} else {
				return '<div id="learndash_checkout_buttons_course_' . $post->ID . '" class="learndash_checkout_buttons">' . $payment_buttons . '</div>';
			}
		}
	} else {
		$join_button = '<div class="learndash_join_button"><form method="post">
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
}
