<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/07/12
 * Time: 14:26
 */
interface UserLibrary extends Iterator, JSONObjectSerializable
{
    /**
     * @abstract
     * Will list all users
     * @return array
     */
    public function listUsers();

    /**
     * @abstract
     * Will delete user. The user must be instance in library.
     * @param User $user
     * @return bool
     */
    public function deleteUser(User $user);

    /**
     * @abstract
     * Will create a user, the username must be unique
     * The created instance can be deleted and will be in list
     * from listUsers.
     * @param string $username
     * @param string $password
     * @param string $mail
     * @param User $parent
     * @return User | bool FALSE on failure else instance of User
     */
    public function createUser($username,$password,$mail,User $parent);

    /**
     * @abstract
     * @return User | null User logged in else null if no user is logged in.
     */
    public function getUserLoggedIn();

    /**
     * @abstract
     * @param string $username
     * @return User | null User if username is found, else null
     */
    public function getUser($username);

    /**
     * @abstract
     *
     * Parameter must be an instance provided from Library.
     * @param User $user
     * @return User | null Will return User if the user provided has parent, else null.
     */
    public function getParent(User $user);

    /**
     * @abstract
     * Input must be instance of User and an instance provided by the library.
     * @param User $user
     * @return array Array containing children User instances. Empty array on no children or input not valid.
     */
    public function getChildren(User $user);
}
