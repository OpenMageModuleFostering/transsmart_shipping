<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_Sales_Shipment_Grid_Renderer_Link
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        $value =  $row->getData($this->getColumn()->getIndex());
        if ($value == null || strlen($value) == 0) {
            return '';
        }
        
        return '<a href="'. $value . '" target="_blank">' . Mage::helper('transsmart_shipping')->__('View') . '</span>';
    }
}