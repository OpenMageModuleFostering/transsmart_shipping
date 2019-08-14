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

/**
 * Create table 'transsmart_shipping/carrier'
 */
$table = $connection->newTable($installer->getTable('transsmart_shipping/carrier'))
    ->addColumn('carrier_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'primary'   => true,
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Carrier ID')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Carrier Code')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Carrier Name')
    ->addColumn('location_select', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => true,
        'unsigned'  => true,
    ), 'Location Selector Available')
    ->setComment('Transsmart Carrier');
$connection->createTable($table);

/**
 * Create table 'transsmart_shipping/servicelevel_time'
 */
$table = $connection->newTable($installer->getTable('transsmart_shipping/servicelevel_time'))
    ->addColumn('servicelevel_time_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'primary'   => true,
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Servicelevel Time ID')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Servicelevel Time Code')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Servicelevel Time Name')
    ->setComment('Transsmart Servicelevel Time');
$connection->createTable($table);

/**
 * Create table 'transsmart_shipping/servicelevel_other'
 */
$table = $connection->newTable($installer->getTable('transsmart_shipping/servicelevel_other'))
    ->addColumn('servicelevel_other_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'primary'   => true,
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Servicelevel Other ID')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Servicelevel Other Code')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Servicelevel Other Name')
    ->setComment('Transsmart Servicelevel Other');
$connection->createTable($table);

/**
 * Create table 'transsmart_shipping/carrierprofile'
 */
$table = $connection->newTable($installer->getTable('transsmart_shipping/carrierprofile'))
    ->addColumn('carrierprofile_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'primary'   => true,
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Carrier Profile ID')
    ->addColumn('carrier_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Carrier ID')
    ->addColumn('servicelevel_time_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Servicelevel Time ID')
    ->addColumn('servicelevel_other_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Servicelevel Other ID')
    ->addForeignKey($installer->getFkName('transsmart_shipping/carrierprofile', 'carrier_id',
                                          'transsmart_shipping/carrier', 'carrier_id'),
                    'carrier_id', $installer->getTable('transsmart_shipping/carrier'),
                    'carrier_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('transsmart_shipping/servicelevel_time', 'servicelevel_time_id',
                                          'transsmart_shipping/servicelevel_time', 'servicelevel_time_id'),
                    'servicelevel_time_id', $installer->getTable('transsmart_shipping/servicelevel_time'),
                    'servicelevel_time_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('transsmart_shipping/servicelevel_other', 'servicelevel_other_id',
                                          'transsmart_shipping/servicelevel_other', 'servicelevel_other_id'),
                    'servicelevel_other_id', $installer->getTable('transsmart_shipping/servicelevel_other'),
                    'servicelevel_other_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Transsmart Carrier Profile');
$connection->createTable($table);

/**
 * Create table 'transsmart_shipping/shipmentlocation'
 */
$table = $connection->newTable($installer->getTable('transsmart_shipping/shipmentlocation'))
    ->addColumn('shipmentlocation_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'primary'   => true,
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Shipment Location ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Shipment Location Name')
    ->addColumn('street', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Shipment Location Street')
    ->addColumn('street_no', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Shipment Location Street No')
    ->addColumn('zip_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Shipment Location Zip Code')
    ->addColumn('city', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Shipment Location City')
    ->addColumn('country', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Shipment Location Country')
    ->addColumn('contact_person', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Shipment Location Contact Person')
    ->addColumn('phone_number', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Shipment Location Phone Number')
    ->addColumn('fax_number', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Shipment Location Fax Number')
    ->addColumn('email_address', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Shipment Location Email Address')
    ->addColumn('account_number', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Shipment Location Account Number')
    ->addColumn('customer_number', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Shipment Location Customer Number')
    ->addColumn('vat_number', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Shipment Location Vat Number')
    ->addColumn('is_default', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => true,
        'unsigned'  => true,
    ), 'Is Default')
    ->setComment('Transsmart Shipment Location');
$connection->createTable($table);

/**
 * Create table 'transsmart_shipping/emailtype'
 */
$table = $connection->newTable($installer->getTable('transsmart_shipping/emailtype'))
    ->addColumn('emailtype_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'primary'   => true,
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Email Type ID')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Email Type Code')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Email Type Name')
    ->addColumn('is_default', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => true,
        'unsigned'  => true,
    ), 'Is Default')
    ->setComment('Transsmart Email Type');
$connection->createTable($table);

/**
 * Create table 'transsmart_shipping/packagetype'
 */
$table = $connection->newTable($installer->getTable('transsmart_shipping/packagetype'))
    ->addColumn('packagetype_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'primary'   => true,
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Package Type ID')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Package Type Code')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Package Type Name')
    ->addColumn('length', Varien_Db_Ddl_Table::TYPE_DECIMAL, '19,6', array(
        'nullable'  => true,
        'unsigned'  => false,
    ), 'Package Length')
    ->addColumn('width', Varien_Db_Ddl_Table::TYPE_DECIMAL, '19,6', array(
        'nullable'  => true,
        'unsigned'  => false,
    ), 'Package Width')
    ->addColumn('height', Varien_Db_Ddl_Table::TYPE_DECIMAL, '19,6', array(
        'nullable'  => true,
        'unsigned'  => false,
    ), 'Package Height')
    ->addColumn('weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '19,6', array(
        'nullable'  => true,
        'unsigned'  => false,
    ), 'Package Weight')
    ->addColumn('is_default', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => true,
        'unsigned'  => true,
    ), 'Is Default')
    ->setComment('Transsmart Package Type');
$connection->createTable($table);

/**
 * Create table 'transsmart_shipping/incoterm'
 */
$table = $connection->newTable($installer->getTable('transsmart_shipping/incoterm'))
    ->addColumn('incoterm_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'primary'   => true,
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Incoterm ID')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Incoterm Code')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Incoterm Name')
    ->addColumn('is_default', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => true,
        'unsigned'  => true,
    ), 'Is Default')
    ->setComment('Transsmart Incoterm');
$connection->createTable($table);

/**
 * Create table 'transsmart_shipping/costcenter'
 */
$table = $connection->newTable($installer->getTable('transsmart_shipping/costcenter'))
    ->addColumn('costcenter_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'primary'   => true,
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Cost Center ID')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Cost Center Code')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Cost Center Name')
    ->addColumn('is_default', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => true,
        'unsigned'  => true,
    ), 'Is Default')
    ->setComment('Transsmart Cost Center');
$connection->createTable($table);

/**
 * Create table 'transsmart_shipping/sync'
 */
$table = $connection->newTable($installer->getTable('transsmart_shipping/sync'))
    ->addColumn('sync_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'primary'   => true,
        'unsigned'  => true,
        'auto_increment' => true,
        'nullable'  => false
    ), 'Sync ID')
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Sync Type')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Sync Status')
    ->addColumn('message', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Sync Message')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(), 'Created at')
    ->setComment('Transsmart Sync');
$connection->createTable($table);

// add carrierprofile to shipment table
$connection->addColumn(
    $this->getTable('sales/shipment'),
    'transsmart_carrierprofile_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Transsmart Carrier Profile Id'
    )
);
$connection->addForeignKey(
    $installer->getFkName(
        'sales/shipment',
        'transsmart_carrierprofile_id',
        'transsmart_shipping/carrierprofile',
        'carrierprofile_id'
    ),
    $this->getTable('sales/shipment'),
    'transsmart_carrierprofile_id',
    $installer->getTable('transsmart_shipping/carrierprofile'),
    'carrierprofile_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

// add shipmentlocation to shipment table
$connection->addColumn(
    $this->getTable('sales/shipment'),
    'transsmart_shipmentlocation_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Transsmart Shipment Location Id'
    )
);
$connection->addForeignKey(
    $installer->getFkName(
        'sales/shipment',
        'transsmart_shipmentlocation_id',
        'transsmart_shipping/shipmentlocation',
        'shipmentlocation_id'
    ),
    $this->getTable('sales/shipment'),
    'transsmart_shipmentlocation_id',
    $installer->getTable('transsmart_shipping/shipmentlocation'),
    'shipmentlocation_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

// add emailtype to shipment table
$connection->addColumn(
    $this->getTable('sales/shipment'),
    'transsmart_emailtype_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Transsmart Email Type Id'
    )
);
$connection->addForeignKey(
    $installer->getFkName(
        'sales/shipment',
        'transsmart_emailtype_id',
        'transsmart_shipping/emailtype',
        'emailtype_id'
    ),
    $this->getTable('sales/shipment'),
    'transsmart_emailtype_id',
    $installer->getTable('transsmart_shipping/emailtype'),
    'emailtype_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

// add incoterm to shipment table
$connection->addColumn(
    $this->getTable('sales/shipment'),
    'transsmart_incoterm_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Transsmart Incoterm Id'
    )
);
$connection->addForeignKey(
    $installer->getFkName(
        'sales/shipment',
        'transsmart_incoterm_id',
        'transsmart_shipping/incoterm',
        'incoterm_id'
    ),
    $this->getTable('sales/shipment'),
    'transsmart_incoterm_id',
    $installer->getTable('transsmart_shipping/incoterm'),
    'incoterm_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

// add costcenter to shipment table
$connection->addColumn(
    $this->getTable('sales/shipment'),
    'transsmart_costcenter_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Transsmart Cost Center Id'
    )
);
$connection->addForeignKey(
    $installer->getFkName(
        'sales/shipment',
        'transsmart_costcenter_id',
        'transsmart_shipping/costcenter',
        'costcenter_id'
    ),
    $this->getTable('sales/shipment'),
    'transsmart_costcenter_id',
    $installer->getTable('transsmart_shipping/costcenter'),
    'costcenter_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

// add packages to shipment table
$connection->addColumn(
    $this->getTable('sales/shipment'),
    'transsmart_packages',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => '20000',
        'comment'   => 'Transsmart Package Data',
        'after'     => 'transsmart_costcenter_id'
    )
);

// add flags to shipment table
$connection->addColumn(
    $this->getTable('sales/shipment'),
    'transsmart_flags',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'comment'   => 'Transsmart Flags',
        'after'     => 'transsmart_packages'
    )
);

// add document ID to shipment table
$connection->addColumn(
    $this->getTable('sales/shipment'),
    'transsmart_document_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Transsmart Document Id'
    )
);
$connection->addIndex(
    $this->getTable('sales/shipment'),
    $installer->getIdxName(
        $this->getTable('sales/shipment'),
        'transsmart_document_id',
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    'transsmart_document_id',
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

// add status to shipment table
$connection->addColumn(
    $this->getTable('sales/shipment'),
    'transsmart_status',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'nullable'  => true,
        'comment'   => 'Transsmart Status'
    )
);

// add document ID to shipment grid table
$connection->addColumn(
    $this->getTable('sales/shipment_grid'),
    'transsmart_document_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Transsmart Document Id'
    )
);

// add status to shipment grid table
$connection->addColumn(
    $this->getTable('sales/shipment_grid'),
    'transsmart_status',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'nullable'  => true,
        'comment'   => 'Transsmart Status'
    )
);

// add status to order grid table
$connection->addColumn(
    $this->getTable('sales/order_grid'),
    'transsmart_status',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Transsmart Status'
    )
);

// add tracking url to shipment table
$connection->addColumn(
    $this->getTable('sales/shipment'),
    'transsmart_tracking_url',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'nullable'  => true,
        'comment'   => 'Transsmart Tracking URL'
    )
);

// add tracking url to shipment grid table
$connection->addColumn(
    $this->getTable('sales/shipment_grid'),
    'transsmart_tracking_url',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'nullable'  => true,
        'comment'   => 'Transsmart Tracking URL'
    )
);

// add servicepoint_id to quote address table
$connection->addColumn(
    $this->getTable('sales/quote_address'),
    'transsmart_servicepoint_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'nullable'  => true,
        'comment'   => 'Transsmart Servicepoint Id'
    )
);

// add servicepoint_id to order address table
$connection->addColumn(
    $this->getTable('sales/order_address'),
    'transsmart_servicepoint_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'nullable'  => true,
        'comment'   => 'Transsmart Servicepoint Id'
    )
);

$installer->endSetup();
