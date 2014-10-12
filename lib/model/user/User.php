<?php
namespace ChristianBudde\cbweb\model\user;
use ChristianBudde\cbweb\controller\json\JSONObjectSerializable;

use ChristianBudde\cbweb\model\Variables;
use ChristianBudde\cbweb\util\Observable;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/07/12
 * Time: 14:25
 */
interface User extends JSONObjectSerializable, Observable
{
    const EVENT_USERNAME_UPDATE = 1;
    const EVENT_DELETE = 2;
    const EVENT_PARENT_UPDATE = 3;
    const EVENT_LOGIN = 4;
    /**
     * @abstract
     * @return string Username as string
     */
    public function getUsername();

    /**
     * @abstract
     * @return string Mail as string
     */
    public function getMail();

    /**
     * @abstract
     * @return int | null Last login in unix timestamp, or null if no entry
     */
    public function getLastLogin();

    /**
     * @abstract
     * @return null | string Will return username as string if the parent is set. Else it will return null
     */
    public function getParent();


    /**
     * This will get the UserPrivileges
     * @return UserPrivileges
     */
    public function getUserPrivileges();

    /**
     * Will get a unique id, which is persistent with regard to changes made to any user information
     * @return String
     */
    public function getUniqueId();


    /**
     * @abstract
     * Will set the username, if password is the current password and username is unique.
     * @param string $username
     * @return bool FALSE if password does not match or username invalid, TRUE on success
     */
    public function setUsername($username);

    /**
     * @abstract
     * Set the mail of the user, if mail is of right format.
     * @param string $mail
     * @return bool FALSE on wrong format of mail, else TRUE on success
     */
    public function setMail($mail);

    /**
     * @abstract
     * Sets the password, this can be on some format described by implementation
     * @param string $password
     * @return bool
     */
    public function setPassword($password);


    /**
     * @abstract
     * @param string $parent
     * @return bool FALSE on failure else TRUE
     */
    public function setParent( $parent);



    /**
     * @abstract
     * Verifies password
     * @param string $password
     * @return bool TRUE on password match, else FALSE
     */
    public function verifyLogin($password);

    /**
     * @abstract
     * Will login the user
     * @param string $password
     * @return bool FALSE if another user is logged in, including self, or if password is not valid. Else TRUE.
     */
    public function login($password);

    /**
     * @abstract
     * Performs check if the user is logged in
     * @return bool TRUE if logged in else FALSE
     */
    public function isLoggedIn();

    /**
     * @abstract
     * @return bool TRUE on success else FALSE, also if user is not logged in
     */
    public function logout();

    /**
     * @abstract
     * Will delete user from persistent storage
     * @return bool Will return FALSE on failure, else TRUE
     */
    public function delete();

    /**
     * @abstract
     * Create the user on persistent storage
     * @return bool Will return TRUE if user has been created on persistent storage, else FALSE
     */
    public function create();

    /**
     * @abstract
     * Checks if the user has been created on persistent storage.
     * @return bool Will return FALSE if user does not exists on persistent storage, else TRUE
     */
    public function exists();

    /**
     * Will return TRUE if valid mail else FALSE
     * @param string $mail
     * @return bool
     */
    public function isValidMail($mail);

    /**
     * Will return TRUE if valid username else FALSE
     * @param string $username
     * @return bool
     */
    public function isValidUsername($username);

    /**
     * Will return TRUE if valid password else FALSE
     * @param string $password
     * @return bool
     */
    public function isValidPassword($password);


    /**
     * @return Variables
     */
    public function getUserVariables();

    /**
     * Returns a token "unique" to the user and the last login time.
     * @return String
     */
    public function getUserToken();

}
