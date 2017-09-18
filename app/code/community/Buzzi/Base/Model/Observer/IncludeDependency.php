<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Base_Model_Observer_IncludeDependency
{
    const CONFIG_PATH_PSR0NAMESPACES = 'global/psr0_namespaces';

    /**
     * @var bool
     */
    protected static $_processed = false;

    /**
     * @var string[][]
     */
    protected $_includes = [
        [BP, 'lib', 'GuzzleHttp', 'functions_include.php'],
        [BP, 'lib', 'GuzzleHttp', 'Promise', 'functions_include.php'],
        [BP, 'lib', 'GuzzleHttp', 'Psr7', 'functions_include.php'],
        [BP, 'vendor', 'autoload.php']
    ];

    /**
     * @param mixed[] $args
     * @return \Buzzi_Base_Model_SplAutoloader
     */
    protected function _createSplAutoloaderModel(array $args = [])
    {
        return Mage::getModel("buzzi_base/splAutoloader", $args);
    }

    /**
     * @param \Varien_Event_Observer $observer
     * @return void
     * @throws \Exception
     */
    public function execute(Varien_Event_Observer $observer)
    {
        if (false === self::$_processed) {
            self::$_processed = true;

            $this->_registerNamespaces();

            foreach ($this->_includes as $pathParts) {
                $this->includeFile(implode(DS, $pathParts));
            }
        }
    }

    /**
     * @return void
     */
    protected function _registerNamespaces()
    {
        foreach ($this->getNamespacesToRegister() as $namespace) {
            $namespacePath = Mage::getBaseDir('lib') . DS . $namespace;
            if (is_dir($namespacePath)) {
                $autoloader = $this->_createSplAutoloaderModel([$namespace, $namespacePath]);
                $autoloader->register();
            }
        }
    }

    /**
     * @return array
     */
    protected function getNamespacesToRegister()
    {
        $node = Mage::getConfig()->getNode(self::CONFIG_PATH_PSR0NAMESPACES);

        $namespaces = [];
        if ($node && is_array($node->asArray())) {
            $namespaces = array_keys($node->asArray());
        }
        return $namespaces;
    }

    /**
     * @param string $filePath
     * @return bool
     */
    protected function includeFile($filePath)
    {
        if (file_exists($filePath)) {
            require_once $filePath;
            return true;
        }
        return false;
    }
}
