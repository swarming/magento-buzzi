<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Model_Resource_Submission extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('buzzi_publish/queue', 'submission_id');
    }

    /**
     * @param \Buzzi_Publish_Model_Submission $object
     * @return $this
     */
    public function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $currentTime = $this->_getCurrentGmtTimestamp();

        if (!$object->getId()) {
            $object->setData('creating_time', $this->formatDate($currentTime));
        }

        if ($object->getStatus() && $object->getStatus() != Buzzi_Publish_Model_Submission::STATUS_PENDING) {
            $object->setData('submission_time', $this->formatDate($currentTime));
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
