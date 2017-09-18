<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishCartAbandonment_Model_Indexer
{
    /**
     * @var \Mage_Log_Model_Visitor_Online
     */
    protected $_visitorOnline;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_visitorOnline = Mage::getModel('log/visitor_online');
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
     * @param int $quoteLastActionDays
     * @param int|null $storeId
     * @return void
     */
    public function reindex($quoteLastActionDays = 1, $storeId = null)
    {
        $quoteCollection = $this->_createReportQuoteCollection();
        $this->prepareFilters($quoteCollection, $quoteLastActionDays, $storeId);

        $cartAbandonment = $this->_createCartAbandonment();

        /** @var \Mage_Sales_Model_Quote $quote */
        foreach ($quoteCollection as $quote) {
            $cartAbandonment->load($quote->getId(), 'quote_id');
            $cartAbandonment->setStoreId($quote->getStoreId());
            $cartAbandonment->setQuoteId($quote->getId());
            $cartAbandonment->setCustomerId($quote->getCustomerId());
            $cartAbandonment->save();
        }
    }

    /**
     * @param \Mage_Sales_Model_Resource_Quote_Collection $quoteCollection
     * @param int $quoteLastActionDays
     * @param int|null $storeId
     * @return void
     */
    protected function prepareFilters($quoteCollection, $quoteLastActionDays, $storeId = null)
    {
        $quoteCollection->addFieldToFilter('main_table.items_count', ['neq' => '0']);
        $quoteCollection->addFieldToFilter('main_table.is_active', '1');

        $quoteLastActionDays = (int)$quoteLastActionDays;
        if ($quoteLastActionDays > 0) {
            $quoteLastActionTime = $this->_getCurrentGmtTimestamp() - 60 * 60 * 24 * $quoteLastActionDays;
            $quoteCollection->addFieldToFilter(
                ['main_table.updated_at', 'main_table.updated_at'],
                [
                    ['gteq' => $quoteCollection->getConnection()->formatDate($quoteLastActionTime)],
                    ['eq' => '0000-00-00 00:00:00']
                ]
            );
        }

        if ($storeId) {
            $quoteCollection->addFieldToFilter('main_table.store_id', ['eq' => $storeId]);
        }

        $this->filterOnlineCustomers($quoteCollection);
    }

    /**
     * @param \Mage_Sales_Model_Resource_Quote_Collection $quoteCollection
     * @return void
     */
    protected function filterOnlineCustomers($quoteCollection)
    {
        $lastActionTime = $this->_getCurrentGmtTimestamp() - 60 * $this->_visitorOnline->getOnlineInterval();

        $quoteCollection->getSelect()
            ->joinInner(
                ['customer' => $quoteCollection->getTable('log/customer')],
                'customer.customer_id = main_table.customer_id',
                null
            )
            ->joinInner(
                ['visitor' => $quoteCollection->getTable('log/visitor')],
                'customer.visitor_id = visitor.visitor_id',
                ['last_action' => 'max(visitor.last_visit_at)']
            )
            ->group('main_table.customer_id')
            ->having('last_action <= ?', $quoteCollection->getConnection()->formatDate($lastActionTime));
    }
}
