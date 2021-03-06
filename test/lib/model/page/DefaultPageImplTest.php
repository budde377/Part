<?php
/**
 * User: budde
 * Date: 25/05/13
 * Time: 22:31
 */

namespace ChristianBudde\Part\model\page;

use ChristianBudde\Part\StubBackendSingletonContainerImpl;
use PHPUnit_Framework_TestCase;

class DefaultPageImplTest extends PHPUnit_Framework_TestCase
{
    /** @var  DefaultPageImpl */
    private $defaultPage;
    private $id, $template, $title, $alias;

    public function setUp()
    {

        $this->id = "someId";
        $this->template = "someTemplate";
        $this->title = "someTitle";
        $this->alias = "/someAlias/";
        $this->defaultPage = new DefaultPageImpl(new StubBackendSingletonContainerImpl(), $this->id, $this->title, $this->template, $this->alias);
    }

    public function testVariables()
    {
        $this->assertEquals($this->id, $this->defaultPage->getID());
        $this->assertEquals($this->title, $this->defaultPage->getTitle());
        $this->assertEquals($this->template, $this->defaultPage->getTemplate());
        $this->assertEquals($this->alias, $this->defaultPage->getAlias());
    }

    public function testDefaultPageDoesNotExist()
    {
        $this->assertFalse($this->defaultPage->exists());
        $this->assertFalse($this->defaultPage->create());
        $this->assertFalse($this->defaultPage->exists());
    }

    public function testDefaultPageCannotBeEdited()
    {
        $this->assertFalse($this->defaultPage->isEditable());
        $this->assertFalse($this->defaultPage->setID("id"));
        $this->assertEquals($this->id, $this->defaultPage->getID());
        $this->defaultPage->setTitle('title');
        $this->assertEquals($this->title, $this->defaultPage->getTitle());
        $this->defaultPage->setTemplate('templ');
        $this->assertEquals($this->template, $this->defaultPage->getTemplate());
        $this->assertFalse($this->defaultPage->setAlias('/asd/'));
        $this->assertEquals($this->alias, $this->defaultPage->getAlias());
        $this->assertFalse($this->defaultPage->isValidAlias('/validAlias/'));
        $this->assertFalse($this->defaultPage->isValidId('validId'));
    }

    public function testMatchIsWorking()
    {
        $this->assertTrue($this->defaultPage->match("someAlias"));
        $this->assertTrue($this->defaultPage->match($this->id));
        $this->assertFalse($this->defaultPage->match("doesnotmatch"));
    }

    public function testIsNotHiddenAndCanNotBe()
    {
        $this->assertFalse($this->defaultPage->isHidden());
        $this->defaultPage->hide();
        $this->assertFalse($this->defaultPage->isHidden());
    }

    public function testContentIsNullPageContent()
    {
        $this->assertInstanceOf('ChristianBudde\Part\model\NullContentImpl', $this->defaultPage->getContent());
    }

    public function testPageIsJSONObjectSerializable()
    {
        $o = $this->defaultPage->jsonObjectSerialize();
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\PageObjectImpl', $o);
        $this->assertEquals($o->getVariable('title'), $this->defaultPage->getTitle());

    }



    public function testGeneratorGeneratesRight(){
        $this->assertTrue($this->defaultPage === $this->defaultPage->generateTypeHandler());
    }
}