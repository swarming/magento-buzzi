<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishCustomerRegistration_Model_DataBuilder
{
    const EVENT_TYPE = 'buzzi.ecommerce.user-registration';

    /**
     * @var \Buzzi_Publish_Helper_DataBuilder_Base
     */
    protected $_dataBuilderBase;

    /**
     * @var \Buzzi_Publish_Helper_DataBuilder_Customer
     */
    protected $_dataBuilderCustomer;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_dataBuilderBase = Mage::helper('buzzi_publish/dataBuilder_base');
        $this->_dataBuilderCustomer = Mage::helper('buzzi_publish/dataBuilder_customer');
    }

    /**
     * @param \Mage_Customer_Model_Customer $customer
     * @return mixed[]
     */
    public function getPayload($customer)
    {
        $payload = $this->_dataBuilderBase->initBaseData(self::EVENT_TYPE);
        $payload['customer'] = $this->_dataBuilderCustomer->getCustomerData($customer);

        $transport = new Varien_Object(['customer' => $customer, 'payload' => $payload]);
        Mage::dispatchEvent('buzzi_publish_customer_registration_payload', ['transport' => $transport]);

        return (array)$transport->getData('payload');
    }
}
