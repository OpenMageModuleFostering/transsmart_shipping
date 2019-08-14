<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_Sales_Order_Shipment_View_Detail extends Mage_Adminhtml_Block_Template
{
    /**
     * Constructor.
     */
    public function _construct()
    {
        $this->setTemplate('transsmart/shipping/sales/order/shipment/view/detail.phtml');
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
            'transsmart_shipping/adminhtml_sales_order_shipment_view_package'
        ));
        return parent::_prepareLayout();
    }

    /**
     * Retrieve shipment model instance
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    public function getShipment()
    {
        return Mage::registry('current_shipment');
    }

    /**
     * Get name of carrier profile for current shipment.
     *
     * @return string
     */
    public function getCarrierprofile()
    {
        $carrierprofile = Mage::getResourceSingleton('transsmart_shipping/carrierprofile_collection')
            ->joinCarrier()
            ->joinServicelevelTime()
            ->joinServicelevelOther()
            ->getItemById($this->getShipment()->getTranssmartCarrierprofileId());
        if ($carrierprofile) {
            return $carrierprofile->getName();
        }
    }

    /**
     * Get name of shipment location for current shipment.
     *
     * @return string
     */
    public function getShipmentlocation()
    {
        $shipmentlocation = Mage::getResourceSingleton('transsmart_shipping/shipmentlocation_collection')
            ->getItemById($this->getShipment()->getTranssmartShipmentlocationId());
        if ($shipmentlocation) {
            return $shipmentlocation->getName();
        }
    }

    /**
     * Get name of email type for current shipment.
     *
     * @return string
     */
    public function getEmailtype()
    {
        $emailtype = Mage::getResourceSingleton('transsmart_shipping/emailtype_collection')
            ->getItemById($this->getShipment()->getTranssmartEmailtypeId());
        if ($emailtype) {
            return $emailtype->getName();
        }
    }

    /**
     * Get name of cost center for current shipment.
     *
     * @return string
     */
    public function getCostcenter()
    {
        $costcenter = Mage::getResourceSingleton('transsmart_shipping/costcenter_collection')
            ->getItemById($this->getShipment()->getTranssmartCostcenterId());
        if ($costcenter) {
            return $costcenter->getName();
        }
    }

    /**
     * Get name of incoterm for current shipment.
     *
     * @return string
     */
    public function getIncoterm()
    {
        $incoterm = Mage::getResourceSingleton('transsmart_shipping/incoterm_collection')
            ->getItemById($this->getShipment()->getTranssmartIncotermId());
        if ($incoterm) {
            return $incoterm->getName();
        }
    }

    /**
     * Get Transsmart Document Id
     *
     * @return string
     */
    public function getTranssmartDocumentId()
    {
        $result = $this->getShipment()->getTranssmartDocumentId();
        if (empty($result)) {
            $result = $this->__('(empty)');
        }
        return $result;
    }

    /**
     * Get Transsmart Status
     *
     * @return string
     */
    public function getTranssmartStatus()
    {
        $result = $this->getShipment()->getTranssmartStatus();
        if (empty($result)) {
            $result = $this->__('(empty)');
        }
        return $result;
    }

    /**
     * Get tracking link for current shipment.
     *
     * @return string
     */
    public function getTrackingLink()
    {
        $url = $this->getShipment()->getTranssmartTrackingUrl();
        if ($url) {
            $label = Mage::helper('transsmart_shipping')->getTrackingCodeFromUrl($url);
            if (empty($label)) {
                $label = $this->__('Click here');
            }
            $result = '<a href="' . $this->escapeHtml($url) . '" title="' . $this->escapeHtml($url) . '"'
                    . ' target="_blank">' . $this->escapeHtml($label) . '</a>';
        }
        else {
            $result = $this->escapeHtml($this->__('(empty)'));
        }
        return $result;
    }
}
