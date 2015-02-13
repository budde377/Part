<?php

namespace ChristianBudde\Part\test\stub;
use ChristianBudde\Part\util\db\DB;
use PDO;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/16/12
 * Time: 10:32 PM
 * To change this template use File | Settings | File Templates.
 */
class StubDBImpl implements DB
{

    /**
     * @var $connection PDO
     */
    private $connection;

    private $mailConnection;

    /**
     * @param PDO $mailConnection
     */
    public function setMailConnection($mailConnection)
    {
        $this->mailConnection = $mailConnection;
    }

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
     * @param string $password
     * @return PDO
     */
    public function getMailConnection($password)
    {
        return $this->connection;
    }
}
