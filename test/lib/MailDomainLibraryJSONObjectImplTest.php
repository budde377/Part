<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 5:59 PM
 */
namespace ChristianBudde\Part\test;

use ChristianBudde\Part\controller\json\MailDomainLibraryObjectImpl;
use ChristianBudde\Part\test\stub\StubMailDomainImpl;
use ChristianBudde\Part\test\stub\StubMailDomainLibraryImpl;
use PHPUnit_Framework_TestCase;


class MailDomainLibraryJSONObjectImplTest extends PHPUnit_Framework_TestCase
{

    public function testConstructorWillSetVariables()
    {

        $domain = new StubMailDomainLibraryImpl();
        $domain->setDomainList($l = ["test" => new StubMailDomainImpl(true, "test")]);
        $o = new MailDomainLibraryObjectImpl($domain);
        $this->assertEquals('mail_domain_library', $o->getName());
        $this->assertEquals($l, $o->getVariable('domains'));
    }

}