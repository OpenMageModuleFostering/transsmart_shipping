<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Basedata_Emailtype
    extends Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Basedata_Abstract
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAvailableOptions('transsmart_shipping/emailtype', 'emailtype_id');
    }
}
