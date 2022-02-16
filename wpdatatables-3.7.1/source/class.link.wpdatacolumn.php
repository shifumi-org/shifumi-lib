<?php

defined('ABSPATH') or die("Cannot access pages directly.");

class LinkWDTColumn extends WDTColumn
{

    protected $_jsDataType = 'string';
    protected $_dataType = 'string';
    protected $_linkTargetAttribute = '_self';
    protected $_linkNoFollowAttribute = 0;
    protected $_linkNoreferrerAttribute = 0;
    protected $_linkSponsoredAttribute = 0;
    protected $_linkButtonAttribute = 0;
    protected $_linkButtonLabel = '';
    protected $_linkButtonClass = '';

    /**
     * LinkWDTColumn constructor.
     * @param array $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'link';
        $this->setLinkTargetAttribute(WDTTools::defineDefaultValue($properties, 'linkTargetAttribute', '_self'));
        $this->setLinkNoFollowAttribute(WDTTools::defineDefaultValue($properties, 'linkNoFollowAttribute', 0));
        $this->setLinkNoreferrerAttribute(WDTTools::defineDefaultValue($properties, 'linkNoreferrerAttribute', 0));
        $this->setLinkSponsoredAttribute(WDTTools::defineDefaultValue($properties, 'linkSponsoredAttribute', 0));
        $this->setLinkButtonAttribute(WDTTools::defineDefaultValue($properties, 'linkButtonAttribute', 0));
        $this->setLinkButtonLabel(WDTTools::defineDefaultValue($properties, 'linkButtonLabel', ''));
        $this->setLinkButtonClass(WDTTools::defineDefaultValue($properties, 'linkButtonClass', ''));


    }

    /**
     * @param $content
     * @return mixed|string
     */
    public function prepareCellOutput($content)
    {
        $targetAttribute = $this->getLinkTargetAttribute();
        $nofollowAttribute = $this->getLinkNofollowAttribute() == 1 ? ' nofollow ' : '';
        $noreferrerAttribute = $this->getLinkNoreferrerAttribute() == 1 ? ' noreferrer ' : '';
        $sponsoredAttribute = $this->getLinkSponsoredAttribute() == 1 ? ' sponsored ' : '';
        $rel = $nofollowAttribute . $noreferrerAttribute . $sponsoredAttribute;
        $buttonClass = $this->getLinkButtonClass();

        $content = apply_filters('wpdatatables_filter_link_cell_before_formatting', $content, $this->getParentTable()->getWpId());

        if (strpos($content, '||') !== false) {
            list($link, $content) = explode('||', $content);
            $buttonLabel = $this->getLinkButtonLabel() !== '' ? $this->getLinkButtonLabel() : $content;

            if ($this->getLinkButtonAttribute() == 1 && $content !== '') {
                $formattedValue = "<a data-content='{$content}' href='{$link}' rel='{$rel}' target='{$targetAttribute}'><button class='{$buttonClass}'>{$buttonLabel}</button></a>";
            } else {
                $formattedValue = "<a data-content='{$content}' href='{$link}' rel='{$rel}' target='{$targetAttribute}'>{$content}</a>";
            }
        } else {
            if ($this->_inputType == 'attachment') {
                $buttonLabel = $this->getLinkButtonLabel() !== '' ? $this->getLinkButtonLabel() : $content;
                if (!empty($content)) {
                    if($this->getLinkButtonAttribute() == 1 ){
                        if( $this->getLinkButtonLabel() !==''){
                            $formattedValue = "<a href='{$content}' rel='{$rel}' target='{$targetAttribute}'><button class='{$buttonClass}'>{$buttonLabel}</button></a>";
                        }else{
                            $formattedValue = "<a href='{$content}' rel='{$rel}' target='{$targetAttribute}'><button class='{$buttonClass}'>{$this->_title}</button></a>";
                        }
                    }else{
                        $formattedValue = "<a href='{$content}' rel='{$rel}' target='{$targetAttribute}'>{$this->_title}</a>";
                    }
                } else {
                    $formattedValue = '';
                }
            } else {
                if($this->getLinkButtonAttribute() == 1 && $content === null ){
                    $formattedValue = "<a href='{$content}' rel='{$rel}' target='{$targetAttribute}'>{$content}</a>";
                }else {
                    if ($this->getLinkButtonAttribute() == 1 && $content !== '') {
                        $buttonLabel = $this->getLinkButtonLabel() !== '' ? $this->getLinkButtonLabel() : $content;
                        $formattedValue = "<a href='{$content}' rel='{$rel}' target='{$targetAttribute}'><button class='{$buttonClass}'>{$buttonLabel}</button></a>";
                    } else {
                        if ($content == '') {
                            return null;
                        } else {
                            $formattedValue = "<a href='{$content}' rel='{$rel}' target='{$targetAttribute}'>{$content}</a>";
                        }
                    }
                }
            }
        }
        return apply_filters('wpdatatables_filter_link_cell', $formattedValue, $this->getParentTable()->getWpId());
    }

    /**
     * @return string
     */
    public function getLinkTargetAttribute()
    {
        return $this->_linkTargetAttribute;
    }

    /**
     * @param string $linkTargetAttribute
     */
    public function setLinkTargetAttribute($linkTargetAttribute)
    {
        $this->_linkTargetAttribute = $linkTargetAttribute;
    }

    /**
     * @return int
     */
    public function getLinkNoFollowAttribute()
    {
        return $this->_linkNoFollowAttribute;
    }

    /**
     * @param int $linkNoFollowAttribute
     */
    public function setLinkNoFollowAttribute($linkNoFollowAttribute)
    {
        $this->_linkNoFollowAttribute = $linkNoFollowAttribute;
    }

    /**
     * @return int
     */
    public function getLinkNoreferrerAttribute()
    {
        return $this->_linkNoreferrerAttribute;
    }

    /**
     * @param int $linkNoreferrerAttribute
     */
    public function setLinkNoreferrerAttribute($linkNoreferrerAttribute)
    {
        $this->_linkNoreferrerAttribute = $linkNoreferrerAttribute;
    }

    /**
     * @return int
     */
    public function getLinkSponsoredAttribute()
    {
        return $this->_linkSponsoredAttribute;
    }

    /**
     * @param int $linkSponsoredAttribute
     */
    public function setLinkSponsoredAttribute($linkSponsoredAttribute)
    {
        $this->_linkSponsoredAttribute = $linkSponsoredAttribute;
    }

    /**
     * @return int
     */
    public function getLinkButtonAttribute()
    {
        return $this->_linkButtonAttribute;
    }

    /**
     * @param int $linkButtonAttribute
     */
    public function setLinkButtonAttribute($linkButtonAttribute)
    {
        $this->_linkButtonAttribute = $linkButtonAttribute;
    }

    /**
     * @return string
     */
    public function getLinkButtonLabel()
    {
        return $this->_linkButtonLabel;
    }

    /**
     * @param string $linkButtonLabel
     */
    public function setLinkButtonLabel($linkButtonLabel)
    {
        $this->_linkButtonLabel = $linkButtonLabel;
    }


    /**
     * @return string
     */
    public function getLinkButtonClass()
    {
        return $this->_linkButtonClass;
    }

    /**
     * @param string $linkButtonClass
     */
    public function setLinkButtonClass($linkButtonClass)
    {
        $this->_linkButtonClass = $linkButtonClass;
    }
}