<?php
require_once dirname(__FILE__) . '/../../_interface/User.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 02/08/12
 * Time: 15:24
 */
class StubUserImpl implements User
{

    private $username;
    private $mail;
    private $lastLogin;
    private $parent;

    /**
     * @return string Username as string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string Mail as string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @return int | null Last login in unix timestamp, or null if no entry
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Will set the username, if password is the current password and username is unique.
     * @param string $username
     * @return bool FALSE if password does not match or username invalid, TRUE on success
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return true;
    }

    /**
     * Set the mail of the user, if mail is of right format.
     * @param string $mail
     * @return bool FALSE on wrong format of mail, else TRUE on success
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
        return true;
    }

    /**
     * Sets the password, this can be on some format described by implementation
     * @param string $password
     * @return bool
     */
    public function setPassword($password)
    {
        return false;
    }

    /**
     * Verifies password
     * @param string $password
     * @return bool TRUE on password match, else FALSE
     */
    public function verifyLogin($password)
    {
        return false;
    }

    /**
     * Will login the user
     * @param string $password
     * @return bool FALSE if another user is logged in, including self, or if password is not valid. Else TRUE.
     */
    public function login($password)
    {
        return false;
    }

    /**
     * Performs check if the user is logged in
     * @return bool TRUE if logged in else FALSE
     */
    public function isLoggedIn()
    {
        return false;
    }

    /**
     * @return bool TRUE on success else FALSE, also if user is not logged in
     */
    public function logout()
    {
        return false;
    }

    /**
     * Will delete user from persistent storage
     * @return bool Will return FALSE on failure, else TRUE
     */
    public function delete()
    {
        return false;
    }

    /**
     * Create the user on persistent storage
     * @return bool Will return TRUE if user has been created on persistent storage, else FALSE
     */
    public function create()
    {
        return false;
    }

    /**
     * Checks if the user has been created on persistent storage.
     * @return bool Will return FALSE if user does not exists on persistent storage, else TRUE
     */
    public function exists()
    {
        return false;
    }

    /**
     * @return UserPrivileges
     */
    public function getUserPrivileges()
    {
        return null;
    }

    /**
     * @return null | string Will return username as string if the parent is set. Else it will return null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param string $parent
     * @return bool FALSE on failure else TRUE
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return true;
    }
}