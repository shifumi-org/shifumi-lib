<?php

defined('ABSPATH') or die('Access denied.');

class WDTColumn {

    protected $_id = null;
    protected $_inputType = '';
    protected $_hiddenOnPhones = false;
    protected $_hiddenOnTablets = false;
    protected $_title;
    protected $_orig_header = '';
    protected $_isVisible = true;
    protected $_cssStyle;
    protected $_width;
    protected $_sorting = true;
    protected $_cssClassArray;
    protected $_dataType;
    protected $_jsDataType = 'html';
    protected $_filterType = 'text';
    protected $_possibleValues = array();
    protected $_filterDefaultValue = '';
    protected $_textBefore = '';
    protected $_textAfter = '';
    protected $_notNull = false;
    protected $_showThousandsSeparator = true;
    protected $_conditionalFormattingData = array();
    protected $_searchable = true;
    protected $_decimalPlaces = -1;
    protected $_exactFiltering;
    protected $_globalSearchColumn = 1;
    protected $_filterLabel;
    protected $_checkboxesInModal = false;
    protected $_possibleValuesType;
    protected $_possibleValuesAddEmpty = false;
    protected $_possibleValuesAjax = 10;
    protected $_rangeSlider;
    protected $_foreignKeyRule;
    protected $_editingDefaultValue = null;
    protected $_parentTable = null;
    protected $_linkButtonLabel;

    /**
     * WDTColumn constructor.
     *
     * @param array $properties
     */
    public function __construct($properties = array()) {
        $this->_cssClassArray = WDTTools::defineDefaultValue($properties, 'classes', array());
        $this->_textBefore = WDTTools::defineDefaultValue($properties, 'text_before', '');
        $this->_textAfter = WDTTools::defineDefaultValue($properties, 'text_after', '');
        $this->setSorting(WDTTools::defineDefaultValue($properties, 'sorting', 1));
        $this->_title = WDTTools::defineDefaultValue($properties, 'title', '');
        $this->_isVisible = WDTTools::defineDefaultValue($properties, 'visible', true);
        $this->_width = WDTTools::defineDefaultValue($properties, 'width', '');
        $this->_orig_header = WDTTools::defineDefaultValue($properties, 'orig_header', '');
        $this->_exactFiltering = WDTTools::defineDefaultValue($properties, 'exactFiltering', '');
        $this->setGlobalSearchColumn(WDTTools::defineDefaultValue($properties,'globalSearchColumn', 1));
        $this->_searchable = WDTTools::defineDefaultValue($properties, 'searchable', true);
        $this->setFilterDefaultValue(WDTTools::defineDefaultValue($properties, 'filterDefaultValue', null));
        $this->setFilterLabel(WDTTools::defineDefaultValue($properties, 'filterLabel', null));
        $this->setCheckboxesInModal(WDTTools::defineDefaultValue($properties, 'checkboxesInModal', false));
        $this->_possibleValuesType = WDTTools::defineDefaultValue($properties, 'possibleValuesType', '');
        $this->setPossibleValuesAddEmpty(WDTTools::defineDefaultValue($properties, 'possibleValuesAddEmpty', false));
        $this->setPossibleValuesAjax(WDTTools::defineDefaultValue($properties, 'possibleValuesAjax', 10));
        $this->setEditingDefaultValue(WDTTools::defineDefaultValue($properties, 'editingDefaultValue', null));
        $this->setParentTable(WDTTools::defineDefaultValue($properties, 'parentTable', null));
        $this->setLinkButtonLabel(WDTTools::defineDefaultValue($properties, 'linkButtonLabel', null));
        $this->_rangeSlider = WDTTools::defineDefaultValue($properties, 'rangeSlider', '');

    }

    /**
     * @return string
     */
    public function getInputType() {
        return $this->_inputType;
    }

    /**
     * @param string $inputType
     */
    public function setInputType($inputType) {
        $this->_inputType = $inputType;
    }

    /**
     * @return bool
     */
    public function isHiddenOnPhones() {
        return $this->_hiddenOnPhones;
    }

    /**
     * @param bool $hiddenOnPhones
     */
    public function setHiddenOnPhones($hiddenOnPhones) {
        $this->_hiddenOnPhones = $hiddenOnPhones;
    }

    /**
     * @return bool
     */
    public function isHiddenOnTablets() {
        return $this->_hiddenOnTablets;
    }

    /**
     * @param bool $hiddenOnTablets
     */
    public function setHiddenOnTablets($hiddenOnTablets) {
        $this->_hiddenOnTablets = $hiddenOnTablets;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return apply_filters('wpdatatables_filter_column_title', $this->_title, $this->getOriginalHeader(), $this);
    }

    /**
     * @param string $title
     */
    public function setTitle($title) {
        $this->_title = $title;
    }

    /**
     * @return string
     */
    public function getOriginalHeader() {
        return $this->_orig_header;
    }

    /**
     * @param string $orig_header
     */
    public function setOriginalHeader($orig_header) {
        $this->_orig_header = $orig_header;
    }

    /**
     * @return bool|string
     */
    public function isVisible() {
        return $this->_isVisible;
    }

    /**
     * @return bool
     */
    public function isVisibleOnMobiles() {
        return ($this->_isVisible && !$this->_hiddenOnPhones && !$this->_hiddenOnTablets);
    }

    /**
     * @param bool|string $isVisible
     */
    public function setIsVisible($isVisible) {
        $this->_isVisible = $isVisible;
    }

    /**
     * @return mixed
     */
    public function getCssStyle() {
        return $this->_cssStyle;
    }

    /**
     * @param mixed $cssStyle
     */
    public function setCssStyle($cssStyle) {
        $this->_cssStyle = $cssStyle;
    }

    /**
     * @return string
     */
    public function getWidth() {
        return $this->_width ? $this->_width : 'auto';
    }

    /**
     * @param string $width
     */
    public function setWidth($width) {
        $this->_width = $width;
    }

    /**
     * @return bool
     */
    public function getSorting() {
        return $this->_sorting;
    }

    /**
     * @param int $sorting
     */
    public function setSorting($sorting) {
        $this->_sorting = (bool)$sorting;
    }

    /**
     * @return mixed
     */
    public function getCSSClasses() {
        $classesStr = implode(' ', $this->_cssClassArray);
        $classesStr = apply_filters('wpdatatables_filter_column_cssClassArray', $classesStr, $this->_title);
        return $classesStr;
    }

    /**
     * @param $class
     */
    public function addCSSClass($class) {
        $this->_cssClassArray[] = $class;
    }

    /**
     * @return mixed
     */
    public function getDataType() {
        return $this->_dataType;
    }

    /**
     * @param mixed $dataType
     */
    public function setDataType($dataType) {
        $this->_dataType = $dataType;
    }

    /**
     * @return string
     */
    public function getFilterType() {
        return $this->_filterType;
    }

    /**
     * @param $filterType
     * @throws WDTException
     */
    public function setFilterType($filterType) {
        if (!in_array($filterType,
            array(
                'none',
                '',
                'text',
                'number',
                'select',
                'multiselect',
                'null',
                'number-range',
                'date-range',
                'checkbox',
                'datetime-range',
                'time-range'
            )
        )
        ) {
            throw new WDTException('Unknown column filter type!');
        }
        if (($filterType == 'none') || ($filterType == '')) {
            $filterType = 'null';
        }
        $this->_filterType = $filterType;
    }

    /**
     * @return array
     */
    public function getPossibleValuesList() {
        return $this->_possibleValues;
    }

    /**
     * @param array $possibleValues
     */
    public function setPossibleValues($possibleValues) {
        if (!empty($possibleValues)) {
            if (!is_array($possibleValues)) {
                $possibleValues = explode('|', $possibleValues);
            }
            $this->_possibleValues = $possibleValues;
        } else {
            $this->_possibleValues = array();
        }
    }

    /**
     * @return string
     * @throws WDTException
     */
    public function getFilterDefaultValue() {
        $value = $this->_filterDefaultValue;

        if ($value) {
            if ($this->getForeignKeyRule()) {
                $foreignKeyRule = $this->getForeignKeyRule();
                $joinedTable = WPDataTable::loadWpDataTable($foreignKeyRule->tableId);
                $distinctValues = $joinedTable->getDistinctValuesForColumns($foreignKeyRule);

                $valueCopy = $value;
                $value = [];
                if (is_array($valueCopy)) {
                    foreach ($valueCopy as $copy) {
                        $copy = $this->applyPlaceholders($copy);

                        $value[] = [
                            'value' => $copy,
                            'text' => $distinctValues[$copy]
                        ];
                    }
                } else {
                    $valueCopy = $this->applyPlaceholders($valueCopy);

                    $value['value'] = $valueCopy;
                    
                    if ($this->getFilterType() == "text") {
                        $key = array_search($valueCopy, $distinctValues);
                        $value['text'] = $distinctValues[$key];
                    }else {
                        $value['text'] = $distinctValues[$valueCopy];
                    }
                }
            } else {
                if (is_array($value)) {
                    foreach ($value as &$singleValue) {
                        $singleValue = $this->applyPlaceholders($singleValue);
                    }
                } else {
                    $value = $this->applyPlaceholders($value);
                }
            }
        }

        $value = apply_filters('wpdt_filter_filtering_default_value', $value , $this->getOriginalHeader(), $this->getParentTable()->getWpId());

        return $value;
    }

    /**
     * @param string $defaultValue
     */
    public function setFilterDefaultValue($defaultValue) {
        if (strpos($defaultValue, '|') !== false) {
            $defaultValue = explode('|', $defaultValue);
        }
        $this->_filterDefaultValue = $defaultValue;
    }

    /**
     * @return string
     */
    public function getTextBefore() {
        return $this->_textBefore;
    }

    /**
     * @param string $textBefore
     */
    public function setTextBefore($textBefore) {
        $this->_textBefore = $textBefore;
    }

    /**
     * @return string
     */
    public function getTextAfter() {
        return $this->_textAfter;
    }

    /**
     * @param string $textAfter
     */
    public function setTextAfter($textAfter) {
        $this->_textAfter = $textAfter;
    }

    /**
     * @return bool
     */
    public function isNotNull() {
        return $this->_notNull;
    }

    /**
     * @param bool $notNull
     */
    public function setNotNull($notNull) {
        $this->_notNull = (bool)$notNull;
    }

    /**
     * @return bool
     */
    public function isShowThousandsSeparator() {
        return $this->_showThousandsSeparator;
    }

    /**
     * @param bool $showThousandsSeparator
     */
    public function setShowThousandsSeparator($showThousandsSeparator) {
        $this->_showThousandsSeparator = $showThousandsSeparator;
    }

    /**
     * @return array
     */
    public function getConditionalFormattingData() {
        return $this->_conditionalFormattingData;
    }

    /**
     * Set conditional formatting data for column and set
     * conditional formatting cell value to today's date if %TODAY%
     * placeholder is used
     *
     * @param array $conditionalFormattingData
     */
    public function setConditionalFormattingData($conditionalFormattingData) {
        $this->_conditionalFormattingData = $conditionalFormattingData;
    }

    /**
     * @return bool
     */
    public function isSearchable() {
        return $this->_searchable;
    }

    /**
     * @param bool $searchable
     */
    public function setSearchable($searchable) {
        $this->_searchable = $searchable;
    }

    /**
     * @return int
     */
    public function getDecimalPlaces() {
        return $this->_decimalPlaces;
    }

    /**
     * @param int $decimalPlaces
     */
    public function setDecimalPlaces($decimalPlaces) {
        $this->_decimalPlaces = $decimalPlaces;
    }

    /**
     * @return string
     */
    public function getExactFiltering() {
        return $this->_exactFiltering;
    }

    /**
     * @param string $exactFiltering
     */
    public function setExactFiltering($exactFiltering) {
        $this->_exactFiltering = $exactFiltering;
    }

    /**
     * @return int
     */
    public function getGlobalSearchColumn() {
        return $this->_globalSearchColumn;
    }

    /**
     * @param int $globalSearchColumn
     */
    public function setGlobalSearchColumn($globalSearchColumn) {
        $this->_globalSearchColumn = $globalSearchColumn;
    }

    /**
     * @return string
     */
    public function getRangeSlider() {
        return $this->_rangeSlider;
    }

    /**
     * @param string $rangeSlider
     */
    public function setRangeSlider($rangeSlider) {
        $this->_rangeSlider = $rangeSlider;
    }

    /**
     * @return string
     */
    public function getFilterLabel() {
        return $this->_filterLabel;
    }

    /**
     * @param string $filterLabel
     */
    public function setFilterLabel($filterLabel) {
        $this->_filterLabel = $filterLabel;
    }

    /**
     * @return string
     */
    public function getLinkButtonLabel() {
        return $this->_linkButtonLabel;
    }

    /**
     * @param string $linkButtonLabel
     */
    public function setLinkButtonLabel($linkButtonLabel) {
        $this->_linkButtonLabel = $linkButtonLabel;
    }

    /**
     * @return bool
     */
    public function isCheckboxesInModal() {
        return $this->_checkboxesInModal;
    }

    /**
     * @param bool $checkboxesInModal
     */
    public function setCheckboxesInModal($checkboxesInModal) {
        $this->_checkboxesInModal = $checkboxesInModal;
    }

    /**
     * @return string
     */
    public function getPossibleValuesType() {
        return $this->_possibleValuesType;
    }

    /**
     * @param string $possibleValuesType
     */
    public function setPossibleValuesType($possibleValuesType) {
        $this->_possibleValuesType = $possibleValuesType;
    }

    /**
     * @return mixed
     */
    public function getPossibleValuesAddEmpty() {
        return $this->_possibleValuesAddEmpty;
    }

    /**
     * @param mixed $possibleValuesAddEmpty
     */
    public function setPossibleValuesAddEmpty($possibleValuesAddEmpty) {
        $this->_possibleValuesAddEmpty = (bool)$possibleValuesAddEmpty;
    }

    /**
     * @return int
     */
    public function getPossibleValuesAjax() {
        return $this->_possibleValuesAjax;
    }

    /**
     * @param int $possibleValuesAjax
     */
    public function setPossibleValuesAjax($possibleValuesAjax) {
        $this->_possibleValuesAjax = $possibleValuesAjax;
    }

    /**
     * @return mixed
     */
    public function getForeignKeyRule() {
        return $this->_foreignKeyRule;
    }

    /**
     * @param mixed $foreignKeyRule
     */
    public function setForeignKeyRule($foreignKeyRule) {
        $this->_foreignKeyRule = $foreignKeyRule;
    }

    /**
     * @return mixed
     */
    public function getEditingDefaultValue() {
        $value = $this->_editingDefaultValue;

        if ($value) {
            if ($this->getForeignKeyRule()) {
                $foreignKeyRule = $this->getForeignKeyRule();
                $joinedTable = WPDataTable::loadWpDataTable($foreignKeyRule->tableId);
                $distinctValues = $joinedTable->getDistinctValuesForColumns($foreignKeyRule);

                $valueCopy = $value;
                $value = [];
                if (is_array($valueCopy)) {
                    foreach ($valueCopy as $copy) {
                        $value[] = [
                            'value' => $copy,
                            'text' => $distinctValues[$copy]
                        ];
                    }
                } else {
                    $value['value'] = $valueCopy;
                    $value['text'] = $distinctValues[$valueCopy];
                }
            } else {
                if (is_array($value)) {
                    foreach ($value as &$singleValue) {
                        $singleValue = $this->applyPlaceholders($singleValue);
                    }
                } else {
                    $value = $this->applyPlaceholders($value);
                }
            }
        }
        $value = apply_filters('wpdt_filter_editing_default_value', $value , $this->getOriginalHeader(), $this->getParentTable()->getWpId());

        return $value;
    }

    /**
     * @param string $editingDefaultValue
     */
    public function setEditingDefaultValue($editingDefaultValue) {
        $this->_editingDefaultValue = $editingDefaultValue;
    }

    /**
     * @return null
     */
    public function getParentTable() {
        return $this->_parentTable;
    }

    /**
     * @param null $parentTable
     */
    public function setParentTable($parentTable) {
        $this->_parentTable = $parentTable;
    }

    /**
     * @param $cellContent
     * @return mixed
     */
    public function returnCellValue($cellContent) {
        $cellValue = $this->prepareCellOutput($cellContent);
        $cellValue = apply_filters('wpdatatables_filter_cell_val', $cellValue, $this->getParentTable()->getWpId());
        return $cellValue;
    }

    /**
     * Get column type for Google Charts
     *
     * @return string
     */
    public function getGoogleChartColumnType() {
        return 'string';
    }

    public function hideOnTablets() {
        $this->_hiddenOnTablets = true;
    }

    public function showOnTablets() {
        $this->_hiddenOnTablets = false;
    }

    public function getHiddenAttr() {
        $hidden = array();
        if ($this->_hiddenOnPhones) {
            $hidden[] = 'phone';
        }
        if ($this->_hiddenOnTablets) {
            $hidden[] = 'tablet';
        }
        return implode(',', $hidden);
    }

    /**
     * @param $content
     * @return mixed
     */
    public function prepareCellOutput($content) {
        if (is_array($content)) {
            return $content['value'];
        }

        return $content;
    }

    /**
     * Apply placeholders
     *
     * @param $value
     * @return mixed
     */
    private function applyPlaceholders($value) {
        global $wdtVar1, $wdtVar2, $wdtVar3;

        if ($value && !is_array($value) && !is_object ($value)) {
            // Current user ID
            if (strpos($value, '%CURRENT_USER_ID%') !== false) {
                $value = str_replace('%CURRENT_USER_ID%', get_current_user_id(), $value);
            }// Current user login
            if (strpos($value, '%CURRENT_USER_LOGIN%') !== false) {
                $value = str_replace('%CURRENT_USER_LOGIN%', wp_get_current_user()->user_login, $value);
            }// Current post id
            if (strpos($value, '%CURRENT_POST_ID%') !== false) {
                $value = str_replace('%CURRENT_POST_ID%', get_the_ID(), $value);
            }// Current user first name
            if (strpos($value, '%CURRENT_USER_FIRST_NAME%') !== false) {
                $value = str_replace('%CURRENT_USER_FIRST_NAME%', wp_get_current_user()->first_name, $value);
            }// Current user last name
            if (strpos($value, '%CURRENT_USER_LAST_NAME%') !== false) {
                $value = str_replace('%CURRENT_USER_LAST_NAME%', wp_get_current_user()->last_name, $value);
            }// Current user email
            if (strpos($value, '%CURRENT_USER_EMAIL%') !== false) {
                $value = str_replace('%CURRENT_USER_EMAIL%', wp_get_current_user()->user_email, $value);
            }// Current date
            if (strpos($value, '%CURRENT_DATE%') !== false) {
                $value = str_replace('%CURRENT_DATE%', current_time(get_option('wdtDateFormat')), $value);
            }// Current datetime
            if (strpos($value, '%CURRENT_DATETIME%') !== false) {
                $value = str_replace('%CURRENT_DATETIME%', current_time(get_option('wdtDateFormat')) . ' ' . current_time(get_option('wdtTimeFormat')), $value);
            }// Current time
            if (strpos($value, '%CURRENT_TIME%') !== false) {
                $value = str_replace('%CURRENT_TIME%', current_time(get_option('wdtTimeFormat')), $value);
            }// Shortcode VAR1
            if (strpos($value, '%VAR1%') !== false) {
                $value = str_replace('%VAR1%', $wdtVar1, $value);
            }// Shortcode VAR2
            if (strpos($value, '%VAR2%') !== false) {
                $value = str_replace('%VAR2%', $wdtVar2, $value);
            }// Shortcode VAR3
            if (strpos($value, '%VAR3%') !== false) {
                $value = str_replace('%VAR3%', $wdtVar3, $value);
            }
        }

        return $value;
    }

    /**
     * Generates column object based on column type
     *
     * @param string $wdtColumnType
     * @param array $properties
     * @return mixed
     */
    public static function generateColumn($wdtColumnType = 'string', $properties = array()) {
        if (!$wdtColumnType) {
            $wdtColumnType = 'string';
        }
        $columnObj = ucfirst($wdtColumnType) . 'WDTColumn';
        $columnFormatterFileName = 'class.' . strtolower($wdtColumnType) . '.wpdatacolumn.php';
        $columnFormatterFileName = apply_filters('wpdatatables_column_formatter_file_name', $columnFormatterFileName, $wdtColumnType);
        require_once($columnFormatterFileName);
        return new $columnObj($properties);
    }

    /**
     * Get JSON for a column
     *
     * @return StdClass
     */
    public function getColumnJSON($columnID) {
        $colJsDefinition = new StdClass();
        $colJsDefinition = apply_filters('wpdatatables_extend_column_js_definition', $colJsDefinition, $this);
        $colJsDefinition->sType = $this->_jsDataType;
        $colJsDefinition->wdtType = $this->_dataType;
        $colJsDefinition->bVisible = $this->isVisible();
        $colJsDefinition->orderable = $this->getSorting();
        $colJsDefinition->searchable = $this->_searchable && $this->_globalSearchColumn;
        $colJsDefinition->InputType = $this->_inputType;
        $colJsDefinition->name = $this->_orig_header;
        $colJsDefinition->origHeader = $this->_orig_header;
        $colJsDefinition->notNull = $this->_notNull;
        $colJsDefinition->conditionalFormattingRules = $this->getConditionalFormattingData();
        if (sanitize_html_class(strtolower(str_replace(' ', '-', $this->_orig_header)))) {
            $colJsDefinition->className = $this->getCSSClasses() . ' column-' . sanitize_html_class(strtolower(str_replace(' ', '-', $this->_orig_header)));
        } else {
            $colJsDefinition->className = $this->getCSSClasses() . ' column-' . $columnID;
        }
        if ($this->_width != '') {
            $colJsDefinition->sWidth = $this->_width;
        }
        $colJsDefinition = apply_filters('wpdatatables_filter_column_js_definition', $colJsDefinition, $this->_title);
        return $colJsDefinition;
    }

    /**
     * Get Filter definition for a column
     *
     * @return stdClass
     * @throws WDTException
     */
    public function getJSFilterDefinition() {
        /** @var WPDataTable $parentTable */
        $parentTable = $this->getParentTable();
        $jsFilterDef = new stdClass();

        $jsFilterDef->type = $this->getFilterType();
        $jsFilterDef->columnType = $this->getDataType();
        $jsFilterDef->numberOfDecimalPlaces = $this->getDecimalPlaces() === -1 ? get_option('wdtDecimalPlaces') : $this->getDecimalPlaces();
        $jsFilterDef->possibleValuesType = $this->getPossibleValuesType();
        $jsFilterDef->globalSearchColumn = $this->getGlobalSearchColumn();

        $jsFilterDef->values = null;
        if ((in_array($this->getFilterType(), array('select', 'multiselect', 'checkbox')) || in_array($this->getInputType(), array('selectbox', 'multi-selectbox')))) {
            if ($this->_possibleValuesType === 'read' && $parentTable->serverSide()) {
                if (has_filter('wpdatatables_possible_values_' . $parentTable->getTableType())) {
                    $distValues = apply_filters('wpdatatables_possible_values_' . $parentTable->getTableType(), $this, true, false);
                } else {
                    $distValues = self::getPossibleValuesRead($this, true,false);
                }
                foreach ($distValues as $value) {
                    $distinctValue['value'] = $value;
                    $distinctValue['label'] = $this->prepareCellOutput($value);
                    $jsFilterDef->values[] = $distinctValue;
                }
            } elseif ($this->_possibleValuesType === 'list') {
                foreach ($this->getPossibleValuesList() as $value) {
                    $distinctValue['value'] = $value;
                    $distinctValue['label'] = $value;
                    $jsFilterDef->values[] = $distinctValue;
                }
            } elseif ($this->_possibleValuesType === 'foreignkey' && $parentTable->serverSide()) {
                $readValues = [];
                if ($this->getParentTable()->getOnlyOwnRows()) {
                    $readValues = self::getPossibleValuesRead($this, true,false);
                }
                foreach ($this->getPossibleValuesList() as $value => $label) {
                    // If foreign key is used with "User can see only own rows"
                    if ($this->getParentTable()->getOnlyOwnRows()) {
                        if (in_array($value, $readValues, false)) {
                            $distinctValue['value'] = $value;
                            $distinctValue['label'] = $label;
                            $jsFilterDef->values[] = $distinctValue;
                        }
                    } else {
                        $distinctValue['value'] = $value;
                        $distinctValue['label'] = $label;
                        $jsFilterDef->values[] = $distinctValue;
                    }
                }
            }
        }

        if ($this->getRangeSlider() === 1 && $this->getParentTable()->serverSide()) {
            $jsFilterDef->minValue = $this->getColumnMinValue();
            $jsFilterDef->maxValue =  $this->getColumnMaxValue();
        }

        if (($this->getFilterType() === 'select') && $parentTable->serverSide() && $this->getPossibleValuesAddEmpty()) {
            array_unshift(
                $jsFilterDef->values,
                [
                    'value' => 'possibleValuesAddEmpty',
                    'label' => ' '
                ]
            );
        }

        $jsFilterDef->origHeader = $this->getOriginalHeader();
        $jsFilterDef->displayHeader = $this->getTitle();
        $jsFilterDef->possibleValuesAddEmpty = $this->getPossibleValuesAddEmpty();
        $jsFilterDef->possibleValuesAjax = $this->getPossibleValuesAjax();
        $jsFilterDef->defaultValue = $this->getFilterDefaultValue();
        $jsFilterDef->exactFiltering = $this->getExactFiltering();
        $jsFilterDef->filterLabel = $this->getFilterLabel();
        $jsFilterDef->checkboxesInModal = $this->isCheckboxesInModal();
        $jsFilterDef->linkButtonLabel = $this->getLinkButtonLabel();
        $jsFilterDef->rangeSlider = $this->getRangeSlider();

        return $jsFilterDef;
    }

    /**
     * Get Editing definition for a column
     *
     * @return stdClass
     */
    public function getJSEditingDefinition() {

        $parentTable = $this->getParentTable();
        $jsEditingDef = new stdClass();

        $jsEditingDef->type = $this->getInputType();
        $jsEditingDef->possibleValuesType = $this->getPossibleValuesType();

        $jsEditingDef->values = null;
        if (in_array($this->getInputType(), array('selectbox', 'multi-selectbox'))) {
            if ($this->_possibleValuesType === 'read' && $parentTable->serverSide()) {
                if (has_filter('wpdatatables_possible_values_' . $parentTable->getTableType())) {
                    $distValues = apply_filters('wpdatatables_possible_values_' . $parentTable->getTableType(), $this, true, false);
                } else {
                    $distValues = self::getPossibleValuesRead($this, true,false);
                }
                foreach ($distValues as $value) {
                    $distinctValue['value'] = $value;
                    $distinctValue['label'] = $this->prepareCellOutput($value);
                    $jsEditingDef->values[] = $distinctValue;
                }
            } elseif ($this->_possibleValuesType === 'list') {
                foreach ($this->getPossibleValuesList() as $value) {
                    $distinctValue['value'] = $value;
                    $distinctValue['label'] = $value;
                    $jsEditingDef->values[] = $distinctValue;
                }
            } elseif ($this->_possibleValuesType === 'foreignkey' && $parentTable->serverSide()) {
                $readValues = [];
                $foreignKeyRule = $this->getForeignKeyRule();
                $allowAllPossibleValuesForeignKey = $foreignKeyRule->allowAllPossibleValuesForeignKey;
                if ($this->getParentTable()->getOnlyOwnRows()) {
                    $readValues = self::getPossibleValuesRead($this, true,false);
                }
                foreach ($this->getPossibleValuesList() as $value => $label) {
                    // If foreign key is used with "User can see only own rows"
                    if ($this->getParentTable()->getOnlyOwnRows() && !$allowAllPossibleValuesForeignKey) {
                        if (in_array($value, $readValues, false)) {
                            $distinctValue['value'] = $value;
                            $distinctValue['label'] = $label;
                            $jsEditingDef->values[] = $distinctValue;
                        }
                    } else {
                        $distinctValue['value'] = $value;
                        $distinctValue['label'] = $label;
                        $jsEditingDef->values[] = $distinctValue;
                    }
                }
            }
        }

        $jsEditingDef->origHeader = $this->getOriginalHeader();
        $jsEditingDef->editorInputType = $this->getInputType();
        $jsEditingDef->defaultValue = $this->getEditingDefaultValue();
        $jsEditingDef->defaultValue = $this->applyPlaceholders($jsEditingDef->defaultValue);
        $jsEditingDef->possibleValuesAjax = $this->getPossibleValuesAjax();
        $jsEditingDef->mandatory = $this->isNotNull();
        $jsEditingDef->displayHeader = $this->getTitle();
        $jsEditingDef->foreignKeyRule = $this->getForeignKeyRule();

        return $jsEditingDef;
    }

    /**
     * Get possible values based on "Possible values for column" type
     *
     * Used to populate "Predefined value(s)" selectboxes in column settings and
     * for loading for possible values with AJAX
     *
     * @return array
     */
    public function getPossibleValues() {
        /** @var WPDataTable $parentTable */
        $parentTable = $this->getParentTable();
        $values = array();

        if (empty($this->_formula) && $this->getDataType() !== '' && !in_array($this->getDataType(), array('date', 'datetime', 'time', 'formula'), true)) {
            if ($this->_possibleValuesType === 'read' && $parentTable->serverSide()) {
                if (has_filter('wpdatatables_possible_values_' . $parentTable->getTableType())) {
                    $values = apply_filters('wpdatatables_possible_values_' . $parentTable->getTableType(), $this, true, false);
                } else {
                    $values = self::getPossibleValuesRead($this, true,false);
                }
            } elseif ($this->_possibleValuesType === 'list' || ($this->_possibleValuesType === 'foreignkey' && $parentTable->serverSide() == false)) {
                $values = $this->getPossibleValuesList();
            } elseif ($this->_possibleValuesType === 'foreignkey') {
                foreach ($this->getPossibleValuesList() as $value => $label) {
                    $distinctValue['value'] = $value;
                    $distinctValue['text'] = $label;
                    $values[] = $distinctValue;
                }
            } else {
                foreach ($parentTable->getDataRows() as $row) {
                    $values[] = $row[$this->getOriginalHeader()];
                }
                $values = array_unique($values);
            }
        }

        return $values;
    }

    /**
     * Get distinct values for a column
     *
     * @param WDTColumn $column
     * @param           $tableData
     * @param           $filterByUserId
     * @return array|bool
     */
    public static function getPossibleValuesRead($column, $filterByUserId, $tableData = null) {
        global $wdtVar1, $wdtVar2, $wdtVar3;
        $distValues = array();
        /** @var WPDataTable $parentTable */
        $parentTable = $column->getParentTable();
        $columnOrigHeader = $column->getOriginalHeader();

        $vendor = Connection::getVendor($parentTable->connection);

        $leftSysIdentifier = Connection::getLeftColumnQuote($vendor);
        $rightSysIdentifier = Connection::getRightColumnQuote($vendor);

        $isMySql = $vendor === Connection::$MYSQL;
        $isMSSql = $vendor === Connection::$MSSQL;
        $isPostgreSql = $vendor === Connection::$POSTGRESQL;

        $where = 'WHERE 1=1';
        $where .= isset($_POST['q']) ? " AND ({$leftSysIdentifier}{$columnOrigHeader}{$rightSysIdentifier}) LIKE '%{$_POST['q']}%'" : '';

        $wdtVar1 = $wdtVar1 === '' && isset($tableData->var1) ? $tableData->var1 : $wdtVar1;
        $wdtVar2 = $wdtVar2 === '' && isset($tableData->var2) ? $tableData->var2 : $wdtVar2;
        $wdtVar3 = $wdtVar3 === '' && isset($tableData->var3) ? $tableData->var3 : $wdtVar3;

        $tableContent = WDTTools::applyPlaceholders($parentTable->getTableContent());

        if ($filterByUserId && $parentTable->getOnlyOwnRows() === true) {
            $where .= " AND {$leftSysIdentifier}" . $parentTable->getUserIdColumn() . "{$rightSysIdentifier} = " . get_current_user_id();
        }

        if ($isMySql) {
            $limit = $column->getPossibleValuesAjax() !== -1 ? 'LIMIT ' . $column->getPossibleValuesAjax() : '';
            $distValuesQuery = "SELECT DISTINCT({$leftSysIdentifier}{$columnOrigHeader}{$rightSysIdentifier}) AS {$leftSysIdentifier}{$columnOrigHeader}{$rightSysIdentifier} FROM ( $tableContent ) tbl $where ORDER BY ({$leftSysIdentifier}{$columnOrigHeader}{$rightSysIdentifier}) $limit";
        }

        if ($isMSSql) {
            $limit = $column->getPossibleValuesAjax() !== -1 ? " OFFSET 0 ROWS FETCH NEXT {$column->getPossibleValuesAjax()} ROWS ONLY" : '';
            $distValuesQuery = "SELECT DISTINCT({$leftSysIdentifier}{$columnOrigHeader}{$rightSysIdentifier}) AS {$leftSysIdentifier}{$columnOrigHeader}{$rightSysIdentifier} FROM ( $tableContent ) tbl $where ORDER BY ({$leftSysIdentifier}{$columnOrigHeader}{$rightSysIdentifier}) $limit";
        }

        if ($isPostgreSql) {
            $limit = $column->getPossibleValuesAjax() !== -1 ? 'LIMIT ' . $column->getPossibleValuesAjax() : '';
            $distValuesQuery = "SELECT DISTINCT({$leftSysIdentifier}{$columnOrigHeader}{$rightSysIdentifier}) AS {$leftSysIdentifier}{$columnOrigHeader}{$rightSysIdentifier} FROM ( $tableContent ) tbl $where ORDER BY ({$leftSysIdentifier}{$columnOrigHeader}{$rightSysIdentifier}) $limit";
        }

        if (!(Connection::isSeparate($parentTable->connection))) {
            global $wpdb;
            $distValues = $wpdb->get_col($distValuesQuery);
            if ($wpdb->last_error) {
                return false;
            }
        } else {
            $sql = Connection::create($parentTable->connection);
            $rows = $sql->getArray($distValuesQuery);

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $distValues[] = $row[0];
                }
            }
        }

        // Filter array to remove NULL from the $distValues
        return array_values(array_filter($distValues, function ($value) {
            return ($value !== null && $value !== false && $value !== '');
        }));
    }

    private function getColumnMinValue ()
    {
        global $wpdb;
        $parentTable = $this->getParentTable();
        $parentTableContent = WDTTools::applyPlaceholders($parentTable->getTableContent());
        $columnOrigHeader = $this->getOriginalHeader();
        $vendor = Connection::getVendor($parentTable->connection);

        $leftSysIdentifier = Connection::getLeftColumnQuote($vendor);
        $rightSysIdentifier = Connection::getRightColumnQuote($vendor);

        $isMySql = $vendor === Connection::$MYSQL;
        $isMSSql = $vendor === Connection::$MSSQL;
        $isPostgreSql = $vendor === Connection::$POSTGRESQL;

        $minQuery = "SELECT MIN(" . $columnOrigHeader . ") as min FROM (" . $parentTableContent . ") as parentTable";

        if ($isMySql) {
            $minQuery = "SELECT MIN({$leftSysIdentifier}{$columnOrigHeader}{$rightSysIdentifier}) as min FROM (" . $parentTableContent . ") as parentTable";
        } else if ($isMSSql) {
            $minQuery = "SELECT min({$leftSysIdentifier}{$columnOrigHeader}{$rightSysIdentifier}) as min FROM (" . $parentTableContent . ") as parentTable";
        } else if ($isPostgreSql) {
            $minQuery = "SELECT MIN({$leftSysIdentifier}{$columnOrigHeader}$rightSysIdentifier) AS min FROM (" . $parentTableContent . ") AS parentTable";
        }

        if (!(Connection::isSeparate($parentTable->connection))) {
            return (float)$wpdb->get_row($minQuery)->min;
        } else {
            $sql = Connection::create($parentTable->connection);
            $minValue= $sql->getRow($minQuery)['min'];
            return (float)($minValue);
        }
    }

    private function getColumnMaxValue ()
    {
        global $wpdb;
        $parentTable = $this->getParentTable();
        $parentTableContent = WDTTools::applyPlaceholders($parentTable->getTableContent());
        $columnOrigHeader = $this->getOriginalHeader();
        $vendor = Connection::getVendor($parentTable->connection);

        $leftSysIdentifier = Connection::getLeftColumnQuote($vendor);
        $rightSysIdentifier = Connection::getRightColumnQuote($vendor);

        $isMySql = $vendor === Connection::$MYSQL;
        $isMSSql = $vendor === Connection::$MSSQL;
        $isPostgreSql = $vendor === Connection::$POSTGRESQL;

        $maxQuery = "SELECT MAX(" . $columnOrigHeader . ") AS max FROM (" . $parentTableContent . ") AS parentTable";

        if ($isMySql) {
            $maxQuery = "SELECT MAX({$leftSysIdentifier}{$columnOrigHeader}{$rightSysIdentifier}) AS max FROM (" . $parentTableContent . ") AS parentTable";
        } else if ($isMSSql) {
            $maxQuery = "SELECT max({$leftSysIdentifier}{$columnOrigHeader}{$rightSysIdentifier}) as max FROM (" . $parentTableContent . ") as parentTable";
        } else if ($isPostgreSql) {
            $maxQuery = "SELECT MAX({$leftSysIdentifier}{$columnOrigHeader}{$rightSysIdentifier}) AS max FROM (" . $parentTableContent . ") AS parentTable";
        }

        if (!(Connection::isSeparate($parentTable->connection))) {
            return (float)$wpdb->get_row($maxQuery)->max;
        } else {
            $sql = Connection::create($parentTable->connection);
            $maxValue = $sql->getRow($maxQuery)['max'];
            return (float)($maxValue);
        }
    }
}
