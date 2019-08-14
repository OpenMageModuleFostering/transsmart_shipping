<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Shipping_Resource_Carrier_Tablerate
    extends Mage_Shipping_Model_Resource_Carrier_Tablerate
{
    /**
     * Array of unique table rate keys to protect from duplicates
     *
     * @var array
     */
    protected $_importUniqueTranssmartHash = array();

    /**
     * Return table rates array. Original table rate returns only one rate. This one adds an additional field named
     * transsmart_carrierprofile_id, and can return multiple rates they have the same country/region/zip/condition.
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return array
     */
    public function getRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = array();

        // first find single rate
        $rate = parent::getRate($request);

        if ($rate) {
            // now get all rates with the same country/region/zip/condition
            $adapter = $this->_getReadAdapter();

            $uniqueFields = array(
                'website_id',
                'dest_country_id',
                'dest_region_id',
                'dest_zip',
                'condition_name',
                'condition_value'
            );

            // render the select query
            $select = $adapter->select()
                ->from($this->getMainTable());

            // add query conditions
            foreach ($rate as $_field => $_value) {
                if (in_array($_field, $uniqueFields)) {
                    if ($_field == 'dest_zip' && ($_value === '' || $_value == '*')) {
                        $select->where("`dest_zip` IN ('', '*')");
                    }
                    else {
                        $select->where($adapter->quoteIdentifier($_field) . ' = ?', $_value);
                    }
                }
            }

            // run the query and process results
            if (($result = $adapter->fetchAll($select))) {
                foreach ($result as $_rate) {
                    // normalize destination zip code
                    if ($_rate['dest_zip'] == '*') {
                        $_rate['dest_zip'] = '';
                    }
                    $results[] = $_rate;
                }
            }
            else {
                // something went wrong, just return the single found rate
                $result = array($rate);
            }
        }

        return $result;
    }

    /**
     * Validate row for import and return table rate array or false
     * Error will be add to _importErrors array
     *
     * @param array $row
     * @param int $rowNumber
     * @return array|false
     */
    protected function _getImportRow($row, $rowNumber = 0)
    {
        $result = parent::_getImportRow($row, $rowNumber);

        // reset original unique hash array
        $this->_importUniqueHash = array();

        if ($result !== false) {
            $carrierprofileId = null;
            if (count($row) >= 6) {
                $carrierprofileId = $row[5];

                // validate carrier profile id
                $carrierprofileCollection = Mage::getResourceSingleton('transsmart_shipping/carrierprofile_collection');
                if (!($carrierprofileCollection->getItemById($carrierprofileId))) {
                    $this->_importErrors[] = Mage::helper('transsmart_shipping')->__(
                        'Invalid Transsmart Carrier Profile Id "%s" in the Row #%s.',
                        $carrierprofileId,
                        $rowNumber
                    );
                    return false;
                }
            }

            $result[] = $carrierprofileId;

            // protect from duplicate
            $hash = sprintf("%s-%d-%s-%F-%s", $result[1], $result[2], $result[3], $result[5], $result[7]);
            if (isset($this->_importUniqueTranssmartHash[$hash])) {
                $this->_importErrors[] = Mage::helper('shipping')->__(
                    'Duplicate Row #%s (Country "%s", Region/State "%s", Zip "%s", Value "%s" and Transsmart Carrier Profile Id "%s").',
                    $rowNumber,
                    $row[0],    // dest_country_id
                    $row[1],    // dest_region_id
                    $result[3], // dest_zip
                    $result[5], // condition_value
                    $result[7]  // transsmart_carrierprofile_id
                );
                return false;
            }
            $this->_importUniqueTranssmartHash[$hash] = true;
        }

        return $result;
    }

    /**
     * Save import data batch
     *
     * @param array $data
     * @return Mage_Shipping_Model_Resource_Carrier_Tablerate
     */
    protected function _saveImportData(array $data)
    {
        if (!empty($data)) {
            $columns = array('website_id', 'dest_country_id', 'dest_region_id', 'dest_zip',
                             'condition_name', 'condition_value', 'price', 'transsmart_carrierprofile_id');
            $this->_getWriteAdapter()->insertArray($this->getMainTable(), $columns, $data);
            $this->_importedRows += count($data);
        }

        return $this;
    }
}
