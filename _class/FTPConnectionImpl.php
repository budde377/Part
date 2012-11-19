<?php
require_once dirname(__FILE__) . '/../_interface/Connection.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 28/10/12
 * Time: 15:33
 */
class FTPConnectionImpl implements Connection
{
    private $host;
    private $port;
    private $connection;
    private $user;
    private $pass;

    /**
     * @param string $host
     * @param int $port
     */
    public function __construct($host, $port = 21)
    {
        $this->host = $host;
        $this->port = $port;

    }

    /**
     * @return bool Return TRUE on successful connection else FALSE
     */
    public function connect()
    {
        if (($con = @ftp_connect($this->host, $this->port)) !== false) {
            $this->connection = $con;
            return true;
        }
        return false;
    }

    /**
     * @return bool Return TRUE on success else FALSE
     */
    public function close()
    {
        return $this->connection !== null && @ftp_close($this->connection);
    }

    /**
     * @param string $path Path to folder on remote
     * @return Folder Will return a folder matching the given path, this might not exist
     */
    public function getFolder($path)
    {
        // TODO: Implement getFolder() method.
    }

    /**
     * @param string $command Executes a command on remote
     * @return array | bool Returns an array containing the result of the command.
     */
    public function exec($command)
    {
        return $this->isConnected() && is_array($a = @ftp_raw($this->connection, $command)) ? $a : false;
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function login($username, $password)
    {
        $ret = $this->connection !== null && ftp_login($this->connection, $username, $password);
        if ($ret) {
            $this->user = $username;
            $this->pass = $password;

        }
        return $ret;
    }

    /**
     * @param $localPath
     * @param $remotePath
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function put($localPath, $remotePath)
    {
        return $this->isConnected() && @ftp_put($this->connection, $remotePath, $localPath, FTP_BINARY);
    }

    /**
     * @param $localPath
     * @param $remotePath
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function get($localPath, $remotePath)
    {
        return $this->isConnected() && @ftp_get($this->connection, $localPath, $remotePath, FTP_BINARY);
    }

    /**
     * @param string $path Path to the new folder
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function createDirectory($path)
    {
        return $this->isConnected() && @ftp_mkdir($this->connection, $path) !== false;
    }

    /**
     * @param string $path Path to directory to be deleted
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function deleteDirectory($path)
    {
        return $this->isConnected() && @ftp_rmdir($this->connection, $path);
    }

    /**
     * @param string $path Path to the file to be deleted
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function deleteFile($path)
    {
        return @ftp_delete($this->connection, $path) && !$this->exists($path);
    }


    /**
     * @return bool Will return TRUE if connected, else FALSE
     */
    public function isConnected()
    {
        return $this->connection !== null && is_array(@ftp_nlist($this->connection, "."));
    }

    /**
     * @param string $path
     * @return array | bool Will return an array containing the names of the files/dirs in the directory,
     */
    public function listDirectory($path)
    {
//        $path = escapeshellarg($path);

        if ($this->isConnected() && is_array($a = ftp_nlist($this->connection, $path))) {
            $returnArray = array();
            foreach ($a as $file) {
                $v = ftp_nlist($this->connection, $file);
                $returnArray[] = basename($file);
            }
            return $returnArray;
        }
        return false;
    }

    /**
     * @param string $path Path to file
     * @return bool Will return FALSE on failure, or file does not exist, else TRUE.
     */
    public function exists($path)
    {
        $containingDir = dirname($path);
        $fileName = basename($path);
        if (($a = $this->listDirectory($containingDir)) !== false) {
            foreach ($a as $file) {
                if ($file == $fileName) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param string $path
     * @return bool Will return FALSE on failure, or if not a file, else TRUE
     */
    public function isFile($path)
    {
        return $this->isConnected() && ftp_size($this->connection, $path) != '-1';
    }

    /**
     * @param string $path
     * @return bool Will return FALSE on failure, or if not a directory, else TRUE
     */
    public function isDirectory($path)
    {
        return $this->exists($path) && ftp_size($this->connection, $path) == "-1";
    }

    /**
     * Will copy the file/folder to a new location (on the connection) cannot be used as put/get
     * @param string $oldFile
     * @param string $newFile
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function copy($oldFile, $newFile)
    {
        $folderName = '.tmp';
        $i = 0;
        while (file_exists($folderName . $i)) {
            $i++;
        }
        $tempFolder = new FolderImpl($folderName . $i);
        $tempFolder->create();

        $copyFunction = function ($oldFile, $newFile, $tmpPath) use (&$copyFunction) {
            if ($this->isDirectory($oldFile)) {
                $res = $this->createDirectory($newFile);
                foreach ($this->listDirectory($oldFile) as $file) {
                        $res = $res && $copyFunction($oldFile.'/'.$file,$newFile.'/'.$file,$tmpPath);
                }
            } else {
                $tempFile = $tmpPath . '/' . basename($oldFile);
                $res = $this->get($tempFile, $oldFile);
                $res = $res && $this->put($tempFile, $newFile);
                @unlink($tempFile);

            }
            return $res;
        };
        $res = $copyFunction($oldFile, $newFile, $tempFolder->getAbsolutePath());
        $res = $tempFolder->delete(Folder::DELETE_FOLDER_RECURSIVE) && $res;

        return  $res;
    }

    /**
     * Will move the file/folder to a new location (on the connection) cannot be used as put/get
     * @param string $oldFile
     * @param string $newFile
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function move($oldFile, $newFile)
    {
        return $this->isConnected() && @ftp_rename($this->connection, $oldFile, $newFile);
    }

    /**
     * @return string Returns a wrapper used for connecting to resource (by fopen or such)
     */
    public function getWrapper()
    {
        $userString = "";
        if ($this->user != null && $this->pass != null) {
            $userString = "{$this->user}:{$this->pass}@";
        }
        return "ftp://$userString{$this->host}";
    }
}