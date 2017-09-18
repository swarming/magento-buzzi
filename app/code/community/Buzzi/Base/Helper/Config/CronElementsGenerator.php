<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Base_Helper_Config_CronElementsGenerator
{
    /**
     * @var \Buzzi_Base_Helper_Config_ElementGenerator
     */
    protected $_elementGenerator;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_elementGenerator = Mage::helper('buzzi_base/config_elementGenerator');
    }

    /**
     * @param \SimpleXMLElement $fields
     * @param bool $isCronOnly
     * @param int &$sortOrder
     * @return void
     */
    public function generate($fields, $isCronOnly, &$sortOrder)
    {
        $depends = [];
        if (!$isCronOnly) {
            $this->_elementGenerator->generateElement(
                $fields,
                'is_cron',
                [
                    'label' => 'Process on Cron',
                    'comment' => 'Disabling can impact performance.',
                    'frontend_type' => 'select',
                    'source_model' => 'adminhtml/system_config_source_yesno',
                    'sort_order' => ++$sortOrder,
                    'show_in_website' => '1'
                ]
            );
            $depends = ['is_cron' => '1'];
        }

        $this->_addCronSettings($fields, $sortOrder, $depends);
    }

    /**
     * @param SimpleXMLElement $fields
     * @param int &$sortOrder
     * @param array $depends
     * @return void
     */
    protected function _addCronSettings($fields, &$sortOrder, $depends)
    {
        $this->_elementGenerator->generateElement(
            $fields,
            'cron_settings',
            [
                'label' => 'Cron Settings',
                'frontend_model' => 'adminhtml/system_config_form_field_heading',
                'sort_order' => ++$sortOrder,
                'depends' => $depends
            ]
        );
        $this->_elementGenerator->generateElement(
            $fields,
            'global_schedule',
            [
                'label' => 'Global Schedule',
                'frontend_type' => 'select',
                'source_model' => 'adminhtml/system_config_source_yesno',
                'sort_order' => ++$sortOrder,
                'depends' => $depends
            ]
        );
        $this->_elementGenerator->generateElement(
            $fields,
            'custom_schedule',
            [
                'label' => 'Custom Schedule',
                'comment' => 'Enter if you know what you are doing. The value is not validated.',
                'frontend_type' => 'select',
                'source_model' => 'adminhtml/system_config_source_yesno',
                'sort_order' => ++$sortOrder,
                'depends' => array_merge($depends, ['global_schedule' => '0'])
            ]
        );
        $this->_elementGenerator->generateElement(
            $fields,
            'cron_schedule',
            [
                'label' => 'Cron Schedule',
                'frontend_type' => 'text',
                'sort_order' => ++$sortOrder,
                'depends' => array_merge($depends, ['global_schedule' => '0', 'custom_schedule' => '1'])
            ]
        );
        $this->_elementGenerator->generateElement(
            $fields,
            'cron_start_time',
            [
                'label' => 'Start Time',
                'frontend_type' => 'time',
                'sort_order' => ++$sortOrder,
                'depends' => array_merge($depends, ['global_schedule' => '0', 'custom_schedule' => '0'])
            ]
        );
        $this->_elementGenerator->generateElement(
            $fields,
            'cron_frequency',
            [
                'label' => 'Frequency',
                'frontend_type' => 'select',
                'source_model' => 'buzzi_base/config_system_source_cronFrequency',
                'sort_order' => ++$sortOrder,
                'depends' => array_merge($depends, ['global_schedule' => '0', 'custom_schedule' => '0'])
            ]
        );
    }
}
