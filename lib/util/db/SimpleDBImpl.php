<?php
namespace ChristianBudde\cbweb\util\db;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 7/16/12
 * Time: 10:28 PM
 */


use PDO;

class SimpleDBImpl implements DB
{
    private $connection;
    private $mailConnection;

    public function __construct(PDO $connection, PDO $mysqlConnection = null)
    {
        $this->connection = $connection;
        $this->mailConnection = $mysqlConnection;


    }

    /**
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param string $password
     * @return PDO
     */
    public function getMailConnection($password)
    {
        return $this->mailConnection;
    }
}
