<?php

defined('ABSPATH') or die('Access denied.');

class EmailWDTColumn extends WDTColumn
{

    protected $_jsDataType = 'html';
    protected $_dataType = 'string';

    /**
     * EmailWDTColumn constructor.
     * @param array $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'email';
    }

    /**
     * @param $content
     * @return mixed|string
     */
    public function prepareCellOutput($content)
    {
        if (strpos($content, '||') !== false) {
            list($link, $content) = explode('||', $content);
            $formattedValue = "<a href='mailto:{$link}'>{$content}</a>";
        } else {
            $formattedValue = "<a href='mailto:{$content}'>{$content}</a>";
        }
        $formattedValue = apply_filters('wpdatatables_filter_email_cell', $formattedValue, $this->getParentTable()->getWpId());
        return $formattedValue;
    }

}