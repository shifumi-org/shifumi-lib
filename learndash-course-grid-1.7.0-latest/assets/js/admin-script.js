jQuery( document ).ready( function( $ ) {
	function learndash_course_grid_course_edit_page_javascript() {
		$( "select[name=sfwd-courses_course_price_type]" ).change( function() {
			var price_type = $( "select[name=sfwd-courses_course_price_type]" ).val();
			if ( price_type == "closed" ) 
				$( "#sfwd-courses_course_price" ).show();
		} );
		$( "select[name=sfwd-courses_course_price_type]" ).change();
	}
	if( $( ".sfwd-courses_settings" ).length )
	setTimeout( function() {learndash_course_grid_course_edit_page_javascript();}, 1000 );
} );
