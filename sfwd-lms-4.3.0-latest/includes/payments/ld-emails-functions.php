<?php
/**
 * Functions for handling the Settings Emails notifications
 *
 * @since 3.6.0
 * @package LearnDash
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Send the course/group purchase success email
 *
 * @since 3.6.0
 *
 * @param int $user_id User ID.
 * @param int $post_id Course/Group post ID.
 */
function learndash_send_purchase_success_email( int $user_id = 0, int $post_id = 0 ) {
	$user_id = absint( $user_id );

	if ( empty( $user_id ) ) {
		return false;
	}

	$user = get_user_by( 'id', $user_id );
	if ( ( ! $user ) || ( ! is_a( $user, 'WP_User' ) ) ) {
		return false;
	}

	$post_id = absint( $post_id );
	if ( empty( $post_id ) ) {
		return false;
	}

	$post = get_post( $post_id );
	if ( ( ! $post ) || ( ! is_a( $post, 'WP_Post' ) ) ) {
		return false;
	}

	if ( ! in_array( $post->post_type, learndash_get_post_type_slug( array( 'course', 'group' ) ), true ) ) {
		return false;
	}

	$placeholders = array(
		'{user_login}'   => $user->user_login,
		'{first_name}'   => $user->user_firstname,
		'{last_name}'    => $user->user_lastname,
		'{display_name}' => $user->display_name,
		'{user_email}'   => $user->user_email,

		'{post_title}'   => get_the_title( $post->ID ),
		'{post_url}'     => get_permalink( $post->ID ),

		'{site_title}'   => wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ),
		'{site_url}'     => wp_parse_url( home_url(), PHP_URL_HOST ),
	);

	/**
	 * Filters purchase email placeholders.
	 *
	 * @param array $placeholders Array of email placeholders and values.
	 * @param int   $user_id      User ID.
	 * @param int   $post_id      Post ID.
	 */
	$placeholders = apply_filters( 'learndash_purchase_email_placeholders', $placeholders, $user_id, $post_id );

	if ( in_array( $post->post_type, learndash_get_post_type_slug( array( 'course' ) ), true ) ) {
		$email_setting = LearnDash_Settings_Section_Emails_Course_Purchase_Success::get_section_settings_all();
	} elseif ( in_array( $post->post_type, learndash_get_post_type_slug( array( 'group' ) ), true ) ) {
		$email_setting = LearnDash_Settings_Section_Emails_Group_Purchase_Success::get_section_settings_all();
	}

	if ( 'on' === $email_setting['enabled'] ) {

		/**
		 * Filters purchase email subject.
		 *
		 * @param string $email_subject Email subject text.
		 * @param int    $user_id       User ID.
		 * @param int    $post_id       Post ID.
		 */
		$email_setting['subject'] = apply_filters( 'learndash_purchase_email_subject', $email_setting['subject'], $user_id, $post_id );
		if ( ! empty( $email_setting['subject'] ) ) {
			$email_setting['subject'] = learndash_emails_parse_placeholders( $email_setting['subject'], $placeholders );
		}

		/**
		 * Filters purchase email message.
		 *
		 * @param string $email_message Email message text.
		 * @param int    $user_id       User ID.
		 * @param int    $post_id       Post ID.
		 */
		$email_setting['message'] = apply_filters( 'learndash_purchase_email_message', $email_setting['message'], $user_id, $post_id );
		if ( ! empty( $email_setting['message'] ) ) {
			$email_setting['message'] = learndash_emails_parse_placeholders( $email_setting['message'], $placeholders );
		}

		if ( ( ! empty( $email_setting['subject'] ) ) && ( ! empty( $email_setting['message'] ) ) ) {
			return learndash_emails_send( $user->user_email, $email_setting );
		}
	}
}

/**
 * Parses the email subject and message to replace email placeholders
 *
 * @since 3.6.0
 *
 * @param string $content     Email content to parse for placeholders.
 * @param array  $placeholders Array of placeholder token/values.
 *
 * @return array $email_content Email content.
 */
function learndash_emails_parse_placeholders( string $content = '', array $placeholders = array() ) {

	if ( ( ! empty( $content ) ) && ( ! empty( $placeholders ) ) ) {
		$content = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $content );
	}

	return do_shortcode( $content );
}

/**
 * Filter the 'From name' used in the email notification.
 *
 * @since 3.6.0
 *
 * @param string $from_name Email from name used by WordPress.
 *
 * @return string From: name sent in the email notification
 */
function learndash_emails_from_name( string $from_name = '' ): string {
	$learndash_from_name = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_Emails_Sender_Settings', 'from_name' );
	if ( ! empty( $learndash_from_name ) ) {
		$from_name = sanitize_text_field( $learndash_from_name );
	}

	return $from_name;
}

/**
 * Filter the 'From email' used in the email notification.
 *
 * @since 3.6.0
 *
 * @param string $from_email Email from used by WordPress.
 *
 * @return string From: email sent in the email notification
 */
function learndash_emails_from_email( string $from_email = '' ): string {
	$learndash_from_email = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_Emails_Sender_Settings', 'from_email' );
	if ( ! empty( $learndash_from_email ) ) {
		$from_email = sanitize_email( $learndash_from_email );
	}

	return sanitize_email( $from_email );
}

/**
 * Defines the From: email sent in the email notification
 *
 * @since 3.6.0
 *
 * @param string $user_email Destination email.
 * @param array  $email_args  Array of email args for 'subject', 'message', and 'content_type'.
 * @param string $headers     Additional email headers.
 * @param array  $attachments Email attachments.
 *
 * @return bool True if the email is sent.
 */
function learndash_emails_send( string $user_email = '', array $email_args = array(), string $headers = '', array $attachments = array() ): bool {
	if ( empty( $user_email ) ) {
		return false;
	}

	$content_type_html = 'text/html' === $email_args['content_type'];

	$email_args_defaults = array(
		'subject'      => '',
		'message'      => '',
		'content_type' => '',
	);

	$email_args = wp_parse_args( $email_args, $email_args_defaults );

	if ( empty( $email_args['subject'] ) && empty( $email_args['message'] ) ) {
		return false;
	}

	if ( empty( $headers ) ) {
		$headers = 'Content-Type: ' . $email_args['content_type'] . ' charset=' . get_option( 'blog_charset' );
	}

	if ( $content_type_html ) {
		$email_args['message'] = wpautop( stripcslashes( $email_args['message'] ) );

		add_filter(
			'wp_mail_content_type',
			function() {
				return 'text/html';
			}
		);
	}

	add_filter( 'wp_mail_from', 'learndash_emails_from_email' );
	add_filter( 'wp_mail_from_name', 'learndash_emails_from_name' );

	$mail_ret = wp_mail( $user_email, $email_args['subject'], $email_args['message'], $headers, $attachments );

	remove_filter( 'wp_mail_from', 'learndash_emails_from_email' );
	remove_filter( 'wp_mail_from_name', 'learndash_emails_from_name' );

	if ( $content_type_html ) {
		remove_filter(
			'wp_mail_content_type',
			function() {
				return 'text/html';
			}
		);
	}

	return $mail_ret;
}

/**
 * Send the course/group purchase invoice email
 *
 * @since 4.2.0
 *
 * @param int $transaction_id Transaction ID.
 *
 * @return void
 */
function learndash_send_purchase_invoice_email( int $transaction_id ): void {

	$transaction_id = absint( $transaction_id );
	if ( empty( $transaction_id ) ) {
		return;
	}

	$user_id = get_post_meta( $transaction_id, 'user_id', true );

	if ( empty( $user_id ) ) {
		return;
	}

	$user = get_user_by( 'id', $user_id );
	if ( ( ! $user ) || ( ! is_a( $user, 'WP_User' ) ) ) {
		return;
	}

	$email_setting = LearnDash_Settings_Section_Emails_Purchase_Invoice::get_section_settings_all();

	if ( 'on' !== $email_setting['enabled'] ) {
		return;
	}

	$post_id = get_post_meta( $transaction_id, 'post_id', true );

	$purchased_post = get_post( $post_id );
	if ( ( ! $purchased_post ) || ( ! is_a( $purchased_post, 'WP_Post' ) ) ) {
		return;
	}

	$transaction_post = get_post( $transaction_id );
	if ( ( ! $transaction_post ) || ( ! is_a( $transaction_post, 'WP_Post' ) ) ) {
		return;
	}

	if ( ! in_array( $purchased_post->post_type, learndash_get_post_type_slug( array( LDLMS_Post_Types::COURSE, LDLMS_Post_Types::GROUP ) ), true ) ) {
		return;
	}

	if ( learndash_get_post_type_slug( LDLMS_Post_Types::TRANSACTION ) !== $transaction_post->post_type ) {
		return;
	}

	$placeholders = array(
		'{user_login}'   => $user->user_login,
		'{first_name}'   => $user->user_firstname,
		'{last_name}'    => $user->user_lastname,
		'{display_name}' => $user->display_name,
		'{user_email}'   => $user->user_email,
		'{post_title}'   => $purchased_post->post_title,
	);

	/**
	 * Filters purchase email placeholders.
	 *
	 * @since 4.2.0
	 *
	 * @param array $placeholders Array of email placeholders and values.
	 * @param int   $user_id      User ID.
	 * @param int   $post_id      Post ID.
	 */
	$placeholders = apply_filters( 'learndash_purchase_invoice_email_placeholders', $placeholders, $user_id, $post_id );

	/**
	 * Filters purchase invoice email subject.
	 *
	 * @since 4.2.0
	 *
	 * @param string $email_subject Email subject text.
	 * @param int    $user_id       User ID.
	 * @param int    $post_id       Post ID.
	 */
	$email_setting['subject'] = apply_filters( 'learndash_purchase_invoice_email_subject', $email_setting['subject'], $user_id, $post_id );
	if ( ! empty( $email_setting['subject'] ) ) {
		$email_setting['subject'] = learndash_emails_parse_placeholders( $email_setting['subject'], $placeholders );
	}

	/**
	 * Filters purchase invoice email message.
	 *
	 * @since 4.2.0
	 *
	 * @param string $email_message Email message text.
	 * @param int    $user_id       User ID.
	 * @param int    $post_id       Post ID.
	 */
	$email_setting['message'] = apply_filters( 'learndash_purchase_invoice_email_message', $email_setting['message'], $user_id, $post_id );
	if ( ! empty( $email_setting['message'] ) ) {
		$email_setting['message'] = learndash_emails_parse_placeholders( $email_setting['message'], $placeholders );
	}

	$transaction_meta = get_post_meta( $transaction_id, '', true );
	// remove Stripe's metadata from meta array.
	if ( true === array_key_exists( 'stripe_metadata', $transaction_meta ) ) {
		unset( $transaction_meta['stripe_metadata'] );
	}

	$purchase_date = date_i18n( get_option( 'date_format' ), strtotime( $transaction_post->post_date ) ) . ' ' . date_i18n( get_option( 'time_format' ), strtotime( $transaction_post->post_date ) );

	$pdf_data = array(
		'purchaser_name'   => learndash_emails_parse_placeholders( $email_setting['purchaser_name'], $placeholders ),
		'user_id'          => $user_id,
		'purchase_date'    => $purchase_date,
		'transaction_id'   => $transaction_id,
		'transaction_meta' => learndash_transaction_get_payment_meta( $transaction_id ),
		'vat_number'       => $email_setting['vat_number'],
		'company_name'     => $email_setting['company_name'],
		'company_address'  => $email_setting['company_address'],
		'company_logo'     => esc_url( wp_get_attachment_url( $email_setting['company_logo'] ) ),
		'logo_location'    => $email_setting['logo_location'],
		'filename'         => learndash_purchase_invoice_filename( $user_id, $post_id ),
		'filepath'         => learndash_purchase_invoice_filepath( $post_id ),
	);

	require_once __DIR__ . '/../ld-convert-post-pdf.php';

	$pdf = learndash_purchase_invoice_pdf(
		array(
			'pdf_data' => $pdf_data,
		)
	);

	if ( ! empty( $email_setting['subject'] ) && ( true === $pdf ) ) {
		learndash_emails_send(
			$user->user_email,
			$email_setting,
			$headers = '',
			array( $pdf_data['filepath'] . $pdf_data['filename'] )
		);
		update_post_meta( $transaction_id, 'purchase_invoice_filename', $pdf_data['filename'] );
	}
}

add_action( 'learndash_transaction_created', 'learndash_send_purchase_invoice_email' );
