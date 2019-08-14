<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Environment
{
    const STAGING    = 0;
    const PRODUCTION = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $_helper = Mage::helper('adminhtml');

        $options = array(
            array(
                'value' => self::STAGING,
                'label' => $_helper->__('Staging')
            ),
            array(
                'value' => self::PRODUCTION,
                'label' => $_helper->__('Production')
            ),
        );

        return $options;
    }
}
