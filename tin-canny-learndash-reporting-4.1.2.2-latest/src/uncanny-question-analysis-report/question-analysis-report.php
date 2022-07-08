<?php

namespace uncanny_learndash_reporting;

/**
 *
 */
class QuestionAnalysisReport extends Config {
	/**
	 * @var string
	 */
	private static $shortcode = 'uo_question_report';
	/**
	 * @var array
	 */
	private static $columns = array();
	/**
	 * @var string
	 */
	private static $dropdown = '';
	/**
	 * @var string
	 */
	private static $date_range = '';

	/**
	 * @var int
	 */
	private static $total_distractors = 0;

	/**
	 * @var array
	 */
	private static $correct_answer_data = array();

	/**
	 * @var array
	 */
	private static $plot_questions_answer_data = array();

	/**
	 * @var array
	 */
	private static $distractors = array();

	/**
	 * @var array
	 */
	private static $questions = array();

	/**
	 * @var
	 */
	private static $quiz_id;
	/**
	 * @var
	 */
	private static $pro_quiz_id;

	/**
	 * @var array
	 */
	private static $css_class_holder = array();

	/**
	 *
	 */
	public function __construct() {
		// Initiate columns
		self::setup_columns();

		// Enqueue scripts and styles
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'analysis_report_scripts' ) );

		// Add shortcode
		add_shortcode( self::$shortcode, array( __CLASS__, 'uo_question_report_func' ) );
	}

	/**
	 * @return void
	 */
	public static function analysis_report_scripts() {
		global $post;
		// If NOT WP_Post type, bail
		if ( ! $post instanceof \WP_Post ) {
			return;
		}
		// Check if block used
		$block_is_on_page = false;
		if ( function_exists( 'parse_blocks' ) ) {
			$blocks = parse_blocks( $post->post_content );
			foreach ( $blocks as $block ) {
				if ( 'tincanny/' . self::$shortcode === $block['blockName'] || 'tincanny/' . self::$shortcode === $block['blockName'] ) {
					$block_is_on_page = true;
				}
			}
		}
		// Shortcode OR block not found, bail
		if ( ! has_shortcode( $post->post_content, self::$shortcode ) && ! $block_is_on_page ) {
			return;
		}

		// Front End Questionnaire JS
		wp_register_script(
			self::$shortcode . '-js',
			self::get_admin_js( 'uo-question-report', '.js' ),
			array(
				'jquery',
				'tclr-select2',
			),
			UNCANNY_REPORTING_VERSION,
			true
		);

		// Attach API data to custom-toolkit-js
		$api_setup = array(
			'root'        => esc_url_raw( rest_url() . 'tincanny/v1/' ),
			'nonce'       => \wp_create_nonce( 'wp_rest' ),
			'currentUser' => get_current_user_id(),
			'i18n'        => array(
				'emptyTable'        => __( 'No data available in table', 'uncanny-learndash-reporting' ),
				'info'              => sprintf( _x( 'Showing %1$s to %2$s of %3$s entries', '%1$s is the start number, %2$s is the end number, and %3$s is the total number of entries', 'uncanny-learndash-reporting' ), '_START_', '_END_', '_TOTAL_' ),
				'infoEmpty'         => __( 'Showing 0 to 0 of 0 entries', 'uncanny-learndash-reporting' ),
				'infoFiltered'      => sprintf( _x( '(filtered from %s total entries)', '%s is a number', 'uncanny-learndash-reporting' ), '_MAX_' ),
				'loadingRecords'    => __( 'Loading...', 'uncanny-learndash-reporting' ),
				'processing'        => __( 'Processing...', 'uncanny-learndash-reporting' ),
				'searchPlaceholder' => __( 'Search...', 'uncanny-learndash-reporting' ),
				'zeroRecords'       => __( 'No matching records found', 'uncanny-learndash-reporting' ),
				'paginate'          => array(
					'first'    => __( 'First', 'uncanny-learndash-reporting' ),
					'last'     => __( 'Last', 'uncanny-learndash-reporting' ),
					'next'     => __( 'Next', 'uncanny-learndash-reporting' ),
					'previous' => __( 'Previous', 'uncanny-learndash-reporting' ),
				),
				'aria'              => array(
					'sortAscending'  => sprintf( ': %s', __( 'activate to sort column ascending', 'uncanny-learndash-reporting' ) ),
					'sortDescending' => sprintf( ': %s', __( 'activate to sort column descending', 'uncanny-learndash-reporting' ) ),
				),
			),
		);

		wp_localize_script( self::$shortcode . '-js', 'uoQuestionAnalysisReportSetup', $api_setup );
		wp_enqueue_script( self::$shortcode . '-js' );

		wp_enqueue_style( 'tclr-select2', Config::get_admin_css( 'select2.min.css' ), array(), UNCANNY_REPORTING_VERSION );

		wp_enqueue_script( 'tclr-select2', Config::get_admin_js( '../scripts/vendor/select2.min' ), array( 'jquery' ), UNCANNY_REPORTING_VERSION );

		wp_enqueue_script( 'datatables-script', self::get_admin_js( 'jquery.dataTables', '.min.js' ), array( 'jquery' ), false, true );

		wp_enqueue_style( self::$shortcode . '-css', self::get_admin_css( 'uo-question-report.css' ), array( 'tclr-select2' ), UNCANNY_REPORTING_VERSION );
		wp_enqueue_style( 'datatables-styles', self::get_admin_css( 'datatables.min.css' ) );

	}

	/**
	 * @return void
	 */
	public static function setup_columns() {
		self::$columns = apply_filters(
			'uo_tincanny_question_analysis_report_cols',
			array(
				'question-title'  => __( 'Question title', 'uncanny-learndash-reporting' ),
				'question'        => __( 'Question', 'uncanny-learndash-reporting' ),
				'correct-answer'  => __( 'Correct answer', 'uncanny-learndash-reporting' ),
				'correct-percent' => __( '%', 'uncanny-learndash-reporting' ),
				'times-asked'     => __( 'Times asked', 'uncanny-learndash-reporting' ),
				'avg-time'        => __( 'Average time (s)', 'uncanny-learndash-reporting' ),
			),
			__CLASS__
		);
	}

	/**
	 * @param $message
	 *
	 * @return false|string
	 */
	public static function output_notice( $message ) {
		ob_start();
		printf( wpautop( $message ) );

		return ob_get_clean();
	}

	/**
	 * @param $atts
	 *
	 * @return string
	 */
	public static function uo_question_report_func( $atts ) {
		// Is the user logged in
		if ( ! is_user_logged_in() ) {
			return self::output_notice( __( 'Please Log in to view the report.', 'uncanny-learndash-reporting' ) );
		}

		// Shortcode attributes
		$atts = shortcode_atts( array(), $atts, 'uo_question_report' );

		// Check if user is an administrator or a group leaders
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'group_leader' ) ) {
			return self::output_notice( __( 'You do not have permission to view this report.', 'uncanny-learndash-reporting' ) );
		}

		// Get Group IDs. Admin has access to all groups
		$group_ids = learndash_get_administrators_group_ids( get_current_user_id() );

		// If groups are empty and the current user IS NOT an admin, return
		if ( empty( $group_ids ) && ! current_user_can( 'manage_options' ) ) {
			return self::output_notice( __( 'You are not the admin of any group.', 'uncanny-learndash-reporting' ) );
		}

		// If there are no columns, just bail
		if ( empty( self::$columns ) ) {
			return self::output_notice( __( 'There are no columns to display.', 'uncanny-learndash-reporting' ) );
		}

		// Get quizzes of the current user
		$quizzes = self::get_quizzes( $group_ids );
		if ( empty( $quizzes ) ) {
			return self::output_notice( __( 'You do not have access to any quizzes.', 'uncanny-learndash-reporting' ) );
		}

		// Generate quiz dropdown
		self::generate_quiz_dropdown( $quizzes );
		self::generate_from_to_ranges();

		// Get selected Quiz ID
		self::$quiz_id = self::get_quiz_id();
		// Get quiz Pro ID
		self::$pro_quiz_id = self::get_quiz_pro_id( self::$quiz_id );
		if ( self::is_fresh_data_required( self::$quiz_id ) ) {
			self::$questions = self::get_quiz_questions( self::$quiz_id, self::$pro_quiz_id );
		}

		return self::display();
	}

	/**
	 * @param $quiz_id
	 *
	 * @return bool
	 */
	private static function is_fresh_data_required( $quiz_id ) {
		if ( true === apply_filters( 'uo_tincanny_question_analysis_report_disable_cache', true, __CLASS__ ) ) {
			return true;
		}
		$distractors       = self::cache_get( 'distractor_data_' . $quiz_id );
		$questions         = self::cache_get( 'questions_data_' . $quiz_id );
		$total_distractors = self::cache_get( 'total_distractors_' . $quiz_id );
		$col_headings      = self::cache_get( 'column_headings_' . $quiz_id );
		if ( empty( $questions ) || empty( $distractors ) || empty( $total_distractors ) || empty( $col_headings ) ) {
			return true;
		}
		self::$distractors       = $distractors;
		self::$total_distractors = $total_distractors;
		self::$columns           = $col_headings;
		self::$questions         = $questions;

		return false;
	}

	/**
	 * @return int
	 */
	private static function get_quiz_id() {
		return isset( $_GET['quiz-id'] ) && 0 !== $_GET['quiz-id'] ? absint( $_GET['quiz-id'] ) : 0;
	}

	/**
	 * @param $quizzes
	 *
	 * @return string
	 */
	private static function generate_quiz_dropdown( $quizzes ) {
		$drop_down = '<select id="uotc-question-report-group" name="quiz-id">';
		$drop_down .= sprintf(
			'<option value="0">%s</option>',
			sprintf(
				__( 'Select a %s', 'uncanny-learndash-reporting' ),
				\LearnDash_Custom_Label::label_to_lower( 'quiz' )
			)
		);

		foreach ( $quizzes as $quiz_id => $quiz_name ) {
			$selected         = isset( $_GET['quiz-id'] ) && 0 !== absint( $_GET['quiz-id'] ) && $quiz_id === absint( $_GET['quiz-id'] ) ? ' selected="selected"' : '';
			$quiz_id_html     = '(ID: ' . $quiz_id . ') ';
			$quiz_in_dropdown = apply_filters( 'uo_tincanny_reporting_questions_quiz_dropdown_title', "$quiz_id_html $quiz_name", $quiz_id_html, $quiz_name, $quiz_id );
			$drop_down        .= '<option value="' . $quiz_id . '" ' . $selected . '>' . $quiz_in_dropdown . '</option>';
		}
		$drop_down      .= '</select>';
		self::$dropdown = $drop_down;
	}

	/**
	 * @return void
	 */
	private static function generate_from_to_ranges() {
		$start_date = '';
		$end_date   = '';
		if ( isset( $_GET['start_date'] ) ) {
			$start_date = sanitize_text_field( $_GET['start_date'] );
		}
		if ( isset( $_GET['end_date'] ) ) {
			$end_date = sanitize_text_field( $_GET['end_date'] );
		}

		ob_start();

		?>

		<div class="uotc-question-report-date">
			<div class="uotc-question-report-date-start">
				<label for="start_date">
					<?php _e( 'Start date', 'uncanny-learndash-reporting' ); ?>
				</label>

				<input type="date" name="start_date" id="start_date" value="<?php echo $start_date; ?>">
			</div>
			<div class="uotc-question-report-date-end">
			<label for="end_date">
					<?php _e( 'End date', 'uncanny-learndash-reporting' ); ?>
				</label>

				<input type="date" name="end_date" id="end_date" value="<?php echo $end_date; ?>">
			</div>
		</div>

		<?php

		self::$date_range = ob_get_clean();
	}

	/**
	 * @param $quiz_id
	 *
	 * @return int
	 */
	private static function get_quiz_pro_id( $quiz_id ) {
		return absint( get_post_meta( $quiz_id, 'quiz_pro_id', true ) );
	}


	/**
	 * @param $quiz_id
	 * @param $pro_quiz_id
	 *
	 * @return array|object|null
	 */
	private static function get_quiz_questions( $quiz_id, $pro_quiz_id ) {
		global $wpdb;
		$ld_question_tbl = \LDLMS_DB::get_table_name( 'quiz_question' );

		$question_post_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT p.ID as question_post_ID
FROM $wpdb->posts p
INNER JOIN $wpdb->postmeta pm
ON pm.post_id = p.ID AND (pm.meta_key = %s OR ( pm.meta_key = %s AND pm.meta_value = %d ))
WHERE p.post_type = %s
AND p.post_status = %s",
				'ld_quiz_' . $quiz_id,
				'quiz_id',
				$quiz_id,
				'sfwd-question',
				'publish'
			)
		);
		if ( empty( $question_post_ids ) ) {
			return array();
		}

		$question_pro_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT meta_value FROM $wpdb->postmeta WHERE post_id IN (" . join( ', ', $question_post_ids ) . ')
AND meta_key = %s',
				'question_pro_id'
			)
		);
		$where            = apply_filters(
			'uo_tincanny_reporting_questions_query_where',
			'',
			$pro_quiz_id,
			$question_pro_ids
		);

		$qry = apply_filters(
			'uo_tincanny_reporting_questions_query',
			$wpdb->prepare(
				"SELECT p.ID AS posts_question_id, p.post_title AS post_question,
       q.id AS question_id, q.title AS question_title, q.question AS question, q.answer_data
FROM $wpdb->posts p
INNER JOIN $wpdb->postmeta pm
ON pm.post_id = p.ID AND pm.meta_key = 'question_pro_id'
JOIN $ld_question_tbl q
ON q.id = pm.meta_value AND q.answer_type = %s
WHERE p.post_type = %s
AND p.post_status = %s
AND q.id IN (" . join( ',', $question_pro_ids ) . ")
$where
ORDER BY q.sort ASC",
				'single',
				'sfwd-question',
				'publish'
			),
			$quiz_id,
			$pro_quiz_id,
			$question_pro_ids
		);

		$questions = $wpdb->get_results( $qry );

		self::get_distractors( $questions );
		self::cache_set( 'distractor_data_' . $quiz_id, self::$distractors );
		self::cache_set( 'questions_data_' . $quiz_id, $questions );
		self::cache_set( 'total_distractors_' . $quiz_id, self::$total_distractors );
		self::cache_set( 'column_headings_' . $quiz_id, self::$columns );

		return $questions;
	}

	/**
	 * @param $question_ids
	 *
	 * @return void
	 */
	private static function get_distractors( $questions ) {
		$answer_data = array_column( $questions, 'answer_data' );
		self::get_total_distractors( $answer_data );
		self::get_correct_answer_data( $questions );
		self::plot_distractor();
	}

	/**
	 * @return void
	 */
	private static function plot_distractor() {
		foreach ( self::$plot_questions_answer_data as $q_id => $data ) {
			$j = 0;
			foreach ( $data as $k => $d ) {
				if ( isset( self::$correct_answer_data[ $q_id ] ) && 1 === self::$correct_answer_data[ $q_id ][ $k ] ) {
					continue;
				}

				$answer_data = self::build_answer_data( $k, $q_id );
				$times       = self::get_times_this_answer_selected( $answer_data, $q_id );
				$q_asked     = self::get_times_question_asked( $q_id );
				$percent     = 0;
				if ( 0 !== absint( $times ) && 0 !== absint( $q_asked ) ) {
					$percent = number_format( ( $times / $q_asked ) * 100, 2 );
				}

				if ( is_object( self::$distractors ) ) {
					self::$distractors = json_decode( json_encode( self::$distractors ), true );
				}

				self::$distractors[ $q_id ][ 'distractor_' . $j ]   = $d;
				self::$distractors[ $q_id ][ 'percent_' . $j ]      = $percent;
				self::$css_class_holder[ $q_id ][ 'percent_' . $j ] = self::get_css_class( 0, true );
				if ( 0 !== absint( $times ) && 0 !== absint( $q_asked ) ) {
					self::$css_class_holder[ $q_id ][ 'percent_' . $j ] = self::get_css_class( ( $times / $q_asked ) * 100, true );
				}
				$j ++;
			}
		}
		self::$distractors = json_decode( json_encode( self::$distractors ), false );
	}

	/**
	 * @param $position
	 *
	 * @return string
	 */
	private static function build_answer_data( $position, $question_id ) {
		$j           = 0;
		$answer_data = array();
		while ( $j < count( self::$correct_answer_data[ $question_id ] ) ) {
			if ( $position === $j ) {
				$answer_data[] = 1;
			} else {
				$answer_data[] = 0;
			}
			$j ++;
		}

		return sprintf( '[%s]', join( ',', $answer_data ) );
	}

	/**
	 * @param $questions
	 *
	 * @return void
	 */
	private static function get_correct_answer_data( $questions ) {
		foreach ( $questions as $q ) {
			$answer_data                                  = maybe_unserialize( $q->answer_data );
			self::$correct_answer_data[ $q->question_id ] = self::plot_default_answer_data( $answer_data );
			self::$plot_questions_answer_data[ $q->question_id ] = self::plot_question_answer_data( $answer_data );
		}
	}

	/**
	 * @param $answer_data
	 *
	 * @return array
	 */
	private static function plot_default_answer_data( $answer_data ) {
		$array = array();
		/** @var \WpProQuiz_Model_AnswerTypes $data */
		foreach ( $answer_data as $data ) {
			if ( 1 === absint( $data->isCorrect() ) ) {
				$array[] = 1;
			} else {
				$array[] = 0;
			}
		}

		return $array;
	}

	/**
	 * @param $answer_data
	 *
	 * @return array
	 */
	private static function plot_question_answer_data( $answer_data ) {
		$array = array();
		/** @var \WpProQuiz_Model_AnswerTypes $data */
		foreach ( $answer_data as $data ) {
			$array[] = $data->getAnswer();
		}

		return $array;
	}

	/**
	 * @param $answer_data
	 */
	private static function get_total_distractors( $answer_data ) {
		$count = 0;
		foreach ( $answer_data as $ans ) {
			$ans = maybe_unserialize( $ans );
			if ( count( $ans ) > $count ) {
				$count = count( $ans );
			}
		}

		self::$total_distractors = $count - 1;
		self::add_distractor_columns();
	}

	/**
	 * @param $group_ids
	 *
	 * @return array
	 */
	private static function get_quizzes( $group_ids ) {
		$quiz_ids = array();
		if ( ! empty( $group_ids ) ) {
			foreach ( $group_ids as $group_id ) {
				$quiz_ids = array_merge( $quiz_ids, learndash_get_group_course_quiz_ids( $group_id ) );
			}
		}
		if ( current_user_can( 'manage_options' ) ) {
			$all_quiz_ids = get_posts(
				array(
					'post_type'      => 'sfwd-quiz',
					'nopaging'       => true,
					'fields'         => 'ids',
					'posts_per_page' => 99999,
					'post_status'    => 'publish',
				)
			);
			$quiz_ids     = array_merge( $quiz_ids, $all_quiz_ids );
		}
		if ( empty( $quiz_ids ) ) {
			return array();
		}
		$quiz_ids = array_unique( $quiz_ids );
		$quizzes  = array();
		foreach ( $quiz_ids as $quiz_id ) {
			$quizzes[ $quiz_id ] = ucfirst( get_the_title( $quiz_id ) );
		}
		asort( $quizzes, SORT_STRING );

		return $quizzes;
	}

	/**
	 * @param $answer_data
	 *
	 * @return string|void
	 */
	private static function get_correct_answer( $answer_data ) {
		$answer_data = maybe_unserialize( $answer_data );
		/** @var \WpProQuiz_Model_AnswerTypes $data */
		foreach ( $answer_data as $data ) {
			if ( 1 === absint( $data->isCorrect() ) ) {
				return $data->getAnswer();
			}
		}

		return '';
	}

	/**
	 * @param $question_id
	 *
	 * @return string|null
	 */
	private static function get_answered_correctly( $question_id ) {
		global $wpdb;
		$table       = \LDLMS_DB::get_table_name( 'quiz_statistic' );
		$table_ref   = \LDLMS_DB::get_table_name( 'quiz_statistic_ref' );
		$pro_quiz_id = self::$pro_quiz_id;
		$date_ranges = self::validate_date_range();
		$qry         = apply_filters(
			'uo_tincanny_reporting_questions_get_answered_correctly',
			"SELECT COUNT(*)
FROM $table s
JOIN $table_ref ref
ON s.statistic_ref_id = ref.statistic_ref_id AND ref.quiz_id = $pro_quiz_id
WHERE s.question_id = $question_id
  AND s.correct_count = 1 $date_ranges",
			$question_id,
			$table,
			$pro_quiz_id
		);

		return $wpdb->get_var( $qry );
	}

	/**
	 * @param $question_id
	 *
	 * @return string|null
	 */
	private static function get_times_question_asked( $question_id ) {
		global $wpdb;
		$table       = \LDLMS_DB::get_table_name( 'quiz_statistic' );
		$table_ref   = \LDLMS_DB::get_table_name( 'quiz_statistic_ref' );
		$pro_quiz_id = self::$pro_quiz_id;
		$date_ranges = self::validate_date_range();
		$qry         = apply_filters(
			'uo_tincanny_reporting_questions_get_times_question_asked',
			"SELECT COUNT(*)
FROM $table s
JOIN $table_ref ref
ON s.statistic_ref_id = ref.statistic_ref_id AND ref.quiz_id = $pro_quiz_id
WHERE s.question_id = $question_id
$date_ranges",
			$question_id,
			$table,
			$pro_quiz_id
		);

		return $wpdb->get_var( $qry );
	}

	/**
	 * @param $question_position
	 * @param $question_id
	 *
	 * @return string|null
	 */
	private static function get_times_this_answer_selected( $question_position, $question_id ) {
		global $wpdb;
		$table       = \LDLMS_DB::get_table_name( 'quiz_statistic' );
		$table_ref   = \LDLMS_DB::get_table_name( 'quiz_statistic_ref' );
		$pro_quiz_id = self::$pro_quiz_id;
		$date_ranges = self::validate_date_range();
		$qry         = apply_filters(
			'uo_tincanny_reporting_questions_get_times_this_answer_selected',
			"SELECT COUNT(*)
FROM $table s
JOIN $table_ref ref
ON s.statistic_ref_id = ref.statistic_ref_id  AND ref.quiz_id = $pro_quiz_id
WHERE s.question_id = $question_id
  AND s.answer_data LIKE '$question_position'
  AND s.incorrect_count = 1
  $date_ranges",
			$question_id,
			$question_position,
			$table,
			$pro_quiz_id
		);

		return $wpdb->get_var( $qry );
	}

	/**
	 * @param $question_id
	 *
	 * @return string|null
	 */
	private static function get_avg_time( $question_id ) {
		global $wpdb;
		$table       = \LDLMS_DB::get_table_name( 'quiz_statistic' );
		$table_ref   = \LDLMS_DB::get_table_name( 'quiz_statistic_ref' );
		$pro_quiz_id = self::$pro_quiz_id;
		$date_ranges = self::validate_date_range();
		$qry         = apply_filters(
			'uo_tincanny_reporting_questions_get_avg_time',
			"SELECT
    AVG(s.question_time)
FROM $table s
JOIN $table_ref ref
ON s.statistic_ref_id = ref.statistic_ref_id AND ref.quiz_id = $pro_quiz_id
WHERE s.question_id = $question_id
$date_ranges",
			$question_id,
			$table,
			$pro_quiz_id
		);

		return number_format( $wpdb->get_var( $qry ), 2 );
	}

	/**
	 * @param $question_id
	 * @param bool $percent_only
	 *
	 * @return string|null
	 */
	private static function get_question_total_percent( $question_id, $percent_only = false ) {
		$answered_correctly = self::get_answered_correctly( $question_id );
		$total              = self::get_times_question_asked( $question_id );
		if ( 0 === absint( $answered_correctly ) || 0 === absint( $total ) ) {
			return 0;
		}
		$percent = ( $answered_correctly / $total ) * 100;
		if ( $percent_only ) {
			return $percent;
		}
		$formatted = number_format( $percent, 2 );

		return apply_filters(
			'uo_tincanny_reporting_questions_get_question_total_percent',
			$formatted,
			$question_id,
			$answered_correctly,
			$total,
			$percent
		);
	}

	/**
	 * @return void
	 */
	private static function add_distractor_columns() {
		$cols = array();
		$i    = 1;
		while ( $i <= self::$total_distractors ) {
			$cols[ 'distractor-' . $i ]         = sprintf( '%s %s', apply_filters( 'uo_tincanny_question_analysis_report_distractor_label', __( 'Distractor', 'uncanny-learndash-reporting' ) ), $i );
			$cols[ 'distractor-percent-' . $i ] = __( '%', 'uncanny-learndash-reporting' );
			$i ++;
		}
		$colll         = self::$columns;
		$end_array     = array_slice( $colll, - 2, 2, true );
		$start_array   = array_slice( $colll, 0, count( $colll ) - 2, true );
		self::$columns = array_merge( $start_array, $cols, $end_array );
	}

	/**
	 * @param $drop_down
	 *
	 * @return false|string
	 */
	public static function display() {
		ob_start();
		?>
		<div class="uotc-question-report" id="uotc-group-report">
			<div class="uotc-question-report__header">
				<div class="uotc-question-report__selects">

					<div id="uotc-question-report-selections">
						<form method="get">
							<?php echo self::$dropdown; ?>
							<?php echo self::$date_range; ?>

							<?php do_action( 'uo_tincanny_reporting_questions_dropdowns', self::$questions, self::$quiz_id, self::$pro_quiz_id, __CLASS__ ); ?>
							<p>
								<input type="submit" value="Search"/>
							</p>
						</form>
					</div>
				</div>
			</div>
			<div class="uotc-question-report__table">
				<?php
				$display = 1;
				if ( empty( self::$questions ) ) {
					$display = 0;
				}
				if ( ! isset( $_GET['quiz-id'] ) ) {
					$display = 0;
				}
				// Placeholder code in case the table needs to be hidden for a logic
				if ( 1 !== $display ) {
					if ( ! isset( $_GET['quiz-id'] ) ) {
						echo wpautop( __( 'Please select a quiz first.', 'uncanny-learndash-reporting' ) );
						echo '</div>';

						return ob_get_clean();
					}
					if ( empty( self::$questions ) ) {
						echo wpautop( __( 'There are no multiple choice questions in this quiz.', 'uncanny-learndash-reporting' ) );
						echo '</div>';

						return ob_get_clean();
					}
				}
				$question_results_sorted = self::get_x_of_y_css_classes();
				$questions               = self::$questions;
				?>
				<table id="uotc-question-report" class="display responsive" cellspacing="0" width="100%">
					<thead>
					<tr>
						<?php foreach ( self::$columns as $id => $col ) { ?>
							<th class="report-header <?php echo sanitize_title( $id ); ?>"><?php echo $col; ?></th>
						<?php } ?>
					</tr>
					</thead>
					<tbody>
					<?php foreach ( $questions as $question ) { ?>
						<tr>
							<!-- Question Title -->
							<td class="report-data question-title"><?php echo $question->question_title; ?></td>
							<!-- Question -->
							<td class="report-data question-question"><?php echo wp_strip_all_tags( $question->question ); ?></td>
							<!-- Correct Answer -->
							<?php
							$question_id = $question->question_id;
							$percent     = self::get_question_total_percent( $question->question_id );
							$raw_percent = self::get_question_total_percent( $question->question_id, true );
							$css         = self::get_css_class( $raw_percent );
							$css         = self::find_position_of_data( $percent, $question_results_sorted[ $question_id ] ) . ' ' . $css;
							?>
							<td class="report-data correct-answer"><?php echo self::get_correct_answer( $question->answer_data ); ?></td>
							<!-- % -->
							<td class="report-data correct-percent">
								<span class="<?php echo $css; ?>">
									<?php echo sprintf( '%s%%', $percent ); ?>
								</span>
							</td>
							<?php
							if ( ! empty( self::$distractors->$question_id ) ) {
								?>
								<?php $i = 0; ?>
								<?php $j = 0; ?>
								<?php
								while ( $i <= self::$total_distractors ) {
									if ( isset( self::$correct_answer_data[ $question_id ] ) && isset( self::$correct_answer_data[ $question_id ][ $i ] ) && 1 === self::$correct_answer_data[ $question_id ][ $i ] ) {
										$i ++;
										continue;
									}
									$distractor_key = "distractor_{$j}";
									$percent_key    = "percent_{$j}";
									$css            = isset( self::$css_class_holder[ $question_id ][ $percent_key ] ) ? self::$css_class_holder[ $question_id ][ $percent_key ] : '';
									$distractor     = isset( self::$distractors->$question_id->$distractor_key ) ? self::$distractors->$question_id->$distractor_key : '-';
									$percent        = isset( self::$distractors->$question_id->$percent_key ) ? self::$distractors->$question_id->$percent_key : '-';
									$css            = self::find_position_of_data( $percent, $question_results_sorted[ $question_id ] ) . ' ' . $css;
									?>
									<!-- Distractor 1 -->
									<td class="report-data distractor-<?php echo $j; ?>"><?php echo $distractor; ?></td>
									<!-- % 1 -->
									<td class="report-data distractor-percent-<?php echo $j; ?>">
										<span class="<?php echo $css; ?>">
											<?php $__percent = $percent; ?>
											<?php echo sprintf( '%s%%', $__percent ); ?>
										</span>
									</td>
									<?php $i ++; ?>
									<?php $j ++; ?>
								<?php } ?>
							<?php } ?>
							<!-- Times Question Asked -->
							<td class="report-data times-asked"><?php echo self::get_times_question_asked( $question->question_id ); ?></td>
							<!-- Average time -->
							<td class="report-data avg-time"><?php echo self::get_avg_time( $question->question_id ); ?></td>
							<!-- Question ID -->
							<!--                            <td>-->
							<?php //echo $question->posts_question_id; ?><!--</td>-->
						</tr>
					<?php } ?>
					</tbody>
					<tfoot>
					<tr>
						<?php foreach ( self::$columns as $col ) { ?>
							<th><?php echo $col; ?></th>
						<?php } ?>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * @param $key
	 * @param $data
	 *
	 * @return void
	 */
	private static function cache_set( $key, $data ) {
		if ( wp_using_ext_object_cache() ) {
			wp_cache_set( $key, $data, 'tincanny', 300 );

			return;
		}
		set_transient( $key, $data, 300 );
	}

	/**
	 * @param $key
	 *
	 * @return false|mixed
	 */
	private static function cache_get( $key ) {
		if ( wp_using_ext_object_cache() ) {
			return wp_cache_get( $key, 'tincanny' );

		}

		return get_transient( $key );
	}

	/**
	 * @param $percent
	 * @param $inverted
	 *
	 * @return string|void
	 */
	private static function get_css_class( $percent, $inverted = false ) {
		$percent = ceil( $percent );
		$css     = '';
		if ( $inverted ) {
			$css = ' uotc-table-color-scale--inverted percent-' . $percent;
		}
		if ( $percent >= 0 && $percent <= 10 ) {
			return 'uotc-table-color-scale uotc-table-color-scale--percentile-0-10' . $css;
		}
		if ( $percent >= 11 && $percent <= 20 ) {
			return 'uotc-table-color-scale uotc-table-color-scale--percentile-10-20' . $css;
		}
		if ( $percent >= 21 && $percent <= 30 ) {
			return 'uotc-table-color-scale uotc-table-color-scale--percentile-20-30' . $css;
		}
		if ( $percent >= 31 && $percent <= 40 ) {
			return 'uotc-table-color-scale uotc-table-color-scale--percentile-30-40' . $css;
		}
		if ( $percent >= 41 && $percent <= 50 ) {
			return 'uotc-table-color-scale uotc-table-color-scale--percentile-40-50' . $css;
		}
		if ( $percent >= 51 && $percent <= 60 ) {
			return 'uotc-table-color-scale uotc-table-color-scale--percentile-50-60' . $css;
		}
		if ( $percent >= 61 && $percent <= 70 ) {
			return 'uotc-table-color-scale uotc-table-color-scale--percentile-60-70' . $css;
		}
		if ( $percent >= 71 && $percent <= 80 ) {
			return 'uotc-table-color-scale uotc-table-color-scale--percentile-70-80' . $css;
		}
		if ( $percent >= 81 && $percent <= 90 ) {
			return 'uotc-table-color-scale uotc-table-color-scale--percentile-80-90' . $css;
		}
		if ( $percent >= 91 && $percent <= 100 ) {
			return 'uotc-table-color-scale uotc-table-color-scale--percentile-90-100' . $css;
		}
	}

	/**
	 * @return array
	 */
	private static function get_x_of_y_css_classes() {
		$questions = self::$questions;
		$data      = array();
		foreach ( $questions as $question ) {
			$p                                = number_format( self::get_question_total_percent( $question->question_id, true ), 2 );
			$data[ $question->question_id ][] = $p;
			$question_id                      = $question->question_id;
			if ( ! empty( self::$distractors->$question_id ) ) {
				$i = 0;
				$j = 0;

				while ( $i <= self::$total_distractors ) {
					if ( isset( self::$correct_answer_data[ $question_id ] ) && isset( self::$correct_answer_data[ $question_id ][ $i ] ) && 1 === self::$correct_answer_data[ $question_id ][ $i ] ) {
						$i ++;
						continue;
					}
					$percent_key                          = "percent_{$j}";
					$percent                              = isset( self::$distractors->$question_id->$percent_key ) ? self::$distractors->$question_id->$percent_key : '-';
					$data[ $question->question_id ][ $i ] = $percent;
					$j ++;
					$i ++;
				}
				rsort( $data[ $question_id ], SORT_NUMERIC );
			}
		}

		return $data;
	}

	/**
	 * @param $percent
	 * @param $question_results_sorted
	 *
	 * @return string
	 */
	private static function find_position_of_data( $percent, $question_results_sorted ) {
		$total    = self::$total_distractors + 1;
		$position = array_search( $percent, $question_results_sorted );
		$position = $position + 1;

		return 'uotc-table-color-scale--' . $position . '-of-' . $total;
	}

	/**
	 * @return string
	 */
	private static function validate_date_range() {
		if ( ! isset( $_GET['start_date'] ) && ! isset( $_GET['end_date'] ) ) {
			return '';
		}
		if ( empty( $_GET['start_date'] ) ) {
			return '';
		}
		$start_date = sanitize_text_field( $_GET['start_date'] );
		$end_date   = sanitize_text_field( $_GET['end_date'] );
		if ( empty( $end_date ) ) {
			$end_date = date( 'Y-m-d' );
		}
		$start_date_timestamp = strtotime( $start_date );
		$end_date_timestamp   = strtotime( $end_date );

		return " AND ref.create_time BETWEEN $start_date_timestamp AND $end_date_timestamp ";
	}
}
