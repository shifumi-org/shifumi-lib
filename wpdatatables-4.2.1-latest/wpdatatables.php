<?php
/**
 * @package wpDataTables
 * @version 4.2.1
 */
/*
Plugin Name: wpDataTables
Plugin URI: https://wpdatatables.com/
Description: Add interactive tables easily from any input source
//[<-- Full version -->]//
Version: 4.2.1
//[<--/ Full version -->]//
//[<-- Full version insertion #27 -->]//
Author: TMS-Plugins
Author URI: https://tms-outsource.com/
Text Domain: wpdatatables
Domain Path: /languages
*/
?>
<?php


defined('ABSPATH') or die('Access denied');

/******************************
 * Includes and configuration *
 ******************************/

define('WDT_ROOT_PATH', plugin_dir_path(__FILE__)); // full path to the wpDataTables root directory
define('WDT_ROOT_URL', plugin_dir_url(__FILE__)); // URL of wpDataTables plugin
if (!defined('WDT_BASENAME')) {
    define('WDT_BASENAME', plugin_basename(__FILE__)); // Base name for wpDataTables plugin
}

// Config file
require_once(WDT_ROOT_PATH . 'config/config.inc.php');

//[<-- Full version -->]//
// AJAX actions handlers
require_once(WDT_ROOT_PATH . 'controllers/wdt_ajax_actions.php');
//[<--/ Full version -->]//

// Plugin functions
require_once(WDT_ROOT_PATH . 'controllers/wdt_functions.php');

if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 50600) {
    require_once WDT_ROOT_PATH . 'lib/autoload.php';
}

function wpdatatables_load()
{
    if (is_admin()) {
        // Admin panel controller
        require_once(WDT_ROOT_PATH . 'controllers/wdt_admin.php');
        // Admin panel AJAX actions
        require_once(WDT_ROOT_PATH . 'controllers/wdt_admin_ajax_actions.php');
        //[<-- Full version -->]//
        // Table constructor
        require_once(WDT_ROOT_PATH . 'source/class.constructor.php');
        //[<--/ Full version -->]//
    }
    require_once(WDT_ROOT_PATH . 'source/class.wdttools.php');
    require_once(WDT_ROOT_PATH . 'source/class.wdtconfigcontroller.php');
    require_once(WDT_ROOT_PATH . 'source/class.wdtsettingscontroller.php');
    require_once(WDT_ROOT_PATH . 'source/class.wdtexception.php');
    require_once(WDT_ROOT_PATH . 'source/class.connection.php');
    require_once(WDT_ROOT_PATH . 'source/class.sql.php');
    require_once(WDT_ROOT_PATH . 'source/class.sql.pdo.php');
    require_once(WDT_ROOT_PATH . 'source/class.wpdatatable.php');
    require_once(WDT_ROOT_PATH . 'source/class.wpdatacolumn.php');
    require_once(WDT_ROOT_PATH . 'source/class.wpdatatablerows.php');
    require_once(WDT_ROOT_PATH . 'source/class.wdtgooglesheets.php');
    //[<-- Full version -->]//
    require_once(WDT_ROOT_PATH . 'source/class.wpexceldatatable.php');
    require_once(WDT_ROOT_PATH . 'source/class.wpexcelcolumn.php');
    require_once(WDT_ROOT_PATH . 'source/class.filterwidget.php');
    require_once(WDT_ROOT_PATH . 'source/class.wpdatachart.php');
    //[<--/ Full version -->]//
    require_once(WDT_ROOT_PATH . 'source/class.wdtbrowsetable.php');
    require_once(WDT_ROOT_PATH . 'source/class.wdtbrowsechartstable.php');
    require_once(WDT_ROOT_PATH . 'integrations/page_builders/gutenberg/GutenbergBlock.php');
    require_once(WDT_ROOT_PATH . 'integrations/page_builders/gutenberg/WpDataTablesGutenbergBlock.php');
    require_once(WDT_ROOT_PATH . 'integrations/page_builders/gutenberg/WpDataChartsGutenbergBlock.php');
    require_once(WDT_ROOT_PATH . 'integrations/page_builders/elementor/class.wdtelementorblock.php');
    require_once(WDT_ROOT_PATH . 'integrations/page_builders/divi-wpdt/divi-wpdt.php');
    require_once(WDT_ROOT_PATH . 'integrations/page_builders/avada/class.wdtavadaelements.php');
    require_once(WDT_ROOT_PATH . 'integrations/page_builders/wpbakery/wdtBakeryBlock.php');

    add_action('plugins_loaded', 'wdtLoadTextdomain');

    if (is_admin()) {
        if (get_option('wdtSeparateCon') === false) {
            add_action('plugins_loaded', 'wdtEnableMultipleConnections', 1, __FILE__);
        }

        if (WDT_CURRENT_VERSION !== get_option('wdtVersion')) {
            if (!function_exists('is_plugin_active_for_network')) {
                include_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }

            wdtActivation(is_plugin_active_for_network(__FILE__));
            update_option('wdtVersion', WDT_CURRENT_VERSION);
        }
    }
}

//[<-- Full version -->]//
// Globals for the shortcode variables
$wdtVar1 = '';
$wdtVar2 = '';
$wdtVar3 = '';


/*******************
 * Filtering widget *
 *******************/
function wdt_register_widget()
{
    register_widget('wdtFilterWidget');
}

//[<--/ Full version -->]//

/********
 * Hooks *
 ********/
register_activation_hook(__FILE__, 'wdtActivation');
register_deactivation_hook(__FILE__, 'wdtDeactivation');
register_uninstall_hook(__FILE__, 'wdtUninstall');

add_shortcode('wpdatatable', 'wdtWpDataTableShortcodeHandler');
add_shortcode('wpdatachart', 'wdtWpDataChartShortcodeHandler');
add_shortcode('wpdatatable_sum', 'wdtFuncsShortcodeHandler');
add_shortcode('wpdatatable_avg', 'wdtFuncsShortcodeHandler');
add_shortcode('wpdatatable_min', 'wdtFuncsShortcodeHandler');
add_shortcode('wpdatatable_max', 'wdtFuncsShortcodeHandler');

//[<-- Full version -->]//
// Widget
add_action('widgets_init', 'wdt_register_widget');
//[<--/ Full version -->]//

wpdatatables_load();

?>
