<?php

/**
 * Transsmart Package Type Model
 *
 * @method Transsmart_Shipping_Model_Resource_Packagetype _getResource()
 * @method Transsmart_Shipping_Model_Resource_Packagetype getResource()
 * @method int getPackagetypeId()
 * @method Transsmart_Shipping_Model_Packagetype setPackagetypeId(int $value)
 * @method string getCode()
 * @method Transsmart_Shipping_Model_Packagetype setCode(string $value)
 * @method string getName()
 * @method Transsmart_Shipping_Model_Packagetype setName(string $value)
 * @method float getLength()
 * @method Transsmart_Shipping_Model_Packagetype setLength(float $value)
 * @method float getWidth()
 * @method Transsmart_Shipping_Model_Packagetype setWidth(float $value)
 * @method float getHeight()
 * @method Transsmart_Shipping_Model_Packagetype setHeight(float $value)
 * @method float getWeight()
 * @method Transsmart_Shipping_Model_Packagetype setWeight(float $value)
 * @method bool getIsDefault()
 * @method Transsmart_Shipping_Model_Packagetype setIsDefault(bool $value)
 *
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Packagetype extends Transsmart_Shipping_Model_Abstract
{
    /**
     * A list of API keys to be mapped to database columns.
     * @var array
     */
    protected $apiKeysMapping = array(
        'Id'             => 'packagetype_id',
        'Type'           => 'code',
        'Name'           => 'name',
        'Length'         => 'length',
        'Width'          => 'width',
        'Height'         => 'height',
        'Weight'         => 'weight',
        'IsDefault'      => 'is_default'
    );

    /**
     * Object initialization
     */
    protected function _construct()
    {
        $this->_init('transsmart_shipping/packagetype');
    }
}
