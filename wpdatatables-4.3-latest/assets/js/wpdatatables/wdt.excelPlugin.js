/**
 * Created by Milos Roksandic
 */
/**
 * @plugin wpDataTablesExcelPlugin.
 * Note: keep in mind, that Handsontable instance creates one instance of the plugin class.
 *
 * @param hotInstance
 * @constructor
 */
(function($){

    function wpDataTablesExcelPlugin(hotInstance) {

        // Call the BasePlugin constructor.
        Handsontable.plugins.BasePlugin.call(this, hotInstance);

        this._superClass = Handsontable.plugins.BasePlugin;

        this.originals = {};
        this.originals.getCopyableData = hotInstance.getCopyableData;

        // Initialize all your public properties in the class' constructor.
        /**
         * Id of table DOM element(table_{i})
         */
        this.table_id = hotInstance.rootElement.id;

        /**
         * Used in ajax table requests for counting sent request.
         * @type {number}
         */
        this.tableDrawRequests = 0;
        /**
         * Used in ajax table requests for checking received requests order.
         * If order of currently received request is lower than previous request, it should be discarded.
         * @type {number}
         */
        this.tableDrawResponses = 0;

        /**
         * table settings that will be loaded from wpdt description.
         * @type {{}}
         */
        this.tableSettings = {};

        /**
         * Search field element
         * @type {*|HTMLElement}
         */
        this.$SEARCH_FIELD = $('#' +this.table_id + '_search_filter input');

        /**
         * loading previously defined languages.
         * mainly used for number display formatting.
         */
        this.loadLanguages();
    }

    wpDataTablesExcelPlugin.prototype = Object.create(Handsontable.plugins.BasePlugin.prototype, {
        constructor: {
            writable: true,
            configurable: true,
            value: wpDataTablesExcelPlugin
        },
    });

    /**
     * Checks if the plugin is enabled in the settings.
     */
    wpDataTablesExcelPlugin.prototype.isEnabled = function() {
        return !!this.hot.getSettings().wpDataTablesExcelPlugin;
    };

    /**
     * The enablePlugin method is triggered on the beforeInit hook. It should contain your initial plugin setup, along with
     * the hook connections.
     * Note, that this method is run only if the statement in the isEnabled method is true.
     */
    wpDataTablesExcelPlugin.prototype.enablePlugin = function() {
        this.overrideHotFunctions();

        //assign loaded languages to numeral plugin so it can be used by handsontable
        for( var lang in this.languages ) {
            numeral.language(lang, this.languages[lang]);
        }

        var tableDescription = {};

        //if table definition is provided in certain html dom element, load it from there.
        var description_element_id = $(this.hot.rootElement).data('described-by');
        if( description_element_id && ($descriptionElement = $('#' + description_element_id)) ) {
            tableDescription = JSON.parse($descriptionElement.val());
            this.tableSettings = tableDescription.dataTableParams;
            delete tableDescription.dataTableParams;
        }

        for( var prop in tableDescription ) {
            this[prop] = tableDescription[prop];
        }

        this.tableSettings.language = 'en-' + this.tableSettings.number_format;

        for(var i = 0; i< this.tableSettings.columns.length; i++) {
            if( this.tableSettings.columns[i].type == 'wdt.date' ) {
                this.tableSettings.columns[i].sortFunction = this.sortDate;
            }

            if( this.serverSide ) {
                this.setCellValidator(this.tableSettings.columns[i]);
            }
        }

        if( this.serverSide ) {
            //TODO: Add conditions here if data is already set
            this.tableSettings.minSpareRows = 1;

            //adding context menu to table definition
            this.addContextMenuItems();

            //setting custom search function as a default search function
            Handsontable.Search.global.setDefaultCallback( this.wdtExcelSearchCallback );

            //adding hooks
            this.addHook('afterChange', this.onAfterChange.bind(this));
            this.addHook('init', this.onInit.bind(this));
            this.addHook('afterValidate', this.onAfterValidate.bind(this));
        } else {
            this.tableSettings.data = JSON.parse( $(tableDescription.selector + '_data').val() );
            this.addHook('init', this.onInit.bind(this));
        }

        this.addHook('beforeAutofill', this.onBeforeAutofill);
        this.addHook('beforeValidate', this.onBeforeValidate);
        this.addHook('beforeChange', this.onBeforeChange);

        if( typeof wpDtExcelTables[this.table_id] == 'object' ) {
            $.extend( true, this.tableSettings, wpDtExcelTables[this.table_id] );
        }

        this._superClass.prototype.enablePlugin.call(this);
    };

    wpDataTablesExcelPlugin.prototype.overrideHotFunctions = function() {
        var getCopyableDataOriginal = this.originals.getCopyableData;

        this.hot.getCopyableData = function( row, column ) {
            var cellMeta = this.getCellMeta( row, column );
            var value = getCopyableDataOriginal.apply( this, arguments );

            if( cellMeta.type == 'numeric' ) {
                value = numeral( value ).format( cellMeta.format );
            } else if( cellMeta.type == 'wdt.date' ) {
                value = moment( value, cellMeta.dataSourceDateFormat).format(cellMeta.displayDateFormat);
            }

            return value;
        };
    };

    wpDataTablesExcelPlugin.prototype.revertOverriddenHotFunctions = function() {
        this.hot.getCopyableData = this.originals.getCopyableData;
    };

    wpDataTablesExcelPlugin.prototype.initTableSettings = function() {
        this.hot.updateSettings( this.tableSettings );
    };

    /**
     * The disablePlugin method is used to disable the plugin. Reset all of your classes properties to their default values here.
     */
    wpDataTablesExcelPlugin.prototype.disablePlugin = function() {
        this.revertOverriddenHotFunctions();

        this.tableDrawRequests = 0;
        this.tableDrawResponses = 0;
        this.tableSettings = {};

        this.removeEvents();

        this.removeHook('beforeAutofill', this.onBeforeAutofill);
        this.removeHook('beforeValidate', this.onBeforeValidate);
        this.removeHook('beforeChange', this.onBeforeChange);

        // The super class' method takes care of clearing the hook connections and assigning the 'false' value to the 'this.enabled' property.
        this._superClass.prototype.disablePlugin.call(this);
    };

    /**
     * The updatePlugin method is called on the afterUpdateSettings hook (unless the updateSettings method turned the plugin off).
     * It should contain all the stuff your plugin needs to do to work properly after the Handsontable instance settings were modified.
     */
    wpDataTablesExcelPlugin.prototype.updatePlugin = function() {
        // The updatePlugin method needs to contain all the code needed to properly re-enable the plugin. In most cases simply disabling and enabling the plugin should do the trick.
        //this.disablePlugin();
        //this.enablePlugin();

        this._superClass.prototype.updatePlugin.call(this);
    };

    /**
     * The destroy method should de-assign all of your properties.
     */
    wpDataTablesExcelPlugin.prototype.destroy = function() {
        // The super method takes care of de-assigning the event callbacks, plugin hooks and clearing all the plugin properties.
        this._superClass.prototype.destroy.call(this);
    };

    wpDataTablesExcelPlugin.prototype.initEvents = function() {
        var hotElement = this.hot.rootElement;
        if (this.serverSide) {
            $(hotElement).on('click.wpExcelTable', 'a.wdt_link_editable', function (e) {
                e.preventDefault();
                if (e.ctrlKey) {
                    window.open($(this).attr('href'), $(this).attr('target'));
                }
            });

            $(hotElement).on('click.wpExcelTable', 'a.wdt_email_editable', function (e) {
                e.preventDefault();
                if (e.ctrlKey) {
                    location.href = $(this).attr('href');
                }
            });
        }
    };

    /**
     * Initialize DOM events for search DOM element and set up default search value.
     */
    wpDataTablesExcelPlugin.prototype.initSearch = function() {
        var hotInstance = this.hot;
        var hotSettings = hotInstance.getSettings();

        if ( hotSettings.search ) {
            this.$SEARCH_FIELD.on('keyup.wpExcelTable search.wpExcelTable', function (e) {
                hotInstance.search.query(this.value);
                hotInstance.render();
            });

            if( hotSettings.searchDefaultValue ) {
                this.$SEARCH_FIELD.val( hotSettings.searchDefaultValue );
            }
        }
    };

    /**
     * Removes all DOM events related to this plugin handsontable instance
     */
    wpDataTablesExcelPlugin.prototype.removeEvents = function() {
        var hotElement = this.hot.rootElement;
        $(hotElement).off('click.wpExcelTable', '.wpExcelTable a.wdt_link_editable');

        $(hotElement).off('click.wpExcelTable', '.wpExcelTable a.wdt_email_editable');

        this.$SEARCH_FIELD.off('keyup.wpExcelTable search.wpExcelTable');
    };

    /**
     * Returns proper validator object for supplied validators name.
     * As validators are not indexed in handsontable like for example cell types and editors,
     * this function is created to get validator object for it's string nick name.
     * If in supplied name there is a prefix "wdt.", then wdt is added on a beggining of class name,
     * for rest of the name firs letter is capitalized than added suffix Validator.
     * E.g: 'wdt.date' => Handsontable.wdtDateValidator, 'numeric' => Handsontable.NumericValidator
     *
     * @param validator_name
     * @returns {*}
     */
    wpDataTablesExcelPlugin.prototype.getCellValidatorByName = function( validator_name ) {
        var validator = validator_name.replace(/(?:(wdt)\.)?(.+)/, function(match, p1, p2){
            if( !p2 ) {
                return null;
            }

            p1 = p1 || '';

            return p1 + p2.charAt(0).toUpperCase() + p2.slice(1) + 'Validator';
        });

        if( validator && Handsontable[validator] ) {
            return Handsontable[validator];
        } else {
            return null;
        }
    };

    wpDataTablesExcelPlugin.prototype.setCellValidator = function( cell_options ) {
        var cell_validator_name = cell_options.validator;

        if( cell_validator_name ) {
            var cell_validator = this.getCellValidatorByName(cell_validator_name);

            if (cell_validator) {
                //for dropdown and multiselect cell types, supplied validator is added as additional validator in order not to override existing(default) one
                if (cell_options.type == 'dropdown' || cell_options.type == 'wdt.multi-select') {
                    cell_options.cell_validator = cell_validator;
                    delete cell_options.validator;
                } else {
                    cell_options.validator = cell_validator;
                }
            }
        }
    };

    /**
     * Making ajax call for loading data of server side mysql data tables
     */
    wpDataTablesExcelPlugin.prototype.getRemoteTableData = function() {
        var excelPlugin = this;
        var hotInstance = this.hot;
        var tableSettings = hotInstance.getSettings();

        if( !tableSettings.serverSide ) {
            return;
        }

        var ajaxSettings = tableSettings.ajax;
        var colsSettings = tableSettings.columns;
        var table_sorting = tableSettings.columnSorting;
        var orderable = Boolean( table_sorting );
        //var search = tableSettings.search;

        var columns = [];
        for ( var i = 0; i < colsSettings.length; i++ ) {
            var column = {
                data: i,
                name: colsSettings[i].data,
                searchable: colsSettings[i].search,
                orderable: orderable,
                //search: {value: '', regex: false}
            };

            columns.push( column );
        }

        //var search_param = { value: '', regex: false };

        var ajax_params = {
            draw: excelPlugin.tableDrawRequests + 1,
            table: 'excel',
            columns: columns,
            start: 0,
            length: -1,
            wdtNonce: $('#wdtNonceFrontendEdit_' + excelPlugin.tableWpId).val()
            //search: search_param
        };

        if ( orderable ) {
            var columns_order = [];

            var order_column = table_sorting.column;
            var order_direction = ( table_sorting.sortOrder )? 'asc': 'desc';
            var order = {column: order_column, dir: order_direction};

            columns_order.push( order );

            ajax_params.order = columns_order;
        }

        var ajax = {
            data: ajax_params,
            dataType: 'json',
            success: function( response ) {
                if( !response.draw ) {
                    return;
                }

                if( response.draw > excelPlugin.tableDrawResponses ) {
                    excelPlugin.tableDrawResponses = response.draw;
                    hotInstance.loadData( response.data );
                    //hotInstance.render();
                }
            },
            error: function( response ) {
                console.log( response );
            },
            beforeSend: function(jqXHR, settings) {
                excelPlugin.tableDrawRequests++;
                excelPlugin.toggleTableOverlay('show');
            },
            complete: function() {
                excelPlugin.toggleTableOverlay('hide');
            }
        };

        $.extend( ajaxSettings, ajax );

        $.ajax( ajaxSettings );
    };

    /**
     * Loading specially predefined languages for numeral js plugin.
     */
    wpDataTablesExcelPlugin.prototype.loadLanguages = function() {
        var languages = {};
        languages['en'] = {
            delimiters: {
                thousands: ',',
                decimal: '.'
            },
            abbreviations: {
                thousand: 'k',
                million: 'm',
                billion: 'b',
                trillion: 't'
            },
            ordinal: function(number) {
                var b = number % 10;
                return (~~(number % 100 / 10) === 1) ? 'th' : (b === 1) ? 'st' : (b === 2) ? 'nd' : (b === 3) ? 'rd' : 'th';
            },
            currency: {symbol: '$'}
        };


        //we are overriding default only for separators
        languages['en-1'] = $.extend(true, {}, languages['en']);
        languages['en-1'].delimiters.thousands = '.';
        languages['en-1'].delimiters.decimal = ',';

        languages['en-2'] = $.extend(true, {}, languages['en']);

        this.languages = languages;
    };

    /**
     * Used for searching date type columns.
     *
     * @param instance
     * @param row
     * @param col
     * @param data
     */
    wpDataTablesExcelPlugin.prototype.queryDateCell = function(instance, row, col, data) {
        var table_settings = instance.getSettings();
        var searchVal = this.$SEARCH_FIELD.val();
        //in order to search properly date needs to be created in display format first.
        var dateToMatch = moment(data, table_settings.dataSourceDateFormat).format(table_settings.displayDateFormat);
        return Handsontable.Search.DEFAULT_QUERY_METHOD( searchVal, dateToMatch );
    };

    /**
     * Used for searching the table.
     * Checks column types and applies proper way for their search.
     *
     * @param instance
     * @param row
     * @param col
     * @param data
     * @param testResult
     */
    wpDataTablesExcelPlugin.prototype.wdtExcelSearchCallback = function(instance, row, col, data, testResult) {
        var cellMeta = instance.getCellMeta(row, col);

        if( cellMeta.renderer == 'wdt.date' && !testResult ) {
            cellMeta.isSearchResult = instance.getPlugin('WpDataTablesExcelPlugin').queryDateCell( instance, row, col, data );
        } else {
            Handsontable.Search.DEFAULT_CALLBACK.apply(this, arguments);
        }
    };

    /**
     * Creates needed format for sending changed data.
     *
     * @param changes 2D array containing information about each of the edited cells [[row, prop, oldVal, newVal],...
     * @returns {{cells: Array, rows: Array}}
     */
    wpDataTablesExcelPlugin.prototype.prepareDataToSaveRemote = function( changes ) {
        var tableSettings = this.hot.getSettings();
        var id_column_prop = tableSettings.idColumnKey;

        var rows_changed_cells = [], changed_rows = [], changed_rows_data = [], cell_row, cell_prop, cell_old_value, cell_new_value;

        for( var i = 0; i < changes.length; i++ ) {
            cell_row = changes[i][0];
            cell_prop = changes[i][1];
            cell_old_value = changes[i][2];
            cell_new_value = changes[i][3];

            if( cell_old_value != cell_new_value ) {
                if( typeof rows_changed_cells[cell_row] == 'undefined' ) {
                    var id_column_val = this.hot.getDataAtRowProp( cell_row, id_column_prop );

                    var ajax_cell_data = {};
                    ajax_cell_data[id_column_prop] = id_column_val;
                    ajax_cell_data[cell_prop] = cell_new_value;

                    rows_changed_cells[cell_row] = ajax_cell_data;
                } else {
                    rows_changed_cells[cell_row][cell_prop] = cell_new_value;
                }
            }

            //adding changed cells rows data for formula columns calculation
            if( $.inArray( cell_row, changed_rows ) == -1 ) {
                var row_data = this.hot.getSourceDataAtRow( cell_row );
                changed_rows_data.push( row_data );
                changed_rows.push( cell_row );
            }
        }

        //correct array indexes
        if( rows_changed_cells.length > 0 ) {
            var tmp_rows = [];

            for( var row in rows_changed_cells ) {
                tmp_rows.push( rows_changed_cells[row] );
            }

            rows_changed_cells = tmp_rows;
        }

        return { cells: rows_changed_cells, rows: changed_rows_data };
    };

    var preventEventPropagation = function(event) {
        event.stopImmediatePropagation();
        event.preventDefault();
    };

    /**
     * Shows/hides overlay over the table and prevents all js events from excecuting when it is shown.
     * @param action
     */
    wpDataTablesExcelPlugin.prototype.toggleTableOverlay = function( action ) {
        var hotInstance = this.hot;

        if ( action == 'show' ) {
            $(hotInstance.rootElement).find('table.htCore').addClass('overlayed_elm');
            hotInstance.addHook('beforeKeyDown', preventEventPropagation);
        } else if ( action == 'hide' ) {
            $(hotInstance.rootElement).find('table.htCore').removeClass('overlayed_elm');
            hotInstance.removeHook('beforeKeyDown', preventEventPropagation);
        }
    };

    /**
     * Sends ajax request for saving changed table data.
     * @param changes
     */
    wpDataTablesExcelPlugin.prototype.saveChangesRemote = function( changes ) {
        var tableSettings = this.hot.getSettings();
        var wpdatatable_id = this.tableWpId;

        var formatted_changes = this.prepareDataToSaveRemote( changes );
        var changed_cells = formatted_changes.cells;
        var changed_rows = formatted_changes.rows;
        var excelPlugin = this;

        if ( changed_cells.length > 0 ) {
            var ajax_data = {
                action: 'wdt_save_table_cells_frontend',
                wdtNonce: $('#wdtNonceFrontendEdit_' + wpdatatable_id).val(),
                table_id: wpdatatable_id,
                cells: changed_cells,
                rows: changed_rows
            };

            $.ajax({
                url: tableSettings.adminAjaxBaseUrl,
                type: 'POST',
                dataType: 'json',
                data: ajax_data,
                success: function ( return_data ) {
                    excelPlugin.toggleTableOverlay('hide');

                    if( return_data.error.length > 0 || return_data.has_new ) {
                        excelPlugin.getRemoteTableData();
                    }

                    if( return_data.error.length > 0 ) {
                        alert(return_data.error);
                    } else if( return_data.formula_cells ) {
                        //do some callback function call
                        excelPlugin.applyCellsData( return_data.formula_cells, 'updateFormulaValues' );
                    }
                },
                error: function ( return_data ) {
                    excelPlugin.toggleTableOverlay('hide');
                    //redraw
                    alert(return_data.error);
                    excelPlugin.getRemoteTableData();
                },
                beforeSend: function() {
                    excelPlugin.toggleTableOverlay('show');
                }
            });
        }
    };

    /**
     * Applies supplied cell values
     * @param rows Array of objects with properties that correspond to column names and values that should be updated. Object should contain id column in order to values can be applied.
     * @param source
     */
    wpDataTablesExcelPlugin.prototype.applyCellsData = function( rows, source ) {
        if( rows.length == 0 ) {
            return;
        }

        var apply_data = [];
        var htInstance = this.hot;
        var idColProp = htInstance.getSettings().idColumnKey;
        var rowsCount = htInstance.countRows();

        for( var iRow = 0; iRow < rowsCount; iRow++ ) {
            var idColVal = htInstance.getDataAtRowProp( iRow, idColProp );

            for( var i = 0; i < rows.length; i++ ) {
                var row = rows[i];
                if( row[idColProp] == idColVal ) {
                    for( var col in row ) {
                        if( col != idColProp ) {
                            apply_data.push([ iRow, col, row[col] ]);
                        }
                    }
                }
            }
        }

        if( apply_data.length > 0 ) {
            htInstance.setDataAtRowProp( apply_data, source );
        }
    };

    wpDataTablesExcelPlugin.prototype.onAfterChange = function( changes, source ) {
        //if changes of data not caused by loading data, save it to db
        if (source !== 'loadData' && source !== 'updateFormulaValues') {
            this.saveChangesRemote( changes );
        }

        var hotInstance = this.hot;

        //take search value into account
        if ( hotInstance.getSettings().search ) {
            hotInstance.search.query(this.$SEARCH_FIELD.val());
            hotInstance.render();
        }

        //TODO: maybe add reorder as well
    };

    wpDataTablesExcelPlugin.prototype.onInit = function() {
        this.initTableSettings();
        this.initEvents();
        this.initSearch();
        this.getRemoteTableData();
    };

    wpDataTablesExcelPlugin.prototype.onAfterValidate = function( isValid, value, row, prop, source ) {
        var $active_element = $(document.activeElement);
        var $parent_element = void 0;

        if( $active_element.is(':input') ) {
            $parent_element = $active_element.closest('#' + this.hot.rootElement.id +' > div');
        }

        if( !$parent_element ) {
            return;
        }

        if( !$parent_element.next('div.tooltip:visible ').length ) {
            $parent_element.attr('title', '');
            $parent_element.tooltip({
                title: wpdatatables_frontend_strings.invalid_value
            });
        }

        //toggle css class "invalid value" and tooltip for editor
        if ( !isValid ) {
            $parent_element.addClass('invalid_editor_value');
            $parent_element.tooltip('show');
        } else {
            $parent_element.removeClass('invalid_editor_value');
            $parent_element.tooltip('destroy');
        }
    };

    //without this cell value drag would add '0000' for every float number
    wpDataTablesExcelPlugin.prototype.onBeforeAutofill = function( start, end, data ) {
        for ( var r = 0; r < data.length; r++ ) {
            for ( var c = 0; c < data[r].length; c++ ) {
                var columnSettings = this.getCellMeta(start.row + r, start.col + c);

                if (columnSettings.type == 'numeric') {
                    if (numeral.validate(data[r][c])) {
                        data[r][c] = numeral(data[r][c]).format(columnSettings.format);
                    }
                }
            }
        }
    };

    wpDataTablesExcelPlugin.prototype.onBeforeValidate = function (value, row, prop, source) {
        return prepareWdtDateColumnValidate.apply( this, arguments );
    };

    var prepareWdtDateColumnValidate = function (value, row, prop, source){
        var col_index = this.propToCol( prop );
        var col_type = this.getDataType( row, col_index, row, col_index );

        if( col_type == 'wdt.date' ) {
            if(!value) {
                return '';
            }

            if( source != 'paste' ) {
                var tableSettings = this.getSettings();
                //values are converted from database format to desired display format in order to be able to validate. Actual data is not changed, just value for validation.
                value = moment(value, tableSettings.dataSourceDateFormat).format(tableSettings.displayDateFormat);
            }
            return value;
        }
    };

    wpDataTablesExcelPlugin.prototype.onBeforeChange = function( changes, source ) {
        for ( var i =0; i< changes.length; i++ ) {
            var col_index = this.propToCol(changes[i][1]);
            var cellMeta = this.getCellMeta( changes[i][0], col_index );

            if( cellMeta.type == 'wdt.date' && source == 'paste' ) {
                changes[i][3] = moment( changes[i][3], cellMeta.displayDateFormat).format(cellMeta.dataSourceDateFormat);
            }
        }
    };

    /**
     * Adds new empty row.
     * Called from context menu.
     *
     * @param itemKey
     * @param opt
     */
    wpDataTablesExcelPlugin.prototype.addNewTableRow = function( itemKey, opt ) {
        var lastSeletedCol = opt.end.col;
        var emptyRowsBottomCnt = this.countEmptyRows( true );
        var rowsCount = this.countRows();

        //if las row is empty, select corresponding cell in last row
        if( emptyRowsBottomCnt > 0 ) {
            var lastRow = rowsCount - 1;

            this.selectCell( lastRow, lastSeletedCol, lastRow, lastSeletedCol );
        } else {
            var emptyRowsCnt = this.countEmptyRows( false );

            //if there are empty rows in the table, select first one
            if( emptyRowsCnt > 0 ) {
                for( var row = 0; row < rowsCount; row++ ) {
                    if( this.isEmptyRow( row ) ) {
                        this.selectCell( row, lastSeletedCol, row, lastSeletedCol );
                        return;
                    }
                }
            } else {
                //add new empty row to the end of the table
                this.alter('insert_row');
            }
        }
    };

    /**
     * Deletes selected table rows.
     * Calls ajax function for delete rows in mysql data table.
     *
     * @param itemKey
     * @param opt
     */
    wpDataTablesExcelPlugin.prototype.deleteTableRows = function( itemKey, opt ) {
        var startRow = opt.start.row;
        var endRow = opt.end.row;
        var tableSettings = this.getSettings();
        var idColProp = tableSettings.idColumnKey;
        var idColIndex = this.propToCol( idColProp );
        //gets row ids column values for all selected rows
        var deleteRowsData = this.getData(startRow, idColIndex, endRow, idColIndex);
        var deleteRowIds = [];

        for ( var i = 0; i < deleteRowsData.length; i++ ) {
            var rowId = deleteRowsData[i][0];
            if ( rowId ) {
                deleteRowIds.push(deleteRowsData[i][0]);
            }
        }

        if( deleteRowIds.length > 0 ) {
            var excelPlugin = this.getPlugin('wpDataTablesExcelPlugin');
            var wpdatatable_id = excelPlugin.tableWpId;
            var deleteRows = {};
            deleteRows[idColProp] = deleteRowIds;

            var ajax_data = {
                action: 'wdt_delete_table_rows',
                table_id: wpdatatable_id,
                rows: deleteRows,
                wdtNonce: $('#wdtNonceFrontendEdit_' + wpdatatable_id).val()
            };

            $.ajax({
                type: 'POST',
                url: tableSettings.adminAjaxBaseUrl,
                data: ajax_data,
                dataType: 'json',
                success: function( response ){
                    excelPlugin.toggleTableOverlay('hide');

                    if( response.error.length > 0 ) {
                        excelPlugin.getRemoteTableData();
                    } else if( response.success.length > 0 ) {
                        for ( var rowIdx = startRow; rowIdx <= endRow; rowIdx++ ) {
                            this.alter('remove_row', rowIdx);
                        }
                    } else {
                        excelPlugin.getRemoteTableData();
                    }
                },
                error: function( response ) {
                    excelPlugin.toggleTableOverlay('hide');
                    console.log( response );
                    //make sure if error happens we have right data displayed
                    excelPlugin.getRemoteTableData();
                },
                beforeSend: function() {
                    excelPlugin.toggleTableOverlay('show');
                }
            });
        }
    };

    //TODO: Context menu items should be added to already set items if any, not to override them completely. Check this.
    wpDataTablesExcelPlugin.prototype.addContextMenuItems = function() {
        //var excelPlugin = this;
        this.contextMenu = {
            items: {
                'add_row': {
                    name: 'New row',
                    callback: this.addNewTableRow,
                },
                'remove_row': {
                    name: 'Delete row(s)',
                    callback: this.deleteTableRows,
                    disabled: function () {
                        var selectedRange = this.getSelected();
                        //if selected only one row and if it is empty
                        return (selectedRange[0] == selectedRange[2] && this.isEmptyRow(selectedRange[0]));
                    }
                }
            }
        };

        this.tableSettings.contextMenu = this.contextMenu;
    };

    wpDataTablesExcelPlugin.prototype.sortDate = function( sortOrder ) {
        var direction = ( sortOrder )? 1: -1;
        return function(a, b) {
            var date1 = moment(a[1], "YYYY-MM-DD");
            var date2 = moment(b[1], "YYYY-MM-DD");
            var value;

            if( !date1.isValid() && !date2.isValid() ) {
                value = 0;
            } else if( !date1.isValid() ) {
                value = -1;
            } else if( !date2.isValid() ) {
                value = 1;
            } else {
                value = date1.valueOf() - date2.valueOf();
            }

            return direction * value;
        }
    };

    wpDataTablesExcelPlugin.prototype.removeContextMenuItems = function() {
        //TODO: Implement removeContextMenuItems function
        //It should remove previously added context menu items and leave items that are added externally if there are any
        //This method should be called on plugin disabled.
    };

// You need to register your plugin in order to use it within Handsontable.
Handsontable.plugins.registerPlugin('wpDataTablesExcelPlugin', wpDataTablesExcelPlugin);

})(jQuery);
