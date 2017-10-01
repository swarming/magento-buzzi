<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Helper_DataBuilder_Address
{
    /**
     * @param \Mage_Customer_Model_Address|\Mage_Sales_Model_Order_Address|\Mage_Sales_Model_Quote_Address $address
     * @return array
     */
    public function getAddressData($address)
    {
        $payload = [
            'company' => (string)$address->getCompany(),
            'name' => (string)$address->getName(),
            'street' => implode(' ', (array)$address->getStreet()),
            'state' => (string)$address->getRegionCode(),
            'city' => (string)$address->getCity(),
            'zip' => (string)$address->getPostcode(),
            'country' => (string)$address->getCountryId(),
            'phone' => (string)$address->getTelephone(),
        ];

        $transport = new Varien_Object(['address' => $address, 'payload' => $payload]);
        Mage::dispatchEvent('buzzi_publish_address_build_after', ['transport' => $transport]);

        return (array)$transport->getData('payload');
    }

    /**
     * @param \Mage_Customer_Model_Address|\Mage_Sales_Model_Order_Address|\Mage_Sales_Model_Quote_Address $address
     * @return array|null
     */
    protected function _validateAndRenderAddress($address)
    {
        return $address && $address->getFirstname() ? $this->getAddressData($address) : null;
    }

    /**
     * @param \Mage_Customer_Model_Customer $customer
     * @return array|string
     */
    public function getBillingAddresses($customer)
    {
        return $this->_validateAndRenderAddress($customer->getPrimaryBillingAddress());
    }

    /**
     * @param \Mage_Customer_Model_Customer $customer
     * @return array|string
     */
    public function getShippingAddresses($customer)
    {
        return $this->_validateAndRenderAddress($customer->getPrimaryShippingAddress());
    }

    /**
     * @param \Mage_Sales_Model_Quote $quote
     * @return array|string
     */
    public function getBillingAddressesFromQuote($quote)
    {
        return $this->_validateAndRenderAddress($quote->getBillingAddress());
    }

    /**
     * @param \Mage_Sales_Model_Quote $quote
     * @return array|string
     */
    public function getShippingAddressesFromQuote($quote)
    {
        return $this->_validateAndRenderAddress($quote->getShippingAddress());
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array|string
     */
    public function getBillingAddressesFromOrder($order)
    {
        return $this->_validateAndRenderAddress($order->getBillingAddress());
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array|string
     */
    public function getShippingAddressesFromOrder($order)
    {
        return $this->_validateAndRenderAddress($order->getShippingAddress());
    }
}
