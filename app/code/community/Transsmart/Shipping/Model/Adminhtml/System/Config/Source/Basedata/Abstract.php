<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Basedata_Abstract
{
    /**
     * @param string $modelName
     * @param string $idField
     * @param string $nameField
     * @param string $defaultField
     * @return array
     */
    public function getAvailableOptions($modelName, $idField = 'id', $nameField = 'name', $defaultField = 'is_default')
    {
        $_helper = Mage::helper('adminhtml');

        $options = array();

        $default = $_helper->__('(empty)');

        /** @var Mage_Core_Model_Resource_Db_Collection_Abstract $collection */
        $collection = Mage::getModel($modelName)->getCollection();
        foreach ($collection as $_model) {
            $options[] = array(
                'value' => $_model->getData($idField),
                'label' => $_model->getData($nameField),
            );

            if ($_model->getData($defaultField)) {
                $default = $_model->getData($nameField);
            }
        }

        array_unshift($options, array(
            'value' => 0,
            'label' => $_helper->__('Transsmart default value: %s', $default)
        ));

        return $options;
    }
}
