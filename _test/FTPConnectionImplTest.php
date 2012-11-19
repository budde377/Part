<?php
require_once dirname(__FILE__).'/../_class/FTPConnectionImpl.php';
require_once dirname(__FILE__).'/../_class/FileImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 28/10/12
 * Time: 15:28
 */
class FTPConnectionImplTest extends PHPUnit_Framework_TestCase
{
    private static $user = 'testcms2012';
    private static $password = 'T73LWgPeOwLlxh';
    private static $host = 'christian-budde.dk';
    private static $port = 21;
    private static $path = '/home/testcms2012/test';

    /** @var $connection FTPConnectionImpl */
    private $connection;

    public function deleteContent(){
        $remoteFolder = self::$path.'/newFolder';
        $remoteFolder2 = self::$path.'/newFolder2';
        $this->connection->deleteFile( self::$path.'/newFile2');
        $this->connection->deleteFile( self::$path.'/newFile');
        $this->connection->deleteFile($remoteFolder.'/newFile');
        $this->connection->deleteFile($remoteFolder2.'/newFile');
        $this->connection->deleteDirectory($remoteFolder);
        $this->connection->deleteDirectory($remoteFolder2);
    }

    public function setUp(){
        $this->connection = new FTPConnectionImpl(self::$host,self::$port);
    }

    public function setUpLogin(){
        $this->assertTrue($this->connection->connect(),'Could not connect');
        $this->assertTrue($this->connection->login(self::$user,self::$password),'Could not login');

    }


    public function testConnectReturnTrueIfValidHostAndPort(){
        $this->assertTrue($this->connection->connect(),'Did not return true on correct host and port');
    }

    public function testConnectionWillReturnFalseIfInvalidHostAndPort(){
        $this->connection = new FTPConnectionImpl('notAValidHost',79877987879797879897879);
        $this->assertFalse($this->connection->connect(),'Did not return false on invalid host/port');
    }


    public function testLoginWillReturnTrueWhenSuccessfulLogin(){
        $this->assertTrue($this->connection->connect(),'Could not connect');
        $this->assertTrue($this->connection->login(self::$user,self::$password),'Did not return true on successfully login');

    }

    public function testLoginWillReturnFalseIfLoginFailure(){
        $this->assertFalse($this->connection->login('asdasdasdasd','ipiopi124jkllæasd'));
    }

    public function testIsConnectedReturnFalseIfNotConnected(){
        $this->assertFalse($this->connection->isConnected(),'Did not return false when not connected');
    }

    public function testIsConnectedReturnFalseIfNotLoggedIn(){
        $this->assertTrue($this->connection->connect(),'Could not connect');
        $this->assertFalse($this->connection->isConnected(),'Did not return false when not loggedin');
    }

    public function testIsConnectedReturnTrueIfConnected(){
        $this->setUpLogin();
        $this->assertTrue($this->connection->isConnected(),'Did not return true when connected');
    }

    public function testCloseConnectionWillCloseConnection(){
        $this->setUpLogin();
        $this->assertTrue($this->connection->isConnected(),'Was not connected');
        $this->assertTrue($this->connection->close(),'Did not return true');
        $this->assertFalse($this->connection->isConnected(),'Did not disconnect');
    }

    public function testCloseWillReturnFalseIfNotConnected(){
        $this->assertFalse($this->connection->close(),'Did not return false');
    }

    public function testExecuteWillExecuteCommandAndReturnResult(){
        $this->setUpLogin();
        $ret = $this->connection->exec("HELP");
        $this->assertTrue(is_array($ret),'Did not return an array');
        $this->assertGreaterThan(0,count($ret),'Did return empty array');
    }

    public function testExecuteWillReturnFalseIfNotConnected(){
        $this->assertFalse($this->connection->exec('HELP'),'Did not return false');
    }

    public function testListDirectoryWillReturnArrayWhenFailure(){
        $this->assertFalse($this->connection->listDirectory(self::$path),'Did not return an array');
    }

    public function testListDirectoryWillReturnArrayWhenConnected(){
        $this->setUpLogin();
        $ret = $this->connection->listDirectory(self::$path);
        $this->assertTrue(is_array($ret),'Did not return an array');
    }

    public function testListDirectoryWillReturnNonEmptyArrayWithRightContent(){
        $this->setUpLogin();
        $ret = $this->connection->listDirectory(self::$path);
        $this->assertGreaterThan(0,count($ret),'Did not return array of right size');
        $foundElement = false;
        foreach($ret as $val){
            $foundElement = $foundElement || $val == 'rootFile';
        }
        $this->assertTrue($foundElement,'Did not contain element');
     }

    public function testExistsReturnTrueIfFileExist(){
        $this->setUpLogin();
        $this->assertTrue($this->connection->exists(self::$path.'/rootFile'),'Did not return true when file exist');
    }

    public function testExistsReturnFalseIfFileDoesNotExist(){
        $this->setUpLogin();
        $this->assertFalse($this->connection->exists(self::$path.'/not A Real File'),'Did not return false when file not exist');
    }

    public function testExistReturnFalseOnFailure(){
        $this->assertFalse($this->connection->exists(self::$path.'/rootFile'),'Did not return false on failure');
    }

    public function testIsFileWillReturnTrueIfFile(){
        $this->setUpLogin();
        $this->assertTrue($this->connection->isFile(self::$path.'/rootFile'),'Did not return true when file');
    }

    public function testIsFileReturnFalseIfNotFile(){
        $this->setUpLogin();
        $this->assertFalse($this->connection->isFile(self::$path),'Did not return false when folder');
    }

    public function testIsFileReturnFalseWhenNotConnected(){
        $this->assertFalse($this->connection->isFile(self::$path),'Did not return false when not connected');

    }

    public function testIsFileReturnFalseWhenNotExist(){
        $this->setUpLogin();
        $this->assertFalse($this->connection->isFile(self::$path.'/not a real file'),'Did not return false when not a real file');
    }

    public function testIsDirectoryWillReturnTrueIfFolder(){
        $this->setUpLogin();
        $this->assertTrue($this->connection->isDirectory(self::$path),'Did not return true when dir');
    }

    public function testIsDirectoryWillReturnFalseIfFile(){
        $this->setUpLogin();
        $this->assertFalse($this->connection->isDirectory(self::$path.'/rootFile'),'Did not return false when file');
    }

    public function testIsDirectoryWillReturnFalseWhenNotConnected(){
        $this->assertFalse($this->connection->isDirectory(self::$path),'Did not return false when not connected');
    }

    public function testIsDirectoryWillReturnFalseWhenNotExist(){
        $this->setUpLogin();
        $this->assertFalse($this->connection->isDirectory(self::$path.'/not a real folder'),'Did not return false when not a real folder');
    }

    public function testCreateDirectoryWillCreateDirectory(){
        $this->setUpLogin();
        $dir = 'some Dir';
        $this->connection->deleteDirectory(self::$path.'/'.$dir);
        $this->assertFalse($this->connection->exists(self::$path.'/'.$dir),'Directory did exist');
        $this->assertTrue($this->connection->createDirectory(self::$path.'/'.$dir),'Did not return true');
        $this->assertTrue($this->connection->exists(self::$path.'/'.$dir),'Directory was not created');
    }

    public function testDeleteDirectoryWillDeleteDirectory(){
        $this->setUpLogin();
        $dir = 'some Dir';
        $this->connection->createDirectory(self::$path.'/'.$dir);
        $this->assertTrue($this->connection->exists(self::$path.'/'.$dir),'Directory was did not exists');
        $this->assertTrue($this->connection->deleteDirectory(self::$path.'/'.$dir),'Did not return true');
        $this->assertFalse($this->connection->exists(self::$path.'/'.$dir),'Directory was not deleted');
    }

    public function testCreateWillReturnFalseOnNotConnected(){
        $dir = self::$path.'/some Dir';
        $this->assertFalse($this->connection->createDirectory($dir),'Did not return false');
    }

    public function testCreateWillReturnFalseOnFolderExists(){
        $this->setUpLogin();
        $dir = self::$path.'/some Dir';
        $this->connection->createDirectory($dir);
        $this->assertFalse($this->connection->createDirectory($dir),'Did not return false');

    }

    public function testDeleteDirectoryWillReturnFalseOnFolderNotExists(){
        $this->setUpLogin();
        $dir = self::$path.'/some Dir';
        $this->connection->deleteDirectory($dir);
        $this->assertFalse($this->connection->deleteDirectory($dir),'Did not return false');

    }

    public function testDeleteDirectoryWillReturnFalseIfNoConnection(){
        $dir = self::$path.'/some Dir';
        $this->assertFalse($this->connection->deleteDirectory($dir),'Did not return false');
    }

    public function testDeleteFileWillReturnFalseOnNoConnection(){
        $file = self::$path.'/testFile';
        $localFile = dirname(__FILE__).'/_stub/fileStub';
        $this->connection->put($localFile,$file);
        $this->assertFalse($this->connection->deleteFile($file),'Did not return false on delete failure');

    }

    public function testDeleteFileWillReturnFalseIfFileNotFound(){
        $this->setUpLogin();
        $file = self::$path.'/nonExistingFile';
        $this->assertFalse($this->connection->deleteFile($file),'Did not return false on delete failure');
    }

    public function testDeleteFileWillDeleteFileAndReturnTrue(){
        $this->setUpLogin();
        $file = self::$path.'/newFile';
        $this->assertTrue($this->connection->put(dirname(__FILE__).'/_stub/fileStub',$file),'Could not put file');
        $this->assertTrue($this->connection->deleteFile($file),'Could Not delete File');
        $this->assertFalse($this->connection->exists($file),'File was not deleted');

    }

    public function testPutFileWillPutFile(){
        $this->setUpLogin();
        $localFile = dirname(__FILE__).'/_stub/fileStub';
        $remoteFile = self::$path.'/newFile';
        $this->connection->deleteFile($remoteFile);
        $this->assertTrue($this->connection->put($localFile,$remoteFile),'Could not put file');
        $this->assertTrue($this->connection->exists($remoteFile),'Did not exists');
    }

    public function testPutReturnFalseWhenNotConnected(){
        $localFile = dirname(__FILE__).'/_stub/fileStub';
        $remoteFile = self::$path.'/newFile';
        $this->connection->deleteFile($remoteFile);
        $this->assertFalse($this->connection->put($localFile,$remoteFile),'Did not return false on no connection');

    }

    public function testPutReturnFalseWhenLocalFileNotFound(){
        $this->setUpLogin();
        $localFile = dirname(__FILE__).'/_stub/nonExistingFile';
        $remoteFile = self::$path.'/newFile';
        $this->connection->deleteFile($remoteFile);
        $this->assertFalse($this->connection->put($localFile,$remoteFile),'Did not return false on no connection');

    }

    public function testGetPullFileFromServer(){
        $this->setUpLogin();
        $localFile = dirname(__FILE__).'/_stub/fileStub';
        $remoteFile = self::$path.'/newFile';
        $this->connection->put($localFile,$remoteFile);
        $this->assertTrue($this->connection->exists($remoteFile),'File was not put');
        $newLocalFile = dirname(__FILE__).'/_stub/newFileStub';
        $this->assertTrue($this->connection->get($newLocalFile,$remoteFile),'Did not return true on get');
        $file = new FileImpl($newLocalFile);
        $this->assertTrue($file->exists(),'File was not get');
        $this->connection->deleteFile($remoteFile);
        $file->delete();
    }

    public function testGetReturnFalseOnNoConnection(){
        $this->setUpLogin();
        $localFile = dirname(__FILE__).'/_stub/fileStub';
        $remoteFile = self::$path.'/newFile';
        $this->connection->put($localFile,$remoteFile);
        $this->assertTrue($this->connection->exists($remoteFile),'File was not put');
        $this->connection->close();
        $newLocalFile = dirname(__FILE__).'/_stub/newFileStub';
        $this->assertFalse($this->connection->get($newLocalFile,$remoteFile),'Did not return false on no connection');
    }

    public function testGetReturnFalseOnNoRemoteFile(){
        $this->setUpLogin();
        $remoteFile = self::$path.'/newFile';
        $this->connection->deleteFile($remoteFile);
        $localFile = dirname(__FILE__).'/_stub/newFileStub';
        $this->assertFalse($this->connection->get($localFile,$remoteFile),'Did not return false on no file');
    }

    public function testDeleteFolderFailsWithNonEmptyFolder(){
        $this->setUpLogin();
        $folder = self::$path.'/newFolder';
        $remoteFile = $folder.'/newFile';
        $localFile = dirname(__FILE__).'/_stub/fileStub';
        $this->connection->createDirectory($folder);
        $this->connection->put($localFile,$remoteFile);
        $this->assertFalse($this->connection->deleteDirectory($remoteFile),'Did return true on non-empty folder delete');
    }

    public function testGetFolderWillReturnFalse(){
        $this->setUpLogin();
        $folder = self::$path.'/newFolder';
        $remoteFile = $folder.'/newFile';
        $localFile = dirname(__FILE__).'/_stub/fileStub';
        $localFolder = dirname(__FILE__).'/_stub/folderStub';
        $this->connection->createDirectory($folder);
        $this->connection->put($localFile,$remoteFile);
        $f = new FolderImpl($localFolder);
        $f->delete(Folder::DELETE_FOLDER_RECURSIVE);
        $this->assertFalse($this->connection->get($localFolder,$folder),'Did not return true');
        $this->assertFalse($f->exists(),'Folder was created');
    }

    public function testPutFolderWillReturnFalse(){
        $this->setUpLogin();
        $localFolder = dirname(__FILE__).'/_stub/folderStub';
        $folder = self::$path.'/newFolder';
        $f = new FolderImpl($localFolder);
        $f->create();
        $this->assertFalse($this->connection->put($localFolder,$folder),'Did not return false');
        $f->delete(Folder::DELETE_FOLDER_RECURSIVE);
    }

    public function testMatchWrapper(){
        $user = self::$user;
        $pass = self::$password;
        $host = self::$host;
        $this->assertEquals("ftp://$host",$this->connection->getWrapper(),'Wrapper did not match');
        $this->setUpLogin();
        $this->assertEquals("ftp://$user:$pass@$host",$this->connection->getWrapper(),'Wrapper did not match');
    }

    public function testMoveWillMoveFile(){
        $this->setUpLogin();
        $localFile = dirname(__FILE__).'/_stub/fileStub';
        $remoteFile = self::$path.'/newFile';
        $newRemoteFile = self::$path.'/newFile2';
        $this->connection->put($localFile,$remoteFile);
        $this->assertTrue($this->connection->exists($remoteFile),'The file was not created');
        $this->assertTrue($this->connection->move($remoteFile,$newRemoteFile),'Did not return true on move file');
        $this->assertFalse($this->connection->exists($remoteFile),'The file was not moved');
        $this->assertTrue($this->connection->exists($newRemoteFile),'The file was not moved');
    }

    public function testMoveWillMoveFolder(){
        $this->setUpLogin();
        $remoteFolder = self::$path.'/newFolder';
        $newRemoteFolder = self::$path.'/newFolder2';
        $this->deleteContent();
        $this->connection->createDirectory($remoteFolder);
        $this->assertTrue($this->connection->exists($remoteFolder),'The folder was not created');
        $this->assertTrue($this->connection->move($remoteFolder,$newRemoteFolder),'Did not return true on move folder');
        $this->assertFalse($this->connection->exists($remoteFolder),'The folder was not moved');
        $this->assertTrue($this->connection->exists($newRemoteFolder),'The folder was not moved');
        $this->connection->deleteDirectory($newRemoteFolder);
    }

    public function testMoveWillMoveFolderWithContent(){
        $this->setUpLogin();
        $remoteFolder = self::$path.'/newFolder';
        $newRemoteFolder = self::$path.'/newFolder2';
        $localFile = dirname(__FILE__).'/_stub/fileStub';
        $remoteFile = $remoteFolder.'/newFile';
        $this->deleteContent();
        $this->connection->createDirectory($remoteFolder);
        $this->connection->put($localFile,$remoteFile);
        $this->assertTrue($this->connection->exists($remoteFolder),'The folder was not created');
        $this->assertTrue($this->connection->move($remoteFolder,$newRemoteFolder),'Did not return true on move folder');
        $this->assertTrue($this->connection->exists($newRemoteFolder.'/newFile'),'File was not moved');
    }

    public function testMoveFileWillReturnFalseOnNoConnection(){
        $this->setUpLogin();
        $localFile = dirname(__FILE__).'/_stub/fileStub';
        $remoteFile = self::$path.'/newFile';
        $newRemoteFile = self::$path.'/newFile2';
        $this->deleteContent();
        $this->connection->put($localFile,$remoteFile);
        $this->assertTrue($this->connection->exists($remoteFile),'The file was not created');
        $this->connection->close();
        $this->assertFalse($this->connection->move($remoteFile,$newRemoteFile),'Did not return false on no connection');

    }

    public function testMoveFileWillReturnFalseOnNoFile(){
        $this->setUpLogin();
        $remoteFile = self::$path.'/nonExistingFile';
        $newRemoteFile = self::$path.'/newFile2';
        $this->assertFalse($this->connection->exists($remoteFile),'The file existed');
        $this->assertFalse($this->connection->move($remoteFile,$newRemoteFile),'Did not return false on no file');
    }

    public function testCopyWillReturnFalseIfNoFile(){
        $this->assertFalse($this->connection->copy(self::$path.'/nonExistingFolder',self::$path.'/anotherNonExistingFolder'));
    }


    public function testCopyWillCopyFileOnServer(){
        $this->setUpLogin();
        $string = "HELLO";
        $localFilePath = dirname(__FILE__).'/_stub/FTPFileStub';
        $newLocalFilePath = dirname(__FILE__).'/_stub/FTPFileStub2';
        $localFile = new FileImpl($localFilePath);
        $localFile->setAccessMode(File::FILE_MODE_W_TRUNCATE_FILE_TO_ZERO_LENGTH);
        $localFile->write($string);
        $remoteFile = self::$path.'/newFile';
        $newRemoteFile = self::$path.'/newFile2';
        $this->connection->put($localFilePath,$remoteFile);
        $this->connection->deleteFile($newRemoteFile);
        $this->assertTrue($this->connection->copy($remoteFile,$newRemoteFile),'Did not return true on copy file');
        $this->assertTrue($this->connection->exists($newRemoteFile),'New file was not created');
        $this->assertTrue($this->connection->exists($remoteFile),'New file was not created');
        $this->connection->get($newLocalFilePath,$newRemoteFile);
        $newLocalFile = new FileImpl($newLocalFilePath);
        $this->assertEquals($string,$newLocalFile->getContents(),"Contents did not match");

    }

    public function testCopyWillCopyFolderOnServer(){
        $this->setUpLogin();
        $string = "HELLO";
        $localFilePath = dirname(__FILE__).'/_stub/FTPFileStub';
        $newLocalFilePath = dirname(__FILE__).'/_stub/FTPFileStub2';

        $localFile = new FileImpl($localFilePath);
        $localFile->setAccessMode(File::FILE_MODE_W_TRUNCATE_FILE_TO_ZERO_LENGTH);
        $localFile->write($string);

        $remoteDir = self::$path.'/newFolder';
        $newRemoteDir = self::$path.'/newFolder2';

        $this->connection->deleteFile($remoteDir);
        $this->connection->deleteDirectory($remoteDir);
        $this->connection->deleteFile($newRemoteDir);
        $this->connection->deleteDirectory($newRemoteDir);

        $this->connection->createDirectory($remoteDir);

        $remoteFileName = 'newFile';

        $this->connection->put($localFilePath,$remoteDir.'/'.$remoteFileName);


        $this->assertTrue($this->connection->copy($remoteDir,$newRemoteDir),'Did not return true on copy folder');
        $this->assertTrue($this->connection->exists($newRemoteDir),'New file was not created');
        $this->assertTrue($this->connection->exists($remoteDir),'New file was not created');
        $this->connection->get($newLocalFilePath,$newRemoteDir.'/'.$remoteFileName);
        $newLocalFile = new FileImpl($newLocalFilePath);
        $this->assertEquals($string,$newLocalFile->getContents(),"Contents did not match");
    }


    public function tearDown(){
        $localFilePath = dirname(__FILE__).'/_stub/FTPFileStub';
        $newLocalFilePath = dirname(__FILE__).'/_stub/FTPFileStub2';
        @unlink($localFilePath);
        @unlink($newLocalFilePath);
        $this->connection->close();
    }

}
