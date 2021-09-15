<?php

defined('ABSPATH') or die('Access denied.');

/**
 * Main wpDataTables functions
 * @package wpDataTables
 * @since 1.6.0
 */
?>
<?php

global $wp_version;

/**
 * The installation/activation method, installs the plugin table
 */
function wdtActivationCreateTables() {
    global $wpdb;

    $connection = Connection::enabledSeparate() ? 'abcdefghijk' : '';

    $tablesTableName = $wpdb->prefix . 'wpdatatables';
    $tablesSql = "CREATE TABLE {$tablesTableName} (
						id INT( 11 ) NOT NULL AUTO_INCREMENT,
						title varchar(255) NOT NULL,
                        show_title tinyint(1) NOT NULL default '1',
						table_type varchar(55) NOT NULL,
						connection varchar(55) NOT NULL DEFAULT '$connection',
						content text NOT NULL,
						filtering tinyint(1) NOT NULL default '1',
						filtering_form tinyint(1) NOT NULL default '0',
						sorting tinyint(1) NOT NULL default '1',
						tools tinyint(1) NOT NULL default '1',
						server_side tinyint(1) NOT NULL default '0',
						editable tinyint(1) NOT NULL default '0',
						inline_editing tinyint(1) NOT NULL default '0',
						popover_tools tinyint(1) NOT NULL default '0',
						editor_roles varchar(255) NOT NULL default '',
						mysql_table_name varchar(255) NOT NULL default '',
                        edit_only_own_rows tinyint(1) NOT NULL default 0,
                        userid_column_id int( 11 ) NOT NULL default 0,
						display_length int(3) NOT NULL default '10',
                        auto_refresh int(3) NOT NULL default 0,
						fixed_columns tinyint(1) NOT NULL default '-1',
						fixed_layout tinyint(1) NOT NULL default '0',
						responsive tinyint(1) NOT NULL default '0',
						scrollable tinyint(1) NOT NULL default '0',
						word_wrap tinyint(1) NOT NULL default '0',
						hide_before_load tinyint(1) NOT NULL default '0',
                        var1 VARCHAR( 255 ) NOT NULL default '',
                        var2 VARCHAR( 255 ) NOT NULL default '',
                        var3 VARCHAR( 255 ) NOT NULL default '',
                        tabletools_config VARCHAR( 255 ) NOT NULL default '',
						advanced_settings TEXT NOT NULL default '',
						UNIQUE KEY id (id)
						) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";

    $columnsTableName = $wpdb->prefix . 'wpdatatables_columns';
    $columnsSql = "CREATE TABLE {$columnsTableName} (
						id INT( 11 ) NOT NULL AUTO_INCREMENT,
						table_id int(11) NOT NULL,
						orig_header varchar(255) NOT NULL,
						display_header varchar(255) NOT NULL,
						filter_type enum('none','null_str','text','number','number-range','date-range','datetime-range','time-range','select','multiselect','checkbox') NOT NULL,
						column_type enum('autodetect','string','int','float','date','link','email','image','formula','datetime','time','masterdetail') NOT NULL,
						input_type enum('none','text','textarea','mce-editor','date','datetime','time','link','email','selectbox','multi-selectbox','attachment') NOT NULL default 'text',
						input_mandatory tinyint(1) NOT NULL default '0',
                        id_column tinyint(1) NOT NULL default '0',
						group_column tinyint(1) NOT NULL default '0',
						sort_column tinyint(1) NOT NULL default '0',
						hide_on_phones tinyint(1) NOT NULL default '0',
						hide_on_tablets tinyint(1) NOT NULL default '0',
						visible tinyint(1) NOT NULL default '1',
						sum_column tinyint(1) NOT NULL default '0',
						skip_thousands_separator tinyint(1) NOT NULL default '0',
						width VARCHAR( 4 ) NOT NULL default '',
						possible_values TEXT NOT NULL default '',
						default_value VARCHAR(100) NOT NULL default '',
						css_class VARCHAR(255) NOT NULL default '',
						text_before VARCHAR(255) NOT NULL default '',
						text_after VARCHAR(255) NOT NULL default '',
                        formatting_rules TEXT NOT NULL default '',
                        calc_formula TEXT NOT NULL default '',
						color VARCHAR(255) NOT NULL default '',
						advanced_settings TEXT NOT NULL default '',
						pos int(11) NOT NULL default '0',
						UNIQUE KEY id (id)
						) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    $chartsTableName = $wpdb->prefix . 'wpdatacharts';
    $chartsSql = "CREATE TABLE {$chartsTableName} (
                                  id int(11) NOT NULL AUTO_INCREMENT,
                                  wpdatatable_id int(11) NOT NULL,
                                  title varchar(255) NOT NULL,
                                  engine enum('google','highcharts','chartjs') NOT NULL,
                                  type varchar(255) NOT NULL,
                                  json_render_data text NOT NULL,
                                  UNIQUE KEY id (id)
                                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    $rowsTableName = $wpdb->prefix . 'wpdatatables_rows';
    $rowsSql = "CREATE TABLE {$rowsTableName} (
                                  id int(11) NOT NULL AUTO_INCREMENT,
                                  table_id int(11) NOT NULL,
                                  data TEXT NOT NULL default '',
                                  UNIQUE KEY id (id)
                                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($tablesSql);
    dbDelta($columnsSql);
    dbDelta($chartsSql);
    dbDelta($rowsSql);
    if (!get_option('wdtUseSeparateCon')) {
        update_option('wdtUseSeparateCon', false);
    }
    if (!get_option('wdtSeparateCon')) {
        update_option('wdtSeparateCon', false);
    }
    if (!get_option('wdtRenderCharts')) {
        update_option('wdtRenderCharts', 'below');
    }
    if (!get_option('wdtRenderFilter')) {
        update_option('wdtRenderFilter', 'footer');
    }
    if (!get_option('wdtRenderFilter')) {
        update_option('wdtTopOffset', '0');
    }
    if (!get_option('wdtLeftOffset')) {
        update_option('wdtLeftOffset', '0');
    }
    if (!get_option('wdtBaseSkin')) {
        update_option('wdtBaseSkin', 'light');
    }
    if (get_option('wdtBaseSkin') && get_option('wdtBaseSkin') == 'skin0') {
        update_option('wdtBaseSkin', 'material');
    }
    if (get_option('wdtBaseSkin') && get_option('wdtBaseSkin') == 'skin1') {
        update_option('wdtBaseSkin', 'light');
    }
    if (get_option('wdtBaseSkin') && get_option('wdtBaseSkin') == 'skin2') {
        update_option('wdtBaseSkin', 'graphite');
    }
    if (!get_option('wdtTimeFormat')) {
        update_option('wdtTimeFormat', 'h:i A');
    }
    if (!get_option('wdtTimeFormat')) {
        update_option('wdtTimeFormat', 'h:i A');
    }
    if (!get_option('wdtInterfaceLanguage')) {
        update_option('wdtInterfaceLanguage', '');
    }
    if (!get_option('wdtTablesPerPage')) {
        update_option('wdtTablesPerPage', 10);
    }
    if (!get_option('wdtNumberFormat')) {
        update_option('wdtNumberFormat', 1);
    }
    if (!get_option('wdtDecimalPlaces')) {
        update_option('wdtDecimalPlaces', 2);
    }
    if (!get_option('wdtCSVDelimiter')) {
        update_option('wdtCSVDelimiter', ',');
    }
    if (!get_option('wdtSortingOrderBrowseTables')) {
        update_option('wdtSortingOrderBrowseTables', 'ASC');
    }
    if (!get_option('wdtDateFormat')) {
        update_option('wdtDateFormat', 'd/m/Y');
    }
    if (get_option('wdtParseShortcodes') === false) {
        update_option('wdtParseShortcodes', false);
    }
    if (get_option('wdtNumbersAlign') === false) {
        update_option('wdtNumbersAlign', true);
    }
    if (get_option('wdtBorderRemoval') === false) {
        update_option('wdtBorderRemoval', 0);
    }
    if (get_option('wdtBorderRemovalHeader') === false) {
        update_option('wdtBorderRemovalHeader', 0);
    }
    if (!get_option('wdtFontColorSettings')) {
        update_option('wdtFontColorSettings', '');
    }
    if (!get_option('wdtCustomJs')) {
        update_option('wdtCustomJs', '');
    }
    if (!get_option('wdtCustomCss')) {
        update_option('wdtCustomCss', '');
    }
    if (!get_option('wdtGoogleSettings')) {
        update_option('wdtGoogleSettings', '');
    }
    if (!get_option('wdtGoogleToken')) {
        update_option('wdtGoogleToken', '');
    }
    if (get_option('wdtMinifiedJs') === false) {
        update_option('wdtMinifiedJs', 1);
    }
    if (!get_option('wdtTabletWidth')) {
        update_option('wdtTabletWidth', 1024);
    }
    if (!get_option('wdtMobileWidth')) {
        update_option('wdtMobileWidth', 480);
    }
    if (get_option('wdtGettingStartedPageStatus') === false) {
        update_option('wdtGettingStartedPageStatus', 0 );
    }
    if (get_option('wdtLiteVSPremiumPageStatus') === false) {
        update_option('wdtLiteVSPremiumPageStatus', 0 );
    }
    if (get_option('wdtIncludeBootstrap') === false) {
        update_option('wdtIncludeBootstrap', true);
    }
    if (get_option('wdtIncludeBootstrapBackEnd') === false) {
        update_option('wdtIncludeBootstrapBackEnd', true);
    }
    if (get_option('wdtPreventDeletingTables') === false) {
        update_option('wdtPreventDeletingTables', true);
    }
    if (!get_option('wdtActivated')) {
        update_option('wdtActivated', 0);
    }
    if (!get_option('wdtPurchaseCodeStore')) {
        update_option('wdtPurchaseCodeStore', '');
    }
    if (!get_option('wdtEnvatoTokenEmail')) {
        update_option('wdtEnvatoTokenEmail', '');
    }
    if (!get_option('wdtActivatedPowerful')) {
        update_option('wdtActivatedPowerful', 0);
    }
    if (!get_option('wdtPurchaseCodeStorePowerful')) {
        update_option('wdtPurchaseCodeStorePowerful', '');
    }
    if (!get_option('wdtEnvatoTokenEmailPowerful')) {
        update_option('wdtEnvatoTokenEmailPowerful', '');
    }
    if (!get_option('wdtActivatedReport')) {
        update_option('wdtActivatedReport', 0);
    }
    if (!get_option('wdtActivatedMasterDetail')) {
        update_option('wdtActivatedMasterDetail', 0);
    }
    if (!get_option('wdtPurchaseCodeStoreMasterDetail')) {
        update_option('wdtPurchaseCodeStoreMasterDetail', '');
    }
    if (!get_option('wdtPurchaseCodeStoreReport')) {
        update_option('wdtPurchaseCodeStoreReport', '');
    }
    if (!get_option('wdtEnvatoTokenEmailReport')) {
        update_option('wdtEnvatoTokenEmailReport', '');
    }
    if (!get_option('wdtActivatedGravity')) {
        update_option('wdtActivatedGravity', 0);
    }
    if (!get_option('wdtPurchaseCodeStoreGravity')) {
        update_option('wdtPurchaseCodeStoreGravity', '');
    }
    if (!get_option('wdtEnvatoTokenEmailGravity')) {
        update_option('wdtEnvatoTokenEmailGravity', '');
    }
    if (!get_option('wdtActivatedFormidable')) {
        update_option('wdtActivatedFormidable', 0);
    }
    if (!get_option('wdtPurchaseCodeStoreFormidable')) {
        update_option('wdtPurchaseCodeStoreFormidable', '');
    }
    if (!get_option('wdtEnvatoTokenEmailFormidable')) {
        update_option('wdtEnvatoTokenEmailFormidable', '');
    }
    if (get_option('wdtInstallDate') === false) {
        update_option('wdtInstallDate', date( 'Y-m-d' ));
    }
    if (get_option('wdtRatingDiv') === false) {
        update_option('wdtRatingDiv', 'no' );
    }
    if (get_option('wdtShowForminatorNotice') === false) {
        update_option('wdtShowForminatorNotice', 'yes' );
    }
    if (get_option('wdtMDNewsDiv') === false) {
        update_option('wdtMDNewsDiv', 'no' );
    }
    if (get_option('wdtSimpleTableAlert') === false) {
        update_option('wdtSimpleTableAlert', true );
    }
    if (get_option('wdtTempFutureDate') === false) {
        update_option('wdtTempFutureDate', date( 'Y-m-d'));
    }
}

function wdtDeactivation() {

}

/**
 * Table and option deleting upon plugin deleting
 */
function wdtUninstallDelete() {
    global $wpdb;
    if (get_option('wdtPreventDeletingTables') == false) {
        delete_option('wdtUseSeparateCon');
        delete_option('wdtSeparateCon');
        delete_option('wdtTimepickerRange');
        delete_option('wdtTimeFormat');
        delete_option('wdtTabletWidth');
        delete_option('wdtTablesPerPage');
        delete_option('wdtSumFunctionsLabel');
        delete_option('wdtRenderFilter');
        delete_option('wdtRenderCharts');
        delete_option('wdtGettingStartedPageStatus');
        delete_option('wdtLiteVSPremiumPageStatus');
        delete_option('wdtIncludeBootstrap');
        delete_option('wdtIncludeBootstrapBackEnd');
        delete_option('wdtPreventDeletingTables');
        delete_option('wdtParseShortcodes');
        delete_option('wdtNumbersAlign');
        delete_option('wdtBorderRemoval');
        delete_option('wdtBorderRemovalHeader');
        delete_option('wdtNumberFormat');
        delete_option('wdtMobileWidth');
        delete_option('wdtMinifiedJs');
        delete_option('wdtMinFunctionsLabel');
        delete_option('wdtMaxFunctionsLabel');
        delete_option('wdtLeftOffset');
        delete_option('wdtTopOffset');
        delete_option('wdtInterfaceLanguage');
        delete_option('wdtGeneratedTablesCount');
        delete_option('wdtFontColorSettings');
        delete_option('wdtDecimalPlaces');
        delete_option('wdtCSVDelimiter');
        delete_option('wdtDateFormat');
        delete_option('wdtCustomJs');
        delete_option('wdtGoogleSettings');
        delete_option('wdtGoogleToken');
        delete_option('wdtCustomCss');
        delete_option('wdtBaseSkin');
        delete_option('wdtAvgFunctionsLabel');
        delete_option('wdtInstallDate');
        delete_option('wdtRatingDiv');
        delete_option('wdtShowForminatorNotice');
        delete_option('wdtMDNewsDiv');
        delete_option('wdtTempFutureDate');
        delete_option('wdtSimpleTableAlert');
        delete_option('wdtActivated');
        delete_option('wdtPurchaseCodeStore');
        delete_option('wdtEnvatoTokenEmail');
        delete_option('wdtActivatedPowerful');
        delete_option('wdtPurchaseCodeStorePowerful');
        delete_option('wdtEnvatoTokenEmailPowerful');
        delete_option('wdtActivatedMasterDetail');
        delete_option('wdtPurchaseCodeStoreMasterDetail');
        delete_option('wdtActivatedReport');
        delete_option('wdtPurchaseCodeStoreReport');
        delete_option('wdtEnvatoTokenEmailReport');
        delete_option('wdtActivatedGravity');
        delete_option('wdtPurchaseCodeStoreGravity');
        delete_option('wdtEnvatoTokenEmailGravity');
        delete_option('wdtActivatedFormidable');
        delete_option('wdtPurchaseCodeStoreFormidable');
        delete_option('wdtEnvatoTokenEmailFormidable');

        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpdatatables");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpdatatables_columns");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpdatacharts");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpdatatables_rows");
    }
}

/**
 * Activation hook
 * @param $networkWide
 */
function wdtActivation($networkWide) {
    global $wpdb;

    // Check PHP version
    if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50600) {
        deactivate_plugins(WDT_BASENAME);
        wp_die(
            '<p>The <strong>wpDataTables</strong> plugin requires PHP version 5.6 or greater.</p>',
            'Plugin Activation Error',
            array('response' => 200, 'back_link' => TRUE)
        );
    }

    if (function_exists('is_multisite') && is_multisite()) {
        //check if it is network activation if so run the activation function for each id
        if ($networkWide) {
            $oldBlog = $wpdb->blogid;
            //Get all blog ids
            $blogIds = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

            foreach ($blogIds as $blogId) {
                switch_to_blog($blogId);
                //Create database table if not exists
                wdtActivationCreateTables();
            }
            switch_to_blog($oldBlog);

            return;
        }
    }
    //Create database table if not exists
    wdtActivationCreateTables();
}

/**
 * Uninstall hook
 */
function wdtUninstall() {
    if (function_exists('is_multisite') && is_multisite()) {
        global $wpdb;
        $oldBlog = $wpdb->blogid;
        //Get all blog ids
        $blogIds = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
        foreach ($blogIds as $blogId) {
            switch_to_blog($blogId);
            wdtUninstallDelete();
        }
        switch_to_blog($oldBlog);
    } else {
        wdtUninstallDelete();
    }
}


/**
 * Add rating massage on wpdt-admin pages after 2 weeks of using
 */
function wdtAdminRatingMessages() {
    global $wpdb;
    $query = "SELECT COUNT(*) FROM {$wpdb->prefix}wpdatatables ORDER BY id";

    $allTables = $wpdb->get_var($query);

    $installDate = get_option( 'wdtInstallDate' );
    $currentDate = date( 'Y-m-d' );
    $tempIgnoreDate = get_option( 'wdtTempFutureDate' );
    $wpdtPage = isset($_GET['page']) ? $_GET['page'] : '';
    $urlAddonsPage = get_site_url() . '/wp-admin/admin.php?page=wpdatatables-add-ons';

    $tempIgnore = strtotime($currentDate) >= strtotime($tempIgnoreDate) ? true : false;
    $datetimeInstallDate = new DateTime( $installDate );
    $datetimeCurrentDate = new DateTime( $currentDate );
    $diffIntrval = round( ($datetimeCurrentDate->format( 'U' ) - $datetimeInstallDate->format( 'U' )) / (60 * 60 * 24) );

    if( is_admin() && strpos($wpdtPage,'wpdatatables') !== false &&
        $diffIntrval >= 14 && get_option( 'wdtRatingDiv' ) == "no" && $tempIgnore && isset($allTables) && $allTables > 5) {
        include WDT_TEMPLATE_PATH . 'admin/common/ratingDiv.inc.php';
    }

    if ( is_admin() && strpos($wpdtPage,'wpdatatables') !== false && get_option( 'wdtMDNewsDiv' ) == "no" ) {
        echo '<div class="notice notice-info is-dismissible wpdt-md-news-notice">
             <p class="wpdt-md-news">NEWS! wpDataTables just launched a new addon - Master-Detail Tables. You can find it in the <a href="'. $urlAddonsPage . '">Addons page</a>, read more about it in our docs on this <a href="https://wpdatatables.com/documentation/addons/master-detail-tables/">link</a>.</p>
         </div>';
    }

    if( is_admin() && strpos($wpdtPage,'wpdatatables') !== false &&
        get_option( 'wdtShowForminatorNotice' ) == "yes" && defined( 'FORMINATOR_PLUGIN_BASENAME' )
        && !defined('WDT_FRF_ROOT_PATH')) {
        echo '<div class="notice notice-info is-dismissible wpdt-forminator-news-notice">
             <p class="wpdt-forminator-news"><strong style="color: #ff8c00">NEWS!</strong> wpDataTables just launched a new <strong style="color: #ff8c00">FREE</strong> addon - <strong style="color: #ff8c00">wpDataTables integration for Forminator Forms</strong>. You can download it and read more about it on wp.org on this <a class="wdt-forminator-link" href="https://wordpress.org/plugins/wpdatatables-forminator/" style="color: #ff8c00" target="_blank">link</a>.</p>
         </div>';
    }
}

add_action( 'admin_notices', 'wdtAdminRatingMessages' );

/**
 * Remove rating message
 */
function wdtHideRating() {
    update_option( 'wdtRatingDiv', 'yes' );
    echo json_encode( array("success") );
    exit;
}

add_action( 'wp_ajax_wdtHideRating', 'wdtHideRating' );

/**
 * Remove Forminator notice message
 */
function wdtRemoveForminatorNotice() {
    update_option( 'wdtShowForminatorNotice', 'no' );
    echo json_encode( array("success") );
    exit;
}

add_action( 'wp_ajax_wdt_remove_forminator_notice', 'wdtRemoveForminatorNotice' );

/**
 * Remove Simple Table alert message
 */
function wdtHideSimpleTableAlert() {
        update_option( 'wdtSimpleTableAlert', false );
        echo json_encode( array("success") );
    exit;
}

add_action( 'wp_ajax_wdtHideSimpleTableAlert', 'wdtHideSimpleTableAlert' );

/**
 * Remove Master Detail news message
 */
function wpdtHideMDNewsDiv() {
    update_option( 'wdtMDNewsDiv', 'yes' );
    echo json_encode( array("success") );
    exit;
}

add_action( 'wp_ajax_wpdtHideMDNewsDiv', 'wpdtHideMDNewsDiv' );

/**
 * Temperary hide rating message for 7 days
 */
function wpdtTempHideRatingDiv() {
    $date = strtotime("+7 day");
    update_option('wdtTempFutureDate', date( 'Y-m-d', $date));
    echo json_encode( array("success") );
    exit;
}

add_action( 'wp_ajax_wdtTempHideRating', 'wpdtTempHideRatingDiv' );

/**
 * Create tables on every new site (multisite)
 * @param $blogId
 */
function wdtOnCreateSiteOnMultisiteNetwork($blogId) {
    if (is_plugin_active_for_network('wpdatatables/wpdatatables.php')) {
        switch_to_blog($blogId);
        wdtActivationCreateTables();
        restore_current_blog();
    }
}

add_action('wpmu_new_blog', 'wdtOnCreateSiteOnMultisiteNetwork');

/**
 * Delete table on site delete (multisite)
 * @param $tables
 * @return array
 */
function wdtOnDeleteSiteOnMultisiteNetwork($tables) {
    global $wpdb;
    $tables[] = $wpdb->prefix . 'wpdatatables';
    $tables[] = $wpdb->prefix . 'wpdatatables_columns';
    $tables[] = $wpdb->prefix . 'wpdatacharts';

    return $tables;
}

add_filter('wpmu_drop_tables', 'wdtOnDeleteSiteOnMultisiteNetwork');

function wdtAddBodyClass($classes) {

    $classes .= ' wpdt-c';

    return $classes;
}

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
    exit;
}

/**
 * Helper func that prints out the table
 * @param $id
 */
function wdtOutputTable($id) {
    echo wdtWpDataTableShortcodeHandler(array('id' => $id));
}

/**
 * Handler for the chart shortcode
 * @param $atts
 * @param null $content
 * @return bool|string
 */
function wdtWpDataChartShortcodeHandler($atts, $content = null) {
    extract(shortcode_atts(array(
        'id' => '0'
    ), $atts));


    /** @var mixed $id */
    if (!$id) {
        return false;
    }

    $wpDataChart = new WPDataChart();
    $wpDataChart->setId($id);
    $wpDataChart->loadFromDB();

    $chartExists = $wpDataChart->getwpDataTableId();
    if (empty($chartExists)) {
        return __('wpDataChart with provided ID not found!', 'wpdatatables');
    }

    do_action('wpdatatables_before_render_chart', $wpDataChart->getId());

    return $wpDataChart->renderChart();
}

/**
 * Handler for the table shortcode
 * @param $atts
 * @param null $content
 * @return mixed|string
 */
function wdtWpDataTableShortcodeHandler($atts, $content = null) {
    global $wdtVar1, $wdtVar2, $wdtVar3, $wdtExportFileName;

    extract(shortcode_atts(array(
        'id' => '0',
        'var1' => '%%no_val%%',
        'var2' => '%%no_val%%',
        'var3' => '%%no_val%%',
        'export_file_name' => '%%no_val%%',
        'table_view' => 'regular'
    ), $atts));

    /**
     * Protection
     * @var int $id
     */
    if (!$id) {
        return false;
    }

    $tableData = WDTConfigController::loadTableFromDB($id);
    if (empty($tableData->content)) {
        return __('wpDataTable with provided ID not found!', 'wpdatatables');
    }

    do_action('wpdatatables_before_render_table', $id);

    /** @var mixed $var1 */
    $wdtVar1 = $var1 !== '%%no_val%%' ? $var1 : $tableData->var1;
    /** @var mixed $var2 */
    $wdtVar2 = $var2 !== '%%no_val%%' ? $var2 : $tableData->var2;
    /** @var mixed $var3 */
    $wdtVar3 = $var3 !== '%%no_val%%' ? $var3 : $tableData->var3;

    /** @var mixed $export_file_name */
    $wdtExportFileName = $export_file_name !== '%%no_val%%' ? $export_file_name : '';

    do_action('wpdatatables_before_get_table_metadata',$id);

    if ($tableData->table_type === 'simple'){
        try {
            $wpDataTableRows = WPDataTableRows::loadWpDataTableRows($id);
            $output = $wpDataTableRows->generateTable($id);
        } catch (Exception $e) {
            $output = ltrim($e->getMessage(), '<br/><br/>');
        }
    } else {
        try{
            /** @var mixed $table_view */
            if ($table_view == 'excel') {
                /** @var WPExcelDataTable $wpDataTable */
                $wpDataTable = new WPExcelDataTable($tableData->connection);
            } else {
                /** @var WPDataTable $wpDataTable */
                $wpDataTable = new WPDataTable($tableData->connection);
            }
        } catch (Exception $e) {
            echo WDTTools::wdtShowError($e->getMessage());
            return;
        }


        $wpDataTable->setWpId($id);

        $columnDataPrepared = $wpDataTable->prepareColumnData($tableData);

        try {
            $wpDataTable->fillFromData($tableData, $columnDataPrepared);
            $wpDataTable = apply_filters('wpdatatables_filter_initial_table_construct', $wpDataTable);

            $output = '';
            if ($tableData->show_title && $tableData->title) {
                $output .= apply_filters('wpdatatables_filter_table_title', (empty($tableData->title) ? '' : '<h2 class="wpdt-c" id="wdt-table-title-'. $id .'">' . $tableData->title . '</h2>'), $id);
            }
            $output .= $wpDataTable->generateTable($tableData->connection);
        } catch (Exception $e) {
            $output = WDTTools::wdtShowError($e->getMessage());
        }
    }

    $output = apply_filters('wpdatatables_filter_rendered_table', $output, $id);

    return $output;
}

/**
 * Handler for the SUM, AVG, MIN and MAX function shortcode
 * @param $atts
 * @param null $content
 * @param null $shortcode
 * @return string
 */
function wdtFuncsShortcodeHandler($atts, $content = null, $shortcode = null) {

    $attributes = shortcode_atts(array(
        'table_id' => 0,
        'col_id' => 0,
        'label' => null
    ), $atts);

    if (!$attributes['table_id']) {
        return __("Please provide table_id attribute for {$shortcode} shortcode!", 'wpdatatables');
    }
    if (!$attributes['col_id']) {
        return __("Please provide col_id attribute for {$shortcode} shortcode!", 'wpdatatables');
    }

    $wpDataTable = WPDataTable::loadWpDataTable($attributes['table_id'], null, true);

    $wpDataTableColumns = $wpDataTable->getColumns();
    if (empty($wpDataTableColumns)) {
        return __('wpDataTable with provided ID not found!', 'wpdatatables');
    }

    $column = WDTConfigController::loadSingleColumnFromDB($attributes['col_id']);

    $columnExists = $column['table_id'] === $attributes['table_id'];
    if ($columnExists === false) {
        return __("Column with ID {$attributes['col_id']} is not found in table with ID {$attributes['table_id']}!", 'wpdatatables');
    }
    if ($column['column_type'] !== 'int' && $column['column_type'] !== 'float' && $column['column_type'] !== 'formula') {
        return __('Provided column is not Integer or Float column type', 'wpdatatables');
    }

    if ($shortcode === 'wpdatatable_sum') {
        $function = 'sum';
        if (!isset($attributes['label'])) {
            $attributes['label'] = get_option('wdtSumFunctionsLabel') ? get_option('wdtSumFunctionsLabel') : '&#8721; =';
        }
    } else if ($shortcode === 'wpdatatable_avg') {
        $function = 'avg';
        if (!isset($attributes['label'])) {
            $attributes['label'] = get_option('wdtAvgFunctionsLabel') ? get_option('wdtAvgFunctionsLabel') : 'Avg =';
        }
    } else if ($shortcode === 'wpdatatable_min') {
        $function = 'min';
        if (!isset($attributes['label'])) {
            $attributes['label'] = get_option('wdtMinFunctionsLabel') ? get_option('wdtMinFunctionsLabel') : 'Min =';
        }
    } else {
        $function = 'max';
        if (!isset($attributes['label'])) {
            $attributes['label'] = get_option('wdtMaxFunctionsLabel') ? get_option('wdtMaxFunctionsLabel') : 'Max =';
        }
    }

    /** @noinspection PhpUnusedLocalVariableInspection */
    $funcResult = $wpDataTable->calcColumnFunction($column['orig_header'], $function);

    ob_start();
    include WDT_TEMPLATE_PATH . 'frontend/aggregate_functions.inc.php';
    $aggregateFunctionsHtml = ob_get_contents();
    ob_end_clean();

    return $aggregateFunctionsHtml;

}

function wdtRenderScriptStyleBlock($tableID) {
    $customJs = get_option('wdtCustomJs');
    $scriptBlockHtml = '';
    $styleBlockHtml = '';
    $wpDataTable = WDTConfigController::loadTableFromDB($tableID,false);

    if ($customJs) {
        $scriptBlockHtml .= '<script type="text/javascript">' . stripslashes_deep(html_entity_decode($customJs)) . '</script>';
    }
    $returnHtml = $scriptBlockHtml;

    // Color and font settings
    $wdtFontColorSettings = get_option('wdtFontColorSettings');
    if (!empty($wdtFontColorSettings)) {
        /** @noinspection PhpUnusedLocalVariableInspection */
        ob_start();
        include WDT_TEMPLATE_PATH . 'frontend/style_block.inc.php';
        $styleBlockHtml = ob_get_contents();
        ob_end_clean();
        $styleBlockHtml = apply_filters('wpdatatables_filter_style_block', $styleBlockHtml, $wpDataTable->id);
    }

    $returnHtml .= $styleBlockHtml;
    return $returnHtml;
}
function wdtTableRenderScriptStyleBlock($obj) {

    // Generate the style block for table
    $styleBlockHtml = '';
    $returnData = "<style>\n";

    $tableCustomCss = $obj->getTableCustomCss();

    if ($tableCustomCss) {
        $returnData .= stripslashes_deep($tableCustomCss);
    }

    if ($obj->getTableBorderRemoval()) {
        $returnData .= ".wpDataTablesWrapper table.wpDataTable[data-wpdatatable_id='" .  $obj->getWpId() . "'] > tbody > tr > td{ border: none !important; }\n";
        $returnData .= ".wpDataTablesWrapper table.wpDataTable[data-wpdatatable_id='" .  $obj->getWpId() . "'] > thead { border: none !important; }\n";
        $returnData .= ".wpDataTablesWrapper table.wpDataTable[data-wpdatatable_id='" .  $obj->getWpId() . "'] > tfoot > tr > td{ border: none !important; }\n";
        $returnData .= ".wpDataTablesWrapper table.wpDataTable[data-wpdatatable_id='" .  $obj->getWpId() . "'] > tfoot { border: none !important; }\n";
    }
    if ($obj->getTableBorderRemovalHeader()) {
        $returnData .= ".wpDataTablesWrapper table.wpDataTable[data-wpdatatable_id='" .  $obj->getWpId() . "'] > thead > tr > th{ border: none !important; }\n";
    }

    $returnData .= "</style>\n";

    $returnHtml = $returnData;
    $wdtTableFontColorSettings = $obj->getTableFontColorSettings();

     //Color and font settings
    if (!empty($wdtTableFontColorSettings)) {
        /** @noinspection PhpUnusedLocalVariableInspection */
        ob_start();
        include WDT_TEMPLATE_PATH . 'frontend/style_table_block.inc.php';
        $styleBlockHtml = ob_get_contents();
        ob_end_clean();
        $styleBlockHtml = apply_filters('wpdatatables_filter_style_table_block', $styleBlockHtml, $obj->getWpId());
    }

    $returnHtml .= $styleBlockHtml;
    return $returnHtml;
}

/**
 * Checks if current user can edit table on the front-end
 * @param $tableEditorRoles
 * @param $id
 * @return bool|mixed
 */
function wdtCurrentUserCanEdit($tableEditorRoles, $id) {
    $wpRoles = new WP_Roles();
    $userCanEdit = false;

    $tableEditorRoles = strtolower($tableEditorRoles);
    $editorRoles = array();

    if (empty($tableEditorRoles)) {
        $userCanEdit = true;
    } else {
        $editorRoles = explode(',', $tableEditorRoles);

        $allRoles = $wpRoles->get_names();

        $currentUser = wp_get_current_user();
        if (!($currentUser instanceof WP_User)) {
            return false;
        }

        foreach ($currentUser->roles as $userRole) {
            if (in_array(strtolower($allRoles[$userRole]), $editorRoles)) {
                $userCanEdit = true;
                break;
            }
        }
    }

    return apply_filters('wpdatatables_allow_edit_table', $userCanEdit, $editorRoles, $id);
}

/**
 * Removes all dangerous strings from query
 * @param $query
 * @return mixed|string
 */
function wdtSanitizeQuery($query) {
    $query = str_replace('DELETE', '', $query);
    $query = str_replace('DROP', '', $query);
    $query = str_replace('INSERT', '', $query);
    $query = stripslashes($query);

    $query = apply_filters('wpdt_sanitize_query',$query);

    return $query;
}

/**
 * Init wpDataTabes block for Gutenberg
 */
function initGutenbergBlocks (){
    WpDataTablesGutenbergBlock::init();
    WpDataChartsGutenbergBlock::init();
    add_filter( 'block_categories', 'addWpDataTablesBlockCategory', 10, 2);
}
add_action('plugins_loaded', 'initGutenbergBlocks');

/**
 * Creating wpDataTables block category in Gutenberg
 */
function addWpDataTablesBlockCategory ($categories, $post) {
    return array_merge(
        array(
            array(
                'slug' => 'wpdatatables-blocks',
                'title' => 'wpDataTables',
            ),
        ),
        $categories
    );
}

/**
 * Buttons for "insert wpDataTable" and "insert wpDataCharts" in WP MCE editor
 */
function wdtMCEButtons() {
    add_filter("mce_external_plugins", "wdtAddButtons");
    add_filter('mce_buttons', 'wdtRegisterButtons');
}

add_action('init', 'wdtMCEButtons');

/**
 * Function that add buttons for MCE editor
 * @param $pluginArray
 * @return mixed
 */
function wdtAddButtons($pluginArray) {
    $pluginArray['wpdatatables'] = WDT_JS_PATH . '/wpdatatables/wdt.mce.js';

    return $pluginArray;
}

/**
 * Function that register buttons for MCE editor
 * @param $buttons
 * @return mixed
 */
function wdtRegisterButtons($buttons) {
    array_push($buttons, 'wpdatatable', 'wpdatachart');

    return $buttons;
}

/**
 * Loads the translations
 */
function wdtLoadTextdomain() {
    load_plugin_textdomain('wpdatatables', false, dirname(plugin_basename(dirname(__FILE__))) . '/languages/' . get_locale() . '/');
}

/**
 * Enable Multiple connection
 */
function wdtEnableMultipleConnections() {
    update_option('wdtSeparateCon', json_encode(array(
        array(
            "id"       => 'abcdefghijk',
            "host"     => get_option('wdtMySqlHost') ?: '',
            "database" => get_option('wdtMySqlDB') ?: '',
            "user"     => get_option('wdtMySqlUser') ?: '',
            "password" => get_option('wdtMySqlPwd') ?: '',
            "port"     => get_option('wdtMySqlPort') ?: '',
            "vendor"   => 'mysql',
            "driver"   => 'dblib',
            "name"     => "MYSQL",
            "default"  => get_option('wdtUseSeparateCon') ?: ''
        )
    )));

    delete_option('wdtMySqlHost');
    delete_option('wdtMySqlDB');
    delete_option('wdtMySqlUser');
    delete_option('wdtMySqlPwd');
    delete_option('wdtMySqlPort');
}

/**
 * Workaround for NULLs in WP
 */
if ($wp_version < 4.4) {
    add_filter('query', 'wdtSupportNulls');

    function wdtSupportNulls($query) {
        $query = str_ireplace("'NULL'", "NULL", $query);
        $query = str_replace('null_str', 'null', $query);

        return $query;
    }
}

global $wdtPluginSlug;

$filePath = plugin_basename(__FILE__);
$filePathArr = explode('/', $filePath);
$wdtPluginSlug = $filePathArr[0] . '/wpdatatables.php';

/**
 * @param $transient
 *
 * @return mixed
 */
function wdtCheckUpdate($transient)
{
    global $wdtPluginSlug;

    if (empty($transient->checked)) {
        return $transient;
    }

    $purchaseCode = get_option('wdtPurchaseCodeStore');

    $envatoTokenEmail = get_option('wdtEnvatoTokenEmail');

    // Get the remote info
    $remoteInformation = WDTTools::getRemoteInformation('wpdatatables', $purchaseCode, $envatoTokenEmail);

    // If a newer version is available, add the update
    if ($remoteInformation && version_compare(WDT_CURRENT_VERSION, $remoteInformation->new_version, '<')) {
        $remoteInformation->package = $remoteInformation->download_link;
        $transient->response[$wdtPluginSlug] = $remoteInformation;
    }

    return $transient;
}

add_filter('pre_set_site_transient_update_plugins', 'wdtCheckUpdate');

/**
 * @param $response
 * @param $action
 * @param $args
 *
 * @return bool|mixed
 */
function wdtCheckInfo($response, $action, $args)
{
    global $wdtPluginSlug;

    if ('plugin_information' !== $action) {
        return $response;
    }

    if (empty($args->slug)) {
        return $response;
    }

    $purchaseCode = get_option('wdtPurchaseCodeStore');

    $envatoTokenEmail = get_option('wdtEnvatoTokenEmail');

    if ($args->slug === $wdtPluginSlug) {
        return WDTTools::getRemoteInformation('wpdatatables', $purchaseCode, $envatoTokenEmail);
    }

    return $response;
}

add_filter('plugins_api', 'wdtCheckInfo', 10, 3);

function wdtAddMessageOnPluginsPage() {
    /** @var bool $activated */
    $activated = get_option('wdtActivated');

    /** @var string $url */
    $url = get_site_url() . '/wp-admin/admin.php?page=wpdatatables-settings&activeTab=activation';

    /** @var string $redirect */
    $redirect = '<a href="' . $url . '" target="_blank">' . __('settings', 'wpdatatables') . '</a>';

    if (!$activated) {
        echo sprintf(' ' . __('To receive automatic updates license activation is required. Please visit %s to activate wpDataTables.', 'wpdatatables'), $redirect);
    }
}

add_action('in_plugin_update_message-' . $wdtPluginSlug, 'wdtAddMessageOnPluginsPage');

function wdtAddMessageOnUpdate($reply, $package, $updater) {
    if (isset($updater->skin->plugin_info['Name']) && $updater->skin->plugin_info['Name'] === 'wpDataTables') {
        /** @var string $url */
        $url = get_site_url() . '/wp-admin/admin.php?page=wpdatatables-settings&activeTab=activation';

        /** @var string $redirect */
        $redirect = '<a href="' . $url . '" target="_blank">' . __('settings', 'wpdatatables') . '</a>';

        if (!$package) {
            return new WP_Error(
                'wpdatatables_not_activated',
                sprintf(' ' . __('To receive automatic updates license activation is required. Please visit %s to activate wpDataTables.', 'wpdatatables'), $redirect)
            );
        }

        return $reply;
    }

    return $reply;
}

add_filter('upgrader_pre_download', 'wdtAddMessageOnUpdate', 10, 4);

/**
 * Redirect on Welcome page after activate plugin
 */
function welcome_page_activation_redirect( $plugin ) {
    $filePath = plugin_basename(__FILE__);
    $filePathArr = explode('/', $filePath);
    $wdtPluginSlug = $filePathArr[0] . '/wpdatatables.php';

    if( $plugin == plugin_basename( $wdtPluginSlug ) && (isset($_GET['action']) && $_GET['action'] == 'activate')) {
        exit( wp_redirect( admin_url( 'admin.php?page=wpdatatables-welcome-page' ) ) );
    }
}

add_action( 'activated_plugin', 'welcome_page_activation_redirect' );



/**
 *  Add plugin action links Plugins page
 */
function wpdt_add_plugin_action_links( $links ) {

    // Settings link.
    $action_links['settings'] = '<a href="' . admin_url( 'admin.php?page=wpdatatables-settings' ) . '" aria-label="' . esc_attr( __( 'Go to Settings', 'wpdatatables' ) ) . '">' . esc_html__( 'Settings', 'wpdatatables' ) . '</a>';

    // Add ons link.
    $action_links['addons'] = '<a href="' .  esc_url( 'https://wpdatatables.com/addons/' )  . '" aria-label="' . esc_attr( __( 'Add-ons', 'wpdatatables' ) ) . '" style="color: #ff8c00;" target="_blank">' . esc_html__( 'Add-ons', 'wpdatatables' ) . '</a>';

    // Documentation link.
    $action_links['docs'] = '<a href="' . esc_url( 'https://wpdatatables.com/documentation/general/features-overview/' ) . '" aria-label="' . esc_attr( __( 'Docs', 'wpdatatables' ) ) . '" target="_blank">' . esc_html__( 'Docs', 'wpdatatables' ) . '</a>';

    return array_merge( $action_links, $links );
}
add_filter( 'plugin_action_links_' . WDT_BASENAME , 'wpdt_add_plugin_action_links'  );


/**
 *  Add links next to plugin details on Plugins page
 */
function wpdt_plugin_row_meta( $links, $file, $plugin_data ) {

    if ( WDT_BASENAME === $file ) {
        // Show network meta links only when activated network wide.
        if ( is_network_admin() ) {
            return $links;
        }

        // Change AuthorURI link.
        if ( isset( $links[1] ) ){
            $author_uri = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                $plugin_data['AuthorURI'],
                $plugin_data['Author']
            );
            $links[1] = sprintf( __( 'By %s' ), $author_uri );
        }
        // Change View details link.
        if ( isset( $links[2] ) ) {
            $links[2] = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                esc_url( 'https://wpdatatables.com/features/'  ),
                __( 'View details' )
            );
        }
        // Add Docs and Premium support links
        $row_meta['docs'] = '<a href="' . esc_url( 'https://wpdatatables.com/documentation/general/features-overview/' ) . '" aria-label="' . esc_attr__( 'Docs', 'wpdatatables' ) . '" target="_blank">' . esc_html__( 'Docs', 'wpdatatables' ) . '</a>';
        $row_meta['support'] = '<a href="' . admin_url( 'admin.php?page=wpdatatables-support' ) . '" aria-label="' . esc_attr__( 'Support Center', 'wpdatatables' ) . '" target="_blank">' . esc_html__( 'Support Center', 'wpdatatables' ) . '</a>';

        return array_merge( $links, $row_meta );
    }

    return $links;

}

add_filter( 'plugin_row_meta', 'wpdt_plugin_row_meta' , 10, 3 );

/**
 * Optional Visual Composer integration
 */
if (function_exists('vc_map')) {

    /**
     * Get all tables non-paged for the Visual Composer integration
     */
    function wdtGetAllTablesVC() {
        global $wpdb;
        $query = "SELECT id, title FROM {$wpdb->prefix}wpdatatables ORDER BY id";

        $allTables = $wpdb->get_results($query, ARRAY_A);

        $returnTables = array(__('Choose a table', 'wpdatatables') => '');
        foreach ($allTables as $table) {
            $returnTables[$table['title']] = $table['id'];
        }

        return $returnTables;
    }

    /**
     * Get all charts non-paged for the Visual Composer integration
     */
    function wdtGetAllChartsVC() {
        global $wpdb;
        $query = "SELECT id, title FROM {$wpdb->prefix}wpdatacharts ORDER BY id";

        $all_charts = $wpdb->get_results($query, ARRAY_A);

        $returnTables = array();
        foreach ($all_charts as $chart) {
            $returnTables[$chart['title']] = $chart['id'];
        }

        return $returnTables;
    }

    /**
     * Insert wpDataTable button
     */
    vc_map(
        array(
            'name' => 'wpDataTable',
            'base' => 'wpdatatable',
            'description' => __('Interactive Responsive Table', 'wpdatatable'),
            'category' => __('Content'),
            'icon' => plugin_dir_url(dirname(__FILE__)) . 'assets/img/vc-icon.png',
            'params' => array(
                array(
                    'type' => 'dropdown',
                    'class' => '',
                    'heading' => __('wpDataTable', 'wpdatatables'),
                    'admin_label' => true,
                    'param_name' => 'id',
                    'value' => wdtGetAllTablesVC(),
                    'description' => __('Choose the wpDataTable from a dropdown', 'wpdatatables')
                ),
                array(
                    'type' => 'dropdown',
                    'class' => '',
                    'heading' => __('Table view', 'wpdatatables'),
                    'admin_label' => true,
                    'param_name' => 'table_view',
                    'value' => array(
                        __('Regular wpDataTable', 'wpdatatables') => 'regular',
                        __('Excel-like table', 'wpdatatables') => 'excel'
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __('Variable placeholder #1', 'wpdatatables'),
                    'param_name' => 'var1',
                    'value' => '',
                    'group' => __('Variables', 'wpdatatables'),
                    'description' => __('If you used the VAR1 placeholder you can assign a value to it here', 'wpdatatables')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __('Variable placeholder #2', 'wpdatatables'),
                    'param_name' => 'var2',
                    'value' => '',
                    'group' => __('Variables', 'wpdatatables'),
                    'description' => __('If you used the VAR2 placeholder you can assign a value to it here', 'wpdatatables')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __('Variable placeholder #3', 'wpdatatables'),
                    'param_name' => 'var3',
                    'value' => '',
                    'group' => __('Variables', 'wpdatatables'),
                    'description' => __('If you used the VAR3 placeholder you can assign a value to it here', 'wpdatatables')
                )
            )
        )
    );

    /**
     * Insert wpDataChart button
     */
    vc_map(
        array(
            'name' => 'wpDataChart',
            'base' => 'wpdatachart',
            'description' => __('Google or Highcharts chart based on a wpDataTable', 'wpdatatable'),
            'category' => __('Content'),
            'icon' => plugin_dir_url(dirname(__FILE__)) . 'assets/img/vc-charts-icon.png',
            "params" => array(
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => __('wpDataChart', 'wpdatatables'),
                    "param_name" => "id",
                    'admin_label' => true,
                    "value" => wdtGetAllChartsVC(),
                    "description" => __("Choose one of wpDataCharts from the list", 'wpdatatables')
                )
            )
        )
    );

}
