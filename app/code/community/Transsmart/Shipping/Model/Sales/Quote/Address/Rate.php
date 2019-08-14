<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Sales_Quote_Address_Rate extends Mage_Sales_Model_Quote_Address_Rate
{
    public function importShippingRate(Mage_Shipping_Model_Rate_Result_Abstract $rate)
    {
        if ($rate instanceof Mage_Shipping_Model_Rate_Result_Method) {
            $this->setTranssmartCarrierprofileId($rate->getTranssmartCarrierprofileId());
        }
        return parent::importShippingRate($rate);
    }
}
