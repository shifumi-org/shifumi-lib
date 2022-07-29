<?php
/**
 * Base class for payment gateways.
 *
 * @since 4.2.0
 *
 * @package LearnDash
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'LearnDash_Payment_Gateway_Integration' ) ) {
	/**
	 * Payment gateway class.
	 *
	 * @since 4.2.0
	 */
	class LearnDash_Payment_Gateway_Integration {
		/**
		 * Associates a course/group with a user.
		 *
		 * @since 4.2.0
		 *
		 * @param int|null $post_id Post ID.
		 * @param int      $user_id User ID.
		 *
		 * @return void
		 */
		protected function add_post_access( ?int $post_id, int $user_id ): void {
			$this->update_post_access( $post_id, $user_id );
		}

		/**
		 * Removes course/group access from a user.
		 *
		 * @since 4.2.0
		 *
		 * @param int|null $post_id Post ID.
		 * @param int      $user_id User ID.
		 *
		 * @return void
		 */
		protected function remove_post_access( ?int $post_id, int $user_id ): void {
			$this->update_post_access( $post_id, $user_id, true );
		}

		/**
		 * Updates course/group access for a user.
		 *
		 * @since 4.2.0
		 *
		 * @param int|null $post_id Post ID.
		 * @param int      $user_id User ID.
		 * @param bool     $remove  True to remove, false to add.
		 *
		 * @return void
		 */
		private function update_post_access( ?int $post_id, int $user_id, bool $remove = false ): void {
			if ( learndash_is_course_post( $post_id ) ) {
				ld_update_course_access( $user_id, $post_id, $remove );
			} elseif ( learndash_is_group_post( $post_id ) ) {
				ld_update_group_access( $user_id, $post_id, $remove );
			}
		}
	}
}
