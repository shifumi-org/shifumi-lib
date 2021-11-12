var reportingTabs = {

	currentTabID: false,

	viewingCurrentUserID: false,

	viewingCurrentCourseID: false,

	firstLoadIsComplete: false,

	elTabNav: jQuery('.uo-admin-reporting-tabs .tclr-admin-nav-items a'),

	elTabGroup: jQuery('.uo-admin-reporting-tabgroup > div'),

	triggerTabGroup: function (element) {
		jQuery('a[href="' + element + '"]').trigger("click");
	},

	navigateTabs: function( event ){
		// Prevent default
		event.preventDefault();

		// Object reference
		let _reportingTabs = this;

		// Get the element of the current tab
		let $currentTab     = jQuery( event.currentTarget );

		// Get ID of the tab
		let target          = $currentTab.data( 'tab_id' );

		// Get all the other tabs, except the selected one
		let $unselectedTabs = jQuery( '.uo-admin-reporting-tabs a' ).filter(( index, element ) => {
			// Get jQuery DOM element
			let $element    = jQuery( element );

			// If it's not the selected one, then return true
			return $element.data( 'tab_id' ) != target;
		});

		// Set parameters to the URL when changing the tab
		// First, create an instance of URLSearchParams
		var searchParams = new URLSearchParams( window.location.search );
		// Set the tab parameter
		searchParams.set( 'tab', target.replace( '#', '' ) );
		// Get the new URL with the new value of the tab parameter
		var newRelativePathQuery = window.location.pathname + '?' + searchParams.toString();
		// Push the URL to the history. We use this approach to avoid doing a reload
		history.pushState( null, '', newRelativePathQuery );

		// Update the hidden field that contains the ID of the current tab
		jQuery( '#reporting-group-selector-tab' )
			.val( target );

		// Remove "active" class to all the tabs, but add it again to the new tab
		$unselectedTabs.removeClass( 'nav-tab-active' );
		$currentTab.addClass( 'nav-tab-active' );

		// Get the containers with the content of each tab
		let $contentContainers = jQuery( '.uo-admin-reporting-tab-single' );
		// Get the container of the current tab
		let $currentTabContent = jQuery( `.uo-admin-reporting-tab-single#${ target.replace( '#', '' ) }` );

		// Hide the content of all the other tabs
		$contentContainers.hide();
		// and show the content's container of the selected one
		$currentTabContent.show();

		// Show group selector, and hide it again if the target is Tin Can
		let $groupSelector = jQuery( '#reporting-group-selector-container' );
		// Check if the group selector exists
		if ( $groupSelector.length == 1 ){
			// Show it
			$groupSelector.show();
			// Check if the target is the tin-can tab
			if ( target == '#tin-can' || target == '#xapi-tincan' ){
				// and hide the selector
				$groupSelector.hide();
			}

			// Update the tab parameter in the group selector URL
			// Get group selector field
			let $groupSelectorTabField = jQuery( '#reporting-group-selector-tab' );
			// Set new target
			$groupSelectorTabField.val( target.replace( '#', '' ) );
		}

		if ( ( '#tin-can' !== target || '#xapi-tincan' !== target ) && false === dataObject.dataObjectPopulated ){
			reportingQueryString.isTinCanOnly = false;
			dataObject.dataObjectPopulated = true;

			dataObject.getData( target, () => {
				// Update property to know that the data is loaded
				_reportingTabs.firstLoadIsComplete = true;

				// After the AJAX request, get the tab again, just in case the user changed it
				// before the AJAX request finished
				reportingTabs.getTabParameterAndSelectTab();
			});
		}
		else {
			switch ( target ){
				case '#courseReportTab':
					settingsPageModule.removeSwitchEvents();
					// set up data with data

					if ( _reportingTabs.firstLoadIsComplete ){
						reportingTables.createTable( 'coursesOverviewTable', '#coursesOverviewTable', 0 );
						chartVars.setCourseOverviewGraphTable( dataObject.dataTables.coursesOverviewTable, 1 );
					}

					break;

				case '#userReportTab':
					settingsPageModule.removeSwitchEvents();

					if ( _reportingTabs.firstLoadIsComplete ){
						reportingTables.createTable( 'usersOverviewTable', '#usersOverviewTable', 0 );
					}

					break;

				case '#tin-can':
					settingsPageModule.removeSwitchEvents();

					if ( _reportingTabs.firstLoadIsComplete ){
						reportingTables.createTable( 'tin-can', '.uo-admin-reporting-tab-single#tin-can table', 0 );
					}

					break;

				case '#xapi-tincan':

					if ( _reportingTabs.firstLoadIsComplete ){
						reportingTables.createTable( 'xapi-tincan', '.uo-admin-reporting-tab-single#xapi-tincan table', 0 );
					}

					break;

				case '#settings':
					settingsPageModule.addSwitchEvents();
					break;

				default:
					settingsPageModule.removeSwitchEvents();
			}
		}
	},

	getTabParameterAndSelectTab: function(){
		// Get active tab on page load
		let activeTab = new URLSearchParams( window.location.search );
		activeTab     = activeTab.get( 'tab' );
		activeTab     = isDefined( activeTab ) ? activeTab : 'courseReportTab';

		// Then trigger the tab change
		this.triggerTabGroup( `#${ activeTab }` );
	},

	addTabEvents: function () {
		this.elTabNav.on('click', ( event ) => {
			this.navigateTabs( event );
		});
		this.getTabParameterAndSelectTab();
	},

	removeTabEvents: function () {
		this.elTabNav.off('click', this.navigateTabs);
	},

	addTableEvents: function () {
		var vThis = this;
		var courseNavigateLink = jQuery('#course-navigate-link');

		jQuery('.uo-admin-reporting').on( 'click', '.reporting-table-see-details', function (e) {

			jQuery('#userSingleCourseLessonsContainer').hide();
			jQuery('#userSingleCourseTopicsContainer').hide();
			jQuery('#userSingleCourseQuizzesContainer').hide();
			jQuery('#userSingleCourseAssignmentsContainer').hide();
			jQuery('#userSingleCourseTinCanContainer').hide();

			let $row = jQuery(this).closest( 'tr' );
			var tableElementID = $row.closest('table').attr('id');

			if ( $row.hasClass('selected')) {
				$row.removeClass('selected');
			}
			else {
				jQuery('tr.selected').removeClass('selected');
				$row.addClass('selected');
			}

			// The first row of the bale is hidden from the user facing chart, it lists the ids for either course or
			// user depending of the chart type

			// check if the row is a child row on the main row that contains the date
			// - a child row is create when data needs to be collapsed
			var rowData;

			if (jQuery( $row[0] ).hasClass( 'child' )) {
				var $parent_row = jQuery( $row[0] ).prev();
				rowData = reportingTables.tableObjects[tableElementID].row( $parent_row[0] ).data();
			} else {
				rowData = reportingTables.tableObjects[tableElementID].row( $row[0] ).data();
			}

			var ID = Number( rowData.ID );

			if ('coursesOverviewTable' === tableElementID) {

				vThis.viewingCurrentCourseID = ID;

				// Hide Courses Overview and Show Course Single data
				jQuery('#coursesOverviewContainer').slideUp(0, function(){

					jQuery('#courseSingleTitle').html( `<span>${ dataObject.courseList[ID].post_title }</span>` );

					/********** NEW LINE ***/
					jQuery( '#reporting-course-navigation li' ).removeClass( 'reporting-breadcrumbs-item--previous reporting-breadcrumbs-item--visible reporting-breadcrumbs-item--current' );

					// set back navigation
					// courseNavigateLink.css('display', 'block');
					courseNavigateLink.find( 'span' ).addClass( 'reporting-breadcrumbs-item__link' );

					jQuery( '#course-navigate-link' ).addClass( 'reporting-breadcrumbs-item--previous reporting-breadcrumbs-item--visible' );
					jQuery( '#courseSingleTitle' ).addClass( 'reporting-breadcrumbs-item--current' );

					jQuery('#courseSingleContainer').slideDown(0);
				});

				// create tables
				reportingTables.createTable('courseSingleOverviewSummaryTable', '#courseSingleOverviewSummaryTable', ID);
				reportingTables.createTable('courseSingleTable', '#courseSingleTable', ID);

				// Destroy graphs before creating new ones
				let courseSingleOverviewPieChart = vThis.getAmchartInstanceWithElementId( 'courseSingleOverviewPieChart' );
				if ( isDefined( courseSingleOverviewPieChart ) ){
					courseSingleOverviewPieChart.clear();
				}

				let courseSingleActivitiesGraph = vThis.getAmchartInstanceWithElementId( 'courseSingleActivitiesGraph' );
				if ( isDefined( courseSingleActivitiesGraph ) ){
					courseSingleActivitiesGraph.clear();
				}

				AmCharts.makeChart("courseSingleOverviewPieChart", chartVars.courseSingleOverviewPieChartData( ID ) );
				AmCharts.makeChart("courseSingleActivitiesGraph", chartVars.courseSingleCompletionChartData( ID ) );
			}

			if ('courseSingleTable' === tableElementID) {

				if(reportingApiSetup.editUsers === "1"){
					document.getElementById( 'singleUserProfileDisplayName' ).innerHTML = `<a href="${ sprintf( '%s?user_id=%s', dataObject.links.profile, ID ) }">${ vThis.getUserLinkName(ID) }</a>`;
				}
				else{
					document.getElementById( 'singleUserProfileDisplayName' ).innerHTML = vThis.getUserLinkName(ID);
				}

				vThis.viewingCurrentUserID = ID;

				let courseId = vThis.viewingCurrentCourseID;

				document.getElementById('singleUserProfileID').innerHTML = sprintf( reportingApiSetup.localizedStrings.overviewUserCardId, ID );
				document.getElementById('singleUserProfileEmail').innerHTML = dataObject.userList.userOverview[ID].user_email;

				// Remove current user avatar
				jQuery( '#singleUserProfileAvatar' ).html( '' );

				// Get and set new user avatar
				vThis.getAndSetUserAvatar( ID );

				jQuery( '#reporting-user-navigation li' ).removeClass( 'reporting-breadcrumbs-item--previous reporting-breadcrumbs-item--visible reporting-breadcrumbs-item--current' );

				jQuery('#user-navigate-link')
					.data( 'target-last', 'usersOverviewTable' )
					.html( `<span class="reporting-breadcrumbs-item__link">${ reportingApiSetup.localizedStrings.overviewGoToCourseUserReport }</span>` )

				jQuery( '#userCourseSingleTitle' ).html( `<span>${ dataObject.courseList[courseId].post_title }</span>` );

				jQuery( '#userCourseDisplayName' ).addClass( 'reporting-breadcrumbs-item--previous reporting-breadcrumbs-item--visible' ).html( `<span class="reporting-breadcrumbs-item__link">${ dataObject.userList.userOverview[ID].display_name }</span>` )
				jQuery( '#userCourseDisplayName' ).on( 'click.getmissingdata', () => {
					if(typeof dataObject.userList.userOverview[ID].tinCanStatements === 'undefined'){
						var testTinCan = uoReportingAPI.reportingApiCall('tincan_data', ID);
						testTinCan.done(function (response) {
							dataObject.addToTinCanStatements(response.user_ID, response.tinCanStatements);
						});

					}

					vThis.viewingCurrentUserID = ID;
					// Populate Simple Profile
					if(reportingApiSetup.editUsers === "1"){
						document.getElementById('singleUserProfileDisplayName').innerHTML = `<a href="${ sprintf( '%s?user_id=%s', dataObject.links.profile, ID ) }">${ vThis.getUserLinkName(ID) }</a>`;
					}
					else{
						document.getElementById('singleUserProfileDisplayName').innerHTML = vThis.getUserLinkName(ID)
					}

					document.getElementById('singleUserProfileID').innerHTML = sprintf( reportingApiSetup.localizedStrings.overviewUserCardId, ID );
					document.getElementById('singleUserProfileEmail').innerHTML = dataObject.userList.userOverview[ID].user_email;

					// Remove current user avatar
					jQuery( '#singleUserProfileAvatar' ).html( '' );

					// Get and set new user avatar
					vThis.getAndSetUserAvatar( ID );

					reportingTables.createTable('userSingleOverviewTable', '#userSingleOverviewTable', ID);
					reportingTables.createTable('userSingleCoursesOverviewTable', '#userSingleCoursesOverviewTable', ID);

					jQuery('#usersOverviewContainer').slideUp(0, function(){
						jQuery( '#reporting-user-navigation li' ).removeClass( 'reporting-breadcrumbs-item--previous reporting-breadcrumbs-item--visible reporting-breadcrumbs-item--current' );

						// Navigation
						jQuery('#user-navigate-link')
							.data('target-last','usersOverviewTable')
							.addClass( 'reporting-breadcrumbs-item--previous reporting-breadcrumbs-item--visible' )
							.html( `<span class="reporting-breadcrumbs-item__link">${ reportingApiSetup.localizedStrings.overviewGoToCourseUserReport }</span>` );

						jQuery( '#userCourseDisplayName' ).addClass( 'reporting-breadcrumbs-item--current' ).html( `<span>${ dataObject.userList.userOverview[ID].display_name }</span>` );

						jQuery('#singleUserProfileContainer').slideDown(0);
						jQuery('#userSingleOverviewContainer').slideDown(0, function(){
							jQuery('#userSingleCoursesOverviewContainer').slideDown(0);
						});
					});

					jQuery( '#userCourseDisplayName' ).off( 'click.getmissingdata' );
				})

				jQuery( '#userCourseSingleTitle' ).addClass( 'reporting-breadcrumbs-item--current' );

				jQuery('#singleUserProfileContainer').slideDown(0);
				jQuery('#userSingleOverviewContainer').slideDown(0, function(){
					jQuery('#userSingleCoursesOverviewContainer').slideDown(0);
				});

				jQuery('#courseSingleContainer').slideUp(0, function(){
					jQuery('#usersOverviewContainer').hide();
					reportingTabs.triggerTabGroup('#userReportTab');
					jQuery('#courseSingleContainer').show();
				});

				reportingTables.createTable('userSingleCourseProgressSummaryTable', '#userSingleCourseProgressSummaryTable', courseId);
				reportingTables.createTable('userSingleCourseLessonsTable', '#userSingleCourseLessonsTable', courseId);
				reportingTables.createTable('userSingleCourseTopicsTable', '#userSingleCourseTopicsTable', courseId);
				reportingTables.createTable('userSingleCourseQuizzesTable', '#userSingleCourseQuizzesTable', courseId);
				reportingTables.createTable('userSingleCourseAssignmentsTable', '#userSingleCourseAssignmentsTable', courseId);
				reportingTables.createTable('userSingleCourseTinCanTable', '#userSingleCourseTinCanTable', courseId);

				jQuery('#userSingleOverviewContainer').slideUp(0);
				jQuery('#userSingleCoursesOverviewContainer').slideUp(0, function(){

					jQuery('#userSingleCourseProgressSummaryContainer').slideDown(0, function(){
						jQuery('#userSingleCourseProgressMenu li').removeClass( 'reporting-single-course-progress-tabs__item--selected' );
						jQuery('#menuLessons').addClass( 'reporting-single-course-progress-tabs__item--selected' );
						jQuery('#userSingleCourseProgressMenuContainer').slideDown(0);
						jQuery('#userSingleCourseLessonsContainer').slideDown(0);
					});
				});

			}

			if ('usersOverviewTable' === tableElementID) {

				if(typeof dataObject.userList.userOverview[ID].tinCanStatements === 'undefined'){
					var testTinCan = uoReportingAPI.reportingApiCall('tincan_data', ID);
					testTinCan.done(function (response) {
						dataObject.addToTinCanStatements(response.user_ID, response.tinCanStatements);
					});

				}

				vThis.viewingCurrentUserID = ID;
				// Populate Simple Profile
				if(reportingApiSetup.editUsers === "1"){
					document.getElementById('singleUserProfileDisplayName').innerHTML = `<a href="${ sprintf( '%s?user_id=%s', dataObject.links.profile, ID ) }">${ vThis.getUserLinkName(ID) }</a>`;
				}else{
					document.getElementById('singleUserProfileDisplayName').innerHTML = vThis.getUserLinkName(ID);
				}

				document.getElementById('singleUserProfileID').innerHTML = sprintf( reportingApiSetup.localizedStrings.overviewUserCardId, ID );
				document.getElementById('singleUserProfileEmail').innerHTML = dataObject.userList.userOverview[ID].user_email;

				// Remove current user avatar
				jQuery( '#singleUserProfileAvatar' ).html( '' );

				// Get and set new user avatar
				vThis.getAndSetUserAvatar( ID );

				reportingTables.createTable('userSingleOverviewTable', '#userSingleOverviewTable', ID);
				reportingTables.createTable('userSingleCoursesOverviewTable', '#userSingleCoursesOverviewTable', ID);

				jQuery('#usersOverviewContainer').slideUp(0, function(){
					jQuery( '#reporting-user-navigation li' ).removeClass( 'reporting-breadcrumbs-item--previous reporting-breadcrumbs-item--visible reporting-breadcrumbs-item--current' );

					// Navigation
					jQuery('#user-navigate-link')
						.data('target-last','usersOverviewTable')
						.addClass( 'reporting-breadcrumbs-item--previous reporting-breadcrumbs-item--visible' )
						.html( `<span class="reporting-breadcrumbs-item__link">${ reportingApiSetup.localizedStrings.overviewGoToCourseUserReport }</span>` );

					jQuery( '#userCourseDisplayName' ).addClass( 'reporting-breadcrumbs-item--current' ).html( `<span>${ dataObject.userList.userOverview[ID].display_name }</span>` );

					jQuery('#singleUserProfileContainer').slideDown(0);
					jQuery('#userSingleOverviewContainer').slideDown(0, function(){
						jQuery('#userSingleCoursesOverviewContainer').slideDown(0);
					});
				});

			}

			if ('userSingleCoursesOverviewTable' === tableElementID) {

				jQuery('#userCourseSingleTitle').html( `<span>${ dataObject.courseList[ID].post_title }</span>` );

				reportingTables.createTable('userSingleCourseProgressSummaryTable', '#userSingleCourseProgressSummaryTable', ID);
				reportingTables.createTable('userSingleCourseLessonsTable', '#userSingleCourseLessonsTable', ID);
				reportingTables.createTable('userSingleCourseTopicsTable', '#userSingleCourseTopicsTable', ID);
				reportingTables.createTable('userSingleCourseQuizzesTable', '#userSingleCourseQuizzesTable', ID);
				reportingTables.createTable('userSingleCourseAssignmentsTable', '#userSingleCourseAssignmentsTable', ID);
				reportingTables.createTable('userSingleCourseTinCanTable', '#userSingleCourseTinCanTable', ID);

				jQuery('#userSingleOverviewContainer').slideUp(0);
				jQuery('#userSingleCoursesOverviewContainer').slideUp(0, function(){
					jQuery( '#reporting-user-navigation li' ).removeClass( 'reporting-breadcrumbs-item--previous reporting-breadcrumbs-item--visible reporting-breadcrumbs-item--current' );

					jQuery('#user-navigate-link')
						.data('target-last','usersOverviewTable')
						.html( `<span class="reporting-breadcrumbs-item__link">${ reportingApiSetup.localizedStrings.overviewGoToCourseUserReport }</span>` );

					jQuery( '#userCourseDisplayName' )
						.data( 'target-last', 'userSingleCoursesOverviewTable' )
						.addClass( 'reporting-breadcrumbs-item--previous reporting-breadcrumbs-item--visible' )
						.html( `<span class="reporting-breadcrumbs-item__link">${ jQuery( '#userCourseDisplayName' ).text() }</span>` )

					jQuery( '#userCourseSingleTitle' ).addClass( 'reporting-breadcrumbs-item--current' )

					jQuery('#userSingleCourseProgressSummaryContainer').slideDown(0, function(){
						jQuery('#userSingleCourseProgressMenu li').removeClass( 'reporting-single-course-progress-tabs__item--selected' );
						jQuery('#menuLessons').addClass( 'reporting-single-course-progress-tabs__item--selected' );
						jQuery('#userSingleCourseProgressMenuContainer').slideDown(0);
						jQuery('#userSingleCourseLessonsContainer').slideDown(0);
					});
				});

			}


		});
	},

	getAmchartInstanceWithElementId: function( elementId ){
		// Define default value
		let amChartInstance = null;

		// Check if AmCharts.charts is defined
		if ( isDefined( AmCharts.charts ) ){
			// Search instance
			AmCharts.charts.forEach(( chart, index ) => {
				if ( elementId == chart.div.id ){
					amChartInstance = chart;
				}
			});
		}

		// Return instance
		return amChartInstance;
	},

	getAndSetUserAvatar: function( userId ){
		// Create request
		let request = uoReportingAPI.reportingApiCallDataPost( 'user_avatar', {
			user_id: userId
		});

		// Add done's callback
		request.done(( response ) => {

			// Check the request was success
			if ( response.success ){
				// Get avatar container
				let $avatarContainer = jQuery( '#singleUserProfileAvatar' );

				// Set the avatar image
				$avatarContainer.html( `<img src="${ response.data.avatar }">` );
			}
		});
	},

	getUserLinkName: function( userId){

		if (typeof reportingApiSetup.userIdentifierDisplayName !== 'undefined' && '1' === reportingApiSetup.userIdentifierDisplayName) {
			return dataObject.userList.userOverview[ userId ].display_name;
		}

		let firstLast = [];
		if (typeof reportingApiSetup.userIdentifierFirstName !== 'undefined' && '1' === reportingApiSetup.userIdentifierFirstName) {
			firstLast.push(dataObject.userList.userOverview[ userId ].first_name);
		}

		if (typeof reportingApiSetup.userIdentifierLastName !== 'undefined' && '1' === reportingApiSetup.userIdentifierLastName) {
			firstLast.push(dataObject.userList.userOverview[ userId ].last_name);
		}

		firstLast = firstLast.join(' ');
		if( '' !== firstLast ){
			return firstLast;
		}

		if (typeof reportingApiSetup.userIdentifierUsername !== 'undefined' && '1' === reportingApiSetup.userIdentifierUsername) {
			return dataObject.userList.userOverview[ userId ].user_login;
		}

		if (typeof reportingApiSetup.userIdentifierEmail !== 'undefined' && '1' === reportingApiSetup.userIdentifierEmail) {
			return dataObject.userList.userOverview[ userId ].user_email;
		}


		return dataObject.userList.userOverview[ userId ].display_name;
	},

	addNavigationEvents : function(){

		// Course Report Back Button
		jQuery('#course-navigate-link').on('click', function(event){

			jQuery('#courseSingleContainer').slideUp(0, function(){

				jQuery( '#reporting-course-navigation li' ).removeClass( 'reporting-breadcrumbs-item--previous reporting-breadcrumbs-item--visible reporting-breadcrumbs-item--current' );

				jQuery( '#course-navigate-link' ).addClass( 'reporting-breadcrumbs-item--current reporting-breadcrumbs-item--visible' );

				jQuery(event.currentTarget).find( 'span' ).removeClass( 'reporting-breadcrumbs-item__link' );

				jQuery('#courseSingleTitle').html('');
				jQuery('#coursesOverviewContainer').delay(0).slideDown(0);
			});

		});

		// User Report Back button
		jQuery( '#user-navigate-link' ).on('click', function(event){
			var target = jQuery(event.currentTarget).data('target-last');

			if(target == 'usersOverviewTable'){

				jQuery('#userSingleCourseProgressSummaryContainer').slideUp(0);
				jQuery('#userSingleCourseProgressMenuContainer').slideUp(0);
				jQuery('#userSingleCourseLessonsContainer').slideUp(0);
				jQuery('#userSingleCourseTopicsContainer').slideUp(0);
				jQuery('#userSingleCourseQuizzesContainer').slideUp(0);
				jQuery('#userSingleCourseAssignmentsContainer').slideUp(0);
				jQuery('#userSingleCourseTinCanContainer').slideUp(0);

				jQuery('#userSingleCoursesOverviewContainer').slideUp(0, function(){
					jQuery('#userSingleOverviewContainer, #singleUserProfileContainer ').slideUp(0, function(){
						jQuery('#usersOverviewContainer').slideDown(0);

						jQuery( '#reporting-user-navigation li' ).removeClass( 'reporting-breadcrumbs-item--previous reporting-breadcrumbs-item--visible reporting-breadcrumbs-item--current' );

						jQuery(event.currentTarget).addClass( 'reporting-breadcrumbs-item--visible' ).find( 'span' ).removeClass( 'reporting-breadcrumbs-item__link' );

						jQuery( '#userCourseDisplayName' ).html( '' );
						jQuery( '#userCourseSingleTitle' ).html( '' );
					});
				});
			}
		});

		jQuery( '#userCourseDisplayName' ).on( 'click', function( event ){
			jQuery('#userSingleCoursesOverviewContainer').slideUp(0, function(){
				var userSingleCoursesOverviewContainers = '#userSingleCourseProgressMenuContainer,#userSingleCourseLessonsContainer,#userSingleCourseTopicsContainer,#userSingleCourseQuizzesContainer,#userSingleCourseAssignmentsContainer,#userSingleCourseTinCanContainer';
				jQuery(userSingleCoursesOverviewContainers).slideUp(0, function(){
					jQuery('#userSingleCourseProgressSummaryContainer').slideUp(0, function(){

						jQuery( '#reporting-user-navigation li' ).removeClass( 'reporting-breadcrumbs-item--previous reporting-breadcrumbs-item--visible reporting-breadcrumbs-item--current' );

						jQuery('#userCourseSingleTitle').html('');

						jQuery( '#user-navigate-link' ).addClass( 'reporting-breadcrumbs-item--previous reporting-breadcrumbs-item--visible' );

						// Navigation
						jQuery('#userCourseDisplayName')
							.data( 'target-last', 'usersOverviewTable' )
							.addClass( 'reporting-breadcrumbs-item--current' )
							.html( `<span>${ jQuery('#userCourseDisplayName').text() }</span>` );

						jQuery( '#userSingleOverviewContainer ').slideDown(0, function() {
							jQuery('#userSingleCoursesOverviewContainer').slideDown(0);
						});
					});
				});
			});
		});

		// User's Single Course Module Toggle
		jQuery('#userSingleCourseProgressMenu li').on('click', function(event){
			var listItem = event.currentTarget;
			var toggleTarget  = listItem.id;

			jQuery('#userSingleCourseProgressMenu li').removeClass( 'reporting-single-course-progress-tabs__item--selected' );
			jQuery(listItem).addClass( 'reporting-single-course-progress-tabs__item--selected');

			var lessons = jQuery('#userSingleCourseLessonsContainer');
			var topics = jQuery('#userSingleCourseTopicsContainer');
			var quizzes = jQuery('#userSingleCourseQuizzesContainer');
			var assignments = jQuery('#userSingleCourseAssignmentsContainer');
			var tinCan = jQuery('#userSingleCourseTinCanContainer');

			lessons.hide();
			topics.hide();
			quizzes.hide();
			assignments.hide();
			tinCan.hide();

			switch (toggleTarget){
				case 'menuLessons': lessons.show(); break;
				case 'menuTopics': topics.show(); break;
				case 'menuQuizzes': quizzes.show(); break;
				case 'menuAssignments': assignments.show(); break;
				case 'menuTinCan': tinCan.show(); break;
			}



		});

	}

};