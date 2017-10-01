<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Helper_DataBuilder_Product
{
    /**
     * @return \Mage_Catalog_Model_Resource_Category_Collection|\Mage_Catalog_Model_Resource_Category_Flat_Collection
     */
    protected function _createCategoryCollectionModel()
    {
        return Mage::getModel('catalog/category')->getCollection();
    }

    /**
     * @param \Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getProductData($product)
    {
        $payload = [
            'base_price' => (string)$product->getPrice(),
            'category' => (array)$this->_getProductCategories($product),
            'product_sku' => (string)$product->getSku(),
            'product_name' => (string)$product->getName(),
            'product_description' => (string)$product->getShortDescription(),
            'product_image_url' => (string)$product->getImageUrl(),
            'product_url' => (string)$product->getProductUrl(),
        ];

        $transport = new Varien_Object(['product' => $product, 'payload' => $payload]);
        Mage::dispatchEvent('buzzi_publish_product_build_after', ['transport' => $transport]);

        return (array)$transport->getData('payload');
    }

    /**
     * @param \Mage_Catalog_Model_Product $product
     * @return string[]
     */
    protected function _getProductCategories($product)
    {
        $categoryIds = $product->getCategoryIds();
        $categoryCollection = $this->_createCategoryCollectionModel();
        $categoryCollection->addIdFilter($categoryIds);
        $categoryCollection->addNameToResult();
        $categoryNames = $categoryCollection->getColumnValues('name');
        return $categoryNames;
    }
}
