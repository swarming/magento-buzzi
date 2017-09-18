<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
class Buzzi_Base_Model_JsonFile
{
    /**
     * @var Varien_Io_File
     */
    protected $_io;

    /**
     * @param string $subFolder
     */
    public function __construct($subFolder = '')
    {
        $this->_io = new Varien_Io_File();
        $this->_io->setAllowCreateFolders(true);
        $this->_io->open(['path' => $this->_iniBaseDir($subFolder)]);
    }

    /**
     * @param string $subFolder
     * @return string
     */
    protected function _iniBaseDir($subFolder = '')
    {
        $baseDir = Mage::getBaseDir('var') . DS . 'buzzi' . DS;
        return $subFolder ? $baseDir . $subFolder . DS : $baseDir;
    }

    /**
     * @param string $jsonData
     * @return string
     * @throws Exception
     */
    public function save($jsonData)
    {
        $fileName = md5($jsonData) . '.json';

        $this->_io->streamOpen($fileName);
        $this->_io->streamLock(true);
        $this->_io->streamWrite($jsonData);
        $this->_io->streamUnlock();
        $this->_io->streamClose();

        return $fileName;
    }

    /**
     * @param string $fileName
     * @return string
     * @throws Mage_Core_Exception
     */
    public function load($fileName)
    {
        if (!$this->_io->fileExists($fileName)) {
            Mage::throwException('File does not exist.');
        }

        $fileContent = $this->_io->read($fileName);
        if (false === $fileContent) {
            Mage::throwException('File is not readable.');
        }
        return $fileContent;
    }

    /**
     * @param string $fileName
     * @return bool
     */
    public function delete($fileName)
    {
        return $this->_io->rm($fileName);
    }
}
