jQuery( document ).ready( function( $ ) {
	$( '.ld_course_grid_video_embed > *' ).each( function( index, el ) {
		var height = $( this ).outerWidth() * 0.75;
		$( this ).css( 'height', height );

		var wrapper_height = $( this ).parent().outerWidth() * 0.75;
		$( this ).parent().css( 'height', wrapper_height );
	});
});