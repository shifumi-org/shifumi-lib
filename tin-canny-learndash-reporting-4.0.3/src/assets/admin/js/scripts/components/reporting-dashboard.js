var tinCannyDashboard = null;

jQuery(document).ready( function($){
    tinCannyDashboard = {
        init: function (){
            this.insertHTML();
            this.getData();
            this.groupSelector();
        },

        insertHTML: function(){
            // Get elements
            let $dashboard = $( this.html );

            // Get containerdataProvider: completionAndTinCanDates
            let $container;

            if ( $( '#dashboard-widgets-wrap' ).length > 0 ){
                $container = $( '#dashboard-widgets-wrap' ).prev();
            }
            else if ( $( '#uo-welcome-panel' ).length > 0 ){
                $container = $( '#uo-welcome-panel' ).parent();
            }
            else {
                $container = $( '#coursesOverviewGraphHeading' );
            }

            // Get mainContainer
            let $mainContainer = $( '.tclr.wrap' );

            // Get context
            // Values:
            // - dashboard:  WP Admin main page
            // - plugin:     The Tin Canny Dashboard page
            // - frontend:   Frontend
            let context = reportingApiSetup.page;
            context     = context == 'reporting' && $( 'body' ).hasClass( 'wp-admin' ) ? 'plugin' : context;
            context     = context == 'reporting' ? 'frontend' : context;

            // Filter HTML and add context class
            if ( context == 'plugin' ){
                $mainContainer.addClass( 'uo-reporting--plugin' );
                $dashboard.find( '.reporting-dashboard-col-3' ).remove();
            }

            if ( context == 'frontend' ){
                $mainContainer.addClass( 'uo-reporting--frontend' );
                // $dashboard.find( '.reporting-dashboard-col-1' ).remove();
                $dashboard.find( '.reporting-dashboard-col-3' ).remove();
            }

            if ( context == 'dashboard' ){
                $mainContainer.addClass( 'uo-reporting--dashboard' );

                // Add class to the dashboard container
                $dashboard.addClass( 'uo-reporting--dashboard' );
            }

            // Check if we have to remove the Tin Can report button
            if ( reportingApiSetup.showTinCanTab == '0' ){
                $dashboard.find( '.reporting-dashboard-quick-links__item[data-id="tin-can"]' ).remove();
            }

            // Save context
            this.context = context;

            // Insert HTML
            $dashboard.insertAfter( $container );
        },

        getData: function(){

            // Create variable to save AJAX
            let coursesOverviewApiCall;

            if ( typeof reportingApiSetup.isolated_group_id == 'string' && parseInt( reportingApiSetup.isolated_group_id ) ){
                if ( this.context == 'dashboard' ){
                   coursesOverviewApiCall = this.restCall( '/?group_id=' + reportingApiSetup.isolated_group_id );
                }
            }
            else {
               if ( this.context == 'dashboard' ){
                    coursesOverviewApiCall = this.restCall();
                }
            }

            if ( this.context == 'dashboard' ){
                // On success
                coursesOverviewApiCall.done(( response ) => {
                    this.dataObject = response;

                    this.renderData();

                    // Trigger tab selection to render the tables
                    // Get current tab
                    let $tabs = $( '.uo-admin-reporting-tabs' );

                    // Check if the element exists
                    if ( $tabs.length > 0 ){
                        // Get id of current tab
                        let activeTab = $tabs.data( 'tab_on_load' );

                        // Trigger click on tab
                        $( `.uo-admin-reporting-tabs .nav-tab[data-tab_id="${ activeTab }"]` ).trigger( 'click' );
                    }
                });

                // On error
                coursesOverviewApiCall.fail( ( response ) => {
                    // Append error message
                    $( '#wpbody-content' ).append( $( '<div/>', {
                        id:   'api-error',
                        text: response.responseTex
                    }));
                });
            }
        },

        restCall: function( parameters = '' ){
            return $.ajax({
                url: `${ reportingApiSetup.root }dashboard_data${ parameters }`,
                beforeSend: ( xhr ) => {
                    xhr.setRequestHeader( 'X-WP-Nonce', reportingApiSetup.nonce );
                },
            });
        },

        renderData: function(){
            // Create metaboxes
            this.metaboxReports();
            this.metaboxRecentActivity();
            this.metaboxMostAndLeastCompletedCourses();
        },

        metaboxReports: function(){
            // Bind links
            $( '.reporting-dashboard-quick-links__item' ).on( 'click', () => {
                window.location.href = `${ this.dataObject.report_link }${ $( event.currentTarget ).data( 'append' ) }`;
            });

            // Fill quick data numbers
            $( '.reporting-dashboard-quick-stats__item' ).each(( index, element ) => {
                // Get elements
                let $item   = $( element ),
                    $number = $item.find( '.reporting-dashboard-quick-stats__number' ),
                    // Get id
                    id      = $item.data( 'id' );

                // Get number
                // First, define default value
                let number = 0;
                // Get number depending of the id
                switch ( id ){
                    case 'users':
                        number = this.dataObject.total_users;
                        break;

                    case 'courses':
                        number = this.dataObject.total_courses;
                        break;
                }

                // Add number to the container
                $number.text( number );

                // Add ready class
                $item.addClass( 'reporting-dashboard-quick-stats__item--ready' );
            });
        },

        metaboxRecentActivity: function(){
            // Create pattern to find parts of a date ( YYYY-MM-DD )
            const datePattern = /^(\d{4})-(\d{2})-(\d{2})$/;

            // Add missing days between entries in the graph data.
            const addMissingDaysToGraphData = ( graphData ) => {
                // Get number of entries
                let numberOfEntries = graphData.length;

                // New dates
                let newGraphData = [];

                // Iterate each entry
                graphData.forEach( ( element, index ) => {
                    // Add this day to the graph data array
                    newGraphData.push( element );

                    // if it isn't the last one then search and add missing days
                    if ( index != numberOfEntries - 1 ){
                        // Get current and next elements
                        let currentEntry = element,
                            nextEntry    = graphData[ index + 1 ];

                        // Get date
                        let [, currentEntryYear, currentEntryMonth, currentEntryDay] = datePattern.exec( currentEntry.date );
                        //let currentEntryDate = new Date( `${currentEntryYear}-${currentEntryMonth}-${currentEntryDay}T00:00:00Z` );
                        let currentEntryDate = new Date("".concat(currentEntryYear, "-").concat(currentEntryMonth, "-").concat(currentEntryDay, "T02:00:00Z"));

                        // Get date of the next entry
                        let [, nextEntryYear, nextEntryMonth, nextEntryDay] = datePattern.exec( nextEntry.date );
                        //let nextEntryDate = new Date( `${nextEntryYear}-${nextEntryMonth}-${nextEntryDay}T00:00:00Z` );
                        let nextEntryDate = new Date("".concat(nextEntryYear, "-").concat(nextEntryMonth, "-").concat(nextEntryDay, "T02:00:00Z"));

                        // Days difference between those two
                        let differenceInDays = Math.ceil( ( nextEntryDate - currentEntryDate ) / 1000 / 60 / 60 / 24 );

                        // Clone currentEntryDate
                        let newDate = new Date( +currentEntryDate );

                        // Add missing days (don't count the last one)
                        for ( let i = 1; i <= differenceInDays - 1; i++ ){
                            // Get new date
                            newDate.setDate( newDate.getDate() + 1 );

                            let newDateParts = {
                                year:  newDate.getUTCFullYear(),
                                month: newDate.getUTCMonth() + 1,
                                day:   newDate.getUTCDate()
                            }

                            // Add zeros
                            newDateParts.month = newDateParts.month < 10 ? `0${ newDateParts.month }` : newDateParts.month;
                            newDateParts.day   = newDateParts.day < 10 ? `0${ newDateParts.day }` : newDateParts.day;

                            // Add date
                            newGraphData.push({
                                date: `${ newDateParts.year }-${ newDateParts.month }-${ newDateParts.day }`
                            });
                        }
                    }
                });

                // Return data
                return newGraphData;
            }

            // Get data
            let completionAndTinCanDates = this.dataObject.courses_tincan_completed;
            
            // Merge data if they have the same day
            // So, for example, if we have an array with two elements: { date: '2019-01-01', completions: 1 }
            // and { data: '2019-01-01', tinCan: 5 } we will merge them to create a new array
            // with only one element: { data: '2019-01-01', completions: 1, tinCan: 5 }
            let dataGroupedByDate = [],
                dateAndIndexPair  = {};

            completionAndTinCanDates.forEach(( element, index ) => {
                // Check if we already added that date
                if ( dateAndIndexPair[ element.date ] !== undefined ){
                    // Then merge this object with the stored one
                    dataGroupedByDate[ dateAndIndexPair[ element.date ] ] = Object.assign({}, dataGroupedByDate[ dateAndIndexPair[ element.date ] ], element );
                }
                else {
                    // Create element and store object, and
                    // add the pair index
                    dateAndIndexPair[ element.date ] = dataGroupedByDate.push( element ) - 1;
                }
            });

            completionAndTinCanDates = dataGroupedByDate;

            // Check if it has data
            let hasData = completionAndTinCanDates.length > 0;

            // If there is no data then add some
            // We only have to add two days separated by 7 days. The following
            // function will add all the missing dates (the ones between those two)
            if ( ! hasData ){
                // Get dates
                let dateNow       = new Date(),
                    date10DaysAgo = new Date();

                date10DaysAgo.setDate( date10DaysAgo.getDate() - 7 );

                // Get parts of both dates
                let dateParts = {
                    now: {
                        year:  dateNow.getUTCFullYear(),
                        month: dateNow.getUTCMonth() + 1,
                        day:   dateNow.getUTCDate()
                    },
                    daysAgo: {
                        year:  date10DaysAgo.getUTCFullYear(),
                        month: date10DaysAgo.getUTCMonth() + 1,
                        day:   date10DaysAgo.getUTCDate()
                    }
                };

                // Add zeros
                dateParts.now.month     = dateParts.now.month < 10 ? `0${ dateParts.now.month }` : dateParts.now.month;
                dateParts.now.day       = dateParts.now.day < 10 ? `0${dateParts.now.day }` :dateParts.now.day;
                dateParts.daysAgo.month = dateParts.daysAgo.month < 10 ? `0${ dateParts.daysAgo.month }` : dateParts.daysAgo.month;
                dateParts.daysAgo.day   = dateParts.daysAgo.day < 10 ? `0${ dateParts.daysAgo.day }` : dateParts.daysAgo.day;

                completionAndTinCanDates.push( ...[{
                    date: `${ dateParts.daysAgo.year }-${ dateParts.daysAgo.month }-${ dateParts.daysAgo.day }`
                },{
                    date: `${ dateParts.now.year }-${ dateParts.now.month }-${ dateParts.now.day }`
                }]);
            }
            else {
                // Check that we have information from the date of the last entry until today
                // So, if we the last completion or Tin Can statement was 10 days ago we have to add those 10 missing days
                // Actually, we have to add only the last one, the script has other function that will add the days in the middle
                // First, get the date
                let [, lastElementYear, lastElementMonth, lastElementDay] = datePattern.exec( completionAndTinCanDates[ completionAndTinCanDates.length - 1 ].date );
                let lastElementDate = new Date( `${ lastElementYear }-${ lastElementMonth }-${ lastElementDay }T02:00:00Z` );

                // Compare that day with today
                let todayDate = new Date();

                // Days difference between those two
                let differenceInDays = Math.ceil( ( todayDate - lastElementDate ) / 1000 / 60 / 60 / 24 ) - 1;

                // Add the days missing
                lastElementDate.setDate( lastElementDate.getDate() + differenceInDays );

                // If there is day difference then we have to add the date
                if ( differenceInDays > 0 ){
                    // Get date parts
                    let dateParts = {
                        year:  lastElementDate.getUTCFullYear(),
                        month: lastElementDate.getUTCMonth() + 1,
                        day:   lastElementDate.getUTCDate()
                    };

                    // Add zeros
                    dateParts.month = dateParts.month < 10 ? `0${ dateParts.month }` : dateParts.month;
                    dateParts.day   = dateParts.day < 10 ? `0${dateParts.day }` :dateParts.day;

                    // Add element
                    completionAndTinCanDates.push({
                        date: `${ dateParts.year }-${ dateParts.month }-${ dateParts.day }`
                    });
                }                    
            }

            // Add missing days
            completionAndTinCanDates = addMissingDaysToGraphData( completionAndTinCanDates );

            // If there isn't enough data to fill 7 days then add some
            // days to complete the week
            if ( completionAndTinCanDates.length < 7 ){
                // Calculate days to add
                let daysToAdd = 7 - completionAndTinCanDates.length;

                // Get date of the first element
                let [, firstElementYear, firstElementMonth, firstElementDay] = datePattern.exec( completionAndTinCanDates[0].date );
                let firstElementDate = new Date( `${firstElementYear}-${firstElementMonth}-${firstElementDay}T00:00:00Z` );

                // Get new date (so we have 7 days)
                firstElementDate.setDate( firstElementDate.getDate() - daysToAdd );

                // Get date parts
                let dateParts = {
                    year:  firstElementDate.getUTCFullYear(),
                    month: firstElementDate.getUTCMonth() + 1,
                    day:   firstElementDate.getUTCDate()
                };

                // Add zeros
                dateParts.month = dateParts.month < 10 ? `0${ dateParts.month }` : dateParts.month;
                dateParts.day   = dateParts.day < 10 ? `0${dateParts.day }` :dateParts.day;

                // Prepend new elements
                completionAndTinCanDates = [{
                    date: `${ dateParts.year }-${ dateParts.month }-${ dateParts.day }`
                }, ...completionAndTinCanDates ];

                // Add missing days
                completionAndTinCanDates = addMissingDaysToGraphData( completionAndTinCanDates );
            }

            // If it has data, then
            if ( hasData ){
                // Add missing properties
                completionAndTinCanDates = completionAndTinCanDates.map(( entry ) => {
                    // Create new object
                    let day = {
                        date:        entry.date,
                        completions: entry.completions === undefined ? 0 : entry.completions,
                    }

                    // Check if we have to show the Tin Can data,
                    // otherwise, don't add it
                    if ( TincannyUI.show.tinCanData ){
                        // Add tin can data
                        day.tinCan = entry.tinCan === undefined ? 0 : entry.tinCan;
                    }

                    // Return day data
                    return day;
                });
            }

            // Create chart data
            var chart = {
                type: 'serial',
                listeners: [{
                    event: 'init',
                    method: ( e ) => {
                        // Define the number of days to show
                        const numberOfDaysToShow = 30;

                        // Zoom automatically to the last X days
                        // First, check if there are more than X days of data
                        if ( completionAndTinCanDates.length > numberOfDaysToShow ){

							let dates = {end:   completionAndTinCanDates[ completionAndTinCanDates.length - 1 ].date};
                        	if( 0 >= completionAndTinCanDates.length - 1 - numberOfDaysToShow ){
								dates.start = completionAndTinCanDates[ 0 ].date;
							}else{
								dates.start = completionAndTinCanDates[ completionAndTinCanDates.length - 1 - numberOfDaysToShow ].date;
							}
                            // Those dates are strings, convert them to Date objects
                            let [, startYear, startMonth, startDay] = datePattern.exec( dates.start );
                            let startDate = new Date( `${startYear}-${startMonth}-${startDay}T00:00:00Z` );

                            let [, endYear, endMonth, endDay] = datePattern.exec( dates.end );
                            let endDate = new Date( `${endYear}-${endMonth}-${endDay}T00:00:00Z` );

                            // Set zoom
                            e.chart.zoomToDates( startDate, endDate );
                        }
                    }
                },{
                    event: 'drawn',
                    method: () => {
                        // If it doesn't have data then render a notice
                        if ( ! hasData ){
                            // Get container
                            let $chartContainer = $( '#reporting-recent-activities .amcharts-chart-div' );

                            // Create notice
                            let $notice = $( `
                                <div class="reporting-dashboard-status reporting-dashboard-status--warning">
                                    <div class="reporting-dashboard-status__icon"></div>
                                    <div class="reporting-dashboard-status__text">
                                        ${ reportingApiSetup.localizedStrings.graphNoActivity }
                                    </div>
                                </div>
                            ` );

                            // Append notice
                            $chartContainer.append( $notice );
                        }
                    },
                }],
                pathToImages: 'https://cdn.amcharts.com/lib/3/images/',
                categoryField: 'date',
                columnWidth: 0,
                dataDateFormat: 'YYYY-MM-DD',
                angle: 34,
                marginBottom: 14,
                marginLeft: 19,
                plotAreaBorderAlpha: 0.04,
                plotAreaFillAlphas: 0.03,
                colors: [
                    TincannyUI.colors.primary,
                    TincannyUI.colors.secondary,
                    '#84b761',
                    '#cc4748',
                    '#cd82ad',
                    '#2f4074',
                    '#448e4d',
                    '#b7b83f',
                    '#b9783f',
                    '#b93e3d',
                    '#913167'
                ],
                startDuration: 0,
                fontFamily: TincannyUI.mainFont,
                fontSize: 13,
                handDrawScatter: 1,
                theme: 'light',
                categoryAxis: {
                    autoRotateAngle: 0,
                    gridPosition: 'start',
                    parseDates: true,
                    offset: 9
                },
                chartCursor: {
                    enabled: true,
                    bulletSize: 6,
                    cursorColor: '#000000'
                },
                chartScrollbar: {
                    enabled: true,
                    autoGridCount: true,
                    backgroundAlpha: 0.46,
                    backgroundColor: '#F3F3F3',
                    color: '#666766',
                    dragIconHeight: 30,
                    dragIconWidth: 30,
                    // Check if we should show Tin Can data. If we should, then use
                    // the Tin Can graph to render the scroll graph, otherwise use
                    // the Completions graph
                    graph: TincannyUI.show.tinCanData ? 'tin-can' : 'completion',
                    graphFillAlpha: 0.15,
                    graphLineAlpha: 1,
                    graphType: 'smoothedLine',
                    gridAlpha: 0.02,
                    gridColor: 'F3F3F3',
                    offset: 20,
                    scrollbarHeight: 35,
                    scrollDuration: 7,
                    selectedBackgroundAlpha: 1,
                    selectedGraphFillAlpha: 0,
                    selectedGraphFillColor: 'A4A4A4',
                    selectedGraphLineAlpha: 1,
                    selectedGraphLineColor: '979797'
                },
                trendLines: [],
                graphs: [
                    {
                        balloonColor: TincannyUI.colors.primary,
                        balloonText: sprintf( reportingApiSetup.localizedStrings.graphTooltipCompletions, '[[value]]' ),
                        bulletAlpha: 0,
                        color: '',
                        columnWidth: 0,
                        cornerRadiusTop: 50,
                        fontSize: 12,
                        id: 'completion',
                        lineThickness: 2,
                        minDistance: 0,
                        negativeFillAlphas: 0,
                        title: sprintf( reportingApiSetup.localizedStrings.graphCourseCompletions, reportingApiSetup.learnDashLabels.course ),
                        type: 'smoothedLine',
                        valueAxis: 'completion',
                        valueField: 'completions',
                        yAxis: 'completion'
                    },
                    // Below we will check if we have to add or not the Tin Can graph
                ],
                guides: [],
                valueAxes: [
                    {
                        gridType: 'circles',
                        id: 'completion',
                        stackType: 'regular',
                        axisAlpha: 1,
                        axisColor: TincannyUI.colors.primary,
                        color: TincannyUI.colors.primary,
                        labelOffset: 6,
                        tickLength: 0,
                        title: '',
                        titleBold: false,
                        titleFontSize: 13,
                        titleRotation: -90
                    },
                    // Below we will check if we have to add or not the Tin Can graph
                ],
                allLabels: [],
                balloon: {},
                legend: {
                    enabled: true,
                    useGraphSettings: true
                },
                titles: [],
                dataProvider: completionAndTinCanDates
            }

            // Check if we have to add the Tin Can data
            if ( TincannyUI.show.tinCanData ){
                // Add Graph
                chart.graphs.push({
                    balloonColor: TincannyUI.colors.secondary,
                    balloonText: sprintf( reportingApiSetup.localizedStrings.graphTooltipStatements, '[[value]]' ),
                    columnWidth: 0,
                    fillColors: TincannyUI.colors.secondary,
                    id: 'tin-can',
                    lineThickness: 2,
                    minDistance: 0,
                    negativeBase: 2,
                    showBulletsAt: 'open',
                    title: reportingApiSetup.localizedStrings.graphTinCanStatements,
                    topRadius: 0,
                    type: 'smoothedLine',
                    valueAxis: 'tin-can',
                    valueField: 'tinCan',
                    yAxis: 'tin-can'
                });

                // Add value axes
                chart.valueAxes.push({
                    id: 'tin-can',
                    pointPosition: 'middle',
                    position: 'right',
                    stackType: 'regular',
                    axisAlpha: 1,
                    axisColor: TincannyUI.colors.secondary,
                    color: TincannyUI.colors.secondary,
                    title: '',
                    titleBold: false,
                    titleFontSize: 13,
                    titleRotation: 90
                });
            }

            if ( isDefined( wp.hooks ) ){
                chart = wp.hooks.applyFilters( 'tc_chartObject', chart, completionAndTinCanDates );
            }

            // Create Recent Activities chart
            AmCharts.makeChart( 'reporting-recent-activities', chart );
        },

        metaboxMostAndLeastCompletedCourses: function(){
            // Get list
            let courses = this.dataObject.top_course_completions;

            // Clean data
            courses = courses.map(( element ) => {
                // Define default percentage
                let percentage = 0;

                // Now get the real percentage
                // Get the number of enrolled users
                let numberOfEnrolledUsers = isDefined( element.course_user_access_list ) ? Object.keys( element.course_user_access_list ).length : 0;

                // Check if it has at least one enrolled user
                if ( numberOfEnrolledUsers >= 1 ){
                    percentage = Math.floor( ( element.completions / Object.keys( element.course_user_access_list ).length ) * 100 );
                }

                // Return new object
                return {
                    percentage: percentage,
                    title: element.post_title
                }
            });

            // Sort data
            courses = courses.sort(( a, b ) => {
                return b.percentage - a.percentage;
            });

            // Add element number
            courses = courses.map(( element, index ) => {
                return Object.assign({}, element, {
                    order: index + 1
                })
            });

            // Get parts (top, middle, bottom)
            let coursesRanking = {
                   top:    [],
                   middle: [],
                   bottom: [] 
                },
                coursesCopy    = courses.slice(0);

            // Get best ones
            coursesRanking.top    = coursesCopy.slice( 0, 3 );
            coursesCopy           = coursesCopy.slice( 3 );

            // Get the ones at the bottom
            if ( coursesCopy.length <= 3 ){
                coursesRanking.bottom = coursesCopy;
                coursesCopy           = [];
            }
            else {
                coursesRanking.bottom = coursesCopy.slice( coursesCopy.length - 3, coursesCopy.length );
                coursesCopy           = coursesCopy.slice( 0, coursesCopy.length - 3 );
            }

            // Get the ones at the middle
            coursesRanking.middle = coursesCopy;

            // Get container where we're going to insert the list of courses
            let $container = $( '#reporting-completed-ranking' );

            // Remove loading data
            $container.html( '' );
            
            // Create function to create elements
            let createRow = ( course ) => {
                return `
                    <div class="reporting-completed-ranking__item">
                        <div class="reporting-completed-ranking__order">${ course.order }</div>
                        <div class="reporting-completed-ranking__title">${ course.title }</div>
                        <div class="reporting-completed-ranking__percentage">${ course.percentage }%</div>
                    </div>
                `;
            }

            // Check if we have items to add 
            if ( courses.length > 0 ){
                // Add elements to the container
                if ( coursesRanking.top.length > 0 ){
                    $container.append( $( `
                        <div class="reporting-completed-ranking__top">
                            ${ coursesRanking.top.map(( element ) => {
                                return createRow( element );
                            }).join( '' ) }
                        </div>`
                    ));
                }

                if ( coursesRanking.middle.length > 0 ){
                    $container.append($( `
                        <div class="reporting-completed-ranking__middle">
                            <div id="reporting-completed-ranking-middle__points" class="reporting-completed-ranking-middle__points" tclr-tooltip="${ sprintf( reportingApiSetup.localizedStrings.overviewBoxesReportsCoursesCompletionSeeAll, reportingApiSetup.learnDashLabels.courses.toLowerCase() ) }">
                                ···
                            </div>
                            <div id="reporting-completed-ranking-middle__items" class="reporting-completed-ranking-middle__items">
                                ${ coursesRanking.middle.map(( element ) => {
                                    return createRow( element );
                                }).join( '' ) }
                            </div>
                        </div>`
                    ));
                }

                if ( coursesRanking.bottom.length > 0 ){
                    $container.append( $( `
                        <div class="reporting-completed-ranking__bottom">
                            ${ coursesRanking.bottom.map(( element ) => {
                                return createRow( element );
                            }).join( '' ) }
                        </div>`
                    ));
                }
            }
            else {
                // Add "No course completions" notice
                // Create notice
                let $notice = $( `
                    <div class="reporting-dashboard-status reporting-dashboard-status--warning">
                        <div class="reporting-dashboard-status__icon"></div>
                        <div class="reporting-dashboard-status__text">
                            ${ reportingApiSetup.localizedStrings.overviewBoxesReportsCoursesCompletionNoData }
                        </div>
                    </div>
                ` );

                // Append notice
                $container.append( $notice );
            }

            // Bind button to show all courses
            let $showAllElements = {
                btn:   $( '#reporting-completed-ranking-middle__points' ),
                items: $( '#reporting-completed-ranking-middle__items' )
            }

            // Listen click
            $showAllElements.btn.on( 'click', () => {
                // Hide button
                $showAllElements.btn.hide();

                // Show items
                $showAllElements.items.slideToggle( 300 );
            });

            // Define width of .reporting-completed-ranking__order and .reporting-completed-ranking__percentage
            // Context: Normally we would do this with CSS using a table structure, but in this case
            // we have each row separated in three different containers; in the first container 
            // .reporting-completed-ranking__order can have 1 digit, while in the third container
            // that element can have 2 digits (or even more in big sites). So to fix this we have to use JS
            let fixedWidth = {
                order:      0,
                percentage: 0
            }

            $( '.reporting-completed-ranking__order' ).each(( index, element ) => {
                // Get cell width
                let cellWidth = $( element ).width();

                // Check if it's the biggest one
                fixedWidth.order = fixedWidth.order < cellWidth ? cellWidth : fixedWidth.order;
            });

            $( '.reporting-completed-ranking__percentage' ).each(( index, element ) => {
                // Get cell width
                let cellWidth = $( element ).width();

                // Check if it's the biggest one
                fixedWidth.percentage = fixedWidth.percentage < cellWidth ? cellWidth : fixedWidth.percentage;
            });

            $( '.reporting-completed-ranking__order' ).css({
                width: `${ fixedWidth.order }px`
            });

            $( '.reporting-completed-ranking__percentage' ).css({
                width: `${ fixedWidth.percentage }px`
            });
        },

        groupSelector: function(){
            // Get group selector
            let $groupSelector = $( '#reporting-group-selector' );

            // Check if it exists
            if ( $groupSelector.length == 1 ){
                // Init select2
                $groupSelector.select2({
                    theme: 'default tclr-select2'
                });
            }
        },

        html: `
            <div class="tclr uo-reporting-dashboard-container">
                <div class="reporting-dashboard-col-container reporting-dashboard-col-1">
                    <div class="reporting-dashboard-col-inner-container">
                        <div class="reporting-dashboard-col-heading">${ reportingApiSetup.localizedStrings.overviewBoxesTitleReports }</div>
                        <div class="reporting-dashboard-col-content">
                            <div class="reporting-dashboard-quick-links">
                                <div class="reporting-dashboard-quick-links__item" data-id="courseReportTab" data-append="">
                                    <div class="reporting-dashboard-quick-links__icon">
                                        <span class="tincanny-icon tincanny-icon-book"></span>
                                    </div>
                                    <div class="reporting-dashboard-quick-links__content">
                                        <div class="reporting-dashboard-quick-links__title">
                                            ${ sprintf( reportingApiSetup.localizedStrings.overviewBoxesReportsCourseReportTitle, reportingApiSetup.learnDashLabels.course ) }
                                        </div>
                                        <div class="reporting-dashboard-quick-links__description">
                                            ${ reportingApiSetup.localizedStrings.overviewBoxesReportsCourseReportDescription }
                                        </div>
                                    </div>
                                </div>
                                <div class="reporting-dashboard-quick-links__item" data-id="userReportTab" data-append="&tab=userReportTab">
                                    <div class="reporting-dashboard-quick-links__icon">
                                        <span class="tincanny-icon tincanny-icon-user"></span>
                                    </div>
                                    <div class="reporting-dashboard-quick-links__content">
                                        <div class="reporting-dashboard-quick-links__title">
                                            ${ reportingApiSetup.localizedStrings.overviewBoxesReportsUserReportTitle }
                                        </div>
                                        <div class="reporting-dashboard-quick-links__description">
                                            ${ reportingApiSetup.localizedStrings.overviewBoxesReportsUserReportDescription }
                                        </div>
                                    </div>
                                </div>
                                <div class="reporting-dashboard-quick-links__item" data-id="tin-can" data-append="&tab=tin-can">
                                    <div class="reporting-dashboard-quick-links__icon">
                                        <span class="tincanny-icon tincanny-icon-chart-bar"></span>
                                    </div>
                                    <div class="reporting-dashboard-quick-links__content">
                                        <div class="reporting-dashboard-quick-links__title">
                                            ${ reportingApiSetup.localizedStrings.overviewBoxesReportsTinCanReportTitle }
                                        </div>
                                        <div class="reporting-dashboard-quick-links__description">
                                            ${ reportingApiSetup.localizedStrings.overviewBoxesReportsTinCanReportDescription }
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="reporting-dashboard-quick-stats">
                                <div class="reporting-dashboard-quick-stats__item" data-id="users">
                                    <div class="reporting-dashboard-quick-stats__number">-</div>
                                    <div class="reporting-dashboard-quick-stats__description">${ reportingApiSetup.localizedStrings.overviewUsers }</div>
                                </div>
                                <div class="reporting-dashboard-quick-stats__item" data-id="courses">
                                    <div class="reporting-dashboard-quick-stats__number">-</div>
                                    <div class="reporting-dashboard-quick-stats__description">${ reportingApiSetup.learnDashLabels.courses }</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="reporting-dashboard-col-container reporting-dashboard-col-2">
                    <div class="reporting-dashboard-col-inner-container">
                        <div class="reporting-dashboard-col-heading">${ reportingApiSetup.localizedStrings.overviewBoxesTitleRecentActivities }</div>
                        <div class="reporting-dashboard-col-content reporting-dashboard-col-content--small-padding">
                            <div id="reporting-recent-activities" class="reporting-recent-activities">
                                <div class="reporting-dashboard-status reporting-dashboard-status--loading">
                                    <div class="reporting-dashboard-status__icon"></div>
                                    <div class="reporting-dashboard-status__text">
                                        ${ reportingApiSetup.localizedStrings.overviewLoading }
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="reporting-dashboard-col-container reporting-dashboard-col-3">
                    <div class="reporting-dashboard-col-inner-container">
                        <div class="reporting-dashboard-col-heading">
                            ${ sprintf( reportingApiSetup.localizedStrings.overviewBoxesTitleCompletedCourses, reportingApiSetup.learnDashLabels.courses ) }
                        </div>
                        <div class="reporting-dashboard-col-content">
                            <div class="reporting-completed-ranking" id="reporting-completed-ranking">
                                <div class="reporting-dashboard-status reporting-dashboard-status--loading">
                                    <div class="reporting-dashboard-status__icon"></div>
                                    <div class="reporting-dashboard-status__text">
                                        ${ reportingApiSetup.localizedStrings.overviewLoading }
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`,
    };

    tinCannyDashboard.init();
});