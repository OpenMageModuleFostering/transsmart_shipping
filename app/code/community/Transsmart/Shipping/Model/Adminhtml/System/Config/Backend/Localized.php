<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Adminhtml_System_Config_Backend_Localized extends Varien_Object
{
    /**
     * Translate config value after loading.
     */
    public function afterLoad()
    {
        $value = (string)$this->getValue();
        $helper = Mage::helper('transsmart_shipping');

        if (!empty($value) && ($localized = $helper->__($value))) {
            $this->setValue($localized);
        }

        return $this;
    }
}
