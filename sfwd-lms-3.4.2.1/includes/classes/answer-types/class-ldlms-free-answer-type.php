<?php
/**
 * Class for getting answers and student nodes for `free` type questions.
 *
 * @since 3.3.0
 * @package Learndash\Question\Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'LDLMS_Free_Answer' ) ) {

	/**
	 * Class LDLMS_Sort_Answer
	 *
	 * @package Learndash
	 */
	class LDLMS_Free_Answer extends LDLMS_Base_Answer_Type {
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
		 * Override setup method in parent.
		 *
		 * @return void
		 */
		public function setup() {
			parent::setup();

			remove_filter( 'learndash_rest_statistic_answer_node_data', array( $this, 'maybe_add_points' ), 10 );
			add_filter( 'learndash_rest_statistic_answer_node_data', array( $this, 'student_answers_value_key' ), 30, 5 );
		}

		/**
		 * Get answers data in the form of array.
		 *
		 * @return array
		 */
		public function get_answers() {
			$answers = array();

			/**
			 * As of now, there can only be one field for free answer question.
			 * So, there will always be one answer node.
			 */
			$answers[ $this->get_answer_key( '0' ) ] = array(
				'label' => $this->parsed_answers,
			);

			$answers[ $this->get_answer_key( '0' ) ] = apply_filters(
				'learndash_rest_statistic_answer_node_data',
				$answers[ $this->get_answer_key( '0' ) ],
				'answer',
				array(),
				$this->question->getId(),
				0
			);

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
				$ans_key = array_search( strtolower( $answer ), $this->parsed_answers, true );

				$answers[] = array(
					'answer_key' => ( false !== $ans_key ) ? $this->get_answer_key( (string) $ans_key ) : '',
					'answer'     => $answer,
					'correct'    => (bool) ( false !== $ans_key ),
				);

				$answers[ count( $answers ) - 1 ] = apply_filters(
					'learndash_rest_statistic_answer_node_data',
					$answers[ count( $answers ) - 1 ],
					'student',
					array(),
					$this->question->getId(),
					$key
				);
			}

			return $answers;
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
			$answer           = array();
			$possible_answers = explode( PHP_EOL, $this->answer_data[0]->getAnswer() );

			if ( ! empty( $possible_answers ) ) {
				foreach ( $possible_answers as $ans ) {
					$answer[] = trim( strtolower( $ans ) );
				}
			}

			return array_filter( $answer, 'strlen' );
		}
	}
}
