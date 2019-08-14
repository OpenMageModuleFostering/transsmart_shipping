<?php

/**
 * Transsmart Sync Model
 *
 * @todo Cleanup old records from sync table
 *
 * @method Transsmart_Shipping_Model_Resource_Sync _getResource()
 * @method Transsmart_Shipping_Model_Resource_Sync getResource()
 * @method Transsmart_Shipping_Model_Resource_Sync_Collection getCollection()
 * @method int getSyncId()
 * @method Transsmart_Shipping_Model_Sync setSyncId(int $value)
 * @method string getType()
 * @method Transsmart_Shipping_Model_Sync setType(string $value)
 * @method string getStatus()
 * @method Transsmart_Shipping_Model_Sync setStatus(string $value)
 * @method string getMessage()
 * @method Transsmart_Shipping_Model_Sync setMessage(string $value)
 * @method string getCreatedAt()
 * @method Transsmart_Shipping_Model_Sync setCreatedAt(string $value)
 *
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Sync extends Mage_Core_Model_Abstract
{
    /**
     * Represents the initial synchronization to retrieve base data.
     */
    const TYPE_BASE       = 'base';
    const TYPE_SHIPMENTS  = 'shipments';

    const STATUS_RUNNING  = 'running';
    const STATUS_FAILED   = 'failed';
    const STATUS_FINISHED = 'finished';

    /**
     * Object initialization
     */
    protected function _construct()
    {
        $this->_init('transsmart_shipping/sync');
    }

    /**
     * Synchronizes the basic data that is necessary to show shipping details.
     * The data is retrieved through the Transsmart API and then stored in the database.
     *
     * The following information is synchronized:
     * - Carriers
     * - Carrier profiles
     * - Service level time
     * - Service level other
     * - E-mail types
     * - Package types
     * - Incoterms
     * - Cost centers
     */
    public function syncBaseData()
    {
        $this->clearInstance();
        $this->setType(self::TYPE_BASE)
            ->setStatus(self::STATUS_RUNNING)
            ->setCreatedAt(Mage::getModel('core/date')->gmtDate())
            ->save();

        $this->syncCarriers()
            ->syncServiceLevelTimes()
            ->syncServiceLevelOther()
            ->syncCarrierProfiles()
            ->syncEmailTypes()
            ->syncPackageTypes()
            ->syncIncoterms()
            ->syncCostCenters()
            ->syncShipmentLocations();

        $this->setStatus(self::STATUS_FINISHED)
            ->save();
    }

    /**
     * Retrieve carriers from the API and insert into the database.
     * Carriers that are not present in the API will be deleted.
     * @return Transsmart_Shipping_Model_Sync
     */
    public function syncCarriers()
    {
        $apiClient = $this->getApiClient();

        // Keep track of all carrier ID's that come from the API
        $apiItemIds = array();

        // Retrieve the items from the API
        $apiItems = $apiClient->getCarrier();

        if (is_array($apiItems)) {
            foreach ($apiItems as $apiItem) {
                /** @var Transsmart_Shipping_Model_Carrier $model */
                $model = Mage::getModel('transsmart_shipping/carrier');
                $mappedData = $model->mapApiKeysToDbColumns($apiItem);

                // LocationSelect is returned as Y/N from the API, Map that to 1 and 0
                if (isset($mappedData['location_select'])) {
                    $mappedData['location_select'] = $mappedData['location_select'] == 'Y' ? 1 : 0;
                }

                $model->setData($mappedData);
                $model->save();

                $apiItemIds []= $model->getId();
            }
        }

        // Remove all items that we didn't receive from the API
        $this->deleteItemsNotInApi(
            Mage::getModel('transsmart_shipping/carrier'),
            $apiItemIds
        );

        return $this;
    }

    /**
     * Retrieve carrier profiles from the API and insert into the database.
     * Carrier profiles that are not present in the API will be deleted.
     * @return Transsmart_Shipping_Model_Sync
     */
    public function syncCarrierProfiles()
    {
        $apiClient = $this->getApiClient();

        // Keep track of all carrier ID's that come from the API
        $apiItemIds = array();

        // Retrieve the items from the API
        $apiItems = $apiClient->getCarrierProfile();

        if (is_array($apiItems)) {
            foreach ($apiItems as $apiItem) {
                /** @var Transsmart_Shipping_Model_Carrierprofile $model */
                $model = Mage::getModel('transsmart_shipping/carrierprofile');
                $mappedData = $model->mapApiKeysToDbColumns($apiItem);

                // EnableLocationSelect is returned as Y/N from the API, Map that to 1 and 0
                if (isset($mappedData['enable_location_select'])) {
                    $mappedData['enable_location_select'] = $mappedData['enable_location_select'] == 'Y' ? 1 : 0;
                }

                $model->setData($mappedData);
                $model->save();

                $apiItemIds []= $model->getId();
            }
        }

        // Remove all items that we didn't receive from the API
        $this->deleteItemsNotInApi(
            Mage::getModel('transsmart_shipping/carrierprofile'),
            $apiItemIds
        );

        return $this;
    }

    /**
     * Retrieve service level times from the API and insert into the database.
     * Service level times that are not present in the API will be deleted.
     * @return Transsmart_Shipping_Model_Sync
     */
    public function syncServiceLevelTimes()
    {
        $apiClient = $this->getApiClient();

        // Keep track of all ID's that come from the API
        $apiItemIds = array();

        // Retrieve the items from the API
        $apiItems = $apiClient->getServiceLevelTime();

        if (is_array($apiItems)) {
            foreach ($apiItems as $apiItem) {
                /** @var Transsmart_Shipping_Model_Servicelevel_Time $model */
                $model = Mage::getModel('transsmart_shipping/servicelevel_time');
                $mappedData = $model->mapApiKeysToDbColumns($apiItem);

                // Skip this item if it has been marked as deleted, it will be deleted later
                if (isset($mappedData['deleted']) && $mappedData['deleted']) {
                    continue;
                }

                $model->setData($mappedData);
                $model->save();

                $apiItemIds []= $model->getId();
            }
        }

        // Remove all items that we didn't receive from the API
        $this->deleteItemsNotInApi(
            Mage::getModel('transsmart_shipping/servicelevel_time'),
            $apiItemIds
        );

        return $this;
    }

    /**
     * Retrieve service level other from the API and insert into the database.
     * Service level other that are not present in the API will be deleted.
     * @return Transsmart_Shipping_Model_Sync
     */
    public function syncServiceLevelOther()
    {
        $apiClient = $this->getApiClient();

        // Keep track of all ID's that come from the API
        $apiItemIds = array();

        // Retrieve the items from the API
        $apiItems = $apiClient->getServiceLevelOther();

        if (is_array($apiItems)) {
            foreach ($apiItems as $apiItem) {
                /** @var Transsmart_Shipping_Model_Servicelevel_Other $model */
                $model = Mage::getModel('transsmart_shipping/servicelevel_other');
                $mappedData = $model->mapApiKeysToDbColumns($apiItem);

                // Skip this item if it has been marked as deleted, it will be deleted later
                if (isset($mappedData['deleted']) && $mappedData['deleted']) {
                    continue;
                }

                $model->setData($mappedData);
                $model->save();

                $apiItemIds []= $model->getId();
            }
        }

        // Remove all items that we didn't receive from the API
        $this->deleteItemsNotInApi(
            Mage::getModel('transsmart_shipping/servicelevel_other'),
            $apiItemIds
        );

        return $this;
    }

    /**
     * Retrieve email types from the API and insert into the database.
     * Email types that are not present in the API will be deleted.
     * @return Transsmart_Shipping_Model_Sync
     */
    public function syncEmailTypes()
    {
        $apiClient = $this->getApiClient();

        // Keep track of all ID's that come from the API
        $apiItemIds = array();

        // Retrieve the items from the API
        $apiItems = $apiClient->getEmailType();

        if (is_array($apiItems)) {
            foreach ($apiItems as $apiItem) {
                /** @var Transsmart_Shipping_Model_Emailtype $model */
                $model = Mage::getModel('transsmart_shipping/emailtype');
                $mappedData = $model->mapApiKeysToDbColumns($apiItem);

                // is_default is returned as Y/N from the API, Map that to 1 and 0
                if (isset($mappedData['is_default'])) {
                    $mappedData['is_default'] = $mappedData['is_default'] == 'Y' ? 1 : 0;
                }

                $model->setData($mappedData);
                $model->save();

                $apiItemIds []= $model->getId();
            }
        }

        // Remove all items that we didn't receive from the API
        $this->deleteItemsNotInApi(
            Mage::getModel('transsmart_shipping/emailtype'),
            $apiItemIds
        );

        return $this;
    }

    /**
     * Retrieve package types from the API and insert into the database.
     * Package types that are not present in the API will be deleted.
     * @return Transsmart_Shipping_Model_Sync
     */
    public function syncPackageTypes()
    {
        $apiClient = $this->getApiClient();

        // Keep track of all ID's that come from the API
        $apiItemIds = array();

        // Retrieve the items from the API
        $apiItems = $apiClient->getPackage();

        if (is_array($apiItems)) {
            foreach ($apiItems as $apiItem) {
                /** @var Transsmart_Shipping_Model_Packagetype $model */
                $model = Mage::getModel('transsmart_shipping/packagetype');
                $mappedData = $model->mapApiKeysToDbColumns($apiItem);

                // is_default is returned as Y/N from the API, Map that to 1 and 0
                if (isset($mappedData['is_default'])) {
                    $mappedData['is_default'] = $mappedData['is_default'] == 'Y' ? 1 : 0;
                }

                $model->setData($mappedData);
                $model->save();

                $apiItemIds []= $model->getId();
            }
        }

        // Remove all items that we didn't receive from the API
        $this->deleteItemsNotInApi(
            Mage::getModel('transsmart_shipping/packagetype'),
            $apiItemIds
        );

        return $this;
    }

    /**
     * Retrieve incoterms from the API and insert into the database.
     * Incoterms that are not present in the API will be deleted.
     * @return Transsmart_Shipping_Model_Sync
     */
    public function syncIncoterms()
    {
        $apiClient = $this->getApiClient();

        // Keep track of all ID's that come from the API
        $apiItemIds = array();

        // Retrieve the items from the API
        $apiItems = $apiClient->getIncoterm();

        if (is_array($apiItems)) {
            foreach ($apiItems as $apiItem) {
                /** @var Transsmart_Shipping_Model_Incoterm $model */
                $model = Mage::getModel('transsmart_shipping/incoterm');
                $mappedData = $model->mapApiKeysToDbColumns($apiItem);

                // is_default is returned as Y/N from the API, Map that to 1 and 0
                if (isset($mappedData['is_default'])) {
                    $mappedData['is_default'] = $mappedData['is_default'] == 'Y' ? 1 : 0;
                }

                $model->setData($mappedData);
                $model->save();

                $apiItemIds []= $model->getId();
            }
        }

        // Remove all items that we didn't receive from the API
        $this->deleteItemsNotInApi(
            Mage::getModel('transsmart_shipping/incoterm'),
            $apiItemIds
        );

        return $this;
    }

    /**
     * Retrieve cost centers from the API and insert into the database.
     * Cost centers that are not present in the API will be deleted.
     * @return Transsmart_Shipping_Model_Sync
     */
    public function syncCostCenters()
    {
        $apiClient = $this->getApiClient();

        // Keep track of all ID's that come from the API
        $apiItemIds = array();

        // Retrieve the items from the API
        $apiItems = $apiClient->getCostcenter();

        if (is_array($apiItems)) {
            foreach ($apiItems as $apiItem) {
                /** @var Transsmart_Shipping_Model_Costcenter $model */
                $model = Mage::getModel('transsmart_shipping/costcenter');
                $mappedData = $model->mapApiKeysToDbColumns($apiItem);

                // is_default is returned as Y/N from the API, Map that to 1 and 0
                if (isset($mappedData['is_default'])) {
                    $mappedData['is_default'] = $mappedData['is_default'] == 'Y' ? 1 : 0;
                }

                $model->setData($mappedData);
                $model->save();

                $apiItemIds []= $model->getId();
            }
        }

        // Remove all items that we didn't receive from the API
        $this->deleteItemsNotInApi(
            Mage::getModel('transsmart_shipping/costcenter'),
            $apiItemIds
        );

        return $this;
    }

    /**
     * Retrieve shipment locations from the API and insert into the database.
     * Shipment locations that are not present in the API will be deleted.
     * @return Transsmart_Shipping_Model_Sync
     */
    public function syncShipmentLocations()
    {
        $apiClient = $this->getApiClient();

        // Keep track of all ID's that come from the API
        $apiItemIds = array();

        // Retrieve the items from the API
        $apiItems = $apiClient->getShipmentLocation();

        if (is_array($apiItems)) {
            foreach ($apiItems as $apiItem) {
                /** @var Transsmart_Shipping_Model_Shipmentlocation $model */
                $model = Mage::getModel('transsmart_shipping/shipmentlocation');
                $mappedData = $model->mapApiKeysToDbColumns($apiItem);

                // is_default is returned as Y/N from the API, Map that to 1 and 0
                if (isset($mappedData['is_default'])) {
                    $mappedData['is_default'] = $mappedData['is_default'] == 'Y' ? 1 : 0;
                }

                $model->setData($mappedData);
                $model->save();

                $apiItemIds []= $model->getId();
            }
        }

        // Remove all items that we didn't receive from the API
        $this->deleteItemsNotInApi(
            Mage::getModel('transsmart_shipping/shipmentlocation'),
            $apiItemIds
        );

        return $this;
    }

    /**
     * Deletes items from the database that are not present in the API
     * @param Mage_Core_Model_Abstract $model
     * @param $apiItemIds array A list of ID's that should not be deleted
     * @return Transsmart_Shipping_Model_Sync
     */
    protected function deleteItemsNotInApi(Mage_Core_Model_Abstract $model, array $apiItemIds)
    {
        // Remove all items that are not present in the API.
        // But only if we receive any id's.
        // This prevents us from deleting items when the API returns an error
        if (count($apiItemIds) > 0) {
            $expiredItems = $model->getCollection()
                ->addFieldToFilter($model->getIdFieldName(), array('nin' => $apiItemIds))
                ->load();

            /** @var Transsmart_Shipping_Model_Servicelevel_Other $expiredItem */
            foreach ($expiredItems as $expiredItem) {
                $expiredItem->delete();
            }
        }

        return $this;
    }

    /**
     * Retrieve the API client from the shipping helper
     * @return Transsmart_Shipping_Model_Client
     */
    protected function getApiClient()
    {
        return Mage::helper('transsmart_shipping')->getApiClient();
    }

    /**
     * Retrieve the most recent date that we synchronized this type on
     * @param string $type Type of synchronization. See constants TYPE_* for available options.
     * @return null|string Returns null when no record could be found
     */
    public function getLastSync($type = self::TYPE_BASE)
    {
        $syncDateCollection = $this->getCollection()
            ->addFieldToFilter('type', $type)
            ->setOrder('created_at', Mage_Core_Model_Resource_Db_Collection_Abstract::SORT_ORDER_DESC)
            ->setPageSize(1)
            ->load();

        if ($syncDateCollection->count() == 0) {
            return null;
        }

        /** @var Transsmart_Shipping_Model_Sync $lastSyncDate */
        $lastSyncDate = $syncDateCollection->getFirstItem();
        return $lastSyncDate->getCreatedAt();
    }

    /**
     * @return $this
     */
    protected function exportShipments()
    {
        Mage::helper('transsmart_shipping/shipment')->doMassExport();
        return $this;
    }

    /**
     * Convert GMT timestamp (which is used by Magento) to CET timestamp (which is used by Transsmart API).
     * Input and output format: 'YYYY-MM-DD HH:MM:SS'
     *
     * @param string $gmtTimestamp
     * @return string
     * @throws Zend_Date_Exception
     */
    protected function _convertGmtToCet($gmtTimestamp)
    {
        $date = new Zend_Date($gmtTimestamp . ' GMT');
        $date->setTimezone('CET');
        return $date->toString('Y-MM-dd HH:m:s');
    }

    /**
     * Convert CET timestamp (which is used by Transsmart API) to GMT timestamp (which is used by Magento).
     * Input and output format: 'YYYY-MM-DD HH:MM:SS'
     *
     * @param string $cetTimestamp
     * @return string
     * @throws Zend_Date_Exception
     */
    protected function _convertCetToGmt($cetTimestamp)
    {
        $date = new Zend_Date($cetTimestamp . ' CET');
        $date->setTimezone('GMT');
        return $date->toString('Y-MM-dd HH:m:s');
    }

    /**
     * Get actual document statuses and tracking URL's since a given timestamp.
     *
     * @param string $sinceTime "YYYY-MM-DD HH:MM:SS"
     * @param string $maxTime Returns the newest timestamp in the results.
     * @return array
     */
    protected function _getShipmentStatuses($sinceTime = null, &$maxTime = null)
    {
        $result = array();

        if (is_null($sinceTime)) {
            $maxTime = Mage::getModel('core/date')->gmtDate();
            $response = $this->getApiClient()->getDocument();
            foreach ($response as $_item) {
                $result[$_item['Reference']] = array(
                    'Status'      => $_item['Status'],
                    'TrackingUrl' => $_item['TrackingUrl']
                );
            }
        }
        else {
            $maxTime = $sinceTime;
            $queryDefinition = array(
                'Carriers'      => array(),
                'CostCenters'   => array(),
                'SubCustomers'  => array(),
                'DateTimeFrom'  => $this->_convertGmtToCet($sinceTime),
                'DateTimeTo'    => $this->_convertGmtToCet(Mage::getModel('core/date')->gmtDate()),
                'MaxResults'    => 100,
                'IsIncremental' => true
            );
            $response = $this->getApiClient()->getStatus($queryDefinition);
            foreach ($response as $_item) {
                $_timestamp = $sinceTime;
                $_resultItem = array(
                    'Status'      => isset($_item['GenericStatusCode']) ? $_item['GenericStatusCode'] : false,
                    'TrackingUrl' => $_item['TrackingUrl']
                );
                foreach ($_item['Statuses'] as $_status) {
                    $_eventTime = $this->_convertCetToGmt(sprintf(
                        '%04d-%02d-%02d %02d:%02d:%02d',
                        (int)substr($_status['EventDate'], 0, 4),
                        (int)substr($_status['EventDate'], 4, 2),
                        (int)substr($_status['EventDate'], 6, 2),
                        (int)substr($_status['EventTime'], 0, 2),
                        (int)substr($_status['EventTime'], 2, 2),
                        (int)substr($_status['EventTime'], 4, 2)
                    ));
                    if ($_eventTime > $maxTime) {
                        $maxTime = $_eventTime;
                    }
                    if ($_eventTime >= $_timestamp || empty($_resultItem['Status'])) {
                        $_timestamp = $_eventTime;
                        $_resultItem['Status'] = $_status['StatusCode'];
                    }
                }
                $result[$_item['Reference']] = $_resultItem;
            }
        }

        return $result;
    }

    /**
     * @return $this
     */
    protected function updateShipmentStatuses($lastSyncTime)
    {
        $statuses = $this->_getShipmentStatuses($lastSyncTime, $maxTime);

        /** @var Mage_Sales_Model_Resource_Order_Shipment_Collection $shipmentCollection */
        $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
            ->addFieldToFilter('transsmart_document_id', array('notnull' => true))
            ->addFieldToFilter('increment_id', array('in' => array_keys($statuses)));

        /** @var Mage_Sales_Model_Order_Shipment $_shipment */
        foreach ($shipmentCollection as $_shipment) {
            // set original data manually (because we didn't call object load())
            $_shipment->setOrigData();

            $this->syncShipment($_shipment, $statuses[$_shipment->getIncrementId()]);
        }

        $this->setCreatedAt($maxTime);

        return $this;
    }

    /**
     * Fetch actual Transsmart status and track-and-trace URL for a single shipment and update its database record.
     * Returns TRUE if something changed.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param array|null $documentData Optional. Array containing 'Status' and/or 'TrackingUrl' keys
     * @return bool
     */
    public function syncShipment($shipment, $documentData = null)
    {
        $updatedAttributes = array();

        if (is_null($documentData)) {
            $documentId = $shipment->getTranssmartDocumentId();
            if (!empty($documentId)) {
                $documentData = $this->getApiClient()->getDocument($documentId);
            }
        }

        if (is_array($documentData)) {
            if (array_key_exists('Id', $documentData)) {
                $value = $documentData['Id'];
                if ($shipment->getData('transsmart_document_id') != $value) {
                    $shipment->setData('transsmart_document_id', $value);
                    $updatedAttributes[] = 'transsmart_document_id';
                }
            }

            if (array_key_exists('Status', $documentData)) {
                $value = $documentData['Status'];
                if ($shipment->getData('transsmart_status') != $value) {
                    $shipment->setData('transsmart_status', $value);
                    $updatedAttributes[] = 'transsmart_status';
                }
            }

            if (array_key_exists('ShipmentError', $documentData)) {
                $value = $documentData['ShipmentError'];
                if ($shipment->getData('transsmart_shipment_error') != $value) {
                    $shipment->setData('transsmart_shipment_error', $value);
                    $updatedAttributes[] = 'transsmart_shipment_error';
                }
            }

            if (array_key_exists('TrackingUrl', $documentData)) {
                $value = $documentData['TrackingUrl'];
                if ($shipment->getData('transsmart_tracking_url') != $value) {
                    $shipment->setData('transsmart_tracking_url', $value);
                    $updatedAttributes[] = 'transsmart_tracking_url';
                }
            }

            if (array_key_exists('CarrierId', $documentData)) {
                $value = $documentData['CarrierId'];
                if ($shipment->getData('transsmart_final_carrier_id') != $value) {
                    $shipment->setData('transsmart_final_carrier_id', $value);
                    $updatedAttributes[] = 'transsmart_final_carrier_id';
                }
            }

            if (array_key_exists('ServiceLevelTimeId', $documentData)) {
                $value = $documentData['ServiceLevelTimeId'];
                if ($shipment->getData('transsmart_final_servicelevel_time_id') != $value) {
                    $shipment->setData('transsmart_final_servicelevel_time_id', $value);
                    $updatedAttributes[] = 'transsmart_final_servicelevel_time_id';
                }
            }

            if (array_key_exists('ServiceLevelOtherId', $documentData)) {
                $value = $documentData['ServiceLevelOtherId'];
                if ($shipment->getData('transsmart_final_servicelevel_other_id') != $value) {
                    $shipment->setData('transsmart_final_servicelevel_other_id', $value);
                    $updatedAttributes[] = 'transsmart_final_servicelevel_other_id';
                }
            }
        }

        if (count($updatedAttributes)) {
            $shipment->getResource()->saveAttribute($shipment, $updatedAttributes);

            // write log message
            $message = Mage::helper('transsmart_shipping')->__('Shipment #%s updated.', $shipment->getIncrementId());
            foreach ($updatedAttributes as $_attributeCode) {
                $message .= ' ' . Mage::helper('transsmart_shipping')->__(
                    'New %s value: "%s".', $_attributeCode, $shipment->getData($_attributeCode)
                );
            }
            Mage::log($message, Zend_Log::INFO, 'transsmart_shipping.log');

            return true;
        }

        return false;
    }

    /**
     * @return $this
     */
    public function syncShipments()
    {
        $lastSyncTime = $this->getLastSync(self::TYPE_SHIPMENTS);

        $this->clearInstance();
        $this->setType(self::TYPE_SHIPMENTS)
            ->setStatus(self::STATUS_RUNNING)
            ->setCreatedAt(Mage::getModel('core/date')->gmtDate())
            ->save();

        $this->updateShipmentStatuses($lastSyncTime);
        $this->exportShipments();

        $this->setStatus(self::STATUS_FINISHED)
            ->save();
    }
}
