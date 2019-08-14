<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_LocationController extends Mage_Core_Controller_Front_Action
{
    /**
     * Route to lookup locations
     */
    public function lookupAction()
    {
        $model = Mage::getSingleton('checkout/cart');
        $response = Mage::helper('transsmart_shipping/location')->getLookupResponse($model, $this->getRequest());

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(Zend_Json_Encoder::encode($response));
    }
}
