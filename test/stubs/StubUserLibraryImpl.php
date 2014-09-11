<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 06/08/12
 * Time: 22:24
 */
class StubUserLibraryImpl implements ChristianBudde\cbweb\UserLibrary
{
    private $userList = array();
    private $userLoggedIn;

    /**
     * Will list all users
     * @return array
     */
    public function listUsers()
    {
        return $this->userList;
    }

    /**
     * Will delete user. The user must be instance in library.
     * @param ChristianBudde\cbweb\User $user
     * @return bool
     */
    public function deleteUser(ChristianBudde\cbweb\User $user)
    {
        return false;
    }

    /**
     * Will create a user, the username must be unique
     * The created instance can be deleted and will be in list
     * from listUsers.
     * @param string $username
     * @param string $password
     * @param string $mail
     * @param ChristianBudde\cbweb\User $parent
     * @return ChristianBudde\cbweb\User | bool FALSE on failure else instance of User
     */
    public function createUser($username, $password, $mail, ChristianBudde\cbweb\User $parent)
    {
        return false;
    }

    /**
     * @return ChristianBudde\cbweb\User | null User logged in else null if no user is logged in.
     */
    public function getUserLoggedIn()
    {
        return $this->userLoggedIn;
    }

    /**
     * @param string $username
     * @return ChristianBudde\cbweb\User | null User if username is found, else null
     */
    public function getUser($username)
    {
        foreach($this->userList as $user){
            /** @var $user ChristianBudde\cbweb\User */
            if($user instanceof ChristianBudde\cbweb\User && $user->getUsername() == $username){
                return $user;
            }

        }
        return null;
    }

    public function setUserList(array $list){
        $this->userList = $list;
    }

    public function setUserLoggedIn(ChristianBudde\cbweb\User $userLoggedIn=null)
    {
        $this->userLoggedIn = $userLoggedIn;
    }


    /**
     *
     * Parameter must be an instance provided from Library.
     * @param ChristianBudde\cbweb\User $user
     * @return ChristianBudde\cbweb\User | null Will return User if the user provided has parent, else null.
     */
    public function getParent(ChristianBudde\cbweb\User $user)
    {
    }

    /**
     * Input must be instance of User and an instance provided by the library.
     * @param ChristianBudde\cbweb\User $user
     * @return array Array containing children User instances. Empty array on no children or input not valid.
     */
    public function getChildren(ChristianBudde\cbweb\User $user)
    {
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
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
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
    }

    /**
     * Serializes the object to an instance of JSONObject.
     * @return ChristianBudde\cbweb\JSONObject
     */
    public function jsonObjectSerialize()
    {
        return new ChristianBudde\cbweb\UserLibraryJSONObjectImpl($this);
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
