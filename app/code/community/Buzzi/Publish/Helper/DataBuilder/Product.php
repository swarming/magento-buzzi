<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Helper_DataBuilder_Product
{
    /**
     * @var \Buzzi_Publish_Model_Config_General
     */
    protected $_configGeneral;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configGeneral = Mage::getModel('buzzi_publish/config_general');
    }

    /**
     * @return \Mage_Catalog_Helper_Image
     */
    protected function _getImageHelper()
    {
        return Mage::helper('catalog/image');
    }

    /**
     * @return \Mage_Catalog_Model_Product_Media_Config
     */
    protected function _getProductMediaConfig()
    {
        return Mage::getSingleton('catalog/product_media_config');
    }

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
            'product_image_url' => (string)$this->_getProductImage($product),
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

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    protected function _getProductImage($product)
    {
        return $this->_configGeneral->isUseOriginalProductImages()
            ? $this->_getOriginalProductImage($product)
            : $this->_getImageHelper()->init($product, 'image');
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    protected function _getOriginalProductImage($product)
    {
        return $product->getImage()
            ? $this->_getProductMediaConfig()->getMediaUrl($product->getImage())
            : Mage::getDesign()->getSkinUrl($this->_getImageHelper()->getPlaceholder());
    }
}
