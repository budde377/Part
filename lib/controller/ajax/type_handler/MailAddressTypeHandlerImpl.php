<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:15 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\mail\Address;

class MailAddressTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    private $container;
    private $address;

    function __construct(BackendSingletonContainer $container, Address $address)
    {
        $this->container = $container;
        $this->address = $address;
    }


}