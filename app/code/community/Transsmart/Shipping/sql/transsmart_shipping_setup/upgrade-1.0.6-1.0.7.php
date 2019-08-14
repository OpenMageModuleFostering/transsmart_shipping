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

// remove unique index from tablerate table
$connection->dropIndex(
    $installer->getTable('shipping/tablerate'),
    $installer->getIdxName(
        'shipping/tablerate',
        array('website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'condition_name', 'condition_value'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    )
);

// add new unique index to tablerate table
$connection->addIndex(
    $installer->getTable('shipping/tablerate'),
    $installer->getIdxName(
        'shipping/tablerate',
        array('website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'condition_name', 'condition_value', 'transsmart_carrierprofile_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'condition_name', 'condition_value', 'transsmart_carrierprofile_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->endSetup();
