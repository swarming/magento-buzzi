<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Base_Block_Adminhtml_System_TestConnection extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @var string
     */
    protected $_template = 'buzzi/base/system/test_connection.phtml';

    /**
     * @param \Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()
            ->unsCanUseWebsiteValue()
            ->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param \Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getButtonHtml()
    {
        /** @var \Mage_Adminhtml_Block_Widget_Button $button */
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData([
                'id'      => 'buzzi_base_api_buzzi_connection',
                'label'   => $this->helper('buzzi_base')->__('Test Connection'),
                'onclick' => 'javascript:testConnection(); return false;'
            ]);

        return $button->toHtml();
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->helper('adminhtml')->getUrl('*/buzzi/testConnection');
    }

    /**
     * @return string
     */
    public function getWebsiteCode()
    {
        return $this->getRequest()->getParam('website', '');
    }

    /**
     * @return string
     */
    public function getStoreCode()
    {
        return $this->getRequest()->getParam('store', '');
    }
}
