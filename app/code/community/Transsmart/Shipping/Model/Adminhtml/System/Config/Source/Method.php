<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Method
{
    const NOT_AVAILABLE = '';
    const DELIVERY      = 'transsmartdelivery';
    const PICKUP        = 'transsmartpickup';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $_helper = Mage::helper('adminhtml');

        $options = array(
            array(
                'value' => self::NOT_AVAILABLE,
                'label' => $_helper->__('No')
            ),
            array(
                'value' => self::DELIVERY,
                'label' => $_helper->__('As Delivery Option')
            ),
            array(
                'value' => self::PICKUP,
                'label' => $_helper->__('As Pickup Option')
            ),
        );

        return $options;
    }
}
