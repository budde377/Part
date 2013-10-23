<?php
require_once dirname(__FILE__) . '/../_interface/Variables.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/22/13
 * Time: 3:46 PM
 * To change this template use File | Settings | File Templates.
 */

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
}