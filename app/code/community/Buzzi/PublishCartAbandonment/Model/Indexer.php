<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishCartAbandonment_Model_Indexer
{
    /**
     * @var \Buzzi_Publish_Model_Config_Events
     */
    protected $_configEvents;

    /**
     * @var \Mage_Log_Model_Visitor_Online
     */
    protected $_visitorOnline;

    /**
     * @var \Buzzi_PublishCartAbandonment_Model_Resource_CartAbandonment
     */
    protected $_cartAbandonmentResource;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configEvents = Mage::getSingleton('buzzi_publish/config_events');
        $this->_visitorOnline = Mage::getModel('log/visitor_online');
        $this->_cartAbandonmentResource = Mage::getResourceModel('buzzi_publish_cart_abandonment/cartAbandonment');
    }

    /**
     * @return \Buzzi_PublishCartAbandonment_Model_CartAbandonment
     */
    protected function _createCartAbandonment()
    {
        return Mage::getModel('buzzi_publish_cart_abandonment/cartAbandonment');
    }

    /**
     * @return \Mage_Sales_Model_Resource_Quote_Collection
     */
    protected function _createReportQuoteCollection()
    {
        return Mage::getResourceModel('sales/quote_collection');
    }

    /**
     * @return int
     */
    protected function _getCurrentGmtTimestamp()
    {
        return Mage::getModel('core/date')->gmtTimestamp();
    }

    /**
     * @param int|null $storeId
     * @return void
     */
    public function reindex($storeId)
    {
        $quoteCollection = $this->_createReportQuoteCollection();
        $this->_prepareFingerprint($quoteCollection);
        $this->_prepareFilters($quoteCollection, $storeId);

        $quotesLimit = (int)$this->_configEvents->getValue(Buzzi_PublishCartAbandonment_Model_DataBuilder::EVENT_TYPE, 'quotes_limit', $storeId);
        if ($quotesLimit > 0) {
            $this->_processQuoteLimit($quoteCollection, $quotesLimit);
        }

        $isResubmissionAllowed = (bool)$this->_configEvents->getValue(Buzzi_PublishCartAbandonment_Model_DataBuilder::EVENT_TYPE, 'resubmission', $storeId);

        $cartAbandonment = $this->_createCartAbandonment();

        /** @var \Mage_Sales_Model_Quote $quote */
        foreach ($quoteCollection as $quote) {
            $cartAbandonment->unsetData();
            $quoteFingerprint = $quote->getData('fingerprint');
            $fingerprints = $this->_cartAbandonmentResource->getQuoteFingerprints($quote->getId());
            if (!empty($fingerprints) && (!$isResubmissionAllowed || in_array($quoteFingerprint, $fingerprints))) {
                continue;
            }
            $cartAbandonment->setStoreId($quote->getStoreId());
            $cartAbandonment->setQuoteId($quote->getId());
            $cartAbandonment->setFingerprint($quoteFingerprint);
            $cartAbandonment->setCustomerId($quote->getCustomerId());
            $cartAbandonment->setStatus(Buzzi_PublishCartAbandonment_Model_CartAbandonment::STATUS_PENDING);
            $cartAbandonment->setCreatedAt($quoteCollection->getConnection()->formatDate($this->_getCurrentGmtTimestamp()));
            $cartAbandonment->save();
        }
    }

    /**
     * @param \Mage_Sales_Model_Resource_Quote_Collection $quoteCollection
     * @return void
     */
    protected function _prepareFingerprint($quoteCollection)
    {
        $quoteCollection->getSelect()->columns(
            ['fingerprint' => new Zend_Db_Expr("md5(CONCAT(main_table.entity_id, (SELECT GROUP_CONCAT(sqi.product_id, sqi.qty ORDER BY sqi.product_id ASC) FROM sales_flat_quote_item as sqi WHERE sqi.quote_id = main_table.entity_id GROUP BY sqi.quote_id)))")]
        );
    }

    /**
     * @param \Mage_Sales_Model_Resource_Quote_Collection $quoteCollection
     * @param int|null $storeId
     * @return void
     */
    protected function _prepareFilters($quoteCollection, $storeId = null)
    {
        $quoteLastActionDays = (int)$this->_configEvents->getValue(Buzzi_PublishCartAbandonment_Model_DataBuilder::EVENT_TYPE, 'quote_last_action', $storeId);
        $trackOnlineCustomers = (bool)$this->_configEvents->getValue(Buzzi_PublishCartAbandonment_Model_DataBuilder::EVENT_TYPE, 'track_online_customers', $storeId);

        $quoteLastActionTime = $quoteLastActionDays > 0
            ? $this->_getCurrentGmtTimestamp() - 60 * 60 * 24 * $quoteLastActionDays
            : 0;

        $customerLastActionTime = !$trackOnlineCustomers
            ? $this->_getCurrentGmtTimestamp() - 60 * $this->_visitorOnline->getOnlineInterval()
            : 0;

        $quoteCollection->addFieldToFilter('main_table.items_count', ['neq' => '0']);
        $quoteCollection->addFieldToFilter('main_table.is_active', '1');
        $this->_filterQuoteUpdateTime($quoteCollection, $quoteLastActionTime, $customerLastActionTime);

        if ($storeId) {
            $quoteCollection->addFieldToFilter('main_table.store_id', ['eq' => $storeId]);
        }

        $quoteCollection->addFieldToFilter('main_table.customer_id', ['notnull' => null]);
        if ($trackOnlineCustomers) {
            $this->_visitorOnline->prepare();
            $this->_filterOnlineCustomers($quoteCollection);
        } else {
            $quoteCollection->addFieldToFilter('main_table.customer_id', ['gt' => 0]);
        }
    }

    /**
     * @param \Mage_Sales_Model_Resource_Quote_Collection $quoteCollection
     * @param int $lastActionTimeStart
     * @param int $lastActionTimeEnd
     * @return void
     */
    protected function _filterQuoteUpdateTime($quoteCollection, $lastActionTimeStart, $lastActionTimeEnd)
    {
        if ($lastActionTimeStart) {
            $startField = 'main_table.updated_at';
            $startCondition = ['gteq' => $quoteCollection->getConnection()->formatDate($lastActionTimeStart)];
            if (!$lastActionTimeEnd) {
                $startField = [$startField, 'main_table.updated_at'];
                $startCondition = [$startCondition, ['eq' => '0000-00-00 00:00:00']];
            }
            $quoteCollection->addFieldToFilter($startField, $startCondition);
        }

        if ($lastActionTimeEnd) {
            $quoteCollection->addFieldToFilter(
                'main_table.updated_at',
                ['lteq' => $quoteCollection->getConnection()->formatDate($lastActionTimeEnd)]
            );
            if (!$lastActionTimeStart) {
                $quoteCollection->addFieldToFilter('main_table.updated_at', ['neq' => '0000-00-00 00:00:00']);
            }
        }
    }

    /**
     * @param \Mage_Sales_Model_Resource_Quote_Collection $quoteCollection
     * @return void
     */
    protected function _filterOnlineCustomers($quoteCollection)
    {
        $onlineCustomerSelect = $quoteCollection->getConnection()->select();
        $onlineCustomerSelect->from($quoteCollection->getTable('log/visitor_online'), ['customer_id']);
        $onlineCustomerSelect->where('visitor_type = ?', Mage_Log_Model_Visitor::VISITOR_TYPE_CUSTOMER);
        $onlineCustomerQuery = $quoteCollection->getConnection()->query($onlineCustomerSelect);

        $onlineCustomerIds = $onlineCustomerQuery->fetchAll(Zend_Db::FETCH_COLUMN);

        if (!empty($onlineCustomerIds)) {
            $quoteCollection->addFieldToFilter('main_table.customer_id', ['nin' => $onlineCustomerIds]);
        }
    }

    /**
     * @param \Mage_Sales_Model_Resource_Quote_Collection $quoteCollection
     * @param int $quotesLimit
     * @return void
     */
    protected function _processQuoteLimit($quoteCollection, $quotesLimit)
    {
        $quoteCollection->getSelect()->limit($quotesLimit);
    }
}
