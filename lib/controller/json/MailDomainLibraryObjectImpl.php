<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/16/14
 * Time: 10:36 PM
 */

namespace ChristianBudde\Part\controller\json;


use ChristianBudde\Part\model\mail\Domain;
use ChristianBudde\Part\model\mail\DomainLibrary;

class MailDomainLibraryObjectImpl extends ObjectImpl{

    function __construct(DomainLibrary $domainLibrary)
    {
        parent::__construct('mail_domain_library');
        $l = $domainLibrary->listDomains();
        $this->setVariable('domains', array_combine(array_map(function (Domain $k){ return $k->getDomainName();}, $l), $l));

    }
} 