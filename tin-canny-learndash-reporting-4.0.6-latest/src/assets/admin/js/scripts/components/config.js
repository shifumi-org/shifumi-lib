var ls = function( string ){

    if( typeof reportingApiSetup.localizedStrings[string] !== 'undefined' ){
        return reportingApiSetup.localizedStrings[string];
    }else{
        return string;
    }
};

jQuery(document).ready(function ($) {

	$.fn.dataTable.ext.errMode = 'none';

// Setup Page Tab Navigation
    reportingTabs.addTabEvents();
    reportingTabs.addTableEvents();
    reportingTabs.addNavigationEvents();

    // Check Query parameters
    // If it is TinCan search, nav goes to Tin Can Tab
    reportingQueryString.init();

    var saveSettings = function(event){

        event.preventDefault();

        // Cache inputs
        var tinCanActivation = $('input[type=radio][name=tinCanActivation]');
        var disableMarkComplete = $('input[type=radio][name=disableMarkComplete]');
        var nonceProtection = $('input[type=radio][name=nonceProtection]');

        // Disable inputs while storing data
        tinCanActivation.prop('disabled', true);
        disableMarkComplete.prop('disabled', true);
        nonceProtection.prop('disabled', true);

        // Get settings values
        var tinCanActivationSelected = $('input[type=radio][name=tinCanActivation]:checked');
        var disableMarkCompleteSelected = $('input[type=radio][name=disableMarkComplete]:checked');
        var nonceProtectionSelected = $('input[type=radio][name=nonceProtection]:checked');

        // Make Api Calls to save input values
        var toggleTinCan = uoReportingAPI.reportingApiCall('show_tincan',tinCanActivationSelected.val());
        var toggleMarkComplete = uoReportingAPI.reportingApiCall('disable_mark_complete',disableMarkCompleteSelected.val());
        var toggleNonceProtection = uoReportingAPI.reportingApiCall('nonce_protection',nonceProtectionSelected.val());

        toggleTinCan.done(function (response) {

            if( '1' === tinCanActivationSelected.val() ){
                $('li#menuTinCan').show();
                $('a[href="#tin-can"]').show();
            }

            if( '0' === tinCanActivationSelected.val() ){
                $('li#menuTinCan').hide();
                $('a[href="#tin-can"]').hide();
            }

            $('input[type=radio][name=tinCanActivation]').prop('disabled', false);
            $('#save-settings').addClass('reporting-button-green');

        });

        toggleMarkComplete.done(function (response) {
            $('input[type=radio][name=disableMarkComplete]').prop('disabled', false);
            $('#save-settings').addClass('reporting-button-green');

            setTimeout(function(){
                $('#save-settings').removeClass('reporting-button-green');
            },2500 );

        });

        toggleNonceProtection.done(function (response) {
            $('input[type=radio][name=nonceProtection]').prop('disabled', false);
            $('#save-settings').addClass('reporting-button-green');

            setTimeout(function(){
                $('#save-settings').removeClass('reporting-button-green');
            },2500 );

        });

        setTimeout(function(){

        },3000 );
    };

	var resetTinCanData = function( event ) {
		event.preventDefault();
		var text = ls('Are you sure you want to permanently delete all Tin Can data? This cannot be undone.');

		if ( ! confirm( text ) )
			return;

		$( '#btnResetTinCanData' ).attr( 'disabled', 'disabled' );

        var callResetTinCanData = uoReportingAPI.reportingApiCall( 'reset_tincan_data' );

        callResetTinCanData.done( function ( response ) {
			$( '#btnResetTinCanData' ).removeAttr( 'disabled' );

			$( '.uo-admin-reporting-tab-single#tin-can .tablenav-pages' ).html( '' );
			$( '.uo-admin-reporting-tab-single#tin-can .tablenav.bottom' ).html( '' );
			$( '.uo-admin-reporting-tab-single#tin-can .wp-list-table' ).remove();
        });
    };

    var resetQuizData = function( event ) {

        event.preventDefault();
        var text = ls('Are you sure you want to permanently delete all Quiz data? This cannot be undone.');

        if ( ! confirm( text ) )
            return;

        $( '#btnResetQuizData' ).attr( 'disabled', 'disabled' );

        var callResetQuizData = uoReportingAPI.reportingApiCall( 'reset_quiz_data' );

        callResetQuizData.done( function ( response ) {
            $( '#btnResetQuizData' ).removeAttr( 'disabled' );

            $( '.uo-admin-reporting-tab-single#xapi-tincan .tablenav-pages' ).html( '' );
            $( '.uo-admin-reporting-tab-single#xapi-tincan .tablenav.bottom' ).html( '' );
            $( '.uo-admin-reporting-tab-single#xapi-tincan .wp-list-table' ).remove();
        });
    };

    var resetBookmarkCanData = function( event ) {
        event.preventDefault();
        var text = ls('Are you sure you want to permanently delete all Bookmark data? This cannot be undone.');

        if ( ! confirm( text ) )
            return;

        $( '#btnResetBookmarkData' ).attr( 'disabled', 'disabled' );

        var callbtnResetBookmarkData = uoReportingAPI.reportingApiCall( 'reset_bookmark_data' );

        callbtnResetBookmarkData.done( function ( response ) {
            $( '#btnResetTinCanData' ).removeAttr( 'disabled' );
        });
    };

    var purgeExperienced = function( event ) {
        event.preventDefault();
        var text = ls('Are you sure you want to permanently delete all saved xApi statements with the "Experienced" verb? This cannot be undone.');

        if ( ! confirm( text ) )
            return;

        $( '#btnPurgeExperienced' ).attr( 'disabled', 'disabled' );

        var callbtnPurgeExperienced = uoReportingAPI.reportingApiCall( 'purge_experienced' );

        callbtnPurgeExperienced.done( function ( response ) {
            $( '#btnPurgeExperienced' ).removeAttr( 'disabled' );
        });
    };


    var purgeAnswered = function( event ) {
        event.preventDefault();
        var text = ls('Are you sure you want to permanently delete all saved xApi statements with the "Answered" verb? This cannot be undone.');

        if ( ! confirm( text ) )
            return;

        $( '#btnPurgeAnswered' ).attr( 'disabled', 'disabled' );

        var callbtnPurgeAnswered = uoReportingAPI.reportingApiCall( 'purge_answered' );

        callbtnPurgeAnswered.done( function ( response ) {
            $( '#btnPurgeAnswered' ).removeAttr( 'disabled' );
        });
    };

    // On Click save settings button store user input selections
    $('#save-settings').on('click', saveSettings );

    // On Click Reset Tin Can Data button
    $('#btnResetTinCanData').on('click', resetTinCanData );
    $('#btnResetQuizData').on('click', resetQuizData );
    $('#btnResetBookmarkData').on('click', resetBookmarkCanData );
    $('#btnPurgeExperienced').on('click', purgeExperienced );
    $('#btnPurgeAnswered').on('click', purgeAnswered );

});


