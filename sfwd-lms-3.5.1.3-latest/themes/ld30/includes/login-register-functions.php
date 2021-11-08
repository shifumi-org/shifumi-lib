<?php
/**
 * LearnDash LD30 Login and Registration functions
 *
 * Handles authentication, registering, resetting passwords and other user handling.
 *
 * @since 3.0.0
 *
 * @package LearnDash\Templates\LD30
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LOGIN FUNCTIONS
 */

/**
 * Adds a hidden form field to the login form.
 *
 * Fires on `login_form_top` hook.
 *
 * @since 3.0.0
 *
 * @param string $content Login form content.
 *
 * @return string Login form hidden field content.
 */
function learndash_add_login_field_top( $content = '' ) {
	$content .= '<input id="learndash-login-form" type="hidden" name="learndash-login-form" value="' . wp_create_nonce( 'learndash-login-form' ) . '" />';

	$post_type = get_post_type( get_the_ID() );
	if ( in_array( $post_type, learndash_get_post_types( 'course' ), true ) ) {
		$course_id = learndash_get_course_id( get_the_ID() );

		/**
		 * Filters whether to allow enrollment of course with the login. The default value is true.
		 *
		 * @since 3.1.0
		 *
		 * @param boolean $include_course Whether to allow login from the course.
		 * @param int     $course_id      Course ID.
		 */
		if ( ( ! empty( $course_id ) ) && ( in_array( learndash_get_setting( $course_id, 'course_price_type' ), array( 'free' ), true ) ) && ( apply_filters( 'learndash_login_form_include_course', true, $course_id ) ) ) {
			$content .= '<input name="learndash-login-form-course" value="' . $course_id . '" type="hidden" />';
			$content .= wp_nonce_field( 'learndash-login-form-course-' . $course_id . '-nonce', 'learndash-login-form-course-nonce', false, false );
		}
	} elseif ( in_array( $post_type, array( learndash_get_post_type_slug( 'group' ) ), true ) ) {
		$group_id = get_the_ID();

		/**
		 * Filters whether to allow enrollment of group with the login. The default value is true.
		 *
		 * @since 3.2.0
		 *
		 * @param boolean $include_group Whether to allow login from the group.
		 * @param int     $group_id       Group ID.
		 */
		if ( ( ! empty( $group_id ) ) && ( in_array( learndash_get_setting( $group_id, 'group_price_type' ), array( 'free' ), true ) ) && ( apply_filters( 'learndash_login_form_include_group', true, $group_id ) ) ) {
			$content .= '<input name="learndash-login-form-post" value="' . $group_id . '" type="hidden" />';
			$content .= wp_nonce_field( 'learndash-login-form-post-' . $group_id . '-nonce', 'learndash-login-form-post-nonce', false, false );
		}
	}

	return $content;
}

// Add a filter for validation returns.
add_filter( 'login_form_top', 'learndash_add_login_field_top' );

/**
 * Updates user course data on user login.
 *
 * Fires on `authenticate` hook.
 *
 * @since 3.0.7
 *
 * @param WP_User $user     WP_User object if success. wp_error is error.
 * @param string  $username Login form entered user login.
 * @param string  $password Login form entered user password.
 *
 * @return WP_User|void Returns WP_User if a valid user object is passed.
 */
function learndash_authenticate( $user, $username, $password ) {
	if ( ( $user ) && ( is_a( $user, 'WP_User' ) ) ) {
		/**
		 * If the user started from a Course and registered then once they
		 * go through the password setup they will login. The login form
		 * could be the default WP login, the LD course modal or some other
		 * plugin. During the registration if the captured course ID is saved
		 * in the user meta we enroll that user into that course.
		 */
		$registered_post_id = get_user_meta( $user->ID, '_ld_registered_post', true );
		if ( '' !== $registered_post_id ) {
			delete_user_meta( $user->ID, '_ld_registered_post' );
		}
		$registered_post_id = absint( $registered_post_id );
		if ( ! empty( $registered_post_id ) ) {
			if ( in_array( get_post_type( $registered_post_id ), array( learndash_get_post_type_slug( 'course' ) ), true ) ) {
				ld_update_course_access( $user->ID, $registered_post_id );
			} elseif ( in_array( get_post_type( $registered_post_id ), array( learndash_get_post_type_slug( 'group' ) ), true ) ) {
				ld_update_group_access( $user->ID, $registered_post_id );
			}
		}

		/**
		 * If the user login is coming from a LD course then we enroll the
		 * user into the course. This helps save a step for the user.
		 */
		$login_post_id = learndash_validation_login_form_course();
		$login_post_id = absint( $login_post_id );
		if ( ! empty( $login_post_id ) ) {
			if ( in_array( get_post_type( $login_post_id ), array( learndash_get_post_type_slug( 'course' ) ), true ) ) {
				ld_update_course_access( $user->ID, $login_post_id );
			} elseif ( in_array( get_post_type( $login_post_id ), array( learndash_get_post_type_slug( 'group' ) ), true ) ) {
				ld_update_group_access( $user->ID, $login_post_id );
			}
		}
	} elseif ( ( is_wp_error( $user ) ) && ( $user->has_errors() ) ) {
		/**
		 * This is here instead of learndash_login_failed() because WP
		 * handles 'empty_username', 'empty_password' conditions different
		 * then invalid values.
		 *
		 * See logic in wp_authenticate()
		 */
		$redirect_to = learndash_validation_login_form_redirect_to();
		if ( $redirect_to ) {
			$ignore_codes = array( 'empty_username', 'empty_password' );

			if ( is_wp_error( $user ) && in_array( $user->get_error_code(), $ignore_codes, true ) ) {
				$redirect_to = add_query_arg( 'login', 'failed', $redirect_to );
				$redirect_to = learndash_add_login_hash( $redirect_to );
				learndash_safe_redirect( $redirect_to );
			}
		}
	}

	return $user;
}
add_filter( 'authenticate', 'learndash_authenticate', 99, 3 );

/**
 * Handles the login fail scenario from WP.
 *
 * Fires on `wp_login_failed` hook.
 * Note for 'empty_username', 'empty_password' error conditions this action
 * will not be called. Those conditions are handled in learndash_authenticate()
 * if the user logged in via the LD modal.
 *
 * @since 3.0.0
 *
 * @param string $username Login name from login form process. Not used.
 */
function learndash_login_failed( $username = '' ) {
	$redirect_to = learndash_validation_login_form_redirect_to();
	if ( $redirect_to ) {
		$redirect_to = add_query_arg( 'login', 'failed', $redirect_to );
		$redirect_to = learndash_add_login_hash( $redirect_to );
		learndash_safe_redirect( $redirect_to );
	}
}
add_action( 'wp_login_failed', 'learndash_login_failed', 1, 1 );

/**
 * Gets the login form course ID.
 *
 * @since 3.1.2
 *
 * @return int|false $course_id Valid course_id if valid otherwise false.
 */
function learndash_validation_login_form_course() {
	if ( ( isset( $_POST['learndash-login-form'] ) ) && ( wp_verify_nonce( $_POST['learndash-login-form'], 'learndash-login-form' ) ) ) {
		if ( ( isset( $_POST['learndash-login-form-post'] ) ) && ( ! empty( $_POST['learndash-login-form-post'] ) ) ) {
			$post_id = absint( $_POST['learndash-login-form-post'] );
			if ( ( isset( $_POST['learndash-login-form-post-nonce'] ) ) && ( wp_verify_nonce( $_POST['learndash-login-form-post-nonce'], 'learndash-login-form-post-' . $post_id . '-nonce' ) ) ) {

				if ( in_array( get_post_type( $post_id ), array( learndash_get_post_type_slug( 'course' ) ), true ) ) {
					/** This filter is documented in themes/ld30/includes/login-register-functions.php */
					if ( ( ! empty( $post_id ) ) && ( apply_filters( 'learndash_login_form_include_course', true, $post_id ) ) ) {
						return absint( $post_id );
					}
				} elseif ( in_array( get_post_type( $post_id ), array( learndash_get_post_type_slug( 'group' ) ), true ) ) {
					/** This filter is documented in themes/ld30/includes/login-register-functions.php */
					if ( ( ! empty( $post_id ) ) && ( apply_filters( 'learndash_login_form_include_group', true, $post_id ) ) ) {
						return absint( $post_id );
					}
				}
			}
		}
	}
	return false;
}

/**
 * Gets the login form validation redirect URL.
 *
 * @since 3.1.2
 *
 * @return int|false $course_id Valid course_id if valid otherwise false.
 */
function learndash_validation_login_form_redirect_to() {
	if ( ( isset( $_POST['learndash-login-form'] ) ) && ( wp_verify_nonce( $_POST['learndash-login-form'], 'learndash-login-form' ) ) ) {
		if ( ( isset( $_POST['redirect_to'] ) ) && ( ! empty( $_POST['redirect_to'] ) ) ) {
			return esc_url( $_POST['redirect_to'] );
		}
	}
	return false;
}


/**
 * REGISTRATION FUNCTIONS
 */

/**
 * Handles user registration success.
 *
 * Fires on `user_register` hook.
 * When the user registers it if was from a Course we capture that for later
 * when the user goes through the password set logic. After the password set
 * we can redirect the user to the course. See learndash_password_reset()
 * function.
 *
 * @since 3.1.2
 *
 * @param integer $user_id The Registers user ID.
 */
function learndash_register_user_success( $user_id = 0 ) {
	if ( ! empty( $user_id ) ) {
		$post_id = learndash_validation_registration_form_course();
		if ( ! empty( $post_id ) ) {
			add_user_meta( $user_id, '_ld_registered_post', absint( $post_id ) );
		}
	}
}
add_action( 'user_register', 'learndash_register_user_success', 10, 1 );

/**
 * Handles user registration failure.
 *
 * Fires on `register_post` hook.
 * From this function we capture the failed registration errors and send the user
 * back to the registration form part of the LD login modal.
 *
 * @since 3.1.1.1
 *
 * @param string $sanitized_user_login User entered login (sanitized).
 * @param string $user_email           User entered email.
 * @param array  $errors               Array of registration errors.
 */
function learndash_user_register_error( $sanitized_user_login, $user_email, $errors ) {

	$redirect_url = learndash_validation_registration_form_redirect_to();
	if ( $redirect_url ) {
		$redirect_url = remove_query_arg( 'ld-registered', $redirect_url );

		/**
		 * This line is copied from register_new_user function of wp-login.php. So the
		 * filtername should not be prefixed with 'learndash_'.
		 */
		/** This filter is documented in https://developer.wordpress.org/reference/hooks/registration_errors/ */
		$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		// This if check is copied from register_new_user function of wp-login.php.
		if ( $errors->get_error_code() ) {
			$has_errors = true;

			// add error codes to custom redirection URL one by one.
			foreach ( $errors->errors as $e => $m ) {
				$redirect_url = add_query_arg( $e, '1', $redirect_url );
			}

			$redirect_url = learndash_add_login_hash( $redirect_url );

			/**
			 * Filters URL that a user should be redirected when there is an error while registration.
			 *
			 * @since 3.1.1.1
			 *
			 * @param string  $redirect_url The URL to be redirected when there are errors.
			 */
			$redirect_url = apply_filters( 'learndash_registration_error_url', $redirect_url );
			if ( ! empty( $redirect_url ) ) {
				// add finally, redirect to your custom page with all errors in attributes.
				learndash_safe_redirect( $redirect_url );
			}
		}
	}
}
add_action( 'register_post', 'learndash_user_register_error', 99, 3 );

/**
 * Utility function to check and return the registration form course_id.
 *
 * @since 3.1.2
 *
 * @return int|false $course_id Valid course_id if valid otherwise false.
 */
function learndash_validation_registration_form_course() {
	if ( ( isset( $_POST['learndash-registration-form'] ) ) && ( wp_verify_nonce( $_POST['learndash-registration-form'], 'learndash-registration-form' ) ) ) {
		if ( ( isset( $_POST['learndash-registration-form-post'] ) ) && ( ! empty( $_POST['learndash-registration-form-post'] ) ) ) {
			$post_id = absint( $_POST['learndash-registration-form-post'] );
			if ( ! empty( $post_id ) ) {
				if ( ! in_array( get_post_type( $post_id ), array( learndash_get_post_type_slug( 'course' ) ), true ) ) {
					/**
					 * Filters whether to allow user registration from the course.
					 *
					 * @since 3.1.0
					 *
					 * @param boolean $include_course whether to allow user registration from the course.
					 * @param int     $post_id      Course ID.
					 */
					if ( ( ! empty( $post_id ) ) && ( apply_filters( 'learndash_registration_form_include_course', true, $post_id ) ) ) {
						if ( ( isset( $_POST['learndash-registration-form-post-nonce'] ) ) && ( wp_verify_nonce( $_POST['learndash-registration-form-post-nonce'], 'learndash-registration-form-post-' . $post_id . '-nonce' ) ) ) {
							return absint( $post_id );
						}
					}
				} elseif ( ! in_array( get_post_type( $post_id ), array( learndash_get_post_type_slug( 'group' ) ), true ) ) {
					/**
					 * Filters whether to allow user registration from the group.
					 *
					 * @since 3.2.0
					 *
					 * @param boolean $include_group whether to allow user registration from the group.
					 * @param int     $post_id      Course ID.
					 */
					if ( ( ! empty( $post_id ) ) && ( apply_filters( 'learndash_registration_form_include_group', true, $post_id ) ) ) {
						if ( ( isset( $_POST['learndash-registration-form-post-nonce'] ) ) && ( wp_verify_nonce( $_POST['learndash-registration-form-post-nonce'], 'learndash-registration-form-post-' . $post_id . '-nonce' ) ) ) {
							return absint( $post_id );
						}
					}
				}
			}
		}
	}
	return false;
}

/**
 * Utility function to check the registration form course_id.
 *
 * @since 3.1.2
 *
 * @return int|false $course_id Valid course_id if valid otherwise false.
 */
function learndash_validation_registration_form_redirect_to() {
	if ( ( isset( $_POST['learndash-registration-form'] ) ) && ( wp_verify_nonce( $_POST['learndash-registration-form'], 'learndash-registration-form' ) ) ) {
		if ( ( isset( $_POST['redirect_to'] ) ) && ( ! empty( $_POST['redirect_to'] ) ) ) {
			return esc_url( $_POST['redirect_to'] );
		}
	}
	return false;
}

/**
 * PASSWORD RESET FUNCTIONS
 */

/**
 * Variable to capture the user from the reset password. This var
 * is used in the learndash_password_reset_login_url() function to
 * redirect the user back to the origin.
 */
global $ld_password_reset_user;
$ld_password_reset_user = ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

/**
 * Handles password reset logic.
 *
 * Called after the user updates new password.
 *
 * @since 3.1.2
 *
 * @global WP_User $ld_password_reset_user Global password reset user.
 *
 * @param WP_User $user     WP_User object.
 * @param string  $new_pass New Password.
 */
function learndash_password_reset( $user, $new_pass ) {
	if ( $user ) {
		global $ld_password_reset_user;
		$ld_password_reset_user = $user; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

		add_filter( 'login_url', 'learndash_password_reset_login_url', 30, 3 );
	}
}
add_action( 'password_reset', 'learndash_password_reset', 30, 2 );

/**
 * Handles password reset logic.
 *
 * Fires on `login_url` hook.
 *
 * @since 3.1.2
 *
 * @global WP_User $ld_password_reset_user Global password reset user.
 *
 * @param string         $login_url    Current login_url.
 * @param string         $redirect     Query string redirect_to parameter and value.
 * @param boolean|string $force_reauth Whether to force reauthentication.
 *
 * @return string Returns login URL.
 */
function learndash_password_reset_login_url( $login_url = '', $redirect = '', $force_reauth = '' ) {
	global $ld_password_reset_user;

	if ( ( isset( $_GET['action'] ) ) && ( 'resetpass' === $_GET['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No nonces on public facing login forms
		if ( ( ! empty( $login_url ) ) && ( empty( $redirect ) ) ) {
			$user = $ld_password_reset_user;
			if ( ( $user ) && ( is_a( $user, 'WP_User' ) ) ) {
				$ld_login_url = get_user_meta( $user->ID, '_ld_lostpassword_redirect_to', true );
				delete_user_meta( $user->ID, '_ld_lostpassword_redirect_to' );
				if ( ! empty( $ld_login_url ) ) {
					$login_url = esc_url( $ld_login_url );
				} else {
					$registered_post_id = get_user_meta( $user->ID, '_ld_registered_post', true );
					//delete_user_meta( $user->ID, '_ld_registered_post', $registered_post_id );
					if ( ! empty( $registered_post_id ) ) {
						$registered_post_url = get_permalink( $registered_post_id );
						$registered_post_url = learndash_add_login_hash( $registered_post_url );
						$login_url           = esc_url( $registered_post_url );
					}
				}
			}
		}
	}

	return $login_url;
}
/**
 * Stores the password reset redirect_to URL.
 *
 * Fires on `login_form_lostpassword` hook.
 *
 * When the user clicks the password reset on the LD login popup we capture the
 * 'redirect_to' URL. This is done at step 2 of the password reset process after
 * the user has enter their username/email.
 *
 * The user will then receive an email from WP with a link to reset the
 * password. Once the user has created a new password they will be shown a
 * login link. That login URL will be the stored 'redirect_to' user meta value.
 * See the function learndash_password_reset_login_url() for that stage of the
 * processing.
 *
 * @since 3.1.1.1
 */
function learndash_login_form_lostpassword() {
	if ( isset( $_POST['learndash-registration-form'], $_REQUEST['redirect_to'] ) &&
		wp_verify_nonce( $_POST['learndash-registration-form'], 'learndash-registration-form' ) &&
		! empty( $_REQUEST['redirect_to'] ) ) {
		$redirect_to = esc_url( $_REQUEST['redirect_to'] );

		// Only if the 'redirect_to' link contains our parameter.
		if ( false !== strpos( $redirect_to, 'ld-resetpw=true' ) ) {
			if ( isset( $_POST['user_login'] ) && is_string( $_POST['user_login'] ) ) {
				$user_login = wp_unslash( $_POST['user_login'] );
				$user       = get_user_by( 'login', $user_login );
				if ( ( $user ) && ( is_a( $user, 'WP_User' ) ) ) {
					/**
					 * We remove the 'ld-resetpw' part because we don't want to trigger
					 * the login modal showing the password has been reset again.
					 */
					$redirect_to = remove_query_arg( 'ld-resetpw', $redirect_to );

					/**
					 * Store the redirect URL in user meta. This will be retrieved in
					 * the function learndash_password_reset_login_url().
					 */
					update_user_meta( $user->ID, '_ld_lostpassword_redirect_to', $redirect_to );
				}
			}
		}
	}
}
add_action( 'login_form_lostpassword', 'learndash_login_form_lostpassword', 30 );


/**
 * Adds '#login' to the end of a the login URL.
 *
 * Used throughout the LD30 login model and processing functions.
 *
 * @since 3.1.2
 *
 * @param string $url URL to check and append hash.
 *
 * @return string Returns URL after adding login hash.
 */
function learndash_add_login_hash( $url = '' ) {
	if ( strpos( $url, '#login' ) === false ) {
		$url .= '#login';
	}

	return $url;
}

/**
 * Gets an array of login error conditions.
 *
 * @since 3.1.2
 *
 * @param boolean $return_keys True to return keys of conditions only.
 *
 * @return array Returns an array of login error conditions.
 */
function learndash_login_error_conditions( $return_keys = false ) {

	/**
	 * Filters list of User registration errors.
	 *
	 * @since 3.0.0
	 *
	 * @param array $registration_errors An Associative array of Registration error and description.
	 */
	$errors_conditions = apply_filters(
		'learndash-registration-errors',
		array(
			'username_exists'  => __( 'Registration username exists.', 'learndash' ),
			'email_exists'     => __( 'Registration email exists.', 'learndash' ),
			'empty_username'   => __( 'Registration requires a username.', 'learndash' ),
			'empty_email'      => __( 'Registration requires a valid email.', 'learndash' ),
			'invalid_username' => __( 'Invalid username.', 'learndash' ),
			'invalid_email'    => __( 'Invalid email.', 'learndash' ),
		)
	);
	if ( true === $return_keys ) {
		return array_keys( $errors_conditions );
	}
	return $errors_conditions;
}
