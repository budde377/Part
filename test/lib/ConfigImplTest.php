<?php
/**
 * User: budde
 * Date: 5/15/12
 * Time: 7:07 PM
 */
namespace ChristianBudde\Part;

use ChristianBudde\Part\exception\InvalidXMLException;
use PHPUnit_Framework_TestCase;
use SimpleXMLElement;

class ConfigImplTest extends PHPUnit_Framework_TestCase
{

    private $defaultOwner = /** @lang XML */
        "<siteInfo><domain name='test' extension='dk'/><owner name='Admin Jensen' mail='test@test.dk' username='asd' /></siteInfo>";
    /** @var  ConfigImpl */
    private $config;


    protected function setUp()
    {
        parent::setUp();
        $this->config = $this->setupConfig("<config>{$this->defaultOwner}</config>");

    }


    private function setupConfig($configXml){
        $rootPath = dirname(__FILE__) . '/';
        return new ConfigImpl(simplexml_load_string($configXml), $rootPath);
    }

    public function testSimpleXMLInputMustBeValidElseException()
    {
        $invalidConfigXML = simplexml_load_string("
        <notValidRoot>
        </notValidRoot>");
        $rootPath = dirname(__FILE__) . '/';
        $exceptionWasThrown = false;
        try {
            new ConfigImpl($invalidConfigXML, $rootPath);
        } catch (InvalidXMLException $exception) {
            $exceptionWasThrown = true;
            $this->assertEquals("site-config", $exception->getExpectedSchema(), "Did expect the wrong Schema");
            $this->assertEquals("ConfigXML", $exception->getXmlDesc(), "Did validate the wrong XML");
        }

        $this->assertTrue($exceptionWasThrown, "Did not throw expected InvalidXMLException");
    }


    public function testRootPathWillReturnRootPath()
    {
        $path = dirname(__FILE__);
        $config = new ConfigImpl(simplexml_load_string("<config>{$this->defaultOwner}</config>"), $path);
        $this->assertEquals($path, $config->getRootPath());
    }

    public function testGetTemplateReturnNullWithEmptyConfigXML()
    {
        $emptyConfigXML = simplexml_load_string("<config>{$this->defaultOwner}</config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($emptyConfigXML, $rootPath);
        $template = $config->getTemplate('main');
        $this->assertNull($template, 'The template was not null with empty config XML');
    }

    public function testGetTemplateReturnNullWithTemplateNIL()
    {
        $config = $this->setupConfig(/** @lang XML */
            "
        <config>{$this->defaultOwner}
        <templates path='folder'>
            <template filename='some_link'>main</template>
        </templates>
        </config>");
        $template = $config->getTemplate('nil');
        $this->assertNull($template, 'The template was not null with template NIL');
    }

    public function testGetTemplateFolderPathReturnsRightPath()
    {

        $config = $this->setupConfig(/** @lang XML */
            "
        <config>{$this->defaultOwner}
        <templates path='folder'>
            <template filename='some_link'>main</template>
        </templates>
        </config>");
        $this->assertEquals($config->getTemplateFolderPath('main'), "{$config->getRootPath()}/folder");
    }

    public function testGetTemplateFolderPathReturnsNullIfNotDefined()
    {
        $this->assertNull($this->config->getTemplateFolderPath('not defined'));
    }

    public function testGetTemplateReturnLinkWithTemplateInList()
    {
        $config = $this->setupConfig(/** @lang XML */
            "
        <config>{$this->defaultOwner}
        <templates path='folder'>
            <template filename='some_link'>main</template>
        </templates>
        </config>");
        $template = $config->getTemplate('main');
        $this->assertEquals('some_link', $template, 'The config did not return the right link.');
    }

    public function testGetPageElementReturnNullWithEmptyConfigXML()
    {

        $template = $this->config->getPageElement('main');
        $this->assertNull($template, 'The getPageElement was not null with empty config XML');
    }


    public function testGetPageElementReturnNullWithTemplateElementNIL()
    {
        $config = $this->setupConfig("
        <config>{$this->defaultOwner}
        <pageElements>
            <class name='someName' link='someLink'>SomeClassName</class>
        </pageElements>
        </config>");
        $template = $config->getPageElement('nil');
        $this->assertNull($template, 'The getPageElement was not null with pageElement NIL');
    }

    public function testGetPageElementReturnArrayWithElementInList()
    {
        $config = $this->setupConfig("
        <config>{$this->defaultOwner}
        <pageElements>
            <class name='someName' link='someLink'>SomeClassName</class>
        </pageElements>
        </config>");
        $element = $config->getPageElement('someName');
        $this->assertEquals(['name'=>'someName', 'className'=>'SomeClassName', 'link'=>$config->getRootPath().'someLink'], $element);

    }

    public function testGetPageElementReturnArrayWithElementInListButNoLink()
    {
        $config = $this->setupConfig("
        <config>{$this->defaultOwner}
        <pageElements>
            <class name='someName'>SomeClassName</class>
        </pageElements>
        </config>");
        $element = $config->getPageElement('someName');
        $this->assertEquals(['name'=>'someName', 'className'=>'SomeClassName'], $element);


    }



    public function testGetPreTaskReturnEmptyArrayWithEmptyConfig()
    {

        $preTask = $this->config->getPreTasks();
        $this->assertTrue(is_array($preTask), 'getPreTasks did not return an array with empty config.');
        $this->assertTrue(empty($preTask), 'getPreTasks did not return an empty array with empty config.');
    }


    public function testGetPreTaskHasEntrySpecifiedInConfigWithLinkAsVal()
    {
        $config = $this->setupConfig("
        <config>{$this->defaultOwner}
        <preTasks>
        <class link='some_link'>main</class>
        </preTasks>
        </config>");
        $preTask = $config->getPreTasks();
        $this->assertArrayHasKey('main', $preTask, 'getPreTasks did not return array with right entrance');
        $this->assertEquals($config->getRootPath() . 'some_link', $preTask['main'], 'getPreTasks did not return array with right link');

    }

    public function testGetPreTaskHasEntrySpecifiedInConfigWithLinkAsValButNoLink()
    {
        $config = $this->setupConfig("
        <config>{$this->defaultOwner}
        <preTasks>
        <class >main</class>
        </preTasks>
        </config>");
        $preTask = $config->getPreTasks();
        $this->assertArrayHasKey('main', $preTask, 'getPreTasks did not return array with right entrance');
        $this->assertNull($preTask['main'], 'getPreTasks did not return array with right link');

    }

    public function testGetPostTaskReturnEmptyArrayWithEmptyConfig()
    {
        $emptyConfigXML = simplexml_load_string("<config>{$this->defaultOwner}</config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($emptyConfigXML, $rootPath);
        $preTask = $config->getPostTasks();
        $this->assertTrue(is_array($preTask), 'getPostTasks did not return an array with empty config.');
        $this->assertTrue(empty($preTask), 'getPostTasks did not return an empty array with empty config.');
    }

    public function testGetPostTaskHasEntrySpecifiedInConfig()
    {
        $config = $this->setupConfig("
        <config>{$this->defaultOwner}
        <postTasks>
        <class link=''>main</class>
        <class link=''>main2</class>
        </postTasks>
        </config>");
        $preTask = $config->getPostTasks();
        $this->assertArrayHasKey('main', $preTask, 'getPostTasks did not return array with right entrance');
        $this->assertArrayHasKey('main2', $preTask, 'getPostTasks did not return array with right entrance');
    }

    public function testGetPostTaskHasEntrySpecifiedInConfigWithLinkAsValAndRootPrepended()
    {
        $config = $this->setupConfig("
        <config>{$this->defaultOwner}
        <postTasks>
        <class link='some_link'>main</class>
        </postTasks>
        </config>");
        $preTask = $config->getPostTasks();
        $this->assertEquals($config->getRootPath() . 'some_link', $preTask['main'], 'getPostTasks did not return array with right link');

    }

    public function testGetPostTaskHasEntrySpecifiedInConfigWithLinkAsValAndRootPrependedButNoLink()
    {
        $config = $this->setupConfig("
        <config>{$this->defaultOwner}
        <postTasks>
        <class >main</class>
        </postTasks>
        </config>");
        $preTask = $config->getPostTasks();
        $this->assertNull($preTask['main'], 'getPostTasks did not return array with right link');

    }

    public function testOrderOfPostTaskIsTheSameInFileAsOutput()
    {
        $config = $this->setupConfig("
        <config>{$this->defaultOwner}
        <postTasks>
        <class link='some_link2'>main2</class>
        <class link='some_link'>main</class>
        </postTasks>
        </config>");
        $postTasks = $config->getPostTasks();
        $postTasksCopy = $postTasks;
        ksort($postTasksCopy);
        $this->assertNotEquals(array_pop($postTasks), array_pop($postTasksCopy), 'The order was not as defined in file');

    }

    public function testOrderOfPreTaskIsTheSameInFileAsOutput()
    {
        $config = $this->setupConfig("
        <config>{$this->defaultOwner}
        <preTasks>
        <class link='some_link2'>main2</class>
        <class link='some_link'>main</class>
        </preTasks>
        </config>");
        $preTasks = $config->getPreTasks();
        $preTasksCopy = $preTasks;
        ksort($preTasksCopy);
        $this->assertNotEquals(array_pop($preTasks), array_pop($preTasksCopy), 'The order was not as defined in file');

    }

    public function testGetVariablesReturnsEmptyArrayOnNotPresent()
    {
        $config = $this->setupConfig("
        <config>{$this->defaultOwner}
        </config>");
        $this->assertEquals([], $config->getVariables());
    }

    public function testGetVariablesReflectsTheVariables()
    {
        $config = $this->setupConfig(/** @lang XML */
            "
        <config>{$this->defaultOwner}
            <variables>
                <var key='KEY1' value='VALUE1' />
                <var key='KEY2' value='VALUE2' />
            </variables>
        </config>");
        $this->assertEquals(['KEY1' => 'VALUE1', 'KEY2' => 'VALUE2'], $config->getVariables());
    }

    public function testArrayAccessIsRight()
    {
        $config = $this->setupConfig(/** @lang XML */
            "
        <config>{$this->defaultOwner}
            <variables>
                <var key='KEY1' value='VALUE1' />
                <var key='KEY2' value='VALUE2' />
            </variables>
        </config>");
        $this->assertEquals('VALUE2', $config['KEY2']);
    }

    public function testArrayAccessSetterDoesNotSet()
    {
        $config = $this->setupConfig(/** @lang XML */
            "
        <config>{$this->defaultOwner}
            <variables>
                <var key='KEY1' value='VALUE1' />
                <var key='KEY2' value='VALUE2' />
            </variables>
        </config>");
        $config['KEY2'] = 'VALUE3';
        $this->assertEquals('VALUE2', $config['KEY2']);
    }





    public function testGetAJAXTypeHandlersReturnEmptyArrayWithEmptyConfig()
    {
        $emptyConfigXML = simplexml_load_string("<config>{$this->defaultOwner}</config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($emptyConfigXML, $rootPath);
        $registrable = $config->getAJAXTypeHandlers();
        $this->assertTrue(is_array($registrable), 'getAJAXTypeHandlers did not return an array with empty config.');
        $this->assertTrue(empty($registrable), 'getAJAXTypeHandlers did not return an empty array with empty config.');
    }

    public function testGetAJAXTypeHandlersHasEntrySpecifiedInConfig()
    {
        $path1 = "path1";
        $config = $this->setupConfig("
        <config>{$this->defaultOwner}
        <AJAXTypeHandlers>
        <class link='$path1'>main</class>
        <class >main2</class>
        </AJAXTypeHandlers>
        </config>");

        $registrable = $config->getAJAXTypeHandlers();
        $this->assertEquals([
            ['class_name'=>'main', 'link'=>$config->getRootPath().'/'.$path1],
            ['class_name'=>'main2']
            ], $registrable);

    }


    public function testOrderOfAJAXTypeHandlersIsTheSameInFileAsOutput()
    {
        $config = $this->setupConfig("
        <config>{$this->defaultOwner}
        <AJAXTypeHandlers>
        <class link='some_link2'>main2</class>
        <class link='some_link'>main</class>
        </AJAXTypeHandlers>
        </config>");
        $registrable = $config->getAJAXTypeHandlers();
        $this->assertEquals('main2', $registrable[0]['class_name']);
    }


    public function testGetDefaultPagesWillReturnArrayOnEmptyConfig()
    {
        $config = $this->setupConfig("
        <config>{$this->defaultOwner}
        </config>");
        $pages = $config->getDefaultPages();
        $this->assertTrue(is_array($pages), "Did not return array");
        $this->assertEquals(0, count($pages), "Did not return empty array");
    }

    public function testGetDefaultPagesWillReturnArraySimilarToConfig()
    {
        $config = $this->setupConfig("
        <config>{$this->defaultOwner}
        <defaultPages>
            <page alias='' id='t1' template='someTemplate'>someTitle</page>
            <page alias='/alias/' id='t2' template='someTemplate2' >someTitle2</page>
        </defaultPages>
        </config>");
        $pages = $config->getDefaultPages();
        $this->assertEquals([
            'someTitle'=>['template'=>'someTemplate', 'alias'=>'', 'id'=>'t1'],
            'someTitle2'=>['template'=>'someTemplate2', 'alias'=>'/alias/', 'id'=>'t2']
        ], $pages);


    }

    public function testListTemplateNamesWillReturnEmptyArrayOnEmptyConfig()
    {
        $config = $this->setupConfig("
        <config>{$this->defaultOwner}
        </config>");
        $templates = $config->listTemplateNames();
        $this->assertTrue(is_array($templates), "Did not return array");
        $this->assertEquals(0, count($templates), "Did not return empty array");
    }

    public function testListTemplateNamesWillReturnArraySimilarToConfig()
    {
        $config = $this->setupConfig(/** @lang XML */
            "
        <config>{$this->defaultOwner}
        <templates path='folder'>
            <template filename='some_link'>main</template>
            <template filename='some_link2'>main2</template>
        </templates>
        </config>");
        $templates = $config->listTemplateNames();
        $this->assertEquals(['main', 'main2'], $templates);
    }

    public function testGetDefaultTemplateWillReturnNullIfNotDefined()
    {
        $config = $this->setupConfig(/** @lang XML */
            "
        <config>{$this->defaultOwner}
        <templates path='folder'>
            <template filename='some_link'>main</template>
            <template filename='some_link2'>main2</template>
        </templates>
        </config>");
        $this->assertNull($config->getDefaultTemplate());
    }

    public function testGetDefaultTemplateNameWillReturnNullIfNotDefined()
    {
        $config = $this->setupConfig(/** @lang XML */
            "
        <config>{$this->defaultOwner}
        <templates path='folder'>
            <template filename='some_link'>main</template>
            <template filename='some_link2'>main2</template>
        </templates>
        </config>");
        $this->assertNull($config->getDefaultTemplateName());
    }

    public function testGetDefaultTemplateWillReturnCorrectValueIfDefined()
    {
        $config = $this->setupConfig(/** @lang XML */
            "
        <config>{$this->defaultOwner}
        <templates path='folder'>
            <template filename='some_link'>main</template>
            <template filename='some_link2' default='true'>main2</template>
        </templates>
        </config>");
        $this->assertEquals('some_link2',$config->getDefaultTemplate());
    }

    public function testGetDefaultTemplateNameWillReturnCorrectValueIfDefined()
    {
        $config = $this->setupConfig(/** @lang XML */
            "
        <config>{$this->defaultOwner}
        <templates path='folder'>
            <template filename='some_link'>main</template>
            <template filename='some_link2' default='true'>main2</template>
        </templates>
        </config>");
        $this->assertEquals('main2',$config->getDefaultTemplateName());
    }


    public function testUsingTemplateCollectionIsCool()
    {
        $config = $this->setupConfig(/** @lang XML */
            "
        <config>{$this->defaultOwner}
        <templateCollection>
        <templates path='folder'>
            <template filename='some_link'>main</template>
        </templates>
        <templates path='folder2'>
            <template filename='some_link2'>main2</template>
        </templates>

        </templateCollection>
        </config>");
        $templates = $config->listTemplateNames();
        $this->assertEquals(['main', 'main2'], $templates);
    }

    public function testUsingEmptyTemplatesIsAlsoCool()
    {
        $config = $this->setupConfig(/** @lang XML */
            "
        <config>{$this->defaultOwner}
        <templates path='folder' />
        </config>");
        $templates = $config->listTemplateFolders();
        $this->assertEquals([$config->getRootPath().'/folder'], $templates);
    }

    public function testTemplateFoldersWillReturnTemplateFolders()
    {
        $config = $this->setupConfig(/** @lang XML */
            "
        <config>{$this->defaultOwner}
        <templateCollection>
            <templates path='somePath' />
            <templates path='somePath2' namespace='someNS'/>
        </templateCollection>
        </config>");
        $this->assertEquals([$config->getRootPath().'/somePath', ['path'=>$config->getRootPath().'/somePath2', 'namespace'=>'someNS']], $config->listTemplateFolders());
        $this->assertTrue((string) $config->listTemplateFolders()[1]['namespace'] === $config->listTemplateFolders()[1]['namespace']);
        $this->assertTrue((string) $config->listTemplateFolders()[1]['path'] === $config->listTemplateFolders()[1]['path']);
        $this->assertTrue((string) $config->listTemplateFolders()[0] === $config->listTemplateFolders()[0]);

    }


    public function testGetMySQLConnectionWillReturnArrayWithInfoAsInConfigXML()
    {
        /** @var $configXML SimpleXMLElement */
        $config =  $this->setupConfig("
        <config>{$this->defaultOwner}
            <MySQLConnection>
                <host>someHost</host>
                <database>someDatabase</database>
                <username>someUser</username>
                <password>somePassword</password>
            </MySQLConnection>
        </config>");
        $connArray = $config->getMySQLConnection();
        $this->assertEquals([
            'user' => 'someUser',
            'host' => 'someHost',
            'password' => 'somePassword',
            'database' => 'someDatabase',
            'folders' => []], $connArray);
    }

    public function testGetMySQLConnectionWillReturnArrayWithInfoAsInConfigXMLEvenWhenEmptyPassword()
    {
        /** @var $configXML SimpleXMLElement */
        $config =  $this->setupConfig(/** @lang XML */
            "
        <config>{$this->defaultOwner}
            <MySQLConnection>
                <host>someHost</host>
                <database>someDatabase</database>
                <username>someUser</username>
                <password />
            </MySQLConnection>
        </config>");
        $connArray = $config->getMySQLConnection();
        $this->assertEquals([
            'user' => 'someUser',
            'host' => 'someHost',
            'password' => '',
            'database' => 'someDatabase',
            'folders' => []], $connArray);
    }

    public function testGetMySQLConnectionWillAddFolderArrays()
    {
        /** @var $configXML SimpleXMLElement */
        $config =  $this->setupConfig(/** @lang XML */
            "
        <config>{$this->defaultOwner}
            <MySQLConnection>
                <host>someHost</host>
                <database>someDatabase</database>
                <username>someUser</username>
                <password>somePassword</password>
                <folders>
                    <folder name='name' path='path' />
                    <folder name='name2' path='path2' />
                </folders>
            </MySQLConnection>
        </config>");
        $connArray = $config->getMySQLConnection();
        $this->assertEquals([
            'user' => 'someUser',
            'host' => 'someHost',
            'password' => 'somePassword',
            'database' => 'someDatabase',
            'folders' => [
                'name' => 'path',
                'name2' => 'path2']], $connArray);

    }




    public function testWillReturnNullIfNotSpecifiedInConfig()
    {
        /** @var $configXML SimpleXMLElement */
        $connArray = $this->config->getMySQLConnection();
        $this->assertNull($connArray, 'Was not null.');
    }

    public function testIsDebugModeWillReturnFalseOnEmpty()
    {
        /** @var $configXML SimpleXMLElement */
        $config = $this->setupConfig("<config>{$this->defaultOwner}</config>");
        $this->assertFalse($config->isDebugMode());

    }

    public function testIsDebugModeWillReturnFalseOnFalse()
    {
        /** @var $configXML SimpleXMLElement */
        $config =  $this->setupConfig("<config>{$this->defaultOwner}
        <debugMode>false</debugMode>
        </config>");
        $this->assertFalse($config->isDebugMode());

    }

    public function testIsDebugModeWillReturnTrueOnTrue()
    {
        /** @var $configXML SimpleXMLElement */
        $config =  $this->setupConfig("<config>{$this->defaultOwner}
        <debugMode>true</debugMode>
        </config>");
        $this->assertTrue($config->isDebugMode());

    }

    public function testIsUpdaterEnabledWillReturnTrueOnEmpty()
    {
        /** @var $configXML SimpleXMLElement */

        $this->assertTrue($this->config->isUpdaterEnabled());

    }

    public function testIsUpdaterEnabledWillReturnFalseOnFalse()
    {
        /** @var $configXML SimpleXMLElement */
        $config =  $this->setupConfig("<config>{$this->defaultOwner}
        <enableUpdater>false</enableUpdater>
        </config>");
        $this->assertFalse($config->isUpdaterEnabled());

    }

    public function testIsUpdaterEnabledWillReturnTrueOnTrue()
    {
        /** @var $configXML SimpleXMLElement */
        $config =  $this->setupConfig("<config>{$this->defaultOwner}
        <enableUpdater>true</enableUpdater>
        </config>");
        $this->assertTrue($config->isUpdaterEnabled());

    }

    public function testGetTmpPathReturnsRightPath()
    {
        /** @var $configXML SimpleXMLElement */
        $config =  $this->setupConfig(/** @lang XML */
            "<config>{$this->defaultOwner}
                <tmpFolder path='/some/path' />
        </config>");
        $this->assertEquals("/some/path", $config->getTmpFolderPath());
    }

    public function testGetTmpPathReturnsReturnsEmptyWhenNotDefined()
    {
        /** @var $configXML SimpleXMLElement */
        $config =  $this->setupConfig("<config>{$this->defaultOwner}
        </config>");
        $this->assertEquals("", $config->getTmpFolderPath());
    }

    public function testGetLogPathReturnsReturnsEmptyWhenNotDefined()
    {
        /** @var $configXML SimpleXMLElement */
        $config =  $this->setupConfig("<config>{$this->defaultOwner}
        </config>");
        $this->assertEquals("", $config->getLogPath());
    }

    public function testGetErrorLogReturnsRightPath()
    {
        /** @var $configXML SimpleXMLElement */
        $config =  $this->setupConfig(/** @lang XML */
            "<config>{$this->defaultOwner}
                <log path='/some/path' />
        </config>");
        $this->assertEquals("/some/path", $config->getLogPath());
    }



    public function testGetErrorLogReturnsReturnsEmptyWhenNotDefined()
    {
        /** @var $configXML SimpleXMLElement */
        $config =  $this->setupConfig("<config>{$this->defaultOwner}
        </config>");
        $this->assertEquals("", $config->getTmpFolderPath());
    }


    public function testGetDomainWillReturnDomainOnExist()
    {
        $config =  $this->setupConfig(/** @lang XML */
            "<config>
        <siteInfo>
            <domain name='test' extension='com' />
            <owner name='Test Testesen' mail='test@test.dk' username='test' />
        </siteInfo>
        </config>");
        $this->assertEquals($config->getDomain(), "test.com");

    }

    public function testGetOwnerWillReturnArrayOfRightFormat()
    {
        $config =  $this->setupConfig(/** @lang XML */
            "<config>
        <siteInfo>
            <domain name='test' extension='com' />
            <owner name='test' mail='test@test.dk' username='test' />
        </siteInfo>
        </config>");
        $array = $config->getOwner();
        $this->assertEquals([
            'name'=>'test',
            'mail'=>'test@test.dk',
            'username'=>'test'
        ], $array);

        $this->assertEquals($config->getDomain(), "test.com");

    }


}
