<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Consume_Model_Resource_Delivery_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('buzzi_consume/delivery');
    }

    /**
     * @param int[] $deliveryIds
     * @return $this
     */
    public function filterDeliveryIds($deliveryIds)
    {
        $this->addFilter('delivery_id', ['in' => $deliveryIds], 'public');
        return $this;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function filterStore($storeId)
    {
        $this->addFilter('store_id', $storeId);
        return $this;
    }

    /**
     * @param string $eventType
     * @return $this
     */
    public function filterType($eventType)
    {
        $this->addFilter('event_type', $eventType);
        return $this;
    }

    /**
     * @return $this
     */
    public function filterDone()
    {
        $this->addFieldToFilter('status', ['eq' => Buzzi_Consume_Model_Delivery::STATUS_DONE]);
        return $this;
    }

    /**
     * @return $this
     */
    public function filterNotDone()
    {
        $this->addFieldToFilter('status', ['neq' => Buzzi_Consume_Model_Delivery::STATUS_DONE]);
        return $this;
    }

    /**
     * @return $this
     */
    public function filterPending()
    {
        $this->addFilter('status', Buzzi_Consume_Model_Delivery::STATUS_PENDING);
        return $this;
    }

    /**
     * @param int $days
     * @return $this
     */
    public function filterHandleTime($days)
    {
        $lastActionTime = $this->_getCurrentGmtTimestamp() - 60 * 60 * 24 * (int)$days;
        $this->addFilter('handle_time', ['lteq' => $this->getConnection()->formatDate($lastActionTime)], 'public');
        return $this;
    }

    /**
     * @return int
     */
    protected function _getCurrentGmtTimestamp()
    {
        return Mage::getModel('core/date')->gmtTimestamp();
    }
}
