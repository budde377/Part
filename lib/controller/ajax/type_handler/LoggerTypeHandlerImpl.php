<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/27/15
 * Time: 11:08 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\log\Logger;
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

//TODO test this
class LoggerTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    use TypeHandlerTrait;

    function __construct(BackendSingletonContainer $container, Logger $logger)
    {
        parent::__construct($logger, 'Logger');

        $this->addAuthFunction($this->currentUserLoggedInAuthFunction($container));

        $this->addFunctionAuthFunction("Logger", 'clearLog', $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addFunctionAuthFunction("Logger", 'listLog', $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addFunctionAuthFunction("Logger", 'getContextAt', $this->currentUserSitePrivilegesAuthFunction($container));

    }
}