<?php
namespace ChristianBudde\cbweb\model;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/22/13
 * Time: 3:46 PM
 * To change this template use File | Settings | File Templates.
 */
use ChristianBudde\cbweb\util\db\DB;

use Traversable;
use ArrayObject;
use PDO;

class SiteVariablesImpl implements Variables
{
    private $preparedRemoveKey;
    private $preparedSetValue;
    private $preparedUpdateValue;

    /** @var  DB */
    private $db;

    private $map;


    function __construct(DB $db)
    {
        $this->db = $db;
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
        if ($this->preparedSetValue == null) {
            $this->preparedSetValue = $this->db->getConnection()->prepare("INSERT INTO SiteVariables (`key`, `value`) VALUES (?, ? )");
        }

        $this->preparedSetValue->execute(array($key, $value));
        $this->map[$key] = $value;
    }

    private function updateValue($key, $value){
        if ($this->preparedUpdateValue == null) {
            $this->preparedUpdateValue = $this->db->getConnection()->prepare("UPDATE SiteVariables SET `value`= ? WHERE `key` = ? ");
        }
        $this->preparedUpdateValue->execute(array($value, $key));
        $this->map[$key] = $value;

    }

    /**
     * @param string $key
     * @return mixed
     */
    public function removeKey($key)
    {
        $this->initialize();
        if ($this->preparedRemoveKey == null) {
            $this->preparedRemoveKey = $this->db->getConnection()->prepare("DELETE FROM SiteVariables WHERE `key` = ?");
        }
        $this->preparedRemoveKey->execute(array($key));
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
        $ar = new ArrayObject($this->map);
        return $ar->getIterator();
    }


    private function initialize()
    {

        if ($this->map != null) {
            return;
        }
        $this->map = array();
        $prep = $this->db->getConnection()->prepare("SELECT `key`,`value` FROM SiteVariables ");
        $prep->execute();
        while ($row = $prep->fetch(PDO::FETCH_ASSOC)) {
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