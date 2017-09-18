<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Base_Helper_Config_ElementGenerator
{
    /**
     * @var \Buzzi_Base_Helper_Data
     */
    protected $_helper;

    /**
     * @var array
     */
    protected $_elementFields = [
        'label' => null,
        'comment' => null,
        'frontend_type' => null,
        'source_model' => null,
        'frontend_model' => null,
        'backend_model' => null,
        'sort_order' => null,
        'show_in_default' => '1',
        'show_in_website' => '0',
        'show_in_store' => '0'
    ];

    /**
     * @var string[]
     */
    protected $_translateFields = [
        'label',
        'comment'
    ];

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_helper = Mage::helper('buzzi_base');
    }

    /**
     * @param SimpleXMLElement $parent
     * @param string $key
     * @param array $data
     * @return $this
     */
    public function generateElement($parent, $key, $data)
    {
        $subElement = $parent->addChild($key);

        foreach ($this->_elementFields as $fieldName => $defaultValue) {
            $value = isset($data[$fieldName]) ? $data[$fieldName] : $defaultValue;
            if (null === $value) {
                continue;
            }

            $value = in_array($fieldName, $this->_translateFields) ? $this->_helper->__($value) : $value;
            $subElement->addChild($fieldName, $value);
        }

        if (isset($data['depends'])) {
            $this->_processDepends($subElement, (array)$data['depends']);
        }

        return $this;
    }

    /**
     * @param SimpleXMLElement $element
     * @param array $dependFields
     * @return void
     */
    protected function _processDepends($element, $dependFields)
    {
        $depends = $element->addChild('depends');

        $this->_addChildren($depends, $dependFields);
    }

    /**
     * @param SimpleXMLElement $element
     * @param mixed[] $fields
     * @return void
     */
    protected function _addChildren($element,  $fields)
    {
        foreach ($fields as $field => $value) {
            if (is_string($value)) {
                $element->addChild($field, $value);
            } elseif (is_array($value) && !empty($value['@'])) {
                $nestedElement = $element->addChild($field, $value[0]);
                $nestedElement->addAttribute(key($value['@']), current($value['@']));
            } else {
                $nestedElement = $element->addChild($field);
                $this->_addChildren($nestedElement, $value);
            }
        }
    }
}
