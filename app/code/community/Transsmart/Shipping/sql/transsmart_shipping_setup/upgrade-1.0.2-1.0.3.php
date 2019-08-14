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

// add error to shipment table
$connection->addColumn(
    $this->getTable('sales/shipment'),
    'transsmart_shipment_error',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 1024,
        'nullable'  => true,
        'comment'   => 'Transsmart Shipment Error',
        'after'     => 'transsmart_status'
    )
);

$installer->endSetup();
