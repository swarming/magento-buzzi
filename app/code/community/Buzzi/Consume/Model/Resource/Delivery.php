<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Consume_Model_Resource_Delivery extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('buzzi_consume/queue', 'delivery_id');
    }

    /**
     * @param \Buzzi_Consume_Model_Delivery $object
     * @return $this
     */
    public function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $currentTime = $this->_getCurrentGmtTimestamp();

        if (!$object->getId()) {
            $object->setData('delivery_time', $this->formatDate($currentTime));
        }

        if ($object->getStatus() && $object->getStatus() != Buzzi_Consume_Model_Delivery::STATUS_PENDING) {
            $object->setData('handle_time', $this->formatDate($currentTime));
        }
        return parent::_beforeSave($object);
    }

    /**
     * @return int
     */
    protected function _getCurrentGmtTimestamp()
    {
        return Mage::getModel('core/date')->gmtTimestamp();
    }
}
