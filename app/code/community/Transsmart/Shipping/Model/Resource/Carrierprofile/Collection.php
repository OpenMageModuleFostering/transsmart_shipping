<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Resource_Carrierprofile_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_joinCarrier = false;
    protected $_joinServicelevelTime = false;
    protected $_joinServicelevelOther = false;

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
     * Init collection select
     *
     * @return $this
     */
    public function _initSelect()
    {
        parent::_initSelect();

        if ($this->_joinCarrier) {
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
        }

        if ($this->_joinServicelevelTime) {
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
        }

        if ($this->_joinServicelevelOther) {
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
        }

        return $this;
    }

    /**
     * Join the carrier table.
     *
     * @return $this
     */
    public function joinCarrier()
    {
        if (!$this->_joinCarrier) {
            $this->_joinCarrier = true;
            $this->_reset();
        }
        return $this;
    }

    /**
     * Join the servicelevel_time table.
     *
     * @return $this
     */
    public function joinServicelevelTime()
    {
        if (!$this->_joinServicelevelTime) {
            $this->_joinServicelevelTime = true;
            $this->_reset();
        }
        return $this;
    }

    /**
     * Join the servicelevel_other table.
     *
     * @return $this
     */
    public function joinServicelevelOther()
    {
        if (!$this->_joinServicelevelOther) {
            $this->_joinServicelevelOther = true;
            $this->_reset();
        }
        return $this;
    }
}
