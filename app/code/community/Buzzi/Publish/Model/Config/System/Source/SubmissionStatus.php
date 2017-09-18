<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Model_Config_System_Source_SubmissionStatus
{
    /**
     * @var \Buzzi_Publish_Helper_Data
     */
    protected $_helper;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_helper = Mage::helper('buzzi_publish');
    }

    /**
     * @return array
     */
    public function toOptionHash()
    {
        return [
            Buzzi_Publish_Model_Submission::STATUS_PENDING => $this->_helper->__('Pending'),
            Buzzi_Publish_Model_Submission::STATUS_DONE => $this->_helper->__('Done'),
            Buzzi_Publish_Model_Submission::STATUS_FAIL => $this->_helper->__('Fail'),
        ];
    }
}
