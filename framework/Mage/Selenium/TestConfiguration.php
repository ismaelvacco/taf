<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test run configuration
 *
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_TestConfiguration
{
    /**
     * Configuration object instance
     * @var Mage_Selenium_TestConfiguration|null
     */
    private static $instance = null;

    /**
     * File helper instance
     * @var Mage_Selenium_Helper_File|null
     */
    protected $_fileHelper = null;

    /**
     * Config helper instance
     * @var Mage_Selenium_Helper_Config|null
     */
    protected $_configHelper = null;

    /**
     * UIMap helper instance
     * @var Mage_Selenium_Helper_Uimap|null
     */
    protected $_uimapHelper = null;

    /**
     * Data helper instance
     * @var Mage_Selenium_Helper_Data|null
     */
    protected $_dataHelper = null;

    /**
     * Params helper instance
     * @var Mage_Selenium_Helper_Params|null
     */
    protected $_paramsHelper = null;

    /**
     * Data generator helper instance
     * @var Mage_Selenium_Helper_DataGenerator|null
     */
    protected $_dataGeneratorHelper = null;

    /**
     * Cache helper instance
     * @var Mage_Selenium_Helper_Cache
     */
    protected $_cacheHelper = null;

    /**
     * Array of files paths to fixtures
     * @var array
     */
    protected $_configFixtures = array();

    /**
     * Array of class names for test Helper files
     * @var array
     */
    protected $_testHelperClassNames = array();

    /**
     * Handle to log file
     * @var null|resource
     */
    protected $_logFile = null;

    /**
     * Uimap include folder name
     * @var string
     */
    const UIMAP_INCLUDE_FOLDER = '_uimapIncludes';

    /**
     * Constructor defined as private to implement singleton
     */
    private function __construct()
    {
    }

    /**
     * Clone defined as private to implement singleton
     */
    private function __clone()
    {
    }

    /**
     * Get test configuration instance
     *
     * @static
     * @return Mage_Selenium_TestConfiguration
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    /**
     * Initializes test configuration instance which includes:
     * <ul>
     * <li>Initialize configuration
     * <li>Initialize all paths to Fixture files
     * <li>Initialize Fixtures
     * </ul>
     */
    public function init()
    {
        $this->_initConfig();
        $this->_initLogFile($this->getHelper('config')->getLogDir());
        $this->_initFixturesPaths();
        $this->_initTestHelperClassNames();
        $this->_initFixtures();
    }

    /**
     * Initializes and loads configuration data
     * @return Mage_Selenium_TestConfiguration
     */
    protected function _initConfig()
    {
        $this->getHelper('config');
        return $this;
    }

    /**
     * Initialize log file
     *
     * @param string $dirPath
     *
     * @return Mage_Selenium_TestConfiguration
     */
    protected function _initLogFile($dirPath)
    {
        if (is_null($this->_logFile)) {
            $this->_logFile = fopen($dirPath . DIRECTORY_SEPARATOR
                                        . 'selenium-rc-' . date('d-m-Y-H-i-s') . '.log', 'a+');
        }
        return $this;
    }

    /**
     * Initialize all paths to fixture files
     * @return Mage_Selenium_TestConfiguration
     */
    protected function _initFixturesPaths()
    {
        $this->getConfigFixtures();
        return $this;
    }

    /**
     * Initialize all class names for test Helper files
     * @return Mage_Selenium_TestConfiguration
     */
    protected function _initTestHelperClassNames()
    {
        $this->getTestHelperClassNames();
        return $this;
    }

    /**
     * Initializes and loads fixtures data
     * @return Mage_Selenium_TestConfiguration
     */
    protected function _initFixtures()
    {
        $this->getHelper('uimap');
        $this->getHelper('data');
        return $this;
    }

    /**
     * Get log file
     * @return null|resource
     */
    public function getLogFile()
    {
        if (empty($this->_logFile)) {
            $this->_initLogFile($this->getHelper('config')->getLogDir());
        }
        return $this->_logFile;
    }

    /**
     * Get $helperName helper instance
     *
     * @param string $helperName cache|config|data|dataGenerator|file|params|uimap
     *
     * @return Mage_Selenium_Helper_Uimap|Mage_Selenium_Helper_Params|Mage_Selenium_Helper_File|Mage_Selenium_Helper_DataGenerator|Mage_Selenium_Helper_Data|Mage_Selenium_Helper_Config|Mage_Selenium_Helper_Cache
     * @throws OutOfRangeException
     */
    public function getHelper($helperName)
    {
        $class = 'Mage_Selenium_Helper_' . ucfirst($helperName);
        if (!class_exists($class)) {
            throw new OutOfRangeException($class . ' does not exist');
        }
        $variableName = '_' . preg_replace('/^[A-Za-z]/', strtolower($helperName[0]), $helperName) . 'Helper';
        if (is_null($this->$variableName)) {
            if (strtolower($helperName) !== 'params') {
                $this->$variableName = new $class($this);
            } else {
                $this->$variableName = new $class();
            }
        }
        return $this->$variableName;
    }

    /**
     * Get all paths to fixture files and all paths to include uimap elements
     * @return array
     */
    public function getConfigFixtures()
    {
        if (!empty($this->_configFixtures)) {
            return $this->_configFixtures;
        }
        //Get initial path to fixtures
        $frameworkConfig = $this->_configHelper->getConfigFramework();
        $initialPath = SELENIUM_TESTS_BASEDIR . DIRECTORY_SEPARATOR . $frameworkConfig['fixture_base_path'];
        //Get fixtures sequence
        $fallbackOrderFixture = $this->_configHelper->getFixturesFallbackOrder();
        //Get folder names where uimaps are stored for specified area
        $uimapFolders = array();
        $configAreas = $this->_configHelper->getConfigAreas();
        foreach ($configAreas as $areaName => $areaConfig) {
            $uimapFolders[$areaName] = $areaConfig['uimap_path'];
        }
        $separator = preg_quote(DIRECTORY_SEPARATOR);

        $facade = new File_Iterator_Facade();
        foreach ($fallbackOrderFixture as $codePoolName) {
            $projectPath = $initialPath . DIRECTORY_SEPARATOR . $codePoolName;
            if (!is_dir($projectPath)) {
                continue;
            }
            $files = $facade->getFilesAsArray($projectPath, '.yml');
            foreach ($files as $file) {
                if (preg_match('|' . $separator . 'data' . $separator . '|', $file)) {
                    $this->_configFixtures[$codePoolName]['data'][] = $file;
                }
                if (preg_match('|' . $separator . 'uimap' . $separator . '|', $file)) {
                    foreach ($uimapFolders as $areaName => $uimapFolder) {
                        $pattern = implode($separator, array('', 'uimap', $uimapFolder, ''));
                        if (preg_match('|' . $pattern . '|', $file)) {
                            $this->_configFixtures[$codePoolName]['uimap'][$areaName][] = $file;
                        }
                    }
                }
                if (preg_match('|' . $separator . self::UIMAP_INCLUDE_FOLDER . $separator . '|', $file)) {
                    foreach ($uimapFolders as $areaName => $uimapFolder) {
                        $pattern = implode($separator, array('', self::UIMAP_INCLUDE_FOLDER, $uimapFolder)) . '\.yml';
                        if (preg_match('|' . $pattern . '|', $file)) {
                            $this->_configFixtures['uimapInclude'][$areaName][] = $file;
                        }
                    }
                }
            }
        }
        return $this->_configFixtures;
    }

    /**
     * Get all test helper class names
     * @return array
     */
    public function getTestHelperClassNames()
    {
        if (!empty($this->_testHelperClassNames)) {
            return $this->_testHelperClassNames;
        }
        //Get initial path to test helpers
        $frameworkConfig = $this->_configHelper->getConfigFramework();
        $initialPath = SELENIUM_TESTS_BASEDIR . DIRECTORY_SEPARATOR . $frameworkConfig['testsuite_base_path'];
        //Get test helpers sequence
        $fallbackOrderHelper = $this->_configHelper->getHelpersFallbackOrder();

        $facade = new File_Iterator_Facade();
        foreach ($fallbackOrderHelper as $codePoolName) {
            $projectPath = $initialPath . DIRECTORY_SEPARATOR . $codePoolName;
            if (!is_dir($projectPath)) {
                continue;
            }
            $files = $facade->getFilesAsArray($projectPath, 'Helper.php');
            foreach ($files as $file) {
                $className = str_replace($initialPath . DIRECTORY_SEPARATOR, '', $file);
                $className = str_replace(DIRECTORY_SEPARATOR, '_', str_replace('.php', '', $className));
                $array = explode('_', str_replace('_Helper', '', $className));
                $helperName = end($array);
                $this->_testHelperClassNames[$helperName] = $className;
            }
        }
        return $this->_testHelperClassNames;
    }

    /**
     * Get node|value by path
     *
     * @param array  $data Array of Configuration|DataSet data
     * @param string $path XPath-like path to Configuration|DataSet value
     *
     * @return array|string|bool
     */
    public function _descend($data, $path)
    {
        $pathArr = (!empty($path)) ? explode('/', $path) : '';
        $currNode = $data;
        if (!empty($pathArr)) {
            foreach ($pathArr as $node) {
                if (isset($currNode[$node])) {
                    $currNode = $currNode[$node];
                } else {
                    return false;
                }
            }
        }
        return $currNode;
    }
}