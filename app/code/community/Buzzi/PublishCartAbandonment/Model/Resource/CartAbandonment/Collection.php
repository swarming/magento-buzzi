<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishCartAbandonment_Model_Resource_CartAbandonment_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('buzzi_publish_cart_abandonment/cartAbandonment');
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
     * @return $this
     */
    public function filterStatusPending()
    {
        $this->addFilter('status', Buzzi_PublishCartAbandonment_Model_CartAbandonment::STATUS_PENDING);
        return $this;
    }

    /**
     * @return $this
     */
    public function filterStatusDone()
    {
        $this->addFilter('status', Buzzi_PublishCartAbandonment_Model_CartAbandonment::STATUS_DONE);
        return $this;
    }
}
