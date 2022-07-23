<?php

namespace LearnDash\Achievements;

use LearnDash\Achievements\Achievement;
use LearnDash\Achievements\Database;

/**
 * Trigger class
 */
class Trigger {

	public static $allowed_post_types;

	public static function init() {
		self::$allowed_post_types = apply_filters(
			'learndash_achievements_allowed_post_types',
			array( 'post', 'page' )
		);

		// WordPress hooks.
		// User registration.
		add_action( 'user_register', array( __CLASS__, 'user_register' ) );
		// User log in.
		add_action( 'wp_login', array( __CLASS__, 'user_login' ), 10, 2 );
		// User adds post.
		add_action( 'transition_post_status', array( __CLASS__, 'user_add_post' ), 10, 3 );
		// User adds comment.
		add_action( 'wp_insert_comment', array( __CLASS__, 'user_add_comment' ), 10, 2 );
		// User visits posts.
		add_action( 'wp', array( __CLASS__, 'user_visit_post' ) );
		// User's post visited.
		add_action( 'wp', array( __CLASS__, 'user_post_visited' ) );

		// LearnDash hooks.
		// User enrolls into a group.
		add_action( 'ld_added_group_access', array( __CLASS__, 'user_enroll_group' ), 10, 2 );
		// User enrolls into a course.
		add_action( 'learndash_update_course_access', array( __CLASS__, 'user_enroll_course' ), 10, 4 );
		// User completes course.
		add_action( 'learndash_before_course_completed', array( __CLASS__, 'user_complete_course' ), 15, 1 );
		// User completes lesson.
		add_action( 'learndash_lesson_completed', array( __CLASS__, 'user_complete_lesson' ) );
		// User completes topic.
		add_action( 'learndash_topic_completed', array( __CLASS__, 'user_complete_topic' ), 10, 1 );
		// User completes quiz.
		add_action( 'learndash_quiz_completed', array( __CLASS__, 'user_complete_quiz' ), 10, 2 );
		// User passes quiz.
		add_action( 'learndash_quiz_completed', array( __CLASS__, 'user_pass_quiz' ), 10, 2 );
		// User fails quiz.
		add_action( 'learndash_quiz_completed', array( __CLASS__, 'user_fail_quiz' ), 10, 2 );
		// Quiz above a certain score.
		add_action( 'learndash_quiz_completed', array( __CLASS__, 'quiz_score_above' ), 10, 2 );
		// User uploads assignment.
		add_action( 'learndash_assignment_uploaded', array( __CLASS__, 'user_upload_assignment' ), 10, 2 );
		// User's assignment approved.
		add_action( 'learndash_assignment_approved', array( __CLASS__, 'user_assignment_approved' ), 10, 1 );
		// User's essay has been graded.
		add_action( 'learndash_essay_all_quiz_data_updated', array( __CLASS__, 'user_essay_graded' ), 10, 4 );
	}

	/*******************************
	 * ********* WordPress **********
	 *******************************/

	/**
	 * Give achievements when user registers
	 *
	 * @param int $user_id    User ID.
	 */
	public static function user_register( $user_id ) {
		Achievement::create_new( 'register', $user_id );
	}

	/**
	 * Give achievements when user logs in
	 *
	 * @param string $user_login Username.
	 * @param object $user       WP_User object.
	 */
	public static function user_login( $user_login, $user ) {
		Achievement::create_new( 'log_in', $user->ID );
	}

	/**
	 * Give achievements when user enrolls add a post
	 *
	 * @param int    $post_id    Post ID.
	 * @param object $post       WP Post object.
	 * @param bool   $update     True if it's update process, false otherwise.
	 */
	public static function user_add_post( $new_status, $old_status, $post ) {
		if ( 'publish' != $new_status || 'publish' == $old_status ) {
			return;
		}

		if ( in_array( $post->post_type, self::$allowed_post_types ) ) {
			Achievement::create_new( 'add_post', $post->post_author );
		}
	}

	/**
	 * Give achievements when user enrolls into a course
	 *
	 * @param int    $comment_id Comment ID.
	 * @param object $comment    WP Comment object.
	 */
	public static function user_add_comment( $comment_id, $comment ) {
		if ( $comment->user_id > 0 ) {
			Achievement::create_new( 'add_comment', $comment->user_id );
		}
	}

	/**
	 * Give achievements when user visits post
	 */
	public static function user_visit_post() {
		if ( is_admin() || ! is_single() ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( $user_id > 0 ) {

			$post_type = get_post_type();

			if ( in_array( $post_type, self::$allowed_post_types ) ) {
				$post_id = get_the_ID();
				Achievement::create_new( 'visit_post', $user_id, $post_id );
			}
		}
	}

	/**
	 * Give achievements when user's post get visited
	 */
	public static function user_post_visited() {
		if ( is_admin() || ! is_single() ) {
			return;
		}

		$post_type = get_post_type();

		if ( in_array( $post_type, self::$allowed_post_types ) ) {

			$post = get_post();

			Achievement::create_new( 'post_visited', $post->post_author, $post->ID );
		}
	}

	/*******************************
	 * ********* LearnDash **********
	 *******************************/
	/**
	 * Give achievements when user enrolls into a group
	 *
	 * @param int $user_id    User ID.
	 * @param int $group_id   Group ID.
	 */
	public static function user_enroll_group( $user_id, $group_id ) {
		Achievement::create_new( 'enroll_group', $user_id, $group_id, $group_id );
	}

	/**
	 * Give achievements when user enrolls into a course
	 *
	 * @param int    $user_id    User ID.
	 * @param int    $course_id  Data of the quiz taken.
	 * @param string $user       Course access list.
	 * @param bool   $remove     True if remove user.
	 */
	public static function user_enroll_course( $user_id, $course_id, $access_list, $remove ) {
		if ( true === $remove ) {
			return;
		}

		Achievement::create_new( 'enroll_course', $user_id, $course_id, $group_id = null, $course_id, $lesson_id = null, $topic_id = null, $quiz_id = null );
	}

	/**
	 * Give achievements when user completes a course
	 *
	 * @param array $data Course data with keys:
	 *                    'user' (user object),
	 *                    'course' (post object),
	 *                    'progress' (array).
	 */
	public static function user_complete_course( $data ) {
		Achievement::create_new( 'complete_course', $data['user']->ID, $data['course']->ID, $group_id = null, $data['course']->ID, $lesson_id = null, $topic_id = null, $quiz_id = null );
	}

	/**
	 * Process notification when user completes a lesson
	 *
	 * @param array $data Lesson data with array keys:
	 *                    'user' (WP_User object),
	 *                    'course' (post object),
	 *                    'lesson' (post object),
	 *                    'progress' (array).
	 */
	public static function user_complete_lesson( $data ) {
		Achievement::create_new( 'complete_lesson', $data['user']->ID, $data['lesson']->ID, $group_id = null, $data['course']->ID, $data['lesson']->ID, $topic_id = null, $quiz_id = null );
	}

	/**
	 * Give achievement when user completes a topic
	 *
	 * @param array $data Topic data with array keys:
	 *                    'user' (WP_User object),
	 *                    'course' (WP_Post object),
	 *                    'lesson' (WP_Post object),
	 *                    'topic' (WP_Post object),
	 *                    'progress' (array).
	 */
	public static function user_complete_topic( $data ) {
		Achievement::create_new( 'complete_topic', $data['user']->ID, $data['topic']->ID, $group_id = null, $course_id = $data['course']->ID, $lesson_id = $data['lesson']->ID, $topic_id = $data['topic']->ID, $quiz_id = null );
	}

	/**
	 * Give achievement when user completes a quiz
	 *
	 * @param array  $data Data of the quiz taken.
	 * @param object $user Current user WP object who take the quiz.
	 */
	public static function user_complete_quiz( $data, $user ) {
		$quiz = is_object( $data['quiz'] ) ? $data['quiz']->ID : $data['quiz'];
		Achievement::create_new( 'complete_quiz', $user->ID, $quiz, $group_id = null, $course_id = $data['course']->ID, $lesson_id = null, $topic_id = null, $quiz );
	}

	/**
	 * Give achievement when user passes a quiz
	 *
	 * @param array  $data Data of the quiz taken.
	 * @param object $user Current user WP object who take the quiz.
	 */
	public static function user_pass_quiz( $data, $user ) {
		if ( $data['has_graded'] ) {
			foreach ( $data['graded'] as $id => $essay ) {
				if ( $essay['status'] == 'not_graded' ) {
					return;
				}
			}
		}

		$quiz = is_object( $data['quiz'] ) ? $data['quiz']->ID : $data['quiz'];

		// If user passes the quiz
		if ( $data['pass'] == 1 ) {
			Achievement::create_new( 'pass_quiz', $user->ID, $quiz, $group_id = null, $course_id = $data['course']->ID, $lesson_id = null, $topic_id = null, $quiz );
		}
	}

	/**
	 * @param $data
	 * @param $user
	 */
	public static function quiz_score_above( $data, $user ) {
		Achievement::$temp_data = $data;
		$quiz                   = is_object( $data['quiz'] ) ? $data['quiz']->ID : $data['quiz'];
		Achievement::create_new( 'quiz_score_above', $user->ID, $quiz, $group_id = null, $course_id = $data['course']->ID, $lesson_id = null, $topic_id = null, $quiz );
	}

	/**
	 * Give achievement when user fail a quiz
	 *
	 * @param array  $data Data of the quiz taken.
	 * @param object $user Current user WP object who take the quiz.
	 */
	public static function user_fail_quiz( $data, $user ) {
		if ( $data['has_graded'] ) {
			foreach ( $data['graded'] as $id => $essay ) {
				if ( $essay['status'] == 'not_graded' ) {
					return;
				}
			}
		}
		$quiz = is_object( $data['quiz'] ) ? $data['quiz']->ID : $data['quiz'];
		// If user passes the quiz
		if ( $data['pass'] == 0 ) {
			Achievement::create_new( 'fail_quiz', $user->ID, $quiz, $group_id = null, $course_id = $data['course']->ID, $lesson_id = null, $topic_id = null, $quiz );
		}
	}

	/**
	 * Give achievement when user upload an assignment
	 *
	 * @param int   $assignment_id      ID of assignment post object.
	 * @param array $assignment_meta    Meta data of the assignment.
	 */
	public static function user_upload_assignment( $assignment_id, $assignment_meta ) {
		Achievement::create_new( 'upload_assignment', $assignment_meta['user_id'] );
	}

	/**
	 * Give achievement when admin approves an assignment
	 *
	 * @param int $assignment_id ID of assignment post object.
	 */
	public static function user_assignment_approved( $assignment_id ) {
		$user_id = get_post_meta( $assignment_id, 'user_id', true );

		Achievement::create_new( 'assignment_approved', $user_id );
	}

	/**
	 * Give achievement when essay question is graded
	 *
	 * @param  int    $quiz_id           Quiz ID.
	 * @param  int    $question_id       Question ID.
	 * @param  object $updated_scoring   Essay object.
	 * @param  object $essay             Submitted essay object.
	 */
	public static function user_essay_graded( $quiz_id, $question_id, $updated_scoring, $essay ) {
		if ( 'graded' != $essay->post_status ) {
			return;
		}

		Achievement::create_new( 'essay_graded', $essay->post_author );
	}
}

Trigger::init();
