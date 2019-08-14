<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_System_Config_Form_Fieldset_Carrierprofile
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * Return header title part of html for fieldset
     *
     * @param Varien_Data_Form_Element_Fieldset $element
     * @return string
     */
    protected function _getHeaderTitleHtml($element)
    {
        $originalData = $element->getOriginalData();

        $carrierprofileId      = $originalData['carrierprofile_id'];
        $carrierName           = $originalData['carrier_name'];
        $servicelevelTimeName  = $originalData['servicelevel_time_name'];
        $servicelevelOtherName = $originalData['servicelevel_other_name'];

        $style = "display:inline-block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;";

        $html = "<span style=\"width:10%;$style\">$carrierprofileId</span>"
              . "<span style=\"width:30%;$style\">$carrierName</span>"
              . "<span style=\"width:30%;$style\">$servicelevelTimeName</span>"
              . "<span style=\"width:30%;$style\">$servicelevelOtherName</span>";

        $element->setLegend($html);

        return parent::_getHeaderTitleHtml($element);

        //return '<div class="entry-edit-head collapseable" ><a id="' . $element->getHtmlId()
        //. '-head" href="#" onclick="Fieldset.toggleCollapse(\'' . $element->getHtmlId() . '\', \''
        //. $this->getUrl('*/*/state') . '\'); return false;">' . $html . '</a></div>';
    }
}
