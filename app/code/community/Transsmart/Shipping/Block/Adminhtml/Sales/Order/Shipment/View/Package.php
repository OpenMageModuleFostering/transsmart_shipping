<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_Sales_Order_Shipment_View_Package extends Mage_Adminhtml_Block_Template
{
    /**
     * Constructor.
     */
    public function _construct()
    {
        $this->setTemplate('transsmart/shipping/sales/order/shipment/view/package.phtml');
        return parent::_construct();
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
     * Get formatted package lines for the current shipment.
     *
     * @return array
     */
    public function getFormattedPackageLines()
    {
        $packages = $this->getShipment()->getTranssmartPackages();
        if ($packages) {
            $packages = @unserialize($packages);
        }

        $result = array();
        if ($packages) {
            $format = array('locale' => new Zend_Locale(Mage::app()->getLocale()->getLocale()));

            foreach ($packages as $_package) {
                $result[] = array(
                    'package_type' => $_package['PackagingType'],
                    'qty'          => $_package['Quantity'],
                    'length'       => Zend_Locale_Format::toNumber($_package['Length'], $format),
                    'width'        => Zend_Locale_Format::toNumber($_package['Width'], $format),
                    'height'       => Zend_Locale_Format::toNumber($_package['Height'], $format),
                    'weight'       => Zend_Locale_Format::toNumber($_package['Weight'], $format),
                );
            }
        }

        return $result;
    }
}
