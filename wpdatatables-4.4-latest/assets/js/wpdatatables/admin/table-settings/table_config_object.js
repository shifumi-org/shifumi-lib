/**
 * wpDataTable config object
 *
 * Contains all the settings for the table and columns.
 * setter methods adjust the binded jQuery elements
 *
 * @author Alexander Gilmanov
 * @since 15.11.2016
 */
var wpdatatable_config = {
    id: null,
    title: 'New wpDataTable',
    show_title: 1,
    tools: 1,
    responsive: 1,
    responsiveAction: 'icon',
    hide_before_load: 1,
    fixed_layout: 0,
    scrollable: 0,
    verticalScroll: 0,
    sorting: 1,
    word_wrap: 0,
    table_type: '',
    server_side: 1,
    auto_refresh: 0,
    content: '',
    info_block: 1,
    pagination:1,
    paginationAlign: 'right',
    paginationLayout: 'full_numbers',
    simpleResponsive: 0,
    simpleHeader: 0,
    stripeTable: 0,
    cellPadding: 10,
    removeBorders: 0,
    borderCollapse: 'collapse',
    borderSpacing: 0,
    verticalScrollHeight: 600,
    filtering: 1,
    global_search: 1,
    editable: 0,
    popover_tools: 0,
    mysql_table_name: '',
    connection: '',
    edit_only_own_rows: 0,
    editButtonsDisplayed: ["all"],
    enableDuplicateButton: false,
    userid_column_id: null,
    showAllRows: false,
    inline_editing: 0,
    filtering_form: 0,
    clearFilters: 0,
    display_length: 10,
    showRowsPerPage: true,
    id_editing_column: false,
    editor_roles: null,
    table_html: '',
    dataTable: null,
    datatable_config: null,
    tabletools_config: { print: 1, copy: 1, excel: 1, csv: 1, pdf: 0 },
    pdfPaperSize: 'A4',
    pdfPageOrientation: 'portrait',
    showTableToolsIncludeHTML: 0,
    columns: [],
    columns_by_headers: {},
    currentOpenColumn: null,
    var1: '',
    var2: '',
    var3: '',
    var4: '',
    var5: '',
    var6: '',
    var7: '',
    var8: '',
    var9: '',
    currentUserIdPlaceholder: jQuery('#wdt-user-id-placeholder').val(),
    currentUserLoginPlaceholder: jQuery('#wdt-user-login-placeholder').val(),
    currentPostIdPlaceholder: '',
    currentUserFirstNamePlaceholder: jQuery('#wdt-user-first-name-palceholder').val(),
    currentUserLastNamePlaceholder: jQuery('#wdt-user-last-name-palceholder').val(),
    currentUserEmailPlaceholder: jQuery('#wdt-user-email-palceholder').val(),
    currentDatePlaceholder: jQuery('#wdt-date-palceholder').val(),
    currentDateTimePlaceholder: jQuery('#wdt-datetime-palceholder').val(),
    currentTimePlaceholder: jQuery('#wdt-time-palceholder').val(),
    wpdbPlaceholder: jQuery('#wdt-wpdb-placeholder').val(),
    language: '',
    tableSkin: '',
    tableBorderRemoval: 0,
    tableBorderRemovalHeader: 0,
    tableCustomCss: '',
    /**
     * Method to set the data source type - hides all dependent controls
     * @param type mysql, google_spreadsheet, xml, json, serialized, csv, excel
     */
    setTableType: function( type ){
        wpdatatable_config.table_type = type;
        jQuery('#wdt-input-url').val('');
        switch( type ){
            case 'mysql':
                if (wpdatatable_config.content.length > 5 && !jQuery('.placeholders-settings-tab').is(':visible')) {
                    jQuery('.placeholders-settings-tab').animateFadeIn();
                }
                if( jQuery('.wdt-table-settings .mysql-settings-block').hasClass('hidden') ){
                    jQuery('.wdt-table-settings .input-path-block').addClass('hidden');
                    jQuery('.wdt-table-settings .mysql-settings-block').animateFadeIn();
                    jQuery('.wdt-table-settings .wdt-server-side-processing').animateFadeIn();
                }
                break;
            case 'manual':
                wpdatatable_config.setServerSide( 1 );
                jQuery('.wdt-input-data-source-type').hide();
                jQuery('.placeholders-settings-tab').animateFadeIn();
                break;
            case 'csv':
            case 'xls':
            case 'google_spreadsheet':
            case 'xml':
            case 'json':
            case 'serialized':
                jQuery('.placeholders-settings-tab').animateFadeIn();
                jQuery('.wdt-table-settings #wdt-browse-button').removeClass('hidden');
                if( jQuery('.wdt-table-settings .input-path-block').hasClass('hidden') ) {
                    jQuery('.wdt-table-settings .input-path-block').animateFadeIn();
                }
                jQuery('.wdt-table-settings .mysql-settings-block').addClass('hidden');
                jQuery('.wdt-table-settings .wdt-server-side-processing').addClass('hidden');
                jQuery('.wdt-table-settings .wdt-auto-refresh').addClass('hidden');
                wpdatatable_config.setServerSide( 0 );
                if (jQuery.inArray(type, ['google_spreadsheet', 'xml', 'json', 'serialized']) != -1 )
                    jQuery('.wdt-table-settings #wdt-browse-button').addClass('hidden');
                break;
            default:
                jQuery('.wdt-table-settings .input-path-block').addClass('hidden');
                jQuery('.wdt-table-settings .mysql-settings-block').addClass('hidden');
                jQuery('.wdt-table-settings .wdt-server-side-processing').addClass('hidden');
                jQuery('.wdt-table-settings .wdt-auto-refresh').addClass('hidden');
                break;
        }
        jQuery( '#wdt-table-type').val( type ).selectpicker('refresh');
    },
    /**
     * Method to set ID for new tables
     * Shows the label with the shortcode if hiddem
     */
    setId: function( id ){
        wpdatatable_config.id = id;
        jQuery( '#wdt-table-id' ).html(
            ' <a class="wdt-copy-shortcode" data-toggle="tooltip" data-shortcode-type="table" data-placement="top" title="Click to copy shortcode">' +
            '            <i class="wpdt-icon-copy"></i>' +
            '        </a>' +
            '        <span id="wdt-table-shortcode-id">[wpdatatable id='+id+']</span>' );
        if( jQuery( '#wdt-table-id' ).is(':hidden') ){
            jQuery( '#wdt-table-id' ).animateFadeIn();
        }
    },
    /**
     * Method to set the table title
     */
    setTitle: function( title ){
        wpdatatable_config.title = title;
        jQuery( '#wdt-table-title-edit' ).val( title );
    },
    /**
     * Method to enable or disable the server side processing
     * Shows or hides the auto-refresh input
     * @param serverSide 1 or 0
     */
    setServerSide: function( serverSide ){
        wpdatatable_config.server_side = serverSide;
        if( serverSide == 1 ){
            jQuery('.wdt-table-settings .wdt-auto-refresh').animateFadeIn();
            jQuery('.editing-settings-tab').animateFadeIn();
        }else{
            wpdatatable_config.setEditable( 0 );
            jQuery('.wdt-table-settings .wdt-auto-refresh').addClass('hidden');
        }
        jQuery('.wdt-server-side').prop( 'checked', serverSide );
    },
    /**
     * Defines the auto-refresh period
     * @param autoRefresh
     */
    setAutoRefresh: function( autoRefresh ){
        wpdatatable_config.auto_refresh = autoRefresh;
        if( jQuery('#wdt-auto-refresh').val() != wpdatatable_config.auto_refresh ){
            jQuery('#wdt-auto-refresh').val( wpdatatable_config.auto_refresh );
        }
    },
    /**
     * Sets the content of the table
     * @param content string with MySQL query for MySQL-based tables, or path/URL to other types of tables
     */
    setContent: function( content ){
        wpdatatable_config.content = content;
        if( ( content != '' ) && ( content.length > 5 ) ){
            // TODO - validate content
            if( !jQuery('.display-settings-tab').is(':visible') ){
                jQuery('.display-settings-tab').animateFadeIn();
                jQuery('.table-sorting-filtering-settings-tab').animateFadeIn();
                jQuery('.table-tools-settings-tab').animateFadeIn();
                jQuery('.customize-table-settings-tab').animateFadeIn();
            }
            if( wpdatatable_config.table_type == 'mysql' ) {
                if( !jQuery('.placeholders-settings-tab').is(':visible') ){
                    jQuery('.placeholders-settings-tab').animateFadeIn();
                }
                var aceEditor = ace.edit('wdt-mysql-query');
                aceEditor.$blockScrolling = Infinity;
                if( aceEditor.getValue() != content ){
                    aceEditor.setValue( content );
                }
            }else{
                wpdatatable_config.table_type == 'manual' ?
                    !jQuery('.editing-settings-tab').is(':visible') ? jQuery('.editing-settings-tab').animateFadeIn() : null :
                    jQuery('.editing-settings-tab').addClass('hidden');
                jQuery('#wdt-input-url').val( content );
            }
            jQuery('button.wdt-apply').prop( 'disabled', '' );
        }
        else{
            jQuery('.display-settings-tab').addClass('hidden');
            jQuery('.table-sorting-filtering-settings-tab').addClass('hidden');
            jQuery('.table-tools-settings-tab').addClass('hidden');
            jQuery('.editing-settings-tab').addClass('hidden');
            jQuery('.placeholders-settings-tab').addClass('hidden');
            jQuery('button.wdt-apply').prop( 'disabled', 'disabled' );
        }
        if (jQuery.inArray(wpdatatable_config.table_type, ['xml', 'json', 'serialized', 'csv', 'excel', 'google_spreadsheet']) != -1 ){
            if( !jQuery('.placeholders-settings-tab').is(':visible') ){
                jQuery('.placeholders-settings-tab').animateFadeIn();
            }
        }
    },
    /**
     * Set the show / hide title
     * @param show_title 1 or 0
     */
    setShowTitle: function( show_title ){
        wpdatatable_config.show_title = show_title;
        jQuery('#wdt-show-title').prop( 'checked', show_title );
    },
    /**
     * Set the table tools
     * @param show_tabletools 1 or 0
     * @param table_tools
     */
    setShowTableTools: function( show_tabletools, table_tools ){
        wpdatatable_config.tools = show_tabletools;
        if( show_tabletools == 1 ){
            jQuery('.wdt-table-settings .table-tools-settings-block').animateFadeIn();
            jQuery('.wdt-table-settings .table-tools-include-html-block').animateFadeIn();
            jQuery.isEmptyObject( table_tools ) ?
                wpdatatable_config.setTableToolsConfig( { print: 1, copy: 1, excel: 1, csv: 1, pdf: 0 } ) :
                wpdatatable_config.setTableToolsConfig( table_tools );
            wpdatatable_config.setTableToolsIncludeHTML( 0 );
            // Show/hide PDF export options
            if (typeof wpdatatable_config.tabletools_config.pdf !== "undefined" && wpdatatable_config.tabletools_config.pdf == 1) {
                jQuery('div.pdf-export-options').animateFadeIn();
            } else {
                jQuery('div.pdf-export-options').animateFadeOut();
            }
        }else{
            jQuery('.wdt-table-settings .table-tools-settings-block').addClass('hidden');
            jQuery('.wdt-table-settings .table-tools-include-html-block').addClass('hidden');
            jQuery('div.pdf-export-options').animateFadeOut();
            wpdatatable_config.setTableToolsConfig({})
            wpdatatable_config.setTableToolsIncludeHTML( 0 );
            wpdatatable_config.setPdfPaperSize('A4')
            wpdatatable_config.setPdfPageOrientation('portrait')
        }
        jQuery('#wdt-table-tools').prop( 'checked', show_tabletools );
    },
    /**
     * Enable or disable table tools include HTML
     * @param showTableToolsIncludeHTML 1 or 0
     */
    setTableToolsIncludeHTML: function( showTableToolsIncludeHTML ){
        wpdatatable_config.showTableToolsIncludeHTML = showTableToolsIncludeHTML;
        jQuery('#wdt-table-tools-include-html').prop( 'checked', showTableToolsIncludeHTML );
    },
    /**
     * Enable or disable responsiveness
     * @param responsive 1 or 0
     */
    setResponsive: function( responsive ){
        wpdatatable_config.responsive = responsive;
        jQuery('#wdt-responsive').prop('checked', responsive);

        if (responsive === 1) {
            jQuery('.responsive-action-block').removeClass('hidden');
        } else {
            jQuery('.responsive-action-block').addClass('hidden');
            wpdatatable_config.setResponsiveAction('icon');
        }
    },
    /**
     * Set responsive action
     * @param responsiveAction string
     */
    setResponsiveAction: function (responsiveAction) {
        wpdatatable_config.responsiveAction = responsiveAction;
        jQuery('#wdt-responsive-action')
            .val(responsiveAction)
            .selectpicker('refresh');
    },
    /**
     * Enable or disable scrollable feature
     * @param scrollable 1 or 0
     */
    setScrollable: function( scrollable ){
        wpdatatable_config.scrollable = scrollable;
        if ( scrollable == 1 ) {
            wpdatatable_config.setLimitLayout(0);
            jQuery('.limit-table-width-settings-block').addClass('hidden');
        } else {
            !jQuery('.limit-table-width-settings-block').is(':visible') ? jQuery('.limit-table-width-settings-block').animateFadeIn() : null;
        }
        jQuery('#wdt-scrollable').prop( 'checked', scrollable );
    },
    /**
     * Enable or disable vertical scroll feature
     * @param verticalScroll 1 or 0
     */
    setVerticalScroll: function( verticalScroll ){
        wpdatatable_config.verticalScroll = verticalScroll;
        if ( verticalScroll == 1 ){
            jQuery('.vertical-scroll-height-block').animateFadeIn();
        } else {
            wpdatatable_config.setVerticalScrollHeight(600);
            jQuery('.vertical-scroll-height-block').animateFadeOut();
        }
        jQuery('#wdt-vertical-scroll').prop( 'checked', verticalScroll );
    },
    /**
     * Enable or disable hiding before load
     * @param hideBeforeLoad 1 or 0
     */
    setHideBeforeLoad: function( hideBeforeLoad ){
        wpdatatable_config.hide_before_load = hideBeforeLoad;
        jQuery('#wdt-hide-until-loaded').prop( 'checked', hideBeforeLoad );
    },
    /**
     * Enable or disable limit table layout
     * @param limitLayout 1 or 0
     */
    setLimitLayout: function( limitLayout ) {
        wpdatatable_config.fixed_layout = limitLayout;
        if ( limitLayout == 1 ){
            wpdatatable_config.setScrollable(0);
            jQuery('.word-wrap-settings-block').animateFadeIn();
            jQuery('.wdt-scrollable-block').addClass('hidden');
            jQuery('.wdt-column-width-block').show();
        } else {
            wpdatatable_config.setWordWrap(0);
            jQuery('.word-wrap-settings-block').addClass('hidden');
            !jQuery('.wdt-scrollable-block').is(':visible') ? jQuery('.wdt-scrollable-block').animateFadeIn() : null;
            jQuery('.wdt-column-width-block').hide();
        }
        jQuery('#wdt-limit-layout').prop( 'checked', limitLayout );
    },
    /**
     * Enable or disable Word Wrap
     * @param wordWrap 1 or 0
     */
    setWordWrap: function( wordWrap ){
        wpdatatable_config.word_wrap = wordWrap;
        jQuery('#wdt-word-wrap').prop( 'checked', wordWrap );
    },
    /**
     * Enable or disable display length
     * @param displayLength integer - 10, 20, 50, 100, -1 (all)
     */
    setDisplayLength: function( displayLength ){
        wpdatatable_config.display_length = displayLength;
        jQuery('#wdt-rows-per-page')
            .val( displayLength )
            .selectpicker('refresh');
    },
    /**
     * Show or hide "Show X entries" dropdown
     */
    setShowRowsPerPage: function( showRowsPerPage ) {
        wpdatatable_config.showRowsPerPage = showRowsPerPage;
        jQuery('#wdt-show-rows-per-page').prop( 'checked', showRowsPerPage );
    },
    /**
     * Enable or disable the info block
     * @param infoBlock 1 or 0
     */
    setInfoBlock: function( infoBlock ){
        wpdatatable_config.info_block = infoBlock;
        jQuery('#wdt-info-block').prop( 'checked', infoBlock );
    },
    /**
     * Enable or disable pagination
     * @param pagination 1 or 0
     */
    setPagination: function( pagination ){
        wpdatatable_config.pagination = pagination;
        jQuery('#wdt-pagination').prop( 'checked', pagination );
        if ( pagination == 1 ){
            jQuery('.pagination-align-settings-block').removeClass('hidden');
            jQuery('.pagination-layout-settings-block').removeClass('hidden');
        } else {
            wpdatatable_config.setPaginationAlign('right');
            wpdatatable_config.setPaginationLayout('full_numbers');
            jQuery('.pagination-align-settings-block').addClass('hidden');
            jQuery('.pagination-layout-settings-block').addClass('hidden');
        }
    },
    /**
     * Set pagination alignment
     * @param paginationAlign string
     */
    setPaginationAlign: function( paginationAlign ){
        wpdatatable_config.paginationAlign = paginationAlign;
        jQuery('#wdt-pagination-align')
            .val( paginationAlign )
            .selectpicker( 'refresh' );
    },
    /**
     * Set pagination layout
     * @param paginationLayout string
     */
    setPaginationLayout: function( paginationLayout ){
        wpdatatable_config.paginationLayout = paginationLayout;
        jQuery('#wdt-pagination-layout')
            .val( paginationLayout )
            .selectpicker( 'refresh' );
    },
    /**
     * Enable or disable simple responsive
     * @param simpleResponsive 1 or 0
     */
    setSimpleResponsive: function( simpleResponsive ){
        wpdatatable_config.simpleResponsive = simpleResponsive;
        if ( simpleResponsive == 1 ){
            wpdatatable_config.setScrollable(0);
            wpdatatable_config.setLimitLayout(0);
            wpdatatable_config.setWordWrap(0);
            jQuery('.wdt-scrollable-block').addClass('hidden');
            jQuery('.limit-table-width-settings-block').addClass('hidden');
            jQuery('.word-wrap-settings-block').addClass('hidden');
        } else {
            if (wpdatatable_config.scrollable == 1){
                jQuery('.limit-table-width-settings-block').hide();
                jQuery('.word-wrap-settings-block').hide();
                jQuery('.wdt-scrollable-block').show();
            } else if(wpdatatable_config.fixed_layout == 1){
                jQuery('.wdt-scrollable-block').hide();
                jQuery('.limit-table-width-settings-block').show();
                jQuery('.word-wrap-settings-block').show();
            } else {
                jQuery('.wdt-scrollable-block').animateFadeIn();
                jQuery('.limit-table-width-settings-block').animateFadeIn();
            }


        }
        jQuery('#wdt-simple-responsive').prop( 'checked', simpleResponsive );
    },
    /**
     * Enable or disable first row as a header
     * @param simpleHeader 1 or 0
     */
    setSimpleHeader: function( simpleHeader ){
        wpdatatable_config.simpleHeader = simpleHeader;
        jQuery('#wdt-simple-header').prop( 'checked', simpleHeader );
    },
    /**
     * Enable or disable odds and even row classes
     * @param stripeTable 1 or 0
     */
    setStripeTable: function( stripeTable ){
        wpdatatable_config.stripeTable = stripeTable;
        jQuery('#wdt-stripe-table').prop( 'checked', stripeTable );
    },
    /**
     * Set cell padding value
     * @param cellPadding 1 or 0
     */
    setCellPadding: function( cellPadding ){
        wpdatatable_config.cellPadding = cellPadding;
        if( jQuery('#wdt-cell-padding').val() != wpdatatable_config.cellPadding ){
            jQuery('#wdt-cell-padding').val( wpdatatable_config.cellPadding );
        }
    },
    /**
     * Enable or disable borders
     * @param removeBorders 1 or 0
     */
    setRemoveBorders: function( removeBorders ){
        wpdatatable_config.removeBorders = removeBorders;
        jQuery('#wdt-remove-borders').prop( 'checked', removeBorders );
    },
    /**
     * Set the collapsing borders
     * @param borderCollapse
     */
    setBorderCollapse: function( borderCollapse ){
        wpdatatable_config.borderCollapse = borderCollapse;
        jQuery('#wdt-border-collapse').val( borderCollapse ).selectpicker( 'refresh' );
    },
    /**
     * Set border spacing value
     * @param borderSpacing
     */
    setBorderSpacing: function( borderSpacing ){
        wpdatatable_config.borderSpacing = borderSpacing;
        if( jQuery('#wdt-border-spacing').val() !== wpdatatable_config.borderSpacing ){
            jQuery('#wdt-border-spacing').val( wpdatatable_config.borderSpacing );
        }
    },
    /**
     * Set vertical scroll height value
     * @param verticalScrollHeight 1 or 0
     */
    setVerticalScrollHeight: function( verticalScrollHeight ){
        wpdatatable_config.verticalScrollHeight = verticalScrollHeight;
        if( jQuery('#wdt-vertical-scroll-height').val() != wpdatatable_config.verticalScrollHeight ){
            jQuery('#wdt-vertical-scroll-height').val( wpdatatable_config.verticalScrollHeight );
        }
    },
    /**
     * Enable or disable the advanced filtering
     * @param filtering 1 or 0
     */
    setAdvancedFiltering: function( filtering ){
        wpdatatable_config.filtering = filtering;
        if( filtering == 0 ){
            jQuery('.filtering-form-block').addClass('hidden');

            wpdatatable_config.filtering_form = 0;
            wpdatatable_config.clearFilters = 0;
            jQuery('#wdt-filter-in-form').prop( 'checked', 0 );
            jQuery('#wdt-clear-filters').prop( 'checked', 0 );
        }else{
            if (!jQuery('.filtering-form-block').is(':visible')) {
                jQuery('.filtering-form-block').animateFadeIn();
            }
        }
        jQuery('#wdt-advanced-filter').prop( 'checked', filtering );
    },
    /**
     * Enable or disable the filtering form
     * @param filteringForm 1 or 0
     */
    setFilteringForm: function( filteringForm ){
        wpdatatable_config.filtering_form = filteringForm;
        jQuery('#wdt-filter-in-form').prop( 'checked', filteringForm );
    },
    /**
     * Enable or disable the clear filters button
     * @param clearFilters 1 or 0
     */
    setClearFilters: function( clearFilters ){
        wpdatatable_config.clearFilters = clearFilters;
        jQuery('#wdt-clear-filters').prop( 'checked', clearFilters );
    },
    /**
     * Enable or disable sorting
     * @param sorting 1 or 0
     */
    setSorting: function( sorting ){
        wpdatatable_config.sorting = sorting;
        jQuery('#wdt-global-sorting').prop( 'checked', sorting );
    },
    /**
     * Enable or disable Global Search block
     * @param globalSearch 1 or 0
     */
    setGlobalSearch: function( globalSearch ){
        wpdatatable_config.global_search = globalSearch;
        jQuery('#wdt-global-search').prop( 'checked', globalSearch );
    },
    /**
     * Enable or disable Editable for MySQL-based tables
     * Toggles the dependent feature switches
     * @param editable 1 or 0
     */
    setEditable: function( editable ){
        wpdatatable_config.editable = editable;
        if ( wpdatatable_config.editable == 1 && !jQuery('.editing-settings-tab').is(':visible') ) {
            jQuery('.editing-settings-tab').animateFadeIn();
        }

        // Show switch view buttons if table type is 'manual' or it is 'mysql' and editing is enabled
        if ((wpdatatable_config.table_type === 'manual' ||
            (wpdatatable_config.editable === 1 && wpdatatable_config.table_type === 'mysql'))) {
            jQuery('div.wdt-edit-buttons').animateFadeIn();
        } else {
            jQuery('div.wdt-edit-buttons').hide();
        }

        if( editable == 1 ){
            jQuery('.editing-settings-block:not(.hideDuplicateForGF)').animateFadeIn();
            if(wpdatatable_config.table_type === 'gravity') {
                jQuery('.hideDuplicateForGF').hide();
            }
            if( wpdatatable_config.edit_only_own_rows ){
                jQuery('.own-rows-editing-settings-block').animateFadeIn();
                jQuery('.show-all-rows-editing-settings-block').animateFadeIn();
            }

            // Apply selecter and guess the default ID column for editing
            if( !jQuery( '#editing-settings #wdt-id-editing-column' ).val() ){
                var id_headers = ['id','ID','Id','wdt_ID', 'wdt_id'];

                var idColumnDefined = false;
                for( var i in id_headers ){
                    if ( wpdatatable_config.columns_by_headers[ id_headers[i] ] ){
                        wpdatatable_config.setIdEditingColumn( wpdatatable_config.columns_by_headers[ id_headers[i] ].id );
                        idColumnDefined = true;
                        break;
                    }
                }
                if (!idColumnDefined && wpdatatable_config.columns.length > 0) {
                    wpdatatable_config.setIdEditingColumn( wpdatatable_config.columns[0].id );
                }
            }

            // Try to guess MySQL table name for editing
            var mysqlTableName = wpdatatable_config.content;
            mysqlTableName = mysqlTableName.slice( mysqlTableName.toLowerCase().indexOf('from')+5 );
            mysqlTableName = jQuery.trim( mysqlTableName );
            mysqlTableName = mysqlTableName.replace(new RegExp("\n", "g"), ' ');
            mysqlTableName = mysqlTableName.replace(new RegExp("`", "g"), '');
            mysqlTableName.indexOf(' ') != -1
                ? mysqlTableName = mysqlTableName.slice( 0, mysqlTableName.indexOf(' ') ) : null;
            wpdatatable_config.setMySQLTableName( mysqlTableName );

            wpdatatable_config.setServerSide( 1 );
        }else{
            // Reset all editing settings to default
            jQuery('.editing-settings-block').addClass('hidden');
            jQuery('#wdt-popover-tools').prop( 'checked', 0 );
            jQuery('#wdt-inline-editable').prop( 'checked', 0 );
            jQuery('.own-rows-editing-settings-block').addClass('hidden');
            jQuery('#wdt-edit-only-own-rows').prop( 'checked', 0 );
            jQuery('.show-all-rows-editing-settings-block').addClass('hidden');
            jQuery('#wdt-show-all-row').prop( 'checked', 0 );

            wpdatatable_config.popover_tools = 0;
            wpdatatable_config.inline_editing = 0;
            wpdatatable_config.id_editing_column = false;
            wpdatatable_config.editor_roles = '';
            jQuery( '#wdt-editor-roles' )
                .val( '' )
                .selectpicker( 'refresh' );

            wpdatatable_config.editButtonsDisplayed = ["all"];
            jQuery( '#wdt-edit-buttons-displayed' )
                .val( '' )
                .selectpicker( 'refresh' );

            wpdatatable_config.edit_only_own_rows = 0;
            wpdatatable_config.showAllRows = false;
            wpdatatable_config.userid_column_id = null;
            if (wpdatatable_config.table_type != 'manual')
                wpdatatable_config.setMySQLTableName( '' );
        }
        jQuery('#wdt-editable').prop( 'checked', editable );
    },
    /**
     * Enable or disable the popover tools
     * @param popoverTools 1 or 0
     */
    setPopoverTools: function( popoverTools ){
        wpdatatable_config.popover_tools = popoverTools;
        jQuery('#wdt-popover-tools').prop( 'checked', popoverTools );
    },
    /**
     * Enable or disable inline editing
     * @param inlineEditing 1 or 0
     */
    setInlineEditing: function( inlineEditing ){
        wpdatatable_config.inline_editing = inlineEditing;
        jQuery('#wdt-inline-editable').prop( 'checked', inlineEditing );
    },
    /**
     * Define MySQL table for editing
     * @param mysqlTableName
     */
    setMySQLTableName: function( mysqlTableName ){
        wpdatatable_config.mysql_table_name = mysqlTableName;
        if( jQuery('#wdt-mysql-table-name').val() != wpdatatable_config.mysql_table_name ){
            jQuery('#wdt-mysql-table-name').val( wpdatatable_config.mysql_table_name );
        }
        if (wpdatatable_config.table_type === 'manual') {
            jQuery('#wdt-mysql-table-name').prop('disabled', true);
        }
    },
    /**
     * Define the ID column for editing
     * @param idEditingColumn integer
     */
    setIdEditingColumn: function( idEditingColumn ){
        wpdatatable_config.id_editing_column = true;
        jQuery( '#wdt-id-editing-column' )
            .val( idEditingColumn )
            .selectpicker( 'refresh' );

        for( var i in wpdatatable_config.columns ){
            wpdatatable_config.columns[i].id_column = wpdatatable_config.columns[i].id == idEditingColumn ? 1 : 0;
        }
    },
    /**
     * Set the editor roles
     * @param editorRoles comma-separated string
     */
    setEditorRoles: function( editorRoles ){
        wpdatatable_config.editor_roles = editorRoles;
        jQuery( '#wdt-editor-roles')
            .val( editorRoles )
            .selectpicker( 'refresh' );
    },
    /**
     * Set the displayed edit buttons
     * @param editButtonsDisplayed array
     */

    setEditButtonsDisplayed: function( editButtonsDisplayed ){
        wpdatatable_config.editButtonsDisplayed = editButtonsDisplayed;
        jQuery( '#wdt-edit-buttons-displayed')
            .val( editButtonsDisplayed )
            .selectpicker( 'refresh' );
    },
    /**
     * Set option enable duplicate button
     * @param enableDuplicateButton 1 or 0
     */
    setEnableDuplicateButton: function (enableDuplicateButton) {
        wpdatatable_config.enableDuplicateButton = enableDuplicateButton;
        jQuery('#wdt-enable-duplicate-button').prop( 'checked', enableDuplicateButton );
    },
    /**
     * Set option Show all rows
     * @param showAllRows 1 or 0
     */
    setShowAllRows: function( showAllRows ){
        wpdatatable_config.showAllRows = showAllRows;
        jQuery('#wdt-show-all-rows').prop( 'checked', showAllRows );
    },
    /**
     * Enable editing of only own rows for editable tables
     * @param editOwnRows 1 or 0
     */
    setEditOwnRows: function( editOwnRows ){
        wpdatatable_config.edit_only_own_rows = editOwnRows;
        jQuery('#wdt-edit-only-own-rows').prop( 'checked', editOwnRows );
        if( editOwnRows ){
            jQuery('.own-rows-editing-settings-block').animateFadeIn();
            jQuery('.show-all-rows-editing-settings-block').animateFadeIn();
            wpdatatable_config.setShowAllRows( wpdatatable_config.showAllRows);
            if( wpdatatable_config.userid_column_id == null ){
                jQuery('#wdt-user-id-column').selectpicker('refresh');
                wpdatatable_config.setUserIdColumn( wpdatatable_config.columns[0].id );
            } else {
                wpdatatable_config.setUserIdColumn( wpdatatable_config.userid_column_id );
            }
        }else{
            jQuery('.own-rows-editing-settings-block').animateFadeOut();
            wpdatatable_config.userid_column_id = null;
            jQuery('.show-all-rows-editing-settings-block').animateFadeOut();
            wpdatatable_config.showAllRows = false;
        }
    },
    /**
     * Set the user ID column for tables where users can see and edit
     * only their own rows
     * @param userIdColumn
     */
    setUserIdColumn: function( userIdColumn ){
        wpdatatable_config.userid_column_id = parseInt( userIdColumn );
        if( jQuery('#wdt-user-id-column').val() != userIdColumn ){
            jQuery('#wdt-user-id-column').val( userIdColumn ).selectpicker('refresh');
        }
    },
    /**
     * Set the selection for table tools
     * @param tableToolsConfig
     */
    setTableToolsConfig: function( tableToolsConfig ){
        wpdatatable_config.tabletools_config = tableToolsConfig;
        var tabletoolsConfigVal = [];
        for( var i in tableToolsConfig ){
            if (tableToolsConfig[i] == 1)
                tabletoolsConfigVal.push(i);
        }
        if( jQuery('#wdt-table-tools-config').val() != tabletoolsConfigVal ){
            jQuery('#wdt-table-tools-config').val( tabletoolsConfigVal ).selectpicker('refresh');
        }
    },
    setPdfPaperSize: function ( pdfPaperSize ) {
      wpdatatable_config.pdfPaperSize = pdfPaperSize;
      jQuery( '#wdt-pdf-paper-size' )
            .val( pdfPaperSize )
            .selectpicker( 'refresh' );
    },
    setPdfPageOrientation: function ( pdfPageOrientation ) {
        wpdatatable_config.pdfPageOrientation = pdfPageOrientation;
        jQuery( '#wdt-pdf-page-orientation' )
            .val( pdfPageOrientation )
            .selectpicker( 'refresh' );
    },
    /**
     * Set the VAR 1 placeholder value
     */
    setPlaceholderVar1: function( var1 ) {
        wpdatatable_config.var1 = var1;
        if( jQuery('#wdt-var1-placeholder').val() != wpdatatable_config.var1 ){
            jQuery('#wdt-var1-placeholder').val( wpdatatable_config.var1 );
        }
    },
    /**
     * Set the VAR 2 placeholder value
     */
    setPlaceholderVar2: function( var2 ) {
        wpdatatable_config.var2 = var2;
        if( jQuery('#wdt-var2-placeholder').val() != wpdatatable_config.var2 ){
            jQuery('#wdt-var2-placeholder').val( wpdatatable_config.var2 );
        }
    },
    /**
     * Set the VAR 3 placeholder value
     */
    setPlaceholderVar3: function( var3 ) {
        wpdatatable_config.var3 = var3;
        if( jQuery('#wdt-var3-placeholder').val() != wpdatatable_config.var3 ){
            jQuery('#wdt-var3-placeholder').val( wpdatatable_config.var3 );
        }
    },
    /**
     * Set the VAR 4 placeholder value
     */
    setPlaceholderVar4: function (var4) {
        wpdatatable_config.var4 = var4;
        if (jQuery('#wdt-var4-placeholder').val() != wpdatatable_config.var4) {
            jQuery('#wdt-var4-placeholder').val(wpdatatable_config.var4);
        }
    },
    /**
     * Set the VAR 5 placeholder value
     */
    setPlaceholderVar5: function (var5) {
        wpdatatable_config.var5 = var5;
        if (jQuery('#wdt-var5-placeholder').val() != wpdatatable_config.var5) {
            jQuery('#wdt-var5-placeholder').val(wpdatatable_config.var5);
        }
    },
    /**
     * Set the VAR 6 placeholder value
     */
    setPlaceholderVar6: function (var6) {
        wpdatatable_config.var6 = var6;
        if (jQuery('#wdt-var6-placeholder').val() != wpdatatable_config.var6) {
            jQuery('#wdt-var6-placeholder').val(wpdatatable_config.var6);
        }
    },
    /**
     * Set the VAR 7 placeholder value
     */
    setPlaceholderVar7: function (var7) {
        wpdatatable_config.var7 = var7;
        if (jQuery('#wdt-var7-placeholder').val() != wpdatatable_config.var7) {
            jQuery('#wdt-var7-placeholder').val(wpdatatable_config.var7);
        }
    },
    /**
     * Set the VAR 8 placeholder value
     */
    setPlaceholderVar8: function (var8) {
        wpdatatable_config.var8 = var8;
        if (jQuery('#wdt-var8-placeholder').val() != wpdatatable_config.var8) {
            jQuery('#wdt-var8-placeholder').val(wpdatatable_config.var8);
        }
    },
    /**
     * Set the VAR 9 placeholder value
     */
    setPlaceholderVar9: function (var9) {
        wpdatatable_config.var9 = var9;
        if (jQuery('#wdt-var9-placeholder').val() != wpdatatable_config.var9) {
            jQuery('#wdt-var9-placeholder').val(wpdatatable_config.var9);
        }
    },
    /**
     * Set the Current User ID placeholder value
     */
    setPlaceholderCurrentUserId: function( currentUserIdPlaceholder ) {
        wpdatatable_config.currentUserIdPlaceholder = currentUserIdPlaceholder;
        if( jQuery('#wdt-user-id-placeholder').val() != wpdatatable_config.currentUserIdPlaceholder ){
            jQuery('#wdt-user-id-placeholder').val( wpdatatable_config.currentUserIdPlaceholder );
        }
    },
    /**
     * Set the Current User Login placeholder value
     */
    setPlaceholderCurrentUserLogin: function( currentUserLoginPlaceholder ) {
        wpdatatable_config.currentUserLoginPlaceholder = currentUserLoginPlaceholder;
        if( jQuery('#wdt-user-login-placeholder').val() != wpdatatable_config.currentUserLoginPlaceholder ){
            jQuery('#wdt-user-login-placeholder').val( wpdatatable_config.currentUserLoginPlaceholder );
        }
    },
    /**
     * Set the Current Post ID placeholder value
     */
    setPlaceholderCurrentPostId: function( currentPostIdPlaceholder ) {
        wpdatatable_config.currentPostIdPlaceholder = currentPostIdPlaceholder;
        if( jQuery('#wdt-post-id-placeholder').val() != wpdatatable_config.currentPostIdPlaceholder ){
            jQuery('#wdt-post-id-placeholder').val( wpdatatable_config.currentPostIdPlaceholder );
        }
    },
    /**
     * Set the wpdb placeholder value
     */
    setPlaceholderWpdb: function( wpdbPlaceholder ) {
        wpdatatable_config.wpdbPlaceholder = wpdbPlaceholder;
        if( jQuery('#wdt-wpdb-placeholder').val() != wpdatatable_config.wpdbPlaceholder ){
            jQuery('#wdt-wpdb-placeholder').val( wpdatatable_config.wpdbPlaceholder );
        }
    },
    /**
     * Set the Current User First Name placeholder value
     */
    setPlaceholderCurrentUserFirstName: function( currentUserFirstNamePlaceholder ) {
        wpdatatable_config.currentUserFirstNamePlaceholder = currentUserFirstNamePlaceholder;
        if( jQuery('#wdt-user-first-name-placeholder').val() != wpdatatable_config.currentUserFirstNamePlaceholder ){
            jQuery('#wdt-user-first-name-placeholder').val( wpdatatable_config.currentUserFirstNamePlaceholder );
        }
    },
    /**
     * Set the Current User Last Name placeholder value
     */
    setPlaceholderCurrentUserLastName: function( currentUserLastNamePlaceholder ) {
        wpdatatable_config.currentUserLastNamePlaceholder = currentUserLastNamePlaceholder;
        if( jQuery('#wdt-user-last-name-placeholder').val() != wpdatatable_config.currentUserLastNamePlaceholder ){
            jQuery('#wdt-user-last-name-placeholder').val( wpdatatable_config.currentUserLastNamePlaceholder );
        }
    },
    /**
     * Set the Current User Email placeholder value
     */
    setPlaceholderCurrentUserEmail: function( currentUserEmailPlaceholder ) {
        wpdatatable_config.currentUserEmailPlaceholder = currentUserEmailPlaceholder;
        if( jQuery('#wdt-user-email-placeholder').val() != wpdatatable_config.currentUserEmailPlaceholder ){
            jQuery('#wdt-user-email-placeholder').val( wpdatatable_config.currentUserEmailPlaceholder );
        }
    },
    /**
     * Set the Current Date placeholder value
     */
    setPlaceholderCurrentDate: function( currentDatePlaceholder ) {
        wpdatatable_config.currentDatePlaceholder = currentDatePlaceholder;
        if( jQuery('#wdt-date-placeholder').val() != wpdatatable_config.currentDatePlaceholder ){
            jQuery('#wdt-date-placeholder').val( wpdatatable_config.currentDatePlaceholder );
        }
    },
    /**
     * Set the Current DateTime placeholder value
     */
    setPlaceholderCurrentDateTime: function( currentDateTimePlaceholder ) {
        wpdatatable_config.currentDateTimePlaceholder = currentDateTimePlaceholder;
        if( jQuery('#wdt-datetime-placeholder').val() != wpdatatable_config.currentDateTimePlaceholder ){
            jQuery('#wdt-datetime-placeholder').val( wpdatatable_config.currentDateTimePlaceholder );
        }
    },
    /**
     * Set the Current Time placeholder value
     */
    setPlaceholderCurrentTime: function( currentTimePlaceholder ) {
        wpdatatable_config.currentTimePlaceholder = currentTimePlaceholder;
        if( jQuery('#wdt-time-placeholder').val() != wpdatatable_config.currentTimePlaceholder ){
            jQuery('#wdt-time-placeholder').val( wpdatatable_config.currentTimePlaceholder );
        }
    },
    /**
     * Set language for table interface
     */
    setLanguage: function( language ){
        if( wpdatatable_config.language != language ){
            wpdatatable_config.language = language;
        }
        if( jQuery('#wdt-table-interface-language').val() != language ){
            jQuery('#wdt-table-interface-language').selectpicker( 'val', language );
        }
    },
    /**
     * Set skin for table
     */
    setTableSkin: function ( tableSkin ) {
        if( wpdatatable_config.tableSkin != tableSkin ){
            wpdatatable_config.tableSkin = tableSkin;
        }
        if( jQuery('#wdt-table-base-skin').val() != tableSkin ){
            jQuery('#wdt-table-base-skin').selectpicker( 'val', tableSkin );
        }
    },
    /**
     * Set table colors
     */
    setTableFontColorSettings: function( settingName, settingValue ) {
        if( typeof wpdatatable_config.tableFontColorSettings != 'object' ){
            wpdatatable_config.tableFontColorSettings = {};
        }
        if (wpdatatable_config.tableFontColorSettings[settingName] != settingValue) {
            wpdatatable_config.tableFontColorSettings[settingName] = settingValue;
        }
        if (jQuery('input[data-name=' + settingName + '], select[data-name=' + settingName + ']').val() != settingValue) {
            switch (settingName) {
                case "wdtTableBorderInputRadius":
                    jQuery('input[data-name=' + settingName + ']').val( settingValue );
                    break;
                case "wdtTableFont":
                    jQuery('select[data-name=' + settingName + ']').selectpicker( 'val', settingValue );
                    break;
                case "wdtTableFontSize":
                    jQuery('input[data-name=' + settingName + ']').val( settingValue );
                    break;
                default:
                    jQuery('input[data-name=' + settingName + ']').val( settingValue );
                    jQuery('input[data-name=' + settingName + '] + .wpcolorpicker-icon i').css( "background-color",  settingValue );

            }
        }
    },
    /**
     * Set option for removing border of the table
     */
    setTableBorderRemoval: function ( tableBorderRemoval ) {
        wpdatatable_config.tableBorderRemoval = tableBorderRemoval;
        if( jQuery('#wdt-table-remove-borders').val() != tableBorderRemoval ){
            jQuery('#wdt-table-remove-borders').prop( 'checked', tableBorderRemoval );
        }
    },
    /**
     * Set option for removing border of the table header
     */
    setTableBorderRemovalHeader: function ( tableBorderRemovalHeader ) {
        wpdatatable_config.tableBorderRemovalHeader = tableBorderRemovalHeader;
        if( jQuery('#wdt-table-remove-borders-header').val() != tableBorderRemovalHeader ){
            jQuery('#wdt-table-remove-borders-header').prop( 'checked', tableBorderRemovalHeader );
        }
    },
    /**
     * Set custom CSS for table
     */
    setTableCustomCss: function ( tableCustomCss ) {
        if( wpdatatable_config.tableCustomCss != tableCustomCss ){
            wpdatatable_config.tableCustomCss = tableCustomCss;
        }
        if( wpdatatable_config.table_type != 'simple' ) {
            var aceEditorTableCSS = ace.edit('wdt-table-custom-css');
            aceEditorTableCSS.$blockScrolling = Infinity;
            if (aceEditorTableCSS.getValue() != tableCustomCss) {
                aceEditorTableCSS.setValue(tableCustomCss);
            }
        }
        if( jQuery('#wdt-custom-css').val() != tableCustomCss ){
            jQuery('#wdt-custom-css').val( tableCustomCss );
        }
    },
    /**
     * Add a column to the list
     * @param column
     */
    addColumn: function( column ){
        wpdatatable_config.columns.push( column );
        wpdatatable_config.columns_by_headers[column.orig_header] = column;
    },
    /**
     * Define complete column list at once
     * @param columns
     */
    setColumns: function( columns ){
        wpdatatable_config.columns = columns;
    },
    /**
     * Open the properties block for the column with defined index
     * @param columnIndex
     */
    showColumn: function( columnIndex ){
        wpdatatable_config.columns[ columnIndex ].show();
        wpdatatable_config.currentOpenColumn = wpdatatable_config.columns[ columnIndex ];
        jQuery('#wdt-filter-default-value-selectpicker').selectpicker('refresh');
        jQuery('#wdt-editing-default-value-selectpicker').selectpicker('refresh');
    },
    /**
     * Returns the column by given index
     * @param columnIndex
     */
    getColumn: function( columnIndex ){
        return wpdatatable_config.columns[ columnIndex ];
    },
    /**
     * Returns the column by given header (orig_header)
     */
    getColumnByHeader: function( origHeader ){
        return typeof wpdatatable_config.columns_by_headers[origHeader] !== 'undefined' ?
            wpdatatable_config.columns_by_headers[origHeader] : null;
    },
    /**
     * Method to fetch columns of remote tables and insert to the Foreign Key config modal
     */
    getForeignColumns: function( tableId, displayColumn, storeColumn ){
        if( tableId ){
            jQuery('#wdt-configure-foreign-key-modal div.wdt-preload-layer').animateFadeIn();
            jQuery.ajax({
                url: ajaxurl,
                method: 'post',
                dataType: 'json',
                data: {
                    wdtNonce: jQuery('#wdtNonce').val(),
                    action: 'wpdatatables_get_columns_data_by_table_id',
                    table_id: tableId
                },
                success: function( columns ){
                    jQuery('#wdt-foreign-column-display-value').html('');
                    jQuery('#wdt-foreign-column-store-value').html('');
                    for( var i in columns ){
                        var option_str = '<option value="'+columns[i].id+'" data-orignal_header="'+columns[i].orig_header+'">'+columns[i].display_header+'</option>';
                        jQuery('#wdt-foreign-column-display-value').append( option_str );
                        jQuery('#wdt-foreign-column-store-value').append( option_str );
                    }
                    if( typeof displayColumn !== 'undefined' ){
                        jQuery('#wdt-foreign-column-display-value').val( displayColumn );
                    }
                    if( typeof storeColumn !== 'undefined' ){
                        jQuery('#wdt-foreign-column-store-value').val( storeColumn );
                    }
                    if( jQuery('#wdt-column-foreign-table').val() != tableId ){
                        jQuery('#wdt-column-foreign-table').selectpicker( 'val', tableId );
                    }
                    jQuery('#wdt-foreign-column-display-value, #wdt-foreign-column-store-value').selectpicker('refresh');
                    jQuery('#wdt-configure-foreign-key-modal div.wdt-preload-layer').addClass('hidden');
                }
            });
        }
    },
    /**
     * Method to validate config and enable/disable the apply button
     */
    validateConfig: function(){

    },
    /**
     * Returns table config in JSON format
     */
    getJSON: function(){
        var properties = _.difference(_.keys(wpdatatable_config), _.functions(wpdatatable_config));
        var config = {};

        _.map(properties, function (property) {
            config[property] = wpdatatable_config[property];
        });

        config.columns = _.map(wpdatatable_config.columns, function (column) {
            return column.getJSON()
        });

        delete config.columns_by_headers;
        delete config.dataTable;
        delete config.table_html;

        return config;
    },
    /**
     * Initializes the table and columns config from JSON - for save and for edit
     */
    initFromJSON: function( tableJSON ){
        wpdatatable_config.setId( tableJSON.id );
        wpdatatable_config.setTitle( tableJSON.title );
        wpdatatable_config.setTableType( tableJSON.table_type );
        wpdatatable_config.setAutoRefresh( tableJSON.auto_refresh );
        wpdatatable_config.setShowTitle( tableJSON.show_title );
        if( wpdatatable_config.table_type == 'mysql' ){
            wpdatatable_config.setServerSide( tableJSON.server_side );
        }
        wpdatatable_config.setContent( tableJSON.content );
        wpdatatable_config.setDisplayLength( tableJSON.display_length );
        wpdatatable_config.setShowRowsPerPage( tableJSON.showRowsPerPage );
        wpdatatable_config.connection = tableJSON.connection;
        wpdatatable_config.columns = [];
        wpdatatable_config.columns_by_headers = {};
        for( var i in tableJSON.columns ){
            tableJSON.columns[i].parent_table = wpdatatable_config;
            wpdatatable_config.addColumn( new WDTColumn( tableJSON.columns[i] ) );
        }
        wpdatatable_config.fillColumnsBlock();
        wpdatatable_config.setEditable( parseInt( tableJSON.editable ) );
        if (wpdatatable_config.editable || wpdatatable_config.table_type == 'manual') {
            wpdatatable_config.setMySQLTableName( tableJSON.mysql_table_name );
            wpdatatable_config.setEnableDuplicateButton( tableJSON.enableDuplicateButton );
        }
        if( wpdatatable_config.editable ){
            wpdatatable_config.setUserIdColumn( tableJSON.userid_column_id );
            wpdatatable_config.setEditOwnRows( tableJSON.edit_only_own_rows );
            wpdatatable_config.setShowAllRows( tableJSON.showAllRows );
            wpdatatable_config.setEditorRoles( tableJSON.editor_roles );
            wpdatatable_config.setInlineEditing( tableJSON.inline_editing );
            wpdatatable_config.setPopoverTools( tableJSON.popover_tools );
            wpdatatable_config.setEditButtonsDisplayed( tableJSON.editButtonsDisplayed );
        }
        wpdatatable_config.setAdvancedFiltering( parseInt( tableJSON.filtering ) );
        if( wpdatatable_config.filtering ){
            wpdatatable_config.setFilteringForm( parseInt( tableJSON.filtering_form ) );
            wpdatatable_config.setClearFilters( parseInt( tableJSON.clearFilters ) );
        }
        wpdatatable_config.setLimitLayout( parseInt( tableJSON.fixed_layout ) );
        wpdatatable_config.setGlobalSearch( parseInt( tableJSON.global_search ) );
        wpdatatable_config.setHideBeforeLoad( parseInt( tableJSON.hide_before_load ) );
        wpdatatable_config.setInfoBlock( parseInt( tableJSON.info_block ) );
        wpdatatable_config.setPagination( parseInt( tableJSON.pagination ) );
        wpdatatable_config.setPaginationAlign( tableJSON.paginationAlign );
        wpdatatable_config.setPaginationLayout( tableJSON.paginationLayout );
        wpdatatable_config.setSimpleHeader( parseInt( tableJSON.simpleHeader ) );
        wpdatatable_config.setStripeTable( parseInt( tableJSON.stripeTable ) );
        wpdatatable_config.setCellPadding( parseInt( tableJSON.cellPadding ) );
        wpdatatable_config.setRemoveBorders( parseInt( tableJSON.removeBorders ) );
        wpdatatable_config.setBorderCollapse( tableJSON.borderCollapse );
        wpdatatable_config.setBorderSpacing( parseInt( tableJSON.borderSpacing) );
        wpdatatable_config.setVerticalScrollHeight( parseInt( tableJSON.verticalScrollHeight ) );
        wpdatatable_config.setResponsive( parseInt( tableJSON.responsive ) );
        wpdatatable_config.setResponsiveAction( tableJSON.responsiveAction );
        wpdatatable_config.setScrollable( parseInt( tableJSON.scrollable ) );
        wpdatatable_config.setSimpleResponsive( parseInt( tableJSON.simpleResponsive ) );
        wpdatatable_config.setVerticalScroll( parseInt( tableJSON.verticalScroll ) );
        wpdatatable_config.setSorting( parseInt( tableJSON.sorting ) );
        wpdatatable_config.setShowTableTools( parseInt( tableJSON.tools ), tableJSON.tabletools_config );
        wpdatatable_config.setTableToolsIncludeHTML( parseInt( tableJSON.showTableToolsIncludeHTML ) );
        wpdatatable_config.setPdfPaperSize( tableJSON.pdfPaperSize);
        wpdatatable_config.setPdfPageOrientation( tableJSON.pdfPageOrientation);
        wpdatatable_config.setWordWrap( tableJSON.word_wrap );
        wpdatatable_config.setPlaceholderVar1( tableJSON.var1 );
        wpdatatable_config.setPlaceholderVar2( tableJSON.var2 );
        wpdatatable_config.setPlaceholderVar3( tableJSON.var3 );
        wpdatatable_config.setPlaceholderVar4( tableJSON.var4 );
        wpdatatable_config.setPlaceholderVar5( tableJSON.var5 );
        wpdatatable_config.setPlaceholderVar6( tableJSON.var6 );
        wpdatatable_config.setPlaceholderVar7( tableJSON.var7 );
        wpdatatable_config.setPlaceholderVar8( tableJSON.var8 );
        wpdatatable_config.setPlaceholderVar9( tableJSON.var9 );
        wpdatatable_config.setLanguage( tableJSON.language );
        wpdatatable_config.setTableSkin( tableJSON.tableSkin );
        wpdatatable_config.setTableBorderRemoval( tableJSON.tableBorderRemoval );
        wpdatatable_config.setTableBorderRemovalHeader( tableJSON.tableBorderRemovalHeader );
        wpdatatable_config.setTableCustomCss( tableJSON.tableCustomCss );

        for (var value in tableJSON.tableFontColorSettings) {
            wpdatatable_config.setTableFontColorSettings(value, tableJSON.tableFontColorSettings[value]);
        }

    },
    /**
     * Method which draws the "column settings" and "delete formula" buttons in wpDataTable
     * and adds events and logic for these buttons
     */
    drawColumnSettingsButtons: function( $table ){
        jQuery('.wdt-preload-layer').animateFadeOut();
        $table.find('thead tr:eq(0) th.wdtheader').each(function(){
            if (wpdatatable_config.columns[wpdatatable_config.dataTable.column( jQuery(this) ).index()].type == 'formula') {
                var $formulaDeleteButton = jQuery('<button class="btn btn-default pull-right btn-xs wdt-delete-formula-column" data-toggle="tooltip" title="Click to delete formula column"><i class="wpdt-icon-trash"></i></button>');
                $formulaDeleteButton.appendTo(this).click(function(e){
                    var formulaColumn = wpdatatable_config.columns.slice(wpdatatable_config.dataTable.column(jQuery(this).closest('th')).index())[0];
                    for (var i = formulaColumn.pos + 1; i <= wpdatatable_config.columns.length - 1; i++ ) {
                        wpdatatable_config.columns[i].pos = --wpdatatable_config.columns[i].pos;
                    }
                    wpdatatable_config.columns = _.reject(
                        wpdatatable_config.columns,
                        function(el) {
                            return el.orig_header == formulaColumn.orig_header;
                        });
                    jQuery('button.wdt-apply:eq(0)').click();
                });
            }
            var $button = jQuery('<button class="btn btn-default pull-right btn-xs wdt-column-settings" data-toggle="tooltip" title="Click to open column settings"><i class="wpdt-icon-cog"></i></button>');
            $button.appendTo(this).click(function(e){
                e.preventDefault();
                e.stopImmediatePropagation();
                var columnIndex = wpdatatable_config.dataTable.column( jQuery(this).closest('th')).index();
                wpdatatable_config.showColumn( columnIndex );
            });
        });
        $table.find('thead th button[data-toggle="tooltip"]').tooltip();
        jQuery(document).off('click','span.columnTitle button.wdt-column-settings').on('click','span.columnTitle button.wdt-column-settings',function(e){
            e.preventDefault();
            e.stopImmediatePropagation();
            var columnIndex = jQuery(this).closest('li').data('column');
            wpdatatable_config.showColumn( columnIndex );
        });
        jQuery('#wpdatatable-preview-container table').show();
        // Intentionally left commented
        // jQuery('input.number-range-filter').keyup();
        // jQuery('input.date-range-filter').keyup();
        // jQuery('input.datetime-range-filter').keyup();
        // jQuery('input.time-range-filter').keyup();
        wdtHideTooltip();
    },
    /**
     * Sets the string for table HTML
     * @param table_html
     */
    setTableHtml: function( table_html ){
        wpdatatable_config.table_html = table_html;
    },
    /**
     * Sets the JSON object for datatable_config
     * @param datatable_config
     */
    setDataTableConfig: function( datatable_config ){
        wpdatatable_config.datatable_config = datatable_config;
    },
    renderTable: function() {
        if( !jQuery('div.column-settings').is(':visible') ){
            jQuery('div.column-settings').fadeInDown();
        }
        if( wpdatatable_config.dataTable != null ){
            wpdatatable_config.dataTable.destroy();
        }
        if( wpdatatable_config.table_html != '' ){
            jQuery('#wpdatatable-preview-container').html('');
            jQuery('#wpdatatable-preview-container').html( wpdatatable_config.table_html );
        }
        wpdatatable_config.dataTable = wdtRenderDataTable(
            jQuery('#wpdatatable-preview-container table' ),
            wpdatatable_config.datatable_config
        ).api();

        wpdatatable_config.drawColumnSettingsButtons( jQuery('#wpdatatable-preview-container table') );
        jQuery('.wpDataTablesWrapper .dataTables_length .length_menu').selectpicker();
    },
    /**
     * Helper method that fills in the columns in he column popup
     * from the wpdatatable_config.columns array
     */
    fillColumnsBlock: function(){
        jQuery( '#wdt-columns-list-modal div.wdt-columns-container' ).html('');
        jQuery( '#wdt-formula-editor-modal div.formula-columns-container' ).html('');
        jQuery( '#editing-settings #wdt-id-editing-column' ).html('');
        jQuery( '#editing-settings #wdt-user-id-column' ).html('');
        for( var i in wpdatatable_config.columns ){
            wpdatatable_config.columns[i].renderSmallColumnBlock(i);
            if (wpdatatable_config.table_type == 'mysql' || wpdatatable_config.table_type == 'manual') {
                wpdatatable_config.columns[i].populateColumnForEditing();
                wpdatatable_config.columns[i].populateUserIdColumn();
            }
        }

        jQuery('#wdt-id-editing-column').selectpicker( 'refresh' );
        if (wpdatatable_config.id_editing_column == false)
            jQuery('#wdt-id-editing-column').selectpicker('val', '');

        jQuery( '#wdt-user-id-column' ).selectpicker('val', wpdatatable_config.userid_column_id);

        // Apply new tooltips
        jQuery( '#wdt-columns-list-modal [data-toggle="tooltip"]').tooltip();
    },
    /**
     * Helper method to generate a formula name, checking that same name wouldn't already exist in the table
     */
    generateFormulaName: function(){
        var i = 1;
        var nameGenerated = false;
        var name = '';
        while( !nameGenerated ){
            name = 'formula_'+i;
            if( wpdatatable_config.getColumnByHeader( name ) == null ){
                nameGenerated = true;
            }
            i++;
        }
        return name;
    }
};
