<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:16 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\mail\Mailbox;

class MailboxTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    private $container;
    private $mailbox;

    function __construct(BackendSingletonContainer $container, Mailbox $mailbox)
    {
        $this->container = $container;
        $this->mailbox = $mailbox;
    }


}