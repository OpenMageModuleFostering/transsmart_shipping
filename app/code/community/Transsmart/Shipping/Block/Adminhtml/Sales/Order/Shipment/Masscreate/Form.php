<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_Sales_Order_Shipment_Masscreate_Form extends Mage_Adminhtml_Block_Sales_Order_Abstract
{
    /**
     * Constructor.
     */
    public function _construct()
    {
        $this->setTemplate('transsmart/shipping/sales/order/shipment/masscreate/form.phtml');
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

        $this->setChild('action', $this->getLayout()->createBlock(
            'adminhtml/template',
            'shipment.create.transsmart.action',
            array(
                'template' => 'transsmart/shipping/sales/order/shipment/create/action.phtml'
            )
        ));

        $this->setChild(
            'submit_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                'label'     => Mage::helper('sales')->__('Create Shipment'),
                'class'     => 'save submit-button',
                'onclick'   => 'submitShipment(this);',
            ))
        );

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/massCreateSave');
    }

    /**
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    public function getOrderCollection()
    {
        return $this->getParentBlock()->getOrderCollection();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return float
     */
    public function getQtyToShip($order)
    {
        $qty = 0.0;
        if ($order->canShip()) {
            foreach ($order->getAllItems() as $_item) {
                if ($_item->getQtyToShip() > 0 && !$_item->getIsVirtual() && !$_item->getLockedDoShip()) {
                    $qty += $_item->getQtyToShip();
                }
            }
        }
        return $qty;
    }

    /**
     * @return int
     */
    public function getTotalOrderCount()
    {
        return count($this->getOrderCollection());
    }

    /**
     * @return int
     */
    public function getTotalShipmentsToCreate()
    {
        $count = 0;
        /** @var Mage_Sales_Model_Order $_order */
        foreach ($this->getOrderCollection() as $_order) {
            if ($this->getQtyToShip($_order) > 0) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * @return float
     */
    public function getTotalQtyToShip()
    {
        $qty = 0.0;
        /** @var Mage_Sales_Model_Order $_order */
        foreach ($this->getOrderCollection() as $_order) {
            $qty += $this->getQtyToShip($_order);
        }
        return $qty;
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
     * Return array with available shipment locations.
     *
     * @return array
     */
    public function getShipmentlocations()
    {
        return Mage::getResourceSingleton('transsmart_shipping/shipmentlocation_collection')->toOptionHash();
    }

    /**
     * Return array with available incoterms.
     *
     * @return array
     */
    public function getIncoterms()
    {
        return Mage::getResourceSingleton('transsmart_shipping/incoterm_collection')->toOptionHash();
    }

    /**
     * Return array with available email types.
     *
     * @return array
     */
    public function getEmailtypes()
    {
        return Mage::getResourceSingleton('transsmart_shipping/emailtype_collection')->toOptionHash();
    }

    /**
     * Return array with available cost centers.
     *
     * @return array
     */
    public function getCostcenters()
    {
        return Mage::getResourceSingleton('transsmart_shipping/costcenter_collection')->toOptionHash();
    }
}
