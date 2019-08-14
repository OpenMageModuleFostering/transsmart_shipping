<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Adminhtml_Transsmart_Shipping_LocationController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Route to lookup locations
     */
    public function lookupAction()
    {
        $model = Mage::getSingleton('adminhtml/sales_order_create');
        $response = Mage::helper('transsmart_shipping/location')->getLookupResponse($model, $this->getRequest());

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(Zend_Json_Encoder::encode($response));
    }

    /**
     * Check if action is allowed for the current user.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        if ($this->getRequest()->getActionName() == 'lookup') {
            return Mage::getSingleton('admin/session')->isAllowed('admin/system/config/transsmart_shipping');
        }
        return false;
    }
}
