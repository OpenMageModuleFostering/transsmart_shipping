<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_Sales_Order_Create_Shipping_Method_Form
    extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form
{
    /**
     * @return string
     */
    protected function _toHtml()
    {
        return parent::_toHtml()
             . Mage::helper('transsmart_shipping/location')->getMethodsUpdateHtml($this->getShippingRates());
    }
}
