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

// add carrierprofile id to tablerate table
$connection->addColumn(
    $this->getTable('shipping/tablerate'),
    'transsmart_carrierprofile_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Transsmart Carrierprofile Id'
    )
);
$connection->addForeignKey(
    $installer->getFkName(
        'shipping/tablerate',
        'transsmart_carrierprofile_id',
        'transsmart_shipping/carrier',
        'carrierprofile_id'
    ),
    $this->getTable('shipping/tablerate'),
    'transsmart_carrierprofile_id',
    $installer->getTable('transsmart_shipping/carrierprofile'),
    'carrierprofile_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

// add carrierprofile id to quote shipping rate table
$connection->addColumn(
    $this->getTable('sales/quote_address_shipping_rate'),
    'transsmart_carrierprofile_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Transsmart Carrierprofile Id'
    )
);
$connection->addForeignKey(
    $installer->getFkName(
        'sales/quote_address_shipping_rate',
        'transsmart_carrierprofile_id',
        'transsmart_shipping/carrier',
        'carrierprofile_id'
    ),
    $this->getTable('sales/quote_address_shipping_rate'),
    'transsmart_carrierprofile_id',
    $installer->getTable('transsmart_shipping/carrierprofile'),
    'carrierprofile_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

// add carrierprofile id to quote address table
$connection->addColumn(
    $this->getTable('sales/quote_address'),
    'transsmart_carrierprofile_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Transsmart Carrierprofile Id'
    )
);
$connection->addForeignKey(
    $installer->getFkName(
        'sales/quote_address',
        'transsmart_carrierprofile_id',
        'transsmart_shipping/carrier',
        'carrierprofile_id'
    ),
    $this->getTable('sales/quote_address'),
    'transsmart_carrierprofile_id',
    $installer->getTable('transsmart_shipping/carrierprofile'),
    'carrierprofile_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

// add carrierprofile id to order address table
$connection->addColumn(
    $this->getTable('sales/order_address'),
    'transsmart_carrierprofile_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Transsmart Carrierprofile Id'
    )
);
$connection->addForeignKey(
    $installer->getFkName(
        'sales/order_address',
        'transsmart_carrierprofile_id',
        'transsmart_shipping/carrier',
        'carrierprofile_id'
    ),
    $this->getTable('sales/order_address'),
    'transsmart_carrierprofile_id',
    $installer->getTable('transsmart_shipping/carrierprofile'),
    'carrierprofile_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

$installer->endSetup();
