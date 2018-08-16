<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishPageView_Model_DataBuilder
{
    const EVENT_TYPE = 'buzzi.ecommerce.page-view';

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
     * @param string $websiteCode
     * @param string $pageId
     * @param \Mage_Catalog_Model_Category|null $category
     * @return mixed[]
     */
    public function getPayload($customer, $websiteCode, $pageId, $category = null)
    {
        $payload = $this->_dataBuilderBase->initBaseData(self::EVENT_TYPE);
        $payload['customer'] = $this->_dataBuilderCustomer->getCustomerData($customer);
        $payload['page_id'] = $pageId;
        $payload['site_id'] = $websiteCode;

        if ($category) {
            $payload['category'] = $category->getName();
        }

        $transport = new Varien_Object(['customer' => $customer, 'payload' => $payload]);
        Mage::dispatchEvent('buzzi_publish_page_view_payload', ['transport' => $transport]);

        return (array)$transport->getData('payload');
    }
}
