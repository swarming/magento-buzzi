<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Model_Queue
{
    /**
     * @var \Buzzi_Publish_Model_Config_General
     */
    protected $_configGeneral;

    /**
     * @var \Buzzi_Publish_Model_Config_Events
     */
    protected $_configEvents;

    /**
     * @var \Buzzi_Publish_Model_Platform
     */
    protected $_platform;

    /**
     * @var \Buzzi_Publish_Model_Submission_PayloadPacker
     */
    protected $_payloadPacker;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configGeneral = Mage::getModel('buzzi_publish/config_general');
        $this->_configEvents = Mage::getSingleton('buzzi_publish/config_events');
        $this->_platform = Mage::getModel('buzzi_publish/platform');
        $this->_payloadPacker = Mage::getModel('buzzi_publish/submission_payloadPacker');
    }

    /**
     * @return \Buzzi_Publish_Model_Submission
     */
    protected function _createMessageModel()
    {
        return Mage::getModel('buzzi_publish/submission');
    }

    /**
     * @return \Buzzi_Publish_Model_Resource_Submission_Collection
     */
    protected function _createSubmissionCollection()
    {
        return Mage::getResourceModel('buzzi_publish/submission_collection');
    }

    /**
     * @param string $eventType
     * @param array $payload
     * @param int|string $storeId
     * @return \Buzzi_Publish_Model_Submission
     */
    public function add($eventType, array $payload, $storeId)
    {
        $transport = new Varien_Object(['event_type' => $eventType, 'store_id' => $storeId, 'payload' => $payload]);
        Mage::dispatchEvent('buzzi_publish_add_to_queue_before', ['transport' => $transport]);
        $payload = (array)$transport->getData('payload');

        $submission = $this->_createMessageModel();
        $submission->setStoreId($storeId);
        $submission->setEventType($eventType);
        $this->_payloadPacker->packPayload($submission, $payload);
        $submission->setStatus(Buzzi_Publish_Model_Submission::STATUS_PENDING);
        $submission->save();

        return $submission;
    }

    /**
     * @param string $eventType
     * @param array $payload
     * @param int|string $storeId
     * @return bool
     */
    public function send($eventType, array $payload, $storeId)
    {
        $submission = $this->add($eventType, $payload, $storeId);
        return $this->_submit($submission);
    }

    /**
     * @param \Buzzi_Publish_Model_Submission $submission
     * @return bool
     */
    protected function _submit($submission)
    {
        if (Buzzi_Publish_Model_Submission::STATUS_DONE == $submission->getStatus()) {
            Mage::throwException(sprintf('Submission with %s ID is already sent.', $submission->getSubmissionId()));
        }

        try {
            $eventId = $this->_platform->send($submission->getEventType(), $this->_payloadPacker->unpackPayload($submission), $submission->getStoreId());
            $errorMessage = null;
        } catch (\Exception $e) {
            $eventId = null;
            $errorMessage = $e->getMessage();
        }

        if ($eventId && $this->_configGeneral->isRemoveImmediately($submission->getStoreId())) {
            $this->_deleteSubmission($submission);
        } else {
            $this->_updateSubmission($submission, $eventId, $errorMessage);
        }

        return (bool)$eventId;
    }

    /**
     * @param \Buzzi_Publish_Model_Submission $submission
     * @param string|bool $eventId
     * @param string|null $errorMessage
     * @return void
     */
    protected function _updateSubmission($submission, $eventId, $errorMessage)
    {
        if ($eventId) {
            $this->_payloadPacker->cleanPayload($submission);

            $submission->setEventId($eventId);
            $submission->setStatus(Buzzi_Publish_Model_Submission::STATUS_DONE);
            $submission->setErrorMessage('');
        } else {
            $submission->setStatus(Buzzi_Publish_Model_Submission::STATUS_FAIL);
            $submission->setErrorMessage($errorMessage);
        }

        $count = $submission->getCounter();
        $submission->setCounter(++$count);

        $submission->save();
    }

    /**
     * @param \Buzzi_Publish_Model_Resource_Submission_Collection $submissions
     * @return int
     */
    protected function _submitSubmissions($submissions)
    {
        $counter = 0;
        /** @var \Buzzi_Publish_Model_Submission $submission */
        foreach ($submissions as $submission) {
            try {
                $counter += $this->_submit($submission) ? 1 : 0;
            } catch (\Exception $e) {
                Mage::logException($e);
            }
        }
        return $counter;
    }

    /**
     * @param int[] $submissionIds
     * @return int
     */
    public function sendByIds(array $submissionIds)
    {
        $submissions = $this->_createSubmissionCollection();
        $submissions->filterNotDone();
        if ($submissionIds) {
            $submissions->filterSubmissionIds($submissionIds);
        }

        return $this->_submitSubmissions($submissions);
    }

    /**
     * @param string $eventType
     * @param int|string $storeId
     * @return int
     */
    public function sendByType($eventType, $storeId)
    {
        $submissions = $this->_createSubmissionCollection();
        $submissions->filterEventType($eventType);
        $submissions->filterPending();
        if ($storeId) {
            $submissions->filterStore($storeId);
        }

        return $this->_submitSubmissions($submissions);
    }

    /**
     * @param int|null $storeId
     * @return int
     */
    public function resendFailed($storeId = null)
    {
        $submissions = $this->_createSubmissionCollection();
        $submissions->filterFailed($this->_configGeneral->getResendMaxTime($storeId));
        if ($storeId) {
            $submissions->filterStore($storeId);
        }

        return $this->_submitSubmissions($submissions);
    }

    /**
     * @param int $delay
     * @param int|null $storeId
     * @return void
     */
    public function deleteDone($delay, $storeId = null)
    {
        $submissions = $this->_createSubmissionCollection();
        $submissions->filterDone();
        $submissions->filterSubmissionTime($delay);
        if ($storeId) {
            $submissions->filterStore($storeId);
        }

        /** @var \Buzzi_Publish_Model_Submission $submission */
        foreach ($submissions as $submission) {
            $this->_deleteSubmission($submission);
        };
    }

    /**
     * @param int[] $submissionIds
     * @return void
     */
    public function deleteByIds(array $submissionIds)
    {
        $submissions = $this->_createSubmissionCollection();
        if ($submissionIds) {
            $submissions->filterSubmissionIds($submissionIds);
        }

        /** @var \Buzzi_Publish_Model_Submission $submission */
        foreach ($submissions as $submission) {
            $this->_deleteSubmission($submission);
        };
    }

    /**
     * @param \Buzzi_Publish_Model_Submission $submission
     * @return void
     */
    protected function _deleteSubmission($submission)
    {
        $this->_payloadPacker->cleanPayload($submission);
        $submission->delete();
    }
}
