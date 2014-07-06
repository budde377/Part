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
     * Indicates if the address is a mailbox
     * @return bool
     */
    public function isMailbox();

    /**
     * Indicates if the address is an alias
     * @return bool
     */
    public function isAlias();

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
     * @return void
     */
    public function activate();

    /**
     * @return void
     */
    public function deactivate();
} 