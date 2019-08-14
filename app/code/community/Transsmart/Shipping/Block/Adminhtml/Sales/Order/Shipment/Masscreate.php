<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_Sales_Order_Shipment_Masscreate
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'transsmart_shipping';
        $this->_objectId = 'order_ids';
        $this->_controller = 'adminhtml_sales_order_shipment';
        $this->_mode = 'masscreate';

        parent::__construct();

        //$this->_updateButton('save', 'label', Mage::helper('sales')->__('Submit Shipment'));
        $this->_removeButton('save');
        $this->_removeButton('delete');
    }

    public function getHeaderText()
    {
        $header = $this->__('Create New Shipment(s) for Order(s)');
        return $header;
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/sales_order/index');
    }
}
