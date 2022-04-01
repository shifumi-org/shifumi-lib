<?php

defined('ABSPATH') or die('Access denied.');

/**
 * Class WDTConfigController
 *
 * This class contains static methods that bridge front-end for table configuration (add from data source)
 * and the database. Validating, sanitizing and saving column and table settings.
 *
 * @since 2.0
 * @author Alexander Gilmanov
 */
class WDTConfigController {

    private static $_tableConfigCache = array();
    private static $_resetColumnPosition = false;

    /**
     * Validate and save the table config to DB
     * @param StdClass $tableData
     */
    public static function saveTableConfig($tableData) {
        global $wpdb, $wdtVar1, $wdtVar2, $wdtVar3;
        $tableData = self::sanitizeTableConfig($tableData);

        // Fetching the 3 placeholders
        $wdtVar1 = isset($tableData->var1) ?
            sanitize_text_field($tableData->var1) : '';
        $wdtVar2 = isset($tableData->var2) ?
            sanitize_text_field($tableData->var2) : '';
        $wdtVar3 = isset($tableData->var3) ?
            sanitize_text_field($tableData->var3) : '';

        // trying to generate/validate the WPDataTable config
        $res = self::tryCreateTable($tableData->table_type, $tableData->content, $tableData->connection);

        if (empty($res->error)) {
            // If the table can be created by wpDataTables performing the save to DB
            self::saveTableToDB($tableData);
            // If table saved successfully saving the columns as well
            if ($wpdb->last_error == '') {
                if (!isset($tableData->id)) {
                    $tableData->id = $wpdb->insert_id;
                }
                // Saving the columns
                try {
                    self::saveColumns($tableData->columns, $res->table, $tableData->id);

                    $wpDataTable = WPDataTable::loadWpDataTable($tableData->id);
                    $tableData = self::loadTableFromDB($tableData->id, false);

                    if (count($wpDataTable->getDataRows()) > 2000) {
                        $tableData->server_side = 1;
                        $wpdb->update(
                            $wpdb->prefix .'wpdatatables',
                            array(
                                'server_side' => 1,
                            ),
                            array(
                                'id' => $wpDataTable->getWpId()
                            )
                        );
                    }
                    if ($tableData->table_type === 'csv' || $tableData->table_type === 'xls') {
                        $tableData->content = WDTTools::pathToUrl($tableData->content);
                    }
                    $tableData->editor_roles = !empty($tableData->editor_roles) ? explode(",", $tableData->editor_roles) : '';

                    // Return Filter and Editing Default value when Foreign key is set
                    foreach ($tableData->columns as $column) {
                        if (!empty($column->foreignKeyRule) && !($column->filter_type == 'text')) {
                            $column->filterDefaultValue = $wpDataTable->getColumn($column->orig_header)->getFilterDefaultValue();
                            $column->editingDefaultValue = $wpDataTable->getColumn($column->orig_header)->getEditingDefaultValue();
                        }
                    }

                    $res->table = $tableData;
                    $res->wdtJsonConfig = json_decode($wpDataTable->getJsonDescription());
                    $res->wdtHtml = $wpDataTable->generateTable($tableData->connection);

                } catch (Exception $e) {
                    $res->error = ltrim($e->getMessage(), '<br/><br/>');
                }
            } else {
                $res->error = $wpdb->last_error;
            }
        }

        echo json_encode($res);
        exit();
    }

    /**
     * Returns the JSON string with config object for the table and all of its columns
     *
     * @param $tableId - Id of the table
     * @param $tableView - Standard or Excel-like table view
     * @throws Exception
     * @return stdClass Object with the wpDataTable HTML and config
     */
    public static function loadTableConfig($tableId, $tableView = null) {
        $res = new stdClass();

        try {
            $wpDataTable = WPDataTable::loadWpDataTable($tableId, $tableView);
            $tableData = self::loadTableFromDB($tableId);

            if (count($wpDataTable->getDataRows()) > 2000) {
                $tableData->server_side = 1;
            }
            if ($tableData->table_type === 'csv' || $tableData->table_type === 'xls') {
                $tableData->content = WDTTools::pathToUrl($tableData->content);
            }
            $tableData->editor_roles = !empty($tableData->editor_roles) ? explode(',', $tableData->editor_roles) : '';

            // Return Filter and Editing Default value when Foreign key is set
            foreach ($tableData->columns as $column) {
                if (!empty($column->foreignKeyRule)) {
                    $column->filterDefaultValue = $wpDataTable->getColumn($column->orig_header)->getFilterDefaultValue();
                    $column->editingDefaultValue = $wpDataTable->getColumn($column->orig_header)->getEditingDefaultValue();
                }
            }

            $res->table = $tableData;
            $res->wdtJsonConfig = json_decode($wpDataTable->getJsonDescription());
            $res->wdtHtml = $wpDataTable->generateTable($tableData->connection);
        } catch (Exception $e) {
            $res->error = ltrim($e->getMessage(), '<br/><br/>');
        }
        return $res;
    }


    /**
     * Helper method that load table config data from DB
     * @param $tableId
     * @return array|null|bool|object|stdClass
     * @throws Exception
     */
    public static function loadTableFromDB($tableId, $loadFromCache = true) {
        global $wpdb;

        do_action('wpdatatables_before_get_table_metadata', $tableId);

        if (!isset(self::$_tableConfigCache[$tableId]) || $loadFromCache === false) {

            $tableQuery = $wpdb->prepare(
                'SELECT * FROM ' . $wpdb->prefix . 'wpdatatables WHERE id = %d',
                $tableId
            );

            $table = $wpdb->get_row($tableQuery);

            if (!empty($wpdb->last_error)) {
                throw new Exception(
                    __(
                        'There was an error trying to fetch the table data: ',
                        'wpdatatables'
                    ) . $wpdb->last_error
                );
            }

            if (!isset($table)) {
                return false;
            }
            $globalLanguage = get_option('wdtInterfaceLanguage') != '' ? get_option('wdtInterfaceLanguage') : '';
            $advancedSettings = json_decode($table->advanced_settings);

            $table->tabletools_config = unserialize($table->tabletools_config);
            $table->columns = self::getColumnsConfig($tableId);
            $table->info_block = (isset($advancedSettings->info_block)) ? $advancedSettings->info_block : 1;
            $table->showTableToolsIncludeHTML = (isset($advancedSettings->showTableToolsIncludeHTML)) ? $advancedSettings->showTableToolsIncludeHTML : 0;
            $table->pagination = (isset($advancedSettings->pagination)) ? $advancedSettings->pagination : 1;
            $table->paginationAlign = (isset($advancedSettings->paginationAlign)) ? $advancedSettings->paginationAlign : 'right';
            $table->paginationLayout = (isset($advancedSettings->paginationLayout)) ? $advancedSettings->paginationLayout : 'full_numbers';
            $table->global_search = (isset($advancedSettings->global_search)) ? $advancedSettings->global_search : 1;
            $table->showRowsPerPage = (isset($advancedSettings->showRowsPerPage)) ? $advancedSettings->showRowsPerPage : 1;
            $table->showAllRows = (isset($advancedSettings->showAllRows)) ? $advancedSettings->showAllRows : false;
            $table->clearFilters = (isset($advancedSettings->clearFilters)) ? $advancedSettings->clearFilters : 0;
            $table->connection = (isset($table->connection)) ? $table->connection : null;
            $table->simpleHeader = (isset($advancedSettings->simpleHeader)) ? $advancedSettings->simpleHeader : 0;
            $table->simpleResponsive = (isset($advancedSettings->simpleResponsive)) ? $advancedSettings->simpleResponsive : 0;
            $table->stripeTable = (isset($advancedSettings->stripeTable)) ? $advancedSettings->stripeTable : 0;
            $table->cellPadding = (isset($advancedSettings->cellPadding)) ? $advancedSettings->cellPadding : 10;
            $table->removeBorders = (isset($advancedSettings->removeBorders)) ? $advancedSettings->removeBorders : 0;
            $table->borderCollapse = (isset($advancedSettings->borderCollapse)) ? $advancedSettings->borderCollapse : 'collapse';
            $table->borderSpacing = (isset($advancedSettings->borderSpacing)) ? $advancedSettings->borderSpacing : 0;
            $table->verticalScroll = (isset($advancedSettings->verticalScroll)) ? $advancedSettings->verticalScroll : 0;
            $table->verticalScrollHeight = (isset($advancedSettings->verticalScrollHeight)) ? $advancedSettings->verticalScrollHeight : 0;
            $table->editButtonsDisplayed = (isset($advancedSettings->editButtonsDisplayed)) ? $advancedSettings->editButtonsDisplayed : array('all');
            $table->enableDuplicateButton = (isset($advancedSettings->enableDuplicateButton)) ? $advancedSettings->enableDuplicateButton : false;
            $table->language = isset($advancedSettings->language) ? $advancedSettings->language : $globalLanguage;
            $table->tableSkin = isset($table->tableSkin) || isset($advancedSettings->tableSkin)  ? $advancedSettings->tableSkin : get_option('wdtBaseSkin');
            $table->tableBorderRemoval = isset($table->tableBorderRemoval) || isset($advancedSettings->tableBorderRemoval)  ? $advancedSettings->tableBorderRemoval : get_option('wdtBorderRemoval');
            $table->tableBorderRemovalHeader = isset($table->tableBorderRemovalHeader) || isset($advancedSettings->tableBorderRemovalHeader)  ? $advancedSettings->tableBorderRemovalHeader : get_option('wdtBorderRemovalHeader');
            $table->tableCustomCss = isset($table->tableCustomCss) || isset($advancedSettings->tableCustomCss)  ? $advancedSettings->tableCustomCss : '';
            $table->tableFontColorSettings = isset($table->tableFontColorSettings) || isset($advancedSettings->tableFontColorSettings)  ? $advancedSettings->tableFontColorSettings : get_option('wdtFontColorSettings');
            $table->pdfPaperSize = isset($advancedSettings->pdfPaperSize) ? $advancedSettings->pdfPaperSize : 'A4';
            $table->pdfPageOrientation = isset($advancedSettings->pdfPageOrientation) ? $advancedSettings->pdfPageOrientation : 'portrait';

            $table = self::sanitizeTableConfig($table);

            self::$_tableConfigCache[$tableId] = $table;
        }

        self::$_tableConfigCache[$tableId] = apply_filters('wpdatatables_filter_table_metadata', self::$_tableConfigCache[$tableId], $tableId);

        return self::$_tableConfigCache[$tableId];
    }

    /**
     * Helper method that load columns config data from DB
     * @param $tableId
     * @param array $columnNames
     * @return array|null|object
     */
    public static function loadColumnsFromDB($tableId, $columnNames = array()) {
        global $wpdb;

        do_action('wpdatatables_before_get_columns_metadata', $tableId);

        $params[] = $tableId;

        $qWhere = '';
        foreach ($columnNames as $column) {
            if ($qWhere != '') {
                $qWhere .= ', ';
            }
            $qWhere .= '%s';
            $params[] = $column;
        }

        if ($qWhere != '') {
            $qWhere = " AND orig_header IN ( $qWhere )";
        }

        $columnsQuery = $wpdb->prepare(
            'SELECT * FROM ' . $wpdb->prefix . 'wpdatatables_columns
                WHERE table_id = %d ' . $qWhere . '
                ORDER BY pos',
            $params
        );

        $columns = $wpdb->get_results($columnsQuery);
        $columns = apply_filters('wpdatatables_filter_columns_metadata', $columns, $tableId);

        if (!defined('WDT_MD_VERSION')){
            $columnPosition = -1;
            foreach ($columns as $key=>$column){
                if ($column->orig_header == 'masterdetail') {
                    $columnPosition = $column->pos;
                    unset($columns[$key]);
                }
                if ($columnPosition > -1){
                    $column->pos = $column->pos - 1;
                }
            }
        }

        return $columns;
    }


    public static function loadSingleColumnFromDB($columnId) {
        global $wpdb;

        $columnQuery = $wpdb->prepare(
            'SELECT * FROM ' . $wpdb->prefix . 'wpdatatables_columns  WHERE id = %d',
            $columnId
        );

        $column = $wpdb->get_row($columnQuery, ARRAY_A);
        $column = apply_filters('wpdatatables_filter_column_metadata', $column, $columnId);

        return $column;
    }

    /**
     * Helper method that formats table config data in a format for DB
     * and saves to DB
     * @param $table - stdObj with table dada
     */
    public static function saveTableToDB($table) {
        global $wpdb, $wdtVar1, $wdtVar2, $wdtVar3;

        // Fetching the 3 placeholders
        $wdtVar1 = isset($table->var1) ?
            sanitize_text_field($table->var1) : '';
        $wdtVar2 = isset($table->var2) ?
            sanitize_text_field($table->var2) : '';
        $wdtVar3 = isset($table->var3) ?
            sanitize_text_field($table->var3) : '';

        $tableSkin = in_array($table->tableSkin, ['material','light','graphite','aqua','purple','dark']) ? $table->tableSkin : get_option('wdtBaseSkin');

        // Preparing the config
        $tableConfig = array(
            'title' => $table->title,
            'show_title' => $table->show_title,
            'table_type' => $table->table_type,
            'connection' => $table->connection,
            'content' => $table->content,
            'sorting' => $table->sorting,
            'fixed_layout' => $table->fixed_layout,
            'word_wrap' => $table->word_wrap,
            'tools' => $table->tools,
            'display_length' => $table->display_length,
            'hide_before_load' => $table->hide_before_load,
            'tabletools_config' => serialize($table->tabletools_config),
            'filtering' => $table->filtering,
            'filtering_form' => $table->filtering_form,
            'responsive' => $table->responsive,
            'scrollable' => $table->scrollable,
            'server_side' => $table->server_side,
            'auto_refresh' => $table->auto_refresh,
            'editable' => $table->editable,
            'inline_editing' => $table->inline_editing,
            'popover_tools' => $table->popover_tools,
            'editor_roles' => $table->editor_roles,
            'mysql_table_name' => $table->mysql_table_name,
            'edit_only_own_rows' => (int)$table->edit_only_own_rows,
            'userid_column_id' => (int)$table->userid_column_id,
            'var1' => $wdtVar1,
            'var2' => $wdtVar2,
            'var3' => $wdtVar3,
            'advanced_settings' => json_encode(
                array(
                    'info_block' => $table->info_block,
                    'showTableToolsIncludeHTML' => $table->showTableToolsIncludeHTML,
                    'pagination' => $table->pagination,
                    'paginationAlign' => $table->paginationAlign,
                    'paginationLayout' => $table->paginationLayout,
                    'global_search' => $table->global_search,
                    'showRowsPerPage' => $table->showRowsPerPage,
                    'showAllRows' => $table->showAllRows,
                    'clearFilters' => $table->clearFilters,
                    'simpleResponsive' => $table->simpleResponsive,
                    'simpleHeader' => $table->simpleHeader,
                    'stripeTable' => $table->stripeTable,
                    'cellPadding' => $table->cellPadding,
                    'removeBorders' => $table->removeBorders,
                    'borderCollapse' => $table->borderCollapse,
                    'borderSpacing' => $table->borderSpacing,
                    'verticalScroll' => $table->verticalScroll,
                    'verticalScrollHeight' => $table->verticalScrollHeight,
                    'editButtonsDisplayed' => $table->editButtonsDisplayed,
                    'enableDuplicateButton' => $table->enableDuplicateButton,
                    'language' => $table->language,
                    'tableSkin' => $tableSkin,
                    'tableBorderRemoval' => $table->tableBorderRemoval,
                    'tableBorderRemovalHeader' => $table->tableBorderRemovalHeader,
                    'tableCustomCss' => $table->tableCustomCss,
                    'tableFontColorSettings' => $table->tableFontColorSettings,
                    'pdfPaperSize' => $table->pdfPaperSize,
                    'pdfPageOrientation' => $table->pdfPageOrientation,
                )
            )
        );

        $tableConfig = apply_filters('wpdatatables_filter_insert_table_array', $tableConfig);

        if (!$table->id) {
            // It is a new table.
            // Inserting an entry to wp_wpdatatables table
            $wpdb->insert(
                $wpdb->prefix . 'wpdatatables',
                $tableConfig
            );
        } else {
            // It is an existing table.
            // Updating the DB entry
            $wpdb->update(
                $wpdb->prefix . 'wpdatatables',
                $tableConfig,
                array(
                    'id' => $table->id
                )
            );
        }

        do_action('wpdatatables_after_save_table', $table->id);

    }

    /**
     * Helper method for sanitizing the user input in the table config
     * @param stdClass $table object with table config
     * @return stdClass object with sanitized table config
     */
    public static function sanitizeTableConfig($table) {
        if (isset($table->id)) {
            $table->id = (int)$table->id;
        }
        $table->title = sanitize_text_field($table->title);
        $table->show_title = (int)$table->show_title;
        $table->table_type = sanitize_text_field($table->table_type);
        $table->tools = (int)$table->tools;
        $table->showTableToolsIncludeHTML = (int)$table->showTableToolsIncludeHTML;
        $table->responsive = (int)$table->responsive;
        $table->hide_before_load = (int)$table->hide_before_load;
        $table->fixed_layout = (int)$table->fixed_layout;
        $table->scrollable = (int)$table->scrollable;
        $table->verticalScroll = (int)$table->verticalScroll;
        $table->sorting = (int)$table->sorting;
        $table->word_wrap = (int)$table->word_wrap;
        $table->server_side = (int)$table->server_side;
        $table->auto_refresh = (int)$table->auto_refresh;
        $table->info_block = (int)$table->info_block;
        $table->pagination = (int)$table->pagination;
        $table->paginationAlign = sanitize_text_field($table->paginationAlign);
        $table->paginationLayout = sanitize_text_field($table->paginationLayout);
        $table->simpleResponsive = (int)$table->simpleResponsive;
        $table->simpleHeader = (int)$table->simpleHeader;
        $table->stripeTable = (int)$table->stripeTable;
        $table->cellPadding = (int)$table->cellPadding;
        $table->removeBorders = (int)$table->removeBorders;
        $table->borderCollapse = sanitize_text_field($table->borderCollapse);
        $table->borderSpacing = (int)$table->borderSpacing;
        $table->verticalScrollHeight = (int)$table->verticalScrollHeight;
        $table->filtering = (int)$table->filtering;
        $table->global_search = (int)$table->global_search;
        $table->editable = (int)$table->editable;
        $table->popover_tools = (int)$table->popover_tools;
        $table->edit_only_own_rows = (int)$table->edit_only_own_rows;
        $table->inline_editing = (int)$table->inline_editing;
        $table->mysql_table_name = sanitize_text_field($table->mysql_table_name);
        $table->filtering_form = (int)$table->filtering_form;
        $table->clearFilters = (int)$table->clearFilters;
        $table->display_length = (int)$table->display_length;
        $table->showRowsPerPage = (int)$table->showRowsPerPage;
        $table->language = sanitize_text_field($table->language);
        $table->tableSkin = sanitize_text_field($table->tableSkin);
        $table->tableBorderRemoval = (int)$table->tableBorderRemoval;
        $table->tableBorderRemovalHeader = (int)$table->tableBorderRemovalHeader;
        $table->tableCustomCss = sanitize_textarea_field($table->tableCustomCss);
        $table->showAllRows = (int)$table->showAllRows;
        $table->pdfPaperSize = sanitize_text_field($table->pdfPaperSize);
        $table->pdfPageOrientation = sanitize_text_field($table->pdfPageOrientation);
        $table->userid_column_id = $table->userid_column_id != null ?
            (int)$table->userid_column_id : null;

        if(!empty($table->editButtonsDisplayed)){
            $table->editButtonsDisplayed = (array)$table->editButtonsDisplayed;
            foreach ($table->editButtonsDisplayed as &$editButtonsDisplayed) {
                $editButtonsDisplayed = sanitize_text_field($editButtonsDisplayed);
            }
        } else {
            $table->editButtonsDisplayed = array('all');
        }
        $table->enableDuplicateButton = (int)$table->enableDuplicateButton;

        if (!empty($table->editor_roles)) {
            $table->editor_roles = (array)$table->editor_roles;
            foreach ($table->editor_roles as &$editor_roles) {
                $editor_roles = sanitize_text_field($editor_roles);
            }
        } else {
            $table->editor_roles = array();
        }
        $table->editor_roles = implode(",", $table->editor_roles);

        if (!empty($table->tabletools_config)) {
            $table->tabletools_config = (array)$table->tabletools_config;
            foreach ($table->tabletools_config as &$tabletools_config) {
                $tabletools_config = (int)$tabletools_config;
            }
        } else {
            $table->tabletools_config = array();
        }

        if (!empty($table->tableFontColorSettings)) {
            $table->tableFontColorSettings = (array)$table->tableFontColorSettings;
            foreach ($table->tableFontColorSettings as &$tableFontColorSetting) {
                $tableFontColorSetting = sanitize_text_field($tableFontColorSetting);
            }
        } else {
            $table->tableFontColorSettings = array();
        }
        if ($table->table_type != 'simple') {
            $table->columns = WDTConfigController::sanitizeColumnsConfig($table->columns);
        } else {
            $table = self::sanitizeTableSettingsSimpleTable($table);
        }

        if (isset($table->cascadeFiltering) && $table->cascadeFiltering === 1) {
            foreach ($table->columns as &$column) {
                $column->possibleValuesAjax = -1;
            }
        }

        if (($table->table_type == 'csv') || ($table->table_type == 'xls')) {
            $table->content = WDTTools::urlToPath($table->content);
        }

        return $table;

    }

    /**
     * Helper method for sanitizing the user input in the table settings of Simple table
     * @param stdClass $table object with table config
     * @return stdClass object with sanitized table config
     */
    public static function sanitizeTableSettingsSimpleTable($table) {
        $table->method = 'simple';
        $table->connection = '';
        $table->columnCount = 0;
        $table->columns = array();

        if (isset($table->name)){
            $table->name = sanitize_text_field($table->name);
        } else {
            $table->name = '';
        }

        if (isset($table->content)){
            $isContentObj = false;
            if (!is_object($table->content)){
                $isContentObj = true;
                $table->content = json_decode($table->content);
            }
            if (isset($table->content->colNumber)){
                $table->content->colNumber = (int)$table->content->colNumber;
            } else {
                $table->content->colNumber = 5;
            }

            if (isset($table->content->rowNumber)){
                $table->content->rowNumber = (int)$table->content->rowNumber;
            } else {
                $table->content->rowNumber = 5;
            }

            if (isset($table->content->reloadCounter)){
                $table->content->reloadCounter = (int)$table->content->reloadCounter;
            } else {
                $table->content->reloadCounter = 0;
            }

            if (isset($table->content->mergedCells)){
                if (!empty($table->content->mergedCells)){
                    foreach ($table->content->mergedCells as $key => $mergedCell){
                        $table->content->mergedCells[$key]->row = (int)$mergedCell->row;
                        $table->content->mergedCells[$key]->col = (int)$mergedCell->col;
                        $table->content->mergedCells[$key]->rowspan = (int)$mergedCell->rowspan;
                        $table->content->mergedCells[$key]->colspan = (int)$mergedCell->colspan;
                        $table->content->mergedCells[$key]->removed = (bool)$mergedCell->removed;
                    }
                } else {
                    $table->content->mergedCells = array();
                }
            }
            if (isset($table->content->colHeaders)){
                if (!empty($table->content->colHeaders)){
                    foreach ($table->content->colHeaders as $keyColHeader => $colHeader){
                        $table->content->colHeaders[$keyColHeader] = sanitize_text_field($colHeader);
                    }
                } else {
                    $table->content->colHeaders = array();
                }
            }
            if (isset($table->content->colWidths)){
                foreach ($table->content->colWidths as $keyColWidth => $colWidth){
                    $table->content->colWidths[$keyColWidth] = (int)$colWidth;
                }
            }
            if ($isContentObj){
                $table->content = json_encode($table->content);
            }
        }
        return $table;
    }

    /**
     * Helper method for sanitizing the user input for generated SQL based tables data
     * @param array $tableData Array with the tableData coming from SQL based constructor
     * @return array $sanitizedTableData Array with sanitized table data
     */
    public static function sanitizeGeneratedSQLTableData($tableData) {
        $sanitizedTableData = [];

        $sanitizedTableData['name'] = sanitize_text_field($tableData['name']);
        $sanitizedTableData['method'] = sanitize_text_field($tableData['method']);
        $sanitizedTableData['columnCount'] = sanitize_text_field($tableData['columnCount']);
        $sanitizedTableData['connection'] = sanitize_text_field($tableData['connection']);

        if(isset($tableData['handlePostTypes']))
            $sanitizedTableData['handlePostTypes'] = sanitize_text_field($tableData['handlePostTypes']);

        if(isset($tableData['allMySqlColumns']))
            $sanitizedTableData['allMySqlColumns'] = array_map('sanitize_text_field', $tableData['allMySqlColumns']);

        if(isset($tableData['mySqlColumns']))
            $sanitizedTableData['mySqlColumns'] = array_map('sanitize_text_field', $tableData['mySqlColumns']);

        if(isset($tableData['postTypes']))
            $sanitizedTableData['postTypes'] = array_map('sanitize_text_field', $tableData['postTypes']);

        if(isset($tableData['postColumns']))
            $sanitizedTableData['postColumns'] = array_map('sanitize_text_field', $tableData['postColumns']);

        if(isset($tableData['joinRules'])){
            foreach ($tableData['joinRules'] as $ruleKey => $joinRule){
                $sanitizedTableData['joinRules'][$ruleKey] = array_map('sanitize_text_field', $joinRule);
            }
        }

        if(isset($tableData['whereConditions'])){
            foreach ($tableData['whereConditions'] as $whereKey => $whereCondition){
                $sanitizedTableData['whereConditions'][$whereKey] = array_map('sanitize_text_field', $whereCondition);
            }
        }

        if(isset($tableData['groupingRules']))
            $sanitizedTableData['groupingRules'] = array_map('sanitize_text_field', $tableData['groupingRules']);

        return $sanitizedTableData;
    }

    /**
     * Helper method for sanitizing the user input in the row data of Simple table
     */
    public static function sanitizeRowDataSimpleTable($rowsData) {
        $rowsDataSanitized = [];
        foreach ($rowsData as $rowKey => $rowData){
            $rowsDataSanitized[$rowKey] = $rowData;
            foreach ($rowsDataSanitized[$rowKey]->cells as $cellKey => $cell){
                if ($cell->data != '' ){
                    if ( ! current_user_can( 'unfiltered_html' ) ) {
                        $rowsDataSanitized[$rowKey]->cells[$cellKey]->data = wp_kses_post($cell->data);
                    } else {
                        $rowsDataSanitized[$rowKey]->cells[$cellKey]->data = $cell->data;
                    }
                } else {
                    $rowsDataSanitized[$rowKey]->cells[$cellKey]->data = '';
                }
            }
        }

        return $rowsDataSanitized;
    }

    /**
     * Helper method for sanitizing the user input in the table config
     * @param array $columns Array with the columns coming from front-end form
     * @return array $columns Array with sanitized column data
     */
    public static function sanitizeColumnsConfig($columns) {
        if (!empty($columns)) {
            foreach ($columns as &$column) {
                $column->calculateAvg = (int)$column->calculateAvg;
                $column->calculateMax = (int)$column->calculateMax;
                $column->calculateMin = (int)$column->calculateMin;
                $column->calculateTotal = (int)$column->calculateTotal;
                $column->checkboxesInModal = (int)$column->checkboxesInModal;
                $column->color = sanitize_text_field($column->color);
                $column->dateInputFormat = sanitize_text_field($column->dateInputFormat);
                $column->decimalPlaces = isset($column->decimalPlaces) ? (int)$column->decimalPlaces : get_option('wdtDecimalPlaces');
                $column->defaultSortingColumn = (int)$column->defaultSortingColumn;
                $column->display_header = wp_kses_post($column->display_header);
                if (is_object($column->editingDefaultValue)) {
                    $column->editingDefaultValue = sanitize_text_field($column->editingDefaultValue->value);
                } else {
                    $column->editingDefaultValue = sanitize_text_field($column->editingDefaultValue);
                }
                if (is_object($column->filterDefaultValue)) {
                    $column->filterDefaultValue = wp_kses_post($column->filterDefaultValue->value);
                } else {
                    $column->filterDefaultValue = is_array($column->filterDefaultValue) ? array_map('wp_kses_post', $column->filterDefaultValue) : wp_kses_post($column->filterDefaultValue);
                }
                $column->exactFiltering = (int)$column->exactFiltering;
                $column->globalSearchColumn = (int)($column->globalSearchColumn);
                $column->filterLabel = wp_kses_post($column->filterLabel);
                $column->searchInSelectBox = (int)$column->searchInSelectBox;
                $column->searchInSelectBoxEditing = (int)$column->searchInSelectBoxEditing;
                $column->formula = sanitize_text_field($column->formula);
                $column->hide_on_mobiles = (int)$column->hide_on_mobiles;
                $column->hide_on_tablets = (int)$column->hide_on_tablets;
                $column->id = (int)$column->id;
                $column->id_column = (int)$column->id_column;
                $column->orig_header = sanitize_text_field($column->orig_header);
                $column->linkTargetAttribute = sanitize_text_field($column->linkTargetAttribute);
                $column->linkNoFollowAttribute = (int)($column->linkNoFollowAttribute);
                $column->linkNoreferrerAttribute = (int)($column->linkNoreferrerAttribute);
                $column->linkSponsoredAttribute = (int)($column->linkSponsoredAttribute);
                $column->linkButtonAttribute = (int)$column->linkButtonAttribute;
                $column->linkButtonLabel = sanitize_text_field($column->linkButtonLabel);
                $column->linkButtonClass = sanitize_text_field($column->linkButtonClass);
                $column->pos = (int)$column->pos;
                $column->possibleValuesAddEmpty = (int)$column->possibleValuesAddEmpty;
                $column->possibleValuesAjax = (int)$column->possibleValuesAjax;
                $column->possibleValuesType = sanitize_text_field($column->possibleValuesType);
                $column->rangeSlider = (int)$column->rangeSlider;
                $column->skip_thousands_separator = (int)$column->skip_thousands_separator;
                $column->sorting = (int)$column->sorting;
                $column->text_after = sanitize_text_field($column->text_after);
                $column->text_before = sanitize_text_field($column->text_before);
                $column->css_class = sanitize_text_field($column->css_class);
                $column->type = sanitize_text_field($column->type);
                $column->visible = (int)$column->visible;
                $column->width = sanitize_text_field($column->width);
                if (!empty($column->conditional_formatting)){
                    foreach ($column->conditional_formatting as &$cond){
                        $cond->ifClause = sanitize_text_field($cond->ifClause);
                        $cond->action = sanitize_text_field($cond->action);
                        if ( ! current_user_can( 'unfiltered_html' ) ) {
                            $cond->cellVal = wp_kses_post($cond->cellVal);
                            $cond->setVal = wp_kses_post($cond->setVal);
                        }
                    }
                }

                if (isset($column->foreignKeyRule->tableId) && $column->foreignKeyRule->tableId != 0) {
                    $column->foreignKeyRule->tableId = (int)$column->foreignKeyRule->tableId;
                    $column->foreignKeyRule->tableName = sanitize_text_field($column->foreignKeyRule->tableName);
                    $column->foreignKeyRule->displayColumnId = (int)$column->foreignKeyRule->displayColumnId;
                    $column->foreignKeyRule->displayColumnName = sanitize_text_field($column->foreignKeyRule->displayColumnName);
                    $column->foreignKeyRule->storeColumnId = (int)$column->foreignKeyRule->storeColumnId;
                    $column->foreignKeyRule->storeColumnName = sanitize_text_field($column->foreignKeyRule->storeColumnName);
                    $column->foreignKeyRule->allowAllPossibleValuesForeignKey = (int)$column->foreignKeyRule->allowAllPossibleValuesForeignKey;
                }
            }
        }
        return $columns;
    }

    /**
     * Helper method that tries to create a wpDataTable based on the provided content
     * Returns an object which contains a wpDataTable in case of success,
     * or an error message otherwise
     *
     * @param $type - Type of the table (mysql, excel, csv, google spreadsheet, serialized array)
     * @param $content - Content for creating the table (path to source or a MySQL query)
     * @return stdClass Object which has an 'error' property in case there were problems, or a 'table' on success
     */
    public static function tryCreateTable($type, $content, $connection = null) {

        global $wdtVar1, $wdtVar2, $wdtVar3;

        $tbl = new WPDataTable($connection);
        WPDataTable::$wdt_internal_idcount = 0;
        $result = new stdClass();

        do_action('wpdatatables_try_generate_table', $type, $content);

        // Defining the table data for init read
        $tableData = new stdClass();
        $tableData->table_type = $type;
        $tableData->content = $content;
        $tableData->init_read = true;
        $tableData->limit = 10;
        $tableData->var1 = !empty($wdtVar1) ? $wdtVar1 : '';
        $tableData->var2 = !empty($wdtVar2) ? $wdtVar2 : '';
        $tableData->var3 = !empty($wdtVar3) ? $wdtVar3 : '';

        $tableData = apply_filters('wpdatatables_filter_init_table_data', $tableData, $connection);

        // Trying to generate the table and returning
        // an error message in case of thrown exception
        try {
            $tbl->fillFromData($tableData, array());
            if ($tbl->getNoData()) {
                throw new WDTException(__('Table in data source has no rows.', 'wpdatatables'));
            }
            $result->table = $tbl;
        } catch (Exception $e) {
            $result->error = ltrim($e->getMessage(), '<br/><br/>');
            return $result;
        }

        $result = apply_filters('wpdatatables_try_generate_table_result', $result);

        return $result;

    }

    /**
     * Save the columns for the table in DB
     *
     * @param $frontendColumns array of column config objects in front-end format
     * @param $table WPDataTable object that is generated from the data source
     * @param $tableId int ID of the table
     * @throws Exception
     */
    public static function saveColumns($frontendColumns, $table, $tableId) {
        global $wpdb;

        do_action('wpdatatables_before_create_columns', $table, $tableId, $frontendColumns);

        // Get existing columns array
        $existingColumnsQuery = $wpdb->prepare(
            "SELECT orig_header
                FROM " . $wpdb->prefix . "wpdatatables_columns
                WHERE table_id = %d",
            $tableId
        );

        $columnsNotInSource = $wpdb->get_col($existingColumnsQuery);

        $existingColumnsTypesQuery = $wpdb->prepare(
            "SELECT column_type
                FROM " . $wpdb->prefix . "wpdatatables_columns
                WHERE table_id = %d",
            $tableId
        );

        $columnsTypes = $wpdb->get_col($existingColumnsTypesQuery);
        $columnsTypesArray = array_diff(array_combine($columnsNotInSource, $columnsTypes), ['formula']);
        $columnsTypesArray = apply_filters('wpdatatables_columns_types_array', $columnsTypesArray, $columnsNotInSource, $columnsTypes);
        // Getting columns returned by the data source
        $dataSourceColumns = $table->getColumns();

        $dataSourceColumnsHeaders = array_map(function ($column) {
            return $column->getOriginalHeader();
        }, $dataSourceColumns);

        try {
            foreach ($dataSourceColumnsHeaders as $dataSourceColumnsHeader)
                if ($dataSourceColumnsHeader == '') throw new WDTException(__('One or more columns doesn\'t have a header. Please enter headers for all columns in order to proceed.'));
        } catch (WDTException $exception) {
            die($exception);
        }


        self::$_resetColumnPosition = count(array_diff($dataSourceColumnsHeaders, array_keys($columnsTypesArray))) > 0 ||
            count(array_diff(array_keys($columnsTypesArray), $dataSourceColumnsHeaders)) > 0;

        /** @var WDTColumn $column */
        foreach ($dataSourceColumns as $key => &$column) {

            $columnConfig = self::prepareDBColumnConfig($column, $frontendColumns, $tableId, $key);

            // Change column type in database structure, if column type is changes on the frontend
            if ($table->getTableType() == 'manual' && $columnsTypesArray[$column->getOriginalHeader()] != $columnConfig['column_type']) {

                $vendor = Connection::getVendor($table->connection);
                $isMySql = $vendor === Connection::$MYSQL;
                $isMSSql = $vendor === Connection::$MSSQL;
                $isPostgreSql = $vendor === Connection::$POSTGRESQL;

                if ($isMySql) {
                    $columnIntType = 'INT(11)';
                    $columnDateTimeType = 'DATETIME';
                }

                if ($isMSSql) {
                    $columnIntType = 'INT';
                    $columnDateTimeType = 'DATETIME';
                }

                if ($isPostgreSql) {
                    $columnIntType = 'INT';
                    $columnDateTimeType = 'TIMESTAMP';
                }

                switch ($columnConfig['column_type']) {
                    case 'int':
                        $newType = $columnIntType;
                        break;
                    case 'float':
                        $newType = 'DECIMAL(16,4)';
                        break;
                    case 'date':
                        $newType = 'date';
                        break;
                    case 'datetime':
                        $newType = $columnDateTimeType;
                        break;
                    case 'time':
                        $newType = 'time';
                        break;
                    default:
                        $newType = 'VARCHAR(255)';
                }

                $table_config = wdtConfigController::loadTableFromDB($tableId);
                $mysql_table_name = $table_config->mysql_table_name;
                $alterQuery = "ALTER TABLE {$mysql_table_name} MODIFY COLUMN {$columnConfig['orig_header']} {$newType}";
                $alterQueryNull = '';
                if (in_array($newType ,['date','datetime','time'])
                    && !in_array($columnsTypesArray[$column->getOriginalHeader()], ['date','datetime'])){
                    $alterQueryNull= "UPDATE {$mysql_table_name} SET {$columnConfig['orig_header']} = null";
                }

                if ($isMySql) {
                    $alterQuery = "ALTER TABLE {$mysql_table_name} MODIFY COLUMN {$columnConfig['orig_header']} {$newType}";
                }

                if ($isMSSql) {
                    $alterQuery = "ALTER TABLE {$mysql_table_name} MODIFY COLUMN {$columnConfig['orig_header']} {$newType}";
                }

                if ($isPostgreSql) {
                    $alterQuery = "ALTER TABLE {$mysql_table_name} ALTER COLUMN {$columnConfig['orig_header']} SET DATA TYPE {$newType}";
                }

                if (!Connection::isSeparate($table->connection)) {
                    $wpdb->query($alterQuery);
                    if (!empty($alterQueryNull)) {
                        $wpdb->query($alterQueryNull);
                    }
                } else {
                    $sql = Connection::create($table->connection);
                    $sql->doQuery($alterQuery);
                    if ($alterQueryNull) {
                        $sql->doQuery($alterQueryNull);
                    }
                }
            }

            $columnConfig = apply_filters('wpdatatables_filter_column_before_save', $columnConfig, $tableId);

            // Removing this column from the array of marked for deletion
            $columnsNotInSource = array_diff($columnsNotInSource, array($columnConfig['orig_header']));

            self::saveSingleColumn($columnConfig);

        }
        // Go through the Master Details Column and add it
        do_action('wpdatatables_add_and_save_custom_column',$table, $tableId, $frontendColumns );
        // Add columns that are not in source any more
        $columnsNotInSource = apply_filters('wpdatatables_columns_not_in_source',$columnsNotInSource, $table, $tableId, $frontendColumns );
        // Go through the formula columns and add / update them
        if ($frontendColumns != null) {
            foreach ($frontendColumns as $feColumn) {
                // We are only interested in formula columns in this loop
                if ($feColumn->type != 'formula') {
                    continue;
                }

                // Removing this column from the array of marked for deletiong
                $columnsNotInSource = array_diff($columnsNotInSource, array($feColumn->orig_header));

                $wdtColumn = WDTColumn::generateColumn(
                    'formula',
                    array(
                        'orig_header' => $feColumn->orig_header,
                        'decimalPlaces' => $feColumn->decimalPlaces
                    )
                );
                $columnConfig = self::prepareDBColumnConfig($wdtColumn, $frontendColumns, $tableId);
                $columnConfig['filter_type'] = 'none';

                self::saveSingleColumn($columnConfig);

            }
        }

        // Delete columns that are not in source any more
        foreach ($columnsNotInSource as $orig_header) {

            // If column doesn't exist in front-end, or doesn't exist in data source any more we delete it
            $wpdb->delete(
                $wpdb->prefix . "wpdatatables_columns",
                array(
                    'orig_header' => $orig_header,
                    'table_id' => $tableId
                ),
                array(
                    '%s',
                    '%d'
                )
            );

        }

        do_action('wpdatatables_after_save_columns');

    }

    /**
     * Method iterates through the array of column configs received from front-end
     * Tries to find config for the provided column by the key (original header from the data source)
     * Returns the config for a given column on success, FALSE on failure.
     *
     * @param $frontendColumns
     * @param $columnOrigHeader
     * @return bool|StdClass FALSE if column not found, Object with column properties on success
     */
    public static function getFrontEndColumnConfig($frontendColumns, $columnOrigHeader) {
        $result = FALSE;
        if (!empty($frontendColumns)) {
            foreach ($frontendColumns as $feColumn) {
                if ($feColumn->orig_header == $columnOrigHeader) {
                    return $feColumn;
                }
            }
        }
        return $result;
    }

    /**
     * Helper method which prepares the column config object for saving in the DB
     * Merges the data returned by the data source, and config provided in frontend
     *
     * @param WDTColumn $column - wpDataColumn Data for column returned by data source
     * @param $frontendColumns - Array of objects describing config which was sent from front-end
     * @param $tableId - ID of the table
     * @param int $pos - Position of the column in the data source
     * @return array - Array with merged column config
     */
    public static function prepareDBColumnConfig($column, $frontendColumns, $tableId, $pos = 0) {
        $feColumn = self::getFrontEndColumnConfig($frontendColumns, $column->getOriginalHeader());

        // Initializing config array for the column
        $columnConfig = array(
            'calc_formula' => $feColumn ? $feColumn->formula : '',
            'color' => $feColumn ? $feColumn->color : '',
            'column_type' => $feColumn ? $feColumn->type : $column->getDataType(),
            'css_class' => $feColumn ? $feColumn->css_class : '',
            'default_value' => $feColumn ? $feColumn->filterDefaultValue : '',
            'display_header' => $feColumn ? $feColumn->display_header : $column->getTitle(),
            'filter_type' => $feColumn ? $feColumn->filter_type : $column->getFilterType(),
            'formatting_rules' => $feColumn ? json_encode($feColumn->conditional_formatting) : 0,
            'group_column' => $feColumn ? $feColumn->groupColumn : 0,
            'hide_on_phones' => $feColumn ? $feColumn->hide_on_mobiles : 0,
            'hide_on_tablets' => $feColumn ? $feColumn->hide_on_tablets : 0,
            'id_column' => $feColumn ? $feColumn->id_column : 0,
            'input_mandatory' => $feColumn ? $feColumn->editingNonEmpty : 0,
            'input_type' => $feColumn ? $feColumn->editor_type : 'text',
            'orig_header' => $column->getOriginalHeader(),
            'pos' => self::$_resetColumnPosition ? $pos : $feColumn->pos,
            'skip_thousands_separator' => $feColumn ? $feColumn->skip_thousands_separator : 0,
            'sort_column' => $feColumn ? $feColumn->defaultSortingColumn : 0,
            'sum_column' => $feColumn ? $feColumn->calculateTotal : 0,
            'table_id' => $tableId,
            'text_after' => $feColumn ? $feColumn->text_after : '',
            'text_before' => $feColumn ? $feColumn->text_before : '',
            'visible' => $feColumn ? $feColumn->visible : 1,
            'width' => $feColumn ? $feColumn->width : ''
        );

        // Add ID if provided
        if (isset($feColumn->id)) {
            $columnConfig['id'] = $feColumn->id;
        }
        if (isset($feColumn->defaultSortingColumn)) {
            $columnConfig['sort_column'] = $feColumn->defaultSortingColumn;
        }

        // 2.0+ version settings all go to single JSON-encoded DB table column
        $columnConfig['advanced_settings'] = array();

        $columnConfig['advanced_settings']['decimalPlaces'] =
            $feColumn ? $feColumn->decimalPlaces : -1;
        $columnConfig['advanced_settings']['possibleValuesAddEmpty'] =
            $feColumn ? $feColumn->possibleValuesAddEmpty : 0;
        $columnConfig['advanced_settings']['possibleValuesAjax'] =
            $feColumn ? $feColumn->possibleValuesAjax : 10;
        $columnConfig['advanced_settings']['calculateAvg'] =
            $feColumn ? $feColumn->calculateAvg : 0;
        $columnConfig['advanced_settings']['calculateMax'] =
            $feColumn ? $feColumn->calculateMax : 0;
        $columnConfig['advanced_settings']['calculateMin'] =
            $feColumn ? $feColumn->calculateMin : 0;
        $columnConfig['advanced_settings']['sorting'] =
            $feColumn ? $feColumn->sorting : 1;
        $columnConfig['advanced_settings']['exactFiltering'] =
            $feColumn ? $feColumn->exactFiltering : 0;
        $columnConfig['advanced_settings']['globalSearchColumn'] =
            $feColumn ? $feColumn->globalSearchColumn : 1;
        $columnConfig['advanced_settings']['filterLabel'] =
            $feColumn ? $feColumn->filterLabel : null;
        $columnConfig['advanced_settings']['searchInSelectBox'] =
            $feColumn ? $feColumn->searchInSelectBox : 1;
        $columnConfig['advanced_settings']['searchInSelectBoxEditing'] =
            $feColumn ? $feColumn->searchInSelectBoxEditing : 1;
        $columnConfig['advanced_settings']['checkboxesInModal'] =
            $feColumn ? $feColumn->checkboxesInModal : null;
        $columnConfig['advanced_settings']['editingDefaultValue'] =
            $feColumn ? $feColumn->editingDefaultValue : null;
        $columnConfig['advanced_settings']['dateInputFormat'] =
            $feColumn ? $feColumn->dateInputFormat : '';
        $columnConfig['advanced_settings']['linkTargetAttribute'] =
            $feColumn ? $feColumn->linkTargetAttribute : '';
        $columnConfig['advanced_settings']['linkNoFollowAttribute'] =
            $feColumn ? $feColumn->linkNoFollowAttribute : 0;
        $columnConfig['advanced_settings']['linkNoreferrerAttribute'] =
            $feColumn ? $feColumn->linkNoreferrerAttribute : 0;
        $columnConfig['advanced_settings']['linkSponsoredAttribute'] =
            $feColumn ? $feColumn->linkSponsoredAttribute : 0;
        $columnConfig['advanced_settings']['linkButtonAttribute'] =
            $feColumn ? $feColumn->linkButtonAttribute : 0;
        $columnConfig['advanced_settings']['linkButtonLabel'] =
            $feColumn ? $feColumn->linkButtonLabel : null;
        $columnConfig['advanced_settings']['linkButtonClass'] =
            $feColumn ? $feColumn->linkButtonClass : null;
        $columnConfig['advanced_settings']['rangeSlider'] =
            $feColumn ? $feColumn->rangeSlider : 0;

        // Possible values
        $columnConfig['possible_values'] = '';
        if (isset($feColumn->possibleValuesType)) {
            $columnConfig['advanced_settings']['possibleValuesType'] = $feColumn->possibleValuesType;
            if ($feColumn->possibleValuesType == 'list') {
                $columnConfig['possible_values'] = $feColumn->valuesList;
                if ($feColumn->valuesList == null) {
                    $columnConfig['advanced_settings']['possibleValuesType'] = 'read';
                }
            } elseif ($feColumn->possibleValuesType == 'foreignkey') {
                $columnConfig['possible_values'] = '';
                $feColumn->foreignKeyRule->tableId != 0 ?
                    $columnConfig['advanced_settings']['foreignKeyRule'] = $feColumn->foreignKeyRule :
                    $columnConfig['advanced_settings']['possibleValuesType'] = 'read';
            }
        } else {
            $columnConfig['possible_values'] = '';
            $columnConfig['advanced_settings']['possibleValuesType'] = 'read';
        }

        // JSON-encoding all the 2.0+ settings
        $columnConfig['advanced_settings'] = json_encode($columnConfig['advanced_settings']);

        $columnConfig = apply_filters('wpdt_filter_column_config_object', $columnConfig, $feColumn);
        //[<--/ Full version -->]//

        return $columnConfig;
    }

    /**
     * Tries to save (insert or update) a column with the provided config to the database
     * Throws exception on error with DB error message
     * Otherwise returns true
     *
     * @param stdClass $columnConfig Configuration for the column
     * @return bool True in case column saved successfully
     * @throws Exception
     */
    public static function saveSingleColumn($columnConfig) {
        global $wpdb;

        if (!empty($columnConfig['id'])) {

            $columnConfig = apply_filters('wpdatatables_filter_update_column_array', $columnConfig, $columnConfig['table_id']);

            $columnId = $columnConfig['id'];
            unset($columnConfig['id']);

            $wpdb->update(
                $wpdb->prefix . 'wpdatatables_columns',
                $columnConfig,
                array(
                    'id' => $columnId
                ),
                array(),
                array(
                    '%d'
                )
            );

        } else {

            $columnConfig = apply_filters('wpdatatables_filter_insert_column_array', $columnConfig, $columnConfig['table_id']);

            $wpdb->insert(
                $wpdb->prefix . 'wpdatatables_columns',
                $columnConfig
            );

            $columnConfig['id'] = $wpdb->insert_id;
        }

        if ($wpdb->last_error !== '') {
            throw new Exception($wpdb->last_error);
        } else {
            do_action('wpdatatables_after_insert_column', $columnConfig, $columnConfig['table_id']);
            return true;
        }

    }

    /**
     * Method which returns an array of column config objects for front-end
     * @param $tableId
     * @return array Array of column config objects
     */
    public static function getColumnsConfig($tableId) {

        $dbColumns = self::loadColumnsFromDB($tableId);

        $feColumns = array();

        if (!empty($dbColumns)) {
            foreach ($dbColumns as $dbColumn) {
                $feColumns[] = self::prepareFEColumnConfig($dbColumn);
            }
        }

        return $feColumns;
    }

    /**
     * Method which prepares a column description object to be returned to frontend JSON
     * @param $dbColumn - Array with the column config from DB
     * @return stdClass A class describing the column config for front-end
     */
    public static function prepareFEColumnConfig($dbColumn) {
        $feColumn = new stdClass();

        $feColumn->calculateTotal = (int)$dbColumn->sum_column;
        $feColumn->color = $dbColumn->color;
        $feColumn->conditional_formatting = json_decode($dbColumn->formatting_rules);
        $feColumn->css_class = $dbColumn->css_class;
        $feColumn->defaultSortingColumn = (int)$dbColumn->sort_column;
        $feColumn->display_header = $dbColumn->display_header;
        $feColumn->editor_type = $dbColumn->input_type;
        $feColumn->filter_type = $dbColumn->filter_type;
        $feColumn->filterDefaultValue = $dbColumn->default_value;
        $feColumn->formula = $dbColumn->calc_formula;
        $feColumn->groupColumn = (int)$dbColumn->group_column;
        $feColumn->hide_on_mobiles = (int)$dbColumn->hide_on_phones;
        $feColumn->hide_on_tablets = (int)$dbColumn->hide_on_tablets;
        $feColumn->id = (int)$dbColumn->id;
        $feColumn->id_column = (int)$dbColumn->id_column;
        $feColumn->input_mandatory = (int)$dbColumn->input_mandatory;
        $feColumn->orig_header = $dbColumn->orig_header;
        $feColumn->pos = (int)$dbColumn->pos;
        $feColumn->skip_thousands_separator = (int)$dbColumn->skip_thousands_separator;
        $feColumn->text_after = $dbColumn->text_after;
        $feColumn->text_before = $dbColumn->text_before;
        $feColumn->type = $dbColumn->column_type;
        $feColumn->valuesList = $dbColumn->possible_values;
        $feColumn->visible = (int)$dbColumn->visible;
        $feColumn->width = $dbColumn->width;

        $advancedSettings = json_decode($dbColumn->advanced_settings);
        $feColumn->decimalPlaces = isset($advancedSettings->decimalPlaces) ?
            $advancedSettings->decimalPlaces : -1;
        $feColumn->possibleValuesAddEmpty = isset($advancedSettings->possibleValuesAddEmpty) ?
            $advancedSettings->possibleValuesAddEmpty : 0;
        $feColumn->possibleValuesAjax = isset($advancedSettings->possibleValuesAjax) ?
            $advancedSettings->possibleValuesAjax : 0;
        $feColumn->calculateAvg = isset($advancedSettings->calculateAvg) ?
            $advancedSettings->calculateAvg : 0;
        $feColumn->calculateMax = isset($advancedSettings->calculateMax) ?
            $advancedSettings->calculateMax : 0;
        $feColumn->calculateMin = isset($advancedSettings->calculateMin) ?
            $advancedSettings->calculateMin : 0;
        $feColumn->sorting = isset($advancedSettings->sorting) ?
            $advancedSettings->sorting : 1;
        $feColumn->exactFiltering = isset($advancedSettings->exactFiltering) ?
            $advancedSettings->exactFiltering : 0;
        $feColumn->filterLabel = isset($advancedSettings->filterLabel) ?
            $advancedSettings->filterLabel : null;
        $feColumn->searchInSelectBox = isset($advancedSettings->searchInSelectBox) ?
            $advancedSettings->searchInSelectBox : 1;
        $feColumn->searchInSelectBoxEditing = isset($advancedSettings->searchInSelectBoxEditing) ?
            $advancedSettings->searchInSelectBoxEditing : 1;
        $feColumn->checkboxesInModal = isset($advancedSettings->checkboxesInModal) ?
            $advancedSettings->checkboxesInModal : 0;
        $feColumn->possibleValuesType = isset($advancedSettings->possibleValuesType) ?
            $advancedSettings->possibleValuesType : 'read';
        $feColumn->editingDefaultValue = isset($advancedSettings->editingDefaultValue) ?
            $advancedSettings->editingDefaultValue : null;
        $feColumn->dateInputFormat = isset($advancedSettings->dateInputFormat) ?
            $advancedSettings->dateInputFormat : '';
        $feColumn->linkTargetAttribute = isset($advancedSettings->linkTargetAttribute) ?
            $advancedSettings->linkTargetAttribute : '';
        $feColumn->linkNoFollowAttribute = isset($advancedSettings->linkNoFollowAttribute) ?
            $advancedSettings->linkNoFollowAttribute : 0;
        $feColumn->linkNoreferrerAttribute = isset($advancedSettings->linkNoreferrerAttribute) ?
            $advancedSettings->linkNoreferrerAttribute : 0;
        $feColumn->linkSponsoredAttribute = isset($advancedSettings->linkSponsoredAttribute) ?
            $advancedSettings->linkSponsoredAttribute : 0;
        $feColumn->linkButtonAttribute = isset($advancedSettings->linkButtonAttribute) ?
            $advancedSettings->linkButtonAttribute : 0;
        $feColumn->globalSearchColumn = isset($advancedSettings->globalSearchColumn) ?
            $advancedSettings->globalSearchColumn : 1;
        $feColumn->linkButtonLabel = isset($advancedSettings->linkButtonLabel) ?
            $advancedSettings->linkButtonLabel : null;
        $feColumn->linkButtonClass = isset($advancedSettings->linkButtonClass) ?
            $advancedSettings->linkButtonClass : null;
        $feColumn->rangeSlider = isset($advancedSettings->rangeSlider) ?
            $advancedSettings->rangeSlider : 0;


        if ($feColumn->possibleValuesType === 'foreignkey') {
            if (!isset($feColumn->foreignKeyRule)) {
                $feColumn->foreignKeyRule = new stdClass();
            }
            $feColumn->foreignKeyRule->tableId = $advancedSettings->foreignKeyRule->tableId;
            $feColumn->foreignKeyRule->tableName = $advancedSettings->foreignKeyRule->tableName;
            $feColumn->foreignKeyRule->displayColumnId = $advancedSettings->foreignKeyRule->displayColumnId;
            $feColumn->foreignKeyRule->displayColumnName = $advancedSettings->foreignKeyRule->displayColumnName;
            $feColumn->foreignKeyRule->storeColumnId = $advancedSettings->foreignKeyRule->storeColumnId;
            $feColumn->foreignKeyRule->storeColumnName = $advancedSettings->foreignKeyRule->storeColumnName;
            $feColumn->foreignKeyRule->allowAllPossibleValuesForeignKey =  $advancedSettings->foreignKeyRule->allowAllPossibleValuesForeignKey;
        }

        $feColumn = apply_filters('wpdt_filter_column_description_object', $feColumn, $dbColumn, $advancedSettings );

        return $feColumn;

    }

    /**
     * Helper method returning default settings for table object
     * @return stdClass with default settings for the table object
     * // TODO - allow changing/saving default settings from GUI
     */
    public static function getConfigDefaults() {
        $table = new \stdClass();
        $table->id = null;
        $table->title = '';
        $table->show_title = 0;
        $table->table_type = '';
        $table->tools = 1;
        $table->showTableToolsIncludeHTML = 0;
        $table->responsive = 0;
        $table->hide_before_load = 1;
        $table->fixed_layout = 0;
        $table->scrollable = 0;
        $table->verticalScroll = 0;
        $table->sorting = 1;
        $table->word_wrap = 0;
        $table->server_side = 0;
        $table->auto_refresh = 0;
        $table->info_block = 1;
        $table->pagination = 1;
        $table->paginationAlign = 'right';
        $table->paginationLayout = 'full_numbers';
        $table->simpleResponsive = 0;
        $table->simpleHeader = 0;
        $table->stripeTable = 0;
        $table->cellPadding = 10;
        $table->removeBorders = 0;
        $table->borderCollapse = 'collapse';
        $table->borderSpacing = 0;
        $table->verticalScrollHeight = 600;
        $table->filtering = 1;
        $table->global_search = 1;
        $table->editable = 0;
        $table->popover_tools = 0;
        $table->edit_only_own_rows = 0;
        $table->inline_editing = 0;
        $table->editButtonsDisplayed = array('all');
        $table->enableDuplicateButton = false;
        $table->mysql_table_name = '';
        $table->filtering_form = 0;
        $table->clearFilters = 0;
        $table->display_length = 10;
        $table->showRowsPerPage = 10;
        $table->showAllRows = false;
        $table->userid_column_id = null;
        $table->editor_roles = array();
        $table->tabletools_config = array(
            'print' => 1,
            'copy' => 1,
            'excel' => 1,
            'csv' => 1,
            'pdf' => 0,
        );
        $table->columns = array();
        $table->content = '';
        $table->pdfPaperSize = 'A4';
        $table->pdfPageOrientation = 'portrait';

        return $table;
    }

    /**
     * Helper method that load table config data for Simple table from DB
     * @param int $tableID
     */
    public static function loadSimpleTableConfig($tableID){
        $res = new stdClass();

        try {
            $wpDataTableRows = WPDataTableRows::loadWpDataTableRows($tableID);
            $res->tableID = $wpDataTableRows->getTableID();
            $res->table = $wpDataTableRows->getTableSettingsData();
            $res->wdtHtml = $wpDataTableRows->generateTable($tableID);
        } catch (Exception $e) {
            $res->error = ltrim($e->getMessage(), '<br/><br/>');
        }
        return $res;
    }
    /**
     * Helper method that load rows config data from DB
     * @param int $tableID
     */
    public static function loadRowsDataFromDB($tableID){
        global $wpdb;

        do_action('wpdatatables_before_get_rows_metadata', $tableID);

        $rowsQuery = $wpdb->prepare(
            "SELECT data FROM " . $wpdb->prefix . "wpdatatables_rows WHERE table_id = %d ORDER BY id ASC", $tableID);

        $rows = $wpdb->get_results($rowsQuery);

        foreach ($rows as $key=> $row){
            $rows[$key] = json_decode($row->data);
        }

        $rows = apply_filters('wpdatatables_filter_rows_metadata', $rows, $tableID);

        return $rows;
    }

    /**
     * Save row data from Simple table in database
     * @param stdClass $rowData
     * @param int $tableID
     */
    public static function saveRowData($rowData, $tableID)
    {
        global $wpdb;

        do_action('wpdatatables_before_create_row', $tableID, $rowData);

        $wpdb->insert(
            $wpdb->prefix . "wpdatatables_rows",
            array(
                'table_id' => $tableID,
                'data' => json_encode($rowData)
            )
        );

        do_action('wpdatatables_after_save_row');
    }

    /**
     * Helper function for getting all tables and charts from the database for page builders
     *
     * @param $builder
     * @param $type
     * @return array
     */
    public static function getAllTablesAndChartsForPageBuilders($builder, $type)
    {
        $selectedType = substr($type, 0, -1);

        global $wpdb;
        $returnData = [];

        $query = "SELECT id, title FROM {$wpdb->prefix}wpdata{$type} ORDER BY id";

        $allItems = $wpdb->get_results($query, ARRAY_A);

        if ($builder === 'avada' || $builder === 'elementor' || $builder === 'divi') {
            $returnData[0] = esc_attr__( 'Select a ' . $selectedType, 'wpdatatables' );
        } else if ($builder === 'bakery') {
            $returnData[__( 'Select a ' . $selectedType, 'wpdatatables' )] = '';
        }

        if ($allItems != null){
            foreach ($allItems as $item) {
                switch ($builder) {
                    case 'gutenberg':
                        $returnData[] = [
                            'name' => $item['title'],
                            'id' => $item['id'],
                        ];
                        break;
                    case 'avada':
                    case 'elementor':
                    case 'divi':
                        $returnData[$item['id']] = $item['title']  . ' (id: ' . $item['id'] . ')';
                        break;
                    case 'bakery':
                        $returnData[$item['title']] = $item['id'];
                        break;
                }
            }
        }
        return $returnData;
    }

    public static function wdt_create_chart_notice() {

        return 'Please create a wpDataChart first. You can check out how on this <a target="_blank" href="https://wpdatatables.com/documentation/wpdatacharts/creating-charts-wordpress-wpdatachart-wizard/">link</a>.';

    }

    public static function wdt_select_chart_notice() {

        return 'Please select a wpDataChart.';

    }

    public static function wdt_create_table_notice() {

        return 'Please create a wpDataTable first. You can find detailed instructions in our docs on this <a target="_blank" href="https://wpdatatables.com/documentation/general/features-overview/">link</a>.';
    }

    public static function wdt_select_table_notice() {

        return 'Please select a wpDataTable.';
    }

}
