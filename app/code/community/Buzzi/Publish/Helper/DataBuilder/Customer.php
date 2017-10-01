<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Helper_DataBuilder_Customer
{
    /**
     * @param \Mage_Customer_Model_Customer $customer
     * @return array
     */
    public function getCustomerData($customer)
    {
        $payload = [
            'customer_id' => (string)$customer->getId(),
            'email' => (string)$customer->getEmail(),
            'first_name' => (string)$customer->getFirstname(),
            'last_name' => (string)$customer->getLastname(),
            'customer_since' => (string)$this->convertDateTime($customer->getCreatedAt())
        ];

        $transport = new Varien_Object(['customer' => $customer, 'payload' => $payload]);
        Mage::dispatchEvent('buzzi_publish_customer_build_after', ['transport' => $transport]);

        return (array)$transport->getData('payload');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function getCustomerDataFromOrder($order)
    {
        $payload = [
            'email' => (string)$order->getCustomerEmail(),
            'first_name' => (string)$order->getCustomerFirstname(),
            'last_name' => (string)$order->getCustomerLastname()
        ];

        $transport = new Varien_Object(['order' => $order, 'payload' => $payload]);
        Mage::dispatchEvent('buzzi_publish_guest_order_customer_build_after', ['transport' => $transport]);

        return (array)$transport->getData('payload');
    }

    /**
     * @param string $dateTime
     * @return string
     */
    protected function convertDateTime($dateTime)
    {
        /** @var Mage_Core_Model_Date $date */
        $date = Mage::getModel('core/date');
        return $date->gmtDate(\DateTime::ATOM, $date->timestamp($dateTime));
    }
}
