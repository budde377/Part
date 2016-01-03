<?php

namespace ChristianBudde\Part\util\db;
use PDO;

/**
 * User: budde
 * Date: 6/16/12
 * Time: 10:32 PM
 */
class StubDBImpl implements DB
{

    /**
     * @var $connection PDO
     */
    private $connection;

    /**
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    public function setConnection(PDO $connection)
    {
        $this->connection = $connection;
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
