<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Consume_Block_Adminhtml_Queue extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_blockGroup = 'buzzi_consume';
        $this->_controller = 'adminhtml_queue';
        $this->_headerText = $this->__('Buzzi Consume Deliveries');

        parent::_construct();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->_removeButton('add');
        return parent::_prepareLayout();
    }
}
