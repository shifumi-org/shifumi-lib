<?php
/**
 * LearnDash LD30 Displays the infobar in group context
 *
 * @var int    $group_id     Group ID.
 * @var int    $user_id      User ID.
 * @var bool   $has_access   User has access to group or is enrolled.
 * @var bool   $group_status User's Group Status. Completed, No Started, or In Complete.
 * @var object $post         Group Post Object.
 *
 * @since 3.2.0
 *
 * @package LearnDash\Templates\LD30\Modules
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$group_pricing = learndash_get_group_price( $group_id );

if ( is_user_logged_in() && isset( $has_access ) && $has_access ) :
	?>
	<div class="ld-course-status ld-course-status-enrolled">
		<?php
		/**
		 * Action to add custom content inside the ld-course-status infobox before the progress bar
		 *
		 * @since 3.2.0
		 *
		 * @param string|false $post_type Post type slug.
		 * @param int          $group_id  Group ID.
		 * @param int          $user_id   User ID.
		 */
		do_action( 'learndash-group-infobar-access-progress-before', get_post_type(), $group_id, $user_id );

		learndash_get_template_part(
			'modules/progress-group.php',
			array(
				'context'  => 'group',
				'user_id'  => $user_id,
				'group_id' => $group_id,
			),
			true
		);

		/**
		 * Action to add custom content inside the ld-course-status infobox after the progress bar
		 *
		 * @since 3.2.0
		 *
		 * @param string|false $post_type Post type slug.
		 * @param int          $group_id  Group ID.
		 * @param int          $user_id   User ID.
		 */
		do_action( 'learndash-group-infobar-access-progress-after', get_post_type(), $group_id, $user_id );

		learndash_status_bubble( $group_status );

		/**
		 * Action to add custom content inside the ld-course-status infobox after the access status
		 *
		 * @since 3.2.0
		 *
		 * @param string|false $post_type Post type slug.
		 * @param int          $group_id  Group ID.
		 * @param int          $user_id   User ID.
		 */
		do_action( 'learndash-group-infobar-access-status-after', get_post_type(), $group_id, $user_id );
		?>

	</div> <!--/.ld-course-status-->

<?php elseif ( 'open' !== $group_pricing['type'] ) : ?>

	<div class="ld-course-status ld-course-status-not-enrolled">

		<?php
		/**
		 * Action to add custom content inside the un-enrolled ld-course-status infobox before the status
		 *
		 * @since 3.2.0
		 *
		 * @param string|false $post_type Post type slug.
		 * @param int          $group_id  Group ID.
		 * @param int          $user_id   User ID.
		 */
		do_action( 'learndash-group-infobar-noaccess-status-before', get_post_type(), $group_id, $user_id );
		?>

		<div class="ld-course-status-segment ld-course-status-seg-status">

			<?php do_action( 'learndash-group-infobar-status-cell-before', get_post_type(), $group_id, $user_id ); ?>

			<span class="ld-course-status-label"><?php echo esc_html__( 'Current Status', 'learndash' ); ?></span>
			<div class="ld-course-status-content">
				<span class="ld-status ld-status-waiting ld-tertiary-background" data-ld-tooltip="
				<?php
					printf(
						// translators: placeholder: group
						esc_attr_x( 'Enroll in this %s to get access', 'placeholder: group', 'learndash' ),
						esc_html( learndash_get_custom_label_lower( 'group' ) )
					);
				?>
				">
				<?php esc_html_e( 'Not Enrolled', 'learndash' ); ?></span>
			</div>

			<?php do_action( 'learndash-group-infobar-status-cell-after', get_post_type(), $group_id, $user_id ); ?>

		</div> <!--/.ld-course-status-segment-->

		<?php
		/**
		 * Action to add custom content inside the un-enrolled ld-course-status infobox before the price
		 *
		 * @since 3.0.0
		 *
		 * @param string|false $post_type Post type slug.
		 * @param int          $group_id  Group ID.
		 * @param int          $user_id   User ID.
		 */
		do_action( 'learndash-group-infobar-noaccess-price-before', get_post_type(), $group_id, $user_id );
		?>

		<div class="ld-course-status-segment ld-course-status-seg-price">

			<?php do_action( 'learndash-group-infobar-price-cell-before', get_post_type(), $group_id, $user_id ); ?>

			<span class="ld-course-status-label"><?php echo esc_html__( 'Price', 'learndash' ); ?></span>

			<div class="ld-course-status-content">
				<span class="ld-course-status-price">
					<?php
					if ( isset( $group_pricing['price'] ) && ! empty( $group_pricing['price'] ) ) :
						if ( 'closed' !== $group_pricing['type'] ) :
							echo wp_kses_post( '<span class="ld-currency">' . learndash_30_get_currency_symbol() . '</span>' );
							endif;
						echo wp_kses_post( $group_pricing['price'] );
						else :
								$label = apply_filters( 'learndash_no_price_price_label', ( 'closed' === $group_pricing['type'] ? __( 'Closed', 'learndash' ) : __( 'Free', 'learndash' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Late escaped on output
								echo esc_html( $label );
						endif;

						if ( isset( $group_pricing['type'] ) && 'subscribe' === $group_pricing['type'] ) :
							?>
							<span class="ld-text ld-recurring-duration">
									<?php
									echo sprintf(
										// translators: Recurring duration message.
										esc_html_x( 'Every %1$s %2$s', 'Recurring duration message', 'learndash' ),
										esc_html( $group_pricing['interval'] ),
										esc_html( $group_pricing['frequency'] )
									);
									?>
							</span>
						<?php endif; ?>
				</span>
			</div>

			<?php do_action( 'learndash-group-infobar-price-cell-after', get_post_type(), $group_id, $user_id ); ?>

		</div> <!--/.ld-group-status-segment-->

		<?php
		/**
		 * Action to add custom content inside the un-enrolled ld-course-status infobox before the action
		 *
		 * @since 3.2.0
		 *
		 * @param string|false $post_type Post type slug.
		 * @param int          $group_id  Group ID.
		 * @param int          $user_id   User ID.
		 */
		do_action( 'learndash-group-infobar-noaccess-action-before', get_post_type(), $group_id, $user_id );

		$group_status_class = apply_filters(
			'ld-course-status-segment-class',
			'ld-course-status-segment ld-course-status-seg-action status-' .
			( isset( $group_pricing['type'] ) ? sanitize_title( $group_pricing['type'] ) : '' )
		);
		?>

		<div class="<?php echo esc_attr( $group_status_class ); ?>">
			<span class="ld-course-status-label"><?php echo esc_html_e( 'Get Started', 'learndash' ); ?></span>
			<div class="ld-course-status-content">
				<div class="ld-course-status-action">
					<?php
						do_action( 'learndash-course-infobar-action-cell-before', get_post_type(), $group_id, $user_id );

						$login_model = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'login_mode_enabled' );

						/** This filter is documented in themes/ld30/includes/shortcode.php */
						$login_url = apply_filters( 'learndash_login_url', ( 'yes' === $login_model ? '#login' : wp_login_url( get_permalink() ) ) );

					switch ( $group_pricing['type'] ) {
						case ( 'open' ):
						case ( 'free' ):
							if ( apply_filters( 'learndash_login_modal', true, $group_id, $user_id ) && ! is_user_logged_in() ) :
								echo '<a class="ld-button" href="' . esc_url( $login_url ) . '">' . esc_html__( 'Login to Enroll', 'learndash' ) . '</a></span>';
								else :
									echo learndash_payment_buttons( $post ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Outputs Payment button HTML
								endif;
							break;
						case ( 'paynow' ):
						case ( 'subscribe' ):
							// Price (Free / Price)
							$ld_payment_buttons = learndash_payment_buttons( $post );
							echo $ld_payment_buttons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Outputs Button HTML
							if ( apply_filters( 'learndash_login_modal', true, $group_id, $user_id ) && ! is_user_logged_in() ) :
								echo '<span class="ld-text">';
								if ( ! empty( $ld_payment_buttons ) ) {
									esc_html_e( 'or', 'learndash' );
								}
								echo '<a class="ld-login-text" href="' . esc_url( $login_url ) . '">' . esc_html__( 'Login', 'learndash' ) . '</a></span>';
								endif;
							break;
						case ( 'closed' ):
							$button = learndash_payment_buttons( $post );
							if ( empty( $button ) ) :
								echo '<span class="ld-text">' . sprintf(
									// translators: placeholder: group
									esc_html_x( 'This %s is currently closed', 'placeholder: group', 'learndash' ),
									esc_html( learndash_get_custom_label_lower( 'group' ) )
								)
									 . '</span>';
								else :
									echo $button; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Outputs Button HTML
								endif;
							break;
					}

					/**
					 * Fires after the group infobar action cell.
					 *
					 * @since 3.2.0
					 *
					 * @param string|false $post_type Post type slug.
					 * @param int          $group_id  Group ID.
					 * @param int          $user_id   User ID.
					 */
					do_action( 'learndash-group-infobar-action-cell-after', get_post_type(), $group_id, $user_id );
					?>
				</div>
			</div>
		</div> <!--/.ld-group-status-action-->

		<?php
		/**
		 * Fires inside the un-enrolled group infobox after the price
		 *
		 * @since 3.2.0
		 *
		 * @param string|false $post_type Post type slug.
		 * @param int          $group_id  Group ID.
		 * @param int          $user_id   User ID.
		 */
		do_action( 'learndash-group-infobar-noaccess-price-after', get_post_type(), $group_id, $user_id );
		?>

	</div> <!--/.ld-course-status-->

<?php endif; ?>
