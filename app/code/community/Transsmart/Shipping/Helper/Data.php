<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_CONNECTION_USERNAME      = 'transsmart_shipping/connection/username';
    const XML_PATH_CONNECTION_PASSWORD      = 'transsmart_shipping/connection/password';
    const XML_PATH_CONNECTION_ENVIRONMENT   = 'transsmart_shipping/connection/environment';

    const XML_PATH_PRINT_QZHOST             = 'transsmart_shipping/print/qzhost';
    const XML_PATH_PRINT_SELECTEDPRINTER    = 'transsmart_shipping/print/selectedprinter';

    const TRANSSMART_ORDER_STATUS_NOT_APPLICABLE     = 0;
    const TRANSSMART_ORDER_STATUS_PENDING            = 1;
    const TRANSSMART_ORDER_STATUS_PARTIALLY_EXPORTED = 2;
    const TRANSSMART_ORDER_STATUS_EXPORTED           = 3;
    const TRANSSMART_ORDER_STATUS_ERROR              = 4;

    /**
     * @var Transsmart_Shipping_Model_Client
     */
    protected $_apiClient;

    /**|
     * A list of carrier profile ID's that can use the location selector
     * @var array
     */
    protected $_locationSelectCarrierProfiles;

    /**
     * Get the Transsmart API client, and initialize it with the configured authentication details. The same instance
     * will be returned for subsequent calls. Configuration settings are always read from the global scope, as there
     * can be only one Transsmart account per Magento installation.
     *
     * @return Transsmart_Shipping_Model_Client
     */
    public function getApiClient()
    {
        if (is_null($this->_apiClient)) {
            // collect configuration settings
            $username    = Mage::getStoreConfig(self::XML_PATH_CONNECTION_USERNAME, 0);
            $password    = Mage::getStoreConfig(self::XML_PATH_CONNECTION_PASSWORD, 0);
            $environment = Mage::getStoreConfig(self::XML_PATH_CONNECTION_ENVIRONMENT, 0);

            // check if username and password are specified
            if (empty($username)) {
                Mage::throwException('No Transsmart connection username is configured');
            }
            if (empty($password)) {
                Mage::throwException('No Transsmart connection password is configured');
            }

            // use environment setting to determine url
            if ($environment == Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Environment::STAGING) {
                $url = 'https://connect.test.api.transwise.eu';
            }
            elseif ($environment == Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Environment::PRODUCTION) {
                $url = 'https://connect.api.transwise.eu';
            }
            else {
                Mage::throwException('An invalid Transsmart connection environment is configured');
            }

            // instantiate API client model
            $this->_apiClient = Mage::getModel('transsmart_shipping/client', array(
                'url'      => $url,
                'username' => $username,
                'password' => $password
            ));
        }

        return $this->_apiClient;
    }

    /**
     * Get all possible Transsmart order statuses.
     *
     * @return array
     */
    public function getOrderStatuses()
    {
        return array(
            self::TRANSSMART_ORDER_STATUS_NOT_APPLICABLE     => $this->__('N/A'),
            self::TRANSSMART_ORDER_STATUS_PENDING            => $this->__('Pending'),
            self::TRANSSMART_ORDER_STATUS_PARTIALLY_EXPORTED => $this->__('Partially Exported'),
            self::TRANSSMART_ORDER_STATUS_EXPORTED           => $this->__('Exported'),
            self::TRANSSMART_ORDER_STATUS_ERROR              => $this->__('Error'),
        );
    }

    /**
     * Checks the provided shipping method to see if it's a Transsmart shipping method.
     *
     * @param string $shippingMethod
     * @return bool
     */
    public function isTranssmartShippingMethod($shippingMethod)
    {
        return preg_match('/^transsmart(delivery|pickup)_carrierprofile_([0-9]+)$/', $shippingMethod) === 1;
    }

    /**
     * Checks the provided shipping method to see if it's a Transsmart shipping method for pickup.
     *
     * @param string $shippingMethod
     * @return bool
     */
    public function isTranssmartPickup($shippingMethod)
    {
        return preg_match('/^transsmartpickup_carrierprofile_([0-9]+)$/', $shippingMethod) === 1;
    }

    /**
     * Checks the shipping method of the given quote to see if it's a Transsmart shipping method. Also checks if the
     * quote address has a Transsmart carrier profile.
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function isTranssmartQuote($quote)
    {
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress) {
            // check shipping method
            $shippingMethod = $shippingAddress->getShippingMethod();
            if (Mage::helper('transsmart_shipping')->isTranssmartShippingMethod($shippingMethod)) {
                return true;
            }

            // check shipping address carrier profile
            if ($shippingAddress->getTranssmartCarrierprofileId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check the shipping address for the given quote to see if the location selector is enabled (and thus, a pickup
     * address is required).
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param bool $totalsCollected False if $quote->collectTotals() still needs to be called
     * @return bool
     */
    public function isLocationSelectQuote($quote, $totalsCollected = true)
    {
        $enableLocationSelect = false;

        if (($shippingAddress = $quote->getShippingAddress())) {

            $carrierprofileId = null;
            if ($totalsCollected || $quote->getTotalsCollectedFlag()) {
                /** @see Transsmart_Shipping_Model_Sales_Quote_Address_Total_Shipping::collect */
                $carrierprofileId = $shippingAddress->getTranssmartCarrierprofileId();
            }
            else {
                // we can't rely on the shipping address 'transsmart_carrierprofile_id' yet, so use the shipping rate
                if (($rate = $shippingAddress->getShippingRateByCode($shippingAddress->getShippingMethod()))) {
                    $carrierprofileId = $rate->getTranssmartCarrierprofileId();
                }
            }

            if ($carrierprofileId) {
                /** @var Transsmart_Shipping_Model_Carrierprofile $carrierprofile */
                $carrierprofile = Mage::getResourceSingleton('transsmart_shipping/carrierprofile_collection')
                    ->joinCarrier()
                    ->getItemById($carrierprofileId);
                if ($carrierprofile) {
                    $enableLocationSelect = (bool)$carrierprofile->isLocationSelectEnabled();
                }
            }
        }

        return $enableLocationSelect;
    }

    /**
     * Checks the shipping method of the given order to see if it's a Transsmart shipping method. Also checks if the
     * order address has a Transsmart carrier profile.
     * If this logic changes, also update Transsmart_Shipping_Model_Sales_Resource_Order::joinVirtualGridColumnsToSelect
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function isTranssmartOrder($order)
    {
        // check shipping method
        $shippingMethod = $order->getShippingMethod();
        if (Mage::helper('transsmart_shipping')->isTranssmartShippingMethod($shippingMethod)) {
            return true;
        }

        // check shipping address carrier profile
        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress) {
            if ($shippingAddress->getTranssmartCarrierprofileId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieves IDs of carrier profiles that use the location selector.
     *
     * @deprecated
     * @param Mage_Core_Model_Store|int $store
     * @return array
     */
    public function getLocationSelectCarrierProfiles($store = null)
    {
        if (is_null($this->_locationSelectCarrierProfiles)) {
            $this->_locationSelectCarrierProfiles = array();

            $carrierProfiles = Mage::getResourceSingleton('transsmart_shipping/carrierprofile_collection')
                ->joinCarrier();
            foreach ($carrierProfiles as $_carrierProfile) {
                if ($_carrierProfile->isLocationSelectEnabled()) {
                    $this->_locationSelectCarrierProfiles[] = $_carrierProfile->getId();
                }
            }
        }

        return $this->_locationSelectCarrierProfiles;
    }

    /**
     * Translates the provided string with the provided localeCode.
     * This way it is possible to force a translation in a certain language.
     *
     * @param string|array $translationArgs This can either be the string that needs to be localized or
     * an array can be provided which servers as arguments. For example: ["Translation number %s", 1]
     * @param string $localeCode Locale code to translate the string to
     * @param string $moduleName The module name to translate for. When none is provided transsmart_shipping is used.
     * @return string
     */
    public function forceTranslate($translationArgs, $localeCode, $moduleName = null)
    {
        if ($moduleName == null) {
            $moduleName = $this->_getModuleName();
        }

        $translationString = null;

        if (is_array($translationArgs)) {
            $translationString = array_shift($translationArgs);
        }
        else {
            $translationString = $translationArgs;
            $translationArgs = array();
        }

        $origLocaleCode = Mage::app()->getLocale()->getLocaleCode();
        Mage::app()->getLocale()->setLocale($localeCode);

        $translator = Mage::getModel('core/translate')->init('frontend')->setTranslateInline(false);
        $translateExpr = Mage::getModel('core/translate_expr')
            ->setModule($moduleName)
            ->setText($translationString);

        array_unshift($translationArgs, $translateExpr);

        $translatedString = $translator->translate($translationArgs);
        Mage::app()->getLocale()->setLocale($origLocaleCode);

        return $translatedString;
    }

    /**
     * Extract the track-and-trace code from the given URL. Returns FALSE if none is found.
     *
     * @param string $url
     * @return string|FALSE
     */
    public function getTrackingCodeFromUrl($url)
    {
        $code = false;
        if (preg_match_all('/=([A-Z0-9-]+)/si', $url, $matches)) {
            foreach ($matches[1] as $_match) {
                if ($code === false || strlen($_match) > strlen($code)) {
                    $code = $_match;
                }
            }
        }
        return $code;
    }
}
