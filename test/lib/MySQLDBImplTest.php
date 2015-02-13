<?php
namespace ChristianBudde\Part\test;

use ChristianBudde\Part\Config;
use ChristianBudde\Part\ConfigImpl;
use ChristianBudde\Part\test\util\CustomDatabaseTestCase;
use ChristianBudde\Part\test\util\MailMySQLConstantsImpl;
use ChristianBudde\Part\util\db\MySQLDBImpl;
use Exception;
use SimpleXMLElement;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/12/12
 * Time: 7:30 PM
 * To change this template use File | Settings | File Templates.
 */
class MySQLDBImplTest extends CustomDatabaseTestCase
{

    private $host;
    private $user;
    private $pass;
    private $database;
    /** @var SimpleXMLElement */
    private $configXML;
    /** @var  Config */
    private $config;
    /** @var  \ChristianBudde\Part\util\db\MySQLDBImpl */
    private $mysql;

    private $mailHost;
    private $mailUser;
    private $mailPassword;
    private $mailDatabase;


    private $defaultOwner = "<siteInfo><domain name='test' extension='dk'/><owner name='Admin Jensen' mail='test@test.dk' username='asd' /></siteInfo>";

    public function setUp()
    {
        parent::setUp();
        $this->host = self::$mysqlOptions->getHost();
        $this->user = self::$mysqlOptions->getUsername();
        $this->pass = self::$mysqlOptions->getPassword();
        $this->database = self::$mysqlOptions->getDatabase();

        $opt = new MailMySQLConstantsImpl();
        $this->mailHost = $opt->getHost();
        $this->mailUser = $opt->getUsername();
        $this->mailDatabase = $opt->getDatabase();
        $this->mailPassword = $opt->getPassword();

        $this->configXML = simplexml_load_string("
        <config>
            {$this->defaultOwner}
            <MySQLConnection>
            <host>{$this->host}</host>
            <database>{$this->database}</database>
            <username>{$this->user}</username>
            <password>{$this->pass}</password>
            </MySQLConnection>
            <MailMySQLConnection >
                <host>{$this->mailHost}</host>
                <database>{$this->mailDatabase}</database>
                <username>{$this->mailUser}</username>
            </MailMySQLConnection>

        </config>");
        $this->config = new ConfigImpl($this->configXML, dirname(__FILE__));
        $this->mysql = new MySQLDBImpl($this->config);

    }

    /**
     * @param $xml
     * @return MySQLDBImpl
     */
    private function setUpMySQLFromXML($xml)
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string($xml);

        $config = new ConfigImpl($configXML, dirname(__FILE__));

        return new MySQLDBImpl($config);

    }

    public function testConnectionUsesConfigInfo()
    {

        $connection = $this->mysql->getConnection();
        $this->assertInstanceOf('PDO', $connection, 'Did not return instance of PDO');
        $this->assertEquals(0, $connection->errorCode(), 'Did not connect with no error');
    }

    public function testConnectionWillThrowExceptionWithWrongInfo()
    {

        $mysql = $this->setUpMySQLFromXML("
        <config>
            {$this->defaultOwner}
            <MySQLConnection>
                <host>10.8.0.1</host>
                <database>thisIsNotAValidDB</database>
                <username>nosSchUser</username>
                <password>password</password>
            </MySQLConnection>
        </config>");

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


        $connection = $this->mysql->getConnection();
        $connection2 = $this->mysql->getConnection();
        $this->assertEquals($connection, $connection2, 'Did not reuse connections');
    }


    public function testMailConnectionUsesConfigInfo()
    {

        $connection = $this->mysql->getMailConnection($this->mailPassword);
        $this->assertInstanceOf('PDO', $connection, 'Did not return instance of PDO');
        $this->assertEquals(0, $connection->errorCode(), 'Did not connect with no error');
    }

    public function testMailConnectionWillThrowExceptionWithWrongInfo()
    {
        $mysql = $this->setUpMySQLFromXML("
        <config>
            {$this->defaultOwner}
            <MySQLConnection>
                <host>10.8.0.1</host>
                <database>thisIsNotAValidDB</database>
                <username>nosSchUser</username>
                <password>password</password>
            </MySQLConnection>
            <MailMySQLConnection>
                <host>10.8.0.1</host>
                <database>thisIsNotAValidDB</database>
                <username>nosSchUser</username>
            </MailMySQLConnection>
        </config>");

        $exceptionWasThrown = false;
        try {
            $mysql->getMailConnection($this->mailPassword);
        } catch (Exception $e) {
            $exceptionWasThrown = true;
            $this->assertInstanceOf('PDOException', $e, 'Throw wrong exception');
            $this->assertEquals(1045, $e->getCode(), 'Wrong error code');

        }

        $this->assertTrue($exceptionWasThrown, 'Exception was not thrown');

    }


    public function testMailConnectionWillReuseConnection()
    {

        $connection = $this->mysql->getMailConnection($this->mailPassword);
        $connection2 = $this->mysql->getMailConnection($this->mailPassword);
        $this->assertEquals($connection, $connection2, 'Did not reuse connections');
    }


    public function testMailConnectionWillNotReuseConnectionWHenDifferentPassword()
    {

        $this->mysql->getMailConnection($this->mailPassword);
        $exceptionWasThrown = false;
        try {
            $this->mysql->getMailConnection($this->mailPassword . '42');
        } catch (Exception $e) {
            $exceptionWasThrown = true;
            $this->assertInstanceOf('PDOException', $e, 'Throw wrong exception');
            $this->assertEquals(1045, $e->getCode(), 'Wrong error code');
        }
        $this->assertTrue($exceptionWasThrown);
    }

}
