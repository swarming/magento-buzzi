<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishWishlistItem_Model_DataBuilder
{
    const EVENT_TYPE = 'buzzi.ecommerce.wishlist-item';

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
     * @return Mage_Customer_Model_Customer
     */
    protected function _createCustomerModel()
    {
        return Mage::getModel('customer/customer');
    }

    /**
     * @param \Mage_Wishlist_Model_Item $wishlistItem
     * @return mixed[]
     */
    public function getPayload($wishlistItem)
    {
        /** @var \Mage_Wishlist_Model_Wishlist $wishlist */
        $wishlist = $wishlistItem->getWishlist();

        $payload = $this->_dataBuilderBase->initBaseData(self::EVENT_TYPE);
        $payload['customer'] = $this->_getCustomerData($wishlist);
        $payload['product'] = $this->_dataBuilderProduct->getProductData($wishlistItem->getProduct());

        $transport = new Varien_Object(['wishlist' => $wishlist, 'wishlist_item' => $wishlistItem, 'payload' => $payload]);
        Mage::dispatchEvent('buzzi_publish_wishlist_item_payload', ['transport' => $transport]);

        return (array)$transport->getData('payload');
    }

    /**
     * @param \Mage_Wishlist_Model_Wishlist $wishlist
     * @return array
     */
    protected function _getCustomerData($wishlist)
    {
        $customer = $this->_createCustomerModel();
        $customer->load($wishlist->getCustomerId());
        if (!$customer->getId()) {
            Mage::throwException(sprintf('Customer with %s id is not found', $wishlist->getCustomerId()));
        }

        return $this->_dataBuilderCustomer->getCustomerData($customer);
    }
}
