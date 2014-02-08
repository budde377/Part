<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 1/31/14
 * Time: 10:58 PM
 */

class StubVariablesImpl implements Variables {

    private $array;

    function __construct()
    {
        $this->array = array();
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new ArrayIterator($this->array);
    }

    /**
     * @param string | null $key
     * @return string
     */
    public function getValue($key)
    {
        return $this->hasKey($key)?$this->array[$key]: null;
    }

    /**
     * @return array Containing all keys
     */
    public function listKeys()
    {
        return array_keys($this->array);
    }

    /**
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setValue($key, $value)
    {
        $this->array[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function removeKey($key)
    {
        if(!$this->hasKey($key)){
            return;
        }
        unset($this->array[$key]);
    }

    /**
     * @param $key
     * @return bool TRUE if has key else FALSE
     */
    public function hasKey($key)
    {
        return isset($this->array[$key]);
    }
}