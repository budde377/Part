<?php
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\util\file\FolderImpl;
use ChristianBudde\cbweb\util\file\FileImpl;
use ChristianBudde\cbweb\util\file\Folder;
use ChristianBudde\cbweb\util\file\File;
use ChristianBudde\cbweb\util\traits\FilePathTrait;
use PHPUnit_Framework_TestCase;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/27/12
 * Time: 1:38 PM
 * To change this template use File | Settings | File Templates.
 */
class FolderImplTest extends PHPUnit_Framework_TestCase
{

    use FilePathTrait;

    public function rrmdir($dir)
    {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file))
                $this->rrmdir($file);
            else
                unlink($file);
        }
        rmdir($dir);
    }

    public function testFolderWillReturnCorrectAbsolutePath()
    {
        $f = new FolderImpl(dirname(__FILE__) . '/../stubs/../stubs');
        $this->assertEquals($this->relativeToAbsolute(dirname(__FILE__) . '/../stubs'), $f->getAbsolutePath(), 'Did not return right path');
    }

    public function testExistsWillReturnExistIfExist()
    {
        $f = new FolderImpl(dirname(__FILE__) . '/../stubs/');
        $this->assertTrue($f->exists(), 'Did not return true on exist');
    }

    public function testExistsWillReturnFalseOnNotExist()
    {
        $f = new FolderImpl('nonExistingFolder/');
        $this->assertFalse($f->exists(), 'Did not return false on not exist');
    }

    public function testExistsWillReturnFalseIfFile()
    {
        $f = new FolderImpl(dirname(__FILE__) . '/../stubs/fileStub');
        $this->assertFalse($f->exists(), 'Did not return false on file');
    }

    public function testGetParentFolderWillReturnParentFolder()
    {
        $f = new FolderImpl(dirname(__FILE__) . '/../stubs/../stubs');
        $parentF = $f->getParentFolder();
        $this->assertNotNull($parentF, 'Did return null');
        $this->assertEquals($this->relativeToAbsolute(dirname(__FILE__)."/../"), $parentF->getAbsolutePath(), 'Did not return right parent folder');
    }

    public function testGetParentFolderOfRootWillReturnNull()
    {
        $f = new FolderImpl('/');
        $parentF = $f->getParentFolder();
        $this->assertNull($parentF, 'Did not return null on parent of root');
    }

    public function testGetNameWillReturnTheNameOfTheFolder()
    {
        $name = 'someFolder';
        $f = new FolderImpl(dirname(__FILE__) . '/' . $name);
        $this->assertEquals($name, $f->getName(), 'Did not return right name');
    }

    public function testGetRelativePathToWillReturnRelativePathToSubElement()
    {
        $f1 = new FolderImpl(dirname(__FILE__));
        $this->assertEquals('../lib', $f1->getRelativePathTo(dirname(__FILE__) . '/../stubs/'), 'Did not return right relative path');
    }

    public function testGetRelativePathToWillReturnRelativePathToSibling()
    {
        $f1 = new FolderImpl(dirname(__FILE__));
        $this->assertEquals('', $f1->getRelativePathTo(dirname(__FILE__)), 'Did not return right relative path');
    }

    public function testGetRelativePathToWillReturnRelativePathToSuperElement()
    {
        $f1 = new FolderImpl(dirname(__FILE__));
        $this->assertEquals('lib', $f1->getRelativePathTo(dirname(__FILE__) . '/..'), 'Did not return right relative path');
    }

    public function testCreateWillCreateFolder()
    {
        $folder = dirname(__FILE__) . '/../stubs/testFolder';
        @$this->rrmdir($folder);
        $f = new FolderImpl($folder);
        $this->assertFalse($f->exists(), 'Folder did exist');
        $this->assertTrue($f->create(), 'Did not return TRUE on create');
        $this->assertTrue($f->exists(), 'Folder was not created');
    }

    public function testCreateRecursiveWillCreateFolderRecursive()
    {
        $folder = dirname(__FILE__) . '/../stubs/testFolder';
        @$this->rrmdir($folder);
        $f = new FolderImpl($folder . '/test');
        $this->assertFalse($f->exists(), 'Folder did exist');
        $this->assertTrue($f->create(true), 'Did not return TRUE on create');
        $this->assertTrue($f->exists(), 'Folder was not created');
    }

    public function testCreateWillReturnFalseIfFolderExist()
    {
        $folder = dirname(__FILE__) . '/../stubs/testFolder';
        @mkdir($folder);
        $f = new FolderImpl($folder);
        $this->assertFalse($f->create(), 'Did not return false on folder exists');
    }

    public function testCreateWillReturnFalseIfFileExists()
    {
        $folder = dirname(__FILE__) . '/../stubs/fileStub';
        $f = new FolderImpl($folder);
        $this->assertFalse($f->create(), 'Did not return false on file exists');

    }

    public function testDeleteWillDeleteFolder()
    {
        $folder = dirname(__FILE__) . '/../stubs/testFolder';
        @$this->rrmdir($folder);
        $f = new FolderImpl($folder);
        $f->create();
        $this->assertTrue($f->delete(), 'Did not return true on deletion');
        $this->assertFalse($f->exists(), 'Folder was not deleted');
    }

    public function testDeleteWillReturnFalseOnNonExistingFolder()
    {
        $folder = dirname(__FILE__) . '/../stubs/nonExistingFolder';
        $f = new FolderImpl($folder);
        $this->assertFalse($f->exists(), 'Folder does exist');
        $this->assertFalse($f->delete(), 'Did not return false on folder not existing');
    }

    public function testDeleteOnFolderBeingAFileWillReturnFalse()
    {
        $folder = dirname(__FILE__) . '/../stubs/fileStub';
        $f = new FolderImpl($folder);
        $file = new FileImpl($folder);
        $this->assertTrue($file->exists(), 'File did not exist');
        $this->assertFalse($f->delete(), 'Did not return false on folder not existing');
        $this->assertTrue($file->exists(), 'File was deleted');
    }

    public function testDeleteNonEmptyFolderWillReturnFalse()
    {
        $folder = dirname(__FILE__) . '/../stubs/testFolder';
        $f = $this->setUpNonEmptyFolder($folder);
        $this->assertFalse($f->delete(), 'Did not return false on deletion of non empty folder');
        $this->assertTrue($f->exists(), 'Folder was deleted');
    }

    public function testDeleteNonEmptyFolderWillReturnTrueAndDeleteWithRecursiveArgument()
    {
        $folder = dirname(__FILE__) . '/../stubs/testFolder';
        $f = $this->setUpNonEmptyFolder($folder);
        $this->assertTrue($f->delete(Folder::DELETE_FOLDER_RECURSIVE), 'Did not return true on deletion of non empty folder');
        $this->assertFalse($f->exists(), 'Folder was not deleted');
    }

    public function testListDirectoryWillReturnFalseOnFolderNotExisting()
    {
        $folder = dirname(__FILE__) . '/../stubs/nonExistingFolder';
        $f = new FolderImpl($folder);
        $this->assertFalse($f->exists(), 'Folder does exist');
        $this->assertFalse($f->listFolder(), 'Did not return false on non existing folder');
    }

    public function testListDirectoryWillReturnFalseOnFolderBeingFile()
    {
        $folder = dirname(__FILE__) . '/../stubs/fileStub';
        $f = new FolderImpl($folder);
        $this->assertFalse($f->listFolder(), 'Did not return false on folder being file');

    }

    public function testListDirectoryWillReturnArrayOfRightSize()
    {
        $folder = dirname(__FILE__) . '/../stubs/';
        $f = new FolderImpl($folder);
        $this->assertTrue(is_array($f->listFolder()), 'Did not return array');
        $this->assertEquals(count(scandir($folder)) - 2, count($f->listFolder()), 'Array length did not match');
    }

    public function testListDirectoryWillReturnArrayOfFileAndFolderInstances()
    {
        $folder = dirname(__FILE__) . '/../stubs/testFolder';
        $fol = $this->setUpNonEmptyFolder($folder);
        $l = $fol->listFolder();
        /** @var $e \ChristianBudde\cbweb\util\file\File */
        $e = $l[0];
        $this->assertInstanceOf('ChristianBudde\cbweb\util\file\File', $e);
        $this->assertEquals('1', $e->getFilename());
        /** @var $e File */
        $e = $l[1];
        $this->assertInstanceOf('ChristianBudde\cbweb\util\file\File', $e);
        $this->assertEquals('2', $e->getFilename());
        /** @var $e \ChristianBudde\cbweb\util\file\Folder */
        $e = $l[2];
        $this->assertInstanceOf('ChristianBudde\cbweb\util\file\Folder', $e);
        $this->assertEquals('3', $e->getName());

        $l = $fol->listFolder(Folder::LIST_FOLDER_FILES);
        $this->assertGreaterThan(0, count($l));
        foreach ($l as $f) {
            $this->assertInstanceOf("ChristianBudde\\cbweb\\util\\file\\File", $f);
        }

        $l = $fol->listFolder(Folder::LIST_FOLDER_FOLDERS);
        $this->assertGreaterThan(0, count($l));
        foreach ($l as $f) {
            $this->assertInstanceOf("ChristianBudde\\cbweb\\util\\file\\Folder", $f);
        }


        $this->rrmdir($folder);

    }

    public function testMoveFolderWillMoveFolder()
    {
        $folder = $this->relativeToAbsolute(dirname(__FILE__) . '/../stubs/testFolder');
        @$this->rrmdir($folder);
        @$this->rrmdir($folder . '2');
        $f = $this->setUpNonEmptyFolder($folder);
        $this->assertTrue($f->move($folder . '2'), 'Folder move did not return true');
        $this->assertEquals($folder . '2', $f->getAbsolutePath());
        $this->assertTrue($f->exists(), 'Folder was not moved');
    }

    public function testMoveFolderWillReturnFalseIfDestinationExists()
    {
        $folder = dirname(__FILE__) . '/../stubs/testFolder';
        $folder2 = dirname(__FILE__) . '/../stubs/testFolder2';
        @$this->rrmdir($folder);
        @$this->rrmdir($folder . '2');
        $f = $this->setUpNonEmptyFolder($folder);
        $f2 = $this->setUpNonEmptyFolder($folder2);
        $this->assertFalse($f->move($folder2), 'Folder move did not return false');
        $this->assertTrue($f->exists(), 'Folder was moved');
        $this->assertTrue($f2->exists(), 'Folder was moved');
    }

    public function testMoveFolderWillReturnFalseOnNonExistingFolder()
    {
        $folder = dirname(__FILE__) . '/../stubs/nonExistingFolder';

        $f = new FolderImpl($folder);
        $this->assertFalse($f->move($folder . '2'));
    }

    public function testMoveFolderWillReturnFalseOnFolderBeingAFile()
    {
        $folder = dirname(__FILE__) . '/../stubs/fileStub';

        $f = new FolderImpl($folder);
        $this->assertFalse($f->move($folder . '2'));
    }

    public function testMoveFolderWillReturnTrueDestinationBeingOrigin()
    {
        $folder = dirname(__FILE__) . '/../stubs/testFolder';
        @$this->rrmdir($folder);
        $f = $this->setUpNonEmptyFolder($folder);
        $this->assertTrue($f->move($folder));
    }

    public function testCopyFolderWillCopyFolder()
    {
        $folder = $this->relativeToAbsolute(dirname(__FILE__) . '/../stubs/testFolder');
        @$this->rrmdir($folder);
        @$this->rrmdir($folder . '2');
        $f = $this->setUpNonEmptyFolder($folder);
        $f2 = $f->copy($folder . '2');
        $this->assertInstanceOf('ChristianBudde\cbweb\util\file\Folder', $f2, 'Did not return instance of Folder');
        /** @var $f2  Folder */
        $this->assertEquals($folder . '2', $f2->getAbsolutePath());
        $this->assertTrue($f->exists(), 'Folder was moved');
        $this->assertEquals(count($f->listFolder()), count($f2->listFolder()), 'Folder length did not match');
    }

    public function testCopyFolderWillReturnNullIfDestinationExists()
    {
        $folder = dirname(__FILE__) . '/../stubs/testFolder';
        $folder2 = dirname(__FILE__) . '/../stubs/testFolder2';
        @$this->rrmdir($folder);
        @$this->rrmdir($folder . '2');
        $f = $this->setUpNonEmptyFolder($folder);
        $f2 = $this->setUpNonEmptyFolder($folder2);
        $this->assertNull($f->copy($folder2), 'Did not return null');
        $this->assertTrue($f->exists(), 'Folder was moved');
        $this->assertTrue($f2->exists(), 'Folder was moved');
    }

    public function testCopyFolderWillReturnFalseOnNonExistingFolder()
    {
        $folder = dirname(__FILE__) . '/../stubs/nonExistingFolder';

        $f = new FolderImpl($folder);
        $this->assertNull($f->copy($folder . '2'));
    }

    public function testCopyFolderWillReturnFalseOnFolderBeingAFile()
    {
        $folder = dirname(__FILE__) . '/../stubs/fileStub';

        $f = new FolderImpl($folder);
        $this->assertNull($f->copy($folder . '2'));
    }

    public function testCopyFolderWillReturnFileIfDestinationBeingOrigin()
    {
        $folder = dirname(__FILE__) . '/../stubs/testFolder';
        @$this->rrmdir($folder);
        $f = $this->setUpNonEmptyFolder($folder);
        $f2 = $f->copy($folder);
        $this->assertInstanceOf('ChristianBudde\cbweb\util\file\Folder', $f2);
        $this->assertEquals($f->getAbsolutePath(), $f2->getAbsolutePath());

    }

    private function nop()
    {

    }

    public function testIteratingWillMatchList()
    {
        $folder = dirname(__FILE__) . '/../stubs/testFolder';
        @$this->rrmdir($folder);
        $f = $this->setUpNonEmptyFolder($folder);
        $count = 0;
        foreach ($f as $val) {
            $this->nop($val);

            $count++;
        }


        $this->assertEquals(3, $count, 'Was not of right size');
        $f->rewind();
        foreach ($f->listFolder() as $k => $v) {
            $this->assertEquals($f->key(), $k, 'Keys did not match');
            $this->assertEquals($v, $f->current());
            $f->next();
        }


    }


    public function testCleanWillCleanFolder()
    {
        $folder = dirname(__FILE__) . '/../stubs/testFolder';
        @$this->rrmdir($folder);
        $f = $this->setUpNonEmptyFolder($folder);
        $this->assertGreaterThan(0, count($f->listFolder()));
        $f->clean();
        $this->assertEquals(0, count($f->listFolder()));

    }

    public function testPutFileWillReturnNullIfDirDoesNotExist()
    {
        $file = new FileImpl(dirname(__FILE__) . '/../stubs/fileStub');
        $folder = new FolderImpl(dirname(__FILE__) . "/../stubs/nonExistingFolder");
        $this->assertNull($folder->putFile($file));
    }

    public function testPutFileWillReturnNullIfFile()
    {
        $file = new FileImpl(dirname(__FILE__) . '/../stubs/fileStub');
        $folder = new FolderImpl(dirname(__FILE__) . "/../stubs/fileStub");
        $this->assertNull($folder->putFile($file));

    }


    public function testPutFileWillPutFile()
    {
        $folder = $this->setUpNonEmptyFolder(dirname(__FILE__) . '/../stubs/testFolder');
        $file = new FileImpl(dirname(__FILE__) . '/../stubs/fileStub');
        $newFile = $folder->putFile($file);
        $this->assertEquals(0, strpos($folder->getAbsolutePath(), $newFile->getAbsoluteFilePath()));
        $this->assertTrue($newFile->exists());
        $this->assertEquals($file->getFilename(), $newFile->getFilename());
    }

    public function testPutFileWillChangeName()
    {
        $folder = $this->setUpNonEmptyFolder(dirname(__FILE__) . '/../stubs/testFolder');
        $file = new FileImpl(dirname(__FILE__) . '/../stubs/fileStub');
        $newName = "newFileStub";
        $newFile = $folder->putFile($file, $newName);
        $this->assertEquals(0, strpos($folder->getAbsolutePath(), $newFile->getAbsoluteFilePath()));
        $this->assertTrue($newFile->exists());
        $this->assertEquals($newName, $newFile->getFilename());

    }

    public function testPutFileWillOverride()
    {
        $folder = $this->setUpNonEmptyFolder(dirname(__FILE__) . '/../stubs/testFolder');
        $file = new FileImpl(dirname(__FILE__) . '/../stubs/fileStub');
        $newName = "1";
        $newFile = $folder->putFile($file, $newName);
        $this->assertTrue($newFile->exists());
        $this->assertEquals($newName, $newFile->getFilename());
        $c = $newFile->getContents();
        $this->assertNotEquals("1", $c);
    }

    public function testPutFileWillReturnNullIfGivenFileDoesNotExist()
    {
        $folder = $this->setUpNonEmptyFolder(dirname(__FILE__) . '/../stubs/testFolder');
        $file = new FileImpl(dirname(__FILE__) . '/../stubs/NonExistingFile');
        $this->assertNull($folder->putFile($file));
    }


    private function setUpNonEmptyFolder($folder)
    {
        @$this->rrmdir($folder);
        $f = new FolderImpl($folder);
        $f->create();
        $this->assertTrue($f->exists(), 'Folder does not exist');
        $file1 = new FileImpl($folder . '/1');
        $file1->write('1');
        $file1->setAccessMode(File::FILE_MODE_RW_POINTER_AT_END);
        $file2 = new FileImpl($folder . '/2');
        $file2->write('2');
        $folder1 = new FolderImpl($folder . '/3');
        $folder1->create();
        return $f;
    }

    public function tearDown()
    {
        @$this->rrmdir(dirname(__FILE__) . '/../stubs/testFolder');
        @$this->rrmdir(dirname(__FILE__) . '/../stubs/testFolder2');

    }


}