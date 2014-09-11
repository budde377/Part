<?php
namespace ChristianBudde\cbweb;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/3/14
 * Time: 2:13 PM
 */

interface MailMailbox extends Observable{

    const EVENT_DELETE = 1;

    /**
     * Sets the owners name of the mailbox
     * @param string $name
     * @return void
     */
    public function setName($name);

    /**
     * @return string The name of the owner
     */
    public function getName();

    /**
     * Sets the password of the mailbox
     * @param string $password
     * @return void
     */
    public function setPassword($password);

    /**
     * Checks if the password matches the stored password.
     * @param string $password
     * @return bool
     */
    public function checkPassword($password);

    /**
     * Deletes the mailbox
     * @return bool
     */
    public function delete();

    /**
     * @return bool
     */
    public function exists();

    /**
     * Creates the mailbox
     * @return void
     */
    public function create();

    /**
     * @return MailAddress
     */
    public function getAddress();

    /**
     * @return MailAddressLibrary
     */
    public function getAddressLibrary();

    /**
     * @return MailDomain
     */
    public function getDomain();

    /**
     * @return MailDomainLibrary
     */
    public function getDomainLibrary();


    /**
     * @return int
     */
    public function lastModified();


    /**
     * @return int
     */
    public function createdAt();
}