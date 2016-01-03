<?php

namespace ChristianBudde\Part;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/13/12
 * Time: 5:00 PM
 * To change this template use File | Settings | File Templates.
 */
class StubConfigImpl implements Config
{

    private $variables;
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
    private $domain = "";

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
        return $this->domain;
    }


    /**
     * @return array containing owner information
     */
    public function getOwner()
    {
        return array();
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


    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param mixed $variables
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;
    }



    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->variables[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->variables[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->variables[$offset] = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->variables[$offset]);
    }

    /**
     * Lists the folders where to look for other templates.
     * @return string[]
     */
    public function listTemplateFolders()
    {
        return [];
    }

    /**
     * Will path relative to project root to templates.
     * @param string $name The name of the template
     * @return null|string Null if template not defined
     */
    public function getTemplateFolderPath($name)
    {
        return "";
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return bool
     */
    public function isCacheEnabled()
    {
        return false;
    }
}
