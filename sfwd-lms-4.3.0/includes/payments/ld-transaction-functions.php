<?php
/**
 * Functions related to transaction post type
 *
 * @since 4.2.0
 *
 * @package LearnDash
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gets payment meta for transaction based upon payment processor.
 *
 * @since 4.2.0
 *
 * @param int $transaction_id The ID of the transaction.
 *
 * @return array Array of transaction meta.
 */
function learndash_transaction_get_payment_meta( int $transaction_id = 0 ): array {
	$transaction_id = absint( $transaction_id );
	if ( empty( $transaction_id ) ) {
		return array();
	}

	$transaction = get_post( $transaction_id );
	if ( ! $transaction || ! is_a( $transaction, 'WP_Post' ) ) {
		return array();
	}

	if ( LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::TRANSACTION ) !== $transaction->post_type ) {
		return array();
	}

	$transaction_meta = get_post_meta( $transaction_id );

	// remove Stripe's metadata from meta array.
	if ( true === array_key_exists( 'stripe_metadata', $transaction_meta ) ) {
		unset( $transaction_meta['stripe_metadata'] );
		// Stripe addon does not add this so we force it here.
		$transaction_processor = 'stripe';
	} else {
		$transaction_processor = $transaction_meta['ld_payment_processor'][0];
	}

	if ( true === array_key_exists( 'coupon', $transaction_meta ) ) {
		$meta = learndash_transaction_get_coupon_meta( $transaction_id );
	} else {
		if ( 'stripe' === $transaction_processor ) {
			$meta = learndash_transaction_get_stripe_meta( $transaction_id );
		} elseif ( 'paypal' === $transaction_processor ) {
			$meta = learndash_transaction_get_paypal_meta( $transaction_id );
		} elseif ( 'razorpay' === $transaction_processor ) {
			$meta = learndash_transaction_get_razorpay_meta( $transaction_id );
		} else {
			$meta = array();
		}
	}

	return $meta;
}

/**
 * Gets meta for transaction if coupon was used.
 *
 * @since 4.2.0
 *
 * @param int $transaction_id The ID of the transaction.
 *
 * @return array Array of transaction meta.
 */
function learndash_transaction_get_coupon_meta( int $transaction_id = 0 ): array {
	$transaction_id = absint( $transaction_id );
	if ( empty( $transaction_id ) ) {
		return array();
	}

	$transaction = get_post( $transaction_id );
	if ( ! $transaction || ! is_a( $transaction, 'WP_Post' ) ) {
		return array();
	}

	if ( LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::TRANSACTION ) !== $transaction->post_type ) {
		return array();
	}

	$transaction_data = get_post_meta( $transaction_id );
	// remove Stripe's metadata from meta array.
	if ( true === array_key_exists( 'stripe_metadata', $transaction_data ) ) {
		unset( $transaction_data['stripe_metadata'] );
	}

	$coupon_meta = $transaction_data['coupon'][0];
	$coupon_meta = unserialize( $coupon_meta );
	$currency    = $coupon_meta['currency'];

	$meta = array();

	if ( ! empty( $coupon_meta ) ) {
		$final_price = $coupon_meta['discounted_price'];
		$discount    = LEARNDASH_COUPON_TYPE_FLAT === $coupon_meta['type']
			? learndash_get_price_formatted( $coupon_meta['discount'], $currency )
			: $coupon_meta['amount'] . '%';

		$meta = array(
			'item_name'             => get_the_title( $transaction_data['post_id'][0] ),
			'item_price'            => learndash_get_price_formatted( $coupon_meta['price'], $currency ),
			'item_coupon'           => $coupon_meta['code'],
			'item_discount'         => $discount,
			'item_discounted_price' => learndash_get_price_formatted( $final_price, $currency ),
		);
	}

	return $meta;
}

/**
 * Grabs the final price of the transaction.
 *
 * @since 4.2.0
 *
 * @param int $transaction_id Post ID.
 *
 * @return int Actual price paid for the transaction.
 */
function learndash_transaction_get_final_price( int $transaction_id ): int {
	$transaction_id = absint( $transaction_id );
	if ( empty( $transaction_id ) ) {
		return 0;
	}

	$transaction = get_post( $transaction_id );
	if ( ! $transaction || ! is_a( $transaction, 'WP_Post' ) ) {
		return 0;
	}

	if ( LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::TRANSACTION ) !== $transaction->post_type ) {
		return 0;
	}

	$transaction_meta      = get_post_meta( $transaction_id );
	$transaction_processor = $transaction_meta['ld_payment_processor'][0];
	// remove Stripe's metadata from meta array.
	if ( true === array_key_exists( 'stripe_metadata', $transaction_meta ) ) {
		unset( $transaction_meta['stripe_metadata'] );
	}

	if ( 'stripe' === $transaction_processor ) {
		$final_price = $transaction_meta['stripe_price'][0];
	} elseif ( 'paypal' === $transaction_processor ) {
		$final_price = $transaction_meta['mc_gross'][0];
	} elseif ( 'razorpay' === $transaction_processor ) {
		$price       = json_decode( $transaction_meta['pricing'][0] );
		$final_price = $price->price;
	} else {
		$final_price = 0;
	}

	return $final_price;
}

/**
 * Gets meta for transaction related to Stripe.
 *
 * @since 4.2.0
 *
 * @param int $transaction_id The ID of the transaction.
 *
 * @return array Array of transaction meta.
 */
function learndash_transaction_get_stripe_meta( int $transaction_id = 0 ): array {
	$transaction_id = absint( $transaction_id );
	if ( empty( $transaction_id ) ) {
		return array();
	}

	$transaction = get_post( $transaction_id );
	if ( ! $transaction || ! is_a( $transaction, 'WP_Post' ) ) {
		return array();
	}

	if ( LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::TRANSACTION ) !== $transaction->post_type ) {
		return array();
	}

	$transaction_meta = get_post_meta( $transaction_id );

	// remove Stripe's metadata from meta array.
	if ( true === array_key_exists( 'stripe_metadata', $transaction_meta ) ) {
		unset( $transaction_meta['stripe_metadata'] );
	}

	$currency = $transaction_meta['stripe_currency'][0];

	$meta = array(
		'item_name'  => get_the_title( $transaction_meta['post_id'][0] ),
		'item_price' => learndash_get_price_formatted( $transaction_meta['stripe_price'][0], $currency ),
	);
	if ( ! empty( $transaction_meta['pricing_billing_p3'][0] ) ) {
		$duration_value  = $transaction_meta['pricing_billing_p3'][0];
		$duration_length = $transaction_meta['pricing_billing_t3'][0];
		$subscribe_meta  = array(
			'item_price'      => learndash_get_price_formatted( $transaction_meta['subscribe_price'][0], $currency ),
			'recurring_times' => $transaction_meta['no_of_cycles'][0],
			'duration_value'  => $duration_value,
			'duration_length' => $duration_length,
		);

		$meta = array_merge( $meta, $subscribe_meta );
	}
	if ( ! empty( $transaction_meta['trial_price'][0] ) ) {
		$trial_duration_value  = $transaction_meta['trial_duration_p1'][0];
		$trial_duration_length = $transaction_meta['trial_duration_t1'][0];
		$trial_meta            = array(
			'trial_price'           => learndash_get_price_formatted( $transaction_meta['trial_price'][0], $currency ),
			'trial_duration_value'  => $trial_duration_value,
			'trial_duration_length' => $trial_duration_length,
		);

		$meta = array_merge( $meta, $trial_meta );
	}

	return $meta;
}

/**
 * Gets meta for transaction related to PayPal.
 *
 * @since 4.2.0
 *
 * @param int $transaction_id The ID of the transaction.
 *
 * @return array Array of transaction meta.
 */
function learndash_transaction_get_paypal_meta( int $transaction_id = 0 ): array {
	$transaction_id = absint( $transaction_id );
	if ( empty( $transaction_id ) ) {
		return array();
	}

	$transaction = get_post( $transaction_id );
	if ( ! $transaction || ! is_a( $transaction, 'WP_Post' ) ) {
		return array();
	}

	if ( LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::TRANSACTION ) !== $transaction->post_type ) {
		return array();
	}

	$transaction_meta = get_post_meta( $transaction_id );
	$currency         = $transaction_meta['mc_currency'][0];

	$meta = array(
		'item_name'  => get_the_title( $transaction_meta['post_id'][0] ),
		'item_price' => learndash_get_price_formatted( $transaction_meta['mc_gross'][0], $currency ),
	);

	if ( 'subscr_payment' === $transaction_meta['txn_type'][0] ) {
		$duration_value  = $transaction_meta['period3'][0][0];
		$duration_length = $transaction_meta['period3'][0][2];
		$subscribe_meta  = array(
			'item_price'      => learndash_get_price_formatted( $transaction_meta['amount3'][0], $currency ),
			'recurring_times' => $transaction_meta['recur_times'][0],
			'duration_value'  => $duration_value,
			'duration_length' => $duration_length,
		);

		$meta = array_merge( $meta, $subscribe_meta );
	}

	if ( ! empty( $transaction_meta['trial_price'][0] ) ) {
		$trial_duration_value  = $transaction_meta['period1'][0][0];
		$trial_duration_length = $transaction_meta['period1'][0][2];
		$trial_meta            = array(
			'trial_price'           => learndash_get_price_formatted( $transaction_meta['trial_price'][0], $currency ),
			'trial_duration_value'  => $trial_duration_value,
			'trial_duration_length' => $trial_duration_length,
		);

		$meta = array_merge( $meta, $trial_meta );
	}

	return $meta;
}

/**
 * Gets meta for transaction related to RazorPay.
 *
 * @since 4.2.0
 *
 * @param int $transaction_id The ID of the transaction.
 *
 * @return array Array of transaction meta.
 */
function learndash_transaction_get_razorpay_meta( int $transaction_id = 0 ): array {
	$transaction_id = absint( $transaction_id );
	if ( empty( $transaction_id ) ) {
		return array();
	}

	$transaction = get_post( $transaction_id );
	if ( ( ! $transaction ) || ( ! is_a( $transaction, 'WP_Post' ) ) ) {
		return array();
	}

	if ( LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::TRANSACTION ) !== $transaction->post_type ) {
		return array();
	}

	$transaction_meta = get_post_meta( $transaction_id );
	$pricing_meta     = get_post_meta( $transaction_id, 'pricing', true );

	$meta = array(
		'item_name'  => get_the_title( $transaction_meta['post_id'][0] ),
		'item_price' => learndash_get_price_formatted( $pricing_meta['price'], $pricing_meta['currency'] ),
	);

	if ( ! empty( $pricing_meta['pricing_billing_t3'] ) ) {
		switch ( $pricing_meta['pricing_billing_t3'] ) {
			case 'D':
				$pricing_meta['pricing_billing_t3'] = 'day';
				break;

			case 'W':
				$pricing_meta['pricing_billing_t3'] = 'week';
				break;

			case 'M':
				$pricing_meta['pricing_billing_t3'] = 'month';
				break;

			case 'Y':
				$pricing_meta['pricing_billing_t3'] = 'year';
				break;
		}

		switch ( $pricing_meta['trial_duration_t1'] ) {
			case 'D':
				$pricing_meta['trial_duration_t1'] = 'day';
				break;

			case 'W':
				$pricing_meta['trial_duration_t1'] = 'week';
				break;

			case 'M':
				$pricing_meta['trial_duration_t1'] = 'month';
				break;

			case 'Y':
				$pricing_meta['trial_duration_t1'] = 'year';
				break;
		}

		$subscribe_meta = array(
			'item_price'      => learndash_get_price_formatted( $pricing_meta['price'], $pricing_meta['currency'] ),
			'recurring_times' => $pricing_meta['no_of_cycles'],
			'duration_value'  => $pricing_meta['pricing_billing_p3'],
			'duration_length' => $pricing_meta['pricing_billing_t3'],
		);

		$meta = array_merge( $meta, $subscribe_meta );
	}

	if ( ! empty( $pricing_meta['trial_price'] ) ) {
		$trial_meta = array(
			'trial_price'           => learndash_get_price_formatted( $pricing_meta['trial_price'], $pricing_meta['currency'] ),
			'trial_duration_value'  => $pricing_meta['trial_duration_p1'],
			'trial_duration_length' => $pricing_meta['trial_duration_t1'],
		);

		$meta = array_merge( $meta, $trial_meta );
	}

	return $meta;
}

/**
 * Creates a transaction.
 *
 * @since 4.2.0
 *
 * @param array   $meta_fields Meta fields.
 * @param WP_Post $post        Post.
 * @param WP_User $user        User.
 *
 * @return void
 */
function learndash_transaction_create( array $meta_fields, WP_Post $post, WP_User $user ): void {
	/**
	 * Transaction ID.
	 *
	 * @var int|WP_Error $transaction_id Transaction ID.
	 */
	$transaction_id = wp_insert_post(
		array(
			'post_title'  => $post->post_title . __( ' Purchased By ', 'learndash' ) . $user->user_email,
			'post_type'   => LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::TRANSACTION ),
			'post_status' => 'publish',
			'post_author' => $user->ID,
		)
	);

	$meta_fields['learndash_version'] = LEARNDASH_VERSION;
	$meta_fields['post_id']           = $post->ID;
	$meta_fields['user_id']           = $user->ID;

	foreach ( $meta_fields as $key => $value ) {
		update_post_meta( $transaction_id, $key, $value );
	}

	if ( 0 === $transaction_id || is_wp_error( $transaction_id ) ) {
		return;
	}

	/**
	 * Fires after the payment transaction is created with all meta fields.
	 *
	 * @since 4.1.0
	 *
	 * @param int $transaction_id Transaction ID.
	 */
	do_action( 'learndash_transaction_created', $transaction_id );
}

/**
 * Saves current LD version as a transaction meta.
 *
 * @since 4.2.0
 *
 * @param int $transaction_id Transaction ID.
 *
 * @return void
 */
function learndash_transaction_add_learndash_version( int $transaction_id ): void {
	update_post_meta( $transaction_id, 'learndash_version', LEARNDASH_VERSION );
}

add_action( 'learndash_transaction_created', 'learndash_transaction_add_learndash_version' );
