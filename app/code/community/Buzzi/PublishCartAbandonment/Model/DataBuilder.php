<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishCartAbandonment_Model_DataBuilder
{
    const EVENT_TYPE = 'buzzi.ecommerce.cart-abandonment';

    /**
     * @var \Buzzi_Publish_Helper_DataBuilder_Base
     */
    protected $_dataBuilderBase;

    /**
     * @var \Buzzi_Publish_Helper_DataBuilder_Cart
     */
    protected $_dataBuilderCart;

    /**
     * @var \Buzzi_Publish_Helper_DataBuilder_Customer
     */
    protected $_dataBuilderCustomer;

    /**
     * @var \Buzzi_Publish_Helper_DataBuilder_Address
     */
    protected $_dataBuilderAddress;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_dataBuilderBase = Mage::helper('buzzi_publish/dataBuilder_base');
        $this->_dataBuilderCart = Mage::helper('buzzi_publish/dataBuilder_cart');
        $this->_dataBuilderCustomer = Mage::helper('buzzi_publish/dataBuilder_customer');
        $this->_dataBuilderAddress = Mage::helper('buzzi_publish/dataBuilder_address');
    }

    /**
     * @return \Mage_Customer_Model_Customer
     */
    protected function _createCustomerModel()
    {
        return Mage::getModel('customer/customer');
    }

    /**
     * @return \Mage_Sales_Model_Quote
     */
    protected function _createQuoteModel()
    {
        return Mage::getModel('sales/quote');
    }

    /**
     * @param \Buzzi_PublishCartAbandonment_Model_CartAbandonment $abandonment
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getPayload($abandonment)
    {
        $customer = $this->_createCustomerModel();
        $customer->load($abandonment->getCustomerId());
        if (!$customer->getId()) {
            Mage::throwException(sprintf('Customer with %s id is not found', $abandonment->getCustomerId()));
        }

        $quote = $this->_createQuoteModel();
        $quote->setStore($customer->getStore());
        $quote->loadActive($abandonment->getQuoteId());
        if (!$quote->getId()) {
            Mage::throwException(sprintf('Quote with %s id is not found or is not acvite.', $quote->getId()));
        }

        $payload = $this->_dataBuilderBase->initBaseData(self::EVENT_TYPE);
        $payload['customer'] = $this->_dataBuilderCustomer->getCustomerData($customer);
        $payload['cart'] = $this->_dataBuilderCart->getCartData($quote);
        $payload['cart']['cart_items'] = $this->_dataBuilderCart->getCartItemsData($quote);

        $billingAddress = $this->_dataBuilderAddress->getBillingAddresses($customer);
        if ($billingAddress) {
            $payload['cart']['billing_address'] = $billingAddress;
        }

        $shippingAddress = $this->_dataBuilderAddress->getShippingAddresses($customer);
        if ($shippingAddress) {
            $payload['cart']['shipping_address'] = $shippingAddress;
        }

        $transport = new Varien_Object(['abandonment' => $abandonment, 'payload' => $payload]);
        Mage::dispatchEvent('buzzi_publish_cart_abandonment_payload', ['transport' => $transport]);

        return (array)$transport->getData('payload');
    }
}
