<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Checkout_Onepage_Shipping_Method_Available
    extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
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
