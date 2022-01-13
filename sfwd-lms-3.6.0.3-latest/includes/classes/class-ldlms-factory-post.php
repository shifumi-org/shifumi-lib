<?php
/**
 * LearnDash Factory Post Class.
 * This is a factory class used to instansiate course and quiz related data.
 *
 * @since 2.5.0
 * @package LearnDash
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( ! class_exists( 'LDLMS_Factory_Post' ) ) && ( class_exists( 'LDLMS_Factory' ) ) ) {
	/**
	 * Class for LearnDash Factory Post.
	 *
	 * @since 2.5.0
	 * @uses LDLMS_Factory
	 */
	class LDLMS_Factory_Post extends LDLMS_Factory {

		/**
		 * Get a Course.
		 *
		 * @param integer $course Either course_id integer or WP_Post instance.
		 * @param boolean $reload To force reload of instance.
		 *
		 * @return new instance of LDLMS_Model_Course or null
		 */
		public static function course( $course = null, $reload = false ) {
			if ( ! empty( $course ) ) {
				$model = 'LDLMS_Model_Course';

				$course_id = 0;
				if ( ( is_a( $course, 'WP_Post' ) ) && ( learndash_get_post_type_slug( 'course' ) === $course->post_type ) ) {
					$course_id = absint( $course->ID );
				} else {
					$course_id = absint( $course );
				}

				if ( ! empty( $course_id ) ) {
					if ( true === $reload ) {
						self::remove_instance( $model, $course_id );
					}
					return self::add_instance( $model, $course_id, $course_id );
				}
			}

			return null;
		}

		/**
		 * Get a Lesson.
		 *
		 * @param integer $lesson Either lesson_id integer or WP_Post instance.
		 * @param boolean $reload To force reload of instance.
		 *
		 * @return new instance of LDLMS_Model_Lesson or null
		 */
		public static function lesson( $lesson = null, $reload = false ) {
			if ( ! empty( $lesson ) ) {
				$model = 'LDLMS_Model_Lesson';

				$lesson_id = 0;
				if ( ( is_a( $lesson, 'WP_Post' ) ) && ( learndash_get_post_type_slug( 'lesson' ) === $lesson->post_type ) ) {
					$lesson_id = absint( $lesson->ID );
				} else {
					$lesson_id = absint( $lesson );
				}

				if ( ! empty( $lesson_id ) ) {
					if ( true === $reload ) {
						self::remove_instance( $model, $lesson_id );
					}
					return self::add_instance( $model, $lesson_id, $lesson_id );
				}
			}

			return null;
		}

		/**
		 * Get Course.
		 *
		 * @param mixed $course Either course_id integer or WP_Post instance.
		 */
		public static function get_course( $course ) {}

		/**
		 * Get Course Lessons.
		 *
		 * @param mixed $course Either course_id integer or WP_Post instance.
		 * @param mixed $lesson Either lesson_id integer or WP_Post instance.
		 *
		 * @return new instance of LDLMS_Model_Course or null
		 */
		public static function get_course_lessons( $course = null, $lesson = null ) {
			if ( ! empty( $course ) ) {
				$course = self::get_course( $course );
				if ( $course ) {
					$lesson_id = 0;

					if ( ( is_a( $lesson, 'WP_Post' ) ) && ( learndash_get_post_type_slug( 'lesson' ) === $lesson->post_type ) ) {
						$lesson_id = absint( $lesson->ID );
					} else {
						$lesson_id = absint( $lesson );
					}

					$course_lesson = $course->get_lesson( $lesson_id );

					return $course_lesson;
				}
			}

			return null;
		}

		/**
		 * Get a Quiz Questions.
		 *
		 * @param mixed   $quiz Either quiz_id integer or WP_Post instance.
		 * @param boolean $reload To force reload of instance.
		 *
		 * @return new instance of LDLMS_Quiz_Questions or null
		 */
		public static function quiz_questions( $quiz = null, $reload = false ) {
			if ( ! empty( $quiz ) ) {
				$model = 'LDLMS_Quiz_Questions';

				$quiz_id = 0;

				if ( ( is_a( $quiz, 'WP_Post' ) ) && ( learndash_get_post_type_slug( 'quiz' ) === $quiz->post_type ) ) {
					$quiz_id = absint( $quiz->ID );
				} else {
					$quiz_id = absint( $quiz );
				}

				if ( ! empty( $quiz_id ) ) {
					if ( true === $reload ) {
						self::remove_instance( $model, $quiz_id );
					}
					return self::add_instance( $model, $quiz_id, $quiz_id );
				}
			}

			return null;
		}

		/**
		 * Get a Course Steps.
		 *
		 * @param mixed   $course Either course_id integer or WP_Post instance.
		 * @param boolean $reload To force reload of instance.
		 *
		 * @return new instance of LDLMS_Course_Steps or null
		 */
		public static function course_steps( $course = null, $reload = false ) {
			if ( ! empty( $course ) ) {
				$model = 'LDLMS_Course_Steps';

				$course_id = 0;

				if ( ( is_a( $course, 'WP_Post' ) ) && ( learndash_get_post_type_slug( 'course' ) === $course->post_type ) ) {
					$course_id = absint( $course->ID );
				} else {
					$course_id = absint( $course );
				}

				if ( ! empty( $course_id ) ) {
					if ( true === $reload ) {
						self::remove_instance( $model, $course_id );
					}
					return self::add_instance( $model, $course_id, $course_id );
				}
			}

			return null;
		}
	}
}
