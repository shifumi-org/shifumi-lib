<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LearnDash Import CPT
 *
 * This file contains functions to handle import of the LearnDash CPT Topic
 *
 * @package LearnDash
 * @subpackage LearnDash
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( ! class_exists( 'LearnDash_Import_Quiz_Question' ) ) && ( class_exists( 'LearnDash_Import_Post' ) ) ) {
	class LearnDash_Import_Quiz_Question extends LearnDash_Import_Post {
		private $version = '1.0';

		public function __construct() {
		}

		public function startQuizQuestionSet() {
			$pro_quiz_question_import = new WpProQuiz_Model_Question();

			return $pro_quiz_question_import->get_object_as_array();
		}

		public function saveQuizQuestionSet( $quiz_question_data = array() ) {
			if ( ! empty( $quiz_question_data ) ) {

				// Called to ensure we have a working Question Set ( WpProQuiz_Model_Question )
				$pro_quiz_question_import = new WpProQuiz_Model_Question();
				$pro_quiz_question_import->set_array_to_object( $quiz_question_data );

				$quiz_question_mapper = new WpProQuiz_Model_QuestionMapper();
				$new_question         = $quiz_question_mapper->save( $pro_quiz_question_import );
				if ( is_a( $new_question, 'WpProQuiz_Model_Question' ) ) {
					return $new_question->getId();
				}
			}
		}

		public function startQuizQuestionAnswerTypesSet() {
			$pro_quiz_question_answer_types_import = new WpProQuiz_Model_AnswerTypes();

			return $pro_quiz_question_answer_types_import->get_object_as_array();
		}

		// End of functions
	}
}
