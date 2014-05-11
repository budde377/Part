<?php
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
    /** @var File */
    private $testFile;
    /** @var File */
    private $testFile2;
    /** @var Folder */
    private $testFolder;
    /** @var Folder */
    private $testFolder2;

    public function setUp()
    {
        $this->path = 'testFolder';
        $this->connection = new StubConnectionImpl();
        $this->folder = new ConnectionFolderImpl($this->path, $this->connection);

    }

    public function setUpFolder()
    {
        $this->testFolder = new FolderImpl(dirname(__FILE__) . '/stubs/ConnectionTestFolder');
        $this->testFolder->create();
        $this->testFolder2 = new FolderImpl(dirname(__FILE__) . '/stubs/ConnectionTestFolder2');
        $this->testFolder2->create();
        $this->testFile = new FileImpl(dirname(__FILE__) . '/stubs/ConnectionTestFile');
        $this->testFile->write("TEST");
        $this->testFile2 = new FileImpl($this->testFolder2->getAbsolutePath().'/ConnectionTestFile2');
        $this->testFile2->write("TEST2");
        $this->connection->wrapper = '';
        $this->folder = new ConnectionFolderImpl(dirname(__FILE__) . '/stubs/ConnectionTestFolder', $this->connection);

    }

    public function tearDown()
    {
        if ($this->testFile2 instanceof File) {
            $this->testFile->delete();
        }
        if ($this->testFolder instanceof Folder) {
            $this->testFolder->delete(Folder::DELETE_FOLDER_RECURSIVE);
        }
        if ($this->testFolder2 instanceof Folder) {
            $this->testFolder2->delete(Folder::DELETE_FOLDER_RECURSIVE);
        }
        if ($this->testFile instanceof File) {
            $this->testFile->delete();
        }

    }

    public function testGetNameReturnsName()
    {
        $this->assertEquals($this->path, $this->folder->getName(), 'Did not return right name');
    }

    public function testGetAbsolutePathIsAbsolutePath()
    {
        $this->assertEquals('/' . $this->path, $this->folder->getAbsolutePath(), 'Absolute path did not match');
    }

    public function testGetRelativePathIsRelative()
    {
        $relativeTo = 'test/test/test';
        $this->assertEquals('../../../' . $this->path, $this->folder->getRelativePathTo($relativeTo), 'Path was not correct path');
    }

    public function testExistsWillReturnTrueIfDirExists()
    {
        $this->connection->isDirectoryReturn[] = '/' . $this->path;
        $this->assertTrue($this->folder->exists(), 'Did not return true when folder exists');
    }

    public function testExistsWillReturnFalseIfFileExists()
    {
        $this->connection->isFileReturn[] = '/' . $this->path;
        $this->assertFalse($this->folder->exists(), 'Did not return false when file exists');
    }

    public function testExistsWillReturnFalseIfNotExists()
    {
        $this->assertFalse($this->folder->exists(), 'Did not return false when not exists');
    }


    public function testCreateDirectoryWillCreateDirectory()
    {
        $newFolder = 'someFolder';
        $folder = new ConnectionFolderImpl($newFolder, $this->connection);
        $this->assertTrue($folder->create());
        $found = false;
        foreach ($this->connection->dirsCreated as $dir) {
            $found = $found || $dir == '/' . $newFolder;
        }
        $this->assertTrue($found, 'Did not create folder');
    }

    public function testCreateDirWillReturnFalseOnFailure()
    {
        $this->connection->createDirectoryReturn = false;
        $this->assertFalse($this->folder->create(), 'Did not return false on failure');
    }

    public function testMoveFolderWillReturnFalseOnMoveFile()
    {
        $this->connection->isFileReturn[] = '/' . $this->path;
        $newPath = 'someFolder';
        $this->assertFalse($this->folder->move($newPath), 'Did not return false on move file');
    }

    public function testMoveFolderWillReturnFalseOnNotExists()
    {
        $newPath = 'someFolder';
        $this->assertFalse($this->folder->move($newPath), 'Did not return false on move none');
    }

    public function testMoveFolderWillMoveFolderIfFolder()
    {
        $this->connection->isDirectoryReturn[] = '/' . $this->path;

        $newPath = 'someFolder';
        $this->assertTrue($this->folder->move($newPath), 'Did not return true on success');
        $this->assertEquals('/' . $newPath, $this->folder->getAbsolutePath());
        $found = false;
        foreach ($this->connection->moves as $move) {
            $found = $found || ($move['oldFile'] = '/' . $this->path && $move['newFile'] = '/' . $newPath);
        }
        $this->assertTrue($found, 'Could not find move');

    }

    public function testCopyFolderWillReturnNullOnCopyFile()
    {
        $this->connection->isFileReturn[] = '/' . $this->path;
        $newPath = 'someFolder';
        $this->assertNull($this->folder->copy($newPath), 'Did not return false on copy file');
    }

    public function testCopyFolderWillReturnNullOnNotExists()
    {
        $newPath = 'someFolder';
        $this->assertNull($this->folder->copy($newPath), 'Did not return false on copy none');
    }

    public function testCopyFolderWillCopyFolderIfFolder()
    {
        $this->connection->isDirectoryReturn[] = '/' . $this->path;

        $newPath = 'someFolder';
        $f = $this->folder->copy($newPath);
        $this->assertInstanceOf('Folder', $f, 'Did not return true on success');
        $this->assertEquals('/' . $this->path, $this->folder->getAbsolutePath());
        $this->assertEquals('/' . $newPath, $f->getAbsolutePath());
        $found = false;
        foreach ($this->connection->copies as $copy) {
            $found = $found || ($copy['oldFile'] = '/' . $this->path && $copy['newFile'] = '/' . $newPath);
        }
        $this->assertTrue($found, 'Could not find copy');

    }

    public function testGetParentFolderWillReturnNullOnRoot()
    {
        $folder = new ConnectionFolderImpl('/', $this->connection);
        $this->assertNull($folder->getParentFolder(), 'Did not return null on root');
    }

    public function testGetParentFolderWillReturnFolder()
    {
        $f = $this->folder->getParentFolder();
        $this->assertInstanceOf('Folder', $f);
        $this->assertEquals('', $f->getAbsolutePath());
    }

    public function testListDirectoryWillReturnFalseIfNotExists()
    {

        $this->assertFalse($this->folder->listFolder(), 'Did not return false on not found');

    }

    public function testListDirectoryWillReturnFalseIfFile()
    {
        $this->connection->isFileReturn[] = '/' . $this->path;
        $this->assertFalse($this->folder->listFolder(), 'Did not return false on file');
    }

    public function testListDirectoryWillReturnListIfDir()
    {
        $this->connection->isDirectoryReturn[] = '/' . $this->path;
        $l = $this->folder->listFolder();
        $this->assertTrue(is_array($l), 'Did not return array');
        $this->assertEquals(0, count($l), 'Length did not match');
    }

    public function testListDirectoryWillListDirectory()
    {
        $path = '/' . $this->path;
        $this->connection->isDirectoryReturn[] = $path;
        $folder = 'SomeFolder';
        $file = 'SomeFile';
        $this->connection->isDirectoryReturn[] = $path . '/' . $folder;
        $this->connection->isFileReturn[] = $path . '/' . $file;
        $this->connection->directoryList[] = $folder;
        $this->connection->directoryList[] = $file;

        $l = $this->folder->listFolder();
        $this->assertEquals(2, count($l), 'Wrong array size');
        $folderB = $fileB = false;
        foreach ($l as $e) {
            if ($e instanceof File) {
                $fileB = $e->getAbsoluteFilePath() == $path . '/' . $file && $e->exists();
            } else if ($e instanceof Folder) {
                $folderB = $e->getAbsolutePath() == $path . '/' . $folder && $e->exists();
            }
        }
        $this->assertTrue($fileB, 'File was not found');
        $this->assertTrue($folderB, 'Folder was not found');

        $l = $this->folder->listFolder(Folder::LIST_FOLDER_FILES);
        $this->assertGreaterThan(0, count($l));
        foreach($l as $f){
            $this->assertInstanceOf("File", $f);
        }


        $l = $this->folder->listFolder(Folder::LIST_FOLDER_FOLDERS);
        $this->assertGreaterThan(0, count($l));
        foreach($l as $f){
            $this->assertInstanceOf("Folder", $f);
        }


    }


    public function testDeleteWillReturnFalseIfNotExists()
    {

        $this->assertFalse($this->folder->delete(), 'Did not return false on not found');
        $this->assertFalse($this->folder->delete(Folder::DELETE_FOLDER_RECURSIVE), 'Did not return false on not found');

    }

    public function testDeleteWillReturnFalseIfFile()
    {
        $this->connection->isFileReturn[] = '/' . $this->path;
        $this->assertFalse($this->folder->delete(), 'Did not return false on file');
        $this->assertFalse($this->folder->delete(Folder::DELETE_FOLDER_RECURSIVE), 'Did not return false on file');
    }

    public function testDeleteWillReturnFalseIfNonEmptyAndNonRecursive()
    {
        $path = '/' . $this->path;
        $this->connection->isDirectoryReturn[] = $path;
        $folder = 'SomeFolder';
        $file = 'SomeFile';
        $this->connection->isDirectoryReturn[] = $path . '/' . $folder;
        $this->connection->isFileReturn[] = $path . '/' . $file;
        $this->connection->directoryList[] = $folder;
        $this->connection->directoryList[] = $file;

        $this->assertFalse($this->folder->delete(), 'Did not return false on non-empty delete');
    }

    public function testDeleteWillReturnTrueIfEmptyAndNonRecursive()
    {
        $path = '/' . $this->path;
        $this->connection->isDirectoryReturn[] = $path;

        $this->assertTrue($this->folder->delete(), 'Did return false on empty delete');

    }

    public function testDeleteWillReturnTrueIfNonEmptyAndNonRecursive()
    {
        $path = '/' . $this->path;
        $this->connection->isDirectoryReturn[] = $path;
        $folder = 'SomeFolder';
        $file = 'SomeFile';
        $this->connection->isDirectoryReturn[] = $path . '/' . $folder;
        $this->connection->isFileReturn[] = $path . '/' . $file;
        $this->connection->directoryList[] = $folder;
        $this->connection->directoryList[] = $file;

        $this->assertTrue($this->folder->delete(Folder::DELETE_FOLDER_RECURSIVE), 'Did not return false on non-empty delete');


        $this->assertEquals(3, count($this->connection->deleted));
        $this->assertTrue(array_search($path, $this->connection->deleted) !== false);
        $this->assertTrue(array_search($path . '/' . $folder, $this->connection->deleted) !== false);
        $this->assertTrue(array_search($path . '/' . $file, $this->connection->deleted) !== false);
    }

    public function testIteratorIsLikeList()
    {
        $path = '/' . $this->path;
        $this->connection->isDirectoryReturn[] = $path;
        $folder = 'SomeFolder';
        $file = 'SomeFile';
        $this->connection->isDirectoryReturn[] = $path . '/' . $folder;
        $this->connection->isFileReturn[] = $path . '/' . $file;
        $this->connection->directoryList[] = $folder;
        $this->connection->directoryList[] = $file;

        $list = $this->folder->listFolder();
        $this->folder->rewind();

        $size = 0;
        foreach ($this->folder as $elem) {
            $found = false;
            foreach ($list as $e) {
                $found = $found ||
                    ($elem instanceof File && $e instanceof File && $elem->getAbsoluteFilePath() == $e->getAbsoluteFilePath()) ||
                    ($elem instanceof Folder && $e instanceof Folder && $elem->getAbsolutePath() == $e->getAbsolutePath());

            }
            $this->assertTrue($found);
            $size++;
        }
        $this->assertEquals(count($list), $size);
    }


    public function testPutFileWillReturnNullIfError()
    {
        $file = new ConnectionFileImpl($this->path, $this->connection);
        $this->assertNull($this->folder->putFile($file), 'Did not return false on error');

    }

    public function testPutFileWillReturnNullIfDirDoesNotExist()
    {
        $f = new FileImpl(dirname(__FILE__).'/stubs/fileStub');
        $this->assertNull($this->folder->putFile($f));
    }

    public function testPutFileWillReturnNullIfFile()
    {
        $this->connection->isFileReturn[] = $this->folder->getAbsolutePath();
        $f = new FileImpl(dirname(__FILE__).'/stubs/fileStub');
        $this->assertNull($this->folder->putFile($f));
    }



    public function testPutFileWillPutFile()
    {
        $this->setUpFolder();
        $this->connection->isDirectoryReturn[] = $this->folder->getAbsolutePath();
        $f = $this->folder->putFile($this->testFile);
        $this->assertInstanceOf('File', $f);
        $this->assertEquals($this->testFolder->getAbsolutePath() . '/' . $this->testFile->getFilename(), $f->getAbsoluteFilePath(), 'Path did not match');
        $f = new FileImpl($f->getAbsoluteFilePath());
        $this->assertTrue($f->exists());
    }

    public function testPutFileWillChangeName()
    {
        $this->setUpFolder();
        $this->connection->isDirectoryReturn[] = $this->folder->getAbsolutePath();
        $newName = 'somethingDifferent';
        $f = $this->folder->putFile($this->testFile,$newName);
        $this->assertInstanceOf('File', $f);
        $this->assertEquals($this->testFolder->getAbsolutePath() . '/' . $newName, $f->getAbsoluteFilePath(), 'Path did not match');
        $f = new FileImpl($f->getAbsoluteFilePath());
        $this->assertTrue($f->exists());

    }


    public function testPutFileWillOverride(){
        $this->setUpFolder();
        $this->connection->isDirectoryReturn[] = $this->folder->getAbsolutePath();
        $this->folder->putFile($this->testFile);
        $f = $this->folder->putFile($this->testFile);
        $f = new FileImpl($f->getAbsoluteFilePath());
        $this->assertEquals($this->testFile->getContents(),$f->getContents(),'Contents did not match');
    }

    public function testPutFileWillReturnNullIfGivenFileDoesNotExist(){
        $this->setUpFolder();
        $this->connection->isDirectoryReturn[] = $this->folder->getAbsolutePath();
        $file = new ConnectionFileImpl($this->testFile->getAbsoluteFilePath(),$this->connection);
        $this->assertNull($this->folder->putFile($file));
    }

    public function testPutFolderWillReturnNullIfFolderDoesNotExist(){
        $this->setUpFolder();
        $this->assertNull($this->folder->putFolder($this->testFolder));
    }
    public function testPutFolderWillReturnNullIfFolderIsFile(){
        $this->setUpFolder();
        $this->connection->isFileReturn[] = $this->folder->getAbsolutePath();
        $this->assertNull($this->folder->putFolder($this->testFolder));
    }

    public function testPutFolderWillReturnNullIfGivenFolderDoesNotExist(){
        $this->setUpFolder();
        $this->connection->isDirectoryReturn[] = $this->folder->getAbsolutePath();
        $folder = new ConnectionFolderImpl($this->testFolder2->getAbsolutePath(),$this->connection);
        $this->assertNull($this->folder->putFolder($folder));
    }

    public function testPutFolderWillReturnNullOnError(){
        $this->assertNull($this->folder->putFolder($this->folder));
    }

    public function testPutFolderWillReturnFolderOnSuccess(){
        $this->setUpFolder();
        $this->connection->localCreate = true;
        $this->connection->isDirectoryReturn[] = $this->folder->getAbsolutePath();
        $this->connection->isDirectoryReturn[] = $this->folder->getAbsolutePath().'/'.$this->testFolder2->getName();
        $this->connection->directoryList[] = $this->testFile2->getFilename();
        $this->connection->isFileReturn[] = $this->folder->getAbsolutePath().'/'.$this->testFile2->getFilename();
        $this->connection->isFileReturn[] = $this->testFile2->getAbsoluteFilePath();
        $f = $this->folder->putFolder($this->testFolder2);
        $this->assertInstanceOf('Folder',$f);
        $this->assertEquals($this->folder->getAbsolutePath().'/'.$this->testFolder2->getName(),$f->getAbsolutePath());
        $f = new FolderImpl($f->getAbsolutePath());
        $this->assertTrue(is_array($f->listFolder()));
        $this->assertEquals(1,count($f->listFolder()));
        $f = array_pop($f->listFolder());
        /** @var $f File */
        $this->assertInstanceOf('File',$f);
        $f  = new FileImpl($f->getAbsoluteFilePath());
        $this->assertTrue($f->exists());
        $this->assertEquals($this->folder->getAbsolutePath().'/'.$this->testFolder2->getName().'/'.$this->testFile2->getFilename(),$f->getAbsoluteFilePath());
    }

    public function testPutFolderWillReturnFolderWithNewName(){
        $this->setUpFolder();
        $newName = 'somethingDifferent';
        $this->connection->localCreate = true;
        $this->connection->isDirectoryReturn[] = $this->folder->getAbsolutePath();
        $this->connection->isDirectoryReturn[] = $this->folder->getAbsolutePath().'/'.$newName;
        $this->connection->directoryList[] = $this->testFile2->getFilename();
        $this->connection->isFileReturn[] = $this->folder->getAbsolutePath().'/'.$this->testFile2->getFilename();
        $this->connection->isFileReturn[] = $this->testFile2->getAbsoluteFilePath();
        $f = $this->folder->putFolder($this->testFolder2,$newName);
        $this->assertInstanceOf('Folder',$f);
        $this->assertEquals($this->folder->getAbsolutePath().'/'.$newName,$f->getAbsolutePath());
        $f = new FolderImpl($f->getAbsolutePath());
        $this->assertTrue($f->exists());
    }

}
