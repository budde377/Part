<?php

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 8/13/13
 * Time: 11:41 AM
 * To change this template use File | Settings | File Templates.
 */

class SiteContentImplTest extends PHPUnit_Extensions_Database_TestCase{

    /** @var  SiteImpl */
    private $site;
    /** @var  DB */
    private $db;
    /** @var  SiteContentImpl */
    private $existingContent;
    /** @var  SiteContentImpl */
    private $existingContent2;
    /** @var  SiteContentImpl */
    private $nonExistingContent;


    public function setUp(){
        parent::setUp();
        $this->site = new StubSiteImpl();
        $this->db = new StubDBImpl();
        $pdo = new PDO('mysql:dbname=' . MySQLConstants::MYSQL_DATABASE. ';host=' . MySQLConstants::MYSQL_HOST, MySQLConstants::MYSQL_USERNAME, MySQLConstants::MYSQL_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $this->db->setConnection($pdo);
        $this->existingContent = new SiteContentImpl($this->db, $this->site);
        $this->existingContent2 = new SiteContentImpl($this->db, $this->site, "Test");
        $this->nonExistingContent = new SiteContentImpl($this->db, $this->site, "NoContent");
    }

    public function testListContentWillReturnArray(){
        $this->assertTrue(is_array($this->existingContent->listContentHistory()));
    }


    public function testListContentWillReturnArrayOfRightSize(){

        $this->assertEquals(1, count($ar = $this->existingContent->listContentHistory()));
        $this->assertEquals(2, count($ar[0]));
        $this->assertEquals("Some Content", trim($ar[0]['content']));
        $this->assertGreaterThan(0, trim($ar[0]['time']));
    }

    public function testFromWillLimitList(){
        $this->assertEquals(0, count($this->existingContent->listContentHistory(time())));
    }

    public function testToWillLimitList(){
        $this->existingContent->addContent("TEST!");
        $this->assertEquals(1, count($this->existingContent->listContentHistory(null, time()-100)));

    }

    public function testFromToWillBeAccurate(){
        $this->existingContent2->addContent("3");
        $this->assertEquals(3, count($this->existingContent2->listContentHistory()));
        $this->assertEquals(1, count($this->existingContent2->listContentHistory(1356994000, 1356995000)));
    }


    public function testAddContentWillAddContent(){
        $content = "Lorem Ipsum";
        $this->assertGreaterThan(time()-100, $this->existingContent->addContent($content));
        $ec = $this->existingContent->listContentHistory(time()-100);
        $this->assertEquals(2, count($this->existingContent->listContentHistory()));
        $this->assertEquals(1, count($ec));
        $this->assertEquals($content, $ec[0]['content']);
    }

    public function testAddContentIsVolatile(){
        $this->assertEquals(1, count($this->existingContent->listContentHistory()));
        $this->existingContent->addContent("ASD");
        $this->assertEquals(2, count($this->existingContent->listContentHistory()));
        $this->existingContent  = new SiteContentImpl($this->db, $this->site);
        $this->assertEquals(2, count($this->existingContent->listContentHistory()));
    }


    public function testLatestContentWillReturnLatestContent(){
        $content = "LoremIp";
        $this->existingContent->addContent($content);
        $this->assertEquals($content, $this->existingContent->latestContent());
    }

    public function testGetContentBeforeTimeReturnsNull(){
        $this->assertNull($this->existingContent2->getContentAt(1));
    }

    public function testGetContentNowReturnsRightResult(){
        $this->assertEquals($this->existingContent2->latestContent(), $this->existingContent2->getContentAt(time())['content']);
    }

    public function testGetContentBetweenTimesReturnRightResult (){
        $this->assertEquals("1", $this->existingContent2->getContentAt(1356994000)['content']);
    }

    public function testLatestContentWillReturnNullOnNoContent(){
        $this->assertNull($this->nonExistingContent->latestContent());
    }


    public function testLatestTimeWillReturnNullOnNoContent(){
        $this->assertNull($this->nonExistingContent->latestTime());
    }


    public function testLatestTimeWillReturnLatestTime(){
        $this->existingContent->addContent("ASD");
        $this->assertGreaterThan(time()-100, $this->existingContent->latestTime());
    }

    public function testAddingContentWillModifySite(){
        $this->existingContent->addContent("LOL");
        $this->assertEquals($this->site->lastModified(), $this->existingContent->latestTime());
    }


    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        $pdo = new PDO('mysql:dbname=' . MySQLConstants::MYSQL_DATABASE . ';host=' . MySQLConstants::MYSQL_HOST, MySQLConstants::MYSQL_USERNAME, MySQLConstants::MYSQL_PASSWORD);
        return $this->createDefaultDBConnection($pdo);
    }

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createMySQLXMLDataSet(dirname(__FILE__) . '/mysqlXML/SiteContentImplTest.xml');
    }

    public function getSetUpOperation()
    {
        $cascadeTruncates = true;
        return new PHPUnit_Extensions_Database_Operation_Composite(array(new TruncateOperation($cascadeTruncates), PHPUnit_Extensions_Database_Operation_Factory::INSERT()));
    }

}