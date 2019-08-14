<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Mapping_Street
{
    const FULL     = -1;
    const NONE     = 0;
    const STREET_1 = 1;
    const STREET_2 = 2;
    const STREET_3 = 3;
    const STREET_4 = 4;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $_helper = Mage::helper('adminhtml');

        $options = array(
            array(
                'value' => self::NONE,
                'label' => $_helper->__('(empty)')
            ),
            array(
                'value' => self::STREET_1,
                'label' => $_helper->__('Street %s', 1)
            ),
            array(
                'value' => self::STREET_2,
                'label' => $_helper->__('Street %s', 2)
            ),
            array(
                'value' => self::STREET_3,
                'label' => $_helper->__('Street %s', 3)
            ),
            array(
                'value' => self::STREET_4,
                'label' => $_helper->__('Street %s', 4)
            ),
            array(
                'value' => self::FULL,
                'label' => $_helper->__('Combined Street Fields')
            ),
        );

        return $options;
    }
}
