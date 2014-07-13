<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 11:00 AM
 * To change this template use File | Settings | File Templates.
 */
interface DB
{
    /**
     * @abstract
     * @return PDO
     */
    public function getConnection();

    /**
     * @param string $password
     * @return PDO
     */
    public function getMailConnection($password);

}
