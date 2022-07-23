jQuery( document ).ready( function( $ ) {

var LD_Achievements = LD_Achievements || {};

LD_Achievements.admin = {
	init: function() {
		this.toggle_child_input();
		this.select_image();
		this.settings_page();
		this.submit_disabled_fields();
		this.ajax_get_children_list();
	},

	toggle_child_input: function() {
		if ( $( '.ld_achievements_metabox_settings' ).length > 0 ) {
			$( 'select[name="trigger"]' ).change( function( e ) {
				LD_Achievements.admin.update_select_values();

				var option_class = $( this ).val();

				$( '.sfwd_input.' + option_class ).show();
				$( '.sfwd_input.child-input' ).not( '.' + option_class ).hide();
				$( '.sfwd_input.hide_on_' + option_class ).hide();
			});

			$( window ).load( function( e ) {
				LD_Achievements.admin.update_select_values_onload();

				var option_class = $( 'select[name="trigger"]' ).val();

				$( '.sfwd_input.' + option_class ).show();
				$( '.sfwd_input.child-input' ).not( '.' + option_class ).hide();
				$( '.sfwd_input.hide-empty-select' ).hide();
			});
		}
	},

	select_image: function()
	{
		if ( $( '#image-field' ).length == 0 ) {
			return
		}

		var image_field = $( '#image-field' );
		var image_preview_holder = $( '#image-preview-holder' );
		var image_preview = $( '#image-preview-holder img' );
		var image_selector_buttons = $( '.image-selector-buttons' );
		var icon_selection = $( '.icon-selection' );

		$( document ).on( 'click', '.select-image-btn', function(e) {
			e.preventDefault();
			icon_selection.toggle();
		});

		$( window ).load( function() {
			var image = $( '#image-field' ).val();
			var icon  = $( 'img.radio-btn[src="' + image + '"]' );

			if ( image.length === 0 && icon.length > 0 ) {
				icon.addClass( 'selected' );
				$( '.icon-selection' ).show();
			} else if ( image.length > 0 ) {
				image_selector_buttons.hide();

				image_field.val( image );
				image_preview.attr(	'src', image );
				image_preview_holder.show();
			}
		});

		$( document ).on( 'click', '.icon-selection .radio-btn', function(e) {
			e.preventDefault();
			$( '.icon-selection input[type=radio]' ).removeAttr( 'checked' );
			$( '.icon-selection .radio-btn' ).removeClass('selected');

			$( this ).prev().attr( 'checked', 'checked' );
			$( this ).addClass( 'selected' );
			$( '#image-field' ).val( $( this ).attr( 'src' ) );
		});

		var uploader;

		$( document ).on( 'click', '#upload-image', function(e) {
			e.preventDefault();

			if( uploader ){
				uploader.open();
				return;
			}

			uploader = wp.media.frames.file_frame = wp.media({
				title: 'Choose Image',
				button: {
					text: 'Choose Image'
				},
				multiple: false
			});

			uploader.on( 'select', function() {
				attachment = uploader.state().get( 'selection' ).first().toJSON();
				image_field.val( attachment.url );
				image_preview.attr(	'src', attachment.url );
				image_preview_holder.show();

				$( '.radio-btn.selected' ).removeClass( 'selected' );
				image_selector_buttons.hide();
				icon_selection.hide();
			});

			uploader.open();
		});

		$( document ).on( 'click', '#remove-image-btn', function( e ) {
			e.preventDefault();

			image_preview_holder.hide();
			image_preview.attr( 'src', '' );
			image_field.val( '' );

			image_selector_buttons.show();
		});
	},

	settings_page: function () {
		$( '.color-picker' ).wpColorPicker();
	},

	submit_disabled_fields: function() {
		$( 'form' ).on( 'submit', function() {
			$( this ).find( ':input' ).prop( 'disabled', false );
		});
	},

	ajax_get_children_list: function() {
		$( '.parent_field select' ).change( function( e ) {

			var el = $( this );
			var parent_type = '';
			var val  = $( this ).val();
			var name = $( this ).attr( 'name' );

			switch ( name ) {
				case 'course_id':
					parent_type = 'course';
					break;
				case 'lesson_id':
					parent_type = 'lesson';
					break;
				case 'topic_id':
					parent_type = 'topic';
					break;
			}

			var course_id = $( 'select[name="course_id"]' ).val();

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'ld_achievements_get_children_list',
					course_id: course_id,
					parent_type: parent_type,
					parent_id: val,
					nonce: LD_Achievements_String.nonce,
				},
			})
			.done( function( data, textStatus, jqXHR ) {
				var response = data;
				response = JSON.parse( response );
				console.log( response );

				if ( el.attr( 'name' ).indexOf( 'course' ) != '-1' ) {
					$( 'select[name="topic_id"]' ).html(
						'<option>' + LD_Achievements_String.select_lesson_first + '</option>'
					);

					$( 'select[name="quiz_id"]' ).html(
						'<option>' + LD_Achievements_String.select_topic_first + '</option>'
					);

					$( 'select[name="lesson_id"]' ).html(
						'<option>' + LD_Achievements_String.select_lesson + '</option>' +
						'<option value="all">' + LD_Achievements_String.all_lessons + '</option>'
					);

					$.each( response, function( i, val ) {
						$( 'select[name="lesson_id"]' ).append( '<option value="' + i + '">' + val + '</option>' );
					});
				}

				if ( el.attr( 'name' ).indexOf( 'lesson' ) != '-1' ) {
					$( 'select[name="quiz_id"]' ).html(
						'<option>' + LD_Achievements_String.select_topic_first + '</option>'
					);

					$( 'select[name="topic_id"]' ).html(
						'<option>' + LD_Achievements_String.select_topic + '</option>' +
						'<option value="all">' + LD_Achievements_String.all_topics + '</option>'
					);

					$.each( response, function( i, val ) {
						$( 'select[name="topic_id"]' ).append( '<option value="' + i + '">' + val + '</option>' );
					});
				}

				if ( el.attr( 'name' ).indexOf( 'topic' ) != '-1' ) {
					$( 'select[name="quiz_id"]' ).html(
						'<option>' + LD_Achievements_String.select_quiz + '</option>' +
						'<option value="all">' + LD_Achievements_String.all_quizzes + '</option>'
					);

					$.each( response, function( i, val ) {
						$( 'select[name="quiz_id"]' ).append( '<option value="' + i + '">' + val + '</option>' );
					});
				}
			});

		});
	},

	update_select_values: function() {
		$( 'select[name="course_id"]' ).prop( 'selectedIndex', 0 );

		$( 'select[name="lesson_id"]' ).html(
			'<option>' + LD_Achievements_String.select_course_first + '</option>'
		);

		$( 'select[name="topic_id"]' ).html(
			'<option>' + LD_Achievements_String.select_lesson_first + '</option>'
		);

		$( 'select[name="quiz_id"]' ).html(
			'<option>' + LD_Achievements_String.select_topic_first + '</option>'
		);
	},

	update_select_values_onload: function() {
		if ( $( 'select[name="course_id"]' ).val() === '' ) {
			$( 'select[name="course_id"]' ).prop( 'selectedIndex', 0 );
		}

		if ( $( 'select[name="lesson_id"]' ).val() === '' ) {
			$( 'select[name="lesson_id"]' ).html(
				'<option>' + LD_Achievements_String.select_course_first + '</option>'
			);
		}

		if ( $( 'select[name="topic_id"]' ).val() === '' ) {
			$( 'select[name="topic_id"]' ).html(
				'<option>' + LD_Achievements_String.select_lesson_first + '</option>'
			);
		}

		if ( $( 'select[name="quiz_id"]' ).val() === '' ) {
			$( 'select[name="quiz_id"]' ).html(
				'<option>' + LD_Achievements_String.select_topic_first + '</option>'
			);
		}
	}
};

LD_Achievements.admin.init();

} );
