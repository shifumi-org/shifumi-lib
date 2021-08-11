<?php

defined('ABSPATH') or die('Access denied.');

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Ods;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Class Constructor contains methods and properties for constructing the tables
 * in wpDataTables WordPress plugin
 *
 * @author cjbug@ya.ru
 *
 * @since June 2014
 */
class wpDataTableConstructor
{

    private $_name;
    private $_index;
    private $_db;
    private $_id = false;


    /*** For the WP DB type query ***/

    private $_tables_fields = array();
    private $_select_arr = array();
    private $_where_arr = array();
    private $_group_arr = array();
    private $_from_arr = array();
    private $_inner_join_arr = array();
    private $_left_join_arr = array();
    private $_table_aliases = array();
    private $_column_aliases = array();
    private $_column_headers = array();
    private $_has_groups = false;

    /** Query text **/
    private $_query = '';

    /**
     * The constructor
     *
     * @param String $connection
     */
    public function __construct($connection = null)
    {
        if (WDT_ENABLE_MYSQL && (Connection::isSeparate($connection))) {
            $this->_db = Connection::create($connection);
        }
    }

    /**
     * Sets ID;
     *
     * @param int $id
     */
    public function setTableId($id)
    {
        $this->_id = $id;
    }

    /**
     * Gets table ID
     */
    public function getTableId()
    {
        return $this->_id;
    }

    /**
     * Generate the new unique table name (For MySQL)
     *
     * @param String $connection
     */
    public function generateTableName($connection)
    {

        $this->_index = (int)get_option('wdtGeneratedTablesCount', 0);
        $this->_index += 1;

        $this->_name = 'wpdatatable_' . $this->_index;

        if (!(Connection::isSeparate($connection))) {
            global $wpdb;
            $this->_name = $wpdb->prefix . $this->_name;
        }

        $this->_name = apply_filters('wpdatatables_before_generate_constructed_table_name', $this->_name);

        return $this->_name;
    }

    /**
     * Helper function to prepare the filter, editor, column types, and create statement
     *
     * @param $column_header
     * @param $column
     * @param String $connection
     *
     * @return array
     */
    private static function defineColumnProperties($column_header, $column, $connection = null)
    {
        $vendor = Connection::getVendor($connection);
        $isMySql = $vendor === Connection::$MYSQL;
        $isMSSql = $vendor === Connection::$MSSQL;
        $isPostgreSql = $vendor === Connection::$POSTGRESQL;

        $columnQuoteStart = Connection::getLeftColumnQuote($vendor);
        $columnQuoteEnd = Connection::getRightColumnQuote($vendor);

        if ($isMySql) {
            $columnCollate = '';
            $columnTextType = 'TEXT';
            $columnIntType = 'INT(11)';
            $columnDateTimeType = 'DATETIME';
            $nullable = '';
        }

        if ($isMSSql) {
            $columnCollate = 'COLLATE Latin1_General_CS_AI';
            $columnTextType = 'VARCHAR(8000)';
            $columnIntType = 'INT';
            $columnDateTimeType = 'DATETIME';
            $nullable = 'NULL';
        }

        if ($isPostgreSql) {
            $columnCollate = '';
            $columnTextType = 'TEXT';
            $columnIntType = 'INT';
            $columnDateTimeType = 'TIMESTAMP';
            $nullable = '';
        }

        $column_header = $columnQuoteStart . $column_header. $columnQuoteEnd;

        switch ($column['type']) {
            case 'input':
                $columnProperties = array(
                    'editor_type' => 'text',
                    'column_type' => 'string',
                    'filter_type' => 'text',
                    'create_block' => "{$column_header} VARCHAR(255) $columnCollate "
                );
                break;
            case 'int':
                $columnProperties = array(
                    'editor_type' => 'text',
                    'column_type' => 'int',
                    'filter_type' => 'number',
                    'create_block' => "{$column_header} $columnIntType "
                );
                break;
            case 'float':
                $columnProperties = array(
                    'editor_type' => 'text',
                    'column_type' => 'float',
                    'filter_type' => 'number',
                    'create_block' => "{$column_header} DECIMAL(16,4) "
                );
                break;
            case 'memo':
                $columnProperties = array(
                    'editor_type' => 'textarea',
                    'column_type' => 'string',
                    'filter_type' => 'text',
                    'create_block' => "{$column_header} $columnTextType $columnCollate "
                );
                break;
            case 'select':
                $columnProperties = array(
                    'editor_type' => 'selectbox',
                    'column_type' => 'string',
                    'filter_type' => 'select',
                    'create_block' => "{$column_header} VARCHAR(2000) $columnCollate "
                );
                break;
            case 'multiselect':
                $columnProperties = array(
                    'editor_type' => 'multi-selectbox',
                    'column_type' => 'string',
                    'filter_type' => 'multiselect',
                    'create_block' => "{$column_header} VARCHAR(2000) $columnCollate "
                );
                break;
            case 'date':
                $columnProperties = array(
                    'editor_type' => 'date',
                    'column_type' => 'date',
                    'filter_type' => 'date-range',
                    'create_block' => "{$column_header} DATE "
                );
                break;
            case 'datetime':
                $columnProperties = array(
                    'editor_type' => 'datetime',
                    'column_type' => 'datetime',
                    'filter_type' => 'datetime-range',
                    'create_block' => "{$column_header} $columnDateTimeType "
                );
                break;
            case 'time':
                $columnProperties = array(
                    'editor_type' => 'time',
                    'column_type' => 'time',
                    'filter_type' => 'time-range',
                    'create_block' => "{$column_header} TIME "
                );
                break;
            case 'email':
                $columnProperties = array(
                    'editor_type' => 'email',
                    'column_type' => 'email',
                    'filter_type' => 'text',
                    'create_block' => "{$column_header} VARCHAR(2000) $columnCollate "
                );
                break;
            case 'link':
                $columnProperties = array(
                    'editor_type' => 'link',
                    'column_type' => 'link',
                    'filter_type' => 'text',
                    'create_block' => "{$column_header} VARCHAR(2000) $columnCollate "
                );
                break;
            case 'file':
                $columnProperties = array(
                    'editor_type' => 'attachment',
                    'column_type' => 'link',
                    'filter_type' => 'none',
                    'create_block' => "{$column_header} VARCHAR(2000) $columnCollate "
                );
                break;
            case 'image':
                $columnProperties = array(
                    'editor_type' => 'attachment',
                    'column_type' => 'image',
                    'filter_type' => 'none',
                    'create_block' => "{$column_header} VARCHAR(2000) $columnCollate "
                );
                break;
            default:
                $columnProperties = array(
                    'editor_type' => 'text',
                    'column_type' => 'string',
                    'filter_type' => 'text',
                    'create_block' => "{$column_header} VARCHAR(2000) $columnCollate "
                );
                break;
        }

        $columnProperties['advanced_settings'] = array(
            'sorting' => 1,
            'exactFiltering' => 0,
            'rangeSlider' => 0,
            'filterLabel' => '',
            'editingDefaultValue' => $column['type'] === 'multiselect' ? isset($column['default_value']) ? sanitize_text_field(implode('|', $column['default_value'])) : '': sanitize_text_field($column['default_value']),
            'possibleValuesAjax' => $columnProperties['column_type'] === 'string' ? 10 : -1,
            'globalSearchColumn' => 1,
        );

        $columnProperties['create_block'] = $columnProperties['create_block'] . ' ' . $nullable;

        if (!empty($column['possible_values'])) {
            $columnProperties['advanced_settings']['possibleValuesType'] = 'list';
        }

        return $columnProperties;

    }

    /**
     * Generates and saves a new MySQL table and a new wpDataTable
     */
    public function generateManualTable($tableData)
    {
        global $wpdb;

        $this->_table_data = apply_filters('wdt_before_generate_manual_table', $tableData);

        // Selected Database Connection Name
        $connection = $tableData['connection'];

        // Generate the MySQL table name
        $this->generateTableName($connection);

        // Create the wpDataTable metadata
        $wpdb->insert(
            $wpdb->prefix . "wpdatatables",
            array(
                'title' => sanitize_text_field($this->_table_data['name']),
                'table_type' => 'manual',
                'connection' => $connection,
                'content' => 'SELECT 
                              * FROM ' . $this->_name,
                'server_side' => 1,
                'mysql_table_name' => $this->_name,
                'tabletools_config' => serialize(array(
                    'print' => 1,
                    'copy' => 1,
                    'excel' => 1,
                    'csv' => 1,
                    'pdf' => 0
                ))
            )
        );

        // Store the new table metadata ID
        $wpdatatable_id = $wpdb->insert_id;

        $vendor = Connection::getVendor($connection);
        $isMySql = $vendor === Connection::$MYSQL;
        $isMSSql = $vendor === Connection::$MSSQL;
        $isPostgreSql = $vendor === Connection::$POSTGRESQL;

        $origHeader = 'wdt_ID';

        // Prepare the create statement for the table itself
        if ($isMySql) {
            $create_statement = "CREATE TABLE " . $this->_name . " (
 	 							wdt_ID INT( 11 ) NOT NULL AUTO_INCREMENT,";
        }

        if ($isMSSql) {
            $create_statement = "CREATE TABLE " . $this->_name . " (
 	 							wdt_ID INT NOT NULL IDENTITY(1,1),";
        }

        if ($isPostgreSql) {
            $create_statement = "CREATE TABLE " . $this->_name . " (
 	 							wdt_ID SERIAL,";
            $origHeader = 'wdt_id';
        }


        $column_headers = array();

        $column_index = 0;

        // Add metadata for ID column
        $wpdb->insert(
            $wpdb->prefix . "wpdatatables_columns",
            array(
                'table_id' => $wpdatatable_id,
                'orig_header' => $origHeader,
                'display_header' => $origHeader,
                'filter_type' => 'none',
                'column_type' => 'int',
                'visible' => 0,
                'pos' => $column_index,
                'id_column' => 1
            )
        );
        $column_index++;

        $additional_statement = '';
        $additional_statement = apply_filters('wpdt_add_default_columns', $additional_statement, $wpdatatable_id, $column_index);
        if (!empty($additional_statement)) {
            $create_statement .= $additional_statement['statment'];
            $column_index = $additional_statement['column_index'];
        }

        foreach ($this->_table_data['columns'] as $column) {

            $column_header = WDTTools::generateMySQLColumnName(sanitize_text_field($column['name']), $column_headers);

            $column_headers[] = $column_header;
            if (!isset($column['orig_header'])) {
                $column['orig_header'] = sanitize_text_field($column['name']);
            }
            $this->_column_headers[$column['orig_header']] = $column_header;

            $columnProperties = self::defineColumnProperties($column_header, $column, $connection);

            // Create the column metadata in WPDB
            $wpdb->insert(
                $wpdb->prefix . "wpdatatables_columns",
                array(
                    'table_id' => $wpdatatable_id,
                    'orig_header' => $column_header,
                    'display_header' => sanitize_text_field($column['name']),
                    'filter_type' => sanitize_text_field($columnProperties['filter_type']),
                    'column_type' => sanitize_text_field($columnProperties['column_type']),
                    'pos' => $column_index,
                    'possible_values' => str_replace(',,;,|', '|', sanitize_text_field($column['possible_values'])),
                    'advanced_settings' => json_encode($columnProperties['advanced_settings']),
                    'input_type' => sanitize_text_field($columnProperties['editor_type'])
                )
            );

            $create_statement .= $columnProperties['create_block'] . ', ';

            $column_index++;
        }

        // Add the ID unique key
        if ($isMySql) {
            $create_statement .= " UNIQUE KEY wdt_ID (wdt_ID)) CHARACTER SET=utf8 COLLATE utf8_general_ci";
        }

        if ($isMSSql) {
            $create_statement .= " CONSTRAINT UC_{$this->_name}_wdt_ID UNIQUE (wdt_ID))";
        }

        if ($isPostgreSql) {
            $create_statement .= " UNIQUE (wdt_ID))";
        }



        // Call the create statement on WPDB or on external DB if it is defined
        if (Connection::isSeparate($connection)) {
            // External DB
            $this->_db->doQuery($create_statement, array());
            if ($this->_db->getLastError() != '') {
                WPDataTable::deleteTable($wpdatatable_id);
                throw new Exception(__('There was an error when trying to create the table on MySQL side', 'wpdatatables') . ': ' . $this->_db->getLastError());
            }
        } else {
            $wpdb->query($create_statement);
            $db_error = $wpdb->last_error;
            if ($db_error != '') {
                WPDataTable::deleteTable($wpdatatable_id);
                throw new Exception(__('There was an error when trying to create the table on MySQL side', 'wpdatatables') . ': ' . $db_error);
            }
        }

        // Update the index in WPDB
        update_option('wdtGeneratedTablesCount', $this->_index);

        return $wpdatatable_id;

    }

    /**
     * Generates and returns a MySQL query based on user's visual input
     *
     * @param $tableData
     */
    public function generateMySQLBasedQuery($tableData)
    {
        global $wpdb;

        $this->_table_data = apply_filters('wdt_before_generate_mysql_based_query', $tableData);

        if (!isset($this->_table_data['whereConditions'])) {
            $this->_table_data['whereConditions'] = array();
        }

        if (isset($this->_table_data['groupingRules'])) {
            $this->_has_groups = true;
        }

        if (!isset($tableData['mySqlColumns'])) {
            $tableData['mySqlColumns'] = array();
        }

        $vendor = Connection::getVendor($tableData['connection']);

        $columnQuoteStart = Connection::getLeftColumnQuote($vendor);
        $columnQuoteEnd = Connection::getRightColumnQuote($vendor);

        // Initializing structure for the SELECT part of query
        $this->_prepareMySQLSelectBlock();

        // Initializing structure for the WHERE part of query
        $this->_prepareMySQLWhereBlock($columnQuoteStart, $columnQuoteEnd);

        // Prepare the GROUP BY block
        $this->_prepareMySQLGroupByBlock($columnQuoteStart, $columnQuoteEnd);

        // Prepare the join rules
        $this->_prepareMySQLJoinedQueryStructure($columnQuoteStart, $columnQuoteEnd);

        // Prepare the query itself
        $this->_query = $this->_buildMySQLQuery($columnQuoteStart, $columnQuoteEnd);
    }


    /**
     * Generates and returns a WP based MySQL query based on user's visual input
     *
     * @param $tableData
     */
    public function generateWPBasedQuery($tableData)
    {
        global $wpdb;
        $this->_table_data = apply_filters('wdt_before_generate_wp_based_query', $tableData);

        if (!isset($this->_table_data['whereConditions'])) {
            $this->_table_data['whereConditions'] = array();
        }

        if (isset($this->_table_data['groupingRules'])) {
            $this->_has_groups = true;
        }

        $this->_tables_fields = self::generateTablesFieldsStructureWPBased($this->_table_data['postColumns']);

        // Initializing structure for the SELECT part of query
        $this->_prepareWPSelectBlock();

        // We need to go through the rest of where conditions and add 'inner join' parts for them if needed
        $this->_prepareWPWhereBlock();

        // We need to add 'GROUP BY' blocks
        $this->_prepareWPGroupByBlock();

        if (($this->_table_data['handlePostTypes'] == 'join')
            || count($this->_table_data['postTypes']) == 1
        ) {
            // We do JOINs
            $this->_prepareWPJoinedQueryStructure();

            $this->_query = $this->_buildWPJoinedQuery();

        } else {
            // We do UNIONs
            $this->_query = $this->_buildWPJoinedQuery();
        }

    }

    /**
     * Helper function to generate the fields structure from MySQL tables
     */
    private function _prepareMySQLSelectBlock()
    {

        foreach ($this->_table_data['mySqlColumns'] as $mysql_column) {

            $mysql_column_arr = explode('.', $mysql_column);
            if (!isset($this->_select_arr[$mysql_column_arr[0]])) {
                $this->_select_arr[$mysql_column_arr[0]] = array();
            }
            $this->_select_arr[$mysql_column_arr[0]][] = $mysql_column;

            if (!in_array($mysql_column_arr[0], $this->_from_arr)) {
                $this->_from_arr[] = $mysql_column_arr[0];
            }

        }

    }

    /**
     * Helper function to generate the fields structire from WP Posts data
     */
    public static function generateTablesFieldsStructureWPBased($columns)
    {
        $tables_fields = array();

        // Parse the columns list, generate table aliases and the columns
        foreach ($columns as $post_column) {
            $post_column_arr = explode('.', $post_column);

            if (count($post_column_arr) == 2) {
                // This is a column of a post table
                if (!isset($tables_fields[$post_column_arr[0]])) {
                    $tables_fields[$post_column_arr[0]] = array(
                        'table' => 'posts',
                        'post_type' => $post_column_arr[0],
                        'sql_alias' => self::prepareSqlAlias('posts_' . $post_column_arr[0]),
                        'columns' => array()
                    );
                }
                $tables_fields[$post_column_arr[0]]['columns'][] = array(
                    'field' => $post_column_arr[1],
                    'col_alias' => self::prepareSqlAlias($post_column),
                    'col_internal_name' => $post_column
                );
            } else {
                // This is a taxonomy or a meta value
                if ($post_column_arr[1] == 'meta') {
                    // This is a meta value
                    $tables_fields[$post_column_arr[2]] = array(
                        'table' => 'postmeta',
                        'sql_alias' => self::prepareSqlAlias($post_column . '_tbl'),
                        'col_alias' => self::prepareSqlAlias($post_column),
                        'col_internal_name' => $post_column,
                        'post_type' => $post_column_arr[0]
                    );
                } elseif ($post_column_arr[1] == 'taxonomy') {
                    // This is a taxonomy value
                    $tables_fields[$post_column_arr[2]] = array(
                        'table' => 'taxonomy',
                        'sql_alias' => self::prepareSqlAlias($post_column . '_tbl'),
                        'col_alias' => self::prepareSqlAlias($post_column),
                        'col_internal_name' => $post_column,
                        'post_type' => $post_column_arr[0]
                    );
                }
            }
        }

        return $tables_fields;

    }

    public static function prepareSqlAlias($alias)
    {

        $sqlAlias = str_replace('.', '_', $alias);
        $sqlAlias = str_replace('-', '_', $sqlAlias);

        return $sqlAlias;
    }

    public static function buildWhereCondition($leftOperand, $operator, $rightOperand, $isValue = true)
    {
        $rightOperand = stripslashes_deep($rightOperand);
        $wrap = $isValue ? "'" : "";
        switch ($operator) {
            case 'eq':
                return "{$leftOperand} = {$wrap}{$rightOperand}{$wrap}";
            case 'neq':
                return "{$leftOperand} != {$wrap}{$rightOperand}{$wrap}";
            case 'gt':
                return "{$leftOperand} > {$wrap}{$rightOperand}{$wrap}";
            case 'gtoreq':
                return "{$leftOperand} >= {$wrap}{$rightOperand}{$wrap}";
            case 'lt':
                return "{$leftOperand} < {$wrap}{$rightOperand}{$wrap}";
            case 'ltoreq':
                return "{$leftOperand} <= {$wrap}{$rightOperand}{$wrap}";
            case 'in':
                return "{$leftOperand} IN ({$rightOperand})";
            case 'like':
                return "{$leftOperand} LIKE {$wrap}{$rightOperand}{$wrap}";
            case 'plikep':
                return "{$leftOperand} LIKE {$wrap}%{$rightOperand}%{$wrap}";
        }
    }

    /**
     * Prepares the SELECT part for the WP-based tables
     */
    private function _prepareWPSelectBlock()
    {
        global $wpdb;

        if (empty($this->_tables_fields)) {
            return;
        }

        $thumbSizeString = self::getThumbSizeString();

        foreach ($this->_tables_fields as $valueName => &$fields) {
            // Fill in the SQL alias of the table
            $this->_table_aliases[] = $fields['sql_alias'];

            if ($fields['table'] == 'posts') {

                foreach ($fields['columns'] as $table_column) {
                    if (!isset($this->_select_arr[$fields['sql_alias']])) {
                        $this->_select_arr[$fields['sql_alias']] = array();
                    }

                    if ($table_column['field'] == 'title_with_link_to_post') {
                        // Generating an "<a href="..."" link to the post
                        $this->_select_arr[$fields['sql_alias']][] = 'CONCAT(\'<a href="\',' . $fields['sql_alias'] . '.guid,\'">\',' . $fields['sql_alias'] . '.post_title,\'</a>\') AS ' . $table_column['col_alias'];
                    } elseif ($table_column['field'] == 'thumbnail_with_link_to_post') {
                        // Generating an "<a href="..."" link to the post and a thumbnail URL depending on WP settings
                        $this->_select_arr[$fields['sql_alias'] . '_img'][] = 'CONCAT(
                                    \'<a href="\',
                                    ' . $fields['sql_alias'] . '.guid,
                                    \'"><img src="\', 
                                    REPLACE( 
                                        ' . $fields['sql_alias'] . '_img' . '.guid,
                                        CONCAT(
                                            \'.\',
                                            SUBSTRING_INDEX(  
                                                ' . $fields['sql_alias'] . '_img' . '.guid,
                                                \'.\',
                                                -1
                                            )
                                        ),
                                        CONCAT(
                                            \'' . $thumbSizeString . '\' ,
                                            SUBSTRING_INDEX(  
                                                ' . $fields['sql_alias'] . '_img' . '.guid,
                                                \'.\',
                                                -1
                                            )
                                        )
                                        ), 
                                    \'" /></a>\'
                                  ) AS ' . $table_column['col_alias'];
                        $this->_left_join_arr[$fields['sql_alias'] . '_img'] = '(SELECT ' . $fields['sql_alias'] . '_imgposts.guid AS guid, ' . $fields['sql_alias'] . '_imgpostmeta.post_id AS post_id
                                        FROM ' . $wpdb->postmeta . ' AS ' . $fields['sql_alias'] . '_imgpostmeta 
                                        INNER JOIN ' . $wpdb->posts . ' AS ' . $fields['sql_alias'] . '_imgposts 
                                            ON ' . $fields['sql_alias'] . '_imgpostmeta.meta_value = ' . $fields['sql_alias'] . '_imgposts.ID
                                        WHERE ' . $fields['sql_alias'] . "_imgpostmeta.meta_key = '_thumbnail_id' " .
                            ') AS ' . $fields['sql_alias'] . '_img';
                        $this->_where_arr[$fields['sql_alias'] . '_img'][] = $fields['sql_alias'] . '_img.post_id = ' . $fields['sql_alias'] . '.ID';
                    } elseif ($table_column['field'] == 'post_author') {
                        // Get the author nicename instead of ID
                        $this->_select_arr[$fields['sql_alias'] . '_author'][] = $fields['sql_alias'] . '_author.display_name AS ' . $table_column['col_alias'];
                        $this->_inner_join_arr[$fields['sql_alias'] . '_author'] = $wpdb->users . ' AS ' . $fields['sql_alias'] . '_author';
                        $this->_where_arr[$fields['sql_alias'] . '_author'][] = $fields['sql_alias'] . '_author.ID = ' . $fields['sql_alias'] . '.post_author';
                    } elseif ($table_column['field'] == 'post_content_limited_100_chars') {
                        // Get post content limited to 100 chars
                        $this->_select_arr[$fields['sql_alias']][] = 'LEFT( ' . $fields['sql_alias'] . '.post_content, 100) AS ' . $table_column['col_alias'];
                    } else {
                        $this->_select_arr[$fields['sql_alias']][] = $fields['sql_alias'] . '.' . $table_column['field'] . ' AS ' . $table_column['col_alias'];
                    }

                    $this->_column_aliases[$table_column['col_internal_name']] = $table_column['col_alias'];

                    // Look up for this column in additional 'where' conditions
                    foreach ($this->_table_data['whereConditions'] as $where_key => &$where_condition) {
                        $where_column_arr = explode('.', $where_condition['column']);
                        if ((count($where_column_arr) == 2)
                            && ($valueName == $where_column_arr[0])
                        ) {
                            if (!isset($this->_where_arr[$fields['sql_alias']])) {
                                $this->_where_arr[$fields['sql_alias']] = array();
                            }
                            $this->_where_arr[$fields['sql_alias']][] = self::buildWhereCondition(
                                'posts_' . $where_condition['column'],
                                $where_condition['operator'],
                                $where_condition['value']
                            );
                            unset($this->_table_data['whereConditions'][$where_key]);
                        }
                    }
                }
                $this->_from_arr[$fields['sql_alias']] = $wpdb->posts . ' AS ' . $fields['sql_alias'];
                if ($fields['post_type'] != 'all') {
                    $this->_where_arr[$fields['sql_alias']][] = $fields['sql_alias'] . ".post_type = '" . $fields['post_type'] . "'";
                }
            } elseif ($fields['table'] == 'postmeta') {
                if (!isset($this->_select_arr[$fields['sql_alias']])) {
                    $this->_select_arr[$fields['sql_alias']] = array();
                }
                if (!$this->_has_groups) {
                    $this->_select_arr[$fields['sql_alias']][] = $fields['sql_alias'] . '.meta_value AS ' . $fields['col_alias'];
                } else {
                    $this->_select_arr[$fields['sql_alias']][] = 'GROUP_CONCAT(distinct ' . $fields['sql_alias'] . '.meta_value) AS ' . $fields['col_alias'];
                }
                $this->_inner_join_arr[$fields['sql_alias']] = self::preparePostMetaSubquery($fields['sql_alias'], $fields['post_type']);

                if (!isset($this->_where_arr[$fields['sql_alias']])) {
                    $this->_where_arr[$fields['sql_alias']] = array();
                }
                $this->_where_arr[$fields['sql_alias']][] = $fields['sql_alias'] . ".meta_key = '" . $valueName . "' AND " . $fields['sql_alias'] . ".id = posts_" . $fields['post_type'] . ".ID ";

                $this->_column_aliases[$fields['col_internal_name']] = $fields['col_alias'];

                foreach ($this->_table_data['whereConditions'] as $where_key => &$where_condition) {
                    $where_column_arr = explode('.', $where_condition['column']);
                    if ((count($where_column_arr) == 3)
                        && ($where_column_arr[1] == 'meta')
                        && ($valueName == $where_column_arr[2])
                    ) {
                        if (!isset($this->_where_arr[$fields['sql_alias']])) {
                            $this->_where_arr[$fields['sql_alias']] = array();
                        }
                        $this->_where_arr[$fields['sql_alias']][] = self::buildWhereCondition(
                            $fields['col_alias'],
                            $where_condition['operator'],
                            $where_condition['value']
                        );
                        unset($this->_table_data['whereConditions'][$where_key]);
                    }
                }

            } elseif ($fields['table'] == 'taxonomy') {
                if (!isset($this->_select_arr[$fields['sql_alias']])) {
                    $this->_select_arr[$fields['sql_alias']] = array();
                }
                if (!$this->_has_groups) {
                    $this->_select_arr[$fields['sql_alias']][] = $fields['sql_alias'] . '.name AS ' . $fields['col_alias'];
                } else {
                    $this->_select_arr[$fields['sql_alias']][] = 'GROUP_CONCAT(distinct ' . $fields['sql_alias'] . '.name) AS ' . $fields['col_alias'];
                }
                $this->_inner_join_arr[$fields['sql_alias']] = self::preparePostTaxonomySubquery($fields['sql_alias'], $valueName);
                $this->_where_arr[$fields['sql_alias']][] = $fields['sql_alias'] . ".ID = posts_" . $fields['post_type'] . ".id ";

                $this->_column_aliases[$fields['col_internal_name']] = $fields['col_alias'];

                foreach ($this->_table_data['whereConditions'] as $where_key => &$where_condition) {
                    $where_column_arr = explode('.', $where_condition['column']);
                    if ((count($where_column_arr) == 3)
                        && ($where_column_arr[1] == 'taxonomy')
                        && ($valueName == $where_column_arr[2])
                    ) {
                        if (!isset($this->_where_arr[$fields['sql_alias']])) {
                            $this->_where_arr[$fields['sql_alias']] = array();
                        }
                        $this->_where_arr[$fields['sql_alias']][] = self::buildWhereCondition(
                            $fields['col_alias'],
                            $where_condition['operator'],
                            $where_condition['value']
                        );
                        unset($this->_table_data['whereConditions'][$where_key]);
                    }
                }
            }

        }

    }

    private function _prepareMySQLWhereBlock($columnQuoteStart, $columnQuoteEnd)
    {

        if (empty($this->_table_data['whereConditions'])) {
            return;
        }

        foreach ($this->_table_data['whereConditions'] as $where_condition) {

            $where_column_arr = explode('.', $where_condition['column']);

            if (!in_array($where_column_arr[0], $this->_from_arr)) {
                $this->_from_arr[] = $where_column_arr[0];
            }

            $this->_where_arr[$where_column_arr[0]][] = self::buildWhereCondition(
                $this->_quouteColumnNames([$where_condition['column']], $columnQuoteStart, $columnQuoteEnd)[0],
                $where_condition['operator'],
                $where_condition['value']
            );

        }

    }

    /**
     * Prepares the WHERE block for WP-based query
     */
    private function _prepareWPWhereBlock()
    {
        global $wpdb;

        if (empty($this->_table_data['whereConditions'])) {
            return;
        }

        foreach ($this->_table_data['whereConditions'] as $where_condition) {

            $where_column_arr = explode('.', $where_condition['column']);
            if (count($where_column_arr) == 2) {
                $tbl_alias = 'posts_' . $where_column_arr[0];
                $tbl_alias = str_replace('-', '_', $tbl_alias);
                $this->_from_arr[$tbl_alias] = $wpdb->posts . '_' . $where_column_arr[0] . ' AS ' . $tbl_alias;
                $this->_where_arr[$tbl_alias] = array();
                $this->_where_arr[$tbl_alias][] = self::buildWhereCondition(
                    $where_column_arr[1],
                    $where_condition['operator'],
                    $where_condition['value']
                );
            } else {
                if (count($where_column_arr) == 3) {
                    $tbl_alias = str_replace('.', '_', $where_condition['column']) . '_tbl';
                    $tbl_alias = str_replace('-', '_', $tbl_alias);
                    if ($where_column_arr[1] == 'meta') {
                        $this->_inner_join_arr[$tbl_alias] = self::preparePostMetaSubquery(
                            $tbl_alias,
                            $where_column_arr[0],
                            $where_column_arr[2]
                        );
                        $this->_where_arr[$tbl_alias] = array();
                        $this->_where_arr[$tbl_alias][] = self::buildWhereCondition(
                            $tbl_alias . '.meta_value',
                            $where_condition['operator'],
                            $where_condition['value']
                        );
                    } elseif ($where_column_arr[1] == 'taxonomy') {

                        $this->_inner_join_arr[$tbl_alias] = self::preparePostTaxonomySubquery($tbl_alias, $where_column_arr[2]);

                        $this->_where_arr[$tbl_alias] = array();
                        $this->_where_arr[$tbl_alias][] = self::buildWhereCondition(
                            $tbl_alias . '.name',
                            $where_condition['operator'],
                            $where_condition['value']
                        );
                    }
                    $this->_where_arr[$tbl_alias][] = self::buildWhereCondition(
                        $tbl_alias . '.id',
                        'eq',
                        'posts_' . $where_column_arr[0] . '.ID'
                    );

                }
            }
        }
    }

    public static function preparePostMetaSubquery($alias, $post_type, $meta_key = '')
    {
        global $wpdb;

        if (empty($alias) || empty($post_type)) {
            return '';
        }

        $post_meta_subquery = "(SELECT {$alias}_posts.ID as id, meta_value, meta_key ";
        $post_meta_subquery .= " FROM {$wpdb->postmeta} AS {$alias}_postmeta ";
        $post_meta_subquery .= " INNER JOIN {$wpdb->posts} AS {$alias}_posts ";
        $post_meta_subquery .= "  ON {$alias}_postmeta.post_id = {$alias}_posts.ID ";
        if (!empty($meta_key)) {
            $post_meta_subquery .= "  AND {$alias}_postmeta.meta_key = '{$meta_key}'";
        }
        $post_meta_subquery .= "  AND {$alias}_posts.post_type = '{$post_type}') AS {$alias}";

        return $post_meta_subquery;

    }

    /**
     * Prepare the query text for the WP based wpDataTable
     */
    private function _buildWPJoinedQuery()
    {

        // Build the final output
        $query = "SELECT ";
        $i = 0;
        foreach ($this->_select_arr as $table_alias => $select_block) {
            $query .= implode(",\n       ", $select_block);
            $i++;
            if ($i < count($this->_select_arr)) {
                $query .= ",\n       ";
            }
        }
        $query .= "\nFROM ";
        $query .= implode(', ', $this->_from_arr) . "\n";
        if (!empty($this->_inner_join_arr)) {
            $i = 0;
            foreach ($this->_inner_join_arr as $table_alias => $inner_join_block) {
                $query .= "  INNER JOIN " . $inner_join_block . "\n";
                if (!empty($this->_where_arr[$table_alias])) {
                    $query .= "     ON " . implode("\n     AND ", $this->_where_arr[$table_alias]) . "\n";
                    unset($this->_where_arr[$table_alias]);
                }
            }
        }
        if (!empty($this->_left_join_arr)) {

            foreach ($this->_left_join_arr as $table_alias => $left_join_block) {
                $query .= "  LEFT JOIN " . $left_join_block . "\n";
                if (!empty($this->_where_arr[$table_alias])) {
                    $query .= "     ON " . implode("\n     AND ", $this->_where_arr[$table_alias]) . "\n";
                    unset($this->_where_arr[$table_alias]);
                }
            }
        }
        if (!empty($this->_where_arr)) {
            $query .= "WHERE 1=1 \n   AND ";
            $i = 0;
            foreach ($this->_where_arr as $table_alias => $where_block) {
                $query .= implode("\n   AND ", $where_block);
                $i++;
                if ($i < count($this->_where_arr)) {
                    $query .= "\n   AND ";
                }
            }
        }
        if (!empty($this->_group_arr)) {
            $query .= "\nGROUP BY " . implode(', ', $this->_group_arr);
        }
        return $query;
    }

    /**
     * Prepares the structure of the JOIN rules for MySQL based tables
     */
    private function _prepareMySQLJoinedQueryStructure($columnQuoteStart, $columnQuoteEnd)
    {
        if (!isset($this->_table_data['joinRules'])) {
            return;
        }

        foreach ($this->_table_data['joinRules'] as $join_rule) {
            if (empty($join_rule['initiatorColumn'])
                || empty($join_rule['connectedColumn'])
            ) {
                continue;
            }

            $connected_column_arr = explode('.', $join_rule['connectedColumn']);

            if (in_array($connected_column_arr[0], $this->_from_arr)
                && count($this->_from_arr) > 1
            ) {
                if ($join_rule['type'] == 'left') {
                    $this->_left_join_arr[$connected_column_arr[0]] = $connected_column_arr[0];
                } else {
                    $this->_inner_join_arr[$connected_column_arr[0]] = $connected_column_arr[0];
                }
                unset($this->_from_arr[array_search($connected_column_arr[0], $this->_from_arr)]);
            } else {
                if ($join_rule['type'] == 'left') {
                    $this->_left_join_arr[$connected_column_arr[0]] = $connected_column_arr[0];
                } else {
                    $this->_inner_join_arr[$connected_column_arr[0]] = $connected_column_arr[0];
                }
            }



            $this->_where_arr[$connected_column_arr[0]][] = self::buildWhereCondition(
                $join_rule['initiatorTable'] . '.' . $columnQuoteStart . $join_rule['initiatorColumn'] . $columnQuoteEnd,
                'eq',
                $this->_quouteColumnNames([$join_rule['connectedColumn']], $columnQuoteStart, $columnQuoteEnd)[0],
                false
            );
        }

    }

    private function _quouteColumnNames($columnNames, $columnQuoteStart, $columnQuoteEnd) {
        return array_map(function ($value) use ($columnQuoteStart, $columnQuoteEnd) {
            $parts = explode('.', $value);
            return ($parts[0] . ".$columnQuoteStart" . $parts[1] . "$columnQuoteEnd");
        }, $columnNames);
    }

    /**
     * Prepares the query text for MySQL based table
     */
    private function _buildMySQLQuery($columnQuoteStart, $columnQuoteEnd)
    {

        // Build the final output
        $query = "SELECT ";
        $i = 0;
        foreach ($this->_select_arr as $table_alias => $select_block) {
            $query .= implode(",\n       ", $this->_quouteColumnNames($select_block, $columnQuoteStart, $columnQuoteEnd));
            $i++;
            if ($i < count($this->_select_arr)) {
                $query .= ",\n       ";
            }
        }
        $query .= "\nFROM ";
        $query .= implode(', ', $this->_from_arr) . "\n";
        if (!empty($this->_inner_join_arr)) {
            $i = 0;
            foreach ($this->_inner_join_arr as $table_alias => $inner_join_block) {
                $query .= "  INNER JOIN " . $inner_join_block . "\n";
                if (!empty($this->_where_arr[$table_alias])) {
                    $query .= "     ON " . implode("\n     AND ", $this->_where_arr[$table_alias]) . "\n";
                    unset($this->_where_arr[$table_alias]);
                }
            }
        }
        if (!empty($this->_left_join_arr)) {

            foreach ($this->_left_join_arr as $table_alias => $left_join_block) {
                $query .= "  LEFT JOIN " . $left_join_block . "\n";
                if (!empty($this->_where_arr[$table_alias])) {
                    $query .= "     ON " . implode("\n     AND ", $this->_where_arr[$table_alias]) . "\n";
                    unset($this->_where_arr[$table_alias]);
                }
            }
        }
        if (!empty($this->_where_arr)) {
            $query .= "WHERE 1=1 \n   AND ";
            $i = 0;
            foreach ($this->_where_arr as $table_alias => $where_block) {
                $query .= implode("\n   AND ", $where_block);
                $i++;
                if ($i < count($this->_where_arr)) {
                    $query .= "\n   AND ";
                }
            }
        }
        if (!empty($this->_group_arr)) {
            $query .= "\nGROUP BY " . implode(', ', $this->_group_arr);
        }
        return $query;

    }

    /**
     * Prepares the Joined query structure for WP-based wpDataTables
     */
    private function _prepareWPJoinedQueryStructure()
    {
        global $wpdb;

        if (!isset($this->_table_data['joinRules'])) {
            return;
        }

        // Need to go through each post type and define the join rule
        foreach ($this->_table_data['joinRules'] as $join_rule) {
            if (empty($join_rule['initiatorColumn'])
                || empty($join_rule['connectedColumn'])
            ) {
                continue;
            }

            $connected_column_arr = explode('.', $join_rule['connectedColumn']);
            if (count($connected_column_arr) == 2) {
                // Joining by posts table column

                $tbl_alias = self::prepareSqlAlias('posts_' . $connected_column_arr[0]);

                if (!isset($this->_where_arr[$tbl_alias])) {
                    $this->_where_arr[$tbl_alias] = array();
                }
                if (isset($this->_from_arr[$tbl_alias])
                    && count($this->_from_arr) > 1
                ) {
                    if ($join_rule['type'] == 'left') {
                        $this->_left_join_arr[$tbl_alias] = $this->_from_arr[$tbl_alias];
                    } else {
                        $this->_inner_join_arr[$tbl_alias] = $this->_from_arr[$tbl_alias];
                    }
                    unset($this->_from_arr[$tbl_alias]);
                } else {
                    if ($join_rule['type'] == 'left') {
                        $this->_left_join_arr[$tbl_alias] = $wpdb->posts . ' AS ' . $tbl_alias;
                    } else {
                        $this->_inner_join_arr[$tbl_alias] = $wpdb->posts . ' AS ' . $tbl_alias;
                    }
                    $this->_where_arr[$tbl_alias][] = $tbl_alias . ".post_type = '" . $connected_column_arr[0] . "'";
                }

                $this->_where_arr[$tbl_alias][] = self::buildWhereCondition(
                    str_replace('-', '_', 'posts_' . $join_rule['connectedColumn']),
                    'eq',
                    self::prepareSqlAlias('posts_' . $join_rule['initiatorPostType'])
                    . '.' . self::prepareSqlAlias($join_rule['initiatorColumn']),
                    false
                );
            } else {
                if ($connected_column_arr[1] == 'meta') {
                    // joining by a meta value
                    $tbl_alias = self::prepareSqlAlias($join_rule['connectedColumn'] . '_tbl');

                    if (!isset($this->_where_arr[$tbl_alias])) {
                        $this->_where_arr[$tbl_alias] = array();
                    }
                    if (isset($this->_from_arr[$tbl_alias])
                        && count($this->_from_arr) > 1
                    ) {
                        if ($join_rule['type'] == 'left') {
                            $this->_left_join_arr[$tbl_alias] = $this->_from_arr[$tbl_alias];
                        } else {
                            $this->_inner_join_arr[$tbl_alias] = $this->_from_arr[$tbl_alias];
                        }
                        unset($this->_from_arr[$tbl_alias]);
                    } elseif (!isset($this->_inner_join_arr[$tbl_alias]) && !isset($this->_left_join_arr[$tbl_alias])) {
                        $rule = self::preparePostMetaSubquery($tbl_alias, $connected_column_arr[0], $connected_column_arr[2]);
                        $this->_where_arr[$tbl_alias] = array();
                        if ($join_rule['type'] == 'left') {
                            $this->_left_join_arr[$tbl_alias] = $rule;
                            if (isset($this->_from_arr['posts_' . $connected_column_arr[0]]) && count($this->_from_arr) > 1) {
                                $this->_left_join_arr['posts_' . $connected_column_arr[0]] = $this->_from_arr['posts_' . $connected_column_arr[0]];
                                unset($this->_from_arr['posts_' . $connected_column_arr[0]]);
                            } else {
                                $this->_left_join_arr['posts_' . $connected_column_arr[0]] = $wpdb->posts . 'AS posts_' . $connected_column_arr[0];
                            }
                        } else {
                            $this->_inner_join_arr[$tbl_alias] = $rule;
                            if (isset($this->_from_arr['posts_' . $connected_column_arr[0]]) && count($this->_from_arr) > 1) {
                                $this->_inner_join_arr['posts_' . $connected_column_arr[0]] = $this->_from_arr['posts_' . $connected_column_arr[0]];
                                unset($this->_from_arr['posts_' . $connected_column_arr[0]]);
                            } else {
                                $this->_inner_join_arr['posts_' . $connected_column_arr[0]] = $wpdb->posts . 'AS posts_' . $connected_column_arr[0];
                            }
                        }
                    }
                    $this->_where_arr[$tbl_alias][] = self::buildWhereCondition(
                        $tbl_alias . '.meta_value',
                        'eq',
                        'posts_' . $join_rule['initiatorPostType']
                        . '.' . $join_rule['initiatorColumn'],
                        false
                    );

                } elseif ($connected_column_arr[1] == 'taxonomy') {
                    // joining by taxonomy

                    $tbl_alias = self::prepareSqlAlias($join_rule['connectedColumn'] . '_tbl');

                    if (!isset($this->_where_arr[$tbl_alias])) {
                        $this->_where_arr[$tbl_alias] = array();
                    }
                    if (isset($this->_from_arr[$tbl_alias])
                        && count($this->_from_arr) > 1
                    ) {
                        if ($join_rule['type'] == 'left') {
                            $this->_left_join_arr[$tbl_alias] = $this->_from_arr[$tbl_alias];
                        } else {
                            $this->_inner_join_arr[$tbl_alias] = $this->_from_arr[$tbl_alias];
                        }
                        unset($this->_from_arr[$tbl_alias]);
                    } elseif (!isset($this->_inner_join_arr[$tbl_alias]) && !isset($this->_left_join_arr[$tbl_alias])) {
                        $rule = self::preparePostTaxonomySubquery($tbl_alias, $connected_column_arr[2]);

                        $this->_where_arr[$tbl_alias] = array();
                        $this->_where_arr[$tbl_alias][] = self::buildWhereCondition(
                            $tbl_alias . '.name',
                            'eq',
                            $where_condition['value']
                        );
                        $this->_where_arr[$tbl_alias][] = self::buildWhereCondition(
                            $tbl_alias . '.id',
                            'eq',
                            'posts_' . $where_column_arr[0] . '.ID',
                            false
                        );
                        if ($join_rule['type'] == 'left') {
                            $this->_left_join_arr[$tbl_alias] = $rule;
                            if (isset($this->_from_arr['posts_' . $connected_column_arr[0]]) && count($this->_from_arr) > 1) {
                                $this->_left_join_arr['posts_' . $connected_column_arr[0]] = $this->_from_arr['posts_' . $connected_column_arr[0]];
                                unset($this->_from_arr['posts_' . $connected_column_arr[0]]);
                            } else {
                                $this->_left_join_arr['posts_' . $connected_column_arr[0]] = $wpdb->posts . 'AS posts_' . $connected_column_arr[0];
                            }
                        } else {
                            $this->_inner_join_arr[$tbl_alias] = $rule;
                            if (isset($this->_from_arr['posts_' . $connected_column_arr[0]]) && count($this->_from_arr) > 1) {
                                $this->_inner_join_arr['posts_' . $connected_column_arr[0]] = $this->_from_arr['posts_' . $connected_column_arr[0]];
                                unset($this->_from_arr['posts_' . $connected_column_arr[0]]);
                            } else {
                                $this->_inner_join_arr['posts_' . $connected_column_arr[0]] = $wpdb->posts . 'AS posts_' . $connected_column_arr[0];
                            }
                        }
                    }
                    $this->_where_arr[$tbl_alias][] = self::buildWhereCondition(
                        $tbl_alias . '.meta_value',
                        'eq',
                        'posts_' . $join_rule['initiatorPostType']
                        . '.' . $join_rule['initiatorColumn'],
                        false
                    );

                }
            }
        }
    }

    /**
     * Prepare a GROUP BY block for MySQL based wpDataTables
     */
    private function _prepareMySQLGroupByBlock($columnQuoteStart, $columnQuoteEnd)
    {
        if (!$this->_has_groups) {
            return;
        }

        foreach ($this->_table_data['groupingRules'] as $grouping_rule) {
            if (empty($grouping_rule)) {
                continue;
            }
            $this->_group_arr[] = $this->_quouteColumnNames([$grouping_rule], $columnQuoteStart, $columnQuoteEnd)[0];
        }

    }

    /**
     * Prepare a GROUP BY block for WP based wpDataTables
     */
    private function _prepareWPGroupByBlock()
    {
        if (!$this->_has_groups) {
            return;
        }

        foreach ($this->_table_data['groupingRules'] AS $grouping_rule) {
            if (empty($grouping_rule)) {
                continue;
            }
            $this->_group_arr[] = $this->_column_aliases[$grouping_rule];
        }

    }

    public static function preparePostTaxonomySubquery($alias, $taxonomy)
    {
        global $wpdb;

        if (empty($alias) || empty($taxonomy)) {
            return '';
        }

        $taxonomy_subquery = "(SELECT name, object_id as id";
        $taxonomy_subquery .= " FROM {$wpdb->terms} AS {$alias}_terms";
        $taxonomy_subquery .= " INNER JOIN {$wpdb->term_taxonomy} AS {$alias}_termtaxonomy";
        $taxonomy_subquery .= " ON {$alias}_termtaxonomy.term_id = {$alias}_terms.term_id ";
        $taxonomy_subquery .= " AND {$alias}_termtaxonomy.taxonomy = '{$taxonomy}'";
        $taxonomy_subquery .= " INNER JOIN {$wpdb->term_relationships} AS rel_{$alias}";
        $taxonomy_subquery .= "  ON {$alias}_termtaxonomy.term_taxonomy_id = rel_{$alias}.term_taxonomy_id";
        $taxonomy_subquery .= ") AS {$alias}";

        return $taxonomy_subquery;

    }

    public function setQuery($query)
    {
        $this->_query = wdtSanitizeQuery($query);
    }

    public function getQuery()
    {
        return $this->_query;
    }

    public function getQueryPreview($connection = null)
    {

        if (Connection::isSeparate($connection)) {
            $sql = Connection::create($connection);

            $vendor = Connection::getVendor($connection);
            $isMySql = $vendor === Connection::$MYSQL;
            $isMSSql = $vendor === Connection::$MSSQL;
            $isPostgreSql = $vendor === Connection::$POSTGRESQL;

            if ($isMySql) {
                $query = $this->_query . " LIMIT 5";
            }

            if ($isMSSql) {
                $query = $this->_query . " ORDER BY(SELECT NULL) OFFSET 0 ROWS FETCH NEXT 5 ROWS ONLY";
            }

            if ($isPostgreSql) {
                $query = $this->_query . " LIMIT 5";
            }

            $result = $sql->getAssoc($query, array());
        } else {
            global $wpdb;
            $wpdb->hide_errors();
            $result = $wpdb->get_results($this->_query . ' LIMIT 5', ARRAY_A);

        }

        if (!empty($result)) {
            ob_start();
            include(WDT_TEMPLATE_PATH . '/admin/constructor/constructor_table_preview.inc.php');
            $ret_val = ob_get_contents();
            ob_end_clean();
        } else {
            $ret_val = __('<div class="alert alert-danger"><i class="wpdt-icon-exclamation-triangle"></i>No results found. Please check if this query is correct! Table Constructor needs a query that returns data to build a wpDataTable.', 'wpdatatables');
            if (!(Connection::isSeparate($connection)) && !empty($wpdb->last_error)) {
                $ret_val .= '<br>Error: ' . $wpdb->last_error . '</div>';
            }
        }
        return $ret_val;
    }

    /**
     * Returns the ending of the thumbnail URL string
     *
     * @return string
     */
    public static function getThumbSizeString()
    {
        return '-' . get_option('thumbnail_size_w') . 'x' . get_option('thumbnail_size_h') . '.';
    }

    /**
     * Generates a wpDataTable based on WP data query
     */
    public function generateWdtBasedOnQuery($tableData)
    {
        global $wpdb;

        $tableData['query'] = wdtSanitizeQuery(($tableData['query']));

        $table_array = array(
            'title' =>  sanitize_text_field($tableData['name']),
            'table_type' => 'mysql',
            'connection' => $tableData['connection'],
            'content' => '',
            'filtering' => 1,
            'filtering_form' => 0,
            'sorting' => 1,
            'fixed_layout' => 0,
            'responsive' => 0,
            'word_wrap' => 1,
            'tools' => 1,
            'display_length' => 10,
            'fixed_columns' => 0,
            'server_side' => 1,
            'editable' => 0,
            'editor_roles' => '',
            'mysql_table_name' => '',
            'hide_before_load' => 1,
            'tabletools_config' => serialize(array(
                'print' => 1,
                'copy' => 1,
                'excel' => 1,
                'csv' => 1,
                'pdf' => 0
            ))
        );

        $table_array['content'] = $tableData['query'];

        $res = WDTConfigController::tryCreateTable('mysql', $table_array['content'], $tableData['connection']);

        if (empty($res->error)) {
            // get the newly generated table ID
            $wpdb->insert($wpdb->prefix . 'wpdatatables', $table_array);
            $tableId = $wpdb->insert_id;
            $res->table_id = $tableId;
            WDTConfigController::saveColumns(null, $res->table, $res->table_id);
            $res->columns = WDTConfigController::getColumnsConfig($tableId);
            do_action('wpdatatables_after_save_table', $tableId);
        }

        return $res;

    }


    /**
     * Returns a list of tables in the chosen DB
     *
     * @return array
     */
    public static function listMySQLTables($connection)
    {

        $tables = array();

        if (Connection::isSeparate($connection)) {
            try {
                $sql = Connection::create($connection);
            } catch (Exception $ex) {
                return $tables;
            }

            $vendor = Connection::getVendor($connection);
            $isMySql = $vendor === Connection::$MYSQL;
            $isMSSql = $vendor === Connection::$MSSQL;
            $isPostgreSql = $vendor === Connection::$POSTGRESQL;

            if ($isMySql) {
                $query = 'SHOW TABLES';
            }

            if ($isMSSql) {
                $query = 'SELECT table_name FROM INFORMATION_SCHEMA.TABLES';
            }

            if ($isPostgreSql) {
                $query = "SELECT tablename FROM pg_catalog.pg_tables where schemaname = 'public'";
            }

            $result = $sql->getArray($query, array());
            if (empty($result)) {
                return $tables;
            }
        } else {
            global $wpdb;
            $result = $wpdb->get_results('SHOW TABLES', ARRAY_N);
        }
        // Formatting the result to plain array
        foreach ($result as $row) {
            $tables[] = $row[0];
        }

        return $tables;

    }

    /**
     * Return a list of columns for the selected tables
     */
    public static function listMySQLColumns($tables, $connection)
    {
        $columns = array('allColumns' => array(), 'sortedColumns' => array());
        if (!empty($tables)) {
            if (Connection::isSeparate($connection)) {
                try {
                    $sql = Connection::create($connection);
                } catch (Exception $ex) {
                    return $columns;
                }
                foreach ($tables as $table) {
                    $columns['sortedColumns'][$table] = array();

                    $vendor = Connection::getVendor($connection);
                    $isMySql = $vendor === Connection::$MYSQL;
                    $isMSSql = $vendor === Connection::$MSSQL;
                    $isPostgreSql = $vendor === Connection::$POSTGRESQL;

                    if ($isMySql) {
                        $columns_query = "SHOW COLUMNS FROM {$table}";
                    }

                    if ($isMSSql) {
                        $columns_query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$table}'";
                    }

                    if ($isPostgreSql) {
                        $columns_query = "SELECT * FROM information_schema.columns WHERE table_schema = 'public' AND table_name   = '{$table}'";
                    }


                    $table_columns = $sql->getAssoc($columns_query);
                    foreach ($table_columns as $table_column) {
                        if ($isMySql) {
                            $columns['sortedColumns'][$table][] = "{$table}.{$table_column['Field']}";
                            $columns['allColumns'][] = "{$table}.{$table_column['Field']}";
                        }

                        if ($isMSSql) {
                            $columns['sortedColumns'][$table][] = "{$table}.{$table_column['COLUMN_NAME']}";
                            $columns['allColumns'][] = "{$table}.{$table_column['COLUMN_NAME']}";
                        }

                        if ($isPostgreSql) {
                            $columns['sortedColumns'][$table][] = "{$table}.{$table_column['column_name']}";
                            $columns['allColumns'][] = "{$table}.{$table_column['column_name']}";
                        }
                    }
                }
            } else {
                global $wpdb;
                foreach ($tables as $table) {
                    $columns['sortedColumns'][$table] = array();
                    $table_columns = $wpdb->get_results("SHOW COLUMNS FROM {$table};", ARRAY_A);
                    foreach ($table_columns as $table_column) {
                        $columns['sortedColumns'][$table][] = "{$table}.{$table_column['Field']}";
                        $columns['allColumns'][] = "{$table}.{$table_column['Field']}";
                    }
                }
            }
        }

        return $columns;
    }

    /**
     * Generates a table based on the provided file and shows a preview
     *
     * @param $tableData
     * @return array
     * @throws WDTException
     */
    public function previewFileTable($tableData)
    {

        if (!empty($tableData['file'])) {
            $xls_url = urldecode(esc_url($tableData['file']));
            $uploads_dir = wp_upload_dir();
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $xls_url = str_replace($uploads_dir['baseurl'], str_replace('\\', '/', $uploads_dir['basedir']), $xls_url);
            } else {
                $xls_url = str_replace($uploads_dir['baseurl'], $uploads_dir['basedir'], $xls_url);
            }
        } else {
            return array('result' => 'error', 'message' => __('Empty file', 'wpdatatables'));
        }

        if (strpos(strtolower($xls_url), 'https://docs.google.com/spreadsheets') !== false) {
            // Preview from Google Spreadsheet
            $credentials = get_option('wdtGoogleSettings');
            $token = get_option('wdtGoogleToken');
            if ($credentials) {
                $googleSheet = new WPDataTable_Google_Sheet();
                $namedDataArray = $googleSheet->getData($xls_url, $credentials, $token);
            } else {
                $namedDataArray = WDTTools::extractGoogleSpreadsheetArray($xls_url);
            }
            if (!empty($namedDataArray)) {
                $headingsArray = array_keys($namedDataArray[0]);
                $namedDataArray = array_slice($namedDataArray, 0, 4);
            } else {
                return array(
                    'result' => 'error',
                    'message' => __(
                        'Could not read Google spreadsheet, please check if the URL is correct and the spreadsheet is published to everyone',
                        'wpdatatables'
                    )
                );
            }
        } else {
            if (strpos(strtolower($xls_url), '.xlsx')) {
                $objReader = new Xlsx();
            } elseif (strpos(strtolower($xls_url), '.xls')) {
                $objReader = new Xls();
            } elseif (strpos(strtolower($xls_url), '.ods')) {
                $objReader = new Ods();
            } elseif (strpos(strtolower($xls_url), '.csv')) {
                $objReader = new Csv();
                $csvDelimiter = get_option('wdtCSVDelimiter') ? get_option('wdtCSVDelimiter') : WDTTools::detectCSVDelimiter($xls_url);
                $objReader->setDelimiter($csvDelimiter);
            } else {
                return array('result' => 'error', 'message' => __('Could not read input file!', 'wpdatatables'));
            }

            $objPHPExcel = $objReader->load($xls_url);
            $objWorksheet = $objPHPExcel->getActiveSheet();
            $highestRow = $objWorksheet->getHighestRow();
            $highestRow = $highestRow > 5 ? 5 : $highestRow;
            $highestColumn = $objWorksheet->getHighestDataColumn();

            $headingsArray = $objWorksheet->rangeToArray('A1:' . $highestColumn . '1', null, true, true, true);
            $headingsArray = array_map('trim', $headingsArray[1]);

            $r = 0;
            $namedDataArray = array();
            $dataRows = $objWorksheet->rangeToArray('A2:' . $highestColumn . $highestRow, null, true, true, true);
            for ($row = 2; $row <= $highestRow; ++$row) {
                if (max($dataRows[$row]) !== null) {
                    ++$r;
                    foreach ($headingsArray as $dataColumnIndex => $dataColumnHeading) {
                        $dataColumnHeading = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataColumnHeading)));
                        $namedDataArray[$r][$dataColumnHeading] = $dataRows[$row][$dataColumnIndex];
                    }
                }
            }
        }


        /** @noinspection PhpUnusedLocalVariableInspection */
        $columnTypeArray = WDTTools::detectColumnDataTypes($namedDataArray, $headingsArray);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $possibleColumnTypes = WDTTools::getPossibleColumnTypes();

        $ret_val = '';

        if (!empty($namedDataArray)) {
            ob_start();
            include(WDT_TEMPLATE_PATH . 'admin/constructor/constructor_file_preview.inc.php');
            $ret_val = ob_get_contents();
            ob_end_clean();
        }

        return array('result' => 'success', 'message' => $ret_val);

    }

    /**
     * Reads the data from file in the DB and generates a wpDataTable
     *
     * @param $tableData
     * @return mixed|string
     * @throws WDTException
     * @throws Exception
     */
    public function readFileData($tableData)
    {
        $columnTypes = array();
        $columnDateInputFormat = array();
        $numberFormat = get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1;

        if (!empty($tableData['file'])) {
            $xls_url = urldecode(esc_url($tableData['file']));
            $uploads_dir = wp_upload_dir();
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $xls_url = str_replace($uploads_dir['baseurl'], str_replace('\\', '/', $uploads_dir['basedir']), $xls_url);
            } else {
                $xls_url = str_replace($uploads_dir['baseurl'], $uploads_dir['basedir'], $xls_url);
            }
        } else {
            return _('Empty file', 'wpdatatables');
        }

        for ($i = 0; $i < count($tableData['columns']); $i++) {
            if ($tableData['columns'][$i]['orig_header'] == '%%NEW_COLUMN%%') {
                $tableData['columns'][$i]['orig_header'] = 'column' . $i;
            }
            $columnTypes[$tableData['columns'][$i]['orig_header']] = sanitize_text_field($tableData['columns'][$i]['type']);
            $columnDateInputFormat[$tableData['columns'][$i]['orig_header']] = sanitize_text_field($tableData['columns'][$i]['dateInputFormat']);
        }

        $this->_id = $this->generateManualTable($tableData);

        if (strpos(strtolower($xls_url), 'https://docs.google.com/spreadsheets') !== false) {
            $table_type = 'google';
            $credentials = get_option('wdtGoogleSettings');
            $token = get_option('wdtGoogleToken');
            if ($credentials) {
                $googleSheet = new WPDataTable_Google_Sheet();
                $namedDataArray = $googleSheet->getData($xls_url, $credentials, $token);
            } else {
                $namedDataArray = WDTTools::extractGoogleSpreadsheetArray($xls_url);
            }
            $headingsArray = array_keys($namedDataArray[0]);
            $highestRow = count($namedDataArray) - 1;
        } else {
            $table_type = 'excel';
            if (strpos(strtolower($xls_url), '.xlsx')) {
                $objReader = new Xlsx();
            } elseif (strpos(strtolower($xls_url), '.xls')) {
                $objReader = new Xls();
            } elseif (strpos(strtolower($xls_url), '.ods')) {
                $objReader = new Ods();
            } elseif (strpos(strtolower($xls_url), '.csv')) {
                $objReader = new Csv();
                $csvDelimiter = get_option('wdtCSVDelimiter') ? get_option('wdtCSVDelimiter') : WDTTools::detectCSVDelimiter($xls_url);
                $objReader->setDelimiter($csvDelimiter);
            } else {
                return _('File format not supported!', 'wpdatatables');
            }

            $objPHPExcel = $objReader->load($xls_url);
            $objWorksheet = $objPHPExcel->getActiveSheet();
            $highestRow = $objWorksheet->getHighestRow();
            $highestColumn = $objWorksheet->getHighestDataColumn();

            $headingsArray = $objWorksheet->rangeToArray('A1:' . $highestColumn . '1', null, true, true, true);
            $headingsArray = array_map('trim', $headingsArray[1]);
        }

        $r = -1;


        $this->_column_headers = apply_filters('wpdt_insert_additional_column_header', $this->_column_headers);

        $vendor = Connection::getVendor($tableData['connection']);

        $columnQuoteStart = Connection::getLeftColumnQuote($vendor);
        $columnQuoteEnd = Connection::getRightColumnQuote($vendor);

        // Insert statement default beginning
        $insert_statement_beginning = "INSERT INTO "
            . $this->_name . " ("
            . implode(
                ', ',
                array_map(
                    function ($header) use ($columnQuoteStart, $columnQuoteEnd) {
                        return "{$columnQuoteStart}{$header}{$columnQuoteEnd}";
                    },
                    array_values($this->_column_headers)
                )
            )
            . ") ";
        $insert_blocks = array();
        if ($table_type != 'google') {
            $dataRows = $objWorksheet->rangeToArray('A2:' . $highestColumn . $highestRow, null, true, true, true);
        }
        for ($row = 0; $row <= $highestRow; ++$row) {

            $insertArray = array();

            if (($row <= 1) && ($table_type == 'excel')) {
                continue;
            }

            if ($table_type == 'google') {

                // Set all cells in the row to their defaults
                foreach ($tableData['columns'] as $column) {
                    $insertArray[$this->_column_headers[$column['orig_header']]] = "'" . sanitize_text_field($column['default_value']) . "'";
                }

                foreach ($headingsArray as $dataColumnIndex => $dataColumnHeading) {

                    $dataColumnHeading = addslashes($dataColumnHeading);

                    if (!in_array($dataColumnHeading, array_keys($this->_column_headers))) {
                        continue;
                    }

                    if (in_array($columnTypes[$dataColumnHeading], array('date', 'datetime', 'time'))) {
                        if ($columnTypes[$dataColumnHeading] == 'date') {
                            $date = WDTTools::wdtConvertStringToUnixTimestamp($namedDataArray[$row][$dataColumnHeading], $columnDateInputFormat[$dataColumnHeading]);
                            $insertArray[$this->_column_headers[$dataColumnHeading]] = "'" . date('Y-m-d', $date) . "'";
                        } elseif ($columnTypes[$dataColumnHeading] == 'datetime') {
                            $date = strtotime($namedDataArray[$row][$dataColumnHeading]);
                            $insertArray[$this->_column_headers[$dataColumnHeading]] = "'" . date('Y-m-d H:i:s', $date) . "'";
                        } elseif ($columnTypes[$dataColumnHeading] == 'time') {
                            $insertArray[$this->_column_headers[$dataColumnHeading]] = "'" . date('H:i:s', strtotime($namedDataArray[$row][$dataColumnHeading])) . "'";
                        }
                    } elseif ($columnTypes[$dataColumnHeading] == 'float') {
                        if ($numberFormat == 1) {
                            $insertArray[$this->_column_headers[$dataColumnHeading]] = "'" . esc_sql(str_replace(',', '.', str_replace('.', '', $namedDataArray[$row][$dataColumnHeading]))) . "'";
                        } else {
                            $insertArray[$this->_column_headers[$dataColumnHeading]] = "'" . esc_sql(str_replace(',', '', $namedDataArray[$row][$dataColumnHeading])) . "'";
                        }
                    } elseif ($columnTypes[$dataColumnHeading] == 'int') {
                        if ($numberFormat == 1) {
                            $insertArray[$this->_column_headers[$dataColumnHeading]] = "'" . esc_sql(str_replace('.', '', $namedDataArray[$row][$dataColumnHeading])) . "'";
                        } else {
                            $insertArray[$this->_column_headers[$dataColumnHeading]] = "'" . esc_sql(str_replace(',', '', $namedDataArray[$row][$dataColumnHeading])) . "'";
                        }
                    } else {
                        $insertArray[$this->_column_headers[$dataColumnHeading]] = "'" . esc_sql($namedDataArray[$row][$dataColumnHeading]) . "'";
                    }
                }
            } else {
                if (max($dataRows[$row]) !== null) {
                    // Set all cells in the row to their defaults
                    foreach ($tableData['columns'] as $column) {
                        $insertArray[$this->_column_headers[$column['orig_header']]] = "'" . sanitize_text_field($column['default_value']) . "'";
                    }
                    ++$r;
                    foreach ($headingsArray as $dataColumnIndex => $dataColumnHeading) {
                        $dataColumnHeading = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataColumnHeading)));
                        $dataColumnHeading = addslashes($dataColumnHeading);

                        if (!in_array($dataColumnHeading, array_keys($this->_column_headers))) {
                            continue;
                        }

                        if (in_array($columnTypes[$dataColumnHeading], array('date', 'datetime', 'time'))) {
                            if ($objReader instanceof PHPExcel_Reader_CSV) {
                                $date = WDTTools::wdtConvertStringToUnixTimestamp($dataRows[$row][$dataColumnIndex], $columnDateInputFormat[$dataColumnHeading]);
                            } else {
                                if ($dataRows[$row][$dataColumnIndex] == null) {
                                    $date = null;
                                } else {
                                    $cell = $objPHPExcel->getActiveSheet()->getCell($dataColumnIndex . '' . $row);
                                    if (Date::isDateTime($cell)) {
                                        $date = Date::excelToTimestamp($cell->getValue());
                                    } else {
                                        $date = WDTTools::wdtConvertStringToUnixTimestamp($dataRows[$row][$dataColumnIndex], $columnDateInputFormat[$dataColumnHeading]);
                                    }
                                }
                            }

                            if ($columnTypes[$dataColumnHeading] == 'date') {
                                $insertArray[$this->_column_headers[$dataColumnHeading]] = ($date == null) ? 'NULL' : "'" . date('Y-m-d', $date) . "'";
                            } elseif ($columnTypes[$dataColumnHeading] == 'datetime') {
                                $insertArray[$this->_column_headers[$dataColumnHeading]] = ($date == null) ? 'NULL' : "'" . date('Y-m-d H:i:s', $date) . "'";
                            } elseif ($columnTypes[$dataColumnHeading] == 'time') {
                                $insertArray[$this->_column_headers[$dataColumnHeading]] = ($date == null) ? 'NULL' : "'" . date('H:i:s', $date) . "'";
                            }
                        } elseif ($columnTypes[$dataColumnHeading] == 'float' && gettype($dataRows[$row][$dataColumnIndex]) === 'string') {
                            if ($numberFormat == 1) {
                                $insertArray[$this->_column_headers[$dataColumnHeading]] = $dataRows[$row][$dataColumnIndex] !== null ? "'" . esc_sql(str_replace(',', '.', str_replace('.', '', $dataRows[$row][$dataColumnIndex]))) . "'" : 'NULL';
                            } else {
                                $insertArray[$this->_column_headers[$dataColumnHeading]] = $dataRows[$row][$dataColumnIndex] !== null ? "'" . esc_sql(str_replace(',', '', $dataRows[$row][$dataColumnIndex])) . "'" : 'NULL';
                            }
                        } elseif ($columnTypes[$dataColumnHeading] == 'int') {
                            if ($numberFormat == 1) {
                                $insertArray[$this->_column_headers[$dataColumnHeading]] = $dataRows[$row][$dataColumnIndex] !== null ? "'" . esc_sql(str_replace('.', '', $dataRows[$row][$dataColumnIndex])) . "'" : 'NULL';
                            } else {
                                $insertArray[$this->_column_headers[$dataColumnHeading]] = $dataRows[$row][$dataColumnIndex] !== null ? "'" . esc_sql(str_replace(',', '', $dataRows[$row][$dataColumnIndex])) . "'" : 'NULL';
                            }
                        } else {
                            if ($columnTypes[$dataColumnHeading] === 'float') {
                                $insertArray[$this->_column_headers[$dataColumnHeading]] = $dataRows[$row][$dataColumnIndex] !== null ? "'" . esc_sql($dataRows[$row][$dataColumnIndex]) . "'" : 'NULL';
                            } else {
                                $insertArray[$this->_column_headers[$dataColumnHeading]] =  "'" . esc_sql($dataRows[$row][$dataColumnIndex]) . "'";
                            }
                        }

                    }
                }
            }

            $insertArray = apply_filters('wpdt_insert_additional_column_value', $insertArray, $row, $table_type);

            if (!empty($insertArray)) {
                $insert_blocks[] = '(' . implode(', ', $insertArray) . ')';
            }

            if ($row % 100 == 0) {
                $this->insertRowsChunk($insert_statement_beginning, $insert_blocks, $tableData['connection']);
                $insert_blocks = array();
            }

        }

        $this->insertRowsChunk($insert_statement_beginning, $insert_blocks, $tableData['connection']);

    }

    /**
     * Inserts a blocks of rows read from file
     *
     * @param $insert_statement_beginning
     * @param $insert_blocks
     */
    private function insertRowsChunk($insert_statement_beginning, $insert_blocks, $connection = null)
    {
        global $wpdb;

        if (count($insert_blocks) > 0) {
            $insert_statement = $insert_statement_beginning . " VALUES " . implode(', ', $insert_blocks);
            if (Connection::isSeparate($connection)) {
                // External DB
                $this->_db->doQuery($insert_statement, array());
            } else {
                $wpdb->query($insert_statement);
            }
        }
    }

    /**
     * Delete a column from a manually generated table
     *
     * @param $tableId
     * @param $columnName
     */
    public static function deleteManualColumn($tableId, $columnName)
    {
        global $wpdb;

        $tableData = WDTConfigController::loadTableFromDB($tableId);
        $existing_columns = WDTConfigController::loadColumnsFromDB($tableId);

        $delete_column_id = 0;
        $delete_column_index = 0;

        foreach ($existing_columns as $existing_column) {
            if ($existing_column->orig_header == $columnName) {
                $delete_column_index = $existing_column->pos;
                $delete_column_id = $existing_column->id;
                break;
            }
        }

        $drop_statement = "ALTER TABLE {$tableData->mysql_table_name} DROP COLUMN {$columnName}";

        // First delete the column from the MySQL table
        if (Connection::isSeparate($tableData->connection)) {
            // External DB
            $Sql = Connection::create($tableData->connection);
            $Sql->doQuery($drop_statement, array());
        } else {
            $wpdb->query($drop_statement);
        }

        if ($delete_column_id != 0) {
            // Delete the column from wp_wpdatatable_columns
            $wpdb->delete(
                $wpdb->prefix . 'wpdatatables_columns',
                array('id' => $delete_column_id)
            );

            // Update the order of other columns
            $update_statement = "UPDATE " . $wpdb->prefix . "wpdatatables_columns 
                                      SET pos = pos - 1 
                                      WHERE table_id = {$tableId} 
                                          AND pos >= " . (int)$delete_column_index;
            $wpdb->query($update_statement);

        }

    }

    /**
     * Add a new column to manually generated table
     */
    public static function addNewManualColumn($tableId, $column_data)
    {
        global $wpdb;

        $tableData = WDTConfigController::loadTableFromDB($tableId);
        $existing_columns = WDTConfigController::loadColumnsFromDB($tableId);
        $dateFormat = get_option('wdtDateFormat');
        $timeFormat = get_option('wdtTimeFormat');

        $existing_headers = array();
        $column_index = 0;
        foreach ($existing_columns as $existing_column) {
            $existing_headers[] = $existing_column->orig_header;
            if ($existing_column->orig_header == $column_data['insert_after']) {
                $column_index = $existing_column->pos + 1;
            }
        }

        $new_column_mysql_name = WDTTools::generateMySQLColumnName($column_data['name'], $existing_headers);
        $columnProperties = self::defineColumnProperties($new_column_mysql_name, $column_data, $tableData->connection);

        $vendor = Connection::getVendor($tableData->connection);
        $leftColumnQuote = Connection::getLeftColumnQuote($vendor);
        $rightColumnQuote = Connection::getRightColumnQuote($vendor);
        $isMySql = $vendor === Connection::$MYSQL;
        $isMSSql = $vendor === Connection::$MSSQL;
        $isPostgreSql = $vendor === Connection::$POSTGRESQL;

        // Add the column to MySQL table
        if ($isMySql) {
            $alter_table_statement = "ALTER TABLE {$tableData->mysql_table_name} 
                                        ADD COLUMN {$columnProperties['create_block']} ";

            if ($column_data['insert_after'] == '%%beginning%%') {
                $alter_table_statement .= " FIRST";
            } else if ($column_data['insert_after'] != '%%end%%') {
                $alter_table_statement .= " AFTER {$leftColumnQuote}{$column_data['insert_after']}{$rightColumnQuote}";
            }
        }

        if ($isMSSql) {
            $alter_table_statement = "ALTER TABLE {$tableData->mysql_table_name} 
                                        ADD {$columnProperties['create_block']} ";
        }

        if ($isPostgreSql) {
            $alter_table_statement = "ALTER TABLE {$tableData->mysql_table_name} 
                                        ADD COLUMN {$columnProperties['create_block']} ";
        }

        // Call the create statement on WPDB or on external DB if it is defined
        if (Connection::isSeparate($tableData->connection)) {
            // External DB
            $Sql = Connection::create($tableData->connection);
            $Sql->doQuery($alter_table_statement, array());
        } else {
            $wpdb->query($alter_table_statement);
        }

        // Fill in with default value if requested
        $column_data['default_value'] = $column_data['type'] === 'multiselect' ? sanitize_text_field(implode(", ", $column_data['default_value'])) : sanitize_text_field($column_data['default_value']);
        if ($column_data['fill_default'] == 1 && $column_data['default_value']) {

            $valueQuoute = "'";

            if ($column_data['type'] == 'date') {
                $column_data['default_value'] =
                    DateTime::createFromFormat(
                        $dateFormat,
                        $column_data['default_value']
                    )->format('Y-m-d');
            } else if ($column_data['type'] == 'datetime') {
                $column_data['default_value'] =
                    DateTime::createFromFormat(
                        $dateFormat . ' ' . $timeFormat,
                        $column_data['default_value']
                    )->format('Y-m-d H:i:s');
            } else if ($column_data['type'] == 'time') {
                $column_data['default_value'] =
                    DateTime::createFromFormat(
                        $timeFormat,
                        $column_data['default_value']
                    )->format('H:i:s');
            } else if ($column_data['type'] == 'int' || $column_data['type'] == 'float') {
                if ($isMSSql || $isPostgreSql) {
                    $valueQuoute = '';
                }
            }

            $where = '';


            if ($isMySql) {
                $where = 'WHERE 1';
            }

            if ($isMSSql) {
                $where = '';
            }

            if ($isPostgreSql) {
                $where = '';
            }

            $update_fill_default = "UPDATE {$tableData->mysql_table_name} 
                                            SET {$leftColumnQuote}{$new_column_mysql_name}{$rightColumnQuote} = {$valueQuoute}{$column_data['default_value']}{$valueQuoute} 
                                            {$where}";
            if (Connection::isSeparate($tableData->connection)) {
                // External DB
                $Sql->doQuery($update_fill_default, array());
            } else {
                $wpdb->query($update_fill_default);
            }
        }

        // Move the existing columns if necessary
        if ($column_data['insert_after'] == '%%end%%') {
            $column_index = count($existing_columns);
        } else {
            $update_statement = "UPDATE " . $wpdb->prefix . "wpdatatables_columns 
                                        SET pos = pos + 1 
                                        WHERE table_id = {$tableId} 
                                            AND pos >= " . (int)$column_index;
            $wpdb->query($update_statement);
        }
        // Add the column to wp_wpdatatables_columns
        $wpdb->insert(
            $wpdb->prefix . "wpdatatables_columns",
            array(
                'table_id' => $tableId,
                'orig_header' => $new_column_mysql_name,
                'display_header' => sanitize_text_field($column_data['name']),
                'filter_type' => $columnProperties['filter_type'],
                'column_type' => $columnProperties['column_type'],
                'pos' => $column_index,
                'possible_values' => str_replace(',,;,|', '|', sanitize_text_field($column_data['possible_values'])),
                'advanced_settings' => json_encode($columnProperties['advanced_settings']),
                'input_type' => $columnProperties['editor_type']
            )
        );


    }

    /**
     * Prepare a list of all possible meta keys for provided post types
     * arranged in multidimensional array
     */
    public static function wdtGetPostMetaKeysForPostTypes()
    {
        global $wpdb;

        $query = "SELECT $wpdb->postmeta.meta_key, $wpdb->posts.post_type
	        FROM $wpdb->posts
	        LEFT JOIN $wpdb->postmeta 
	        ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
                AND $wpdb->postmeta.meta_key != '' 
	        AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)' 
	        AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'
	        GROUP BY $wpdb->postmeta.meta_key;
	    ";

        $metaRes = $wpdb->get_results($query);

        $metaKeys = array();

        foreach ($metaRes as $metaRow) {
            if (!isset($metaKeys[$metaRow->post_type])) {
                $metaKeys[$metaRow->post_type] = array();
            }
            $metaKeys[$metaRow->post_type][] = $metaRow->meta_key;
        }

        echo json_encode($metaKeys);
    }

    /**
     * Prepare a list of all possible meta keys for provided post types
     * arranged in multidimensional array
     */
    public static function wdtGetTaxonomiesForPostTypes()
    {
        global $wp_taxonomies;

        $returnTaxonomies = array();

        foreach ($wp_taxonomies as $tax_name => $tax_obj) {
            foreach ($tax_obj->object_type as $post_type) {
                if (!isset($returnTaxonomies[$post_type])) {
                    $returnTaxonomies[$post_type] = array();
                }
                $returnTaxonomies[$post_type][] = $tax_name;
            }
        }
        echo json_encode($returnTaxonomies);
    }

}
