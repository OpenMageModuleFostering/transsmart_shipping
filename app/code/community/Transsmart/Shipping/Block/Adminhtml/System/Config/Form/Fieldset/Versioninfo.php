<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Block_Adminhtml_System_Config_Form_Fieldset_Versioninfo
    extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $version = Mage::getConfig()->getNode('modules/Transsmart_Shipping/version');
        $helper = Mage::helper('transsmart_shipping');

        $helpdesk = 'http://transsmart.freshdesk.com/solution/articles/';

        $links = array(
            'Transsmart API'       => 'https://connect.api.transwise.eu/',
            'User Manual'          => $helpdesk . '3000046401-gebruikershandleiding-magento-transsmart-connector',
            'Configuration Manual' => $helpdesk . '3000046402-configuratie-handleiding-magento-transsmart-connector',
            'Transsmart Support'   => 'http://support.transsmart.com/',
        );

        $linksHtml = '';
        foreach ($links as $_title => $_url) {
            $linksHtml .= (($linksHtml != '') ? ' | ' : '')
                        . '<a href="' . $this->escapeHtml($_url) . '" target="_blank">'
                        . $helper->__($_title)
                        . '</a>';
        }

        $html = '<fieldset class="config">'
              . '<p><strong>'
              . $helper->__('Transsmart Shipping extension version %s', $version)
              . '</strong>'
              . '<span style="float:right"><a href="http://www.transsmart.com/" target="_blank">'
              . $helper->__('www.transsmart.com') . '</a></span>'
              . "</p>\n"
              . '<p style="margin-bottom:0">' . $linksHtml . '</p>'
              . '</fieldset>';

        return $html;
    }
}
