<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/16/14
 * Time: 10:36 PM
 */

namespace ChristianBudde\Part\controller\json;


use ChristianBudde\Part\model\mail\Domain;

class MailDomainObjectImpl extends ObjectImpl{

    function __construct(Domain $domainLibrary)
    {
        parent::__construct('mail_domain');
        $this->setVariable('domain_name',$domainLibrary->getDomainName());
        $this->setVariable('description',$domainLibrary->getDescription());
        $this->setVariable('last_modified',$domainLibrary->lastModified());
        $this->setVariable('address_library',$domainLibrary->getAddressLibrary());
        $this->setVariable('alias_target',$domainLibrary->isAliasDomain()?$domainLibrary->getAliasTarget()->getDomainName():null);
        $this->setVariable('active',$domainLibrary->isActive());

    }
} 