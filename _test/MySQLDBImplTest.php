<?php
require_once dirname(__FILE__) . '/MySQLConstants.php';
require_once dirname(__FILE__) . '/../_class/MySQLDBImpl.php';
require_once dirname(__FILE__) . '/../_class/ConfigImpl.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/12/12
 * Time: 7:30 PM
 * To change this template use File | Settings | File Templates.
 */
class MySQLDBImplTest extends PHPUnit_Framework_TestCase
{

    private $host = MySQLConstants::MYSQL_HOST;
    private $user = MySQLConstants::MYSQL_USERNAME;
    private $pass = MySQLConstants::MYSQL_PASSWORD;
    private $database = MySQLConstants::MYSQL_DATABASE;

    public function testConnectionUsesConfigInfo()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("
        <config xmlns='http://christian-budde.dk/SiteConfig'>
            <MySQLConnection>
            <host>{$this->host}</host>
            <database>{$this->database}</database>
            <username>{$this->user}</username>
            <password>{$this->pass}</password>
            </MySQLConnection>
        </config>");

        $config = new ConfigImpl($configXML, dirname(__FILE__));

        $mysql = new MySQLDBImpl($config);
        $connection = $mysql->getConnection();
        $this->assertInstanceOf('PDO', $connection, 'Did not return instance of PDO');
        $this->assertEquals(0, $connection->errorCode(), 'Did not connect with no error');
    }

    public function testConnectionWillThrowExceptionWithWrongInfo()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string('
        <config xmlns="http://christian-budde.dk/SiteConfig">
            <MySQLConnection><host>10.8.0.1</host><database>thisIsNotAValidDB</database><username>nosSchUser</username><password>password</password></MySQLConnection>
        </config>');

        $config = new ConfigImpl($configXML, dirname(__FILE__));

        $mysql = new MySQLDBImpl($config);

        $exceptionWasThrown = false;
        try {
            $mysql->getConnection();
        } catch (Exception $e) {
            $exceptionWasThrown = true;
            $this->assertInstanceOf('PDOException', $e, 'Throw wrong exception');
            $this->assertEquals(1045, $e->getCode(), 'Wrong error code');

        }

        $this->assertTrue($exceptionWasThrown, 'Exception was not thrown');

    }


    public function testConnectionWillReuseConnection()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("
        <config xmlns='http://christian-budde.dk/SiteConfig'>
            <MySQLConnection>
            <host>{$this->host}</host>
            <database>{$this->database}</database>
            <username>{$this->user}</username>
            <password>{$this->pass}</password>
            </MySQLConnection>
        </config>");

        $config = new ConfigImpl($configXML, dirname(__FILE__));

        $mysql = new MySQLDBImpl($config);
        $connection = $mysql->getConnection();
        $connection2 = $mysql->getConnection();
        $this->assertEquals($connection, $connection2, 'Did not reuse connections');
    }


}
