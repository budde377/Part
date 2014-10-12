<?php
namespace ChristianBudde\cbweb\model\mail;
use ChristianBudde\cbweb\model\user\User;
use ChristianBudde\cbweb\util\Observable;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/3/14
 * Time: 2:19 PM
 */

interface Address extends Observable {

    const EVENT_DELETE = 1;
    const EVENT_CHANGE_LOCAL_PART = 2;

    /**
     * @return string
     */
    public function getLocalPart();

    /**
     * @param $localPart
     * @return void
     */
    public function setLocalPart($localPart);

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
     * @return bool
     */
    public function delete();

    /**
     * Creates an address
     * @return bool
     */
    public function create();


    /**
     * @return Domain
     */
    public function getDomain();


    /**
     * @return AddressLibrary
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
     * @param string $target
     * @return bool
     */
    public function hasTarget($target);


    /**
     * Will return a mailbox, if there is any. If not it will return NULL
     * @return Mailbox | null
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
     * @return Mailbox
     */
    public function createMailbox($name, $password);

    /**
     * Removes the mailbox.
     * @return void
     */
    public function deleteMailbox();


    /**
     * @return DomainLibrary
     */
    public function getDomainLibrary();

    /**
     * @return string
     */
    public function getId();

    /**
     * Adds a user as owner of the address.
     * @param User $owner
     * @return void
     */
    public function addOwner(User $owner);

    /**
     * Removes a user as owner of the address.
     * @param User $owner
     * @return void
     */
    public function removeOwner(User $owner);

    /**
     * Checks if a user is a owner of the address.
     * @param User $owner
     * @return bool
     */
    public function isOwner(User $owner);

    /**
     * Lists the username of the owners as strings.
     * @param bool $instances If true will returns instances rather than username strings
     * @return array
     */
    public function listOwners($instances = false);
}