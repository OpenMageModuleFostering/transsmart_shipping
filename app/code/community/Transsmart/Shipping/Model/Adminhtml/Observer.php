<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Adminhtml_Observer
{
    /**
     * This event observer is responsible for adding the carrier profiles to the system configuration section.
     *
     * @see Mage_Adminhtml_Model_Config::_initSectionsAndTabs
     * @param Varien_Event_Observer $observer
     */
    public function adminhtmlInitSystemConfig($observer)
    {
        // no need to add the carrier profiles for other config sections
        $section = Mage::app()->getRequest()->getParam('section');
        if ($section != 'transsmart_carrier_profiles') {
            return;
        }

        Varien_Profiler::start(__METHOD__);

        /** @var Mage_Core_Model_Config_Base $config */
        $config = $observer->getConfig();

        /** @var Mage_Core_Model_Config_Element $groups */
        $groups = $config->getNode('sections/transsmart_carrier_profiles/groups');

        /** @var Transsmart_Shipping_Model_Resource_Carrierprofile_Collection $carrierprofileCollection */
        $carrierprofileCollection = Mage::getResourceModel('transsmart_shipping/carrierprofile_collection')
            ->joinCarrier()
            ->joinServicelevelTime()
            ->joinServicelevelOther();

        foreach ($carrierprofileCollection as $_carrierprofile) {
            /** @var Mage_Core_Model_Config_Element $_group */
            $_group = clone $groups->template;
            $groups->template->sort_order += 10;

            // add carrierprofile data to the fieldset so it can be used by the renderer
            foreach ($_carrierprofile->getData() as $_key => $_value) {
                $_group->$_key = $_value;
            }

            $groups->addChild('carrierprofile_' . $_carrierprofile->getId())
                ->extend($_group);
        }

        unset($groups->template);

        Varien_Profiler::stop(__METHOD__);
        //echo '<pre>'.htmlspecialchars($groups->asNiceXml());exit;
    }

    /**
     * Method is triggered when the data is being processed after a post is done on sales order create.
     *
     * @see Mage_Adminhtml_Sales_Order_CreateController::_processActionData
     * @param Varien_Event_Observer $observer
     */
    public function adminhtmlSalesOrderCreateProcessData($observer)
    {
        /** @var Mage_Adminhtml_Model_Sales_Order_Create $orderCreateModel */
        $orderCreateModel = $observer->getOrderCreateModel();

        // prevent removing pickup address when no shipping method is posted
        if (!$orderCreateModel->hasShippingMethod()) {
            return;
        }

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $orderCreateModel->getQuote();

        // check if Mage_Adminhtml_Model_Sales_Order_Create::setShippingAsBilling resets transsmart_carrierprofile_id
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getData('transsmart_carrierprofile_id') &&
             $shippingAddress->getOrigData('transsmart_carrierprofile_id')) {
            return;
        }

        // check if a pickup address is required
        if (!Mage::helper('transsmart_shipping')->isLocationSelectQuote($quote)) {
            // location selector disabled. remove the pickup addresses, if there are any
            Mage::helper('transsmart_shipping/pickupaddress')->removePickupAddressFromQuote($quote);
            return;
        }

        // get pickup address data from order create model (post data)
        $pickupAddressData = $orderCreateModel->getData('transsmart_pickup_address_data');
        if (empty($pickupAddressData)) {
            // nothing posted... does the quote already have a pickup address?
            $pickupAddress = Mage::helper('transsmart_shipping/pickupaddress')->getPickupAddressFromQuote($quote);
            if ($pickupAddress) {
                return;
            }
            Mage::throwException(Mage::helper('transsmart_shipping')->__('A pickup location has to be selected'));
        }

        // base64 decode, convert Latin1 to UTF-8 and JSON decode
        $pickupAddressData = Zend_Json_Decoder::decode(utf8_encode(base64_decode($pickupAddressData)));
        // TODO: verify pickup address data
        Mage::helper('transsmart_shipping/pickupaddress')
            ->savePickupAddressIntoQuote($quote, $pickupAddressData);
    }

    /**
     * This observer adds the Transsmart shipment detail fields to the create/view shipment pages in the admin panel.
     *
     * @see Mage_Adminhtml_Block_Template::_toHtml
     * @param Varien_Event_Observer $observer
     */
    public function adminhtmlBlockHtmlBefore($observer)
    {
        /** @var Mage_Adminhtml_Block_Template $block */
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_Create_Form) {
            // is this a Transsmart order?
            if (!Mage::helper('transsmart_shipping')->isTranssmartOrder($block->getOrder())) {
                return;
            }

            // add the Transsmart shipment detail block, replacing the original 'shipment_tracking' block
            $block->setChild('shipment_tracking', $block->getLayout()->createBlock(
                'transsmart_shipping/adminhtml_sales_order_shipment_create_detail',
                'shipment.create.transsmart.detail'
            ));
        }
        elseif ($block instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_Create_Items) {
            // is this a Transsmart order?
            if (!Mage::helper('transsmart_shipping')->isTranssmartOrder($block->getOrder())) {
                return;
            }

            /** @var Mage_Adminhtml_Block_Widget_Button $submitButton */
            $submitButton = $block->getChild('submit_button');
            if (!$submitButton) {
                return;
            }

            // add the Transsmart action checkboxes to the 'Submit Shipment' button
            $submitButton->setBeforeHtml($block->getLayout()->createBlock(
                'adminhtml/template',
                'shipment.create.transsmart.action',
                array(
                    'template' => 'transsmart/shipping/sales/order/shipment/create/action.phtml'
                )
            )->toHtml());

            // change button caption from 'Submit Shipment' to 'Create Shipment' for clarity
            $submitButton->setLabel(Mage::helper('sales')->__('Create Shipment'));
        }
        elseif ($block instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_View_Form) {
            // is this a Transsmart order?
            if (!Mage::helper('transsmart_shipping')->isTranssmartOrder($block->getOrder())) {
                return;
            }

            // add the Transsmart shipment detail block, replacing the original 'shipment_tracking' block
            $block->setChild('shipment_tracking', $block->getLayout()->createBlock(
                'transsmart_shipping/adminhtml_sales_order_shipment_view_detail',
                'shipment.view.transsmart.detail'
            ));
        }
    }

    /**
     * Observer for the 'sales_order_shipment_save_before' event. Set the posted default shipmentlocation, emailtype,
     * incoterm, costcenter, packages and flags for new shipments created from the admin panel.
     *
     * Note: There's also a global observer for this event, which is called before this one.
     *       @see Transsmart_Shipping_Model_Observer::salesOrderShipmentSaveBefore
     *
     * @see Mage_Sales_Model_Order_Shipment::_beforeSave
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderShipmentSaveBefore($observer)
    {
        /** @var Mage_Sales_Model_Order_Shipment $shipment */
        $shipment = $observer->getShipment();

        // is it a new shipment?
        if (!$shipment->isObjectNew()) {
            return;
        }

        // is this a Transsmart order?
        if (!Mage::helper('transsmart_shipping')->isTranssmartOrder($shipment->getOrder())) {
            return;
        }

        // get posted data
        if (!($frontController = Mage::app()->getFrontController())) {
            return;
        }
        $shipmentPostData = $frontController->getRequest()->getPost('shipment');

        // update the shipment model
        if (!empty($shipmentPostData['transsmart_carrierprofile_id'])) {
            if (Mage::helper('transsmart_shipping/shipment')->getAllowChangeCarrierprofile($shipment)) {
                $shipment->setTranssmartCarrierprofileId($shipmentPostData['transsmart_carrierprofile_id']);
            }
        }
        if (!empty($shipmentPostData['transsmart_shipmentlocation_id'])) {
            $shipment->setTranssmartShipmentlocationId($shipmentPostData['transsmart_shipmentlocation_id']);
        }
        if (!empty($shipmentPostData['transsmart_emailtype_id'])) {
            $shipment->setTranssmartEmailtypeId($shipmentPostData['transsmart_emailtype_id']);
        }
        if (!empty($shipmentPostData['transsmart_costcenter_id'])) {
            $shipment->setTranssmartCostcenterId($shipmentPostData['transsmart_costcenter_id']);
        }
        if (!empty($shipmentPostData['transsmart_incoterm'])) {
            $shipment->setTranssmartIncotermId($shipmentPostData['transsmart_incoterm']);
        }

        if (!empty($shipmentPostData['transsmart_package'])) {
            /** @var Transsmart_Shipping_Model_Resource_Packagetype_Collection $packageTypeCollection */
            $packageTypeCollection = Mage::getResourceSingleton('transsmart_shipping/packagetype_collection');

            $packages = array();
            foreach ($shipmentPostData['transsmart_package'] as $_index => $_packageData) {
                // skip non-numeric indexes
                if (!ctype_digit((string)$_index)) {
                    continue;
                }

                // use default package type if none is specified
                if ($_packageData['package_type'] == 0) {
                    $_packageData['package_type'] = Mage::helper('transsmart_shipping/shipment')
                        ->getDefaultPackagetypeId($shipment->getStore());
                }

                $_packageTypeCode = (string)$_packageData['package_type'];
                $_packageTypeDescription = '';

                /** @var Transsmart_Shipping_Model_Packagetype $_packageType */
                if (($_packageType = $packageTypeCollection->getItemById($_packageData['package_type']))) {
                    $_packageTypeCode = $_packageType->getCode();
                    $_packageTypeDescription = $_packageType->getName();

                    if (!isset($_packageData['length'])) {
                        $_packageData['length'] = $_packageType->getLength();
                    }
                    if (!isset($_packageData['width'])) {
                        $_packageData['width'] = $_packageType->getWidth();
                    }
                    if (!isset($_packageData['height'])) {
                        $_packageData['height'] = $_packageType->getHeight();
                    }
                    if (!isset($_packageData['weight'])) {
                        $_packageData['weight'] = $_packageType->getWeight();
                    }
                }

                $packages[] = array(
                    'PackagingType' => $_packageTypeCode,
                    'Description'   => $_packageTypeDescription,
                    'Quantity'      => (int)$_packageData['qty'],
                    'Length'        => Mage::app()->getLocale()->getNumber($_packageData['length']),
                    'Width'         => Mage::app()->getLocale()->getNumber($_packageData['width']),
                    'Height'        => Mage::app()->getLocale()->getNumber($_packageData['height']),
                    'Weight'        => Mage::app()->getLocale()->getNumber($_packageData['weight']),
                );
            }

            $shipment->setTranssmartPackages(serialize($packages));
        }

        $flags = (int)$shipment->getTranssmartFlags();
        if (!empty($shipmentPostData['transsmart_book'])) {
            $flags |= Transsmart_Shipping_Helper_Shipment::FLAG_BOOK_ON_CREATE;
        }
        if (!empty($shipmentPostData['transsmart_bookandprint'])) {
            $flags |= Transsmart_Shipping_Helper_Shipment::FLAG_BOOKANDPRINT_ON_CREATE;
        }
        $shipment->setTranssmartFlags($flags);
    }

    /**
     * Observer for the 'sales_order_shipment_save_after' event. Create a document for new shipments created from the
     * admin panel immediately.
     *
     * @see Mage_Sales_Model_Order_Shipment::_afterSave
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderShipmentSaveAfter($observer)
    {
        /** @var Mage_Sales_Model_Order_Shipment $shipment */
        $shipment = $observer->getShipment();
        
        // TODO: make a setting for this?

        if ($shipment->getData('transsmart_prevent_export_on_save')) {
            return;
        }

        // export to Transsmart API
        Mage::helper('transsmart_shipping/shipment')->doExport($shipment);
    }
}
