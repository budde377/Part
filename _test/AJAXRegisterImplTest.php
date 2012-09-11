<?php
require_once dirname(__FILE__) . '/../_class/AJAXRegisterImpl.php';
require_once dirname(__FILE__) . '/_stub/NullRegistrableImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/13/12
 * Time: 6:21 PM
 * To change this template use File | Settings | File Templates.
 */
class AJAXRegisterImplTest extends PHPUnit_Framework_TestCase
{

    /** @var $register AJAXRegisterImpl */
    private $register;

    protected function setUp()
    {
        $this->register = new AJAXRegisterImpl();
    }

    public function testListRegisterWillReturnArrayWithNoRegistered()
    {
        $this->assertTrue(is_array($this->register->listRegistered()), 'Did not return array');
    }

    public function testListRegisterWillReturnRegisteredIDs()
    {
        $registrable = new NullRegistrableImpl();
        $this->register->registerAJAX('id1', $registrable);
        $registered = $this->register->listRegistered();
        $this->assertTrue(in_array('id1', $registered), 'Did not have id "id1"');
    }

    public function testListRegisteredWillNotContainDuplicates()
    {
        $registrable1 = new NullRegistrableImpl();
        $registrable2 = new NullRegistrableImpl();
        $this->register->registerAJAX('id1', $registrable1);
        $this->register->registerAJAX('id1', $registrable2);
        $registered = $this->register->listRegistered();
        $this->assertEquals(1, count($registered), 'Did contain duplicates');

    }

    public function testGetAJAXFromRegisteredWillReturnValueOfCallback()
    {
        $id = 'id1';
        $val = 'someVal';
        $registrable = new NullRegistrableImpl(array($id => $val));
        $this->register->registerAJAX($id, $registrable);
        $registeredVal = $this->register->getAJAXFromRegistered($id);
        $this->assertEquals($val, $registeredVal, 'Values did not match');
    }

    public function testGetAJAXFromRegisteredWillReturnNullIfIDNotThere()
    {
        $id = 'id1';
        $registeredVal = $this->register->getAJAXFromRegistered($id);
        $this->assertNull($registeredVal, 'Was not null');
    }

    public function testOnDuplicatesWillOldBeKept()
    {
        $id = 'id1';
        $val1 = 'val1';
        $val2 = 'val2';
        $registrable1 = new NullRegistrableImpl(array($id => $val1));
        $registrable2 = new NullRegistrableImpl(array($id => $val2));
        $this->register->registerAJAX($id, $registrable1);
        $this->register->registerAJAX($id, $registrable2);
        $registeredVal = $this->register->getAJAXFromRegistered($id);
        $this->assertEquals($val1, $registeredVal, 'Did not keep old registrable');
    }


}
