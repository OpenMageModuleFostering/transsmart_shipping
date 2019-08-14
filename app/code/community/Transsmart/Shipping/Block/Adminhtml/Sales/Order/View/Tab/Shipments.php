<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_Sales_Order_View_Tab_Shipments
    extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Shipments
{
    /**
     * Returns TRUE if the order uses a Transsmart shipping method.
     *
     * @return bool
     */
    public function usesTranssmartShippingMethod()
    {
        $shippingMethod = $this->getOrder()->getShippingMethod();
        return Mage::helper('transsmart_shipping')->isTranssmartShippingMethod($shippingMethod);
    }

    /**
     * Set collection object
     *
     * @param Varien_Data_Collection $collection
     */
    public function setCollection($collection)
    {
        if ($collection instanceof Mage_Sales_Model_Resource_Order_Shipment_Grid_Collection) {
            if ($this->usesTranssmartShippingMethod()) {
                $collection
                    ->addFieldToSelect('transsmart_document_id')
                    ->addFieldToSelect('transsmart_status')
                    ->addFieldToSelect('transsmart_tracking_url');
            }
        }
        return parent::setCollection($collection);
    }

    /**
     * Prepare and add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        if ($this->usesTranssmartShippingMethod()) {
            $this->addColumnAfter('transsmart_document_id', array(
                'header'  => Mage::helper('sales')->__('Transsmart Document Id'),
                'index'   => 'transsmart_document_id',
                'type'    => 'text',
                'width'   => '70px',
            ), 'total_qty');

            $this->addColumnAfter('transsmart_status', array(
                'header'  => Mage::helper('sales')->__('Transsmart Status'),
                'index'   => 'transsmart_status',
                'type'    => 'options',
                'width'   => '70px',
                'options' => Mage::helper('transsmart_shipping/shipment')->getShipmentStatuses(),
            ), 'transsmart_document_id');

            $this->addColumnAfter('transsmart_tracking_url', array(
                'header'    => Mage::helper('transsmart_shipping')->__('Track & Trace'),
                'index'     => 'transsmart_tracking_url',
                'width'     => '70px',
                'renderer'  => 'transsmart_shipping/adminhtml_sales_shipment_grid_renderer_link',
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
            ), 'transsmart_status');
        }

        return parent::_prepareColumns();
    }
}
