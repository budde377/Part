<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/3/14
 * Time: 2:13 PM
 */

interface PostfixAddressLibrary {

    const LIST_MODE_ALL = 1;
    const LIST_MODE_ALIAS = 2;
    const LIST_MODE_MAILBOX = 3;

    /**
     * @param int $mode Decides which will be listed (alias, mailbox or both)
     * @return array An array containing selected entries.
     */
    public function listAddresses($mode = PostfixAddressLibrary::LIST_MODE_ALL);

    /**
     * @param string $address
     * @return bool
     */
    public function hasAddress($address);

    /**
     * Gets a address from the given address. Null if not found.
     * @param string $address
     * @return PostfixAddress
     */
    public function getAddress($address);

    /**
     * Gets a alias from the given address. Null if not found.
     * @param string $address
     * @return PostfixAlias
     */
    public function getAlias($address);

    /**
     * Gets a mailbox from the given address. Null if not found.
     * @param string $address
     * @return PostfixMailbox
     */
    public function getMailbox($address);

    /**
     * Creates an Alias.
     * @param string $address
     * @param array $targets
     * @return PostfixAlias
     */
    public function createAlias($address, array $targets);

    /**
     * Creates an mailbox.
     * @param string $address
     * @return PostFixAddress
     */
    public function createMailbox($address);

    /**
     * Deletes an address. It must be an instance in the library.
     * @param PostfixAddress $address
     * @return void
     */
    public function deleteAddress(PostfixAddress $address);

    /**
     * Returns the domain associated with the address.
     * @return PostfixDomain
     */
    public function getDomain();

    /**
     * @return PostfixAlias
     */
    public function getCatchallAlias();

    /**
     * @param array $targets
     * @return PostfixAlias
     */
    public function createCatchallAlias(array $targets);

    /**
     * @return void
     */
    public function deleteCatchallAlias();

    /**
     * @return bool
     */
    public function hasCatchallAlias();
}