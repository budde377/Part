<?php
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

    private $localFilePath;
    /** @var $localFile File */
    private $localFile;

    private $localDir;

    private $fileName;



    public function setUp(){
        $this->fileName = 'someName';
        $this->connection = new StubConnectionImpl();
        $this->file = new ConnectionFileImpl($this->fileName,$this->connection);
    }

    public function setUpLocalFile(){
        $this->localFilePath = dirname(__FILE__).'/stubs/ConnectionFileStub';
        $this->localFile = new FileImpl($this->localFilePath);
        @unlink($this->localFilePath);
        $this->localFile->write("HELLO WORLD");
        $this->connection->isFileReturn[] = $this->localFilePath;
        $this->connection->wrapper = '';
        $this->file = new ConnectionFileImpl($this->localFilePath,$this->connection);
    }

    public function setUpLocalDir(){
        $this->localFilePath = dirname(__FILE__).'/stubs/ConnectionFileStub';
        $this->localDir = new FolderImpl($this->localFilePath);
        @unlink($this->localFilePath);
        $this->localDir->delete(Folder::DELETE_FOLDER_RECURSIVE);
        $this->connection->isDirectoryReturn[] = $this->localFilePath;
        $this->connection->wrapper = '';
        $this->file = new ConnectionFileImpl($this->localFilePath,$this->connection);

    }

    public function tearDown(){
        @unlink($this->localFilePath);
    }

    public function testExistsReturnTrueIfExistsInConnection(){
        $this->connection->isFileReturn[] = '/'.$this->fileName;
        $this->assertTrue($this->file->exists(),'Did not return true on exists');
    }

    public function testExistsReturnFalseIfNotExists(){
        $this->assertFalse($this->file->exists(),'Did not return false on not exists');
    }

    public function testExistsReturnFalseIfDir(){
        $this->connection->isDirectoryReturn[] = $this->fileName;
        $this->assertFalse($this->file->exists(),'Did not return false on folder');

    }

    public function testReturnBaseNameWillReturnFileName(){
        $this->assertEquals(basename($this->fileName),$this->file->getBaseName(),'Filenames did not match');
    }

    public function testGetAbsolutePathWillReturnAbsolutePath(){
        $this->assertEquals('/'.$this->fileName,$this->file->getAbsoluteFilePath(),'Did not return right path');
    }

    public function testGetRelativePathToWillReturnRelativePathTo(){
        $path = 'test/test/test';
        $relativePath = '../../../'.$this->fileName;
        $this->assertEquals($relativePath,$this->file->getRelativeFilePathTo($path),'Did not return right relative path');
    }

    public function testGetAccessModeWillBeRWPointerAtEndAsDefault(){
        $this->assertEquals($this->file->getAccessMode(),File::FILE_MODE_RW_POINTER_AT_END,'Wrong mode');
    }

    public function testSetAccessModeWillSetAccessMode(){
        $this->file->setAccessMode(File::FILE_MODE_W_POINTER_AT_END);
        $this->assertEquals($this->file->getAccessMode(),File::FILE_MODE_W_POINTER_AT_END,'Mode was not set');
    }

    public function testSetAccessModeWillNotSetOnInvalidMode(){
        $invalidMode = 'invalidMode';
        $this->file->setAccessMode($invalidMode);
        $this->assertEquals(File::FILE_MODE_RW_POINTER_AT_END,$this->file->getAccessMode(),'Mode was set');
    }

    public function testSizeWillReturnSize(){
        $size = 123;
        $this->connection->sizeArray['/'.$this->fileName] = $size;
        $this->assertEquals($this->file->size(),$size,'Size did not match');
    }

    public function testSizeWillReturnMinusOneOnNoFile(){
        $this->assertEquals($this->file->size(),-1,'Size did not match');
    }

    public function testGetResourceWillReturnFalseOnError(){
        $this->assertFalse($this->file->getResource(),'Did not return false on error');
    }

    public function testGetResourceWillReturnResourceOnNoError(){
        $this->connection->wrapper = '';
        $file = new ConnectionFileImpl(dirname(__FILE__).'/stubs/fileStub',$this->connection);
        $resource = $file->getResource();
        $this->assertTrue(is_resource($resource),'Did not return resource');

    }

    public function testFileGetContentsWillReturnContentsOfFile(){
        $this->setUpLocalFile();
        $this->assertEquals($this->file->getContents(),$this->localFile->getContents(),'Contents did not match');
    }

    public function testFileGetContentsWillReturnEmptyStringOnNonExistingFile(){
        $this->setUpLocalFile();
        $this->localFile->delete();
        $this->assertEquals('',$this->file->getContents(),'Was not empty string on no file');
    }

    public function testFileGetContentsWillReturnFalseOnDirectory(){
        $this->setUpLocalDir();
        $this->assertFalse($this->file->getContents(),'Did not return false on dir');
    }

    public function testWriteWillReturnFalseOnError(){
        $this->setUp();
        $this->assertFalse($this->file->write("someString"),'Did not return false on error');
    }

    public function testWriteWillReturnIntOnSuccessfulWrite(){
        $this->setUpLocalFile();
        $string = "TEST STRING";
        $this->file->setAccessMode(File::FILE_MODE_W_TRUNCATE_FILE_TO_ZERO_LENGTH);
        $this->assertGreaterThan(0,$this->file->write($string),'Did not return an int greater than 0');
        $this->assertEquals($this->localFile->getContents(),$string,'Did not write');
    }

    public function testMoveWillReturnFalseIfNotExists(){
        $newLocation = 'some2';
        $this->assertFalse($this->file->move($newLocation),'Did not return false on not exists');
    }

    public function testMoveWillReturnFalseOnMoveDir(){
        $newLocation = 'some2';
        $this->connection->isDirectoryReturn[] = '/'.$this->fileName;
        $this->assertFalse($this->file->move($newLocation),'Did not return false on not exists');
    }

    public function testMoveWillReturnTrueOnFileAndSuccess(){
        $newLocation = 'some2';
        $this->connection->isFileReturn[] = '/'.$this->fileName;
        $this->assertTrue($this->file->move($newLocation),'Did not return true on success');
        $this->assertEquals($this->file->getAbsoluteFilePath(),'/'.$newLocation,'Did not change location');
        $found = false;
        foreach($this->connection->moves as $move){
            $found = $found || ($move['oldFile'] == '/'.$this->fileName && $move['newFile'] == '/'.$newLocation);
        }
        $this->assertTrue($found,'Connection was not called');
    }

    public function testCopyWillReturnNullIfNotExists(){
        $newLocation = 'some2';
        $this->assertNull($this->file->copy($newLocation),'Did not return null on not exists');
    }

    public function testCopyWillReturnFalseOnCopyDir(){
        $newLocation = 'some2';
        $this->connection->isDirectoryReturn[] = '/'.$this->fileName;
        $this->assertNull($this->file->copy($newLocation),'Did not return null on not exists');
    }

    public function testCopyWillReturnTrueOnFileAndSuccess(){
        $newLocation = 'some2';
        $this->connection->isFileReturn[] = '/'.$this->fileName;
        $f = $this->file->copy($newLocation);
        $this->assertInstanceOf('File',$f,'Did not return true on success');
        $this->assertEquals($this->file->getAbsoluteFilePath(),'/'.$this->fileName,'Did change location');
        $this->assertEquals($f->getAbsoluteFilePath(),'/'.$newLocation,'Did not return folder with right path');
        $found = false;
        foreach($this->connection->copies as $move){
            $found = $found || ($move['oldFile'] == '/'.$this->fileName && $move['newFile'] == '/'.$newLocation);
        }
        $this->assertTrue($found,'Could not find copy');
    }

    public function testDeleteWillReturnFalseIfNotExists(){
        $this->assertFalse($this->file->delete(),'Did not return false on not exists');
    }

    public function testDeleteWillReturnFalseOnMoveDir(){
        $this->connection->isDirectoryReturn[] = '/'.$this->fileName;
        $this->assertFalse($this->file->delete(),'Did not return false on not exists');
    }

    public function testDeleteWillReturnTrueOnFileAndSuccess(){
        $this->connection->isFileReturn[] = '/'.$this->fileName;
        $this->assertTrue($this->file->delete(),'Did not return true on success');
        $this->assertTrue($this->connection->deleteFileCalled,'Did not call delete file');
    }

    public function testGetParentFolderWillReturnNullIfRoot(){
        $file = new ConnectionFileImpl('/',$this->connection);
        $parent = $file->getParentFolder();
        $this->assertNull($parent);
    }

    public function testGetParentFolderWillReturnFolderIfNotRoot(){
        $f = $this->file->getParentFolder();
        $this->assertInstanceOf('Folder',$f);
        $this->assertEquals('',$f->getAbsolutePath());
    }
}
