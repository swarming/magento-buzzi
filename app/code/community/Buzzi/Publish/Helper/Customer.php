<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */


class Buzzi_Publish_Helper_Customer
{
    const ATTR_ACCEPTS_MARKETING = 'accepts_marketing';

    /**
     * @return \Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * @return bool
     */
    public function isCurrentAcceptsMarketing()
    {
        return $this->isAcceptsMarketing($this->_getCustomerSession()->getCustomer());
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return bool
     */
    public function isAcceptsMarketing($customer)
    {
        return (bool)$customer->getData(self::ATTR_ACCEPTS_MARKETING);
    }
}
