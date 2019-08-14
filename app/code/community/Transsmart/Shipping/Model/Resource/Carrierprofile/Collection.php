<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Resource_Carrierprofile_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_hasJoinedCarrier = false;
    protected $_hasJoinedServicelevelTime = false;
    protected $_hasJoinedServicelevelOther = false;

    protected function _construct()
    {
        $this->_init('transsmart_shipping/carrierprofile');
    }

    public function toOptionHash()
    {
        $this->joinCarrier()
            ->joinServicelevelTime()
            ->joinServicelevelOther();

        $res = array();
        foreach ($this as $item) {
            $res[$item->getData('carrierprofile_id')] = $item->getName();
        }
        return $res;
    }

    /**
     * Join the carrier table.
     *
     * @return $this
     */
    public function joinCarrier()
    {
        if ($this->_hasJoinedCarrier) {
            return $this;
        }
        $this->_hasJoinedCarrier = true;

        $this->getSelect()
            ->join(
                array(
                    'carrier' => $this->getTable('transsmart_shipping/carrier')
                ),
                'carrier.carrier_id = main_table.carrier_id',
                array(
                    'carrier_code'            => 'code',
                    'carrier_name'            => 'name',
                    'carrier_location_select' => 'location_select',
                )
            );
        return $this;
    }

    /**
     * Join the servicelevel_time table.
     *
     * @return $this
     */
    public function joinServicelevelTime()
    {
        if ($this->_hasJoinedServicelevelTime) {
            return $this;
        }
        $this->_hasJoinedServicelevelTime = true;

        $this->getSelect()
            ->join(
                array(
                    'servicelevel_time' => $this->getTable('transsmart_shipping/servicelevel_time')
                ),
                'servicelevel_time.servicelevel_time_id = main_table.servicelevel_time_id',
                array(
                    'servicelevel_time_code' => 'code',
                    'servicelevel_time_name' => 'name',
                )
            );
        return $this;
    }

    /**
     * Join the servicelevel_other table.
     *
     * @return $this
     */
    public function joinServicelevelOther()
    {
        if ($this->_hasJoinedServicelevelOther) {
            return $this;
        }
        $this->_hasJoinedServicelevelOther = true;

        $this->getSelect()
            ->join(
                array(
                    'servicelevel_other' => $this->getTable('transsmart_shipping/servicelevel_other')
                ),
                'servicelevel_other.servicelevel_other_id = main_table.servicelevel_other_id',
                array(
                    'servicelevel_other_code' => 'code',
                    'servicelevel_other_name' => 'name',
                )
            );
        return $this;
    }
}
