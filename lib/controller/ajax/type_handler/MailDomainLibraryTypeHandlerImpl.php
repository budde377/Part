<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:10 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\mail\DomainLibrary;

class MailDomainLibraryTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    private $container;
    private $library;

    function __construct(BackendSingletonContainer $container, DomainLibrary $library)
    {
        $this->container = $container;
        $this->library = $library;
    }


}