<?php
/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

// add enable_location_select to carrierprofile table
$connection->addColumn(
    $this->getTable('transsmart_shipping/carrierprofile'),
    'enable_location_select',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable'  => true,
        'unsigned'  => true,
        'comment'   => 'Location Selector Enabled'
    )
);

$installer->endSetup();
