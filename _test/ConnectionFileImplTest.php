<?php
require_once dirname(__FILE__).'/../_class/ConnectionFileImpl.php';
require_once dirname(__FILE__).'/_stub/StubConnectionImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 19/11/12
 * Time: 21:56
 */
class ConnectionFileImplTest extends PHPUnit_Framework_TestCase
{
    /** @var $connection StubConnectionImpl */
    private $connection;
    /** @var $file ConnectionFileImpl */
    private $file;

    private $fileName;

    public function setUp(){
        $this->fileName = '/someName';
        $this->connection = new StubConnectionImpl();
        $this->file = new ConnectionFileImpl($this->fileName,$this->connection);
    }



    public function testExistsReturnTrueIfExistsInConnection(){
        $this->connection->isFileReturn[] = $this->fileName;
        $this->assertTrue($this->file->exists(),'Did not return true on exists');
    }

    public function testExistsReturnFalseIfNotExists(){
        $this->assertFalse($this->file->exists(),'Did not return false on not exists');
    }

    public function testExistsReturnFalseIfDir(){
        $this->connection->isDirectoryReturn[] = $this->fileName;
        $this->assertFalse($this->file->exists(),'Did not return false on folder');

    }

    public function testReturnFileNameWillReturnFileName(){
        $this->assertEquals(basename($this->fileName),$this->file->getFileName(),'Filenames did not match');
    }

}
