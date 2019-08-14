<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_System_Config_Form_Field_Fetch
    extends Mage_Adminhtml_Block_Widget
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $time = Mage::getModel('transsmart_shipping/sync')->getLastSync();
        if ($time) {
            $time = new Zend_Date($time);
            $message = $this->__('Base data last fetched on %s.', Mage::helper('core')->formatDate($time, 'long', true));
            $class = 'success-msg';
        }
        else {
            $message = $this->__('Base data not fetched yet.');
            $class = 'error-msg';
        }

        if ($this->getError()) {
            $message .= "\n\n" . $this->getError();
            $class = 'error-msg';
        }

        $this->setElement($element);
        $this->setMessageClass($class);
        $this->setMessageText($message);

        $this->setTemplate('transsmart/shipping/system/config/form/field/fetch.phtml');

        return $this->renderView();
    }

    /**
     * @return bool
     */
    public function useContainer()
    {
        $htmlId = $this->getRequest()->getParam('htmlId');
        if (!empty($htmlId) && preg_match('/^[a-z0-9_]+$/i', $htmlId)) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getHtmlId()
    {
        $htmlId = $this->getRequest()->getParam('htmlId');
        if (!empty($htmlId) && preg_match('/^[a-z0-9_]+$/i', $htmlId)) {
            return $htmlId;
        }
        return $this->getElement()->getHtmlId();
    }

    /**
     * @return bool
     */
    public function showFetchButton()
    {
        // only show 'Fetch Now' button when username and password are configured
        $username = Mage::getStoreConfig(Transsmart_Shipping_Helper_Data::XML_PATH_CONNECTION_USERNAME);
        $password = Mage::getStoreConfig(Transsmart_Shipping_Helper_Data::XML_PATH_CONNECTION_PASSWORD);

        if (strlen($username) == 0 || strlen($password) == 0) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getFetchUrl()
    {
        return $this->getUrl('*/transsmart/fetch', array('htmlId' => $this->getHtmlId()));
    }

    /**
     * Retrieve 'Fetch Now' button HTML
     *
     * @return string
     */
    public function getFetchButtonHtml()
    {
        $htmlId = $this->getHtmlId();

        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => $this->__('Fetch Now'),
                'onclick' => "transsmart_fetchBaseData('$htmlId'); return false;",
                'class'   => 'go',
                'id'      => $htmlId . '_button'
            ));

        return $button->toHtml();
    }
}
