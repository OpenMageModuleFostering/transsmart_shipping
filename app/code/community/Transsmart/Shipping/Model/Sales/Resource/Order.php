<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Sales_Resource_Order extends Mage_Sales_Model_Resource_Order
{
    /**
     * Join virtual grid columns to select. These will be copied into the sales_flat_order_grid table.
     *
     * @param string $mainTableAlias
     * @param Zend_Db_Select $select
     * @param array $columnsToSelect
     * @return Mage_Sales_Model_Resource_Order_Abstract
     */
    public function joinVirtualGridColumnsToSelect($mainTableAlias, Zend_Db_Select $select, &$columnsToSelect)
    {
        /** @var Magento_Db_Adapter_Pdo_Mysql $adapter */
        $adapter = $this->getReadConnection();

        // join Transsmart shipments and group by order ID, so we can use aggregate functions
        $select
            ->joinLeft(
                array('transsmart_shipments' => $this->getTable('sales/shipment')),
                "transsmart_shipments.order_id = $mainTableAlias.entity_id" .
                " AND transsmart_shipments.transsmart_status IS NOT NULL",
                array()
            )
            ->joinLeft(
                array('transsmart_shipments_error' => $this->getTable('sales/shipment')),
                "transsmart_shipments_error.order_id = $mainTableAlias.entity_id" .
                " AND transsmart_shipments_error.transsmart_status = 'ERR'",
                array()
            )
            ->group("$mainTableAlias.entity_id");

        // define the logic that determines the Transsmart order status
        $casesResults = array();

        $casesResults['main_table.shipping_method NOT LIKE \'transsmart%\_carrierprofile\_%\''] =
            Transsmart_Shipping_Helper_Data::TRANSSMART_ORDER_STATUS_NOT_APPLICABLE;

        $casesResults['SUM(transsmart_shipments_error.total_qty) > 0'] =
            Transsmart_Shipping_Helper_Data::TRANSSMART_ORDER_STATUS_ERROR;

        $casesResults['SUM(transsmart_shipments.total_qty) = main_table.total_qty_ordered'] =
            Transsmart_Shipping_Helper_Data::TRANSSMART_ORDER_STATUS_EXPORTED;

        $casesResults['SUM(transsmart_shipments.total_qty) > 0'] =
            Transsmart_Shipping_Helper_Data::TRANSSMART_ORDER_STATUS_PARTIALLY_EXPORTED;

        $defaultValue =
            Transsmart_Shipping_Helper_Data::TRANSSMART_ORDER_STATUS_PENDING;

        // add the transsmart_status column
        $select
            ->columns(
                array('transsmart_status' => new Zend_Db_Expr($adapter->getCaseSql('', $casesResults, $defaultValue)))
            );
        $columnsToSelect[] = 'transsmart_status';

        return parent::joinVirtualGridColumnsToSelect($mainTableAlias, $select, $columnsToSelect);
    }
}
