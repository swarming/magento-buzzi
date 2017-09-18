<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Consume_Model_Delivery_PayloadPacker
{
    /**
     * @var \Buzzi_Base_Model_JsonFile
     */
    protected $_jsonFile;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_jsonFile = Mage::getModel('buzzi_base/jsonFile', 'consume');
    }

    /**
     * @param \Buzzi_Consume_Model_Delivery $delivery
     * @param array $payload
     * @return void
     */
    public function packPayload($delivery, array $payload)
    {
        $jsonPayload = json_encode($payload);
        $useFile = $delivery->getUseFile() || mb_strlen($jsonPayload) >= Buzzi_Consume_Model_Delivery::MAX_PAYLOAD_LENGTH;
        $jsonPayload = $useFile ? $this->_jsonFile->save($jsonPayload) : $jsonPayload;

        $delivery->setUseFile($useFile);
        $delivery->setPayload($jsonPayload);
    }

    /**
     * @param \Buzzi_Consume_Model_Delivery $delivery
     * @return array
     */
    public function unpackPayload($delivery)
    {
        $jsonPayload = $delivery->getUseFile() ? $this->_jsonFile->load($delivery->getPayload()) : $delivery->getPayload();
        return json_decode($jsonPayload, true);
    }

    /**
     * @param \Buzzi_Consume_Model_Delivery $delivery
     * @return void
     */
    public function cleanPayload($delivery)
    {
        if ($delivery->getUseFile()) {
            $this->_jsonFile->delete($delivery->getPayload());
            $delivery->setPayload('');
        }
    }
}
