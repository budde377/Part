<?php
namespace ChristianBudde\Part\model\user;
use ChristianBudde\Part\controller\json\UserObjectImpl;
use ChristianBudde\Part\model\Variables;
use ChristianBudde\Part\util\db\DB;
use ChristianBudde\Part\util\Observer;
use ChristianBudde\Part\util\traits\RequestTrait;
use ChristianBudde\Part\util\traits\ValidationTrait;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/07/12
 * Time: 14:29
 */
class UserImpl implements User
{
    use RequestTrait;
    use ValidationTrait;

    private $database;
    private $connection;

    private $username;
    private $mail;
    private $password;
    private $lastLogin;
    private $id;

    private $userPrivileges;
    private $userVariables;


    /** @var $existsStatement PDOStatement | null */
    private $existsStatement;
    /** @var $createStatement PDOStatement | null */
    private $createStatement;
    /** @var $setUsernameStatement PDOStatement | null */
    private $setUsernameStatement;
    /** @var $setParentStatement PDOStatement | null */
    private $setParentStatement;
    /** @var $setMailStatement PDOStatement | null */
    private $setMailStatement;
    /** @var $setPasswordStatement PDOStatement | null */
    private $setPasswordStatement;
    /** @var $deleteStatement PDOStatement | null */
    private $deleteStatement;
    /** @var $lastLoginStatement PDOStatement | null */
    private $lastLoginStatement;
    /** @var $circularParentingStatement PDOStatement | null */
    private $circularParentingStatement;

    private $valuesHasBeenSet = false;
    private $observers = array();
    private $parent = null;
    private $parentID = null;


    public function __construct($username, DB $database)
    {
        $this->username = $username;
        $this->database = $database;
        $this->connection = $database->getConnection();
    }

    /**
     * @return string Username as string
     */
    public function getUsername()
    {
        $this->setInitialValues();
        return $this->username;
    }

    /**
     * @return string Mail as string
     */
    public function getMail()
    {
        $this->setInitialValues();
        return $this->mail;
    }

    /**
     * Will set the username, if username is unique.
     * @param string $username
     * @return bool FALSE if username invalid, TRUE on success
     */
    public function setUsername($username)
    {
        if($username == $this->username){
            return true;
        }
        if ($this->usernameExists($username)) {
            return false;
        }

        if ($this->setUsernameStatement === null) {
            $this->setUsernameStatement = $this->connection->prepare("UPDATE User SET username = ? WHERE username = ?");
        }
        $wasLoggedIn = $this->isLoggedIn();

        $this->setUsernameStatement->execute(array($username, $this->username));
        $this->username = $username;

        if ($wasLoggedIn) {
            $this->updateLoginSession();
        }
        $this->notifyObservers(User::EVENT_USERNAME_UPDATE);
        return true;
    }

    /**
     * Set the mail of the user, if mail is of right format.
     * @param string $mail
     * @return bool FALSE on wrong format of mail, else TRUE on success
     */
    public function setMail($mail)
    {
        if (!$this->validMail($mail)) {
            return false;
        }

        if ($this->setMailStatement === null) {
            $this->setMailStatement = $this->connection->prepare("UPDATE User SET mail = ? WHERE username = ?");
            $this->setMailStatement->bindParam(1, $this->mail);
            $this->setMailStatement->bindParam(2, $this->username);
        }
        $this->mail = $mail;
        $this->setMailStatement->execute();
        return true;
    }

    /**
     * Sets the password. Password must be non-empty string
     * @param string $password
     * @return bool
     */
    public function setPassword($password)
    {
        if(!$this->isValidPassword($password)){
            return false;
        }

        if ($this->setPasswordStatement === null) {
            $this->setPasswordStatement = $this->connection->prepare("UPDATE User SET password=? WHERE username=?");
            $this->setPasswordStatement->bindParam(1, $this->password);
            $this->setPasswordStatement->bindParam(2, $this->username);
        }
        $wasLoggedIn = $this->isLoggedIn();
        $this->password = $this->hashPassword($password);
        $this->setPasswordStatement->execute();

        if ($wasLoggedIn) {

            $this->updateLoginSession($password);
        }

        return true;
    }

    /**
     * Verifies password
     * @param string $password
     * @return bool TRUE on password match, else FALSE
     */
    public function verifyLogin($password)
    {
        return password_verify($password, $this->getPassword());
    }

    /**
     * Will delete user from persistent storage
     * @return bool Will return FALSE on failure, else TRUE
     */
    public function delete()
    {
        if ($this->deleteStatement == null) {
            $this->deleteStatement = $this->connection->prepare("DELETE FROM User WHERE username = ?");
            $this->deleteStatement->bindParam(1, $this->username);
        }
        try{
            $this->deleteStatement->execute();
        } catch (PDOException $exception){
            return false;
        }
        if ($this->exists()) {
            return false;
        }
        $this->notifyObservers(User::EVENT_DELETE);
        return true;
    }

    /**
     * Create the user on persistent storage
     * @return bool Will return TRUE if user has been created on persistent storage, else FALSE
     */
    public function create()
    {
        if ($this->createStatement == null) {
            $this->createStatement = $this->connection->prepare("
            INSERT INTO User (username,mail,password,id,parent) VALUES (?,?,?,?,?)");
            $this->createStatement->bindParam(1, $this->username);
            $this->createStatement->bindParam(2, $this->mail);
            $this->createStatement->bindParam(3, $this->password);
            $this->createStatement->bindParam(4, $this->id);
            $this->createStatement->bindParam(5, $this->parentID);
        }
        try {
            $this->createStatement->execute();
        } catch (PDOException $e) {
            return false;
        }
        $this->setInitialValues();
        return $this->exists();
    }

    /**
     * Checks if the user has been created on persistent storage.
     * @return bool Will return FALSE if user does not exists on persistent storage, else TRUE
     */
    public function exists()
    {
        return $this->usernameExists($this->username);
    }

    /**
     * @return int | null Last login in unix timestamp, or null if no entry
     */
    public function getLastLogin()
    {
        $this->setInitialValues();
        return $this->lastLogin;
    }

    /**
     * Will login the user
     * @param string $password
     * @return bool FALSE if another user is logged in, including self, or if password is not valid. Else TRUE.
     */
    public function login($password)
    {
        if (!$this->exists() ||
            !$this->verifyLogin($password) ||
            $this->someOneLoggedIn()
        ) {
            return false;
        }

        if ($this->lastLoginStatement === null) {
            $this->lastLoginStatement = $this->connection->prepare("UPDATE User SET lastLogin = FROM_UNIXTIME(?) WHERE username = ?");
            $this->lastLoginStatement->bindParam(1, $this->lastLogin);
            $this->lastLoginStatement->bindParam(2, $this->username);

        }
        $this->lastLogin = time();
        $this->lastLoginStatement->execute();


        $this->updateLoginSession();
        $this->notifyObservers(User::EVENT_LOGIN);
        return true;
    }

    /**
     * Performs check if the user is logged in
     * @return bool TRUE if logged in else FALSE
     */
    public function isLoggedIn()
    {
        return $this->SESSIONValueOfIndexIfSetElseDefault('model-user-login-token', false) == $this->getUsernamePasswordHash();
    }

    /**
     * @return bool TRUE on success else FALSE, also if user is not logged in
     */
    public function logout()
    {
        if (!$this->isLoggedIn()) {
            return false;
        }
        unset($_SESSION['model-user-login-token']);
        return true;
    }

    private function usernameExists($username)
    {
        if ($this->existsStatement === null) {
            $this->existsStatement = $this->connection->prepare("SELECT *,UNIX_TIMESTAMP(lastLogin) as lastLogin
            FROM User WHERE username=? ");
        }

        $this->existsStatement->execute(array($username));
        return $this->existsStatement->rowCount() > 0;
    }

    private function setInitialValues()
    {
        if (!$this->valuesHasBeenSet && $this->exists()) {
            $this->valuesHasBeenSet = true;
            $result = $this->existsStatement->fetch(PDO::FETCH_ASSOC);
            $this->password = $result['password'];
            $this->mail = $result['mail'];
            $this->username = $result['username'];
            $this->lastLogin = ($r = $result['lastLogin']) == null ? null : intval($r);
            $this->parentID = $result['parent'];
            $this->id = $result['id'];
            $statement = $this->connection->prepare("SELECT username FROM User WHERE id=?");
            $statement->execute(array($this->parentID));
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            $this->parent = isset($result['username']) ? $result['username'] : null;

        } else if($this->id == null){
            $this->id = uniqid('', true);
        }
    }


    private function getPassword()
    {
        $this->setInitialValues();
        return $this->password;
    }

    private function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
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
            /** @var $observer \ChristianBudde\Part\util\Observer */
            $observer->onChange($this, $event);
        }

    }

    /**
     * @return null | string Will return username as string if the parent is set. Else it will return null
     */
    public function getParent()
    {
        $this->setInitialValues();
        return $this->parent;
    }

    /**
     * @param string $parent
     * @return bool FALSE on failure else TRUE
     */
    public function setParent($parent)
    {
        if (!$this->usernameExists($parent)) {
            return false;
        }
        $result = $this->existsStatement->fetch(PDO::FETCH_ASSOC);
        $parentID = $result['id'];

        if (!$this->detectCircularParenting($parentID)) {
            return false;
        }
        if ($this->setParentStatement == null) {
            $this->setParentStatement = $this->connection->prepare("UPDATE User SET parent = ? WHERE username = ?");
            $this->setParentStatement->bindParam(1, $this->parentID);
            $this->setParentStatement->bindParam(2, $this->username);
        }
        $this->parent = $parent;
        $this->parentID = $parentID;
        $this->setParentStatement->execute();
        $this->notifyObservers(User::EVENT_PARENT_UPDATE);
        return true;
    }

    private function detectCircularParenting($parentID)
    {
        if ($this->circularParentingStatement == null) {
            $this->circularParentingStatement = $this->connection->prepare("SELECT parent FROM User WHERE id = ?");
        }
        $failure = false;
        $success = false;
        while (!$failure && !$success) {
            if ($parentID == null) {
                $success = true;
            }
            if ($parentID == $this->id) {
                $failure = true;
            }
            $this->circularParentingStatement->execute(array($parentID));
            $result = $this->circularParentingStatement->fetch(PDO::FETCH_ASSOC);
            $parentID = $result['parent'];

        }
        return $success;
    }

    /**
     * Will return TRUE if valid mail else FALSE
     * @param string $mail
     * @return bool
     */
    public function isValidMail($mail)
    {
        return $this->validMail($mail);
    }

    /**
     * Will return TRUE if valid username else FALSE
     * @param string $username
     * @return bool
     */
    public function isValidUsername($username)
    {
        return !$this->usernameExists($username);
    }

    /**
     * Will return TRUE if valid password else FALSE
     * @param string $password
     * @return bool
     */
    public function isValidPassword($password)
    {
        return is_string($password) && strlen($password) > 0;
    }

    /**
     * @return UserPrivileges
     */
    public function getUserPrivileges()
    {
        return $this->userPrivileges == null ? $this->userPrivileges = new UserPrivilegesImpl($this, $this->database) : $this->userPrivileges;
    }

    /**
     * Will get a unique id, which is persistent with regard to changes made to any user information
     * @return String
     */
    public function getUniqueId()
    {
        $this->setInitialValues();
        return $this->id;
    }

    /**
     * @return Variables
     */
    public function getUserVariables()
    {
        return $this->userVariables == null?$this->userVariables = new UserVariablesImpl($this->database, $this): $this->userVariables;
    }

    /**
     * Serializes the object to an instance of JSONObject.
     * @return \ChristianBudde\Part\controller\json\Object
     */
    public function jsonObjectSerialize()
    {
        return new UserObjectImpl($this);
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

    private function updateLoginSession()
    {
        $_SESSION['model-user-login-token'] = $this->getUsernamePasswordHash();
    }

    private function someOneLoggedIn()
    {
        return isset($_SESSION['model-user-login-token']);
    }

    /**
     * Returns a token "unique" to the user and the last login time.
     * @return String
     */
    public function getUserToken()
    {
        return sha1($this->getUniqueId().$this->getLastLogin());
    }

    private function getUsernamePasswordHash()
    {
        return sha1($this->getUsername().$this->getPassword());
    }
}
