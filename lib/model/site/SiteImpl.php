<?php
namespace ChristianBudde\Part\model\site;
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\model\Content;
use ChristianBudde\Part\model\ContentLibrary;
use ChristianBudde\Part\model\SiteVariablesImpl;
use ChristianBudde\Part\model\Variables;


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
    private $container;

    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
        $this->db = $container->getDBInstance();
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

    /**
     * @return TypeHandler
     */
    public function generateTypeHandler()
    {
        return $this->container->getTypeHandlerLibraryInstance()->getSiteTypeHandlerInstance($this);
    }
}