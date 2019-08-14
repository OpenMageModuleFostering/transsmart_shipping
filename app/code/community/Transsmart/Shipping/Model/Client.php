<?php

/**
 * Transsmart API client.
 *
 * @see         http://www.odata.org/documentation/odata-version-2-0/uri-conventions/#FilterSystemQueryOption
 *
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Client
{
    /**
     * @var string
     */
    protected $_url;

    /**
     * @var string
     */
    protected $_username;

    /**
     * @var string
     */
    protected $_password;

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct($options)
    {
        $this->_url      = isset($options['url']     ) ? $options['url']      : null;
        $this->_username = isset($options['username']) ? $options['username'] : null;
        $this->_password = isset($options['password']) ? $options['password'] : null;
    }

    /**
     * Wrapper for curl_init and curl_exec, which also performs error checking and logging. Returns the response body.
     *
     * @param string $method API method (e.g.: "/Api/MethodName")
     * @param string|null $requestBody This is usually a JSON encoded string
     * @return string This is usually a JSON encoded string
     * @throws Mage_Core_Exception
     */
    protected function _curlExec($method, $requestBody = null)
    {
        $curlOptions = array(
            // SSL credentials
            CURLOPT_CAINFO         => dirname(dirname(__FILE__)) . DS . 'ssl' . DS . 'CARoot.crt',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            // HTTP authentication
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_USERPWD        => $this->_username . ':' . $this->_password,
            // HTTP request
            CURLOPT_URL            => $this->_url . $method,
            CURLOPT_HTTPHEADER     => array(
                                          'User-Agent: ' . sprintf(
                                              'Magento/%s Transsmart_Shipping/%s',
                                              Mage::getVersion(),
                                              (string)Mage::getConfig()->getNode('modules/Transsmart_Shipping/version')
                                          ),
                                          'Accept: application/json',
                                      ),
            // connection settings
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 10,
        );

        if (!is_null($requestBody)) {
            $curlOptions[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
            $curlOptions[CURLOPT_POST]         = true;
            $curlOptions[CURLOPT_POSTFIELDS]   = $requestBody;
        }

        // initialize cURL
        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);

        // execute the request
        $time = microtime(true);
        $responseBody = curl_exec($ch);
        $time = microtime(true) - $time;

        // write request details and response to log
        Mage::log(sprintf(
            "REQUEST: %s\nREQUEST BODY: %s\nDURATION: %0.4f sec\nRESPONSE CODE: %s\nRESPONSE BODY: %s",
            rtrim(curl_getinfo($ch, CURLINFO_HEADER_OUT)),
            $requestBody,
            $time,
            curl_getinfo($ch, CURLINFO_HTTP_CODE),
            $responseBody
        ), Zend_Log::DEBUG, 'transsmart_shipping.log');

        // collect details and close cURL resource
        $curlError = curl_error($ch);
        $httpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // check for cURL failure
        if ($responseBody === false) {
            Mage::throwException('Transsmart API connection failure: ' . $curlError);
        }

        // check for unexpected HTTP response code
        if ($httpResponseCode < 200 || $httpResponseCode >= 300) {
            // try to extract JSON encoded message from response body
            $message = false;
            if ($responseBody !== false) {
                $message = str_replace(array("\r", "\n"), ' ', strip_tags($responseBody));
                try {
                    $response = Zend_Json_Decoder::decode($responseBody);
                    if (is_string($response)) {
                        $message = $response;
                    }
                    elseif (is_array($response) && isset($response['Message'])) {
                        $message = $response['Message'];
                    }
                }
                catch (Zend_Json_Exception $exception) {
                    Mage::logException($exception);
                }
            }

            if ($message) {
                Mage::throwException('Transsmart API error: ' . $message);
            }
            else {
                Mage::throwException('Transsmart API returned unexpected HTTP response code: ' . $httpResponseCode);
            }
        }

        return $responseBody;
    }

    /**
     * Get configured carriers.
     *
     * @param string $filter OData filter argument
     * @return array
     * @throws Zend_Json_Exception
     */
    public function getCarrier($filter = null)
    {
        $query = '';
        if (!is_null($filter)) {
            $query = '?' . http_build_query(array('$filter' => $filter));
        }

        $responseBody = $this->_curlExec('/Api/Carrier' . $query);

        $response = Zend_Json_Decoder::decode($responseBody);

        return $response;
    }

    /**
     * Get configured time service levels.
     *
     * @param string $filter OData filter argument
     * @return array
     * @throws Zend_Json_Exception
     */
    public function getServiceLevelTime($filter = null)
    {
        $query = '';
        if (!is_null($filter)) {
            $query = '?' . http_build_query(array('$filter' => $filter));
        }

        $responseBody = $this->_curlExec('/Api/ServiceLevelTime' . $query);

        $response = Zend_Json_Decoder::decode($responseBody);

        return $response;
    }

    /**
     * Get configured other service levels.
     *
     * @param string $filter OData filter argument
     * @return array
     * @throws Zend_Json_Exception
     */
    public function getServiceLevelOther($filter = null)
    {
        $query = '';
        if (!is_null($filter)) {
            $query = '?' . http_build_query(array('$filter' => $filter));
        }

        $responseBody = $this->_curlExec('/Api/ServiceLevelOther' . $query);

        $response = Zend_Json_Decoder::decode($responseBody);

        return $response;
    }

    /**
     * Get configured carrier profiles.
     *
     * @param string $filter OData filter argument
     * @return array
     * @throws Zend_Json_Exception
     */
    public function getCarrierProfile($filter = null)
    {
        $query = '';
        if (!is_null($filter)) {
            $query = '?' . http_build_query(array('$filter' => $filter));
        }

        $responseBody = $this->_curlExec('/Api/CarrierProfile' . $query);

        $response = Zend_Json_Decoder::decode($responseBody);

        return $response;
    }

    /**
     * Get configured shipment locations.
     *
     * @param string $filter OData filter argument
     * @return array
     * @throws Zend_Json_Exception
     */
    public function getShipmentLocation($filter = null)
    {
        $query = '';
        if (!is_null($filter)) {
            $query = '?' . http_build_query(array('$filter' => $filter));
        }

        $responseBody = $this->_curlExec('/Api/ShipmentLocation' . $query);

        $response = Zend_Json_Decoder::decode($responseBody);

        return $response;
    }

    /**
     * Get configured email types.
     *
     * @param string $filter OData filter argument
     * @return array
     * @throws Zend_Json_Exception
     */
    public function getEmailType($filter = null)
    {
        $query = '';
        if (!is_null($filter)) {
            $query = '?' . http_build_query(array('$filter' => $filter));
        }

        $responseBody = $this->_curlExec('/Api/EmailType' . $query);

        $response = Zend_Json_Decoder::decode($responseBody);

        return $response;
    }

    /**
     * Get configured incoterms.
     *
     * @param string $filter OData filter argument
     * @return array
     * @throws Zend_Json_Exception
     */
    public function getIncoterm($filter = null)
    {
        $query = '';
        if (!is_null($filter)) {
            $query = '?' . http_build_query(array('$filter' => $filter));
        }

        $responseBody = $this->_curlExec('/Api/Incoterm' . $query);

        $response = Zend_Json_Decoder::decode($responseBody);

        return $response;
    }

    /**
     * Get configured cost centers.
     *
     * @param string $filter OData filter argument
     * @return array
     * @throws Zend_Json_Exception
     */
    public function getCostcenter($filter = null)
    {
        $query = '';
        if (!is_null($filter)) {
            $query = '?' . http_build_query(array('$filter' => $filter));
        }

        $responseBody = $this->_curlExec('/Api/Costcenter' . $query);

        $response = Zend_Json_Decoder::decode($responseBody);

        return $response;
    }

    /**
     * Get configured package types.
     *
     * @param string $filter OData filter argument
     * @return array
     * @throws Zend_Json_Exception
     */
    public function getPackage($filter = null)
    {
        $query = '';
        if (!is_null($filter)) {
            $query = '?' . http_build_query(array('$filter' => $filter));
        }

        $responseBody = $this->_curlExec('/Api/Package' . $query);

        $response = Zend_Json_Decoder::decode($responseBody);

        return $response;
    }

    /**
     * @param string $zipcode
     * @param string $country
     * @param string $carrier
     * @param string $city
     * @param string $street
     * @param string $housenr
     * @return array
     * @throws Zend_Json_Exception
     */
    public function getLocationSelect($zipcode, $country, $carrier, $city, $street, $housenr)
    {
        $query = '?' . http_build_query(array(
            'Zipcode' => $zipcode,
            'Country' => $country,
            'Carrier' => $carrier,
            'City'    => $city,
            'Street'  => $street,
            'HouseNr' => $housenr
        ));

        $responseBody = $this->_curlExec('/Api/LocationSelect' . $query);

        $response = Zend_Json_Decoder::decode($responseBody);

        return $response;
    }

    /**
     * Get documents.
     *
     * @param string $id Document id (optional)
     * @return array
     * @throws Zend_Json_Exception
     */
    public function getDocument($id = null)
    {
        $query = '';
        if (!is_null($id)) {
            $query = '/' . (string)$id;
        }

        $responseBody = $this->_curlExec('/Api/Document' . $query);

        $response = Zend_Json_Decoder::decode($responseBody);

        return $response;
    }

    /**
     * Get shipment status info.
     *
     * @param array $queryDefinition
     * @return array
     * @throws Zend_Json_Exception
     */
    public function getStatus($queryDefinition)
    {
        $responseBody = $this->_curlExec('/Api/Status', Zend_Json_Encoder::encode($queryDefinition));

        $response = Zend_Json_Decoder::decode($responseBody);

        return $response;
    }

    /**
     * Create a new document.
     *
     * @param string|array $referenceOrDocument Referentie van het adres of het complete document als array
     * @param int|null $carrierId Carrier van het document
     * @param int|null $serviceLevelTimeId Tijd servicelevel van het document
     * @param int|null $serviceLevelOtherId Overig servicelevel van het document
     * @param int|null $shipmentLocationId Verzendlocatie van het document
     * @param int|null $mailTypeId Mailtype van het document
     * @param int|null $incotermId Incoterm van het document
     * @param string|null $serviceType Servicetype van het document
     * @param int|null $costCenterId Kostenplaats van het document
     * @param float|null $spotPrice Spotprijs van het document
     * @param float|null $codCosts Rembourskosten van het document
     * @param float|null $loadMeter Laadmeter van het document
     * @param string|null $preferredPickupDateFrom Voorkeursophaaldatum (van) van het document '2015-10-22T08:00:00'
     * @param string|null $preferredPickupDateTo Voorkeursophaaldatum (tot) van het document 'yyyy-mm-ddThh:mm:ss'
     * @param string|null $preferredDeliveryDateFrom Afleverdatum (van) van het document 'yyyy-mm-ddThh:mm:ss'
     * @param string|null $preferredDeliveryDateTo Afleverdatum (tot) van het document 'yyyy-mm-ddThh:mm:ss'
     * @param string|null $refInvoice Factuursreferentie van het document
     * @param string|null $refCustomerOrder Klantorderreferentie van het document
     * @param string|null $refOrder Orderreferentie van het document
     * @param string|null $refDeliveryNote Pakbonreferentie van het document
     * @param string|null $refDeliveryId Leveringsnummerreferentie van het document
     * @param string|null $refOther Overige referentie van het document
     * @param string|null $refServicePoint Servicepoint referentie van het document
     * @param string|null $refProject Projectreferentie van het document
     * @param string|null $refYourReference Eigen referentie van het document
     * @param string|null $refEngineer Engineersreferentie van het document
     * @param string|null $addressName Naam van het afleveradres
     * @param string|null $addressStreet Straat van het afleveradres
     * @param string|null $addressStreetNo Huisnummer van het afleveradres
     * @param string|null $addressZipcode Postcode van het afleveradres
     * @param string|null $addressCity Plaats van het afleveradres
     * @param string|null $addressState Staat van het afleveradres
     * @param string|null $addressCountry Land van het afleveradres
     * @param string|null $addressContact Contactpersoon van het afleveradres
     * @param string|null $addressPhone Telefoonnummer van het afleveradres
     * @param string|null $addressFax Faxnummer van het afleveradres
     * @param string|null $addressEmail Emailadres van het afleveradres
     * @param string|null $addressAccountNo Accountnummer van het afleveradres
     * @param string|null $addressCustomerNo Klantnummer van het afleveradres
     * @param string|null $addressVatNumber BTW Nummer van het afleveradres
     * @param string|null $addressNamePickup Naam van het ophaaladres
     * @param string|null $addressStreetPickup Straat van het ophaaladres
     * @param string|null $addressStreetNoPickup Huisnummer van het ophaaladres
     * @param string|null $addressZipcodePickup Postcode van het ophaaladres
     * @param string|null $addressCityPickup Plaats van het ophaaladres
     * @param string|null $addressStatePickup Staat van het ophaaladres
     * @param string|null $addressCountryPickup Land van het ophaaladres
     * @param string|null $addressContactPickup Contactpersoon van het ophaaladres
     * @param string|null $addressPhonePickup Telefoonnummer van het ophaaladres
     * @param string|null $addressFaxPickup Faxnummer van het ophaaladres
     * @param string|null $addressEmailPickup Emailadres van het ophaaladres
     * @param string|null $addressAccountNoPickup Accountnummer van het ophaaladres
     * @param string|null $addressCustomerNoPickup Klantnummer van het ophaaladres
     * @param string|null $addressVatNumberPickup BTN Nummer van het ophaaladres
     * @param string|null $addressNameInvoice Naam van het factuuradres
     * @param string|null $addressStreetInvoice Staat van het factuuradres
     * @param string|null $addressStreetNoInvoice Huisnummer van het factuuradres
     * @param string|null $addressZipcodeInvoice Postcode van het factuuradres
     * @param string|null $addressCityInvoice Plaats van het factuuradres
     * @param string|null $addressStateInvoice Staat van het factuuradres
     * @param string|null $addressCountryInvoice Land van het factuuradres
     * @param string|null $addressContactInvoice Contactpersoon van het factuuradres
     * @param string|null $addressPhoneInvoice Telefoonnummer van het factuuradres
     * @param string|null $addressFaxInvoice Faxnummer van het factuuradres
     * @param string|null $addressEmailInvoice Emailadres van het factuuradres
     * @param string|null $addressAccountNoInvoice Accountnummer van het factuuradres
     * @param string|null $addressCustomerNoInvoice Klantnummer van het factuuradres
     * @param string|null $addressVatNumberInvoice BTW Nummer van het factuuradres
     * @return mixed
     * @throws Zend_Json_Exception
     */
    public function createDocument(
        $referenceOrDocument,
        $carrierId = null,
        $serviceLevelTimeId = null,
        $serviceLevelOtherId = null,
        $shipmentLocationId = null,
        $mailTypeId = null,
        $incotermId = null,
        $serviceType = null,
        $costCenterId = null,
        $spotPrice = null,
        $codCosts = null,
        $loadMeter = null,
        $preferredPickupDateFrom = null,
        $preferredPickupDateTo = null,
        $preferredDeliveryDateFrom = null,
        $preferredDeliveryDateTo = null,
        $refInvoice = null,
        $refCustomerOrder = null,
        $refOrder = null,
        $refDeliveryNote = null,
        $refDeliveryId = null,
        $refOther = null,
        $refServicePoint = null,
        $refProject = null,
        $refYourReference = null,
        $refEngineer = null,
        $addressName = null,
        $addressStreet = null,
        $addressStreetNo = null,
        $addressZipcode = null,
        $addressCity = null,
        $addressState = null,
        $addressCountry = null,
        $addressContact = null,
        $addressPhone = null,
        $addressFax = null,
        $addressEmail = null,
        $addressAccountNo = null,
        $addressCustomerNo = null,
        $addressVatNumber = null,
        $addressNamePickup = null,
        $addressStreetPickup = null,
        $addressStreetNoPickup = null,
        $addressZipcodePickup = null,
        $addressCityPickup = null,
        $addressStatePickup = null,
        $addressCountryPickup = null,
        $addressContactPickup = null,
        $addressPhonePickup = null,
        $addressFaxPickup = null,
        $addressEmailPickup = null,
        $addressAccountNoPickup = null,
        $addressCustomerNoPickup = null,
        $addressVatNumberPickup = null,
        $addressNameInvoice = null,
        $addressStreetInvoice = null,
        $addressStreetNoInvoice = null,
        $addressZipcodeInvoice = null,
        $addressCityInvoice = null,
        $addressStateInvoice = null,
        $addressCountryInvoice = null,
        $addressContactInvoice = null,
        $addressPhoneInvoice = null,
        $addressFaxInvoice = null,
        $addressEmailInvoice = null,
        $addressAccountNoInvoice = null,
        $addressCustomerNoInvoice = null,
        $addressVatNumberInvoice = null
    )
    {
        if (is_array($referenceOrDocument)) {
            $document = $referenceOrDocument;
        }
        else {
            $document = array(
                'Reference'                 => $referenceOrDocument,
                'CarrierId'                 => $carrierId,
                'ServiceLevelTimeId'        => $serviceLevelTimeId,
                'ServiceLevelOtherId'       => $serviceLevelOtherId,
                'ShipmentLocationId'        => $shipmentLocationId,
                'MailTypeId'                => $mailTypeId,
                'IncotermId'                => $incotermId,
                'ServiceType'               => $serviceType,
                'CostCenterId'              => $costCenterId,
                'SpotPrice'                 => $spotPrice,
                'CodCosts'                  => $codCosts,
                'LoadMeter'                 => $loadMeter,
                'PreferredPickupDateFrom'   => $preferredPickupDateFrom,
                'PreferredPickupDateTo'     => $preferredPickupDateTo,
                'PreferredDeliveryDateFrom' => $preferredDeliveryDateFrom,
                'PreferredDeliveryDateTo'   => $preferredDeliveryDateTo,
                'RefInvoice'                => $refInvoice,
                'RefCustomerOrder'          => $refCustomerOrder,
                'RefOrder'                  => $refOrder,
                'RefDeliveryNote'           => $refDeliveryNote,
                'RefDeliveryId'             => $refDeliveryId,
                'RefOther'                  => $refOther,
                'RefServicePoint'           => $refServicePoint,
                'RefProject'                => $refProject,
                'RefYourReference'          => $refYourReference,
                'RefEngineer'               => $refEngineer,
                'AddressName'               => $addressName,
                'AddressStreet'             => $addressStreet,
                'AddressStreetNo'           => $addressStreetNo,
                'AddressZipcode'            => $addressZipcode,
                'AddressCity'               => $addressCity,
                'AddressState'              => $addressState,
                'AddressCountry'            => $addressCountry,
                'AddressContact'            => $addressContact,
                'AddressPhone'              => $addressPhone,
                'AddressFax'                => $addressFax,
                'AddressEmail'              => $addressEmail,
                'AddressAccountNo'          => $addressAccountNo,
                'AddressCustomerNo'         => $addressCustomerNo,
                'AddressVatNumber'          => $addressVatNumber,
                'AddressNamePickup'         => $addressNamePickup,
                'AddressStreetPickup'       => $addressStreetPickup,
                'AddressStreetNoPickup'     => $addressStreetNoPickup,
                'AddressZipcodePickup'      => $addressZipcodePickup,
                'AddressCityPickup'         => $addressCityPickup,
                'AddressStatePickup'        => $addressStatePickup,
                'AddressCountryPickup'      => $addressCountryPickup,
                'AddressContactPickup'      => $addressContactPickup,
                'AddressPhonePickup'        => $addressPhonePickup,
                'AddressFaxPickup'          => $addressFaxPickup,
                'AddressEmailPickup'        => $addressEmailPickup,
                'AddressAccountNoPickup'    => $addressAccountNoPickup,
                'AddressCustomerNoPickup'   => $addressCustomerNoPickup,
                'AddressVatNumberPickup'    => $addressVatNumberPickup,
                'AddressNameInvoice'        => $addressNameInvoice,
                'AddressStreetInvoice'      => $addressStreetInvoice,
                'AddressStreetNoInvoice'    => $addressStreetNoInvoice,
                'AddressZipcodeInvoice'     => $addressZipcodeInvoice,
                'AddressCityInvoice'        => $addressCityInvoice,
                'AddressStateInvoice'       => $addressStateInvoice,
                'AddressCountryInvoice'     => $addressCountryInvoice,
                'AddressContactInvoice'     => $addressContactInvoice,
                'AddressPhoneInvoice'       => $addressPhoneInvoice,
                'AddressFaxInvoice'         => $addressFaxInvoice,
                'AddressEmailInvoice'       => $addressEmailInvoice,
                'AddressAccountNoInvoice'   => $addressAccountNoInvoice,
                'AddressCustomerNoInvoice'  => $addressCustomerNoInvoice,
                'AddressVatNumberInvoice'   => $addressVatNumberInvoice,
            );
        }

        $document = array_filter($document);

        $responseBody = $this->_curlExec('/Api/Document', Zend_Json_Encoder::encode($document));

        $response = Zend_Json_Decoder::decode($responseBody);

        return $response;
    }

    /**
     * @param int $id Id of the document
     * @throws Zend_Json_Exception
     * @return mixed
     */
    public function doBooking($id)
    {
        $query = '?' . http_build_query(array('id' => $id));

        $responseBody = $this->_curlExec('/Api/DoBooking' . $query);

        $response = Zend_Json_Decoder::decode($responseBody);

        return $response;
    }

    /**
     * @param int $id
     * @param string $username
     * @param int|null $pdf
     * @param int|null $downloadonly
     * @param string|null $qzhost
     * @param string|null $selectedprinter
     * @return mixed
     * @throws Zend_Json_Exception
     */
    public function doLabel($id, $username, $pdf = null, $downloadonly = null, $qzhost = null, $selectedprinter = null)
    {
        $parameters = array(
            'id'       => $id,
            'username' => $username,
        );
        if (!is_null($pdf)) {
            $parameters['pdf'] = $pdf;
        }
        if (!is_null($downloadonly)) {
            $parameters['downloadonly'] = $downloadonly;
        }
        if (!is_null($qzhost)) {
            $parameters['qzhost'] = $qzhost;
        }
        if (!is_null($selectedprinter)) {
            $parameters['selectedprinter'] = $selectedprinter;
        }

        $query = '?' . http_build_query($parameters);

        $responseBody = $this->_curlExec('/Api/DoLabel' . $query);

        $response = Zend_Json_Decoder::decode($responseBody);

        return $response;
    }

    /**
     * @param int $id
     * @param string $username
     * @param int|null $downloadonly
     * @param string|null $qzhost
     * @param string|null $selectedprinter
     * @return mixed
     * @throws Zend_Json_Exception
     */
    public function doBookAndPrint($id, $username, $downloadonly = null, $qzhost = null, $selectedprinter = null)
    {
        $parameters = array(
            'id'       => $id,
            'username' => $username,
        );
        if (!is_null($downloadonly)) {
            $parameters['downloadonly'] = $downloadonly;
        }
        if (!is_null($qzhost)) {
            $parameters['qzhost'] = $qzhost;
        }
        if (!is_null($selectedprinter)) {
            $parameters['selectedprinter'] = $selectedprinter;
        }

        $query = '?' . http_build_query($parameters);

        $responseBody = $this->_curlExec('/Api/DoBookAndPrint' . $query);

        $response = Zend_Json_Decoder::decode($responseBody);

        return $response;
    }
}
