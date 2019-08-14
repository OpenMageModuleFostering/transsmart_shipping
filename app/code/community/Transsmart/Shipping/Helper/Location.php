<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Helper_Location extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieve geo location from the provided details
     * @param $zipcode
     * @param $country
     * @param $city
     * @param $street
     * @param $housenr
     * @return null
     * @throws Mage_Core_Exception
     */
    public function getGeoLocation($zipcode, $country, $city, $street, $housenr)
    {
        $address = urlencode($zipcode . ', ' . $country);
        $url = 'http://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&region=' . $country;

        $ch = curl_init($url);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $responseBody = curl_exec($ch);

        // collect details and close cURL resource
        $curlError = curl_error($ch);
        $httpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // check for cURL failure
        if ($responseBody === false) {
            Mage::throwException('Google GeoCode failure: ' . $curlError);
        }

        // check for unexpected HTTP response code
        if ($httpResponseCode == 200) {
            // try to extract JSON encoded message from response body
            if ($responseBody !== false) {
                try {
                    $response = Zend_Json_Decoder::decode($responseBody);
                    if (isset($response['results']) && count($response['results']) > 0) {
                        if (isset($response['results'][0]['geometry'])) {
                            return $response['results'][0]['geometry']['location'];
                        }

                        return null;
                    }
                }
                catch (Zend_Json_Exception $exception) {
                    Mage::logException($exception);
                }
            }
        }
        else {
            Mage::throwException('Google GeoCode  returned unexpected HTTP response code: ' . $httpResponseCode);
        }

        return null;
    }

    /**
     * Retrieves lookup response
     * @param Mage_Checkout_Model_Cart|Mage_Adminhtml_Model_Sales_Order_Create $model
     * @param Mage_Core_Controller_Request_Http $request
     * @return array
     */
    public function getLookupResponse($model, Mage_Core_Controller_Request_Http $request)
    {
        $response = array();

        try {
            // fetch address parameters
            $street  = $request->getParam('street');
            $housenr = $request->getParam('housenr');
            $zipcode = $request->getParam('zipcode');
            $city    = $request->getParam('city');
            $country = $request->getParam('country');

            // We need the zipcode, city and country or an active cart
            if (empty($zipcode) || empty($city) || empty($country)) {
                // Retrieve the quote
                $quote = $model->getQuote();

                if (empty($quote)) {
                    Mage::throwException($this->__('Missing required parameters'));
                }

                $shippingAddress = $quote->getShippingAddress();

                // We need to have a shipping address
                if (empty($shippingAddress)) {
                    Mage::throwException($this->__('Quote does not contain a shipping address'));
                }

                $zipcode = $shippingAddress->getPostcode();
                $city = $shippingAddress->getCity();
                $country = $shippingAddress->getCountryId();
                $street = $shippingAddress->getStreet(1);

                if (!empty($street) && strlen($street) > 0) {
                    preg_match('#^([^\d]*[^\d\s]) *(\d.*)$#', $street, $match);
                    if (count($match) == 3) {
                        $street = $match[1];
                        $housenr = $match[2];
                    }
                }
                else {
                    $street = null;
                    $housenr = null;
                }
            }

            // Validate everything once more
            if (empty($zipcode) || empty($city) || empty($country)) {
                Mage::throwException($this->__('Zipcode, city and country are required.'));
            }

            // fetch carrier parameters
            $carrier        = $request->getParam('carrier');
            $shippingMethod = $request->getParam('shipping_method');
            $search         = $request->getParam('search');

            if (strlen(trim($search)) > 0) {
                // Otherwise we are looking for <zipcode>,<city>,<street>,<housenumber>
                $searchParts = explode(',', $search);
                $zipcode = isset($searchParts[0]) ? $searchParts[0] : $zipcode;
                $city    = isset($searchParts[1]) ? $searchParts[1] : $city;
                $street  = isset($searchParts[2]) ? $searchParts[2] : $street;
                $housenr = isset($searchParts[3]) ? $searchParts[3] : $housenr;
            }

            if (empty($carrier) && !empty($shippingMethod)) {
                /** @var Transsmart_Shipping_Model_Carrierprofile $carrierprofile */
                $carrierprofile = Mage::getModel('transsmart_shipping/carrierprofile')
                    ->loadByShippingMethodCode($shippingMethod);

                if (!$carrierprofile->isLocationSelectEnabled()) {
                    Mage::throwException($this->__('Location selector not available for this shipping method.'));
                }

                $carrier = $carrierprofile->getCarrierCode();
            }

            $pickupAddress = null;
            if (($quote = $model->getQuote())) {
                $pickupAddress = Mage::helper('transsmart_shipping/pickupaddress')->getPickupAddressFromQuote($quote);
            }

            /** @var Transsmart_Shipping_Helper_Data $helper */
            $helper = Mage::helper('transsmart_shipping');

            /** @var Transsmart_Shipping_Model_Client $client */
            $client = $helper->getApiClient();

            $apiResponse = $client->getLocationSelect($zipcode, $country, $carrier, $city, $street, $housenr);

            // convert API response so it can be used by the JavaScript module
            $locations = array(); $selected = false;
            foreach ($apiResponse as $_item) {
                // convert address fields
                $_location['name']             = $_item['Name'];
                $_location['street']           = $_item['Street'];
                $_location['street_no']        = $_item['StreetNo'];
                $_location['zipcode']          = $_item['Zipcode'];
                $_location['city']             = $_item['City'];
                $_location['country']          = $_item['Country'];
                $_location['phone']            = $_item['Phone'];

                // convert coordinates
                $_location['lat']              = (float)$_item['Latitude'];
                $_location['lng']              = (float)$_item['Longitude'];

                // convert other data
                $_location['servicepoint_id']  = $_item['ServicePoinTID'];

                $_location['selected'] = false;
                if (!$selected && !empty($_location['servicepoint_id']) && $pickupAddress) {
                    if ($pickupAddress->getTranssmartServicepointId() == $_location['servicepoint_id']) {
                        $_location['selected'] = true;
                        $selected = true;
                    }
                }

                // convert opening hours
                $_location['has_openinghours'] = false;
                $_location['openinghours']     = array();
                $_location['is_open_early']    = false;
                $_location['is_open_late']     = false;
                $_location['is_open_sunday']   = false;

                if ($_item['HasOpeningHours']) {
                    foreach ($_item['OpeningHours'] as $_openingHoursItem) {
                        $_convertedOpeningHoursItem = $this->_convertOpeningHoursItem($_openingHoursItem);
                        if ($_convertedOpeningHoursItem) {
                            if ($_convertedOpeningHoursItem['open_morning'] && (
                                $_convertedOpeningHoursItem['open_morning'] < '08:00')) {
                                $_location['is_open_early'] = true;
                            }
                            if ($_convertedOpeningHoursItem['close_afternoon'] > '18:00') {
                                $_location['is_open_late'] = true;
                            }
                            if ($_convertedOpeningHoursItem['day'] == 7 && (
                                $_convertedOpeningHoursItem['open_morning'] ||
                                $_convertedOpeningHoursItem['close_afternoon'])) {
                                $_location['is_open_sunday'] = true;
                            }
                            $_location['openinghours'][] = $_convertedOpeningHoursItem;
                            $_location['has_openinghours'] = true;
                        }
                    }
                }

                // dispatch event so other extensions can update the location
                $_transport = new Varien_Object(array('location' => $_location));
                Mage::dispatchEvent('transsmart_shipping_pickup_location_convert', array(
                    'data'      => $_item,
                    'transport' => $_transport
                ));
                $_location = $_transport->getLocation();

                if ($_location) {
                    $locations[] = $_location;
                }
            }

            if (count($locations) == 0) {
                Mage::throwException($this->__('No pickup locations found.'));
            }

            $response['locations'] = $locations;
            $response['current_location'] = $this->getGeoLocation($zipcode, $country, $city, $street, $housenr);
            $response['result'] = true;
        }
        catch (Mage_Core_Exception $exception) {
            $response['error'] = $exception->getMessage();
            $response['result'] = false;
        }
        catch (Exception $exception) {
            Mage::logException($exception);
            $response['error'] = $this->__('Unknown error');
            $response['result'] = false;
        }

        return $response;
    }

    /**
     * Convert and validate openinghours items.
     *
     * @param array $data
     * @return array
     */
    protected function _convertOpeningHoursItem($data)
    {
        // detect Dutch or English day names or abbreviations
        switch (strtolower(substr($data['Day'], 0, 2))) {
            case 'mo';
            case 'ma';
                $day = 1;
                $dayName = $this->__('Monday');
                break;
            case 'tu';
            case 'di';
                $day = 2;
                $dayName = $this->__('Tuesday');
                break;
            case 'we';
            case 'wo';
                $day = 3;
                $dayName = $this->__('Wednesday');
                break;
            case 'th';
            case 'do';
                $day = 4;
                $dayName = $this->__('Thursday');
                break;
            case 'fr';
            case 'vr';
                $day = 5;
                $dayName = $this->__('Friday');
                break;
            case 'sa';
            case 'za';
                $day = 6;
                $dayName = $this->__('Saturday');
                break;
            case 'su';
            case 'zo';
                $day = 7;
                $dayName = $this->__('Sunday');
                break;
            default:
                return false;
        }

        $result = array(
            'day'             => $day,
            'day_name'        => $dayName,
            'open_morning'    => $this->_convertOpeningHoursTime($data['OpenMorning']),
            'close_morning'   => $this->_convertOpeningHoursTime($data['CloseMorning']),
            'open_afternoon'  => $this->_convertOpeningHoursTime($data['OpenAfternoon']),
            'close_afternoon' => $this->_convertOpeningHoursTime($data['CloseAfternoon'])
        );

        $display = '';
        if ($result['open_morning']) {
            $display .= $result['open_morning'] . ' - ';
            if ($result['close_morning']) {
                $display .= $result['close_morning'];
                if ($result['open_afternoon']) {
                    $display .= ' / ';
                }
            }
        }
        if ($result['open_afternoon']) {
            $display .= $result['open_afternoon'] . ' - ';
        }
        if ($result['close_afternoon']) {
            $display .= $result['close_afternoon'];
        }
        if ($display == '') {
            $display = $this->__('Closed');
        }

        $result['display'] = $display;

        return $result;
    }

    /**
     * Converts and validate times. For example, '08.00' is converted to '08:00'.
     *
     * @param string $time
     * @return null|string
     */
    protected function _convertOpeningHoursTime($time)
    {
        $value = substr(preg_replace('/[^0-9]+/', '', (string)$time), 0, 4);
        if ($value === '' || $value === false) {
            return null;
        }
        $hour = floor(intval($value) / 100);
        $minute = intval($value) % 100;
        if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
            return null;
        }
        return sprintf('%02d:%02d', $hour, $minute);
    }
}
