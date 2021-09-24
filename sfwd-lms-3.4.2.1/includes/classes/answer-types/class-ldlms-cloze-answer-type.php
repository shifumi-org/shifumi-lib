<?php
/**
 * Class for getting answers and student nodes for `fill in the blank` type questions.
 *
 * @since 3.3.0
 * @package Learndash\Question\Cloze
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'LDLMS_Cloze_Answer' ) ) {

	/**
	 * Class LDLMS_Sort_Answer
	 *
	 * @package Learndash
	 */
	class LDLMS_Cloze_Answer extends LDLMS_Base_Answer_Type {

		/**
		 * This type of question will have answer_text in response object.
		 *
		 * @var bool
		 */
		public $has_answer_text = true;

		/**
		 * Parsed list of answers for a question.
		 *
		 * @var array
		 */
		private $parsed_answers;

		/**
		 * LDLMS_Cloze_Answer constructor.
		 *
		 * @param WpProQuiz_Model_Question          $question        Question model object.
		 * @param string                            $student_answers Submitted answers' list.
		 * @param WpProQuiz_Model_StatisticRefModel $stat_ref_model  Statistic reference model.
		 */
		public function __construct( WpProQuiz_Model_Question $question, $student_answers, WpProQuiz_Model_StatisticRefModel $stat_ref_model ) {
			parent::__construct( $question, $student_answers, $stat_ref_model );

			$this->parsed_answers = $this->parse_answers();
		}

		/**
		 * Override parent function call.
		 */
		public function setup() {
			parent::setup();
			add_filter( 'learndash_rest_statistic_answer_node_data', array( $this, 'student_answers_value_key' ), 30, 5 );
		}

		/**
		 * Get answers data in the form of array.
		 *
		 * @return array
		 */
		public function get_answers() {
			$answers = array();

			foreach ( $this->parsed_answers as $key => $answer ) {
				$answers[ $this->get_answer_key( $key ) ] = array(
					'label' => $answer['label'],
				);

				$answer_node_data = $answers[ $this->get_answer_key( (string) $key ) ];

				/**
				 * Filters the individual answer node.
				 *
				 * @since 3.3.0
				 *
				 * @param array  $answer_node_data The answer node.
				 * @param string $type             Whether the node is answer node or student answer node.
				 * @param mixed  $data             Individual answer data.
				 */
				$answer_node_data = apply_filters(
					'learndash_rest_statistic_answer_node_data',
					$answer_node_data,
					'answer',
					$answer,
					$this->question->getId(),
					$key
				);

				$answers[ $this->get_answer_key( (string) $key ) ] = $answer_node_data;

			}

			return $answers;
		}

		/**
		 * Get student's answers' response.
		 *
		 * @return array
		 */
		public function get_student_answers() {
			$answers = array();

			foreach ( $this->student_answers as $key => $answer ) {
				$answers[] = array(
					'answer_key' => $this->get_answer_key( $key ),
					'answer'     => $answer,
					'correct'    => in_array( strtolower( $answer ), array_map( 'strtolower', $this->parsed_answers[ $key ]['label'] ), true ),
				);

				$answers[ $key ] = apply_filters(
					'learndash_rest_statistic_answer_node_data',
					$answers[ $key ],
					'student',
					$this->parsed_answers[ $key ],
					$this->question->getId(),
					$key
				);
			}

			return $answers;
		}

		/**
		 * If individual answers points are activated, add points field
		 * to each answer.
		 *
		 * @param array  $answer_data Answer Data.
		 * @param string $answer_type Type of answer node.
		 * @param array  $answer      Answer info.
		 * @param int    $question_id Question ID.
		 *
		 * @return array
		 */
		public function maybe_add_points( array $answer_data, $answer_type, $answer, $question_id ) {

			if ( ( $question_id === $this->question->getId() ) && $this->question->isAnswerPointsActivated() ) {
				$answer_data['points'] = $answer['points'];
			}

			return $answer_data;
		}

		/**
		 * Add the `value_key` to answer data.
		 * Also, if label is not required, omit it.
		 *
		 * @param array                       $answer_data       Answer Data.
		 * @param string                      $answer_type       Type of answer node.
		 * @param WpProQuiz_Model_AnswerTypes $answer_type_model Answer type model object.
		 * @param int                         $question_id       Question ID.
		 * @param int                         $key               Position of answer.
		 *
		 * @return array
		 */
		public function student_answers_value_key( array $answer_data, $answer_type, $answer_type_model, $question_id, $key = 0 ) {
			if ( $question_id !== $this->question->getId() ) {
				return $answer_data;
			}

			switch ( $answer_type ) {

				case 'answer':
					unset( $answer_data['label'] );
					unset( $answer_data['points'] );
					break;
			}

			return $answer_data;
		}

		/**
		 * Parse the answer in array form from the answer markup.
		 *
		 * @return array List of parsed answers.
		 */
		private function parse_answers() {
			$answer = array();

			foreach ( $this->answer_data as $index => $answer_data ) {
				$no_markup_answer = wp_strip_all_tags( $answer_data->getAnswer() );
				preg_match_all( '#\{(.*?)(?:\|(\d+))?(?:[\s]+)?\}#im', $no_markup_answer, $matches );

				if ( $matches ) {

					$points_list = array_map(
						function ( $point ) {
							return empty( $point ) ? 1 : intval( $point );
						},
						$matches[2]
					);

					$answer_fillers = array_map(
						function( $val ) {

							preg_match_all( '#\[([^\]]*)\]#im', $val, $ans_labels );

							if ( ! empty( $ans_labels[1] ) ) {
								return $ans_labels[1];
							}

							return array( $val );
						},
						$matches[1]
					);

					foreach ( $answer_fillers as $key => $val ) {
						$answer[] = array(
							'label'  => $val,
							'points' => $points_list[ $key ],
						);
					}
				}
			}

			return $answer;
		}
	}
}
