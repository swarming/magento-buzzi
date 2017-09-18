<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Model_Observer_GenerateConfig
{
    /**
     * @var \Buzzi_Publish_Model_Config_General
     */
    protected $_configGeneral;

    /**
     * @var \Buzzi_Publish_Model_Config_Structure_ConfigGenerator
     */
    protected $_configGenerator;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configGeneral = Mage::getSingleton('buzzi_publish/config_general');
        $this->_configGenerator = Mage::getModel('buzzi_publish/config_structure_configGenerator');
    }

    /**
     * @param \Varien_Event_Observer $event
     * @return void
     */
    public function execute($event)
    {
        if (!$this->_configGeneral->isEnabled()) {
            return;
        }

        /** @var $config Mage_Core_Model_config_Base */
        $config = $event->getData('config');

        $this->_configGenerator->generate($config);
    }
}
