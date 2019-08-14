<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Resource_Servicelevel_Time extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Primary key auto increment flag
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('transsmart_shipping/servicelevel_time', 'servicelevel_time_id');
    }
}
