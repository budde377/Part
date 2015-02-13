<?php
namespace ChristianBudde\Part\test;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\ConfigImpl;
use ChristianBudde\Part\exception\EntryNotFoundException;
use ChristianBudde\Part\exception\FileNotFoundException;
use ChristianBudde\Part\model\site\Site;
use ChristianBudde\Part\test\stub\StubBackendSingletonContainerImpl;
use ChristianBudde\Part\test\stub\StubCurrentPageStrategyImpl;
use ChristianBudde\Part\test\stub\StubPageImpl;
use ChristianBudde\Part\test\stub\StubPageOrderImpl;
use ChristianBudde\Part\test\stub\StubSiteImpl;
use ChristianBudde\Part\test\stub\StubUserLibraryImpl;
use ChristianBudde\Part\test\stub\StubVariablesImpl;
use ChristianBudde\Part\util\file\FileImpl;
use ChristianBudde\Part\util\file\FolderImpl;
use ChristianBudde\Part\view\page_element\PageElementFactoryImpl;
use ChristianBudde\Part\view\template\TemplateImpl;
use Exception;
use PHPUnit_Framework_TestCase;
use SimpleXMLElement;
use Twig_Error;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/29/12
 * Time: 10:46 AM
 * To change this template use File | Settings | File Templates.
 */
class TemplateImplTest extends PHPUnit_Framework_TestCase
{

    /** @var $backFactory BackendSingletonContainer */
    private $backFactory;
    /** @var  \ChristianBudde\Part\model\page\Page */
    private $inactivePage;
    /** @var $template \ChristianBudde\Part\view\template\TemplateImpl */
    private $template;
    private $rootPath;
    /** @var  StubPageImpl */
    private $currentPage;
    /** @var  Site */
    private $site;

    private $defaultOwner = "<siteInfo><domain name='test' extension='dk'/><owner name='Admin Jensen' mail='test@test.dk' username='asd' /></siteInfo>";

    protected function setUp()
    {
        $this->backFactory = $this->template = $this->rootPath = $this->currentPage = null;
        @session_start();

    }

    protected function tearDown()
    {
        unset($this->template);
        @session_destroy();
    }

    private function setUpConfig($config = null)
    {
        if ($config == null) {
            $config = "
            <config>{$this->defaultOwner}
            <pageElements>
                <class name='someElement' link='lib/stub/HelloPageElementImpl.php'>ChristianBudde\\Part\\test\\stub\\HelloPageElementImpl</class>
                <class name='initElement' link='lib/stub/CheckInitializedPageElementImpl.php'>ChristianBudde\\Part\\test\\stub\\CheckInitializedPageElementImpl</class>
            </pageElements>

            </config>";
        }

        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string($config);
        $rootDir = new FolderImpl(dirname(__FILE__) . '/../');
        $this->rootPath = $rootDir->getAbsolutePath() . "/";
        $config = new ConfigImpl($configXML, $this->rootPath);

        // Setting up pages
        $this->currentPage = new StubPageImpl();

        $this->inactivePage = new StubPageImpl();
        $this->inactivePage->setID("someid");

        // Setting up current page strategy
        $currentPageStrategy = new StubCurrentPageStrategyImpl();
        $currentPageStrategy->setCurrentPage($this->currentPage);


        // Setting up order
        $order = new StubPageOrderImpl();
        $order->setInactiveList(array($this->inactivePage));


        // Setting up site
        $this->site = new StubSiteImpl();
        $this->site->setVariables(new StubVariablesImpl());

        // Setting up back factory
        $this->backFactory = new StubBackendSingletonContainerImpl();
        $this->backFactory->setCurrentPageStrategyInstance($currentPageStrategy);
        $this->backFactory->setConfigInstance($config);
        $this->backFactory->setUserLibraryInstance(new StubUserLibraryImpl());
        $this->backFactory->setPageOrderInstance($order);
        $this->backFactory->setSiteInstance($this->site);

        // Setting up null page element factory
        $nullPageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);

        // Setting up template
        $this->template = new TemplateImpl($nullPageElementFactory, $this->backFactory);
    }

    public function testWillThrowExceptionIfTemplateIsNotFound()
    {

        $this->setUpConfig();
        $exceptionWasThrown = false;
        $file = new FileImpl('nonExistingFile');
        try {
            $this->template->setTemplate($file);
        } catch (Exception $exception) {
            $this->assertInstanceOf('ChristianBudde\Part\exception\FileNotFoundException', $exception, 'Got the wrong exception');
            /** @var $exception FileNotFoundException */
            $exceptionWasThrown = true;
            $this->assertEquals($file->getAbsoluteFilePath(), $exception->getFileName(), 'Did not expect the right file');

        }

        $this->assertTrue($exceptionWasThrown, 'Exception was not thrown');
    }


    public function testCanSetTemplateFromExistingFile()
    {
        $this->setUpConfig("
        <config>
            {$this->defaultOwner}
            <templates path='stubs/'>
                <template filename='templateStub.twig'>main</template>
            </templates>
        </config>");

        $this->template->setTemplateFromConfig('main');
        $this->assertContains("Hello World", $this->template->render());

    }


    public function testWillThrowExceptionIfTemplateFileIsNotFoundFromConfig()
    {
        $this->setUpConfig("
        <config>
            {$this->defaultOwner}
            <templates path='folder'>
                <template filename='NonExistingFile'>main</template>
            </templates>
        </config>");

        $exceptionWasThrown = false;
        try {
            $this->template->setTemplateFromConfig('main');
        } catch (Exception $exception) {
            $this->assertInstanceOf('ChristianBudde\Part\exception\FileNotFoundException', $exception, 'Got the wrong exception');
            /** @var $exception FileNotFoundException */
            $exceptionWasThrown = true;
            $this->assertEquals($this->rootPath . 'folder/NonExistingFile', $exception->getFileName(), 'Did not expect the right file');

        }

        $this->assertTrue($exceptionWasThrown, 'Exception was not thrown');

    }

    public function testCanAddFallbackConfigOnEntryNotFoundInConfigWithoutExceptions()
    {
        $this->setUpConfig("
        <config>
            {$this->defaultOwner}
            <templates path='stubs/'>
                <template filename='templateStub.twig'>main</template>
            </templates>
        </config>");

        $this->template->setTemplateFromConfig('lololol', "main");
        $this->assertContains("Hello World", $this->template->render());
    }

    public function testWillThrowExceptionIfTemplateNotInConfig()
    {
        $this->setUpConfig();
        $exceptionWasThrown = false;
        try {
            $this->template->setTemplateFromConfig('main');
        } catch (Exception $exception) {
            $this->assertInstanceOf('ChristianBudde\Part\exception\EntryNotFoundException', $exception, 'Got the wrong exception');
            /** @var $exception EntryNotFoundException */
            $exceptionWasThrown = true;
            $this->assertEquals('main', $exception->getEntry(), 'Could not find the right wrong entry');

        }

        $this->assertTrue($exceptionWasThrown, 'Exception was not thrown');
    }


    public function testTemplatesUsesTwig()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{%set t='World' %}Hello{{t}}");
        $this->assertEquals("HelloWorld", $this->template->render());
    }

    public function testDebugEnablesDebug()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $this->assertGreaterThan(0, strlen($this->template->render()));

    }

    public function testTemplateAddsCurrentUserName()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertTrue(strpos($v, "current_user") !== false);
    }


    public function testTemplateAddsUpdater()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertTrue(strpos($v, "updater") !== false);
    }

    public function testTemplateAddsHasSitePrivileges()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertTrue(strpos($v, "has_site_privileges") !== false);
    }

    public function testTemplateAddsHasPagePrivileges()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertTrue(strpos($v, "has_page_privileges") !== false);
    }

    public function testTemplateAddsHasRootPrivileges()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertTrue(strpos($v, "has_root_privileges") !== false);
    }

    public function testTemplateAddsUserLibrary()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertTrue(strpos($v, "user_lib") !== false);
    }

    public function testTemplateAddsCurrentPage()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertTrue(strpos($v, "current_page") !== false);
    }


    public function testTemplateAddsCurrentPagePath()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertTrue(strpos($v, "current_page_path") !== false);
    }

    public function testTemplateAddsPageOrder()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertTrue(strpos($v, "page_order") !== false);
    }

    public function testTemplateAddsBackendContainer()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertTrue(strpos($v, "backend_container") !== false);
    }

    public function testTemplateAddsCSSRegister()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertTrue(strpos($v, "css_register") !== false);
    }

    public function testTemplateAddsJSRegister()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertTrue(strpos($v, "js_register") !== false);
    }

    public function testTemplateAddsPageElementFacoty()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertTrue(strpos($v, "page_element_factory") !== false);
    }

    public function testTemplateAddsSite()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertContains("site", $v);
    }

    public function testTemplateAddsConfig()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertContains("config", $v);
    }

    public function testTemplateAddsInitialize()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertContains("initialize", $v);
    }

    public function testTemplateAddsLastModified()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertContains("last_modified", $v);

    }

    public function testTemplateAddsDebug()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{{ dump() }}");
        $v = $this->template->render();
        $this->assertContains("debug_mode", $v);

    }

    public function testTemplateSupportsPageElementTag()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{%page_element someElement%}");
        $v = $this->template->render();
        $this->assertEquals("Hello World", $v);
    }

    public function testTemplateSupportsPageElementTagAlsoWithElementNotInConfig()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{%page_element ChristianBudde.Part.test.stub.HelloPageElementImpl%}");
        $v = $this->template->render();
        $this->assertEquals("Hello World", $v);
    }

    public function testTemplateSupportsPageElementTagAlsoWithElementNotInConfigAndWithNamespaces()
    {
        $this->setUpConfig();
        $this->template->setTwigDebug(true);
        $this->template->setTemplateFromString("{%page_element ChristianBudde.Part.test.stub.HelloNamespacePageElementImpl  %}");
        $v = $this->template->render();
        $this->assertEquals("Hello World", $v);
    }

    public function testTemplateBreakIfNoPageElement()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%page_element nonExistingElement%}");
        $exception = false;
        try {
            $this->template->render();
        } catch (Twig_Error $error) {
            $exception = true;
            $this->assertEquals(1, $error->getTemplateLine());
        }
        $this->assertTrue($exception);
    }

    public function testTemplateInitializePageElementIsSupported()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%init_page_element someElement%}");
        $this->assertEquals("", $this->template->render());
    }

    public function testTemplateInitializePageElementIsSupportedFromClassName()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%init_page_element ChristianBudde.Part.test.stub.HelloPageElementImpl%}");
        $this->assertEquals("", $this->template->render());
    }

    public function testTemplateInitializePageElementIsSupportedFromClassNameWithNamespace()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%init_page_element ChristianBudde.Part.view.page_element.TitlePageElementImpl%}");
        $this->assertEquals("", $this->template->render());
    }

    public function testTemplateInitializePageElementDoesJustThat()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%init_page_element initElement%}");
        $_SESSION['initialized'] = 0;
        $this->template->render();
        $this->assertEquals(1, $_SESSION['initialized']);
    }

    public function testTemplateInitializePageElementCanBeCalledMultipleTimes()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%init_page_element initElement%}{%init_page_element initElement%}");
        $_SESSION['initialized'] = 0;
        $this->template->render();
        $this->assertEquals(1, $_SESSION['initialized']);
    }

    public function testTemplateInitializePageElementAndPageElementCanBothBeDone()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%init_page_element initElement%}{%page_element initElement%}");
        $_SESSION['initialized'] = 0;
        $this->template->render();
        $this->assertEquals(1, $_SESSION['initialized']);
    }

    public function testTemplateInitializeWillBeDoneOnPageElement()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%page_element initElement%}");
        $_SESSION['initialized'] = 0;
        $this->assertEquals(0, $_SESSION['initialized']);
        $this->template->render();
        $this->assertEquals(1, $_SESSION['initialized']);
    }

    public function testTemplateInitializePageElementWillOnlyBeInitializedOnceOnOneInitAndOneUsage()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%init_page_element initElement%}{%page_element initElement%}");
        $_SESSION['initialized'] = 0;
        $this->assertEquals(0, $_SESSION['initialized']);
        $this->template->render();
        $this->assertEquals(1, $_SESSION['initialized']);
    }

    public function testInitPageElementWillBeDoneTwiceOnTwiceRender()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%init_page_element initElement%}");
        $_SESSION['initialized'] = 0;
        $this->template->render();
        $this->template->render();
        $this->assertEquals(2, $_SESSION['initialized']);
    }

    public function testTemplateInitializePageElementBreaksOnElementNotFound()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%init_page_element nonExistingElement%}");
        $exception = false;
        try {
            $this->template->render();
        } catch (Twig_Error $error) {
            $exception = true;
            $this->assertEquals(1, $error->getTemplateLine());
        }
        $this->assertTrue($exception);
    }

    public function testTemplateWillSupportPageContent()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%page_content someElement %}");
        $this->assertEquals("", $this->template->render());
    }

    public function testTemplateWillSupportPageContentWithNoId()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%page_content asd%}");
        $this->assertEquals("", $this->template->render());
    }

    public function testTemplateWillAddPageContent()
    {
        $this->setUpConfig();
        $this->currentPage->getContent()->addContent("Hello World");
        $this->template->setTemplateFromString("{%page_content%}");
        $this->assertEquals("Hello World", $this->template->render());
    }


    public function testTemplateWillUpdatePageContent()
    {
        $this->setUpConfig();
        $this->currentPage->getContent()->addContent("Hello World");
        $this->template->setTemplateFromString("{%page_content%}");
        $this->template->render();
        $this->currentPage->getContent()->addContent("Hello World2");
        $this->assertEquals("Hello World2", $this->template->render());
    }

    public function testTemplateWillGetPageContentFromOtherSiteThanCurrent()
    {
        $this->setUpConfig();
        $c = "LOLSTRING";
        $this->inactivePage->getContent("SomeId")->addContent($c);
        $this->template->setTemplateFromString("{%page_content someid[SomeId] %}");
        $r = $this->template->render();
        $this->assertEquals($c, $r);
    }

    public function testTemplateCanCallEmptyContentIdOnOtherSite()
    {
        $this->setUpConfig();
        $c = "LOLSTRING";
        $this->inactivePage->getContent()->addContent($c);
        $this->template->setTemplateFromString("{%page_content someid[] %}");
        $r = $this->template->render();
        $this->assertEquals($c, $r);

    }

    public function testTemplateWillReturnEmptyOnWrongId()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%page_content somenonexitingid[SomeId] %}");
        $r = $this->template->render();
        $this->assertEquals("", $r);
    }


    public function testTemplateWillSupportSiteContent()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%site_content someElement %}");
        $this->assertEquals("", $this->template->render());
    }

    public function testTemplateWillAddSiteContent()
    {
        $this->setUpConfig();
        $this->site->getContent("")->addContent("Hello World");
        $this->template->setTemplateFromString("{%site_content%}");
        $this->assertEquals("Hello World", $this->template->render());
    }


    public function testTemplateWillUpdateSiteContent()
    {
        $this->setUpConfig();
        $this->site->getContent("")->addContent("Hello World");
        $this->template->setTemplateFromString("{%site_content%}");
        $this->template->render();
        $this->site->getContent("")->addContent("Hello World2");
        $this->assertEquals("Hello World2", $this->template->render());
    }


    public function testTemplateWillAddSiteContentWithId()
    {
        $this->setUpConfig();
        $this->site->getContent("someid")->addContent("Hello World");
        $this->template->setTemplateFromString("{%site_content someid%}");
        $this->assertEquals("Hello World", $this->template->render());
    }


    public function testTemplateWillSupportPageVariables()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%page_variable someElement %}");
        $this->assertEquals("", $this->template->render());
    }

    public function testTemplateWillSupportPageVariableWithNoId()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%page_variable asd %}");
        $this->assertEquals("", $this->template->render());
    }

    public function testTemplateWillAddPageVariable()
    {
        $this->setUpConfig();
        $this->currentPage->getVariables()->setValue("asd", "Hello World");
        $this->template->setTemplateFromString("{%page_variable asd%}");
        $this->assertEquals("Hello World", $this->template->render());
    }


    public function testTemplateWillUpdatePageVariable()
    {
        $this->setUpConfig();
        $this->currentPage->getVariables()->setValue("foo", "Hello World");
        $this->template->setTemplateFromString("{%page_variable foo%}");
        $this->template->render();
        $this->currentPage->getVariables()->setValue("foo", "Hello World2");
        $this->assertEquals("Hello World2", $this->template->render());
    }

    public function testTemplateSupportPageVariablesFromOtherPages()
    {
        $this->setUpConfig();
        $k = "Foo";
        $v = "Bar";
        $id = $this->inactivePage->getID();
        $this->inactivePage->getVariables()->setValue($k, $v);
        $this->template->setTemplateFromString("{% page_variable {$id}[$k] %}");
        $this->assertEquals($v, $this->template->render());
    }

    public function testTemplateSupportPageVariablesFromOtherNonExistingPages()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{% page_variable somenonexistingid[someid] %}");
        $this->assertEquals("", $this->template->render());

    }


    public function testTemplateWillSupportSiteVariable()
    {
        $this->setUpConfig();
        $this->template->setTemplateFromString("{%site_variable someElement %}");
        $this->assertEquals("", $this->template->render());
    }

    public function testTemplateWillAddSiteVariable()
    {
        $this->setUpConfig();
        $this->site->getVariables()->setValue("foo", "Hello World");
        $this->template->setTemplateFromString("{%site_variable foo%}");
        $this->assertEquals("Hello World", $this->template->render());
    }


    public function testTemplateWillUpdateSiteVariable()
    {
        $this->setUpConfig();
        $this->site->getVariables()->setValue("foo", "Hello World");
        $this->template->setTemplateFromString("{%site_variable foo%}");
        $this->template->render();
        $this->site->getVariables()->setValue("foo", "Hello World2");
        $this->assertEquals("Hello World2", $this->template->render());
    }

}

