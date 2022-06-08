/**
 * wpDataTable plugin config object
 *
 * Contains all the settings for the plugin.
 * setter methods adjust the binded jQuery elements
 *
 * @author Miljko Milosevic
 * @since 23.11.2016
 */

var wpdatatable_plugin_config = {

    setSeparateConnection: function( separateConnection ){
        wdt_current_config.wdtUseSeparateCon = separateConnection;
        if( separateConnection == 1 ){
            jQuery('.plugin-settings .mysql-serverside-settings-block').animateFadeIn();
        }else{
            jQuery('.plugin-settings .mysql-serverside-settings-block').addClass('hidden');
        }
        if( jQuery('#wdt-separate-connection').val() != separateConnection ) {
            jQuery('#wdt-separate-connection').prop('checked', separateConnection);
        }
    },

    setLanguage: function( language ){
        if( wdt_current_config.wdtInterfaceLanguage != language ){
            wdt_current_config.wdtInterfaceLanguage = language;
        }
        if( jQuery('#wdt-interface-language').val() != language ){
            jQuery('#wdt-interface-language').selectpicker( 'val', language );
        }
    },

    setDateFormat: function ( dateFormat ) {
        if( wdt_current_config.wdtDateFormat != dateFormat ){
            wdt_current_config.wdtDateFormat = dateFormat;
        }
        if( jQuery('#wdt-date-format').val() != dateFormat ){
            jQuery('#wdt-date-format').selectpicker( 'val', dateFormat );
        }
    },

    setTablesAdmin: function ( tablesAdmin ) {
        if( wdt_current_config.wdtTablesPerPage != tablesAdmin ){
            wdt_current_config.wdtTablesPerPage = tablesAdmin;
        }
        if( jQuery('#wdt-tables-per-page').val() != tablesAdmin ){
            jQuery('#wdt-tables-per-page').selectpicker( 'val', tablesAdmin );
        }
    },

    setTimeFormat: function ( timeFormat ) {
        if( wdt_current_config.wdtTimeFormat != timeFormat ){
            wdt_current_config.wdtTimeFormat = timeFormat;
        }
        if( jQuery('#wdt-time-format').val() != timeFormat ){
            jQuery('#wdt-time-format').selectpicker( 'val', timeFormat );
        }
    },

    setBaseSkin: function ( baseSkin ) {
        if( wdt_current_config.wdtBaseSkin != baseSkin ){
            wdt_current_config.wdtBaseSkin = baseSkin;
        }
        if( jQuery('#wdt-base-skin').val() != baseSkin ){
            jQuery('#wdt-base-skin').selectpicker( 'val', baseSkin );
        }
    },

    setNumberFormat: function ( numberFormat ) {
        if( wdt_current_config.wdtNumberFormat != numberFormat ){
            wdt_current_config.wdtNumberFormat = numberFormat;
        }
        if( jQuery('#wdt-number-format').val() != numberFormat ){
            jQuery('#wdt-number-format').selectpicker( 'val', numberFormat );
        }
    },

    setCSVDelimiter: function ( wdtCSVDelimiter ) {
        if( wdt_current_config.wdtCSVDelimiter != wdtCSVDelimiter ){
            wdt_current_config.wdtCSVDelimiter = wdtCSVDelimiter;
        }
        if( jQuery('#wdt-csv-delimiter').val() != wdtCSVDelimiter ){
            jQuery('#wdt-csv-delimiter').selectpicker( 'val', wdtCSVDelimiter );
        }
    },

    setSortingOrderBrowseTables: function ( wdtSortingOrderBrowseTables ) {
        if( wdt_current_config.wdtSortingOrderBrowseTables != wdtSortingOrderBrowseTables ){
            wdt_current_config.wdtSortingOrderBrowseTables = wdtSortingOrderBrowseTables;
        }
        if( jQuery('#wdt-sorting-order-browse-tables').val() != wdtSortingOrderBrowseTables ){
            jQuery('#wdt-sorting-order-browse-tables').selectpicker( 'val', wdtSortingOrderBrowseTables );
        }
    },

    setRenderPosition: function ( renderPosition ) {
        if( wdt_current_config.wdtRenderFilter != renderPosition ){
            wdt_current_config.wdtRenderFilter = renderPosition;
        }
        if( jQuery('#wp-render-filter').val() != renderPosition ){
            jQuery('#wp-render-filter').selectpicker( 'val', renderPosition );
        }
    },

    setDecimalPlaces: function ( decimalPlaces ) {
        if( wdt_current_config.wdtDecimalPlaces != decimalPlaces ){
            wdt_current_config.wdtDecimalPlaces = decimalPlaces;
        }
        if( jQuery('#wdt-decimal-places').val() != decimalPlaces ){
            jQuery('#wdt-decimal-places').val( decimalPlaces );
        }
    },

    setTabletWidth: function ( tabletWidth ) {
        if( wdt_current_config.wdtTabletWidth != tabletWidth ){
            wdt_current_config.wdtTabletWidth = tabletWidth;
        }
        if( jQuery('#wdt-tablet-width').val() != tabletWidth ){
            jQuery('#wdt-tablet-width').val( tabletWidth );
        }
    },

    setMobileWidth: function ( mobileWidth ) {
        if( wdt_current_config.wdtMobileWidth != mobileWidth ){
            wdt_current_config.wdtMobileWidth = mobileWidth;
        }
        if( jQuery('#wdt-mobile-width').val() != mobileWidth ){
            jQuery('#wdt-mobile-width').val( mobileWidth );
        }
    },

    setPurchaseCodeStore: function (purchaseCode) {

        if (parseInt(purchaseCode)) {
            jQuery('.wdt-purchase-code-store-wrapper').hide();
            jQuery('.wdt-purchase-code .wdt-security-massage-wrapper').removeClass('hidden');
            jQuery('#wdt-activate-plugin').removeClass('btn-primary').addClass('btn-danger').html('<i class="wpdt-icon-times-circle-full"></i>Deactivate');
        } else {
            jQuery('.wdt-purchase-code-store-wrapper').show();
            jQuery('.wdt-purchase-code .wdt-security-massage-wrapper').addClass('hidden');
            jQuery('#wdt-activate-plugin').removeClass('btn-danger').addClass('btn-primary').html('<i class="wpdt-icon-check-circle-full"></i>Activate');
        }

    },

    setGettingStartedPageStatus: function (gettingStartedPageStatus) {
        wdt_current_config.wdtGettingStartedPageStatus = gettingStartedPageStatus;
        if( jQuery('#wdt-getting-started-page-status').val() != gettingStartedPageStatus ){
            jQuery('#wdt-getting-started-page-status').prop( 'checked', gettingStartedPageStatus );
        }
    },
    setLiteVSPremiumPageStatus: function (liteVSPremiumPageStatus) {
        wdt_current_config.wdtLiteVSPremiumPageStatus = liteVSPremiumPageStatus;
        if( jQuery('#wdt-lite-vs-premium-page-status').val() != liteVSPremiumPageStatus ){
            jQuery('#wdt-lite-vs-premium-page-status').prop( 'checked', liteVSPremiumPageStatus );
        }
    },
    setIncludeBootstrap: function (includeBootstrap) {
        wdt_current_config.wdtIncludeBootstrap = includeBootstrap;
        if( jQuery('#wdt-include-bootstrap').val() != includeBootstrap ){
            jQuery('#wdt-include-bootstrap').prop( 'checked', includeBootstrap );
        }
    },
    setIncludeBootstrapBackEnd: function (includeBootstrapBackEnd) {
        wdt_current_config.wdtIncludeBootstrapBackEnd = includeBootstrapBackEnd;
        if( jQuery('#wdt-include-bootstrap-back-end').val() != includeBootstrapBackEnd ){
            jQuery('#wdt-include-bootstrap-back-end').prop( 'checked', includeBootstrapBackEnd );
        }
    },
    setPreventDeletingTables: function (preventDeletingTables) {
        wdt_current_config.wdtPreventDeletingTables = preventDeletingTables;
        if( jQuery('#wdt-prevent-deleting-tables').val() != preventDeletingTables ){
            jQuery('#wdt-prevent-deleting-tables').prop( 'checked', preventDeletingTables );
        }
    },

    setParseShortcodes: function ( wdtParseShortcodes ) {
        wdt_current_config.wdtParseShortcodes = wdtParseShortcodes;
        if( jQuery('#wdt-parse-shortcodes').val() != wdtParseShortcodes ){
            jQuery('#wdt-parse-shortcodes').prop( 'checked', wdtParseShortcodes );
        }
    },

    setAlignNumber: function ( alignNumber ) {
        wdt_current_config.wdtNumbersAlign = alignNumber;
        if( jQuery('#wdt-numbers-align').val() != alignNumber ){
            jQuery('#wdt-numbers-align').prop( 'checked', alignNumber );
        }
    },

    setColorFontSetting: function( settingName, settingValue ) {
        if( typeof wdt_current_config.wdtFontColorSettings != 'object' ){
            wdt_current_config.wdtFontColorSettings = {};
        }
        if (wdt_current_config.wdtFontColorSettings[settingName] != settingValue) {
            wdt_current_config.wdtFontColorSettings[settingName] = settingValue;
        }
        if (jQuery('input[data-name=' + settingName + '], select[data-name=' + settingName + ']').val() != settingValue) {
            switch (settingName) {
                case "wdtBorderInputRadius":
                    jQuery('input[data-name=' + settingName + ']').val( settingValue );
                    break;
                case "wdtTableFont":
                    jQuery('select[data-name=' + settingName + ']').selectpicker( 'val', settingValue );
                    break;
                case "wdtFontSize":
                    jQuery('input[data-name=' + settingName + ']').val( settingValue );
                    break;
                default:
                    jQuery('input[data-name=' + settingName + ']').val( settingValue );
                    jQuery('input[data-name=' + settingName + '] + .wpcolorpicker-icon i').css( "background-color",  settingValue );

            }
        }
    },

    setBorderRemoval: function ( borderRemoval ) {
        wdt_current_config.wdtBorderRemoval = borderRemoval;
        if( jQuery('#wdt-remove-borders').val() != borderRemoval ){
            jQuery('#wdt-remove-borders').prop( 'checked', borderRemoval );
        }
    },

    setBorderRemovalHeader: function ( borderRemoval ) {
        wdt_current_config.wdtBorderRemovalHeader = borderRemoval;
        if( jQuery('#wdt-remove-borders-header').val() != borderRemoval ){
            jQuery('#wdt-remove-borders-header').prop( 'checked', borderRemoval );
        }
    },

    setCustomCss: function ( customCss ) {
        if( wdt_current_config.wdtCustomCss != customCss ){
            wdt_current_config.wdtCustomCss = customCss;
        }
        var aceEditorGlobalCSS = ace.edit('wdt-custom-css');
        aceEditorGlobalCSS.$blockScrolling = Infinity;
        if( aceEditorGlobalCSS.getValue() != customCss ){
            aceEditorGlobalCSS.setValue( customCss );
        }
        if( jQuery('#wdt-custom-css').val() != customCss ){
            jQuery('#wdt-custom-css').val( customCss );
        }
    },

    setCustomJs: function ( customJs ) {
        if( wdt_current_config.wdtCustomJs != customJs ){
            wdt_current_config.wdtCustomJs = customJs;
        }
        if(jQuery('#wdt-custom-js').length){
            var aceEditorGlobalJS = ace.edit('wdt-custom-js');
            aceEditorGlobalJS.$blockScrolling = Infinity;
            if( aceEditorGlobalJS.getValue() != customJs ){
                aceEditorGlobalJS.setValue( customJs );
            }
            if( jQuery('#wdt-custom-js').val() != customJs ){
                jQuery('#wdt-custom-js').val( customJs );
            }
        }
    },

    setMinifiedJs: function ( minifiedJs ) {
        wdt_current_config.wdtMinifiedJs = minifiedJs;
        if( jQuery('#wdt-minified-js').val() != minifiedJs ){
            jQuery('#wdt-minified-js').prop( 'checked', minifiedJs );
        }
    },

    setSumFunctionsLabel: function ( sumFunctionsLabel ) {
        if( wdt_current_config.wdtSumFunctionsLabel != sumFunctionsLabel ){
            wdt_current_config.wdtSumFunctionsLabel = sumFunctionsLabel;
        }
        if( jQuery('#wdt-sum-function-label').val() != sumFunctionsLabel ){
            jQuery('#wdt-sum-function-label').val( sumFunctionsLabel );
        }
    },

    setAvgFunctionsLabel: function ( avgFunctionsLabel ) {
        if( wdt_current_config.wdtAvgFunctionsLabel != avgFunctionsLabel ){
            wdt_current_config.wdtAvgFunctionsLabel = avgFunctionsLabel;
        }
        if( jQuery('#wdt-avg-function-label').val() != avgFunctionsLabel ){
            jQuery('#wdt-avg-function-label').val( avgFunctionsLabel );
        }
    },

    setMinFunctionsLabel: function ( minFunctionsLabel ) {
        if( wdt_current_config.wdtMinFunctionsLabel != minFunctionsLabel ){
            wdt_current_config.wdtMinFunctionsLabel = minFunctionsLabel;
        }
        if( jQuery('#wdt-min-function-label').val() != minFunctionsLabel ){
            jQuery('#wdt-min-function-label').val( minFunctionsLabel );
        }
    },

    setMaxFunctionsLabel: function ( maxFunctionsLabel ) {
        if( wdt_current_config.wdtMaxFunctionsLabel != maxFunctionsLabel ){
            wdt_current_config.wdtMaxFunctionsLabel = maxFunctionsLabel;
        }
        if( jQuery('#wdt-max-function-label').val() != maxFunctionsLabel ){
            jQuery('#wdt-max-function-label').val( maxFunctionsLabel );
        }
    }

};
