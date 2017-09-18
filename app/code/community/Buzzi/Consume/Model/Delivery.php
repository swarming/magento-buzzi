<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

/**
 * @method setDeliveryId($deliveryId)
 * @method int getDeliveryId()
 * @method setStoreId($storeId)
 * @method int getStoreId()
 * @method $this setEventType($eventType)
 * @method string getEventType()
 * @method $this setUseFile($isFile)
 * @method bool getUseFile()
 * @method $this setPayload($payload)
 * @method string getPayload()
 * @method $this setCounter($counter)
 * @method int getCounter()
 * @method $this setReceipt($receipt)
 * @method string getReceipt()
 * @method setIsConfirmed($isConfirmed)
 * @method bool getIsConfirmed()
 * @method string getDeliveryTime()
 * @method string getHandleTime()
 * @method $this setStatus($status)
 * @method string getStatus()
 * @method $this setErrorMessage($errorMessage)
 * @method string getErrorMessage()
 */
class Buzzi_Consume_Model_Delivery extends Mage_Core_Model_Abstract
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
        $this->_init('buzzi_consume/delivery');
    }
}
