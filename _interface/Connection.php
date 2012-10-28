<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/28/12
 * Time: 10:33 AM
 * To change this template use File | Settings | File Templates.
 */
interface Connection
{
    const FILE_TYPE_DIRECTORY = 1;
    const FILE_TYPE_FILE = 2;

    /**
     * @return bool Return TRUE on successful connection else FALSE
     */
    public function connect();

    /**
     * @return bool Return TRUE on success else FALSE
     */
    public function close();

    /**
     * @param string $path Path to folder on remote
     * @return Folder Will return a folder matching the given path, this might not exist
     */
    public function getFolder($path);

    /**
     * @param string $command Executes a command on remote
     * @return array | bool Returns an array containing the result of the command.
     */
    public function exec($command);

    /**
     * @param string $username
     * @param string $password
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function login($username,$password);

    /**
     * @param $localPath
     * @param $remotePath
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function put($localPath,$remotePath);

    /**
     * @param $localPath
     * @param $remotePath
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function get($localPath,$remotePath);

    /**
     * @param string $path Path to the new folder
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function createDirectory($path);

    /**
     * @param string $path Path to directory to be deleted
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function deleteDirectory($path);

    /**
     * @param string $path Path to the file to be deleted
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function deleteFile($path);

    /**
     * @param string $path
     * @return array Will return an array with associative arrays with keys: type, name
     * Where the type will be of Connection[const] and the name will be a string
     */
    public function listDirectory($path);

    /**
     * @param string $path Path to file
     * @return int | bool Will return False if file/dir does not exist, else int corresponding to file type constants
     */
    public function exists($path);

    /**
     * @return bool Will return TRUE if connected, else FALSE
     */
    public function isConnected();
}
