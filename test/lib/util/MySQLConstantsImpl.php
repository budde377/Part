<?php
namespace ChristianBudde\Part\test\util;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/4/14
 * Time: 11:54 PM
 */
abstract class MySQLConstantsImpl implements MySQLConstants
{
    private $username;
    private $password;
    private $host;
    private $database;

    function __construct($host, $database, $username, $password)
    {
        $this->database = $database;
        $this->host = $host;
        $this->password = $password;
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

}