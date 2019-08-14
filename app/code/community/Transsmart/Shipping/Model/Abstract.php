<?php
/**
 * Transsmart Abstract Model
 *
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
abstract class Transsmart_Shipping_Model_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * A list of API keys to be mapped to database columns.
     * @var array
     */
    protected $apiKeysMapping = array();

    /**
     * Maps the API keys to the database columns.
     * When an API key is not present in the mapping list, the API key is used as is.
     * This ensures that all data is always present.
     *
     * @param array $data
     * @return array
     */
    public function mapApiKeysToDbColumns(array $data)
    {
        $mappedData = array();

        foreach ($data as $key => $value) {
            $newKey = isset($this->apiKeysMapping[$key]) ? $this->apiKeysMapping[$key] : $key;
            $mappedData[$newKey] = $value;
        }

        return $mappedData;
    }

}