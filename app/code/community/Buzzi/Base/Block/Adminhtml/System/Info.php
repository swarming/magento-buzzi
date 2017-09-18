<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Base_Block_Adminhtml_System_Info extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * @param string $content
     * @return string
     */
    protected function _getInfo($content)
    {
        $output = $this->_getStyle()
            . '<div class="buzzi-info">' . $content . '</div>';
        return $output;
    }

    /**
     * @return string
     */
    protected function _getStyle()
    {
        $content = '<style>'
            . '.buzzi-info { border: 1px solid #cccccc; background: #e7efef; margin-bottom: 10px; padding: 5px; }'
            . '.buzzi-info .buzzi-logo { padding-right: 5px; height: 35px; vertical-align: middle; }'
            . '</style>';
        return $content;
    }

    /**
     * @param \Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $content = '<img class="buzzi-logo" src="' . $this->getSkinUrl('images/buzzi/buzzi.png') . '" />'
            . $this->__('To use this extension, you must first sign up for a %s account to receive an authorization ID & secret to access the Buzzi.io service.', '<a href="https://buzzi.io" target="_blank">Buzzi.io</a>');
        return $this->_getInfo($content);
    }
}
