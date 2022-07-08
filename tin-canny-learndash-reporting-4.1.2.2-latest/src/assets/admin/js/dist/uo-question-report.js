// Global access to all functions and variables in name space
// @namespace sampleNamespace
if (typeof tinCannyReporting === 'undefined') {
	// the namespace is not defined
	var tinCannyReporting = {};
}

(function ($) { // Self Executing function with $ alias for jQuery

	/* Initialization  similar to include once but since all js is loaded by the browser automatically the all
	 * we have to do is call our functions to initialize them, his is only run in the main configuration file
	 */
	$(document).ready(function () {

		if ($('#uotc-question-report').length) {
			tinCannyReporting.questionAnalysisReport.constructor();
		}

		console.log( $( '#uotc-question-report-selections' ) );

		if ( $( '#uotc-question-report-selections' ).length ) {

			tinCannyReporting.questionAnalysisFilters.init();
		}

	});

	tinCannyReporting.questionAnalysisReport = {
		uoTable: null,

		constructor: function () {
			this.createTable();
		},

		createTable: function (data) {

			// Create shared options applied to all export buttons
			const buttonCommon = {
				filename: function () {
					// File name parts
                    const filenameParts = [
                        // Base name
                        document.title
                    ];

                    // Get the ID of the dropdowns used to set the filters
                    const filtersFields = [
                        'uotc-question-report-group'
                    ];

                    // Get the value of the filters
                    filtersFields.forEach( ( filterID ) => {
                        // Get the field of the filter
                        const $filter = document.getElementById( filterID );

                        try {
                            // Check if it exists
                            if ( $filter ) {
                                // Get the value
                                const filterValue = $filter.value;

                                // Make sure the value is not empty
                                if (
                                    filterValue !== ''
                                    && filterValue != '0'
                                ) {
                                    // Get the TEXT of the option
                                    filenameParts.push(
                                        $filter.querySelector( `option[value="${ filterValue }"]` )
                                            .innerText
                                            .trim()
                                    );
                                }
                            }
                        } catch ( e ) {}
                    } );

                    // Return the name
                    return filenameParts.join( ' - ' );
				}
			};

			var tableVars = {
				dom: '<"reporting-datatable"<"reporting-datatable__top"<"reporting-datatable__search"f><"reporting-datatable-top__middle"><"reporting-datatable__buttons"B>><"reporting-datatable__table"t><"reporting-datatable__bottom"<"reporting-datatable__bottom-left"<"reporting-datatable__bottom-left-info"i><"reporting-datatable__bottom-left-info"l>><"reporting-datatable__bottom-right"p><"reporting-datatable__bottom-notice">>>',
				aLengthMenu: [
					[15, 30, 60, -1],
					[15, 30, 60, 'All']
				],
				order: [],
				iDisplayLength: 30,
				responsive: true,
				searching: true,
				columnDefs: [
					{
						targets: [3, 4, 6, 7],
						orderable: true
					},
					{
						targets: '_all',
						orderable: false,
						searchable: true
					},
				],
				buttons: [
					$.extend( true, {}, buttonCommon, {
						extend: 'csv',
						text: 'CSV',
						
					} ),
					$.extend( true, {}, buttonCommon, {
						extend: 'excelHtml5',
						text: 'Excel',
					} ),
				],
			};

			if (null !== this.uoTable) {
				this.uoTable.destroy();
			}

			tableVars.language = {
				emptyTable: uoQuestionAnalysisReportSetup.i18n.emptyTable,
				info: uoQuestionAnalysisReportSetup.i18n.info,
				infoEmpty: uoQuestionAnalysisReportSetup.i18n.infoEmpty,
				infoFiltered: uoQuestionAnalysisReportSetup.i18n.infoFiltered,
				lengthMenu: uoQuestionAnalysisReportSetup.i18n.lengthMenu,
				loadingRecords: uoQuestionAnalysisReportSetup.i18n.loadingRecords,
				processing: uoQuestionAnalysisReportSetup.i18n.processing,
				search: '',
				searchPlaceholder: uoQuestionAnalysisReportSetup.i18n.searchPlaceholder,
				zeroRecords: uoQuestionAnalysisReportSetup.i18n.zeroRecords,
				paginate: {
					first: uoQuestionAnalysisReportSetup.i18n.paginate.first,
					last: uoQuestionAnalysisReportSetup.i18n.paginate.last,
					next: uoQuestionAnalysisReportSetup.i18n.paginate.next,
					previous: uoQuestionAnalysisReportSetup.i18n.paginate.previous
				},
				aria: {
					sortAscending: uoQuestionAnalysisReportSetup.i18n.sortAscending,
					sortDescending: uoQuestionAnalysisReportSetup.i18n.sortDescending
				}
			}

			this.uoTable = jQuery('#uotc-question-report').DataTable(tableVars);

			$('.uotc-question-report__btn.uotc-question-report__btn--csv').on('click', function () {
				$('#uotc-user-report-container--hidden .buttons-csv').click();
			});

			$('.uotc-question-report__btn.uotc-question-report__btn--pdf').on('click', function () {
				$('#uotc-user-report-container--hidden .buttons-pdf').click();
			});
		}
	};

	tinCannyReporting.questionAnalysisFilters = {
		init: function() {

			// Loading on submit
			this.loadingOnSubmit();

			// Add select2 to fields
			this.select2Dropdowns();

		},

		loadingOnSubmit: function(){
			// Instance reference
			let _this = this;

			// Get selects
			const $selects = this.getForm().find( 'select' );

			// Submit when a field changes
			this.getForm().on( 'submit', function() {

				// Add loading class
				_this.getForm().addClass( 'uotc-question-report-selections-form--loading' );

				// Disable selects
				$selects.prop( 'readonly', true );

			} );
		},

		select2Dropdowns: function(){
			// Get selects
			const $selects = this.getForm().find( 'select' );

			// Init select2
			$selects.each( function( i, select ){
				try {
					const $select = $( select );

					// Create the options
					const options = {};

					// Add placeholder
					const placeholder = $select.data( 'placeholder' );
					if ( placeholder ) {
						options.placeholder = placeholder;
					}

					// Init select 2
					$select.select2( options );
				} catch ( e ) {
					console.log( e );
				}
			} );

		},

		getForm: function() {
			return $( '#uotc-question-report-selections > form' );
		}
	}
})(jQuery);
