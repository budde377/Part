<?php
require_once dirname(__FILE__) . '/MySQLConstants.php';
require_once dirname(__FILE__) . '/../_class/PageImpl.php';
require_once dirname(__FILE__) . '/_stub/StubDBImpl.php';
require_once dirname(__FILE__) . '/_stub/StubObserverImpl.php';
require_once dirname(__FILE__) . '/TruncateOperation.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/16/12
 * Time: 9:18 PM
 * To change this template use File | Settings | File Templates.
 */
class PageImplTest extends PHPUnit_Extensions_Database_TestCase
{

    /** @var $db StubDBImpl */
    private $db;
    /** @var $pdo PDO */
    private $pdo;

    public function setUp()
    {
        parent::setUp();
        $this->pdo = new PDO('mysql:dbname=' . self::database . ';host=' . self::host, self::username, self::password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $this->db = new StubDBImpl();
        $this->db->setConnection($this->pdo);
    }




    public function testGetIDWillReturnIDGivenInConstructorAndReflectChangesInSetTitle()
    {

        $page = new PageImpl('someID', $this->db);
        $this->assertEquals('someID', $page->getID(), "ID's did not match.");
        $newID = 'nonExistingID';
        $ret = $page->setID($newID);
        $this->assertEquals($newID, $page->getID());
        $this->assertTrue($ret, 'Did not return true');

    }

    public function testIsEditableWillBeTrue(){
        $page = new PageImpl('someID', $this->db);
        $this->assertTrue($page->isEditable());
    }


    public function testGetTitleWillReflectChangesInSetTitle()
    {
        $page = new PageImpl('someID', $this->db);
        $oldTitle = $page->getTitle();
        $this->assertTrue(is_string($oldTitle), 'Did not return string');
        $newTitle = 'newTitle';
        $page->setTitle($newTitle);
        $this->assertEquals($newTitle, $page->getTitle(), 'Title was not changed');
    }



    public function testGetTemplateWillReflectChangesInSetTemplate()
    {
        $page = new PageImpl('someID', $this->db);
        $oldTemplate = $page->getTemplate();
        $this->assertTrue(is_string($oldTemplate), 'Did not return string');
        $newTemplate = 'newTemplate';
        $page->setTemplate($newTemplate);
        $this->assertEquals($newTemplate, $page->getTemplate(), 'Template was not changed');
    }

    public function testGetAliasWillReflectChangesInSetAlias()
    {
        $page = new PageImpl('someID', $this->db);
        $oldAlias = $page->getAlias();
        $this->assertTrue(is_string($oldAlias), 'Did not return string');
        $newAlias = '/newAlias/';
        $aliasRet = $page->setAlias($newAlias);
        $this->assertTrue($aliasRet, 'Did not return true on valid alias');
        $this->assertEquals($newAlias, $page->getAlias(), 'Alias was not changed');
    }


    public function testExistsWillBeTrueIfPageExistsInDatabase()
    {
        $page = new PageImpl('testpage', $this->db);
        $this->assertTrue($page->exists(), 'Was not true');

    }

    public function testExistsWillBeFalseIfPageDoesNotExistsInDatabase()
    {
        $page = new PageImpl('notAValidId', $this->db);
        $this->assertFalse($page->exists(), 'Did not return false when page does not exists');
    }


    public function testCreateWillReturnTrueIfSuccess()
    {
        $page = new PageImpl('idDoesNotExists', $this->db);
        $this->assertFalse($page->exists(), 'Page did exist');
        $createRet = $page->create();
        $this->assertTrue($page->exists(), 'Page was not created');
        $this->assertTrue($createRet, 'Did not return true');
    }


    public function testCreateWillReturnTrueIfEntranceExists()
    {
        $page = new PageImpl('testpage', $this->db);
        $this->assertTrue($page->exists(), 'Did not exists');
        $this->assertFalse($page->create(), 'Did not return true when exists');
    }

    public function testDeleteWillReturnTrueOnSuccess()
    {
        $page = new PageImpl('testpage', $this->db);
        $this->assertTrue($page->exists(), 'Did not exist');
        $deleteRet = $page->delete();
        $this->assertFalse($page->exists(), 'Did not delete');
        $this->assertTrue($deleteRet, 'Did not return true');
    }

    public function testDeleteWillReturnFalseOnFailure()
    {
        $page = new PageImpl('idDoesNotExists', $this->db);
        $this->assertFalse($page->exists(), 'Did exist');
        $this->assertFalse($page->delete(), 'Did not return false');
    }

    public function testGetsWillMatchThatOfDatabase()
    {
        $page = new PageImpl('testpage', $this->db);
        $this->assertEquals('title', $page->getTitle());
        $this->assertEquals('template', $page->getTemplate());
        $this->assertEquals('alias', $page->getAlias());
        $this->assertEquals('testpage', $page->getID());
    }


    public function testSetsWillBePersistent()
    {
        $page = new PageImpl('testpage', $this->db);
        $this->assertTrue($page->exists(), 'Page did not exists');
        $newString = 'string';
        $page->setAlias('/' . $newString . '/');
        $page->setID($newString);
        $page->setTemplate($newString);
        $page->setTitle($newString);

        $newPage = new PageImpl('string', $this->db);
        $this->assertTrue($newPage->exists(), 'Did not exists');
        $this->assertEquals('/' . $newString . '/', $newPage->getAlias(), 'Alias did not match');
        $this->assertEquals($newString, $newPage->getID(), 'ID did not match');
        $this->assertEquals($newString, $newPage->getTemplate(), 'Template did not match');
        $this->assertEquals($newString, $newPage->getTitle(), 'Title did not match');

    }


    public function testSetIDMustNotContainIllegalCharactersOrBeEmpty()
    {
        $page = new PageImpl('somePage', $this->db);
        $oldID = $page->getID();
        $idRet = $page->setID('illegalID**"")=?');
        $this->assertFalse($idRet, 'Did not return false');
        $this->assertEquals($oldID, $page->getID(), 'ID was updated');

    }


    public function testIDInConstructorMustBeValidElseThrowException()
    {
        $exceptionWasThrown = false;

        try {
            $page = new PageImpl('illegalID**"""', $this->db);

        } catch (Exception $e) {
            $exceptionWasThrown = true;
            $this->assertInstanceOf('MalformedParameterException', $e, 'Exception was of wrong instance');
            /** @var $e MalformedParameterException */
            $this->assertEquals(1, $e->getParameterNumber(), 'Wrong parameter no');
            $this->assertEquals('RegEx[a-zA-Z0-9-_]+', $e->getExpectedType(), 'Wrong type');
        }

        $this->assertTrue($exceptionWasThrown, 'Exception was not thrown');
    }

    public function testSetIDMustReturnFalseWithChangeToExistingID()
    {
        $page = new PageImpl('somePage', $this->db);
        $page->create();
        $idRet = $page->setID('testpage');
        $this->assertFalse($idRet, 'setID did not return false');
        $this->assertEquals('somePage', $page->getID(), 'ID was changed');
    }


    public function testSetIDToCurrentIDReturnTrue()
    {
        $page = new PageImpl('testpage', $this->db);
        $idRet = $page->setID('testpage');
        $this->assertTrue($idRet, 'setID did not return true');
        $this->assertEquals('testpage', $page->getID(), 'Did change id');
    }

    public function testSetAliasMustMatchPatternOfPregMatch()
    {
        $page = new PageImpl('somePage', $this->db);
        $idRet = $page->setAlias('nonValidPattern');
        $this->assertFalse($idRet, 'setAlias did not return false');
        $this->assertEquals('', $page->getAlias(), 'Did change alias');

    }


    public function testMatchWillReturnTrueIfCurrentIDGiven()
    {
        $id = 'tespage';
        $page = new PageImpl($id, $this->db);
        $match = $page->match($id);
        $this->assertTrue($match, 'Did not return true on match');
    }


    public function testMatchWillReturnFalseIfNonIdDoesNotMatchAlias()
    {
        $page = new PageImpl('somePage', $this->db);
        $match = $page->match('stringThatDoesNotMatchEmptyAlias');
        $this->assertFalse($match, 'Did not return false on no match');

    }

    public function testMatchWillReturnTrueIfNonIdDoesMatchAlias()
    {
        $page = new PageImpl('somePage', $this->db);
        $page->setAlias('/stringThatDoesMatchEmptyAlias/');
        $match = $page->match('stringThatDoesMatchEmptyAlias');
        $this->assertTrue($match, 'Did return false on match');

    }

    public function testChangeIDWillCallObserver()
    {
        $page = new PageImpl('somePage', $this->db);
        $observer1 = new StubObserverImpl();
        $observer2 = new StubObserverImpl();
        $page->attachObserver($observer1);
        $page->attachObserver($observer2);

        $page->setID('anotherID');
        $this->assertTrue($observer1->hasBeenCalled());
        $this->assertTrue($observer2->hasBeenCalled());
        $this->assertTrue($observer1->getLastCallSubject() == $observer2->getLastCallSubject());
        $this->assertTrue($observer1->getLastCallType() == $observer2->getLastCallType());
        $this->assertTrue($observer1->getLastCallSubject() === $page);
        $this->assertTrue($observer1->getLastCallType() == Page::EVENT_ID_UPDATE);
    }

    public function testDetachObserverWillDetachObserver()
    {
        $page = new PageImpl('somePage', $this->db);
        $observer1 = new StubObserverImpl();
        $observer2 = new StubObserverImpl();
        $page->attachObserver($observer1);
        $page->attachObserver($observer2);
        $page->detachObserver($observer2);
        $page->setID('anotherID');
        $this->assertTrue($observer1->hasBeenCalled());
        $this->assertFalse($observer2->hasBeenCalled());
    }

    public function testDeleteWillCallObserver()
    {
        $page = new PageImpl('testpage', $this->db);
        $observer1 = new StubObserverImpl();
        $page->attachObserver($observer1);
        $page->delete();
        $this->assertTrue($observer1->hasBeenCalled());
        $this->assertTrue($page === $observer1->getLastCallSubject());
        $this->assertEquals(Page::EVENT_DELETE, $observer1->getLastCallType());
    }

    public function getSetUpOperation()
    {
        $cascadeTruncates = true;
        return new PHPUnit_Extensions_Database_Operation_Composite(array(new TruncateOperation($cascadeTruncates), PHPUnit_Extensions_Database_Operation_Factory::INSERT()));
    }

    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        $pdo = new PDO('mysql:dbname=' . self::database . ';host=' . self::host, self::username, self::password);
        return $this->createDefaultDBConnection($pdo);
    }

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createMySQLXMLDataSet(dirname(__FILE__) . '/_mysqlXML/PageImplTest.xml');
    }


    const database = MySQLConstants::MYSQL_DATABASE;
    const password = MySQLConstants::MYSQL_PASSWORD;
    const username = MySQLConstants::MYSQL_USERNAME;
    const host = MySQLConstants::MYSQL_HOST;


}
