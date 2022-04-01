<?php

/**
 * Register Tin Canny Course/User Report and
 * render it with a callback function
 */

register_block_type( 'tincanny/group-quiz-report', [
	'attributes'      => [
		'user_report_url' => [
			'type'    => 'string',
			'default' => '',
		],
	],
	'render_callback' => 'render_uo_group_quiz_report'
] );

function render_uo_group_quiz_report( $attributes ) {
	$output = '';

	if ( class_exists( '\uncanny_learndash_reporting\QuizModuleReports' ) ) {
		$output = new uncanny_learndash_reporting\QuizModuleReports();
		$output = $output->group_quiz_report(
			[
				'user_report_url' => $attributes['user_report_url'],
			]
		);
	}

	return $output;
}