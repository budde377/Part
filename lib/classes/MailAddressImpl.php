<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/8/14
 * Time: 6:02 PM
 */

class MailAddressImpl implements MailAddress{

    /**
     * @return string
     */
    public function getAddress()
    {
        // TODO: Implement getAddress() method.
    }

    /**
     * @param $address
     * @return void
     */
    public function setAddress($address)
    {
        // TODO: Implement setAddress() method.
    }

    /**
     * Indicates if the address is active
     * @return bool
     */
    public function isActive()
    {
        // TODO: Implement isActive() method.
    }

    /**
     * Last modified
     * @return int UNIX timestamp in seconds.
     */
    public function lastModified()
    {
        // TODO: Implement lastModified() method.
    }

    /**
     * Creation time
     * @return int UNIX timestamp in seconds.
     */
    public function createdAt()
    {
        // TODO: Implement createdAt() method.
    }

    /**
     * Checks if the address exists
     * @return bool
     */
    public function exists()
    {
        // TODO: Implement exists() method.
    }

    /**
     * Deletes an address
     * @return void
     */
    public function delete()
    {
        // TODO: Implement delete() method.
    }

    /**
     * Creates an address
     * @return void
     */
    public function create()
    {
        // TODO: Implement create() method.
    }

    /**
     * @return MailDomain
     */
    public function getDomain()
    {
        // TODO: Implement getDomain() method.
    }

    /**
     * @return void
     */
    public function activate()
    {
        // TODO: Implement activate() method.
    }

    /**
     * @return void
     */
    public function deactivate()
    {
        // TODO: Implement deactivate() method.
    }

    /**
     * @return array An array of strings containing targets. This should be a numeric array.
     */
    public function getTargets()
    {
        // TODO: Implement getTargets() method.
    }

    /**
     * Adds an target if it doesn't exists
     * @param string $address
     * @return void
     */
    public function addTarget($address)
    {
        // TODO: Implement addTarget() method.
    }

    /**
     * Removes a target if exists.
     * @param string $address
     * @return void
     */
    public function removeTarget($address)
    {
        // TODO: Implement removeTarget() method.
    }

    /**
     * Removes all targets.
     * @return void
     */
    public function clearTargets()
    {
        // TODO: Implement clearTargets() method.
    }

    /**
     * Will return a mailbox, if there is any. If not it will return NULL
     * @return MailMailbox | null
     */
    public function getMailbox()
    {
        // TODO: Implement getMailbox() method.
    }

    /**
     * @return bool
     */
    public function hasMailbox()
    {
        // TODO: Implement hasMailbox() method.
    }

    /**
     * Creates a new mailbox if it doesn't have one, else it returns the instance.
     * @param string $name
     * @param string $password
     * @return MailMailbox
     */
    public function createMailbox($name, $password)
    {
        // TODO: Implement createMailbox() method.
    }

    /**
     * Removes the mailbox.
     * @return void
     */
    public function deleteMailbox()
    {
        // TODO: Implement deleteMailbox() method.
    }

    /**
     * @return MailAddressLibrary
     */
    public function getAddressLibrary()
    {
        // TODO: Implement getAddressLibrary() method.
    }

    /**
     * @return MailDomainLibrary
     */
    public function getDomainLibrary()
    {
        // TODO: Implement getDomainLibrary() method.
    }
}