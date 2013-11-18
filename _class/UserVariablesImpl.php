<?php
require_once dirname(__FILE__) . '/../_interface/Variables.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/22/13
 * Time: 3:46 PM
 * To change this template use File | Settings | File Templates.
 */

class UserVariablesImpl implements Variables
{
    private $preparedRemoveKey;
    private $preparedSetValue;
    private $preparedUpdateValue;

    /** @var  User */
    private $user;
    /** @var  DB */
    private $db;

    private $map;


    function __construct(DB $db, User $user)
    {
        $this->db = $db;
        $this->user = $user;
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
        if(!$this->user->exists()){
            return;
        }
        $this->initialize();
        if($this->hasKey($key)){
            $this->updateValue($key, $value);
            return;
        }
        if ($this->preparedSetValue == null) {
            $this->preparedSetValue = $this->db->getConnection()->prepare("INSERT INTO UserVariables (`key`, `value`, username) VALUES (?, ? , ? )");
        }

        $this->preparedSetValue->execute(array($key, $value, $this->user->getUsername()));
        $this->map[$key] = $value;
    }

    private function updateValue($key, $value){
        if ($this->preparedUpdateValue == null) {
            $this->preparedUpdateValue = $this->db->getConnection()->prepare("UPDATE UserVariables SET `value`= ? WHERE username = ? AND `key` = ? ");
        }
        $this->preparedUpdateValue->execute(array($value, $this->user->getUsername(), $key));
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
            $this->preparedRemoveKey = $this->db->getConnection()->prepare("DELETE FROM UserVariables WHERE username = ? AND `key` = ?");
        }
        $this->preparedRemoveKey->execute(array($this->user->getUsername(), $key));
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
        $prep = $this->db->getConnection()->prepare("SELECT `key`,`value` FROM UserVariables WHERE username = ?");
        $prep->execute(array($this->user->getUsername()));
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