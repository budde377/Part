<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/8/14
 * Time: 6:02 PM
 */

class MailMailboxImpl implements MailMailbox{

    /**
     * Sets the owners name of the mailbox
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        // TODO: Implement setName() method.
    }

    /**
     * @return string The name of the owner
     */
    public function getName()
    {
        // TODO: Implement getName() method.
    }

    /**
     * Sets the password of the mailbox
     * @param string $password
     * @return void
     */
    public function setPassword($password)
    {
        // TODO: Implement setPassword() method.
    }

    /**
     * Deletes the mailbox
     * @return bool
     */
    public function delete()
    {
        // TODO: Implement delete() method.
    }

    /**
     * @return bool
     */
    public function exists()
    {
        // TODO: Implement exists() method.
    }

    /**
     * Creates the mailbox
     * @return bool
     */
    public function create()
    {
        // TODO: Implement create() method.
    }

    public function attachObserver(Observer $observer)
    {
        // TODO: Implement attachObserver() method.
    }

    public function detachObserver(Observer $observer)
    {
        // TODO: Implement detachObserver() method.
    }

    /**
     * Checks if the password matches the stored password.
     * @param string $password
     * @return bool
     */
    public function checkPassword($password)
    {
        // TODO: Implement checkPassword() method.
    }

    /**
     * @return MailAddress
     */
    public function getAddress()
    {
        // TODO: Implement getAddress() method.
    }

    /**
     * @return MailAddressLibrary
     */
    public function getAddressLibrary()
    {
        // TODO: Implement getAddressLibrary() method.
    }

    /**
     * @return MailDomain
     */
    public function getDomain()
    {
        // TODO: Implement getDomain() method.
    }

    /**
     * @return MailDomainLibrary
     */
    public function getDomainLibrary()
    {
        // TODO: Implement getDomainLibrary() method.
    }
}