<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Model_Observer_CustomerSaveBefore
{
    /**
     * @var \Buzzi_Publish_Model_Config_General
     */
    protected $_configGeneral;

    public function __construct()
    {
        $this->_configGeneral = Mage::getModel('buzzi_publish/config_general');
    }

    /**
     * @param \Varien_Event_Observer $event
     * @return void
     */
    public function execute($event)
    {
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = $event->getData('customer');

        if (!$customer instanceof Mage_Customer_Model_Customer || $customer->getId()) {
            return;
        }

        $customer->setData(
            \Buzzi_Publish_Helper_Customer::ATTR_EXCEPTS_MARKETING,
            $this->_configGeneral->getDefaultExceptsMarketing()
        );
    }
}
