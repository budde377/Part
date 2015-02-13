<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/16/14
 * Time: 11:01 PM
 */

namespace ChristianBudde\Part\controller\json;


use ChristianBudde\Part\model\mail\Mailbox;

class MailMailboxObjectImpl extends ObjectImpl{
    function __construct(Mailbox $mailbox)
    {
        parent::__construct('mail_mailbox');
        $this->setVariable('name', $mailbox->getName());
        $this->setVariable('last_modified', $mailbox->lastModified());
    }
} 