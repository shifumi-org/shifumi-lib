<?php 

/**
 * Register Tin Canny Course/User Report and
 * render it with a callback function
 */

register_block_type( 'tincanny/course-user-report', [
	'render_callback' => 'render_tincanny_course_user_report'
]);

function render_tincanny_course_user_report( $attributes ){
	$output = '';

	if ( class_exists( '\uncanny_learndash_reporting\ReportingAdminMenu' ) ){
		$output = uncanny_learndash_reporting\ReportingAdminMenu::frontend_tincanny_block();
	}

	return $output;
}