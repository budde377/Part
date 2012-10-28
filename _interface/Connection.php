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

    /**
     * @param string $host The host to which we wish to connect to
     * @return bool Return TRUE on successful connection else FALSE
     */
    public function connect($host);

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
     * @return array Returns an array containing the result of the command.
     */
    public function exec($command);

    /**
     * @param string $username
     * @param string $password
     * @return void
     */
    public function login($username,$password);
}
