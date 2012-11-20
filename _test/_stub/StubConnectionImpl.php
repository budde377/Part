<?php
require_once dirname(__FILE__).'/../../_interface/Connection.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 19/11/12
 * Time: 21:12
 */
class StubConnectionImpl implements Connection
{
    public $isConnected = false;
    public $connectionReturn = true;
    public $closeReturn = true;
    public $loginReturn = true;
    public $folder;
    public $puts = array();
    public $gets = array();
    public $dirsCreated = array();
    public $moves = array();
    public $copies = array();
    public $deleteDirReturn = true;
    public $deleteFileReturn = true;
    public $directoryList = array();
    public $existsReturn = true;
    public $isFileReturn = array();
    public $isDirectoryReturn = array();
    public $sizeArray = array();
    public $wrapper = 'wrapper://';
    public $deleteFileCalled = false;
    public $createDirectoryReturn = true;
    public $deleted = array();

    /**
     * @return bool Return TRUE on successful connection else FALSE
     */
    public function connect()
    {
        $this->isConnected = true;
        return $this->connectionReturn;
    }

    /**
     * @return bool Return TRUE on success else FALSE
     */
    public function close()
    {
        return !$this->closeReturn;
    }

    /**
     * @param string $path Path to folder on remote
     * @return Folder Will return a folder matching the given path, this might not exist
     */
    public function getFolder($path)
    {
        return $this->folder;
    }

    /**
     * @param string $command Executes a command on remote
     * @return array | bool Returns an array containing the result of the command.
     */
    public function exec($command)
    {
        return false;
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function login($username, $password)
    {
        return !$this->loginReturn;
    }

    /**
     * @param $localPath
     * @param $remotePath
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function put($localPath, $remotePath)
    {
        $this->puts[] = array('localPath'=>$localPath,'remotePath'=>$remotePath);
        return true;
    }

    /**
     * @param $localPath
     * @param $remotePath
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function get($localPath, $remotePath)
    {
        $this->gets[] = array('localPath'=>$localPath,'remotePath'=>$remotePath);
        return true;
    }

    /**
     * @param string $path Path to the new folder
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function createDirectory($path)
    {
        $this->dirsCreated[] = $path;
        return $this->createDirectoryReturn;
    }

    /**
     * @param string $path Path to directory to be deleted
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function deleteDirectory($path)
    {
        $this->deleted[] = $path;
        return $this->deleteDirReturn;
    }

    /**
     * @param string $path Path to the file to be deleted
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function deleteFile($path)
    {

        $this->deleted[] = $path;
        $this->deleteFileCalled = true;
        return $this->deleteFileReturn;
    }

    /**
     * @param string $path
     * @return array | bool Will return an array containing the names of the files/dirs in the directory or FALSE on failure,
     */
    public function listDirectory($path)
    {
        return $this->directoryList;
    }

    /**
     * @param string $path Path to file
     * @return bool Will return FALSE on failure, or file does not exist, else TRUE.
     */
    public function exists($path)
    {
        return $this->isFile($path) || $this->isDirectory($path);
    }

    /**
     * @param string $path
     * @return bool Will return FALSE on failure, or if not a file, else TRUE
     */
    public function isFile($path)
    {
        return array_search($path,$this->isFileReturn) !== false;
    }

    /**
     * @param string $path
     * @return bool Will return FALSE on failure, or if not a directory, else TRUE
     */
    public function isDirectory($path)
    {

        return array_search($path,$this->isDirectoryReturn) !== false;
    }

    /**
     * @return bool Will return TRUE if connected, else FALSE
     */
    public function isConnected()
    {
        return $this->isConnected;
    }

    /**
     * Will copy the file/folder to a new location (on the connection) cannot be used as put/get
     * @param string $oldFile
     * @param string $newFile
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function copy($oldFile, $newFile)
    {
        $this->copies[] = array('oldFile'=>$oldFile,'newFile'=>$newFile);
        return true;
    }

    /**
     * Will move the file/folder to a new location (on the connection) cannot be used as put/get
     * @param string $oldFile
     * @param string $newFile
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function move($oldFile, $newFile)
    {
        $this->moves[] = array('oldFile'=>$oldFile,'newFile'=>$newFile);
        return true;
    }

    /**
     * @return string Returns a wrapper used for connecting to resource (by fopen or such)
     */
    public function getWrapper()
    {
        return $this->wrapper;
    }


    /**
     * Will return the size of a file or null on error
     * @param string $file
     * @return int
     */
    public function size($file)
    {
        return isset($this->sizeArray[$file]) ? $this->sizeArray[$file] : -1;
    }
}
