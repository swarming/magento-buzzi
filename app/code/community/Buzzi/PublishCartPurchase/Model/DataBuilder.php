<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishCartPurchase_Model_DataBuilder
{
    const EVENT_TYPE = 'buzzi.ecommerce.cart-purchase';

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
     * @param \Mage_Sales_Model_Order $order
     * @return mixed[]
     */
    public function getPayload($order)
    {
        /**@var Mage_Sales_Model_Quote $quote */
        $quote = $order->getQuote();

        $payload = $this->_dataBuilderBase->initBaseData(self::EVENT_TYPE);
        $payload['customer'] = $this->_getCustomerData($order);
        $payload['cart'] = $this->_dataBuilderCart->getCartData($quote, $order);
        $payload['cart']['cart_items'] = $this->_dataBuilderCart->getCartItemsData($quote);

        $billingAddress = $this->_dataBuilderAddress->getBillingAddressesFromOrder($order);
        if ($billingAddress) {
            $payload['cart']['billing_address'] = $billingAddress;
        }

        $shippingAddress = $this->_dataBuilderAddress->getShippingAddressesFromOrder($order);
        if ($shippingAddress) {
            $payload['cart']['shipping_address'] = $shippingAddress;
        }

        $transport = new Varien_Object(['order' => $order, 'payload' => $payload]);
        Mage::dispatchEvent('buzzi_publish_cart_purchase_payload', ['transport' => $transport]);

        return (array)$transport->getData('payload');
    }

    /**
     * @param \Mage_Sales_Model_Order $order
     * @return array
     */
    protected function _getCustomerData($order)
    {
        if ($order->getCustomerId()) {
            $customer = $this->_createCustomerModel();
            $customer->load($order->getCustomerId());
            $customerData = $this->_dataBuilderCustomer->getCustomerData($customer);
        } else {
            $customerData = $this->_dataBuilderCustomer->getCustomerDataFromOrder($order);
        }

        return $customerData;
    }
}
