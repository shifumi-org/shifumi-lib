/*
 * Parses the query string(URL)
 *
 * @Class Reporting Query String
 * @since 1.0.1
 *
 */
var reportingQueryString = {};


//Store array query string data values
reportingQueryString.vars = undefined;

reportingQueryString.isTinCanOnly = false;


reportingQueryString.init = function(){

    // Stored array of query string key values(URL data values)
    var query_string = {};

    var query = window.location.search.substring(1);
    var vars = query.split("&");

    for (var i=0;i<vars.length;i++) {
        var pair = vars[i].split("=");
        // If first entry with this name
        if (typeof query_string[pair[0]] === "undefined") {
            query_string[pair[0]] = decodeURIComponent(pair[1]);
            // If second entry with this name
        } else if (typeof query_string[pair[0]] === "string") {
            var arr = [ query_string[pair[0]],decodeURIComponent(pair[1]) ];
            query_string[pair[0]] = arr;
            // If third or later entry with this name
        } else {
            query_string[pair[0]].push(decodeURIComponent(pair[1]));
        }
    }

    reportingQueryString.vars = query_string;

    if( reportingQueryString.vars.hasOwnProperty('tc_filter_mode') ){

        // Hide loader and show page
        jQuery('#reporting-loader').hide();
        jQuery('#reporting-loader-dashboard').hide();
        jQuery('.uo-admin-reporting-tabs').show();
        jQuery('.uo-admin-reporting-tabgroup').show();

        reportingQueryString.isTinCanOnly = true;

        if ( reportingQueryString.vars.hasOwnProperty('tab') && reportingQueryString.vars.tab === 'xapi-tincan'){
            reportingTabs.triggerTabGroup('#xapi-tincan');
            var inactiveTinCanReportTable = jQuery('#tin-can table.toplevel_page_uncanny-learndash-reporting');
            inactiveTinCanReportTable.after( "<h3>" + ls('Please select your criteria to filter the Tin Can data.') + "</h3>" );
            inactiveTinCanReportTable.hide();
            jQuery('#tin-can .tablenav.bottom').hide();
        } else {
            reportingTabs.triggerTabGroup('#tin-can');
            var inactiveTinCanReportTable = jQuery('#xapi-tincan table.toplevel_page_uncanny-learndash-reporting');
            inactiveTinCanReportTable.after( "<h3>" + ls('Please select your criteria to filter the xAPI Quiz data.') + "</h3>" );
            inactiveTinCanReportTable.hide();
            jQuery('#xapi-tincan .tablenav.bottom').hide();
        }

    }else{

        var inactiveTinCanReportTable = jQuery('#tin-can table.toplevel_page_uncanny-learndash-reporting');
        inactiveTinCanReportTable.after( "<h3>" + ls('Please select your criteria to filter the Tin Can data.') + "</h3>" );
        inactiveTinCanReportTable.hide();

        var inactiveTinCanReportTable = jQuery('#xapi-tincan table.toplevel_page_uncanny-learndash-reporting');
        inactiveTinCanReportTable.after( "<h3>" + ls('Please select your criteria to filter the xAPI Quiz data.') + "</h3>" );
        inactiveTinCanReportTable.hide();
        // Hide Tin Can Table Navigation
        jQuery('.tablenav.bottom').hide();

    }
};