<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 5:59 PM
 */
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\controller\json\MailDomainObjectImpl;
use ChristianBudde\cbweb\test\stub\StubMailDomainImpl;
use PHPUnit_Framework_TestCase;


class MailDomainJSONObjectImplTest extends PHPUnit_Framework_TestCase
{

    public function testConstructorWillSetVariables()
    {

        $domain = new StubMailDomainImpl($active = true, $domainName = "domain_name");
        $domain->description = $description = "some desc";
        $domain->lastModified = $lastModified = 123;
        $domain->aliasDomain = new StubMailDomainImpl(true, $aliasDomain = "SomeAlias");
        $jsonObject = new MailDomainObjectImpl($domain);

        $this->assertEquals('mail_domain', $jsonObject->getName());
        $this->assertEquals($domainName, $jsonObject->getVariable('domain_name'));
        $this->assertEquals($description , $jsonObject->getVariable('description'));
        $this->assertEquals($lastModified, $jsonObject->getVariable('last_modified'));
        $this->assertEquals($aliasDomain, $jsonObject->getVariable('alias_target'));

    }

}