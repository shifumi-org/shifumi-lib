var chartVars = {


    courseOverviewGraphTable: [],

    setCourseOverviewGraphTable: function (table, sortOn) {

        if (typeof sortOn == 'undefined') {
            sortOn = 1;
        }

        this.courseOverviewGraphTable = table.sort(function (a, b) {
            return (a[sortOn] > b[sortOn]) ? 1 : ((b[sortOn] > a[sortOn]) ? -1 : 0);
        });

    },

    courseOverviewGraph: function () {

        var courseSummary = [];

        courseSummary[0] = {};
        courseSummary[0].name = this.courseOverviewGraphTable[0][1];
        courseSummary[0].notStarted = this.courseOverviewGraphTable[0][3];
        courseSummary[0].inProgress = this.courseOverviewGraphTable[0][4];
        courseSummary[0].completed = this.courseOverviewGraphTable[0][5];

        courseSummary[1] = {};
        courseSummary[1].name = this.courseOverviewGraphTable[1][1];
        courseSummary[1].notStarted = this.courseOverviewGraphTable[1][3];
        courseSummary[1].inProgress = this.courseOverviewGraphTable[1][4];
        courseSummary[1].completed = this.courseOverviewGraphTable[1][5];

        courseSummary[2] = {};
        courseSummary[2].name = this.courseOverviewGraphTable[2][1];
        courseSummary[2].notStarted = this.courseOverviewGraphTable[2][3];
        courseSummary[2].inProgress = this.courseOverviewGraphTable[2][4];
        courseSummary[2].completed = this.courseOverviewGraphTable[2][5];

        return {
            "type": "serial",
            "categoryField": "category",
            "rotate": true,
            "plotAreaFillAlphas": 1,
            "plotAreaFillColors": "#FFFFFF",
            "startDuration": 1,
            "theme": "light",
            "categoryAxis": {
                "gridPosition": "start",
                "fontSize": 15,
                "labelOffset": 6,
                "axisColor": "#C4C3C3",
                "axisThickness": 3,
                "tickLength": 0,
                "titleFontSize": 14
            },
            "graphs": [
                {
                    "balloonText": "",
                    "fillAlphas": 1,
                    "fillColors": "#f1f1f1",
                    "gapPeriod": 2,
                    "id": "AmGraph-1",
                    "labelText": "[[value]]",
                    "lineColor": "#E1E1E1",
                    "tabIndex": 1,
                    "title": ls("Not Started"),
                    "type": "column",
                    "valueField": "notStarted"
                },
                {
                    "balloonText": "",
                    "color": "#FFFFFF",
                    "fillAlphas": 1,
                    "fillColors": "#7bc47b",
                    "gapPeriod": 2,
                    "id": "AmGraph-2",
                    "labelText": "[[value]]",
                    "lineColor": "#E1E1E1",
                    "tabIndex": 1,
                    "title": ls("In Progress"),
                    "type": "column",
                    "valueField": "inProgress"
                },
                {
                    "balloonText": "",
                    "color": "#FFFFFF",
                    "fillAlphas": 1,
                    "fillColors": "#29779e",
                    "gapPeriod": 2,
                    "id": "AmGraph-3",
                    "labelText": "[[value]]",
                    "lineColor": "#E1E1E1",
                    "tabIndex": 1,
                    "title": ls("Completed"),
                    "type": "column",
                    "valueField": "completed"
                }
            ],
            "valueAxes": [
                {
                    "id": "ValueAxis-1",
                    "axisColor": "#C4C3C3",
                    "axisThickness": 3,
                    "stackType": "regular",
                    "title": ""
                }
            ],
            "legend": {
                "enabled": true,
                "useGraphSettings": true,
                "position": "right"
            },
            "titles": [
                {
                    "id": "Title-1",
                    "text": ""
                }
            ],
            "dataProvider": [
                {
                    "category": courseSummary[0].name,
                    "notStarted": courseSummary[0].notStarted,
                    "inProgress": courseSummary[0].inProgress,
                    "completed": courseSummary[0].completed
                },
                {
                    "category": courseSummary[1].name,
                    "notStarted": courseSummary[1].notStarted,
                    "inProgress": courseSummary[1].inProgress,
                    "completed": courseSummary[1].completed
                },
                {
                    "category": courseSummary[2].name,
                    "notStarted": courseSummary[2].notStarted,
                    "inProgress": courseSummary[2].inProgress,
                    "completed": courseSummary[2].completed
                }
            ]
        };

    },

    courseSingleOverviewPieChartData: function( courseID ){

        let completed = 0;
        let inProgress = 0;
        let notStarted = 0;
        let userAccess;
        let enrolled = 0;

        if ('open' === dataObject.courseList[courseID].course_price_type) {
            enrolled = Object.keys(dataObject.userList.allUserIds).length;
        } else {

            if ("1" === reportingApiSetup.optimized_build) {
                if (typeof dataObject.userList.course_user_access_list[courseID] !== 'undefined') {

                    // Check enrollment
                    if ('open' === dataObject.courseList[courseID].course_price_type) {
                        userAccess = dataObject.userList.allUserIds;
                    } else {
                        userAccess = dataObject.userList.course_user_access_list[courseID];
                    }

                    jQuery.each(userAccess, function (key, userID) {
                        if (typeof dataObject.userList.userOverview[userID] === 'undefined') {
                            return true;
                        }
                        enrolled++;
                    });
                } else {
                    enrolled = 0;
                }
            }
        }

        if (typeof dataObject.userList.completed[courseID] !== 'undefined') {
            completed = dataObject.userList.completed[courseID];
        }

        if (typeof dataObject.userList.inProgress[courseID] !== 'undefined') {
            inProgress = dataObject.userList.inProgress[courseID];
        } else {
            inProgress = 0;
        }

        notStarted = enrolled - (completed + inProgress);

        // Check if it has data
        let hasData = ! ( completed == 0 && inProgress == 0 && notStarted == 0 );

        return {
            type: 'pie',
            listeners: [{
                event: 'drawn',
                method: () => {
                    // If it doesn't have data then render a notice
                    if ( ! hasData ){
                        // Get container
                        let $chartContainer = jQuery( '#courseSingleOverviewPieChart .amcharts-chart-div' );

                        // Create notice
                        let $notice = jQuery( `
                            <div class="reporting-dashboard-status reporting-dashboard-status--warning">
                                <div class="reporting-dashboard-status__icon"></div>
                                <div class="reporting-dashboard-status__text">
                                    ${ reportingApiSetup.localizedStrings.graphNoEnrolledUsers }
                                </div>
                            </div>
                        ` );

                        // Append notice
                        $chartContainer.append( $notice );
                    }
                },
            }],
            balloonText: '[[title]]<br><span style="font-size:13px"><b>[[value]]</b> ([[percents]]%)</span>',
            innerRadius: '40%',
            startDuration: 0,
            colors: [
                TincannyUI.colors.status.completed,
                TincannyUI.colors.status.inProgress,
                TincannyUI.colors.status.notStarted,
                '#FCD202',
                '#F8FF01',
                '#B0DE09',
                '#04D215',
                '#0D8ECF',
                '#0D52D1',
                '#2A0CD0',
                '#8A0CCF',
                '#CD0D74',
                '#754DEB',
                '#DDDDDD',
                '#999999',
                '#333333',
                '#000000',
                '#57032A',
                '#CA9726',
                '#990000',
                '#4B0C25'
            ],
            marginBottom: 0,
            marginTop: 0,
            titleField: 'category',
            valueField: 'value',
            fontFamily: TincannyUI.mainFont,
            fontSize: 14,
            theme: 'light',
            allLabels: [],
            balloon: {},
            titles: [],
            dataProvider: [
                {
                    category: ls( 'Completed' ),
                    value: completed
                },
                {
                    category: ls( 'In Progress' ),
                    value: inProgress
                },
                {
                    category: ls( 'Not Started' ),
                    value: notStarted
                }
            ]
        }
    },

    courseSingleCompletionChartData: function ( courseID ) {
        const $ = jQuery;

        var completionDates = [];

        jQuery.each( dataObject.userList.courseCompletionByCourse[courseID], function( index, data){
            completionDates.push({"date": data.date, "amountUsers": data.completions } );
        });

        completionDates.sort(function(a,b){
            // Turn your strings into dates, and then subtract them
            // to get a value that is either negative, positive, or zero.
            return (new Date(b.date) - new Date(a.date))*-1;
        });

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
                    let currentEntryDate = new Date( `${currentEntryYear}-${currentEntryMonth}-${currentEntryDay}T00:00:00Z` );

                    // Get date of the next entry
                    let [, nextEntryYear, nextEntryMonth, nextEntryDay] = datePattern.exec( nextEntry.date );
                    let nextEntryDate = new Date( `${nextEntryYear}-${nextEntryMonth}-${nextEntryDay}T00:00:00Z` );

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

        // Check if it has data
        let hasData = completionDates.length > 0;

        // If there is no data then add some
        // We only have to add two days separated by 7 days. The following
        // function will add all the missing dates (the ones between those two)
        if ( ! hasData ){
            // Get dates
            let dateNow     = new Date(),
                dateDaysAgo = new Date();

            dateDaysAgo.setDate( dateDaysAgo.getDate() - 7 );

            // Get parts of both dates
            let dateParts = {
                now: {
                    year:  dateNow.getUTCFullYear(),
                    month: dateNow.getUTCMonth() + 1,
                    day:   dateNow.getUTCDate()
                },
                daysAgo: {
                    year:  dateDaysAgo.getUTCFullYear(),
                    month: dateDaysAgo.getUTCMonth() + 1,
                    day:   dateDaysAgo.getUTCDate()
                }
            };

            // Add zeros
            dateParts.now.month     = dateParts.now.month < 10 ? `0${ dateParts.now.month }` : dateParts.now.month;
            dateParts.now.day       = dateParts.now.day < 10 ? `0${dateParts.now.day }` :dateParts.now.day;
            dateParts.daysAgo.month = dateParts.daysAgo.month < 10 ? `0${ dateParts.daysAgo.month }` : dateParts.daysAgo.month;
            dateParts.daysAgo.day   = dateParts.daysAgo.day < 10 ? `0${ dateParts.daysAgo.day }` : dateParts.daysAgo.day;

            completionDates.push( ...[{
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
            let [, lastElementYear, lastElementMonth, lastElementDay] = datePattern.exec( completionDates[ completionDates.length - 1 ].date );
            let lastElementDate = new Date( `${ lastElementYear }-${ lastElementMonth }-${ lastElementDay }T00:00:00Z` );

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
                completionDates.push({
                    date: `${ dateParts.year }-${ dateParts.month }-${ dateParts.day }`
                });
            }               
        }

        // Add missing days
        completionDates = addMissingDaysToGraphData( completionDates );

        // If there isn't enough data to fill 7 days then add some
        // days to complete the week
        if ( completionDates.length < 7 ){
            // Calculate days to add
            let daysToAdd = 7 - completionDates.length;

            // Get date of the first element
            let [, firstElementYear, firstElementMonth, firstElementDay] = datePattern.exec( completionDates[0].date );
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
            completionDates = [{
                date: `${ dateParts.year }-${ dateParts.month }-${ dateParts.day }`
            }, ...completionDates ];

            // Add missing days
            completionDates = addMissingDaysToGraphData( completionDates );
        }

        // If it has data, then
        if ( hasData ){
            // Add missing properties
            completionDates = completionDates.map(( entry ) => {
                return {
                    date:        entry.date,
                    amountUsers: entry.amountUsers === undefined ? 0 : entry.amountUsers,
                }
            });
        }

        return {
            type: 'serial',
            listeners: [{
                event: 'init',
                method: ( e ) => {
                    // Define the number of days to show
                    const numberOfDaysToShow = 30;

                    // Zoom automatically to the last X days
                    // First, check if there are more than X days of data
                    if ( completionDates.length >= numberOfDaysToShow ){
                        // Get dates
                        let dates = {
                            start: completionDates[ completionDates.length - 1 - numberOfDaysToShow ].date,
                            end:   completionDates[ completionDates.length - 1 ].date
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
                        let $chartContainer = $( '#courseSingleActivitiesGraph .amcharts-chart-div' );

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
                graph: 'amountUsers',
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
                    columnWidth: 0,
                    id: 'amountUsers',
                    lineThickness: 2,
                    minDistance: 0,
                    negativeBase: 2,
                    showBulletsAt: 'open',
                    title: sprintf( reportingApiSetup.localizedStrings.graphCourseCompletions, reportingApiSetup.learnDashLabels.course ),
                    topRadius: 0,
                    type: 'smoothedLine',
                    valueAxis: 'amountUsers',
                    valueField: 'amountUsers',
                    yAxis: 'amountUsers'
                }
            ],
            guides: [],
            valueAxes: [
                {
                    id: 'amountUsers',
                    pointPosition: 'middle',
                    position: 'right',
                    stackType: 'regular',
                    axisAlpha: 1,
                    axisColor: TincannyUI.colors.primary,
                    color: TincannyUI.colors.primary,
                    title: '',
                    titleBold: false,
                    titleFontSize: 13,
                    titleRotation: 90
                }
            ],
            allLabels: [],
            balloon: {},
            legend: {
                enabled: true,
                useGraphSettings: true
            },
            titles: [],
            dataProvider: completionDates
        }
    }
};