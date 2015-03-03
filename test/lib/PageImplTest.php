<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/16/12
 * Time: 9:18 PM
 * To change this template use File | Settings | File Templates.
 */
namespace ChristianBudde\Part\test;

use ChristianBudde\Part\exception\MalformedParameterException;
use ChristianBudde\Part\model\page\Page;
use ChristianBudde\Part\model\page\PageImpl;
use ChristianBudde\Part\test\stub\StubBackendSingletonContainerImpl;
use ChristianBudde\Part\test\stub\StubDBImpl;
use ChristianBudde\Part\test\stub\StubObserverImpl;
use ChristianBudde\Part\test\util\CustomDatabaseTestCase;
use Exception;

class PageImplTest extends CustomDatabaseTestCase
{

    /** @var $db StubDBImpl */
    private $db;
    /** @var PageImpl */
    private $testPage;
    /** @var PageImpl */
    private $testPage2;
    private $container;


    function __construct()
    {
        parent::__construct(dirname(__FILE__) . '/../mysqlXML/PageImplTest.xml');
    }


    public function setUp()
    {
        parent::setUp();
        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->container = new StubBackendSingletonContainerImpl();
        $this->container->setDBInstance($this->db);
        $this->testPage = new PageImpl($this->container, 'testpage');
        $this->testPage2 = new PageImpl($this->container, 'testpage2');
    }


    public function testGetIDWillReturnIDGivenInConstructorAndReflectChangesInSetTitle()
    {

        $page = new PageImpl($this->container, 'someID');
        $this->assertEquals('someID', $page->getID(), "ID's did not match.");
        $newID = 'nonExistingID';
        $ret = $page->setID($newID);
        $this->assertEquals($newID, $page->getID());
        $this->assertTrue($ret, 'Did not return true');

    }

    public function testIsEditableWillBeTrue()
    {
        $page = new PageImpl($this->container, 'someID');
        $this->assertTrue($page->isEditable());
    }


    public function testGetTitleWillReflectChangesInSetTitle()
    {
        $page = new PageImpl($this->container, 'someID');
        $oldTitle = $page->getTitle();
        $this->assertTrue(is_string($oldTitle), 'Did not return string');
        $newTitle = 'newTitle';
        $page->setTitle($newTitle);
        $this->assertEquals($newTitle, $page->getTitle(), 'Title was not changed');
    }


    public function testGetTemplateWillReflectChangesInSetTemplate()
    {
        $page = new PageImpl($this->container, 'someID');
        $oldTemplate = $page->getTemplate();
        $this->assertTrue(is_string($oldTemplate), 'Did not return string');
        $newTemplate = 'newTemplate';
        $page->setTemplate($newTemplate);
        $this->assertEquals($newTemplate, $page->getTemplate(), 'Template was not changed');
    }

    public function testGetAliasWillReflectChangesInSetAlias()
    {
        $page = new PageImpl($this->container, 'someID');
        $oldAlias = $page->getAlias();
        $this->assertTrue(is_string($oldAlias), 'Did not return string');
        $newAlias = '/newAlias/';
        $aliasRet = $page->setAlias($newAlias);
        $this->assertTrue($aliasRet, 'Did not return true on valid alias');
        $this->assertEquals($newAlias, $page->getAlias(), 'Alias was not changed');
    }


    public function testExistsWillBeTrueIfPageExistsInDatabase()
    {
        $this->assertTrue($this->testPage->exists(), 'Was not true');

    }

    public function testExistsWillBeFalseIfPageDoesNotExistsInDatabase()
    {
        $page = new PageImpl($this->container, 'notAValidId');
        $this->assertFalse($page->exists(), 'Did not return false when page does not exists');
    }


    public function testCreateWillReturnTrueIfSuccess()
    {
        $page = new PageImpl($this->container, 'idDoesNotExists');
        $this->assertFalse($page->exists(), 'Page did exist');
        $createRet = $page->create();
        $this->assertTrue($page->exists(), 'Page was not created');
        $this->assertTrue($createRet, 'Did not return true');
    }


    public function testCreateWillReturnTrueIfEntranceExists()
    {

        $this->assertTrue($this->testPage->exists(), 'Did not exists');
        $this->assertFalse($this->testPage->create(), 'Did not return true when exists');
    }

    public function testDeleteWillReturnTrueOnSuccess()
    {
        $this->assertTrue($this->testPage->exists(), 'Did not exist');
        $deleteRet = $this->testPage->delete();
        $this->assertFalse($this->testPage->exists(), 'Did not delete');
        $this->assertTrue($deleteRet, 'Did not return true');
    }

    public function testDeleteWillReturnFalseOnFailure()
    {
        $page = new PageImpl($this->container, 'idDoesNotExists');
        $this->assertFalse($page->exists(), 'Did exist');
        $this->assertFalse($page->delete(), 'Did not return false');
    }

    public function testGetsWillMatchThatOfDatabase()
    {
        $this->assertEquals('title', $this->testPage->getTitle());
        $this->assertEquals('template', $this->testPage->getTemplate());
        $this->assertEquals('alias', $this->testPage->getAlias());
        $this->assertEquals('testpage', $this->testPage->getID());
    }


    public function testSetsWillBePersistent()
    {
        $this->assertTrue($this->testPage->exists(), 'Page did not exists');
        $newString = 'string';
        $this->testPage->setAlias('/' . $newString . '/');
        $this->testPage->setID($newString);
        $this->testPage->setTemplate($newString);
        $this->testPage->setTitle($newString);

        $newPage = new PageImpl($this->container, 'string');
        $this->assertTrue($newPage->exists(), 'Did not exists');
        $this->assertEquals('/' . $newString . '/', $newPage->getAlias(), 'Alias did not match');
        $this->assertEquals($newString, $newPage->getID(), 'ID did not match');
        $this->assertEquals($newString, $newPage->getTemplate(), 'Template did not match');
        $this->assertEquals($newString, $newPage->getTitle(), 'Title did not match');

    }


    public function testSetIDMustNotContainIllegalCharactersOrBeEmpty()
    {
        $page = new PageImpl($this->container, 'somePage');
        $oldID = $page->getID();
        $idRet = $page->setID('illegalID**"")=?');
        $this->assertFalse($idRet, 'Did not return false');
        $this->assertEquals($oldID, $page->getID(), 'ID was updated');

    }


    public function testIDInConstructorMustBeValidElseThrowException()
    {
        $exceptionWasThrown = false;

        try {
            new PageImpl($this->container, 'illegalID**"""');

        } catch (Exception $e) {
            $exceptionWasThrown = true;
            $this->assertInstanceOf('ChristianBudde\Part\exception\MalformedParameterException', $e, 'Exception was of wrong instance');
            /** @var $e MalformedParameterException */
            $this->assertEquals(1, $e->getParameterNumber(), 'Wrong parameter no');
            $this->assertEquals('RegEx[a-zA-Z0-9-_]+', $e->getExpectedType(), 'Wrong type');
        }

        $this->assertTrue($exceptionWasThrown, 'Exception was not thrown');
    }

    public function testSetIDMustReturnFalseWithChangeToExistingID()
    {
        $page = new PageImpl($this->container, 'somePage');
        $page->create();
        $idRet = $page->setID('testpage');
        $this->assertFalse($idRet, 'setID did not return false');
        $this->assertEquals('somePage', $page->getID(), 'ID was changed');
    }


    public function testSetIDToCurrentIDReturnTrue()
    {
        $idRet = $this->testPage->setID('testpage');
        $this->assertTrue($idRet, 'setID did not return true');
        $this->assertEquals('testpage', $this->testPage->getID(), 'Did change id');
    }

    public function testSetAliasMustMatchPatternOfPregMatch()
    {
        $page = new PageImpl($this->container, 'somePage');
        $idRet = $page->setAlias('nonValidPattern');
        $this->assertFalse($idRet, 'setAlias did not return false');
        $this->assertEquals('', $page->getAlias(), 'Did change alias');

    }

    public function testSetAliasCanBeEmpty()
    {
        $page = new PageImpl($this->container, 'somePage');
        $idRet = $page->setAlias('');
        $this->assertTrue($idRet, 'setAlias did not return false');
        $this->assertEquals('', $page->getAlias(), 'Did change alias');
    }


    public function testMatchWillReturnTrueIfCurrentIDGiven()
    {
        $id = 'tespage';
        $page = new PageImpl($this->container, $id);
        $match = $page->match($id);
        $this->assertTrue($match, 'Did not return true on match');
    }


    public function testMatchWillReturnFalseIfNonIdDoesNotMatchAlias()
    {
        $page = new PageImpl($this->container, 'somePage');
        $match = $page->match('stringThatDoesNotMatchEmptyAlias');
        $this->assertFalse($match, 'Did not return false on no match');

    }

    public function testMatchWillReturnTrueIfNonIdDoesMatchAlias()
    {
        $page = new PageImpl($this->container, 'somePage');
        $page->setAlias('/stringThatDoesMatchEmptyAlias/');
        $match = $page->match('stringThatDoesMatchEmptyAlias');
        $this->assertTrue($match, 'Did return false on match');

    }

    public function testChangeIDWillCallObserver()
    {
        $page = new PageImpl($this->container, 'somePage');
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
        $page = new PageImpl($this->container, 'somePage');
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
        $observer1 = new StubObserverImpl();
        $this->testPage->attachObserver($observer1);
        $this->testPage->delete();
        $this->assertTrue($observer1->hasBeenCalled());
        $this->assertTrue($this->testPage === $observer1->getLastCallSubject());
        $this->assertEquals(Page::EVENT_DELETE, $observer1->getLastCallType());
    }

    public function testValidatorWillValidate()
    {
        $this->assertTrue($this->testPage->isValidAlias('/validAlias/'));
        $this->assertFalse($this->testPage->isValidAlias('invalidAlias'));
        $this->assertTrue($this->testPage->isValidId('validid'));
        $this->assertFalse($this->testPage->isValidId('testpage'));

    }

    public function testHiddenHasCorrectValue()
    {
        $this->assertFalse($this->testPage->isHidden());
    }

    public function testHideDoesHidePage()
    {
        $this->testPage->hide();
        $this->assertTrue($this->testPage->isHidden());
    }

    public function testShowPageDoesShowPage()
    {
        $this->testPage->hide();
        $this->assertTrue($this->testPage->isHidden());
        $this->testPage->show();
        $this->assertFalse($this->testPage->isHidden());
    }

    public function testHiddingIsPersistent()
    {
        $this->assertFalse($this->testPage->isHidden());
        $this->testPage->hide();
        $page = new PageImpl($this->container, 'testpage');
        $this->assertTrue($page->isHidden());
    }

    public function testCreateWillSaveHidden()
    {
        $id = 'nonExistingID';
        $page = new PageImpl($this->container, $id);
        $page->hide();
        $page->create();
        $this->assertTrue($page->isHidden());
        $page = new PageImpl($this->container, $id);
        $this->assertTrue($page->isHidden());
    }

    public function testGetContentReturnsInstanceOfContent()
    {
        $this->assertInstanceOf("ChristianBudde\\Part\\model\\Content", $this->testPage->getContent());
    }

    public function testGetContentReturnSameInstanceOnSameId()
    {
        $content = $this->testPage->getContent();
        $content2 = $this->testPage->getContent();
        $this->assertTrue($content === $content2);
    }

    public function testGetContentReturnDifferentInstanceOnDifferentId()
    {
        $content = $this->testPage->getContent();
        $content2 = $this->testPage->getContent('someId');
        $this->assertFalse($content === $content2);

    }

    public function testWillReturnMinusOneOnNotModified()
    {
        $this->assertEquals(0, $this->testPage->lastModified());
    }

    public function testWillReturnLaterTimestampOnModifiedCalled()
    {
        $this->testPage->modify();
        $this->assertGreaterThan(0, $this->testPage->lastModified());
    }

    public function testGetVariablesWillReturnInstanceOfVariables()
    {
        $var = $this->testPage->getVariables();
        $this->assertInstanceOf("ChristianBudde\\Part\\model\\page\\PageVariablesImpl", $var);
    }

    public function testGetVariablesWillReturnSameInstance()
    {
        $this->assertTrue($this->testPage->getVariables() === $this->testPage->getVariables());
    }


    public function testGetPageContentWillReturnAndReuseContentLibrary()
    {
        $lib1 = $this->testPage->getContentLibrary();
        $lib2 = $this->testPage->getContentLibrary();
        $this->assertTrue($lib1 === $lib2);
        $this->assertInstanceOf("ChristianBudde\\Part\\model\\ContentLibrary", $lib1);
    }

    public function testPageIsJSONObjectSerializable()
    {
        $o = $this->testPage->jsonObjectSerialize();
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\PageObjectImpl', $o);
        $this->assertEquals($o->getVariable('title'), $this->testPage->getTitle());

    }

    public function testLibraryReturnsRightPageInstance()
    {
        $this->assertTrue($this->testPage === $this->testPage->generateTypeHandler());
    }

}
