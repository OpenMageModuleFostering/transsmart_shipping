<?php

/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
class Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Mapping_Attribute
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => '',
                'label' => Mage::helper('adminhtml')->__('(empty)')
            )
        );

        /** @var Mage_Catalog_Model_Resource_Product_Attribute_Collection $attributeCollection */
        $attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('is_visible', 1)
            ->addFieldToFilter('frontend_input', array('nin' => array('media_image', 'gallery')))
            ->setOrder('frontend_label', Varien_Data_Collection::SORT_ORDER_ASC);

        foreach ($attributeCollection as $_attribute) {
            $options[] = array(
                'value' => $_attribute->getAttributeCode(),
                'label' => $_attribute->getFrontendLabel()
            );
        }

        return $options;
    }
}
