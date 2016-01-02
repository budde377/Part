<?php
namespace ChristianBudde\Part\util\db;
use PDO;

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
     * @deprecated
     * @param string $password
     * @return PDO
     */
    public function getMailConnection($password);


    /**
     * Updates the database according to the sql files
     * in the designated db folders.
     *
     * @return void
     */
    public function update();


    /**
     * @param string $name
     * @return array|string If $name is not empty a version string will be returned else an array containing
     *                      name=>version entries.
     */
    public function getVersion($name = "");

}
