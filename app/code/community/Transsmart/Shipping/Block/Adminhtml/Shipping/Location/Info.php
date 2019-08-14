<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_Shipping_Location_Info extends Mage_Adminhtml_Block_Template
{
    /**
     * Constructor.
     */
    public function _construct()
    {
        $this->setTemplate('transsmart/shipping/location/info.phtml');
        return parent::_construct();
    }

    /**
     * Retrieves the pickup address from the current quote.
     *
     * @return Mage_Sales_Model_Quote_Address|null
     */
    public function getPickupAddress()
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getSingleton('adminhtml/sales_order_create')->getQuote();

        return Mage::helper('transsmart_shipping/pickupaddress')->getPickupAddressFromQuote($quote);
    }
}
