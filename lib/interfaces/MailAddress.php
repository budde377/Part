<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/3/14
 * Time: 2:19 PM
 */

interface MailAddress {

    const EVENT_DELETE = 1;
    const EVENT_CHANGE_ADDRESS = 2;

    /**
     * @return string
     */
    public function getAddress();

    /**
     * @param $address
     * @return void
     */
    public function setAddress($address);

    /**
     * Indicates if the address is active
     * @return bool
     */
    public function isActive();

    /**
     * Last modified
     * @return int UNIX timestamp in seconds.
     */
    public function lastModified();

    /**
     * Creation time
     * @return int UNIX timestamp in seconds.
     */
    public function createdAt();

    /**
     * Checks if the address exists
     * @return bool
     */
    public function exists();

    /**
     * Deletes an address
     * @return void
     */
    public function delete();

    /**
     * Creates an address
     * @return void
     */
    public function create();


    /**
     * @return MailDomain
     */
    public function getDomain();


    /**
     * @return MailAddressLibrary
     */
    public function getAddressLibrary();


    /**
     * @return void
     */
    public function activate();

    /**
     * @return void
     */
    public function deactivate();


    /**
     * @return array An array of strings containing targets. This should be a numeric array.
     */
    public function getTargets();

    /**
     * Adds an target if it doesn't exists
     * @param string $address
     * @return void
     */
    public function addTarget($address);

    /**
     * Removes a target if exists.
     * @param string $address
     * @return void
     */
    public function removeTarget($address);

    /**
     * Removes all targets.
     * @return void
     */
    public function clearTargets();


    /**
     * Will return a mailbox, if there is any. If not it will return NULL
     * @return MailMailbox | null
     */
    public function getMailbox();

    /**
     * @return bool
     */
    public function hasMailbox();

    /**
     * Creates a new mailbox if it doesn't have one, else it returns the instance.
     * @param string $name
     * @param string $password
     * @return MailMailbox
     */
    public function createMailbox($name, $password);

    /**
     * Removes the mailbox.
     * @return void
     */
    public function deleteMailbox();


    /**
     * @return MailDomainLibrary
     */
    public function getDomainLibrary();

}