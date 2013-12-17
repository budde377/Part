<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 7/16/12
 * Time: 10:28 PM
 */
class SimpleDBImpl implements DB
{
    private $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;


    }

    /**
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
