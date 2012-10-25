<?php
require_once dirname(__FILE__) . '/../_class/ConfigImpl.php';
require_once dirname(__FILE__) . '/../_class/PageElementFactoryImpl.php';
require_once dirname(__FILE__) . '/../_class/TemplateImpl.php';
require_once dirname(__FILE__) . '/_stub/NullPageElementFactoryImpl.php';
require_once dirname(__FILE__) . '/_stub/HelloPageElementImpl.php';
require_once dirname(__FILE__) . '/_stub/NullPageElementImpl.php';
require_once dirname(__FILE__) . '/_stub/NullBackendSingletonContainerImpl.php';
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

    /** @var $template TemplateImpl */
    private $template;
    private $rootPath;

    protected function setUp()
    {
        $this->backFactory = new NullBackendSingletonContainerImpl();
    }

    private function setUpConfig($config = '<config></config>')
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string($config);
        $this->rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $this->rootPath);
        $nullPageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $this->template = new TemplateImpl($config, $nullPageElementFactory);
    }

    public function testWillThrowExceptionIfTemplateIsNotFound()
    {

        $this->setUpConfig();
        $exceptionWasThrown = false;
        $file = new FileImpl('nonExistingFile');
        try {
            $this->template->setTemplate($file);
        } catch (Exception $exception) {
            $this->assertInstanceOf('FileNotFoundException', $exception, 'Got the wrong exception');
            /** @var $exception FileNotFoundException */
            $exceptionWasThrown = true;
            $this->assertEquals($file->getAbsoluteFilePath(), $exception->getFileName(), 'Did not expect the right file');

        }

        $this->assertTrue($exceptionWasThrown, 'Exception was not thrown');
    }

    public function testWillThrowExceptionIfTemplateFileIsNotFoundFromConfig()
    {
        $this->setUpConfig("
        <config xmlns='http://christian-budde.dk/SiteConfig'>
            <templates>
                <template link='NonExistingFile'>main</template>
            </templates>
        </config>");

        $exceptionWasThrown = false;
        try {
            $this->template->setTemplateFromConfig('main');
        } catch (Exception $exception) {
            $this->assertInstanceOf('FileNotFoundException', $exception, 'Got the wrong exception');
            /** @var $exception FileNotFoundException */
            $exceptionWasThrown = true;
            $this->assertEquals($this->rootPath . 'NonExistingFile', $exception->getFileName(), 'Did not expect the right file');

        }

        $this->assertTrue($exceptionWasThrown, 'Exception was not thrown');

    }

    public function testWillThrowExceptionIfTemplateNotInConfig()
    {
        $this->setUpConfig();
        $exceptionWasThrown = false;
        try {
            $this->template->setTemplateFromConfig('main');
        } catch (Exception $exception) {
            $this->assertInstanceOf('EntryNotFoundException', $exception, 'Got the wrong exception');
            /** @var $exception EntryNotFoundException */
            $exceptionWasThrown = true;
            $this->assertEquals('main', $exception->getEntry(), 'Could not find the right wrong entry');

        }

        $this->assertTrue($exceptionWasThrown, 'Exception was not thrown');
    }

    public function testGetModifiedTemplateWillReturnTemplateWithNoModificationsIfNonModifiable()
    {
        $this->setUpConfig();
        $oldTemplate = 'Hello';
        $this->template->setTemplateFromString($oldTemplate);
        $newTemplate = $this->template->getModifiedTemplate();
        $this->assertEquals($oldTemplate, $newTemplate, 'Did not return the right template');
    }

    public function testGetModifiedTemplateWillReturnTemplateWithNoModificationsIfNonModifiableFromFile()
    {
        $this->setUpConfig();
        $file = dirname(__FILE__) . '/_stub/templateStub';
        $f = new FileImpl($file);
        $oldTemplate = $f->getContents();
        $this->template->setTemplate($f);
        $newTemplate = $this->template->getModifiedTemplate();
        $this->assertEquals($oldTemplate, $newTemplate, 'Did not return the right template');
    }

    public function testGetModifiedTemplateWillReturnTemplateWithNoModificationsIfNonModifiableFromConfig()
    {
        $this->setUpConfig("
        <config xmlns='http://christian-budde.dk/SiteConfig'>
            <templates>
                <template link='_stub/templateStub'>main</template>
            </templates>
        </config>");


        $file = $this->rootPath . '_stub/templateStub';
        $oldTemplate = file_get_contents($file);
        $this->template->setTemplateFromConfig('main');
        $newTemplate = $this->template->getModifiedTemplate();
        $this->assertEquals($oldTemplate, $newTemplate, 'Did not return the right template');
    }

    public function testGetModifiedTemplateReturnsTemplateWithChangesIfModifiable()
    {
        $this->setUpConfig('
        <config xmlns="http://christian-budde.dk/SiteConfig">
            <pageElements>
                <class name="main" link="_stub/HelloPageElementImpl.php">HelloPageElementImpl</class>
                <class name="main2" link="_stub/NullPageElementImpl.php">NullPageElementImpl</class>
            </pageElements>
        </config>');

        $this->template->setTemplateFromString("prepend <!-- pageElement:main --> something <!-- pageElement:main2 --> something else ");
        $modTemplate = $this->template->getModifiedTemplate();
        $helloElement = new HelloPageElementImpl();
        $nullElement = new NullPageElementImpl();
        $expectedString = 'prepend ' . $helloElement->getContent() . ' something ' . $nullElement->getContent() . ' something else ';
        $this->assertEquals($expectedString, $modTemplate, 'Did not return expected template.');
    }

    public function testGetModifiedTemplateReturnsTemplateWithEntryNotFoundStillThere()
    {
        $this->setUpConfig('
        <config xmlns="http://christian-budde.dk/SiteConfig">
            <pageElements>
                <class name="main" link="_stub/HelloPageElementImpl.php">HelloPageElementImpl</class>
            </pageElements>
        </config>');

        $this->template->setTemplateFromString("prepend <!-- pageElement:main --> something <!-- pageElement:main2 --> something else ");
        $modTemplate = $this->template->getModifiedTemplate();
        $helloElement = new HelloPageElementImpl();
        $expectedString = 'prepend ' . $helloElement->getContent() . ' something <!-- pageElement:main2 --> something else ';
        $this->assertEquals($expectedString, $modTemplate, 'Did not return expected template.');
    }

    public function testGetModifiedTemplateWithAbsoluteExtension(){
        $this->setUpConfig();
        $templateFile = dirname(__FILE__).'/_stub/templateStub';
        $this->template->setTemplateFromString("<!--extends:$templateFile-->");
        $modTemplate = $this->template->getModifiedTemplate();
        $f = new FileImpl(dirname(__FILE__).'/_stub/templateStub');
        $this->assertEquals($f->getContents(), $modTemplate, 'Did not return expected template.');

    }

    public function testGetModifiedTemplateWithExtraElementsInExtension(){
        $this->setUpConfig('
        <config xmlns="http://christian-budde.dk/SiteConfig">
            <pageElements>
                <class name="main" link="_stub/HelloPageElementImpl.php">HelloPageElementImpl</class>
            </pageElements>
        </config>');
        $templateFile = dirname(__FILE__).'/_stub/templateStub2';
        $this->template->setTemplateFromString("
        <!--extends:$templateFile-->
        <!--replaceElement[someElement]:main-->");
        $modTemplate = $this->template->getModifiedTemplate();
        $this->assertEquals("Hello World",$modTemplate,'Did not return expected template');
    }

    public function testGetModifiedTemplateWithExtendWillRemoveExtraContent(){
        $this->setUpConfig();
        $templateFile = dirname(__FILE__).'/_stub/templateStub';
        $this->template->setTemplateFromString("<!--extends:$templateFile--> something something");
        $modTemplate = $this->template->getModifiedTemplate();
        $f = new FileImpl(dirname(__FILE__).'/_stub/templateStub');
        $this->assertEquals($f->getContents(), $modTemplate, 'Did not return expected template.');
    }

    public function testGetModifiedTemplateWithExtendAndExtendedReplace(){
        $this->setUpConfig('
        <config xmlns="http://christian-budde.dk/SiteConfig">
            <pageElements>
                <class name="main" link="_stub/HelloPageElementImpl.php">HelloPageElementImpl</class>
            </pageElements>
        </config>');
        $templateFile = dirname(__FILE__).'/_stub/templateStub2';
        $this->template->setTemplateFromString("
        <!--extends:$templateFile-->
        <!--replaceElementStart[someElement]-->Hello DEAD Fellow<!--pageElement:main--><!--replaceElementEnd-->");
        $modTemplate = $this->template->getModifiedTemplate();
        $this->assertEquals("Hello DEAD FellowHello World",$modTemplate,'Did not return expected template');
    }
}
