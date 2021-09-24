<?php
/**
 * Class to extend LDLMS_Model_Post to LDLMS_Model_Group.
 *
 * @since 3.4.0
 * @package LearnDash\Group
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( class_exists( 'LDLMS_Model_Post' ) ) && ( ! class_exists( 'LDLMS_Model_Group' ) ) ) {
	/**
	 * Class for LearnDash Model Group.
	 *
	 * @since 3.4.0
	 * @uses LDLMS_Model_Post
	 */
	class LDLMS_Model_Group extends LDLMS_Model_Post {

		/**
		 * Initialize post.
		 *
		 * @since 3.4.0
		 *
		 * @param int $post_id Group Post ID to load.
		 *
		 * @return bool True if post was loaded. False otherwise.
		 */
		public function __construct( $group_id = 0 ) {
			$this->post_type = learndash_get_post_type_slug( 'group' );

			$this->load( $group_id );
		}

		// End of functions.
	}
}
