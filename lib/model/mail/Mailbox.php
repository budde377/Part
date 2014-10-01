<?php
namespace ChristianBudde\cbweb\model\mail;
use ChristianBudde\cbweb\util\Observable;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/3/14
 * Time: 2:13 PM
 */

interface Mailbox extends Observable{

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
     * @return Address
     */
    public function getAddress();

    /**
     * @return AddressLibrary
     */
    public function getAddressLibrary();

    /**
     * @return Domain
     */
    public function getDomain();

    /**
     * @return DomainLibrary
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