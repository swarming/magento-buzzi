<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishWishlistItem_Model_Observer_WishlistAddProduct
{
    /**
     * @var \Buzzi_Publish_Model_Config_Events
     */
    protected $_configEvents;

    /**
     * @var \Buzzi_Publish_Model_Queue
     */
    protected $_queue;

    /**
     * @var \Buzzi_PublishWishlistItem_Model_DataBuilder
     */
    protected $_dataBuilder;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configEvents = Mage::getSingleton('buzzi_publish/config_events');
        $this->_queue = Mage::getModel('buzzi_publish/queue');
        $this->_dataBuilder = Mage::getModel('buzzi_publish_wishlist_item/dataBuilder');
    }

    /**
     * @param \Varien_Event_Observer $observer
     * @return void
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $wishlistItems = (array)$observer->getData('items');

        if (empty($wishlistItems)) {
            return;
        }

        if (!$wishlistItems[0] instanceof Mage_Wishlist_Model_Item) {
            return;
        }
        $storeId = $wishlistItems[0]->getStoreId();

        if (!$this->_configEvents->isEventEnabled(Buzzi_PublishWishlistItem_Model_DataBuilder::EVENT_TYPE, $storeId)) {
            return;
        }

        foreach ($wishlistItems as $wishlistItem) {
            $this->processWishlistItem($wishlistItem, $storeId);
        }
    }

    /**
     * @param \Mage_Wishlist_Model_Item $wishlistItem
     * @param int $storeId
     * @return void
     */
    protected function processWishlistItem($wishlistItem, $storeId)
    {
        $payload = $this->_dataBuilder->getPayload($wishlistItem);

        if ($this->_configEvents->isCron(Buzzi_PublishWishlistItem_Model_DataBuilder::EVENT_TYPE, $storeId)) {
            $this->_queue->add(Buzzi_PublishWishlistItem_Model_DataBuilder::EVENT_TYPE, $payload, $storeId);
        } else {
            $this->_queue->send(Buzzi_PublishWishlistItem_Model_DataBuilder::EVENT_TYPE, $payload, $storeId);
        }
    }
}
