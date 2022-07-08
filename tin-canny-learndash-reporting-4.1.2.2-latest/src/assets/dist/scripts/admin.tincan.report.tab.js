/**
 * @package Uncanny TinCan API
 * @author Uncanny Owl
 * @version 1.0.0
 */

jQuery( document ).ready( function($) {
	// Per Page
	$( '#tincan-filters-per_page select' ).change( function() {
		console.log('changed per page select');
		var value = $(this).find( 'option:selected' ).val();

		insertParam( 'per_page', value );
	});

	// <!-- Change Date Range Type
	$( '#tincan-filters-top #tc_filter_date_range_last' ).click( function() {
		$( '#tincan-filters-top input[name="tc_filter_date_range"][value="last"]' ).click();
	});

	$( '#tincan-filters-top input[name="tc_filter_start"], #tincan-filters-top input[name="tc_filter_end"]' ).click( function() {
		$( '#tincan-filters-top input[name="tc_filter_date_range"][value="from"]' ).click();
	});

	$( '#xapi-filters-top #tcx_filter_date_range_last' ).click( function() {
		$( '#xapi-filters-top input[name="tc_filter_date_range"][value="last"]' ).click();
	});

	$( '#xapi-filters-top input[name="tc_filter_start"], #xapi-filters-top input[name="tc_filter_end"]' ).click( function() {
		$( '#xapi-filters-top input[name="tc_filter_date_range"][value="from"]' ).click();
	});
	// Change Date Range Type -->

	// CSV Export
	$( '#do_tc_export_csv' ).click( function( e ) {
		var fields = [ 'tc_filter_group', 'tc_filter_user', 'tc_filter_course', 'tc_filter_lesson', 'tc_filter_module', 'tc_filter_action', 'tc_filter_date_range', 'tc_filter_date_range_last', 'tc_filter_start', 'tc_filter_end' ];

		$.each( fields, function( index, value ) {
			var value_top = $( '#tincan-filters-top [name="' + value + '"]' ).val();

			$( '#tincan-filters-bottom [name="' + value + '"]' ).val( value_top );
		});

		var tc_filter_date_range = $( '#tincan-filters-top input[name="tc_filter_date_range"]:checked' ).val();

		if ( typeof tc_filter_date_range != 'undefined' ) {
			$( '#tincan-filters-bottom input[name="tc_filter_date_range"]' ).val( tc_filter_date_range );
		} else {
			$( '#tincan-filters-bottom input[name="tc_filter_date_range"]' ).val( '' );
		}
	});

    // CSV Export
    $( '#do_tc_export_csv_xapi' ).click( function( e ) {
        var fields = [ 'tc_filter_group', 'tc_filter_user', 'tc_filter_course', 'tc_filter_lesson', 'tc_filter_module', 'tc_filter_action', 'tc_filter_quiz', 'tc_filter_results', 'tc_filter_date_range', 'tc_filter_date_range_last', 'tc_filter_start', 'tc_filter_end' ];

        $.each( fields, function( index, value ) {
            var value_top = $( '#xapi-filters-top [name="' + value + '"]' ).val();

            $( '#xapi-filters-bottom [name="' + value + '"]' ).val( value_top );
        });

        var tc_filter_date_range = $( '#xapi-filters-top input[name="tc_filter_date_range"]:checked' ).val();

        if ( typeof tc_filter_date_range != 'undefined' ) {
            $( '#xapi-filters-bottom input[name="tc_filter_date_range"]' ).val( tc_filter_date_range );
        } else {
            $( '#xapi-filters-bottom input[name="tc_filter_date_range"]' ).val( '' );
        }
    });

	// Ajax : Modules from Course
	$( '#tincan-filters-top select[name="tc_filter_course"]' ).change( function() {
		if ( !$(this).val() ) {
			ResetModules();
			return;
		}

		var data = {
			'action': 'GET_Modules',
			'tc_filter_course' : $( this ).val()
		};

		$.post( reportingApiSetup.ajaxurl, data, function( response ) {
			// Do Something

			ResetModules();
			$( '#tincan-filters-top select[name="tc_filter_module"]' ).append( response );
		});
	});

	// Ajax : Modules from Course
	$( '#xapi-filters-top select[name="tc_filter_course"]' ).change( function() {
		if ( !$(this).val() ) {
            ResetModulesXAPI();
			return;
		}

		var data = {
			'action': 'GET_Modules',
            'type'  : 'quiz',
			'tc_filter_course' : $( this ).val()
		};

		$.post( reportingApiSetup.ajaxurl, data, function( response ) {
			// Do Something

            ResetModulesXAPI();
			$( '#xapi-filters-top select[name="tc_filter_module"]' ).append( response );
		});
	});

	if( reportingApiSetup.isAdmin == "" ) {
        // Ajax : Modules from Course
        $('#tincan-filters-top select[name="tc_filter_group"]').change(function () {
            if (!$(this).val()) {
                ResetCourses();
                return;
            }

            var data = {
                'action': 'GET_Courses',
                'tc_filter_group': $(this).val()
            };

            $.post(reportingApiSetup.ajaxurl, data, function (response) {
                // Do Something

                ResetCourses();
                $('#tincan-filters-top select[name="tc_filter_course"]').append(response);
            });
        });

        // Ajax : Modules from Course
        $('#xapi-filters-top select[name="tc_filter_group"]').change(function () {
            if (!$(this).val()) {
                ResetCoursesXAPI();
                return;
            }

            var data = {
                'action': 'GET_Courses',
                'type': 'quiz',
                'tc_filter_group': $(this).val()
            };

            $.post(reportingApiSetup.ajaxurl, data, function (response) {
                // Do Something

                ResetCoursesXAPI();
                $('#xapi-filters-top select[name="tc_filter_course"]').append(response);
            });
        });
    }
	// Change Sorting Indicator
	$( '.uo-admin-reporting-tab-single#tin-can table.wp-list-table thead th' ).each( function() {
		var $indicator = $(this).find( '.sorting-indicator' );

		if ( $indicator.css( 'visibility' ) == 'hidden' ) {
			$indicator.addClass( 'double-headed-triangles' );
		}
	});

	function ResetModules() {
		$( '#tincan-filters-top select[name="tc_filter_module"]' ).children().each( function (i) {
			if ( i !== 0 ) $(this).remove();
		});
	}

	function ResetModulesXAPI() {
		$( '#xapi-filters-top select[name="tc_filter_module"]' ).children().each( function (i) {
			if ( i !== 0 ) $(this).remove();
		});
	}

    function ResetCourses() {
        $( '#tincan-filters-top select[name="tc_filter_course"]' ).children().each( function (i) {
            if ( i !== 0 ) $(this).remove();
        });
    }

    function ResetCoursesXAPI() {
        $( '#xapi-filters-top select[name="tc_filter_course"]' ).children().each( function (i) {
            if ( i !== 0 ) $(this).remove();
        });
    }

	function insertParam(key, value)
	{
		console.log('inserting param');
		console.log([key, value]);
	    key = encodeURI(key); value = encodeURI(value);

	    var kvp = document.location.search.substr(1).split('&');

	    var i=kvp.length; var x; while(i--)
	    {
	        x = kvp[i].split('=');

	        if (x[0]==key)
	        {
	            x[1] = value;
	            kvp[i] = x.join('=');
	            break;
	        }
	    }

	    if(i<0) {kvp[kvp.length] = [key,value].join('=');}

	    //this will reload the page, it's likely better to store this until finished
	    document.location.search = kvp.join('&');
	}

    $("#tc_filter_quiz").select2({
        ajax: {
            url: reportingApiSetup.ajaxurl,
            dataType: 'json',
            type: 'POST',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    action: 'GET_Questions'
                };
            },

            cache: true
        },
        placeholder: {
            id: '1', // the value of the option
            text: TincannyData.i18n.allQuestions
        },
        allowClear: true,
        minimumInputLength: 1,
        language: {
            errorLoading: function() {
                return TincannyData.i18n.dropdown.errorLoading
            },
            inputTooLong: function(e) {
                var n = e.input.length - e.maximum;

                if ( n == 1 ){
                    return TincannyData.i18n.dropdown.inputTooLong.singular;
                } else {
                    return TincannyData.i18n.dropdown.inputTooLong.plural.replace( '%s', n );
                }
            },
            inputTooShort: function(e) {
                return TincannyData.i18n.dropdown.inputTooShort.replace( '%s', (e.minimum - e.input.length) );
            },
            loadingMore: function() {
                return TincannyData.i18n.dropdown.loadingMore;
            },
            maximumSelected: function(e) {
                if ( e.maximum == 1 ){
                    return TincannyData.i18n.dropdown.maximumSelected.singular;
                } else {
                    return TincannyData.i18n.dropdown.maximumSelected.plural.replace( '%s', e.maximum );
                }
            },
            noResults: function() {
                return TincannyData.i18n.dropdown.noResults;
            },
            searching: function() {
                return TincannyData.i18n.dropdown.searching;
            },
            removeAllItems: function() {
                return TincannyData.i18n.dropdown.removeAllItems;
            }
        },
    });

});