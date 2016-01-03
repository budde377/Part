<?php
namespace ChristianBudde\Part\model\page;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\json\PageObjectImpl;
use ChristianBudde\Part\exception\MalformedParameterException;
use ChristianBudde\Part\model\Content;
use ChristianBudde\Part\model\ContentLibrary;
use ChristianBudde\Part\model\Variables;


/**
 * User: budde
 * Date: 6/16/12
 * Time: 9:15 PM
 */
class PageImpl implements Page
{

    private $page_id;
    private $title = '';
    private $template = '';
    private $alias = '';

    private $lastModified = -1;

    private $hidden = false;

    private $database;
    private $connection;

    private $contentLibrary;
    private $variables;


    private $container;
    private $pageOrder;

    /**
     * @param BackendSingletonContainer $container
     * @param PageOrderImpl $pageOrder
     * @param string $id
     * @param string $title
     * @param string $template
     * @param string $alias
     * @param int $lastModified
     * @param bool $hidden
     * @throws MalformedParameterException
     */
    public function __construct(BackendSingletonContainer $container, PageOrderImpl $pageOrder, $id, $title, $template, $alias, $lastModified, $hidden)
    {
        if(!$this->validID($id)){
            throw new MalformedParameterException('RegEx[a-zA-Z0-9-_]+', 2);
        }

        $this->pageOrder = $pageOrder;
        $database = $container->getDBInstance();
        $this->page_id = $id;
        $this->title = $title;
        $this->template = $template;
        $this->alias = $alias;
        $this->lastModified = $lastModified;
        $this->hidden = $hidden;
        $this->container = $container;
        $this->database = $database;
        $this->connection = $database->getConnection();

    }

    /**
     * @return string
     */
    public function getID()
    {
        return $this->page_id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * The return string should match a template in some config.
     * @return string
     */
    public function getTemplate()
    {
        if($this->template != ''){
            return $this->template;
        }

        if(($defaultTemplate = $this->container->getConfigInstance()->getDefaultTemplateName()) == null){
            return '';
        }

        return $defaultTemplate;
    }

    /**
     * This will return the alias as a string.
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set the id of the page. The ID should be of type [a-zA-Z0-9-_]+
     * If the id does not conform to above, it will return FALSE, else, TRUE
     * Also the ID must be unique, if not it will fail and return FALSE
     * @param $page_id string
     * @return bool
     */
    public function setID($page_id)
    {
        if ($page_id == $this->page_id) {
            return true;
        }

        if (!$this->isValidId($page_id)) {
            return false;
        }

        if(!$this->pageOrder->changeId($this, $page_id)){
            return false;
        }

        $this->page_id = $page_id;
        return true;
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {

        $updateTitleStm = $this->connection->prepare("UPDATE Page SET title = ? WHERE page_id = ?");
        $updateTitleStm->bindParam(1, $this->title);
        $updateTitleStm->bindParam(2, $this->page_id);
        $this->title = $title;
        $updateTitleStm->execute();
    }

    /**
     * Set the template, the template should match element in config.
     * @param $template string
     * @return void
     */
    public function setTemplate($template)
    {
        $updateTemplateStm = $this->connection->prepare("UPDATE Page SET template = :template WHERE page_id = :page_id");
        $updateTemplateStm->bindParam(":template", $this->template);
        $updateTemplateStm->bindParam(":page_id", $this->page_id);
        $this->template = $template;
        $updateTemplateStm->execute();
    }

    /**
     * Will set the alias. This should support RegEx
     * @param $alias string
     * @return bool
     */
    public function setAlias($alias)
    {
        if (!$this->isValidAlias($alias)) {
            return false;
        }

        $updateAliasStm = $this->connection->prepare("UPDATE Page SET alias = :alias WHERE page_id = :page_id");
        $updateAliasStm->bindParam(":alias", $this->alias);
        $updateAliasStm->bindParam(":page_id", $this->page_id);
        $this->alias = $alias;
        $updateAliasStm->execute();

        return true;
    }

    /**
     * Will return TRUE if the page exists, else FALSE
     * @return bool
     */
    public function exists()
    {
        return $this->IDExists($this->page_id);
    }


    private function IDExists($id)
    {

        $existsStm = $this->connection->prepare("SELECT *, UNIX_TIMESTAMP(last_modified) AS last_modified FROM Page WHERE page_id=?");
        $existsStm->execute(array($id));
        return $existsStm->rowCount() > 0;
    }


    /**
     * Will delete the page from persistent storage
     * @return bool
     */
    public function delete()
    {

        return $this->pageOrder->deletePage($this);

    }


    /**
     * This will return TRUE if the $id match the page else FALSE.
     * @param $id string
     * @return bool
     */
    public function match($id)
    {

        return $id == $this->page_id || (strlen($this->getAlias()) && @preg_match($this->getAlias(), $id));

    }

    private function validID($id)
    {
        return preg_match('/^[a-z0-9-_]+$/i', $id);
    }



    /**
     * Return TRUE if is editable, else FALSE
     * @return bool
     */
    public function isEditable()
    {
        return true;
    }

    /**
     * Check if given id is valid
     * @param String $id
     * @return bool
     */
    public function isValidId($id)
    {
        return $this->validID($id) && !$this->IDExists($id);
    }

    /**
     * Check if given alias is valid
     * @param String $alias
     * @return bool
     */
    public function isValidAlias($alias)
    {
        return strlen($alias) == 0 || @preg_match($alias, '') !== false;
    }

    /**
     * @return bool Return TRUE if the page has been marked as hidden, else false
     */
    public function isHidden()
    {
        return boolval($this->hidden);
    }

    /**
     * This will mark the page as hidden.
     * If the page is already hidden, nothing will happen.
     * @return void
     */
    public function hide()
    {
        if ($this->isHidden())
            return;

        $updateHiddenStm = $this->connection->prepare("UPDATE Page SET hidden=? WHERE page_id = ?");
        $updateHiddenStm->bindParam(1, $this->hidden);
        $updateHiddenStm->bindParam(2, $this->page_id);
        $this->hidden = true;
        $updateHiddenStm->execute();
    }

    /**
     * This will un-mark the page as hidden, iff it is hidden.
     * If the page is not hidden, nothing will happen.
     * @return void
     */
    public function show()
    {
        if (!$this->isHidden())
            return;

        $updateHiddenStm = $this->connection->prepare("UPDATE Page SET hidden=? WHERE page_id = ?");
        $updateHiddenStm->bindParam(1, $this->hidden);
        $updateHiddenStm->bindParam(2, $this->page_id);
        $this->hidden = false;
        $updateHiddenStm->execute();
    }

    /**
     * This will return an object used to retrieve the content.
     * @param null | string $id Optional parameter specifying an id for the content.
     * @return Content
     */
    public function getContent($id = "")
    {
        return $this->getContentLibrary()->getContent($id);

    }

    /**
     * Returns the time of last modification. This is for caching, and should reflect all content of the page.
     * @return int
     */
    public function lastModified()
    {
        return $this->lastModified;
    }

    /**
     * Will update the page with a new modify timestamp
     * @return int New modified time
     */
    public function modify()
    {
        $updLastModStm = $this->connection->prepare("UPDATE Page SET last_modified=FROM_UNIXTIME(?) WHERE page_id=?");
        $updLastModStm->bindParam(1, $this->lastModified);
        $updLastModStm->bindParam(2, $this->page_id);
        $this->lastModified = time();
        $updLastModStm->execute();
        return $this->lastModified;

    }

    /**
     * @return Variables Will return and reuse instance of variables
     */
    public function getVariables()
    {
        return $this->variables == null ? $this->variables = new PageVariablesImpl($this->database, $this) : $this->variables;
    }

    /**
     * Will return and reuse a ContentLibrary instance.
     * @return ContentLibrary
     */
    public function getContentLibrary()
    {
        return $this->contentLibrary == null ?
            $this->contentLibrary = new PageContentLibraryImpl($this->container, $this) :
            $this->contentLibrary;
    }

    /**
     * Serializes the object to an instance of JSONObject.
     * @return Object
     */
    public function jsonObjectSerialize()
    {
        return new PageObjectImpl($this);
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return $this->jsonObjectSerialize()->jsonSerialize();
    }

    /**
     * @return TypeHandler
     */
    public function generateTypeHandler()
    {
        return $this->container->getTypeHandlerLibraryInstance()->getPageTypeHandlerInstance($this);
    }
}
