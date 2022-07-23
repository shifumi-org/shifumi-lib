<?php

namespace LearnDash\Achievements;

/**
 * Hook into LD content for output a button, allow user to redeem the course if needed.
 */
class Course_Point {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'learndash_header_data', array( $this, 'add_achievement_setting_tab' ), 11, 3 );
		add_filter( 'learndash_payment_button', array( $this, 'add_redeem_button' ), 20, 2 );
		add_action( 'learndash-course-infobar-price-cell-after', array( $this, 'show_price' ), 10, 3 );
		add_action( 'wp_loaded', array( $this, 'process' ) );
	}

	/**
	 * @param $post_type
	 * @param $course_id
	 * @param $user_id
	 */
	public function show_price( $post_type, $course_id, $user_id ) {
		if ( learndash_get_post_type_slug( 'course' ) !== $post_type ) {
			return;
		}

		if ( ! $this->is_enabled( $course_id ) ) {
			return;
		}

		$price            = learndash_get_setting( $course_id, 'achievements_buy_course_course_price' );
		$use_inline_style = apply_filters( 'learndash_achievements_buy_course_inline_style', true );
		?>
		<?php if ( $use_inline_style ) : ?>
			<style>
				.achievement-course-price-points {
					position: relative;
					top: -35px;
					font-size: 1rem;
					line-height: 1rem;
				}
			</style>
		<?php endif; ?>
		<span class="achievement-course-price-points">
		<?php
		if ( $this->is_points_enough( $course_id ) ) {
			echo sprintf(
					_x( 'Or use %d points', 'price_label', 'learndash-achievements' ),
					$price
			);
		} else {
			echo sprintf(
					_x( 'Or use %d points (You have %d)', 'price_label', 'learndash-achievements' ),
					$price,
					Database::get_user_points( $user_id )
			);
		}
		?>
	</span>
		<?php
	}

	/**
	 * Process
	 */
	public function process() {
		if ( ! isset( $_POST['_achievementsnonce'] ) ) {
			return;
		}
		$_POST   = wp_unslash( $_POST );
		$post_id = isset( $_POST['course_id'] ) ? $_POST['course_id'] : absint( $_POST['course_id'] );
		$post    = get_post( $post_id );
		if ( ! is_object( $post ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST['_achievementsnonce'], 'achievements_redeem_' . $post_id ) ) {
			return;
		}

		if ( ! $this->is_enabled( $post_id ) ) {
			return;
		}

		if ( ! $this->is_points_enough( $post_id ) ) {
			return;
		}

		ld_update_course_access( get_current_user_id(), $post_id );
		$used_points = get_user_meta( get_current_user_id(), 'achievements_points_used', true );
		if ( empty( $used_points ) ) {
			$used_points = 0;
		}
		$price       = learndash_get_setting( $post_id, 'achievements_buy_course_course_price' );
		$price       = absint( $price );
		$used_points = absint( $used_points );
		$used_points += $price;
		update_user_meta( get_current_user_id(), 'achievements_points_used', $used_points );
		// record transactions.
		$post_id = wp_insert_post(
				array(
						'post_title'  => get_the_title( $post_id ),
						'post_type'   => 'sfwd-transactions',
						'post_status' => 'publish',
						'post_author' => get_current_user_id(),
				)
		);
		$arr     = array(
				'course_id' => $post_id,
				'user_id'   => get_current_user_id(),
		);
		wp_safe_redirect( get_permalink( $post ) );
		exit();
	}

	/**
	 * Need to support for Paypal and stripe if they already activated.
	 *
	 * @param $paypal_button
	 * @param $payment_params
	 */
	public function add_redeem_button( $paypal_button, $payment_params ) {
		if ( ! $this->is_enabled( $payment_params['post']->ID ) ) {
			return $paypal_button;
		}

		if ( $this->is_points_enough( $payment_params['post']->ID ) ) {
			// get the user points.
			$current_points = Database::get_user_points( get_current_user_id() );
			// can show up the button here.
			$button_text = sprintf(
					esc_attr_x(
							'Use points (Your points: %d)',
							'payment_button',
							'learndash-achievements'
					),
					$current_points
			);
			ob_start();
			?>
			<div class="learndash_checkout_button learndash_achievements_points_button">
				<form method="post">
					<?php
					echo wp_nonce_field(
							'achievements_redeem_' . $payment_params['post']->ID,
							'_achievementsnonce'
					);
					?>
					<input type="hidden" name="course_id" value="<?php echo $payment_params['post']->ID; ?>"/>
					<input type="submit" value="<?php echo $button_text; ?>" class="btn-join"/>
				</form>
			</div>
			<?php
			$strings       = ob_get_clean();
			$paypal_button .= $strings;
		}

		return $paypal_button;
	}

	/**
	 * @param $post_id
	 *
	 * @return bool
	 */
	protected function is_points_enough( $post_id ) {
		$price = learndash_get_setting( $post_id, 'achievements_buy_course_course_price' );
		$price = absint( $price );
		// get the user points.
		$current_points = Database::get_user_points( get_current_user_id() );
		$current_points = absint( $current_points );

		return $current_points >= $price;
	}

	/**
	 * @param $post_id
	 *
	 * @return bool
	 */
	protected function is_enabled( $post_id ) {
		$course_pricing = learndash_get_course_price( $post_id );
		if ( 'paynow' !== $course_pricing['type'] ) {
			return false;
		}
		$is_enabled = learndash_get_setting( $post_id, 'achievements_buy_course' );

		return '1' === $is_enabled;
	}

	/**
	 * Add a settings tab into course settings page
	 *
	 * @param $header_data
	 * @param $menu_tab_key
	 * @param $keydata
	 */
	public function add_achievement_setting_tab( $header_data, $menu_tab_key, $keydata ) {
		if ( 'edit.php?post_type=sfwd-courses' === $menu_tab_key && isset( $_GET['post'] ) && ! empty( $_GET['post'] ) ) {
			$header_data['tabs'][] = array(
					'id'        => 'learndash_course_achievements',
					'name'      => __( 'Achievements', 'learndash-achievements' ),
					'metaboxes' => array(
							'learndash-course-achievements-settings',
					),
			);
		}

		return $header_data;
	}
}

new Course_Point();
