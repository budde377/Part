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

    private $db_user;
    private $db_password;
    private $db_host;
    private $db_db;

    private $ft_user;
    private $ft_password;
    private $ft_host;
    private $ft_port;
    private $ft_type;
    private $ft_path;

    private $updateTitleStatement;
    private $updateDBStatement;
    private $updateDBHostStatement;
    private $updateDBPasswordStatement;
    private $updateDBUserStatement;
    /** @var $existsStatement PDOStatement | null */
    private $existsStatement;
    /** @var $createStatement PDOStatement | null */
    private $createStatement;
    /** @var $deleteStatement PDOStatement | null */
    private $deleteStatement;

    private $initialValuesHasBeenSet = false;

    /** @var $pageOrder PageOrder | null */
    private $pageOrder;

    private $observers = array();
    private $address;
    private $updateFTHostStatement;
    private $updateFTPasswordStatement;
    private $updateFTUserStatement;
    private $updateFTPortStatement;
    private $updateFTPathStatement;
    private $updateFTTypeStatement;
    private $updateAddressStatement;


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
    public function setDBHost($host)
    {
        if ($this->updateDBHostStatement == null) {
            $this->updateDBHostStatement = $this->connection->prepare("UPDATE Sites SET db_host=? WHERE title=?");
            $this->updateDBHostStatement->bindParam(1, $this->db_host);
            $this->updateDBHostStatement->bindParam(2, $this->title);
        }
        $this->db_host = $host;
        $this->updateDBHostStatement->execute();
    }

    /**
     * Will set the database of the site
     * @param string $database
     * @return void
     */
    public function setDBDatabase($database)
    {
        if ($this->updateDBStatement == null) {
            $this->updateDBStatement = $this->connection->prepare("UPDATE Sites SET db_db=? WHERE title=?");
            $this->updateDBStatement->bindParam(1, $this->db_db);
            $this->updateDBStatement->bindParam(2, $this->title);
        }
        $this->db_db = $database;
        $this->updateDBStatement->execute();
    }

    /**
     * Will set the user of the site
     * @param string $user
     * @return void
     */
    public function setDBUser($user)
    {

        if ($this->updateDBUserStatement == null) {
            $this->updateDBUserStatement = $this->connection->prepare("UPDATE Sites SET db_user=? WHERE title=?");
            $this->updateDBUserStatement->bindParam(1, $this->db_user);
            $this->updateDBUserStatement->bindParam(2, $this->title);
        }
        $this->db_user = $user;
        $this->updateDBUserStatement->execute();
    }

    /**
     * Will set the password of the site
     * @param string $password
     * @return void
     */
    public function setDBPassword($password)
    {
        $password = $this->encrypt($password, self::$key);
        if ($this->updateDBPasswordStatement == null) {
            $this->updateDBPasswordStatement = $this->connection->prepare("UPDATE Sites SET db_password=? WHERE title=?");
            $this->updateDBPasswordStatement->bindParam(1, $this->db_password);
            $this->updateDBPasswordStatement->bindParam(2, $this->title);
        }
        $this->db_password = $password;
        $this->updateDBPasswordStatement->execute();

    }

    /**
     * Will return the host of the site
     * @return string
     */
    public function getDBHost()
    {
        $this->setInitialValues();
        return $this->db_host;
    }

    /**
     * Will return the database of the site
     * @return string
     */
    public function getDBDatabase()
    {
        $this->setInitialValues();
        return $this->db_db;
    }

    /**
     * Will return the user of the site
     * @return string
     */
    public function getDBUser()
    {
        $this->setInitialValues();
        return $this->db_user;
    }

    /**
     * Will return the password of the site
     * @return string
     */
    public function getDBPassword()
    {
        $this->setInitialValues();
        return $this->decrypt($this->db_password, self::$key);
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
        if ($this->exists()) {
            return false;
        }

        if ($this->createStatement == null) {
            $this->createStatement = $this->connection->prepare("
            INSERT INTO Sites (title,db_host,db_db,db_password,db_user,ft_host,ft_password,ft_user,ft_port,ft_path,ft_type,address)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $this->createStatement->bindParam(1, $this->title);
            $this->createStatement->bindParam(2, $this->db_host);
            $this->createStatement->bindParam(3, $this->db_db);
            $this->createStatement->bindParam(4, $this->db_password);
            $this->createStatement->bindParam(5, $this->db_user);
            $this->createStatement->bindParam(6, $this->ft_host);
            $this->createStatement->bindParam(7, $this->ft_password);
            $this->createStatement->bindParam(8, $this->ft_user);
            $this->createStatement->bindParam(9, $this->ft_port);
            $this->createStatement->bindParam(10, $this->ft_path);
            $this->createStatement->bindParam(11, $this->ft_type);
            $this->createStatement->bindParam(12, $this->address);

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
            $this->db_db = $result['db_db'];
            $this->db_user = $result['db_user'];
            $this->db_password = $result['db_password'];
            $this->db_host = $result['db_host'];
            $this->ft_host = $result['ft_host'];
            $this->ft_password = $result['ft_password'];
            $this->ft_user = $result['ft_user'];
            $this->ft_port = $result['ft_port'];
            $this->ft_path = $result['ft_path'];
            $this->ft_type = $result['ft_type'];
            $this->address = $result['address'];
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
                    'mysql:dbname=' . $this->getDBDatabase() . ';host=' . $this->getDBHost(),
                    $this->getDBUser(),
                    $this->getDBPassword(),
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


    /**
     * @return Folder | bool Will return the filesystem on success, else false if connection is not valid
     */
    public function getFolder()
    {
        // TODO: Implement getFileSystem() method.
    }

    /**
     * @return string
     */
    public function getFTUser()
    {
        return $this->ft_user;
    }

    /**
     * Will set the File Transfer user
     * @param string $ft_user
     * @return void
     */
    public function setFTUser($ft_user)
    {
        if ($this->updateFTUserStatement == null) {
            $this->updateFTUserStatement = $this->connection->prepare("UPDATE Sites SET ft_user=? WHERE title=?");
            $this->updateFTUserStatement->bindParam(1, $this->ft_user);
            $this->updateFTUserStatement->bindParam(2, $this->title);
        }
        $this->ft_user = $ft_user;
        $this->updateFTUserStatement->execute();

    }

    /**
     * @return string
     */
    public function getFTPassword()
    {
        $this->setInitialValues();
        $pass = $this->decrypt($this->ft_password, self::$key);
        ;
        return $pass;
    }

    /**
     * Will set the File Transfer password
     * @param $FT_password
     * @return string
     */
    public function setFTPassword($FT_password)
    {
        if ($this->updateFTPasswordStatement == null) {
            $this->updateFTPasswordStatement = $this->connection->prepare("UPDATE Sites SET ft_password=? WHERE title=?");
            $this->updateFTPasswordStatement->bindParam(1, $this->ft_password);
            $this->updateFTPasswordStatement->bindParam(2, $this->title);
        }
        $this->ft_password = $this->encrypt($FT_password, self::$key);
        $this->updateFTPasswordStatement->execute();

    }

    /**
     * @return string
     */
    public function getFTHost()
    {
        $this->setInitialValues();
        return $this->ft_host;
    }

    /**
     * Will set the File Transfer host
     * @param string $FT_host
     * @return void
     */
    public function setFTHost($FT_host)
    {
        if ($this->updateFTHostStatement == null) {
            $this->updateFTHostStatement = $this->connection->prepare("UPDATE Sites SET ft_host=? WHERE title=?");
            $this->updateFTHostStatement->bindParam(1, $this->ft_host);
            $this->updateFTHostStatement->bindParam(2, $this->title);
        }
        $this->ft_host = $FT_host;
        $this->updateFTHostStatement->execute();
    }

    /**
     * @return string
     */
    public function getFTPort()
    {
        $this->setInitialValues();
        return $this->ft_port;
    }

    /**
     * Will set the File Transfer port
     * @param int $FT_port
     * @return void
     */
    public function setFTPort($FT_port)
    {
        if ($this->updateFTPortStatement == null) {
            $this->updateFTPortStatement = $this->connection->prepare("UPDATE Sites SET ft_port=? WHERE title=?");
            $this->updateFTPortStatement->bindParam(1, $this->ft_port);
            $this->updateFTPortStatement->bindParam(2, $this->title);
        }
        $this->ft_port = $FT_port;
        $this->updateFTPortStatement->execute();
    }

    /**
     * @return int
     */
    public function getFTType()
    {
        $this->setInitialValues();
        return $this->ft_type;
    }

    /**
     * Will set the File Transfer type (either FTP or SFTP)
     * @param int $FT_type
     * @return void
     */
    public function setFTType($FT_type)
    {
        if($FT_type != Site::FILE_TRANSFER_PROTOCOL_SFTP && $FT_type != Site::FILE_TRANSFER_PROTOCOL_FTP){
            return;
        }
        if ($this->updateFTTypeStatement == null) {
            $this->updateFTTypeStatement = $this->connection->prepare("UPDATE Sites SET ft_type = ? WHERE title = ?");
            $this->updateFTTypeStatement->bindParam(1, $this->ft_type);
            $this->updateFTTypeStatement->bindParam(2, $this->title);
        }
        $this->ft_type = $FT_type;
        $this->updateFTTypeStatement->execute();
    }

    /**
     * @param string $address
     * @return void
     */
    public function setAddress($address)
    {
        if ($this->updateAddressStatement == null) {
            $this->updateAddressStatement = $this->connection->prepare("UPDATE Sites SET address = ? WHERE title = ? ");
            $this->updateAddressStatement->bindParam(1, $this->address);
            $this->updateAddressStatement->bindParam(2, $this->title);
        }
        $this->address = $address;
        $this->updateAddressStatement->execute();
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $path The root path of the remote site
     * @return void
     */
    public function setFTPath($path)
    {
        if ($this->updateFTPathStatement == null) {
            $this->updateFTPathStatement = $this->connection->prepare("UPDATE Sites SET ft_path = ? WHERE title = ? ");
            $this->updateFTPathStatement->bindParam(1, $this->ft_path);
            $this->updateFTPathStatement->bindParam(2, $this->title);
        }
        $this->ft_path = $path;
        $this->updateFTPathStatement->execute();
    }

    /**
     * @return string
     */
    public function getFTPath()
    {
        $this->setInitialValues();
        return $this->ft_path;
    }
}
