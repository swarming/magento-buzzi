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
            'customer_id' => $customer->getId(),
            'email' => $customer->getEmail(),
            'first_name' => $customer->getFirstname(),
            'last_name' => $customer->getLastname(),
            'customer_since' => $customer->getCreatedAt()
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
            'customer_id' => null,
            'email' => $order->getCustomerEmail(),
            'first_name' => $order->getCustomerFirstname(),
            'last_name' => $order->getCustomerLastname(),
            'customer_since' => $order->getCreatedAt(),
        ];

        $transport = new Varien_Object(['order' => $order, 'payload' => $payload]);
        Mage::dispatchEvent('buzzi_publish_guest_order_customer_build_after', ['transport' => $transport]);

        return (array)$transport->getData('payload');
    }
}
