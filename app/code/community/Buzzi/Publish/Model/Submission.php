<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

/**
 * @method $this setSubmissionId($submissionId)
 * @method string getSubmissionId()
 * @method setStoreId($storeId)
 * @method int getStoreId()
 * @method $this setEventType($eventType)
 * @method string getEventType()
 * @method $this setPayload($payload)
 * @method string getPayload()
 * @method $this setUseFile($isFile)
 * @method bool getUseFile()
 * @method $this setCounter($counter)
 * @method int getCounter()
 * @method $this setEventId($eventId)
 * @method string getEventId()
 * @method string getCreatingTime()
 * @method string getSubmissionTime()
 * @method $this setStatus($status)
 * @method string getStatus()
 * @method $this setErrorMessage($errorMessage)
 * @method string getErrorMessage()
 */
class Buzzi_Publish_Model_Submission extends Mage_Core_Model_Abstract
{
    const STATUS_PENDING = 'pending';
    const STATUS_DONE = 'done';
    const STATUS_FAIL = 'fail';

    const MAX_PAYLOAD_LENGTH = Varien_Db_Ddl_Table::MAX_TEXT_SIZE;

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('buzzi_publish/submission');
    }
}
