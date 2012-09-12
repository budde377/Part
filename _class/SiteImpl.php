<?php
require_once dirname(__FILE__) . '/SimpleDBImpl.php';
require_once dirname(__FILE__) . '/PageOrderImpl.php';
require_once dirname(__FILE__) . '/../_interface/Observable.php';
require_once dirname(__FILE__) . '/../_interface/Site.php';
require_once dirname(__FILE__) . '/../_trait/EncryptionTrait.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 7/16/12
 * Time: 7:39 PM
 */
class SiteImpl implements Site, Observable
{
    use EncryptionTrait;

    private static $key = 'GXSqvTuLKsx1gW3VwQHQ';

    private $title;
    private $database;
    private $connection;

    private $user;
    private $password;
    private $host;
    private $db;


    /** @var $updateTitleStatement PDOStatement | null */
    private $updateTitleStatement;
    /** @var $updateDBStatement PDOStatement | null */
    private $updateDBStatement;
    /** @var $updateHostStatement PDOStatement | null */
    private $updateHostStatement;
    /** @var $updatePasswordStatement PDOStatement | null */
    private $updatePasswordStatement;
    /** @var $updateUserStatement PDOStatement | null */
    private $updateUserStatement;
    /** @var $existsStatement PDOStatement | null */
    private $existsStatement;
    /** @var $createStatement PDOStatement | null */
    private $createStatement;
    /** @var $deleteStatement PDOStatement | null  */
    private $deleteStatement;

    private $initialValuesHasBeenSet = false;

    /** @var $pageOrder PageOrder | null */
    private $pageOrder;

    private $observers = array();


    /**
     * @param string $title
     * @param DB $database
     */
    public function __construct($title, DB $database)
    {

        $this->title = $title;
        $this->database = $database;
        $this->connection = $database->getConnection();


    }


    /**
     * Will return the title of the site.
     * The title must be unique in a way which conforms to the implementation
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Will set the title and will return false if the title is not unique.
     * @param string $title
     * @return bool
     */
    public function setTitle($title)
    {
        if ($title == $this->title) {
            return true;
        }

        if ($this->titleExists($title)) {
            return false;
        }

        if ($this->updateTitleStatement === null) {
            $this->updateTitleStatement = $this->connection->prepare("UPDATE Sites SET title = ? WHERE title = ?");
        }
        $this->updateTitleStatement->execute(array($title, $this->title));
        $this->title = $title;
        $this->updateTitleStatement->execute();
        $this->notifyObservers(Site::EVENT_TITLE_UPDATE);
        return true;
    }

    /**
     * Will set the host of the site
     * @param string $host
     * @return void
     */
    public function setHost($host)
    {
        if ($this->updateHostStatement == null) {
            $this->updateHostStatement = $this->connection->prepare("UPDATE Sites SET host=? WHERE title=?");
            $this->updateHostStatement->bindParam(1, $this->host);
            $this->updateHostStatement->bindParam(2, $this->title);
        }
        $this->host = $host;
        $this->updateHostStatement->execute();
    }

    /**
     * Will set the database of the site
     * @param string $database
     * @return void
     */
    public function setDatabase($database)
    {
        if ($this->updateDBStatement == null) {
            $this->updateDBStatement = $this->connection->prepare("UPDATE Sites SET db=? WHERE title=?");
            $this->updateDBStatement->bindParam(1, $this->db);
            $this->updateDBStatement->bindParam(2, $this->title);
        }
        $this->db = $database;
        $this->updateDBStatement->execute();
    }

    /**
     * Will set the user of the site
     * @param string $user
     * @return void
     */
    public function setUser($user)
    {

        if ($this->updateUserStatement == null) {
            $this->updateUserStatement = $this->connection->prepare("UPDATE Sites SET user=? WHERE title=?");
            $this->updateUserStatement->bindParam(1, $this->user);
            $this->updateUserStatement->bindParam(2, $this->title);
        }
        $this->user = $user;
        $this->updateUserStatement->execute();
    }

    /**
     * Will set the password of the site
     * @param string $password
     * @return void
     */
    public function setPassword($password)
    {
        $password = $this->encrypt($password,self::$key);
        if ($this->updatePasswordStatement == null) {
            $this->updatePasswordStatement = $this->connection->prepare("UPDATE Sites SET password=? WHERE title=?");
            $this->updatePasswordStatement->bindParam(1, $this->password);
            $this->updatePasswordStatement->bindParam(2, $this->title);
        }
        $this->password = $password;
        $this->updatePasswordStatement->execute();

    }

    /**
     * Will return the host of the site
     * @return string
     */
    public function getHost()
    {
        $this->setInitialValues();
        return $this->host;
    }

    /**
     * Will return the database of the site
     * @return string
     */
    public function getDatabase()
    {
        $this->setInitialValues();
        return $this->db;
    }

    /**
     * Will return the user of the site
     * @return string
     */
    public function getUser()
    {
        $this->setInitialValues();
        return $this->user;
    }

    /**
     * Will return the password of the site
     * @return string
     */
    public function getPassword()
    {
        $this->setInitialValues();
        return $this->decrypt($this->password,self::$key);
    }

    private function titleExists($title)
    {
        if ($this->existsStatement === null) {
            $this->existsStatement = $this->connection->prepare("SELECT * FROM Sites WHERE title=?");
        }

        $this->existsStatement->execute(array($title));
        return $this->existsStatement->rowCount() > 0;
    }

    /**
     * Create the site on persistent storage
     * @return bool Will return TRUE if site has been created on persistent storage, else FALSE
     */
    public function create()
    {
        if ($this->exists()){
            return false;
        }

        if ($this->createStatement == null) {
            $this->createStatement = $this->connection->prepare("
            INSERT INTO Sites (title,host,db,password,user) VALUES (?,?,?,?,?)");
            $this->createStatement->bindParam(1, $this->title);
            $this->createStatement->bindParam(2, $this->host);
            $this->createStatement->bindParam(3, $this->db);
            $this->createStatement->bindParam(4, $this->password);
            $this->createStatement->bindParam(5, $this->user);
        }

        $this->createStatement->execute();

        return $this->exists();
    }

    /**
     * Checks if the site has been created on persistent storage.
     * @return bool Will return FALSE if site does not exists on persistent storage, else TRUE
     */
    public function exists()
    {
        return $this->titleExists($this->title);
    }

    /**
     * Will delete site from persistent storage
     * @return bool Will return FALSE on failure, else TRUE
     */
    public function delete()
    {
        if (!$this->exists()) {
            return false;
        }
        if ($this->deleteStatement == null) {
            $this->deleteStatement = $this->connection->prepare("DELETE FROM Sites WHERE title = ?");
            $this->deleteStatement->bindParam(1, $this->title);
        }
        $this->deleteStatement->execute();
        if (!$this->exists()) {
            $this->notifyObservers(Site::EVENT_DELETE);
            return true;
        }
        return false;
    }

    private function setInitialValues()
    {
        if (!$this->initialValuesHasBeenSet && $this->exists()) {
            $this->initialValuesHasBeenSet = true;
            $result = $this->existsStatement->fetch(PDO::FETCH_ASSOC);
            $this->title = $result['title'];
            $this->db = $result['db'];
            $this->user = $result['user'];
            $this->password = $result['password'];
            $this->host = $result['host'];
        }
    }

    /**
     * @return PageOrder | bool Will return PageOrder on success, else false if connection is not valid
     */
    public function getPageOrder()
    {
        if ($this->pageOrder == null) {
            try {
                $connection = $this->connection = new PDO(
                    'mysql:dbname=' . $this->getDatabase() . ';host=' . $this->getHost(),
                    $this->getUser(),
                    $this->getPassword(),
                    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                $db = new SimpleDBImpl($connection);
                $this->pageOrder = new PageOrderImpl($db);
            } catch (PDOException $e) {
                return false;
            }
        }
        return $this->pageOrder;
    }

    public function attachObserver(Observer $observer)
    {
        $this->observers[] = $observer;
    }

    public function detachObserver(Observer $observer)
    {
        foreach ($this->observers as $key => $o) {
            if ($o === $observer) {
                unset($this->observers[$key]);
            }
        }
    }

    private function notifyObservers($event)
    {
        foreach ($this->observers as $observer) {
            /** @var $observer Observer */
            $observer->onChange($this, $event);
        }
    }

}
