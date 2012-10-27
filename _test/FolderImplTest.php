<?php
require_once dirname(__FILE__) . '/../_class/FolderImpl.php';
require_once dirname(__FILE__) . '/../_class/FileImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/27/12
 * Time: 1:38 PM
 * To change this template use File | Settings | File Templates.
 */
class FolderImplTest extends PHPUnit_Framework_TestCase
{

    public function rrmdir($dir){
        foreach(glob($dir . '/*') as $file) {
            if(is_dir($file))
                $this->rrmdir($file);
            else
                unlink($file);
        }
        rmdir($dir);
    }

    public function testFolderWillReturnCorrectAbsolutePath(){
        $f = new FolderImpl(dirname(__FILE__).'/_stub/../_stub');
        $this->assertEquals(dirname(__FILE__).'/_stub',$f->getAbsolutePath(),'Did not return right path');
    }

    public function testExistsWillReturnExistIfExist(){
        $f = new FolderImpl(dirname(__FILE__).'/_stub/');
        $this->assertTrue($f->exists(),'Did not return true on exist');
    }

    public function testExistsWillReturnFalseOnNotExist(){
        $f = new FolderImpl('nonExistingFolder/');
        $this->assertFalse($f->exists(),'Did not return false on not exist');
    }

    public function testExistsWillReturnFalseIfFile(){
        $f = new FolderImpl(dirname(__FILE__).'/_stub/fileStub');
        $this->assertFalse($f->exists(),'Did not return false on file');
    }

    public function testGetParentFolderWillReturnParentFolder(){
        $f = new FolderImpl(dirname(__FILE__).'/_stub/../_stub');
        $parentF = $f->getParentFolder();
        $this->assertNotNull($parentF,'Did return null');
        $this->assertEquals(dirname(__FILE__),$parentF->getAbsolutePath(),'Did not return right parent folder');
    }

    public function testGetParentFolderOfRootWillReturnNull(){
        $f = new FolderImpl('/');
        $parentF = $f->getParentFolder();
        $this->assertNull($parentF,'Did not return null on parent of root');
    }

    public function testGetNameWillReturnTheNameOfTheFolder(){
        $name = 'someFolder';
        $f = new FolderImpl(dirname(__FILE__).'/'.$name);
        $this->assertEquals($name,$f->getName(),'Did not return right name');
    }

    public function testGetRelativePathToWillReturnRelativePathToSubElement(){
        $f1 = new FolderImpl(dirname(__FILE__));
        $this->assertEquals('../',$f1->getRelativePathTo(dirname(__FILE__).'/_stub'),'Did not return right relative path');
    }

    public function testGetRelativePathToWillReturnRelativePathToSibling(){
        $f1 = new FolderImpl(dirname(__FILE__));
        $this->assertEquals('',$f1->getRelativePathTo(dirname(__FILE__)),'Did not return right relative path');
    }

    public function testGetRelativePathToWillReturnRelativePathToSuperElement(){
        $f1 = new FolderImpl(dirname(__FILE__));
        $this->assertEquals('_test',$f1->getRelativePathTo(dirname(__FILE__).'/..'),'Did not return right relative path');
    }

    public function testCreateWillCreateFolder(){
        $folder = dirname(__FILE__).'/_stub/testFolder';
        @$this->rrmdir($folder);
        $f = new FolderImpl($folder);
        $this->assertFalse($f->exists(),'Folder did exist');
        $this->assertTrue($f->create(),'Did not return TRUE on create');
        $this->assertTrue($f->exists(),'Folder was not created');
    }

    public function testCreateWillReturnFalseIfFolderExist(){
        $folder = dirname(__FILE__).'/_stub/testFolder';
        @mkdir($folder);
        $f = new FolderImpl($folder);
        $this->assertFalse($f->create(),'Did not return false on folder exists');
    }

    public function testCreateWillReturnFalseIfFileExists(){
        $folder = dirname(__FILE__).'/_stub/fileStub';
        $f = new FolderImpl($folder);
        $this->assertFalse($f->create(),'Did not return false on file exists');

    }

    public function testDeleteWillDeleteFolder(){
        $folder = dirname(__FILE__).'/_stub/testFolder';
        $f = new FolderImpl($folder);
        $f->create();
        $this->assertTrue($f->delete(),'Did not return true on deletion');
        $this->assertFalse($f->exists(),'Folder was not deleted');
    }

    public function testDeleteWillReturnFalseOnNonExistingFolder(){
        $folder = dirname(__FILE__).'/_stub/nonExistingFolder';
        $f = new FolderImpl($folder);
        $this->assertFalse($f->exists(),'Folder does exist');
        $this->assertFalse($f->delete(),'Did not return false on folder not existing');
    }

    public function testDeleteOnFolderBeingAFileWillReturnFalse(){
        $folder = dirname(__FILE__).'/_stub/fileStub';
        $f = new FolderImpl($folder);
        $file = new FileImpl($folder);
        $this->assertTrue($file->fileExists(),'File did not exist');
        $this->assertFalse($f->delete(),'Did not return false on folder not existing');
        $this->assertTrue($file->fileExists(),'File was deleted');
    }

    public function testDeleteNonEmptyFolderWillReturnFalse(){
        $folder = dirname(__FILE__).'/_stub/testFolder';
        $f = $this->setUpNonEmptyFolder($folder);
        $this->assertFalse($f->delete(),'Did not return false on deletion of non empty folder');
        $this->assertTrue($f->exists(),'Folder was deleted');
    }

    public function testDeleteNonEmptyFolderWillReturnTrueAndDeleteWithRecursiveArgument(){
        
    }

    public function testListDirectoryWillReturnFalseOnFolderNotExisting(){
        $folder = dirname(__FILE__).'/_stub/nonExistingFolder';
        $f = new FolderImpl($folder);
        $this->assertFalse($f->exists(),'Folder does exist');
        $this->assertFalse($f->listFolder(),'Did not return false on non existing folder');
    }

    public function testListDirectoryWillReturnFalseOnFolderBeingFile(){
        $folder = dirname(__FILE__).'/_stub/fileStub';
        $f = new FolderImpl($folder);
        $this->assertFalse($f->listFolder(),'Did not return false on folder being file');

    }

    public function testListDirectoryWillReturnArrayOfRightSize(){
        $folder = dirname(__FILE__).'/_stub/';
        $f = new FolderImpl($folder);
        $this->assertTrue(is_array($f->listFolder()),'Did not return array');
        $this->assertEquals(count(scandir($folder))-2,count($f->listFolder()),'Array length did not match');
    }

    public function testListDirectoryWillReturnArrayOfFileAndFolderInstances(){
        $folder = dirname(__FILE__).'/_stub/testFolder';
        $f = $this->setUpNonEmptyFolder($folder);
        foreach($f->listFolder() as $key=>$e){
            switch($key){
                case 0:
                    /** @var $e File */
                    $this->assertInstanceOf('File',$e);
                    $this->assertEquals('1',$e->getFileName());
                    break;
                case 1:
                    /** @var $e File */
                    $this->assertInstanceOf('File',$e);
                    $this->assertEquals('2',$e->getFileName());
                    break;
                case 2:
                    /** @var $e Folder */
                    $this->assertInstanceOf('Folder',$e);
                    $this->assertEquals('3',$e->getName());
            }
        }
        $this->rrmdir($folder);

    }

    private function setUpNonEmptyFolder($folder)
    {
        $f = new FolderImpl($folder);
        $f->create();
        $this->assertTrue($f->exists(),'Folder does not exist');
        $file1 = new FileImpl($folder.'/1');
        $file1->write('1');
        $file2 = new FileImpl($folder.'/2');
        $file2->write('2');
        $folder1 = new FolderImpl($folder.'/3');
        $folder1->create();
        return $f;
    }

}
