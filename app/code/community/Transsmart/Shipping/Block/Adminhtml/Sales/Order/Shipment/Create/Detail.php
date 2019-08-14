<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_Sales_Order_Shipment_Create_Detail extends Mage_Adminhtml_Block_Template
{
    protected $_defaultCarrierprofileId;
    protected $_defaultShipmentlocationId;
    protected $_defaultEmailtypeId;
    protected $_defaultCostcenterId;
    protected $_defaultIncotermId;

    /**
     * Constructor.
     */
    public function _construct()
    {
        $this->setTemplate('transsmart/shipping/sales/order/shipment/create/detail.phtml');
        return parent::_construct();
    }

    /**
     * Prepares layout of block
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild('package', $this->getLayout()->createBlock(
            'transsmart_shipping/adminhtml_sales_order_shipment_create_package'
        ));
        return parent::_prepareLayout();
    }

    /**
     * Get store for current shipment.
     *
     * @return Mage_Core_Model_Store|null
     */
    public function getStore()
    {
        $shipment = Mage::registry('current_shipment');
        if ($shipment) {
            return $shipment->getStore();
        }
        return null;
    }

    /**
     * Returns TRUE if the carrierprofile may be changed. This is not allowed when the order has a pickup address.
     *
     * @return bool
     */
    public function getAllowChangeCarrierprofile()
    {
        return Mage::helper('transsmart_shipping/shipment')
            ->getAllowChangeCarrierprofile(Mage::registry('current_shipment'));
    }

    /**
     * Get available carrier profiles.
     *
     * @return array
     */
    public function getAvailableCarrierprofiles()
    {
        $carrierprofiles = Mage::getResourceSingleton('transsmart_shipping/carrierprofile_collection')
            ->joinCarrier()
            ->joinServicelevelTime()
            ->joinServicelevelOther();

        $res = array();
        /** @var Transsmart_Shipping_Model_Carrierprofile $_carrierprofile */
        foreach ($carrierprofiles as $_carrierprofile) {
            if (!$_carrierprofile->isLocationSelectEnabled()) {
                $res[$_carrierprofile->getData('carrierprofile_id')] = $_carrierprofile->getName();
            }
        }

        return $res;
    }

    /**
     * Get the default carrier profile ID for the current shipment.
     *
     * @return int
     */
    public function getDefaultCarrierprofileId()
    {
        if (empty($this->_defaultCarrierprofileId)) {
            $this->_defaultCarrierprofileId = Mage::helper('transsmart_shipping/shipment')
                ->getDefaultCarrierprofileId(Mage::registry('current_shipment'));
        }
        return $this->_defaultCarrierprofileId;
    }

    /**
     * Get the default carrier profile for the current shipment.
     *
     * @return string
     */
    public function getDefaultCarrierprofile()
    {
        $carrierprofile = Mage::getResourceSingleton('transsmart_shipping/carrierprofile_collection')
            ->joinCarrier()
            ->joinServicelevelTime()
            ->joinServicelevelOther()
            ->getItemById($this->getDefaultCarrierprofileId());
        if ($carrierprofile) {
            return $carrierprofile->getName();
        }
    }

    /**
     * Get available shipment locations.
     *
     * @return array
     */
    public function getShipmentlocations()
    {
        return Mage::getResourceSingleton('transsmart_shipping/shipmentlocation_collection')->toOptionHash();
    }

    /**
     * Get the default shipment location for the current shipment.
     *
     * @return array
     */
    public function getDefaultShipmentlocationId()
    {
        if (is_null($this->_defaultShipmentlocationId)) {
            $this->_defaultShipmentlocationId = Mage::helper('transsmart_shipping/shipment')
                ->getDefaultShipmentlocationId($this->getStore());
        }
        return $this->_defaultShipmentlocationId;
    }

    /**
     * Get available email types.
     *
     * @return array
     */
    public function getEmailtypes()
    {
        return Mage::getResourceSingleton('transsmart_shipping/emailtype_collection')->toOptionHash();
    }

    /**
     * Get the default email type for the current shipment.
     *
     * @return array
     */
    public function getDefaultEmailtypeId()
    {
        if (is_null($this->_defaultEmailtypeId)) {
            $this->_defaultEmailtypeId = Mage::helper('transsmart_shipping/shipment')
                ->getDefaultEmailtypeId($this->getStore());
        }
        return $this->_defaultEmailtypeId;
    }

    /**
     * Get available cost centers.
     *
     * @return array
     */
    public function getCostcenters()
    {
        return Mage::getResourceSingleton('transsmart_shipping/costcenter_collection')->toOptionHash();
    }

    /**
     * Get the default cost center for the current shipment.
     *
     * @return array
     */
    public function getDefaultCostcenterId()
    {
        if (is_null($this->_defaultCostcenterId)) {
            $this->_defaultCostcenterId = Mage::helper('transsmart_shipping/shipment')
                ->getDefaultCostcenterId($this->getStore());
        }
        return $this->_defaultCostcenterId;
    }

    /**
     * Get available incoterms.
     *
     * @return array
     */
    public function getIncoterms()
    {
        return Mage::getResourceSingleton('transsmart_shipping/incoterm_collection')->toOptionHash();
    }

    /**
     * Get the default incoterm for the current shipment.
     *
     * @return array
     */
    public function getDefaultIncotermId()
    {
        if (is_null($this->_defaultIncotermId)) {
            $this->_defaultIncotermId = Mage::helper('transsmart_shipping/shipment')
                ->getDefaultIncotermId($this->getStore());
        }
        return $this->_defaultIncotermId;
    }
}
