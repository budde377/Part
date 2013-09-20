<?php
require_once dirname(__FILE__) . '/../../_interface/DB.php';

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


}
