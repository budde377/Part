<?php
require_once dirname(__FILE__).'/../_class/ConnectionFolderImpl.php';
require_once dirname(__FILE__).'/_stub/StubConnectionImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 20/11/12
 * Time: 20:02
 */
class ConnectionFolderImplTest extends PHPUnit_Framework_TestCase
{

    private $path;
    /** @var $folder ConnectionFolderImpl */
    private $folder;
    /** @var $connection StubConnectionImpl */
    private $connection;


    public function setUp(){
        $this->path = 'testFolder';
        $this->connection = new StubConnectionImpl();
        $this->folder = new ConnectionFolderImpl($this->path,$this->connection);
    }

    public function testGetNameReturnsName(){
        $this->assertEquals($this->path,$this->folder->getName(),'Did not return right name');
    }

    public function testGetAbsolutePathIsAbsolutePath(){
        $this->assertEquals('/'.$this->path,$this->folder->getAbsolutePath(),'Absolute path did not match');
    }

    public function testGetRelativePathIsRelative(){
        $relativeTo = 'test/test/test';
        $this->assertEquals('../../../'.$this->path, $this->folder->getRelativePathTo($relativeTo),'Path was not correct path');
    }

    public function testExistsWillReturnTrueIfDirExists(){
        $this->connection->isDirectoryReturn[] = '/'.$this->path;
        $this->assertTrue($this->folder->exists(),'Did not return true when folder exists');
    }

    public function testExistsWillReturnFalseIfFileExists(){
        $this->connection->isFileReturn[] = '/'.$this->path;
        $this->assertFalse($this->folder->exists(),'Did not return false when file exists');
    }

    public function testExistsWillReturnFalseIfNotExists(){
        $this->assertFalse($this->folder->exists(),'Did not return false when not exists');
    }


    public function testCreateDirectoryWillCreateDirectory(){
        $newFolder = 'someFolder';
        $folder = new ConnectionFolderImpl($newFolder,$this->connection);
        $this->assertTrue($folder->create());
        $found = false;
        foreach($this->connection->dirsCreated as $dir){
            $found = $found || $dir == '/'.$newFolder;
        }
        $this->assertTrue($found,'Did not create folder');
    }

    public function testCreateDirWillReturnFalseOnFailure(){
        $this->connection->createDirectoryReturn = false;
        $this->assertFalse($this->folder->create(),'Did not return false on failure');
    }

    public function testMoveFolderWillReturnFalseOnMoveFile(){
        $this->connection->isFileReturn[] = '/'.$this->path;
        $newPath = 'someFolder';
        $this->assertFalse($this->folder->move($newPath),'Did not return false on move file');
    }
    public function testMoveFolderWillReturnFalseOnNotExists(){
        $newPath = 'someFolder';
        $this->assertFalse($this->folder->move($newPath),'Did not return false on move none');
    }

    public function testMoveFolderWillMoveFolderIfFolder(){
        $this->connection->isDirectoryReturn[] = '/'.$this->path;

        $newPath = 'someFolder';
        $this->assertTrue($this->folder->move($newPath),'Did not return true on success');
        $this->assertEquals('/'.$newPath,$this->folder->getAbsolutePath());
        $found = false;
        foreach($this->connection->moves as $move){
            $found = $found || ($move['oldFile'] = '/'.$this->path && $move['newFile'] = '/'.$newPath);
        }
        $this->assertTrue($found,'Could not find move');

    }

    public function testCopyFolderWillReturnNullOnCopyFile(){
        $this->connection->isFileReturn[] = '/'.$this->path;
        $newPath = 'someFolder';
        $this->assertNull($this->folder->copy($newPath),'Did not return false on copy file');
    }
    public function testCopyFolderWillReturnNullOnNotExists(){
        $newPath = 'someFolder';
        $this->assertNull($this->folder->copy($newPath),'Did not return false on copy none');
    }

    public function testCopyFolderWillCopyFolderIfFolder(){
        $this->connection->isDirectoryReturn[] = '/'.$this->path;

        $newPath = 'someFolder';
        $f = $this->folder->copy($newPath);
        $this->assertInstanceOf('Folder',$f,'Did not return true on success');
        $this->assertEquals('/'.$this->path,$this->folder->getAbsolutePath());
        $this->assertEquals('/'.$newPath,$f->getAbsolutePath());
        $found = false;
        foreach($this->connection->copies as $copy){
            $found = $found || ($copy['oldFile'] = '/'.$this->path && $copy['newFile'] = '/'.$newPath);
        }
        $this->assertTrue($found,'Could not find copy');

    }

    public function testGetParentFolderWillReturnNullOnRoot(){
        $folder = new ConnectionFolderImpl('/',$this->connection);
        $this->assertNull($folder->getParentFolder(),'Did not return null on root');
    }

    public function testGetParentFolderWillReturnFolder(){
        $f = $this->folder->getParentFolder();
        $this->assertInstanceOf('Folder',$f);
        $this->assertEquals('',$f->getAbsolutePath());
    }

    public function testListDirectoryWillReturnFalseIfNotExists(){

        $this->assertFalse($this->folder->listFolder(),'Did not return false on not found');

    }

    public function testListDirectoryWillReturnFalseIfFile(){
        $this->connection->isFileReturn[] = '/'.$this->path;
        $this->assertFalse($this->folder->listFolder(),'Did not return false on file');
    }

    public function testListDirectoryWillReturnListIfDir(){
        $this->connection->isDirectoryReturn[] = '/'.$this->path;
        $l = $this->folder->listFolder();
        $this->assertTrue(is_array($l),'Did not return array');
        $this->assertEquals(0,count($l),'Length did not match');
    }

    public function testListDirectoryWillListDirectory(){
        $path = '/'.$this->path;
        $this->connection->isDirectoryReturn[] = $path;
        $folder = 'SomeFolder';
        $file = 'SomeFile';
        $this->connection->isDirectoryReturn[] = $path.'/'.$folder;
        $this->connection->isFileReturn[] = $path.'/'.$file;
        $this->connection->directoryList[] = $folder;
        $this->connection->directoryList[] = $file;

        $l = $this->folder->listFolder();
        $this->assertEquals(2,count($l),'Wrong array size');
        $folderB = $fileB = false;
        foreach($l as $e){
            if($e instanceof File){
                $fileB = $e->getAbsoluteFilePath() == $path.'/'.$file && $e->exists();
            } else if ($e instanceof Folder){
                $folderB = $e->getAbsolutePath() == $path.'/'.$folder && $e->exists();
            }
        }
        $this->assertTrue($fileB, 'File was not found');
        $this->assertTrue($folderB,'Folder was not found');


    }

    public function testDeleteWillReturnFalseIfNotExists(){

        $this->assertFalse($this->folder->delete(),'Did not return false on not found');
        $this->assertFalse($this->folder->delete(Folder::DELETE_FOLDER_RECURSIVE),'Did not return false on not found');

    }

    public function testDeleteWillReturnFalseIfFile(){
        $this->connection->isFileReturn[] = '/'.$this->path;
        $this->assertFalse($this->folder->delete(),'Did not return false on file');
        $this->assertFalse($this->folder->delete(Folder::DELETE_FOLDER_RECURSIVE),'Did not return false on file');
    }

    public function testDeleteWillReturnFalseIfNonEmptyAndNonRecursive(){
        $path = '/'.$this->path;
        $this->connection->isDirectoryReturn[] = $path;
        $folder = 'SomeFolder';
        $file = 'SomeFile';
        $this->connection->isDirectoryReturn[] = $path.'/'.$folder;
        $this->connection->isFileReturn[] = $path.'/'.$file;
        $this->connection->directoryList[] = $folder;
        $this->connection->directoryList[] = $file;

        $this->assertFalse($this->folder->delete(),'Did not return false on non-empty delete');
    }

    public function testDeleteWillReturnTrueIfEmptyAndNonRecursive(){
        $path = '/'.$this->path;
        $this->connection->isDirectoryReturn[] = $path;

        $this->assertTrue($this->folder->delete(),'Did return false on empty delete');

    }

    public function testDeleteWillReturnTrueIfNonEmptyAndNonRecursive(){
        $path = '/'.$this->path;
        $this->connection->isDirectoryReturn[] = $path;
        $folder = 'SomeFolder';
        $file = 'SomeFile';
        $this->connection->isDirectoryReturn[] = $path.'/'.$folder;
        $this->connection->isFileReturn[] = $path.'/'.$file;
        $this->connection->directoryList[] = $folder;
        $this->connection->directoryList[] = $file;

        $this->assertTrue($this->folder->delete(Folder::DELETE_FOLDER_RECURSIVE),'Did not return false on non-empty delete');


        $this->assertEquals(3,count($this->connection->deleted));
        $this->assertTrue(array_search($path,$this->connection->deleted) !== false);
        $this->assertTrue(array_search($path.'/'.$folder,$this->connection->deleted) !== false);
        $this->assertTrue(array_search($path.'/'.$file,$this->connection->deleted) !== false);
    }

    public function testIteratorIsLikeList(){
        $path = '/'.$this->path;
        $this->connection->isDirectoryReturn[] = $path;
        $folder = 'SomeFolder';
        $file = 'SomeFile';
        $this->connection->isDirectoryReturn[] = $path.'/'.$folder;
        $this->connection->isFileReturn[] = $path.'/'.$file;
        $this->connection->directoryList[] = $folder;
        $this->connection->directoryList[] = $file;

        $list = $this->folder->listFolder();
        $this->folder->rewind();

        $size = 0;
        foreach($this->folder as $elem){
            $found = false;
            foreach($list as $e){
                $found = $found ||
                    ($elem instanceof File && $e instanceof File &&  $elem->getAbsoluteFilePath() == $e->getAbsoluteFilePath()) ||
                    ($elem instanceof Folder && $e instanceof Folder &&  $elem->getAbsolutePath() == $e->getAbsolutePath());

            }
            $this->assertTrue($found);
            $size++;
        }
        $this->assertEquals(count($list),$size);
    }


}
