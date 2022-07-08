<?php

use jlawrence\eos\Parser;
use PHPSQLParser\PHPSQLCreator;
use PHPSQLParser\PHPSQLParser;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Ods;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Shared\Date;


defined('ABSPATH') or die('Access denied.');


/**
 * Main engine of wpDataTables plugin
 */
class WPDataTable {

    protected static $_columnClass = 'WDTColumn';
    protected $_wdtIndexedColumns = array();
    private $_wdtNamedColumns = array();
    private $_defaultSortColumn;
    private $_defaultSortDirection = 'ASC';
    private $_tableContent = '';
    private $_tableType = '';
    private $_title = '';
    private $_interfaceLanguage;
    private $_responsive = false;
    private $_responsiveAction = 'icon';
    private $_scrollable = false;
    private $_inlineEditing = false;
    private $_popoverTools = false;
    private $_tableSkin = '';
    private $_tableFontColorSettings;
    private $_tableBorderRemoval = 0;
    private $_tableBorderRemovalHeader = 0;
    private $_tableCustomCss = '';
    private $_no_data = false;
    private $_filtering_form = false;
    private $_hide_before_load = false;
    public static $wdt_internal_idcount = 0;
    public static $modalRendered = false;
    private $_showFilter = true;
    private $_firstOnPage = false;
    private $_groupingEnabled = false;
    private $_wdtColumnGroupIndex = 0;
    private $_showAdvancedFilter = false;
    private $_wdtTableSort = true;
    private $_serverProcessing = false;
    private $_wdtColumnTypes = array();
    private $_dataRows = array();
    public $_cacheHash = '';
    private $_showTT = true;
    private $_lengthDisplay = 10;
    private $_cssClassArray = array();
    private $_style = '';
    private $_editable = false;
    private $_id;
    private $_idColumnKey = '';
    private $_db;
    private $_wpId = '';
    private $_onlyOwnRows = false;
    private $_userIdColumn = 0;
    private $_defaultSearchValue = '';
    protected $_sumColumns = array();
    protected $_avgColumns = array();
    protected $_minColumns = array();
    protected $_maxColumns = array();
    protected $_sumFooterColumns = array();
    protected $_avgFooterColumns = array();
    protected $_minFooterColumns = array();
    protected $_maxFooterColumns = array();
    protected $_columnsDecimalPlaces = array();
    protected $_columnsThousandsSeparator = array();
    protected $_conditionalFormattingColumns = array();
    private $_fixedLayout = false;
    private $_wordWrap = false;
    private $_columnsCSS = '';
    private $_showTableToolsIncludeHTML = 0;
    private $_tableToolsConfig = array();
    private $_autoRefreshInterval = 0;
    private $_infoBlock = true;
    private $_pagination = true;
    private $_paginationAlign = 'right';
    private $_paginationLayout = 'full_numbers';
    private $_simpleResponsive = false;
    private $_verticalScroll = false;
    private $_simpleHeader = false;
    private $_stripeTable= false;
    private $_cellPadding= 10;
    private $_removeBorders = false;
    private $_borderCollapse = 'collapse';
    private $_borderSpacing = 0;
    private $_verticalScrollHeight= 600;
    private $_globalSearch = true;
    private $_showRowsPerPage = true;
    private $_showAllRows = false;
    private $_aggregateFuncsRes = array();
    private $_ajaxReturn = false;
    private $_clearFilters = false;
    public $connection;
    public static $allowedTableTypes = array('xls', 'csv', 'manual', 'mysql', 'json', 'google_spreadsheet', 'xml', 'serialized', 'simple');
    private $_editButtonsDisplayed = array('all');
    private $_enableDuplicateButton = false;
    private $_pdfPaperSize = 'A4';
    private $_pdfPageOrientation = 'portrait';

    /**
     * @return bool
     */
    public function isClearFilters() {
        return $this->_clearFilters;
    }

    /**
     * @return array
     */
    public function getWdtColumnTypes()
    {
        return $this->_wdtColumnTypes;
    }


    /**
     * @param bool $clearFilters
     */
    public function setClearFilters($clearFilters) {
        $this->_clearFilters = $clearFilters;
    }

    /**
     * @return bool
     */
    public function isFixedLayout() {
        return $this->_fixedLayout;
    }

    /**
     * @param bool $fixedLayout
     */
    public function setFixedLayout($fixedLayout) {
        $this->_fixedLayout = $fixedLayout;
    }

    /**
     * @return bool
     */
    public function isWordWrap() {
        return $this->_wordWrap;
    }

    /**
     * @param bool $wordWrap
     */
    public function setWordWrap($wordWrap) {
        $this->_wordWrap = $wordWrap;
    }

    /**
     * @return bool
     */
    public function isAjaxReturn() {
        return $this->_ajaxReturn;
    }

    /**
     * @param bool $ajaxReturn
     */
    public function setAjaxReturn($ajaxReturn) {
        $this->_ajaxReturn = $ajaxReturn;
    }

    public function setNoData($no_data) {
        $this->_no_data = $no_data;
    }

    public function getNoData() {
        return $this->_no_data;
    }

    public function getId() {
        return $this->_id;
    }

    public function setId($id) {
        $this->_id = $id;
    }

    /**
     * @return string
     */
    public function getTableContent() {
        return $this->_tableContent;
    }

    /**
     * @param string $tableContent
     */
    public function setTableContent($tableContent) {
        $this->_tableContent = $tableContent;
    }

    /**
     * @return string
     */
    public function getTableType() {
        return $this->_tableType;
    }

    /**
     * @param string $tableType
     */
    public function setTableType($tableType) {
        $this->_tableType = $tableType;
    }

    public function setDefaultSearchValue($value) {
        if (!empty($value)) {
            $this->_defaultSearchValue = urlencode($value);
        }
    }

    public function getDefaultSearchValue() {
        return urldecode($this->_defaultSearchValue);
    }

    public function sortEnabled() {
        return $this->_wdtTableSort;
    }

    public function sortEnable() {
        $this->_wdtTableSort = true;
    }

    public function sortDisable() {
        $this->_wdtTableSort = false;
    }

    public function addSumColumn($columnKey) {
        $this->_sumColumns[] = $columnKey;
    }

    public function setSumColumns($sumColumns) {
        $this->_sumColumns = $sumColumns;
    }

    public function getSumColumns() {
        return $this->_sumColumns;
    }

    public function addAvgColumn($columnKey) {
        $this->_avgColumns[] = $columnKey;
    }

    public function setAvgColumns($avgColumns) {
        $this->_avgColumns = $avgColumns;
    }

    public function getAvgColumns() {
        return $this->_avgColumns;
    }

    public function addMinColumn($columnKey) {
        $this->_minColumns[] = $columnKey;
    }

    public function setMinColumns($minColumns) {
        $this->_minColumns = $minColumns;
    }

    public function getMinColumns() {
        return $this->_minColumns;
    }

    public function addMaxColumn($columnKey) {
        $this->_maxColumns[] = $columnKey;
    }

    public function setMaxColumns($maxColumns) {
        $this->_maxColumns = $maxColumns;
    }

    public function getMaxColumns() {
        return $this->_maxColumns;
    }

    public function addSumFooterColumn($columnKey) {
        $this->_sumFooterColumns[] = $columnKey;
    }

    public function setSumFooterColumns($sumColumns) {
        $this->_sumFooterColumns = $sumColumns;
    }

    public function getSumFooterColumns() {
        return $this->_sumFooterColumns;
    }

    public function addAvgFooterColumn($columnKey) {
        $this->_avgFooterColumns[] = $columnKey;
    }

    public function setAvgFooterColumns($avgColumns) {
        $this->_avgFooterColumns = $avgColumns;
    }

    public function getAvgFooterColumns() {
        return $this->_avgFooterColumns;
    }

    public function addMinFooterColumn($columnKey) {
        $this->_minFooterColumns[] = $columnKey;
    }

    public function setMinFooterColumns($minColumns) {
        $this->_minFooterColumns = $minColumns;
    }

    public function getMinFooterColumns() {
        return $this->_minFooterColumns;
    }

    public function addMaxFooterColumn($columnKey) {
        $this->_maxFooterColumns[] = $columnKey;
    }

    public function setMaxFooterColumns($maxColumns) {
        $this->_maxFooterColumns = $maxColumns;
    }

    public function getMaxFooterColumns() {
        return $this->_maxFooterColumns;
    }

    public function addColumnsDecimalPlaces($columnKey, $decimalPlaces) {
        $this->_columnsDecimalPlaces[$columnKey] = $decimalPlaces;
    }

    public function addColumnsThousandsSeparator($columnKey, $thousandsSeparator) {
        $this->_columnsThousandsSeparator[$columnKey] = $thousandsSeparator;
    }

    public function getColumnsCSS() {
        return $this->_columnsCSS;
    }

    public function setColumnsCss($css) {
        $this->_columnsCSS = $css;
    }

    public function reorderColumns($posArray) {
        if (!is_array($posArray)) {
            throw new WDTException('Invalid position data provided!');
        }
        $resultArray = array();
        $resultByKeys = array();

        foreach ($posArray as $pos => $dataColumnIndex) {
            $resultArray[$pos] = $this->_wdtNamedColumns[$dataColumnIndex];
            $resultByKeys[$dataColumnIndex] = $this->_wdtNamedColumns[$dataColumnIndex];
        }
        $this->_wdtIndexedColumns = $resultArray;
        $this->_wdtNamedColumns = $resultByKeys;
    }

    public function getWpId() {
        return $this->_wpId;
    }

    public function setWpId($wpId) {
        $this->_wpId = $wpId;
    }

    public function getCssClassesArr() {
        $classesStr = $this->_cssClassArray;
        $classesStr = apply_filters('wpdatatables_filter_table_cssClassArray', $classesStr, $this->getWpId());
        return implode(' ', $classesStr);
    }

    public function getCSSClasses() {
        return implode(' ', $this->_cssClassArray);
    }

    public function addCSSClass($cssClass) {
        $this->_cssClassArray[] = $cssClass;
    }

    public function getCSSStyle() {
        return $this->_style;
    }

    public function setCSSStyle($style) {
        $this->_style = $style;
    }

    public function setTitle($title) {
        $this->_title = $title;
    }

    public function getName() {
        return $this->_title;
    }

    public function setScrollable($scrollable) {
        if ($scrollable) {
            $this->_scrollable = true;
        } else {
            $this->_scrollable = false;
        }
    }

    public function isScrollable() {
        return $this->_scrollable;
    }
    public function setVerticalScroll($verticalScroll) {
        if ($verticalScroll) {
            $this->_verticalScroll = true;
        } else {
            $this->_verticalScroll = false;
        }
    }

    public function isVerticalScroll() {
        return $this->_verticalScroll;
    }

    public function setInterfaceLanguage($lang) {

        $lang = apply_filters('wpdatatables_filter_interface_lang', $lang, WDTSettingsController::getArrInterfaceLanguages(), $this->getWpId());

        if (empty($lang)) {
            throw new WDTException('Incorrect language parameter!');
        }
        if (!file_exists(WDT_ROOT_PATH . 'source/lang/' . $lang)) {
            throw new WDTException('Language file not found');
        }
        $this->_interfaceLanguage = WDT_ROOT_PATH . 'source/lang/' . $lang;
    }

    public function getInterfaceLanguage() {
        return $this->_interfaceLanguage;
    }

    public function setAutoRefresh($refresh_interval) {
        $this->_autoRefreshInterval = (int)$refresh_interval;
    }

    public function getRefreshInterval() {
        return (int)$this->_autoRefreshInterval;
    }

    public function paginationEnabled() {
        return $this->_pagination;
    }

    public function enablePagination() {
        $this->_pagination = true;
    }

    public function disablePagination() {
        $this->_pagination = false;
    }

    public function enableTT() {
        $this->_showTT = true;
    }

    public function disableTT() {
        $this->_showTT = false;
    }

    public function TTEnabled() {
        return $this->_showTT;
    }

    public function getTableToolsIncludeHTML()
    {
        return $this->_showTableToolsIncludeHTML;
    }

    public function setTableToolsIncludeHTML($showTableToolsIncludeHTML)
    {
        $this->_showTableToolsIncludeHTML = $showTableToolsIncludeHTML;
    }

    public function hideToolbar() {
        $this->_toolbar = false;
    }

    public function setDefaultSortColumn($key) {
        if (!isset($this->_wdtIndexedColumns[$key])
            && !isset($this->_wdtNamedColumns[$key])
        ) {
            throw new WDTException('Incorrect column index');
        }

        $key = array_search($key, array_keys($this->_wdtNamedColumns));

        $this->_defaultSortColumn = $key;
    }

    public function getDefaultSortColumn() {
        return $this->_defaultSortColumn;
    }

    public function setDefaultSortDirection($direction) {
        if (
        !in_array(
            $direction,
            array(
                'ASC',
                'DESC'
            )
        )
        ) {
            return false;
        }
        $this->_defaultSortDirection = $direction;
    }

    public function getDefaultSortDirection() {
        return $this->_defaultSortDirection;
    }

    public function hideBeforeLoad() {
        $this->setCSSStyle('display: none; ');
        $this->_hide_before_load = true;
    }

    public function showBeforeLoad() {
        $this->_hide_before_load = false;
    }

    public function doHideBeforeLoad() {
        return $this->_hide_before_load;
    }

    public function getDisplayLength() {
        return $this->_lengthDisplay;
    }

    public function setDisplayLength($length) {
        if (!in_array($length, array(1, 5, 10, 20, 25, 30, 50, 100, 200, -1))) {
            return false;
        }
        $this->_lengthDisplay = $length;
    }

    public function setIdColumnKey($key) {
        $this->_idColumnKey = $key;
    }

    public function getIdColumnKey() {
        return $this->_idColumnKey;
    }

    /**
     * @return boolean
     */
    public function isInfoBlock() {
        return $this->_infoBlock;
    }

    /**
     * @param boolean $infoBlock
     */
    public function setInfoBlock($infoBlock) {
        $this->_infoBlock = (bool)$infoBlock;
    }

    /**
     * @return bool
     */
    public function isPagination()
    {
        return $this->_pagination;
    }

    /**
     * @param bool $pagination
     */
    public function setPagination($pagination)
    {
        $this->_pagination = $pagination;
    }

    /**
     * @return string
     */
    public function getPaginationAlign()
    {
        return $this->_paginationAlign;
    }

    /**
     * @param string $paginationAlign
     */
    public function setPaginationAlign($paginationAlign)
    {
        $this->_paginationAlign = $paginationAlign;
    }

    /**
     * @return string
     */
    public function getPaginationLayout()
    {
        return $this->_paginationLayout;
    }

    /**
     * @param string $paginationLayout
     */
    public function setPaginationLayout($paginationLayout)
    {
        $this->_paginationLayout = $paginationLayout;
    }

    /**
     * @return boolean
     */
    public function isSimpleResponsive() {
        return $this->_simpleResponsive;
    }

    /**
     * @param boolean $simpleResponsive
     */
    public function setSimpleResponsive($simpleResponsive) {
        $this->_simpleResponsive = (bool)$simpleResponsive;
    }
    /**
     * @return boolean
     */
    public function isSimpleHeader() {
        return $this->_simpleHeader;
    }

    /**
     * @param boolean $simpleHeader
     */
    public function setSimpleHeader($simpleHeader) {
        $this->_simpleHeader = (bool)$simpleHeader;
    }
    /**
     * @return boolean
     */
    public function isStripeTable() {
        return $this->_stripeTable;
    }

    /**
     * @param boolean $stripeTable
     */
    public function setStripeTable($stripeTable) {
        $this->_stripeTable = (bool)$stripeTable;
    }

    /**
     * @return boolean
     */
    public function getCellPadding() {
        return $this->_cellPadding;
    }

    /**
     * @param boolean $cellPadding
     */
    public function setCellPadding($cellPadding) {
        $this->_cellPadding = (bool)$cellPadding;
    }
    /**
     * @return boolean
     */
    public function isRemoveBorders()
    {
        return $this->_removeBorders;
    }

    /**
     * @param boolean $removeBorders
     */
    public function setRemoveBorders($removeBorders)
    {
        $this->_removeBorders = (bool)$removeBorders;
    }

    /**
     * @return string
     */
    public function getBorderCollapse()
    {
        return $this->_borderCollapse;
    }

    /**
     * @param string $borderCollapse
     */
    public function setBorderCollapse($borderCollapse)
    {
        $this->_borderCollapse = $borderCollapse;
    }

    /**
     * @return int
     */
    public function getBorderSpacing()
    {
        return $this->_borderSpacing;
    }

    /**
     * @param int $borderSpacing
     */
    public function setBorderSpacing($borderSpacing)
    {
        $this->_borderSpacing = (int)$borderSpacing;
    }
    /**
     * @return boolean
     */
    public function getVerticalScrollHeight() {
        return $this->_verticalScrollHeight;
    }

    /**
     * @param boolean $verticalScrollHeight
     */
    public function setVerticalScrollHeight($verticalScrollHeight) {
        $this->_verticalScrollHeight = (bool)$verticalScrollHeight;
    }

    /**
     * @return boolean
     */
    public function isGlobalSearch() {
        return $this->_globalSearch;
    }

    /**
     * @param boolean $globalSearch
     */
    public function setGlobalSearch($globalSearch) {
        $this->_globalSearch = (bool)$globalSearch;
    }

    /**
     * @return boolean
     */
    public function isShowRowsPerPage() {
        return $this->_showRowsPerPage;
    }

    /**
     * @param boolean $showRowsPerPage
     */
    public function setShowRowsPerPage($showRowsPerPage) {
        $this->_showRowsPerPage = (bool)$showRowsPerPage;
    }

    /**
     * @return array
     */
    public function getEditButtonsDisplayed() {
        return $this->_editButtonsDisplayed;
    }

    /**
     * @param array $editButtonsDisplayed
     */
    public function setEditButtonsDisplayed(array $editButtonsDisplayed) {
        $this->_editButtonsDisplayed = $editButtonsDisplayed;
    }

    public function isEnableDuplicateButton()
    {
        return $this->_enableDuplicateButton;
    }

    public function setEnableDuplicateButton($enableDuplicateButton)
    {
        $this->_enableDuplicateButton = $enableDuplicateButton;
    }

    /**
     * @return string
     */
    public function getTableSkin()
    {
        return $this->_tableSkin;
    }

    /**
     * @param string $tableSkin
     */
    public function setTableSkin($tableSkin)
    {
        $this->_tableSkin = $tableSkin;
    }

    /**
     * @return mixed
     */
    public function getTableFontColorSettings()
    {
        return $this->_tableFontColorSettings;
    }

    /**
     * @param mixed $tableFontColorSettings
     */
    public function setTableFontColorSettings($tableFontColorSettings)
    {
        $this->_tableFontColorSettings = $tableFontColorSettings;
    }

    /**
     * @return int
     */
    public function getTableBorderRemoval()
    {
        return $this->_tableBorderRemoval;
    }

    /**
     * @param int $tableBorderRemoval
     */
    public function setTableBorderRemoval($tableBorderRemoval)
    {
        $this->_tableBorderRemoval = $tableBorderRemoval;
    }

    /**
     * @return int
     */
    public function getTableBorderRemovalHeader()
    {
        return $this->_tableBorderRemovalHeader;
    }

    /**
     * @param int $tableBorderRemovalHeader
     */
    public function setTableBorderRemovalHeader($tableBorderRemovalHeader)
    {
        $this->_tableBorderRemovalHeader = $tableBorderRemovalHeader;
    }


    /**
     * @return string
     */
    public function getTableCustomCss()
    {
        return $this->_tableCustomCss;
    }


    /**
     * @return string
     */
    public function getPdfPaperSize()
    {
        return $this->_pdfPaperSize;
    }

    /**
     * @param string $pdfPaperSize
     */
    public function setPdfPaperSize($pdfPaperSize)
    {
        $this->_pdfPaperSize = $pdfPaperSize;
    }

    /**
     * @return string
     */
    public function getPdfPageOrientation()
    {
        return $this->_pdfPageOrientation;
    }

    /**
     * @param string $pdfPageOrientation
     */
    public function setPdfPageOrientation($pdfPageOrientation)
    {
        $this->_pdfPageOrientation = $pdfPageOrientation;
    }


    /**
     * @param string $tableCustomCss
     */
    public function setTableCustomCss($tableCustomCss)
    {
        $this->_tableCustomCss = $tableCustomCss;
    }

    public function __construct($connection = null) {
        //[<-- Full version -->]//
        // connect to MySQL if enabled
        if (WDT_ENABLE_MYSQL && (Connection::isSeparate($connection))) {
            $this->_db = Connection::create($connection);
            $this->connection = $connection;
        }
        //[<--/ Full version -->]//
        if (self::$wdt_internal_idcount == 0) {
            $this->_firstOnPage = true;
        }
        self::$wdt_internal_idcount++;
        $this->_id = 'table_' . self::$wdt_internal_idcount;
    }

    public function wdtDefineColumnsWidth($widthsArray) {
        if (empty($this->_wdtIndexedColumns)) {
            throw new WDTException('wpDataTable reports no columns are defined!');
        }
        if (!is_array($widthsArray)) {
            throw new WDTException('Incorrect parameter passed!');
        }
        if (wdtTools::isArrayAssoc($widthsArray)) {
            foreach ($widthsArray as $name => $value) {
                if (!isset($this->_wdtNamedColumns[$name])) {
                    continue;
                }
                $this->_wdtNamedColumns[$name]->setWidth($value);
            }
        } else {
            // if width is provided in indexed array
            foreach ($widthsArray as $name => $value) {
                $this->_wdtIndexedColumns[$name]->setWidth($value);
            }
        }
    }

    public function setColumnsPossibleValues($valuesArray) {
        if (empty($this->_wdtIndexedColumns)) {
            throw new WDTException('No columns in the table!');
        }
        if (!is_array($valuesArray)) {
            throw new WDTException('Valid array of width values is required!');
        }
        if (WDTTools::isArrayAssoc($valuesArray)) {
            foreach ($valuesArray as $key => $value) {
                if (!isset($this->_wdtNamedColumns[$key])) {
                    continue;
                }
                $possibleValues = $this->_wdtNamedColumns[$key]->getPossibleValuesList();
                if (empty($possibleValues)) {
                    $this->_wdtNamedColumns[$key]->setPossibleValues($value);
                }
            }
        } else {
            foreach ($valuesArray as $key => $value) {
                $this->_wdtIndexedColumns[$key]->setPossibleValues($value);
            }
        }
    }

    public function getHiddenColumnCount() {
        $count = 0;
        foreach ($this->_wdtIndexedColumns as $dataColumn) {
            if (!$dataColumn->isVisible()) {
                $count++;
            }
        }
        return $count;
    }

    //[<-- Full version -->]//
    public function enableServerProcessing() {
        $this->_serverProcessing = true;
    }

    public function disableServerProcessing() {
        $this->_serverProcessing = false;
    }

    public function serverSide() {
        return $this->_serverProcessing;
    }

    public function setResponsive($responsive) {
        if ($responsive) {
            $this->_responsive = true;
        } else {
            $this->_responsive = false;
        }
    }

    public function isResponsive() {
        return $this->_responsive;
    }

    /**
     * @return string
     */
    public function getResponsiveAction()
    {
        return $this->_responsiveAction;
    }

    /**
     * @param string $responsiveAction
     */
    public function setResponsiveAction($responsiveAction)
    {
        $this->_responsiveAction = $responsiveAction;
    }

    public function enableEditing() {
        $this->_editable = true;
    }

    public function disableEditing() {
        $this->_editable = false;
    }

    public function isEditable() {
        return $this->_editable;
    }

    public function enablePopoverTools() {
        $this->_popoverTools = true;
    }

    public function disablePopoverTools() {
        $this->_popoverTools = false;
    }

    public function popoverToolsEnabled() {
        return $this->_popoverTools;
    }

    public function enableInlineEditing() {
        $this->_inlineEditing = true;
    }

    public function disableInlineEditing() {
        $this->_inlineEditing = false;
    }

    public function inlineEditingEnabled() {
        return $this->_inlineEditing;
    }

    public function filterEnabled() {
        return $this->_showFilter;
    }

    public function enableFilter() {
        $this->_showFilter = true;
    }

    public function disableFilter() {
        $this->_showFilter = false;
    }

    public function setFilteringForm($filteringForm) {
        $this->_filtering_form = (bool)$filteringForm;
    }

    public function getFilteringForm() {
        return $this->_filtering_form;
    }

    public function advancedFilterEnabled() {
        return $this->_showAdvancedFilter;
    }

    public function enableAdvancedFilter() {
        $this->_showAdvancedFilter = true;
    }

    public function disableAdvancedFilter() {
        $this->_showAdvancedFilter = false;
    }

    //[<--/ Full version -->]//

    public function enableGrouping() {
        $this->_groupingEnabled = true;
    }

    public function disableGrouping() {
        $this->_groupingEnabled = false;
    }

    public function groupingEnabled() {
        return $this->_groupingEnabled;
    }

    public function groupByColumn($key) {
        if (!isset($this->_wdtIndexedColumns[$key])
            && !isset($this->_wdtNamedColumns[$key])
        ) {
            throw new WDTException('Column not found!');
        }

        if (!is_numeric($key)) {
            $key = array_search(
                $key,
                array_keys($this->_wdtNamedColumns)
            );
        }

        $this->enableGrouping();
        $this->_wdtColumnGroupIndex = $key;
    }

    /**
     * Returns the index of grouping column
     */
    public function groupingColumnIndex() {
        return $this->_wdtColumnGroupIndex;
    }

    /**
     * Returns the grouping column index
     */
    public function groupingColumn() {
        return $this->_wdtColumnGroupIndex;
    }

    public function countColumns() {
        return count($this->_wdtIndexedColumns);
    }

    public function getColumnKeys() {
        return array_keys($this->_wdtNamedColumns);
    }

    public function setOnlyOwnRows($ownRows) {
        $this->_onlyOwnRows = (bool)$ownRows;
    }

    public function getOnlyOwnRows() {
        return $this->_onlyOwnRows;
    }

    public function setUserIdColumn($column) {
        $this->_userIdColumn = $column;
    }

    public function getUserIdColumn() {
        return $this->_userIdColumn;
    }

    /**
     * @return bool
     */
    public function isShowAllRows()
    {
        return $this->_showAllRows;
    }

    /**
     * @param bool $showAllRows
     */
    public function setShowAllRows($showAllRows)
    {
        $this->_showAllRows = $showAllRows;
    }

    public function getColumns() {
        return $this->_wdtIndexedColumns;
    }

    public function getColumnsByHeaders() {
        return $this->_wdtNamedColumns;
    }

    public function addConditionalFormattingColumn($column) {
        $this->_conditionalFormattingColumns[] = $column;
    }

    public function getConditionalFormattingColumns() {
        return $this->_conditionalFormattingColumns;
    }

    public function createColumnsFromArr($headerArr, $wdtParameters, $wdtColumnTypes) {
        foreach ($headerArr as $key) {
            $dataColumnProperties =                             array();
            $dataColumnProperties['title'] =                    isset($wdtParameters['columnTitles'][$key]) ? $wdtParameters['columnTitles'][$key] : $key;
            $dataColumnProperties['width'] =                    !empty($wdtParameters['columnWidths'][$key]) ? $wdtParameters['columnWidths'][$key] : '';
            $dataColumnProperties['sorting'] =                  isset($wdtParameters['sorting'][$key]) ? $wdtParameters['sorting'][$key] : true;
            $dataColumnProperties['decimalPlaces'] =            isset($wdtParameters['decimalPlaces'][$key]) ? $wdtParameters['decimalPlaces'][$key] : get_option('wdtDecimalPlaces');
            $dataColumnProperties['orig_header'] =              $key;
            $dataColumnProperties['exactFiltering'] =           !empty($wdtParameters['exactFiltering'][$key]) ? $wdtParameters['exactFiltering'][$key] : false;
            $dataColumnProperties['filterLabel'] =              isset($wdtParameters['filterLabel'][$key]) ? $wdtParameters['filterLabel'][$key] : null;
            $dataColumnProperties['searchInSelectBox'] =        !empty($wdtParameters['searchInSelectBox'][$key]) ? $wdtParameters['searchInSelectBox'][$key] : false;
            $dataColumnProperties['searchInSelectBoxEditing'] = !empty($wdtParameters['searchInSelectBoxEditing'][$key]) ? $wdtParameters['searchInSelectBoxEditing'][$key] : false;
            $dataColumnProperties['checkboxesInModal'] =        isset($wdtParameters['checkboxesInModal'][$key]) ? $wdtParameters['checkboxesInModal'][$key] : null;
            $dataColumnProperties['filterDefaultValue'] =       isset($wdtParameters['filterDefaultValue'][$key]) ? $wdtParameters['filterDefaultValue'][$key] : null;
            $dataColumnProperties['possibleValuesType'] =       !empty($wdtParameters['possibleValuesType'][$key]) ? $wdtParameters['possibleValuesType'][$key] : 'read';
            $dataColumnProperties['possibleValuesAddEmpty'] =   !empty($wdtParameters['possibleValuesAddEmpty'][$key]) ? $wdtParameters['possibleValuesAddEmpty'][$key] : false;
            $dataColumnProperties['possibleValuesAjax'] =       !empty($wdtParameters['possibleValuesAjax'][$key]) ? $wdtParameters['possibleValuesAjax'][$key] : 10;
            $dataColumnProperties['foreignKeyRule'] =           isset($wdtParameters['foreignKeyRule'][$key]) ? $wdtParameters['foreignKeyRule'][$key] : '';
            $dataColumnProperties['editingDefaultValue'] =      isset($wdtParameters['editingDefaultValue'][$key]) ? $wdtParameters['editingDefaultValue'][$key] : '';
            $dataColumnProperties['linkTargetAttribute'] =      isset($wdtParameters['linkTargetAttribute'][$key]) ? $wdtParameters['linkTargetAttribute'][$key] : '';
            $dataColumnProperties['linkNoFollowAttribute'] =    isset($wdtParameters['linkNoFollowAttribute'][$key]) ? $wdtParameters['linkNoFollowAttribute'][$key] : false;
            $dataColumnProperties['linkNoreferrerAttribute'] = isset($wdtParameters['linkNoreferrerAttribute'][$key]) ? $wdtParameters['linkNoreferrerAttribute'][$key] : false;
            $dataColumnProperties['linkSponsoredAttribute'] = isset($wdtParameters['linkSponsoredAttribute'][$key]) ? $wdtParameters['linkSponsoredAttribute'][$key] : false;
            $dataColumnProperties['linkButtonAttribute'] =      isset($wdtParameters['linkButtonAttribute'][$key]) ? $wdtParameters['linkButtonAttribute'][$key] : false;
            $dataColumnProperties['linkButtonLabel'] =          isset($wdtParameters['linkButtonLabel'][$key]) ? $wdtParameters['linkButtonLabel'][$key] : '';
            $dataColumnProperties['linkButtonClass'] =          isset($wdtParameters['linkButtonClass'][$key]) ? $wdtParameters['linkButtonClass'][$key] : '';
            $dataColumnProperties['rangeSlider'] =              !empty($wdtParameters['rangeSlider'][$key]) ? $wdtParameters['rangeSlider'][$key] : false;
            $dataColumnProperties['parentTable'] =              $this;
            $dataColumnProperties['globalSearchColumn'] =       isset($wdtParameters['globalSearchColumn'][$key]) ? $wdtParameters['globalSearchColumn'][$key] : false;
            $dataColumnProperties = apply_filters('wpdt_filter_data_column_properties', $dataColumnProperties, $wdtParameters, $key);
            /** @var WDTColumn $tableColumnClass */
            $tableColumnClass = static::$_columnClass;
            $dataColumn = NULL;

            if (isset($wdtColumnTypes[$key])) {
                /** @var WDTColumn $dataColumn */
                $dataColumn = $tableColumnClass::generateColumn($wdtColumnTypes[$key], $dataColumnProperties);
                $dataColumn = apply_filters('wpdatatables_extend_datacolumn_object', $dataColumn, $dataColumnProperties);
                if ($wdtColumnTypes[$key] === 'formula') {
                    /** @var FormulaWDTColumn $dataColumn */
                    if (!empty($wdtParameters['columnFormulas'][$key])) {
                        $dataColumn->setFormula($wdtParameters['columnFormulas'][$key]);
                        if ($this->serverSide()) {
                            $dataColumn->setSorting(false);
                            $dataColumn->setSearchable(false);
                        }
                    } else {
                        $dataColumn->setFormula('');
                    }
                }

                do_action('wpdatatables_columns_from_arr', $this, $dataColumn, $wdtColumnTypes, $key);

            }

            if ($dataColumn != null && $dataColumn->getPossibleValuesType() == 'foreignkey' && $dataColumn->getForeignKeyRule() != null) {
                $foreignKeyData = $this->joinWithForeignWpDataTable($dataColumn->getOriginalHeader(), $dataColumn->getForeignKeyRule(), $this->getDataRows());
                $this->_dataRows = $foreignKeyData['dataRows'];
                $dataColumn->setPossibleValues($foreignKeyData['distinctValues']);
            }

            $this->_wdtIndexedColumns[] = $dataColumn;
            $this->_wdtNamedColumns[$key] = &$this->_wdtIndexedColumns[count($this->_wdtIndexedColumns) - 1];
        }

    }

    public function getColumnHeaderOffset($key) {
        $keys = $this->getColumnKeys();
        if (!empty($key) && in_array($key, $keys)) {
            return array_search($key, $keys);
        } else {
            return -1;
        }
    }

    public function getColumnDefinitions() {
        $defs = array();
        foreach ($this->_wdtIndexedColumns as $key => &$dataColumn) {
            $def = $dataColumn->getColumnJSON($key);
            $def->aTargets = array($key);
            $defs[] = json_encode($def);
        }
        return implode(', ', $defs);
    }

    /**
     * Get column filter definitions
     *
     * @return string
     */
    public function getColumnFilterDefinitions() {
        $columnDefinitions = array();
        foreach ($this->_wdtIndexedColumns as $key => $dataColumn) {

            /** @var WDTColumn $dataColumn */
            $columnDefinition = $dataColumn->getJSFilterDefinition();

            if ($this->getFilteringForm()) {
                $columnDefinition->sSelector = '#' . $this->getId() . '_' . $key . '_filter';
            }

            $columnDefinitions[] = json_encode($columnDefinition);
        }
        return implode(', ', $columnDefinitions);
    }

    /**
     * Get column editing definitions
     *
     * @return string
     */
    public function getColumnEditingDefinitions() {
        $columnDefinitions = array();
        foreach ($this->_wdtIndexedColumns as $key => $dataColumn) {

            /** @var WDTColumn $dataColumn */
            $columnDefinition = $dataColumn->getJSEditingDefinition();

            $columnDefinitions[] = json_encode($columnDefinition);
        }
        return implode(', ', $columnDefinitions);
    }

    /**
     * Get WDTColumn by column original header
     *
     * @param $originalHeader
     * @return bool|mixed
     */
    public function getColumn($originalHeader) {
        if (!isset($originalHeader)
            || (!isset($this->_wdtNamedColumns[$originalHeader])
                && !isset($this->_wdtIndexedColumns[$originalHeader]))
        ) {
            return false;
        }
        if (!is_int($originalHeader)) {
            return $this->_wdtNamedColumns[$originalHeader];
        }

        return $this->_wdtIndexedColumns[$originalHeader];
    }

    /**
     * Generates the structure in memory needed to render the tables
     *
     * @param array $rawDataArr Array of data for the table content
     * @param array $wdtParameters Array of rendering parameters
     * @return bool Result of generation
     */
    public function arrayBasedConstruct($rawDataArr, $wdtParameters) {

        if (empty($rawDataArr)) {
            if (!isset($wdtParameters['data_types'])) {
                $rawDataArr = array(0 => array('No data' => 'No data'));
            } else {
                $arrayEntry = array();
                foreach ($wdtParameters['data_types'] as $cKey => $cType) {
                    $arrayEntry[$cKey] = $cKey;
                }
                $rawDataArr[] = $arrayEntry;
            }
            $this->setNoData(true);
        }

        $headerArr = WDTTools::extractHeaders($rawDataArr);
        //[<-- Full version insertion #09 -->]//
        if (!empty($wdtParameters['columnTitles'])) {
            $headerArr = array_unique(
                array_merge(
                    $headerArr,
                    array_keys($wdtParameters['columnTitles'])
                )
            );
        }

        $wdtColumnTypes = isset($wdtParameters['data_types']) ? $wdtParameters['data_types'] : array();

        if (empty($wdtColumnTypes)) {
            $wdtColumnTypes = WDTTools::detectColumnDataTypes($rawDataArr, $headerArr);
        }

        if (empty($wdtColumnTypes)) {
            foreach ($headerArr as $key) {
                $wdtColumnTypes[$key] = 'string';
            }
        }

        $this->_wdtColumnTypes = $wdtColumnTypes;

        if (!$this->getNoData()) {
            $this->_dataRows = $rawDataArr;
        }

        $this->createColumnsFromArr($headerArr, $wdtParameters, $wdtColumnTypes);

        if (empty($wdtParameters['dates_detected'])
            && count(array_intersect(array('date', 'datetime', 'time'), $wdtColumnTypes))
        ) {
            foreach ($wdtColumnTypes as $key => $columnType) {
                $currentDateFormat = isset($wdtParameters['dateInputFormat'][$key]) ? $wdtParameters['dateInputFormat'][$key] : null;
                if (in_array($columnType, array('date', 'datetime', 'time'))) {
                    foreach ($this->_dataRows as &$dataRow) {
                        $dataRow[$key] = WDTTools::wdtConvertStringToUnixTimestamp($dataRow[$key], $currentDateFormat);
                    }
                }
            }
        }

        if (!in_array($wdtParameters['tableType'], array('mysql', 'manual')) && count(array_intersect(array('float', 'int'), $wdtColumnTypes))) {
            $numberFormat = get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1;
            foreach ($wdtColumnTypes as $key => $columnType) {
                if ($columnType === 'float') {
                    foreach ($this->_dataRows as &$dataRow) {
                        if ($numberFormat == 1) {
                            $dataRow[$key] = str_replace(',', '.', str_replace('.', '', $dataRow[$key]));
                        } else {
                            $dataRow[$key] = str_replace(',', '', $dataRow[$key]);
                        }
                    }
                }
                if ($columnType === 'int') {
                    foreach ($this->_dataRows as &$dataRow) {
                        if ($numberFormat == 1) {
                            $dataRow[$key] = str_replace('.', '', $dataRow[$key]);
                        } else {
                            $dataRow[$key] = str_replace(',', '', $dataRow[$key]);
                        }
                    }
                }
                if ($columnType === 'string') {
                    foreach ($this->_dataRows as &$dataRow) {
                        if (is_float($dataRow[$key]) || is_int($dataRow[$key])) {
                            $dataRow[$key] = strval($dataRow[$key]);
                        }
                    }
                }
            }
        }

        //[<-- Full version -->]//
        // Calculate formula columns
        if (in_array('formula', $wdtColumnTypes)) {
            $this->calculateFormulaCells();
        }

        do_action('wpdatatables_custom_populate_cells',$this, $wdtColumnTypes);
        //[<--/ Full version -->]//

        return true;

    }

    //[<-- Full version -->]//

    /**
     * Helper function that helps to calculate the formula-based cells
     */
    public function calculateFormulaCells() {

        foreach (array_keys($this->_wdtColumnTypes, 'formula') as $column_key) {
            $headers = array();

            $formula = $this->getColumn($column_key)->getFormula();
            $headersInFormula = $this->detectHeadersInFormula($formula);
            $headers = WDTTools::sanitizeHeaders($headersInFormula);
            foreach ($this->_dataRows as &$row) {
                try {
                    $row[$column_key] =
                        self::solveFormula(
                            $formula,
                            $headers,
                            $row
                        );
                } catch (Exception $e) {
                    $row[$column_key] = 0;
                }
            }
        }
    }

    //[<--/ Full version -->]//

    public function hideColumn($dataColumnIndex) {
        if (!isset($dataColumnIndex)
            || !isset($this->_wdtNamedColumns[$dataColumnIndex])
        ) {
            throw new WDTException('A column with provided header does not exist.');
        }
        $this->_wdtNamedColumns[$dataColumnIndex]->setIsVisible(false);
    }

    public function showColumn($dataColumnIndex) {
        if (!isset($dataColumnIndex)
            || !isset($this->_wdtNamedColumns[$dataColumnIndex])
        ) {
            throw new WDTException('A column with provided header does not exist.');
        }
        $this->_wdtNamedColumns[$dataColumnIndex]->setIsVisible(true);
    }


    public function getCell($dataColumnIndex, $rowKey) {
        if (!isset($dataColumnIndex)
            || !isset($rowKey)
        ) {
            throw new WDTException('Please provide the column key and the row key');
        }
        if (!isset($this->_dataRows[$rowKey])) {
            throw new WDTException('Row does not exist.');
        }
        if (!isset($this->_wdtNamedColumns[$dataColumnIndex])
            && !isset($this->_wdtIndexedColumns[$dataColumnIndex])
        ) {
            throw new WDTException('Column does not exist.');
        }
        return $this->_dataRows[$rowKey][$dataColumnIndex];
    }

    public function returnCellValue($cellContent, $wdtColumnIndex) {
        if (!isset($wdtColumnIndex)) {
            throw new WDTException('Column index not provided!');
        }
        if (!isset($this->_wdtNamedColumns[$wdtColumnIndex])) {
            throw new WDTException('Column index out of bounds!');
        }
        return $this->_wdtNamedColumns[$wdtColumnIndex]->returnCellValue($cellContent);
    }

    public function getDataRows() {
        return $this->_dataRows;
    }

    public function setDataRows($dataRows) {
        return $this->_dataRows= $dataRows;
    }

    public function getDataRowsFormatted() {
        $dataRowsFormatted = array();
        foreach ($this->_dataRows as $dataRow) {
            $formattedRow = array();
            foreach ($dataRow as $colHeader => $cellValue) {
                $formattedRow[$colHeader] = $this->returnCellValue($cellValue, $colHeader);
            }
            $dataRowsFormatted[] = $formattedRow;
        }
        return $dataRowsFormatted;
    }

    public function getRow($index) {
        if (!isset($index) || !isset($this->_dataRows[$index])) {
            throw new WDTException('Invalid row index!');
        }
        $rowArray = &$this->_dataRows[$index];
        apply_filters('wdt_get_row', $rowArray);
        return $rowArray;
    }

    public function addDataColumn(&$dataColumn) {
        if (!($dataColumn instanceof WDTColumn)) {
            throw new WDTException('Please provide a wpDataTable column.');
        }
        apply_filters('wdt_add_column', $dataColumn);
        $this->_wdtIndexedColumns[] = &$dataColumn;
        return true;
    }

    public function addColumns(&$dataColumns) {
        if (!is_array($dataColumns)) {
            throw new WDTException('Please provide an array of wpDataTable column objects.');
        }
        apply_filters('wdt_add_columns', $dataColumns);
        foreach ($dataColumns as &$dataColumn) {
            $this->addDataColumn($dataColumn);
        }
    }

    /**
     * Helper method to calculate value for the specified column and function
     *
     * @param $columnKey
     * @param $function
     * @return float|int
     */
    public function calcColumnFunction($columnKey, $function) {
        $result = null;
        if ($function == 'sum' || $function == 'avg') {
            foreach ($this->getDataRows() as $wdtRowDataArr) {
                $result += $wdtRowDataArr[$columnKey];
            }

            if ($function == 'avg') {
                $result = $result / count($this->getDataRows());

                require_once(WDT_ROOT_PATH . 'source/class.float.wpdatacolumn.php');
                $floatCol = new FloatWDTColumn();
                $floatCol->setParentTable($this);
                return $floatCol->prepareCellOutput($result);
            }

        } else if ($function == 'min') {
            foreach ($this->getDataRows() as $wdtRowDataArr) {
                if (!isset($result) || $wdtRowDataArr[$columnKey] < $result) {
                    $result = $wdtRowDataArr[$columnKey];
                }
            }
        } else if ($function == 'max') {
            foreach ($this->getDataRows() as $wdtRowDataArr) {
                if (!isset($result) || $wdtRowDataArr[$columnKey] > $result) {
                    $result = $wdtRowDataArr[$columnKey];
                }
            }
        }

        return $this->returnCellValue($result, $columnKey);

    }

    /**
     * Helper method to generate values for SUM, MIN, MAX, AVG
     */
    private function calcColumnsAggregateFuncs() {
        if (empty($this->_aggregateFuncsRes)) {
            $this->_aggregateFuncsRes = array(
                'sum' => array(),
                'avg' => array(),
                'min' => array(),
                'max' => array()
            );
        }
        foreach ($this->getColumnKeys() as $columnKey) {
            if (
            in_array(
                $columnKey,
                array_unique(
                    array_merge(
                        $this->getSumColumns(),
                        $this->getAvgColumns(),
                        $this->getMinColumns(),
                        $this->getMaxColumns()
                    )
                )
            )
            )
                foreach ($this->getDataRows() as $wdtRowDataArr) {
                    if (
                    in_array(
                        $columnKey,
                        array_unique(
                            array_merge(
                                $this->getSumColumns(),
                                $this->getAvgColumns()
                            )

                        )
                    )
                    ) {
                        if (!isset($this->_aggregateFuncsRes['sum'][$columnKey])) {
                            $this->_aggregateFuncsRes['sum'][$columnKey] = 0;
                        }

                        if ($wdtRowDataArr[$columnKey] != null && is_numeric($wdtRowDataArr[$columnKey])) {
                            $this->_aggregateFuncsRes['sum'][$columnKey] += $wdtRowDataArr[$columnKey];
                        }
                    }
                    if (
                    in_array(
                        $columnKey,
                        $this->getMinColumns()
                    )
                    ) {
                        if (
                            !isset($this->_aggregateFuncsRes['min'][$columnKey])
                            || ($wdtRowDataArr[$columnKey] < $this->_aggregateFuncsRes['min'][$columnKey]
                                && is_numeric($wdtRowDataArr[$columnKey]))
                        ) {
                            $this->_aggregateFuncsRes['min'][$columnKey] = $wdtRowDataArr[$columnKey];
                        }
                    }

                    if (
                    in_array(
                        $columnKey,
                        $this->getMaxColumns()
                    )
                    ) {
                        if (
                            !isset($this->_aggregateFuncsRes['max'][$columnKey])
                            || ($wdtRowDataArr[$columnKey] > $this->_aggregateFuncsRes['max'][$columnKey]
                                && is_numeric($wdtRowDataArr[$columnKey]))
                        ) {
                            $this->_aggregateFuncsRes['max'][$columnKey] = $wdtRowDataArr[$columnKey];
                        }
                    }
                }

            if (in_array($columnKey, $this->getAvgColumns())) {
                $filteredRowsNumber = count(array_filter(array_column($this->getDataRows(),$columnKey)));
                $notNullRowNumber = $filteredRowsNumber !== 0 ? $filteredRowsNumber : count($this->getDataRows());
                $this->_aggregateFuncsRes['avg'][$columnKey] = $this->_aggregateFuncsRes['sum'][$columnKey] / $notNullRowNumber;
            }
        }
    }

    /**
     * Return LIKE expression for given vendor
     *
     * @param string $vendor
     * @param string $table
     * @param string $column
     * @param string $pattern
     * @return string
     */
    private function getLikeExpression($vendor, $table, $column, $pattern) {
        if ($vendor === Connection::$MYSQL) {
            //$search .= '`' . $tableName . '`.`' . $aColumns[$i] . "` LIKE '%" . $columnSearch . "%' ";
            return "$table.$column LIKE '$pattern'";
        }

        if ($vendor === Connection::$MSSQL) {
            return "$table.$column LIKE '$pattern'";
        }

        if ($vendor === Connection::$POSTGRESQL) {
            return "LOWER(CAST($table.$column AS TEXT)) LIKE LOWER('$pattern') ";
        }
    }

    /**
     * Return LIKE expression for given vendor
     *
     * @param string $vendor
     * @param string $filterType
     * @param string $value
     * @return string
     */
    private function getDateTimeExpression($vendor, $filterType, $value) {
        $wpDateFormat = get_option('wdtDateFormat');

        $date_format = '';

        if ($vendor === Connection::$MYSQL) {
            if ($filterType != 'time-range') {
                $date_format = str_replace('m', '%m', $wpDateFormat);
                $date_format = str_replace('M', '%M', $date_format);
                $date_format = str_replace('Y', '%Y', $date_format);
                $date_format = str_replace('y', '%y', $date_format);
                $date_format = str_replace('d', '%d', $date_format);
                $date_format = str_replace('F', '%M', $date_format);
                $date_format = str_replace('j', '%d', $date_format);
            }
            if ($filterType == 'datetime-range'
                || $filterType == 'time-range'
            ) {
                $date_format .= ' ' . get_option('wdtTimeFormat');
                $date_format = str_replace('H', '%H', $date_format);
                $date_format = str_replace('h', '%h', $date_format);
                $date_format = str_replace('i', '%i', $date_format);
                $date_format = str_replace('A', '%p', $date_format);
                $date_format = str_replace('s', '%s', $date_format);
            }

            return "STR_TO_DATE('$value', '$date_format')";
        }

        if ($vendor === Connection::$MSSQL) {
            $type = $filterType === 'time-range' ? 'time' : 'datetime';

            switch($wpDateFormat) {
                case ('d/m/Y'):
                    return "CONVERT($type, '$value', 103)";
                case ('m/d/Y'):
                    return "CONVERT($type, '$value', 101)";
                case ('d.m.Y'):
                    return "CONVERT($type, '$value', 104)";
                case ('m.d.Y'):
                    return "CONVERT($type, REPLACE ('$value', '.' , '/') , 101)";
                case ('d-m-Y'):
                    return "CONVERT($type, '$value', 105)";
                case ('m-d-Y'):
                    return "CONVERT($type, '$value', 110)";
                case ('d.m.y'):
                    return "CONVERT($type, '$value', 4)";
                case ('d.m'):
                    return "LEFT(CONVERT($type, '$value', 4), 5)";
                case ('m.d.y'):
                    return "CONVERT($type, REPLACE ('$value', '.' , '-'), 10)";
                case ('d-m-y'):
                    return "CONVERT($type, '$value', 5)";
                case ('m-d-y'):
                    return "CONVERT($type, '$value', 10)";
                case ('d M Y'):
                    return "CONVERT($type, '$value', 106)";
                case ('M Y'):
                    return "CONVERT($type, '$value', 23)";
                case ('F Y'):
                    return "CONVERT($type, '$value', 23)";
                case ('F j, Y'):
                    return "CONVERT($type, '$value', 107)";
                case ('j. F Y.'):
                    return "CONVERT($type, REPLACE ('$value', '.' , '') , 106)";
                case ('Y'):
                    return "CONVERT($type, '$value', 23)";
            }
        }

        if ($vendor === Connection::$POSTGRESQL) {
            $type = $filterType === 'time-range' ? '::TIME' : '';

            if ($filterType != 'time-range') {
                $date_format = str_replace('M', 'Mon', $wpDateFormat);
                $date_format = str_replace('m', 'MM', $date_format);
                $date_format = str_replace('Y', 'YYYY', $date_format);
                $date_format = str_replace('y', 'YY', $date_format);
                $date_format = str_replace('d', 'DD', $date_format);
                $date_format = str_replace('F', 'Month', $date_format);
                $date_format = str_replace('j', 'DD', $date_format);
            }

            if ($filterType == 'datetime-range'
                || $filterType == 'time-range'
            ) {
                $date_format .= ' ' . get_option('wdtTimeFormat');
                $date_format = str_replace('H', 'HH24', $date_format);
                $date_format = str_replace('h:', 'HH12:', $date_format);
                $date_format = str_replace('i', 'MI', $date_format);

                if (substr($value, -2) === 'AM') {
                    $date_format = str_replace('A', 'AM', $date_format);
                }

                if (substr($value, -2) === 'PM') {
                    $date_format = str_replace('A', 'PM', $date_format);
                }
            }

            if ($filterType === 'time-range' && strlen(explode(':', $value)[0]) === 1) {
                $value = '0' . $value;
            }

            $date_format = trim($date_format);

            return "to_timestamp('$value', '$date_format')$type";
        }
    }

    /**
     * Return aggregate function results
     *
     * @param $columnKey
     * @param $function
     * @return mixed
     */
    public function getColumnsAggregateFuncsResult($columnKey, $function) {
        if (!isset($this->_aggregateFuncsRes[$function][$columnKey])) {
            $this->calcColumnsAggregateFuncs();
        }
        return $this->_aggregateFuncsRes[$function][$columnKey];
    }

    //[<-- Full version -->]//
    public function queryBasedConstruct($query, $queryParams = array(), $wdtParameters = array(), $init_read = false) {
        global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9, $wpdb;


        $vendor = Connection::getVendor($this->connection);
        $isMySql = $vendor === Connection::$MYSQL;
        $isMSSql = $vendor === Connection::$MSSQL;
        $isPostgreSql = $vendor === Connection::$POSTGRESQL;

        $leftSysIdentifier = Connection::getLeftColumnQuote($vendor);
        $rightSysIdentifier = Connection::getRightColumnQuote($vendor);

        $query = wdtSanitizeQuery($query);
        $query = WDTTools::applyPlaceholders($query);

        $parser = new PHPSQLParser(false, true);
        $creator = new PHPSQLCreator();

        $query = apply_filters('wpdatatables_filter_query_before_limit', $query, $this->getWpId());

        $parsedQuery = $parser->parse($query, true);

        $foreignKeyJoin = '';
        $parsedOrderBy = '';
        $parsedLimit = '';
        $msSqlParsedLimit = '';
        $postgreSqlParsedLimit = '';
        $parsedSearch = '';
        $msSqlParsedSearch = '';
        $postgreSqlParsedSearch = '';
        $parsedOnlyOwnRows = '';

        $tableName = isset($parsedQuery['FROM']) ? $parsedQuery['FROM'][0]['table'] : '';

        if (isset($parsedQuery['DROP']) ||
            isset($parsedQuery['INSERT']) ||
            isset($parsedQuery['UPDATE']) ||
            isset($parsedQuery['DELETE']) ||
            isset($parsedQuery['EXPLAIN']) ||
            isset($parsedQuery['DESCRIBE']) ||
            isset($parsedQuery['CREATE INDEX']) ||
            isset($parsedQuery['CREATE TABLE']))
            throw new Exception('SQL is not valid. Commands not allowed!');

        // Adding limits if necessary
        if (!empty($wdtParameters['limit']) &&
            (strpos(strtolower($query), 'limit') === false) &&
            empty($wdtParameters['disable_limit'])
        ) {
            if ($isMySql) {
                $parsedLimit = $parser->parse(' LIMIT ' . $wdtParameters['limit'], true);
            }

            if ($isPostgreSql) {
                $postgreSqlParsedLimit = " LIMIT {$wdtParameters['limit']}";
            }

            if ($isMSSql) {
                $msSqlParsedLimit = " OFFSET 0 ROWS FETCH NEXT {$wdtParameters['limit']} ROWS ONLY";
            }
        }

        // Server-side requests
        if ($this->serverSide()) {

            if (!isset($_POST['draw'])) {
                if (empty($wdtParameters['disable_limit'])) {
                    $lengthValue = $this->getDisplayLength();
                    if ($lengthValue != -1){
                        if ($isMySql) {
                            $parsedLimit = $parser->parse(' LIMIT ' . $this->getDisplayLength(), true);
                        }

                        if ($isPostgreSql) {
                            $postgreSqlParsedLimit = " LIMIT {$this->getDisplayLength()}";
                        }

                        if ($isMSSql) {
                            $msSqlParsedLimit = " OFFSET 0 ROWS FETCH NEXT {$this->getDisplayLength()} ROWS ONLY";
                        }
                    }
                }
            } else {
                // Server-side params
                $aColumns = array_keys($wdtParameters['columnTitles']);

                $startValue = (int)$_POST['start'];
                $lengthValue = (int)$_POST['length'];

                if (isset($startValue) &&
                    $lengthValue != '-1' &&
                    empty($wdtParameters['disable_limit'])
                    ) {
                    if ($isMySql) {
                        $parsedLimit = $parser->parse("LIMIT " . addslashes($startValue) . ", " .
                            addslashes($lengthValue), true);
                    }

                    if ($isPostgreSql) {
                        $postgreSqlParsedLimit = ' LIMIT ' . addslashes($lengthValue) . ' OFFSET ' . addslashes($startValue);
                    }

                    if ($isMSSql) {
                        $msSqlParsedLimit = ' OFFSET ' . addslashes($startValue) . ' ROWS FETCH NEXT ' .
                            addslashes($lengthValue) . ' ROWS ONLY';
                    }
                }

                // Adding sort parameters for AJAX if necessary
                if (isset($_POST['order'])) {
                    $orderBy = "ORDER BY  ";
                    $orderDirection = 'ASC';
                    for ($i = 0; $i < count($_POST['order']); $i++) {

                        if(isset($_POST['order'][$i]['dir']) && in_array($_POST['order'][$i]['dir'],['asc','desc'])){
                            $tempOrderDirection = addslashes($_POST['order'][$i]['dir']);
                            $orderDirection = $tempOrderDirection === 'asc' ? 'ASC' : 'DESC';
                        }
                        if (isset($wdtParameters['foreignKeyRule'][$_POST['columns'][$_POST['order'][$i]['column']]['name']])) {
                            $foreignKeyRule = $wdtParameters['foreignKeyRule'][$_POST['columns'][$_POST['order'][$i]['column']]['name']];
                            $columnName = $_POST['columns'][$_POST['order'][$i]['column']]['name'];
                            $joinedTable = WPDataTable::loadWpDataTable($foreignKeyRule->tableId);
                            $joinedTableContent = WDTTools::applyPlaceholders( $joinedTable->getTableContent());
                            $storeColumn = WDTConfigController::loadSingleColumnFromDB($foreignKeyRule->storeColumnId);
                            $displayColumn = WDTConfigController::loadSingleColumnFromDB($foreignKeyRule->displayColumnId);
                            if ($joinedTable->getTableType() == 'mysql' || $joinedTable->getTableType() == 'manual') {
                                $foreignKeyJoin .= 'FROM LEFT JOIN (' . $joinedTableContent . ') AS wdttemptbl' . $i .
                                    ' ON ' . $tableName . '.' . $columnName . ' = wdttemptbl' . $i . '.' . $storeColumn['orig_header'] . ' ';
                                $orderBy .= 'wdttemptbl' . $i . '.' . $displayColumn['orig_header'] . ' ' . $orderDirection . ', ';
                            } else {
                                $sortedForeignRows = $joinedTable->getDataRows();
                                usort($sortedForeignRows, function ($a, $b, $displayColumn) {
                                    return $a[$displayColumn['orig_header']] > $b[$displayColumn['orig_header']];
                                });
                                $sortedForeignRows = implode(array_map($sortedForeignRows, $storeColumn['orig_header']), ', ');
                                $orderBy .= 'FIELD (' . $columnName . ', ' . $sortedForeignRows . ') ' . $orderDirection . ', ';
                            }
                        } else {
                            if(isset($aColumns[$_POST['order'][$i]['column']]))
                            $orderBy .= $leftSysIdentifier . $aColumns[$_POST['order'][$i]['column']] . "{$rightSysIdentifier} " . $orderDirection . ", ";
                        }
                    }

                    $orderBy = substr_replace($orderBy, "", -2);
                    if ($orderBy == "ORDER BY") {
                        $orderBy = "";
                    }

                    if ($vendor === Connection::$MYSQL) {
                        $parsedOrderBy = $parser->parse($orderBy);
                    }

                    if ($vendor === Connection::$MSSQL) {
                        $msSqlParsedOrderBy = ' ' . $orderBy;
                    }

                    if ($vendor === Connection::$POSTGRESQL) {
                        $postgreSqlParsedOrderBy = ' ' . $orderBy;
                    }
                }

                // Global search
                $search = '';
                if (!empty($_POST['search']['value'])) {
                    $search = " (";
                    for ($i = 0; $i < count($aColumns); $i++) {
                        if (isset($_POST['columns'][$i]) && $_POST['columns'][$i]['searchable'] == "true") {
                            if (in_array($wdtParameters['data_types'][$_POST['columns'][$i]['name']], array('date', 'datetime', 'time'))) {
                                continue;
                            } else {
                                if (is_null($wdtParameters['foreignKeyRule'][$_POST['columns'][$i]['name']])) {
                                    $search .= $this->getLikeExpression($vendor, $leftSysIdentifier . $tableName . $rightSysIdentifier, $leftSysIdentifier . $aColumns[$i] . $rightSysIdentifier, '%' . addslashes($_POST['search']['value']) . '%') . ' OR ';
                                } else {
                                    $foreignKeyRule = $wdtParameters['foreignKeyRule'][$_POST['columns'][$i]['name']];
                                    $joinedTable = WPDataTable::loadWpDataTable($foreignKeyRule->tableId);
                                    $distinctValues = $joinedTable->getDistinctValuesForColumns($foreignKeyRule);
                                    $distinctValues = array_map('strtolower', $distinctValues);

                                    $filteredValues = preg_grep('~' . preg_quote(strtolower($_POST['search']['value']), '~') . '~', $distinctValues);

                                    if (!empty($filteredValues)) {
                                        $search .= $leftSysIdentifier . $tableName . "{$rightSysIdentifier}.{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} IN (" . implode(', ', array_keys($filteredValues)) . ")  OR ";
                                    } else {
                                        $search .= $leftSysIdentifier . $tableName . "{$rightSysIdentifier}.{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} = '" . addslashes($_POST['search']['value']) . "' OR ";
                                    }
                                }

                            }
                        }
                    }
                    $search = substr_replace($search, "", -3);
                    $search .= ')';
                }

                // Individual column filtering
                for ($i = 0; $i < count($aColumns); $i++) {

                    $columnSearchFromTable = false;
                    $columnSearchFromDefaultValue = false;

                    //Apply placeholders when they are set in filter predefined value
                    if (isset($wdtParameters['filterDefaultValue'][$i]))
                        $wdtParameters['filterDefaultValue'][$i]=  WDTTools::applyPlaceholders($wdtParameters['filterDefaultValue'][$i]);

                    if (isset($_POST['columns'][$i]['search']) &&
	                    $_POST['columns'][$i]['search']['value'] != '' &&
	                    $_POST['columns'][$i]['search']['value'] != '|') {
                		$columnSearchFromTable = true;
	                }
	                if (($_POST['draw'] == 1 || $columnSearchFromTable == true) &&
                        (isset($wdtParameters['filterDefaultValue'][$i]) &&
	                    $wdtParameters['filterDefaultValue'][$i] !== '' &&
	                    $wdtParameters['filterDefaultValue'][$i] !== '|' )) {
                		$columnSearchFromDefaultValue = true;
	                }

                    if ( isset($_POST['columns'][$i]) && $_POST['columns'][$i]['searchable'] == true && ($columnSearchFromTable || $columnSearchFromDefaultValue)) {

                    	$columnSearch = $columnSearchFromTable ? $_POST['columns'][$i]['search']['value'] : $wdtParameters['filterDefaultValue'][$i];
                        if (!empty($search)) {
                            $search .= ' AND ';
                        }
                        if (isset($wdtParameters['filterTypes'][$aColumns[$i]])) {
                            switch ($wdtParameters['filterTypes'][$aColumns[$i]]) {
                                case 'number-range':
                                    list($left, $right) = explode('|', $columnSearch);
                                    if ($left !== '') {
                                        if (get_option('wdtNumberFormat') == 1) {
                                            $left = str_replace(',', '.', str_replace('.',  '', $left));
                                        } else {
                                            $left = str_replace(',',  '', $left);
                                        }
                                        $left = (float)$left;
                                        $search .= $leftSysIdentifier . $tableName . "{$rightSysIdentifier}.{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} >= $left ";
                                    }
                                    if ($right !== '') {
                                        if (get_option('wdtNumberFormat') == 1) {
                                            $right = str_replace(',', '.', str_replace('.',  '', $right));
                                        } else {
                                            $right = str_replace(',',  '', $right);
                                        }
                                        $right = (float)$right;
                                        if (!empty($search) && $left !== '') {
                                            $search .= ' AND ';
                                        }
                                        $search .= $leftSysIdentifier . $tableName . "{$rightSysIdentifier}.{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} <= $right ";
                                    }
                                    break;
                                case 'date-range':
                                case 'time-range':
                                case 'datetime-range':
                                    list($left, $right) = explode('|', $columnSearch);

                                    if ($left && $right) {
                                        $search .= $leftSysIdentifier . $tableName . "{$rightSysIdentifier}.{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} BETWEEN {$this->getDateTimeExpression($vendor, $wdtParameters['filterTypes'][$aColumns[$i]], $left)} AND {$this->getDateTimeExpression($vendor, $wdtParameters['filterTypes'][$aColumns[$i]], $right)} ";
                                    } elseif ($left) {
                                        $search .= $leftSysIdentifier . $tableName . "{$rightSysIdentifier}.{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} >= {$this->getDateTimeExpression($vendor, $wdtParameters['filterTypes'][$aColumns[$i]], $left)} ";
                                    } elseif ($right) {
                                        $search .= $leftSysIdentifier . $tableName . "{$rightSysIdentifier}.{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} <= {$this->getDateTimeExpression($vendor, $wdtParameters['filterTypes'][$aColumns[$i]], $right)} ";
                                    }
                                    break;
                                case 'select':
                                    if ($columnSearch == 'possibleValuesAddEmpty') {
                                        $search .= $leftSysIdentifier . $tableName . "{$rightSysIdentifier}.{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} = '' OR {$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} IS NULL";
                                    } else {
                                        if ($wdtParameters['exactFiltering'][$aColumns[$i]] == 1) {
                                            $search .= $leftSysIdentifier . $tableName . "{$rightSysIdentifier}.{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} = '" . $columnSearch . "' ";
                                        } else {
                                            $search .= $this->getLikeExpression($vendor, $leftSysIdentifier . $tableName . $rightSysIdentifier, $leftSysIdentifier . $aColumns[$i] . $rightSysIdentifier, '%' . $columnSearch . '%');
                                        }
                                    }
                                    break;
                                case 'checkbox':
                                case 'multiselect':
                                    if ($wdtParameters['exactFiltering'][$aColumns[$i]] == 1) {
                                        // Trim regex parts for first and last one
                                        if (strpos($columnSearch, '$') !== false) {
                                            $checkboxSearches = explode('$|^', $columnSearch);
                                            $checkboxSearches[0] = substr($checkboxSearches[0], 1);
                                            if (count($checkboxSearches) > 1) {
                                                $checkboxSearches[count($checkboxSearches) - 1] = substr($checkboxSearches[count($checkboxSearches) - 1], 0, -1);
                                            } else {
                                                $checkboxSearches[0] = substr($checkboxSearches[0], 0, -1);
                                            }
                                        } else {
                                            $checkboxSearches = explode('|', $columnSearch);
                                        }
                                    } else {
                                        if (strpos($columnSearch, '||')) {
                                            $checkboxSearches = preg_split('/(?<!\|)\|(?!\|)/', $columnSearch);
                                        } else {
                                            $checkboxSearches = explode('|', $columnSearch);
                                        }
                                    }
                                    $j = 0;
                                    $search .= " (";
                                    foreach ($checkboxSearches as $checkboxSearch) {
                                        if ($j > 0) {
                                            $search .= " OR ";
                                        }

                                        if ($wdtParameters['exactFiltering'][$aColumns[$i]] == 1) {
                                            $search .= $leftSysIdentifier . $tableName . "{$rightSysIdentifier}.{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} = '" . $checkboxSearch . "' ";
                                        } else {
                                            $search .= $this->getLikeExpression($vendor, $leftSysIdentifier . $tableName . $rightSysIdentifier, $leftSysIdentifier . $aColumns[$i] . $rightSysIdentifier, '%' . $checkboxSearch . '%');
                                        }

                                        $j++;
                                    }
                                    $search .= ") ";
                                    break;
                                case 'text':
                                case 'number':
                                    if (is_null($wdtParameters['foreignKeyRule'][$_POST['columns'][$i]['name']])) {
                                        if ($wdtParameters['exactFiltering'][$aColumns[$i]] == 1) {
                                            $search .= $leftSysIdentifier . $tableName . "{$rightSysIdentifier}.{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} = '" . $columnSearch . "' ";
                                        } else {
                                            if ($wdtParameters['filterTypes'][$aColumns[$i]] == 'number') {
                                                $search .= $this->getLikeExpression($vendor, $leftSysIdentifier . $tableName . $rightSysIdentifier, $leftSysIdentifier . $aColumns[$i] . $rightSysIdentifier, $columnSearch . '%');
                                            } else {
                                                $search .= $this->getLikeExpression($vendor, $leftSysIdentifier . $tableName . $rightSysIdentifier, $leftSysIdentifier . $aColumns[$i] . $rightSysIdentifier, '%' . $columnSearch . '%');
                                            }
                                        }
                                    } else {
                                        $foreignKeyRule = $wdtParameters['foreignKeyRule'][$_POST['columns'][$i]['name']];
                                        $joinedTable = WPDataTable::loadWpDataTable($foreignKeyRule->tableId);
                                        $distinctValues = $joinedTable->getDistinctValuesForColumns($foreignKeyRule);
                                        $distinctValues = array_map('strtolower', $distinctValues);

                                        if ($wdtParameters['exactFiltering'][$aColumns[$i]] == 1) {
                                            $filteredValues = preg_grep('~^' . preg_quote(strtolower($columnSearch), null) . '$~', $distinctValues);
                                        } else {
                                            $filteredValues = preg_grep('~' . preg_quote(strtolower($columnSearch), '~') . '~', $distinctValues);
                                        }

                                        if (!empty($filteredValues)) {
                                            $search .= $leftSysIdentifier . $tableName . "{$rightSysIdentifier}.{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} IN (" . implode(', ', array_keys($filteredValues)) . ")";
                                        } else {
                                            $search .= $leftSysIdentifier . $tableName . "{$rightSysIdentifier}.{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} = '" . $columnSearch . "' ";
                                        }
                                    }
                                    break;
                                default:
                                    $search .= $this->getLikeExpression($vendor, $leftSysIdentifier . $tableName . $rightSysIdentifier, $leftSysIdentifier . $aColumns[$i] . $rightSysIdentifier, '%' . $columnSearch . '%');
                            }
                        }
                    }
                }

                if ($search) {
                    if ($isMySql) {
                        $parsedSearch = $parser->parse('WHERE ' . $search);
                    }

                    if ($isMSSql) {
                        $msSqlParsedSearch = ' ' . $search;
                    }

                    if ($isPostgreSql) {
                        $postgreSqlParsedSearch = ' ' . $search;
                    }
                }

            }

        }

        // Add the filtering by user ID column, if requested
    if ( (!isset($_POST['showAllRows']) && $this->_onlyOwnRows) || (isset($_POST['showAllRows']) && $this->_onlyOwnRows && !$this->isShowAllRows())) {
            $userIdColumnCondition = "WHERE {$leftSysIdentifier}" . $this->_userIdColumn . "{$rightSysIdentifier} = " . get_current_user_id();
            $parsedOnlyOwnRows = $parser->parse($userIdColumnCondition);
        }

        // The serverside return scenario
        if ($this->isAjaxReturn()) {

            /**
             * 1. Forming the query
             */

            if ($isMySql) {
                array_unshift($parsedQuery['SELECT'], array(
                        'expr_type' => 'reserved',
                        'alias' => '',
                        'base_expr' => 'SQL_CALC_FOUND_ROWS',
                        'sub_tree' => '',
                        'delim' => ''
                    )
                );
            } else if ($isMSSql || $isPostgreSql) {
                array_unshift($parsedQuery['SELECT'], array(
                        'expr_type' => 'reserved',
                        'alias' => '',
                        'base_expr' => 'COUNT(*) OVER() as count',
                        'sub_tree' => '',
                        'delim' => ','
                    )
                );
            }

            if ($foreignKeyJoin) {

                $parsedForeignKeyJoin = $parser->parse($foreignKeyJoin);
                array_push($parsedQuery['FROM'], $parsedForeignKeyJoin['FROM'][1]);

                foreach ($parsedQuery['SELECT'] as &$selectClause) {
                    if ($selectClause['expr_type'] == 'colref') {
                        if (strpos($selectClause['base_expr'], '.') === false) {
                            $selectClause['base_expr'] = $tableName . '.' . $selectClause['base_expr'];
                        }
                    }
                }

                if (isset($parsedQuery['WHERE'])) {
                    foreach ($parsedQuery['WHERE'] as &$whereClause) {
                        if ($whereClause['expr_type'] == 'colref') {
                            if (strpos($whereClause['base_expr'], '.') === false) {
                                $whereClause['base_expr'] = $tableName . '.' . $whereClause['base_expr'];
                            }
                        }
                    }
                }
            }

            if ($vendor === Connection::$MYSQL) {
                if ($parsedOrderBy) {
                    if (isset($parsedQuery['ORDER'])) {
                        array_unshift($parsedQuery['ORDER'], $parsedOrderBy['ORDER'][0]);
                    } else {
                        $parsedQuery = array_merge($parsedQuery, $parsedOrderBy);
                    }
                }

                if ($parsedSearch) {
                    if (isset($parsedQuery['WHERE'])) {
                        array_push($parsedQuery['WHERE'], ['expr_type' => 'operator', 'base_expr' => 'AND', 'sub_tree' => false]);
                        $parsedQuery['WHERE'] = array_merge($parsedQuery['WHERE'], $parsedSearch['WHERE']);
                    } else {
                        $parsedQuery['WHERE'] = $parsedSearch['WHERE'];
                    }
                }
            }


            if ($parsedOnlyOwnRows) {
                if (isset($parsedQuery['WHERE'])) {
                    array_push($parsedQuery['WHERE'], ['expr_type' => 'operator', 'base_expr' => 'AND', 'sub_tree' => false]);
                    $parsedQuery['WHERE'] = array_merge($parsedQuery['WHERE'], $parsedOnlyOwnRows['WHERE']);
                } else {
                    $parsedQuery['WHERE'] = $parsedOnlyOwnRows['WHERE'];
                }
            }

            if ($isMySql) {
                if ($parsedLimit) {
                    $parsedQuery = array_merge($parsedQuery, $parsedLimit);
                }
            }

            /**
             * 2. Executing the queries
             */
            // The main query
            $query = $creator->create($parsedQuery);

            // Add Limit Rule if Vendor is MSSQL
            if ($isMSSql) {
                $query .= ($msSqlParsedSearch ? ((strpos( $query, 'WHERE' ) || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $msSqlParsedSearch) : '') . $msSqlParsedOrderBy . $msSqlParsedLimit;
            }

            // Add Limit Rule if Vendor is PostgreSQL
            if ($isPostgreSql) {
                $query .= ($postgreSqlParsedSearch ? (( strpos( $query, 'WHERE' ) || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $postgreSqlParsedSearch) : '') . $postgreSqlParsedOrderBy . $postgreSqlParsedLimit;
            }

            $query = apply_filters('wpdatatables_filter_mysql_query', $query, $this->getWpId());

            if (Connection::isSeparate($this->connection)) {
                $main_res_dataRows = $this->_db->getAssoc($query, $queryParams);
            } else {
                // querying using the WP driver otherwise
                $main_res_dataRows = $wpdb->get_results($query, ARRAY_A);
            }
            // result length after filtering
            if (Connection::isSeparate($this->connection)) {
                if ($isMySql) {
                    $resultLength = $this->_db->getField('SELECT FOUND_ROWS()');
                } elseif(($isMSSql  || $isPostgreSql) && !empty($main_res_dataRows)) {
                    $resultLength = $main_res_dataRows[0]['count'];
                } else {
                    $resultLength = 0;
                }
            } else {
                // querying using the WP driver otherwise
                $resultLength = $wpdb->get_row('SELECT FOUND_ROWS()', ARRAY_A);
                $resultLength = $resultLength['FOUND_ROWS()'];
            }
            // total data length
            if (Connection::isSeparate($this->connection)) {
                $totalLengthQuery = 'SELECT COUNT(*) FROM ' . $tableName;
                $totalLengthQuery = apply_filters('wpdatatables_filter_total_length_query', $totalLengthQuery , $this->getWpId());
                // If "Only own rows" options is defined, do not count other user's rows
                if (isset($userIdColumnCondition)) {
                    $totalLengthQuery .= ' ' . $userIdColumnCondition;
                }
                $totalLength = $this->_db->getField($totalLengthQuery);
            } else {
                // querying using the WP driver otherwise
                $totalLengthQuery = 'SELECT COUNT(*) as cnt_total FROM ' . $tableName;
                $totalLengthQuery = apply_filters('wpdatatables_filter_total_length_query', $totalLengthQuery , $this->getWpId());
                // If "Only own rows" options is defined, do not count other user's rows
                if (isset($userIdColumnCondition)) {
                    $totalLengthQuery .= ' ' . $userIdColumnCondition;
                }
                $totalLength = $wpdb->get_row($totalLengthQuery, ARRAY_A);
                $totalLength = $totalLength['cnt_total'];
            }

            /**
             * 3. Forming the output
             */
            // base array
            $output = array(
                "draw" => (int)$_POST['draw'],
                "recordsTotal" => $totalLength,
                "recordsFiltered" => $resultLength ? $resultLength : 0,
                "data" => array()
            );

            // Create the supplementary array of column objects
            $colObjs = $this->prepareColumns($wdtParameters);

            // reformat output array and reorder as user wanted
            $output['data'] = $this->prepareOutputData($main_res_dataRows, $wdtParameters, $colObjs);
            $output['data'] = apply_filters('wpdatatables_custom_prepare_output_data', $output['data'], $this, $main_res_dataRows, $wdtParameters, $colObjs);

            // If aggregate functions are requested
            $sumColumns = $this->getSumColumns();
            $avgColumns = $this->getAvgColumns();
            $maxColumns = $this->getMaxColumns();
            $minColumns = $this->getMinColumns();

            if (!empty($sumColumns) || !empty($avgColumns) || !empty($maxColumns) || !empty($minColumns)) {
                // Remove the LIMIT, ORDER BY and JOIN from query
                $functionsParsedQuery = $parsedQuery;
                unset($functionsParsedQuery['LIMIT']);
                unset($functionsParsedQuery['ORDER']);
                $functionsParsedQuery['FROM'] = array_slice($functionsParsedQuery['FROM'], 0, 1);

                if (!empty($sumColumns) || !empty($avgColumns)) {
                    $functionsParsedQuery['SELECT'] = [];
                    $output['sumAvgColumns'] = array_unique(array_merge($this->getSumColumns(), $this->getAvgColumns()), SORT_REGULAR);
                    $output['sumFooterColumns'] = $this->getSumFooterColumns();
                    $output['avgFooterColumns'] = $this->getAvgFooterColumns();

                    foreach ($output['sumAvgColumns'] as $key => $columnTitle) {
                        array_unshift($functionsParsedQuery['SELECT'], array(
                                'expr_type' => 'colref',
                                'base_expr' => 'SUM(' . $leftSysIdentifier . $columnTitle . $rightSysIdentifier . ') 
                                                 AS ' . $leftSysIdentifier . $columnTitle . $rightSysIdentifier,
                                'delim' => ','
                            )
                        );
                        if ($columnTitle === end($output['sumAvgColumns'])) {
                            $functionsParsedQuery['SELECT'][$key]['delim'] = '';
                        }
                    }

                    $sumFunctionQuery = $creator->create($functionsParsedQuery);

                    if ($isPostgreSql) {
                        $sumFunctionQuery .= ($postgreSqlParsedSearch ? ((strpos( $sumFunctionQuery, 'WHERE' ) || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $postgreSqlParsedSearch) : '');
                    }

                    if ($isMSSql) {
                        $sumFunctionQuery .= ($msSqlParsedSearch ? ((strpos( $sumFunctionQuery, 'WHERE' ) || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $msSqlParsedSearch) : '');
                    }

                    // execute query
                    if (Connection::isSeparate($this->connection)) {
                        $sql = Connection::create($this->connection);
                        $sumRow = $sql->getRow($sumFunctionQuery);
                        $sql = null;
                    } else {
                        // querying using the WP driver otherwise
                        $sumRow = $wpdb->get_row($sumFunctionQuery, ARRAY_A);
                    }
                    foreach ($this->getSumColumns() as $columnTitle) {
                        if (is_null($sumRow[$columnTitle])) {
                            $sumRow[$columnTitle] = 0;
                        }
                        $output['sumColumnsValues'][$columnTitle] = $colObjs[$columnTitle]->returnCellValue($sumRow[$columnTitle]);
                    }
                    foreach ($this->getAvgColumns() as $columnTitle) {
                        require_once(WDT_ROOT_PATH . 'source/class.float.wpdatacolumn.php');
                        $floatCol = new FloatWDTColumn();

                        $floatCol->setDecimalPlaces($colObjs[$columnTitle]->getDecimalPlaces());
                        $floatCol->setParentTable($this);
                        $nonNullValues = (int)$output['recordsFiltered'];
                        foreach ($main_res_dataRows as $row) {
                            if ($row[$columnTitle] == null)
                                $nonNullValues--;
                        }
                        $output['avgColumnsValues'][$columnTitle] = $nonNullValues != 0 ?
                            $floatCol->returnCellValue(($sumRow[$columnTitle]) / $nonNullValues) : 0;

                    }
                    $output['sumAvgColumns'] = array_flip($output['sumAvgColumns']);
                    $output['sumFooterColumns'] = array_flip($output['sumFooterColumns']);
                    $output['avgFooterColumns'] = array_flip($output['avgFooterColumns']);
                }
                if (!empty($minColumns)) {
                    $functionsParsedQuery['SELECT'] = [];
                    $output['minColumns'] = $this->getMinColumns();
                    $output['minFooterColumns'] = $this->getMinFooterColumns();
                    foreach ($output['minColumns'] as $key => $columnTitle) {
                        array_unshift($functionsParsedQuery['SELECT'], array(
                                'expr_type' => 'colref',
                                'base_expr' => 'MIN(' . $leftSysIdentifier . $columnTitle . $rightSysIdentifier . ') 
                                                 AS ' . $leftSysIdentifier . $columnTitle . $rightSysIdentifier,
                                'delim' => ','
                            )
                        );
                        if ($columnTitle === end($output['minColumns'])) {
                            $functionsParsedQuery['SELECT'][$key]['delim'] = '';
                        }
                    }

                    $minFunctionQuery = $creator->create($functionsParsedQuery);

                    if ($isPostgreSql) {
                        $minFunctionQuery .= ($postgreSqlParsedSearch ? ((strpos( $minFunctionQuery, 'WHERE' ) || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $postgreSqlParsedSearch) : '');
                    }

                    if ($isMSSql) {
                        $minFunctionQuery .= ($msSqlParsedSearch ? ((strpos( $sumFunctionQuery, 'WHERE' ) || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $msSqlParsedSearch) : '');
                    }

                    if (Connection::isSeparate($this->connection)) {
                        $sql = Connection::create($this->connection);
                        $minRow = $sql->getRow($minFunctionQuery);
                        $sql = null;
                    } else {
                        $minRow = $wpdb->get_row($minFunctionQuery, ARRAY_A);
                    }
                    foreach ($this->getMinColumns() as $columnTitle) {
                        if (is_null($minRow[$columnTitle])) {
                            $minRow[$columnTitle] = 0;
                        }
                        $output['minColumnsValues'][$columnTitle] = $colObjs[$columnTitle]->returnCellValue($minRow[$columnTitle]);
                    }
                    $output['minColumns'] = array_flip($output['minColumns']);
                    $output['minFooterColumns'] = array_flip($output['minFooterColumns']);
                }
                if (!empty($maxColumns)) {
                    $functionsParsedQuery['SELECT'] = [];
                    $output['maxColumns'] = $this->getMaxColumns();
                    $output['maxFooterColumns'] = $this->getMaxFooterColumns();
                    foreach ($output['maxColumns'] as $key => $columnTitle) {
                        array_unshift($functionsParsedQuery['SELECT'], array(
                                'expr_type' => 'colref',
                                'base_expr' => 'MAX(' . $leftSysIdentifier . $columnTitle . $rightSysIdentifier . ') 
                                                 AS ' . $leftSysIdentifier . $columnTitle . $rightSysIdentifier,
                                'delim' => ','
                            )
                        );
                        if ($columnTitle === end($output['maxColumns'])) {
                            $functionsParsedQuery['SELECT'][$key]['delim'] = '';
                        }
                    }

                    $maxFunctionQuery = $creator->create($functionsParsedQuery);

                    if ($isPostgreSql) {
                        $maxFunctionQuery .= ($postgreSqlParsedSearch ? ((strpos( $maxFunctionQuery, 'WHERE' ) || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $postgreSqlParsedSearch) : '');
                    }

                    if ($isMSSql) {
                        $maxFunctionQuery .= ($msSqlParsedSearch ? ((strpos( $maxFunctionQuery, 'WHERE' ) || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $msSqlParsedSearch) : '');
                    }

                    if (Connection::isSeparate($this->connection)) {
                        $sql = Connection::create($this->connection);
                        $maxRow = $sql->getRow($maxFunctionQuery);
                        $sql = null;
                    } else {
                        $maxRow = $wpdb->get_row($maxFunctionQuery, ARRAY_A);
                    }
                    foreach ($this->getMaxColumns() as $columnTitle) {
                        if (is_null($maxRow[$columnTitle])) {
                            $maxRow[$columnTitle] = 0;
                        }
                        $output['maxColumnsValues'][$columnTitle] = $colObjs[$columnTitle]->returnCellValue($maxRow[$columnTitle]);
                    }
                    $output['maxColumns'] = array_flip($output['maxColumns']);
                    $output['maxFooterColumns'] = array_flip($output['maxFooterColumns']);
                }
            }

            /**
             * 4. Returning the result
             */
            return json_encode($output);
        } else {

            if ($isMySql) {
                if ($parsedLimit) {
                    $parsedQuery = array_merge($parsedQuery, $parsedLimit);
                }
            }

            if ($parsedOnlyOwnRows) {
                if (isset($parsedQuery['WHERE'])) {
                    array_push($parsedQuery['WHERE'], ['expr_type' => 'operator', 'base_expr' => 'AND', 'sub_tree' => false]);
                    $parsedQuery['WHERE'] = array_merge($parsedQuery['WHERE'], $parsedOnlyOwnRows['WHERE']);
                } else {
                    $parsedQuery['WHERE'] = $parsedOnlyOwnRows['WHERE'];
                }
            }

            $query = $creator->create($parsedQuery);

            // Add Limit Rule if Vendor is MSSQL
            if ($isMSSql) {
                if ($msSqlParsedLimit) {
                    $defaultOrder = isset($parsedQuery['ORDER']) ? '' : ' ORDER BY(SELECT NULL) ';
                    $query .= $defaultOrder . $msSqlParsedLimit;
                }
            }

            if ($isPostgreSql) {
                if ($postgreSqlParsedLimit) {
                    $query .= $postgreSqlParsedLimit;
                }
            }

            // Getting the query result
            if (Connection::isSeparate($this->connection)) {
                $query = apply_filters('wpdatatables_filter_mysql_query', $query, $this->getWpId());
                $res_dataRows = $this->_db->getAssoc($query, $queryParams);
                $mysql_error = $this->_db->getLastError();
            } else {
                $query = apply_filters('wpdatatables_filter_mysql_query', $query, $this->getWpId());
                $res_dataRows = $wpdb->get_results($query, ARRAY_A);
                $mysql_error = $wpdb->last_error;
            }

            if (is_array($res_dataRows) && count($res_dataRows) > 2000) {
                $this->enableServerProcessing();
            }

            // If this is the table initialization from WP-admin, and no data is returned, throw an exception
            if ($init_read && empty($res_dataRows)) {
                if (!strpos($mysql_error, 'doesn\'t exist')) {
                    $msg = __('No data fetched!  <br/> If you are trying to save table for the first time, please enter some date before saving so table could be set accurately. <br/> You can remove it later if you need empty table to start with.', 'wpdatatables');
                }
                $msg .= '<br/><br/>' . __('Rendered query: ', 'wpdatatables') . '<strong>' . $query . '</strong><br/>';
                if (!empty($mysql_error)) {
                    $msg .= __(' MySQL said: ', 'wpdatatables') . $mysql_error;
                }
                throw new Exception($msg);
            }

            // Sending the array to arrayBasedConstruct
            return $this->arrayBasedConstruct($res_dataRows, $wdtParameters);

        }
    }

    /**
     * Create the supplementary array of column objects
     * which we will use for formatting
     * @param $wdtParameters
     * @return array
     */
    public function prepareColumns($wdtParameters) {
        $colObjs = array();
        foreach ($wdtParameters['data_types'] as $dataColumn_key => $dataColumn_type) {
            $tableColumnClass = static::$_columnClass;
            $colObjOptions = array(
                'title' => $wdtParameters['columnTitles'][$dataColumn_key],
                'decimalPlaces' => $wdtParameters['decimalPlaces'][$dataColumn_key],
                'linkTargetAttribute' => $wdtParameters['linkTargetAttribute'][$dataColumn_key],
                'linkNoFollowAttribute' => $wdtParameters['linkNoFollowAttribute'][$dataColumn_key],
                'linkNoreferrerAttribute' => $wdtParameters['linkNoreferrerAttribute'][$dataColumn_key],
                'linkSponsoredAttribute' => $wdtParameters['linkSponsoredAttribute'][$dataColumn_key],
                'linkButtonAttribute' => $wdtParameters['linkButtonAttribute'][$dataColumn_key],
                'linkButtonLabel' => $wdtParameters['linkButtonLabel'][$dataColumn_key],
                'linkButtonClass' => $wdtParameters['linkButtonClass'][$dataColumn_key],
                'rangeSlider' => $wdtParameters['rangeSlider'][$dataColumn_key]
            );
            $colObjOptions = apply_filters('wpdt_filter_supplementary_array_column_object', $colObjOptions, $wdtParameters, $dataColumn_key);
            $colObjs[$dataColumn_key] = $tableColumnClass::generateColumn($dataColumn_type, $colObjOptions);
            $colObjs[$dataColumn_key]->setInputType($wdtParameters['input_types'][$dataColumn_key]);
            $colObjs[$dataColumn_key]->setParentTable($this);
            if ($dataColumn_type == 'int') {
                if (in_array($dataColumn_key, $wdtParameters['skip_thousands']) || ($dataColumn_key == $wdtParameters['idColumn'])) {
                    $colObjs[$dataColumn_key]->setShowThousandsSeparator(false);
                    $this->addColumnsThousandsSeparator($dataColumn_key, 0);
                } else {
                    $this->addColumnsThousandsSeparator($dataColumn_key, 1);
                }
            }
        }
        return $colObjs;
    }

    /**
     * Reformat output array and reorder as user wanted
     * @param $main_res_dataRows
     * @param $wdtParameters
     * @param $colObjs
     * @return mixed
     * @throws WDTException
     */
    public function prepareOutputData($main_res_dataRows, $wdtParameters, $colObjs) {
        $output = [];
        if (!empty($main_res_dataRows)) {
            foreach ($wdtParameters['foreignKeyRule'] as $columnKey => $foreignKeyRule) {
                if ($foreignKeyRule != null) {
                    $foreignKeyData = $this->joinWithForeignWpDataTable($columnKey, $foreignKeyRule, $main_res_dataRows);
                    $main_res_dataRows = $foreignKeyData['dataRows'];
                }
            }

            foreach ($main_res_dataRows as $res_row) {
                $row = array();
                foreach ($wdtParameters['columnOrder'] as $dataColumn_key) {
                    if ($wdtParameters['data_types'][$dataColumn_key] == 'formula') {
                        try {
                            $headers = array();
                            $headersInFormula = $this->detectHeadersInFormula($wdtParameters['columnFormulas'][$dataColumn_key], array_keys($wdtParameters['data_types']));
                            $headers = WDTTools::sanitizeHeaders($headersInFormula);
                            $formulaVal =
                                self::solveFormula(
                                    $wdtParameters['columnFormulas'][$dataColumn_key],
                                    $headers,
                                    $res_row
                                );
                            $row[$dataColumn_key] = apply_filters(
                                'wpdatatables_filter_cell_output',
                                $colObjs[$dataColumn_key]->returnCellValue($formulaVal),
                                $this->_wpId,
                                $dataColumn_key
                            );
                        } catch (Exception $e) {
                            $row[$dataColumn_key] = 0;
                        }
                    } else {
                        if ($dataColumn_key != 'masterdetail'){
                            $row[$dataColumn_key] = apply_filters('wpdatatables_filter_cell_output', $colObjs[$dataColumn_key]->returnCellValue($res_row[$dataColumn_key]), $this->_wpId, $dataColumn_key);
                        }

                    }
                }
                $output[] = $this->formatAjaxQueryResultRow($row);
            }
        }
        return $output;
    }


    /**
     * Formatting row data structure for ajax display table
     * @param $row - key => value pairs as column name and cell value of a row
     * @return array
     */
    protected function formatAjaxQueryResultRow($row) {
        return array_values($row);
    }


    public function jsonBasedConstruct($json, $wdtParameters = array()) {
        $json = WDTTools::applyPlaceholders($json);
        $json = WDTTools::curlGetData($json);
        $json = apply_filters('wpdatatables_filter_json', $json, $this->getWpId());
        return $this->arrayBasedConstruct(json_decode($json, true), $wdtParameters);
    }

    public function XMLBasedConstruct($xml, $wdtParameters = array()) {
        if (!$xml) {
            throw new WDTException('File you provided cannot be found.');
        }
        $xml = WDTTools::applyPlaceholders($xml);
        $XMLObject = simplexml_load_file($xml);
        $XMLObject = apply_filters('wpdatatables_filter_simplexml', $XMLObject, $this->getWpId());
        $XMLArray = WDTTools::convertXMLtoArr($XMLObject);
        foreach ($XMLArray as &$xml_el) {
            if (is_array($xml_el) && array_key_exists('attributes', $xml_el)) {
                $xml_el = $xml_el['attributes'];
            }
        }
        return $this->arrayBasedConstruct($XMLArray, $wdtParameters);
    }

    public function excelBasedConstruct($xls_url, $wdtParameters = array()) {
        ini_set('memory_limit', '2048M');
        if (!$xls_url) {
            throw new WDTException('Excel file not found!');
        }
        if (!file_exists($xls_url)) {
            throw new WDTException('Provided file ' . stripcslashes($xls_url) . ' does not exist!');
        }

        $format = 'xls';
        if (strpos(strtolower($xls_url), '.xlsx')) {
            $objReader = new Xlsx();
        } elseif (strpos(strtolower($xls_url), '.xls')) {
            $objReader = new Xls();
        } elseif (strpos(strtolower($xls_url), '.ods')) {
            $format = 'ods';
            $objReader = new Ods();
        } elseif (strpos(strtolower($xls_url), '.csv')) {
            $format = 'csv';
            $objReader = new Csv();
            $csvDelimiter = stripcslashes(get_option('wdtCSVDelimiter')) ? stripcslashes(get_option('wdtCSVDelimiter')) : WDTTools::detectCSVDelimiter($xls_url);
            $objReader->setDelimiter($csvDelimiter);
        } else {
            throw new WDTException('File format not supported!');
        }
        $xls_url = apply_filters( 'wpdatatables_filter_excel_based_data_url', $xls_url, $this->getWpId() );
        $objPHPExcel = $objReader->load($xls_url);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestDataColumn();

        $headingsArray = $objWorksheet->rangeToArray('A1:' . $highestColumn . '1', null, true, true, true);
        $headingsArray = array_map('trim', $headingsArray[1]);

        $r = -1;
        $namedDataArray = array();

        $dataRows = $objWorksheet->rangeToArray('A2:' . $highestColumn . $highestRow, null, true, true, true);
        for ($row = 2; $row <= $highestRow; ++$row) {
            if (max($dataRows[$row]) !== null) {
                ++$r;
                foreach ($headingsArray as $dataColumnIndex => $dataColumnHeading) {
                    $dataColumnHeading = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataColumnHeading)));
                    $namedDataArray[$r][$dataColumnHeading] = $dataRows[$row][$dataColumnIndex];
                    $currentDateFormat = isset($wdtParameters['dateInputFormat'][$dataColumnHeading]) ? $wdtParameters['dateInputFormat'][$dataColumnHeading] : null;
                    if (!empty($wdtParameters['data_types'][$dataColumnHeading]) && in_array($wdtParameters['data_types'][$dataColumnHeading], array('date', 'datetime', 'time'))) {
                        if ($format === 'xls' || $format === 'ods') {
                            $cell = $objPHPExcel->getActiveSheet()->getCell($dataColumnIndex . '' . $row);
                            if (Date::isDateTime($cell) && $cell->getValue() !== null) {
                                $namedDataArray[$r][$dataColumnHeading] = Date::excelToTimestamp($cell->getValue());
                            } else {
                                $namedDataArray[$r][$dataColumnHeading] = WDTTools::wdtConvertStringToUnixTimestamp($dataRows[$row][$dataColumnIndex], $currentDateFormat);
                            }
                        } elseif ($format === 'csv') {
                            $namedDataArray[$r][$dataColumnHeading] = WDTTools::wdtConvertStringToUnixTimestamp($dataRows[$row][$dataColumnIndex], $currentDateFormat);
                        }
                    }
                }
            }
        }

        // Let arrayBasedConstruct know that dates have been converted to timestamps
        $wdtParameters['dates_detected'] = true;

        $namedDataArray = apply_filters('wpdatatables_filter_excel_array', $namedDataArray, $this->getWpId(), $xls_url);

        return $this->arrayBasedConstruct($namedDataArray, $wdtParameters);
    }

    /**
     * Helper method that renders the modal
     */
    public static function renderModal() {
        include_once WDT_TEMPLATE_PATH . 'frontend/modal.inc.php';
        include_once WDT_TEMPLATE_PATH . 'common/delete_modal.inc.php';
        do_action('wpdatatables_add_custom_template_modal');
    }

    /**
     * Generates table HTML
     * @return string
     */
    public function generateTable($connection) {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $tableContent = $this->renderWithJSAndStyles();

        ob_start();
        include WDT_TEMPLATE_PATH . 'frontend/wrap_template.inc.php';
        if (!self::$modalRendered) {
            if (!is_admin()) {
                add_action('wp_footer', array('WPDataTable', 'renderModal'));
            }
            self::$modalRendered = true;
        }
        $returnData = ob_get_contents();
        ob_end_clean();

        // Generate the style block
        $returnData .= "<style>\n";
        // Columns text before and after
        $returnData .= $this->getColumnsCSS();

        // Table layout
        $customCss = get_option('wdtCustomCss');

        $returnData .= $this->isFixedLayout() ? "table.wpDataTable { table-layout: fixed !important; }\n" : '';
        $returnData .= $this->isWordWrap() ? "table.wpDataTable td, table.wpDataTable th { white-space: normal !important; }\n" : '';

        if ($customCss) {
            $returnData .= stripslashes_deep($customCss);
        }
        if (get_option('wdtNumbersAlign')) {
            $returnData .= "table.wpDataTable td.numdata { text-align: right !important; }\n";
        }

        if (get_option('wdtBorderRemoval')) {
            $returnData .= ".wpDataTablesWrapper table.wpDataTable > tbody > tr > td{ border: none !important; }\n";
        }
        if (get_option('wdtBorderRemovalHeader')) {
            $returnData .= ".wpDataTablesWrapper table.wpDataTable > thead > tr > th{ border: none !important; }\n";
        }
        $returnData .= "</style>\n";

        $returnData .= wdtRenderScriptStyleBlock($this->getWpId());
        $returnData .= wdtTableRenderScriptStyleBlock($this);
        return $returnData;
    }

    /**
     * Function that return table HTML content and
     * enqueue all necessary JS and CSS files
     * @return string
     */
    protected function renderWithJSAndStyles() {

        $this->enqueueJSAndStyles();

        $this->addCSSClass('data-t');

        /** @noinspection PhpUnusedLocalVariableInspection */
        $advancedFilterPosition = get_option('wdtRenderFilter');
        /** @noinspection PhpUnusedLocalVariableInspection */
        $wdtSumFunctionsLabel = get_option('wdtSumFunctionsLabel');
        /** @noinspection PhpUnusedLocalVariableInspection */
        $wdtAvgFunctionsLabel = get_option('wdtAvgFunctionsLabel');
        /** @noinspection PhpUnusedLocalVariableInspection */
        $wdtMinFunctionsLabel = get_option('wdtMinFunctionsLabel');
        /** @noinspection PhpUnusedLocalVariableInspection */
        $wdtMaxFunctionsLabel = get_option('wdtMaxFunctionsLabel');

        ob_start();
        include WDT_TEMPLATE_PATH . 'frontend/table_main.inc.php';
        do_action('wpdatatables_add_custom_modal',$this);
        $tableContent = ob_get_contents();
        ob_end_clean();

        return $tableContent;
    }

    /**
     * Function that enqueue all necessary JS and CSS files for wpDataTable
     */
    protected function enqueueJSAndStyles() {

        WDTTools::wdtUIKitEnqueue();

        wp_enqueue_script('wdt-common', WDT_ROOT_URL . 'assets/js/wpdatatables/admin/common.js', array(), WDT_CURRENT_VERSION, true);
        if (get_option('wdtMinifiedJs')) {
            wp_enqueue_style('wdt-wpdatatables', WDT_CSS_PATH . 'wdt.frontend.min.css', array(),WDT_CURRENT_VERSION);

            wp_enqueue_script('wdt-wpdatatables', WDT_JS_PATH . 'wpdatatables/wdt.frontend.min.js', array('wdt-common'), WDT_CURRENT_VERSION, true);
            wp_localize_script('wdt-wpdatatables', 'wdt_ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ));
        } else {
            wp_enqueue_style('wdt-wpdatatables', WDT_CSS_PATH . 'wpdatatables.min.css', array(),WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-table-tools', WDT_CSS_PATH . 'TableTools.css', array(),WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-datatables-responsive', WDT_CSS_PATH . 'datatables.responsive.css', array(),WDT_CURRENT_VERSION);

            if (WDT_INCLUDE_DATATABLES_CORE) {
                wp_enqueue_script('wdt-datatables', WDT_JS_PATH . 'jquery-datatables/jquery.dataTables.min.js', array(), WDT_CURRENT_VERSION, true);
            }
            if ($this->filterEnabled()) {
                wp_enqueue_script('wdt-advanced-filter', WDT_JS_PATH . 'wpdatatables/wdt.columnFilter.js', array(), WDT_CURRENT_VERSION, true);
                wp_localize_script('wdt-advanced-filter', 'wdt_ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ));
            }
            if ($this->groupingEnabled()) {
                wp_enqueue_script('wdt-row-grouping', WDT_JS_PATH . 'jquery-datatables/jquery.dataTables.rowGrouping.js', array('jquery', 'wdt-datatables'), WDT_CURRENT_VERSION, true);
            }
            if ($this->TTEnabled() || $this->isEditable()) {
                wp_enqueue_script('wdt-buttons', WDT_JS_PATH . 'export-tools/dataTables.buttons.min.js', array('jquery', 'wdt-datatables'), WDT_CURRENT_VERSION, true);
                if ($this->TTEnabled()) {
                    wp_enqueue_script('wdt-buttons-html5', WDT_JS_PATH . 'export-tools/buttons.html5.min.js', array('jquery', 'wdt-datatables'), WDT_CURRENT_VERSION, true);
                    !empty($this->_tableToolsConfig['print']) ? wp_enqueue_script('wdt-button-print', WDT_JS_PATH . 'export-tools/buttons.print.min.js', array('jquery', 'wdt-datatables'), WDT_CURRENT_VERSION, true) : null;
                    !empty($this->_tableToolsConfig['columns']) ? wp_enqueue_script('wdt-button-vis', WDT_JS_PATH . 'export-tools/buttons.colVis.min.js', array('jquery', 'wdt-datatables'), WDT_CURRENT_VERSION, true) : null;
                }
                if ($this->isEditable()) {
                    wp_enqueue_script('wdt-jquery-mask-money', WDT_JS_PATH . 'maskmoney/jquery.maskMoney.js', array('jquery'), WDT_CURRENT_VERSION, true);
                    if ($this->inlineEditingEnabled()) {
                        wp_enqueue_script('wdt-inline-editing', WDT_JS_PATH . 'wpdatatables/wdt.inlineEditing.js', array(), WDT_CURRENT_VERSION, true);
                    }
                }
            }
            if ($this->isResponsive()) {
                wp_enqueue_script('wdt-responsive', WDT_JS_PATH . 'responsive/datatables.responsive.js', array(), WDT_CURRENT_VERSION, true);
            }
            wp_enqueue_script('wdt-funcs-js', WDT_JS_PATH . 'wpdatatables/wdt.funcs.js', array('jquery', 'wdt-datatables', 'wdt-common'), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-wpdatatables', WDT_JS_PATH . 'wpdatatables/wpdatatables.js', array('jquery', 'wdt-datatables'), WDT_CURRENT_VERSION, true);
        }

        $skin = $this->getTableSkin();
        if (empty($skin)) {
            $skin = 'light';
        }
        switch ($skin) {
            case "material":
                $renderSkin = WDT_ASSETS_PATH . 'css/wdt-skins/material.css';
                break;
            case "light":
                $renderSkin = WDT_ASSETS_PATH . 'css/wdt-skins/light.css';
                break;
            case "graphite":
                $renderSkin = WDT_ASSETS_PATH . 'css/wdt-skins/graphite.css';
                break;
            case "aqua":
                $renderSkin = WDT_ASSETS_PATH . 'css/wdt-skins/aqua.css';
                break;
            case "purple":
                $renderSkin = WDT_ASSETS_PATH . 'css/wdt-skins/purple.css';
                break;
            case "dark":
                $renderSkin = WDT_ASSETS_PATH . 'css/wdt-skins/dark.css';
                break;
            default:
                $renderSkin = WDT_ASSETS_PATH . 'css/wdt-skins/material.css';
                break;
        }

        wp_enqueue_style('wdt-skin-' . $skin, $renderSkin, array(), WDT_CURRENT_VERSION);

        wp_enqueue_style('dashicons');

        wp_enqueue_script('underscore');
        !empty($this->_tableToolsConfig['excel']) ? wp_enqueue_script('wdt-js-zip', WDT_JS_PATH . 'export-tools/jszip.min.js', array('jquery'), WDT_CURRENT_VERSION, true) : null;
        !empty($this->_tableToolsConfig['pdf']) ? wp_enqueue_script('wdt-pdf-make', WDT_JS_PATH . 'export-tools/pdfmake.min.js', array('jquery'), WDT_CURRENT_VERSION, true) : null;
        !empty($this->_tableToolsConfig['pdf']) ? wp_enqueue_script('wdt-vfs-fonts', WDT_JS_PATH . 'export-tools/vfs_fonts.js', array('jquery'), WDT_CURRENT_VERSION, true) : null;

        if ($this->isEditable()) {
            wp_enqueue_media();
        }

        do_action('wdt_enqueue_on_frontend', $this);

        wp_localize_script('wdt-common', 'wpdatatables_edit_strings', WDTTools::getTranslationStrings());
        wp_localize_script('wdt-wpdatatables', 'wpdatatables_settings', WDTTools::getDateTimeSettings());
        wp_localize_script('wdt-wpdatatables', 'wpdatatables_frontend_strings', WDTTools::getTranslationStrings());
        wp_localize_script('wdt-advanced-filter', 'wpdatatables_frontend_strings', WDTTools::getTranslationStrings());
    }

    /**
     * * Helper method which prepares the column data from values stored in DB
     * @param $tableData
     * @return array
     */
    public function prepareColumnData($tableData) {

        $returnArray = array(
            'dateInputFormat' => array(),
            'columnFormulas' => array(),
            'columnOrder' => array(),
            'columnTitles' => array(),
            'columnTypes' => array(),
            'columnWidths' => array(),
            'decimalPlaces' => array(),
            'editingDefaultValue' => array(),
            'exactFiltering' => array(),
            'filterDefaultValue' => array(),
            'filterLabel' => array(),
            'searchInSelectBox' => array(),
            'searchInSelectBoxEditing' => array(),
            'checkboxesInModal' => array(),
            'filterTypes' => array(),
            'foreignKeyRule' => array(),
            'possibleValues' => array(),
            'possibleValuesAddEmpty' => array(),
            'possibleValuesAjax' => array(),
            'possibleValuesType' => array(),
            'sorting' => array(),
            'userIdColumnHeader' => NULL,
            'linkTargetAttribute' => array(),
            'linkNoFollowAttribute' => array(),
            'linkNoreferrerAttribute' => array(),
            'linkSponsoredAttribute' => array(),
            'linkButtonAttribute' => array(),
            'linkButtonLabel' => array(),
            'linkButtonClass' => array(),
            'rangeSlider' => array(),
            'globalSearchColumn' => array(),
        );



        if ($tableData) {
            foreach ($tableData->columns as $column) {
                $returnArray['columnOrder'][(int)$column->pos] = $column->orig_header;
                if ($column->display_header) {
                    $returnArray['columnTitles'][$column->orig_header] = $column->display_header;
                }
                if ($column->width) {
                    $returnArray['columnWidths'][$column->orig_header] = $column->width;
                }
                if ($column->type !== 'autodetect') {
                    $returnArray['columnTypes'][$column->orig_header] = $column->type;
                }
                if ($column->type === 'formula') {
                    $returnArray['columnFormulas'][$column->orig_header] = $column->formula;
                }
                if ($tableData->edit_only_own_rows && $tableData->userid_column_id == $column->id) {
                    $returnArray['userIdColumnHeader'] = $column->orig_header;
                }
                if ($column->filterDefaultValue) {
                    $returnArray['filterDefaultValue'][$column->orig_header] = $column->filterDefaultValue;
                }

                $returnArray['dateInputFormat'][$column->orig_header] = isset($column->dateInputFormat) ? $column->dateInputFormat : null;
                $returnArray['decimalPlaces'][$column->orig_header] = isset($column->decimalPlaces) ? $column->decimalPlaces : null;
                $returnArray['editingDefaultValue'][$column->orig_header] = isset($column->editingDefaultValue) ? $column->editingDefaultValue : null;
                $returnArray['exactFiltering'][$column->orig_header] = isset($column->exactFiltering) ? $column->exactFiltering : null;
                $returnArray['filterLabel'][$column->orig_header] = isset($column->filterLabel) ? $column->filterLabel : null;
                $returnArray['searchInSelectBox'][$column->orig_header] = isset($column->searchInSelectBox) ? $column->searchInSelectBox : null;
                $returnArray['searchInSelectBoxEditing'][$column->orig_header] = isset($column->searchInSelectBoxEditing) ? $column->searchInSelectBoxEditing : null;
                $returnArray['checkboxesInModal'][$column->orig_header] = isset($column->checkboxesInModal) ? $column->checkboxesInModal : null;
                $returnArray['foreignKeyRule'][$column->orig_header] = isset($column->foreignKeyRule) ? $column->foreignKeyRule : null;
                $returnArray['possibleValues'][$column->orig_header] = isset($column->valuesList) ? $column->valuesList : null;
                $returnArray['possibleValuesAddEmpty'][$column->orig_header] = isset($column->possibleValuesAddEmpty) ? $column->possibleValuesAddEmpty : null;
                $returnArray['possibleValuesAjax'][$column->orig_header] = isset($column->possibleValuesAjax) ? $column->possibleValuesAjax : null;
                $returnArray['possibleValuesType'][$column->orig_header] = isset($column->possibleValuesType) ? $column->possibleValuesType : null;
                $returnArray['sorting'][$column->orig_header] = isset($column->sorting) ? $column->sorting : null;
                $returnArray['linkTargetAttribute'][$column->orig_header]= isset($column->linkTargetAttribute) ? $column->linkTargetAttribute : null;
                $returnArray['linkNoFollowAttribute'][$column->orig_header] = isset($column->linkNoFollowAttribute) ? $column->linkNoFollowAttribute : null;
                $returnArray['linkNoreferrerAttribute'][$column->orig_header] = isset($column->linkNoreferrerAttribute) ? $column->linkNoreferrerAttribute : null;
                $returnArray['linkSponsoredAttribute'][$column->orig_header] = isset($column->linkSponsoredAttribute) ? $column->linkSponsoredAttribute : null;
                $returnArray['linkButtonAttribute'][$column->orig_header]= isset($column->linkButtonAttribute) ? $column->linkButtonAttribute : null;
                $returnArray['linkButtonLabel'][$column->orig_header]= isset($column->linkButtonLabel) ? $column->linkButtonLabel : null;
                $returnArray['linkButtonClass'][$column->orig_header]= isset($column->linkButtonClass) ? $column->linkButtonClass : null;
                $returnArray['rangeSlider'][$column->orig_header] = isset($column->rangeSlider) ? $column->rangeSlider : null;
                $returnArray['globalSearchColumn'][$column->orig_header] = isset($column->globalSearchColumn) ? $column->globalSearchColumn : null;

                $returnArray = apply_filters('wpdatatables_prepare_column_data', $returnArray, $column);
            }
        }
        return $returnArray;
    }

    //[<-- Full version -->]//

    /**
     * Helper method to detect the headers that are present in formula
     */
    public function detectHeadersInFormula($formula, $headers = null) {
        if (is_null($headers)) {
            $headers = $this->getColumnKeys();
        }

        return WDTTools::getColHeadersInFormula($formula, $headers);
    }

    /**
     * @param $formula String - formula that is passed from formula input
     * @param $headers array - where keys are original column headers and values are sanitized column headers
     * @param $row
     * @return float
     */
    public static function solveFormula($formula, $headers, $row) {
        $vars = array();
        $formula = str_replace(array('$', '_', '&'), '', strtr($formula, $headers));

        foreach ($headers as $origHeader => $sanitizedHeader) {
            $vars[$sanitizedHeader] = (float)$row[$origHeader];
        }

        $parser = new Parser();
        return $parser->solve($formula, $vars);
    }

    /**
     * Tries to calculate formula value for first 5 table rows (or less if table has less than 5 rows)
     * @param String $formula A string representation of formula to calculate
     * @return String A result - first
     */
    public function calcFormulaPreview($formula) {
        $headers = array();
        $headersInFormula = $this->detectHeadersInFormula($formula);
        $headers = WDTTools::sanitizeHeaders($headersInFormula);

        $count = count($this->_dataRows) > 5 ? 5 : count($this->_dataRows);
        $result = __('Unable to calculate', 'wpdatatables');
        if ($count > 0) {
            $res_arr = array();
            try {
                for ($i = 0; $i < $count; $i++) {
                    $res_arr[] = self::solveFormula($formula, $headers, $this->_dataRows[$i]);
                }
                $result = __('Result for first 5 rows: ', 'wpdatatables') . implode(', ', $res_arr);
            } catch (Exception $e) {
                $result = __('Unable to calculate, error message: ', 'wpdatatables') . $e->getMessage();
            }
        }
        return $result;
    }
    //[<--/ Full version -->]//

    /**
     * Helper method which populates the wpdatatables object with passed in parameters and data (stored in DB)
     *
     * @param $tableData
     * @param $columnData
     * @throws WDTException
     * @throws Exception
     */
    public function fillFromData($tableData, $columnData) {
        if (empty($tableData->table_type)) {
            return;
        }
        global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9;

        // Set placeholders
        $wdtVar1 = $wdtVar1 === '' ? $tableData->var1 : $wdtVar1;
        $wdtVar2 = $wdtVar2 === '' ? $tableData->var2 : $wdtVar2;
        $wdtVar3 = $wdtVar3 === '' ? $tableData->var3 : $wdtVar3;
        $wdtVar4 = $wdtVar4 === '' ? $tableData->var4 : $wdtVar4;
        $wdtVar5 = $wdtVar5 === '' ? $tableData->var5 : $wdtVar5;
        $wdtVar6 = $wdtVar6 === '' ? $tableData->var6 : $wdtVar6;
        $wdtVar7 = $wdtVar7 === '' ? $tableData->var7 : $wdtVar7;
        $wdtVar8 = $wdtVar8 === '' ? $tableData->var8 : $wdtVar8;
        $wdtVar9 = $wdtVar9 === '' ? $tableData->var9 : $wdtVar9;

        // Defining column parameters if provided
        $params = array();
        if (isset($tableData->limit)) {
            $params['limit'] = $tableData->limit;
        }
        if (isset($tableData->table_type)) {
            $params['tableType'] = $tableData->table_type;
        }
        if (isset($columnData['columnTypes'])) {
            $params['data_types'] = $columnData['columnTypes'];
        }
        if (isset($columnData['columnTitles'])) {
            $params['columnTitles'] = $columnData['columnTitles'];
        }
        if (isset($columnData['columnFormulas'])) {
            $params['columnFormulas'] = $columnData['columnFormulas'];
        }
        if (isset($columnData['sorting'])) {
            $params['sorting'] = $columnData['sorting'];
        }
        if (isset($columnData['decimalPlaces'])) {
            $params['decimalPlaces'] = $columnData['decimalPlaces'];
        }
        if (isset($columnData['exactFiltering'])) {
            $params['exactFiltering'] = $columnData['exactFiltering'];
        }
        if (isset($columnData['globalSearchColumn'])) {
            $params['globalSearchColumn'] = $columnData['globalSearchColumn'];
        }
        if (isset($columnData['searchInSelectBox'])) {
            $params['searchInSelectBox'] = $columnData['searchInSelectBox'];
        }
        if (isset($columnData['searchInSelectBoxEditing'])) {
            $params['searchInSelectBoxEditing'] = $columnData['searchInSelectBoxEditing'];
        }
        if (isset($columnData['rangeSlider'])) {
            $params['rangeSlider'] = $columnData['rangeSlider'];
        }
        if (isset($columnData['filterDefaultValue'])) {
            $params['filterDefaultValue'] = $columnData['filterDefaultValue'];
        }
        if (isset($columnData['filterLabel'])) {
            $params['filterLabel'] = $columnData['filterLabel'];
        }
        if (isset($columnData['checkboxesInModal'])) {
            $params['checkboxesInModal'] = $columnData['checkboxesInModal'];
        }
        if (isset($columnData['possibleValuesType'])) {
            $params['possibleValuesType'] = $columnData['possibleValuesType'];
        }
        if (isset($columnData['possibleValuesAddEmpty'])) {
            $params['possibleValuesAddEmpty'] = $columnData['possibleValuesAddEmpty'];
        }
        if (isset($columnData['possibleValuesAjax'])) {
            $params['possibleValuesAjax'] = $columnData['possibleValuesAjax'];
        }
        if (isset($columnData['foreignKeyRule'])) {
            $params['foreignKeyRule'] = $columnData['foreignKeyRule'];
        }
        if (isset($columnData['editingDefaultValue'])) {
            $params['editingDefaultValue'] = $columnData['editingDefaultValue'];
        }
        if (isset($columnData['dateInputFormat'])) {
            $params['dateInputFormat'] = $columnData['dateInputFormat'];
        }
        if (isset($columnData['linkTargetAttribute'])){
            $params['linkTargetAttribute'] = $columnData['linkTargetAttribute'];
        }
        if (isset($columnData['linkNoFollowAttribute'])) {
            $params['linkNoFollowAttribute'] = $columnData['linkNoFollowAttribute'];
        }
        if (isset($columnData['linkNoreferrerAttribute'])) {
            $params['linkNoreferrerAttribute'] = $columnData['linkNoreferrerAttribute'];
        }
        if (isset($columnData['linkSponsoredAttribute'])) {
            $params['linkSponsoredAttribute'] = $columnData['linkSponsoredAttribute'];
        }
        if (isset($columnData['linkButtonAttribute'])){
            $params['linkButtonAttribute'] = $columnData['linkButtonAttribute'];
        }
        if (isset($columnData['linkButtonLabel'])){
            $params['linkButtonLabel'] = $columnData['linkButtonLabel'];
        }
        if (isset($columnData['linkButtonClass'])){
            $params['linkButtonClass'] = $columnData['linkButtonClass'];
        }

        $params = apply_filters('wpdt_filter_column_params', $params, $columnData);

        if (isset($tableData->display_length) && $tableData->display_length != 0) {
            $this->setDisplayLength($tableData->display_length);
        } else {
            $this->disablePagination();
        }
        switch ($tableData->table_type) {
            //[<-- Full version -->]//
            case 'mysql' :
            case 'manual' :
                if (!empty($tableData->server_side)) {
                    $this->enableServerProcessing();
                    if (!empty($tableData->auto_refresh)) {
                        $this->setAutoRefresh((int)$tableData->auto_refresh);
                    }
                }
                if (!empty($tableData->editable)) {
                    $editor_roles = isset($tableData->editor_roles) ? $tableData->editor_roles : '';
                    if (wdtCurrentUserCanEdit($editor_roles, $this->getWpId())) {
                        $this->enableEditing();
                        if (!empty($tableData->inline_editing)) {
                            $this->enableInlineEditing();
                        }
                        if (!empty($tableData->popover_tools)) {
                            $this->enablePopoverTools();
                        }
                    }
                    if (!empty($tableData->edit_only_own_rows)
                        && !empty($columnData['userIdColumnHeader'])
                    ) {
                        $this->setOnlyOwnRows(true);
                        $this->setUserIdColumn($columnData['userIdColumnHeader']);
                        if (isset($tableData->advanced_settings)) {
                            $advancedSettingsTable = json_decode($tableData->advanced_settings);
                            $this->setShowAllRows($advancedSettingsTable->showAllRows);
                        } else {
                            $this->setShowAllRows(false);
                        }
                    }
                }
                if (is_admin() && $tableData->table_type == 'manual') {
                    $this->enableEditing();
                }
            $params['disable_limit'] = apply_filters('wpdt_filter_sql_disable_limit', !empty($tableData->disable_limit), $this->connection);

                $this->queryBasedConstruct(
                    $tableData->content,
                    array(),
                    $params,
                    isset($tableData->init_read)
                );
                break;
            //[<--/ Full version -->]//
            case 'xls':
            case 'csv':
                $this->excelBasedConstruct(
                    $tableData->content,
                    $params
                );
                break;
            case 'xml':
                $this->XMLBasedConstruct(
                    $tableData->content,
                    $params
                );
                break;
            case 'json':
                $this->jsonBasedConstruct(
                    $tableData->content,
                    $params
                );
                break;
            case 'serialized':
                $url =  apply_filters('wpdatatables_filter_url_php_array', WDTTools::applyPlaceholders($tableData->content), $this->_wpId);
                $serialized_content = apply_filters('wpdatatables_filter_serialized', WDTTools::curlGetData($url), $this->_wpId);
                $array = unserialize($serialized_content);
                $this->arrayBasedConstruct(
                    $array,
                    $params
                );
                break;
            case 'google_spreadsheet':
                $credentials = get_option('wdtGoogleSettings');
                $token = get_option('wdtGoogleToken');
                if ($credentials) {
                    $googleSheet = new WPDataTable_Google_Sheet();
                    $array = $googleSheet->getData($tableData->content, $credentials, $token);
                } else{
                    $array = WDTTools::extractGoogleSpreadsheetArray($tableData->content);
                }
                $this->arrayBasedConstruct(
                    $array,
                    $params
                );
                break;
            default:
                // Solution for addons
                if (has_action('wpdatatables_generate_' . $tableData->table_type)) {
                    //Check if Server-side processing is integrated in Add-on
                    if (isset($tableData->advanced_settings) && isset(json_decode($tableData->advanced_settings, true)[$tableData->table_type]['hasServerSideIntegration'])) {
                        if (!empty($tableData->server_side)) {
                            $this->enableServerProcessing();
                            if (!empty($tableData->auto_refresh)) {
                                $this->setAutoRefresh((int)$tableData->auto_refresh);
                            }
                        }
                        if (!empty($tableData->editable)) {
                            $editor_roles = isset($tableData->editor_roles) ? $tableData->editor_roles : '';
                            if (wdtCurrentUserCanEdit($editor_roles, $this->getWpId())) {
                                $this->enableEditing();
                                if (!empty($tableData->popover_tools)) {
                                    $this->enablePopoverTools();
                                }
                            }
                        }
                    }
                    do_action(
                        'wpdatatables_generate_' . $tableData->table_type,
                        $this,
                        $tableData->content,
                        $params
                    );
                } else {
                    throw new WDTException(__('You are trying to load a table of an unknown type. Probably you did not activate the addon which is required to use this table type.', 'wpdatatables'));
                }
                break;
        }
        if (!empty($tableData->content)) {
            $this->setTableContent($tableData->content);
        }
        if (!empty($tableData->table_type)) {
            $this->setTableType($tableData->table_type);
        }
        if (!empty($tableData->title)) {
            $this->setTitle($tableData->title);
        }
        if (!empty($tableData->hide_before_load)) {
            $this->hideBeforeLoad();
        } else {
            $this->showBeforeLoad();
        }
        if (!empty($tableData->fixed_layout)) {
            $this->setFixedLayout(true);
        }
        if (!empty($tableData->word_wrap)) {
            $this->setWordWrap(true);
        }
        $this->setFilteringForm(!empty($tableData->filtering_form));

        $this->setClearFilters(!empty($tableData->clearFilters));

        if (!empty($tableData->responsive)) {
            $this->setResponsive(true);
        }
        if (!empty($tableData->scrollable)) {
            $this->setScrollable(true);
        }
        if (empty($tableData->sorting)) {
            $this->sortDisable();
        }
        if (empty($tableData->tools)) {
            $this->disableTT();
        } else {
            $this->enableTT();
            if (isset($tableData->tabletools_config)) {
                $this->_tableToolsConfig = $tableData->tabletools_config;
            } else {
                $this->_tableToolsConfig = array(
                    'print' => 1,
                    'copy' => 1,
                    'excel' => 1,
                    'csv' => 1,
                    'pdf' => 0
                );
            }
        }
        if (get_option('wdtInterfaceLanguage') != '') {
            $this->setInterfaceLanguage(get_option('wdtInterfaceLanguage'));
        }
        if (!empty($tableData->filtering)) {
            $this->enableAdvancedFilter();
        }

        if (!empty($tableData->advanced_settings)) {
            $advancedSettings = json_decode($tableData->advanced_settings);
            $this->setInfoBlock($advancedSettings->info_block);
            $this->setGlobalSearch($advancedSettings->global_search);
            $this->setShowRowsPerPage($advancedSettings->showRowsPerPage);
            isset($advancedSettings->showAllRows) ? $this->setShowAllRows($advancedSettings->showAllRows) : $this->setShowAllRows(false);
            isset($advancedSettings->simpleResponsive) ? $this->setSimpleResponsive($advancedSettings->simpleResponsive) : $this->setSimpleResponsive(false);
            isset($advancedSettings->simpleHeader) ? $this->setSimpleHeader($advancedSettings->simpleHeader) : $this->setSimpleHeader(false);
            isset($advancedSettings->stripeTable) ? $this->setStripeTable($advancedSettings->stripeTable) : $this->setStripeTable(false);
            isset($advancedSettings->cellPadding) ? $this->setCellPadding($advancedSettings->cellPadding) : $this->setCellPadding(10);
            isset($advancedSettings->removeBorders) ? $this->setRemoveBorders($advancedSettings->removeBorders) : $this->setRemoveBorders(false);
            isset($advancedSettings->borderCollapse) ? $this->setBorderCollapse($advancedSettings->borderCollapse) : $this->setBorderCollapse('collapse');
            isset($advancedSettings->borderSpacing) ? $this->setBorderSpacing($advancedSettings->borderSpacing) : $this->setBorderSpacing(0);
            isset($advancedSettings->verticalScroll) ? $this->setVerticalScroll($advancedSettings->verticalScroll) : $this->setVerticalScroll(false);
            isset($advancedSettings->verticalScrollHeight) ? $this->setVerticalScrollHeight($advancedSettings->verticalScrollHeight) : $this->setVerticalScrollHeight(600);
            isset($advancedSettings->responsiveAction) ? $this->setResponsiveAction($advancedSettings->responsiveAction) : $this->setResponsiveAction('icon');
            isset($advancedSettings->pagination) ? $this->setPagination($advancedSettings->pagination) : $this->setPagination(true);
            isset($advancedSettings->paginationAlign) ? $this->setPaginationAlign($advancedSettings->paginationAlign) : $this->setPaginationAlign('right');
            isset($advancedSettings->paginationLayout) ? $this->setPaginationLayout($advancedSettings->paginationLayout) : $this->setPaginationLayout('full_numbers');
            isset($advancedSettings->editButtonsDisplayed) ? $this->setEditButtonsDisplayed($advancedSettings->editButtonsDisplayed) : $this->setEditButtonsDisplayed(array('all'));
            isset($advancedSettings->enableDuplicateButton) ? $this->setEnableDuplicateButton($advancedSettings->enableDuplicateButton) : $this->setEnableDuplicateButton(false);
            (isset($advancedSettings->language) && $advancedSettings->language != '' ? $this->setInterfaceLanguage($advancedSettings->language) : get_option('wdtInterfaceLanguage') != '') ? $this->setInterfaceLanguage(get_option('wdtInterfaceLanguage')) : '';
            isset($advancedSettings->tableSkin) ? $this->setTableSkin($advancedSettings->tableSkin) : $this->setTableSkin(get_option('wdtBaseSkin'));
            isset($advancedSettings->tableFontColorSettings) ? $this->setTableFontColorSettings($advancedSettings->tableFontColorSettings) : $this->setTableFontColorSettings(get_option('wdtFontColorSettings'));
            isset($advancedSettings->tableBorderRemoval) ? $this->setTableBorderRemoval($advancedSettings->tableBorderRemoval) : $this->setTableBorderRemoval(get_option('wdtBorderRemoval'));
            isset($advancedSettings->tableBorderRemovalHeader) ? $this->setTableBorderRemovalHeader($advancedSettings->tableBorderRemovalHeader) : $this->setTableBorderRemovalHeader(get_option('wdtBorderRemovalHeader'));
            isset($advancedSettings->tableCustomCss) ? $this->setTableCustomCss($advancedSettings->tableCustomCss) : $this->setTableCustomCss('');
            isset($advancedSettings->pdfPaperSize) ? $this->setPdfPaperSize($advancedSettings->pdfPaperSize) : $this->setPdfPaperSize('A4');
            isset($advancedSettings->pdfPageOrientation) ? $this->setPdfPageOrientation($advancedSettings->pdfPageOrientation) : $this->setPdfPageOrientation('portrait');
            isset($advancedSettings->showTableToolsIncludeHTML) ? $this->setTableToolsIncludeHTML($advancedSettings->showTableToolsIncludeHTML) : $this->setTableToolsIncludeHTML(false);
        } else {
            $this->setInfoBlock(true);
            $this->setGlobalSearch(true);
            $this->setShowRowsPerPage(true);
            $this->setShowAllRows(false);
            $this->setSimpleHeader(false);
            $this->setSimpleResponsive(false);
            $this->setStripeTable(false);
            $this->setCellPadding(10);
            $this->setRemoveBorders(false);
            $this->setBorderCollapse('collapse');
            $this->setBorderSpacing(0);
            $this->setVerticalScroll(false);
            $this->setVerticalScrollHeight(600);
            $this->setPagination(true);
            $this->setPaginationAlign('right');
            $this->setPaginationLayout('full_numbers');
            $this->setEditButtonsDisplayed(array('all'));
            $this->setEnableDuplicateButton(false);
            $this->setTableSkin(get_option('wdtBaseSkin'));
            get_option('wdtInterfaceLanguage') != '' ? $this->setInterfaceLanguage(get_option('wdtInterfaceLanguage')) : '';
            $this->setTableFontColorSettings(get_option('wdtFontColorSettings'));
            $this->setTableBorderRemoval(get_option('wdtBorderRemoval'));
            $this->setTableBorderRemovalHeader(get_option('wdtBorderRemovalHeader'));
            $this->setTableCustomCss('');
            $this->setPdfPaperSize('A4');
            $this->setPdfPageOrientation('portrait');
            $this->setTableToolsIncludeHTML(false);
        }

        if (!empty($columnData['columnOrder'])) {
            $this->reorderColumns($columnData['columnOrder']);
        }
        if (!empty($columnData['columnWidths'])) {
            $this->wdtDefineColumnsWidth($columnData['columnWidths']);
        }
        if (!empty($columnData['possibleValues'])) {
            $this->setColumnsPossibleValues($columnData['possibleValues']);
        }
        if (!empty($tableData->columns)) {
            $this->prepareRenderingRules($tableData->columns);
        }

        do_action('wdt_extend_wpdatatable_object', $this, $tableData);

    }

    /**
     * Helper method that prepares the rendering rules
     * @param array $columnData
     */
    public function prepareRenderingRules($columnData) {
        $columnIndex = 1;
        // Check the search values passed from URL
        if (isset($_GET['wdt_search'])) {
            $this->setDefaultSearchValue($_GET['wdt_search']);
        }

        // Define all column-dependent rendering rules
        foreach ($columnData as $key=>$column) {

            $this->column_id = $key;
            // Set filter types
            $this->getColumn($column->orig_header)->setFilterType($column->filter_type);
            // Set CSS class
            $this->getColumn($column->orig_header)->addCSSClass($column->css_class);
            // Set visibility
            if (!$column->visible) {
                $this->getColumn($column->orig_header)->setIsVisible(false);
            }
            // Set default value
            $this->getColumn($column->orig_header)->setFilterDefaultValue($column->filterDefaultValue);
            // Set conditional formatting rules
            if ($column->conditional_formatting && $column->conditional_formatting !== '[]') {
                $this->getColumn($column->orig_header)
                    ->setConditionalFormattingData($column->conditional_formatting);
                $this->addConditionalFormattingColumn($column->orig_header);
            }
            //[<-- Full version -->]//
            // Set SUM columns
            if ($column->calculateTotal) {
                $this->addSumColumn($column->orig_header);
                $this->addSumFooterColumn($column->orig_header);
            }

            // Add AVG, MAX, MIN columns and Column decimal places
            if (isset($column->calculateAvg) && $column->calculateAvg == 1) {
                $this->addAvgColumn($column->orig_header);
                $this->addAvgFooterColumn($column->orig_header);
            }
            if (isset($column->calculateMin) && $column->calculateMin == 1) {
                $this->addMinColumn($column->orig_header);
                $this->addMinFooterColumn($column->orig_header);
            }
            if (isset($column->calculateMax) && $column->calculateMax == 1) {
                $this->addMaxColumn($column->orig_header);
                $this->addMaxFooterColumn($column->orig_header);
            }
            if (isset ($column->decimalPlaces)) {
                $this->addColumnsDecimalPlaces($column->orig_header, $column->decimalPlaces);
            }

            // Set hiding on phones and tablets for responsiveness
            if ($this->isResponsive()) {
                if ($column->hide_on_mobiles) {
                    $this->getColumn($column->orig_header)->setHiddenOnPhones(true);
                }
                if ($column->hide_on_tablets) {
                    $this->getColumn($column->orig_header)->setHiddenOnTablets(true);
                }
            }

            // if grouping enabled for this column, passing it to table class
            if ($column->groupColumn) {
                $this->groupByColumn($column->orig_header);
            }
            if ($column->defaultSortingColumn != '0') {
                $this->setDefaultSortColumn($column->orig_header);
                if ($column->defaultSortingColumn == '1') {
                    $this->setDefaultSortDirection('ASC');
                } elseif ($column->defaultSortingColumn == '2') {
                    $this->setDefaultSortDirection('DESC');
                }
            }
            // If thousands separator is disabled or column is "ID column for editing"
            // pass it to the column class instance
            if ($column->type == 'int') {
                if ($column->skip_thousands_separator || $column->id_column) {
                    $this->getColumn($column->orig_header)->setShowThousandsSeparator(false);
                    $this->addColumnsThousandsSeparator($column->orig_header, 0);
                } else {
                    $this->addColumnsThousandsSeparator($column->orig_header, 1);
                }
            }

            // Set ID column if specified
            if ($column->id_column) {
                $this->setIdColumnKey($column->orig_header);
            }
            // Set front-end editor input type
            $this->getColumn($column->orig_header)
                ->setInputType($column->editor_type);
            // Define if input cannot be empty
            $this->getColumn($column->orig_header)
                ->setNotNull((bool)$column->input_mandatory);

            // Get display before/after and color
            if (sanitize_html_class(strtolower(str_replace(' ', '-', $column->orig_header))) === '') {
                $cssColumnHeader = 'column-' . $this->column_id;
            } else {
                $cssColumnHeader = 'column-' . sanitize_html_class( strtolower( str_replace( ' ', '-', $column->orig_header ) ) );
            }
            if ($column->text_before != '') {
                $this->_columnsCSS .= "\n#{$this->getId()} > tbody > tr > td.{$cssColumnHeader}:not(:empty):before,
                                       \n#{$this->getId()} > tbody > tr.row-detail ul li.{$cssColumnHeader} span.columnValue:before
                                            { content: '{$column->text_before}' }";
            }
            if ($column->text_after != '') {
                $this->_columnsCSS .= "\n#{$this->getId()} > tbody > tr > td.{$cssColumnHeader}:not(:empty):after,
                                       \n#{$this->getId()} > tbody > tr.row-detail ul li.{$cssColumnHeader} span.columnValue:after
                                            { content: '{$column->text_after}' }";
            }
            if ($column->color != '') {
                $this->_columnsCSS .= "\n#{$this->getId()} > tbody > tr > td.{$cssColumnHeader}, "
                    . "#{$this->getId()} > tbody > tr.row-detail ul li.{$cssColumnHeader}, "
                    . "#{$this->getId()} > thead > tr > th.{$cssColumnHeader}, "
                    . "#{$this->getId()} > tfoot > tr > th.{$cssColumnHeader} { background-color: {$column->color} !important; }";
            }

            $this->_columnsCSS = apply_filters('wpdt_filter_columns_css', $this->_columnsCSS, $column, $this->getId(), $cssColumnHeader );

            $columnIndex++;
        }

        // Check the default values passed from URL
        if (isset($_GET['wdt_column_filter'])) {
            foreach ($_GET['wdt_column_filter'] as $fltColKey => $fltDefVal) {
                $wdtCol = $this->getColumn($fltColKey);
                if (!empty($wdtCol)) {
                    $this->getColumn($fltColKey)->setFilterDefaultValue($fltDefVal);
                }
            }
        }
    }

    /**
     * Returns JSON object for table description
     */
    public function getJsonDescription() {
        //[<-- Full version -->]//
        global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9;
        //[<--/ Full version -->]//
        global $wdtExportFileName;

        $obj = new stdClass();
        $obj->tableId = $this->getId();
        $obj->tableType = $this->getTableType();
        $obj->selector = '#' . $this->getId();
        //[<-- Full version -->]//
        $obj->responsive = $this->isResponsive();
        $obj->responsiveAction = $this->getResponsiveAction();
        $obj->editable = $this->isEditable();
        $obj->inlineEditing = $this->inlineEditingEnabled();
        $obj->infoBlock = $this->isInfoBlock();
        $obj->pagination = $this->isPagination();
        $obj->paginationAlign = $this->getPaginationAlign();
        $obj->paginationLayout = $this->getPaginationLayout();
        $obj->tableSkin = $this->getTableSkin();
        $obj->globalSearch = $this->isGlobalSearch();
        $obj->showRowsPerPage = $this->isShowRowsPerPage();
        $obj->popoverTools = $this->popoverToolsEnabled();
        //[<--/ Full version -->]//
        $obj->hideBeforeLoad = $this->doHideBeforeLoad();
        $obj->number_format = (int)(get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1);
        $obj->decimalPlaces = (int)(get_option('wdtDecimalPlaces') ? get_option('wdtDecimalPlaces') : 2);
        //[<-- Full version -->]//
        if ($this->isEditable()) {
            $obj->fileUploadBaseUrl = site_url() . '/wp-admin/admin-ajax.php?action=wdt_upload_file&table_id=' . $this->getWpId();
            $obj->adminAjaxBaseUrl = site_url() . '/wp-admin/admin-ajax.php';
            $obj->idColumnIndex = $this->getColumnHeaderOffset($this->getIdColumnKey());
            $obj->idColumnKey = $this->getIdColumnKey();
            $obj->showAllRows = $this->isShowAllRows();
        }
        //[<--/ Full version -->]//
        $obj->spinnerSrc = WDT_ASSETS_PATH . '/img/spinner.gif';
        $obj->groupingEnabled = $this->groupingEnabled();
        if ($this->groupingEnabled()) {
            $obj->groupingColumnIndex = $this->groupingColumn();
        }
        $obj->tableWpId = $this->getWpId();
        $obj->dataTableParams = new StdClass();

        $infoBlock = ($obj->infoBlock == true) ? 'i' : '';
        $globalSearch = ($obj->globalSearch == true) ? 'f' : '';
        $showRowsPerPage = ($obj->showRowsPerPage == true) ? 'l' : '';
        $pagination = ($obj->pagination == true) ? 'p' : '';
        $scrollable = ($this->isScrollable() == true) ? "<'wdtscroll't>" : 't';
        $obj->dataTableParams->sDom = "BT<'clear'>{$showRowsPerPage}{$globalSearch}{$scrollable}{$infoBlock}{$pagination}";

        $obj->dataTableParams->bSortCellsTop = false;
        //[<-- Full version -->]//
        $obj->dataTableParams->bFilter = $this->filterEnabled();
        //[<--/ Full version -->]//
        if ($this->paginationEnabled()) {
            $obj->dataTableParams->bPaginate = true;
            $obj->dataTableParams->sPaginationType = $this->getPaginationLayout();
            $obj->dataTableParams->aLengthMenu = json_decode('[[1,5,10,25,50,100,-1],[1,5,10,25,50,100,"' . __('All', 'wpdatatables') . '"]]');
            $obj->dataTableParams->iDisplayLength = (int)$this->getDisplayLength();
        } else {
            $obj->dataTableParams->aLengthMenu = json_decode('[[1,5,10,25,50,100,-1],[1,5,10,25,50,100,"' . __('All', 'wpdatatables') . '"]]');
            $obj->dataTableParams->iDisplayLength = (int)$this->getDisplayLength();
            if ($this->groupingEnabled()) {
                $obj->dataTableParams->aaSortingFixed = json_decode('[[' . $this->groupingColumn() . ', "asc"]]');
            }
        }
        if (get_option('wdtTabletWidth')) {
            $obj->tabletWidth = get_option('wdtTabletWidth');
        }
        if (get_option('wdtMobileWidth')) {
            $obj->mobileWidth = get_option('wdtMobileWidth');
        }
        $obj->dataTableParams->columnDefs = json_decode('[' . $this->getColumnDefinitions() . ']');
        $obj->dataTableParams->bAutoWidth = false;

        if (!is_null($this->getDefaultSortColumn())) {
            $obj->dataTableParams->order = json_decode('[[' . $this->getDefaultSortColumn() . ', "' . strtolower($this->getDefaultSortDirection()) . '" ]]');
        } else {
            $orderColumn = '';
            foreach ($obj->dataTableParams->columnDefs as $columnKey => $column) {
                if ($column->orderable === true) {
                    $orderColumn = $columnKey;
                    break;
                }
            }
            $obj->dataTableParams->order = json_decode('[[' . $orderColumn . ' ,"asc"]]');
        }

        if ($this->sortEnabled()) {
            $obj->dataTableParams->ordering = true;
        } else {
            $obj->dataTableParams->ordering = false;
        }

        if ($this->getInterfaceLanguage()) {
            $obj->dataTableParams->oLanguage = json_decode(file_get_contents($this->getInterfaceLanguage()));
        }

        if (empty($wdtExportFileName)) {
            if (!empty($this->_title)) {
                $wdtExportFileName = $this->_title;
            } else {
                $wdtExportFileName = 'wpdt_export';
            }
        }

        if (!$this->getNoData() && $this->advancedFilterEnabled()) {
            $obj->advancedFilterEnabled = true;
            $obj->advancedFilterOptions = array();
            if (get_option('wdtRenderFilter') == 'header') {
                $obj->advancedFilterOptions['sPlaceHolder'] = "head:before";
            }
            if ($this->getFilteringForm()) {
                $obj->filterInForm = true;
            } else {
                $obj->filterInForm = false;
                if ($this->isClearFilters()) {
                    (!isset($obj->dataTableParams->buttons)) ? $obj->dataTableParams->buttons = array() : '';
                    $obj->dataTableParams->buttons[] =
                        array(
                            'text' => __('Clear filters', 'wpdatatables'),
                            'className' => 'wdt-clear-filters-button DTTT_button DTTT_button_clear_filters'
                        );
                }
            }
            $obj->advancedFilterOptions['aoColumns'] = json_decode('[' . $this->getColumnFilterDefinitions() . ']');
            $obj->advancedFilterOptions['bUseColVis'] = true;
        } else {
            $obj->advancedFilterEnabled = false;
        }

        $currentSkin = $this->getTableSkin();
        $skinsWithNewTableToolsButtons = ['aqua','purple','dark'];
        $tableToolsIncludeHTML = !$this->getTableToolsIncludeHTML();

        $pdfPaperSize = $this->getPdfPaperSize();
        $pdfPageOrientation = $this->getPdfPageOrientation();

        if ($this->TTEnabled()) {
            (!isset($obj->dataTableParams->buttons)) ? $obj->dataTableParams->buttons = array() : '';
            if (in_array($currentSkin, $skinsWithNewTableToolsButtons)){

                if (!empty($this->_tableToolsConfig['columns'])) {
                    $obj->dataTableParams->buttons[] =
                        array(
                            'extend' => 'colvis',
                            'className' => 'DTTT_button DTTT_button_colvis',
                            'text' => __('Columns', 'wpdatatables'),
                            'collectionLayout' => 'wdt-skin-' . $currentSkin
                        );
                }
                if (!empty($this->_tableToolsConfig['print'])) {
                    $obj->dataTableParams->buttons[] =
                        array(
                            'extend' => 'print',
                            'exportOptions' => array(
                                'columns' => ':visible',
                                'stripHtml' => $tableToolsIncludeHTML
                            ),
                            'className' => 'DTTT_button DTTT_button_print',
                            'text' => __('Print', 'wpdatatables'),
                            'title' => $wdtExportFileName
                        );
                }

                if (!empty($this->_tableToolsConfig['excel'])) {
                    $exportButtons[] =
                        array(
                            'extend' => 'excelHtml5',
                            'exportOptions' => array(
                                'columns' => ':visible',
                                'stripHtml' => $tableToolsIncludeHTML
                            ),
                            'title' => $wdtExportFileName,
                            'text' => __('Excel', 'wpdatatables')
                        );
                }
                if (!empty($this->_tableToolsConfig['csv'])) {
                    $exportButtons[] =
                        array(
                            'extend' => 'csvHtml5',
                            'exportOptions' => array(
                                'columns' => ':visible',
                                'stripHtml' => $tableToolsIncludeHTML
                            ),
                            'title' => $wdtExportFileName,
                            'text' => __('CSV', 'wpdatatables')
                        );
                }
                if (!empty($this->_tableToolsConfig['copy'])) {
                    $exportButtons[] =
                        array(
                            'extend' => 'copyHtml5',
                            'exportOptions' => array(
                                'columns' => ':visible',
                                'stripHtml' => $tableToolsIncludeHTML
                            ),
                            'title' => $wdtExportFileName,
                            'text' => __('Copy', 'wpdatatables')
                        );
                }
                if (!empty($this->_tableToolsConfig['pdf'])) {
                    $exportButtons[] =
                        array(
                            'extend' => 'pdfHtml5',
                            'exportOptions' => array('columns' => ':visible'),
                            'className' => 'DTTT_button DTTT_button_pdf',
                            'orientation' => $pdfPageOrientation,
                            'pageSize' => $pdfPaperSize,
                            'title' => $wdtExportFileName,
                            'text' => __('PDF', 'wpdatatables')
                        );
                }

                if (!empty($exportButtons)) {
                    $obj->dataTableParams->buttons[] = array(
                        'extend' => 'collection',
                        'className' => 'DTTT_button DTTT_button_export',
                        'text' => __('Export', 'wpdatatables'),
                        'buttons' => $exportButtons
                    );
                }

            } else {

                if (!empty($this->_tableToolsConfig['columns'])) {
                    $obj->dataTableParams->buttons[] =
                        array(
                            'extend' => 'colvis',
                            'className' => 'DTTT_button DTTT_button_colvis',
                            'text' => __('Columns', 'wpdatatables'),
                            'collectionLayout' => 'wdt-skin-' . $currentSkin
                        );
                }
                if (!empty($this->_tableToolsConfig['print'])) {
                    $obj->dataTableParams->buttons[] =
                        array(
                            'extend' => 'print',
                            'exportOptions' => array(
                                'columns' => ':visible',
                                'stripHtml' => $tableToolsIncludeHTML
                            ),
                            'className' => 'DTTT_button DTTT_button_print',
                            'text' => __('Print', 'wpdatatables'),
                            'title' => $wdtExportFileName
                        );
                }

                if (!empty($this->_tableToolsConfig['excel'])) {
                    $obj->dataTableParams->buttons[] =
                        array(
                            'extend' => 'excelHtml5',
                            'exportOptions' => array(
                                'columns' => ':visible',
                                'stripHtml' => $tableToolsIncludeHTML
                            ),
                            'className' => 'DTTT_button DTTT_button_xls',
                            'title' => $wdtExportFileName,
                            'text' => __('Excel', 'wpdatatables')
                        );
                }
                if (!empty($this->_tableToolsConfig['csv'])) {
                    $obj->dataTableParams->buttons[] =
                        array(
                            'extend' => 'csvHtml5',
                            'exportOptions' => array(
                                'columns' => ':visible',
                                'stripHtml' => $tableToolsIncludeHTML
                            ),
                            'className' => 'DTTT_button DTTT_button_csv',
                            'title' => $wdtExportFileName,
                            'text' => __('CSV', 'wpdatatables')
                        );
                }
                if (!empty($this->_tableToolsConfig['copy'])) {
                    $obj->dataTableParams->buttons[] =
                        array(
                            'extend' => 'copyHtml5',
                            'exportOptions' => array(
                                'columns' => ':visible',
                                'stripHtml' => $tableToolsIncludeHTML
                            ),
                            'className' => 'DTTT_button DTTT_button_copy',
                            'title' => $wdtExportFileName,
                            'text' => __('Copy', 'wpdatatables')
                        );
                }
                if (!empty($this->_tableToolsConfig['pdf'])) {
                    $obj->dataTableParams->buttons[] =
                        array(
                            'extend' => 'pdfHtml5',
                            'exportOptions' => array('columns' => ':visible'),
                            'className' => 'DTTT_button DTTT_button_pdf',
                            'orientation' => $pdfPageOrientation,
                            'pageSize' => $pdfPaperSize,
                            'title' => $wdtExportFileName,
                            'text' => __('PDF', 'wpdatatables')
                        );
                }
            }
        }

        //[<-- Full version -->]//
        if ($this->isEditable()) {
            (!isset($obj->dataTableParams->buttons)) ? $obj->dataTableParams->buttons = array() : '';

            $obj->dataTableParams->editButtonsDisplayed = $this->getEditButtonsDisplayed();

            /** @var array $editButtons */
            $editButtons = array(
                'new_entry' => array(
                    'text' => __('New entry', 'wpdatatables'),
                    'className' => 'new_table_entry DTTT_button DTTT_button_new'
                ),
                'edit' => array(
                    'text' => __('Edit', 'wpdatatables'),
                    'className' => 'edit_table DTTT_button DTTT_button_edit',
                    'enabled' => false
                ),
                'delete' => array(
                    'text' => __('Delete', 'wpdatatables'),
                    'className' => 'delete_table_entry DTTT_button DTTT_button_delete',
                    'enabled' => false
                )
            );

            if($obj->dataTableParams->editButtonsDisplayed === ['all']) {
                foreach ($editButtons as $editButton){
                    $obj->dataTableParams->buttons[] = $editButton;
                }
            } else {
                foreach ($obj->dataTableParams->editButtonsDisplayed as $editButtonDisplayed) {
                    if (isset($editButtons[$editButtonDisplayed]))
                        $obj->dataTableParams->buttons[] = $editButtons[$editButtonDisplayed];
                }
            }

            if ($this->isEnableDuplicateButton() &&
                !empty(array_intersect(['all', 'duplicate'], $obj->dataTableParams->editButtonsDisplayed))) {
                $obj->dataTableParams->buttons[] = [
                    'text' => __('Duplicate', 'wpdatatables'),
                    'className' => 'duplicate_table_entry DTTT_button DTTT_button_duplicate',
                    'enabled' => false,
                ];
            }

            //Define the order for the edit buttons
            $order    = 'text';
            $ordering = ['New Entry', 'Edit', 'Duplicate', 'Delete'];
            $compare  = function($a, $b) use ($order, $ordering) {
                $hasA = array_search($a[$order], $ordering);
                $hasB = array_search($b[$order], $ordering);
                if ($hasA === $hasB && $hasA === false) {
                    return 0;
                }
                if ($hasA !== false && $hasB !== false) {
                    return $hasA - $hasB;
                }
                return $hasA === false ? -1 : 1;
            };

            usort($obj->dataTableParams->buttons, $compare);

            $obj->advancedEditingOptions = array();
            $obj->advancedEditingOptions['aoColumns'] = json_decode('[' . $this->getColumnEditingDefinitions() . ']');
        }

        if (in_array($currentSkin, $skinsWithNewTableToolsButtons)) {

            if (!isset($obj->dataTableParams->oLanguage)) {
                $obj->dataTableParams->oLanguage = new stdClass();
                $obj->dataTableParams->oLanguage->sSearchPlaceholder = __('Search table', 'wpdatatables');
            }

            $obj->dataTableParams->oLanguage->sSearch = '<span class="wdt-search-icon"></span>';

            if ($this->isEditable() || $this->TTEnabled() || $this->isClearFilters()) {
                $obj->dataTableParams->buttons[] = array(
                    'buttons' => ['pageLength'],
                    'className' => 'DTTT_button DTTT_button_spacer',
                );
            }
        } else {

            if (!isset($obj->dataTableParams->oLanguage)) {
                $obj->dataTableParams->oLanguage = new stdClass();
            }

            $obj->dataTableParams->oLanguage->sSearchPlaceholder = '';
        }

        //[<--/ Full version -->]//

        if (!isset($obj->dataTableParams->buttons)) {
            $obj->dataTableParams->buttons = array();
        }

        //[<-- Full version -->]//
        $obj->dataTableParams->bProcessing = false;
        if ($this->serverSide()) {
            $obj->serverSide = true;
            $obj->autoRefreshInterval = $this->getRefreshInterval();
            $obj->dataTableParams->serverSide = true;
            $obj->processing = true;
            $obj->dataTableParams->ajax = array(
                'url' => site_url() . '/wp-admin/admin-ajax.php?action=get_wdtable&table_id=' . $this->getWpId(),
                'type' => 'POST'
            );
            if (isset($wdtVar1) && $wdtVar1 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var1=' . urlencode($wdtVar1);
            }
            if (isset($wdtVar2) && $wdtVar2 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var2=' . urlencode($wdtVar2);
            }
            if (isset($wdtVar3) && $wdtVar3 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var3=' . urlencode($wdtVar3);
            }
            if (isset($wdtVar4) && $wdtVar4 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var4=' . urlencode($wdtVar4);
            }
            if (isset($wdtVar5) && $wdtVar5 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var5=' . urlencode($wdtVar5);
            }
            if (isset($wdtVar6) && $wdtVar6 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var6=' . urlencode($wdtVar6);
            }
            if (isset($wdtVar7) && $wdtVar7 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var7=' . urlencode($wdtVar7);
            }
            if (isset($wdtVar8) && $wdtVar8 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var8=' . urlencode($wdtVar8);
            }
            if (isset($wdtVar9) && $wdtVar9 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var9=' . urlencode($wdtVar9);
            }
            if (isset($_GET['wdt_column_filter']) && $_GET['wdt_column_filter'] !== '') {
                foreach ($_GET['wdt_column_filter'] as $fltColKey => $fltDefVal) {
                    $obj->dataTableParams->ajax['url'] .= '&wdt_column_filter['. urlencode($fltColKey) .']=' . urlencode($fltDefVal);
                }
            }
            $obj->fnServerData = true;
        } else {
            $obj->serverSide = false;
        }
        //[<--/ Full version -->]//
        $obj->columnsFixed = 0;
        //[<-- Full version -->]//
        $sumColumns = $this->getSumColumns();
        $avgColumns = $this->getAvgColumns();
        $minColumns = $this->getMinColumns();
        $maxColumns = $this->getMaxColumns();
        if (!empty($sumColumns)) {
            $obj->hasSumColumns = true;
            $obj->sumColumns = $this->getSumColumns();
        }
        if (!empty($avgColumns)) {
            $obj->hasAvgColumns = true;
            $obj->avgColumns = $this->getAvgColumns();
        }
        if (!empty($minColumns)) {
            $obj->hasMinColumns = true;
            $obj->minColumns = $this->getMinColumns();
        }
        if (!empty($maxColumns)) {
            $obj->hasMaxColumns = true;
            $obj->maxColumns = $this->getMaxColumns();
        }
        $obj->sumFunctionsLabel = get_option('wdtSumFunctionsLabel');
        $obj->avgFunctionsLabel = get_option('wdtAvgFunctionsLabel');
        $obj->minFunctionsLabel = get_option('wdtMinFunctionsLabel');
        $obj->maxFunctionsLabel = get_option('wdtMaxFunctionsLabel');
        $obj->columnsDecimalPlaces = $this->_columnsDecimalPlaces;
        $obj->columnsThousandsSeparator = $this->_columnsThousandsSeparator;
        $obj->sumColumns = isset($obj->sumColumns) ? $obj->sumColumns : array();
        $obj->avgColumns = isset($obj->avgColumns) ? $obj->avgColumns : array();
        $obj->sumAvgColumns = array_unique(array_merge($obj->sumColumns, $obj->avgColumns), SORT_REGULAR);

        if (!empty($this->_conditionalFormattingColumns)) {
            $obj->conditional_formatting_columns = $this->_conditionalFormattingColumns;
        }
        //[<--/ Full version -->]//
        $init_format = get_option('wdtDateFormat');
        $datepick_format = str_replace('d', 'dd', $init_format);
        $datepick_format = str_replace('m', 'mm', $datepick_format);
        $datepick_format = str_replace('Y', 'yy', $datepick_format);

        $obj->timeFormat = get_option('wdtTimeFormat');
        $obj->datepickFormat = $datepick_format;

        $obj->dataTableParams->oSearch = array('bSmart' => false, 'bRegex' => false, 'sSearch' => $this->getDefaultSearchValue());

        $obj = apply_filters('wpdatatables_filter_table_description', $obj, $this->getWpId(), $this);

        return json_encode($obj, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG);
    }


    /**
     * @param $columnKey
     * @param $foreignKeyRule
     * @param $dataRows
     * @return mixed
     * @throws Exception
     * @throws WDTException
     */
    public function joinWithForeignWpDataTable($columnKey, $foreignKeyRule, $dataRows) {
        $joinedTable = self::loadWpDataTable($foreignKeyRule->tableId);
        $distinctValues = $joinedTable->getDistinctValuesForColumns($foreignKeyRule);
        foreach ($dataRows as &$dataRow) {
            $dataRow[$columnKey] = isset($distinctValues[$dataRow[$columnKey]]) ? $distinctValues[$dataRow[$columnKey]] : $dataRow[$columnKey];
        }

        return array(
            'dataRows' => $dataRows,
            'distinctValues' => $distinctValues
        );

    }

    /**
     * Function that returns related values (ID's and strings) for Foreign Key feature
     * by provided foreign key rule
     * @param $foreignKeyRule stdClass that contains tableId, tableName, displayColumnId, displayColumnName,
     * storeColumnId and storeColumnName
     * @return array
     */
    public function getDistinctValuesForColumns($foreignKeyRule) {
        global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9, $wpdb;

        $distinctValues = array();
        $storeColumnName = $foreignKeyRule->storeColumnName;
        $displayColumnName = $foreignKeyRule->displayColumnName;
        $tableType = $this->getTableType();

        if ($tableType === 'mysql' || $tableType === 'manual') {
            $tableContent = $this->getTableContent();

            $tableContent = WDTTools::applyPlaceholders($tableContent);

            if ($this->getOnlyOwnRows()) {
                if (strpos($tableContent, 'WHERE') !== false) {
                    $tableContent .= ' AND ' . $this->_userIdColumn . '=' . get_current_user_id();
                } else {
                    $tableContent .= ' WHERE ' . $this->_userIdColumn . '=' . get_current_user_id();
                }
            }

            $vendor = Connection::getVendor($this->connection);

            $columnQuoteStart = Connection::getLeftColumnQuote($vendor);
            $columnQuoteEnd = Connection::getRightColumnQuote($vendor);

            $isMySql = $vendor === Connection::$MYSQL;
            $isMSSql = $vendor === Connection::$MSSQL;
            $isPostgreSql = $vendor === Connection::$POSTGRESQL;

            $groupBy = '';

            if ($isMySql) {
                $groupBy = "{$columnQuoteStart}{$storeColumnName}{$columnQuoteEnd}";
            }

            if ($isMSSql || $isPostgreSql) {
                $groupBy = "{$columnQuoteStart}{$storeColumnName}{$columnQuoteEnd}, {$columnQuoteStart}{$displayColumnName}{$columnQuoteEnd}";
            }

            $distValuesQuery = "SELECT({$columnQuoteStart}{$storeColumnName}{$columnQuoteEnd}) AS {$columnQuoteStart}{$storeColumnName}{$columnQuoteEnd}, ({$columnQuoteStart}{$displayColumnName}{$columnQuoteEnd}) AS {$columnQuoteStart}{$displayColumnName}{$columnQuoteEnd} FROM ($tableContent) tbl GROUP BY $groupBy ORDER BY {$columnQuoteStart}{$displayColumnName}{$columnQuoteEnd}";

            if (!(Connection::isSeparate($this->connection))) {
                global $wpdb;
                $mySqlResult = $wpdb->get_results($distValuesQuery);

                foreach ($mySqlResult as $dataRow) {
                    $distinctValues[$dataRow->$storeColumnName] = $dataRow->$displayColumnName;
                }
            } else {
                $sql = Connection::create($this->connection);
                $mySqlResult = $sql->getAssoc($distValuesQuery);

                foreach ($mySqlResult ?: [] as $dataRow) {
                    $distinctValues[$dataRow[$storeColumnName]] = $dataRow[$displayColumnName];
                }
            }
        } else {
            foreach ($this->getDataRows() as $dataRow) {
                $distinctValues[$dataRow[$storeColumnName]] = $dataRow[$displayColumnName];
            }
        }

        return $distinctValues;
    }


    /**
     * Delete table by ID
     * @param $tableId
     * @return bool
     * @throws Exception
     */
    public static function deleteTable($tableId) {
        global $wpdb;

        if (!isset($_REQUEST['wdtNonce']) || empty($tableId) || !current_user_can('manage_options') || !wp_verify_nonce($_REQUEST['wdtNonce'], 'wdtDeleteTableNonce')) {
            return false;
        }

        $table = WDTConfigController::loadTableFromDB($tableId);

        if (!$table) {
            return false;
        }

        if (!empty($table->table_type)) {
            if ($table->table_type == 'manual') {
                if (!(Connection::isSeparate($table->connection))) {
                    $wpdb->query("DROP TABLE {$table->mysql_table_name}");
                } else {
                    $sql = Connection::create($table->connection);
                    $sql->doQuery("DROP TABLE {$table->mysql_table_name}");
                }
            }
        }

        $wpdb->delete("{$wpdb->prefix}wpdatatables", array('id' => (int)$tableId));
        $wpdb->delete("{$wpdb->prefix}wpdatatables_columns", array('table_id' => (int)$tableId));
        $wpdb->delete("{$wpdb->prefix}wpdatatables_rows", array('table_id' => (int)$tableId));
        $wpdb->delete("{$wpdb->prefix}wpdatacharts", array('wpdatatable_id' => (int)$tableId));

        return true;
    }

    /**
     * Get all tables
     * @return array|null|object
     */
    public static function getAllTables() {
        global $wpdb;

        $query = "SELECT id, title, IF(table_type = 'mysql', 'SQL', table_type) AS table_type, connection, server_side FROM {$wpdb->prefix}wpdatatables ORDER BY id";

        $allTables = $wpdb->get_results($query, ARRAY_A);
        return $allTables;
    }
    /**
     * Get all tables except simple tables
     * @return array|null|object
     */
    public static function getAllTablesExceptSimple() {
        global $wpdb;

        $query = "SELECT id, title, connection, server_side FROM {$wpdb->prefix}wpdatatables WHERE NOT table_type = 'simple' ORDER BY id";

        $allTables = $wpdb->get_results($query, ARRAY_A);
        return $allTables;
    }

    /**
     * Helper method that load wpDataTable object by given table ID
     * and return array with $wpDataTable object and $tableData object
     * @param $tableId
     * @param null $tableView
     * @param bool $disableLimit
     * @return WPDataTable|WPExcelDataTable|bool
     * @throws Exception
     * @throws WDTException
     */
    public static function loadWpDataTable($tableId, $tableView = null, $disableLimit = false) {
        $tableData = WDTConfigController::loadTableFromDB($tableId);

        if ($tableData) {
            $tableData->disable_limit = $disableLimit;
        }

        $wpDataTable = $tableView === 'excel' ? new WPExcelDataTable($tableData->connection) : new self($tableData->connection);
        $wpDataTable->setWpId($tableId);

        $columnDataPrepared = $wpDataTable->prepareColumnData($tableData);

        $wpDataTable->fillFromData($tableData, $columnDataPrepared);

        return $wpDataTable;
    }

}