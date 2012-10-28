<?php
require_once dirname(__FILE__) . '/../_class/ConfigImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/15/12
 * Time: 7:07 PM
 * To change this template use File | Settings | File Templates.
 */
class ConfigImplTest extends PHPUnit_Framework_TestCase
{

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
            $this->assertEquals("SiteConfig", $exception->getExpectedSchema(), "Did expect the wrong Schema");
            $this->assertEquals("ConfigXML", $exception->getXmlDesc(), "Did validate the wrong XML");
        }

        $this->assertTrue($exceptionWasThrown, "Did not throw expected InvalidXMLException");
    }


    public function testGetTemplateReturnNullWithEmptyConfigXML()
    {
        $emptyConfigXML = simplexml_load_string('<config></config>');
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($emptyConfigXML, $rootPath);
        $template = $config->getTemplate('main');
        $this->assertNull($template, 'The template was not null with empty config XML');
    }

    public function testGetTemplateReturnNullWithTemplateNIL()
    {
        $configXML = simplexml_load_string("
        <config>
        <templates>
        <template link='some_link'>main</template>
        </templates>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $template = $config->getTemplate('nil');
        $this->assertNull($template, 'The template was not null with template NIL');
    }

    public function testGetTemplateReturnLinkWithTemplateInList()
    {
        $configXML = simplexml_load_string("
        <config>
        <templates>
        <template link='some_link'>main</template>
        </templates>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $template = $config->getTemplate('main');
        $this->assertEquals($rootPath . 'some_link', $template, 'The config did not return the right link.');
    }

    public function testGetPageElementReturnNullWithEmptyConfigXML()
    {
        $emptyConfigXML = simplexml_load_string('<config></config>');
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($emptyConfigXML, $rootPath);
        $template = $config->getPageElement('main');
        $this->assertNull($template, 'The getPageElement was not null with empty config XML');
    }

    public function testGetPageElementReturnNullWithTemplateElementNIL()
    {
        $configXML = simplexml_load_string("
        <config>
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
        <config>
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


    public function testGetOptimizerReturnNullWithEmptyConfigXML()
    {
        /** @var $emptyConfigXML SimpleXMLElement */
        $emptyConfigXML = simplexml_load_string('<config></config>');
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($emptyConfigXML, $rootPath);
        $template = $config->getOptimizer('main');
        $this->assertNull($template, 'The getOptimizer was not null with empty config XML');
    }

    public function testGetOptimizerReturnNullWithTemplateElementNIL()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string('
        <config xmlns="http://christian-budde.dk/SiteConfig">
            <optimizers>
                <class name="someName" link="someLink">SomeClass</class>
            </optimizers>
        </config>');
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $template = $config->getOptimizer('nil');
        $this->assertNull($template, 'The getOptimizer was not null with optimizer NIL');
    }

    public function testGetOptimizerReturnArrayWithOptimizerInList()
    {
        $configXML = simplexml_load_string('
        <config xmlns="http://christian-budde.dk/SiteConfig">
        <optimizers>
        <class name="someName" link="someLink">SomeClassName</class>
        </optimizers>
        </config>');
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

    public function testGetPreScriptReturnEmptyArrayWithEmptyConfig()
    {
        $emptyConfigXML = simplexml_load_string('<config></config>');
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($emptyConfigXML, $rootPath);
        $preScript = $config->getPreScripts();
        $this->assertTrue(is_array($preScript), 'getPreScripts did not return an array with empty config.');
        $this->assertTrue(empty($preScript), 'getPreScripts did not return an empty array with empty config.');
    }


    public function testGetPreScriptHasEntrySpecifiedInConfigWithLinkAsVal()
    {
        $configXML = simplexml_load_string("
        <config>
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

    public function testGetPostScriptReturnEmptyArrayWithEmptyConfig()
    {
        $emptyConfigXML = simplexml_load_string('<config></config>');
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($emptyConfigXML, $rootPath);
        $preScript = $config->getPostScripts();
        $this->assertTrue(is_array($preScript), 'getPostScripts did not return an array with empty config.');
        $this->assertTrue(empty($preScript), 'getPostScripts did not return an empty array with empty config.');
    }

    public function testGetPostScriptHasEntrySpecifiedInConfig()
    {
        $configXML = simplexml_load_string("
        <config>
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
        <config>
        <postScripts>
        <class link='some_link'>main</class>
        </postScripts>
        </config>");
        $rootPath = dirname(__FILE__) . '/';
        $config = new ConfigImpl($configXML, $rootPath);
        $preScript = $config->getPostScripts();
        $this->assertEquals($rootPath . 'some_link', $preScript['main'], 'getPostScripts did not return array with right link');

    }

    public function testOrderOfPostScriptIsTheSameInFileAsOutput()
    {
        $configXML = simplexml_load_string("
        <config>
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
        <config>
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


    public function testGetMySQLConnectionWillReturnArrayWithInfoAsInConfigXML()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string('
        <config xmlns="http://christian-budde.dk/SiteConfig">
            <MySQLConnection>
                <host>someHost</host>
                <database>someDatabase</database>
                <username>someUser</username>
                <password>somePassword</password>
            </MySQLConnection>
        </config>');
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

    public function testWillReturnNullIfNotSpecifiedInConfig()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string('<config></config>');
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');

        $connArray = $config->getMySQLConnection();
        $this->assertNull($connArray, 'Was not null.');
    }

}