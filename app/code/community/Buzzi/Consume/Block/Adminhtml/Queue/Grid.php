<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Consume_Block_Adminhtml_Queue_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('buzzi_consume_queue');
        $this->setDefaultSort('delivery_time');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return \Buzzi_Consume_Model_Resource_Delivery_Collection
     */
    protected function _createMessageCollection()
    {
        return Mage::getResourceModel('buzzi_consume/delivery_collection');
    }

    /**
     * @return array
     */
    protected function _getEventTypes()
    {
        return Mage::getModel('buzzi_consume/config_system_source_EventType')->toOptionHash();
    }

    /**
     * @return array
     */
    protected function _getMessageStatuses()
    {
        return Mage::getModel('buzzi_consume/config_system_source_deliveryStatus')->toOptionHash();
    }

    /**
     * @return array
     */
    protected function _getYesNoOptions()
    {
        return Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray();
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->_createMessageCollection());
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', [
            'header'   => $this->__('ID'),
            'width'    => 30,
            'align'    => 'right',
            'index'    => 'delivery_id',
        ]);

        $this->addColumn('store_id', [
            'header'    => $this->__('Store View'),
            'index'     => 'store_id',
            'type'      => 'store',
            'sortable'  => false,
            'store_view'=> true,
            'width'     => 130
        ]);

        $this->addColumn('event_type', [
            'header'   => $this->__('Event Type'),
            'align'    => 'left',
            'width'     => 150,
            'index'    => 'event_type',
            'type'     => 'options',
            'sortable' => false,
            'options'  => $this->_getEventTypes(),
        ]);

        $this->addColumn('counter', [
            'header'   => $this->__('Counter'),
            'align'    => 'right',
            'index'    => 'counter',
            'width'    => 30,
        ]);

        $this->addColumn('payload', [
            'header'   => $this->__('Payload'),
            'align'    => 'left',
            'index'    => 'payload',
            'type'     => 'text',
            'width'    => 400
        ]);

        $this->addColumn('status', [
            'header'   => $this->__('Status'),
            'align'    => 'left',
            'width'    => 100,
            'index'    => 'status',
            'type'     => 'options',
            'options'  => $this->_getMessageStatuses(),
        ]);

        $this->addColumn('is_confirmed', [
            'header'   => $this->__('Confirmed'),
            'align'    => 'left',
            'width'    => 100,
            'index'    => 'is_confirmed',
            'type'     => 'options',
            'sortable' => false,
            'options'  => $this->_getYesNoOptions(),
        ]);

        $this->addColumn('error_message', [
            'header'   => $this->__('Error Message'),
            'index'    => 'error_message',
            'type'     => 'text',
            'width'    => 180
        ]);

        $this->addColumn('delivery_time', [
            'header'   => $this->__('Delivery Time'),
            'align'    => 'left',
            'width'    => 150,
            'type'     => 'datetime',
            'index'    => 'delivery_time',
        ]);

        $this->addColumn('handle_time', [
            'header'   => $this->__('Handle Time'),
            'align'    => 'left',
            'width'    => 150,
            'type'     => 'datetime',
            'index'    => 'handle_time',
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem('handle', [
            'label' => $this->__('Handle'),
            'url'   => $this->getUrl('*/*/massHandle')
        ]);

        $this->getMassactionBlock()->addItem('delete', [
            'label' => $this->__('Delete'),
            'url'   => $this->getUrl('*/*/massDelete')
        ]);

        return parent::_prepareMassaction();
    }

    /**
     * @param \Buzzi_Consume_Model_Delivery $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '#';
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
}
