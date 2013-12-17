<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/22/13
 * Time: 3:46 PM
 * To change this template use File | Settings | File Templates.
 */

class PageVariablesImpl implements Variables
{
    private $preparedRemoveKey;
    private $preparedSetValue;
    private $preparedUpdateValue;

    /** @var  Page */
    private $page;
    /** @var  DB */
    private $db;

    private $map;


    function __construct(DB $db, Page $page)
    {
        $this->db = $db;
        $this->page = $page;
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
        if(!$this->page->exists()){
            return;
        }
        $this->initialize();
        if($this->hasKey($key)){
            $this->updateValue($key, $value);
            return;
        }
        if ($this->preparedSetValue == null) {
            $this->preparedSetValue = $this->db->getConnection()->prepare("INSERT INTO PageVariables (`key`, `value`, page_id) VALUES (?, ? , ? )");
        }

        $this->preparedSetValue->execute(array($key, $value, $this->page->getID()));
        $this->map[$key] = $value;
    }

    private function updateValue($key, $value){
        if ($this->preparedUpdateValue == null) {
            $this->preparedUpdateValue = $this->db->getConnection()->prepare("UPDATE PageVariables SET `value`= ? WHERE page_id = ? AND `key` = ? ");
        }
        $this->preparedUpdateValue->execute(array($value, $this->page->getID(), $key));
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
            $this->preparedRemoveKey = $this->db->getConnection()->prepare("DELETE FROM PageVariables WHERE page_id = ? AND `key` = ?");
        }
        $this->preparedRemoveKey->execute(array($this->page->getID(), $key));
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
        $prep = $this->db->getConnection()->prepare("SELECT `key`,`value` FROM PageVariables WHERE page_id = ?");
        $prep->execute(array($this->page->getID()));
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