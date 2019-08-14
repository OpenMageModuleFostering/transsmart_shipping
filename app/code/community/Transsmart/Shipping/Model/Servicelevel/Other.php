<?php

/**
 * Transsmart Servicelevel Other Model
 *
 * @method Transsmart_Shipping_Model_Resource_Servicelevel_Other _getResource()
 * @method Transsmart_Shipping_Model_Resource_Servicelevel_Other getResource()
 * @method int getServicelevelOtherId()
 * @method Transsmart_Shipping_Model_Servicelevel_Other setServicelevelOtherId(int $value)
 * @method string getCode()
 * @method Transsmart_Shipping_Model_Servicelevel_Other setCode(string $value)
 * @method string getName()
 * @method Transsmart_Shipping_Model_Servicelevel_Other setName(string $value)
 *
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Servicelevel_Other extends Transsmart_Shipping_Model_Abstract
{
    /**
     * A list of API keys to be mapped to database columns.
     * @var array
     */
    protected $apiKeysMapping = array(
        'Id'      => 'servicelevel_other_id',
        'Code'    => 'code',
        'Name'    => 'name',
        'Deleted' => 'deleted'
    );

    protected function _construct()
    {
        $this->_init('transsmart_shipping/servicelevel_other');
    }
}
