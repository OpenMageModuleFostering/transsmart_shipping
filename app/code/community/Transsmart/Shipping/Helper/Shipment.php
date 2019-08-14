<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Helper_Shipment extends Mage_Core_Helper_Abstract
{
    const XML_PATH_DEFAULT_CARRIERPROFILE    = 'transsmart_shipping/default_shipment/carrierprofile';
    const XML_PATH_DEFAULT_SHIPMENTLOCATION  = 'transsmart_shipping/default_shipment/shipmentlocation';
    const XML_PATH_DEFAULT_EMAILTYPE         = 'transsmart_shipping/default_shipment/emailtype';
    const XML_PATH_DEFAULT_INCOTERM          = 'transsmart_shipping/default_shipment/incoterm';
    const XML_PATH_DEFAULT_COSTCENTER        = 'transsmart_shipping/default_shipment/costcenter';
    const XML_PATH_DEFAULT_PACKAGETYPE       = 'transsmart_shipping/default_shipment/packagetype';

    const XML_PATH_MAPPING_STREET            = 'transsmart_shipping/mapping/street';
    const XML_PATH_MAPPING_STREETNO          = 'transsmart_shipping/mapping/streetno';
    const XML_PATH_MAPPING_STREET2           = 'transsmart_shipping/mapping/street2';
    const XML_PATH_MAPPING_DESCRIPTION       = 'transsmart_shipping/mapping/description';
    const XML_PATH_MAPPING_COUNTRY_OF_ORIGIN = 'transsmart_shipping/mapping/country_of_origin';
    const XML_PATH_MAPPING_HS_CODE           = 'transsmart_shipping/mapping/hs_code';
    const XML_PATH_MAPPING_REASON_OF_EXPORT  = 'transsmart_shipping/mapping/reason_of_export';

    // OR'ed values for the 'transsmart_flags' field in the shipment table
    const FLAG_BOOK_ON_CREATE           = 1;
    const FLAG_BOOKANDPRINT_ON_CREATE   = 2;

    /**
     * Get all possible Transsmart shipment statuses.
     *
     * @return array
     */
    public function getShipmentStatuses()
    {
        return array(
            'NONE'   => $this->__('NONE'),
            'NEW'    => $this->__('NEW'),
            'BOOK'   => $this->__('BOOK'),
            'LABL'   => $this->__('LABL'),
            'MANI'   => $this->__('MANI'),
            'ACCEP'  => $this->__('ACCEP'),
            'TRNS'   => $this->__('TRNS'),
            'DONE'   => $this->__('DONE'),
            'APOD'   => $this->__('APOD'),
            'REFU'   => $this->__('REFU'),
            'ERR'    => $this->__('ERR'),
            'DEL'    => $this->__('DEL'),
            'ONHOLD' => $this->__('ONHOLD'),
        );
    }

    /**
     * Returns TRUE if the carrierprofile may be changed. This is not allowed when the location selector is enabled.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool
     */
    public function getAllowChangeCarrierprofile($shipment)
    {
        $enableLocationSelect = false;

        if ($shipment) {
            if (($shippingAddress = $shipment->getOrder()->getShippingAddress())) {
                /** @see Transsmart_Shipping_Model_Sales_Quote_Address_Total_Shipping::collect */
                if (($carrierprofileId = $shippingAddress->getTranssmartCarrierprofileId())) {
                    /** @var Transsmart_Shipping_Model_Carrierprofile $carrierprofile */
                    $carrierprofile = Mage::getResourceSingleton('transsmart_shipping/carrierprofile_collection')
                        ->joinCarrier()
                        ->getItemById($carrierprofileId);
                    if ($carrierprofile) {
                        $enableLocationSelect = $carrierprofile->isLocationSelectEnabled();
                    }
                }
            }
        }

        return !$enableLocationSelect;
    }

    /**
     * Get configured default carrierprofile for the given shipment or store.
     *
     * @param Mage_Sales_Model_Order_Shipment|null $shipment
     * @param null $store
     * @return int
     */
    public function getDefaultCarrierprofileId($shipment = null, $store = null)
    {
        $defaultValue = false;
        if ($shipment instanceof Mage_Sales_Model_Order_Shipment) {
            // carrierprofile stored in shipping address
            if (($shippingAddress = $shipment->getShippingAddress())) {
                /** @see Transsmart_Shipping_Model_Sales_Quote_Address_Total_Shipping::collect */
                $defaultValue = $shippingAddress->getTranssmartCarrierprofileId();
            }
            if (!$defaultValue) {
                // carrierprofile based on shipping method
                $shippingMethod = $shipment->getOrder()->getShippingMethod(false);
                $carrierprofile = Mage::getModel('transsmart_shipping/carrierprofile')
                    ->loadByShippingMethodCode($shippingMethod);
                $defaultValue = $carrierprofile->getId();
                if (!$defaultValue) {
                    $defaultValue = Mage::getStoreConfig(self::XML_PATH_DEFAULT_CARRIERPROFILE, $shipment->getStore());
                }
            }
        }
        if (!$defaultValue) {
            $defaultValue = Mage::getStoreConfig(self::XML_PATH_DEFAULT_CARRIERPROFILE, $store);
        }
        return $defaultValue;
    }

    /**
     * Get configured default shipmentlocation for the given store.
     *
     * @param null $store
     * @return int
     */
    public function getDefaultShipmentlocationId($store = null)
    {
        $defaultValue = Mage::getStoreConfig(self::XML_PATH_DEFAULT_SHIPMENTLOCATION, $store);
        if (!$defaultValue) {
            $ids = Mage::getResourceModel('transsmart_shipping/shipmentlocation_collection')
                ->addFieldToFilter('is_default', array('eq' => 1))
                ->setPageSize(1)
                ->getAllIds();
            if (count($ids)) {
                $defaultValue = $ids[0];
            }
        }
        return $defaultValue;
    }

    /**
     * Get configured default emailtype for the given store.
     *
     * @param null $store
     * @return int
     */
    public function getDefaultEmailtypeId($store = null)
    {
        $defaultValue = Mage::getStoreConfig(self::XML_PATH_DEFAULT_EMAILTYPE, $store);
        if (!$defaultValue) {
            $ids = Mage::getResourceModel('transsmart_shipping/emailtype_collection')
                ->addFieldToFilter('is_default', array('eq' => 1))
                ->setPageSize(1)
                ->getAllIds();
            if (count($ids)) {
                $defaultValue = $ids[0];
            }
        }
        return $defaultValue;
    }

    /**
     * Get configured default incoterm for the given store.
     *
     * @param null $store
     * @return int
     */
    public function getDefaultIncotermId($store = null)
    {
        $defaultValue = Mage::getStoreConfig(self::XML_PATH_DEFAULT_INCOTERM, $store);
        if (!$defaultValue) {
            $ids = Mage::getResourceModel('transsmart_shipping/incoterm_collection')
                ->addFieldToFilter('is_default', array('eq' => 1))
                ->setPageSize(1)
                ->getAllIds();
            if (count($ids)) {
                $defaultValue = $ids[0];
            }
        }
        return $defaultValue;
    }

    /**
     * Get configured default costcenter for the given store.
     *
     * @param null $store
     * @return int
     */
    public function getDefaultCostcenterId($store = null)
    {
        $defaultValue = Mage::getStoreConfig(self::XML_PATH_DEFAULT_COSTCENTER, $store);
        if (!$defaultValue) {
            $ids = Mage::getResourceModel('transsmart_shipping/costcenter_collection')
                ->addFieldToFilter('is_default', array('eq' => 1))
                ->setPageSize(1)
                ->getAllIds();
            if (count($ids)) {
                $defaultValue = $ids[0];
            }
        }
        return $defaultValue;
    }

    /**
     * Get configured default packagetype for the given store.
     *
     * @param null $store
     * @return int
     */
    public function getDefaultPackagetypeId($store = null)
    {
        $defaultValue = Mage::getStoreConfig(self::XML_PATH_DEFAULT_PACKAGETYPE, $store);
        if (!$defaultValue) {
            $ids = Mage::getResourceModel('transsmart_shipping/packagetype_collection')
                ->addFieldToFilter('is_default', array('eq' => 1))
                ->setPageSize(1)
                ->getAllIds();
            if (count($ids)) {
                $defaultValue = $ids[0];
            }
        }
        return $defaultValue;
    }

    /**
     * Get the street and housenumber from the given address. If there's only one street field, try to split it into
     * separate fields.
     *
     * @param Mage_Sales_Model_Order_Address $address
     * @param mixed $store
     * @return array
     */
    protected function _getStreetFields($address, $store = null)
    {
        if ($address->hasTranssmartServicepointId()) {
            // for addresses from the location selector, always use predetermined fields
            $street   = $address->getStreet(1);
            $streetNo = $address->getStreet(2);
            $street2  = $address->getStreet(3);
        }
        else {
            // read config settings for street fields mapping
            $mappingStreet   = Mage::getStoreConfig(self::XML_PATH_MAPPING_STREET,   $store);
            $mappingStreetNo = Mage::getStoreConfig(self::XML_PATH_MAPPING_STREETNO, $store);
            $mappingStreet2  = Mage::getStoreConfig(self::XML_PATH_MAPPING_STREET2,  $store);

            // get street value
            switch ($mappingStreet) {
                case Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Mapping_Street::NONE:
                    $street = '';
                    break;
                case Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Mapping_Street::FULL:
                    $street = trim(implode(' ', $address->getStreet()));
                    break;
                default:
                    $street = $address->getStreet($mappingStreet);
            }

            // get streetno value
            switch ($mappingStreetNo) {
                case Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Mapping_Street::NONE:
                    $streetNo = '';
                    break;
                case Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Mapping_Street::FULL:
                    $streetNo = trim(implode(' ', $address->getStreet()));
                    break;
                default:
                    $streetNo = $address->getStreet($mappingStreetNo);
            }

            // get street2 value
            switch ($mappingStreet2) {
                case Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Mapping_Street::NONE:
                    $street2 = '';
                    break;
                case Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Mapping_Street::FULL:
                    $street2 = trim(implode(' ', $address->getStreet()));
                    break;
                default:
                    $street2 = $address->getStreet($mappingStreet2);
            }

            // automatic house number detection
            if ($mappingStreet == $mappingStreetNo) {
                $streetNo = '';

                if (preg_match('/^(.*) ([0-9]+ .*)$/', $street, $matches)) {
                    $street   = $matches[1];
                    $streetNo = $matches[2];
                }
                elseif (preg_match('/^(.*) ([0-9]+.*)$/', $street, $matches)) {
                    $street   = $matches[1];
                    $streetNo = $matches[2];
                }
                elseif (preg_match('/^(.*) ([0-9]+.*)$/', $street, $matches)) {
                    $street   = $matches[1];
                    $streetNo = $matches[2];
                }
            }
        }

        // house number cannot be empty
        if ($streetNo === '') {
            $streetNo = '.';
        }

        return array($street, $streetNo, $street2);
    }

    /**
     * Export the given shipment to the Transsmart API.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param bool $allowPrint
     */
    public function doExport($shipment, $allowPrint = true)
    {
        // is it already exported?
        if ($shipment->getTranssmartDocumentId()) {
            return;
        }

        /** @var Transsmart_Shipping_Model_Carrierprofile $carrierprofile */
        $carrierprofile = Mage::getModel('transsmart_shipping/carrierprofile')
            ->load($shipment->getTranssmartCarrierprofileId());
        if (!$carrierprofile->getId()) {
            return;
        }

        $store = $shipment->getStore();
        $address = $shipment->getShippingAddress();
        $invoiceAddress = $shipment->getBillingAddress();

        // check if the order has a pickup address (location selector is used)
        $pickupAddress = Mage::helper('transsmart_shipping/pickupaddress')
            ->getPickupAddressFromOrder($shipment->getOrder());
        $servicePointId = null;
        if ($pickupAddress) {
            $address = $pickupAddress;
            $invoiceAddress = $shipment->getShippingAddress();
            $servicePointId = $pickupAddress->getTranssmartServicepointId();
        }

        // determine address name and contact
        $addressName = $address->getCompany();
        $addressContact = $address->getName();
        if ($addressName == '') {
            $addressName = $addressContact;
        }

        // determine invoice address name and contact
        $invoiceAddressName = $invoiceAddress->getCompany();
        $invoiceAddressContact = $invoiceAddress->getName();
        if ($invoiceAddressName == '') {
            $invoiceAddressName = $invoiceAddressContact;
        }

        // split street fields into street and house number
        list($addressStreet, $addressStreetNo, $addressStreet2) =
            $this->_getStreetFields($address, $store);
        list($invoiceAddressStreet, $invoiceAddressStreetNo, $invoiceAddressStreet2) =
            $this->_getStreetFields($invoiceAddress, $store);

        /** @var Mage_Core_Helper_String $stringHelper */
        $stringHelper = Mage::helper('core/string');

        // calculate shipment value and prepare delivery note lines
        $shipmentValue = 0.0;
        $deliveryNoteInfoLines = array();
        /** @var Mage_Sales_Model_Order_Shipment_Item $_item */
        foreach ($shipment->getAllItems() as $_item) {
            if ($_item->getOrderItem()->getParentItem()) {
                continue;
            }

            $shipmentValue += $_item->getPrice() * $_item->getQty();

            $_line = array(
                'ArticleId'         => $stringHelper->substr($_item->getSku(), 0, 64),
                'ArticleName'       => $stringHelper->substr($_item->getName(), 0, 128),
                'Description'       => $stringHelper->substr($_item->getShortDescription(), 0, 256),
                'Price'             => $_item->getPrice(),
                'Currency'          => $store->getCurrentCurrencyCode(),
                'Quantity'          => $_item->getQty(),
                'QuantityBackorder' => floatval($_item->getOrderItem()->getQtyBackordered()),
                'QuantityOrder'     => $_item->getOrderItem()->getQtyOrdered(),
                'CountryOfOrigin'   => Mage::getStoreConfig('shipping/origin/country_id', $store),
                'ReasonOfExport'    => 'Sale',
            );

            $_additionalFields = array(
                'Description'       => Mage::getStoreConfig(self::XML_PATH_MAPPING_DESCRIPTION,       $store),
                'CountryOfOrigin'   => Mage::getStoreConfig(self::XML_PATH_MAPPING_COUNTRY_OF_ORIGIN, $store),
                'HSCode'            => Mage::getStoreConfig(self::XML_PATH_MAPPING_HS_CODE,           $store),
                'ReasonOfExport'    => Mage::getStoreConfig(self::XML_PATH_MAPPING_REASON_OF_EXPORT,  $store),
            );
            $_product = $_item->getOrderItem()->getProduct();
            foreach ($_additionalFields as $_field => $_attributeCode) {
                if (empty($_attributeCode)) {
                    continue;
                }
                if ($_attribute = $_product->getResource()->getAttribute($_attributeCode)) {
                    if ($_attribute->getBackendType() == 'int' && $_attribute->getFrontendInput() == 'select') {
                        // dropdown or multiselect field; use the frontend label
                        $_attribute->setStoreId($store->getId());
                        $_value = $_attribute->getFrontend()->getValue($_product);
                    }
                    else {
                        $_value = $_product->getData($_attributeCode);
                    }
                    if ($_value) {
                        $_maxLength = ($_field == 'Description') ? 256 : 64;
                        $_line[$_field] = $stringHelper->substr($_value, 0, $_maxLength);
                    }
                }
            }

            $deliveryNoteInfoLines[] = $_line;
        }

        // prepare the document
        $document = array(
            'Reference'                 => $shipment->getIncrementId(),
            'CarrierProfileId'          => $carrierprofile->getId(),
            //'CarrierId'                 => $carrierprofile->getCarrierId(),
            //'ServiceLevelTimeId'        => $carrierprofile->getServicelevelTimeId(),
            //'ServiceLevelOtherId'       => $carrierprofile->getServicelevelOtherId(),
            'ShipmentLocationId'        => $shipment->getTranssmartShipmentlocationId(),
            'MailTypeId'                => $shipment->getTranssmartEmailtypeId(),
            'IncotermId'                => $shipment->getTranssmartIncotermId(),
            'CostCenterId'              => $shipment->getTranssmartCostcenterId(),
            'RefOrder'                  => $shipment->getOrder()->getIncrementId(),
            'RefServicePoint'           => $servicePointId,
            'ShipmentValue'             => $shipmentValue,
            'AddressEmailPickup'        => Mage::getStoreConfig('trans_email/ident_general/email', $store),
            'AddressName'               => $addressName,
            'AddressContact'            => $addressContact,
            'AddressStreet'             => $addressStreet,
            'AddressStreetNo'           => $addressStreetNo,
            'AddressStreet2'            => $addressStreet2,
            'AddressZipcode'            => $address->getPostcode(),
            'AddressCity'               => $address->getCity(),
            'AddressState'              => $address->getRegionCode(),
            'AddressCountry'            => $address->getCountry(),
            'AddressPhone'              => $address->getTelephone(),
            'AddressFax'                => $address->getFax(),
            'AddressEmail'              => $shipment->getShippingAddress()->getEmail(),
            'AddressCustomerNo'         => $address->getCustomerId(),
            'AddressNameInvoice'        => $invoiceAddressName,
            'AddressContactInvoice'     => $invoiceAddressContact,
            'AddressStreetInvoice'      => $invoiceAddressStreet,
            'AddressStreetNoInvoice'    => $invoiceAddressStreetNo,
            'AddressStreet2Invoice'     => $invoiceAddressStreet2,
            'AddressZipcodeInvoice'     => $invoiceAddress->getPostcode(),
            'AddressCityInvoice'        => $invoiceAddress->getCity(),
            'AddressStateInvoice'       => $invoiceAddress->getRegionCode(),
            'AddressCountryInvoice'     => $invoiceAddress->getCountry(),
            'AddressPhoneInvoice'       => $invoiceAddress->getTelephone(),
            'AddressFaxInvoice'         => $invoiceAddress->getFax(),
            'AddressEmailInvoice'       => $invoiceAddress->getEmail(),
            'AddressCustomerNoInvoice'  => $invoiceAddress->getCustomerId(),
            'AddressVatNumberInvoice'   => $shipment->getOrder()->getCustomerTaxvat(),
            'ColliInformation'          => unserialize($shipment->getTranssmartPackages()),
            'DeliveryNoteInfo'          => array(
                array(
                    'DeliveryNoteInfoLines' => $deliveryNoteInfoLines
                )
            )
        );

        // dispatch event so other extensions can update the document
        $transport = new Varien_Object(array('document' => $document));
        Mage::dispatchEvent('transsmart_shipping_shipment_export_before', array(
            'shipment'  => $shipment,
            'transport' => $transport
        ));
        $document = $transport->getDocument();

        // send the document to Transsmart
        $response = Mage::helper('transsmart_shipping')->getApiClient()->createDocument($document);

        // save document ID and status into shipment record
        if (isset($response['Id']) && isset($response['Status'])) {
            Mage::getSingleton('transsmart_shipping/sync')->syncShipment($shipment, $response);

            // book and print if flags indicate so
            $flags = (int)$shipment->getTranssmartFlags();
            try {
                if (($flags & Transsmart_Shipping_Helper_Shipment::FLAG_BOOKANDPRINT_ON_CREATE)) {
                    if ($allowPrint) {
                        $this->doBookAndPrint($shipment);
                    }
                    else {
                        $this->doBooking($shipment);
                    }
                }
                elseif (($flags & Transsmart_Shipping_Helper_Shipment::FLAG_BOOK_ON_CREATE)) {
                    $this->doBooking($shipment);
                }
            }
            catch (Exception $exception) {
                Mage::logException($exception);
            }
        }
    }

    /**
     * Create Transsmart API documents for all shipments that need to be exported.
     */
    public function doMassExport()
    {
        /** @var Mage_Sales_Model_Resource_Order_Shipment_Collection $shipmentCollection */
        $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection');

        // we need only valid Transsmart shipments without document ID
        $shipmentCollection
            ->addFieldToFilter('transsmart_carrierprofile_id', array('notnull' => true))
            ->addFieldToFilter('transsmart_shipmentlocation_id', array('notnull' => true))
            ->addFieldToFilter('transsmart_emailtype_id', array('notnull' => true))
            ->addFieldToFilter('transsmart_incoterm_id', array('notnull' => true))
            ->addFieldToFilter('transsmart_costcenter_id', array('notnull' => true))
            ->addFieldToFilter('transsmart_packages', array('notnull' => true))
            ->addFieldToFilter('transsmart_document_id', array('null' => true));

        /** @var Mage_Sales_Model_Order_Shipment $_shipment */
        foreach ($shipmentCollection as $_shipment) {
            // set original data manually (because we didn't call object load())
            $_shipment->setOrigData();

            $this->doExport($_shipment, false);
        }

        // group documentIds for all book-and-print shipments with the same QZ Host and Selected Printer
        $groupedCalls = $this->_getMassPrintGroupedCalls($shipmentCollection, true);
        if (count($groupedCalls) == 0) {
            return;
        }

        $idsToSync = array();
        try {
            // call Transsmart API doLabel method for each group (doBooking was already called in doExport)
            foreach ($groupedCalls as $_call) {
                $idsToSync += $_call['doc_ids'];
                Mage::helper('transsmart_shipping')->getApiClient()->doLabel(
                    $_call['doc_ids'],
                    Mage::getStoreConfig(Transsmart_Shipping_Helper_Data::XML_PATH_CONNECTION_USERNAME, 0),
                    false,
                    false,
                    $_call['qz_host'],
                    $_call['selected_printer']
                );
            }
        }
        catch (Exception $exception) {
            $this->_massSyncDocuments($shipmentCollection, $idsToSync);
            throw $exception;
        }
        $this->_massSyncDocuments($shipmentCollection, $idsToSync);
    }

    /**
     * Call doBookAndPrint for a Transsmart shipment and process the response. Returns TRUE if successful.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool
     * @throws Exception
     */
    public function doBookAndPrint($shipment)
    {
        if (!$shipment->getTranssmartDocumentId()) {
            Mage::throwException($this->__('Transsmart document ID is not known.'));
        }

        try {
            // call Transsmart API doBookAndPrint method
            Mage::helper('transsmart_shipping')->getApiClient()->doBookAndPrint(
                $shipment->getTranssmartDocumentId(),
                Mage::getStoreConfig(Transsmart_Shipping_Helper_Data::XML_PATH_CONNECTION_USERNAME, 0),
                false,
                Mage::getStoreConfig(
                    Transsmart_Shipping_Helper_Data::XML_PATH_PRINT_QZHOST,
                    $shipment->getStore()
                ),
                Mage::getStoreConfig(
                    Transsmart_Shipping_Helper_Data::XML_PATH_PRINT_SELECTEDPRINTER,
                    $shipment->getStore()
                )
            );
        }
        catch (Exception $exception) {
            Mage::getSingleton('transsmart_shipping/sync')->syncShipment($shipment);
            throw $exception;
        }
        Mage::getSingleton('transsmart_shipping/sync')->syncShipment($shipment);

        return true;
    }

    /**
     * Call doBooking for a Transsmart shipment and process the response. Returns TRUE if successful.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool
     * @throws Exception
     */
    public function doBooking($shipment)
    {
        if (!$shipment->getTranssmartDocumentId()) {
            Mage::throwException($this->__('Transsmart document ID is not known.'));
        }

        try {
            // call Transsmart API doBooking method
            Mage::helper('transsmart_shipping')->getApiClient()->doBooking(
                $shipment->getTranssmartDocumentId()
            );
        }
        catch (Exception $exception) {
            Mage::getSingleton('transsmart_shipping/sync')->syncShipment($shipment);
            throw $exception;
        }
        Mage::getSingleton('transsmart_shipping/sync')->syncShipment($shipment);

        return true;
    }

    /**
     * Call doLabel for a Transsmart shipment.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool
     * @throws Exception
     */
    public function doLabel($shipment)
    {
        if (!$shipment->getTranssmartDocumentId()) {
            Mage::throwException($this->__('Transsmart document ID is not known.'));
        }

        try {
            // call Transsmart API doLabel method
            Mage::helper('transsmart_shipping')->getApiClient()->doLabel(
                $shipment->getTranssmartDocumentId(),
                Mage::getStoreConfig(Transsmart_Shipping_Helper_Data::XML_PATH_CONNECTION_USERNAME, 0),
                false,
                false,
                Mage::getStoreConfig(
                    Transsmart_Shipping_Helper_Data::XML_PATH_PRINT_QZHOST,
                    $shipment->getStore()
                ),
                Mage::getStoreConfig(
                    Transsmart_Shipping_Helper_Data::XML_PATH_PRINT_SELECTEDPRINTER,
                    $shipment->getStore()
                )
            );
        }
        catch (Exception $exception) {
            Mage::getSingleton('transsmart_shipping/sync')->syncShipment($shipment);
            throw $exception;
        }
        Mage::getSingleton('transsmart_shipping/sync')->syncShipment($shipment);

        return true;
    }

    /**
     * Group documentIds for shipments with the same QZ Host and Selected Printer.
     * Used by doMassBookAndPrint and doMassLabel
     *
     * @param Mage_Sales_Model_Resource_Order_Shipment_Collection $shipmentCollection
     * @param bool $onlyWithBookAndPrintFlag
     * @return array
     */
    protected function _getMassPrintGroupedCalls($shipmentCollection, $onlyWithBookAndPrintFlag)
    {
        // group documentIds for shipments with the same QZ Host and Selected Printer.
        $groupedCalls = array();
        foreach ($shipmentCollection as $_shipment) {
            if (!$_shipment->getTranssmartDocumentId()) {
                continue;
            }

            // do we need only shipments with the FLAG_BOOKANDPRINT_ON_CREATE flag?
            if ($onlyWithBookAndPrintFlag) {
                $_flags = (int)$_shipment->getTranssmartFlags();
                if (($_flags & Transsmart_Shipping_Helper_Shipment::FLAG_BOOKANDPRINT_ON_CREATE) == 0) {
                    continue;
                }
            }

            $_qzHost = Mage::getStoreConfig(
                Transsmart_Shipping_Helper_Data::XML_PATH_PRINT_QZHOST,
                $_shipment->getStore()
            );
            $_selectedPrinter = Mage::getStoreConfig(
                Transsmart_Shipping_Helper_Data::XML_PATH_PRINT_SELECTEDPRINTER,
                $_shipment->getStore()
            );

            $_groupKey = $_qzHost . ':' . $_selectedPrinter;
            if (!isset($groupedCalls[$_groupKey])) {
                $groupedCalls[$_groupKey] = array(
                    'qz_host'          => $_qzHost,
                    'selected_printer' => $_selectedPrinter,
                    'doc_ids'          => array()
                );
            }

            $groupedCalls[$_groupKey]['doc_ids'][] = $_shipment->getTranssmartDocumentId();
        }

        return $groupedCalls;
    }

    /**
     * Synchronize status for the given shipments. If idsToSync array is given, only those document IDs will be synced.
     * Used by doMassBookAndPrint and doMassLabel
     *
     * @param Mage_Sales_Model_Resource_Order_Shipment_Collection $shipmentCollection
     * @param array|null $idsToSync
     */
    protected function _massSyncDocuments($shipmentCollection, $idsToSync = null)
    {
        if (!is_null($idsToSync) && count($idsToSync) == 0) {
            return;
        }

        foreach ($shipmentCollection as $_shipment) {
            $_documentId = $_shipment->getTranssmartDocumentId();
            if (!$_documentId || (!is_null($idsToSync) && !in_array($_documentId, $idsToSync))) {
                continue;
            }

            try {
                Mage::getSingleton('transsmart_shipping/sync')->syncShipment($_shipment);
            }
            catch (Mage_Core_Exception $exception) {
                Mage::logException($exception);
            }
        }
    }

    /**
     * Call doBookAndPrint for multiple Transsmart shipments at once.
     *
     * @param Mage_Sales_Model_Resource_Order_Shipment_Collection $shipmentCollection
     * @return bool
     * @throws Exception
     */
    public function doMassBookAndPrint($shipmentCollection)
    {
        // group documentIds for shipments with the same QZ Host and Selected Printer.
        $groupedCalls = $this->_getMassPrintGroupedCalls($shipmentCollection, false);
        if (count($groupedCalls) == 0) {
            return;
        }

        $idsToSync = array();
        try {
            // call Transsmart API doLabel method for each group
            foreach ($groupedCalls as $_call) {
                $idsToSync += $_call['doc_ids'];
                Mage::helper('transsmart_shipping')->getApiClient()->doBookAndPrint(
                    $_call['doc_ids'],
                    Mage::getStoreConfig(Transsmart_Shipping_Helper_Data::XML_PATH_CONNECTION_USERNAME, 0),
                    false,
                    $_call['qz_host'],
                    $_call['selected_printer']
                );
            }
        }
        catch (Exception $exception) {
            $this->_massSyncDocuments($shipmentCollection, $idsToSync);
            throw $exception;
        }
        $this->_massSyncDocuments($shipmentCollection, $idsToSync);

        return true;
    }

    /**
     * Call doLabel for multiple Transsmart shipments at once.
     *
     * @param Mage_Sales_Model_Resource_Order_Shipment_Collection $shipmentCollection
     * @return bool
     * @throws Exception
     */
    public function doMassLabel($shipmentCollection)
    {
        // group documentIds for shipments with the same QZ Host and Selected Printer.
        $groupedCalls = $this->_getMassPrintGroupedCalls($shipmentCollection, false);
        if (count($groupedCalls) == 0) {
            return;
        }

        $idsToSync = array();
        try {
            // call Transsmart API doLabel method for each group
            foreach ($groupedCalls as $_call) {
                $idsToSync += $_call['doc_ids'];
                Mage::helper('transsmart_shipping')->getApiClient()->doLabel(
                    $_call['doc_ids'],
                    Mage::getStoreConfig(Transsmart_Shipping_Helper_Data::XML_PATH_CONNECTION_USERNAME, 0),
                    false,
                    false,
                    $_call['qz_host'],
                    $_call['selected_printer']
                );
            }
        }
        catch (Exception $exception) {
            $this->_massSyncDocuments($shipmentCollection, $idsToSync);
            throw $exception;
        }
        $this->_massSyncDocuments($shipmentCollection, $idsToSync);

        return true;
    }
}
