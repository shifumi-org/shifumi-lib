<?php

namespace LearnDash\Achievements;

use LearnDash\Achievements\Template\General_Template;

/**
 * This class handling the output in user profile page.
 * Class User_Profile
 *
 * @package LearnDash\Achievements
 */
class User_Profile {
	/**
	 * User_Profile constructor.
	 */
	public function __construct() {
		add_action( 'edit_user_profile', array( $this, 'add_badges_list' ) );
		add_action( 'show_user_profile', array( $this, 'add_badges_list' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_extra_points' ) );
		add_action( 'personal_options_update', array( $this, 'save_extra_points' ) );
	}

	/**
	 * Save the extra points
	 */
	public function save_extra_points( $user_id ) {
		if (
				isset( $_POST['_ldachievements_nonces'] ) &&
				wp_verify_nonce( $_POST['_ldachievements_nonces'], 'learndash_achievements_add_extra_points' )
				&& current_user_can( 'manage_options' )
		) {
			$extra_points = isset( $_POST['learndash_achievements_extra_points'] ) ? absint( $_POST['learndash_achievements_extra_points'] ) : false;
			if ( false !== $extra_points ) {
				update_user_meta( $user_id, 'learndash_achievements_extra_points', $extra_points );
			}
		}
	}

	/**
	 * Output the badges list under profile page/
	 *
	 * @param \WP_User $user The current WP_USER instance.
	 */
	public function add_badges_list( \WP_User $user ) {
		$achievements = Database::get_user_achievements( $user->ID );
		if ( ! current_user_can( 'manage_options' ) ) {
			// we won't allow other users can see this.
			return;
		}
		$extra_points = absint(
			get_user_meta(
				$user->ID,
				'learndash_achievements_extra_points',
				true
			)
		);
		?>
		<table class="form-table">
			<?php if ( count( $achievements ) ) : ?>
			<h3><?php esc_attr_e( 'Achievements', 'learndash-achievements' ); ?></h3>
				<?php General_Template::render_badges_table( $achievements ); ?>
			<?php endif ?>
			<h3><?php _e( 'Users\' points.' ); ?></h3>
			<p>
				<strong><?php _e( 'Earned Points', 'learndash-achievements' ); ?></strong>:
				<?php echo Database::get_user_points( $user->ID ); ?>
			</p>
			<p>
				<label for="learndash-achievements-extra-points">
					<strong><?php _e( 'Extra points', 'learndash-achievements' ); ?></strong>
				</label>
				<input id="learndash-achievements-extra-points" name="learndash_achievements_extra_points" type="text" value="<?php echo $extra_points; ?>">
			</p>
			<?php wp_nonce_field( 'learndash_achievements_add_extra_points', '_ldachievements_nonces' ); ?>
		</table>
		<?php
	}
}

new User_Profile();
