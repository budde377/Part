<?php
namespace ChristianBudde\Part\model\mail;
use ChristianBudde\Part\controller\ajax\TypeHandlerGenerator;
use ChristianBudde\Part\controller\json\JSONObjectSerializable;
use ChristianBudde\Part\util\Observable;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/3/14
 * Time: 9:32 AM
 */

interface Domain extends Observable, JSONObjectSerializable, TypeHandlerGenerator{
    const EVENT_DELETE = 2;



    /**
     * Gets the current domain name.
     * @return string
     */
    public function getDomainName();


    /**
     * Indicates if the domain is active
     * @return bool
     */
    public function isActive();

    /**
     * @return void
     */
    public function activate();

    /**
     * @return void
     */
    public function deactivate();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return void
     */
    public function setDescription($description);

    /**
     * The last time it was modified
     * @return int A UNIX timestamp in seconds
     */
    public function lastModified();

    /**
     * The creation time
     * @return int A UNIX timestamp in seconds
     */
    public function createdAt();

    /**
     * @return bool TRUE if exists else FALSE
     */
    public function exists();

    /**
     * Creates the domain if it does not exist.
     * @param string $password
     * @return bool
     */
    public function create($password);

    /**
     * Deletes the domain.
     * @param string $password
     * @return bool
     */
    public function delete($password);

    /**
     * @return AddressLibrary
     */
    public function getAddressLibrary();


    /**
     * @return bool
     */
    public function isAliasDomain();

    /**
     * @param Domain $domain
     * @return void
     */
    public function setAliasTarget(Domain $domain);

    /**
     * @return Domain
     */
    public function getAliasTarget();

    /**
     * @return void
     */
    public function clearAliasTarget();


    /**
     * @return DomainLibrary
     */
    public function getDomainLibrary();


}