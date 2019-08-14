<?php

/**
 * Transsmart Carrier Model
 *
 * @method Transsmart_Shipping_Model_Resource_Carrier _getResource()
 * @method Transsmart_Shipping_Model_Resource_Carrier getResource()
 * @method int getCarrierId()
 * @method Transsmart_Shipping_Model_Carrier setCarrierId(int $value)
 * @method string getCode()
 * @method Transsmart_Shipping_Model_Carrier setCode(string $value)
 * @method string getName()
 * @method Transsmart_Shipping_Model_Carrier setName(string $value)
 * @method bool getLocationSelect()
 * @method Transsmart_Shipping_Model_Carrier setLocationSelect(bool $value)
 *
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Carrier extends Transsmart_Shipping_Model_Abstract
{
    /**
     * A list of API keys to be mapped to database columns.
     * @var array
     */
    protected $apiKeysMapping = array(
        'Id'             => 'carrier_id',
        'Code'           => 'code',
        'Name'           => 'name',
        'LocationSelect' => 'location_select'
    );

    /**
     * Object initialization
     */
    protected function _construct()
    {
        $this->_init('transsmart_shipping/carrier');
    }
}
