<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LearnDash Import CPT
 *
 * This file contains functions to handle import of the LearnDash CPT Course
 *
 * @package LearnDash
 * @subpackage LearnDash
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( ! class_exists( 'LearnDash_Import_Course' ) ) && ( class_exists( 'LearnDash_Import_Post' ) ) ) {
	class LearnDash_Import_Course extends LearnDash_Import_Post {
		private $version = '1.0';

		protected $dest_post_type   = 'sfwd-courses';
		protected $source_post_type = 'sfwd-courses';

		protected $dest_taxonomy = 'ld_course_category';

		public function __construct() {
			parent::__construct();
		}

		public function duplicate_post( $source_post_id = 0, $force_copy = false ) {
			$new_post = parent::duplicate_post( $source_post_id, $force_copy );

			return $new_post;
		}

		public function duplicate_post_tax_term( $source_term, $create_parents = false ) {
			$new_term = parent::duplicate_post_tax_term( $source_term, $create_parents );

			return $new_term;
		}

		// Prerequisite only support by Courses. (well and quizzes)
		// This function also enables course prerequisite
		public function set_post_prerequisite( $dest_post_id = 0, $prereq_post_id = 0 ) {
			if ( ( ! empty( $dest_post_id ) ) && ( ! empty( $prereq_post_id ) ) ) {
				$this->set_course_prerequisite_enabled( $dest_post_id, true );

				$prerequisite_posts   = learndash_get_course_prerequisite( $dest_post_id );
				$prerequisite_posts[] = $prereq_post_id;
				$this->set_course_prerequisite( $dest_post_id, $prerequisite_posts );
			}
		}

		public function set_course_prerequisite_enabled( $course_id, $enabled = true ) {
			if ( true === $enabled ) {
				$enabled = 'on';
			}

			if ( 'on' != $enabled ) {
				$enabled = '';
			}

			return learndash_update_setting( $course_id, 'course_prerequisite_enabled', $enabled );
		}

		public function set_course_prerequisite( $course_id = 0, $course_prerequisites = array() ) {
			if ( ! empty( $course_id ) ) {
				if ( ( ! empty( $course_prerequisites ) ) && ( is_array( $course_prerequisites ) ) ) {
					$course_prerequisites = array_unique( $course_prerequisites );
				}

				return learndash_update_setting( $course_id, 'course_prerequisite', (array) $course_prerequisites );
			}
		}

		public function enroll_user( $user_id = 0, $course_id = 0, $enroll_timestamp_gmt = 0 ) {
			if ( ( ! empty( $user_id ) ) && ( ! empty( $course_id ) ) ) {
				if ( empty( $enroll_timestamp_gmt ) ) {
					$enroll_timestamp_gmt = time();
				}

				$user_course_access_time = get_user_meta( $user_id, 'course_' . $course_id . '_access_from', true );
				if ( empty( $user_course_access_time ) ) {
					update_user_meta( $user_id, 'course_' . $course_id . '_access_from', $enroll_timestamp_gmt );
				}
			}
		}


		public function add_user_progress( $user_id = 0, $course_id = 0, $args = array(), $force = false ) {
			if ( ( ! empty( $user_id ) ) && ( ! empty( $course_id ) ) ) {
				$user_id   = intval( $user_id );
				$course_id = intval( $course_id );

				$user = get_user_by( 'ID', $user_id );
				if ( is_a( $user, 'WP_User' ) ) {
					$user_course_meta = get_user_meta( $user_id, '_sfwd-course_progress', true );
					if ( ( false === $user_course_meta ) || ( ! is_array( $user_course_meta ) ) ) {
						$user_course_meta = array();
					}

					$_changed = false;

					if ( ! isset( $user_course_meta[ $course_id ] ) ) {
						$user_course_meta[ $course_id ] = array();
						$_changed                       = true;
					}

					if ( ! empty( $args ) ) {
						foreach ( $args as $key => $val ) {

							if ( ( ! isset( $user_course_meta[ $course_id ][ $key ] ) ) || ( true === $force ) ) {
								$user_course_meta[ $course_id ][ $key ] = $val;
								$_changed                               = true;
							}
						}
					}

					if ( true === $_changed ) {
						update_user_meta( $user_id, '_sfwd-course_progress', $user_course_meta );
					}
				}
			}
		}

		// End of functions
	}
}
