<?php
require_once dirname(__FILE__) . '/../_class/AJAXRegisterImpl.php';
require_once dirname(__FILE__) . '/_stub/NullRegistrableImpl.php';
require_once dirname(__FILE__) . '/_stub/StubConfigImpl.php';
require_once dirname(__FILE__) . '/_stub/NullBackendSingletonContainerImpl.php';

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
        $this->register = new AJAXRegisterImpl(new NullBackendSingletonContainerImpl());
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

    public function testGetAJAXFromRegisteredWillReturnValueOfCallbackWithFunctionName()
    {
        $id = 'id1';
        $val = 'someVal';
        $registrable = new NullRegistrableImpl(array($id => $val));
        $this->register->registerAJAX($id, $registrable);
        $registeredVal = $this->register->getAJAXFromRegisteredFromFunctionName($id.".someFunction");
        $this->assertEquals($val, $registeredVal, 'Values did not match');
    }

    public function testGetAJAXFromRegisteredWillReturnNullIfIDNotThere()
    {
        $id = 'id1';
        $registeredVal = $this->register->getAJAXFromRegistered($id);
        $this->assertNull($registeredVal, 'Was not null');
    }

    public function testOnDuplicatesBothWillBeKeptOldWillBeReturnIfNotNull()
    {
        $id = 'id1';
        $val1 = 'val1';
        $val2 = 'val2';
        $registrable1 = new NullRegistrableImpl(array($id => $val1));
        $registrable2 = new NullRegistrableImpl(array($id => $val2));
        $this->register->registerAJAX($id, $registrable1);
        $this->register->registerAJAX($id, $registrable2);
        $registeredVal = $this->register->getAJAXFromRegistered($id);
        $this->assertEquals($val1, $registeredVal);
    }

    public function testOnDuplicatesBothWillBeKeptNewestWillBeReturnedIfFirstNull()
    {
        $id = 'id1';
        $val1 = null;
        $val2 = null;
        $val3 = 'val3';
        $registrable1 = new NullRegistrableImpl(array($id => $val1));
        $registrable2 = new NullRegistrableImpl(array($id => $val2));
        $registrable3 = new NullRegistrableImpl(array($id => $val3));
        $this->register->registerAJAX($id, $registrable1);
        $this->register->registerAJAX($id, $registrable2);
        $this->register->registerAJAX($id, $registrable3);
        $registeredVal = $this->register->getAJAXFromRegistered($id);
        $this->assertEquals($val3, $registeredVal);
    }

    public function testRegisterFromConfigWillRegisterOnEmptyInput(){
        $config = new StubConfigImpl();
        $config->setAJAXRegistrable(array());
        $this->assertEquals(0, count($this->register->listRegistered()));
        $this->register->registerAJAXFromConfig($config);
        $this->assertEquals(0, count($this->register->listRegistered()));

    }

    public function testRegisterWillThrowExceptionOnInvalidLink(){
        $config = new StubConfigImpl();
        $config->setAJAXRegistrable(array(array('class_name'=>'nonExistingClass', 'path'=>'notARealFile', 'ajax_id'=>'someId')));
        $this->assertEquals(0, count($this->register->listRegistered()));
        $exceptionWasThrown = false;
        try{
            $this->register->registerAJAXFromConfig($config);
        } catch (Exception $e){
            $exceptionWasThrown = true;
            $this->assertInstanceOf('FileNotFoundException', $e);
        }
        $this->assertTrue($exceptionWasThrown);
    }


    public function testRegisterWillThrowExceptionOnInvalidClassName(){
        $config = new StubConfigImpl();
        $config->setAJAXRegistrable(array(array('class_name'=>'nonExistingClass','path'=>__FILE__, 'ajax_id'=>'someId')));
        $this->assertEquals(0, count($this->register->listRegistered()));
        $exceptionWasThrown = false;
        try{
            $this->register->registerAJAXFromConfig($config);
        } catch (Exception $e){
            $exceptionWasThrown = true;
            $this->assertInstanceOf('ClassNotDefinedException', $e);
        }
        $this->assertTrue($exceptionWasThrown);
    }

    public function testRegisterWillThrowExceptionOnNotRightInstance(){
        $config = new StubConfigImpl();
        $config->setAJAXRegistrable(array(array('class_name'=>'AJAXRegisterImplTest', 'path'=>__FILE__, 'ajax_id'=>'someId')));
        $this->assertEquals(0, count($this->register->listRegistered()));
        $exceptionWasThrown = false;
        try{
            $this->register->registerAJAXFromConfig($config);
        } catch (Exception $e){
            $exceptionWasThrown = true;
            $this->assertInstanceOf('ClassNotInstanceOfException', $e);
        }
        $this->assertTrue($exceptionWasThrown);
    }

    public function testRegisterWillRegisterOnRightInstanceInConfig(){
        $config = new StubConfigImpl();
        $config->setAJAXRegistrable(array(array('class_name'=>'NullRegistrableImpl', 'path'=>dirname(__FILE__).'/_stub/NullRegistrableImpl.php', 'ajax_id'=>'someId')));
        $this->assertEquals(0, count($this->register->listRegistered()));
        $this->assertEquals(0, count($this->register->listRegistered()));
        $this->register->registerAJAXFromConfig($config);
        $registered = $this->register->listRegistered();
        $this->assertEquals(1, count($registered));
        $this->assertEquals('someId', $registered[0]);
    }


    public function testRegisterWillRegisterTwoOnRightInstanceInConfig(){
        $config = new StubConfigImpl();
        $config->setAJAXRegistrable(array(array('class_name'=> 'NullRegistrableImpl', 'path'=>dirname(__FILE__).'/_stub/NullRegistrableImpl.php', 'ajax_id'=>'someId'),array('class_name'=> 'NullRegistrableImpl', 'path'=>dirname(__FILE__).'/_stub/NullRegistrableImpl.php', 'ajax_id'=>'someId2')));
        $this->assertEquals(0, count($this->register->listRegistered()));
        $this->assertEquals(0, count($this->register->listRegistered()));
        $this->register->registerAJAXFromConfig($config);
        $registered = $this->register->listRegistered();
        $this->assertEquals(2, count($registered));
        $this->assertEquals('someId', $registered[0]);
        $this->assertEquals('someId2', $registered[1]);
    }
    /*
     * $chain = new ScriptChainImpl();

        $postScriptArray = $this->config->getPostScripts();
        foreach ($postScriptArray as $className => $location) {

            if (!file_exists($location)) {
                throw new FileNotFoundException($location);
            }
            require_once $location;

            if (!class_exists($className)) {
                throw new ClassNotDefinedException($className);
            }

            $preScript = new $className();

            if (!($preScript instanceof Script)) {
                throw new ClassNotInstanceOfException($className, 'Script');
            }

            $chain->addScript($preScript);
        }

        return $chain;
     */
}
