<?php
namespace ChristianBudde\Part\model\page;
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\json\PageObjectImpl;
use ChristianBudde\Part\exception\MalformedParameterException;
use ChristianBudde\Part\model\Content;
use ChristianBudde\Part\model\ContentLibrary;
use ChristianBudde\Part\model\Variables;
use ChristianBudde\Part\util\Observer;
use PDO;
use PDOException;
use PDOStatement;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/16/12
 * Time: 9:15 PM
 * To change this template use File | Settings | File Templates.
 */
class PageImpl implements Page
{

    private $page_id;
    private $title = '';
    private $template = '';
    private $alias = '';

    private $lastModified = -1;

    private $hidden = 0;

    private $database;
    private $connection;

    private $contentLibrary;
    private $variables;

    /** @var $existsStm PDOStatement */
    private $existsStm;
    /** @var $createStm PDOStatement */
    private $createStm;
    /** @var $deleteStm PDOStatement */
    private $deleteStm;
    /** @var $updateIDStm PDOStatement */
    private $updateIDStm;
    /** @var $updateTitleStm PDOStatement */
    private $updateTitleStm;
    /** @var $updateTemplateStm PDOStatement */
    private $updateTemplateStm;
    /** @var $updateAliasStm PDOStatement */
    private $updateAliasStm;
    /** @var $updateHiddenStm PDOStatement */
    private $updateHiddenStm;
    /** @var PDOStatement */
    private $updLastModStm;



    private $isInitialized = false;
    private $observers = array();
    private $container;

    /**
     * @param BackendSingletonContainer $container
     * @param string $id
     * @throws MalformedParameterException
     */
    public function __construct(BackendSingletonContainer $container, $id)
    {
        if (!$this->validID($id)) {
            throw new MalformedParameterException('RegEx[a-zA-Z0-9-_]+', 1);
        }
        $database = $container->getDBInstance();
        $this->page_id = $id;
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
        $this->setInitialValues();
        return $this->title;
    }

    /**
     * The return string should match a template in some config.
     * @return string
     */
    public function getTemplate()
    {
        $this->setInitialValues();
        return $this->template;
    }

    /**
     * This will return the alias as a string.
     * @return string
     */
    public function getAlias()
    {
        $this->setInitialValues();
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


        if ($this->updateIDStm === null) {
            $this->updateIDStm = $this->connection->prepare("UPDATE Page SET page_id = ? WHERE page_id = ?");
        }
        $this->updateIDStm->execute(array($page_id, $this->page_id));
        $this->page_id = $page_id;

        $this->notifyObservers(Page::EVENT_ID_UPDATE);

        return true;
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {

        if ($this->updateTitleStm === null) {
            $this->updateTitleStm = $this->connection->prepare("UPDATE Page SET title = ? WHERE page_id = ?");
            $this->updateTitleStm->bindParam(1, $this->title);
            $this->updateTitleStm->bindParam(2, $this->page_id);
        }
        $this->title = $title;
        $this->updateTitleStm->execute();
    }

    /**
     * Set the template, the template should match element in config.
     * @param $template string
     * @return void
     */
    public function setTemplate($template)
    {
        if ($this->updateTemplateStm === null) {
            $this->updateTemplateStm = $this->connection->prepare("UPDATE Page SET template = ? WHERE page_id = ?");
            $this->updateTemplateStm->bindParam(1, $this->template);
            $this->updateTemplateStm->bindParam(2, $this->page_id);
        }
        $this->template = $template;
        $this->updateTemplateStm->execute();
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

        if ($this->updateAliasStm === null) {
            $this->updateAliasStm = $this->connection->prepare("UPDATE Page SET alias = ? WHERE page_id = ?");
            $this->updateAliasStm->bindParam(1, $this->alias);
            $this->updateAliasStm->bindParam(2, $this->page_id);
        }
        $this->alias = $alias;
        $this->updateAliasStm->execute();

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
        if ($this->existsStm === null) {
            $this->existsStm = $this->connection->prepare("SELECT *, UNIX_TIMESTAMP(last_modified) AS last_modified FROM Page WHERE page_id=?");
        }

        $this->existsStm->execute(array($id));
        return $this->existsStm->rowCount() > 0;
    }

    /**
     * Will try and create the Page, if success will return TRUE, else FALSE.
     * If already exists will return FALSE.
     * @return bool
     */
    public function create()
    {


        if ($this->createStm === null) {
            $this->createStm = $this->connection->prepare("
            INSERT INTO Page (page_id,template,title,alias,hidden)
            VALUES (?,?,?,?,?)");
            $this->createStm->bindParam(1, $this->page_id);
            $this->createStm->bindParam(2, $this->template);
            $this->createStm->bindParam(3, $this->title);
            $this->createStm->bindParam(4, $this->alias);
            $this->createStm->bindParam(5, $this->hidden);
        }
        try {
            $this->createStm->execute();
        } catch (PDOException $e) {
            return false;
        }
        $rows = $this->createStm->rowCount();
        return $rows > 0;
    }

    /**
     * Will delete the page from persistent storage
     * @return bool
     */
    public function delete()
    {
        if ($this->deleteStm === null) {
            $this->deleteStm = $this->connection->prepare("DELETE FROM Page WHERE page_id=?");
            $this->deleteStm->bindParam(1, $this->page_id);
        }
        $this->deleteStm->execute();
        $success = $this->deleteStm->rowCount() > 0;
        if ($success) {
            $this->notifyObservers(Page::EVENT_DELETE);
        }
        return $success;

    }

    private function setInitialValues()
    {
        if (!$this->isInitialized && $this->exists()) {
            $this->isInitialized = true;
            $result = $this->existsStm->fetch(PDO::FETCH_ASSOC);
            $this->page_id = $result['page_id'];
            $this->alias = $result['alias'];
            $this->title = $result['title'];
            $this->template = $result['template'];
            $this->lastModified = intval($result['last_modified']);
            $this->hidden = $result['hidden'];
        }
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


    public function attachObserver(Observer $observer)
    {
        $this->observers[] = $observer;
    }

    private function notifyObservers($type)
    {
        foreach ($this->observers as $observer) {
            /** @var $observer \ChristianBudde\Part\util\Observer */
            $observer->onChange($this, $type);
        }
    }

    public function detachObserver(Observer $observer)
    {
        foreach ($this->observers as $key => $ob) {
            if ($ob === $observer) {
                unset($this->observers[$key]);
            }
        }
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
        return strlen($alias)==0 || @preg_match($alias,'') !== false;
    }

    /**
     * @return bool Return TRUE if the page has been marked as hidden, else false
     */
    public function isHidden()
    {
        $this->setInitialValues();
        return $this->hidden > 0;
    }

    /**
     * This will mark the page as hidden.
     * If the page is already hidden, nothing will happen.
     * @return void
     */
    public function hide()
    {
        if($this->isHidden())
            return;

        if($this->updateHiddenStm === null){
            $this->updateHiddenStm = $this->connection->prepare("UPDATE Page SET hidden=? WHERE page_id = ?");
            $this->updateHiddenStm->bindParam(1, $this->hidden);
            $this->updateHiddenStm->bindParam(2, $this->page_id);
        }

        $this->hidden = 1;
        $this->updateHiddenStm->execute();
    }

    /**
     * This will un-mark the page as hidden, iff it is hidden.
     * If the page is not hidden, nothing will happen.
     * @return void
     */
    public function show()
    {
        if(!$this->isHidden())
            return;

        if($this->updateHiddenStm === null){
            $this->updateHiddenStm = $this->connection->prepare("UPDATE Page SET hidden=? WHERE page_id = ?");
            $this->updateHiddenStm->bindParam(1, $this->hidden);
            $this->updateHiddenStm->bindParam(2, $this->page_id);
        }

        $this->hidden = 0;
        $this->updateHiddenStm->execute();
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
        $this->setInitialValues();
        return $this->lastModified;
    }

    /**
     * Will update the page with a new modify timestamp
     * @return int New modified time
     */
    public function modify()
    {
        if($this->updLastModStm == null){
            $this->updLastModStm = $this->connection->prepare("UPDATE Page SET last_modified=FROM_UNIXTIME(?) WHERE page_id=?");
            $this->updLastModStm->bindParam(1, $this->lastModified);
            $this->updLastModStm->bindParam(2, $this->page_id);
        }
        $this->lastModified = time();
        $this->updLastModStm->execute();
        return $this->lastModified;

    }

    /**
     * @return Variables Will return and reuse instance of variables
     */
    public function getVariables()
    {
        return $this->variables == null? $this->variables = new PageVariablesImpl($this->database, $this):$this->variables;
    }

    /**
     * Will return and reuse a ContentLibrary instance.
     * @return ContentLibrary
     */
    public function getContentLibrary()
    {
        return $this->contentLibrary == null?
            $this->contentLibrary = new PageContentLibraryImpl($this->container, $this):
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
