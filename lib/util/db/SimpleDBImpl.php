<?php
namespace ChristianBudde\Part\util\db;
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
    public function getMailConnection(/** @noinspection PhpUnusedParameterInspection */
        $password)
    {
        return $this->mailConnection;
    }

    /**
     * Updates the database according to the sql files
     * in the designated db folders.
     *
     * @return void
     */
    public function update()
    {

    }

    /**
     * @param string $name
     * @return array|string If $name is not empty a version string will be returned else an array containing
     *                      name=>version entries.
     */
    public function getVersion($name = "")
    {
    }
}
