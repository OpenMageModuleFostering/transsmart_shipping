<?php

/**
 * Transsmart Shipment Location Model
 *
 * @method Transsmart_Shipping_Model_Resource_Shipmentlocation _getResource()
 * @method Transsmart_Shipping_Model_Resource_Shipmentlocation getResource()
 * @method int getShipmentlocationId()
 * @method Transsmart_Shipping_Model_Shipmentlocation setShipmentlocationId(int $value)
 * @method string getName()
 * @method Transsmart_Shipping_Model_Shipmentlocation setName(string $value)
 * @method string getStreet()
 * @method Transsmart_Shipping_Model_Shipmentlocation setStreet(string $value)
 * @method string getStreetNo()
 * @method Transsmart_Shipping_Model_Shipmentlocation setStreetNo(string $value)
 * @method string getZipCode()
 * @method Transsmart_Shipping_Model_Shipmentlocation setZipCode(string $value)
 * @method string getCity()
 * @method Transsmart_Shipping_Model_Shipmentlocation setCity(string $value)
 * @method string getCountry()
 * @method Transsmart_Shipping_Model_Shipmentlocation setCountry(string $value)
 * @method string getContactPerson()
 * @method Transsmart_Shipping_Model_Shipmentlocation setContactPerson(string $value)
 * @method string getPhoneNumber()
 * @method Transsmart_Shipping_Model_Shipmentlocation setPhoneNumber(string $value)
 * @method string getFaxNumber()
 * @method Transsmart_Shipping_Model_Shipmentlocation setFaxNumber(string $value)
 * @method string getEmailAddress()
 * @method Transsmart_Shipping_Model_Shipmentlocation setEmailAddress(string $value)
 * @method string getAccountNumber()
 * @method Transsmart_Shipping_Model_Shipmentlocation setAccountNumber(string $value)
 * @method string getCustomerNumber()
 * @method Transsmart_Shipping_Model_Shipmentlocation setCustomerNumber(string $value)
 * @method string getVatNumber()
 * @method Transsmart_Shipping_Model_Shipmentlocation setVatNumber(string $value)
 * @method bool getIsDefault()
 * @method Transsmart_Shipping_Model_Shipmentlocation setIsDefault(bool $value)
 *
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Shipmentlocation extends Transsmart_Shipping_Model_Abstract
{
    /**
     * A list of API keys to be mapped to database columns.
     * @var array
     */
    protected $apiKeysMapping = array(
        'Id'             => 'shipmentlocation_id',
        'Name'           => 'name',
        'Street'         => 'street',
        'StreetNo'       => 'street_no',
        'ZipCode'        => 'zip_code',
        'City'           => 'city',
        'Country'        => 'country',
        'ContactPerson'  => 'contact_person',
        'PhoneNumber'    => 'phone_number',
        'FaxNumber'      => 'fax_number',
        'EmailAddress'   => 'email_address',
        'AccountNumber'  => 'account_number',
        'CustomerNumber' => 'customer_number',
        'VatNumber'      => 'vat_number',
        'IsDefault'      => 'is_default'
        // TODO: Unmapped: CostcenterId
    );

    /**
     * Object initialization
     */
    protected function _construct()
    {
        $this->_init('transsmart_shipping/shipmentlocation');
    }
}
