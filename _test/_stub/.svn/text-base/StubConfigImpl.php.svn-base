<?php
require_once dirname(__FILE__) . '/../../_interface/Config.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/13/12
 * Time: 5:00 PM
 * To change this template use File | Settings | File Templates.
 */
class StubConfigImpl implements Config
{

    private $template;
    private $preScripts;
    private $postScripts;
    private $pageElement;
    private $optimizer;
    private $mysqlCon;

    /**
     * Will return the link to the template file as a string.
     * This should be relative to a root path provided.
     * If the link is not in list, this will return null.
     * @param $name string
     * @return string | null
     */
    public function getTemplate($name)
    {
        return isset($this->template[$name]) ? $this->template[$name] : null;
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
        return $this->mysqlCon;
    }

    public function setMysqlCon($mysqlCon)
    {
        $this->mysqlCon = $mysqlCon;
    }

    public function setOptimizer($optimizer)
    {
        $this->optimizer = $optimizer;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
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
}
