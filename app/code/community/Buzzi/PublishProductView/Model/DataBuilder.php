<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishProductView_Model_DataBuilder
{
    const EVENT_TYPE = 'buzzi.ecommerce.product-view';

    /**
     * @var \Buzzi_Publish_Helper_DataBuilder_Base
     */
    protected $_dataBuilderBase;

    /**
     * @var \Buzzi_Publish_Helper_DataBuilder_Customer
     */
    protected $_dataBuilderCustomer;

    /**
     * @var \Buzzi_Publish_Helper_DataBuilder_Product
     */
    protected $_dataBuilderProduct;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_dataBuilderBase = Mage::helper('buzzi_publish/dataBuilder_base');
        $this->_dataBuilderCustomer = Mage::helper('buzzi_publish/dataBuilder_customer');
        $this->_dataBuilderProduct = Mage::helper('buzzi_publish/dataBuilder_product');
    }

    /**
     * @param \Mage_Customer_Model_Customer $customer
     * @param \Mage_Catalog_Model_Product $product
     * @return mixed[]
     */
    public function getPayload($customer, $product)
    {
        $payload = $this->_dataBuilderBase->initBaseData(self::EVENT_TYPE);
        $payload['customer'] = $this->_dataBuilderCustomer->getCustomerData($customer);
        $payload['product'] = $this->_dataBuilderProduct->getProductData($product);

        $transport = new Varien_Object(['customer' => $customer, 'product' => $product, 'payload' => $payload]);
        Mage::dispatchEvent('buzzi_publish_product_view_payload', ['transport' => $transport]);

        return (array)$transport->getData('payload');
    }
}
