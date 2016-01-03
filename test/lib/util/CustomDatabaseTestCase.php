<?php
namespace ChristianBudde\Part\util;

use PDO;
use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use PHPUnit_Extensions_Database_Operation_Composite;
use PHPUnit_Extensions_Database_TestCase;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/24/14
 * Time: 6:24 PM
 */
class CustomDatabaseTestCase extends PHPUnit_Extensions_Database_TestCase
{

    /** @var  PDO */
    protected static $pdo;
    /** @var  MySQLConstants */
    protected static $mysqlOptions;
    /** @var  MySQLConstants */
    protected static $mailMySQLOptions;
    protected $dataset;

    function __construct($dataset = null)
    {
        parent::__construct();

        $this->dataset = $dataset == null ? $GLOBALS['MYSQL_XML_DIR'] .  '/PageContentImplTest.xml' : $dataset;
    }


    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        return $this->createDefaultDBConnection(self::$pdo);
    }


    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createMySQLXMLDataSet($this->dataset);
    }

    public function getSetUpOperation()
    {
        $cascadeTruncates = true;
        return new PHPUnit_Extensions_Database_Operation_Composite(
            [
                new TruncateOperation($cascadeTruncates),
                new InsertOperation($cascadeTruncates)
            ]);
    }


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        if (self::$mysqlOptions == null) {
            self::$mysqlOptions = new StandardMySQLConstantsImpl();
        }
        self::$pdo = new PDO('mysql:dbname=' . self::$mysqlOptions->getDatabase() . ';host=' . self::$mysqlOptions->getHost(), self::$mysqlOptions->getUsername(), self::$mysqlOptions->getPassword(), array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

    }

    public static function tearDownAfterClass()
    {
        self::$pdo = null;
    }




}
