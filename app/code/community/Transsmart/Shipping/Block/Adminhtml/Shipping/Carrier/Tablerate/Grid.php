<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_Shipping_Carrier_Tablerate_Grid
    extends Mage_Adminhtml_Block_Shipping_Carrier_Tablerate_Grid
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setModuleName('Mage_Adminhtml');
        parent::__construct();
    }

    /**
     * Prepare table columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter('transsmart_carrierprofile_id', array(
            'header'    => Mage::helper('transsmart_shipping')->__('Transsmart Carrier Profile Id'),
            'index'     => 'transsmart_carrierprofile_id'
        ), 'price');

        return parent::_prepareColumns();
    }
}
