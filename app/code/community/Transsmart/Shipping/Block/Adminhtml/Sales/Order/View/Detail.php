<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_Sales_Order_View_Detail extends Mage_Adminhtml_Block_Template
{
    protected $_carrierprofileId;

    /**
     * Constructor.
     */
    public function _construct()
    {
        $this->setTemplate('transsmart/shipping/sales/order/address/form/container.phtml');
        return parent::_construct();
    }

    /**
     * Get the default carrier profile ID for the current order.
     *
     * @return int
     */
    public function getCarrierprofileId()
    {
        if (empty($this->_carrierprofileId)) {
            $order = Mage::registry('current_order');

            if (($shippingAddress = $order->getShippingAddress())) {
                /** @see Transsmart_Shipping_Model_Sales_Quote_Address_Total_Shipping::collect */
                $this->_carrierprofileId = $shippingAddress->getTranssmartCarrierprofileId();
            }

            if (empty($this->_carrierprofileId)) {
                // carrierprofile based on shipping method
                $shippingMethod = $order->getShippingMethod(false);
                $carrierprofile = Mage::getModel('transsmart_shipping/carrierprofile')
                    ->loadByShippingMethodCode($shippingMethod);
                $this->_carrierprofileId = $carrierprofile->getId();
            }

            if (!$this->_carrierprofileId) {
                // default configured carrierprofile for storeview
                $this->_carrierprofileId = Mage::getStoreConfig(
                    Transsmart_Shipping_Helper_Shipment::XML_PATH_DEFAULT_CARRIERPROFILE,
                    $order->getStore()
                );
            }
        }
        return $this->_carrierprofileId;
    }

    /**
     * Get the default carrier profile for the current order.
     *
     * @return string
     */
    public function getCarrierprofile()
    {
        $carrierprofile = Mage::getResourceSingleton('transsmart_shipping/carrierprofile_collection')
            ->joinCarrier()
            ->joinServicelevelTime()
            ->joinServicelevelOther()
            ->getItemById($this->getCarrierprofileId());
        if ($carrierprofile) {
            return $carrierprofile->getName();
        }
    }

    /**
     * Retrieves the pickup address from the order.
     *
     * @return Mage_Sales_Model_Order_Address|null Returns the pickup address, otherwise null
     */
    public function getPickupAddress()
    {
        $order = Mage::registry('current_order');
        return Mage::helper('transsmart_shipping/pickupaddress')->getPickupAddressFromOrder($order);
    }

    /**
     * Return flag indicating whether it is allowed to change the pickup address.
     *
     * @return bool
     */
    public function allowChangePickupAddress()
    {
        return false;
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('transsmart_shipping')->isTranssmartOrder(Mage::registry('current_order'))) {
            return '';
        }

        return parent::_toHtml();
    }
}
