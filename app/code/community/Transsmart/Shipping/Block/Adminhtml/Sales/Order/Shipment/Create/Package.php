<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_Sales_Order_Shipment_Create_Package extends Mage_Adminhtml_Block_Template
{
    /**
     * Constructor.
     */
    public function _construct()
    {
        $this->setTemplate('transsmart/shipping/sales/order/shipment/create/package.phtml');
        return parent::_construct();
    }

    /**
     * Prepares layout of block
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => $this->__('Add Package'),
                    'class'   => '',
                    'onclick' => 'tssPackageControl.add()'
                ))

        );
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
     * Get package type configuration.
     *
     * @return array
     */
    public function getPackageConfig()
    {
        $result = array(
            'default' => 0,
            'types'   => array(),
        );

        if (($store = $this->getStore())) {
            $result['default'] = Mage::helper('transsmart_shipping/shipment')->getDefaultPackagetypeId($store);
        }
        else {
            $result['types'][] = array(
                'id'     => 0,
                'name'   => $this->__('Use Configured Default')
            );
        }

        $format = array(
            'locale' => new Zend_Locale(Mage::app()->getLocale()->getLocale()),
            'number_format' => '0.###'
        );

        foreach (Mage::getResourceSingleton('transsmart_shipping/packagetype_collection') as $_packagetype) {
            $result['types'][] = array(
                'id'     => $_packagetype->getId(),
                'name'   => $_packagetype->getName(),
                'length' => Zend_Locale_Format::toNumber((float)$_packagetype->getLength(), $format),
                'width'  => Zend_Locale_Format::toNumber((float)$_packagetype->getWidth(), $format),
                'height' => Zend_Locale_Format::toNumber((float)$_packagetype->getHeight(), $format),
                'weight' => Zend_Locale_Format::toNumber((float)$_packagetype->getWeight(), $format),
            );
        }

        return $result;
    }
}
