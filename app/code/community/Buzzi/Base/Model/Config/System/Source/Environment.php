<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Base_Model_Config_System_Source_Environment
{
    const PRODUCTION = 'production';
    const SANDBOX = 'sandbox';
    const CUSTOM = 'custom';

    /**
     * @var Buzzi_Base_Helper_Data
     */
    protected $_helper;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_helper = Mage::helper('buzzi_base');
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::PRODUCTION => $this->_helper->__('Production'),
            self::SANDBOX => $this->_helper->__('Sandbox'),
            self::CUSTOM => $this->_helper->__('Custom'),
        ];
    }
}
