<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishCartAbandonment_Model_Resource_CartAbandonment extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('buzzi_publish_cart_abandonment/cart_abandonment', 'abandonment_id');
    }
}
