<?php
namespace ChristianBudde\Part\util\db;

use ChristianBudde\Part\Config;
use ChristianBudde\Part\ConfigImpl;
use ChristianBudde\Part\util\CustomDatabaseTestCase;
use ChristianBudde\Part\util\file\File;
use ChristianBudde\Part\util\file\FileImpl;
use Exception;
use SimpleXMLElement;

/**
 * User: budde
 * Date: 6/12/12
 * Time: 7:30 PM
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



    private $defaultOwner = /** @lang XML */
        "<siteInfo><domain name='test' extension='dk'/><owner name='Admin Jensen' mail='test@test.dk' username='asd' /></siteInfo>";
    private $file;
    private $folderPath2;

    public function setUp()
    {
        parent::setUp();
        $this->host = self::$mysqlOptions->getHost();
        $this->user = self::$mysqlOptions->getUsername();
        $this->pass = self::$mysqlOptions->getPassword();
        $this->database = self::$mysqlOptions->getDatabase();

        $folderPath = $GLOBALS['STUBS_DIR'] .  "/db_update";
        $this->folderPath2 = $folderPath."_2";
        $this->configXML = simplexml_load_string(/** @lang XML */
            "
        <config>
            {$this->defaultOwner}
            <MySQLConnection>
            <host>{$this->host}</host>
            <database>{$this->database}</database>
            <username>{$this->user}</username>
            <password>{$this->pass}</password>
            <folders>
                    <folder path='{$folderPath}' name='cms' />
                </folders>
            </MySQLConnection>
        </config>");
        $this->config = new ConfigImpl($this->configXML, getcwd());
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

        }

        $this->assertTrue($exceptionWasThrown, 'Exception was not thrown');

    }


    public function testConnectionWillReuseConnection()
    {


        $connection = $this->mysql->getConnection();
        $connection2 = $this->mysql->getConnection();
        $this->assertEquals($connection, $connection2, 'Did not reuse connections');
    }







    public function testUpdateUpdatesTheDatabase()
    {
        $this->assertEquals([], $this->mysql->getVersion());
        $this->mysql->update();
        $this->assertEquals(1, $this->mysql->getConnection()->query("SHOW TABLES LIKE 'MyGuests'")->rowCount());
        $this->assertEquals(['cms' => 2], $this->mysql->getVersion());
        $this->assertEquals(2, $this->mysql->getVersion('cms'));
    }

    public function testVersionIsPersistent()
    {
        $this->mysql->update();
        $this->mysql = new MySQLDBImpl($this->config);
        $this->assertEquals(['cms' => 2], $this->mysql->getVersion());

    }

    public function testUpdateFolderNotExisting()
    {
        $mysql = $this->setUpMySQLFromXML(/** @lang XML */
            "
        <config>
            {$this->defaultOwner}
            <MySQLConnection>
            <host>{$this->host}</host>
            <database>{$this->database}</database>
            <username>{$this->user}</username>
            <password>{$this->pass}</password>
                <folders>
                <folder name='x' path='nonExisting' />
</folders>
            </MySQLConnection>
        </config>");
        $mysql->update();

    }

    public function testUpdateCanDoWithOneFile()
    {
        $mysql = $this->setUpMySQLFromXML(/** @lang XML */
            "
        <config>
            {$this->defaultOwner}
            <MySQLConnection>
            <host>{$this->host}</host>
            <database>{$this->database}</database>
            <username>{$this->user}</username>
            <password>{$this->pass}</password>
                <folders>
                <folder name='name' path='{$this->folderPath2}' />
</folders>
            </MySQLConnection>
        </config>");
        $mysql->update();
        $this->assertEquals(['name'=>1], $mysql->getVersion());

    }

    public function testVersionIsZeroDefault(){
        $this->assertEquals(0,         $this->mysql->getVersion('name'));
    }

    public function testUpdateWillOnlyRunNewerFiles(){
        $f = new FileImpl($GLOBALS['STUBS_DIR'] .  '/3-insert-row.sql');
        $this->file = $f->copy($GLOBALS['STUBS_DIR'] .  '/db_update/3-insert-row.sql');
        $this->mysql->update();
        $this->mysql->update();
        $this->assertEquals(1, self::$pdo->query('SELECT * FROM MyGuests')->rowCount());

    }



    protected function tearDown()
    {
        parent::tearDown();
        self::$pdo->exec("DROP TABLE IF EXISTS MyGuests");
        self::$pdo->exec("DROP TABLE IF EXISTS _db_version");

        if($this->file instanceof File){
            $this->file->delete();
        }
    }


}
