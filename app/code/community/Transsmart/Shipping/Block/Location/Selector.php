<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Location_Selector extends Mage_Core_Block_Template
{
    /**
     * Retrieve the shipping address
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getShippingAddress()
    {
        return Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress();
    }
}
