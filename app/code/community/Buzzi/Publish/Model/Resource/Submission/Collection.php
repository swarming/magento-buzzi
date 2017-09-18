<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Model_Resource_Submission_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('buzzi_publish/submission');
    }

    /**
     * @param int[] $submissionIds
     * @return $this
     */
    public function filterSubmissionIds($submissionIds)
    {
        $this->addFilter('submission_id', ['in' => $submissionIds], 'public');
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
    public function filterEventType($eventType)
    {
        $this->addFilter('event_type', $eventType);
        return $this;
    }

    /**
     * @return $this
     */
    public function filterDone()
    {
        $this->addFieldToFilter('status', ['eq' => Buzzi_Publish_Model_Submission::STATUS_DONE]);
        return $this;
    }

    /**
     * @return $this
     */
    public function filterNotDone()
    {
        $this->addFieldToFilter('status', ['neq' => Buzzi_Publish_Model_Submission::STATUS_DONE]);
        return $this;
    }

    /**
     * @return $this
     */
    public function filterPending()
    {
        $this->addFilter('status', Buzzi_Publish_Model_Submission::STATUS_PENDING);
        return $this;
    }

    /**
     * @param int $maxTimes
     * @return $this
     */
    public function filterFailed($maxTimes = 0)
    {
        $this->addFilter('status', Buzzi_Publish_Model_Submission::STATUS_FAIL);
        if ($maxTimes > 0) {
            $this->addFilter('counter', ['lteq' => $maxTimes], 'public');
        }
        return $this;
    }

    /**
     * @param int $days
     * @return $this
     */
    public function filterSubmissionTime($days)
    {
        $lastActionTime = $this->_getCurrentGmtTimestamp() - 60 * 60 * 24 * (int)$days;
        $this->addFilter('submission_time', ['lteq' => $this->getConnection()->formatDate($lastActionTime)], 'public');
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
