<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Adminhtml_TranssmartController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Fetch the base data from Transsmart.
     */
    public function fetchAction()
    {
        $error = false;
        try {
            Mage::getModel('transsmart_shipping/sync')->syncBaseData();
        }
        catch (Mage_Core_Exception $exception) {
            $error = $exception->getMessage();
        }
        catch (Exception $exception) {
            Mage::logException($exception);
            $error = Mage::helper('transsmart_shipping')->__('Unknown error');
        }

        /** @var Transsmart_Shipping_Block_Adminhtml_System_Config_Form_Field_Fetch $block */
        $block = $this->getLayout()->createBlock('transsmart_shipping/adminhtml_system_config_form_field_fetch');

        $element = new Varien_Data_Form_Element_Text(array(
            'html_id' => 'transsmart_shipping_fetch'
        ));

        if ($error) {
            $block->setError($error);
        }

        $this->getResponse()->setBody($block->render($element));
    }

    /**
     * Check if action is allowed for the current user.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        if ($this->getRequest()->getActionName() == 'fetch') {
            return Mage::getSingleton('admin/session')->isAllowed('admin/system/config/transsmart_shipping');
        }
        return false;
    }
}
