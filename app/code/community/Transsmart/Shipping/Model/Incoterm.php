<?php

/**
 * Transsmart Incoterm Model
 *
 * @method Transsmart_Shipping_Model_Resource_Incoterm _getResource()
 * @method Transsmart_Shipping_Model_Resource_Incoterm getResource()
 * @method int getIncotermId()
 * @method Transsmart_Shipping_Model_Incoterm setIncotermId(int $value)
 * @method string getCode()
 * @method Transsmart_Shipping_Model_Incoterm setCode(string $value)
 * @method string getName()
 * @method Transsmart_Shipping_Model_Incoterm setName(string $value)
 * @method bool getIsDefault()
 * @method Transsmart_Shipping_Model_Incoterm setIsDefault(bool $value)
 *
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Incoterm extends Transsmart_Shipping_Model_Abstract
{
    /**
     * A list of API keys to be mapped to database columns.
     * @var array
     */
    protected $apiKeysMapping = array(
        'Id'        => 'incoterm_id',
        'Code'      => 'code',
        'Name'      => 'name',
        'IsDefault' => 'is_default'
    );

    /**
     * Object initialization
     */
    protected function _construct()
    {
        $this->_init('transsmart_shipping/incoterm');
    }
}
