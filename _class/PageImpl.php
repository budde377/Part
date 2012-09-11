<?php
require_once dirname(__FILE__) . '/../_interface/Page.php';
require_once dirname(__FILE__) . '/../_interface/Observable.php';
require_once dirname(__FILE__) . '/../_exception/MalformedParameterException.php';
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

    private $database;
    private $connection;

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


    private $initialValuesHasBeenSet = false;
    private $observers = array();

    /**
     * @param string $id
     * @param DB $database
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

        if (!$this->validID($id) || $this->IDExists($id)) {
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
        if (@preg_match($alias, '') === false) {
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
            $this->existsStatement = $this->connection->prepare("SELECT * FROM Page WHERE page_id=?");
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
            $this->createStatement = $this->connection->prepare("INSERT INTO Page (page_id,template,title,alias) VALUES (?,?,?,?)");
            $this->createStatement->bindParam(1, $this->id);
            $this->createStatement->bindParam(2, $this->template);
            $this->createStatement->bindParam(3, $this->title);
            $this->createStatement->bindParam(4, $this->alias);
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
            /** @var $observer Observer */
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
}
