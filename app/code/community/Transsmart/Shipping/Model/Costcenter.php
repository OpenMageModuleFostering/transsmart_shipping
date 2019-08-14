<?php

/**
 * Transsmart Cost Center Model
 *
 * @method Transsmart_Shipping_Model_Resource_Costcenter _getResource()
 * @method Transsmart_Shipping_Model_Resource_Costcenter getResource()
 * @method int getCostcenterId()
 * @method Transsmart_Shipping_Model_Costcenter setCostcenterId(int $value)
 * @method string getCode()
 * @method Transsmart_Shipping_Model_Costcenter setCode(string $value)
 * @method string getName()
 * @method Transsmart_Shipping_Model_Costcenter setName(string $value)
 * @method bool getIsDefault()
 * @method Transsmart_Shipping_Model_Costcenter setIsDefault(bool $value)
 *
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Costcenter extends Transsmart_Shipping_Model_Abstract
{
    /**
     * A list of API keys to be mapped to database columns.
     * @var array
     */
    protected $apiKeysMapping = array(
        'Id'             => 'costcenter_id',
        'Code'           => 'code',
        'Name'           => 'name',
        'IsDefault'      => 'is_default'
    );

    /**
     * Object initialization
     */
    protected function _construct()
    {
        $this->_init('transsmart_shipping/costcenter');
    }
}
