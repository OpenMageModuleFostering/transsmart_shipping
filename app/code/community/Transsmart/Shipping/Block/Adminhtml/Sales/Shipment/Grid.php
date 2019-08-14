<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_Sales_Shipment_Grid extends Mage_Adminhtml_Block_Sales_Shipment_Grid
{
    /**
     * Prepare grid massaction actions
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();
        $this->getMassactionBlock()->removeItem('print_shipping_label');

        if (Mage::getSingleton('admin/session')->isAllowed('sales/shipment')) {
            $this->getMassactionBlock()->addItem('transsmart_book_and_print', array(
                'label'=> Mage::helper('transsmart_shipping')->__('Transsmart: Book & Print'),
                'url'  => $this->getUrl('*/transsmart_shipping_shipment/massBookAndPrint'),
            ));

            $this->getMassactionBlock()->addItem('transsmart_booking', array(
                'label'=> Mage::helper('transsmart_shipping')->__('Transsmart: Book Shipment(s)'),
                'url'  => $this->getUrl('*/transsmart_shipping_shipment/massBooking'),
            ));

            $this->getMassactionBlock()->addItem('transsmart_label', array(
                'label'=> Mage::helper('transsmart_shipping')->__('Transsmart: Print Label(s)'),
                'url'  => $this->getUrl('*/transsmart_shipping_shipment/massLabel'),
            ));
        }

        return $this;
    }

    /**
     * Prepare and add columns to grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter('transsmart_document_id', array(
            'header'  => Mage::helper('transsmart_shipping')->__('Transsmart Document Id'),
            'index'   => 'transsmart_document_id',
            'type'    => 'text',
            'width'   => '70px',
        ), 'total_qty');

        $this->addColumnAfter('transsmart_status', array(
            'header'  => Mage::helper('transsmart_shipping')->__('Transsmart Status'),
            'index'   => 'transsmart_status',
            'type'    => 'options',
            'width'   => '70px',
            'options' => Mage::helper('transsmart_shipping/shipment')->getShipmentStatuses(),
        ), 'transsmart_document_id');

        $this->addColumnAfter('transsmart_tracking_url', array(
            'header'  => Mage::helper('transsmart_shipping')->__('Track & Trace'),
            'index'   => 'transsmart_tracking_url',
            'width'   => '70px',
            'renderer'  => 'transsmart_shipping/adminhtml_sales_shipment_grid_renderer_link',
            'filter'    => false,
            'sortable'  => false,
            'is_system' => true
        ), 'transsmart_status');

        return parent::_prepareColumns();
    }
}
