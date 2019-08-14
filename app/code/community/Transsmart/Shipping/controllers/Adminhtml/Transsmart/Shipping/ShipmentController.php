<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Adminhtml_Transsmart_Shipping_ShipmentController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Mass Create Shipment Action
     */
    public function massCreateAction()
    {
        if ($this->getRequest()->isPost()) {
            // redirect post request to prevent 'confirm submit' browser message when using back-button
            $orderIds = $this->getRequest()->getPost('order_ids');
            $this->_redirect('*/*/*', array('order_ids' => implode(',', $orderIds)));
            return;
        }

        // get order ID's from URL
        $orderIds = $this->getRequest()->getParam('order_ids');
        if (is_string($orderIds)) {
            $orderIds = explode(',', $orderIds);
        }
        $orderIds = array_filter($orderIds, 'intval');

        /** @var Mage_Sales_Model_Resource_Order_Collection $orderCollection */
        $orderCollection = $this->_getMassActionOrderCollection($orderIds);
        if (count($orderCollection) == 0) {
            $this->_getSession()->addError($this->__('No shipments can be created for the selected order(s).'));
            $this->_redirect('*/sales_order');
            return;
        }

        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb(Mage::helper('sales')->__('Sales'), Mage::helper('sales')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('sales')->__('Orders'), Mage::helper('sales')->__('Orders'))
            ->_title(Mage::helper('sales')->__('Sales'))->_title(Mage::helper('sales')->__('Orders'))
            ->_title($this->__('Create New Shipment(s) for Order(s)'));

        $this->getLayout()->getBlock('sales_shipment_create')->setOrderCollection($orderCollection);
        $this->renderLayout();
    }

    /**
     * Mass Create Shipment Save Action
     */
    public function massCreateSaveAction()
    {
        $orderQty = $this->getRequest()->getPost('order_qty');
        $data     = $this->getRequest()->getPost('shipment');

        /** @var Mage_Sales_Model_Resource_Order_Collection $orderCollection */
        $orderCollection = $this->_getMassActionOrderCollection(array_keys($orderQty));

        // check if qty's have changed since the form was loaded
        $totalQty = 0.0;
        foreach ($orderCollection as $_order) {
            foreach ($_order->getAllItems() as $_item) {
                if ($_item->getQtyToShip() > 0 && !$_item->getIsVirtual() && !$_item->getLockedDoShip()) {
                    $totalQty += $_item->getQtyToShip();
                }
            }
        }
        if (count($orderCollection) != count($orderQty) || $totalQty != array_sum($orderQty)) {
            $this->_getSession()->addWarning($this->__('One or more selected orders have changed. Please confirm.'));
            $this->_redirect('*/*/massCreate', array('order_ids' => implode(',', $orderCollection->getAllIds())));
            return;
        }

        // create shipments
        $successCount = 0; $errorCount = 0;
        foreach ($orderCollection as $_order) {
            try {
                // create and register shipment
                $_shipment = Mage::getModel('sales/service_order', $_order)->prepareShipment();
                $_shipment->register();

                // add comment (notification)
                $_comment = '';
                if (!empty($data['comment_text'])) {
                    $_shipment->addComment(
                        $data['comment_text'],
                        !empty($data['comment_customer_notify'])
                    );
                    if (!empty($data['comment_customer_notify'])) {
                        $_comment = $data['comment_text'];
                    }
                }
                if (!empty($data['send_email'])) {
                    $_shipment->setEmailSent(true);
                }

                // update order
                $_order->setCustomerNoteNotify(!empty($data['send_email']))
                    ->setIsInProcess(true);

                /** @see Transsmart_Shipping_Model_Adminhtml_Observer::salesOrderShipmentSaveAfter */
                $_shipment->setData('transsmart_prevent_export_on_save', true);

                // save changes to the shipment and order into database
                Mage::getModel('core/resource_transaction')
                    ->addObject($_shipment)
                    ->addObject($_order)
                    ->save();

                $_shipment->sendEmail(!empty($data['send_email']), $_comment);

                $successCount++;
            }
            catch (Mage_Core_Exception $exception) {
                $this->_getSession()->addError(
                    $this->__(
                        'Error creating shipment for order #%s: %s',
                        $_order->getIncrementId(),
                        $exception->getMessage()
                    )
                );
                $errorCount++;
            }
        }
        if ($successCount > 0) {
            $this->_getSession()->addSuccess($this->__('Successfully created %s shipment(s)!', $successCount));

            Mage::helper('transsmart_shipping/shipment')->doMassExport();
        }

        // redirect user
        if ($errorCount > 0) {
            $this->_redirect('*/*/massCreate', array('order_ids' => implode(',', $orderCollection->getAllIds())));
        }
        else {
            $this->_redirect('*/sales_order');
        }
    }

    /**
     * Get selection of orders for which shipments may to be created. Used by massCreateAction and massCreateSaveAction.
     *
     * @param array $orderIds
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    protected function _getMassActionOrderCollection($orderIds)
    {
        /** @var Mage_Sales_Model_Resource_Order_Collection $orderCollection */
        $orderCollection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds));

        Mage::dispatchEvent('transsmart_shipping_shipment_masscreate_prepare_collection', array(
            'collection' => $orderCollection
        ));

        // remove orders that cannot be shipped
        foreach ($orderCollection as $_key => $_order) {
            if ($_order->getForcedDoShipmentWithInvoice() || !$_order->canShip()) {
                $orderCollection->removeItemByKey($_key);
            }
        }

        return $orderCollection;
    }

    /**
     * Book and print selected Transsmart shipments.
     */
    public function massBookAndPrintAction()
    {
        $shipmentCollection = $this->_getMassActionShipmentCollection();
        $shipmentHelper = Mage::helper('transsmart_shipping/shipment');

        $totalCount = 0;
        $successCount = 0;
        if ($shipmentCollection) {
            try {
                $shipmentHelper->doMassBookAndPrint($shipmentCollection);
            }
            catch (Mage_Core_Exception $_exception) {
                $this->_getSession()->addError(
                    $this->__(
                        'One or more shipments could not be booked and printed: %s',
                        $_exception->getMessage()
                    )
                );
            }

            /** @var Mage_Sales_Model_Order_Shipment $_shipment */
            foreach ($shipmentCollection as $_shipment) {
                // check if Transsmart shipping labels have been printed
                if ($_shipment->getTranssmartDocumentId()) {
                    $totalCount++;

                    if ($_shipment->getTranssmartStatus() == 'LABL') {
                        $successCount++;
                    }
                    else {
                        $_shipmentError = $_shipment->getTranssmartShipmentError();
                        if (empty($_shipmentError)) {
                            $_shipmentError = $this->__('Unknown error');
                        }

                        $this->_getSession()->addError(
                            $this->__(
                                'Shipment #%s for order #%s could not be booked and printed: %s',
                                $_shipment->getIncrementId(),
                                $_shipment->getOrder()->getIncrementId(),
                                $_shipmentError
                            )
                        );
                    }
                }
            }
        }

        if ($totalCount == 0) {
            if ($this->_isMassActionFromOrders()) {
                $this->_getSession()->addError(
                    $this->__('There are no Transsmart documents related to selected order(s).')
                );
            }
            else {
                $this->_getSession()->addError(
                    $this->__('There are no Transsmart documents related to selected shipment(s).')
                );
            }
        }
        elseif ($successCount) {
            $this->_getSession()->addSuccess(
                $this->__('Successfully booked and printed %s Transsmart shipments.', $successCount)
            );
        }

        if ($this->_isMassActionFromOrders()) {
            $this->_redirect('*/sales_order/index');
        }
        else {
            $this->_redirect('*/sales_order_shipment/index');
        }
    }

    /**
     * Book all selected Transsmart shipments.
     */
    public function massBookingAction()
    {
        $shipmentCollection = $this->_getMassActionShipmentCollection();
        $shipmentHelper = Mage::helper('transsmart_shipping/shipment');

        $totalCount = 0;
        $successCount = 0;
        if ($shipmentCollection) {
            /** @var Mage_Sales_Model_Order_Shipment $_shipment */
            foreach ($shipmentCollection as $_shipment) {
                if (!$_shipment->getTranssmartDocumentId()) {
                    continue;
                }

                try {
                    $totalCount++;
                    $shipmentHelper->doBooking($_shipment);
                    $successCount++;
                }
                catch (Mage_Core_Exception $_exception) {
                    $this->_getSession()->addError(
                        $this->__(
                            'Shipment #%s for order #%s could not be booked: %s',
                            $_shipment->getIncrementId(),
                            $_shipment->getOrder()->getIncrementId(),
                            $_exception->getMessage()
                        )
                    );
                }
            }
        }

        if ($totalCount == 0) {
            if ($this->_isMassActionFromOrders()) {
                $this->_getSession()->addError(
                    $this->__('There are no Transsmart documents related to selected order(s).')
                );
            }
            else {
                $this->_getSession()->addError(
                    $this->__('There are no Transsmart documents related to selected shipment(s).')
                );
            }
        }
        elseif ($successCount) {
            $this->_getSession()->addSuccess(
                $this->__('Successfully booked %s Transsmart shipments.', $successCount)
            );
        }

        if ($this->_isMassActionFromOrders()) {
            $this->_redirect('*/sales_order/index');
        }
        else {
            $this->_redirect('*/sales_order_shipment/index');
        }
    }

    /**
     * Print (create labels for) all selected Transsmart shipments.
     * In addition, this also works for non-Transsmart shipments, by calling getShippingLabel.
     * The combined PDF document with all shipping labels will be downloaded to user browser.
     * @see Mage_Adminhtml_Sales_Order_ShipmentController::massPrintShippingLabelAction
     */
    public function massLabelAction()
    {
        $shipmentCollection = $this->_getMassActionShipmentCollection();
        $shipmentHelper = Mage::helper('transsmart_shipping/shipment');

        $pdfLabels = array();
        $totalCount = 0;
        $successCount = 0;
        if ($shipmentCollection) {
            try {
                $shipmentHelper->doMassLabel($shipmentCollection);
            }
            catch (Mage_Core_Exception $_exception) {
                $this->_getSession()->addError(
                    $this->__(
                        'One ore more shipments could not be printed: %s',
                        $_exception->getMessage()
                    )
                );
            }

            /** @var Mage_Sales_Model_Order_Shipment $_shipment */
            foreach ($shipmentCollection as $_shipment) {
                // check if Transsmart shipping labels have been printed
                if ($_shipment->getTranssmartDocumentId()) {
                    $totalCount++;

                    if ($_shipment->getTranssmartStatus() == 'LABL') {
                        $successCount++;
                    }
                    else {
                        $_shipmentError = $_shipment->getTranssmartShipmentError();
                        if (empty($_shipmentError)) {
                            $_shipmentError = $this->__('Unknown error');
                        }

                        $this->_getSession()->addError(
                            $this->__(
                                'Shipment #%s for order #%s could not be printed: %s',
                                $_shipment->getIncrementId(),
                                $_shipment->getOrder()->getIncrementId(),
                                $_shipmentError
                            )
                        );
                    }
                }

                // get original Magento shipping labels
                $_labelContent = $_shipment->getShippingLabel();
                if ($_labelContent) {
                    $totalCount++;
                    $pdfLabels[] = $_labelContent;
                }
            }
        }

        if ($totalCount == 0) {
            if ($this->_isMassActionFromOrders()) {
                $this->_getSession()->addError(
                    $this->__('There are no Transsmart documents or shipping labels related to selected order(s).')
                );
            }
            else {
                $this->_getSession()->addError(
                    $this->__('There are no Transsmart documents or shipping labels related to selected shipment(s).')
                );
            }
        }
        elseif ($successCount) {
            $this->_getSession()->addSuccess(
                $this->__('Successfully printed %s Transsmart shipments.', $successCount)
            );
        }

        // process Magento shipping labels
        if (!empty($pdfLabels)) {
            $outputPdf = $this->_combineLabelsPdf($pdfLabels);
            $this->_prepareDownloadResponse('ShippingLabels.pdf', $outputPdf, 'application/pdf');
            return;
        }

        if ($this->_isMassActionFromOrders()) {
            $this->_redirect('*/sales_order/index');
        }
        else {
            $this->_redirect('*/sales_order_shipment/index');
        }
    }

    /**
     * Get the shipments to which the mass action should be applied, or NULL if none are selected.
     *
     * @return Mage_Sales_Model_Resource_Order_Shipment_Collection|null
     */
    protected function _getMassActionShipmentCollection()
    {
        $request = $this->getRequest();
        $shipmentCollection = null;
        if ($request->has('shipment_ids')) {
            $ids = $request->getParam('shipment_ids');
            array_filter($ids, 'intval');
            if (!empty($ids)) {
                $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
                    ->addFieldToFilter('entity_id', array('in' => $ids));
            }
        }
        elseif ($request->has('order_ids')) {
            $ids = $request->getParam('order_ids');
            array_filter($ids, 'intval');
            if (!empty($ids)) {
                $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
                    ->setOrderFilter(array('in' => $ids));
            }
        }

        return $shipmentCollection;
    }

    /**
     * Return TRUE if the mass action should redirect back to the orders grid, or FALSE for the shipments grid.
     *
     * @return bool
     */
    protected function _isMassActionFromOrders()
    {
        $ids = $this->getRequest()->getParam('order_ids');
        $fromOrders = !empty($ids);
        return $fromOrders;
    }

    /**
     * Combine array of labels as instance PDF
     *
     * @see Mage_Adminhtml_Sales_Order_ShipmentController::_combineLabelsPdf
     * @param array $labelsContent
     * @return Zend_Pdf
     */
    protected function _combineLabelsPdf(array $labelsContent)
    {
        $outputPdf = new Zend_Pdf();
        foreach ($labelsContent as $_content) {
            if (stripos($_content, '%PDF-') !== false) {
                if (count($labelsContent) == 1) {
                    // only one PDF, no need to combine so just return it
                    return $_content;
                }

                $_contentPdf = Zend_Pdf::parse($_content);
                foreach ($_contentPdf->pages as $_page) {
                    $outputPdf->pages[] = clone $_page;
                }
            }
            else {
                $_page = $this->_createPdfPageFromImageString($_content);
                if ($_page) {
                    $outputPdf->pages[] = $_page;
                }
            }
        }
        return $outputPdf->render();
    }

    /**
     * Create Zend_Pdf_Page instance with image from $imageString. Supports JPEG, PNG, GIF, WBMP, and GD2 formats.
     *
     * @see Mage_Adminhtml_Sales_Order_ShipmentController::_createPdfPageFromImageString
     * @param string $imageString
     * @return Zend_Pdf_Page|bool
     */
    protected function _createPdfPageFromImageString($imageString)
    {
        $image = imagecreatefromstring($imageString);
        if (!$image) {
            return false;
        }

        $xSize = imagesx($image);
        $ySize = imagesy($image);
        $page = new Zend_Pdf_Page($xSize, $ySize);

        imageinterlace($image, 0);
        $tmpFileName = sys_get_temp_dir() . DS . 'shipping_labels_' . uniqid(mt_rand()) . time() . '.png';
        imagepng($image, $tmpFileName);
        $pdfImage = Zend_Pdf_Image::imageWithPath($tmpFileName);
        $page->drawImage($pdfImage, 0, 0, $xSize, $ySize);
        unlink($tmpFileName);

        return $page;
    }

    /**
     * Check if action is allowed for the current user.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/shipment');
    }
}
