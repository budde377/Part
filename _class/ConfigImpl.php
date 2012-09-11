<?php
require_once dirname(__FILE__) . '/../_interface/Config.php';
require_once dirname(__FILE__) . '/../_exception/InvalidXMLException.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/15/12
 * Time: 9:01 AM
 */
class ConfigImpl implements Config
{
    private $configFile;
    private $rootPath;

    private $templates = null;
    private $pageElements = null;
    private $preScripts = null;
    private $postScripts = null;
    private $optimizers = null;
    private $mysql = null;

    /**
     * @param SimpleXMLElement $configFile
     * @param string $rootPath
     * @throws InvalidXMLException
     */
    public function __construct(SimpleXMLElement $configFile, $rootPath)
    {
        $namespaces = $configFile->getDocNamespaces();

        if (!count($namespaces)) {
            $configFile->addAttribute('xmlns', 'http://christian-budde.dk/SiteConfig');
        }

        $configFile->asXML();
        $dom = new DOMDocument(1, 'UTF-8');
        $dom->loadXML($configFile->asXML());
        $schema = dirname(__FILE__) . "/../SiteConfig.xsd";

        if (!@$dom->schemaValidate($schema)) {
            throw new InvalidXMLException('SiteConfig', 'ConfigXML');
        }

        $this->configFile = $configFile;
        $this->rootPath = $rootPath;
    }


    /**
     * Will return the link to the template file as a string.
     * This should be relative to a root path provided.
     * If the link is not in list, this will return null.
     * @param $name string
     * @return string | null
     */
    public function getTemplate($name)
    {

        if ($this->templates === null && $this->configFile->templates->getName()) {
            $this->templates = array();
            $templates = $this->configFile->templates->template;
            foreach ($templates as $template) {
                $this->templates[(string)$template] = $this->rootPath . (string)$template['link'];
            }
        }
        if (isset($this->templates[$name])) {
            return $this->templates[$name];
        }
        return null;
    }

    /**
     * Will return PostScripts as an array, with the ClassName as key and the link as value.
     * The link should be relative to a root path provided.
     * @return array
     */
    public function getPostScripts()
    {
        if ($this->postScripts === null && $this->configFile->postScripts->getName()) {
            $postScripts = $this->configFile->postScripts->class;
            $this->postScripts = array();
            foreach ($postScripts as $script) {
                $this->postScripts[(string)$script] = $this->rootPath . $script['link'];
            }
        } else if ($this->postScripts === null) {
            $this->postScripts = array();
        }
        return $this->postScripts;
    }

    /**
     * Will return PreScripts as an array, with the ClassName as key and the link as value.
     * The link should be relative to a root path provided.
     * @return array
     */
    public function getPreScripts()
    {
        if ($this->preScripts === null && $this->configFile->preScripts->getName()) {
            $preScripts = $this->configFile->preScripts->class;
            $this->preScripts = array();
            foreach ($preScripts as $script) {
                $this->preScripts[(string)$script] = $this->rootPath . $script['link'];
            }
        } else if ($this->preScripts === null) {
            $this->preScripts = array();
        }
        return $this->preScripts;
    }


    /**
     * @param string $name name of the pageElement as specified in config
     * @return array | null Array with entrance className, name, path with ClassName,
     * name provided, and absolute path respectively.
     */
    public function getPageElement($name)
    {
        if ($this->pageElements === null && $this->configFile->pageElements->getName()) {
            $this->pageElements = array();
            $elements = $this->configFile->pageElements->class;
            foreach ($elements as $element) {
                $this->pageElements[(string)$element['name']] = array(
                    'name' => (string)$element['name'],
                    'link' => $this->rootPath . (string)$element['link'],
                    'className' => (string)$element);
            }
        }

        if (isset($this->pageElements[$name])) {
            return $this->pageElements[$name];
        }
        return null;

    }

    /**
     * @param $name
     * @return array | null Array with entrance className, name, path with ClassName, name provided, and absolute path respectively.
     */
    public function getOptimizer($name)
    {
        if ($this->optimizers === null && $this->configFile->optimizers->getName()) {
            $this->optimizers = array();
            $optimizer = $this->configFile->optimizers->class;
            foreach ($optimizer as $element) {
                $this->optimizers[(string)$element['name']] = array(
                    'name' => (string)$element['name'],
                    'link' => $this->rootPath . (string)$element['link'],
                    'className' => (string)$element);
            }
        }

        if (isset($this->optimizers[$name])) {
            return $this->optimizers[$name];
        }
        return null;
    }

    /**
     * @return array | null Array with entries host, user, password, prefix, database, or null if not specified
     */
    public function getMySQLConnection()
    {

        if ($this->mysql === null && $this->configFile->MySQLConnection->getName()) {
            $this->mysql = array(
                'user' => (string)$this->configFile->MySQLConnection->username,
                'password' => (string)$this->configFile->MySQLConnection->password,
                'database' => (string)$this->configFile->MySQLConnection->database,
                'host' => (string)$this->configFile->MySQLConnection->host);
        }

        return $this->mysql;

    }
}
