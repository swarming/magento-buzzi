<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Model_Submission_PayloadPacker
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
        $this->_jsonFile = Mage::getModel('buzzi_base/jsonFile', 'publish');
    }

    /**
     * @param \Buzzi_Publish_Model_Submission $submission
     * @param array $payload
     * @return void
     */
    public function packPayload($submission, array $payload)
    {
        $jsonPayload = json_encode($payload);
        $useFile = $submission->getUseFile() || mb_strlen($jsonPayload) >= Buzzi_Publish_Model_Submission::MAX_PAYLOAD_LENGTH;
        $jsonPayload = $useFile ? $this->_jsonFile->save($jsonPayload) : $jsonPayload;

        $submission->setUseFile($useFile);
        $submission->setPayload($jsonPayload);
    }

    /**
     * @param \Buzzi_Publish_Model_Submission $submission
     * @return array
     */
    public function unpackPayload($submission)
    {
        $jsonPayload = $submission->getUseFile() ? $this->_jsonFile->load($submission->getPayload()) : $submission->getPayload();
        return json_decode($jsonPayload, true);
    }

    /**
     * @param \Buzzi_Publish_Model_Submission $submission
     * @return void
     */
    public function cleanPayload($submission)
    {
        if ($submission->getUseFile()) {
            $this->_jsonFile->delete($submission->getPayload());
            $submission->setPayload('');
        }
    }
}
