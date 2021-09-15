<?php

defined('ABSPATH') or die('Access denied.');

/**
 * Test the Separate connection settings
 */
function wdtTestSeparateConnectionSettings()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtSettingsNonce')) {
        exit();
    }

    $returnArray = array('success' => array(), 'errors' => array());

    $connections = Connection::getAll();

    foreach ($_POST['wdtSeparateCon'] as $separateConnection) {
        try {
            $Sql = Connection::create(
                '',
                $separateConnection['host'],
                $separateConnection['database'],
                $separateConnection['user'],
                $separateConnection['password'],
                $separateConnection['port'],
                $separateConnection['vendor'],
                $separateConnection['driver']
            );
            if ($Sql->isConnected()) {
                $returnArray['success'][] = __("Successfully connected to the {$separateConnection['vendor']} server.", 'wpdatatables');

                $isNewConnection = true;

                foreach ($connections as &$connection) {
                    if ($connection['id'] === $separateConnection['id']) {
                        $isNewConnection = false;

                        $connection = $separateConnection;
                    }
                }

                if ($isNewConnection) {
                    $connections[] = $separateConnection;
                }
            } else {
                $returnArray['errors'][] = __("wpDataTables could not connect to {$separateConnection['vendor']} server.", 'wpdatatables');
            }
        } catch (Exception $e) {
            $returnArray['errors'][] = __("wpDataTables could not connect to {$separateConnection['vendor']} server. {$separateConnection['vendor']} said: ", 'wpdatatables') . $e->getMessage();
        }
    }

    if (!$returnArray['errors']) {
        Connection::saveAll(json_encode($connections));
    }

    echo json_encode($returnArray);
    exit();
}

add_action('wp_ajax_wpdatatables_test_separate_connection_settings', 'wdtTestSeparateConnectionSettings');

/**
 * Get connection tables settings
 */
function wdtGetConnectionTables()
{
    if (!current_user_can('manage_options') ||
        !(wp_verify_nonce($_POST['wdtNonce'], 'wdtSettingsNonce')
            || wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')
            || wp_verify_nonce($_POST['wdtNonce'], 'wdtNonce'))) {
        exit();
    }
    $connection = $_POST['connection'];

    $tables = wpDataTableConstructor::listMySQLTables($connection);

    echo json_encode($tables);
    exit();
}

add_action('wp_ajax_wpdatatables_get_connection_tables', 'wdtGetConnectionTables');

/**
 * Method to save the config for the table and columns
 */
function wdtSaveTableWithColumns()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtEditNonce')) {
        exit();
    }

    $table = apply_filters(
        'wpdatatables_before_save_table',
        json_decode(
            stripslashes_deep($_POST['table'])
        )
    );

    WDTConfigController::saveTableConfig($table);
}

add_action('wp_ajax_wpdatatables_save_table_config', 'wdtSaveTableWithColumns');

/**
 * Save plugin settings
 */
function wdtSavePluginSettings()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtSettingsNonce')) {
        exit();
    }

    WDTSettingsController::saveSettings(apply_filters('wpdatatables_before_save_settings', $_POST['settings']));
    exit();
}

add_action('wp_ajax_wpdatatables_save_plugin_settings', 'wdtSavePluginSettings');

/**
 * Save Google settings
 */
function wdtSaveGoogleSettings()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtSettingsNonce')) {
        exit();
    }
    $result = [];
    $settings = json_decode(stripslashes_deep($_POST['settings']),true);
    if (json_last_error() === JSON_ERROR_NONE) {
        WDTSettingsController::saveGoogleSettings($settings);
        $result['link'] = admin_url('admin.php?page=wpdatatables-settings#google_sheet_settings');
        echo json_encode($result);
        exit();
    } else {
        $result['error'] = 'Data don\'t have valid JSON format';
        echo json_encode($result) ;
        exit();
    }

}

add_action('wp_ajax_wpdatatables_save_google_settings', 'wdtSaveGoogleSettings');

/**
 * Delete Google settings
 */
function wdtDeleteGoogleSettings()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtSettingsNonce')) {
        exit();
    }

    update_option('wdtGoogleSettings', '');
    update_option('wdtGoogleToken', '');

    echo admin_url('admin.php?page=wpdatatables-settings#google_sheet_settings');
    exit();
}

add_action('wp_ajax_wpdatatables_delete_google_settings', 'wdtDeleteGoogleSettings');

/**
 * Duplicate the table
 */
function wdtDuplicateTable()
{
    global $wpdb;

    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtDuplicateTableNonce')) {
        exit();
    }

    $tableId = (int)$_POST['table_id'];
    if (empty($tableId)) {
        return false;
    }
    $manualDuplicateInput = (int)$_POST['manual_duplicate_input'];
    $newTableName = sanitize_text_field($_POST['new_table_name']);

    // Getting the table data
    $tableData = WDTConfigController::loadTableFromDB($tableId);
    $mySqlTableName = $tableData->mysql_table_name;
    $content = $tableData->content;

    if ($tableData->table_type != 'simple') {

        // Create duplicate version of input table if checkbox is selected
        if ($manualDuplicateInput && $tableData->table_type == 'manual') {

            // Generating new input table name
            $cnt = 1;
            $newNameGenerated = false;
            while (!$newNameGenerated) {
                $newName = $tableData->mysql_table_name . '_' . $cnt;
                $checkTableQuery = "SHOW TABLES LIKE '{$newName}'";
                if (!(Connection::isSeparate($tableData->connection))) {
                    $res = $wpdb->get_results($checkTableQuery);
                } else {
                    $sql = Connection::create($tableData->connection);
                    $res = $sql->getRow($checkTableQuery);
                }
                if (!empty($res)) {
                    $cnt++;
                } else {
                    $newNameGenerated = true;
                }
            }

            // Input table queries

            $vendor = Connection::getVendor($tableData->connection);
            $isMySql = $vendor === Connection::$MYSQL;
            $isMSSql = $vendor === Connection::$MSSQL;
            $isPostgreSql = $vendor === Connection::$POSTGRESQL;

            if ($isMySql) {
                $query1 = "CREATE TABLE {$newName} LIKE {$tableData->mysql_table_name};";
                $query2 = "INSERT INTO {$newName} SELECT * FROM {$tableData->mysql_table_name};";
            }

            if ($isMSSql || $isPostgreSql) {
                $query1 = "SELECT * INTO {$newName} FROM {$tableData->mysql_table_name};";
            }

            if (!(Connection::isSeparate($tableData->connection))) {
                $wpdb->query($query1);
                $wpdb->query($query2);
            } else {
                $sql->doQuery($query1);

                if ($query2) {
                    $sql->doQuery($query2);
                }
            }
            $mySqlTableName = $newName;

            if ($tableData->table_type != 'gravity') {
                $content = str_replace($tableData->mysql_table_name, $newName, $tableData->content);
            } else {
                $content = $tableData->content;
            }

        }
    }

    // Creating new table
    $wpdb->insert(
        $wpdb->prefix . 'wpdatatables',
        array(
            'title' => $newTableName,
            'show_title' => $tableData->show_title,
            'table_type' => $tableData->table_type,
            'connection' => $tableData->connection,
            'content' => $content,
            'filtering' => $tableData->filtering,
            'filtering_form' => $tableData->filtering_form,
            'sorting' => $tableData->sorting,
            'tools' => $tableData->tools,
            'server_side' => $tableData->server_side,
            'editable' => $tableData->editable,
            'inline_editing' => $tableData->inline_editing,
            'popover_tools' => $tableData->popover_tools,
            'editor_roles' => $tableData->editor_roles,
            'mysql_table_name' => $mySqlTableName,
            'edit_only_own_rows' => $tableData->edit_only_own_rows,
            'userid_column_id' => $tableData->userid_column_id,
            'display_length' => $tableData->display_length,
            'auto_refresh' => $tableData->auto_refresh,
            'fixed_columns' => $tableData->fixed_columns,
            'fixed_layout' => $tableData->fixed_layout,
            'responsive' => $tableData->responsive,
            'scrollable' => $tableData->scrollable,
            'word_wrap' => $tableData->word_wrap,
            'hide_before_load' => $tableData->hide_before_load,
            'var1' => $tableData->var1,
            'var2' => $tableData->var2,
            'var3' => $tableData->var3,
            'tabletools_config' => serialize($tableData->tabletools_config),
            'advanced_settings' => $tableData->advanced_settings
        )
    );

    $newTableId = $wpdb->insert_id;

    if ($tableData->table_type != 'simple') {
        // Getting the column data
        $columns = WDTConfigController::loadColumnsFromDB($tableId);

        // Creating new columns
        foreach ($columns as $column) {
            $wpdb->insert(
                $wpdb->prefix . 'wpdatatables_columns',
                array(
                    'table_id' => $newTableId,
                    'orig_header' => $column->orig_header,
                    'display_header' => $column->display_header,
                    'filter_type' => $column->filter_type,
                    'column_type' => $column->column_type,
                    'input_type' => $column->input_type,
                    'input_mandatory' => $column->input_mandatory,
                    'id_column' => $column->id_column,
                    'group_column' => $column->group_column,
                    'sort_column' => $column->sort_column,
                    'hide_on_phones' => $column->hide_on_phones,
                    'hide_on_tablets' => $column->hide_on_tablets,
                    'visible' => $column->visible,
                    'sum_column' => $column->sum_column,
                    'skip_thousands_separator' => $column->skip_thousands_separator,
                    'width' => $column->width,
                    'possible_values' => $column->possible_values,
                    'default_value' => $column->default_value,
                    'css_class' => $column->css_class,
                    'text_before' => $column->text_before,
                    'text_after' => $column->text_after,
                    'formatting_rules' => $column->formatting_rules,
                    'calc_formula' => $column->calc_formula,
                    'color' => $column->color,
                    'pos' => $column->pos,
                    'advanced_settings' => $column->advanced_settings
                )
            );

            if ($column->id == $tableData->userid_column_id) {
                $userIdColumnNewId = $wpdb->insert_id;

                $wpdb->update(
                    $wpdb->prefix . 'wpdatatables',
                    array('userid_column_id' => $userIdColumnNewId),
                    array('id' => $newTableId)
                );
            }

        }
    } else {
        $rows = WDTConfigController::loadRowsDataFromDB($tableId);
        foreach ($rows as $row) {
            $wpdb->insert(
                $wpdb->prefix . "wpdatatables_rows",
                array(
                    'table_id' => $newTableId,
                    'data' => json_encode($row)
                )
            );
        }
    }

    exit();

}

add_action('wp_ajax_wpdatatables_duplicate_table', 'wdtDuplicateTable');

/**
 * Duplicate the chart
 */

function wdtDuplicateChart()
{
    global $wpdb;

    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtDuplicateChartNonce')) {
        exit();
    }

    $chartId = (int)$_POST['chart_id'];
    if (empty($chartId)) {
        return false;
    }
    $newChartName = sanitize_text_field($_POST['new_chart_name']);

    $chartQuery = $wpdb->prepare(
        'SELECT * FROM ' . $wpdb->prefix . 'wpdatacharts WHERE id = %d',
        $chartId
    );

    $wpDataChart = $wpdb->get_row($chartQuery);

    // Creating new table
    $wpdb->insert(
        $wpdb->prefix . "wpdatacharts",
        array(
            'wpdatatable_id' => $wpDataChart->wpdatatable_id,
            'title' => $newChartName,
            'engine' => $wpDataChart->engine,
            'type' => $wpDataChart->type,
            'json_render_data' => $wpDataChart->json_render_data
        )
    );

    exit();
}

add_action('wp_ajax_wpdatatables_duplicate_chart', 'wdtDuplicateChart');


function wdtCreateSimpleTable()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')) {
        exit();
    }
    $tableData = apply_filters(
        'wpdatatables_before_create_simple_table',
        json_decode(
            stripslashes_deep(
                $_POST['tableData']
            )
        )
    );

    $wpDataTableRows = new WPDataTableRows($tableData);

    // Generate new id and save settings in wpdatatables table in DB
    $newTableId = generateSimpleTableID($wpDataTableRows);

    // Save table with empty data
    $wpDataTableRows->saveTableWithEmptyData($newTableId);

    // Generate a link for new table
    echo admin_url('admin.php?page=wpdatatables-constructor&source&simple&table_id=' . $newTableId);

    exit();
}

add_action('wp_ajax_wpdatatables_create_simple_table', 'wdtCreateSimpleTable');

function wdtGetHandsontableData()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtEditNonce')) {
        exit();
    }

    $tableID = (int)$_POST['tableID'];
    $res = new stdClass();

    try {
        $wpDataTableRows = WPDataTableRows::loadWpDataTableRows($tableID);
        $res->tableData = $wpDataTableRows->getRowsData();
        $res->tableMeta = $wpDataTableRows->getTableSettingsData()->content;
    } catch (Exception $e) {
        $res->error = ltrim($e->getMessage(), '<br/><br/>');
    }
    echo json_encode($res);
    exit();
}

add_action('wp_ajax_wpdatatables_get_handsontable_data', 'wdtGetHandsontableData');

function generateSimpleTableID($wpDataTableRows)
{
    global $wpdb;
    $tableContent = new stdClass();
    $tableContent->rowNumber = $wpDataTableRows->getRowNumber();
    $tableContent->colNumber = $wpDataTableRows->getColNumber();
    $tableContent->colWidths = $wpDataTableRows->getColWidths();
    $tableContent->colHeaders = $wpDataTableRows->getColHeaders();
    $tableContent->reloadCounter = $wpDataTableRows->getReloadCounter();
    $tableContent->mergedCells = $wpDataTableRows->getMergeCells();

    // Create the wpDataTable metadata
    $wpdb->insert(
        $wpdb->prefix . "wpdatatables",
        array(
            'title' => sanitize_text_field($wpDataTableRows->getTableName()),
            'table_type' => $wpDataTableRows->getTableType(),
            'connection' => '',
            'content' => json_encode($tableContent),
            'server_side' => 0,
            'mysql_table_name' => '',
            'tabletools_config' => serialize(array(
                'print' => 1,
                'copy' => 1,
                'excel' => 1,
                'csv' => 1,
                'pdf' => 0
            )),
            'advanced_settings' => json_encode(array(
                'simpleResponsive' => 0,
                'simpleHeader' => 0,
                'stripeTable' => 0,
                'cellPadding' => 10,
                'verticalScroll' => 0,
                'verticalScrollHeight' => 600
                )
            )
        )
    );

    // Store the new table metadata ID
    return $wpdb->insert_id;
}

/**
 * Save data in database for Simple table
 */
function wdtSaveDataSimpleTable()
{
    global $wpdb;

    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtEditNonce')) {
        exit();
    }
    $turnOffSimpleHeader = 0;
    $rowsData = json_decode(stripslashes_deep($_POST['rowsData']));
    $tableSettings = json_decode(stripslashes_deep($_POST['tableSettings']));
    $tableID = intval($tableSettings->id);
    $scrollable = intval($tableSettings->scrollable);
    $fixedLayout = intval($tableSettings->fixed_layout);
    $wordWrap = intval($tableSettings->word_wrap);
    $showTitle = intval($tableSettings->show_title);
    $title = sanitize_text_field($tableSettings->title);
    $result = new stdClass();

    if ($tableSettings->content->mergedCells){
        $mergedCells = $tableSettings->content->mergedCells;
        foreach ($mergedCells as $mergedCell){
            if($mergedCell->row == 0 && $mergedCell->rowspan > 1){
                $turnOffSimpleHeader = 1;
            }
        }
    }

    $wpdb->update(
        $wpdb->prefix . "wpdatatables",
        array(
            'content' => json_encode($tableSettings->content),
            'scrollable' => $scrollable,
            'fixed_layout' => $fixedLayout,
            'word_wrap' => $wordWrap,
            'show_title' => $showTitle,
            'title' => $title,
            'advanced_settings' => json_encode(
                array(
                    'simpleResponsive' => $tableSettings->simpleResponsive,
                    'simpleHeader' => $turnOffSimpleHeader ? 0 : $tableSettings->simpleHeader,
                    'stripeTable' => $tableSettings->stripeTable,
                    'cellPadding' => $tableSettings->cellPadding,
                    'verticalScroll' => $tableSettings->verticalScroll,
                    'verticalScrollHeight' => $tableSettings->verticalScrollHeight,
                )
            )
        ),
        array('id' => $tableID)
    );

    if ($wpdb->last_error == '') {
        try {
            $wpDataTableRows = new WPDataTableRows($tableSettings);

            if ($wpDataTableRows->checkIsExistTableID($tableID)) {
                $wpDataTableRows->deleteRowsData($tableID);
            }
            foreach ($rowsData as $rowData){
                WDTConfigController::saveRowData($rowData, $tableID);
            }
            $wpDataTableRows = WPDataTableRows::loadWpDataTableRows($tableID);
            $result->reload =  $wpDataTableRows->getTableSettingsData()->content->reloadCounter;
            $result->tableHTML = $wpDataTableRows->generateTable($tableID);
        } catch (Exception $e) {
            $result->error = ltrim($e->getMessage(), '<br/><br/>');
        }
    } else {
        $result->error = $wpdb->last_error;
    }

    echo json_encode($result);
    exit();
}

add_action('wp_ajax_wpdatatables_save_simple_table_data', 'wdtSaveDataSimpleTable');

/**
 * Create a manually built table and open in Edit Page
 */
function wdtCreateManualTable()
{

    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')) {
        exit();
    }

    $tableData = stripslashes_deep($_POST['tableData']);
    $tableData = apply_filters('wpdatatables_before_create_manual_table', $tableData);

    // Create a new Constructor object
    $constructor = new wpDataTableConstructor($tableData['connection']);

    // Generate and return a new 'Manual' type table
    $newTableId = $constructor->generateManualTable($tableData);

    // Generate a link for new table
    echo admin_url('admin.php?page=wpdatatables-constructor&source&table_id=' . $newTableId);

    exit();
}

add_action('wp_ajax_wpdatatables_create_manual_table', 'wdtCreateManualTable');

/**
 * Action for generating a WP-based MySQL query
 */
function wdtGenerateWPBasedQuery()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')) {
        exit();
    }

    $tableData = $_POST['tableData'];
    $tableData = apply_filters('wpdatatables_before_generate_wp_based_query', $tableData);

    $constructor = new wpDataTableConstructor();
    $constructor->generateWPBasedQuery($tableData);
    $result = array(
        'query' => $constructor->getQuery(),
        'preview' => $constructor->getQueryPreview()
    );

    echo json_encode($result);
    exit();
}

add_action('wp_ajax_wpdatatables_generate_wp_based_query', 'wdtGenerateWPBasedQuery');

/**
 * Action for refreshing the WP-based query
 */
function wdtRefreshWPQueryPreview()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')) {
        exit();
    }

    $query = $_POST['query'];

    $constructor = new wpDataTableConstructor($_POST['connection']);
    $constructor->setQuery($query);

    echo $constructor->getQueryPreview($_POST['connection']);
    exit();
}

add_action('wp_ajax_wpdatatables_refresh_wp_query_preview', 'wdtRefreshWPQueryPreview');

/**
 * Action for generating the table from query/constructed table data
 */
function wdtConstructorGenerateWDT()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')) {
        exit();
    }

    $tableData = $_POST['table_data'];

    $constructor = new wpDataTableConstructor($tableData['connection']);
    $res = $constructor->generateWdtBasedOnQuery($tableData);
    if (empty($res->error)) {
        $res->link = get_admin_url() . "admin.php?page=wpdatatables-constructor&source&table_id={$res->table_id}";
    }

    echo json_encode($res);
    exit();
}

add_action('wp_ajax_wpdatatables_constructor_generate_wdt', 'wdtConstructorGenerateWDT');

/**
 * Request the column list for the selected tables
 */
function wdtConstructorGetMySqlTableColumns()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')) {
        exit();
    }
    if (isset($_POST['tables'])) {
        $tables = array_map('sanitize_text_field', $_POST['tables']);
        $columns = wpDataTableConstructor::listMySQLColumns($tables, $_POST['connection']);
    } else {
        $columns = array('allColumns' => array(), 'sortedColumns' => array());
    }
    echo json_encode($columns);
    exit();
}

add_action('wp_ajax_wpdatatables_constructor_get_mysql_table_columns', 'wdtConstructorGetMySqlTableColumns');

/**
 * Action for generating a WP-based MySQL query
 */
function wdtGenerateMySqlBasedQuery()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')) {
        exit();
    }

    $tableData = $_POST['tableData'];
    $tableData = apply_filters('wpdatatables_before_generate_mysql_based_query', $tableData);

    $constructor = new wpDataTableConstructor($tableData['connection']);
    $constructor->generateMySQLBasedQuery($tableData);
    $result = array(
        'query' => $constructor->getQuery(),
        'preview' => $constructor->getQueryPreview($tableData['connection'])
    );

    echo json_encode($result);
    exit();
}

add_action('wp_ajax_wpdatatables_generate_mysql_based_query', 'wdtGenerateMySqlBasedQuery');

/**
 * Generate a file-based table preview (first 4 rows)
 */
function wdtConstructorPreviewFileTable()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')) {
        exit();
    }

    $tableData = $_POST['tableData'];
    $tableData = apply_filters('wpdatatables_before_preview_file_table', $tableData);

    $constructor = new wpDataTableConstructor($tableData['connection']);
    $result = $constructor->previewFileTable($tableData);

    echo json_encode($result);
    exit();
}

add_action('wp_ajax_wpdatatables_preview_file_table', 'wdtConstructorPreviewFileTable');

/**
 * Read data from file and generate the table
 */
function wdtConstructorReadFileData()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')) {
        exit();
    }

    $result = array();
    $tableData = $_POST['tableData'];
    $tableData = apply_filters('wpdatatables_before_read_file_data', $tableData);

    $constructor = new wpDataTableConstructor($tableData['connection']);

    try {
        $constructor->readFileData($tableData);
        if ($constructor->getTableId() != false) {
            $result['res'] = 'success';
            $result['link'] = get_admin_url() . "admin.php?page=wpdatatables-constructor&source&table_id=" . $constructor->getTableId();
        } else {
            $result['res'] = 'error';
            $result['text'] = __('There was an error while trying to import table', 'wpdatatables');
        }
    } catch (Exception $e) {
        $result['res'] = 'error';
        $result['text'] = __('There was an error while trying to import table. Exception: ', 'wpdatatables') . $e->getMessage();
    }

    echo json_encode($result);
    exit();
}

add_action('wp_ajax_wpdatatables_constructor_read_file_data', 'wdtConstructorReadFileData');

/**
 * Add a column to a manually  created table
 */
function wdtAddNewManualColumn()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtFrontendEditTableNonce' . (int)$_POST['table_id'])) {
        exit();
    }

    $tableId = (int)$_POST['table_id'];
    $columnData = $_POST['column_data'];
    wpDataTableConstructor::addNewManualColumn($tableId, $columnData);

    exit();
}

add_action('wp_ajax_wpdatatables_add_new_manual_column', 'wdtAddNewManualColumn');

/**
 * Delete a column from a manually created table
 */
function wdtDeleteManualColumn()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtFrontendEditTableNonce' . (int)$_POST['table_id'])) {
        exit();
    }

    $tableId = (int)$_POST['table_id'];
    $columnName = sanitize_text_field($_POST['column_name']);
    wpDataTableConstructor::deleteManualColumn($tableId, $columnName);

    exit();
}

add_action('wp_ajax_wpdatatables_delete_manual_column', 'wdtDeleteManualColumn');

/**
 * Return all columns for a provided table
 */
function wdtGetColumnsDataByTableId()
{
    if (!current_user_can('manage_options') ||
        !(wp_verify_nonce($_POST['wdtNonce'], 'wdtChartWizardNonce') ||
            wp_verify_nonce($_POST['wdtNonce'], 'wdtEditNonce'))
    ) {
        exit();
    }

    $tableId = (int)$_POST['table_id'];

    echo json_encode(WDTConfigController::loadColumnsFromDB($tableId));
    exit();
}

add_action('wp_ajax_wpdatatables_get_columns_data_by_table_id', 'wdtGetColumnsDataByTableId');

/**
 * Returns the complete table for the range picker
 */
function wdtGetCompleteTableJSONById()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtChartWizardNonce')) {
        exit();
    }

    $tableId = (int)$_POST['table_id'];
    $wpDataTable = WPDataTable::loadWpDataTable($tableId, null, true);

    echo json_encode($wpDataTable->getDataRowsFormatted());
    exit();
}

add_action('wp_ajax_wpdatatables_get_complete_table_json_by_id', 'wdtGetCompleteTableJSONById');


function wdtShowChartFromData()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtChartWizardNonce')) {
        exit();
    }

    $chartData = stripslashes_deep($_POST['chart_data']);
    $wpDataChart = WPDataChart::factory($chartData, false);

    echo json_encode($wpDataChart->returnData());
    exit();
}

add_action('wp_ajax_wpdatatable_show_chart_from_data', 'wdtShowChartFromData');


function wdtSaveChart()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtChartWizardNonce')) {
        exit();
    }

    $chartData = stripslashes_deep($_POST['chart_data']);
    $wpDataChart = WPDataChart::factory($chartData, false);
    $wpDataChart->save();

    echo json_encode(array('id' => $wpDataChart->getId(), 'shortcode' => $wpDataChart->getShortCode()));
    exit();
}

add_action('wp_ajax_wpdatatable_save_chart_get_shortcode', 'wdtSaveChart');

/**
 * List all tables in JSON
 */
function wdtListAllTables()
{
    if (!current_user_can('manage_options')) {
        exit();
    }

    echo json_encode(WPDataTable::getAllTables());
    exit();
}

add_action('wp_ajax_wpdatatable_list_all_tables', 'wdtListAllTables');

/**
 * List all charts in JSON
 */
function wdtListAllCharts()
{
    if (!current_user_can('manage_options')) {
        exit();
    }

    echo json_encode(WPDataChart::getAllCharts());
    exit();
}

add_action('wp_ajax_wpdatatable_list_all_charts', 'wdtListAllCharts');

/**
 * Read Distinct Values from the table for column
 * Used to populate possible values list for Server Side tables
 *
 * @throws Exception
 * @throws WDTException
 * @throws Exception
 */
function wdtReadDistinctValuesFromTable()
{
    $tableId = (int)$_POST['tableId'];
    $columnId = (int)$_POST['columnId'];

    $wpDataTable = WPDataTable::loadWpDataTable($tableId);
    $tableData = WDTConfigController::loadTableFromDB($tableId);

    $columnData = WDTConfigController::loadSingleColumnFromDB($columnId);
    $column = $wpDataTable->getColumn($columnData['orig_header']);

    $distValues = WDTColumn::getPossibleValuesRead($column, false, $tableData );
    echo json_encode($distValues);
    exit();
}

add_action('wp_ajax_wpdatatable_get_column_distinct_values', 'wdtReadDistinctValuesFromTable');

/**
 * Get the preview for formula column
 *
 * @throws WDTException
 */
function wdtPreviewFormulaResult()
{
    $tableId = (int)$_POST['table_id'];
    $formula = sanitize_text_field($_POST['formula']);

    $wpDataTable = WPDataTable::loadWpDataTable($tableId);

    echo $wpDataTable->calcFormulaPreview($formula);
    exit();
}

add_action('wp_ajax_wpdatatables_preview_formula_result', 'wdtPreviewFormulaResult');

/**
 * Validate purchase code
 */
function wdtActivatePlugin()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtSettingsNonce')) {
        exit();
    }

    /** @var string $slug */
    $slug = filter_var($_POST['slug'], FILTER_SANITIZE_STRING);

    /** @var string $purchaseCode */
    $purchaseCode = filter_var($_POST['purchaseCodeStore'], FILTER_SANITIZE_STRING);

    /** @var string $domain */
    $domain = filter_var($_POST['domain'], FILTER_SANITIZE_STRING);
    $domain = WDTTools::getDomain($domain);

    /** @var string $subdomain */
    $subdomain = filter_var($_POST['subdomain'], FILTER_SANITIZE_STRING);
    $subdomain = WDTTools::getSubDomain($subdomain);

    $request = wp_remote_get(
        WDT_STORE_API_URL . 'activation/code?slug=' . $slug . '&purchaseCode=' . $purchaseCode . '&domain=' . $domain . '&subdomain=' . $subdomain
    );

    /** @var bool $valid */
    $valid = json_decode($request['body'])->valid;

    /** @var bool $valid */
    $domainRegistered = json_decode($request['body'])->domainRegistered;

    if ($valid && $domainRegistered) {
        if ($slug === 'wpdatatables') {
            update_option('wdtPurchaseCodeStore', $purchaseCode);
            update_option('wdtActivated', true);
        } else if ($slug === 'wdt-powerful-filters') {
            update_option('wdtPurchaseCodeStorePowerful', $purchaseCode);
            update_option('wdtActivatedPowerful', true);
        } else if ($slug === 'reportbuilder') {
            update_option('wdtPurchaseCodeStoreReport', $purchaseCode);
            update_option('wdtActivatedReport', true);
        } else if ($slug === 'wdt-gravity-integration') {
            update_option('wdtPurchaseCodeStoreGravity', $purchaseCode);
            update_option('wdtActivatedGravity', true);
        } else if ($slug === 'wdt-formidable-integration') {
            update_option('wdtPurchaseCodeStoreFormidable', $purchaseCode);
            update_option('wdtActivatedFormidable', true);
        } else if ($slug === 'wdt-master-detail') {
            update_option('wdtPurchaseCodeStoreMasterDetail', $purchaseCode);
            update_option('wdtActivatedMasterDetail', true);
        }
    }

    if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
        echo $request['body'];
    }

    exit();
}

add_action('wp_ajax_wpdatatables_activate_plugin', 'wdtActivatePlugin');

/**
 * Deactivate plugin
 */
function wdtDeactivatePlugin()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtSettingsNonce')) {
        exit();
    }

    /** @var string $slug */
    $slug = filter_var($_POST['slug'], FILTER_SANITIZE_STRING);

    switch ($slug){
        case 'wpdatatables':
            $purchaseCode = get_option('wdtPurchaseCodeStore');
            break;
        case 'wdt-master-detail':
            $purchaseCode = get_option('wdtPurchaseCodeStoreMasterDetail');
            break;
        case 'wdt-powerful-filters':
            $purchaseCode = get_option('wdtPurchaseCodeStorePowerful');
            break;
        case 'wdt-gravity-integration':
            $purchaseCode = get_option('wdtPurchaseCodeStoreGravity');
            break;
        case 'wdt-formidable-integration':
            $purchaseCode = get_option('wdtPurchaseCodeStoreFormidable');
            break;
        case 'reportbuilder':
            $purchaseCode = get_option('wdtPurchaseCodeStoreReport');
            break;
        default:
            $purchaseCode = '';
            break;
    }

    /** @var string $envatoTokenEmail */
    $envatoTokenEmail = filter_var($_POST['envatoTokenEmail'], FILTER_SANITIZE_STRING);

    /** @var string $domain */
    $domain = filter_var($_POST['domain'], FILTER_SANITIZE_STRING);
    $domain = WDTTools::getDomain($domain);

    /** @var string $subdomain */
    $subdomain = filter_var($_POST['subdomain'], FILTER_SANITIZE_STRING);
    $subdomain = WDTTools::getSubDomain($subdomain);

    /** @var string $type */
    $type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);

    if ($type === 'code') {
        $request = wp_remote_get(
            WDT_STORE_API_URL . 'activation/code/deactivate?slug=' . $slug . '&purchaseCode=' . $purchaseCode . '&domain=' . $domain . '&subdomain=' . $subdomain
        );
    } else {
        $request = wp_remote_get(
            WDT_STORE_API_URL . 'activation/envato/deactivate?slug=' . $slug . '&envatoTokenEmail=' . $envatoTokenEmail . '&domain=' . $domain . '&subdomain=' . $subdomain
        );
    }

    /** @var bool $deactivated */
    $deactivated = json_decode($request['body'])->deactivated;

    if ($deactivated === true) {
        WDTTools::deactivatePlugin($slug);
    }

    echo $request['body'];

    exit();
}

add_action('wp_ajax_wpdatatables_deactivate_plugin', 'wdtDeactivatePlugin');

function wdtParseServerName()
{
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtSettingsNonce')) {
        exit();
    }
    /** @var array $serverName */
    $serverName['domain'] = filter_var($_POST['domain'], FILTER_SANITIZE_STRING);
    $serverName['domain'] = WDTTools::getDomain($serverName['domain']);
    $serverName['subdomain'] = filter_var($_POST['subdomain'], FILTER_SANITIZE_STRING);
    $serverName['subdomain'] = WDTTools::getSubDomain($serverName['subdomain']);

    echo json_encode($serverName);

    exit();

}

add_action('wp_ajax_wpdatatables_parse_server_name', 'wdtParseServerName');