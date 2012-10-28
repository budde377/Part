<?php
require_once dirname(__FILE__).'/../_interface/Connection.php';
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

    /**
     * @param string $host
     * @param int $port
     */
    public function __construct($host,$port = 21){
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @return bool Return TRUE on successful connection else FALSE
     */
    public function connect()
    {
        if(($con = @ftp_connect($this->host,$this->port)) !== false){
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
        return $this->isConnected() && is_array($a = @ftp_raw($this->connection,$command))?$a:false;
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function login($username, $password)
    {
        return $this->connection !== null && ftp_login($this->connection,$username,$password);
    }

    /**
     * @param $localPath
     * @param $remotePath
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function put($localPath, $remotePath)
    {
        // TODO: Implement put() method.
    }

    /**
     * @param $localPath
     * @param $remotePath
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function get($localPath, $remotePath)
    {
        // TODO: Implement get() method.
    }

    /**
     * @param string $path Path to the new folder
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function createDirectory($path)
    {
        return $this->isConnected() && @ftp_mkdir($this->connection,$path) !== false;
    }

    /**
     * @param string $path Path to directory to be deleted
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function deleteDirectory($path)
    {
        return $this->isConnected() && @ftp_rmdir($this->connection,$path);
    }

    /**
     * @param string $path Path to the file to be deleted
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function deleteFile($path)
    {
        // TODO: Implement deleteFile() method.
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

        if($this->isConnected() && is_array($a = ftp_nlist($this->connection,$path))){
            $returnArray = array();
            foreach($a as $file){
                $v = ftp_nlist($this->connection,$file);
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
        if(($a = $this->listDirectory($containingDir)) !== false){
            foreach($a as $file){
                if($file == $fileName){
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
        return $this->isConnected() && ftp_size($this->connection,$path) != '-1';
    }

    /**
     * @param string $path
     * @return bool Will return FALSE on failure, or if not a directory, else TRUE
     */
    public function isDirectory($path)
    {
        return $this->exists($path) && ftp_size($this->connection,$path) == "-1";
    }
}
