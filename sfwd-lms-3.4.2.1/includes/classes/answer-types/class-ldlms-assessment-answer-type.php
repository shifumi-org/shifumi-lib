<?php
/**
 * Class for getting answers and student nodes for `assessment` type questions.
 *
 * @since 3.3.0
 * @package Learndash\Question\Assessment
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'LDLMS_Assessment_Answer' ) ) {

	/**
	 * Class LDLMS_Sort_Answer
	 *
	 * @package Learndash
	 */
	class LDLMS_Assessment_Answer extends LDLMS_Base_Answer_Type {

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
		 * LDLMS_Assessment_Answer constructor.
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
		 * Get answers data in the form of array.
		 *
		 * @return array
		 */
		public function get_answers() {
			$answers = array();

			foreach ( $this->parsed_answers as $key => $answer ) {
				$answers[ $this->get_answer_key( $key ) ] = array(
					'label'  => $answer,
					'points' => ( intval( $key ) + 1 ),
				);
			}

			return $answers;
		}

		/**
		 * Get student's answers' response.
		 *
		 * @return array
		 */
		public function get_student_answers() {
			$student_answers = array();

			if ( $this->student_answers ) {
				$student_answer = $this->student_answers[0] ? $this->student_answers[0] : 1;
				$student_answer = intval( $student_answer ) - 1;

				foreach ( $this->parsed_answers as $key => $val ) {

					if ( $student_answer === $key ) {
						$student_answers = array(
							'answer_key' => $this->get_answer_key( $key ),
							'answer'     => $this->get_answer_key( $key ),
							'points'     => ( intval( $key ) + 1 ),
						);
					}
				}
			}

			return $student_answers;
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
				preg_match_all( '#\{(.*?)\}#im', $no_markup_answer, $matches );

				foreach ( $matches[1] as $match ) {
					preg_match_all( '#\[([^\|\]]+)(?:\|(\d+))?\]#im', $match, $ms );

					/**
					 * Currently only one set of answers are supported by LD.
					 * So as soon as we get matches, we assign and break the loop.
					 */
					if ( ! empty( $ms[1] ) ) {
						$answer = $ms[1];
						break;
					}
				}
			}

			return $answer;
		}
	}
}
