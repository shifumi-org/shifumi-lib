// Global access to all functions and variables in name space
// @namespace sampleNamespace
if (typeof customToolkit === 'undefined') {
    // the namespace is not defined
    var customToolkit = {};
}

(function ($) { // Self Executing function with $ alias for jQuery

    /* Initialization  similar to include once but since all js is loaded by the browser automatically the all
     * we have to do is call our functions to initialize them, his is only run in the main configuration file
     */
    $(document).ready(function () {

        if ($('#uotc-group-report-table').length) {
            customToolkit.courseReport.constructor();
        }

        if ($('#uo-quiz-report-table').length) {
            customToolkit.quizReport.constructor();
        }

    });

    // Create new namespaced class.courseReport
    customToolkit.courseReport = {

        uoTable: null,

        uoHiddenTable: null,

        courseId: 0,

        constructor: function () {
            this.setTableDefaults();
            this.createTable();
            this.createHiddenTable();
            this.addEvents();

        },

        createHiddenTable: function (data) {

            var columns = [
                {width: '20%', data: 'user_name'},
                {width: '20%', data: 'quiz_name'},
                {width: '20%', data: 'quiz_score'},
                {width: '20%', data: 'quiz_date',
                    render: {
                        _: 'display',
                        sort: 'timestamp'
                    }
                }
            ];

            var tableVars = {
                "bFilter": false,
                aLengthMenu: [
                    [15, 30, 60, -1],
                    [15, 30, 60, "All"]
                ],
                iDisplayLength: 30,
                columns: columns,
                data: data,
            };

            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth() + 1; //January is 0!

            var yyyy = today.getFullYear();
            if (dd < 10) {
                dd = '0' + dd;
            }

            if (mm < 10) {
                mm = '0' + mm;
            }
            var today = dd + '-' + mm + '-' + yyyy;

            var groupName = $("#uo-group-report-group option:selected").text();
            if ('' === groupName) {
                groupName = $('#uo-group-report-selections').text()
            }
            var courseName = $("#uo-group-report-courses option:selected").text();

            groupName = strTrim(groupName);

            var fileName = capital_letters(groupName).replace(/\s+/g, '') + ' ' + capital_letters(courseName).replace(/\s+/g, '') + ' ' + today;

            tableVars.dom = 'Bfrtip';
            tableVars.buttons = [
                {
                    extend: 'csvHtml5',
                    title: capital_letters(groupName) + ' - ' + capital_letters(courseName) + ' On ' + today,
                    filename: fileName
                },
                {
                    extend: 'pdfHtml5',
                    title: capital_letters(groupName) + ' - ' + capital_letters(courseName) + ' On ' + today,
                    filename: fileName
                }];

            tableVars.language = {
                emptyTable: groupLeaderCourseReportSetup.i18n.emptyTable,
                info: groupLeaderCourseReportSetup.i18n.info,
                infoEmpty: groupLeaderCourseReportSetup.i18n.infoEmpty,
                infoFiltered: groupLeaderCourseReportSetup.i18n.infoFiltered,
                lengthMenu: groupLeaderCourseReportSetup.i18n.lengthMenu,
                loadingRecords: groupLeaderCourseReportSetup.i18n.loadingRecords,
                processing: groupLeaderCourseReportSetup.i18n.processing,
                search: '',
                searchPlaceholder: groupLeaderCourseReportSetup.i18n.searchPlaceholder,
                zeroRecords: groupLeaderCourseReportSetup.i18n.zeroRecords,
                paginate: {
                    first: groupLeaderCourseReportSetup.i18n.paginate.first,
                    last: groupLeaderCourseReportSetup.i18n.paginate.last,
                    next: groupLeaderCourseReportSetup.i18n.paginate.next,
                    previous: groupLeaderCourseReportSetup.i18n.paginate.previous
                },
                aria: {
                    sortAscending: groupLeaderCourseReportSetup.i18n.sortAscending,
                    sortDescending: groupLeaderCourseReportSetup.i18n.sortDescending
                }
            }

            if (this.uoHiddenTable) {
                this.uoHiddenTable.destroy();
            }

            this.uoHiddenTable = jQuery('#uotc-group-report-table--hidden').DataTable(tableVars);


        },

        createTable: function (data) {

            var dataWithUrlLinks = false;

            // Add URLs to name column in attribute is set via localized script
            if ('' !== sfwd_data.userReportUrl) {
                dataWithUrlLinks = [];
                $.each(data, function (key, row) {
                    console.log(row);
                    let _row = row;
                    _row.user_name = '<a href="' + sfwd_data.userReportUrl + '?user_id=' + row.user_id + '">' + row.user_name + '</a>';
                    dataWithUrlLinks.push(_row);
                })
            }

            console.log(dataWithUrlLinks);

            var columns = [
                {width: '20%', data: 'user_name'},
                {width: '20%', data: 'quiz_name'},
                {width: '20%', data: 'quiz_score'},
                {
                    width: '20%',
                    "orderable": false,
                    "searchable": false,
                    "data": 'quiz_modal',
                    "defaultContent": ''
                },
                {width: '20%', data: 'quiz_date',
                    render: {
                        _: 'display',
                        sort: 'timestamp'
                    }}
            ];

            var tableVars = {
                "bFilter": true,
                aLengthMenu: [
                    [15, 30, 60, -1],
                    [15, 30, 60, "All"]
                ],
                iDisplayLength: 30,
                columns: columns,
                columnDefs: [{
                    "targets": 2,
                    "orderable": false,
                    "searchable": false
                }],
                data: data,
                language: {
                    emptyTable: groupLeaderCourseReportSetup.i18n.emptyTable,
                    info: groupLeaderCourseReportSetup.i18n.info,
                    infoEmpty: groupLeaderCourseReportSetup.i18n.infoEmpty,
                    infoFiltered: groupLeaderCourseReportSetup.i18n.infoFiltered,
                    lengthMenu: groupLeaderCourseReportSetup.i18n.lengthMenu,
                    loadingRecords: groupLeaderCourseReportSetup.i18n.loadingRecords,
                    processing: groupLeaderCourseReportSetup.i18n.processing,
                    search: '',
                    searchPlaceholder: groupLeaderCourseReportSetup.i18n.searchPlaceholder,
                    zeroRecords: groupLeaderCourseReportSetup.i18n.zeroRecords,
                    paginate: {
                        first: groupLeaderCourseReportSetup.i18n.paginate.first,
                        last: groupLeaderCourseReportSetup.i18n.paginate.last,
                        next: groupLeaderCourseReportSetup.i18n.paginate.next,
                        previous: groupLeaderCourseReportSetup.i18n.paginate.previous
                    },
                    aria: {
                        sortAscending: groupLeaderCourseReportSetup.i18n.sortAscending,
                        sortDescending: groupLeaderCourseReportSetup.i18n.sortDescending
                    }
                }
            };

            if (this.uoTable) {
                this.uoTable.destroy();
            }

            this.uoTable = jQuery('#uotc-group-report-table').DataTable(tableVars);
        },

        addEvents: function () {

            $('#uo-group-report-group').on('change', function () {

                var groupId = $(this).val();

                customToolkit.reporting.groupId = groupId;

                if (customToolkit.reporting.isNormalInteger(groupId)) {
                    customToolkit.reporting.getGroupCourses(parseInt(groupId));
                }

            });

            $('#uo-group-report-courses').on('change', function () {

                var courseId = $(this).val();
                var groupId = $('#uo-group-report-selections').data('group-id');

                if (typeof groupId === 'undefined') {
                    groupId = $('#uo-group-report-group').val();
                }

                if (customToolkit.reporting.isNormalInteger(courseId)) {
                    customToolkit.courseReport.getUserCourseData(parseInt(courseId), parseInt(groupId));

                }

            });

            $('.uotc-report__btn.uotc-report__btn--csv').on('click', function () {
                $('#uotc-group-report-container--hidden .buttons-csv').click();
            });

            $('.uotc-report__btn.uotc-report__btn--pdf').on('click', function () {
                $('#uotc-group-report-container--hidden .buttons-pdf').click();
            });
        },

        getUserCourseData: function (courseId, groupId) {

            var dataVars = {
                groupId: groupId,
                courseId: courseId
            };

            // Rest api call to activate/deactivate plugins
            $.ajax({

                method: "POST",
                data: dataVars,
                url: groupLeaderCourseReportSetup.root + 'get_user_course_data/',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', groupLeaderCourseReportSetup.nonce);
                },

                success: function (response) {
                    customToolkit.courseReport.createTable(response.data);
                    customToolkit.courseReport.createHiddenTable(response.data);

                },
                fail: function (response) {
                    alert('Rest Call Failed');
                    console.log(response);
                }

            });

        },

        setTableDefaults: function () {
            jQuery.extend(jQuery.fn.dataTable.defaults, {
                searching: false,
                ordering: true,
                paging: true,
                responsive: true
            });
        }


    };

    customToolkit.quizReport = {

        uoTable: null,

        uoTableHidden: null,

        userId: 0,

        groupId: 0,

        courseId: 0,

        constructor: function () {
            this.createTable();
            this.createTableHidden();
            this.addEvents();

        },

        createTable: function (data) {

            var tableVars = {
                aLengthMenu: [
                    [15, 30, 60, -1],
                    [15, 30, 60, "All"]
                ],
                iDisplayLength: 30,
                columnDefs: [{
                    "targets": 3,
                    "orderable": false,
                    "searchable": false
                }],
            };

            if (null !== this.uoTable) {

                this.uoTable.destroy();

                tableVars.data = data.quizzes;

                tableVars.columns = [
                    {data: 'course_name'},
                    {data: 'quiz_name'},
                    {data: 'quiz_score'},
                    {
                        orderable: false,
                        searchable: false,
                        data: 'quiz_modal',
                        defaultContent: ''
                    },
                    {
                        data: 'quiz_date',
                        render: {
                            _: 'display',
                            sort: 'timestamp'
                        }
                    }
                ];

            }

            tableVars.language = {
                emptyTable: groupLeaderCourseReportSetup.i18n.emptyTable,
                info: groupLeaderCourseReportSetup.i18n.info,
                infoEmpty: groupLeaderCourseReportSetup.i18n.infoEmpty,
                infoFiltered: groupLeaderCourseReportSetup.i18n.infoFiltered,
                lengthMenu: groupLeaderCourseReportSetup.i18n.lengthMenu,
                loadingRecords: groupLeaderCourseReportSetup.i18n.loadingRecords,
                processing: groupLeaderCourseReportSetup.i18n.processing,
                search: '',
                searchPlaceholder: groupLeaderCourseReportSetup.i18n.searchPlaceholder,
                zeroRecords: groupLeaderCourseReportSetup.i18n.zeroRecords,
                paginate: {
                    first: groupLeaderCourseReportSetup.i18n.paginate.first,
                    last: groupLeaderCourseReportSetup.i18n.paginate.last,
                    next: groupLeaderCourseReportSetup.i18n.paginate.next,
                    previous: groupLeaderCourseReportSetup.i18n.paginate.previous
                },
                aria: {
                    sortAscending: groupLeaderCourseReportSetup.i18n.sortAscending,
                    sortDescending: groupLeaderCourseReportSetup.i18n.sortDescending
                }
            }

            this.uoTable = jQuery('#uo-quiz-report-table').DataTable(tableVars);

            $('.uotc-report__btn.uotc-report__btn--csv').on('click', function () {
                $('#uotc-user-report-container--hidden .buttons-csv').click();
            });

            $('.uotc-report__btn.uotc-report__btn--pdf').on('click', function () {
                $('#uotc-user-report-container--hidden .buttons-pdf').click();
            });
        },

        createTableHidden: function (data) {

            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth() + 1; //January is 0!

            var yyyy = today.getFullYear();
            if (dd < 10) {
                dd = '0' + dd;
            }

            if (mm < 10) {
                mm = '0' + mm;
            }

            var today = dd + '-' + mm + '-' + yyyy;

            var user_name = JSON.parse(sfwd_data.json).user_name;

            var fileName = user_name + ' - ' + today;

            var tableVars = {
                iDisplayLength: -1,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'csvHtml5',
                        title: fileName
                    },
                    {
                        extend: 'pdfHtml5',
                        title: fileName
                    }
                ],
            };

            if (null !== this.uoTableHidden) {

                this.uoTableHidden.destroy();

                tableVars.data = data.quizzes;
            }

            tableVars.language = {
                emptyTable: groupLeaderCourseReportSetup.i18n.emptyTable,
                info: groupLeaderCourseReportSetup.i18n.info,
                infoEmpty: groupLeaderCourseReportSetup.i18n.infoEmpty,
                infoFiltered: groupLeaderCourseReportSetup.i18n.infoFiltered,
                lengthMenu: groupLeaderCourseReportSetup.i18n.lengthMenu,
                loadingRecords: groupLeaderCourseReportSetup.i18n.loadingRecords,
                processing: groupLeaderCourseReportSetup.i18n.processing,
                search: '',
                searchPlaceholder: groupLeaderCourseReportSetup.i18n.searchPlaceholder,
                zeroRecords: groupLeaderCourseReportSetup.i18n.zeroRecords,
                paginate: {
                    first: groupLeaderCourseReportSetup.i18n.paginate.first,
                    last: groupLeaderCourseReportSetup.i18n.paginate.last,
                    next: groupLeaderCourseReportSetup.i18n.paginate.next,
                    previous: groupLeaderCourseReportSetup.i18n.paginate.previous
                },
                aria: {
                    sortAscending: groupLeaderCourseReportSetup.i18n.sortAscending,
                    sortDescending: groupLeaderCourseReportSetup.i18n.sortDescending
                }
            }

            this.uoTableHidden = jQuery('#uo-quiz-report-table-hidden').DataTable(tableVars);


        },

        flattenArray: function (array, remove_stats) {

            var flatArray = [];

            jQuery.each(array, function (key, value) {

                var quiz_name = value;
                jQuery.each(value.question, function (key2, value2) {
                    flatArray.push({
                        "quiz_name": value.quiz_name,
                        "quiz_score": value.quiz_score,
                        "answer": value.answer[key2],
                        "question": value.question[key2]
                    })
                });

            });

            return flatArray;

        },


        addEvents: function () {

            var _this = this;
            $('#uo-group-report-group').on('change', function () {

                var groupId = $(this).val();

                customToolkit.reporting.groupId = groupId;

                $('#uo-group-report-user option:first-child').attr("selected", "selected");
                $('#uo-group-report-course option:first-child').attr("selected", "selected");

                if (customToolkit.reporting.isNormalInteger(groupId)) {
                    customToolkit.reporting.getGroupCourses(parseInt(groupId));
                }

            });

            $('#uo-group-report-user').on('change', function () {

                var userId = $(this).val();
                customToolkit.quizReport.userId = userId;

                var courseId = $('#uo-group-report-course').val();
                customToolkit.quizReport.courseId = courseId;


                customToolkit.quizReport.getUserQuizData(courseId, userId);

            });

            $('#uo-group-report-course').on('change', function () {

                $('#uo-group-report-user option:first-child').attr("selected", "selected");

                if ($(this).hasClass('learner')) {

                    var userId = $(this).data('user-id');
                    customToolkit.quizReport.userId = userId;

                    var courseId = $(this).val();
                    customToolkit.quizReport.courseId = courseId;

                    customToolkit.quizReport.getUserQuizData(courseId, userId);

                } else {
                    console.log('leader');
                }


            });


            // Add event listener for opening and closing details
            $('#uo-quiz-report-table').on('click', 'td.details-control', function () {

                var tr = $(this).closest('tr');
                var row = customToolkit.quizReport.uoTable.row(tr);

                if (row.child.isShown()) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    // Open this row
                    row.child(customToolkit.quizReport.format(row.data())).show();
                    tr.addClass('shown');
                }
            });

        },

        format: function (d) {

            var rows = '';
            $.each(d.answer, function (key, value) {
                rows +=
                    '<tr>' +
                    '<td>' + d.question[key] + '</td>' +
                    '<td>' + value + '</td>' +
                    '</tr>';
            });
            // `d` is the original data object for the row
            return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">' + rows + '</table>';
        },

        getGroupUsers: function (xuserIds, xcourseId) {
            var dataVars = {
                groupUserIds: xuserIds,
                courseId: xcourseId
            };


            $.ajax({

                method: "POST",
                data: dataVars,
                url: groupLeaderCourseReportSetup.root + 'get_groups_user_quiz_data/',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', groupLeaderCourseReportSetup.nonce);
                },
                success: function (response) {

                    customToolkit.quizReport.createTable(response);
                    customToolkit.quizReport.createTableHidden(response);

                },
                fail: function (response) {
                    alert('Rest Call Failed');
                }

            });

        },

        getUserQuizData: function (courseId, userId) {

            if (!customToolkit.reporting.isNormalInteger(courseId)) {
                $('#uo-group-report-course').addClass('missing');
            }

            if (!customToolkit.reporting.isNormalInteger(courseId)) {
                return false;
            }

            if (!customToolkit.reporting.isNormalInteger(String(userId))) {
                return false;
            }

            $('#uo-group-report-course').removeClass('missing');

            var dataVars = {
                userId: customToolkit.quizReport.userId,
                courseId: customToolkit.quizReport.courseId
            };

            $.ajax({

                method: "POST",
                data: dataVars,
                url: groupLeaderCourseReportSetup.root + 'get_user_quiz_data/',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', groupLeaderCourseReportSetup.nonce);
                },
                success: function (response) {
                    customToolkit.quizReport.createTable(response);
                    customToolkit.quizReport.createTableHidden(response);

                },
                fail: function (response) {
                    alert('Rest Call Failed');
                    console.log(response);
                }

            });

        }

    };

    customToolkit.reporting = {

        getGroupCourses: function (groupId) {
            if (typeof sfwd_data !== 'undfined' && sfwd_data.groupCourses !== 'undefined' && sfwd_data.groupCourses[groupId] !== 'undefined') {
                this.createCourseDropDown(sfwd_data.groupCourses[groupId]);
            } else {
                this.createCourseDropDown([]);
            }

        },

        createCourseDropDown: function (posts) {

            var dropDown = $('#uo-group-report-courses'),
                options = '<option value="0">' + groupLeaderCourseReportSetup.i18n.selectCourseDropdownPlaceholder + '</option>';

            if (0 === posts.length) {
                dropDown.html('<option value="0">' + groupLeaderCourseReportSetup.i18n.selectCourseDropdownNoCourses + '</option>');
                return true;
            }

            $.each(posts, function (key, courseId) {
                let id = parseInt(courseId);

                if (typeof sfwd_data !== 'undfined' && sfwd_data.courses !== 'undefined' && sfwd_data.courses[id] !== 'undefined') {
                    options += '<option value="' + sfwd_data.courses[id]['ID'] + '">' + sfwd_data.courses[id]['post_title'] + '</option>';
                }

            });

            dropDown.html(options);
            dropDown.show();

        },

        createUserDropDown: function (users) {

            var dropDown = $('#uo-group-report-user'),
                options = '<option value="0">' + groupLeaderCourseReportSetup.i18n.selectStudentDropdownPlaceholder + '</option>';

            if (0 === users.length) {
                dropDown.html('<option value="0">' + groupLeaderCourseReportSetup.i18n.selectStudentDropdownNoStudents + '</option>');
                return true;
            }

            $.each(users, function (key, user) {

                var setRealName = user.display_name;

                if (typeof user.first_name !== 'undefined' ||
                    typeof user.last_name !== 'undefined') {
                    if (user.first_name !== '' ||
                        user.last_name !== '') {
                        setRealName = user.first_name + ' ' + user.last_name;
                    }
                }

                options += '<option value="' + user.id + '">' + setRealName + '</option>';
            });

            dropDown.html(options);
            dropDown.show();

        },

        isNormalInteger: function (str) {
            var n = ~~Number(str);
            return String(n) === str && n > 0;
        },

        getQueryVariable: function (variable) {
            var query = window.location.search.substring(1);
            var vars = query.split("&");
            for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split("=");
                if (pair[0] == variable) {
                    return pair[1];
                }
            }
            return (false);
        }

    }

    function capital_letters(str) {
        str = str.split(" ");

        for (let i = 0, x = str.length; i < x; i++) {
            if (typeof str[i][0] !== 'undefined') {
                str[i] = str[i][0].toUpperCase() + str[i].substr(1);
            }
        }

        return str.join(" ");
    }

    function strTrim(x) {
        return x.replace(/^\s+|\s+$/gm, '');
    }


})(jQuery);


