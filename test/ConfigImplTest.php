<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/15/12
 * Time: 7:07 PM
 * To change this template use File | Settings | File Templates.
 */
class ConfigImplTest extends PHPUnit_Framework_TestCase
{

    private $defaultOwner = "<siteInfo><domain name='test' extension='dk'/><owner name='Admin Jensen' mail='test@test.dk' username='asd' /></siteInfo>";

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


    public function testRootPathWillReturnRootPath(){
        $p = dirname(__FILE__);
        $config = new ConfigImpl(simplexml_load_string("<config>{$this->defaultOwner}</config>"), $p);
        $this->assertEquals($p, $config->getRootPath());
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
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <templates path='folder'>
            <template filename='some_link'>main</template>
        </templates>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $template = $config->getTemplate('nil');
        $this->assertNull($template, 'The template was not null with template NIL');
    }

    public function testGetTemplateFolderPathReturnsRightPath()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <templates path='folder'>
            <template filename='some_link'>main</template>
        </templates>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $this->assertEquals($config->getTemplateFolderPath(), "$rootPath/folder");
    }

    public function testGetTemplateFolderPathReturnsNullIfNotDefined()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}</config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $this->assertNull($config->getTemplateFolderPath());
    }

    public function testGetTemplateReturnLinkWithTemplateInList()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <templates path='folder'>
            <template filename='some_link'>main</template>
        </templates>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $template = $config->getTemplate('main');
        $this->assertEquals('some_link', $template, 'The config did not return the right link.');
    }

    public function testGetPageElementReturnNullWithEmptyConfigXML()
    {
        $emptyConfigXML = simplexml_load_string("<config>{$this->defaultOwner}</config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($emptyConfigXML, $rootPath);
        $template = $config->getPageElement('main');
        $this->assertNull($template, 'The getPageElement was not null with empty config XML');
    }

    public function testGetPageElementReturnNullWithTemplateElementNIL()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <pageElements>
            <class name='someName' link='someLink'>SomeClassName</class>
        </pageElements>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $template = $config->getPageElement('nil');
        $this->assertNull($template, 'The getPageElement was not null with pageElement NIL');
    }

    public function testGetPageElementReturnArrayWithElementInList()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <pageElements>
            <class name='someName' link='someLink'>SomeClassName</class>
        </pageElements>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $element = $config->getPageElement('someName');
        $this->assertTrue(is_array($element), 'The getPageElement did not return array with element in list');
        $this->assertArrayHasKey('className', $element, 'The array did not contain key className');
        $this->assertArrayHasKey('name', $element, 'The array did not contain key name');
        $this->assertArrayHasKey('link', $element, 'The array did not contain key link');
        $this->assertEquals('SomeClassName', $element['className'], 'The element[className] was not as expected');
        $this->assertEquals('someName', $element['name'], 'The element[name] was not as expected');
        $this->assertEquals($rootPath . 'someLink', $element['link'], 'The element[link] was not as expected');
    }

    public function testGetPageElementReturnArrayWithElementInListButNoLink()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <pageElements>
            <class name='someName'>SomeClassName</class>
        </pageElements>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $element = $config->getPageElement('someName');
        $this->assertTrue(is_array($element), 'The getPageElement did not return array with element in list');
        $this->assertArrayHasKey('className', $element, 'The array did not contain key className');
        $this->assertArrayHasKey('name', $element, 'The array did not contain key name');
        $this->assertArrayNotHasKey('link', $element);
        $this->assertEquals('SomeClassName', $element['className'], 'The element[className] was not as expected');
        $this->assertEquals('someName', $element['name'], 'The element[name] was not as expected');

    }


    public function testGetOptimizerReturnNullWithEmptyConfigXML()
    {
        /** @var $emptyConfigXML SimpleXMLElement */
        $emptyConfigXML = simplexml_load_string("<config>{$this->defaultOwner}</config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($emptyConfigXML, $rootPath);
        $template = $config->getOptimizer('main');
        $this->assertNull($template, 'The getOptimizer was not null with empty config XML');
    }
    //TODO Fix config tests
    public function testGetOptimizerReturnNullWithTemplateElementNIL()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
            <optimizers>
                <class name='someName' link='someLink'>SomeClass</class>
            </optimizers>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $template = $config->getOptimizer('nil');
        $this->assertNull($template, 'The getOptimizer was not null with optimizer NIL');
    }

    public function testGetOptimizerReturnArrayWithOptimizerInList()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <optimizers>
        <class name='someName' link='someLink'>SomeClassName</class>
        </optimizers>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $element = $config->getOptimizer('someName');
        $this->assertTrue(is_array($element), 'The getOptimizer did not return array with element in list');
        $this->assertArrayHasKey('className', $element, 'The array did not contain key className');
        $this->assertArrayHasKey('name', $element, 'The array did not contain key name');
        $this->assertArrayHasKey('link', $element, 'The array did not contain key link');
        $this->assertEquals('SomeClassName', $element['className'], 'The element[className] was not as expected');
        $this->assertEquals('someName', $element['name'], 'The element[name] was not as expected');
        $this->assertEquals($rootPath . 'someLink', $element['link'], 'The element[link] was not as expected');
    }

    public function testGetOptimizerReturnArrayWithOptimizerInListButNoLink()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <optimizers>
        <class name='someName'>SomeClassName</class>
        </optimizers>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $element = $config->getOptimizer('someName');
        $this->assertTrue(is_array($element), 'The getOptimizer did not return array with element in list');
        $this->assertArrayHasKey('className', $element, 'The array did not contain key className');
        $this->assertArrayHasKey('name', $element, 'The array did not contain key name');
        $this->assertArrayNotHasKey('link', $element, 'The array did contain key link');
        $this->assertEquals('SomeClassName', $element['className'], 'The element[className] was not as expected');
        $this->assertEquals('someName', $element['name'], 'The element[name] was not as expected');
    }

    public function testGetPreScriptReturnEmptyArrayWithEmptyConfig()
    {
        $emptyConfigXML = simplexml_load_string("<config>{$this->defaultOwner}</config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($emptyConfigXML, $rootPath);
        $preScript = $config->getPreScripts();
        $this->assertTrue(is_array($preScript), 'getPreScripts did not return an array with empty config.');
        $this->assertTrue(empty($preScript), 'getPreScripts did not return an empty array with empty config.');
    }


    public function testGetPreScriptHasEntrySpecifiedInConfigWithLinkAsVal()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <preScripts>
        <class link='some_link'>main</class>
        </preScripts>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $preScript = $config->getPreScripts();
        $this->assertArrayHasKey('main', $preScript, 'getPreScripts did not return array with right entrance');
        $this->assertEquals($rootPath . 'some_link', $preScript['main'], 'getPreScripts did not return array with right link');

    }

    public function testGetPreScriptHasEntrySpecifiedInConfigWithLinkAsValButNoLink()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <preScripts>
        <class >main</class>
        </preScripts>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $preScript = $config->getPreScripts();
        $this->assertArrayHasKey('main', $preScript, 'getPreScripts did not return array with right entrance');
        $this->assertNull($preScript['main'], 'getPreScripts did not return array with right link');

    }

    public function testGetPostScriptReturnEmptyArrayWithEmptyConfig()
    {
        $emptyConfigXML = simplexml_load_string("<config>{$this->defaultOwner}</config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($emptyConfigXML, $rootPath);
        $preScript = $config->getPostScripts();
        $this->assertTrue(is_array($preScript), 'getPostScripts did not return an array with empty config.');
        $this->assertTrue(empty($preScript), 'getPostScripts did not return an empty array with empty config.');
    }

    public function testGetPostScriptHasEntrySpecifiedInConfig()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <postScripts>
        <class link=''>main</class>
        <class link=''>main2</class>
        </postScripts>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $preScript = $config->getPostScripts();
        $this->assertArrayHasKey('main', $preScript, 'getPostScripts did not return array with right entrance');
        $this->assertArrayHasKey('main2', $preScript, 'getPostScripts did not return array with right entrance');
    }

    public function testGetPostScriptHasEntrySpecifiedInConfigWithLinkAsValAndRootPrepended()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <postScripts>
        <class link='some_link'>main</class>
        </postScripts>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $preScript = $config->getPostScripts();
        $this->assertEquals($rootPath . 'some_link', $preScript['main'], 'getPostScripts did not return array with right link');

    }
    public function testGetPostScriptHasEntrySpecifiedInConfigWithLinkAsValAndRootPrependedButNoLink()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <postScripts>
        <class >main</class>
        </postScripts>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $preScript = $config->getPostScripts();
        $this->assertNull( $preScript['main'], 'getPostScripts did not return array with right link');

    }

    public function testOrderOfPostScriptIsTheSameInFileAsOutput()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <postScripts>
        <class link='some_link2'>main2</class>
        <class link='some_link'>main</class>
        </postScripts>
        </config>");

        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $postScripts = $config->getPostScripts();
        $postScriptsCopy = $postScripts;
        ksort($postScriptsCopy);
        $this->assertNotEquals(array_pop($postScripts), array_pop($postScriptsCopy), 'The order was not as defined in file');

    }

    public function testOrderOfPreScriptIsTheSameInFileAsOutput()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <preScripts>
        <class link='some_link2'>main2</class>
        <class link='some_link'>main</class>
        </preScripts>
        </config>");

        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $preScripts = $config->getPreScripts();
        $preScriptsCopy = $preScripts;
        ksort($preScriptsCopy);
        $this->assertNotEquals(array_pop($preScripts), array_pop($preScriptsCopy), 'The order was not as defined in file');

    }


    public function testGetAJAXRegistrableReturnEmptyArrayWithEmptyConfig()
    {
        $emptyConfigXML = simplexml_load_string("<config>{$this->defaultOwner}</config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($emptyConfigXML, $rootPath);
        $registrable = $config->getAJAXRegistrable();
        $this->assertTrue(is_array($registrable), 'getAJAXRegistrable did not return an array with empty config.');
        $this->assertTrue(empty($registrable), 'getAJAXRegistrable did not return an empty array with empty config.');
    }

    public function testGetAJAXRegistrableHasEntrySpecifiedInConfig()
    {
        $path1 = "path1";
        $id1 = "id1";
        $id2 = "id2";
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <AJAXRegistrable>
        <class link='$path1' ajax_id='$id1'>main</class>
        <class ajax_id='$id2'>main2</class>
        </AJAXRegistrable>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $registrable = $config->getAJAXRegistrable();
        $this->assertArrayHasKey(0, $registrable, 'getAJAXRegistrable did not return array with right entrance');
        $this->assertArrayHasKey(1, $registrable, 'getAJAXRegistrable did not return array with right entrance');
        $this->assertTrue(is_array($registrable[0]));
        $this->assertTrue(is_array($registrable[1]));
        $this->assertArrayHasKey('link',$registrable[0]);
        $this->assertArrayHasKey('ajax_id',$registrable[0]);
        $this->assertArrayHasKey('class_name',$registrable[0]);
        $this->assertEquals($registrable[0]['link'],$rootPath.$path1);
        $this->assertEquals($registrable[0]['ajax_id'],$id1);
        $this->assertEquals($registrable[0]['class_name'],'main');
        $this->assertArrayNotHasKey('link',$registrable[1]);
        $this->assertArrayHasKey('ajax_id',$registrable[1]);
        $this->assertArrayHasKey('class_name',$registrable[1]);
        $this->assertEquals($registrable[1]['ajax_id'],$id2);
        $this->assertEquals($registrable[1]['class_name'],'main2');
    }



    public function testOrderOfAJAXRegistrableIsTheSameInFileAsOutput()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <AJAXRegistrable>
        <class ajax_id='id2' link='some_link2'>main2</class>
        <class ajax_id='id' link='some_link'>main</class>
        </AJAXRegistrable>
        </config>");

        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $registrable = $config->getAJAXRegistrable();
        $this->assertEquals('main2', $registrable[0]['class_name']);
    }



    public function testGetDefaultPagesWillReturnArrayOnEmptyConfig()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        </config>");

        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $pages = $config->getDefaultPages();
        $this->assertTrue(is_array($pages), "Did not return array");
        $this->assertEquals(0, count($pages), "Did not return empty array");
    }

    public function testGetDefaultPagesWillReturnArraySimilarToConfig()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <defaultPages>
            <page alias='' id='t1' template='someTemplate'>someTitle</page>
            <page alias='/alias/' id='t2' template='someTemplate2' >someTitle2</page>
        </defaultPages>
        </config>");

        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $pages = $config->getDefaultPages();
        $this->assertTrue(is_array($pages), "Did not return array");
        $this->assertEquals(2, count($pages));
        $this->assertArrayHasKey("someTitle", $pages);
        $this->assertArrayHasKey("someTitle2", $pages);

        $this->assertArrayHasKey("template", $pages['someTitle']);
        $this->assertArrayHasKey("template", $pages['someTitle2']);
        $this->assertArrayHasKey("alias", $pages['someTitle']);
        $this->assertArrayHasKey("alias", $pages['someTitle2']);
        $this->assertArrayHasKey("id", $pages['someTitle']);
        $this->assertArrayHasKey("id", $pages['someTitle2']);
        $this->assertEquals("someTemplate",$pages["someTitle"]["template"]);
        $this->assertEquals("someTemplate2",$pages["someTitle2"]["template"]);
        $this->assertEquals("",$pages["someTitle"]["alias"]);
        $this->assertEquals("/alias/",$pages["someTitle2"]["alias"]);
        $this->assertEquals("t1",$pages["someTitle"]["id"]);
        $this->assertEquals("t2",$pages["someTitle2"]["id"]);

    }

    public function testListTemplateNamesWillReturnEmptyArrayOnEmptyConfig()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        </config>");

        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $templates = $config->listTemplateNames();
        $this->assertTrue(is_array($templates), "Did not return array");
        $this->assertEquals(0, count($templates), "Did not return empty array");
    }

    public function testListTemplateNamesWillReturnArraySimilarToConfig()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <templates path='folder'>
            <template filename='some_link'>main</template>
            <template filename='some_link2'>main2</template>
        </templates>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $templates = $config->listTemplateNames();
        $this->assertTrue(is_array($templates), "Did not return array");
        $this->assertEquals(2, count($templates), "Did not return empty array");
        $this->assertArrayHasKey(0, $templates);
        $this->assertArrayHasKey(1, $templates);
        $this->assertEquals("main", $templates[0]);
        $this->assertEquals("main2", $templates[1]);
    }


    public function testGetMySQLConnectionWillReturnArrayWithInfoAsInConfigXML()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
            <MySQLConnection>
                <host>someHost</host>
                <database>someDatabase</database>
                <username>someUser</username>
                <password>somePassword</password>
            </MySQLConnection>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $connArray = $config->getMySQLConnection();
        $this->assertArrayHasKey('user', $connArray, 'Did not have user entry');
        $this->assertArrayHasKey('host', $connArray, 'Did not have host entry');
        $this->assertArrayHasKey('password', $connArray, 'Did not have password entry');
        $this->assertArrayHasKey('database', $connArray, 'Did not have database entry');

        $this->assertEquals('someHost', $connArray['host'], 'Host was not right');
        $this->assertEquals('someDatabase', $connArray['database'], 'Host was not right');
        $this->assertEquals('somePassword', $connArray['password'], 'Host was not right');
        $this->assertEquals('someUser', $connArray['user'], 'Host was not right');

    }

    public function testGetMailMySQLConnectionWillReturnArrayWithInfoAsInConfigXML()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
            <MailMySQLConnection>
                <host>someHost</host>
                <database>someDatabase</database>
                <username>someUser</username>
            </MailMySQLConnection>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $connArray = $config->getMailMySQLConnection();
        $this->assertArrayHasKey('user', $connArray, 'Did not have user entry');
        $this->assertArrayHasKey('host', $connArray, 'Did not have host entry');
        $this->assertArrayHasKey('database', $connArray, 'Did not have database entry');

        $this->assertEquals('someHost', $connArray['host'], 'Host was not right');
        $this->assertEquals('someDatabase', $connArray['database'], 'Host was not right');
        $this->assertEquals('someUser', $connArray['user'], 'Host was not right');

    }


    public function testGetMailMySQLConnectionWillReturnRightArrayAfterReturningMySQL()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
            <MySQLConnection>
                <host>asd</host>
                <database>asd</database>
                <username>asd</username>
                <password>asd</password>
            </MySQLConnection>
            <MailMySQLConnection>
                <host>someHost</host>
                <database>someDatabase</database>
                <username>someUser</username>
            </MailMySQLConnection>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $config->getMySQLConnection();
        $connArray = $config->getMailMySQLConnection();
        $this->assertArrayHasKey('user', $connArray, 'Did not have user entry');
        $this->assertArrayHasKey('host', $connArray, 'Did not have host entry');
        $this->assertArrayHasKey('database', $connArray, 'Did not have database entry');

        $this->assertEquals('someHost', $connArray['host'], 'Host was not right');
        $this->assertEquals('someDatabase', $connArray['database'], 'Host was not right');
        $this->assertEquals('someUser', $connArray['user'], 'Host was not right');

    }

    public function testWillReturnNullIfNotSpecifiedInConfig()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("<config>{$this->defaultOwner}</config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');

        $connArray = $config->getMySQLConnection();
        $this->assertNull($connArray, 'Was not null.');
    }

    public function testIsDebugModeWillReturnFalseOnEmpty(){
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("<config>{$this->defaultOwner}</config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $this->assertFalse($config->isDebugMode());

    }

    public function testIsDebugModeWillReturnFalseOnFalse(){
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("<config>{$this->defaultOwner}
        <debugMode>false</debugMode>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $this->assertFalse($config->isDebugMode());

    }

    public function testIsDebugModeWillReturnTrueOnTrue(){
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("<config>{$this->defaultOwner}
        <debugMode>true</debugMode>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $this->assertTrue($config->isDebugMode());

    }

    public function testIsUpdaterEnabledWillReturnTrueOnEmpty(){
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("<config>{$this->defaultOwner}</config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $this->assertTrue($config->isUpdaterEnabled());

    }

    public function testIsUpdaterEnabledWillReturnFalseOnFalse(){
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("<config>{$this->defaultOwner}
        <enableUpdater>false</enableUpdater>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $this->assertFalse($config->isUpdaterEnabled());

    }

    public function testIsUpdaterEnabledWillReturnTrueOnTrue(){
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("<config>{$this->defaultOwner}
        <enableUpdater>true</enableUpdater>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $this->assertTrue($config->isUpdaterEnabled());

    }

    public function testGetTmpPathReturnsRightPath(){
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("<config>{$this->defaultOwner}
                <tmpFolder path='/some/path' />
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $this->assertEquals("/some/path", $config->getTmpFolderPath());
    }

    public function testGetTmpPathReturnsReturnsEmptyWhenNotDefined(){
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("<config>{$this->defaultOwner}
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $this->assertEquals("", $config->getLogPath());
    }

    public function testGetErrorLogReturnsRightPath(){
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("<config>{$this->defaultOwner}
                <log path='/some/path' />
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $this->assertEquals("/some/path", $config->getLogPath());
    }

    public function testGetErrorLogReturnsReturnsEmptyWhenNotDefined(){
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("<config>{$this->defaultOwner}
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $this->assertEquals("", $config->getTmpFolderPath());
    }


    public function testGetDomainWillReturnDomainOnExist(){
        $configXML = simplexml_load_string("<config>
        <siteInfo>
            <domain name='test' extension='com' />
            <owner name='Test Testesen' mail='test@test.dk' username='test' />
        </siteInfo>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $this->assertEquals($config->getDomain(), "test.com");

    }

    public function testGetOwnerWillReturnArrayOfRightFormat(){
        $configXML = simplexml_load_string("<config>
        <siteInfo>
            <domain name='test' extension='com' />
            <owner name='test' mail='test@test.dk' username='test' />
        </siteInfo>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $array = $config->getOwner();
        $this->assertTrue(is_array($array));
        $this->assertEquals(3, count($array));
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('mail', $array);
        $this->assertArrayHasKey('username', $array);
        $this->assertEquals($array['name'], 'test');
        $this->assertEquals($array['mail'], 'test@test.dk');
        $this->assertEquals($array['username'], 'test');
        $this->assertEquals($config->getDomain(), "test.com");

    }



}
