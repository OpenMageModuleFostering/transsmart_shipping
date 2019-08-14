<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_System_Config_Form_Fieldset_Carrierprofile_Header
    extends Transsmart_Shipping_Block_Adminhtml_System_Config_Form_Fieldset_Carrierprofile
{
    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $carrierprofileId      = $this->__('ID');
        $carrierName           = $this->__('Carrier');
        $servicelevelTimeName  = $this->__('Servicelevel Time');
        $servicelevelOtherName = $this->__('Servicelevel Other');

        $style = "display:inline-block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;";

        $html = "<span style=\"width:10%;$style\">$carrierprofileId</span>"
              . "<span style=\"width:30%;$style\">$carrierName</span>"
              . "<span style=\"width:30%;$style\">$servicelevelTimeName</span>"
              . "<span style=\"width:30%;$style\">$servicelevelOtherName</span>";

        return '<div style="padding:2px 10px"><strong>' . $html . '</strong></div>';
    }
}
