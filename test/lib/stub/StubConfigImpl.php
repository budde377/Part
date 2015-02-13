<?php

namespace ChristianBudde\cbweb\test\stub;
use ChristianBudde\cbweb\Config;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/13/12
 * Time: 5:00 PM
 * To change this template use File | Settings | File Templates.
 */
class StubConfigImpl implements Config
{

    private $AJAXRegistrable;
    private $templates;
    private $preScripts;
    private $postScripts;
    private $pageElement;
    private $optimizer;
    private $mysqlConnection;
    private $mailMysqlConnection;
    private $AJAXTypeHandlers;


    private $defaultPages = array();
    private $logPath;
    private $facebookAppCredentials;

    /**
     * @param array $defaultPages
     */
    public function setDefaultPages($defaultPages)
    {
        $this->defaultPages = $defaultPages;
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
        return isset($this->templates[$name]) ? $this->templates[$name] : null;
    }

    /**
     * Will return PreScripts as an array, with the ClassName as key and the link as value.
     * The link should be relative to a root path provided.
     * @return array
     */
    public function getPreScripts()
    {
        return $this->preScripts;
    }

    /**
     * Will return PostScripts as an array, with the ClassName as key and the link as value.
     * The link should be relative to a root path provided.
     * @return array
     */
    public function getPostScripts()
    {
        return $this->postScripts;
    }

    /**
     * @param string $name name of the pageElement as specified in config
     * @return array | null Array with entrance className, name, path with ClassName, name provided, and absolute path respectively.
     */
    public function getPageElement($name)
    {
        return isset($this->pageElement[$name]) ? $this->pageElement[$name] : null;
    }

    /**
     * @param $name
     * @return array | null Array with entrance className, name, path with ClassName, name provided, and absolute path respectively.
     */
    public function getOptimizer($name)
    {
        return isset($this->optimizer[$name]) ? $this->optimizer[$name] : null;
    }

    /**
     * @return array | null Array with entries host, user, password, prefix, database, or null if not specified
     */
    public function getMySQLConnection()
    {
        return $this->mysqlConnection;
    }

    public function setMysqlConnection($mysqlCon)
    {
        $this->mysqlConnection = $mysqlCon;
    }

    public function setOptimizer($optimizer)
    {
        $this->optimizer = $optimizer;
    }

    public function setTemplates($template)
    {
        $this->templates = $template;
    }

    public function setPreScripts($preScripts)
    {
        $this->preScripts = $preScripts;
    }

    public function setPostScripts($postScripts)
    {
        $this->postScripts = $postScripts;
    }

    public function setPageElement($pageElement)
    {
        $this->pageElement = $pageElement;
    }

    /**
     * Will return a array containing all possible templates by name.
     * @return array
     */
    public function listTemplateNames()
    {
        $ret = array();
        foreach ($this->templates as $template) {
            $ret[] = $template;
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
        return $this->defaultPages;
    }

    /**
     * Will return AJAXRegistrable as an array, with the ClassName as key and an array containing "path" and "ajaxId" as value.
     * The link should be relative to a root path provided.
     * @return array
     */
    public function getAJAXRegistrable()
    {
        return $this->AJAXRegistrable;
    }


    /**
     * @param mixed $AJAXRegistrable
     */
    public function setAJAXRegistrable($AJAXRegistrable)
    {
        $this->AJAXRegistrable = $AJAXRegistrable;
    }

    /**
     * @return bool
     */
    public function isDebugMode()
    {
        return false;
    }

    /**
     * @return string Root path
     */
    public function getRootPath()
    {
        return dirname(__FILE__);
    }

    /**
     * @return bool
     */
    public function isUpdaterEnabled()
    {
        return true;
    }

    /**
     * @return string String containing the domain (name.ext)
     */
    public function getDomain()
    {
        return "";
    }

    /**
     * @return Array containing owner information
     */
    public function getOwner()
    {
        return array();
    }

    /**
     * Will path relative to project root to templates.
     * @return string | null Null if template not defined
     */
    public function getTemplateFolderPath()
    {
        return "";
    }

    /**
     * @return string Path to the tmp folder
     */
    public function getTmpFolderPath()
    {
        return "";

    }

    /**
     * @return string Path to the error log.
     */
    public function getLogPath()
    {
        return $this->logPath;

    }

    /**
     * @param mixed $logPath
     */
    public function setLogPath($logPath)
    {
        $this->logPath = $logPath;
    }

    /**
     * @return array | null Array with entries host, user, prefix, database and File setupFile, or null if not specified
     */
    public function getMailMySQLConnection()
    {
        return $this->mailMysqlConnection;
    }

    /**
     * @param mixed $mailMysqlConnection
     */
    public function setMailMysqlConnection($mailMysqlConnection)
    {
        $this->mailMysqlConnection = $mailMysqlConnection;
    }

    /**
     * Will return AJAXTypeHandlers as an array, with the num key and an array containing "class_name" and "path" as value.
     * The link should be relative to a root path provided.
     * @return array
     */
    public function getAJAXTypeHandlers()
    {
        return $this->AJAXTypeHandlers;
    }


    /**
     * @param mixed $AJAXTypeHandlers
     */
    public function setAJAXTypeHandlers($AJAXTypeHandlers)
    {
        $this->AJAXTypeHandlers = $AJAXTypeHandlers;
    }

    /**
     * Returns true if mail support is enabled. Else false.
     * @return bool
     */
    public function isMailManagementEnabled()
    {
        return $this->getMailMySQLConnection() != null;
    }

    /**
     * @return array|null An assoc array with keys: `id` and `secret` which contains the facebook app id and secret respectively. Returns null if not set.
     */
    public function getFacebookAppCredentials()
    {
        return $this->facebookAppCredentials;
    }

    /**
     * @param mixed $facebookAppCredentials
     */
    public function setFacebookAppCredentials($facebookAppCredentials)
    {
        $this->facebookAppCredentials = $facebookAppCredentials;
    }



}
