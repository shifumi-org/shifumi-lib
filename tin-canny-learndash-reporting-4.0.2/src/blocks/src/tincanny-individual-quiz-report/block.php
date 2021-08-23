<?php 

/**
 * Register Tin Canny Course/User Report and
 * render it with a callback function
 */

register_block_type( 'tincanny/user-quiz-report', [
	'render_callback' => 'render_uo_individual_quiz_report'
]);

function render_uo_individual_quiz_report(){
	$output = '';

	if ( class_exists( '\uncanny_learndash_reporting\QuizModuleReports' ) ){
		$output = new uncanny_learndash_reporting\QuizModuleReports();
		$output = $output->user_quiz_report();
	}

	return $output;
}