<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Consume_Adminhtml_Buzzi_ConsumeController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var \Buzzi_Consume_Model_Queue
     */
    protected $_queue;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_queue = Mage::getModel('buzzi_consume/queue');
        parent::_construct();
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('Buzzi Consume Deliveries'));

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * @return void
     */
    public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * @return void
     */
    public function massHandleAction()
    {
        $ids = (array)$this->getRequest()->getPost('ids');

        try {
            if (empty($ids)) {
                Mage::throwException($this->__("You haven't selected any item!"));
            }

            $this->_queue->handleByIds($ids);
            $this->_getSession()->addSuccess($this->__('Deliveries were handled successfully.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while handling deliveries.'));
        }

        $this->_redirect('*/*/index');
    }

    /**
     * @return void
     */
    public function massDeleteAction()
    {
        $ids = (array)$this->getRequest()->getPost('ids');

        try {
            if (empty($ids)) {
                Mage::throwException($this->__("You haven't selected any item!"));
            }

            $this->_queue->deleteByIds($ids);
            $this->_getSession()->addSuccess($this->__('Deliveries were delete successfully.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while deleting deliveries.'));
        }

        $this->_redirect('*/*/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/buzzi/consume');
    }
}
