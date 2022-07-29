<?php
/**
 * Functions related to payments
 *
 * @since 4.1.0
 *
 * @package LearnDash
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const LEARNDASH_PRICE_TYPE_PAYNOW    = 'paynow';
const LEARNDASH_PRICE_TYPE_SUBSCRIBE = 'subscribe';

/**
 * Outputs the LearnDash global currency symbol.
 *
 * @since 4.1.0
 */
function learndash_the_currency_symbol() {
	echo wp_kses_post( learndash_get_currency_symbol() );
}

/**
 * Gets the LearnDash global currency symbol.
 *
 * @since 4.1.0
 * @since 4.2.0 Add $currency_code parameter
 *
 * @param string $currency_code Optional. The country currency code (@since 4.2.0).
 *
 * @return string Returns currency symbol.
 */
function learndash_get_currency_symbol( $currency_code = '' ) {
	if ( ! empty( $currency_code ) ) {
		$currency = $currency_code;
	} else {
		$currency = learndash_get_currency_code();
	}

	if ( ! empty( $currency ) && class_exists( 'NumberFormatter' ) ) {
		$locale        = get_locale();
		$number_format = new NumberFormatter( $locale . '@currency=' . $currency, NumberFormatter::CURRENCY );
		$currency      = $number_format->getSymbol( NumberFormatter::CURRENCY_SYMBOL );
	}

	/**
	 * Filter the LearnDash global currency symbol.
	 *
	 * @since 4.1.0
	 *
	 * @param string $currency The currency symbol.
	 */
	return apply_filters( 'learndash_currency_symbol', $currency );
}

/**
 * Gets the LearnDash global currency code.
 *
 * @since 4.1.0
 *
 * @return string Returns currency code.
 */
function learndash_get_currency_code() {
	$currency = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_Payments_Defaults', 'currency' );

	/**
	 * Filter the LearnDash global currency code.
	 *
	 * @since 4.1.0
	 *
	 * @param string $currency The currency code.
	 */
	return apply_filters( 'learndash_currency_code', trim( $currency ) );
}

/**
 * Gets the price formatted based on the LearnDash global currency configuration.
 *
 * @since 4.1.0
 * @since 4.2.0 Added $currency_code parameter
 *
 * @param mixed  $price The price to format.
 * @param string $currency_code Optional. The country currency code (@since 4.2.0).
 *
 * @return string Returns price formatted.
 */
function learndash_get_price_formatted( $price, $currency_code = '' ) {
	if ( ! empty( $currency_code ) ) {
		$currency_code = $currency_code;
	} else {
		$currency_code = learndash_get_currency_code();
	}

	if ( '' === $price ) {
		return ''; // empty prices should not be displayed.
	}

	if ( empty( $currency_code ) ) {
		return $price; // no currency set.
	}

	if ( ! is_numeric( $price ) ) {
		return $price; // non-numeric price is shown as is.
	}

	if ( class_exists( 'NumberFormatter' ) ) {
		$locale          = get_locale();
		$number_format   = new NumberFormatter( $locale . '@currency=' . $currency_code, NumberFormatter::CURRENCY );
		$price_formatted = $number_format->format( floatval( $price ) );
	} else {
		$currency_symbol = learndash_get_currency_symbol( $currency_code );
		if ( strlen( $currency_symbol ) > 1 ) {
			// it's currency code: we should display at the end of the price.
			$price_formatted = "$price $currency_symbol";
		} else {
			// show the currency symbol at the beginning of the price (en_US style).
			$price_formatted = "$currency_symbol{$price}";
		}
	}

	return $price_formatted;
}


/**
 * Gets the price as float value.
 *
 * @since 4.1.1
 *
 * @param string $price The price to convert.
 *
 * @return float Returns price as float value.
 */
function learndash_get_price_as_float( string $price ): float {
	if ( is_numeric( $price ) ) {
		return floatval( $price );
	}

	// trying to convert it into a numeric string.
	$dot_position   = strpos( $price, '.' );
	$comma_position = strpos( $price, ',' );

	if ( false !== $dot_position && false !== $comma_position ) {
		if ( $dot_position < $comma_position ) {
			// dot is before comma. Comma is decimal separator.
			$price = str_replace( '.', '', $price ); // remove dot.
			$price = str_replace( ',', '.', $price ); // convert comma to dot.
		} else {
			// comma is before dot. Dot is decimal separator.
			$price = str_replace( ',', '', $price ); // remove comma.
		}
	} elseif ( ! empty( $comma_position ) ) {
		$number_before_comma      = (int) mb_substr( $price, 0, $comma_position );
		$digits_count_after_comma = mb_strlen( mb_substr( $price, $comma_position + 1 ) );

		$price = str_replace(
			',',
			3 === $digits_count_after_comma && 0 !== $number_before_comma ? '' : '.',
			$price
		);
	}

	$price = preg_replace( '/[^0-9.]/', '', $price );

	return floatval( $price );
}

/**
 * Checks currency code is a zero decimal currency.
 *
 * @since 4.1.0
 *
 * @param string $currency Stripe currency ISO code.
 *
 * @return bool
 */
function learndash_is_zero_decimal_currency( string $currency = '' ): bool {
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
 * Maps the payment button label.
 *
 * @since 4.2.0
 *
 * @param WP_Post|int|null $post Post or Post ID.
 *
 * @return string
 */
function learndash_get_payment_button_label( $post ): string {
	if ( empty( $post ) ) {
		return '';
	}

	if ( is_int( $post ) ) {
		$post = get_post( $post );

		if ( is_null( $post ) ) {
			return '';
		}
	}

	$button_label = '';

	if ( class_exists( 'LearnDash_Custom_Label' ) ) {
		if ( learndash_is_course_post( $post ) ) {
			$button_label = LearnDash_Custom_Label::get_label( 'button_take_this_course' );
		} elseif ( learndash_is_group_post( $post ) ) {
			$button_label = LearnDash_Custom_Label::get_label( 'button_take_this_group' );
		}
	} else {
		if ( learndash_is_course_post( $post ) ) {
			$button_label = __( 'Take This Course', 'learndash' );
		} elseif ( learndash_is_group_post( $post ) ) {
			$button_label = __( 'Enroll in Group', 'learndash' );
		}
	}

	return $button_label;
}

/**
 * Gets the course price.
 *
 * Return an array of price type, amount and cycle.
 *
 * @since 3.0.0
 * @since 4.1.0 Optional $user_id param added.
 *
 * @param int|object|null $course  Course `WP_Post` object or post ID. Default to global $post.
 * @param int|null        $user_id User ID. Default to current user id.
 *
 * @return array Course price details.
 * @global WP_Post $post Global post object.
 */
function learndash_get_course_price( $course = null, ?int $user_id = null ): array {
	if ( null === $course ) {
		global $post;
		$course = $post;
	}

	if ( is_numeric( $course ) ) {
		$course = get_post( $course );
	}

	if ( ! is_a( $course, 'WP_Post' ) ) {
		return array();
	}

	// Get the course price.
	$meta = get_post_meta( $course->ID, '_sfwd-courses', true );

	$course_price = array(
		'type'  => ! empty( $meta['sfwd-courses_course_price_type'] ) ? $meta['sfwd-courses_course_price_type'] : LEARNDASH_DEFAULT_COURSE_PRICE_TYPE,
		'price' => ! empty( $meta['sfwd-courses_course_price'] ) ? $meta['sfwd-courses_course_price'] : '',
	);

	// Get the price a user had when was applying a coupon.

	if ( is_null( $user_id ) || 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( $user_id > 0 && learndash_post_has_attached_coupon( $course->ID, $user_id ) ) {
		$attached_coupon_data = learndash_get_attached_coupon_data( $course->ID, $user_id );

		if ( ! empty( $attached_coupon_data ) ) {
			$course_price['price'] = $attached_coupon_data['price'];
		}
	}

	// Add subscription data.
	if ( 'subscribe' === $course_price['type'] ) {
		$interval        = learndash_get_setting( $course->ID, 'course_price_billing_p3' );
		$frequency       = learndash_get_setting( $course->ID, 'course_price_billing_t3' );
		$repeats         = learndash_get_setting( $course->ID, 'course_no_of_cycles' );
		$trial_interval  = learndash_get_setting( $course->ID, 'course_trial_duration_p1' );
		$trial_frequency = learndash_get_setting( $course->ID, 'course_trial_duration_t1' );

		$course_price['interval']  = $interval;
		$course_price['frequency'] = learndash_get_grammatical_number_label_for_interval( $interval, $frequency );

		if ( ! empty( $repeats ) ) {
			$course_price['repeats']          = $repeats;
			$course_price['repeat_frequency'] = learndash_get_grammatical_number_label_for_interval( $repeats, $frequency );
		}

		$course_price['trial_price'] = ! empty( learndash_get_setting( $course->ID, 'course_trial_price' ) ) ? learndash_get_setting( $course->ID, 'course_trial_price' ) : '';

		if ( ! empty( $trial_interval ) ) {
			$course_price['trial_interval']  = $trial_interval;
			$course_price['trial_frequency'] = learndash_get_grammatical_number_label_for_interval( $trial_interval, $trial_frequency );
		}
	}

	/**
	 * Filters price details for a course.
	 *
	 * @since 3.0.0
	 *
	 * @param array $course_price Course price details.
	 */
	return apply_filters( 'learndash_get_course_price', $course_price );
}

/**
 * Get group price
 *
 * Return an array of price type, amount and cycle
 *
 * @since 3.2.0
 * @since 4.1.0 Optional $user_id param added.
 *
 * @param int|object|null $group   Course `WP_Post` object or post ID. Default to global $post.
 * @param int|null        $user_id User ID. Default to current user id.
 *
 * @return array price details.
 */
function learndash_get_group_price( $group = null, ?int $user_id = null ): array {
	if ( null === $group ) {
		global $post;
		$group = $post;
	}

	if ( is_numeric( $group ) ) {
		$group = get_post( $group );
	}

	if ( ! is_a( $group, 'WP_Post' ) ) {
		return array();
	}

	// Get the group price.

	$meta = get_post_meta( $group->ID, '_groups', true );

	$group_price = array(
		'type'  => ! empty( $meta['groups_group_price_type'] ) ? $meta['groups_group_price_type'] : LEARNDASH_DEFAULT_GROUP_PRICE_TYPE,
		'price' => ! empty( $meta['groups_group_price'] ) ? $meta['groups_group_price'] : '',
	);

	// Get the price a user had when was applying a coupon.

	if ( is_null( $user_id ) || 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( $user_id > 0 && learndash_post_has_attached_coupon( $group->ID, $user_id ) ) {
		$attached_coupon_data = learndash_get_attached_coupon_data( $group->ID, $user_id );

		if ( ! empty( $attached_coupon_data ) ) {
			$group_price['price'] = $attached_coupon_data['price'];
		}
	}

	// Add subscription data.

	if ( 'subscribe' === $group_price['type'] ) {
		$frequency       = learndash_get_setting( $group->ID, 'group_price_billing_t3' );
		$interval        = learndash_get_setting( $group->ID, 'group_price_billing_p3' );
		$repeats         = learndash_get_setting( $group->ID, 'post_no_of_cycles' );
		$trial_interval  = learndash_get_setting( $group->ID, 'group_trial_duration_p1' );
		$trial_frequency = learndash_get_setting( $group->ID, 'group_trial_duration_t1' );

		$group_price['interval']  = $interval;
		$group_price['frequency'] = learndash_get_grammatical_number_label_for_interval( $interval, $frequency );

		if ( ! empty( $repeats ) ) {
			$group_price['repeats']          = $repeats;
			$group_price['repeat_frequency'] = learndash_get_grammatical_number_label_for_interval( $repeats, $frequency );
		}

		$group_price['trial_price'] = ! empty( learndash_get_setting( $group->ID, 'group_trial_price' ) ) ? learndash_get_setting( $group->ID, 'group_trial_price' ) : '';

		if ( ! empty( $trial_interval ) ) {
			$group_price['trial_interval']  = $trial_interval;
			$group_price['trial_frequency'] = learndash_get_grammatical_number_label_for_interval( $trial_interval, $trial_frequency );
		}
	}

	/**
	 * Filter Group Price details.
	 *
	 * @since 3.2.0
	 *
	 * @param array $group_price Group Price Details array.
	 */
	return apply_filters( 'learndash_get_group_price', $group_price );
}

/**
 * Get the singular or plural label for recurring payment intervals
 *
 * @since 3.6.0
 *
 * @param string $interval  Number of payment intervals.
 * @param string $frequency PayPal symbol for day, week, month or year.
 *
 * @return string
 */
function learndash_get_grammatical_number_label_for_interval( $interval, $frequency ) {
	$interval = intval( $interval );
	switch ( $frequency ) {
		case ( 'D' ):
			return _n( 'day', 'days', $interval, 'learndash' );

		case ( 'W' ):
			return _n( 'week', 'weeks', $interval, 'learndash' );

		case ( 'M' ):
			return _n( 'month', 'months', $interval, 'learndash' );

		case ( 'Y' ):
			return _n( 'year', 'years', $interval, 'learndash' );

		default:
			return '';
	}
}
