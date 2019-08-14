<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_System_Config_Form_Field_Locationselect
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $originalData = $element->getContainer()->getOriginalData();

        $value = $originalData['enable_location_select'];
        if (is_null($value) || $value === '') {
            $label = 'Unknown';
        }
        elseif ($value) {
            $label = 'Enabled';
        }
        else {
            $label = 'Disabled';
        }

        return '<b>' . $this->escapeHtml(Mage::helper('adminhtml')->__($label)) . '</b>';
    }
}
