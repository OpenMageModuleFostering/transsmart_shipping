<?php

/**
 * Transsmart Email Type Model
 *
 * @method Transsmart_Shipping_Model_Resource_Emailtype _getResource()
 * @method Transsmart_Shipping_Model_Resource_Emailtype getResource()
 * @method int getEmailtypeId()
 * @method Transsmart_Shipping_Model_Emailtype setEmailtypeId(int $value)
 * @method string getCode()
 * @method Transsmart_Shipping_Model_Emailtype setCode(string $value)
 * @method string getName()
 * @method Transsmart_Shipping_Model_Emailtype setName(string $value)
 * @method bool getIsDefault()
 * @method Transsmart_Shipping_Model_Emailtype setIsDefault(bool $value)
 *
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Emailtype extends Transsmart_Shipping_Model_Abstract
{
    /**
     * A list of API keys to be mapped to database columns.
     * @var array
     */
    protected $apiKeysMapping = array(
        'Id'             => 'emailtype_id',
        'Code'           => 'code',
        'Name'           => 'name',
        'IsDefault'      => 'is_default'
    );

    /**
     * Object initialization
     */
    protected function _construct()
    {
        $this->_init('transsmart_shipping/emailtype');
    }
}
