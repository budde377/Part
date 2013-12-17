<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/07/12
 * Time: 14:29
 */
class UserLibraryImpl implements UserLibrary, Observer
{
    private $userList = array();
    private $database;
    private $connection;
    /** @var $userListIterator ArrayIterator */
    private $userListIterator;

    public function __construct(DB $database)
    {
        $this->database = $database;
        $this->connection = $database->getConnection();
        $this->initializeLibrary();
        $this->setUpIterator();
    }


    private function initializeLibrary()
    {
        $query = "SELECT username FROM User";
        foreach ($this->connection->query($query) as $row) {
            $user = new UserImpl($row['username'], $this->database);
            $user->attachObserver($this);
            $this->userList[$user->getUsername()] = $user;
        }
    }

    /**
     * Will list all users
     * @return array
     */
    public function listUsers()
    {
        $returnArray = array();
        foreach ($this->userList as $user) {
            $returnArray[] = $user;
        }
        return $returnArray;
    }

    /**
     * Will delete user. The user must be instance in library.
     * @param User $user
     * @return bool
     */
    public function deleteUser(User $user)
    {
        $parent = $user->getParent();
        if (!isset($this->userList[$user->getUsername()]) || $this->userList[$user->getUsername()] !== $user ||
            $parent == null
        ) {
            return false;
        }
        $this->connection->beginTransaction();
        $children = $this->getChildren($user);
        $success = true;
        foreach ($children as $child) {
            /** @var $child User */
            $success = $success && $child->setParent($parent);
        }
        $success = $success && $user->delete();

        if ($success) {
            $this->connection->commit();
            $this->setUpIterator();
        } else {
            $this->connection->rollBack();
        }
        return $success;
    }

    private function findUserKey(User $user, &$numericArray = array())
    {
        $userKey = null;
        $userFound = false;
        foreach ($this->userList as $key => $u) {
            if ($u === $user) {
                $userKey = $key;
                $userFound = true;
            } else {
                $numericArray[] = $u;
            }
        }
        if (!$userFound) {
            return false;
        }
        return $userKey;

    }

    /**
     * Will create a user, the username must be unique
     * The created instance can be deleted and will be in list
     * from listUsers.
     * @param string $username
     * @param string $password
     * @param string $mail
     * @param User $parent
     * @return User | bool FALSE on failure else instance of User
     */
    public function createUser($username, $password, $mail, User $parent)
    {
        $user = new UserImpl($username, $this->database);
        if (!$user->setMail($mail) || !$user->setPassword($password) || !$user->setParent($parent->getUsername()) || !$user->create()) {
            return false;
        }
        $this->userList[$user->getUsername()] = $user;
        $this->setUpIterator();
        return $user;
    }

    /**
     * @return User | null User logged in else null if no user is logged in.
     */
    public function getUserLoggedIn()
    {

        $loggedInUser = null;
        $list = $this->userList;
        while ($loggedInUser == null && ($u = array_pop($list)) != null) {
            /** @var $u User */
            if ($u->isLoggedIn()) {
                $loggedInUser = $u;
            }
        }
        return $loggedInUser;
    }

    public function onChange(Observable $subject, $changeType)
    {
        switch ($changeType) {
            case User::EVENT_DELETE:
                if ($subject instanceof User) {
                    /** @var $subject User */
                    if (isset($this->userList[$subject->getUsername()]) && $this->userList[$subject->getUsername()] === $subject) {
                        unset($this->userList[$subject->getUsername()]);
                    }
                }
                break;
            case User::EVENT_USERNAME_UPDATE:
                foreach ($this->userList as $key => $user) {
                    if ($subject === $user) {
                        unset($this->userList[$key]);
                    }
                }
                /** @var $subject User */
                $this->userList[$subject->getUsername()] = $subject;

        }
    }

    /**
     * @param string $username
     * @return User | null User if username is found, else null
     */
    public function getUser($username)
    {
        return isset($this->userList[$username]) ? $this->userList[$username] : null;
    }

    /**
     *
     * Parameter must be an instance provided from Library.
     * @param User $user
     * @return User | null Will return User if the user provided has parent, else null.
     */
    public function getParent(User $user)
    {
        return $this->getUser($user->getParent());
    }

    /**
     * Input must be instance of User and an instance provided by the library.
     * @param User $user
     * @return array Array containing children User instances. Empty array on no children or input not valid.
     */
    public function getChildren(User $user)
    {
        $returnArray = array();
        foreach ($this->userList as $u) {
            /** @var $u User */
            if ($u->getParent() == $user->getUsername()) {
                $returnArray[] = $u;
                $returnArray = array_merge($returnArray,$this->getChildren($u));
            }
        }
        return $returnArray;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return User
     */
    public function current()
    {
        return $this->userListIterator->current();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->userListIterator->next();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->userListIterator->key();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->userListIterator->valid();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->userListIterator->rewind();
    }

    private function setUpIterator()
    {
        $arrayObject = new ArrayObject($this->listUsers());
        $this->userListIterator = $arrayObject->getIterator();
    }
}
