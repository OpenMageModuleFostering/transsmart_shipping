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

// add final carrier to shipment table
$connection->addColumn(
    $this->getTable('sales/shipment'),
    'transsmart_final_carrier_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Transsmart Final Carrier Id'
    )
);
$connection->addForeignKey(
    $installer->getFkName(
        'sales/shipment',
        'transsmart_final_carrier_id',
        'transsmart_shipping/carrier',
        'carrier_id'
    ),
    $this->getTable('sales/shipment'),
    'transsmart_final_carrier_id',
    $installer->getTable('transsmart_shipping/carrier'),
    'carrier_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

// add final servicelevel time to shipment table
$connection->addColumn(
    $this->getTable('sales/shipment'),
    'transsmart_final_servicelevel_time_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Transsmart Final Servicelevel Time Id'
    )
);
$connection->addForeignKey(
    $installer->getFkName(
        'sales/shipment',
        'transsmart_final_servicelevel_time_id',
        'transsmart_shipping/servicelevel_time',
        'servicelevel_time_id'
    ),
    $this->getTable('sales/shipment'),
    'transsmart_final_servicelevel_time_id',
    $installer->getTable('transsmart_shipping/servicelevel_time'),
    'servicelevel_time_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

// add final servicelevel other to shipment table
$connection->addColumn(
    $this->getTable('sales/shipment'),
    'transsmart_final_servicelevel_other_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Transsmart Final Servicelevel Other Id'
    )
);
$connection->addForeignKey(
    $installer->getFkName(
        'sales/shipment',
        'transsmart_final_servicelevel_other_id',
        'transsmart_shipping/servicelevel_other',
        'servicelevel_other_id'
    ),
    $this->getTable('sales/shipment'),
    'transsmart_final_servicelevel_other_id',
    $installer->getTable('transsmart_shipping/servicelevel_other'),
    'servicelevel_other_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

$installer->endSetup();
