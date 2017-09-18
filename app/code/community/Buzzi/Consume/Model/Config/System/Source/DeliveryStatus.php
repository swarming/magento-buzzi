<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Consume_Model_Config_System_Source_DeliveryStatus
{
    /**
     * @var Buzzi_Consume_Helper_Data
     */
    protected $_helper;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_helper = Mage::helper('buzzi_consume');
    }

    /**
     * @return array
     */
    public function toOptionHash()
    {
        return [
            Buzzi_Consume_Model_Delivery::STATUS_PENDING => $this->_helper->__('Pending'),
            Buzzi_Consume_Model_Delivery::STATUS_DONE => $this->_helper->__('Done'),
            Buzzi_Consume_Model_Delivery::STATUS_FAIL => $this->_helper->__('Fail'),
        ];
    }
}
