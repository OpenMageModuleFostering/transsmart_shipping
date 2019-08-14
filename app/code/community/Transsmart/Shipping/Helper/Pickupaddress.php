<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Helper_Pickupaddress extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieves the pickup address from a quote.
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Sales_Model_Quote_Address|null Returns the pickup address, otherwise null
     */
    public function getPickupAddressFromQuote($quote)
    {
        /** @var Mage_Sales_Model_Quote_Address $_address */
        foreach ($quote->getAddressesCollection() as $_address) {
            if (!$_address->isDeleted() && $_address->getAddressType() == 'transsmart_pickup') {
                return $_address;
            }
        }
        return null;
    }

    /**
     * Retrieves the pickup address from an order.
     *
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Address|null Returns the pickup address, otherwise null
     */
    public function getPickupAddressFromOrder($order)
    {
        /** @var Mage_Sales_Model_Quote_Address $_address */
        foreach ($order->getAddressesCollection() as $_address) {
            if (!$_address->isDeleted() && $_address->getAddressType() == 'transsmart_pickup') {
                return $_address;
            }
        }
        return null;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Quote_Address $pickupAddress
     * @return $this
     */
    public function setQuotePickupAddress($quote, $pickupAddress)
    {
        $currentPickupAddress = null;
        foreach ($quote->getAddressesCollection() as $_address) {
            if (!$_address->isDeleted() && $_address->getAddressType() == 'transsmart_pickup') {
                $currentPickupAddress = $_address;
                break;
            }
        }

        if (!empty($currentPickupAddress)) {
            $currentPickupAddress->addData($pickupAddress->getData());
        }
        else {
            $quote->addAddress($pickupAddress->setAddressType('transsmart_pickup'));
        }

        return $this;
    }

    /**
     * Saves the location data into the specified quote.
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param array $pickupAddressData
     * @return $this
     * @throws Exception
     */
    public function savePickupAddressIntoQuote($quote, $pickupAddressData)
    {
        /** @var Mage_Sales_Model_Quote_Address $pickupAddress */
        $pickupAddress = Mage::getModel('sales/quote_address')
            ->setSaveInAddressBook(0);

        // pre-populate the pickup address with the shipping address details
        /** @var Mage_Sales_Model_Quote_Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();
        $pickupAddress->setCustomerId($shippingAddress->getCustomerId())
            ->setPrefix($shippingAddress->getPrefix())
            ->setFirstname($shippingAddress->getFirstname())
            ->setMiddlename($shippingAddress->getMiddlename())
            ->setLastname($shippingAddress->getLastname())
            ->setSuffix($shippingAddress->getSuffix())
            ->setTelephone($shippingAddress->getTelephone());

        // add the location data
        $pickupAddress->setCompany($pickupAddressData['name'])
            ->setPostcode($pickupAddressData['zipcode'])
            ->setCity($pickupAddressData['city'])
            ->setCountryId($pickupAddressData['country'])
            ->setStreetFull($pickupAddressData['street'] . "\n" . $pickupAddressData['street_no'])
            ->setTranssmartServicepointId($pickupAddressData['servicepoint_id']);

        return $this->setQuotePickupAddress($quote, $pickupAddress);
    }

    /**
     * Removes the pickup address from the quote.
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return $this
     * @throws Exception
     */
    public function removePickupAddressFromQuote($quote)
    {
        /** @var Mage_Sales_Model_Quote_Address $_address */
        foreach ($quote->getAddressesCollection() as $_address) {
            if ($_address->getAddressType() == 'transsmart_pickup') {
                $_address->isDeleted(true);
                break;
            }
        }
        return $this;
    }
}
