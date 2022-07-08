<?php

defined('ABSPATH') or die('Access denied.');

/**
 * Created by PhpStorm.
 * User: Milos Roksandic
 * Date: 23.2.16.
 * Time: 19.54
 */
class WPExcelDataTable extends WPDataTable {
    protected static $_columnClass = 'WDTExcelColumn';

    protected function renderWithJSAndStyles() {
        $jsExt = get_option('wdtMinifiedJs') ? '.min.js' : '.js';

        WDTTools::wdtUIKitEnqueue();

        if (WDT_INCLUDE_DATATABLES_CORE) {
            wp_register_script('handsontable', WDT_JS_PATH . 'handsontable/handsontable.full' . $jsExt, array('jquery'), WDT_CURRENT_VERSION);
            wp_enqueue_script('handsontable');
        }

        wp_enqueue_script('wpdatatables-urijs', WDT_JS_PATH . 'urijs/URI.min.js', array(), WDT_CURRENT_VERSION);

        wp_enqueue_script('moment', WDT_JS_PATH . 'moment/moment.js', array(), WDT_CURRENT_VERSION);

        wp_enqueue_media();

        wp_register_script('wpdatatables_excel', WDT_JS_PATH . 'wpdatatables/wdt.excel' . $jsExt, array('jquery', 'handsontable', 'wpdatatables-urijs'),WDT_CURRENT_VERSION);
        wp_enqueue_script('wpdatatables_excel_plugin', WDT_JS_PATH . 'wpdatatables/wdt.excelPlugin' . $jsExt, array('jquery', 'handsontable'),WDT_CURRENT_VERSION);

        wp_enqueue_script('wpdatatables_excel');

        // Localization
        wp_localize_script('wpdatatables_excel', 'wpdatatables_frontend_strings', WDTTools::getTranslationStrings());
        wp_localize_script('wpdatatables_excel_plugin', 'wpdatatables_frontend_strings', WDTTools::getTranslationStrings());

        $this->addCSSClass('data-t');

        ob_start();
        include(WDT_TEMPLATE_PATH . 'frontend/excel_table_main.inc.php');
        $tableContent = ob_get_contents();
        ob_end_clean();

        return $tableContent;
    }

    public function generateTable($connection) {

        $cssArray = array(
            'wpdatatables-handsontable-min' => WDT_CSS_PATH . 'handsontable.full.min.css',
            'wpdatatables-excel-min' => WDT_CSS_PATH . 'wpdatatables-excel.min.css'
        );
        foreach ($cssArray as $cssKey => $cssFile) {
            wp_enqueue_style($cssKey, $cssFile,array(),WDT_CURRENT_VERSION);
        }

        /** @noinspection PhpUnusedLocalVariableInspection */
        $tableContent = $this->renderWithJSAndStyles();

        ob_start();
        include(WDT_TEMPLATE_PATH . 'frontend/wrap_template.inc.php');
        $returnData = ob_get_contents();
        ob_end_clean();

        $returnData = apply_filters('wpdatatables_excel_filter_table_template', $returnData, $this->getWpId());
        return $returnData;
    }

    public function getColumnDefinitions() {
        $defs = array();
        foreach ($this->_wdtIndexedColumns as $key => &$dataColumn) {
            $def = $dataColumn->getColumnJSON();
            $defs[] = $def;
        }
        return $defs;
    }

    /**
     * Returns JSON object for table description
     */
    public function getJsonDescription() {
        global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9;

        $obj = new stdClass();
        $obj->tableId = $this->getId();
        $obj->selector = '#' . $this->getId();
        $obj->tableWpId = $this->getWpId();
        $obj->responsive = $this->isResponsive();
        $obj->editable = $this->isEditable();

        $obj->decimalPlaces = (int)(get_option('wdtDecimalPlaces') ? get_option('wdtDecimalPlaces') : 2);

        $obj->dataTableParams = new StdClass();
        $obj->dataTableParams->number_format = (int)(get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1);
        $obj->dataTableParams->readOnly = !$this->isEditable();//set max row number for non editable tables
        $obj->dataTableParams->allowInvalid = false;

        $init_date_format = get_option('wdtDateFormat');
        $obj->dataTableParams->displayDateFormat = WDTTools::convertPhpToMomentDateFormat($init_date_format);//custom option
        $obj->dataTableParams->dataSourceDateFormat = $obj->dataTableParams->displayDateFormat;//custom option
        $timeFormat = get_option('wdtTimeFormat');

        $obj->dataTableParams->origTimeFormat = $timeFormat;
        $obj->dataTableParams->timepickTimeFormat = str_replace('H', 'HH', $timeFormat);
        $obj->dataTableParams->momentTimeFormat = str_replace('i', 'mm', $timeFormat);

        if ($this->isEditable()) {
            $obj->dataTableParams->adminAjaxBaseUrl = site_url() . '/wp-admin/admin-ajax.php';
            $obj->dataTableParams->idColumnIndex = $this->getColumnHeaderOffset($this->getIdColumnKey());
            $obj->dataTableParams->idColumnKey = $this->getIdColumnKey();
            $obj->dataTableParams->dateFormat = $obj->dataTableParams->displayDateFormat;
            $obj->dataTableParams->datePickerConfig = array('format' => $obj->dataTableParams->displayDateFormat);
            $obj->dataTableParams->dataSourceDateFormat = WDTTools::convertPhpToMomentDateFormat('Y-m-d');
        }
        $obj->dataTableParams->columns = $this->getColumnDefinitions();

        if ($this->sortEnabled()) {
            $sort_column = 0;
            $sort_direction = true;//true for ascending, false for descending

            if (!is_null($this->getDefaultSortColumn())) {
                $sort_column = $this->getDefaultSortColumn();

                if (strtolower($this->getDefaultSortDirection()) == 'desc') {
                    $sort_direction = false;
                }
            }

            $obj->dataTableParams->columnSorting = array('column' => $sort_column, 'sortOrder' => $sort_direction);
            $obj->dataTableParams->sortIndicator = true;
        } else {
            $obj->dataTableParams->columnSorting = false;
        }

        if ($this->serverSide()) {
            $obj->serverSide = true;
            $obj->dataTableParams->serverSide = true;

            $obj->dataTableParams->ajax = array(
                'url' => site_url() . '/wp-admin/admin-ajax.php?action=get_wdtable&table_id=' . $this->getWpId(),
                'type' => 'POST'
            );
            if (!empty($wdtVar1)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var1=' . urlencode($wdtVar1);
            }
            if (!empty($wdtVar2)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var2=' . urlencode($wdtVar2);
            }
            if (!empty($wdtVar3)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var3=' . urlencode($wdtVar3);
            }
            if (!empty($wdtVar4)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var4=' . urlencode($wdtVar4);
            }
            if (!empty($wdtVar5)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var5=' . urlencode($wdtVar5);
            }
            if (!empty($wdtVar6)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var6=' . urlencode($wdtVar6);
            }
            if (!empty($wdtVar7)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var7=' . urlencode($wdtVar7);
            }
            if (!empty($wdtVar8)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var8=' . urlencode($wdtVar8);
            }
            if (!empty($wdtVar9)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var9=' . urlencode($wdtVar9);
            }

        } else {
            $obj->serverSide = false;
        }

        if (get_option('wdtTabletWidth')) {
            $obj->tabletWidth = get_option('wdtTabletWidth');
        }
        if (get_option('wdtMobileWidth')) {
            $obj->mobileWidth = get_option('wdtMobileWidth');
        }

        $obj->dataTableParams->search = true;
        $obj->dataTableParams->searchDefaultValue = $this->getDefaultSearchValue();

        $obj = apply_filters('wpdatatables_excel_filter_table_description', $obj, $this->getWpId());

        return json_encode($obj, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG);
    }

    /**
     * Formatting row data structure for ajax display table
     * @param $row - key => value pairs as column name and cell value of a row
     * @return array formatted row
     */
    protected function formatAjaxQueryResultRow($row) {
        return $row;
    }

}
