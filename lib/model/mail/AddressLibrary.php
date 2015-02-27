<?php
namespace ChristianBudde\Part\model\mail;
use ChristianBudde\Part\controller\ajax\TypeHandlerGenerator;
use ChristianBudde\Part\controller\json\JSONObjectSerializable;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/3/14
 * Time: 2:13 PM
 */

interface AddressLibrary extends JSONObjectSerializable, TypeHandlerGenerator{

    /**
     * @return Address[] An array containing selected entries.
     */
    public function listAddresses();

    /**
     * @param string $localPart
     * @return bool
     */
    public function hasAddressWithLocalPart($localPart);

    /**
     * Gets a address from the given address. Null if not found.
     * @param string $localPart
     * @return Address
     */
    public function getAddress($localPart);

    /**
     * @param string $localPart
     * @return Address
     */
    public function createAddress($localPart);

    /**
     * @param Address $address
     * @return bool
     */
    public function contains(Address $address);

    /**
     * Deletes an address. It must be an instance in the library.
     * @param Address $address
     * @return void
     */
    public function deleteAddress(Address $address);


    /**
     * Returns the domain associated with the address.
     * @return Domain
     */
    public function getDomain();

    /**
     * @return Address
     */
    public function getCatchallAddress();

    /**
     * @return Address
     */
    public function createCatchallAddress();

    /**
     * @return void
     */
    public function deleteCatchallAddress();

    /**
     * @return bool
     */
    public function hasCatchallAddress();


    /**
     * @return DomainLibrary
     */
    public function getDomainLibrary();

}