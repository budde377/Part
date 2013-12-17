<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 18/01/13
 * Time: 09:37
 */
class DefaultPageLibraryImplTest extends PHPUnit_Framework_TestCase
{

    /** @var DefaultPageLibraryImpl */
    private $pageLibrary;
    /** @var Config */
    private $config;

    private $defaultPagesArray;

    public function setUp()
    {
        $this->config = new StubConfigImpl();
        $this->defaultPagesArray = array();
        $this->defaultPagesArray["page1"]["alias"] = "";
        $this->defaultPagesArray["page1"]["id"] = "p1";
        $this->defaultPagesArray["page1"]["template"] = "someTemplate";
        $this->defaultPagesArray["page2"]["alias"] = "/alias/";
        $this->defaultPagesArray["page2"]["id"] = "p2";
        $this->defaultPagesArray["page2"]["template"] = "someTemplate2";
        $this->config->setDefaultPages($this->defaultPagesArray);
        $this->pageLibrary = new DefaultPageLibraryImpl($this->config);
    }

    public function testGetPageWillReturnNullIfNoMatch(){
        $page = $this->pageLibrary->getPage("nonExistingPage");
        $this->assertNull($page);
    }

    public function testGetPageWillReturnInstanceMatchingPage()
    {
        /** @var $page Page */
        $page = $this->pageLibrary->getPage("p1");
        $this->assertInstanceOf("DefaultPageImpl",$page);
        $this->assertFalse($page->isEditable());
        $this->assertEquals("p1",$page->getID());
        $this->assertEquals("someTemplate",$page->getTemplate());
        $this->assertEquals("page1",$page->getTitle());
        $this->assertEquals("",$page->getAlias());
    }

    public function testListPagesWillListPages(){
        $pages = $this->pageLibrary->listPages();
        $this->assertArrayHasKey(0,$pages);
        /** @var $page Page */
        $page = $pages[0];
        $this->assertInstanceOf("DefaultPageImpl",$page);
        $this->assertFalse($page->isEditable());
        $this->assertEquals("p1",$page->getID());
        $this->assertEquals("someTemplate",$page->getTemplate());
        $this->assertEquals("page1",$page->getTitle());
        $this->assertEquals("",$page->getAlias());
        $this->assertArrayHasKey(1,$pages);
        /** @var $page Page */
        $page = $pages[1];
        $this->assertInstanceOf("DefaultPageImpl",$page);
        $this->assertFalse($page->isEditable());
        $this->assertEquals("p2",$page->getID());
        $this->assertEquals("someTemplate2",$page->getTemplate());
        $this->assertEquals("page2",$page->getTitle());
        $this->assertEquals("/alias/",$page->getAlias());
    }

    public function testIterateIsAsListingPage(){
        $i = 0;
        foreach($this->pageLibrary as $page){
            switch($i){
                case 0:
                    $this->assertEquals("page1",$page->getTitle());
                    break;
                case 1;
                    $this->assertEquals("page2", $page->getTitle());
                    break;
            }
            $i++;
        }
        $this->assertEquals(2,$i);
    }

    public function testGetPageWillBeCached(){
        $this->assertTrue($this->pageLibrary->getPage("p1") === $this->pageLibrary->getPage("p1"));
    }

}
