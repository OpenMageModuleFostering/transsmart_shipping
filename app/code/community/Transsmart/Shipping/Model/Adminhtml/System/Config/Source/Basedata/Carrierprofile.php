<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Basedata_Carrierprofile
    extends Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Basedata_Abstract
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => 0,
                'label' => Mage::helper('adminhtml')->__('(empty)')
            )
        );

        /** @var Mage_Core_Model_Resource_Db_Collection_Abstract $collection */
        $collection = Mage::getModel('transsmart_shipping/carrierprofile')->getCollection()
            ->joinCarrier()
            ->joinServicelevelTime()
            ->joinServicelevelOther();
        foreach ($collection as $_model) {
            $options[] = array(
                'value' => $_model->getData('carrierprofile_id'),
                'label' => $_model->getName(),
            );
        }

        return $options;
    }
}
