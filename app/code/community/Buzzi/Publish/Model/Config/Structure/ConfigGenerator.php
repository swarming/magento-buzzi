<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Model_Config_Structure_ConfigGenerator
{
    /**
     * Start order config nodes added on flay
     *
     * @var int
     */
    protected static $_startOrder = 1;

    /**
     * @var \Buzzi_Publish_Model_Config_General
     */
    protected $_configGeneral;

    /**
     * @var \Buzzi_Publish_Model_Config_Events
     */
    protected $_configEvents;

    /**
     * @var \Buzzi_Base_Helper_Config_ElementGenerator
     */
    protected $_elementGenerator;

    /**
     * @var \Buzzi_Base_Helper_Config_CronElementsGenerator
     */
    protected $_cronElementsGenerator;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configGeneral = Mage::getModel('buzzi_publish/config_general');
        $this->_configEvents = Mage::getSingleton('buzzi_publish/config_events');
        $this->_elementGenerator = Mage::helper('buzzi_base/config_elementGenerator');
        $this->_cronElementsGenerator = Mage::helper('buzzi_base/config_cronElementsGenerator');
    }

    /**
     * @param Mage_Core_Model_config_Base $config
     * @return $this
     */
    public function generate($config)
    {
        $allTypes = $this->_configEvents->getAllTypes();
        $enabledEvents = $this->_configGeneral->getEnabledEvents();

        /** @var Mage_Core_Model_Config_Element $publishBuzziGroups */
        $publishBuzziGroups = $config->getNode('sections/buzzi_publish_events/groups');

        $sortOrder = self::$_startOrder;
        foreach ($allTypes as $eventType) {
            if (!in_array($eventType, $enabledEvents)) {
                continue;
            }

            $this->_generateEventGroup($publishBuzziGroups, $eventType, $sortOrder);
        }

        return $this;
    }

    /**
     * @param \Mage_Core_Model_Config_Element $configElement
     * @param string $eventType
     * @param int $sortOrder
     * @return $this
     */
    protected function _generateEventGroup($configElement, $eventType, $sortOrder)
    {
        /** @var $element Mage_Core_Model_Config_Element */
        $element = $configElement->{$this->_configEvents->getEventCode($eventType)};
        $element = !empty($element) ? $element : $configElement->addChild($this->_configEvents->getEventCode($eventType));

        $element->addChild('label', $this->_configEvents->getEventLabel($eventType));
        $element->addChild('frontend_type', 'text');
        $element->addChild('sort_order', ++$sortOrder);
        $element->addChild('show_in_default', '1');
        $element->addChild('show_in_website', '1');
        $element->addChild('show_in_store', '0');
        $fields = $element->addChild('fields');

        $this->_cronElementsGenerator->generate($fields, $this->_configEvents->isCronOnly($eventType), $sortOrder);

        return $this;
    }
}
