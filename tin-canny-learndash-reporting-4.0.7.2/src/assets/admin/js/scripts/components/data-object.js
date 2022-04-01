var dataObject = {

    dataObjectPopulated: false,
    courseList: {},
    lessonList: {},
    topicList: {},
    quizList: {},
    assignmentList: {},
    userList: {},
    groupsList: {},
    dataTables: {},
    learnDashLabels: {},
    links: {},
    localizedStrings: {},
    additionalData: [],

    getData: function ( tab = '', onSuccess = null, onError = null ){
        if ( ! reportingQueryString.isTinCanOnly ){

            dataObject.dataObjectPopulated = true;
            // Load UI from wp dashboard

            // Api Call wrapper get course overview ( User List with data and Course List with data )
            if (typeof reportingApiSetup.isolated_group_id === 'string' && parseInt(reportingApiSetup.isolated_group_id)) {
                var coursesOverviewApiCall = uoReportingAPI.reportingApiCall('courses_overview', '/?group_id=' + reportingApiSetup.isolated_group_id);
            } else {
                var coursesOverviewApiCall = uoReportingAPI.reportingApiCall('courses_overview');
            }


            coursesOverviewApiCall.fail(function (response) {

                var failedResponseEl = document.getElementById('failed-response');
                failedResponseEl.innerHTML = response.responseText;

                jQuery('#reporting-loader').hide('slow', function () {
                    failedResponseEl.style.display = "block";
                });

                // Check if the onError function is defined and invoke it
                if ( isDefined( onError ) ){
                    onError();
                } 
            });

            coursesOverviewApiCall.done((response) => {
                // Save data
                tinCannyDashboard.dataObject = response.data.userList.dashboard_data;

                // Set links
                this.setLinks( response.links );

                // Load dashboard
                tinCannyDashboard.renderData();

                // Load initial data into data object
                dataObject.setCourseList(response.data.courseList);
                dataObject.userList.completed = response.data.userList.completions;
                dataObject.userList.inProgress = response.data.userList.in_progress;
                dataObject.userList.allUserIds = response.data.userList.all_user_ids;
                dataObject.userList.courseAccessCount = response.data.userList.course_access_count;
                dataObject.userList.userOverview = response.data.userList.users_overview;
                dataObject.userList.course_user_access_list = response.data.userList.course_access_list;
                dataObject.userList.courseQuizAverages = response.data.userList.course_quiz_averages;
                dataObject.userList.courseCompletionByDates = response.data.userList.course_completion_by_dates;
                dataObject.userList.courseCompletionByCourse = response.data.userList.course_completion_by_course;
                dataObject.additionalData['coursesOverview'] = response.additionalData;

                // Check if the onSuccess function is defined and invoke it
                if ( isDefined( onSuccess ) ){
                    onSuccess();
                } 
            });

        }


    },


    setCourseList: function (courseList) {
        this.courseList = courseList;
    },

    getCourseList: function () {
        return this.courseList;
    },

    addToCourseList: function (courseID, name, data, type) {

        switch (type) {
            case 'string':
                data = String(data);
                break;
            default:
                data = Number(data);
                break;
        }


        if (this.courseList[courseID]) {
            this.courseList[courseID][name] = data;
        }

    },

    setUserList: function (userList) {

        //var allUserIds = [];
        var userListObject = [];
        jQuery.each(userList, function (index, value) {
            if (typeof userList[index].ID !== 'undefined') {
                userListObject[index] = value
            }
        });

        this.userList = userListObject;
    },

    getUserList: function () {
        return this.userList;
    },

    setGroupsList: function (groupsList) {

        this.groupsList = groupsList;
        var vThis = this;

        jQuery.each(groupsList, function (postId, data) {
            if (typeof vThis.courseList[postId] === 'object') {
                jQuery.each(data, function (index, groupID) {

                    if (typeof groupsList[groupID] === 'undefined') {
                        return true;
                    }

                    var userIDsAccess = groupsList[groupID].groups_user;

                    if (typeof vThis.courseList[postId] === 'undefined') {
                        return true;
                    }

                    if (vThis.courseList[postId].course_price_type !== 'open') {
                        vThis.courseList[postId].course_user_access_list = [ ... new Set( vThis.courseList[postId].course_user_access_list.concat( userIDsAccess ) )];
                    }

                });
            }
        });


    },

    getGroupsList: function () {
        return this.groupsList;
    },

    addToUserList: function (userID, name, data, type, courseID) {

        switch (type) {
            case 'string':
                data = String(data);
                break;
            case 'number':
                data = Number(data);
                break;
        }

        if (this.userList[userID]) {
            if (typeof courseID === 'undefined') {
                this.userList[userID][name] = data;
            } else {
                if (typeof this.userList[userID][courseID] === 'undefined') {
                    this.userList[userID][courseID] = {};
                }
                this.userList[userID][courseID][name] = data;
            }
        }


    },

    setLearnDashLabels: function (learnDashLabels) {
        this.learnDashLabels = learnDashLabels;
    },

    getLearnDashLabels: function (label) {
        return this.learnDashLabels(label);
    },

    setLinks: function (links) {
        this.links = links;
    },

    getLinks: function (links) {
        return this.learnDashLabels(links);
    },

    setLocalizedStrings: function (localizedStrings) {
        this.localizedStrings = localizedStrings;
    },

    addToDataTables: function (tableName, tableData) {

        if ( isDefined( wp.hooks ) ){
            tableData = wp.hooks.applyFilters( 'tc_addToDataTables_data', tableData, tableName );
        }

        this.dataTables[tableName] = tableData;
    },

    getDataTable: function (tableName) {
        return this.dataTables[tableName];
    },

    tableEmptyCellPlaceholder: function () {
        return '';
    },

    addToTinCanStatements: function (userID, courseStatements) {

        if (typeof this.userList[userID] !== 'undefined') {
            this.userList[userID].tinCanStatements = courseStatements;
        }

    },

    singleUserCoursePercentComplete: function (userID, courseID) {

        var percentComplete = this.tableEmptyCellPlaceholder();

        return percentComplete;
    },

    singleUserCourseStatus: function (userID, courseID) {

        var percentComplete = this.singleUserCoursePercentComplete(userID, courseID);

        percentComplete = parseInt(percentComplete);

        var status = ls('Not Started');

        if (percentComplete < 100 && percentComplete != 0) {
            status = ls('In Progress');
        }

        if (percentComplete == 100) {
            status = ls('Completed');
        }

        return status;

    },

    singleUserCourseCompletionTime: function (userID, courseID) {

        var timeSpent = this.tableEmptyCellPlaceholder();

        return timeSpent;
    },

    singleUserCourseTimeSpent: function (userID, courseID) {

        var timeSpent = this.tableEmptyCellPlaceholder();

        return timeSpent;
    },

    singleUserCourseCompletionDate: function (userID, courseID) {

        var completionDate = this.tableEmptyCellPlaceholder();

        return completionDate;
    },

    singleUserCourseAvgQuizScore: function (userID, courseID) {


        var avgQuizScore = this.tableEmptyCellPlaceholder();

        if (typeof this.courseList[courseID].associatedPosts === 'undefined') {
            return avgQuizScore;
        }

        var courseAssociatedPosts = this.courseList[courseID].associatedPosts;

        var cumulativePercentage = 0;

        var amountQuizzes = 0;

        // Loop through all modules associated with current course
        jQuery.each(courseAssociatedPosts, function (index, courseAssociatedPost) {

            // Make sure the user has data for the current module before access module data
            if (typeof dataObject.userList.userOverview[userID]['quizzes'] !== 'undefined' &&
                typeof dataObject.userList.userOverview[userID]['quizzes'][0][courseAssociatedPost] !== 'undefined'
            ) {

                // Make sure the current module is a quiz
                if (typeof dataObject.userList.userOverview[userID]['quizzes'][0][courseAssociatedPost].type !== 'undefined' &&
                    dataObject.userList.userOverview[userID]['quizzes'][0][courseAssociatedPost].type === 'quiz'
                ) {

                    // Make sure there is quiz data
                    if (1 <= dataObject.userList.userOverview[userID]['quizzes'][0][courseAssociatedPost].attempts) {

                        // Choose the last(most recent quiz attempt); the attempts are indexed and there is one type object
                        var lastAttempt = dataObject.userList.userOverview[userID]['quizzes'][0][courseAssociatedPost].attempts - 1;

                        cumulativePercentage += parseInt(dataObject.userList.userOverview[userID]['quizzes'][0][courseAssociatedPost][lastAttempt].percentage);
                        amountQuizzes++;

                    }
                }
            }
        });

        if (0 !== amountQuizzes || 0 !== cumulativePercentage) {
            avgQuizScore = Math.ceil(cumulativePercentage / amountQuizzes) + '%';
        }

        return avgQuizScore;
    },

    formatSecondsToDate: function( seconds ){
        // Check if it's a number
        if ( isNaN( seconds ) ){
            return '';
        }

        // Get parts
        let timeParts = {
            hours:   Math.floor( seconds / 3600 ),
            minutes: Math.floor( ( seconds - ( timeParts.hours * 3600 ) ) / 60 ),
            seconds: Math.floor( seconds - ( timeParts.hours * 3600 ) - ( timeParts.minutes * 60 ) )
        }

        // Prepend zero before if the number if less than 10
        timeParts.hours   = timeParts.hours < 10 ? `0${ timeParts.hours }` : timeParts.hours;
        timeParts.minutes = timeParts.minutes < 10 ? `0${ timeParts.minutes }` : timeParts.minutes;
        timeParts.seconds = timeParts.seconds < 10 ? `0${ timeParts.seconds }` : timeParts.seconds;

        // Return time
        return `${ timeParts.hours }:${ timeParts.minutes }:${ timeParts.seconds }`;
    },
};