<?php
require_once dirname(__FILE__) . '/../_interface/Config.php';
require_once dirname(__FILE__) . '/FileImpl.php';
require_once dirname(__FILE__) . '/../_exception/InvalidXMLException.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/15/12
 * Time: 9:01 AM
 */
class ConfigImpl implements Config
{
    private $owner;
    private $domain;
    private $configFile;
    private $rootPath;

    private $templates = null;
    private $pageElements = null;
    private $preScripts = null;
    private $postScripts = null;
    private $ajaxRegistrable = null;
    private $optimizers = null;
    private $mysql = null;
    private $debugMode;
    private $defaultPages;
    private $enableUpdater;

    /**
     * @param SimpleXMLElement $configFile
     * @param string $rootPath
     * @throws InvalidXMLException
     */
    public function __construct(SimpleXMLElement $configFile, $rootPath)
    {
        $namespaces = $configFile->getDocNamespaces();

        if (!count($namespaces)) {
            $configFile->addAttribute('xmlns', 'http://christianbud.de/site-config');
        }

        $configFile->asXML();
        $dom = new DOMDocument(1, 'UTF-8');
        $dom->loadXML($configFile->asXML());
        $schema = dirname(__FILE__) . "/../site-config.xsd";

        if (!@$dom->schemaValidate($schema)) {
            throw new InvalidXMLException('site-config', 'ConfigXML');
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
        $this->setUpTemplate();
        return isset($this->templates[$name]) ? $this->templates[$name] : null;
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
     * @return array | null Array with entries host, user, password, prefix, database and File setupFile, or null if not specified
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

    /**
     * Will return a array containing all possible templates by name.
     * @return array
     */
    public function listTemplateNames()
    {
        $this->setUpTemplate();
        $ret = array();
        foreach ($this->templates as $key => $val) {
            $ret[] = $key;
        }
        return $ret;
    }

    /**
     * Will return an array with default pages. Pages hardcoded into the website.
     * The array will have the page title as key and another array, containing alias', as value.
     * @return array
     */
    public function getDefaultPages()
    {
        if ($this->defaultPages === null) {
            $this->defaultPages = array();
            if ($this->configFile->defaultPages->getName()) {
                foreach ($this->configFile->defaultPages->page as $page) {
                    $title = (string)$page;
                    $this->defaultPages[$title]["template"] = (string)$page["template"];
                    $this->defaultPages[$title]["alias"] = (string)$page["alias"];
                    $this->defaultPages[$title]["id"] = (string)$page["id"];
                }

            }
        }
        return $this->defaultPages;
    }

    private function setUpTemplate()
    {
        if ($this->templates === null) {
            $this->templates = array();
            if ($this->configFile->templates->getName()) {
                $templates = $this->configFile->templates->template;
                foreach ($templates as $template) {
                    $this->templates[(string)$template] = $this->rootPath . (string)$template['link'];
                }
            }

        }
    }

    /**
     * Will return AJAXRegistrable as an array, with the num key and an array containing "class_name", "path" and "ajaxId" as value.
     * The link should be relative to a root path provided.
     * @return array
     */
    public function getAJAXRegistrable()
    {

        if ($this->ajaxRegistrable != null) {
            return $this->ajaxRegistrable;
        }

        $this->ajaxRegistrable = array();

        if (!$this->configFile->AJAXRegistrable->getName()) {
            return $this->ajaxRegistrable;
        }


        foreach ($this->configFile->AJAXRegistrable->class as $registrable) {
            $this->ajaxRegistrable[] = array("class_name" => (string)$registrable, "path" => $this->rootPath . $registrable['link'],
                "ajax_id" => (string)$registrable['ajax_id']);
        }
        return $this->ajaxRegistrable;
    }

    /**
     * @return bool
     */
    public function isDebugMode()
    {
        if($this->debugMode != null){
            return $this->debugMode;
        }

        if(!$this->configFile->debugMode->getName()){
            return $this->debugMode = false;
        }

        return $this->debugMode = (string)$this->configFile->debugMode == "true";
    }

    /**
     * @return string Root path
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    /**
     * @return bool
     */
    public function isUpdaterEnabled()
    {
        if($this->enableUpdater !== null){
            return $this->enableUpdater;
        }
        if(!$this->configFile->enableUpdater->getName()){
            return $this->enableUpdater = true;
        }

        return $this->enableUpdater = (string)$this->configFile->enableUpdater == "true";

    }

    /**
     * @return string String containing the domain (name.ext)
     */
    public function getDomain()
    {
        if($this->domain !== null){
            return $this->domain;
        }
        return $this->domain = (string)$this->configFile->siteInfo->domain['name'].".".(string)$this->configFile->siteInfo->domain['extension'];
    }

    /**
     * @return Array containing owner information
     */
    public function getOwner()
    {
        if($this->owner !== null){
            return $this->owner;
        }

        return $this->owner = array(
            'name'=>(string)$this->configFile->siteInfo->owner['name'],
            'mail'=>(string)$this->configFile->siteInfo->owner['mail'],
            'username'=>(string)$this->configFile->siteInfo->owner['username']
        );
    }
}
