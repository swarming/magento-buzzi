<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Block_Adminhtml_Queue_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('buzzi_publish_queue');
        $this->setDefaultSort('creating_time');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return \Buzzi_Publish_Model_Resource_Submission_Collection
     */
    protected function _createMessageCollection()
    {
        return Mage::getResourceModel('buzzi_publish/submission_collection');
    }

    /**
     * @return array
     */
    protected function _getEventTypes()
    {
        return Mage::getModel('buzzi_publish/config_system_source_eventType')->toOptionHash();
    }

    /**
     * @return array
     */
    protected function _getMessageStatuses()
    {
        return Mage::getModel('buzzi_publish/config_system_source_submissionStatus')->toOptionHash();
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
            'index'    => 'submission_id',
        ]);

        $this->addColumn('store_id', [
            'header'    => $this->__('Store View'),
            'index'     => 'store_id',
            'type'      => 'store',
            'sortable'  => false,
            'store_view'=> true,
            'width'     => 150
        ]);

        $this->addColumn('event_type', [
            'header'   => $this->__('Event Type'),
            'align'    => 'left',
            'index'    => 'event_type',
            'type'     => 'options',
            'sortable' => false,
            'options'  => $this->_getEventTypes(),
            'width'    => 150
        ]);

        $this->addColumn('counter', [
            'header'   => $this->__('Counter'),
            'align'    => 'center',
            'index'    => 'counter',
            'type'     => 'text',
            'width'    => 40,
        ]);

        $this->addColumn('payload', [
            'header'   => $this->__('Payload'),
            'align'    => 'left',
            'index'    => 'payload',
            'type'     => 'text',
            'width'    => 250
        ]);

        $this->addColumn('event_id', [
            'header'   => $this->__('Event ID'),
            'align'    => 'left',
            'index'    => 'event_id',
            'type'     => 'text',
            'width'    => 120,
        ]);

        $this->addColumn('status', [
            'header'   => $this->__('Status'),
            'align'    => 'left',
            'index'    => 'status',
            'type'     => 'options',
            'options'  => $this->_getMessageStatuses(),
        ]);

        $this->addColumn('error_message', [
            'header'   => $this->__('Error Message'),
            'index'    => 'error_message',
            'type'     => 'text',
            'width'    => 180
        ]);

        $this->addColumn('creating_time', [
            'header'   => $this->__('Creating Time'),
            'align'    => 'left',
            'type'     => 'datetime',
            'index'    => 'creating_time',
        ]);

        $this->addColumn('submission_time', [
            'header'   => $this->__('Submission Time'),
            'align'    => 'left',
            'type'     => 'datetime',
            'index'    => 'submission_time',
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

        $this->getMassactionBlock()->addItem('resend', [
            'label' => $this->__('Send'),
            'url'   => $this->getUrl('*/*/massSend')
        ]);

        $this->getMassactionBlock()->addItem('delete', [
            'label' => $this->__('Delete'),
            'url'   => $this->getUrl('*/*/massDelete')
        ]);

        return parent::_prepareMassaction();
    }

    /**
     * @param \Buzzi_Publish_Model_Submission $row
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
