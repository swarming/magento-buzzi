<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishCartAbandonment_Model_Manager
{
    /**
     * @var \Buzzi_Publish_Model_Queue
     */
    protected $_queue;

    /**
     * @var \Buzzi_PublishCartAbandonment_Model_DataBuilder
     */
    protected $_dataBuilder;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_queue = Mage::getModel('buzzi_publish/queue');
        $this->_dataBuilder = Mage::getModel('buzzi_publish_cart_abandonment/dataBuilder');
    }

    /**
     * @return \Buzzi_PublishCartAbandonment_Model_Resource_CartAbandonment_Collection
     */
    protected function _createCartAbandonmentCollection()
    {
        return Mage::getResourceModel('buzzi_publish_cart_abandonment/cartAbandonment_collection');
    }

    /**
     * @param int|null $storeId
     * @return void
     */
    public function sendPending($storeId = null)
    {
        $cartAbandonmentCollection = $this->_createCartAbandonmentCollection();
        $cartAbandonmentCollection->filterStatusPending();
        if ($storeId) {
            $cartAbandonmentCollection->filterStore($storeId);
        }

        /** @var \Buzzi_PublishCartAbandonment_Model_CartAbandonment $cartAbandonment */
        foreach ($cartAbandonmentCollection as $cartAbandonment) {
            $this->send($cartAbandonment);
            $cartAbandonment->setStatus(Buzzi_PublishCartAbandonment_Model_CartAbandonment::STATUS_DONE);
            $cartAbandonment->save();
        }
    }

    /**
     * @param \Buzzi_PublishCartAbandonment_Model_CartAbandonment $cartAbandonment
     * @return void
     */
    protected function send($cartAbandonment)
    {
        try {
            $this->_queue->send(
                Buzzi_PublishCartAbandonment_Model_DataBuilder::EVENT_TYPE,
                $this->_dataBuilder->getPayload($cartAbandonment),
                $cartAbandonment->getStoreId()
            );
        } catch (\Exception $e) {
            // Do nothing
        }
    }
}
