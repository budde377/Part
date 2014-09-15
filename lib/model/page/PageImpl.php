<?php
namespace ChristianBudde\cbweb\model\page;
use ChristianBudde\cbweb\model\ContentLibrary;
use ChristianBudde\cbweb\controller\json\JSONObject;
use ChristianBudde\cbweb\util\db\DB;
use ChristianBudde\cbweb\exception\MalformedParameterException;
use ChristianBudde\cbweb\model\Content;


use ChristianBudde\cbweb\util\Observable;
use ChristianBudde\cbweb\util\Observer;
use ChristianBudde\cbweb\controller\json\PageJSONObjectImpl;

use ChristianBudde\cbweb\model\Variables;
use PDOStatement;
use PDO;
use PDOException;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/16/12
 * Time: 9:15 PM
 * To change this template use File | Settings | File Templates.
 */
class PageImpl implements Page, Observable
{

    private $id;
    private $title = '';
    private $template = '';
    private $alias = '';

    private $lastModified = -1;

    private $hidden = 0;

    private $database;
    private $connection;

    private $contentLibrary;
    private $variables;

    /** @var $existsStatement PDOStatement | null */
    private $existsStatement = null;
    /** @var $createStatement PDOStatement | null */
    private $createStatement = null;
    /** @var $deleteStatement PDOStatement | null */
    private $deleteStatement;
    /** @var $updateIDStatement PDOStatement | null */
    private $updateIDStatement;
    /** @var $updateTitleStatement PDOStatement | null */
    private $updateTitleStatement;
    /** @var $updateTemplateStatement PDOStatement | null */
    private $updateTemplateStatement;
    /** @var $updateAliasStatement PDOStatement | null */
    private $updateAliasStatement;
    /** @var $updateHiddenStatement PDOStatement | null */
    private $updateHiddenStatement;
    /** @var PDOStatement | null */
    private $updateLastModifiedStatement;



    private $initialValuesHasBeenSet = false;
    private $observers = array();

    /**
     * @param string $id
     * @param \ChristianBudde\cbweb\util\db\DB $database
     * @throws MalformedParameterException
     */
    public function __construct($id, DB $database)
    {
        if (!$this->validID($id)) {
            throw new MalformedParameterException('RegEx[a-zA-Z0-9-_]+', 1);
        }
        $this->id = $id;
        $this->database = $database;
        $this->connection = $database->getConnection();

    }

    /**
     * @return string
     */
    public function getID()
    {
        return $this->id;
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
     * @param $id string
     * @return bool
     */
    public function setID($id)
    {
        if ($id == $this->id) {
            return true;
        }

        if (!$this->isValidId($id)) {
            return false;
        }


        if ($this->updateIDStatement === null) {
            $this->updateIDStatement = $this->connection->prepare("UPDATE Page SET page_id = ? WHERE page_id = ?");
        }
        $this->updateIDStatement->execute(array($id, $this->id));
        $this->id = $id;

        $this->notifyObservers(Page::EVENT_ID_UPDATE);

        return true;
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {

        if ($this->updateTitleStatement === null) {
            $this->updateTitleStatement = $this->connection->prepare("UPDATE Page SET title = ? WHERE page_id = ?");
            $this->updateTitleStatement->bindParam(1, $this->title);
            $this->updateTitleStatement->bindParam(2, $this->id);
        }
        $this->title = $title;
        $this->updateTitleStatement->execute();
    }

    /**
     * Set the template, the template should match element in config.
     * @param $template string
     * @return void
     */
    public function setTemplate($template)
    {
        if ($this->updateTemplateStatement === null) {
            $this->updateTemplateStatement = $this->connection->prepare("UPDATE Page SET template = ? WHERE page_id = ?");
            $this->updateTemplateStatement->bindParam(1, $this->template);
            $this->updateTemplateStatement->bindParam(2, $this->id);
        }
        $this->template = $template;
        $this->updateTemplateStatement->execute();
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

        if ($this->updateAliasStatement === null) {
            $this->updateAliasStatement = $this->connection->prepare("UPDATE Page SET alias = ? WHERE page_id = ?");
            $this->updateAliasStatement->bindParam(1, $this->alias);
            $this->updateAliasStatement->bindParam(2, $this->id);
        }
        $this->alias = $alias;
        $this->updateAliasStatement->execute();

        return true;
    }

    /**
     * Will return TRUE if the page exists, else FALSE
     * @return bool
     */
    public function exists()
    {
        return $this->IDExists($this->id);
    }


    private function IDExists($id)
    {
        if ($this->existsStatement === null) {
            $this->existsStatement = $this->connection->prepare("SELECT *, UNIX_TIMESTAMP(last_modified) AS last_modified FROM Page WHERE page_id=?");
        }

        $this->existsStatement->execute(array($id));
        return $this->existsStatement->rowCount() > 0;
    }

    /**
     * Will try and create the Page, if success will return TRUE, else FALSE.
     * If already exists will return FALSE.
     * @return bool
     */
    public function create()
    {


        if ($this->createStatement === null) {
            $this->createStatement = $this->connection->prepare("
            INSERT INTO Page (page_id,template,title,alias,hidden)
            VALUES (?,?,?,?,?)");
            $this->createStatement->bindParam(1, $this->id);
            $this->createStatement->bindParam(2, $this->template);
            $this->createStatement->bindParam(3, $this->title);
            $this->createStatement->bindParam(4, $this->alias);
            $this->createStatement->bindParam(5, $this->hidden);
        }
        try {
            $this->createStatement->execute();
        } catch (PDOException $e) {
            return false;
        }
        $rows = $this->createStatement->rowCount();
        return $rows > 0;
    }

    /**
     * Will delete the page from persistent storage
     * @return bool
     */
    public function delete()
    {
        if ($this->deleteStatement === null) {
            $this->deleteStatement = $this->connection->prepare("DELETE FROM Page WHERE page_id=?");
            $this->deleteStatement->bindParam(1, $this->id);
        }
        $this->deleteStatement->execute();
        $success = $this->deleteStatement->rowCount() > 0;
        if ($success) {
            $this->notifyObservers(Page::EVENT_DELETE);
        }
        return $success;

    }

    private function setInitialValues()
    {
        if (!$this->initialValuesHasBeenSet && $this->exists()) {
            $this->initialValuesHasBeenSet = true;
            $result = $this->existsStatement->fetch(PDO::FETCH_ASSOC);
            $this->id = $result['page_id'];
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

        return $id == $this->id || (strlen($this->getAlias()) && @preg_match($this->getAlias(), $id));

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
            /** @var $observer \ChristianBudde\cbweb\util\Observer */
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

        if($this->updateHiddenStatement === null){
            $this->updateHiddenStatement = $this->connection->prepare("UPDATE Page SET hidden=? WHERE page_id = ?");
            $this->updateHiddenStatement->bindParam(1, $this->hidden);
            $this->updateHiddenStatement->bindParam(2, $this->id);
        }

        $this->hidden = 1;
        $this->updateHiddenStatement->execute();
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

        if($this->updateHiddenStatement === null){
            $this->updateHiddenStatement = $this->connection->prepare("UPDATE Page SET hidden=? WHERE page_id = ?");
            $this->updateHiddenStatement->bindParam(1, $this->hidden);
            $this->updateHiddenStatement->bindParam(2, $this->id);
        }

        $this->hidden = 0;
        $this->updateHiddenStatement->execute();
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
        if($this->updateLastModifiedStatement == null){
            $this->updateLastModifiedStatement = $this->connection->prepare("UPDATE Page SET last_modified=FROM_UNIXTIME(?) WHERE page_id=?");
            $this->updateLastModifiedStatement->bindParam(1, $this->lastModified);
            $this->updateLastModifiedStatement->bindParam(2, $this->id);
        }
        $this->lastModified = time();
        $this->updateLastModifiedStatement->execute();
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
            $this->contentLibrary = new PageContentLibraryImpl($this->database, $this):
            $this->contentLibrary;
    }

    /**
     * Serializes the object to an instance of JSONObject.
     * @return JSONObject
     */
    public function jsonObjectSerialize()
    {
        return new PageJSONObjectImpl($this);
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
}
