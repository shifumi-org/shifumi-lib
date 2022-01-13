<?php
/**
 * Functions for handling the Settings Emails notifications
 *
 * @since 3.6.0
 * @package LearnDash
 */

/**
 * Send the course/group purchase success email
 *
 * @since 3.6.0
 *
 * @param int $user_id User ID.
 * @param int $post_id Course/Group post ID.
 */
function learndash_send_purchase_success_email( $user_id = 0, $post_id = 0 ) {
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

	$email_setting = LearnDash_Settings_Section_Emails_Course_Purchase_Success::get_section_settings_all();
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
function learndash_emails_parse_placeholders( $content = '', $placeholders = array() ) {

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
function learndash_emails_from_name( $from_name = '' ) {
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
function learndash_emails_from_email( $from_email = '' ) {
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
 */
function learndash_emails_send( $user_email = '', $email_args = array() ) {
	if ( empty( $user_email ) ) {
		return false;
	}

	$email_args_defaults = array(
		'subject'      => '',
		'message'      => '',
		'content_type' => '',
	);

	$email_args = wp_parse_args( $email_args, $email_args_defaults );

	if ( ( ! empty( $email_args['subject'] ) ) && ( ! empty( $email_args['message'] ) ) ) {

		$headers = 'Content-Type: ' . $email_args['content_type'] . ' charset=' . get_option( 'blog_charset' );

		if ( 'text/html' === $email_args['content_type'] ) {
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

		$mail_ret = wp_mail( $user_email, $email_args['subject'], $email_args['message'], $headers );

		remove_filter( 'wp_mail_from', 'learndash_emails_from_email' );
		remove_filter( 'wp_mail_from_name', 'learndash_emails_from_name' );

		if ( 'text/html' === $email_args['content_type'] ) {
			remove_filter(
				'wp_mail_content_type',
				function() {
					return 'text/html';
				}
			);
		}

		return $mail_ret;
	}
}
