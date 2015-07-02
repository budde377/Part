<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/20/15
 * Time: 10:49 PM
 */

namespace ChristianBudde\Part\model;


use ArrayObject;
use PDO;
use PDOStatement;
use Traversable;

abstract class VariablesImpl implements Variables{

    /** @var  PDOStatement */
    protected $preparedRemoveKey;
    /** @var  PDOStatement */
    protected $preparedSetValue;
    /** @var  PDOStatement */
    protected $preparedUpdateValue;
    /** @var  PDOStatement */
    protected $preparedInitialize;


    private $map;

    function __construct(PDOStatement $preparedInitialize, PDOStatement $preparedRemoveKey, PDOStatement $preparedSetValue, PDOStatement $preparedUpdateValue)
    {
        $this->preparedRemoveKey = $preparedRemoveKey;
        $this->preparedSetValue = $preparedSetValue;
        $this->preparedUpdateValue = $preparedUpdateValue;
        $this->preparedInitialize = $preparedInitialize;
    }


    /**
     * @param string|null $key
     * @return string
     */
    public function getValue($key)
    {
        $this->initialize();
        return isset($this->map[$key]) ? $this->map[$key] : null;
    }

    /**
     * @return array Containing all keys
     */
    public function listKeys()
    {
        $this->initialize();
        return array_keys($this->map);
    }

    /**
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setValue($key, $value)
    {
        $this->initialize();
        if($this->hasKey($key)){
            $this->updateValue($key, $value);
            return;
        }
        $this->preparedSetValue->bindParam(":key", $key);
        $this->preparedSetValue->bindParam(":value", $value);
        $this->preparedSetValue->execute();
        $this->map[$key] = $value;
    }

    private function updateValue($key, $value){
        $this->preparedUpdateValue->bindParam(":key", $key);
        $this->preparedUpdateValue->bindParam(":value", $value);
        $this->preparedUpdateValue->execute();
        $this->map[$key] = $value;

    }

    /**
     * @param string $key
     * @return mixed
     */
    public function removeKey($key)
    {
        $this->initialize();
        $this->preparedRemoveKey->bindParam(':key', $key);
        $this->preparedRemoveKey->execute();
        unset($this->map[$key]);
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
        $this->initialize();
        return (new ArrayObject($this->map))->getIterator();
    }


    private function initialize()
    {

        if ($this->map != null) {
            return;
        }
        $this->map = [];
        $this->preparedInitialize->execute();
        while ($row = $this->preparedInitialize->fetch(PDO::FETCH_ASSOC)) {
            $this->map[$row['key']] = $row['value'];
        }

    }

    /**
     * @param $key
     * @return bool TRUE if has key else FALSE
     */
    public function hasKey($key)
    {
        $this->initialize();
        return isset($this->map[$key]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->hasKey($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->getValue($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->setValue($offset, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->removeKey($offset);
    }
}