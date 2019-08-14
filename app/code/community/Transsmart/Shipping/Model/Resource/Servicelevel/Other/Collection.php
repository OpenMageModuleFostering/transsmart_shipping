<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Resource_Servicelevel_Other_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('transsmart_shipping/servicelevel_other');
    }

    public function toOptionHash()
    {
        return $this->_toOptionHash('servicelevel_other_id');
    }
}
