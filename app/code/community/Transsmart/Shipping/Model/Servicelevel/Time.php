<?php

/**
 * Transsmart Servicelevel Time Model
 *
 * @method Transsmart_Shipping_Model_Resource_Servicelevel_Time _getResource()
 * @method Transsmart_Shipping_Model_Resource_Servicelevel_Time getResource()
 * @method int getServicelevelTimeId()
 * @method Transsmart_Shipping_Model_Servicelevel_Time setServicelevelTimeId(int $value)
 * @method string getCode()
 * @method Transsmart_Shipping_Model_Servicelevel_Time setCode(string $value)
 * @method string getName()
 * @method Transsmart_Shipping_Model_Servicelevel_Time setName(string $value)
 *
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Servicelevel_Time extends Transsmart_Shipping_Model_Abstract
{
    /**
     * A list of API keys to be mapped to database columns.
     * @var array
     */
    protected $apiKeysMapping = array(
        'Id'      => 'servicelevel_time_id',
        'Code'    => 'code',
        'Name'    => 'name',
        'Deleted' => 'deleted'
    );

    protected function _construct()
    {
        $this->_init('transsmart_shipping/servicelevel_time');
    }
}
