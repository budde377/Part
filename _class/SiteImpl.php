<?php
require_once dirname(__FILE__).'/../_interface/Site.php';
require_once dirname(__FILE__).'/SiteContentImpl.php';
require_once dirname(__FILE__).'/SiteVariablesImpl.php';
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 1:12 PM
 */

class SiteImpl implements Site
{
    private $contentMap = array();
    private $variables;
    private $db;
    private $lastMod = 0 ;

    function __construct(DB $db)
    {
        $this->db = $db;
    }


    /**
     * Returns and reuses instance of site scoped Content
     * @param string $id
     * @return Content
     */
    public function getContent($id = "")
    {
        return isset($this->contentMap[$id])? $this->contentMap[$id]:$this->contentMap[$id] = new SiteContentImpl($this->db, $this, $id);
    }

    /**
     * Returns and reuses instance of site scoped variables
     * @return Variables
     */
    public function getVariables()
    {
        return $this->variables == null?$this->variables = new SiteVariablesImpl($this->db):$this->variables;
    }

    /**
     * Returns last modified timestamp, 0 if site hasnot been modified
     * @return int 0
     */
    public function lastModified()
    {
        return $this->lastMod == 0?$this->lastMod = $this->getVariables()->getValue("last_modified"):$this->lastMod;
    }


    /**
     * "Modifies" the site by changing the last modified timestamp to now
     * @return int The new timestamp
     */
    public function modify()
    {
        $this->getVariables()->setValue("last_modified", $this->lastMod = time());
        return $this->lastMod;
    }
}