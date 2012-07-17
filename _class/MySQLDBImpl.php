<?php
require_once dirname(__FILE__) . '/../_interface/DB.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/11/12
 * Time: 10:09 AM
 * To change this template use File | Settings | File Templates.
 */
class MySQLDBImpl implements DB
{
    /** @var $connection null | PDO */
    private $connection = null;
    private $database = null;
    private $host = null;
    private $password = null;
    private $username = null;

    public function __construct(Config $config)
    {

        $connectionArray = $config->getMySQLConnection();
        if ($connectionArray !== null) {
            $this->database = $connectionArray['database'];
            $this->host = $connectionArray['host'];
            $this->password = $connectionArray['password'];
            $this->username = $connectionArray['user'];
        }
    }


    /**
     * This returns the current connection, with info provided in config.
     * @return PDO
     */
    public function getConnection()
    {
        if ($this->connection === null) {
            $this->connection = new PDO(
                'mysql:dbname=' . $this->database . ';host=' . $this->host,
                $this->username,
                $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }

        return $this->connection;
    }
}
