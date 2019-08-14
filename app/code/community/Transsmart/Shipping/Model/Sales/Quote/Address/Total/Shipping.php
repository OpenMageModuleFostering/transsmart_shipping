<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Sales_Quote_Address_Total_Shipping extends Mage_Sales_Model_Quote_Address_Total_Shipping
{
    /**
     * Collect totals information about shipping
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Shipping
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $carrierprofileId = null;

        $method = $address->getShippingMethod();
        if ($method) {
            foreach ($address->getAllShippingRates() as $rate) {
                if ($rate->getCode() == $method) {
                    $carrierprofileId = $rate->getTranssmartCarrierprofileId();
                    break;
                }
            }
        }

        $address->setTranssmartCarrierprofileId($carrierprofileId);

        return $this;
    }
}
