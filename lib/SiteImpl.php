<?php
namespace ChristianBudde\cbweb;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 1:12 PM
 */

class SiteImpl implements Site
{
    private $contentLibrary;
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
        return $this->getContentLibrary()->getContent($id);
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

    /**
     * Will get and reuse instance of content library.
     * @return ContentLibrary
     */
    public function getContentLibrary()
    {
        return $this->contentLibrary == null?
            $this->contentLibrary = new SiteContentLibraryImpl($this->db, $this):
            $this->contentLibrary;
    }
}