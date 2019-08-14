<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Resource_Carrierprofile extends Mage_Core_Model_Resource_Db_Abstract
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
        $this->_init('transsmart_shipping/carrierprofile', 'carrierprofile_id');
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param Mage_Core_Model_Abstract $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        // join the carrier table
        $select->join(
            array(
                'carrier' => $this->getTable('transsmart_shipping/carrier')
            ),
            'carrier.carrier_id = ' . $this->getMainTable() . '.carrier_id',
            array(
                'carrier_code'            => 'code',
                'carrier_name'            => 'name',
                'carrier_location_select' => 'location_select',
            )
        );

        // join the servicelevel_time table
        $select->join(
            array(
                'servicelevel_time' => $this->getTable('transsmart_shipping/servicelevel_time')
            ),
            'servicelevel_time.servicelevel_time_id = ' . $this->getMainTable() . '.servicelevel_time_id',
            array(
                'servicelevel_time_code' => 'code',
                'servicelevel_time_name' => 'name',
            )
        );

        // join the servicelevel_other table
        $select->join(
            array(
                'servicelevel_other' => $this->getTable('transsmart_shipping/servicelevel_other')
            ),
            'servicelevel_other.servicelevel_other_id = ' . $this->getMainTable() . '.servicelevel_other_id',
            array(
                'servicelevel_other_code' => 'code',
                'servicelevel_other_name' => 'name',
            )
        );

        return $select;
    }
}
