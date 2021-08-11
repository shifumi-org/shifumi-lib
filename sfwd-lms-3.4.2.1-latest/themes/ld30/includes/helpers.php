<?php
/**
 * LearnDash LD30 Helper functions.
 *
 * @since 3.0.0
 *
 * @package LearnDash\Templates\LD30
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$learndash_30_defs = array(
	'LD_30_TEMPLATE_DIR' => LEARNDASH_LMS_PLUGIN_DIR . 'themes/ld30/templates/',
	'LD_30_VER'          => '1.0',
);

foreach ( $learndash_30_defs as $learndash_30_definition => $learndash_30_value ) {
	if ( ! defined( $learndash_30_definition ) ) {
		/**
		 * @ignore
		 */
		define( $learndash_30_definition, $learndash_30_value ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.VariableConstantNameFound -- Used inside foreach loop
	}
}

require 'shortcodes.php';
require 'login-register-functions.php';

/**
 * Gets the course price.
 *
 * Return an array of price type, amount and cycle.
 *
 * @since 3.0.0
 *
 * @global WP_Post $post Global post object.
 *
 * @param int|object|null $course Course `WP_Post` object or post ID. Default to global $post.
 *
 * @return array Course price details.
 */
function learndash_get_course_price( $course = null ) {

	if ( null === $course ) {
		global $post;
		$course = $post;
	}

	if ( is_numeric( $course ) ) {
		$course = get_post( $course );
	}

	// Get the course price
	$meta = get_post_meta( $course->ID, '_sfwd-courses', true );

	$course_price = array(
		'type'  => ! empty( $meta['sfwd-courses_course_price_type'] ) ? $meta['sfwd-courses_course_price_type'] : LEARNDASH_DEFAULT_COURSE_PRICE_TYPE,
		'price' => ! empty( $meta['sfwd-courses_course_price'] ) ? $meta['sfwd-courses_course_price'] : '',
	);

	if ( 'subscribe' === $course_price['type'] ) {

		$frequency = get_post_meta( $course->ID, 'course_price_billing_t3', true );
		$interval  = intval( get_post_meta( $course->ID, 'course_price_billing_p3', true ) );

		$label = '';

		switch ( $frequency ) {
			case ( 'D' ):
				$label = _n( 'day', 'days', $interval, 'learndash' );
				break;
			case ( 'W' ):
				$label = _n( 'week', 'weeks', $interval, 'learndash' );
				break;
			case ( 'M' ):
				$label = _n( 'month', 'months', $interval, 'learndash' );
				break;
			case ( 'Y' ):
				$label = _n( 'year', 'years', $interval, 'learndash' );
				break;
		}

		$course_price['frequency'] = $label;
		$course_price['interval']  = $interval;

	}

	/**
	 * Filters price details for a course.
	 *
	 * @since 3.0.0
	 *
	 * @param array $course_price Course price details.
	 */
	return apply_filters( 'learndash_get_course_price', $course_price );

}

/**
 * Prints breadcrumbs output.
 *
 * Sames as learndash_get_breadcrumbs only it actually outputs escaped markup.
 *
 * @since 3.0.0
 *
 * @global WP_Post $post Global post object.
 *
 * @param int|WP_Post|null $post `WP_Post` object. Default to global $post.
 */
function learndash_the_breadcrumbs( $post = null ) {

	if ( null === $post ) {
		global $post;
	}

	if ( is_numeric( $post ) ) {
		$post = get_post( $post );
	}

	echo wp_kses_post( learndash_get_breadcrumbs( $post ) );

}

/**
 * Gets the breadcrumbs hierarchy.
 *
 * Builds an array of breadcrumbs for the current LearnDash post.
 *
 * @since 3.0.0
 *
 * @global WP_Post $post Global post object.
 *
 * @param int|WP_Post|null $post `WP_Post` object. Default to global $post.
 * @param array|false      $args Arguments used to generate breadcrumbs. Default is false.
 *
 * @return array The hierarchy of breadcrumbs.
 */
function learndash_get_breadcrumbs( $post = null, $args = false ) {

	if ( null === $post ) {
		global $post;
	}

	if ( is_numeric( $post ) ) {
		$post = get_post( $post );
	}

	if ( $args ) {
		extract( $args );
	}

	// Get the course ID of the current element
	$course_id = learndash_get_course_id( $post->ID );
	if ( empty( $course_id ) ) {
		return array();
	}

	$breadcrumbs = array(
		'course'  => array(
			'permalink' => learndash_get_step_permalink( $course_id ),
			'title'     => get_the_title( $course_id ),
		),
		'current' => array(
			'permalink' => learndash_get_step_permalink( $post->ID ),
			'title'     => get_the_title( $post->ID ),
		),
	);

	// If this is a topic or a quiz we might need a third hierarhcy
	switch ( get_post_type() ) {

		case 'sfwd-topic':
			$lesson_id             = learndash_course_get_single_parent_step( $course_id, $post->ID );
			$breadcrumbs['lesson'] = array(
				'permalink' => learndash_get_step_permalink( $lesson_id ),
				'title'     => get_the_title( $lesson_id ),
			);
			break;
		case 'sfwd-quiz':
			// A quiz can have a parent of a course, lesson or topic...
			$parent_ids = learndash_course_get_all_parent_step_ids( $course_id, $post->ID );
			if ( ! empty( $parent_ids ) ) {
				foreach ( $parent_ids as $parent_id ) {
					if ( get_post_type( $parent_id ) === learndash_get_post_type_slug( 'topic' ) ) {
						$key = 'topic';
					} elseif ( get_post_type( $parent_id ) === learndash_get_post_type_slug( 'lesson' ) ) {
						$key = 'lesson';

					} else {
						$key = '';
					}

					if ( ! empty( $key ) ) {
						$breadcrumbs[ $key ] = array(
							'permalink' => learndash_get_step_permalink( $parent_id ),
							'title'     => get_the_title( $parent_id ),
						);
					}
				}
			}

			break;
	}

	/**
	 * Filters Breadcrumbs for the LearnDash post.
	 *
	 * @since 3.0.0
	 *
	 * @param array $breadcrumbs Hierarchy of breadcrumbs.
	 */
	$breadcrumbs = apply_filters( 'learndash_breadcrumbs', $breadcrumbs );

	return $breadcrumbs;

}

/**
 * Gets the essays from a specific quiz attempt - DEPRECATED
 *
 * Look up all the essay responses from a particular quiz attempt
 *
 * @since 3.0.0
 *
 * @deprecated
 *
 * @param int|null $attempt_id Post ID.
 * @param int|null $user_id    User ID.
 *
 * @return array|boolean An array of essay post IDs.
 */
function learndash_get_essays_by_quiz_attempt( $attempt_id = null, $user_id = null ) {

	// Fail gracefully
	if ( null === $attempt_id ) {
		return false;
	}

	if ( null === $user_id ) {
		$cuser   = wp_get_current_user();
		$user_id = $cuser->ID;
	}

	$quiz_attempts = get_user_meta( $user_id, '_sfwd-quizzes', true );
	$essays        = array();

	if ( ! $quiz_attempts || empty( $quiz_attempts ) ) {
		return false;
	}

	foreach ( $quiz_attempts as $attempt ) {

		if ( $attempt['quiz'] != $attempt_id || ! isset( $attempt['graded'] ) ) {
			continue;
		}

		foreach ( $attempt['graded'] as $essay ) {
			$essays[] = $essay['post_id'];
		}
	}

	return $essays;

}

/**
 * Gets the essay details.
 *
 * Returns details about essay such as points details and status.
 *
 * @since 3.0.0
 *
 * @param int|null $post_id Post ID of the essay.
 *
 * @return array|false An array of essay details.
 */
function learndash_get_essay_details( $post_id = null ) {

	if ( null === $post_id ) {
		return false;
	}

	$essay = get_post( $post_id );

	if ( ! $essay || empty( $essay ) ) {
		return false;
	}

	$details = array(
		'points' => array(
			'awarded' => 0,
			'total'   => 0,
		),
		'status' => $essay->post_status,
	);

	$quiz_id     = get_post_meta( $post_id, 'quiz_id', true );
	$question_id = get_post_meta( $post_id, 'question_id', true );

	if ( ! empty( $quiz_id ) ) {
		$question_mapper = new WpProQuiz_Model_QuestionMapper();
		$question        = $question_mapper->fetchById( intval( $question_id ), null );
		if ( $question instanceof WpProQuiz_Model_Question ) {

			$submitted_essay_data = learndash_get_submitted_essay_data( $quiz_id, $question_id, $essay );

			$details['points']['total'] = $question->getPoints();

			if ( isset( $submitted_essay_data['points_awarded'] ) ) {
				$details['points']['awarded'] = intval( $submitted_essay_data['points_awarded'] );
			}
		}
	}

	return $details;

}

/**
 * Gets the current lesson progress.
 *
 * Returns stats about a user's current progress within a lesson.
 *
 * @since 3.0.0
 *
 * @param array|null $topics An array of the topic of the lessons, contextualized for the user's progress.
 *
 * @return array An array of stats including percentage, completed and total
 */
function learndash_get_lesson_progress( $topics = null ) {

	/**
	 * Filters default values for lesson progress.
	 *
	 * @since 3.0.0
	 *
	 * @param array $lesson_progress_defaults Default values for lesson progress.
	 */
	$progress = apply_filters(
		'learndash_get_lesson_progress_defaults',
		array(
			'percentage' => 0,
			'completed'  => 0,
			'total'      => 0,
		)
	);

	// Fail gracefully, return zero's
	if ( null === $topics || empty( $topics ) ) {
		return $progress;
	}

	foreach ( $topics as $key => $topic ) {

		$progress['total']++;

		if ( ! empty( $topic->completed ) ) {
			$progress['completed']++;
		}
	}

	if ( 0 === ! $progress['completed'] ) {
		$progress['percentage'] = floor( $progress['completed'] / $progress['total'] * 100 );
	}

	/**
	 * Filters LearnDash lesson progress.
	 *
	 * @since 3.0.0
	 *
	 * @param array $progress An Associative array of lesson progress with keys total, completed and percentage.
	 * @param array $topics   An array of the topics of the lessons.
	 */
	return apply_filters( 'learndash_get_lesson_progress', $progress, $topics );

}

/**
 * Checks if any LearnDash content type is complete.
 *
 * Works on lessons or topics, single function for simpler logic in the templates.
 *
 * @since 3.0.0
 *
 * @global WP_Post $post Global post object.
 *
 * @param int|WP_Post|null $post      `WP_Post` object. Default to global $post.
 * @param int|null         $user_id   The user to check against.
 * @param int|null         $course_id The course to check against (required for reusable content).
 *
 * @return boolean Returns true if the item is complete otherwise false.
 */
function learndash_is_item_complete( $post = null, $user_id = null, $course_id = null ) {

	$complete = false;

	if ( null === $post ) {
		global $post;
	}

	if ( is_numeric( $post ) ) {
		$post = get_post( $post );
	}

	if ( null === $user_id ) {
		$user    = wp_get_current_user();
		$user_id = $user->ID;
	}

	if ( null === $course_id ) {
		$course_id = learndash_get_course_id( $post->ID );
	}

	switch ( get_post_type( $post ) ) {
		case ( 'sfwd-lessons' ):
			$complete = learndash_is_lesson_complete( $user_id, $post->ID, $course_id );
			break;
		case ( 'sfwd-topic' ):
			$complete = learndash_is_topic_complete( $user_id, $post->ID, $course_id );
			break;
		case ( 'sfwd-quiz' ):
			break;

	}
	/**
	 * Filters whether the LearnDash content type is complete or not.
	 *
	 * @since 3.0.0
	 *
	 * @param boolean $complete  Whether any LearnDash content is complete or not.
	 * @param int     $user_id   User ID.
	 * @param int     $post_id   Post ID.
	 * @param int     $course_id Course ID.
	 */
	return apply_filters( 'learndash_is_item_complete', $complete, $user_id, $post->ID, $course_id );

}

/**
 * Gets a label for the content type by post type.
 *
 * Universal function for simpler template logic and reusable templates
 *
 * @since 3.0.0
 *
 * @param string $post_type The post type slug to check.
 * @param array  $args      An array of arguments used to get the content label.
 *
 * @return string The label for the content type based on user settings
 */
function learndash_get_content_label( $post_type = null, $args = null ) {

	if ( $args ) {
		extract( $args );
	}

	$post_type = ( null === $post_type ? get_post_type() : $post_type );
	$label     = '';

	switch ( $post_type ) {
		case ( 'sfwd-courses' ):
			$label = LearnDash_Custom_Label::get_label( 'course' );
			break;
		case ( 'sfwd-lessons' ):
			if ( isset( $parent ) ) {
				$label = LearnDash_Custom_Label::get_label( 'course' );
			} else {
				$label = LearnDash_Custom_Label::get_label( 'lesson' );
			}
			break;
		case ( 'sfwd-topic' ):
			if ( isset( $parent ) ) {
				$label = LearnDash_Custom_Label::get_label( 'lesson' );
			} else {
				$label = LearnDash_Custom_Label::get_label( 'topic' );
			}
			break;
	}

	/**
	 * Filters label for the content type by post type. Used to override label settings set by the user.
	 *
	 * @since 3.0.0
	 *
	 * @param string $label     Label for the content type
	 * @param string $post_type Post type
	 */
	return apply_filters( 'learndash_get_content_label', $label, $post_type );

}

/**
 * Gets the assignment progress.
 *
 * Returns details of assignment progress.
 *
 * @since 3.0.0
 *
 * @param array $assignments An array of assignment `WP_Post` objects.
 *
 * @return array An Associative array of assignment statistics with keys total, complete.
 */
function learndash_get_assignment_progress( $assignments = null ) {

	$stats = array(
		'total'    => 0,
		'complete' => 0,
	);

	if ( null === $assignments || empty( $assignments ) ) {

		/**
		 * Filters progress of an assignment.
		 *
		 * @since 3.0.0
		 *
		 * @param array $stats An Associative array of assignment statistics with keys total, complete.
		 */
		return apply_filters( 'learndash_get_assignment_progress', $stats );
	}

	foreach ( $assignments as $assignment ) {

		$stats['total']++;

		if ( learndash_is_assignment_approved_by_meta( $assignment->ID ) ) {
			$stats['complete']++;

		}
	}

	/** This filter is documented in themes/ld30/includes/helpers.php */
	return apply_filters( 'learndash_get_assignment_progress', $stats );

}

/**
 * Gets the Lesson Progress.
 *
 * Return stats about the user's current progress within a lesson.
 *
 * @since 3.0.0
 *
 * @global WP_Post $post Global post object.
 *
 * @param int|WP_Post $post      Lesson `WP_Post` object or post ID. Default to global $post.
 * @param int         $course_id The course ID of the lesson.
 *
 * @return array An array of total steps, completed steps and percentage complete.
 */
function learndash_lesson_progress( $post = null, $course_id = null ) {

	if ( null === $post ) {
		global $post;
	}

	if ( is_numeric( $post ) ) {
		$post = get_post( $post );
	}

	if ( null === $course_id ) {
		$course_id = learndash_get_course_id( $post->ID );
	}

	if ( 'sfwd-lessons' === get_post_type( $post->ID ) ) {
		$lesson_id = $post->ID;
	} else {
		$lesson_id = learndash_course_get_single_parent_step( $course_id, $post->ID );
	}

	$topics = learndash_topic_dots( $lesson_id, false, 'array', null, $course_id );

	if ( ! $topics || empty( $topics ) ) {
		return false;
	}

	$progress = array(
		'total'      => 0,
		'completed'  => 0,
		'percentage' => 0,
	);

	foreach ( $topics as $key => $topic ) {

		$progress['total']++;

		if ( isset( $topic->completed ) && $topic->completed ) {
			$progress['completed']++;
		}
	}

	/**
	 * Note: Since we're not counting quizzes at all in the lessons or topics we don't need to count quizzes
	 *
	 * @var [type]
	 */

	if ( 0 !== absint( $progress['completed'] ) ) {
		$progress['percentage'] = floor( $progress['completed'] / $progress['total'] * 100 );
	}

	/**
	 * Filters stats about the user's current progress within a lesson
	 *
	 * @since 3.0.0
	 *
	 * @param array      $progress lesson progress details.
	 * @param int|object $post     Post ID or post object.
	 */
	return apply_filters( 'learndash_lesson_progress', $progress, $post );

}

/**
 * Gets the count of the number of topics and quizzes for a lesson.
 *
 * Counts the number of topics, topic quizzes and lesson quizzes, and returns them as an array.
 *
 * @since 3.0.0
 *
 * @param int|WP_Post $lesson    Lesson `WP_Post` object.
 * @param int         $course_id The course ID of the lesson.
 *
 * @return array Count of topics and quizzes.
 */
function learndash_get_lesson_content_count( $lesson, $course_id ) {

	$count = array(
		'topics'  => 0,
		'quizzes' => 0,
	);

	$quizzes       = learndash_get_lesson_quiz_list( $lesson['post']->ID, get_current_user_id(), $course_id );
	$lesson_topics = learndash_topic_dots( $lesson['post']->ID, false, 'array', null, $course_id );

	if ( ! empty( $quizzes ) ) {
		$count['quizzes'] += count( $quizzes );
	}

	if ( ! empty( $lesson_topics ) ) {

		foreach ( $lesson_topics as $topic ) {

			$count['topics']++;

			$quizzes = learndash_get_lesson_quiz_list( $topic, null, $course_id );

			if ( ! $quizzes || empty( $quizzes ) ) {
				continue;
			}

			$count['quizzes'] += count( $quizzes );

		}
	}

	return $count;

}

/**
 * Ouputs lesson row CSS class.
 *
 * Filterable string of class names populated based on lesson status and attributes.
 *
 * @since 3.0.0
 *
 * @param int|WP_Post $lesson     Lesson `WP_Post` object or post ID. Default to global $post.
 * @param int         $has_access Whether the lesson is accessible or not.
 *
 * @return string|void Lesson row CSS class names.
 */
function learndash_lesson_row_class( $lesson = null, $has_access = false ) {

	if ( null === $lesson ) {
		return;
	}

	/**
	 * Base classes
	 *
	 * ld-item-list-item   -- for styling
	 * ld-item-lesson-item -- more specific
	 * ld-lesson-item-{post_id}
	 * is_sample (if sample)
	 *
	 * @var string $lesson_class
	 */
	$lesson_class = 'ld-item-list-item ld-expandable ld-item-lesson-item ld-lesson-item-' . $lesson['post']->ID . ' ' . $lesson['sample'];

	$bypass_course_limits_admin_users = learndash_can_user_bypass( get_current_user_id(), 'learndash_course_lesson_not_available' );
	if ( true !== $bypass_course_limits_admin_users ) {
		$lesson_class .= ( ! empty( $lesson['lesson_access_from'] ) || ! $has_access ? ' learndash-not-available' : '' );
	}
	// Complete or not complete
	$lesson_class .= ' ' . ( 'completed' === $lesson['status'] ? 'learndash-complete' : 'learndash-incomplete' );

	// If expandable or not
	if ( ! empty( $topics ) ) {
		$lesson_class .= ' ld-expandable';
	}

	if ( ( isset( $is_current_lesson ) && $is_current_lesson ) || ( isset( $_GET['widget_instance']['widget_instance']['current_lesson_id'] ) && absint( $_GET['widget_instance']['widget_instance']['current_lesson_id'] ) === absint( $lesson['post']->ID ) ) ) {  // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Data is only used for conditional and not further processed.
		$lesson_class .= ' ld-current-lesson';
	}

	// Filter
	/**
	 * Filters lesson row CSS class names.
	 *
	 * @since 3.0.0
	 *
	 * @param string $lesson_class Lesson row CSS class names.
	 * @param object $lesson       The lesson post object to evaluate
	 */
	echo esc_attr( apply_filters( 'learndash-lesson-row-class', $lesson_class, $lesson ) );

}

/**
 * Outputs the quiz row CSS classes.
 *
 * @since 3.0.0
 *
 * @param array  $quiz    The quiz details array.
 * @param string $context The context where quiz is shown.
 *
 * @return string Quiz row CSS class.
 */
function learndash_quiz_row_classes( $quiz = null, $context = 'course' ) {

	$classes = array(
		'wrapper' => '',
		'anchor'  => '',
		'preview' => '',
	);

	if ( 'course' === $context ) {
		$classes['wrapper'] .= 'ld-item-list-item ld-item-list-item-quiz';
		$classes['preview'] .= 'ld-item-list-item-preview';
		$classes['anchor']  .= 'ld-item-name ld-primary-color-hover';
	} else {
		$classes['wrapper'] .= 'ld-table-list-item';
		$classes['preview'] .= 'ld-table-list-item-quiz';
		$classes['anchor']  .= 'ld-table-list-item-preview ld-topic-row ld-primary-color-hover';
	}

	$classes['wrapper'] .= ' ' . $quiz['sample'] . ' ' . ( 'completed' === $quiz['status'] ? 'learndash-complete' : 'learndash-incomplete' );

	/**
	 * Filters quiz row CSS classes.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $classes Array of CSS classes with keys wrapper, preview, and anchor.
	 * @param array  $quiz    The quiz array
	 * @param string $context The context where the quiz is being shown.
	 */
	return apply_filters( 'learndash_quiz_row_classes', $classes, $quiz, $context );

}

/**
 * Gets the Lesson attributes.
 *
 * Populates an array of attributes about a lesson, if it's a sample or if it isn't currently available
 *
 * @since 3.0.0
 *
 * @param array $lesson Lesson details array.
 *
 * @return array Attributes including label, icon and class name.
 */
function learndash_get_lesson_attributes( $lesson = null ) {

	$attributes = array();

	// Fail silently
	if ( null === $lesson ) {
		return $attributes;
	}

	if ( 'is_sample' === $lesson['sample'] ) {
		$attributes[] = array(
			// translators: placeholder: Lesson.
			'label' => sprintf( esc_html_x( 'Sample %s', 'placeholder: Lesson', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ),
			'icon'  => 'ld-icon-unlocked',
			'class' => 'ld-status-unlocked ld-primary-color',
		);
	}

	$bypass_course_limits_admin_users = learndash_can_user_bypass( get_current_user_id(), 'learndash_course_lesson_not_available' );
	if ( true !== $bypass_course_limits_admin_users ) {

		if ( ! empty( $lesson['lesson_access_from'] ) ) {
			$attributes[] = array(
				'label' => sprintf(
					// translators: placeholder: Date when lesson will be available.
					esc_html_x( 'Available on %s', 'placeholder: Date when lesson will be available', 'learndash' ),
					learndash_adjust_date_time_display( $lesson['lesson_access_from'] )
				),
				'class' => 'ld-status-waiting ld-tertiary-background',
				'icon'  => 'ld-icon-calendar',
			);
		}
	}

	/**
	 * Filters attributes of a lesson. Used to modify details about a lesson like label, icon and class name
	 *
	 * @since 3.0.0
	 *
	 * @param array  $attributes Array of lesson attributes.
	 * @param object $lesson     The lesson post object
	 */
	return apply_filters( 'learndash_lesson_attributes', $attributes, $lesson );

}

/**
 * Gets the template Part.
 *
 * Function to facilitate including sub-templates.
 *
 * @since 3.0.0
 *
 * @param string  $filepath The path to the template file to include.
 * @param array   $args    Any variables to pass along to the template.
 * @param boolean $echo    Whether to print or return the template output.
 *
 * @return string|void If the echo is false, string with markup returned.
 */
function learndash_get_template_part( $filepath, $args = null, $echo = false ) {
	// Keep this in the logic from LD core to allow the same overrides.
	$filepath = SFWD_LMS::get_template( $filepath, $args, null, true );

	if ( ( ! empty( $filepath ) ) && ( file_exists( $filepath ) ) ) {

		ob_start();
		extract( $args );
		include $filepath;
		$output = ob_get_clean();

		if ( $echo ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Outputting HTML from templates
		} else {
			return $output;
		}
	}
}

/**
 * Gets the Learndash content wrapper CSS class.
 *
 * Filterable function to add a class to all LearnDash content, allows conditional adding of additional classes
 *
 * @since 3.0.0
 *
 * @global WP_Post $post Global post object.
 *
 * @param int|WP_Post $post `WP_Post` object or post ID. Default to global $post.
 *
 * @return string Wrapper CSS class.
 */
function learndash_get_wrapper_class( $post = null ) {

	if ( null === $post ) {
		global $post;
	}

	if ( is_numeric( $post ) ) {
		$post = get_post( $post );
	}

	/**
	 * Filters LearnDash content wrapper class.
	 *
	 * @since 3.0.0
	 *
	 * @param string     $wrapper_class Wrapper class.
	 * @param int|object $post          Post ID or post object.
	 */
	return apply_filters( 'learndash_wrapper_class', 'learndash-wrapper', $post );

}

/**
 * Outputs the Learndash content wrapper CSS class.
 *
 * Same as learndash_get_wrapper_class only outputs it.
 *
 * @since 3.0.0
 *
 * @param  int|WP_Post $post `WP_Post` object or post ID. Default to global $post.
 */
function learndash_the_wrapper_class( $post = null ) {

	if ( null === $post ) {
		global $post;
	}

	if ( is_numeric( $post ) ) {
		$post = get_post( $post );
	}

	echo esc_attr( learndash_get_wrapper_class( $post ) );

}

/**
 * Gets or prints the LearnDash status icon.
 *
 * Output the status icon for a course element. Simplifies template logic.
 *
 * @since 3.0.0
 *
 * @param string  $status    The current item's status, either not-completed or completed (based on current logic and labeling).
 * @param string  $post_type What post type we're checking against so this can be used for courses, lessons, topics, and quizzes.
 * @param array   $args      The arguments to get the status icon.
 * @param boolean $echo      True to print the output and false to return the output.
 *
 * @return void|string Returns the status icon markup if echo is false.
 */
function learndash_status_icon( $status = 'not-completed', $post_type = null, $args = null, $echo = false ) {

	$class = 'ld-status-icon ';

	$markup = '';

	if ( 'sfwd-quiz' !== $post_type ) {

		switch ( $status ) {
			case ( 'not-completed' ):
				$class .= 'ld-status-incomplete';
				$markup = '<div class="' . $class . '"></div>';
				break;
			case ( 'completed' ):
				$class .= 'ld-status-complete ld-secondary-background';
				$markup = '<div class="' . $class . '"><span class="ld-icon-checkmark ld-icon"></span></div>';
				break;
			case ( 'progress' ):
			case ( 'in-progress' ):
				$class .= 'ld-status-in-progress ld-secondary-in-progress-icon';
				$markup = '<div class="' . $class . '"></div>';
				break;
			case ( 'not-started' ):
			default:
				$class .= 'ld-status-incomplete';
				$markup = '<div class="' . $class . '"></div>';
				break;
		}
	} else {

		switch ( $status ) {
			case ( 'notcompleted' ):
			case ( 'failed' ):
				$class .= 'ld-quiz-incomplete';
				$markup = '<div class="' . $class . '"><span class="ld-icon ld-icon-quiz"></span></div>';
				break;
			case ( 'completed' ):
			case ( 'passed' ):
				$class .= 'ld-quiz-complete ld-secondary-color';
				$markup = '<div class="' . $class . '"><span class="ld-icon ld-icon-quiz"></span></div>';
				break;
			case ( 'pending' ):
				$class .= 'ld-quiz-pending';
				$markup = '<div class="' . $class . '"><span class="ld-icon ld-icon-quiz"></span></div>';
				break;
		}
	}

	/**
	 * Filters status icon markup for the course element.
	 *
	 * @since 3.0.0
	 *
	 * @param string $markup    Icon markup.
	 * @param string $status    The current item's status.
	 * @param string $post_type What post type we're checking against so this can be used for courses, lessons, topics, and quizzes.
 	* @param array   $args      The arguments to get the status icon.
 	* @param boolean $echo      True to print the output and false to return the output.
	 */
	$markup = apply_filters( 'learndash_status_icon', $markup, $status, $post_type, $args, $echo );

	if ( $echo ) {
		echo wp_kses_post( $markup );
	}

	return $markup;

}

/**
 * Gets or prints the LearnDash status bubble.
 *
 * Output the status bubble of an element. Simplifies template logic.
 *
 * @since 3.0.0
 *
 * @param string  $status  The current item's status, either incomplete or complete.
 * @param string  $context The current context the bubble is being output, used for color management.
 * @param boolean $echo    True to print the output and false to return the output.
 *
 * @return void|string Returns the status bubble markup if echo is false.
 */
function learndash_status_bubble( $status = 'incomplete', $context = null, $echo = true ) {

	$bubble = '';

	switch ( $status ) {
		case 'In Progress':
		case 'progress':
		case 'incomplete':
			$bubble = '<div class="ld-status ld-status-progress ld-primary-background">' . esc_html_x( 'In Progress', 'In Progress item status', 'learndash' ) . '</div>';
			break;

		case 'complete':
		case 'completed':
		case 'Completed':
			$bubble = '<div class="ld-status ld-status-complete ld-secondary-background">' . esc_html_x( 'Complete', 'In Progress item status', 'learndash' ) . '</div>';
			break;

		case 'graded':
			$bubble = '<div class="ld-status ld-status-complete ld-secondary-background">' . esc_html_x( 'Graded', 'In Progress item status', 'learndash' ) . '</div>';
			break;

		case 'not_graded':
			$bubble = '<div class="ld-status ld-status-progress ld-primary-background">' . esc_html_x( 'Not Graded', 'In Progress item status', 'learndash' ) . '</div>';
			break;

		case '':
		default:
			break;
	}

	/**
	 * Filters item status bubble markup.
	 *
	 * @since 3.0.0
	 *
	 * @param string $bubble Status bubble markup.
	 * @param string $status The current item status
	 */
	$bubble = apply_filters( 'learndash_status_bubble', $bubble, $status );

	if ( $echo ) {
		echo wp_kses_post( $bubble );
	} else {
		return $bubble;
	}

}

// This is just for testing icon sizing and scaling
// add_action( 'admin_footer', 'learndash_test_admin_icon' );
function learndash_test_admin_icon() { ?>

	<style type="text/css">
		#adminmenu #toplevel_page_learndash-lms div.wp-menu-image:before {
			background: url('<?php echo esc_url( LEARNDASH_LMS_PLUGIN_URL . '/themes/ld30/assets/iconfont/admin-icons/browser-checkmark.svg' ); ?>') center center no-repeat;
			content: '';
			opacity: 0.7;
		}
	</style>

	<?php
}

/**
 * Gets the course assignments.
 *
 * Returns `WP_query` object to get course assignments.
 *
 * @since 3.0.0
 *
 * @param int|null $course_id Course ID.
 * @param int|null $user_id   User ID.
 *
 * @return WP_Query|false Return `WP_Query` object if there are assignments in course otherwise false.
 */
function learndash_get_course_assignments( $course_id = null, $user_id = null ) {

	if ( null === $course_id ) {
		$course_id = get_the_ID();
	}

	if ( null === $user_id ) {
		$cuser   = wp_get_current_user();
		$user_id = $cuser->ID;
	}

	$args = array(
		'posts_per_page' => -1,
		'post_type'      => 'sfwd-assignment',
		'meta_query'     => array(
			'relation' => 'AND',
			array(
				'key'   => 'course_id',
				'value' => $course_id,
			),
			array(
				'key'   => 'user_id',
				'value' => $user_id,
			),
		),
	);

	$assignments = new WP_Query( $args );

	if ( ! $assignments->have_posts() ) {
		return false;
	}

	return $assignments;

}

add_action( 'wp_enqueue_scripts', 'learndash_30_remove_legacy_css' );
/**
 * Removes the legacy css.
 *
 * Fires on `wp_enqueue_scripts` hook.
 *
 * @since 3.1.4
 */
function learndash_30_remove_legacy_css() {

	$styles = array(
		'sfwd_front_css',
		'learndash_style',
		'learndash_quiz_front',
	);

	foreach ( $styles as $handle ) {
		wp_dequeue_style( $handle );
	}

}

/**
 * Gets the user statistics.
 *
 * @since 3.0.0
 *
 * @param int|null $user_id The ID of the user. Defaults to current logged in user.
 *
 * @return array An array of user statistics.
 */
function learndash_get_user_stats( $user_id = null ) {

	if ( null === $user_id ) {
		$cuser   = wp_get_current_user();
		$user_id = $cuser->ID;
	} else {
		$user_id = absint( $user_id );
	}

	$progress = get_user_meta( $user_id, '_sfwd-course_progress' );

	$stats = array(
		'courses'      => 0,
		'completed'    => 0,
		'points'       => learndash_get_user_course_points( $user_id ),
		'certificates' => learndash_get_certificate_count( $user_id ),
	);

	$courses = learndash_user_get_enrolled_courses( $user_id, array(), true );

	if ( $courses ) {

		$stats['courses'] = count( $courses );

		foreach ( $courses as $course_id ) {

			$progress = learndash_course_progress(
				array(
					'user_id'   => $user_id,
					'course_id' => $course_id,
					'array'     => true,
				)
			);

			if ( 100 === absint( $progress['percentage'] ) ) {
				$stats['completed']++;
			}
		}
	}

	/**
	 * Filters LearnDash user stats. Used to modify user details like courses, points, certificates.
	 *
	 * @since 3.0.0
	 *
	 * @param array $stats   User stats
	 * @param int   $user_id User ID.
	 */
	return apply_filters( 'learndash-get-user-stats', $stats, $user_id );

}

global $learndash_in_focus_mode;
$learndash_in_focus_mode = false;

add_filter( 'template_include', 'learndash_30_focus_mode', 99 );

/**
 * Returns the focus template path if the focus mode is enabled.
 *
 * Fires on `template_include` hook.
 *
 * @since 3.0.0
 *
 * @param string $template The path of the template to include.
 *
 * @return string The path of the template to include.
 */
function learndash_30_focus_mode( $template ) {

	$focus_mode = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'focus_mode_enabled' );
	if ( 'yes' !== $focus_mode ) {
		/**
		 * @todo: Not sure what setting this global to true controls. Why
		 * set to true if FM is not enabled.
		 *
		 * Seems this is only used here and above this function.
		 */
		global $learndash_in_focus_mode;
		$learndash_in_focus_mode = true;
	} else {
		$post_types = array(
			'sfwd-lessons',
			'sfwd-topic',
			'sfwd-assignment',
			'sfwd-quiz',
		);

		if ( in_array( get_post_type(), $post_types, true ) && is_singular( $post_types ) ) {
			$focus_index_template = SFWD_LMS::get_template( 'focus/index.php', null, false, true );
			if ( empty( $focus_index_template ) ) {
				$focus_index_template = LEARNDASH_LMS_PLUGIN_DIR . 'themes/ld30/templates/focus/index.php';
			}

			/**
			 * Allow override of the Focus Mode index template.
			 *
			 * @since 3.2.0
			 *
			 * @param string $focus_index_template Path to Focus Mode Index Template.
			 */
			$template = apply_filters( 'learndash_ld30_focus_mode_template_index', $focus_index_template );
		}
	}

	return $template;
}

add_filter( 'learndash_template_filename', 'learndash_30_template_filename', 1000, 5 );

/**
 * Gets the template file path by name.
 *
 * Fires on `learndash_template_filename` hook.
 *
 * @since 3.0.3
 *
 * @param string  $filepath         Template file path.
 * @param string  $name            Template name.
 * @param array   $args            Template data.
 * @param boolean $echo            Whether to echo the template output or not.
 * @param boolean $return_file_path Whether to return file or path or not.
 *
 * @return string Returns template file path.
 */
function learndash_30_template_filename( $filepath = '', $name = '', $args = array(), $echo = false, $return_file_path = false ) {
	/**
	 * The Transition Routes array contains the legacy template filename as the key
	 * and the value is the alternate filename to be used.
	 */
	$transition_template_filenames = array(
		// LD Core templates
		'course.php'                                  => 'course.php',
		'lesson.php'                                  => 'lesson.php',
		'topic.php'                                   => 'topic.php',
		'quiz.php'                                    => 'quiz.php',

		// LD Core Shortcode templates
		'profile.php'                                 => 'shortcodes/profile.php',
		'ld_course_list.php'                          => 'shortcodes/ld_course_list.php',
		'course_list_template.php'                    => 'shortcodes/course_list_template.php',
		'ld_topic_list.php'                           => 'shortcodes/ld_topic_list.php',
		'user_groups_shortcode.php'                   => 'shortcodes/user_groups_shortcode.php',
		'course_content_shortcode.php'                => 'shortcodes/course_content_shortcode.php',

		// LD Core Widgets
		'course_navigation_widget.php'                => 'widgets/course-navigation.php',
		'course_progress_widget.php'                  => 'widgets/course-progress.php',

		// LD Core Messages
		'learndash_course_prerequisites_message.php'  => 'modules/messages/prerequisites.php',
		'learndash_course_points_access_message.php'  => 'modules/messages/course-points.php',
		'learndash_course_lesson_not_available.php'   => 'modules/messages/lesson-not-available.php',

		// LD Core Modules.
		'learndash_lesson_video.php'                  => 'modules/lesson-video.php',

		'learndash_lesson_assignment_upload_form.php' => false,

	);

	if ( ( ! empty( $filepath ) ) && ( isset( $transition_template_filenames[ $filepath ] ) ) ) {
		$filepath = $transition_template_filenames[ $filepath ];
	}

	return $filepath;
}

add_action( 'wp_enqueue_scripts', 'learndash_30_template_assets' );

/**
 * Enqueues the ld30 theme template assets.
 *
 * Fires on `wp_enqueue_scripts` hook.
 *
 * @since 3.0.0
 */
function learndash_30_template_assets() {
	// If this function is being called then we are the active theme.
	$theme_template_url = LearnDash_Theme_Register::get_active_theme_base_url();

	/**
	 * @TODO : These assets really should be moved to the /templates directory since they are part of the theme.
	 */
	wp_register_style( 'learndash-front', $theme_template_url . '/assets/css/learndash' . learndash_min_asset() . '.css', array(), LEARNDASH_SCRIPT_VERSION_TOKEN );
	wp_register_script( 'learndash-front', $theme_template_url . '/assets/js/learndash.js', array( 'jquery' ), LEARNDASH_SCRIPT_VERSION_TOKEN, true );

	wp_register_style( 'learndash-quiz-front', $theme_template_url . '/assets/css/learndash.quiz.front' . learndash_min_asset() . '.css', array(), LEARNDASH_SCRIPT_VERSION_TOKEN );

	wp_enqueue_style( 'learndash-front' );
	wp_style_add_data( 'learndash-front', 'rtl', 'replace' );
	wp_enqueue_script( 'learndash-front' );

	wp_localize_script(
		'learndash-front',
		'ldVars',
		array(
			'postID'      => get_the_ID(),
			'videoReqMsg' => esc_html__( 'You must watch the video before accessing this content', 'learndash' ),
			'ajaxurl'     => admin_url( 'admin-ajax.php' ),
		)
	);

	if ( get_post_type() == 'sfwd-quiz' ) {
		wp_enqueue_style( 'learndash-quiz-front' );
		wp_style_add_data( 'learndash-quiz-front', 'rtl', 'replace' );
	}

	$dequeue_styles = array(
		'learndash_pager_css',
		'learndash_template_style_css',
	);

	foreach ( $dequeue_styles as $style ) {
		wp_dequeue_style( $style );
	}

}

add_action( 'enqueue_block_editor_assets', 'learndash_30_editor_scripts' );
/**
 * Enqueues the ld30 theme editor scripts.
 *
 * Fires on `enqueue_block_editor_assets` hook.
 *
 * @since 3.0.0
 */
function learndash_30_editor_scripts() {

	wp_enqueue_style( 'learndash-front', LEARNDASH_LMS_PLUGIN_URL . 'themes/ld30/assets/css/learndash' . learndash_min_asset() . '.css', array(), LEARNDASH_SCRIPT_VERSION_TOKEN );
	wp_style_add_data( 'learndash-front', 'rtl', 'replace' );
	wp_enqueue_script( 'learndash-front', LEARNDASH_LMS_PLUGIN_URL . 'themes/ld30/assets/js/learndash' . learndash_min_asset() . '.js', array( 'jquery' ), LEARNDASH_SCRIPT_VERSION_TOKEN, true );

}

class LearnDash_User_Status_Widget extends WP_Widget {

	/**
	 * Setup Course Info Widget
	 */
	public function __construct() {
		$widget_ops  = array(
			'classname'   => 'widget_lduserstatus',
			'description' => sprintf(
				// translators: placeholder: Courses.
				esc_html_x( 'LearnDash - Registered %s and progress information of users. Visible only to users logged in.', 'placeholders: courses', 'learndash' ),
				LearnDash_Custom_Label::get_label( 'courses' )
			),
		);
		$control_ops = array(); // 'width' => 400, 'height' => 350);
		parent::__construct( 'lduserstatus', __( 'User Status', 'learndash' ), $widget_ops, $control_ops );
	}



	/**
	 * Displays widget
	 *
	 * @since 3.0.0
	 *
	 * @param  array $args     widget arguments
	 * @param  array $instance widget instance
	 * @return string          widget output
	 */
	public function widget( $args, $instance ) {
		global $learndash_shortcode_used;

		extract( $args );

		/** This filter is documented in https://developer.wordpress.org/reference/hooks/widget_title/ */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance );

		if ( empty( $user_id ) ) {
			$current_user = wp_get_current_user();
			if ( empty( $current_user->ID ) ) {
				return;
			}

			$user_id = $current_user->ID;
		}

		if ( empty( $args ) ) {
			$args = array(
				'return' => true,
			);
		} elseif ( ! isset( $args['return'] ) ) {
			$args['return'] = true;
		}

		if ( isset( $instance['registered_num'] ) ) {
			$args['registered_num'] = intval( $instance['registered_num'] );
		}

		if ( isset( $instance['registered_orderby'] ) ) {
			$args['registered_orderby'] = sanitize_text_field( $instance['registered_orderby'] );
		}

		if ( isset( $instance['registered_order'] ) ) {
			$args['registered_order'] = sanitize_text_field( $instance['registered_order'] );
		}

		$course_info = SFWD_LMS::get_course_info( $user_id, $args );

		$user_status = learndash_get_template_part(
			'shortcodes/user-status.php',
			array(
				'course_info'    => $course_info,
				'shortcode_atts' => $args,
				'context'        => 'widget',
			),
			false
		);

		if ( empty( $user_status ) ) {
			return;
		}

		echo $before_widget; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML output before widget

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML output before and after title
		}

		echo $user_status; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML output user status
		echo $after_widget; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML output after widget

		$learndash_shortcode_used = true;
	}


	/**
	 * Handles widget updates in admin
	 *
	 * @since 2.1.0
	 *
	 * @param  array $new_instance
	 * @param  array $old_instance
	 * @return array $instance
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = wp_strip_all_tags( $new_instance['title'] );

		$instance['registered_show_thumbnail'] = esc_attr( $new_instance['registered_show_thumbnail'] );
		if ( '' !== $new_instance['registered_num'] ) {
			$instance['registered_num'] = intval( $new_instance['registered_num'] );
		} else {
			$instance['registered_num'] = false;
		}

		$instance['registered_orderby'] = esc_attr( $new_instance['registered_orderby'] );
		$instance['registered_order']   = esc_attr( $new_instance['registered_order'] );

		return $instance;
	}


	/**
	 * Display widget form in admin
	 *
	 * @since 2.1.0
	 *
	 * @param  array $instance widget instance
	 */
	public function form( $instance ) {
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'                     => '',
				'registered_show_thumbnail' => '',
				'registered_num'            => false,
				'registered_orderby'        => '',
				'registered_order'          => '',
			)
		);

		$title = wp_strip_all_tags( $instance['title'] );

		$registered_show_thumbnail = esc_attr( $instance['registered_show_thumbnail'] );

		if ( '' !== $instance['registered_num'] ) {
			$registered_num = abs( intval( $instance['registered_num'] ) );
		} else {
			$registered_num = '';
		}

		$registered_orderby = esc_attr( $instance['registered_orderby'] );
		$registered_order   = esc_attr( $instance['registered_order'] );

		?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'learndash' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>


			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'registered_show_thumbnail' ) ); ?>"><?php esc_html_e( 'Registered show thumbnail:', 'learndash' ); ?></label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'registered_show_thumbnail' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'registered_show_thumbnail' ) ); ?>">
					<option value="" <?php selected( $registered_show_thumbnail, '' ); ?>><?php esc_html_e( 'Yes (default)', 'learndash' ); ?></option>
					<option value="false" <?php selected( $registered_show_thumbnail, 'false' ); ?>><?php esc_html_e( 'No', 'learndash' ); ?></option>
				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'registered_num' ) ); ?>"><?php esc_html_e( 'Registered per page:', 'learndash' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'registered_num' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'registered_num' ) ); ?>" type="number" min="0" value="<?php echo esc_attr( $registered_num ); ?>" />
				<span class="description">
				<?php
					printf(
						// translators: placeholders: Default amount shown per page
						esc_html_x( 'Default is %d. Set to zero for no pagination.', 'placeholders: default per page', 'learndash' ),
						esc_html( LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_General_Per_Page', 'per_page' ) )
					);
				?>
				</span>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'registered_orderby' ) ); ?>"><?php esc_html_e( 'Registered order by:', 'learndash' ); ?></label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'registered_orderby' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'registered_orderby' ) ); ?>">
					<option value="" <?php selected( $registered_orderby, '' ); ?>><?php esc_html_e( 'Title (default) - Order by post title', 'learndash' ); ?></option>
					<option value="id" <?php selected( $registered_orderby, 'id' ); ?>><?php esc_html_e( 'ID - Order by post id', 'learndash' ); ?></option>
					<option value="date" <?php selected( $registered_orderby, 'date' ); ?>><?php esc_html_e( 'Date - Order by post date', 'learndash' ); ?></option>
					<option value="menu_order" <?php selected( $registered_orderby, 'menuorder' ); ?>><?php esc_html_e( 'Menu - Order by Page Order Value', 'learndash' ); ?></option>
				</select>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'registered_order' ) ); ?>"><?php esc_html_e( 'Registered order:', 'learndash' ); ?></label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'registered_order' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'registered_order' ) ); ?>">
					<option value="" <?php selected( $registered_order, '' ); ?>><?php esc_html_e( 'ASC (default) - lowest to highest values', 'learndash' ); ?></option>
					<option value="DESC" <?php selected( $registered_order, 'DESC' ); ?>><?php esc_html_e( 'DESC - highest to lowest values', 'learndash' ); ?></option>
				</select>
			</p>

		<?php
	}
}

add_action(
	'widgets_init',
	function() {
		return register_widget( 'LearnDash_User_Status_Widget' );
	}
);

add_action( 'init', 'learndash_30_nav_menus' );
/**
 * Registers the ld30 theme nav menus.
 *
 * Fires on `init` hook.
 *
 * @since 3.0.0
 */
function learndash_30_nav_menus() {

	register_nav_menus(
		/**
		 * Filters nav menu locations
		 *
		 * @since 3.0.0
		 *
		 * @param array $locations An Associative array of menu location identifiers (like a slug) and descriptive text.
		 */
		apply_filters(
			'learndash_30_nav_menus',
			array(
				'ld30_focus_mode' => esc_html__( 'LearnDash: Focus Mode Dropdown', 'learndash' ),
			)
		)
	);

}

/**
 * Gets the ld30 theme custom focus menu items.
 *
 * @since 3.0.0
 *
 * @return array|false An array of menu items, otherwise false.
 */
function learndash_30_get_custom_focus_menu_items() {

	$theme_locations = get_nav_menu_locations();

	if ( ! isset( $theme_locations['ld30_focus_mode'] ) ) {
		return false;
	}

	$menu_obj = get_term( $theme_locations['ld30_focus_mode'], 'nav_menu' );

	if ( ! $menu_obj || ! isset( $menu_obj->term_id ) ) {
		return false;
	}

	return wp_get_nav_menu_items( $menu_obj->term_id );

}

add_action( 'wp_enqueue_scripts', 'learndash_30_custom_colors' );

/**
 * Enqueues the ld30 theme custom colors style.
 *
 * Fires on `wp_enqueue_scripts` hook.
 *
 * @since 3.0.0
 */
function learndash_30_custom_colors() {

	/**
	 * Filters default custom colors used in settings to set accent color, progress color, and notifications settings.
	 *
	 * @since 3.0.0
	 *
	 * @param array $custom_colors An Associative array of color name and values in hex code.
	 */
	$colors = apply_filters(
		'learndash_30_custom_colors',
		array(
			'primary'   => LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'color_primary' ),
			'secondary' => LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'color_secondary' ),
			'tertiary'  => LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'color_tertiary' ),
		)
	);

	/**
	 * Filters responsive videos setting value. Override the value of responsive video set in settings.
	 *
	 * @since 3.0.1
	 *
	 * @param string|int $resonsive_video_setting Value is yes if enabled and empty string if disabled. Default is set to 0.
	 */
	$responsive_video = apply_filters( 'learndash_30_responsive_video', LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'responsive_video_enabled' ) );

	/**
	 * Filters focus mode width setting value. Override the focus mode width set in settings.
	 *
	 * @since 3.0.5
	 *
	 * @param string $focus_width_setting Focus mode width. Default value is default.
	 */
	$focus_width = apply_filters( 'learndash_30_focus_mode_width', LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'focus_mode_content_width' ) );

	ob_start();
	if ( ( isset( $colors['primary'] ) ) && ( ! empty( $colors['primary'] ) ) && ( LD_30_COLOR_PRIMARY != $colors['primary'] ) ) {

		// Convert HEX to RGB for for use with rgba()
		$primaryBuildRgb = list($r, $g, $b) = sscanf( $colors['primary'], '#%02x%02x%02x' );
		$primaryRgb      = "$r, $g, $b";

		?>
		.learndash-wrapper .ld-item-list .ld-item-list-item.ld-is-next,
		.learndash-wrapper .wpProQuiz_content .wpProQuiz_questionListItem label:focus-within {
			border-color: <?php echo esc_attr( $colors['primary'] ); ?>;
		}

		/*
		.learndash-wrapper a:not(.ld-button):not(#quiz_continue_link):not(.ld-focus-menu-link):not(.btn-blue):not(#quiz_continue_link):not(.ld-js-register-account):not(#ld-focus-mode-course-heading):not(#btn-join):not(.ld-item-name):not(.ld-table-list-item-preview):not(.ld-lesson-item-preview-heading),
		 */

		.learndash-wrapper .ld-breadcrumbs a,
		.learndash-wrapper .ld-lesson-item.ld-is-current-lesson .ld-lesson-item-preview-heading,
		.learndash-wrapper .ld-lesson-item.ld-is-current-lesson .ld-lesson-title,
		.learndash-wrapper .ld-primary-color-hover:hover,
		.learndash-wrapper .ld-primary-color,
		.learndash-wrapper .ld-primary-color-hover:hover,
		.learndash-wrapper .ld-primary-color,
		.learndash-wrapper .ld-tabs .ld-tabs-navigation .ld-tab.ld-active,
		.learndash-wrapper .ld-button.ld-button-transparent,
		.learndash-wrapper .ld-button.ld-button-reverse,
		.learndash-wrapper .ld-icon-certificate,
		.learndash-wrapper .ld-login-modal .ld-login-modal-login .ld-modal-heading,
		#wpProQuiz_user_content a,
		.learndash-wrapper .ld-item-list .ld-item-list-item a.ld-item-name:hover,
		.learndash-wrapper .ld-focus-comments__heading-actions .ld-expand-button,
		.learndash-wrapper .ld-focus-comments__heading a,
		.learndash-wrapper .ld-focus-comments .comment-respond a,
		.learndash-wrapper .ld-focus-comment .ld-comment-reply a.comment-reply-link:hover,
		.learndash-wrapper .ld-expand-button.ld-button-alternate {
			color: <?php echo esc_attr( $colors['primary'] ); ?> !important;
		}

		.learndash-wrapper .ld-focus-comment.bypostauthor>.ld-comment-wrapper,
		.learndash-wrapper .ld-focus-comment.role-group_leader>.ld-comment-wrapper,
		.learndash-wrapper .ld-focus-comment.role-administrator>.ld-comment-wrapper {
			background-color:rgba(<?php echo esc_attr( $primaryRgb ); ?>, 0.03) !important;
		}


		.learndash-wrapper .ld-primary-background,
		.learndash-wrapper .ld-tabs .ld-tabs-navigation .ld-tab.ld-active:after {
			background: <?php echo esc_attr( $colors['primary'] ); ?> !important;
		}



		.learndash-wrapper .ld-course-navigation .ld-lesson-item.ld-is-current-lesson .ld-status-incomplete,
		.learndash-wrapper .ld-focus-comment.bypostauthor:not(.ptype-sfwd-assignment) >.ld-comment-wrapper>.ld-comment-avatar img,
		.learndash-wrapper .ld-focus-comment.role-group_leader>.ld-comment-wrapper>.ld-comment-avatar img,
		.learndash-wrapper .ld-focus-comment.role-administrator>.ld-comment-wrapper>.ld-comment-avatar img {
			border-color: <?php echo esc_attr( $colors['primary'] ); ?> !important;
		}



		.learndash-wrapper .ld-loading::before {
			border-top:3px solid <?php echo esc_attr( $colors['primary'] ); ?> !important;
		}

		.learndash-wrapper .ld-button:hover:not(.learndash-link-previous-incomplete):not(.ld-button-transparent),
		#learndash-tooltips .ld-tooltip:after,
		#learndash-tooltips .ld-tooltip,
		.learndash-wrapper .ld-primary-background,
		.learndash-wrapper .btn-join,
		.learndash-wrapper #btn-join,
		.learndash-wrapper .ld-button:not(.ld-button-reverse):not(.learndash-link-previous-incomplete):not(.ld-button-transparent),
		.learndash-wrapper .ld-expand-button,
		.learndash-wrapper .wpProQuiz_content .wpProQuiz_button:not(.wpProQuiz_button_reShowQuestion):not(.wpProQuiz_button_restartQuiz),
		.learndash-wrapper .wpProQuiz_content .wpProQuiz_button2,
		.learndash-wrapper .ld-focus .ld-focus-sidebar .ld-course-navigation-heading,
		.learndash-wrapper .ld-focus .ld-focus-sidebar .ld-focus-sidebar-trigger,
		.learndash-wrapper .ld-focus-comments .form-submit #submit,
		.learndash-wrapper .ld-login-modal input[type='submit'],
		.learndash-wrapper .ld-login-modal .ld-login-modal-register,
		.learndash-wrapper .wpProQuiz_content .wpProQuiz_certificate a.btn-blue,
		.learndash-wrapper .ld-focus .ld-focus-header .ld-user-menu .ld-user-menu-items a,
		#wpProQuiz_user_content table.wp-list-table thead th,
		#wpProQuiz_overlay_close,
		.learndash-wrapper .ld-expand-button.ld-button-alternate .ld-icon {
			background-color: <?php echo esc_attr( $colors['primary'] ); ?> !important;
		}

		.learndash-wrapper .ld-focus .ld-focus-header .ld-user-menu .ld-user-menu-items:before {
			border-bottom-color: <?php echo esc_attr( $colors['primary'] ); ?> !important;
		}

		.learndash-wrapper .ld-button.ld-button-transparent:hover {
			background: transparent !important;
		}

		.learndash-wrapper .ld-focus .ld-focus-header .sfwd-mark-complete .learndash_mark_complete_button,
		.learndash-wrapper .ld-focus .ld-focus-header #sfwd-mark-complete #learndash_mark_complete_button,
		.learndash-wrapper .ld-button.ld-button-transparent,
		.learndash-wrapper .ld-button.ld-button-alternate,
		.learndash-wrapper .ld-expand-button.ld-button-alternate {
			background-color:transparent !important;
		}

		.learndash-wrapper .ld-focus-header .ld-user-menu .ld-user-menu-items a,
		.learndash-wrapper .ld-button.ld-button-reverse:hover,
		.learndash-wrapper .ld-alert-success .ld-alert-icon.ld-icon-certificate,
		.learndash-wrapper .ld-alert-warning .ld-button:not(.learndash-link-previous-incomplete),
		.learndash-wrapper .ld-primary-background.ld-status {
			color:white !important;
		}

		.learndash-wrapper .ld-status.ld-status-unlocked {
			background-color: <?php echo esc_attr( learndash_hex2rgb( $colors['primary'], '0.2' ) ); ?> !important;
			color: <?php echo esc_attr( $colors['primary'] ); ?> !important;
		}

		.learndash-wrapper .wpProQuiz_content .wpProQuiz_addToplist {
			background-color: <?php echo esc_attr( learndash_hex2rgb( $colors['primary'], '0.1' ) ); ?> !important;
			border: 1px solid <?php echo esc_attr( $colors['primary'] ); ?> !important;
		}

		.learndash-wrapper .wpProQuiz_content .wpProQuiz_toplistTable th {
			background: <?php echo esc_attr( $colors['primary'] ); ?> !important;
		}

		.learndash-wrapper .wpProQuiz_content .wpProQuiz_toplistTrOdd {
			background-color: <?php echo esc_attr( learndash_hex2rgb( $colors['primary'], '0.1' ) ); ?> !important;
		}

		.learndash-wrapper .wpProQuiz_content .wpProQuiz_reviewDiv li.wpProQuiz_reviewQuestionTarget {
			background-color: <?php echo esc_attr( $colors['primary'] ); ?> !important;
		}
		.learndash-wrapper .wpProQuiz_content .wpProQuiz_time_limit .wpProQuiz_progress {
			background-color: <?php echo esc_attr( $colors['primary'] ); ?> !important;
		}
		<?php
	}

	if ( ( isset( $colors['secondary'] ) ) && ( ! empty( $colors['secondary'] ) ) && ( LD_30_COLOR_SECONDARY != $colors['secondary'] ) ) {
		?>

		.learndash-wrapper #quiz_continue_link,
		.learndash-wrapper .ld-secondary-background,
		.learndash-wrapper .learndash_mark_complete_button,
		.learndash-wrapper #learndash_mark_complete_button,
		.learndash-wrapper .ld-status-complete,
		.learndash-wrapper .ld-alert-success .ld-button,
		.learndash-wrapper .ld-alert-success .ld-alert-icon {
			background-color: <?php echo esc_attr( $colors['secondary'] ); ?> !important;
		}

		.learndash-wrapper .wpProQuiz_content a#quiz_continue_link {
			background-color: <?php echo esc_attr( $colors['secondary'] ); ?> !important;
		}

		.learndash-wrapper .course_progress .sending_progress_bar {
			background: <?php echo esc_attr( $colors['secondary'] ); ?> !important;
		}

		.learndash-wrapper .wpProQuiz_content .wpProQuiz_button_reShowQuestion:hover, .learndash-wrapper .wpProQuiz_content .wpProQuiz_button_restartQuiz:hover {
			background-color: <?php echo esc_attr( $colors['secondary'] ); ?> !important;
			opacity: 0.75;
		}

		.learndash-wrapper .ld-secondary-color-hover:hover,
		.learndash-wrapper .ld-secondary-color,
		.learndash-wrapper .ld-focus .ld-focus-header .sfwd-mark-complete .learndash_mark_complete_button,
		.learndash-wrapper .ld-focus .ld-focus-header #sfwd-mark-complete #learndash_mark_complete_button,
		.learndash-wrapper .ld-focus .ld-focus-header .sfwd-mark-complete:after {
			color: <?php echo esc_attr( $colors['secondary'] ); ?> !important;
		}

		.learndash-wrapper .ld-secondary-in-progress-icon {
			border-left-color: <?php echo esc_attr( $colors['secondary'] ); ?> !important;
			border-top-color: <?php echo esc_attr( $colors['secondary'] ); ?> !important;
		}

		.learndash-wrapper .ld-alert-success {
			border-color: <?php echo esc_attr( $colors['secondary'] ); ?>;
			background-color: transparent !important;
		}

		.learndash-wrapper .wpProQuiz_content .wpProQuiz_reviewQuestion li.wpProQuiz_reviewQuestionSolved,
		.learndash-wrapper .wpProQuiz_content .wpProQuiz_box li.wpProQuiz_reviewQuestionSolved {
			background-color: <?php echo esc_attr( $colors['secondary'] ); ?> !important;
		}

		.learndash-wrapper .wpProQuiz_content  .wpProQuiz_reviewLegend span.wpProQuiz_reviewColor_Answer {
			background-color: <?php echo esc_attr( $colors['secondary'] ); ?> !important;
		}

		<?php
	}

	if ( ( isset( $colors['tertiary'] ) ) && ( ! empty( $colors['tertiary'] ) ) && ( LD_30_COLOR_TERTIARY != $colors['tertiary'] ) ) {
		?>

		.learndash-wrapper .ld-alert-warning {
			background-color:transparent;
		}

		.learndash-wrapper .ld-status-waiting,
		.learndash-wrapper .ld-alert-warning .ld-alert-icon {
			background-color: <?php echo esc_attr( $colors['tertiary'] ); ?> !important;
		}

		.learndash-wrapper .ld-tertiary-color-hover:hover,
		.learndash-wrapper .ld-tertiary-color,
		.learndash-wrapper .ld-alert-warning {
			color: <?php echo esc_attr( $colors['tertiary'] ); ?> !important;
		}

		.learndash-wrapper .ld-tertiary-background {
			background-color: <?php echo esc_attr( $colors['tertiary'] ); ?> !important;
		}

		.learndash-wrapper .ld-alert-warning {
			border-color: <?php echo esc_attr( $colors['tertiary'] ); ?> !important;
		}

		.learndash-wrapper .ld-tertiary-background,
		.learndash-wrapper .ld-alert-warning .ld-alert-icon {
			color:white !important;
		}

		.learndash-wrapper .wpProQuiz_content .wpProQuiz_reviewQuestion li.wpProQuiz_reviewQuestionReview,
		.learndash-wrapper .wpProQuiz_content .wpProQuiz_box li.wpProQuiz_reviewQuestionReview {
			background-color: <?php echo esc_attr( $colors['tertiary'] ); ?> !important;
		}

		.learndash-wrapper .wpProQuiz_content  .wpProQuiz_reviewLegend span.wpProQuiz_reviewColor_Review {
			background-color: <?php echo esc_attr( $colors['tertiary'] ); ?> !important;
		}

		<?php
	}

	if ( isset( $focus_width ) && ! empty( $focus_width ) && 'default' !== $focus_width ) {
		?>
		.learndash-wrapper .ld-focus .ld-focus-main .ld-focus-content {
			max-width: <?php echo esc_attr( $focus_width ); ?>;
		}
		<?php
	}

	$custom_css = ob_get_clean();

	if ( ! empty( $custom_css ) ) {
		wp_add_inline_style( 'learndash-front', $custom_css );
	}

}

add_action( 'wp_ajax_ld30_ajax_profile_search', 'learndash_30_ajax_profile_search' );

/**
 * Gets the ajax profile search data.
 *
 * Fires on `wp_ajax_ld30_ajax_profile_search` and `wp_ajax_nopriv_ld30_ajax_profile_search` ajax action.
 *
 * @since 3.0.0
 */
function learndash_30_ajax_profile_search() {
	if ( ( ! isset( $_GET['ld-profile-search-nonce'] ) ) || ( empty( $_GET['ld-profile-search-nonce'] ) ) || ( ! wp_verify_nonce( $_GET['ld-profile-search-nonce'], 'learndash_profile_course_search_nonce' ) ) ) {
		wp_send_json_error(
			array(
				'success' => false,
				'message' => __(
					'verify failed',
					'learndash'
				),
			)
		);
	}

	ob_start();

	if ( ! isset( $_GET['shortcode_instance'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used for conditional, no data processed
		wp_send_json_error(
			array(
				'success' => false,
				'message' => __(
					'No attributes passed in',
					'learndash'
				),
			)
		);
	}

	if ( isset( $_GET['profile_search'] ) ) {
		$atts['search']            = sanitize_text_field( $_GET['profile_search'] );
		$_GET['ld-profile-search'] = sanitize_text_field( $_GET['profile_search'] );
	}

	/**
	 * Filters ajax profile search attributes.
	 *
	 * @since 3.0.0
	 *
	 * @param array $shortcode_instance Shortcode instance.
	 */
	$atts = apply_filters( 'learndash_profile_ajax_search_atts', $_GET['shortcode_instance'] );

	echo learndash_profile( $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Outputs the LearnDash Profile template

	wp_send_json_success(
		array(
			'success' => true,
			'markup'  => ob_get_clean(),
		)
	);

}

add_action( 'wp_ajax_ld30_ajax_pager', 'learndash_30_ajax_pager' );
add_action( 'wp_ajax_nopriv_ld30_ajax_pager', 'learndash_30_ajax_pager' );

/**
 * Gets the ld30 theme ajax pagination.
 *
 * Fires on `wp_ajax_ld30_ajax_pager` and `wp_ajax_nopriv_ld30_ajax_pager` ajax action.
 *
 * @since 3.0.0
 */
function learndash_30_ajax_pager() {
	if ( ( ! isset( $_GET['pager_nonce'] ) ) || ( empty( $_GET['pager_nonce'] ) ) || ( ! wp_verify_nonce( $_GET['pager_nonce'], 'ld30_ajax_pager' ) ) ) {
		wp_send_json_error(
			array(
				'success' => false,
				'message' => __(
					'No Pagination Match',
					'learndash'
				),
			)
		);
	}

	$course_id = ( isset( $_GET['course_id'] ) ? absint( $_GET['course_id'] ) : false );
	$lesson_id = ( isset( $_GET['lesson_id'] ) ? absint( $_GET['lesson_id'] ) : false );
	$group_id  = ( isset( $_GET['group_id'] ) ? absint( $_GET['group_id'] ) : false );

	$context = ( isset( $_GET['context'] ) ? esc_attr( $_GET['context'] ) : false );

	$widget_instance = ( isset( $_GET['widget_instance'] ) ? $_GET['widget_instance'] : array() );

	// Assumed Course Navigation Widget but always check.
	if ( isset( $widget_instance['widget_instance']['show_lesson_quizzes'] ) ) {
		$widget_instance['widget_instance']['show_lesson_quizzes'] = (bool) $widget_instance['widget_instance']['show_lesson_quizzes'];
	} else {
		$widget_instance['widget_instance']['show_lesson_quizzes'] = true;
	}

	if ( isset( $widget_instance['widget_instance']['show_topic_quizzes'] ) ) {
		$widget_instance['widget_instance']['show_topic_quizzes'] = (bool) $widget_instance['widget_instance']['show_topic_quizzes'];
	} else {
		$widget_instance['widget_instance']['show_topic_quizzes'] = true;
	}

	if ( isset( $widget_instance['widget_instance']['show_course_quizzes'] ) ) {
		$widget_instance['widget_instance']['show_course_quizzes'] = (bool) $widget_instance['widget_instance']['show_course_quizzes'];
	} else {
		$widget_instance['widget_instance']['show_course_quizzes'] = true;
	}

	$cuser   = wp_get_current_user();
	$user_id = ( is_user_logged_in() ? $cuser->ID : false );

	global $course_pager_results;

	$contexts_without_course_id = array(
		'profile',
		'course_info_courses',
		'group_courses',
	);

	if ( ! in_array( $context, $contexts_without_course_id, true ) && ( ! isset( $course_id ) || empty( $course_id ) ) ) {
		wp_send_json_error(
			array(
				'success' => false,
				'message' => sprintf(
					// translators: placeholder: course
					esc_html_x(
						'No %s ID supplied',
						'placeholder: course',
						'learndash'
					),
					learndash_get_custom_label( 'course' )
				),
			)
		);
	}

	if ( 'group_courses' === $context ) {
		if ( ! empty( $group_id ) ) {
			//if ( isset( $_GET['ld-grouo-courses'] ) )
			if ( learndash_is_user_in_group( $user_id, $group_id ) ) {
				$has_access = true;
			} else {
				$has_access = false;
			}

			$group_course_ids = learndash_get_group_courses_list( $group_id );
			ob_start();
			learndash_get_template_part(
				'group/listing.php',
				array(
					'group_id'             => $group_id,
					'user_id'              => $user_id,
					'group_courses'        => $group_course_ids,
					'has_access'           => $has_access,
					'course_pager_results' => $course_pager_results,
				),
				true
			);
			$group_courses_list = ob_get_clean();

			wp_send_json_success(
				array(
					'success' => true,
					'markup'  => $group_courses_list,
				)
			);
			die();
		}
	}

	// We're paginating topics
	if ( isset( $lesson_id ) && ! empty( $lesson_id ) ) {

		$all_topics = learndash_topic_dots( $lesson_id, $course_id, 'array' );

		/**
		 * Filters topic ajax pagination arguments.
		 *
		 * @since 3.0.0
		 *
		 * @param array $pagination_arguments Topic pagination arguments
		 */
		$topic_pager_args = apply_filters(
			'ld30_ajax_topic_pager_args',
			array(
				'course_id' => $course_id,
				'lesson_id' => $lesson_id,
			)
		);

		$topics = learndash_process_lesson_topics_pager( $all_topics, $topic_pager_args );

		if ( empty( $topics ) || ! $topics ) {
			wp_send_json_error(
				array(
					'success' => false,
					'message' => sprintf(
					// translators: No topics for this lesson
						esc_html_x( 'No %1$s for this $2$s', 'placeholder: topics, lesson', 'learndash' ),
						learndash_get_custom_label_lower( 'topics' ),
						learndash_get_custom_label_lower( 'lesson' )
					),
				)
			);
		}

		ob_start();

		foreach ( $topics as $key => $topic ) {
			learndash_get_template_part(
				'topic/partials/row.php',
				array(
					'topic'     => $topic,
					'user_id'   => $user_id,
					'course_id' => $course_id,
				),
				true
			);
		}

		$topic_list = ob_get_clean();

		$nav_topics = '';

		if ( isset( $_GET['widget_instance'] ) ) {

			ob_start();

			foreach ( $topics as $key => $topic ) {
				learndash_get_template_part(
					'widgets/navigation/topic-row.php',
					array(
						'topic'           => $topic,
						'course_id'       => $course_id,
						'user_id'         => $user_id,
						'widget_instance' => $widget_instance['widget_instance'],
					),
					true
				);
			}

			$nav_topics = ob_get_clean();

		}

		/**
		 * Add in quizzes if needed
		 *
		 * @var [type]
		 */

		$show_lesson_quizzes = true;

		if ( isset( $course_pager_results[ $lesson_id ]['pager'] ) && ! empty( $course_pager_results[ $lesson_id ]['pager'] ) ) :
			$show_lesson_quizzes = ( $course_pager_results[ $lesson_id ]['pager']['paged'] == $course_pager_results[ $lesson_id ]['pager']['total_pages'] ? true : false );
		endif;

		/**
		 * Filters whether to show quiz for a particular lesson or not.
		 *
		 * @since 3.0.0
		 *
		 * @param boolean $show_lesson_quizzes Boolean value determines whether to show a quiz or not.
		 * @param int     $lesson_id           Lesson ID.
		 * @param int     $course_id           Course ID.
		 * @param int     $user_id             User ID.
		 */
		$show_lesson_quizzes = apply_filters( 'learndash-show-lesson-quizzes', $show_lesson_quizzes, $lesson_id, $course_id, $user_id );

		if ( $show_lesson_quizzes ) {

			$quizzes = learndash_get_lesson_quiz_list( $lesson_id, $user_id, $course_id );

			if ( $quizzes && ! empty( $quizzes ) ) {

				/**
				 * First add them to the lesson listing
				 *
				 * @var [type]
				 */

				ob_start();

				foreach ( $quizzes as $quiz ) {

					learndash_get_template_part(
						'quiz/partials/row.php',
						array(
							'quiz'      => $quiz,
							'user_id'   => $user_id,
							'course_id' => $course_id,
							'context'   => 'lesson',
						),
						true
					);
				}

				$topic_list .= ob_get_clean();

				/**
				 * See if we should add them to the widget nav
				 *
				 * @var [type]
				 */

				if ( isset( $widget_instance['show_lesson_quizzes'] ) && true === (bool) $widget_instance['show_lesson_quizzes'] ) {

					ob_start();

					foreach ( $quizzes as $quiz ) {
						learndash_get_template_part(
							'widgets/navigation/quiz-row.php',
							array(
								'course_id' => $course_id,
								'user_id'   => $user_id,
								'context'   => 'lesson',
								'quiz'      => $quiz,
							),
							true
						);
					}

					$nav_topics .= ob_get_clean();

				}
			}
		}

		ob_start();

		learndash_get_template_part(
			'modules/pagination.php',
			array(
				'pager_results'   => $course_pager_results[ $lesson_id ]['pager'],
				'pager_context'   => 'course_topics',
				'href_query_arg'  => 'ld-topic-page',
				'lesson_id'       => $lesson_id,
				'course_id'       => $course_id,
				'href_val_prefix' => $lesson_id . '-',
			),
			true
		);

		$pager = ob_get_clean();

		wp_send_json_success(
			array(
				'success'    => true,
				'context'    => $context,
				'topics'     => $topic_list,
				'nav_topics' => $nav_topics,
				'pager'      => $pager,
				'lesson_id'  => $lesson_id,
			)
		);

	} elseif ( 'course_lessons' === $context ) {

		$lesson_query_args          = learndash_focus_mode_lesson_query_args( $course_id );
		$lessons                    = learndash_30_get_course_navigation( $course_id, array(), $lesson_query_args );
		$has_access                 = sfwd_lms_has_access( $course_id );
		$lesson_progression_enabled = learndash_lesson_progression_enabled( $course_id );
		$lesson_topics              = array();

		if ( ! empty( $lessons ) ) {
			foreach ( $lessons as $lesson ) {

				$all_topics = learndash_topic_dots( $lesson['post']->ID, false, 'array', null, $course_id );

				/** This filter is documented in themes/ld30/includes/helpers.php */
				$topic_pager_args = apply_filters(
					'ld30_ajax_topic_pager_args',
					array(
						'course_id' => $course_id,
						'lesson_id' => $lesson['post']->ID,
					)
				);

				$lesson_topics[ $lesson['post']->ID ] = learndash_process_lesson_topics_pager( $all_topics, $topic_pager_args );

				if ( ! empty( $lesson_topics[ $lesson['post']->ID ] ) ) {
					$has_topics = true;
				}
			}
		}

		$quizzes = learndash_get_course_quiz_list( $course_id );

		ob_start();

		learndash_get_template_part(
			'course/listing.php',
			array(
				'course_id'                  => $course_id,
				'user_id'                    => $user_id,
				'lessons'                    => $lessons,
				'lesson_topics'              => $lesson_topics,
				'quizzes'                    => $quizzes,
				'has_access'                 => $has_access,
				'course_pager_results'       => $course_pager_results,
				'lesson_progression_enabled' => $lesson_progression_enabled,
			),
			true
		);

		$lesson_list = ob_get_clean();

		// Need to adjust based on widget settings
		$lessons = learndash_get_course_lessons_list( $course_id, $user_id, $lesson_query_args );

		ob_start();

		learndash_get_template_part(
			'widgets/navigation/rows.php',
			array(
				'course_id'            => $course_id,
				'widget_instance'      => ( isset( $widget_instance['widget_instance'] ) ? $widget_instance['widget_instance'] : false ),
				'lessons'              => $lessons,
				'course_pager_results' => $course_pager_results,
				'has_access'           => $has_access,
				'user_id'              => $user_id,
			),
			true
		);

		$nav_lessons = ob_get_clean();

		wp_send_json_success(
			array(
				'success'         => true,
				'context'         => $context,
				'lessons'         => $lesson_list,
				'nav_lessons'     => $nav_lessons,
				'course_id'       => $course_id,
				'widget_instance' => $widget_instance,
			)
		);

	} elseif ( 'profile' === $context ) {

		ob_start();

		if ( ! isset( $_GET['shortcode_instance'] ) ) {
			wp_send_json_error(
				array(
					'success' => false,
					'message' => __(
						'No attributes passed in',
						'learndash'
					),
				)
			);
		}

		/**
		 * Filters ajax profile search attributes
		 *
		 * @since 3.0.0
		 *
		 * @param array $shortcode_instance Shortcode instance
		 */
		$atts = apply_filters( 'learndash_profile_ajax_pagination_atts', $_GET['shortcode_instance'] );

		echo learndash_profile( $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Outputs the LearnDash Profile shortcode

		wp_send_json_success(
			array(
				'success' => true,
				'markup'  => ob_get_clean(),
			)
		);

	} elseif ( 'course_content_shortcode' === $context ) {

		ob_start();

		/**
		 * Filters course content shortcode ajax pagination arguments.
		 *
		 * @since 3.0.0
		 *
		 * @param array $shortcode_instance Shortcode instance
		 */
		$atts = apply_filters( 'learndash_course_content_shortcode_ajax_pagination_atts', $_GET['shortcode_instance'] );

		echo learndash_course_content_shortcode( $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Outputs the LearnDash Course Content shortcode

		wp_send_json_success(
			array(
				'success' => true,
				'markup'  => ob_get_clean(),
			)
		);

	} elseif ( 'course_info_courses' === $context ) {

		$args = array(
			'return' => true,
			'paged'  => ( isset( $_GET['ld-user-status'] ) ? intval( $_GET['ld-user-status'] ) : 1 ),
		);

		add_filter(
			'learndash_course_info_paged',
			function( $paged = 1, $context = '' ) {
				if ( ( 'registered' === $context ) && ( isset( $_GET['ld-user-status'] ) ) && ( ! empty( $_GET['ld-user-status'] ) ) ) {
					$paged = intval( $_GET['ld-user-status'] );
				}

				// Always return $paged
				return $paged;
			},
			10,
			2
		);

		/**
		 * Filters user stats widget ajax pagination arguments.
		 *
		 * @since 3.0.0
		 *
		 * @param array $shortcode_instance Shortcode instance
		 */
		$instance = apply_filters( 'learndash_user_status_widget_ajax_pagination_atts', $_GET['shortcode_instance'] );

		if ( isset( $instance['registered_num'] ) ) {
			$args['registered_num'] = intval( $instance['registered_num'] );
		}

		if ( isset( $instance['registered_orderby'] ) ) {
			$args['registered_orderby'] = sanitize_text_field( $instance['registered_orderby'] );
		}

		if ( isset( $instance['registered_order'] ) ) {
			$args['registered_order'] = sanitize_text_field( $instance['registered_order'] );
		}

		$course_info = SFWD_LMS::get_course_info( $user_id, $args );

		ob_start();

		learndash_get_template_part(
			'shortcodes/user-status.php',
			array(
				'course_info'    => $course_info,
				'shortcode_atts' => $args,
				'context'        => 'widget',
			),
			true
		);

		wp_send_json_success(
			array(
				'success' => true,
				'markup'  => ob_get_clean(),
			)
		);

	}

	wp_send_json_error(
		array(
			'success' => false,
			'message' => __(
				'No Pagination Match',
				'learndash'
			),
		)
	);

}

/**
 * Gets the focus mode lesson query arguments.
 *
 * @global WP_Post $post Global post object.
 *
 * @since 3.0.0
 *
 * @param int      $course_id               Course ID.
 * @param int|null $course_lessons_per_page Number of course lessons per page.
 *
 * @return array An array of query arguments to get lesson.
 */
function learndash_focus_mode_lesson_query_args( $course_id, $course_lessons_per_page = null ) {

	global $post;

	$lesson_query_args = array();
	$instance          = array();

	if ( null === $course_lessons_per_page ) {
		$course_lessons_per_page = learndash_get_course_lessons_per_page( $course_id );
	}

	if ( $course_lessons_per_page > 0 && ( $post instanceof WP_Post ) ) {

		if ( in_array( $post->post_type, array( 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz' ), true ) ) {

			$instance['current_step_id'] = $post->ID;
			if ( 'sfwd-lessons' === $post->post_type ) {
				$instance['current_lesson_id'] = $post->ID;
			} elseif ( in_array( $post->post_type, array( 'sfwd-topic', 'sfwd-quiz' ), true ) ) {
				$instance['current_lesson_id'] = learndash_course_get_single_parent_step( $course_id, $post->ID, 'sfwd-lessons' );
				$instance['current_lesson_id'] = absint( $instance['current_lesson_id'] );
			}

			if ( ! empty( $instance['current_lesson_id'] ) ) {
				$course_lesson_ids = learndash_course_get_steps_by_type( $course_id, 'sfwd-lessons' );
				if ( ! empty( $course_lesson_ids ) ) {
					$course_lessons_paged = array_chunk( $course_lesson_ids, $course_lessons_per_page, true );
					$lessons_paged        = 0;
					foreach ( $course_lessons_paged as $paged => $paged_set ) {
						if ( in_array( $instance['current_lesson_id'], $paged_set, true ) ) {
							$lessons_paged = $paged + 1;
							break;
						}
					}

					if ( ! empty( $lessons_paged ) ) {
						$lesson_query_args['pagination'] = 'true';
						$lesson_query_args['paged']      = $lessons_paged;
					}
				}
			} elseif ( in_array( $post->post_type, array( 'sfwd-quiz' ), true ) ) {
				// If here we have a global Quiz. So we set the pager to the max number
				$course_lesson_ids = learndash_course_get_steps_by_type( $course_id, 'sfwd-lessons' );
				if ( ! empty( $course_lesson_ids ) ) {
					$course_lessons_paged       = array_chunk( $course_lesson_ids, $course_lessons_per_page, true );
					$lesson_query_args['paged'] = count( $course_lessons_paged );
				}
			}
		}
	} else {
		if ( ( $post ) && ( is_a( $post, 'WP_Post' ) ) && ( in_array( $post->post_type, array( 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz' ), true ) ) ) {

			$instance['current_step_id'] = $post->ID;
			if ( 'sfwd-lessons' === $post->post_type ) {
				$instance['current_lesson_id'] = $post->ID;
			} elseif ( in_array( $post->post_type, array( 'sfwd-topic', 'sfwd-quiz' ), true ) ) {
				$instance['current_lesson_id'] = learndash_course_get_single_parent_step( $course_id, $post->ID, 'sfwd-lessons' );
			}
		}
	}

	return $lesson_query_args;

}

/**
 * Converts the hex color values to rgb.
 *
 * @since 3.0.0
 *
 * @param string            $color  Color value in hex format.
 * @param float|int|boolean $opacity The opacity of color.
 *
 * @return string Color value in rgb format.
 */
function learndash_hex2rgb( $color, $opacity = false ) {

	$default = 'rgb(0,0,0)';

	// Return default if no color provided
	if ( empty( $color ) ) {
		return $default;
	}

	// Sanitize $color if "#" is provided
	if ( '#' === $color[0] ) {
		$color = substr( $color, 1 );
	}

		// Check if color has 6 or 3 characters and get values
	if ( strlen( $color ) == 6 ) {
			$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
	} elseif ( strlen( $color ) == 3 ) {
			$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
	} else {
			return $default;
	}

		// Convert hexadec to rgb
		$rgb = array_map( 'hexdec', $hex );

		// Check if opacity is set(rgba or rgb)
	if ( $opacity ) {
		if ( abs( $opacity ) > 1 ) {
			$opacity = 1.0;
		}
		$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
	} else {
		$output = 'rgb(' . implode( ',', $rgb ) . ')';
	}

		// Return rgb(a) color string
		return $output;
}

/**
 * Gets the ld30 theme course navigation.
 *
 * @since 3.0.0
 *
 * @global array $course_navigation_widget_pager Global course navigation widget pager.
 *
 * @param int   $course_id         Course ID.
 * @param array $widget_instance   An array of widget instance data.
 * @param array $lesson_query_args An array of query arguments to get lesson.
 *
 * @return string|void Course navigation HTML output.
 */
function learndash_30_get_course_navigation( $course_id, $widget_instance = array(), $lesson_query_args = array() ) {

	$course = get_post( $course_id );

	if ( empty( $course->ID ) || $course_id != $course->ID ) {
		return;
	}

	if ( empty( $course->ID ) || 'sfwd-courses' !== $course->post_type ) {
		return;
	}

	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
	} else {
		$user_id = 0;
	}

	$course_navigation_widget_pager = array();

	global $course_navigation_widget_pager;

	add_action(
		'learndash_course_lessons_list_pager',
		function( $query_result = null ) {

			global $course_navigation_widget_pager;

			$course_navigation_widget_pager['paged'] = 1;

			if ( ( isset( $query_result->query_vars['paged'] ) ) && ( $query_result->query_vars['paged'] > 1 ) ) {
				$course_navigation_widget_pager['paged'] = $query_result->query_vars['paged'];
			}

			$course_navigation_widget_pager['total_items'] = $query_result->found_posts;
			$course_navigation_widget_pager['total_pages'] = $query_result->max_num_pages;

		}
	);

	$lessons = learndash_get_course_lessons_list( $course, $user_id, $lesson_query_args );

	return $lessons;

}

/**
 * Gets the ld30 theme course sections.
 *
 * @since 3.0.0
 *
 * @param int|null $course_id Course ID.
 *
 * @return array|false An array of sections or false.
 */
function learndash_30_get_course_sections( $course_id = null ) {

	if ( empty( $course_id ) ) {
		$course_id = get_the_ID();
	}

	if ( learndash_get_post_type_slug( 'course' ) !== get_post_type( $course_id ) ) {
		$course_id = learndash_get_course_id( $course_id );
	}

	$course_sections = learndash_course_get_sections( $course_id );
	$sections        = array();

	if ( ! empty( $course_sections ) ) {
		foreach ( $course_sections as $section ) {
			if ( ( property_exists( $section, 'steps' ) ) && ( ! empty( $section->steps ) ) ) {
				$sections[ $section->steps[0] ] = $section;
			}
		}
	}

	return $sections;
}

add_filter( 'body_class', 'learndash_30_custom_body_classes' );

/**
 * Gets the ld30 theme custom body classes.
 *
 * Fires on `body_class` hook.
 *
 * @since 3.0.0
 *
 * @param array $classes An array of body class names.
 *
 * @return array An array of body class names.
 */
function learndash_30_custom_body_classes( $classes ) {

	$focus_mode = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'focus_mode_enabled' );

	$post_types = array(
		'sfwd-lessons',
		'sfwd-topic',
		'sfwd-quiz',
		'sfwd-assignment',
	);

	if ( 'yes' === $focus_mode && in_array( get_post_type(), $post_types, true ) ) {
		$classes[] = 'ld-in-focus-mode';
	}

	return $classes;

}

/**
 * Checks whether a post can be marked as complete or not in focus mode.
 *
 * @since 3.0.0
 *
 * @param int|WP_Post|null $post      `WP_Post` object or post ID. Default to global $post.
 * @param int|null         $course_id Course ID.
 *
 * @return boolean Whether a post can be marked as complete.
 */
function learndash_30_focus_mode_can_complete( $post = null, $course_id = null ) {

	if ( null === $post ) {
		global $post;
	}

	if ( is_int( $post ) ) {
		$post = get_post( $post );
	}

	if ( ! $course_id ) {
		$course_id = learndash_get_course_id( $course_id );
	}

	// Shouldn't appear regardless if this is a quiz
	if ( get_post_type( $post ) == 'sfwd-quiz' ) {
		return false;
	}

	$complete_button = learndash_mark_complete( $post );

	// If the complete button returns empty, also just return false
	if ( empty( $complete_button ) ) {
		return false;
	}

	// Check if has any outstanding quizzes
	$quizzes = learndash_get_lesson_quiz_list( $post->ID, get_current_user_id(), $course_id );

	// If there is a quiz then the quiz is the mark complete
	if ( $quizzes ) {
		return false;
	}

	return true;

}

/**
 * Deprecated
 *
 * @deprecated
 */
function learndash_30_responsive_videos( $html, $url, $attr, $post_id ) {

	/** This filter is documented in themes/ld30/includes/helpers.php */
	$responsive_video = apply_filters( 'learndash_30_responsive_video', LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'responsive_video_enabled' ) );

	if ( ! isset( $responsive_video ) || 'yes' !== $responsive_video ) {
		return false;
	}

	/**
	 * Filters Responsive video supported post types.
	 *
	 * @param array $post_types Array of supported post type.
	 */
	$post_types = apply_filters(
		'learndash_responsive_video_post_types',
		array(
			'sfwd-courses',
			'sfwd-lessons',
			'sfwd-topic',
			'sfwd-quiz',
			'sfwd-assignments',
		)
	);

	if ( ! in_array( get_post_type( $post_id ), $post_types, true ) ) {
		return $html;
	}

	/**
	 * Filters responsive video domains. Used to modify the supported domains for the responsive video.
	 *
	 * @since 3.0.0
	 *
	 * @param array $video_domains Array of video domains to support responsive video.
	 */
	$matches = apply_filters(
		'learndash_responsive_video_domains',
		array(
			'youtube.com',
			'vimeo.com',
		)
	);

	foreach ( $matches as $match ) {
		if ( strpos( $url, $match ) !== false ) {
			return '<div class="ld-resp-video">' . $html . '</div>';
		}
	}

	return $html;

}

/**
 * Gets the certificate count for a user.
 *
 * @since 3.0.0
 *
 * @param WP_User|int|null $user `WP_User` object or user ID. Defaults to current logged in user.
 *
 * @return int|false Returns users certificate count.
 */
function learndash_get_certificate_count( $user = null ) {

	if ( null === $user ) {
		$user = wp_get_current_user();
	}

	if ( is_int( $user ) ) {
		$user = get_user_by( 'id', $user );
	}

	if ( ! $user ) {
		return false;
	}

	$certificates = 0;

	$course_ids = learndash_user_get_enrolled_courses( $user->ID, array(), true );
	$quizzes    = get_user_meta( $user->ID, '_sfwd-quizzes', true );

	if ( $course_ids && ! empty( $course_ids ) ) {
		foreach ( $course_ids as $course_id ) {

			$link = learndash_get_course_certificate_link( $course_id, $user->ID );

			if ( ! empty( $link ) ) {
				$certificates++;
			}
		}
	}

	if ( $quizzes && ! empty( $quizzes ) ) {
		foreach ( $quizzes as $quiz_attempt ) {
			if ( isset( $quiz_attempt['certificate']['certificateLink'] ) ) {
				$certificates++;
			}
		}
	}

	return $certificates;

}

/**
 * Gets whether the lesson has quiz or not.
 *
 * @since 3.0.0
 *
 * @param int|null $course_id Course ID. Defauls to current post ID in WordPress loop.
 * @param int|null $lessons   An array of lesson `WP_Post` object.
 *
 * @return boolean Returns whether a lesson has quiz or not.
 */
function learndash_30_has_lesson_quizzes( $course_id = null, $lessons = null ) {

	if ( null === $course_id && get_post_type() == 'sfwd-courses' ) {
		$course_id = get_the_ID();
	} elseif ( null === $course_id ) {
		$course_id = learndash_get_course_id( get_the_ID() );
	}

	if ( null === $lessons ) {
		$lessons = learndash_get_course_lessons_list( $course_id );
	}

	foreach ( $lessons as $lesson ) {

		$quizzes = learndash_get_lesson_quiz_list( $lesson['post']->ID, null, $course_id );

		if ( ! empty( $quizzes ) ) {
			return true;
		}
	}

	return false;

}

/**
 * Gets an array of points awarded for an assignment.
 *
 * @since 3.0.0
 *
 * @param int $assignment_id Assignment ID.
 *
 * @return false|array An array of points awarded for an assignment or false if the points are disabled.
 */
function learndash_get_points_awarded_array( $assignment_id ) {

	$points_enabled = learndash_assignment_is_points_enabled( $assignment_id );

	if ( ! $points_enabled ) {
		return false;
	}

	$current = get_post_meta( $assignment_id, 'points', true );

	if ( is_numeric( $current ) ) {
		$assignment_settings_id = intval( get_post_meta( $assignment_id, 'lesson_id', true ) );
		$max_points             = learndash_get_setting( $assignment_settings_id, 'lesson_assignment_points_amount' );
		$max_points             = intval( $max_points );
		if ( ! empty( $max_points ) ) {
			$percentage = ( intval( $current ) / intval( $max_points ) ) * 100;
			$percentage = round( $percentage, 2 );
		} else {
			$percentage = 0.00;
		}

		/**
		 * Filters Points awarded data. Used to modify points given for any particular assignment.
		 *
		 * @since 3.0.0
		 *
		 * @param array $points_awarded Array for points awarded details.
		 * @param int   $assignment_id  Assignment ID.
		 */
		return apply_filters(
			'learndash_get_points_awarded_array',
			array(
				'current'    => $current,
				'max'        => $max_points,
				'percentage' => $percentage,
			),
			$assignment_id
		);

	}

}

/**
 * Gets whether a lesson has topics or not.
 *
 * @since 3.0.0
 *
 * @param int|null   $course_id Course ID.
 * @param array|null $lessons   An array of lesson objects.
 *
 * @return boolean True if the lesson has topics otherwise false.
 */
function learndash_30_has_topics( $course_id = null, $lessons = null ) {

	$course_id = ( null === $course_id ? learndash_get_course_id() : $course_id );

	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
	} else {
		$user_id = 0;
	}

	if ( ! empty( $lessons ) ) {
		foreach ( $lessons as $lesson ) {
			$lesson_topics[ $lesson['post']->ID ] = learndash_topic_dots( $lesson['post']->ID, false, 'array', $user_id, $course_id );
			if ( ! empty( $lesson_topics[ $lesson['post']->ID ] ) ) {
				return true;
			}
		}
	}

}

/**
 * Outputs the currency symbol.
 *
 * @since 3.0.0
 */
function learndash_30_the_currency_symbol() {
	echo wp_kses_post( learndash_30_get_currency_symbol() );
}

/**
 * Gets the currency symbol.
 *
 * @since 3.0.0
 *
 * @return string|false Returns currency symbol.
 */
function learndash_30_get_currency_symbol() {

	$options          = get_option( 'sfwd_cpt_options' );
	$currency_setting = class_exists( 'LearnDash_Settings_Section' ) ? LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_PayPal', 'paypal_currency' ) : null;
	$currency         = '';
	$stripe_settings  = get_option( 'learndash_stripe_settings' );

	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ( is_plugin_active( 'learndash-stripe/learndash-stripe.php' ) && ! empty( $stripe_settings ) && ! empty( $stripe_settings['currency'] ) ) {
		$currency = $stripe_settings['currency'];
	} elseif ( isset( $currency_setting ) || ! empty( $currency_setting ) ) {
		$currency = $currency_setting;
	} elseif ( isset( $options['modules'] ) && isset( $options['modules']['sfwd-courses_options'] ) && isset( $options['modules']['sfwd-courses_options']['sfwd-courses_paypal_currency'] ) ) {
		$currency = $options['modules']['sfwd-courses_options']['sfwd-courses_paypal_currency'];
	}

	if ( class_exists( 'NumberFormatter' ) ) {
		$locale        = get_locale();
		$number_format = new NumberFormatter( $locale . '@currency=' . $currency, NumberFormatter::CURRENCY );
		$currency      = $number_format->getSymbol( NumberFormatter::CURRENCY_SYMBOL );
	}

	return $currency;

}

/**
 * Genesis doesn't use the normal wp_enqueue_scripts or wp_head so we need to call the enqueue function specifically for Genesis
 */
add_action( 'learndash-focus-head', 'learndash_studiopress_compatibility' );

/**
 * Enqueues the genesis main stylesheet.
 *
 * Fires on `learndash-focus-head` hook.
 *
 * @since 3.0.1
 */
function learndash_studiopress_compatibility() {

	if ( function_exists( 'genesis_enqueue_main_stylesheet' ) ) {
		genesis_enqueue_main_stylesheet();
	}

}

add_filter(
	'sfwd_lms_has_access',
	function( $access, $post_id, $user_id ) {
		if ( ( is_single() ) && ( ! is_admin() ) ) {
			$lesson_id = learndash_get_lesson_id( $post_id );
			if ( ( true === (bool) $access ) && ( ! empty( $lesson_id ) ) && ( learndash_is_sample( $lesson_id ) ) ) {

				/**
				 * Filters whether to allow access to the sample lesson or not.
				 *
				 * By default a sample lesson is available even to anonymous users. This
				 * filter will override that access. The filer 'learndash_can_access_sample'
				 * is also used themes/ld30/templates/lesson/partials/row.php to control
				 * visibility of the lesson and sub-steps.
				 *
				 * @since 3.2.0
				 *
				 * @param boolean $access  Access status true if the user can access $post_id.
				 * @param integer $post_id Course step the user is trying to access.
				 * @param integer $user_id User ID.
				 */
				$access = apply_filters( 'learndash_lesson_sample_access', $access, $lesson_id, learndash_get_course_id(), $user_id );
			}
		}

		return $access;
	},
	30,
	3
);


/**
 * Get group price
 *
 * Return an array of price type, amount and cycle
 *
 * @since 3.2.0
 *
 * @param  int/object $group
 * @return array      price details
 */
function learndash_get_group_price( $group = null ) {

	if ( null === $group ) {
		global $post;
		$group = $post;
	}

	if ( is_numeric( $group ) ) {
		$group = get_post( $group );
	}
	if ( ! is_a( $group, 'WP_Post' ) ) {
		return false;
	}

	// Get the course price
	$meta = get_post_meta( $group->ID, '_groups', true );

	$group_price = array(
		'type'  => ! empty( $meta['groups_group_price_type'] ) ? $meta['groups_group_price_type'] : LEARNDASH_DEFAULT_GROUP_PRICE_TYPE,
		'price' => ! empty( $meta['groups_group_price'] ) ? $meta['groups_group_price'] : '',
	);

	if ( 'subscribe' === $group_price['type'] ) {

		$frequency = get_post_meta( $group->ID, 'group_price_billing_t3', true );
		$interval  = intval( get_post_meta( $group->ID, 'group_price_billing_p3', true ) );

		$label = '';

		switch ( $frequency ) {
			case ( 'D' ):
				$label = _n( 'day', 'days', $interval, 'learndash' );
				break;
			case ( 'W' ):
				$label = _n( 'week', 'weeks', $interval, 'learndash' );
				break;
			case ( 'M' ):
				$label = _n( 'month', 'months', $interval, 'learndash' );
				break;
			case ( 'Y' ):
				$label = _n( 'year', 'years', $interval, 'learndash' );
				break;
		}

		$group_price['frequency'] = $label;
		$group_price['interval']  = $interval;

	}

	/**
	 * Filter Group Price details.
	 *
	 * @since 3.2.0
	 *
	 * @param array $group_price Group Price Details array.
	 */
	return apply_filters( 'learndash_get_group_price', $group_price );

}
