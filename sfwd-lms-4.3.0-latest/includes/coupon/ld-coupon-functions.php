<?php
/**
 * Coupon functions
 *
 * @since 4.1.0
 *
 * @package LearnDash\Coupons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const LEARNDASH_COUPON_META_KEY_CODE                = 'code';
const LEARNDASH_COUPON_META_KEY_TYPE                = 'type';
const LEARNDASH_COUPON_META_KEY_AMOUNT              = 'amount';
const LEARNDASH_COUPON_META_KEY_REDEMPTIONS         = 'redemptions';
const LEARNDASH_COUPON_META_KEY_START_DATE          = 'start_date';
const LEARNDASH_COUPON_META_KEY_END_DATE            = 'end_date';
const LEARNDASH_COUPON_META_KEY_PREFIX_APPLY_TO_ALL = 'apply_to_all_';

const LEARNDASH_TRANSACTION_COUPON_META_KEY = 'coupon';

const LEARNDASH_COUPON_TYPE_FLAT       = 'flat';
const LEARNDASH_COUPON_TYPE_PERCENTAGE = 'percentage';

const LEARNDASH_COUPON_ASSOCIATED_FIELDS = array( 'courses', 'groups' );

/**
 * Checks if a coupon is valid.
 *
 * @since 4.1.0
 *
 * @param string $coupon_code Coupon code.
 * @param int    $post_id     Course/Group ID.
 *
 * @return array{is_valid: bool, error: string}
 */
function learndash_check_coupon_is_valid( string $coupon_code, int $post_id ): array {
	$errors = array(
		'invalid'     => __( 'Coupon is invalid.', 'learndash' ),
		'expired'     => __( 'Coupon has expired.', 'learndash' ),
		'usage_limit' => __( 'Coupon max redemption limit reached.', 'learndash' ),
	);

	$course_post_type = LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::COURSE );
	$group_post_type  = LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::GROUP );

	// Check if params are empty.

	if ( empty( $coupon_code ) || empty( $post_id ) ) {
		return array(
			'is_valid' => false,
			'error'    => $errors['invalid'],
		);
	}

	// Check if post type is valid.

	$post_type = get_post_type( $post_id );

	$valid_post_types = array( $course_post_type, $group_post_type );

	if ( ! in_array( $post_type, $valid_post_types, true ) ) {
		return array(
			'is_valid' => false,
			'error'    => $errors['invalid'],
		);
	}

	// Check if a coupon exists.

	$coupon = learndash_get_coupon_by_code( $coupon_code );

	if ( is_null( $coupon ) ) {
		return array(
			'is_valid' => false,
			'error'    => $errors['invalid'],
		);
	}

	$coupon_settings = learndash_get_setting( $coupon );

	// Check if the passed course/group is allowed.

	$post_type_field_key_hash = array(
		$course_post_type => 'courses',
		$group_post_type  => 'groups',
	);

	$post_type_field_key = $post_type_field_key_hash[ $post_type ];

	if ( 'on' !== $coupon_settings[ LEARNDASH_COUPON_META_KEY_PREFIX_APPLY_TO_ALL . $post_type_field_key ] ) {
		$valid_post_ids = $coupon_settings[ $post_type_field_key ];

		if ( empty( $valid_post_ids ) || ! in_array( $post_id, $valid_post_ids, true ) ) {
			return array(
				'is_valid' => false,
				'error'    => $errors['invalid'],
			);
		}
	}

	// Check redemptions limit if needed.

	if (
		$coupon_settings['max_redemptions'] > 0 &&
		$coupon_settings[ LEARNDASH_COUPON_META_KEY_REDEMPTIONS ] >= $coupon_settings['max_redemptions']
	) {
		return array(
			'is_valid' => false,
			'error'    => $errors['usage_limit'],
		);
	}

	// Check dates if needed.

	$current_time = time();
	$start_date   = (int) $coupon_settings[ LEARNDASH_COUPON_META_KEY_START_DATE ];
	$end_date     = (int) $coupon_settings[ LEARNDASH_COUPON_META_KEY_END_DATE ];

	if ( $start_date > 0 && $current_time < $start_date ) {
		return array(
			'is_valid' => false,
			'error'    => $errors['invalid'],
		);
	}

	if ( $end_date > 0 && $current_time >= $end_date ) {
		return array(
			'is_valid' => false,
			'error'    => $errors['expired'],
		);
	}

	return array(
		'is_valid' => true,
		'error'    => '',
	);
}

/**
 * Returns a new price.
 *
 * @since 4.1.0
 *
 * @param int   $coupon_id Coupon ID.
 * @param float $price     Price.
 *
 * @return float
 */
function learndash_calculate_coupon_discounted_price( int $coupon_id, float $price ): float {
	$coupon = get_post( $coupon_id );

	if ( is_null( $coupon ) ) {
		return $price;
	}

	$coupon_settings = learndash_get_setting( $coupon );

	if ( empty( $coupon_settings ) ) {
		return $price;
	}

	$coupon_type = $coupon_settings[ LEARNDASH_COUPON_META_KEY_TYPE ];
	$amount      = (float) $coupon_settings[ LEARNDASH_COUPON_META_KEY_AMOUNT ];

	if ( LEARNDASH_COUPON_TYPE_PERCENTAGE === $coupon_type ) {
		$price = $price - ( $price / 100 * $amount );
	} elseif ( LEARNDASH_COUPON_TYPE_FLAT === $coupon_type ) {
		$price = $price - $amount;
	}

	if ( learndash_is_zero_decimal_currency( learndash_get_currency_code() ) ) {
		$price = ceil( $price );
	}

	if ( $price < 0 ) {
		$price = 0;
	}

	return $price;
}

/**
 * Finds a coupon post by a coupon code.
 *
 * @since 4.1.0
 *
 * @param string $coupon_code Coupon Code.
 *
 * @return WP_Post|null
 */
function learndash_get_coupon_by_code( string $coupon_code ): ?WP_Post {
	if ( empty( $coupon_code ) ) {
		return null;
	}

	$query_args = array(
		'post_type'      => LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::COUPON ),
		'posts_per_page' => - 1,
		'post_status'    => 'publish',
		// phpcs:ignore
		'meta_query'     => array(
			array(
				'key'     => LEARNDASH_COUPON_META_KEY_CODE,
				'value'   => $coupon_code,
				'compare' => '=',
			),
		),
	);

	$query = new WP_Query( $query_args );

	return empty( $query->posts ) ? null : $query->posts[0];
}

/**
 * Checks if active coupons exist.
 *
 * @since 4.1.0
 *
 * @return bool
 */
function learndash_active_coupons_exist(): bool {
	$current_time = time();

	$query_args = array(
		'post_type'      => LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::COUPON ),
		'posts_per_page' => - 1,
		'post_status'    => 'publish',
		// phpcs:ignore
		'meta_query'     => array(
			'relation' => 'OR',
			array(
				'relation' => 'AND',
				array(
					'key'     => LEARNDASH_COUPON_META_KEY_START_DATE,
					'compare' => '=',
					'value'   => 0,
				),
				array(
					'key'     => LEARNDASH_COUPON_META_KEY_END_DATE,
					'compare' => '=',
					'value'   => 0,
				),
			),
			array(
				'relation' => 'AND',
				array(
					'key'     => LEARNDASH_COUPON_META_KEY_START_DATE,
					'compare' => '<=',
					'value'   => $current_time,
					'type'    => 'NUMERIC',
				),
				array(
					'key'     => LEARNDASH_COUPON_META_KEY_END_DATE,
					'compare' => '>',
					'value'   => $current_time,
					'type'    => 'NUMERIC',
				),
			),
			array(
				'relation' => 'AND',
				array(
					'key'     => LEARNDASH_COUPON_META_KEY_START_DATE,
					'compare' => '<=',
					'value'   => $current_time,
					'type'    => 'NUMERIC',
				),
				array(
					'key'     => LEARNDASH_COUPON_META_KEY_END_DATE,
					'compare' => '=',
					'value'   => 0,
					'type'    => 'NUMERIC',
				),
			),
		),
	);

	$query = new WP_Query( $query_args );

	return $query->post_count > 0;
}

/**
 * Increments redemptions meta of a coupon.
 *
 * @since 4.1.0
 *
 * @param int $coupon_id Coupon Post ID.
 * @param int $post_id   Course/Group ID.
 * @param int $user_id   User ID.
 *
 * @return void
 */
function learndash_increment_coupon_redemptions( int $coupon_id, int $post_id, int $user_id ): void {
	learndash_detach_coupon( $post_id, $user_id );

	if ( is_null( get_post( $coupon_id ) ) ) {
		return;
	}

	$redemptions = (int) learndash_get_setting(
		$coupon_id,
		LEARNDASH_COUPON_META_KEY_REDEMPTIONS
	);

	learndash_update_setting(
		$coupon_id,
		LEARNDASH_COUPON_META_KEY_REDEMPTIONS,
		$redemptions + 1
	);
}

/**
 * Attaches a coupon to a course/group.
 *
 * @since 4.1.0
 *
 * @param int   $post_id          Course/Group ID.
 * @param int   $coupon_id        Coupon Post ID.
 * @param float $price            Full price.
 * @param float $discounted_price Price by a coupon.
 *
 * @return void
 */
function learndash_attach_coupon( int $post_id, int $coupon_id, float $price, float $discounted_price ): void {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$coupon_settings = learndash_get_setting( $coupon_id );

	set_transient(
		learndash_map_coupon_transient_key( $post_id, get_current_user_id() ),
		array(
			'coupon_id'                      => $coupon_id,
			'currency'                       => learndash_get_currency_code(),
			'price'                          => $price,
			'discount'                       => $price - $discounted_price,
			'discounted_price'               => $discounted_price,
			LEARNDASH_COUPON_META_KEY_CODE   => $coupon_settings[ LEARNDASH_COUPON_META_KEY_CODE ],
			LEARNDASH_COUPON_META_KEY_TYPE   => $coupon_settings[ LEARNDASH_COUPON_META_KEY_TYPE ],
			LEARNDASH_COUPON_META_KEY_AMOUNT => $coupon_settings[ LEARNDASH_COUPON_META_KEY_AMOUNT ],
		),
		DAY_IN_SECONDS
	);
}

/**
 * Detaches a coupon from a course/group.
 *
 * @since 4.1.0
 *
 * @param int $post_id Course/Group ID.
 * @param int $user_id User ID.
 *
 * @return void
 */
function learndash_detach_coupon( int $post_id, int $user_id ): void {
	delete_transient(
		learndash_map_coupon_transient_key( $post_id, $user_id )
	);
}

/**
 * Get an attached coupon data of a course/group.
 *
 * @since 4.1.0
 *
 * @param int $post_id Course/Group ID.
 * @param int $user_id User ID.
 *
 * @return array|null
 */
function learndash_get_attached_coupon_data( int $post_id, int $user_id ): ?array {
	$attached_coupon_data = get_transient(
		learndash_map_coupon_transient_key( $post_id, $user_id )
	);

	if ( false === $attached_coupon_data ) {
		return null;
	}

	return $attached_coupon_data;
}

/**
 * Check if a course/group has an attached coupon.
 *
 * @since 4.1.0
 *
 * @param int $post_id Course/Group ID.
 * @param int $user_id User ID.
 *
 * @return bool
 */
function learndash_post_has_attached_coupon( int $post_id, int $user_id ): bool {
	$attached_coupon_data = get_transient(
		learndash_map_coupon_transient_key( $post_id, $user_id )
	);

	return false !== $attached_coupon_data;
}

/**
 * Maps a coupon transient key.
 *
 * @since 4.1.0
 *
 * @param int $post_id Post ID.
 * @param int $user_id User ID.
 *
 * @return string
 */
function learndash_map_coupon_transient_key( int $post_id, int $user_id ): string {
	return "ld_coupon_for_post_{$post_id}_by_user_{$user_id}";
}

/**
 * Syncs associated metas of a coupon.
 *
 * @since 4.1.0
 *
 * @param int    $post_id Coupon Post ID.
 * @param string $field   Field Name (courses|groups).
 * @param array  $ids     IDs.
 *
 * @return void
 */
function learndash_sync_coupon_associated_metas( int $post_id, string $field, array $ids ): void {
	if ( ! in_array( $field, LEARNDASH_COUPON_ASSOCIATED_FIELDS, true ) ) {
		return;
	}

	$meta_prefix  = "{$field}_";
	$existing_ids = (array) learndash_get_setting( $post_id, $field );

	// Delete associated metas that we no longer need.
	if ( ! empty( $existing_ids ) && is_array( $existing_ids ) ) {
		foreach ( array_diff( $existing_ids, $ids ) as $id ) {
			delete_post_meta( $post_id, "{$meta_prefix}{$id}" );
		}
	}

	// Add associated metas we need.
	foreach ( $ids as $id ) {
		update_post_meta( $post_id, "{$meta_prefix}{$id}", $id );
	}
}

/**
 * Handles a coupon applying action made via AJAX request on LD Register page.
 *
 * @since 4.1.0
 *
 * @return void
 */
function learndash_apply_coupon(): void {
	if ( empty( $_POST['nonce'] ) || empty( $_POST['post_id'] ) || ! is_user_logged_in() ) {
		wp_send_json_error(
			array(
				'message' => __( 'Invalid request.', 'learndash' ),
			)
		);
	}

	if ( empty( $_POST['coupon_code'] ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Please enter the coupon code.', 'learndash' ),
			)
		);
	}

	$nonce       = sanitize_text_field( wp_unslash( $_POST['nonce'] ) );
	$coupon_code = sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) );
	$post_id     = absint( $_POST['post_id'] ); // Course/Group ID.
	$post        = get_post( $post_id );

	if ( is_null( $post ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Course or group not found.', 'learndash' ),
			)
		);
	}

	if ( ! wp_verify_nonce( $nonce, 'learndash-coupon-nonce' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Invalid nonce.', 'learndash' ),
			)
		);
	}

	// Check if the coupon code is valid.

	$coupon_validation_result = learndash_check_coupon_is_valid( $coupon_code, $post_id );

	if ( ! $coupon_validation_result['is_valid'] ) {
		wp_send_json_error(
			array(
				'message' => $coupon_validation_result['error'],
			)
		);
	}

	// Check if we are processing a course/group and "buy now" pricing.

	if ( learndash_is_course_post( $post ) ) {
		$pricing = learndash_get_course_price( $post );
	} elseif ( learndash_is_group_post( $post ) ) {
		$pricing = learndash_get_group_price( $post );
	} else {
		wp_send_json_error(
			array(
				'message' => __( 'Invalid course or group.', 'learndash' ),
			)
		);
	}

	if ( 'paynow' !== $pricing['type'] ) {
		wp_send_json_error(
			array(
				'message' => __( 'Only "buy now" prices are supported.', 'learndash' ),
			)
		);
	}

	// Process an action.

	$coupon = learndash_get_coupon_by_code( $coupon_code );

	$price = learndash_get_price_as_float( $pricing['price'] );

	$discounted_price = learndash_calculate_coupon_discounted_price( $coupon->ID, $price );
	$discount         = ( $price - $discounted_price ) * - 1;

	$stripe_price = $discounted_price;
	if ( ! learndash_is_zero_decimal_currency( learndash_get_currency_code() ) ) {
		$stripe_price *= 100;
	}

	learndash_attach_coupon( $post_id, $coupon->ID, $price, $discounted_price );

	wp_send_json_success(
		array(
			'coupon_code' => $coupon_code,
			'discount'    => esc_html(
				learndash_get_price_formatted( $discount )
			),
			'total'       => array(
				'value'        => $discounted_price,
				'stripe_value' => $stripe_price,
				'formatted'    => esc_html(
					learndash_get_price_formatted( $discounted_price )
				),
			),
			'message'     => __( 'Coupon applied.', 'learndash' ),
		)
	);
}

/**
 * Handles a coupon removing action made via AJAX request on LD Register page.
 *
 * @since 4.1.0
 *
 * @return void
 */
function learndash_remove_coupon(): void {
	if ( empty( $_POST['nonce'] ) || empty( $_POST['post_id'] ) || ! is_user_logged_in() ) {
		wp_send_json_error(
			array(
				'message' => __( 'Invalid request.', 'learndash' ),
			)
		);
	}

	$nonce   = sanitize_text_field( wp_unslash( $_POST['nonce'] ) );
	$post_id = absint( $_POST['post_id'] ); // Course/Group ID.
	$post    = get_post( $post_id );

	if ( is_null( $post ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Course or group not found.', 'learndash' ),
			)
		);
	}

	if ( ! wp_verify_nonce( $nonce, 'learndash-coupon-nonce' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Invalid nonce.', 'learndash' ),
			)
		);
	}

	// Check if we are processing a course/group.

	if ( learndash_is_course_post( $post ) ) {
		$pricing = learndash_get_course_price( $post );
	} elseif ( learndash_is_group_post( $post ) ) {
		$pricing = learndash_get_group_price( $post );
	} else {
		wp_send_json_error(
			array(
				'message' => __( 'Invalid course or group.', 'learndash' ),
			)
		);
	}

	// Detach the coupon.

	learndash_detach_coupon( $post_id, get_current_user_id() );

	// Calculate the price.

	$stripe_price = $pricing['price'];
	if ( ! empty( $stripe_price ) && ! learndash_is_zero_decimal_currency( learndash_get_currency_code() ) ) {
		$stripe_price *= 100;
	}

	wp_send_json_success(
		array(
			'total'   => array(
				'value'        => number_format(
					$pricing['price'],
					2,
					'.',
					''
				),
				'stripe_value' => $stripe_price,
				'formatted'    => esc_html( learndash_get_price_formatted( $pricing['price'] ) ),
			),
			'message' => __( 'Coupon removed.', 'learndash' ),
		)
	);
}

/**
 * Increments coupon's redemptions and saves a coupon to transaction's meta.
 *
 * @since 4.1.0
 *
 * @param int $transaction_id Transaction ID.
 *
 * @return void
 */
function learndash_process_coupon_after_transaction( int $transaction_id ): void {
	if ( is_null( get_post( $transaction_id ) ) ) {
		return;
	}

	$post_id = get_post_meta( $transaction_id, 'post_id', true );
	$user_id = get_post_meta( $transaction_id, 'user_id', true );

	if ( empty( $post_id ) || empty( $user_id ) ) {
		return;
	}

	if ( ! learndash_post_has_attached_coupon( $post_id, $user_id ) ) {
		return;
	}

	$coupon_data = learndash_get_attached_coupon_data( $post_id, $user_id );

	update_post_meta( $transaction_id, LEARNDASH_TRANSACTION_COUPON_META_KEY, $coupon_data );

	// Maybe we'll need to filter transactions by a coupon code.
	update_post_meta(
		$transaction_id,
		LEARNDASH_TRANSACTION_COUPON_META_KEY . '_code',
		$coupon_data[ LEARNDASH_COUPON_META_KEY_CODE ]
	);

	learndash_increment_coupon_redemptions( $coupon_data['coupon_id'], $post_id, $user_id );
}

/**
 * Modifies course/group price if a coupon is attached.
 *
 * @since 4.1.0
 *
 * @param float    $price   Course/Group Price.
 * @param int      $post_id Course/Group ID.
 * @param int|null $user_id User ID.
 *
 * @return float
 */
function learndash_get_price_by_coupon( float $price, int $post_id, ?int $user_id ): float {
	if ( is_null( $user_id ) || 0 === $user_id ) {
		return $price;
	}

	if ( ! learndash_post_has_attached_coupon( $post_id, $user_id ) ) {
		return $price;
	}

	$attached_coupon_data = learndash_get_attached_coupon_data( $post_id, $user_id );

	return (float) $attached_coupon_data['discounted_price'];
}

/**
 * Enrolls a user if the price by coupon is 0.
 *
 * @since 4.1.0
 *
 * @return void
 */
function learndash_enroll_with_zero_price(): void {
	if ( empty( $_POST['nonce'] ) || empty( $_POST['post_id'] ) || ! is_user_logged_in() ) {
		wp_send_json_error(
			array(
				'message' => __( 'Invalid request.', 'learndash' ),
			)
		);
	}

	$nonce   = sanitize_text_field( wp_unslash( $_POST['nonce'] ) );
	$user    = wp_get_current_user();
	$post_id = absint( $_POST['post_id'] ); // Course/Group ID.
	$post    = get_post( $post_id );

	if ( is_null( $post ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Course or group not found.', 'learndash' ),
			)
		);
	}

	if ( ! wp_verify_nonce( $nonce, 'learndash-coupon-nonce' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Invalid nonce.', 'learndash' ),
			)
		);
	}

	// Check if we are processing a course/group.

	if ( learndash_is_course_post( $post ) ) {
		$pricing = learndash_get_course_price( $post );
	} elseif ( learndash_is_group_post( $post ) ) {
		$pricing = learndash_get_group_price( $post );
	} else {
		wp_send_json_error(
			array(
				'message' => __( 'Invalid course or group.', 'learndash' ),
			)
		);
	}

	// Check if the price by coupon is 0.

	$price = learndash_get_price_by_coupon( floatval( $pricing['price'] ), $post_id, $user->ID );

	if ( 0.0 !== $price ) {
		wp_send_json_error(
			array(
				'message' => __( 'You have to pay for access.', 'learndash' ),
			)
		);
	}

	// Create a transaction.

	$transaction_data = array(
		'is_zero_price' => true,
		'user_id'       => $user->ID,
		'post_id'       => $post->ID,
	);

	learndash_transaction_create( $transaction_data, $post, $user );

	// Enroll, map the redirect url and send the response.

	$redirect_url = '';

	if ( learndash_is_course_post( $post ) ) {
		ld_update_course_access( $user->ID, $post->ID );

		$redirect_url = learndash_get_course_enrollment_url( $post );
	} elseif ( learndash_is_group_post( $post ) ) {
		ld_update_group_access( $user->ID, $post->ID );

		$redirect_url = learndash_get_group_enrollment_url( $post );
	}

	wp_send_json_success(
		array(
			'redirect_url' => $redirect_url,
		)
	);
}

add_action( 'wp_ajax_learndash_apply_coupon', 'learndash_apply_coupon' );
add_action( 'wp_ajax_learndash_remove_coupon', 'learndash_remove_coupon' );
add_action( 'wp_ajax_learndash_enroll_with_zero_price', 'learndash_enroll_with_zero_price' );
add_action( 'learndash_transaction_created', 'learndash_process_coupon_after_transaction' );
add_filter( 'learndash_get_price_by_coupon', 'learndash_get_price_by_coupon', 10, 3 );
