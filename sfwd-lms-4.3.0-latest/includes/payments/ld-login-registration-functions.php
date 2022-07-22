<?php
/**
 * Functions related to login/regisration functions
 *
 * @since 3.6.0
 *
 * @package LearnDash
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LearnDash LD30 Shows registration form for user registration
 *
 * @since 3.6.0
 *
 * @param array $attr Array of attributes for shortcode.
 */
function learndash_registration_output( $attr = array() ) {

	$attr_defaults = array(
		'width' => 0,
	);
	$attr          = shortcode_atts( $attr_defaults, $attr );

	$formwidth = $attr['width'];

	if ( is_multisite() ) {
		$learndash_can_register = users_can_register_signup_filter();
	} else {
		$learndash_can_register = get_option( 'users_can_register' );
	}

	$learndash_errors_conditions = learndash_login_error_conditions();

	$active_template_key = LearnDash_Theme_Register::get_active_theme_key();

	?>

	<div class="<?php echo ( 'ld30' === $active_template_key ) ? esc_attr( learndash_the_wrapper_class() ) : 'learndash-wrapper'; ?>">

	<div id="learndash-registration-wrapper" <?php echo ( ! empty( $formwidth ) ) ? 'style="width: ' . esc_attr( $formwidth ) . ';"' : ''; ?>>

	<?php
	if ( isset( $_GET['ld-registered'] ) && 'true' === $_GET['ld-registered'] ) {
		learndash_get_template_part(
			'modules/alert.php',
			array(
				'type'    => 'success',
				'icon'    => 'alert',
				'message' => __( 'Registration successful.', 'learndash' ),
			),
			true
		);

		/**
		 * Fires after the register modal errors.
		 *
		 * @since 3.6.0
		 *
		 * @param array $errors An array of error details.
		 */
		do_action( 'learndash_registration_successful_after' );
	}

	if ( isset( $_GET['ld_register_id'] ) && '0' < $_GET['ld_register_id'] ) :
		$register_id = absint( $_GET['ld_register_id'] );

		$post_type = get_post_type( $register_id );

		if ( LDLMS_Post_Types::get_post_type_slug( 'course' ) === $post_type ) {
			$course_pricing = learndash_get_course_price( $register_id );
		} elseif ( learndash_get_post_type_slug( 'group' ) === $post_type ) {
			$course_pricing = learndash_get_group_price( $register_id );
		} else {
			esc_html_e( 'Invalid Course or Group', 'learndash' );
			return;
		}

		$course_pricing['price'] = learndash_get_price_as_float( $course_pricing['price'] );

		if ( ! empty( $course_pricing['trial_price'] ) ) {
			$course_pricing['trial_price'] = learndash_get_price_as_float( $course_pricing['trial_price'] );
		}

		$attached_coupon_data = array();
		if ( is_user_logged_in() && learndash_post_has_attached_coupon( $register_id, get_current_user_id() ) ) {
			$attached_coupon_data = learndash_get_attached_coupon_data( $register_id, get_current_user_id() );
		}
		?>

		<div class="order-overview">
			<p class="order-heading">
				<?php esc_html_e( 'Order Overview', 'learndash' ); ?>
			</p>

			<p class="purchase-title">
				<?php echo esc_html( get_the_title( $register_id ) ); ?>
			</p>

			<?php if ( 'paynow' === $course_pricing['type'] && is_user_logged_in() ) : ?>
				<div id="coupon-alerts">
					<div class="coupon-alert coupon-alert-success" style="display: none">
						<?php
						learndash_get_template_part(
							'modules/alert.php',
							array(
								'type'    => 'success',
								'icon'    => 'alert',
								'message' => ' ',
							),
							true
						);
						?>
					</div>
					<div class="coupon-alert coupon-alert-warning" style="display: none">
						<?php
						learndash_get_template_part(
							'modules/alert.php',
							array(
								'type'    => 'warning',
								'icon'    => 'alert',
								'message' => ' ',
							),
							true
						);
						?>
					</div>
				</div>
			<?php endif; ?>

			<div class="purchase-rows">
				<?php if ( 'subscribe' === $course_pricing['type'] && ! empty( $course_pricing['trial_interval'] ) && ! empty( $course_pricing['trial_frequency'] ) ) : ?>
					<div class="purchase-row">
						<span class="purchase-label">
							<?php esc_html_e( 'Trial', 'learndash' ); ?>
						</span>

						<span class="purchase-field-price">
							<?php echo esc_html( learndash_get_price_formatted( $course_pricing['trial_price'] ? $course_pricing['trial_price'] : 0 ) ); ?>

							<?php echo esc_html__( ' for ', 'learndash' ) . absint( $course_pricing['trial_interval'] ) . ' ' . esc_html( $course_pricing['trial_frequency'] ); ?>
						</span>
					</div>
				<?php endif; ?>

				<div class="purchase-row" id="price-row">
					<span class="purchase-label">
						<?php esc_html_e( 'Price', 'learndash' ); ?>
					</span>

					<span class="purchase-value">
						<?php
						echo esc_html(
							( 'free' === $course_pricing['type'] || 'open' === $course_pricing['type'] )
								? __( 'Free', 'learndash' )
								: learndash_get_price_formatted( $course_pricing['price'] )
						);

						if ( ! empty( $course_pricing['interval'] ) ) {
							echo esc_html__( ' every ', 'learndash' ) . absint( $course_pricing['interval'] ) . ' ' . esc_html( $course_pricing['frequency'] );

							if ( ! empty( $course_pricing['repeats'] ) ) {
								echo esc_html__( ' for ', 'learndash' ) . absint( $course_pricing['interval'] ) * absint( $course_pricing['repeats'] ) . ' ' . esc_html( $course_pricing['repeat_frequency'] );
							}
						}
						?>
					</span>
				</div>
			</div>

			<?php if ( 'paynow' === $course_pricing['type'] && is_user_logged_in() ) : ?>
				<?php if ( learndash_active_coupons_exist() ) : ?>
					<form
						class="coupon-form"
						id="apply-coupon-form"
						data-nonce="<?php echo esc_attr( wp_create_nonce( 'learndash-coupon-nonce' ) ); ?>"
						data-post-id="<?php echo esc_attr( $register_id ); ?>"
					>
						<input type="text" id="coupon-field" placeholder="<?php esc_html_e( 'Coupon', 'learndash' ); ?>" />
						<input type="submit" value="<?php esc_html_e( 'Apply Coupon', 'learndash' ); ?>" />
					</form>
				<?php endif; ?>

				<div class="totals" id="totals" style="display: <?php echo ! empty( $attached_coupon_data ) ? 'block' : 'none'; ?>">
					<span class="order-heading">
						<?php esc_html_e( 'Totals', 'learndash' ); ?>
					</span>

					<div class="purchase-rows">
						<div class="purchase-row" id="subtotal-row">
							<span class="purchase-label">
								<?php esc_html_e( 'Subtotal', 'learndash' ); ?>
							</span>
							<span class="purchase-value">
								<?php echo esc_html( learndash_get_price_formatted( $course_pricing['price'] ) ); ?>
							</span>
						</div>

						<div
							class="purchase-row"
							id="coupon-row"
							style="<?php echo esc_attr( empty( $attached_coupon_data ) ? 'display: none' : '' ); ?>"
						>
							<span class="purchase-label">
								<?php esc_html_e( 'Coupon: ', 'learndash' ); ?>
								<span>
									<?php
									if ( ! empty( $attached_coupon_data ) ) {
										echo esc_html( $attached_coupon_data[ LEARNDASH_COUPON_META_KEY_CODE ] );
									}
									?>
								</span>
							</span>
							<span class="purchase-value">
								<form
									id="remove-coupon-form"
									data-nonce="<?php echo esc_attr( wp_create_nonce( 'learndash-coupon-nonce' ) ); ?>"
									data-post-id="<?php echo esc_attr( $register_id ); ?>"
								>
									<span>
										<?php
										if ( ! empty( $attached_coupon_data ) ) {
											echo esc_html( learndash_get_price_formatted( floatval( $attached_coupon_data['discount'] ) ) );
										}
										?>
									</span>
									<input type="submit" class="button-small" value="<?php esc_html_e( 'Remove', 'learndash' ); ?>" />
								</form>
							</span>
						</div>

						<?php
						/** This filter is documented in includes/payments/class-learndash-stripe-connect-checkout-integration.php */
						$total = apply_filters( 'learndash_get_price_by_coupon', floatval( $course_pricing['price'] ), $register_id, get_current_user_id() );
						?>

						<div class="purchase-row" id="total-row" data-total="<?php echo esc_attr( $total ); ?>">
							<span class="purchase-label">
								<?php esc_html_e( 'Total', 'learndash' ); ?>
							</span>
							<span class="purchase-value">
								<?php
								echo esc_html( learndash_get_price_formatted( $total ) );
								?>
							</span>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php
			if ( isset( $_GET['ld-registered'] ) || is_user_logged_in() ) {
				echo learndash_payment_buttons( $register_id );
			}

			// translators: placeholder: Return to Course/Group.
			echo '<span class="order-overview-return">' . sprintf( esc_html_x( 'Return to %s', 'placeholder: Return to Course/Group.', 'learndash' ), '<a href="' . esc_html( get_permalink( absint( $_GET['ld_register_id'] ) ) ) . '">' . esc_html( get_the_title( absint( $_GET['ld_register_id'] ) ) ) . '</a></p>' );
			?>
		</div>
	<?php endif; ?>

	<?php
	if ( ( isset( $_REQUEST['attributes']['preview_show'] ) && 'true' === sanitize_text_field( $_REQUEST['attributes']['preview_show'] ) ) || ! is_user_logged_in() ) {
		// translators: placeholder: Message above registration form if user logged out.
		echo '<p class="registration-login-link">' . sprintf( esc_html_x( 'Already have an account? %s', 'placeholder: Message above registration form if user logged out.', 'learndash' ), '<a href="' . esc_url( learndash_get_login_url() ) . '">' . esc_html__( 'Log In', 'learndash' ) . '</a>' ) . '</p>';

		if ( $learndash_can_register ) :
			if ( has_action( 'learndash_registration_form_override' ) ) {
				/**
				* Allow for replacement of the defaut LearnDash Registration form
				*
				* @since 3.6.0
				*/
				do_action( 'learndash_registration_form_override' );
			} else {
				/**
				* Fires before the registration form heading.
				*
				* @since 3.6.0
				*/
				do_action( 'learndash_registration_form_before' );
				if ( is_multisite() ) {
					$learndash_register_action_url = network_site_url( 'wp-signup.php' );
					$learndash_field_name_login    = 'user_name';
					$learndash_field_name_email    = 'user_email';
				} else {
					$learndash_register_action_url = site_url( 'wp-login.php?action=register', 'login_post' );
					$learndash_field_name_login    = 'user_login';
					$learndash_field_name_email    = 'user_email';
				}

				$learndash_errors = array(
					'has_errors' => false,
					'message'    => '',
				);

				foreach ( $learndash_errors_conditions as $learndash_param => $learndash_message ) {
					if ( isset( $_GET[ $learndash_param ] ) ) {
						$learndash_errors['has_errors'] = true;
						if ( ! empty( $learndash_errors['message'] ) ) {
							$learndash_errors['message'] .= '<br />';
						}
						$learndash_errors['message'] .= $learndash_message;
					}
				}

				if ( $learndash_errors['has_errors'] ) :
					learndash_get_template_part(
						'modules/alert.php',
						array(
							'type'    => 'warning',
							'icon'    => 'alert',
							'message' => $learndash_errors['message'],
						),
						true
					);

						/**
						 * Fires after the register modal errors.
						 *
						 * @since 3.6.0
						 *
						 * @param array $errors An array of error details.
						 */
						do_action( 'learndash_registration_errors_after', $learndash_errors );

				endif;
				?>
				<form name="learndash_registerform" id="learndash_registerform" class="ldregister" action="<?php echo esc_url( $learndash_register_action_url ); ?>" method="post">
				<?php
				/**
				 * Fires before the loop when displaying the registration form fields
				 *
				 * @since 3.6.0
				 */
				do_action( 'learndash_registration_form_fields_before' );

				$learndash_registration_fields = LearnDash_Settings_Section_Registration_Fields::get_section_settings_all();
				$learndash_fields_order        = $learndash_registration_fields['fields_order'];

				foreach ( $learndash_fields_order as $learndash_field ) {
					$learndash_required = ( 'yes' === $learndash_registration_fields[ $learndash_field . '_required' ] ) ? 'aria-required="true"' : '';
					if ( 'username' === $learndash_field ) {
						$learndash_name_field = $learndash_field_name_login;
					} elseif ( 'email' === $learndash_field ) {
						$learndash_name_field = $learndash_field_name_email;
					} else {
						$learndash_name_field = $learndash_field;
					}
					if ( 'yes' === $learndash_registration_fields[ $learndash_field . '_enabled' ] ) {
						echo '<p class="learndash-registration-field learndash-registration-field-' . esc_attr( $learndash_field ) . ' ' . ( ! empty( $learndash_required ) ? 'learndash-required' : '' ) . '"><label for="' . esc_attr( $learndash_field ) . '">' . esc_html( $learndash_registration_fields[ $learndash_field . '_label' ] ) . ( ! empty( $learndash_required ) ? ' <span class="learndash-required-field">*</span>' : '' ) . '</label>
						<input ' . esc_attr( $learndash_required ) . ' type="' . ( 'password' === $learndash_field ? 'password' : 'text' ) . '" id="' . esc_attr( $learndash_field ) . '" name="' . esc_attr( $learndash_name_field ) . '" value="' . ( isset( $_GET[ $learndash_name_field ] ) ? sanitize_text_field( $_GET[ $learndash_name_field ] ) : '' ) . '" /></p>';
						if ( 'password' === $learndash_field ) {
							echo '<p class="learndash-registration-field learndash-registration-field-confirm' . esc_attr( $learndash_field ) . ' ' . ( ! empty( $learndash_required ) ? 'learndash-required' : '' ) . '"><label for="confirm_password">' . esc_html__( 'Confirm Password', 'learndash' ) . ( ! empty( $learndash_required ) ? ' <span class="learndash-required-field">*</span>' : '' ) . '</label><input ' . esc_attr( $learndash_required ) . ' type="password" id="confirm_password" name="confirm_password" /></p>';
						}
					}
				}

				/**
				 * Fires after the loop when displaying the registration form fields
				 *
				 * @since 3.6.0
				 */
				do_action( 'learndash_registration_form_fields_after' );

				if ( isset( $_POST['ld_register_id'] ) && isset( $_GET['ld_register_id'] ) ) {
					$register_id = sanitize_text_field( $_POST['ld_register_id'] );
				} elseif ( isset( $_POST['ld_register_id'] ) ) {
					$register_id = sanitize_text_field( $_POST['ld_register_id'] );
				} elseif ( isset( $_GET['ld_register_id'] ) ) {
					$register_id = sanitize_text_field( $_GET['ld_register_id'] );
				} else {
					$register_id = 0;
				}

				$learndash_redirect_to_url = remove_query_arg( array_keys( $learndash_errors_conditions ), get_permalink() );
				if ( ! is_multisite() ) {
					// $ld_registration_success = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_Registration_Pages', 'registration_success' );
					// if ( ! empty( $ld_registration_success ) && ( ! isset( $_GET['ld_register_id'] ) || '0' === $_GET['ld_register_id'] ) ) {
					// $learndash_redirect_to_url = get_permalink( $ld_registration_success );
					// }
					$learndash_redirect_to_url = add_query_arg(
						array(
							'ld-registered'  => 'true',
							'ld_register_id' => $register_id,
						),
						$learndash_redirect_to_url
					);
				}

				if ( is_multisite() ) {
					signup_nonce_fields();
					?>
					<input type="hidden" name="signup_for" value="user" />
					<input type="hidden" name="stage" value="validate-user-signup" />
					<input type="hidden" name="blog_id" value="<?php echo get_current_blog_id(); ?>" />
					<?php

					/**
					 * Fires at the end of the user registration form on the site sign-up form.
					 *
					 * @since 3.6.0
					 *
					 * @param WP_Error $errors A WP_Error object containing 'user_name' or 'user_email' errors.
					 */
					do_action( 'signup_extra_fields', '' );
				} else {
					/** This filter is documented in https://developer.wordpress.org/reference/hooks/register_form/ */
					do_action( 'register_form' );
				}

				/**
				 * Fires inside the registration form.
				 *
				 * @since 3.6.0
				 */
				do_action( 'learndash_registration_form' );
				?>
				<input name="ld_register_id" value="<?php echo absint( $register_id ); ?>" type="hidden" />
				<input type="hidden" name="learndash-registration-form" value="<?php echo esc_attr( wp_create_nonce( 'learndash-registration-form' ) ); ?>" />
				<input type="hidden" name="redirect_to" value="<?php echo esc_url( $learndash_redirect_to_url ); ?>" />
				<p><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Register', 'learndash' ); ?>" /></p>
			</form>
				<?php
				/**
				 * Fires after the registration form heading.
				 *
				 * @since 3.6.0
				 */
				do_action( 'learndash_registration_form_after' );
			}
		endif;
	} else {
		if ( ! isset( $_GET['ld-registered'] ) && ! isset( $_GET['ld_register_id'] ) ) {
			$current_user = wp_get_current_user();
			// translators: placeholders: Current Logged In Username, WP Logout Link.
			echo sprintf( esc_html_x( 'Hello %1$s, looks like you\'re already logged in. Want to sign in as a different user? %2$s', 'placeholder: Current Logged In Username, WP Logout Link.', 'learndash' ), esc_html( $current_user->user_login ), '<a href="' . esc_url( wp_logout_url() ) . '">' . esc_html__( 'Log Out', 'learndash' ) . '</a>' );
		}
	}

	echo '</div></div>';

	learndash_registerform_password_strength_data();
}

/**
 * Retrieves the LD login URL if using the LD30 template and the LD Login & Registration feature. If not using the Login & Registration feature, uses the wp_login_url() function redirecting back to current page
 *
 * @since 3.6.0
 *
 * @return string The login URL
 */
function learndash_get_login_url() {
	$active_template_key = LearnDash_Theme_Register::get_active_theme_key();
	$login_mode_enabled  = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'login_mode_enabled' );
	$learndash_login_url = '';
	if ( ( 'ld30' === $active_template_key ) && ( 'yes' === $login_mode_enabled ) ) {
		learndash_load_login_modal_html();
		$learndash_login_url = '#login';
	} else {
		$learndash_login_url = wp_login_url( get_permalink( get_the_ID() ) );
	}

	return $learndash_login_url;
}

/**
 * Checks whether the New User Registration email is enabled or not
 *
 * @since 3.6.0
 *
 * @return boolean True if option is enabled
 */
function learndash_new_user_email_enabled() {
	$enabled = LearnDash_Settings_Section_Emails_New_User_Registration::get_section_settings_all();
	if ( 'on' === $enabled['enabled'] ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Grabs email subject/message for the new user register email
 *
 * @since 3.6.0
 *
 * @param array  $wp_new_user_notification_email Email content for new user registration.
 * @param object $user WP_User Object.
 * @param string $blogname Title of the current site.
 *
 * @return array Array of email data to be sent
 */
function learndash_emails_content_new_user( $wp_new_user_notification_email = '', $user = '', $blogname = '' ) {
	$email_setting = LearnDash_Settings_Section_Emails_New_User_Registration::get_section_settings_all();
	if ( 'on' === $email_setting['enabled'] ) {

		$placeholders = array(
			'{user_login}'   => $user->user_login,
			'{first_name}'   => $user->user_firstname,
			'{last_name}'    => $user->user_lastname,
			'{display_name}' => $user->display_name,
			'{user_email}'   => $user->user_email,

			'{post_title}'   => isset( $_REQUEST['ld_register_id'] ) ? get_the_title( absint( $_REQUEST['ld_register_id'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended.
			'{post_url}'     => isset( $_REQUEST['ld_register_id'] ) ? get_permalink( absint( $_REQUEST['ld_register_id'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended.

			'{site_title}'   => $blogname,
			'{site_url}'     => wp_parse_url( home_url(), PHP_URL_HOST ),
		);
		/**
		 * Filters new registration email placeholders.
		 *
		 * @param array $placeholders Array of email placeholders and values.
		 * @param int   $user_id      User ID.
		 */
		$placeholders = apply_filters( 'learndash_registration_email_placeholders', $placeholders, $user->ID );

		/**
		 * Filters registration email subject.
		 *
		 * @param string $email_subject Email subject text.
		 * @param int    $user_id       User ID.
		 */
		$email_setting['subject'] = apply_filters( 'learndash_registration_email_subject', $email_setting['subject'], $user->ID );
		if ( ! empty( $email_setting['subject'] ) ) {
			$wp_new_user_notification_email['subject'] = learndash_emails_parse_placeholders( $email_setting['subject'], $placeholders );
		}

		/**
		 * Filters registration email message.
		 *
		 * @param string $email_message Email message text.
		 * @param int    $user_id       User ID.
		 */
		$email_setting['message'] = apply_filters( 'learndash_registration_email_message', $email_setting['message'], $user->ID );
		if ( ! empty( $email_setting['message'] ) ) {
			$email_setting['message'] = learndash_emails_parse_placeholders( $email_setting['message'], $placeholders );
			if ( 'text/html' === $email_setting['content_type'] ) {
				$email_setting['message'] = wpautop( stripcslashes( $email_setting['message'] ) );
			} else {
				$email_setting['message'] = esc_html( wp_strip_all_tags( wptexturize( $email_setting['message'] ) ) );
			}
			$wp_new_user_notification_email['message'] = $email_setting['message'];
		}

		if ( 'text/html' === $email_setting['content_type'] ) {
			$wp_new_user_notification_email['headers'] = 'Content-Type: ' . $email_setting['content_type'] . ' charset=' . get_option( 'blog_charset' );

			add_filter(
				'wp_mail_content_type',
				function() {
					return 'text/html';
				}
			);
		}
	}
	return $wp_new_user_notification_email;
}

/**
 * Validates that the password and confirm password fields match in the registration form
 *
 * @since 3.6.0
 *
 * @return string Returns error if passwords do not match
 */
function learndash_registration_form_validate( $errors, $sanitized_user_login, $user_email ) {
	if ( isset( $_POST['ld_register_id'] ) ) {
		if ( ( isset( $_POST['learndash-registration-form'] ) ) && ( wp_verify_nonce( $_POST['learndash-registration-form'], 'learndash-registration-form' ) ) ) {
			$learndash_registration_fields = LearnDash_Settings_Section_Registration_Fields::get_section_settings_all();

			$first_name                    = '';
			if ( isset( $_POST['first_name'] ) ) {
				$first_name = sanitize_text_field( $_POST['first_name'] );
			}
			if ( 'yes' === $learndash_registration_fields['first_name_enabled'] && 'yes' === $learndash_registration_fields['first_name_required'] && empty( $first_name ) ) {
				$errors->add( 'required_first_name', __( 'Registration requires a first name.', 'learndash' ) );
			}

			$last_name = '';
			if ( isset( $_POST['last_name'] ) ) {
				$last_name = sanitize_text_field( $_POST['last_name'] );
			}
			if ( 'yes' === $learndash_registration_fields['last_name_enabled'] && 'yes' === $learndash_registration_fields['last_name_required'] && empty( $last_name ) ) {
				$errors->add( 'required_last_name', __( 'Registration requires a last name.', 'learndash' ) );
			}

			$password  = '';
			$cpassword = '';
			if ( isset( $_POST['password'] ) ) {
				$password = sanitize_text_field( $_POST['password'] );
			}
			if ( 'yes' === $learndash_registration_fields['password_required'] && empty( $password ) ) {
				$errors->add( 'empty_password', __( 'Registration requires a password.', 'learndash' ) );
			}
			if ( isset( $_POST['confirm_password'] ) ) {
				$cpassword = sanitize_text_field( $_POST['confirm_password'] );
			}

			if ( $password !== $cpassword ) {
				$errors->add( 'confirm_password', __( 'Passwords do not match.', 'learndash' ) );
			}
		}
	}
	return $errors;
}
/** This filter is documented in https://developer.wordpress.org/reference/hooks/registration_errors/ */
add_filter( 'registration_errors', 'learndash_registration_form_validate', 10, 3 );

/**
 * Utility function to check the registration form course_id.
 *
 * @since 3.1.2
 *
 * @return int|false $course_id Valid course_id if valid otherwise false.
 */
function learndash_validation_registration_form_redirect_to() {
	if ( ( isset( $_POST['learndash-registration-form'] ) ) && ( wp_verify_nonce( $_POST['learndash-registration-form'], 'learndash-registration-form' ) ) || ( isset( $_POST['learndash-login-form'] ) ) && ( wp_verify_nonce( $_POST['learndash-login-form'], 'learndash-login-form' ) ) ) {
		if ( ( isset( $_POST['redirect_to'] ) ) && ( ! empty( $_POST['redirect_to'] ) ) ) {
			return esc_url_raw( $_POST['redirect_to'] );
		}
	}
	return false;
}

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
		if ( ( $errors->has_errors() ) && ( $errors->get_error_code() ) ) {
			$has_errors = true;

			$learndash_registration_fields = LearnDash_Settings_Section_Registration_Fields::get_section_settings_all();
			$learndash_fields_order        = $learndash_registration_fields['fields_order'];

			if ( is_multisite() ) {
				$learndash_register_action_url        = network_site_url( 'wp-signup.php' );
				$learndash_learndash_field_name_login = 'user_name';
				$learndash_field_name_email           = 'user_email';
			} else {
				$learndash_register_action_url = site_url( 'wp-login.php?action=register', 'login_post' );
				$learndash_field_name_login    = 'user_login';
				$learndash_field_name_email    = 'user_email';
			}

			$field_array = array();
			foreach ( $learndash_fields_order as $learndash_field ) {
				if ( 'username' === $learndash_field ) {
					$learndash_name_field = $learndash_field_name_login;
				} elseif ( 'email' === $learndash_field ) {
					$learndash_name_field = $learndash_field_name_email;
				} else {
					$learndash_name_field = $learndash_field;
				}
				if ( 'yes' === $learndash_registration_fields[ $learndash_field . '_enabled' ] && 'password' !== $learndash_field ) {
					$learndash_field                      = sanitize_text_field( $_POST[ $learndash_name_field ] );
					$field_array[ $learndash_name_field ] = $learndash_field;
				}
			}

			$redirect_url = add_query_arg( $field_array, $redirect_url );

			// add error codes to custom redirection URL one by one.
			foreach ( $errors->errors as $e => $m ) {
				$redirect_url = add_query_arg( $e, '1', $redirect_url );
			}

			$login_mode_enabled      = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'login_mode_enabled' );
			$ld_registration_page_id = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_Registration_Pages', 'registration' );

			// If we are NOT using our registration form...
			if ( ! isset( $_POST['ld_register_id'] ) ) {
				if ( 'yes' === $login_mode_enabled ) {
					// We add the '#login' hash.
					$redirect_url = learndash_add_login_hash( $redirect_url );
				}
			}

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
		} else {
			if ( isset( $_POST['ld_register_id'] ) ) {
				if ( empty( $_POST['ld_register_id'] ) ) {
					// We set the 'redirect_to' only if tere are not errors in the registration data.
					$ld_registration_success_id  = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_Registration_Pages', 'registration_success' );
					$ld_registration_success_id  = absint( $ld_registration_success_id );
					$ld_registration_success_url = get_permalink( $ld_registration_success_id );
					if ( ! empty( $ld_registration_success_url ) ) {
						$_POST['redirect_to'] = $ld_registration_success_url;
					}
				}
			}
		}
	}
}
add_action( 'register_post', 'learndash_user_register_error', 99, 3 );

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
		$redirect_to = learndash_validation_registration_form_redirect_to();
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
	$redirect_to = learndash_validation_registration_form_redirect_to();
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
		if ( learndash_new_user_email_enabled() ) {
			add_filter( 'wp_new_user_notification_email', 'learndash_emails_content_new_user', 30, 3 );
			add_filter( 'wp_mail_from', 'learndash_emails_from_email' );
			add_filter( 'wp_mail_from_name', 'learndash_emails_from_name' );
		}
		$post_id = learndash_validation_registration_form_course();
		if ( isset( $_POST['ld_register_id'] ) ) {
			if ( isset( $_POST['first_name'] ) ) {
				$first_name = sanitize_text_field( $_POST['first_name'] );
				if ( ! empty( $first_name ) ) {
					update_user_meta( $user_id, 'first_name', $first_name );
				}
			}
			if ( isset( $_POST['last_name'] ) ) {
				$last_name = sanitize_text_field( $_POST['last_name'] );
				if ( ! empty( $last_name ) ) {
					update_user_meta( $user_id, 'last_name', $last_name );
				}
			}
			if ( isset( $_POST['password'] ) ) {
				$password  = sanitize_text_field( $_POST['password'] );
				$cpassword = sanitize_text_field( $_POST['confirm_password'] );
				if ( ! empty( $password ) && ! empty( $cpassword ) ) {
					wp_set_password( $password, $user_id );
				}
			}
			update_user_meta( $user_id, 'ld_register_form', time() );
		}
		if ( ! empty( $post_id ) ) {
			add_user_meta( $user_id, '_ld_registered_post', absint( $post_id ) );
		}

		if ( ( isset( $_POST['learndash-registration-form'] ) ) && ( wp_verify_nonce( $_POST['learndash-registration-form'], 'learndash-registration-form' ) ) && isset( $password ) ) {
			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id );
		}
	}
}
add_action( 'user_register', 'learndash_register_user_success', 10, 1 );

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
					// delete_user_meta( $user->ID, '_ld_registered_post', $registered_post_id );.
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
			'username_exists'     => __( 'Registration username exists.', 'learndash' ),
			'email_exists'        => __( 'Registration email exists.', 'learndash' ),
			'empty_username'      => __( 'Registration requires a username.', 'learndash' ),
			'empty_email'         => __( 'Registration requires a valid email.', 'learndash' ),
			'invalid_username'    => __( 'Invalid username.', 'learndash' ),
			'invalid_email'       => __( 'Invalid email.', 'learndash' ),
			'empty_password'      => __( 'Registration requires a password.', 'learndash' ),
			'confirm_password'    => __( 'Passwords do not match.', 'learndash' ),
			'required_first_name' => __( 'Registration requires a first name.', 'learndash' ),
			'required_last_name'  => __( 'Registration requires a last name', 'learndash' ),
		)
	);
	if ( true === $return_keys ) {
		return array_keys( $errors_conditions );
	}
	return $errors_conditions;
}

/**
 * Create a unique hash for the pre-purchase action that will validate the
 * return transaction logic.
 */
function learndash_paypal_init_user_purchase_hash( $user_id = 0, $product_id = 0 ) {
	$user_hash = '';

	$user_id    = absint( $user_id );
	$product_id = absint( $product_id );
	if ( ( ! empty( $user_id ) ) && ( ! empty( $product_id ) ) ) {
		$user = get_user_by( 'ID', $user_id );
		if ( ( $user ) && ( is_a( $user, 'WP_User' ) ) ) {
			$user_hash = wp_create_nonce( $user->ID . '-' . $user->user_login . '-' . $product_id );

			if ( ! empty( $user_hash ) ) {
				update_user_meta(
					$user_id,
					'ld_purchase_nonce_' . $user_hash,
					array(
						'user_id'    => $user_id,
						'product_id' => $product_id,
						'time'       => time(),
						'nonce'      => $user_hash,
					)
				);
			}
		}
	}

	return $user_hash;
}

/**
 * Get the PayPal purchase success redirect URL.
 *
 * After the PayPal purchase success, the customer can be redirected
 * to a specific destination URL.
 *
 * @since 3.6.0
 * @param int $post_id Course or Group post ID purchased.
 * @return string $return_url
 */
function learndash_paypal_get_purchase_success_redirect_url( $post_id = 0 ) {
	$return_url = '';

	$post_id = absint( $post_id );
	if ( ! empty( $post_id ) ) {

		$type_slug = '';
		if ( learndash_get_post_type_slug( 'course' ) === get_post_type( $post_id ) ) {
			$type_slug = 'course';
		} elseif ( learndash_get_post_type_slug( 'group' ) === get_post_type( $post_id ) ) {
			$type_slug = 'group';
		}

		if ( ! empty( $type_slug ) ) {
			$price_type = learndash_get_setting( $post_id, $type_slug . '_price_type' );
			if ( ! empty( $price_type ) ) {
				$enrollment_url = learndash_get_setting( $post_id, $type_slug . '_price_type_' . $price_type . '_enrollment_url' );
				if ( ! empty( $enrollment_url ) ) {
					$return_url = $enrollment_url;
				}
			}
		}
	}

	if ( empty( $return_url ) ) {
		$paypal_settings = LearnDash_Settings_Section::get_section_settings_all( 'LearnDash_Settings_Section_PayPal' );
		if ( ( isset( $paypal_settings['paypal_returnurl'] ) ) && ( ! empty( $paypal_settings['paypal_returnurl'] ) ) ) {
			$return_url = $paypal_settings['paypal_returnurl'];
		}
	}

	if ( empty( $return_url ) ) {
		$ld_registration_success_page_id = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_Registration_Pages', 'registration_success' );
		$ld_registration_success_page_id = absint( $ld_registration_success_page_id );
		if ( ! empty( $ld_registration_success_page_id ) ) {
			$return_url = get_permalink( $ld_registration_success_page_id );
		}
	}

	if ( ( empty( $return_url ) ) && ( ! empty( $post_id ) ) ) {
		/**
		 * If the enrollment URL is empty and the global PayPal return URL is empty,
		 * we return the customer to the course/group.
		 */
		$return_url = get_permalink( $post_id );
	}

	if ( empty( $return_url ) ) {
		$return_url = get_home_url();
	}

	/**
	 * Filters URL for PayPal purchase success.
	 *
	 * @since 3.6.0
	 *
	 * @param string $redirect_url The URL to be redirected on PayPal success.
	 * @param int    $post_id      The Course/Group Post ID.
	 */
	$return_url = apply_filters( 'learndash_paypal_purchase_success_url', $return_url, $post_id );

	return $return_url;
}

/**
 * Get the PayPal purchase cancel redirect URL.
 *
 * After the PayPal purchase cancelation, the customer can be redirected
 * to a specific destination URL.
 *
 * @since 3.6.0
 * @param int $post_id Course or Group post ID purchased.
 * @return string $return_url
 */
function learndash_paypal_get_purchase_cancel_redirect_url( $post_id = 0 ) {
	$return_url = '';

	$post_id = absint( $post_id );

	$paypal_settings = LearnDash_Settings_Section::get_section_settings_all( 'LearnDash_Settings_Section_PayPal' );
	if ( ( isset( $paypal_settings['paypal_cancelurl'] ) ) && ( ! empty( $paypal_settings['paypal_cancelurl'] ) ) ) {
		$return_url = $paypal_settings['paypal_cancelurl'];
	}

	if ( empty( $return_url ) ) {
		if ( ! empty( $post_id ) ) {
			if ( empty( $return_url ) ) {
				/**
				 * If the PayPal cencel URL is empty we return the customer to the course/group.
				 */
				$return_url = get_permalink( $post_id );
			}
		}
	}

	if ( empty( $return_url ) ) {
		$return_url = get_home_url();
	}

	/**
	 * Filters URL for PayPal purchase success.
	 *
	 * @since 3.6.0
	 *
	 * @param string $redirect_url The URL to be redirected on PayPal success.
	 * @param int    $post_id   The Course/Group Post ID.
	 */
	$return_url = apply_filters( 'learndash_paypal_purchase_cancel_url', $return_url, $post_id );

	return $return_url;
}

/**
 * Defines data for the password strength meter on registration form
 *
 * @since 3.6.1
 */
function learndash_registerform_password_strength_data() {
	wp_enqueue_script( 'learndash-password-strength-meter' );

	$params = array();

	/**
	 * Filters the mininum password strength for the registration form
	 *
	 * @since 3.6.1
	 *
	 * @param int Minimum password strength value
	 */
	$params['min_password_strength'] = apply_filters( 'learndash_min_password_strength', 3 );

	/**
	 * Additional text to show user defining password strength
	 *
	 * @since 3.6.1
	 *
	 * @param string Text that displays next to password strength rating
	 */
	$params['i18n_password_error'] = esc_attr__( 'Please enter a stronger password.', 'learndash' );

	/**
	 * Additional text displayed below the password strength rating section to explain further
	 *
	 * @since 3.6.1
	 *
	 * @param string Message to display to user with additional information to help choose a better password
	 */
	$params['i18n_password_hint'] = esc_attr__( 'Hint: The password should be at least twelve characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp; ).', 'learndash' );

	/**
	 * Controls disabling registration form submission
	 *
	 * @since 3.6.1
	 *
	 * @param boolean Whether to prevent the registration form submission for a very weak or weak password strength rating. Defaults to true.
	 */
	$params['stop_register'] = apply_filters( 'learndash_weak_password_stop_register', true );

	wp_localize_script( 'learndash-password-strength-meter', 'learndash_password_strength_meter_params', $params );
}
