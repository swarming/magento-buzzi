<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Adminhtml_Buzzi_PublishController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var \Buzzi_Publish_Model_Queue
     */
    protected $_queue;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_queue = Mage::getModel('buzzi_publish/queue');
        parent::_construct();
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('Buzzi Publish Submissions'));

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
    public function massSendAction()
    {
        $ids = (array)$this->getRequest()->getPost('ids');

        try {
            if (empty($ids)) {
                Mage::throwException($this->__("You haven't selected any item!"));
            }

            $this->_queue->sendByIds($ids);
            $this->_getSession()->addSuccess($this->__('Submissions were sent successfully.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while resending submissions.'));
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
            $this->_getSession()->addSuccess($this->__('Submissions were delete successfully.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while deleting submissions.'));
        }

        $this->_redirect('*/*/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/buzzi/publish');
    }
}
