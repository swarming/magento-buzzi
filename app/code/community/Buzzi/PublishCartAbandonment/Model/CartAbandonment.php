<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

/**
 * @method int getStoreId()
 * @method $this setStoreId($storeId)
 * @method int getQuoteId()
 * @method $this setQuoteId($quoteId)
 * @method string getFingerprint()
 * @method $this setFingerprint($fingerprint)
 * @method int getCustomerId()
 * @method $this setCustomerId($customerId)
 * @method string getStatus()
 * @method $this setStatus($status)
 * @method $this setErrorMessage($errorMessage)
 * @method $this getErrorMessage()
 * @method string setCreatedAt($cratedAt)
 * @method string getCreatedAt()
 */
class Buzzi_PublishCartAbandonment_Model_CartAbandonment extends Mage_Core_Model_Abstract
{
    const STATUS_PENDING = 'pending';
    const STATUS_DONE = 'done';
    const STATUS_FAIL = 'fail';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('buzzi_publish_cart_abandonment/cartAbandonment');
    }
}
