<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Consume_Model_Config_System_Source_FetchType
{
    const ENABLED = 'enabled';
    const REGISTERED = 'registered';
    const ALL = 'all';

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
    public function toOptionArray()
    {
        return [
            self::ENABLED => $this->_helper->__('Enabled Only'),
            self::REGISTERED => $this->_helper->__('Registered Only'),
            self::ALL => $this->_helper->__('All Events'),
        ];
    }
}
