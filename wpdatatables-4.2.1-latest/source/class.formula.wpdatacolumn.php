<?php

defined('ABSPATH') or die('Access denied.');

class FormulaWDTColumn extends WDTColumn
{

    protected $_jsDataType = 'formatted-num';
    protected $_dataType = 'float';
    protected $_formula;

    /**
     * FormulaWDTColumn constructor.
     * @param array $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'float';
        $this->_filterType = 'none';
        $this->addCSSClass('numdata formula');
        $this->setDecimalPlaces(WDTTools::defineDefaultValue($properties, 'decimalPlaces', -1));
    }

    /**
     * Sets the formula
     *
     * @param $formula
     */
    public function setFormula($formula)
    {
        $this->_formula = $formula;
    }

    /**
     * Returns the formula
     *
     * @return mixed
     */
    public function getFormula()
    {
        return $this->_formula;
    }

    /**
     * @param $content
     * @return mixed|string
     */
    public function prepareCellOutput($content)
    {
        $content = apply_filters('wpdatatables_filter_formula_cell_before_formatting', $content, $this->getParentTable()->getWpId());

        $number_format = get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1;
        $decimal_places = $this->getDecimalPlaces() != -1 ? $this->getDecimalPlaces() : get_option('wdtDecimalPlaces');

        if ($number_format == 1) {
            $formattedValue = number_format(
                (float)$content,
                $decimal_places,
                ',',
                $this->isShowThousandsSeparator() ? '.' : ''
            );
        } else {
            $formattedValue = number_format(
                (float)$content,
                $decimal_places,
                '.',
                $this->isShowThousandsSeparator() ? ',' : ''
            );
        }

        return apply_filters('wpdatatables_filter_formula_cell', $formattedValue, $this->getParentTable()->getWpId());
    }

    /**
     * @return string
     */
    public function getGoogleChartColumnType()
    {
        return 'number';
    }

}