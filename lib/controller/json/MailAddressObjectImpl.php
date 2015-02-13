<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/16/14
 * Time: 10:58 PM
 */

namespace ChristianBudde\Part\controller\json;


use ChristianBudde\Part\model\mail\Address;

class MailAddressObjectImpl extends ObjectImpl{
    function __construct(Address $address)
    {
        parent::__construct('mail_address');
        $this->setVariable('local_part', $address->getLocalPart());
        $this->setVariable('active', $address->isActive());
        $this->setVariable('last_modified', $address->lastModified());
        $this->setVariable('targets', $address->getTargets());
        $this->setVariable('mailbox', $address->getMailbox());
        $this->setVariable('owners', $address->listOwners(false));
    }
} 