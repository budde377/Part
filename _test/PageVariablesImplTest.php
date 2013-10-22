<?php
require_once dirname(__FILE__).'/TruncateOperation.php';
require_once dirname(__FILE__).'/MySQLConstants.php';
require_once dirname(__FILE__).'/_stub/StubDBImpl.php';
require_once dirname(__FILE__).'/../_class/PageVariablesImpl.php';
require_once dirname(__FILE__).'/../_class/PageImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/22/13
 * Time: 3:53 PM
 * To change this template use File | Settings | File Templates.
 */

class PageVariablesImplTest extends PHPUnit_Extensions_Database_TestCase{

    private $db;
    /** @var  PageVariablesImpl */
    private $existingVariables;
    /** @var  PageVariablesImpl */
    private $nonExistingVariables;
    /** @var  PageVariablesImpl */
    private $nonExistingVariablesNonExistingPage;
    /** @var  Page */
    private $existingPage;
    /** @var  Page */
    private $existingPage2;
    /** @var  Page */
    private $nonExistingPage;

    public function setUp(){
        parent::setUp();
        $this->db = new StubDBImpl();
        $pdo = new PDO('mysql:dbname=' . MySQLConstants::MYSQL_DATABASE. ';host=' . MySQLConstants::MYSQL_HOST, MySQLConstants::MYSQL_USERNAME, MySQLConstants::MYSQL_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $this->db->setConnection($pdo);
        $this->existingPage = new PageImpl('testpage', $this->db);
        $this->existingVariables = new PageVariablesImpl($this->db, $this->existingPage);
        $this->existingPage2 = new PageImpl('testpage2', $this->db);
        $this->nonExistingPage = new PageImpl('nosuchpage', $this->db);
        $this->nonExistingVariables = new PageVariablesImpl($this->db, $this->existingPage2);
        $this->nonExistingVariablesNonExistingPage = new PageVariablesImpl($this->db, $this->nonExistingPage);

    }

    public function testListKeysWillReturnArray(){
        $ar = $this->existingVariables->listKeys();
        $this->assertTrue(is_array($ar));
        $ar = $this->nonExistingVariables->listKeys();
        $this->assertTrue(is_array($ar));

    }

    public function testListKeysWillHaveRightContent(){
        $ar = $this->existingVariables->listKeys();
        $this->assertEquals(2, count($ar));
        $this->assertEquals('test1', $ar[0]);
        $this->assertEquals('test2', $ar[1]);

        $ar = $this->nonExistingVariables->listKeys();
        $this->assertEquals(0, count($ar));
    }

    public function testGetListOfNonExistingPageReturnsArray(){
        $var = new PageVariablesImpl($this->db, $this->nonExistingPage);
        $this->assertTrue(is_array($var->listKeys()));
        $this->assertEquals(0, count($var->listKeys()));
    }

    public function testGetValueWillGetValueIfExist(){
        $this->assertEquals("val1", $this->existingVariables->getValue("test1"));
        $this->assertEquals("val2", $this->existingVariables->getValue("test2"));
    }

    public function testGetValueWillReturnNullIfNotExist(){
        $this->assertNull($this->existingVariables->getValue("NonExistingKey"));
    }

    public function testHasKeyWillReturnTrueIfHasKey(){
        $this->assertTrue($this->existingVariables->hasKey("test1"));
    }

    public function testHasKeyWillReturnFalseIfDoesNotHasKey(){
        $this->assertFalse($this->existingVariables->hasKey("NonExistingKey"));
    }

    public function testRemoveKeyWillRemoveKey(){
        $this->existingVariables->removeKey("test1");
        $this->assertFalse($this->existingVariables->hasKey("test1"));
        $this->assertEquals(1, count($this->existingVariables->listKeys()));
    }

    public function testRemoveOfNonExistingDoesNotChangeAnything(){
        $this->existingVariables->removeKey("nonExisting");
        $this->assertEquals(2, count($this->existingVariables->listKeys()));
    }

    public function testRemoveIsPersistent(){
        $this->existingVariables->removeKey("test1");
        $var = new PageVariablesImpl($this->db, $this->existingPage);
        $this->assertEquals(1, count($var->listKeys()));
        $this->assertFalse($var->hasKey("test1"));
    }

    public function testSetValueWillDoJustThat(){
        $this->existingVariables->setValue("test3", "val3");
        $this->assertEquals(3, count($this->existingVariables->listKeys()));
        $this->assertTrue($this->existingVariables->hasKey("test3"));
        $this->assertEquals("val3", $this->existingVariables->getValue("test3"));
    }

    public function testSetValueWilLBePersistent(){
        $this->existingVariables->setValue("test3", "val3");
        $var = new PageVariablesImpl($this->db, $this->existingPage);
        $this->assertEquals(3, count($var->listKeys()));
        $this->assertTrue($var->hasKey("test3"));
        $this->assertEquals("val3", $var->getValue("test3"));
    }

    public function testSetValueWillOverwrite(){
        $this->existingVariables->setValue("test2", "val2000");
        $this->assertEquals(2, count($this->existingVariables->listKeys()));
        $this->assertTrue($this->existingVariables->hasKey("test2"));
        $this->assertEquals("val2000", $this->existingVariables->getValue("test2"));
    }

    public function testSetValueOfNonExistingPageWillNotWork(){
        $this->nonExistingVariablesNonExistingPage->setValue("test", "lol");
        $this->assertFalse($this->nonExistingVariablesNonExistingPage->hasKey("test"));
    }

    public function testForeachWillTraverse(){
        $seen1 = $seen2 = false;
        $i = 0;
        foreach($this->existingVariables as $key=>$var){
            $seen1 = $seen1 || ($var == "val1" && $key == "test1");
            $seen2 = $seen2 || ($var == "val2" && $key == "test2");
            $i++;
        }
        $this->assertEquals(2, $i);
        $this->assertTrue($seen1);
        $this->assertTrue($seen2);
    }


    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        $pdo = new PDO('mysql:dbname=' . MySQLConstants::MYSQL_DATABASE . ';host=' . MySQLConstants::MYSQL_HOST, MySQLConstants::MYSQL_USERNAME, MySQLConstants::MYSQL_PASSWORD);
        return $this->createDefaultDBConnection($pdo);
    }

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createMySQLXMLDataSet(dirname(__FILE__) . '/_mysqlXML/PageVariablesImplTest.xml');
    }

    public function getSetUpOperation()
    {
        $cascadeTruncates = true;
        return new PHPUnit_Extensions_Database_Operation_Composite(array(new TruncateOperation($cascadeTruncates), PHPUnit_Extensions_Database_Operation_Factory::INSERT()));
    }
}