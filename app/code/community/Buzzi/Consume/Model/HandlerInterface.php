<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

interface Buzzi_Consume_Model_HandlerInterface
{
    /**
     * @param \Buzzi_Consume_Model_Delivery $delivery
     * @return bool
     */
    public function handle($delivery);
}
