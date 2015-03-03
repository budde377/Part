<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:14 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\mail\AddressLibrary;

class MailAddressLibraryTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    private $container;
    private $library;

    function __construct(BackendSingletonContainer $container, AddressLibrary $library)
    {
        $this->container = $container;
        $this->library = $library;
    }


}