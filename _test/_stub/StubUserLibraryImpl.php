<?php
require_once dirname(__FILE__) . '/../../_interface/UserLibrary.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 06/08/12
 * Time: 22:24
 */
class StubUserLibraryImpl implements UserLibrary
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
     * @param User $user
     * @return bool
     */
    public function deleteUser(User $user)
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
     * @param User $parent
     * @return User | bool FALSE on failure else instance of User
     */
    public function createUser($username, $password, $mail, User $parent)
    {
        return false;
    }

    /**
     * @return User | null User logged in else null if no user is logged in.
     */
    public function getUserLoggedIn()
    {
        return $this->userLoggedIn;
    }

    /**
     * @param string $username
     * @return User | null User if username is found, else null
     */
    public function getUser($username)
    {
        foreach($this->userList as $user){
            /** @var $user User */
            if($user instanceof User && $user->getUsername() == $user){
                return $user;
            }

        }
        return null;
    }

    public function setUserList(array $list){
        $this->userList = $list;
    }

    public function setUserLoggedIn(User $userLoggedIn=null)
    {
        $this->userLoggedIn = $userLoggedIn;
    }


    /**
     *
     * Parameter must be an instance provided from Library.
     * @param User $user
     * @return User | null Will return User if the user provided has parent, else null.
     */
    public function getParent(User $user)
    {
        // TODO: Implement getParent() method.
    }

    /**
     * Input must be instance of User and an instance provided by the library.
     * @param User $user
     * @return array Array containing children User instances. Empty array on no children or input not valid.
     */
    public function getChildren(User $user)
    {
        // TODO: Implement getChildren() method.
    }
}
