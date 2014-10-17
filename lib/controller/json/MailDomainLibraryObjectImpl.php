<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/16/14
 * Time: 10:36 PM
 */

namespace ChristianBudde\cbweb\controller\json;


use ChristianBudde\cbweb\model\mail\DomainLibrary;

class MailDomainLibraryObjectImpl extends ObjectImpl{

    function __construct(DomainLibrary $domainLibrary)
    {
        parent::__construct('mail_domain_library');
        $this->setVariable('domains', $domainLibrary->listDomains());

    }
} 