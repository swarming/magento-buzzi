<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Helper_DataBuilder_Base
{
    /**
     * @return \Mage_Core_Model_Session
     */
    protected function _getSessions()
    {
        return Mage::getSingleton('core/session');
    }

    /**
     * @param string $eventType
     * @return array
     */
    public function initBaseData($eventType)
    {
        $data = [
            'event_type' => $eventType,
            'session_id' => md5($this->_getSessions()->getSessionId()),
            'timestamp' => $this->getCurrentTimestamp()
        ];

        return $data;
    }

    /**
     * @return string
     */
    protected function getCurrentTimestamp()
    {
        return Mage::getModel('core/date')->gmtDate(\DateTime::ATOM);
    }
}
