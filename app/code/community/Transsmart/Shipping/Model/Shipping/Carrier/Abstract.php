<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Shipping_Carrier_Abstract
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_isFixed = true;

    /**
     * Collect available shipping methods and calculate rates.
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $freeBoxes = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $_item) {
                if ($_item->getProduct()->isVirtual() || $_item->getParentItem()) {
                    continue;
                }

                if ($_item->getHasChildren() && $_item->isShipSeparately()) {
                    foreach ($_item->getChildren() as $_child) {
                        if ($_child->getFreeShipping() && !$_child->getProduct()->isVirtual()) {
                            $freeBoxes += $_item->getQty() * $_child->getQty();
                        }
                    }
                } elseif ($_item->getFreeShipping()) {
                    $freeBoxes += $_item->getQty();
                }
            }
        }
        $this->setFreeBoxes($freeBoxes);

        $result = Mage::getModel('shipping/rate_result');

        /** @var Transsmart_Shipping_Model_Resource_Carrierprofile_Collection $carrierprofiles */
        $carrierprofiles = Mage::getResourceSingleton('transsmart_shipping/carrierprofile_collection');

        foreach ($carrierprofiles as $_carrierprofile) {
            if ($_carrierprofile->getConfigData('method', $this->getStore()) != $this->getCarrierCode()) {
                continue;
            }

            // check if carrier profile is allowed for this country
            if ($_carrierprofile->getConfigData('sallowspecific', $this->getStore()) == 1) {
                $_availableCountries = $_carrierprofile->getConfigData('specificcountry', $this->getStore());
                $_availableCountries = array_filter(explode(',', $_availableCountries));
                if (!in_array($request->getDestCountryId(), $_availableCountries)) {
                    continue;
                }
            }

            // calculate shipping price
            $_shippingPrice = $_carrierprofile->getConfigData('price', $this->getStore());

            // apply free shipping
            if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
                $_shippingPrice = '0.00';
            }

            // append the method to the result
            $_method = Mage::getModel('shipping/rate_result_method');

            $_method->setCarrier($this->getCarrierCode());
            $_method->setCarrierTitle($this->getConfigData('title'));

            $_method->setMethod('carrierprofile_' . $_carrierprofile->getId());
            $_method->setMethodTitle($_carrierprofile->getConfigData('title', $this->getStore()));

            $_method->setPrice($_shippingPrice);
            $_method->setCost($_shippingPrice);

            $result->append($_method);
        }

        return $result;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $result = array();

        /** @var Transsmart_Shipping_Model_Resource_Carrierprofile_Collection $carrierprofiles */
        $carrierprofiles = Mage::getResourceSingleton('transsmart_shipping/carrierprofile_collection');

        foreach ($carrierprofiles as $_carrierprofile) {
            if ($_carrierprofile->getConfigData('method', $this->getStore()) != $this->getCarrierCode()) {
                continue;
            }

            $result['carrierprofile_' . $_carrierprofile->getId()]
                = $_carrierprofile->getConfigData('title', $this->getStore());
        }

        return $result;
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return $this
     */
    public function checkAvailableShipCountries(Mage_Shipping_Model_Rate_Request $request)
    {
        return $this;
    }

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * Check if carrier has shipping label option available
     *
     * @return boolean
     */
    public function isShippingLabelsAvailable()
    {
        return false; // yes, but labels are not created in Magento
    }
}
