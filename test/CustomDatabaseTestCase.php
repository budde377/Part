<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/24/14
 * Time: 6:24 PM
 */

class CustomDatabaseTestCase extends PHPUnit_Extensions_Database_TestCase{


    protected static $pdo;

    protected $dataset;

    function __construct($dataset = null)
    {
        $this->dataset = $dataset == null?dirname(__FILE__) . '/mysqlXML/PageContentImplTest.xml':$dataset;
    }


    public function testDummy(){

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
        return new PHPUnit_Extensions_Database_Operation_Composite(array(new TruncateOperation($cascadeTruncates), PHPUnit_Extensions_Database_Operation_Factory::INSERT()));
    }


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$pdo = new PDO('mysql:dbname=' . MySQLConstants::MYSQL_DATABASE. ';host=' . MySQLConstants::MYSQL_HOST, MySQLConstants::MYSQL_USERNAME, MySQLConstants::MYSQL_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

    }

    public static function tearDownAfterClass()
    {
        self::$pdo = null;
    }


}