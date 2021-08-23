var reportingTables = {

    tableColumns: null,
    cachedUserList: null,
    tableObjects: {},
    tableObjectsData: {},
    tableDrawActive: {},
    triggerCSV: {},
    triggerExcel: {},
    triggeredUserSingleOverview: false,

    defaultHeadings: {

        coursesOverviewTable: [
            {data: 'ID', title: 'ID'},
            {data: 'course', title: reportingApiSetup.learnDashLabels.course},
            {data: 'enrolled', title: ls('Enrolled')},
            {data: 'notStarted', title: ls('Not Started')},
            {data: 'inProgress', title: ls('In Progress')},
            {data: 'completed', title: ls('Completed')},
            {
                data: 'avgQuizScore',
                data: 'avgQuizScore',
                title: sprintf(reportingApiSetup.localizedStrings.tablesColumnsAvgQuizScore, reportingApiSetup.learnDashLabels.quiz)
            },
            {data: 'percentComplete', title: ls('% Complete')},
            {data: 'details', 'title': reportingApiSetup.localizedStrings.tablesColumnsDetails, className: 'no-sort'}
        ],

        courseSingleOverviewSummaryTable: [
            {data: 'usersEnrolled', title: ls('Users Enrolled')},
            {
                data: 'avgQuizScore',
                title: sprintf(reportingApiSetup.localizedStrings.tablesColumnsAvgQuizScore, reportingApiSetup.learnDashLabels.quiz)
            },
        ],

        courseSingleTable: [
            {data: 'ID', title: 'ID'},
            {data: 'displayName', title: ls('Name')},
			{data: 'firstName', title: ls('First Name')},
			{data: 'lastName', title: ls('Last Name')},
			{data: 'userLogin', title: ls('Username')},
            {data: 'email', title: ls('Email Address')},
            {data: 'quizAvg', title: ls('Quiz Average'), className: 'no-sort'},
            {data: 'completionDate', title: ls('Completion Date'),},
            {data: 'percentComplete', title: ls('% Complete'), className: 'no-sort'},
            {data: 'details', 'title': reportingApiSetup.localizedStrings.tablesColumnsDetails, className: 'no-sort'}
        ],

        usersOverviewTable: [
            {data: 'ID', title: 'ID'},
            {data: 'displayName', title: ls('Name')},
            {data: 'firstName', title: ls('First Name')},
            {data: 'lastName', title: ls('Last Name')},
            {data: 'userLogin', title: ls('Username')},
            {data: 'email', title: ls('Email Address')},
            {
                data: 'coursesEnrolled',
                title: sprintf(reportingApiSetup.localizedStrings.tablesColumnsCoursesEnrolled, reportingApiSetup.learnDashLabels.courses)
            },
            {data: 'notStarted', title: ls('Not Started')},
            {data: 'inProgress', title: ls('In Progress')},
            {data: 'completed', title: ls('Completed')},
            {data: 'details', 'title': reportingApiSetup.localizedStrings.tablesColumnsDetails, className: 'no-sort'}
        ],

        userSingleOverviewTable: [
            {
                data: 'coursesEnrolled',
                title: sprintf(reportingApiSetup.localizedStrings.tablesColumnsCoursesEnrolled, reportingApiSetup.learnDashLabels.courses)
            },
            {data: 'notStarted', title: ls('Not Started')},
            {data: 'inProgress', title: ls('In Progress')},
            {data: 'completed', title: ls('Completed')},
        ],

        userSingleCoursesOverviewTable: [
            {data: 'ID', title: 'ID'},
            {data: 'course', title: reportingApiSetup.learnDashLabels.course},
            {data: 'percentComplete', title: ls('% Complete')},
            {data: 'completionDate', title: ls('Completion Date')},
            {
                data: 'avgQuizScore',
                title: sprintf(reportingApiSetup.localizedStrings.tablesColumnsAvgQuizScore, reportingApiSetup.learnDashLabels.quiz)
            },
            {data: 'details', 'title': reportingApiSetup.localizedStrings.tablesColumnsDetails, className: 'no-sort'}
        ],

        userSingleCourseProgressSummaryTable: [
            {data: 'status', title: ls('Status')},
            {data: 'percentComplete', title: ls('% Complete')},
            {
                data: 'avgQuizScore',
                title: sprintf(reportingApiSetup.localizedStrings.tablesColumnsAvgQuizScore, reportingApiSetup.learnDashLabels.quiz)
            },
            {data: 'certificateLink', title: ls('Certificate Link')},
        ],

        userSingleCourseLessonsTable: [
            {data: 'order', title: ls('Order')},
            {
                data: 'lessonName',
                title: sprintf(reportingApiSetup.localizedStrings.tablesColumnsLessonName, reportingApiSetup.learnDashLabels.lesson)
            },
            {data: 'status', title: ls('Status')}
        ],

        userSingleCourseTopicsTable: [
            {data: 'order', title: ls('Order')},
            {
                data: 'topicName',
                title: sprintf(reportingApiSetup.localizedStrings.tablesColumnsTopicName, reportingApiSetup.learnDashLabels.topic)
            },
            {data: 'status', title: ls('Status')},
            {data: 'associatedLesson', title: ls('Associated Lesson')}
        ],

        userSingleCourseQuizzesTable: [
            {
                data: 'quizName',
                title: sprintf(reportingApiSetup.localizedStrings.tablesColumnsQuizName, reportingApiSetup.learnDashLabels.quiz)
            },
            {data: 'score', title: ls('Score')},
            {data: 'detailedReport', title: ls('Detailed Report')},
            {data: 'dateCompleted', title: ls('Date Completed')},
            {data: 'certificateLink', title: ls('Certificate Link')},
        ],

        userSingleCourseAssignmentsTable: [
            {data: 'assignmentName', title: ls('Assignment Name')},
            {data: 'approval', title: ls('Approval')},
            {data: 'submittedOn', title: ls('Submitted On')}
        ],

        userSingleCourseTinCanTable: [
            {data: 'lesson', title: ls('Page')},
            {data: 'module', title: ls('Module')},
            {data: 'target', title: ls('Target')},
            {data: 'action', title: ls('Action')},
            {data: 'result', title: ls('Result')},
            {data: 'date', title: ls('Date')}
        ],

        customReportingTable: []
    },

    getTableData: function (tableType, ID) {

        var tableData = [];

        switch (tableType) {
            case 'coursesOverviewTable':
                tableData = this.coursesOverviewTableData();
                break;
            case 'courseSingleOverviewSummaryTable':
                tableData = this.courseSingleOverviewSummaryTableDataObject(ID);
                break;
            case 'courseSingleTable':
                this.tableObjectsData['courseSingleTable'] = {
                    courseId: ID
                };
                tableData = this.courseSingleTableData(ID);
                break;
            case 'usersOverviewTable':
                tableData = this.usersOverviewTableData();
                break;
            case 'userSingleOverviewTable':
                tableData = this.userSingleOverviewTableData(ID);
                break;
            case 'userSingleCoursesOverviewTable':
                this.tableObjectsData['userSingleCoursesOverviewTable'] = {
                    ID: ID
                };
                tableData = this.userSingleCoursesOverviewTableData(ID);
                break;
            case 'userSingleCourseProgressSummaryTable':
                this.tableObjectsData['userSingleCourseProgressSummaryTable'] = {
                    ID: reportingTabs.viewingCurrentUserID,
                    courseId: ID,
                };
                tableData = this.userSingleCourseProgressSummaryTable(ID);
                break;
            case 'userSingleCourseLessonsTable':
                tableData = this.userSingleCourseLessonsTableData(ID);
                break;
            case 'userSingleCourseTopicsTable':
                tableData = this.userSingleCourseTopicsTableData(ID);
                break;
            case 'userSingleCourseQuizzesTable':
                tableData = this.userSingleCourseQuizzesTableData(ID);
                break;
            case 'userSingleCourseAssignmentsTable':
                tableData = this.userSingleCourseAssignmentsTableData(ID);
                break;
            case 'userSingleCourseTinCanTable':
                tableData = this.userSingleCourseTinCanTableData(ID);
                break;
        }

        if (isDefined(wp.hooks)) {
            tableData = wp.hooks.applyFilters('tc_table_data', tableData, tableType, ID);
        }

        return tableData;
    },

    getTableHeadings: function (tableType) {

        var headings = [];

        if (typeof this.defaultHeadings[tableType] !== 'undefined') {
            headings = this.defaultHeadings[tableType];
        }

        if (isDefined(wp.hooks)) {
            headings = wp.hooks.applyFilters('tc_table_headings', headings, tableType);
        }

        return headings;
    },

    getColumnDefs: function (tableType) {

        var columnDefs = [];
        let orderableCols;
        let nonOrderableCols;
		let hidden_targets;

        switch (tableType) {
            case 'courseSingleTable':

                orderableCols = [];
                nonOrderableCols = [6, 8, 9];
                if (typeof reportingApiSetup.disablePerformanceEnhancments !== 'undefined' && '1' === reportingApiSetup.disablePerformanceEnhancments) {
                    orderableCols = [8];
                    nonOrderableCols = [6, 9];
                }

				hidden_targets = [0];

				if (typeof reportingApiSetup.userIdentifierDisplayName !== 'undefined' && '0' === reportingApiSetup.userIdentifierDisplayName) {
					hidden_targets.push(1)
				}

				if (typeof reportingApiSetup.userIdentifierFirstName !== 'undefined' && '0' === reportingApiSetup.userIdentifierFirstName) {
					hidden_targets.push(2)
				}

				if (typeof reportingApiSetup.userIdentifierLastName !== 'undefined' && '0' === reportingApiSetup.userIdentifierLastName) {
					hidden_targets.push(3)
				}

				if (typeof reportingApiSetup.userIdentifierUsername !== 'undefined' && '0' === reportingApiSetup.userIdentifierUsername) {
					hidden_targets.push(4)
				}

				if (typeof reportingApiSetup.userIdentifierEmail !== 'undefined' && '0' === reportingApiSetup.userIdentifierEmail) {
					hidden_targets.push(5)
				}

				if( 6 === hidden_targets.length ){
					hidden_targets.splice(1, 1)
				}

                columnDefs = [
                    {
                        targets: hidden_targets,
                        visible: false,
                        searchable: false
                    },
                    {
                        data: 'completionDate',
                        type: 'num-html',
                        targets: [7],
                        render: function (data, type, full) {
                            // this will return the Display value for everything
                            // except for when it's used for sorting,
                            // which then it will use the Sort value
                            let response = data.display;

                            if (type == 'sort') {
                                response = data.timestamp;
                            }

                            return response;
                        }
                    },
                    {
                        targets: nonOrderableCols,
                        visible: true,
                        searchable: false,
                        orderable: false
                    },
                    {
                        targets: orderableCols,
                        visible: true,
                        searchable: false
                    },
                ];
                break;

			case 'userSingleCoursesOverviewTable':

				// Default ordering
                orderableCols = [1]; // Course Name
                nonOrderableCols = [2, 4, 5]; // % complete, Avg Quiz, Details

				let orderableCompletionDate = false;

				// Performance in settings is turned off
                if (typeof reportingApiSetup.disablePerformanceEnhancments !== 'undefined' && '1' === reportingApiSetup.disablePerformanceEnhancments) {
                    orderableCols = [1,2,4]; // Course name, % complete, Avg uiz
                    nonOrderableCols = [5];
					orderableCompletionDate = true;
                }

                //Hide the first ID Column
                columnDefs = [
                    {"targets": [0], "visible": false, "searchable": false}, // Hidden course ID column
					// Completion Date Column
                    {
                        "targets": [3], "visible": true, "searchable": false, "orderable": orderableCompletionDate, render: {
                            _: 'display',
                            sort: 'timestamp'
                        }
                    },
                    {"targets": nonOrderableCols, "visible": true, "searchable": false, "orderable": false},
                    {"targets": orderableCols, "visible": true, "searchable": false, "orderable": true}
                ];
                break;

            case 'coursesOverviewTable':

				hidden_targets = [];

				columnDefs = [
					{"targets": hidden_targets, "visible": false, "searchable": false},
				];
				break;
            case 'usersOverviewTable':

            	hidden_targets = [];

				if (typeof reportingApiSetup.userIdentifierDisplayName !== 'undefined' && '0' === reportingApiSetup.userIdentifierDisplayName) {
					hidden_targets.push(1)
				}

				if (typeof reportingApiSetup.userIdentifierFirstName !== 'undefined' && '0' === reportingApiSetup.userIdentifierFirstName) {
					hidden_targets.push(2)
				}

				if (typeof reportingApiSetup.userIdentifierLastName !== 'undefined' && '0' === reportingApiSetup.userIdentifierLastName) {
					hidden_targets.push(3)
				}

				if (typeof reportingApiSetup.userIdentifierUsername !== 'undefined' && '0' === reportingApiSetup.userIdentifierUsername) {
					hidden_targets.push(4)
				}

				if (typeof reportingApiSetup.userIdentifierEmail !== 'undefined' && '0' === reportingApiSetup.userIdentifierEmail) {
					hidden_targets.push(5)
				}

				if( 5 === hidden_targets.length ){
					hidden_targets.shift()
				}

				columnDefs = [
					{"targets": hidden_targets, "visible": false, "searchable": false},
				];
				break;
            case 'userSingleCourseLessonsTable':
            case 'userSingleCourseTopicsTable':
               // columnDefs = [{"targets": [0], "visible": false, "searchable": false}];
                break;
			case 'userSingleCourseQuizzesTable':

				columnDefs = [
					{
						"targets": [3], "visible": true, "searchable": false, "orderable": true,
						render: {
							_: 'display',
							sort: 'timestamp'
						}
					},
				];
				break;

        }

        if (isDefined(wp.hooks)) {
            columnDefs = wp.hooks.applyFilters('tc_columnDefs', columnDefs, tableType);
        }

        return columnDefs;
    },

    getCustomizations: function (tableData, tableType) {

        switch (tableType) {
            case 'courseSingleOverviewSummaryTable':
            case 'userSingleOverviewTable':
            case 'userSingleCourseProgressSummaryTable':
                tableData["paging"] = false;
                tableData["ordering"] = false;
                tableData["info"] = false;
                tableData["bFilter"] = false;
                break;
        }

        return tableData;
    },
    addColumnsFilter: function (state, tableType) {
        var colFilter_container = jQuery( `<div id="tclr-${ state.sInstance }-filter-columns" class="tclr-dataTables-filter-columns"><input class="tclr-dataTables-filter-columns-field" id="tclr-${ state.sInstance }-filter-columns-field" type="checkbox"><div class="tclr-dataTables-filter-columns__toggle"><label for="tclr-${ state.sInstance }-filter-columns-field" data-label-enable="${ ls( 'customizeColumns' ) }" data-label-disable="${ ls( 'hideCustomizeColumns' ) }"></label></div><div class="tclr-dataTables-filter-columns__fields"></div></div>` );
        var __this = this;

        if( state.oSavedState !== null ) {
            jQuery.each(state.oSavedState.columns, function (key, value) {
                //var title = state.aoColumns[key].sTitle;
                var tmp = document.createElement("DIV");
                tmp.innerHTML = state.aoColumns[key].sTitle;
                var title = tmp.textContent || tmp.innerText || "";
                colFilter_container.find( '.tclr-dataTables-filter-columns__fields' ).append(__this.addColumnCheckbox(title, key, value.visible, tableType));
            });
        } else {
            jQuery.each(state.aoColumns, function (key, value) {
                //var title = state.aoColumns[key].sTitle;
                var tmp = document.createElement("DIV");
                tmp.innerHTML = state.aoColumns[key].sTitle;
                var title = tmp.textContent || tmp.innerText || "";
                colFilter_container.find( '.tclr-dataTables-filter-columns__fields' ).append(__this.addColumnCheckbox(title, key, value.bVisible, tableType));
            });
        }
        return colFilter_container;
    },
    addColumnCheckbox: function (label, column_key, is_visible, tableType) {
        var checkbox = jQuery('<input />', {
            type: 'checkbox',
            id: 'dt_' + column_key,
            value: label,
            checked: is_visible
        });
        var _tableType = tableType;
        checkbox.on('change', function (e) {
            // Get the column API object

            var headers = window.reportingTables.tableObjects[_tableType].columns().header();
            var current_check = jQuery(this).attr('value');
            var is_checked = jQuery(this).is(':checked');

            jQuery.each(headers, function (key, selecter) {
                if (jQuery(selecter).text() === current_check) {
                    var column = window.reportingTables.tableObjects[_tableType].column(key);
                    if (is_checked) {
                        // Toggle the visibility
                        column.visible(true);
                    } else {
                        column.visible(false);
                    }
                }
            });

        });
        var $label = jQuery('<label/>').append(checkbox).append(label);
        return $label;
    },
    createTable: function (tableType, tableElement, ID) {
        // Get context
        let context = reportingApiSetup.page;
        context     = context == 'reporting' && jQuery( 'body' ).hasClass( 'wp-admin' ) ? 'plugin' : context;
        context     = context == 'reporting' ? 'frontend' : context;

        // Check if it's the "Tin Can Report" or "xAPI Quiz Report" table
        if ( [ 'tin-can', 'xapi-tincan' ].indexOf( tableType ) !== -1 ){
            // Check if the table is in the frontend
            if ( context == 'frontend' ){
                // Get the "Tin Can Report" and "xAPI Quiz Report" tables
                let $table = jQuery( tableElement );

                let _this = this;

                // Check if the table is already a DataTable
                if ( ! jQuery.fn.dataTable.isDataTable( $table ) ){
                    // Remove all classes from the table
                    $table.removeClass();
                    // And from all the children
                    $table.find( 'thead, tbody, tr, th, td' ).removeClass();

                    // Remove footer
                    $table.find( 'tfoot' ).remove();

                    // Remove .button class, it's too generic
                    $table.closest( '.reporting-datatable__table' ).find( '.button' ).removeClass( 'button' ).addClass( 'tclr-reporting-button' );

                    // Check if the table has results
                    let hasResults = $table.find( 'thead tr th' ).length == $table.find( 'tbody tr:first-child td' ).length;

                    // If it doesn't have results
                    if ( ! hasResults ){
                        // Remove the "No results" tr
                        $table.find( 'tbody tr' ).remove();
                    }

                    // Create dataTables
                    this.tableObjects[ tableType ] = $table.DataTable({
                        responsive:   true,
                        paging:       false,
                        bSort:        false,
                        dom:          't',
                        stateSave:    true,
                        initComplete: function( s, d ){
                            jQuery( _this.addColumnsFilter( s, tableType ) ).insertAfter( '#' + tableType + ' .reporting-tincan-filters' );
                        }
                    }).on( 'draw.dt', function(){
                        // Trigger a resize event after the table is rendered
                        // so DataTable invokes the responsive events
                        $( window ).trigger( 'resize' );
                    });
                }
            }
        }
        else {
            if (!tableType) {

                return false;
            }

            if (!tableElement) {

                return false;
            }

            var headings = this.getTableHeadings(tableType);
            if (0 === headings) {

                return false;
            }

            var tableData = this.getTableData(tableType, ID);

            var columnDefs = this.getColumnDefs(tableType);

            // Replace empty data with a dash
            tableData.forEach(function (row, index) {
                // Iterate each property in the object
                for (var key in row) {
                    // Check if it's an empty string
                    if (row[key] === '') {
                        // Replace it with a dash
                        tableData[index][key] = '-';
                    }
                }
            });

            var tableVariables = {
                lengthMenu: [[10, 25, 50,  100, 200, 500], [10, 25, 50,  100, 200, 500]],
                data: tableData,
                columns: headings,
                columnDefs: columnDefs,
                responsive: true,
                language: {
                    "sEmptyTable": ls("There are no activities to report."),
                    "sInfo": ls("Showing _START_ to _END_ of _TOTAL_ entries"),
                    "sInfoEmpty": ls("Showing 0 to 0 of 0 entries"),
                    "sInfoFiltered": ls("(filtered from _MAX_ total entries)"),
                    "sInfoPostFix": ls(""),
                    "sInfoThousands": ls(","),
                    "sLengthMenu": ls("Show _MENU_ entries"),
                    "sLoadingRecords": ls("Loading..."),
                    "sProcessing": ls("Processing..."),
                    "sSearch": '',
                    "searchPlaceholder": reportingApiSetup.localizedStrings.tablesSearchPlaceholder,
                    "sZeroRecords": ls("No matching records found"),
                    "oPaginate": {
                        "sFirst": ls("First"),
                        "sLast": ls("Last"),
                        "sNext": ls("Next"),
                        "sPrevious": ls("Previous")
                    },
                    "oAria": {
                        "sSortAscending": ls(": activate to sort column ascending"),
                        "sSortDescending": ls(": activate to sort column descending")
                    }
                },
                dom: '<"reporting-datatable"<"reporting-datatable__top"<"reporting-datatable__search"f><"reporting-datatable-top__middle"><"reporting-datatable__buttons"B>><"reporting-datatable__table"t><"reporting-datatable__bottom"<"reporting-datatable__bottom-left"<"reporting-datatable__bottom-left-info"i><"reporting-datatable__bottom-left-info"l>><"reporting-datatable__bottom-right"p>>>',
                //dom: 'Blfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: ls('Excel Export'),
                        title: this.createCsvFileName(tableType, ID),
                        charset: 'utf-8',
                        bom: true,
                        action: function action(e, dt, button, config) {
                            var tableType = dt.table().node().id;
                            if (typeof reportingTables.tableObjects[tableType] !== 'undefined' && ('userSingleCoursesOverviewTable' === tableType || 'courseSingleTable' === tableType)) {
                                // The course that is being viewed
                                var apiData = {
                                    tableType: tableType,
                                    rows: []
                                };
                                var rows = reportingTables.tableObjects[tableType].rows();

                                for (var row in rows[0]) {
                                    var rowData = reportingTables.tableObjects[tableType].row(rows[0][row]).data();
                                    apiData.rows.push({
                                        rowId: rows[0][row],
                                        ID: rowData.ID
                                    });
                                }

                                apiData.rows = JSON.stringify(apiData.rows);
                                var apiDataCall;

                                if ('userSingleCoursesOverviewTable' === tableType) {
                                    // The user that is being viewed
                                    apiData.userId = reportingTables.tableObjectsData.userSingleCoursesOverviewTable.ID;
                                    apiData.tablePage = reportingTables.tableObjects[tableType].page.info(); //let tableOrder = reportingTables.tableObjects[tableType].order();
                                    //let tableColumn = reportingTables.tableObjects[tableType].column(tableOrder[0][0]);
                                    //apiData.column = tableColumn.dataSrc();
                                    //apiData.order = tableOrder[0][1];
                                }

                                if ('courseSingleTable' === tableType) {
                                    // The course that is being viewed
                                    apiData.courseId = reportingTables.tableObjectsData.courseSingleTable.courseId;
                                    apiData.tablePage = reportingTables.tableObjects[tableType].page.info(); //let tableOrder = reportingTables.tableObjects[tableType].order();
                                    //let tableColumn = reportingTables.tableObjects[tableType].column(tableOrder[0][0]);
                                    //apiData.column = tableColumn.dataSrc();
                                    //apiData.order = tableOrder[0][1];
                                }

                                reportingTables.tableDrawActive[tableType] = true;
                                reportingTables.triggerExcel[tableType] = {
                                    triggered: true,
                                    e: e,
                                    dt: dt,
                                    button: button,
                                    config: config
                                };

                                if (typeof reportingApiSetup.isolated_group_id === 'string' && parseInt(reportingApiSetup.isolated_group_id)) {
                                    apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data' + '/?group_id=' + reportingApiSetup.isolated_group_id, apiData);
                                } else {
                                    apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data', apiData);
                                }

                                if ('userSingleCoursesOverviewTable' === tableType) {
                                    apiDataCall.done(reportingTables.drawUserSingleCoursesOverviewTable);
                                }

                                if ('courseSingleTable' === tableType) {
                                    apiDataCall.done(reportingTables.drawcourseSingleTableDataExcel);
                                }
                            } else {
                                if (jQuery.fn.dataTable.ext.buttons.excelHtml5.available(dt, config)) {
                                    jQuery.fn.dataTable.ext.buttons.excelHtml5.action(e, dt, button, config);
                                } else {
                                    jQuery.fn.dataTable.ext.buttons.excelFlash.action(e, dt, button, config);
                                }
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        text: ls('CSV Export'),
                        title: this.createCsvFileName(tableType, ID),
                        charset: 'utf-8',
                        bom: true,
                        action: function (e, dt, button, config) {

                            var tableType = dt.table().node().id;

                            if (
                                typeof reportingTables.tableObjects[tableType] !== 'undefined'
                                && ('userSingleCoursesOverviewTable' === tableType || 'courseSingleTable' === tableType)
                            ) {

                                // The course that is being viewed
                                let apiData = {
                                    tableType: tableType,
                                    rows: [],
                                };

                                let rows = reportingTables.tableObjects[tableType].rows();

                                for (let row in rows[0]) {
                                    let rowData = reportingTables.tableObjects[tableType].row(rows[0][row]).data();

                                    apiData.rows.push(
                                        {
                                            rowId: rows[0][row],
                                            ID: rowData.ID
                                        }
                                    );
                                }

                                apiData.rows = JSON.stringify(apiData.rows);

                                let apiDataCall;

                                if ('userSingleCoursesOverviewTable' === tableType) {
                                    // The user that is being viewed
                                    apiData.userId = reportingTables.tableObjectsData.userSingleCoursesOverviewTable.ID;
                                    apiData.tablePage = reportingTables.tableObjects[tableType].page.info();
                                    //let tableOrder = reportingTables.tableObjects[tableType].order();
                                    //let tableColumn = reportingTables.tableObjects[tableType].column(tableOrder[0][0]);
                                    //apiData.column = tableColumn.dataSrc();
                                    //apiData.order = tableOrder[0][1];
                                }

                                if ('courseSingleTable' === tableType) {
                                    // The course that is being viewed
                                    apiData.courseId = reportingTables.tableObjectsData.courseSingleTable.courseId;
                                    apiData.tablePage = reportingTables.tableObjects[tableType].page.info();
                                    //let tableOrder = reportingTables.tableObjects[tableType].order();
                                    //let tableColumn = reportingTables.tableObjects[tableType].column(tableOrder[0][0]);
                                    //apiData.column = tableColumn.dataSrc();
                                    //apiData.order = tableOrder[0][1];
                                }

                                reportingTables.tableDrawActive[tableType] = true;
                                reportingTables.triggerCSV[tableType] = {
                                    triggered: true,
                                    e: e,
                                    dt: dt,
                                    button: button,
                                    config: config
                                };

                                if (typeof reportingApiSetup.isolated_group_id === 'string' && parseInt(reportingApiSetup.isolated_group_id)) {
                                    apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data' + '/?group_id=' + reportingApiSetup.isolated_group_id, apiData);
                                } else {
                                    apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data', apiData);
                                }

                                if ('userSingleCoursesOverviewTable' === tableType) {
                                    apiDataCall.done(reportingTables.drawUserSingleCoursesOverviewTable);
                                }

                                if ('courseSingleTable' === tableType) {
                                    apiDataCall.done(reportingTables.drawcourseSingleTableData);
                                }
                            } else {
                                if (jQuery.fn.dataTable.ext.buttons.csvHtml5.available(dt, config)) {
                                    jQuery.fn.dataTable.ext.buttons.csvHtml5.action(e, dt, button, config);
                                } else {
                                    jQuery.fn.dataTable.ext.buttons.csvFlash.action(e, dt, button, config);
                                }
                            }
                        }
                    }
                ]
            };

            // DOM exceptions
            if ( tableType == 'usersOverviewTable' || tableType == 'coursesOverviewTable' ){
                tableVariables.dom = '<"reporting-datatable"<"reporting-datatable__top"<"reporting-datatable__search"f><"reporting-datatable-top__middle"><"reporting-datatable__buttons"B>><"reporting-datatable__table"t><"reporting-datatable__bottom"<"reporting-datatable__bottom-left"<"reporting-datatable__bottom-left-info"i><"reporting-datatable__bottom-left-info"l>><"reporting-datatable__bottom-right"p><"reporting-datatable__bottom-notice">>>';
            }

            tableVariables = this.getCustomizations(tableVariables, tableType);

            // Filter table variables before the table is created
            if (isDefined(wp.hooks)) {
                tableVariables = wp.hooks.applyFilters('tc_data_table_variables', tableVariables, tableType, tableElement, ID);
            }

            // Check if the table is defined
            if (isDefined(this.tableObjects[tableType])) {
                // Try to destroy it
                try {
                    this.tableObjects[tableType].destroy();
                    this.tableObjects[tableType] = undefined;
                }
                catch (e) {
                    // Catch and show the error in the console
                    console.error(e);
                }
            }

            // Define xhr
            jQuery(tableElement).on('xhr.dt', function () {
                // console.log(xhr);
                setTimeout(() => {
                    jQuery(window).trigger('resize');
                }, 100);
            });

            this.tableObjects[tableType] = jQuery(tableElement)

				.on( 'error.dt', function ( e, settings, techNote, message ) {
					console.log( 'An error has been reported by DataTables: ', message );
				} )

				.on('order.dt', function () {

                    if (
                        (typeof reportingApiSetup.disablePerformanceEnhancments !== 'undefined' && '1' !== reportingApiSetup.disablePerformanceEnhancments)
                        || typeof reportingApiSetup.disablePerformanceEnhancments === 'undefined'
                    ) {
                        if (typeof reportingTables.tableObjects[tableType] !== 'undefined') {

                            if ('userSingleCoursesOverviewTable' === tableType || 'courseSingleTable' === tableType) {

                                // The course that is being viewed
                                let apiData = {
                                    tableType: tableType,
                                    rows: [],
                                };

                                let rows = reportingTables.tableObjects[tableType].rows({page: 'current'});

                                for (let row in rows[0]) {
                                    let rowData = reportingTables.tableObjects[tableType].row(rows[0][row]).data();

                                    apiData.rows.push(
                                        {
                                            rowId: rows[0][row],
                                            ID: rowData.ID
                                        }
                                    );
                                }

                                let apiDataCall;

                                if ('userSingleCoursesOverviewTable' === tableType) {
                                    // The user that is being viewed
                                    apiData.userId = reportingTables.tableObjectsData.userSingleCoursesOverviewTable.ID;
                                    apiData.tablePage = reportingTables.tableObjects[tableType].page.info();
                                    //let tableOrder = reportingTables.tableObjects[tableType].order();
                                    //let tableColumn = reportingTables.tableObjects[tableType].column(tableOrder[0][0]);
                                    //apiData.column = tableColumn.dataSrc();
                                    //apiData.order = tableOrder[0][1];
                                }

                                if ('courseSingleTable' === tableType) {
                                    // The course that is being viewed
                                    apiData.courseId = reportingTables.tableObjectsData.courseSingleTable.courseId;

                                    apiData.tablePage = reportingTables.tableObjects[tableType].page.info();
                                    //let tableOrder = reportingTables.tableObjects[tableType].order();
                                    //let tableColumn = reportingTables.tableObjects[tableType].column(tableOrder[0][0]);
                                    //apiData.column = tableColumn.dataSrc();
                                    //apiData.order = tableOrder[0][1];
                                }

                                reportingTables.tableDrawActive[tableType] = true;

                                if (typeof reportingApiSetup.isolated_group_id === 'string' && parseInt(reportingApiSetup.isolated_group_id)) {
                                    apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data' + '/?group_id=' + reportingApiSetup.isolated_group_id, apiData);
                                } else {
                                    apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data', apiData);
                                }

                                if ('userSingleCoursesOverviewTable' === tableType) {
                                    apiDataCall.done(reportingTables.drawUserSingleCoursesOverviewTable);
                                }

                                if ('courseSingleTable' === tableType) {
                                    apiDataCall.done(reportingTables.drawcourseSingleTableData);
                                }


                            }
                        }
                    }
                })
                .on('search.dt', function () {
                    if (
                        (typeof reportingApiSetup.disablePerformanceEnhancments !== 'undefined' && '1' !== reportingApiSetup.disablePerformanceEnhancments)
                        || typeof reportingApiSetup.disablePerformanceEnhancments === 'undefined'
                    ) {
                        if (typeof reportingTables.tableObjects[tableType] !== 'undefined') {

                            if ('userSingleCoursesOverviewTable' === tableType || 'courseSingleTable' === tableType) {

                                if (typeof reportingTables.tableDrawActive[tableType] !== 'undefined') {
                                    if (true === reportingTables.tableDrawActive[tableType]) {
                                        return true;
                                    }
                                }
                                // The course that is being viewed
                                var apiData = {
                                    tableType: tableType,
                                    rows: []
                                };

                                let rows = reportingTables.tableObjects[tableType].rows({page: 'current'});

                                for (let row in rows[0]) {
                                    let rowData = reportingTables.tableObjects[tableType].row(rows[0][row]).data();
                                    apiData.rows.push(
                                        {
                                            rowId: rows[0][row],
                                            ID: rowData.ID
                                        }
                                    );
                                }

                                let apiDataCall;

                                if ('userSingleCoursesOverviewTable' === tableType) {
                                    // The user that is being viewed
                                    apiData.userId = reportingTables.tableObjectsData.userSingleCoursesOverviewTable.ID;
                                    apiData.tablePage = reportingTables.tableObjects[tableType].page.info();
                                }

                                if ('courseSingleTable' === tableType) {
                                    // The course that is being viewed
                                    apiData.courseId = reportingTables.tableObjectsData.courseSingleTable.courseId;

                                    apiData.tablePage = reportingTables.tableObjects[tableType].page.info();
                                }

                                reportingTables.tableDrawActive[tableType] = true;


                                if (typeof reportingApiSetup.isolated_group_id === 'string' && parseInt(reportingApiSetup.isolated_group_id)) {
                                    apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data' + '/?group_id=' + reportingApiSetup.isolated_group_id, apiData);
                                } else {
                                    apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data', apiData);
                                }

                                if ('userSingleCoursesOverviewTable' === tableType) {
                                    apiDataCall.done(reportingTables.drawUserSingleCoursesOverviewTable);
                                }

                                if ('courseSingleTable' === tableType) {
                                    apiDataCall.done(reportingTables.drawcourseSingleTableData);
                                }


                            }
                        }
                    }
                })
				.on('length.dt', function () {

					if (
						(typeof reportingApiSetup.disablePerformanceEnhancments !== 'undefined' && '1' !== reportingApiSetup.disablePerformanceEnhancments)
						|| typeof reportingApiSetup.disablePerformanceEnhancments === 'undefined'
					) {
						if (typeof reportingTables.tableObjects[tableType] !== 'undefined') {

							if ('courseSingleTable' === tableType) {
								// The course that is being viewed
								var apiData = {
									tableType: tableType,
									rows: []
								};

								let rows = reportingTables.tableObjects[tableType].rows({page: 'current'});

								for (let row in rows[0]) {
									let rowData = reportingTables.tableObjects[tableType].row(rows[0][row]).data();

									apiData.rows.push(
										{
											rowId: rows[0][row],
											ID: rowData.ID
										}
									);
								}

								let apiDataCall;

								if ('courseSingleTable' === tableType) {

									// The course that is being viewed
									apiData.courseId = reportingTables.tableObjectsData.courseSingleTable.courseId;

									apiData.tablePage = reportingTables.tableObjects[tableType].page.info();
								}

								reportingTables.tableDrawActive[tableType] = true;


								if (typeof reportingApiSetup.isolated_group_id === 'string' && parseInt(reportingApiSetup.isolated_group_id)) {
									apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data' + '/?group_id=' + reportingApiSetup.isolated_group_id, apiData);
								} else {
									apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data', apiData);
								}

									apiDataCall.done(reportingTables.drawcourseSingleTableData);
							}
						}
					}
				})
                .on('page.dt', function () {
                    if (
                        (typeof reportingApiSetup.disablePerformanceEnhancments !== 'undefined' && '1' !== reportingApiSetup.disablePerformanceEnhancments)
                        || typeof reportingApiSetup.disablePerformanceEnhancments === 'undefined'
                    ) {
                        if (typeof reportingTables.tableObjects[tableType] !== 'undefined') {

                            if ('userSingleCoursesOverviewTable' === tableType || 'courseSingleTable' === tableType) {

                                if (typeof reportingTables.tableDrawActive[tableType] !== 'undefined') {
                                    if (true === reportingTables.tableDrawActive[tableType]) {
                                        return true;
                                    }
                                }
                                // The course that is being viewed
                                var apiData = {
                                    tableType: tableType,
                                    rows: []
                                };

                                let rows = reportingTables.tableObjects[tableType].rows({page: 'current'});

                                for (let row in rows[0]) {
                                    let rowData = reportingTables.tableObjects[tableType].row(rows[0][row]).data();

                                    apiData.rows.push(
                                        {
                                            rowId: rows[0][row],
                                            ID: rowData.ID
                                        }
                                    );
                                }

                                let apiDataCall;

                                if ('userSingleCoursesOverviewTable' === tableType) {
                                    // The user that is being viewed
                                    apiData.userId = reportingTables.tableObjectsData.userSingleCoursesOverviewTable.ID;
                                    apiData.tablePage = reportingTables.tableObjects[tableType].page.info();
                                }

                                if ('courseSingleTable' === tableType) {

                                    // The course that is being viewed
                                    apiData.courseId = reportingTables.tableObjectsData.courseSingleTable.courseId;

                                    apiData.tablePage = reportingTables.tableObjects[tableType].page.info();
                                }

                                reportingTables.tableDrawActive[tableType] = true;


                                if (typeof reportingApiSetup.isolated_group_id === 'string' && parseInt(reportingApiSetup.isolated_group_id)) {
                                    apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data' + '/?group_id=' + reportingApiSetup.isolated_group_id, apiData);
                                } else {
                                    apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data', apiData);
                                }

                                if ('userSingleCoursesOverviewTable' === tableType) {
                                    apiDataCall.done(reportingTables.drawUserSingleCoursesOverviewTable);
                                }

                                if ('courseSingleTable' === tableType) {
                                    apiDataCall.done(reportingTables.drawcourseSingleTableData);
                                }


                            }
                        }
                    }
                }).DataTable(tableVariables);

            // Check if the table is being rendered in the WP admin dashboard or
            // in the Tin Canny page (not in the frontend)
            if ( '1' === TincannyData.administratorView && ( 'dashboard' === context|| 'plugin' === context) ){
                // Add content to the data notice container
                jQuery( '.reporting-datatable__bottom-notice' ).html( '<strong>' + TincannyData.i18n.dataNotRight + '</strong> ' + TincannyData.i18n.tryRunning.replace( '%s', '<a href="' + TincannyData.url.updateData + '" target="_blank">' + TincannyData.i18n.updatesLinkText + '</a>' ) );
            }

            if ('userSingleCourseProgressSummaryTable' === tableType) {

                // The course that is being viewed
                let apiData = {
                    tableType: tableType,
                };

                apiData.courseId = this.tableObjectsData.userSingleCourseProgressSummaryTable.courseId;
                apiData.userId = this.tableObjectsData.userSingleCourseProgressSummaryTable.ID;

                let apiDataCall;

                if (typeof reportingApiSetup.isolated_group_id === 'string' && parseInt(reportingApiSetup.isolated_group_id)) {
                    apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data' + '/?group_id=' + reportingApiSetup.isolated_group_id, apiData);
                } else {

                    apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data', apiData);


                }

                apiDataCall.done(function (response) {

                    dataObject.additionalData['userSingleCourseProgressSummaryTable'] = response.additionalData;

                    // Summary
                    let rowData = reportingTables.tableObjects[tableType].row(0).data();

                    rowData.status = response.data.status == '' ? '-' : response.data.status;
                    rowData.percentComplete = response.data.progress_percentage == '' ? '-' : response.data.progress_percentage;
                    rowData.avgQuizScore = response.data.avg_score == '' ? '-' : response.data.avg_score;

                    let certifcateLink = '';
                    if('' !== response.data.course_certificate ){
                        certifcateLink = '<a target="_blank" href="' + response.data.course_certificate + '">'+ ls('View') +'</a>';
                    }

                    rowData.certificateLink = certifcateLink;

                    if (isDefined(wp.hooks)) {
                        rowData = wp.hooks.applyFilters('tc_table_data_userSingleCourseProgressSummaryTable', rowData, 0, response);
                    }

                    reportingTables.tableObjects[tableType].row(0).data(rowData).draw('page');

                    // Lessons
                    let lessons = response.data.lessons;
                    for (let row in lessons) {
                        if (lessons.hasOwnProperty(row)) {

                            let data = {
                                order: row,
                                lessonName: lessons[row].name,
                                status: lessons[row].status,
                            };

                            if (isDefined(wp.hooks)) {
                                data = wp.hooks.applyFilters('tc_table_data_userSingleCourseLessonsTable', data, row, lessons);
                            }

                            reportingTables.tableObjects['userSingleCourseLessonsTable'].row.add(data);
                        }
                    }

                    reportingTables.tableObjects['userSingleCourseLessonsTable'].draw();

                    // Topics
                    let topics = response.data.topics;
                    for (let row in topics) {
                        if (topics.hasOwnProperty(row)) {

                            let data = {
                                order: row,
                                topicName: topics[row].name,
                                status: topics[row].status,
                                associatedLesson: topics[row].associated_lesson,
                            };

                            if (isDefined(wp.hooks)) {
                                data = wp.hooks.applyFilters('tc_table_data_userSingleCourseTopicsTable', data, row, topics);
                            }

                            reportingTables.tableObjects['userSingleCourseTopicsTable'].row.add(data);
                        }
                    }

                    reportingTables.tableObjects['userSingleCourseTopicsTable'].draw();

                    // Quizzes
                    let quizzes = response.data.quizzes;
                    for (let row in quizzes) {
                        if (quizzes.hasOwnProperty(row)) {

                            let certifcateLink = '';
                            if('' !== quizzes[row].certificate_link){
                                certifcateLink = '<a target="_blank" href="' + quizzes[row].certificate_link + '">'+ ls('View') +'</a>';
                            }

                            let data = {
                                quizName: quizzes[row].name,
                                score: quizzes[row].score,
                                detailedReport: quizzes[row].detailed_report,
                                dateCompleted: quizzes[row].completed_date,
                                certificateLink: certifcateLink,
                            };

                            if (isDefined(wp.hooks)) {
                                data = wp.hooks.applyFilters('tc_table_data_userSingleCourseQuizzesTable', data, row, quizzes);
                            }

                            reportingTables.tableObjects['userSingleCourseQuizzesTable'].row.add(data);
                        }
                    }

                    reportingTables.tableObjects['userSingleCourseQuizzesTable'].draw();

                    // Assigments
                    let assignments = response.data.assigments;

                    for (let row in assignments) {
                        if (assignments.hasOwnProperty(row)) {

                            let data = {
                                assignmentName: assignments[row].name,
                                approval: assignments[row].approval_status,
                                submittedOn: assignments[row].completed_date
                            };

                            if (isDefined(wp.hooks)) {
                                data = wp.hooks.applyFilters('tc_table_data_userSingleCourseAssigmentsTable', data, row, assignments);
                            }

                            reportingTables.tableObjects['userSingleCourseAssignmentsTable'].row.add(data);
                        }
                    }

                    reportingTables.tableObjects['userSingleCourseAssignmentsTable'].draw();

                    // Tin Canny statements
                    let statements = response.data.statements;
                    for (let row in statements) {
                        if (statements.hasOwnProperty(row)) {

                            reportingTables.tableObjects['userSingleCourseTinCanTable'].row.add({
                                lesson: statements[row].related_post,
                                module: statements[row].module,
                                target: statements[row].target,
                                action: statements[row].action,
                                result: statements[row].result,
                                date: statements[row].date,
                            });
                        }
                    }

                    reportingTables.tableObjects['userSingleCourseTinCanTable'].draw();
                })

            }

            if ('userSingleCoursesOverviewTable' === tableType) {

                // The course that is being viewed
                let apiData = {
                    tableType: tableType,
                    rows: [],
                };

                let rows;

                if (typeof reportingApiSetup.disablePerformanceEnhancments !== 'undefined' && '1' === reportingApiSetup.disablePerformanceEnhancments) {
                    rows = reportingTables.tableObjects[tableType].rows();
                } else {
                    rows = reportingTables.tableObjects[tableType].rows({page: 'current'});
                }

                for (let row in rows[0]) {
                    let rowData = reportingTables.tableObjects[tableType].row(row).data();

                    apiData.rows.push(
                        {
                            rowId: row,
                            ID: rowData.ID
                        }
                    );
                }

                // The user that is being viewed
                apiData.userId = this.tableObjectsData.userSingleCoursesOverviewTable.ID;

                let apiDataCall;

                if (typeof reportingApiSetup.isolated_group_id === 'string' && parseInt(reportingApiSetup.isolated_group_id)) {
                    apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data' + '/?group_id=' + reportingApiSetup.isolated_group_id, apiData);
                } else {
                    apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data', apiData);
                }

                reportingTables.triggeredUserSingleOverview = true;

                apiDataCall.done(reportingTables.drawUserSingleCoursesOverviewTable);

            }

            // Api Call wrapper get course overview ( User List with data and Course List with data )
            if ('courseSingleTable' === tableType) {

                // The course that is being viewed
                let apiData = {
                    tableType: tableType,
                    rows: [],
                    tablePage: reportingTables.tableObjects[tableType].page.info()
                };

                let rows;

                if (typeof reportingApiSetup.disablePerformanceEnhancments !== 'undefined' && '1' === reportingApiSetup.disablePerformanceEnhancments) {
                    rows = reportingTables.tableObjects[tableType].rows();
                } else {
                    rows = reportingTables.tableObjects[tableType].rows({page: 'current'});
                }


                for (let row in rows[0]) {
                    let rowData = reportingTables.tableObjects[tableType].row(row).data();

                    apiData.rows.push(
                        {
                            rowId: row,
                            ID: rowData.ID
                        }
                    );
                }

                // The course that is being viewed
                apiData.courseId = this.tableObjectsData.courseSingleTable.courseId;

                let apiDataCall;

                if (typeof reportingApiSetup.isolated_group_id === 'string' && parseInt(reportingApiSetup.isolated_group_id)) {
                    apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data' + '/?group_id=' + reportingApiSetup.isolated_group_id, apiData);
                } else {
                    apiDataCall = uoReportingAPI.reportingApiCallDataPost('table_data', apiData);
                }

                apiDataCall.done(function (response) {

                    dataObject.additionalData[tableType] = response.additionalData;

                    for (let row in response.data) {
                        if (isDefined(response.data[row].completed_date) && isDefined(response.data[row].progress) && isDefined(response.data[row].quiz_average)) {
                            let rowData = reportingTables.tableObjects[tableType].row(row).data();

                            rowData.completionDate = response.data[row].completed_date;
                            rowData.percentComplete = response.data[row].progress + '%';

                            if ('' === response.data[row].quiz_average) {
                                rowData.quizAvg = '-';
                            } else {
                                rowData.quizAvg = response.data[row].quiz_average + '%';
                            }

                            if (isDefined(wp.hooks)) {
                                rowData = wp.hooks.applyFilters('tc_table_data_drawcourseSingleTableData', rowData, row, response);
                            }

                            reportingTables.tableObjects[tableType].row(row).data(rowData).draw('page');
                        }
                    }
                })
            }
        }
    },

    drawUserSingleCoursesOverviewTable: function (response) {

        dataObject.additionalData['userSingleCoursesOverview'] = response.additionalData;

		var userDataList = dataObject.userList.userOverview

		let coursesEnrolled = userDataList[response.user_id].enrolled;

		let inProgress = userDataList[response.user_id].in_progress;

		let completed = userDataList[response.user_id].completed;
		let notStarted = coursesEnrolled - completed - inProgress;

		if (0 >= notStarted) {
			notStarted = 0;
		}

        for (let row in response.data) {

            if (typeof response.data[row].completed_date === 'undefined' || typeof response.data[row].completed_date.display === 'undefined') {
                response.data[row].completed_date = {};
                response.data[row].completed_date.display = '';
                response.data[row].completed_date.timestamp = '0';
            }

            let rowData = reportingTables.tableObjects['userSingleCoursesOverviewTable'].row(row).data();

            if (typeof response.data[row].progress === 'undefined') {
                response.data[row].progress = 0;
            }

            rowData.percentComplete = response.data[row].progress + '%';
            rowData.completionDate = response.data[row].completed_date;

            if ('' === response.data[row].avg_score) {
                rowData.avgQuizScore = '-';
            } else {
                rowData.avgQuizScore = response.data[row].avg_score;
            }

            if (isDefined(wp.hooks)) {
                rowData = wp.hooks.applyFilters('tc_table_data_drawUserSingleCoursesOverviewTable', rowData, response.data[row]);
            }

            reportingTables.tableObjects['userSingleCoursesOverviewTable'].row(row).data(rowData);
        }

        if (true === reportingTables.triggeredUserSingleOverview) {
            //Update user single overview table
            reportingTables.tableObjects['userSingleOverviewTable'].row(0).data(
                {
                    coursesEnrolled: coursesEnrolled,
                    notStarted: notStarted,
                    inProgress: inProgress,
                    completed: completed
                }
            ).draw('page');
        }

        reportingTables.triggeredUserSingleOverview = false;
        reportingTables.tableDrawActive['userSingleCoursesOverviewTable'] = false;

        if (typeof reportingTables.triggerCSV['userSingleCoursesOverviewTable'] !== 'undefined') {
            if (false !== reportingTables.triggerCSV['userSingleCoursesOverviewTable']) {
                var csv = reportingTables.triggerCSV['userSingleCoursesOverviewTable'];
                if (jQuery.fn.dataTable.ext.buttons.csvHtml5.available(csv.dt, csv.config)) {
                    jQuery.fn.dataTable.ext.buttons.csvHtml5.action(csv.e, csv.dt, csv.button, csv.config);
                } else {
                    jQuery.fn.dataTable.ext.buttons.csvFlash.action(csv.e, csv.dt, csv.button, csv.config);
                }
            }
        }

        reportingTables.triggerCSV['userSingleCoursesOverviewTable'] = false;

    },

    /*
     TABLES DATA Creation
     */
    coursesOverviewTableData: function () {


        var courseList = dataObject.getCourseList();
        var userList = dataObject.getUserList();

        // Default values
        var tableData = [];
        var userAverages = [],
            i = 0,
            courseAssociatedPosts,
            courseID,
            courseName,
            courseType;

        // Loop through all courses
        jQuery.each(courseList, function (courseId, courseData) {


            var userAccess = [],
                enrolled = 0,
                notStarted = 0,
                inProgress = 0,
                completed = 0,
                avgQuiz = '',
                avgCompletionTime = '',
                avgTimeSpent = '',
                completion = '';

            courseID = courseId;
            courseName = courseData.post_title;
            courseType = courseData.course_price_type;

            if ('' === courseName) {
                courseName = sprintf(reportingApiSetup.localizedStrings.missingTitleCourseId, reportingApiSetup.learnDashLabels.course) + courseData.ID;
            }

            if ('open' === courseType) {
                enrolled = Object.keys(userList.allUserIds).length;
                userAccess = userList.allUserIds;
            } else {

                if ("1" === reportingApiSetup.optimized_build) {
                    if (typeof userList.course_user_access_list[courseID] !== 'undefined') {

                        // Check enrollment
                        if ('open' === courseType) {
                            userAccess = userList.allUserIds;
                        } else {
                            userAccess = dataObject.userList.course_user_access_list[courseID];
                        }

                        jQuery.each(userAccess, function (key, userID) {
                            if (typeof userList.userOverview[userID] === 'undefined') {
                                return true;
                            }
                            enrolled++;
                        });
                    } else {
                        enrolled = 0;
                    }
                }
            }

            // add amount user enrolled to course
            dataObject.addToCourseList(courseID, 'usersEnrolled', enrolled);

            if (typeof dataObject.userList.courseQuizAverages !== 'undefined' && typeof dataObject.userList.courseQuizAverages[courseId] !== 'undefined') {
                if ('false' === dataObject.userList.courseQuizAverages[courseId]) {
                    avgQuiz = '';
                } else {
                    avgQuiz = dataObject.userList.courseQuizAverages[courseId] + '%';
                }
            }


            if ("1" !== reportingApiSetup.optimized_build) {
                courseAssociatedPosts = courseData.associatedPosts;
                userAverages.cumalitivePercentage = 0;
                userAverages.amountQuizzes = 0;

                if (0 === userAverages.amountCompletionTime || 0 === userAverages.cumalitiveCompletionTime) {
                    avgCompletionTime = '';
                } else {
                    avgCompletionTime = dataObject.formatSecondsToDate(userAverages.cumalitiveCompletionTime / userAverages.amountCompletionTime);

                }

                // add average timer results to course
                dataObject.addToCourseList(courseID, 'userCompletionTimeAverage', avgCompletionTime, 'string');

                userAverages.cumalitiveTimeSpent = 0;
                userAverages.amountTimeSpent = 0;

                jQuery.each(userAccess, function (innerIndex, userID) {

                    if (typeof userList[userID] === 'undefined') {
                        return true;
                    }

                    if (userList[userID][courseID] && userList[userID][courseID].time_spent) {

                        userAverages.cumalitiveTimeSpent += userList[userID][courseID].time_spent;
                        userAverages.amountTimeSpent++;

                    }
                });

                if (0 === userAverages.amountTimeSpent || 0 === userAverages.cumalitiveTimeSpent) {
                    avgTimeSpent = '';
                } else {
                    avgTimeSpent = dataObject.formatSecondsToDate(userAverages.cumalitiveTimeSpent / userAverages.amountTimeSpent);
                }
            }

            userAverages.cumalitiveCompletionTime = 0;
            userAverages.amountCompletionTime = 0;

            jQuery.each(userAccess, function (innerIndex, userID) {

                if (typeof userList[userID] === 'undefined') {
                    return true;
                }

                if (userList[userID][courseID] && userList[userID][courseID].completed_time) {

                    userAverages.cumalitiveCompletionTime += userList[userID][courseID].completed_time;
                    userAverages.amountCompletionTime++;

                }
            });

            // add average time spent results to course
            dataObject.addToCourseList(courseID, 'userTimeSpentAverage', avgTimeSpent, 'string');

            if (typeof userList.completed[courseID] !== 'undefined') {
                completed = userList.completed[courseID];
            } else {
                completed = 0;
            }

            if (typeof userList.inProgress[courseID] !== 'undefined') {
                inProgress = userList.inProgress[courseID];
            } else {
                inProgress = 0;
            }

            notStarted = enrolled - (completed + inProgress);


            // add amount user notStarted course
            dataObject.addToCourseList(courseID, 'notStarted', notStarted);

            // add amount user inProgress course
            dataObject.addToCourseList(courseID, 'inProgress', inProgress);

            // add amount user completed course
            dataObject.addToCourseList(courseID, 'completed', completed);


            if (0 == enrolled || 0 == completed) {
                completion = 0 + '%';
            } else {
                completion = Math.floor((completed / enrolled) * 100) + '%';
            }


            // add completion percentage of course
            dataObject.addToCourseList(courseID, 'completion', completion, 'string');

            //tableData[i] = [courseID, courseName, enrolled, notStarted, inProgress, completed, avgQuiz, avgCompletionTime, avgTimeSpent, completion];

            // Details
            let details = `<span class="reporting-table-see-details">${ reportingApiSetup.localizedStrings.tablesButtonSeeDetails }</span>`;

            // Course name
            courseName = `<span class="reporting-table-see-details">${ courseName }</span>`;

            tableData.push(
                {
                    ID: courseID,
                    course: courseName,
                    enrolled: enrolled,
                    notStarted: notStarted,
                    inProgress: inProgress,
                    completed: completed,
                    avgQuizScore: avgQuiz,
                    percentComplete: completion,
                    details: details
                }
            );

            i++;
        });

        dataObject.addToDataTables('coursesOverviewTable', tableData);

        return tableData;

    },

    courseSingleTableData: function (courseID) {

        let userList = dataObject.userList;

        let courseType,
            userAccess,
            courseSingleTableData = [],
			userDisplayName,
            userCompletionDate,
            percentCompleted,
            completionTime,
            timeSpent;

        courseType = dataObject.courseList[courseID].course_price_type;

        // Check enrollment
        if ('open' === courseType) {
            userAccess = userList.allUserIds;
        } else {
            userAccess = dataObject.userList.course_user_access_list[courseID];
        }

        jQuery.each(userAccess, function (key, userID) {

            if (typeof userList.userOverview[userID] === 'undefined') {
                return true;
            }

            let userData = userList.userOverview[userID];

            if (
                typeof userList.userOverview[userID].completed_on !== 'undefined'
                && typeof userList.userOverview[userID].completed_on[courseID] !== 'undefined'
            ) {
                userCompletionDate = userList.userOverview[userID].completed_on[courseID];
            } else {
                userCompletionDate = {display: "", timestamp: '0'};
            }

            percentCompleted = ls('Not Started');
            completionTime = '';
            timeSpent = '';

            if (typeof userData[courseID] !== 'undefined') {

                if (typeof userData[courseID].completed_on !== 'undefined') {
                    userCompletionDate = userData[courseID].completed_on;
                }

                if (0 !== userData[courseID].completed) {
                    percentCompleted = Math.ceil(userData[courseID].completed / userData[courseID].total * 100) + '%';
                }


                if (typeof userData[courseID].completed_time !== 'undefined') {
                    completionTime = dataObject.formatSecondsToDate(userData[courseID].completed_time);
                }

                if (typeof userData[courseID].time_spent !== 'undefined') {
                    timeSpent = dataObject.formatSecondsToDate(userData[courseID].time_spent);
                }


            } else {
                // console.log('Course data not found ' + userID);
            }

            // Actions
            let details = `<span class="reporting-table-see-details">${ reportingApiSetup.localizedStrings.tablesButtonSeeDetails }</span>`;


            // User Display Name
            userDisplayName = `<span class="reporting-table-see-details">${ userData.display_name }</span>`;

            let rowData = {
                ID: userID,
                displayName: userData.display_name,
				firstName: userData.first_name,
				lastName: userData.last_name,
				userLogin: userData.user_login,
                email: userData.user_email,
                quizAvg: '',
                completionDate: userCompletionDate,
                percentComplete: '',
                details: details
            };

            if (isDefined(wp.hooks)) {
                rowData = wp.hooks.applyFilters('tc_table_data_drawcourseSingleTableData', rowData);
            }

            courseSingleTableData.push(rowData);

        });

        return courseSingleTableData;
    },

    drawcourseSingleTableData: function (response) {

        dataObject.additionalData['courseSingleTable'] = response.additionalData;

        let tableType = 'courseSingleTable';

        for (let row in response.data) {

            let rowData = reportingTables.tableObjects[tableType].row(row).data();

            rowData.completionDate = response.data[row].completed_date;
            rowData.percentComplete = response.data[row].progress + '%';

            if ('' === response.data[row].quiz_average) {
                rowData.quizAvg = response.data[row].quiz_average;
            } else {
                rowData.quizAvg = response.data[row].quiz_average + '%';
            }

            if (isDefined(wp.hooks)) {
                rowData = wp.hooks.applyFilters('tc_table_data_drawcourseSingleTableData', rowData, row, response);
            }

            reportingTables.tableObjects[tableType].row(row).data(rowData)
        }

        reportingTables.tableObjects[tableType].draw('page');

        reportingTables.tableDrawActive['courseSingleTable'] = false;

        if (typeof reportingTables.triggerCSV['courseSingleTable'] !== 'undefined') {
            if (false !== reportingTables.triggerCSV['courseSingleTable']) {
                var csv = reportingTables.triggerCSV['courseSingleTable'];
                if (jQuery.fn.dataTable.ext.buttons.csvHtml5.available(csv.dt, csv.config)) {
                    jQuery.fn.dataTable.ext.buttons.csvHtml5.action(csv.e, reportingTables.tableObjects[tableType], csv.button, csv.config);
                } else {
                    jQuery.fn.dataTable.ext.buttons.csvFlash.action(csv.e, reportingTables.tableObjects[tableType], csv.button, csv.config);
                }
            }
        }

        reportingTables.triggerCSV['courseSingleTable'] = false;
    },

    drawcourseSingleTableDataExcel: function (response) {

            dataObject.additionalData['courseSingleTable'] = response.additionalData;

            let tableType = 'courseSingleTable';

            for (let row in response.data) {

                let rowData = reportingTables.tableObjects[tableType].row(row).data();

                rowData.completionDate = response.data[row].completed_date;
                rowData.percentComplete = response.data[row].progress + '%';

                if ('' === response.data[row].quiz_average) {
                    rowData.quizAvg = response.data[row].quiz_average;
                } else {
                    rowData.quizAvg = response.data[row].quiz_average + '%';
                }

                if (isDefined(wp.hooks)) {
                    rowData = wp.hooks.applyFilters('tc_table_data_drawcourseSingleTableData', rowData, row, response);
                }

                reportingTables.tableObjects[tableType].row(row).data(rowData)
            }

            reportingTables.tableObjects[tableType].draw('page');

            reportingTables.tableDrawActive['courseSingleTable'] = false;

            if (typeof reportingTables.triggerExcel['courseSingleTable'] !== 'undefined') {
                if (false !== reportingTables.triggerExcel['courseSingleTable']) {
                    var excel = reportingTables.triggerExcel['courseSingleTable'];

                    if (jQuery.fn.dataTable.ext.buttons.excelHtml5.available(excel.dt, excel.config)) {
                        jQuery.fn.dataTable.ext.buttons.excelHtml5.action(excel.e, reportingTables.tableObjects[tableType], excel.button, excel.config);
                    } else {
                        jQuery.fn.dataTable.ext.buttons.excelFlash.action(excel.e, reportingTables.tableObjects[tableType], excel.button, excel.config);
                    }
                }
            }

            reportingTables.triggerExcel['courseSingleTable'] = false;
        },

    courseSingleOverviewSummaryTableDataObject: function (courseID) {

        var courseData = dataObject.courseList[courseID];
        var userList = dataObject.userList;
        let userAccess;
        let usersEnrolled = 0;

        // Check enrollment
        if ('open' === courseData.course_price_type) {

            usersEnrolled = Object.keys(userList.allUserIds).length;
        } else {
            if ("1" === reportingApiSetup.optimized_build) {
                if (typeof userList.course_user_access_list[courseID] !== 'undefined') {

                    // Check enrollment
                    if ('open' === courseData.course_price_type) {
                        userAccess = userList.allUserIds;
                    } else {
                        userAccess = dataObject.userList.course_user_access_list[courseID];
                    }

                    jQuery.each(userAccess, function (key, userID) {
                        if (typeof userList.userOverview[userID] === 'undefined') {
                            return true;
                        }
                        usersEnrolled++;
                    });
                } else {
                    usersEnrolled = 0;
                }
            }
        }

        //averageCompletionTime = dataObject.courseList[courseID].userCompletionTimeAverage,
        var userQuizAverage = userList.courseQuizAverages[courseID];

        // Check for undefined values
        usersEnrolled = usersEnrolled == undefined || usersEnrolled == false || usersEnrolled == 'false' ? '0' : usersEnrolled;
        userQuizAverage = userQuizAverage == undefined || userQuizAverage == false || userQuizAverage == 'false' ? '0%' : `${userQuizAverage}%`;

        return [
            {
                usersEnrolled: usersEnrolled,
                //averageCompletionTime,
                avgQuizScore: userQuizAverage
            }
        ];

    },

    usersOverviewTableData: function () {

        var userDataList = dataObject.userList.userOverview,
            tableData = new Array();

        for (let user in userDataList) {
            // Actions
            let details = `<span class="reporting-table-see-details">${ reportingApiSetup.localizedStrings.tablesButtonSeeDetails }</span>`;

            // Course name
            let displayName = `<span class="reporting-table-see-details">${ userDataList[user].display_name }</span>`;

            let coursesEnrolled = userDataList[user].enrolled;

            let inProgress = userDataList[user].in_progress;

            let completed = userDataList[user].completed;
            let notStarted = coursesEnrolled - completed - inProgress;

            if (0 >= notStarted) {
                notStarted = 0;
            }

            tableData.push(
                {
                    ID: userDataList[user].ID,
                    displayName: displayName,
                    firstName: userDataList[user].first_name,
                    lastName: userDataList[user].last_name,
                    userLogin: userDataList[user].user_login,
                    email: userDataList[user].user_email,
                    coursesEnrolled: coursesEnrolled,
                    notStarted: notStarted,
                    inProgress: inProgress,
                    completed: completed,
                    details: details
                }
            );
        }

        dataObject.addToDataTables('usersOverviewTableData', tableData);

        return tableData;
    },

    userSingleOverviewTableData: function (userID) {

        var tableData = [];

        //var enrolledCourseList = [];
        var enrolled = 0;
        var notStarted = 0;
        var inProgress = 0;
        var completed = 0;

        tableData.push(
            {
                coursesEnrolled: enrolled,
                notStarted: notStarted,
                inProgress: inProgress,
                completed: completed
            }
        );
        return tableData;

    },

    userSingleCoursesOverviewTableData: function (userID) {

        var tableData = [];

        for (let courseID in dataObject.courseList) {

            if (typeof  dataObject.userList.course_user_access_list[courseID] !== 'undefined'
                &&
                (
                    typeof  dataObject.userList.course_user_access_list[courseID][userID] !== 'undefined' // TODO THIS WOULD NEED TO CHANGE BECAUSE THE userID as a KEY NO LONGER EXISTS... in array would need to be used.. ref:$course_users[ $course_id ]=$course_users_temp;
                    || dataObject.courseList[courseID].course_price_type === 'open'
                )
            ) {


                var courseName = dataObject.courseList[courseID].post_title;

                if ('' === courseName) {
                    courseName = sprintf(reportingApiSetup.localizedStrings.missingTitleCourseId, reportingApiSetup.learnDashLabels.course) + courseID;
                }

                // Actions
                let details = `<span class="reporting-table-see-details">${ reportingApiSetup.localizedStrings.tablesButtonSeeDetails }</span>`;

                // Course name
                courseName = `<span class="reporting-table-see-details">${ courseName }</span>`;

                let rowData = {
                    ID: courseID,
                    course: courseName,
                    percentComplete: '',
                    completionDate: {display: '', timestamp: '0'},
                    avgQuizScore: '',
                    details: details
                };

                if (isDefined(wp.hooks)) {
                    rowData = wp.hooks.applyFilters('tc_table_data_drawUserSingleCoursesOverviewTable', rowData);
                }

                tableData.push(rowData);
            }
        }

        return tableData;
    },

    userSingleCourseProgressSummaryTable: function (ID) {

        var courseID = ID;

        var userID = reportingTabs.viewingCurrentUserID;

        var _percentComplete = dataObject.singleUserCoursePercentComplete(userID, courseID);

        var _status = dataObject.singleUserCourseStatus(userID, courseID);

        //var completionTime = dataObject.singleUserCourseCompletionTime(userID, courseID);

        var _avgQuizScore = dataObject.singleUserCourseAvgQuizScore(userID, courseID);

        //var timeSpent = dataObject.singleUserCourseTimeSpent(userID, courseID);

        let rowData = {
            status: _status,
            //completionTime,
            percentComplete: _percentComplete,
            avgQuizScore: _avgQuizScore,
            certificateLink: ''
            //timeSpent
        };

        if (isDefined(wp.hooks)) {
            rowData = wp.hooks.applyFilters('tc_table_data_userSingleCourseProgressSummaryTable', rowData, 0);
        }


        return [rowData];

    },

    userSingleCourseLessonsTableData: function (ID) {

        var tableData = [];

        // Get all unique associated posts ... with LD 2.5 there maybe a double course association
        if (typeof dataObject.courseList[ID].associatedPosts === 'undefined') {
            return tableData;
        }

        if (typeof dataObject.courseList[ID].associatedPosts !== 'undefined') {
            var associatedPosts = dataObject.courseList[ID].associatedPosts.filter(this.arrayUnique);

            jQuery.each(associatedPosts, function (index, postID) {
                if (typeof dataObject.lessonList[postID] !== 'undefined') {

                    var postTitle = dataObject.lessonList[postID].post_title;
                    var viewingCurrentUserID = reportingTabs.viewingCurrentUserID;
                    var status = ls('Not Complete');

                    if (typeof dataObject.userList.userOverview[viewingCurrentUserID][ID] !== 'undefined') {
                        if (typeof dataObject.userList.userOverview[viewingCurrentUserID][ID].lessons[postID] !== 'undefined') {
                            status = ls('Completed');
                        }
                    }

                    tableData.push(
                        {
                            lessonName: postTitle,
                            status: status
                        }
                    );
                }
            });
        }


        return tableData;
    },

    userSingleCourseTopicsTableData: function (ID) {

        var tableData = [];

        if (typeof dataObject.courseList[ID].associatedPosts === 'undefined') {
            return tableData;
        }

        if (typeof dataObject.courseList[ID].associatedPosts !== 'undefined') {
            // Get all unique associated posts ... with LD 2.5 there maybe a double course association
            var associatedPosts = dataObject.courseList[ID].associatedPosts.filter(this.arrayUnique);

            jQuery.each(associatedPosts, function (index, postID) {

                if (typeof dataObject.topicList[postID] !== 'undefined') {

                    var postTitle = dataObject.topicList[postID].post_title;
                    var viewingCurrentUserID = reportingTabs.viewingCurrentUserID;
                    var status = ls('Not Complete');

                    if (typeof dataObject.userList.userOverview[viewingCurrentUserID][ID] !== 'undefined') {

                        if (typeof dataObject.userList.userOverview[viewingCurrentUserID][ID].topics[postID] !== 'undefined') {
                            status = ls('Completed');
                        }
                        else {

                            jQuery.each(dataObject.userList.userOverview[viewingCurrentUserID][ID].topics, function (lessonID, topicArray) {
                                if (typeof  topicArray[postID] !== 'undefined' && topicArray[postID] === 1) {
                                    status = ls('Completed');
                                }
                            })

                        }
                    }

                    tableData.push([postTitle, status]);
                }
            });
        }
        return tableData;
    },

    userSingleCourseQuizzesTableData: function (ID) {

        var tableData = [];
        if (typeof dataObject.courseList[ID].associatedPosts === 'undefined') {
            return tableData;
        }

        // Get all unique associated posts ... with LD 2.5 there maybe a double course association
        var associatedPosts = dataObject.courseList[ID].associatedPosts.filter(this.arrayUnique);

        jQuery.each(associatedPosts, function (index, courseAssociatedPost) {


            var viewingCurrentUserID = reportingTabs.viewingCurrentUserID;
            // Make sure the user has data for the current module before access module data
            if (typeof dataObject.userList.userOverview[viewingCurrentUserID]['quizzes'] !== 'undefined' && typeof dataObject.userList[viewingCurrentUserID]['quizzes'][0][courseAssociatedPost] !== 'undefined') {

                // Make sure the user has data for the current module before access module data
                if (typeof dataObject.userList[viewingCurrentUserID]['quizzes'] !== 'undefined' && typeof dataObject.userList.userOverview[viewingCurrentUserID]['quizzes'][0][courseAssociatedPost] !== 'undefined') {

                    // Make sure the current module is a quiz
                    if (typeof dataObject.userList.userOverview[viewingCurrentUserID] !== 'undefined' &&
                        typeof dataObject.userList.userOverview[viewingCurrentUserID]['quizzes'][0][courseAssociatedPost].type !== 'undefined' &&
                        dataObject.userList.userOverview[viewingCurrentUserID]['quizzes'][0][courseAssociatedPost].type === 'quiz') {

                        // Make sure there is quiz data
                        if (1 <= dataObject.userList.userOverview[viewingCurrentUserID]['quizzes'][0][courseAssociatedPost].attempts) {

                            if (typeof dataObject.quizList[courseAssociatedPost] !== 'undefined') {
                                var postTitle = dataObject.quizList[courseAssociatedPost].post_title;
                                var score = ls('Not Complete');
                                var dateCompleted = '';

                                var count;
                                var attempts = dataObject.userList.userOverview[viewingCurrentUserID]['quizzes'][0][courseAssociatedPost].attempts;

                                for (count = 0; count < attempts; count++) {
                                    score = dataObject.userList.userOverview[viewingCurrentUserID]['quizzes'][0][courseAssociatedPost][count].percentage + '%';

                                    dateCompleted = dataObject.userList.userOverview[viewingCurrentUserID]['quizzes'][0][courseAssociatedPost][count].time;
                                    var d = new Date(dateCompleted * 1000);
                                    var month = (d.getMonth() + 1);
                                    var year = d.getFullYear();
                                    if (10 > month) {
                                        month = '0' + month;
                                    }
                                    var date = d.getDate();
                                    dateCompleted = year + '-' + month + '-' + date;

                                    tableData.push([postTitle, score, dateCompleted]);

                                }
                            }
                        }
                    }
                }
            }
        });


        return tableData;
    },

    userSingleCourseAssignmentsTableData: function (ID) {
        var tableData = [];
        if (typeof dataObject.courseList[ID].associatedPosts === 'undefined') {
            return tableData;
        }

        var viewingCurrentUserID = reportingTabs.viewingCurrentUserID;

        if (typeof dataObject.courseList[ID].associatedPosts !== 'undefined') {
            // Get all unique associated posts ... with LD 2.5 there maybe a double course association
            var associatedPosts = dataObject.courseList[ID].associatedPosts.filter(this.arrayUnique);

            jQuery.each(associatedPosts, function (index, postID) {
                if (typeof dataObject.assignmentList[postID] !== 'undefined') {

                    var postTitle = '<a href="' + dataObject.links.assignment + '?post=' + postID + '&action=edit">' + dataObject.assignmentList[postID].post_title + '</a>';

                    var completion = ls('Not Approved');
                    var submittedOn = '';


                    if (typeof dataObject.userList.userOverview[viewingCurrentUserID][ID] !== 'undefined') {
                        if (typeof dataObject.userList.userOverview[viewingCurrentUserID][postID] !== 'undefined') {

                            completion = dataObject.userList.userOverview[viewingCurrentUserID][postID].approval_status;
                            if (1 === completion) {
                                completion = ls('Approved');
                            }
                            submittedOn = dataObject.userList.userOverview[viewingCurrentUserID][postID].completed_date;

                            tableData.push([postTitle, completion, submittedOn]);
                        }
                    }


                }
            });
        }
        return tableData;
    },

    userSingleCourseTinCanTableData: function (courseID) {

        var tableData = [];
        var viewingCurrentUserID = reportingTabs.viewingCurrentUserID;

        if (typeof dataObject.userList.userOverview[viewingCurrentUserID].tinCanStatements !== 'undefined'
            && typeof dataObject.userList.userOverview[viewingCurrentUserID].tinCanStatements[courseID] !== 'undefined') {

            var courseTinCanStatements = dataObject.userList.userOverview[viewingCurrentUserID].tinCanStatements[courseID];

            jQuery.each(courseTinCanStatements, function (index, lessons) {

                jQuery.each(lessons, function (index, statement) {

                    tableData.push([statement.lesson_name, statement.module_name, statement.target_name, statement.verb, statement.result, statement.xstored]);
                });
            });
        }

        return tableData;
    },

    createCsvFileName: function (tableType, ID) {
        var fileName,
            now,
            date,
            table,
            courseId,
            userId;

        now = new Date(Date.now());
        date = now.getFullYear() + '-' + (now.getMonth() + 1) + '-' + now.getDate() + '_';

        table = tableType + '_';

        switch (tableType) {
            case 'coursesOverviewTable':
                courseId = '';
                userId = '';
                break;
            case 'courseSingleOverviewSummaryTable':
                courseId = ID;
                userId = '';
                break;
            case 'courseSingleTable':
                courseId = ID;
                userId = '';
                break;
            case 'usersOverviewTable':
                courseId = '';
                userId = '';
                break;
            case 'userSingleOverviewTable':
                courseId = '';
                userId = ID;
                break;
            case 'userSingleCoursesOverviewTable':
                courseId = '';
                userId = ID;
                break;
            case 'userSingleCourseProgressSummaryTable':
            case 'userSingleCourseLessonsTable':
            case 'userSingleCourseTopicsTable':
            case 'userSingleCourseQuizzesTable':
            case 'userSingleCourseAssignmentsTable':
            case 'userSingleCourseTinCanTable':
                courseId = ID;
                userId = reportingTabs.viewingCurrentUserID;
                break;
            default:
                courseId = '';
                userId = '';
        }

        let userEmail = '';
        if ('' !== userId) {
            if (typeof dataObject.userList.userOverview[userId] !== 'undefined') {
                userEmail = '_' + dataObject.userList.userOverview[userId].user_email;
            }

        }


        let courseSlug = '';

        if ('' !== courseId) {

            if (typeof dataObject.courseList[courseId] !== 'undefined') {
                courseSlug = '_' + dataObject.courseList[courseId].post_name;
            }
        }

        fileName = table + date + courseSlug + userEmail;

        if (isDefined(wp.hooks)) {
            fileName = wp.hooks.applyFilters('tc_createCsvFileName', fileName, tableType, ID);
        }

        return fileName;
    },

    arrayUnique: function (value, index, self) {
        return self.indexOf(value) === index;
    }

};
