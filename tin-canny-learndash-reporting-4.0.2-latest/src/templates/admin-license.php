<?php

namespace uncanny_learndash_reporting;

if ( ! defined( 'WPINC' ) ) {
	die;
}

?>

<div class="wrap">
    <div class="tclr">

    	<?php

		// Add admin header and tabs
		$tab_active = 'uncanny-reporting-license-activation';
		include Config::get_template( 'admin-header.php' );

		?>

		<div class="tclr-license <?php echo implode( ' ', $license_css_classes ); ?>">
			<div class="tclr-license-status">
				<div class="tclr-license-status__icon">

					<?php if ( $license_is_active ) { ?>

						<svg class="tclr-license-status-icon__svg" xmlns="http://www.w3.org/2000/svg"
							 xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512">
							<path class="tclr-license-status-icon__svg-path tclr-license-status-icon__svg-check"
								  d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z"></path>
						</svg>

					<?php } else { ?>

						<svg class="tclr-license-status-icon__svg" xmlns="http://www.w3.org/2000/svg"
							 xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 352 512">
							<path class="tclr-license-status-icon__svg-path tclr-license-status-icon__svg-times"
								  d="M242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28 75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256 9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z"></path>
						</svg>

					<?php } ?>

				</div>
			</div>
			<div class="tclr-license-content">

				<form class="tclr-license-content-form" method="POST">

					<?php settings_fields( 'uncanny-reporting-license-activation' ); ?>

					<?php wp_nonce_field( Config::get_prefix() . '_nonce', Config::get_prefix() . '_nonce' ); ?>

					<div class="tclr-license-content-top">
						<div class="tclr-license-content-info">

							<?php ?>

							<div class="tclr-license-content-title">

								<?php

								if ( $license_is_active ) {
									_e( 'Your License is Active!', 'uncanny-learndash-reporting' );
								} else {
									_e( 'Your License is Not Active!', 'uncanny-learndash-reporting' );
								}

								?>
							</div>

							<div class="tclr-license-content-description">

								<?php

								switch ( $status ) {
									case 'valid':
										break;

									case 'empty':
										_e( 'Please enter a valid license code and click "Activate now".', 'uncanny-learndash-reporting' );
										break;

									case 'expired':
										printf(
											_x(
												'Your license has expired. Please %s to get instant access to updates and support.',
												'Your license has expired. Please renew your license to get instant access to updates and support.',
												'uncanny-learndash-reporting'
											),
											sprintf(
												'<a href="%s" target="_blank">%s</a>',
												'https://www.uncannyowl.com/checkout/?edd_license_key=' . $license . '&download_id=4113',
												_x(
													'renew your license',
													'Your license has expired. Please renew your license to get instant access to updates and support.',
													'uncanny-learndash-reporting' )
											)
										);
										break;

									case 'disabled':
										printf(
											_x( 'Your license is disabled. Please %s to get instant access to updates and support.',
												'Your license has disabled. Please renew your license to get instant access to updates and support.',
												'uncanny-learndash-reporting' ),
											sprintf(
												'<a href="%s" target="_blank">%s</a>',
												'https://www.uncannyowl.com/checkout/?edd_license_key=' . $license . '&download_id=4113',
												_x( 'renew your license',
													'Your license has expired. Please renew your license to get instant access to updates and support.',
													'uncanny-learndash-reporting' )
											)
										);
										break;

									case 'invalid':
										_e( 'The license code you entered is invalid.', 'uncanny-learndash-reporting' );
										break;
									case 'inactive':
										_e( 'The license code you entered is deactivated.', 'uncanny-learndash-reporting' );
										break;
								}

								?>

							</div>

							<div class="tclr-license-content-form">

								<?php if ( $license_is_active ) { ?>

									<input id="tclr-license-field" name="uo_reporting_license_key"
										   type="password" value="<?php esc_attr_e( $license ); ?>"
										   placeholder="<?php _e( 'Enter your license key', 'uncanny-learndash-reporting' ); ?>"
										   required>

								<?php } else { ?>

									<input id="tclr-license-field" name="uo_reporting_license_key" type="text"
										   value="<?php esc_attr_e( $license ); ?>"
										   placeholder="<?php _e( 'Enter your license key', 'uncanny-learndash-reporting' ); ?>"
										   required>

								<?php } ?>

							</div>

							<div class="tclr-license-content-mobile-buttons">

								<?php if ( $license_is_active ) { ?>

									<button type="submit" name="uo_reporting_license_deactivate"
											class="tclr-license-btn tclr-license-btn--error">
										<?php _e( 'Deactivate License', 'uncanny-learndash-reporting' ); ?>
									</button>

								<?php } else { ?>

									<button type="submit" name="uo_reporting_license_activate"
											class="tclr-license-btn tclr-license-btn--primary">
										<?php _e( 'Activate now', 'uncanny-learndash-reporting' ); ?>
									</button>

									<a href="<?php echo $buy_new_license; ?>" target="_blank"
									   class="tclr-license-btn tclr-license-btn--secondary">
										<?php _e( 'Buy license', 'uncanny-learndash-reporting' ); ?>
									</a>

								<?php } ?>

							</div>

						</div>
						<div class="tclr-license-content-faq">
							<div class="tclr-license-content-title">
								<?php _e( 'Need help?', 'uncanny-learndash-reporting' ); ?>
							</div>

							<div class="tclr-license-content-faq-list">
								<ul class="tclr-license-content-faq-list-ul">
									<li class="tclr-license-content-faq-item">
										<a href="<?php echo $where_to_get_my_license; ?>" target="_blank">
											<?php _e( 'Where to get my license key', 'uncanny-learndash-reporting' ); ?>
										</a>
									</li>
									<li class="tclr-license-content-faq-item">
										<a href="<?php echo $buy_new_license; ?>" target="_blank">
											<?php _e( 'Buy a new license', 'uncanny-learndash-reporting' ); ?>
										</a>
									</li>
									<li class="tclr-license-content-faq-item">
										<a href="<?php echo $knowledge_base; ?>" target="_blank">
											<?php _e( 'Knowledge Base', 'uncanny-learndash-reporting' ); ?>
										</a>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="tclr-license-content-footer">

						<?php if ( $license_is_active ) { ?>

							<button type="submit" name="uo_reporting_license_deactivate"
									class="tclr-license-btn tclr-license-btn--error">
								<?php _e( 'Deactivate License', 'uncanny-learndash-reporting' ); ?>
							</button>

						<?php } else { ?>

							<button type="submit" name="uo_reporting_license_activate"
									class="tclr-license-btn tclr-license-btn--primary">
								<?php _e( 'Activate now', 'uncanny-learndash-reporting' ); ?>
							</button>

							<a href="<?php echo $buy_new_license; ?>" target="_blank"
							   class="tclr-license-btn tclr-license-btn--secondary">
								<?php _e( 'Buy license', 'uncanny-learndash-reporting' ); ?>
							</a>

						<?php } ?>

					</div>

				</form>

			</div>
		</div>
	</div>
</div>