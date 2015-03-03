<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:12 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\mail\Domain;

class MailDomainTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    private $container;
    private $domain;

    function __construct(BackendSingletonContainer $container, Domain $domain)
    {
        $this->container = $container;
        $this->domain = $domain;
    }


}