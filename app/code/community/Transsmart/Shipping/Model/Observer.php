<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Observer
{
    /**
     * Observer for the 'sales_order_shipment_save_before' event. Set default shipmentlocation, emailtype, incoterm,
     * costcenter and packages for new shipments.
     *
     * Note: There's also an admin-only observer for this event, which is called after this one.
     *       @see Transsmart_Shipping_Model_Adminhtml_Observer::salesOrderShipmentSaveBefore
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

        // does it use one of our shipping methods?
        $shippingMethod = $shipment->getOrder()->getShippingMethod();
        if (!Mage::helper('transsmart_shipping')->isTranssmartShippingMethod($shippingMethod)) {
            return;
        }

        /** @var Transsmart_Shipping_Helper_Shipment $helper */
        $helper = Mage::helper('transsmart_shipping/shipment');

        // set defaults Transsmart shipment properties if not set
        if (!$shipment->hasTranssmartCarrierprofileId()) {
            $shipment->setTranssmartCarrierprofileId($helper->getDefaultCarrierprofileId($shipment));
        }
        if (!$shipment->hasTranssmartShipmentlocationId()) {
            $shipment->setTranssmartShipmentlocationId($helper->getDefaultShipmentlocationId($shipment->getStore()));
        }
        if (!$shipment->hasTranssmartEmailtypeId()) {
            $shipment->setTranssmartEmailtypeId($helper->getDefaultEmailtypeId($shipment->getStore()));
        }
        if (!$shipment->hasTranssmartIncotermId()) {
            $shipment->setTranssmartIncotermId($helper->getDefaultIncotermId($shipment->getStore()));
        }
        if (!$shipment->hasTranssmartCostcenterId()) {
            $shipment->setTranssmartCostcenterId($helper->getDefaultCostcenterId($shipment->getStore()));
        }
        if (!$shipment->hasTranssmartPackages()) {
            $packageType = Mage::getResourceSingleton('transsmart_shipping/packagetype_collection')
                ->getItemById($helper->getDefaultPackagetypeId($shipment->getStore()));
            $packages = array();
            if ($packageType) {
                $packages[] = array(
                    'PackagingType' => $packageType->getCode(),
                    'Description'   => $packageType->getName(),
                    'Quantity'      => 1,
                    'Length'        => (float)$packageType->getLength(),
                    'Width'         => (float)$packageType->getWidth(),
                    'Height'        => (float)$packageType->getHeight(),
                    'Weight'        => (float)$packageType->getWeight(),
                );
            }
            $shipment->setTranssmartPackages(serialize($packages));
        }
    }

    /**
     * Observer for the 'sales_order_shipment_resource_save_attribute_after' event. This will update the associated
     * record in the order grid table when the transsmart_status field is updated for a shipment. Also dispatches a
     * new event if the status changed to LABL, which means the label was printed.
     *
     * @see Mage_Sales_Model_Resource_Order_Shipment::_afterSaveAttribute
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderShipmentResourceSaveAttributeAfter($observer)
    {
        // only do something when the transsmart_status field was saved
        if (!in_array('transsmart_status', $observer->getAttribute())) {
            return;
        }

        /** @var Mage_Sales_Model_Order_Shipment $shipment */
        $shipment = $observer->getObject();

        if ($shipment->hasDataChanges() && $shipment->getOrderId()) {
            // update order grid table after status update
            Mage::getResourceModel('sales/order')->updateOnRelatedRecordChanged(
                'entity_id',
                $shipment->getOrderId()
            );

            // check if status changed to LABL
            $oldStatus = $shipment->getOrigData('transsmart_status');
            $newStatus = $shipment->getData('transsmart_status');
            if ($oldStatus != $newStatus && $newStatus == 'LABL') {
                Mage::dispatchEvent('transsmart_shipping_shipment_label_printed', array(
                    'shipment' => $shipment
                ));
            }
        }
    }

    /**
     * Method is triggered when saving the shipping method.
     * We use this to store the store location data.
     *
     * @see Mage_Checkout_OnepageController::saveShippingMethodAction()
     * @param Varien_Event_Observer $observer
     */
    public function checkoutControllerOnepageSaveShippingMethod($observer)
    {
        /** @var Mage_Core_Controller_Request_Http $request */
        $request = $observer->getRequest();

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getQuote();

        $shippingAddress = $quote->getShippingAddress();
        $shippingMethod = $shippingAddress->getShippingMethod();

        // remove the pickup addresses, if there are any
        Mage::helper('transsmart_shipping/pickupaddress')->removePickupAddressFromQuote($quote);

        // try to load the carrierprofile based on the shipping method
        $carrierProfile = Mage::getModel('transsmart_shipping/carrierprofile')
            ->loadByShippingMethodCode($shippingMethod);
        if (!$carrierProfile->getId() || !$carrierProfile->isLocationSelectEnabled()) {
            // not a Transsmart shipping method with enabled location selector
            return;
        }

        if (!($pickupAddressData = $request->getPost('transsmart_pickup_address_data'))) {
            // No location data provided
            $errorMessage = Mage::helper('transsmart_shipping')->__('A pickup location has to be selected');
            if ($request->isAjax()) {
                Mage::app()->getFrontController()->getResponse()
                    ->setHeader('Content-Type', 'application/json', true)
                    ->setBody(Mage::helper('core')->jsonEncode(array('error' => -1, 'message' => $errorMessage)))
                    ->sendResponse();
                exit;
            }
            return;
        }

        // base64 decode, convert Latin1 to UTF-8 and JSON decode
        $pickupAddressData = Zend_Json_Decoder::decode(utf8_encode(base64_decode($pickupAddressData)));
        // TODO: verify pickup address data
        Mage::helper('transsmart_shipping/pickupaddress')
            ->savePickupAddressIntoQuote($quote, $pickupAddressData);
    }

    /**
     * Method is triggered when calling a controller action in GoMage LightCheckout, for example when saving the
     * shipping method. We use this to store the store location data.
     *
     * @see GoMage_Checkout_OnepageController::saveAction
     * @see GoMage_Checkout_OnepageController::ajaxAction
     * @param Varien_Event_Observer $observer
     */
    public function controllerActionPredispatchGomageCheckout($observer)
    {
        /** @var GoMage_Checkout_OnepageController $controllerAction */
        $controllerAction = $observer->getEvent()->getControllerAction();

        // verify controller and action name
        $request = $controllerAction->getRequest();
        if ($request->getRequestedControllerName() != 'onepage') {
            return;
        }
        if (!in_array($request->getRequestedActionName(), array('ajax', 'save'))) {
            return;
        }

        // verify ajax action name
        if ($request->getRequestedActionName() == 'ajax') {
            if (!in_array($request->getParam('action'), array('get_totals'))) {
                return;
            }
        }

        $quote = $controllerAction->getOnepage()->getQuote();

        $shippingMethod = $request->getPost('shipping_method', $quote->getShippingAddress()->getShippingMethod());

        // remove the pickup addresses, if there are any
        Mage::helper('transsmart_shipping/pickupaddress')->removePickupAddressFromQuote($quote);

        // try to load the carrierprofile based on the shipping method
        $carrierProfile = Mage::getModel('transsmart_shipping/carrierprofile')
            ->loadByShippingMethodCode($shippingMethod);
        if (!$carrierProfile->getId() || !$carrierProfile->isLocationSelectEnabled()) {
            // not a Transsmart shipping method with enabled location selector
            return;
        }

        if (!($pickupAddressData = $request->getPost('transsmart_pickup_address_data'))) {
            // No location data provided
            if ($request->isAjax() && $request->getRequestedActionName() == 'save') {
                $errorMessage = Mage::helper('transsmart_shipping')->__('A pickup location has to be selected');
                Mage::app()->getFrontController()->getResponse()
                    ->setHeader('Content-Type', 'application/json', true)
                    ->setBody(Mage::helper('core')->jsonEncode(array('error' => -1, 'message' => $errorMessage)))
                    ->sendResponse();
                exit;
            }
            return;
        }

        // base64 decode, convert Latin1 to UTF-8 and JSON decode
        $pickupAddressData = Zend_Json_Decoder::decode(utf8_encode(base64_decode($pickupAddressData)));
        // TODO: verify pickup address data
        Mage::helper('transsmart_shipping/pickupaddress')
            ->savePickupAddressIntoQuote($quote, $pickupAddressData);
    }

    /**
     * Method is triggered when converting the quote to the order.
     *
     * @see Mage_Sales_Model_Convert_Quote::toOrder()
     * @param Varien_Event_Observer $observer
     */
    public function salesConvertQuoteToOrder($observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getQuote();

        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();

        // check whether this is a Transsmart shipping method
        if (!Mage::helper('transsmart_shipping')->isTranssmartPickup($shippingMethod)) {
            return;
        }

        /** @var Mage_Sales_Model_Quote_Address $pickupAddress */
        if (!($pickupAddress = Mage::helper('transsmart_shipping/pickupaddress')->getPickupAddressFromQuote($quote))) {
            // no pickup address
            Mage::throwException(Mage::helper('transsmart_shipping')->__('A pickup location has to be selected'));
        }

        $orderAddress = Mage::getModel('sales/convert_quote')->addressToOrderAddress($pickupAddress);
        $orderAddress->setParentId($order->getId());

        $order->addAddress($orderAddress);
    }

    /**
     * Method is triggered when submitting an order is placed. Make sure it has a pickup address if needed.
     *
     * @see Mage_Sales_Model_Service_Quote::submitOrder
     * @param Varien_Event_Observer $observer
     */
    public function checkoutTypeOnepageSaveOrder($observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();

        // check whether this is a Transsmart shipping method
        if (!Mage::helper('transsmart_shipping')->isTranssmartPickup($order->getShippingMethod())) {
            return;
        }

        /** @var Mage_Sales_Model_Quote_Address $pickupAddress */
        if (!($pickupAddress = Mage::helper('transsmart_shipping/pickupaddress')->getPickupAddressFromOrder($order))) {
            Mage::throwException(Mage::helper('transsmart_shipping')->__('A pickup location has to be selected'));
        }
    }

    /**
     * Method is triggered when converting an order to a quote. This currently only happens during reorder in admin.
     *
     * @see Mage_Adminhtml_Model_Sales_Order_Create::initFromOrder
     * @param Varien_Event_Observer $observer
     */
    public function salesConvertOrderToQuote($observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getQuote();

        // check whether this is a Transsmart shipping method
        if (!Mage::helper('transsmart_shipping')->isTranssmartPickup($order->getShippingMethod())) {
            return;
        }

        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress) {
            $shippingAddress->unsShippingMethod();
        }
    }

    /**
     * Triggered when a label is printed for a Transsmart shipment.
     *
     * @see Transsmart_Shipping_Model_Observer::salesOrderShipmentResourceSaveAttributeAfter
     * @param Varien_Event_Observer $observer
     * @throws Exception
     */
    public function transsmartShippingShipmentLabelPrinted($observer)
    {
        /** @var Mage_Sales_Model_Shipment $shipment */
        $shipment = $observer->getEvent()->getShipment();

        $trackingUrl = $shipment->getData('transsmart_tracking_url');

        // get carrier name for this shipping method
        $carrierName = Mage::getModel('transsmart_shipping/carrierprofile')
            ->load($shipment->getTranssmartCarrierprofileId())
            ->getCarrierName();

        // prepare message before translation
        if ($carrierName) {
            $trackingMessage = array(
                'The shipment is carried by %s and can be tracked <a href="%s">here</a>.',
                $carrierName,
                $trackingUrl
            );
        }
        else {
            $trackingMessage = array(
                'The shipment can be tracked <a href="%s">here</a>.',
                $trackingUrl
            );
        }

        // emulate the store and translate the message
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($shipment->getStoreId());
        $trackingMessage = Mage::helper('transsmart_shipping')->forceTranslate(
            $trackingMessage,
            Mage::app()->getTranslator()->getLocale()
        );
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // add comment and send the tracking URL email
        $shipment->addComment($trackingMessage, true, true);
        /** @var Mage_Sales_Model_Order_Shipment_Comment $_comment */
        foreach ($shipment->getCommentsCollection() as $_comment) {
            $_comment->save();
        }
        $shipment->sendUpdateEmail(true, $trackingMessage);
    }
}
