<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/22/13
 * Time: 3:42 PM
 * To change this template use File | Settings | File Templates.
 */

interface Variables extends IteratorAggregate{

    /**
     * @param string | null $key
     * @return string
     */
    public function getValue($key);

    /**
     * @return array Containing all keys
     */
    public function listKeys();

    /**
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setValue($key, $value);

    /**
     * @param string $key
     * @return mixed
     */
    public function removeKey($key);


    /**
     * @param $key
     * @return bool TRUE if has key else FALSE
     */
    public function hasKey($key);
}