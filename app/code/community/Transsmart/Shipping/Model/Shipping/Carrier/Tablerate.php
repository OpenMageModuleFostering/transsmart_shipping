<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Shipping_Carrier_Tablerate extends Mage_Shipping_Model_Carrier_Tablerate
{
    /**
     * Collect and get rates. Original table rate returns only one rate. This one adds an additional field named
     * transsmart_carrierprofile_id, and can return multiple rates they have the same country/region/zip/condition.
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        // exclude Virtual products price from Package value if pre-configured
        if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getProduct()->isVirtual()) {
                            $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
                        }
                    }
                } elseif ($item->getProduct()->isVirtual()) {
                    $request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
                }
            }
        }

        // Free shipping by qty
        $freeQty = 0;
        if ($request->getAllItems()) {
            $freePackageValue = 0;
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeShipping = is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0;
                            $freeQty += $item->getQty() * ($child->getQty() - $freeShipping);
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    $freeShipping = is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : 0;
                    $freeQty += $item->getQty() - $freeShipping;
                    $freePackageValue += $item->getBaseRowTotal();
                }
            }
            $oldValue = $request->getPackageValue();
            $request->setPackageValue($oldValue - $freePackageValue);
        }

        if ($freePackageValue) {
            $request->setPackageValue($request->getPackageValue() - $freePackageValue);
        }
        if (!$request->getConditionName()) {
            $conditionName = $this->getConfigData('condition_name');
            $request->setConditionName($conditionName ? $conditionName : $this->_default_condition_name);
        }

        // Package weight and qty free shipping
        $oldWeight = $request->getPackageWeight();
        $oldQty = $request->getPackageQty();

        $request->setPackageWeight($request->getFreeMethodWeight());
        $request->setPackageQty($oldQty - $freeQty);

        $result = $this->_getModel('shipping/rate_result');
        $rates = $this->getRates($request);

        $request->setPackageWeight($oldWeight);
        $request->setPackageQty($oldQty);

        $count = 0;
        foreach ($rates as $_rate) {
            if ($_rate['price'] >= 0) {
                $method = $this->_getModel('shipping/rate_result_method');

                $method->setCarrier('tablerate');
                $method->setCarrierTitle($this->getConfigData('title'));

                $method->setMethod($this->getUniqueMethodCode($_rate['transsmart_carrierprofile_id']));
                $method->setMethodTitle($this->getUniqueMethodTitle($_rate['transsmart_carrierprofile_id']));

                if ($request->getFreeShipping() === true || ($request->getPackageQty() == $freeQty)) {
                    $shippingPrice = 0;
                }
                else {
                    $shippingPrice = $this->getFinalPriceWithHandlingFee($_rate['price']);
                }

                $method->setPrice($shippingPrice);
                $method->setCost($_rate['cost']);

                $method->setTranssmartCarrierprofileId($_rate['transsmart_carrierprofile_id']);

                $result->append($method);

                $count++;
            }
        }

        if ($count == 0 && $request->getFreeShipping() === true) {
            /**
             * was applied promotion rule for whole cart
             * other shipping methods could be switched off at all
             * we must show table rate method with 0$ price, if grand_total more, than min table condition_value
             * free setPackageWeight() has already was taken into account
             */
            $request->setPackageValue($freePackageValue);
            $request->setPackageQty($freeQty);
            $rates = $this->getRates($request);
            foreach ($rates as $_rate) {
                if ($_rate['price'] >= 0) {
                    $method = $this->_getModel('shipping/rate_result_method');

                    $method->setCarrier('tablerate');
                    $method->setCarrierTitle($this->getConfigData('title'));

                    $method->setMethod($this->getUniqueMethodCode($_rate['transsmart_carrierprofile_id']));
                    $method->setMethodTitle($this->getUniqueMethodTitle($_rate['transsmart_carrierprofile_id']));

                    $method->setPrice(0);
                    $method->setCost(0);

                    $method->setTranssmartCarrierprofileId($_rate['transsmart_carrierprofile_id']);

                    $result->append($method);

                    $count++;
                }
            }
        }
        elseif ($count == 0) {
            $error = $this->_getModel('shipping/rate_result_error');
            $error->setCarrier('tablerate');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        }

        return $result;
    }

    /**
     * Get Rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return array
     */
    public function getRates(Mage_Shipping_Model_Rate_Request $request)
    {
        return Mage::getResourceModel('shipping/carrier_tablerate')->getRates($request);
    }

    /**
     * Get unique method code.
     *
     * @param int $carrierprofileId
     * @return string
     */
    protected function getUniqueMethodCode($carrierprofileId)
    {
        return 'bestway' . ($carrierprofileId ? '_' . $carrierprofileId : '');
    }

    /**
     * Get unique method title.
     *
     * @param int $carrierprofileId
     * @return string
     */
    protected function getUniqueMethodTitle($carrierprofileId)
    {
        $title = false;

        if ($carrierprofileId) {
            /** @var Transsmart_Shipping_Model_Carrierprofile $carrierprofile */
            $carrierprofile = Mage::getResourceSingleton('transsmart_shipping/carrierprofile_collection')
                ->getItemById($carrierprofileId);

            if ($carrierprofile) {
                $title = $carrierprofile->getConfigData('title', $this->getStore());
            }
        }

        if (empty($title)) {
            $title = $this->getConfigData('name');
        }

        return $title;
    }
}
