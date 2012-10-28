<?php
require_once dirname(__FILE__) . '/../_trait/RequestTrait.php';
require_once dirname(__FILE__) . '/../_interface/User.php';
require_once dirname(__FILE__) . '/../_interface/Observable.php';
require_once dirname(__FILE__) . '/../_class/UserPrivilegesImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/07/12
 * Time: 14:29
 */
class UserImpl implements User, Observable
{
    use RequestTrait;
    private static $randomString = 'S6lmSif7i3gmoyPqoAoW';

    private $database;
    private $connection;

    private $username;
    private $mail;
    private $password;
    private $lastLogin;
    private $id;


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
     * Will set the username, if password is the current password and username is unique.
     * @param string $username
     * @return bool FALSE if password does not match or username invalid, TRUE on success
     */
    public function setUsername($username)
    {
        if ($this->usernameExists($username)) {
            return false;
        }

        if ($this->setUsernameStatement === null) {
            $this->setUsernameStatement = $this->connection->prepare("UPDATE User SET username = ? WHERE username = ?");
        }

        $wasLoggedIn = $this->logout();

        $this->setUsernameStatement->execute(array($username, $this->username));
        $this->username = $username;
//        $this->setPassword($password);

        if ($wasLoggedIn) {
            $_SESSION['loginUsername'] = $this->getUsername();
            $_SESSION['loginPassword'] = $this->getPassword();
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
        if (@preg_match('/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i', $mail) == 0) {
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
        if (!is_string($password) || strlen($password) == 0) {
            return false;
        }

        if ($this->setPasswordStatement === null) {
            $this->setPasswordStatement = $this->connection->prepare("UPDATE User SET password=? WHERE username=?");
            $this->setPasswordStatement->bindParam(1, $this->password);
            $this->setPasswordStatement->bindParam(2, $this->username);
        }

        $wasLoggedIn = $this->logout();

        $this->password = $this->hashPassword($password);
        $this->setPasswordStatement->execute();

        if ($wasLoggedIn) {
            $this->login($password);
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
        return $this->getPassword() == $this->hashPassword($password);
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
            $this->SESSIONValueOfIndexIfSetElseDefault('loginUsername', false) !== false ||
            $this->SESSIONValueOfIndexIfSetElseDefault('loginPassword', false) !== false
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

        $_SESSION['loginUsername'] = $this->getUsername();
        $_SESSION['loginPassword'] = $this->getPassword();
        return true;
    }

    /**
     * Performs check if the user is logged in
     * @return bool TRUE if logged in else FALSE
     */
    public function isLoggedIn()
    {
        return $this->SESSIONValueOfIndexIfSetElseDefault('loginUsername', false) == $this->getUsername() &&
            $this->SESSIONValueOfIndexIfSetElseDefault('loginPassword', false) == $this->getPassword();
    }

    /**
     * @return bool TRUE on success else FALSE, also if user is not logged in
     */
    public function logout()
    {
        if (!$this->isLoggedIn()) {
            return false;
        }
        unset($_SESSION['loginUsername']);
        unset($_SESSION['loginPassword']);
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

        } else {
            $this->id = uniqid('', true);
        }
    }

    private function getId()
    {
        $this->setInitialValues();
        return $this->id;
    }

    private function getPassword()
    {
        $this->setInitialValues();
        return $this->password;
    }

    private function hashPassword($password)
    {
        $salt = '$2a$07$' . self::$randomString . $this->getId() . '$';
        return crypt($password, $salt);
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
}