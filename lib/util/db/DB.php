<?php
namespace ChristianBudde\Part\util\db;
use PDO;

/**
 * User: budde
 * Date: 5/10/12
 * Time: 11:00 AM
 */
interface DB
{
    /**
     * @abstract
     * @return PDO
     */
    public function getConnection();


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
