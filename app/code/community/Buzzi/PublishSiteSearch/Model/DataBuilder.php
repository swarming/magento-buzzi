<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishSiteSearch_Model_DataBuilder
{
    const EVENT_TYPE = 'buzzi.ecommerce.site-search';

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
     * @param array $searchData
     * @return mixed[]
     */
    public function getPayload($customer, $searchData)
    {
        $payload = $this->_dataBuilderBase->initBaseData(self::EVENT_TYPE);
        $payload['customer'] = $this->_dataBuilderCustomer->getCustomerData($customer);
        $payload['search_type'] = $searchData['search_type'];
        $payload['search_query'] = $searchData['search_query'];
        $payload['page_url'] = $searchData['page_url'];

        $transport = new Varien_Object(['customer' => $customer, 'search_data' => $searchData, 'payload' => $payload]);
        Mage::dispatchEvent('buzzi_publish_site_search_payload', ['transport' => $transport]);

        return (array)$transport->getData('payload');
    }
}
