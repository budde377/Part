<?php

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/5/14
 * Time: 3:18 PM
 */
class OrderedListImpl implements OrderedList
{

    /** @var  DB */
    private $db;
    private $id;
    /** @var  PDOStatement */
    private $inListPreparedStatement;
    /** @var  PDOStatement */
    private $createElementPreparedStatement;
    /** @var  array */
    private $list;
    /** @var  PDOStatement */
    private $listPreparedStatement;
    /** @var  PDOStatement */
    private $deleteElementPreparedStatement;
    /** @var  PDOStatement */
    private $rebalancePreparedStatement;


    function __construct(DB $db, $id)
    {
        $this->db = $db;
        $this->id = $id;
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        // TODO: Implement current() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        // TODO: Implement next() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        // TODO: Implement key() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        // TODO: Implement valid() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        // TODO: Implement rewind() method.
    }

    /**
     * Will return the list id.
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array An ordered array of OrderedListElement.
     */
    public function listElements()
    {
        // TODO: Implement listElements() method.
    }

    /**
     * Moves the given element one position up in the order.
     * If the element is the first element, nothing will happen.
     * If the element isn't an instance provided by the list, behaviour is undefined.
     *
     * @param OrderedListElement $element
     * @return void
     */
    public function moveUp(OrderedListElement $element)
    {
        // TODO: Implement moveUp() method.
    }

    /**
     * Moves the given element one position down in the order.
     * If the element is the last element, nothing happen.
     * If the element isn't an instance provided by the list, behaviour is undefined.
     *
     * @param OrderedListElement $element
     * @return mixed
     */
    public function moveDown(OrderedListElement $element)
    {
        // TODO: Implement moveDown() method.
    }

    /**
     * Will set the place of a given element.
     * If the place is negative, the element will be placed first.
     * It the place is larger than the number of elements in the list,
     * it will be placed in the end of the list.
     * If the element isn't an instance provided by the list, behaviour is undefined.
     *
     * @param OrderedListElement $element
     * @param int $place
     * @return void
     */
    public function setElementOrder(OrderedListElement $element, $place)
    {
        // TODO: Implement setElementOrder() method.
    }

    /**
     * Returns the order of an given element.
     * If the element isn't an instance provided by the list, behaviour is undefined.
     *
     * @param OrderedListElement $element
     * @return int
     */
    public function getElementOrder(OrderedListElement $element)
    {
        $this->setUpList();
        return array_search($element, $this->list);
    }

    /**
     * Creates a fresh element at the end of the list.
     *
     * @return OrderedListElement
     */
    public function createElement()
    {
        $this->setUpList();

        if ($this->createElementPreparedStatement == null) {
            $this->createElementPreparedStatement = $this->db->getConnection()
                ->prepare("INSERT INTO OrderedList (list_id, element_id, `order`) VALUES (?,?,?)");
        }
        $elm = new OrderedListElementImpl($this->db);
        $this->createElementPreparedStatement->execute(array($this->id, $elm->getId(), $this->size()));
        $this->list[] = $elm;
        return $elm;
    }

    /**
     * Deletes an element.
     *
     * @param OrderedListElement $element
     * @return void
     */
    public function deleteElement(OrderedListElement $element)
    {
        $this->setUpList();

        if(!$this->isInList($element)){
            return;
        }

        if ($this->deleteElementPreparedStatement == null) {
            $this->deleteElementPreparedStatement = $this->db->getConnection()
                ->prepare("DELETE FROM OrderedList WHERE element_id = ? AND list_id = ?");
            $this->rebalancePreparedStatement = $this->db->getConnection()
                ->prepare("UPDATE OrderedList SET `order`=? WHERE `order`=? AND list_id = ?");
        }
        $this->deleteElementPreparedStatement->execute(array($element->getId(), $this->id));
        $o = $this->getElementOrder($element);
        $s = $this->size();
        $l = array();
        for($i = $o;$i<$s; $i++){
            $this->rebalancePreparedStatement->execute(array($i, $i+1, $this->id));
            if(isset($this->list[$i+1])){
                $l[$i] = $this->list[$i+1];
            }
        }
        $this->list = $l;

    }

    /**
     * Check if element is in list.
     * @param OrderedListElement $element
     * @return bool
     */
    public function isInList(OrderedListElement $element)
    {
        $this->setUpList();
        return in_array($element, $this->list);
    }

    /**
     * @param $place
     * @return OrderedListElement | null Returns the element at given place,
     * if no such element; returns null.
     */
    public function getElementAt($place)
    {
        if(($s = $this->size()) == 0){
            return null;
        }

        $place = max(0, $place);
        $place = min($s-1, $place);
        return $this->list[$place];
    }

    /**
     * @return int Size of the list
     */
    public function size()
    {
        $this->setUpList();
        return count($this->list);

    }

    private function setUpList()
    {
        if ($this->list != null) {
            return;
        }
        $this->list = array();
        if ($this->listPreparedStatement == null) {
            $this->listPreparedStatement = $this->db->getConnection()
                ->prepare("SELECT element_id FROM OrderedList WHERE list_id = ? ORDER BY 'order' ASC");
            $this->listPreparedStatement->bindParam(1, $this->id);
        }
        $this->listPreparedStatement->execute();
        foreach ($this->listPreparedStatement->fetchAll() as $row) {
            $this->list[] = new OrderedListElementImpl($this->db, $row["element_id"]);
        }

    }

    /**
     * Returns the first element.
     * @return OrderedListElement
     */
    public function firstElement()
    {
        return $this->getElementAt(0);
    }

    /**
     * Returns the last element.
     * @return OrderedListElement
     */
    public function lastElement()
    {
        return $this->getElementAt($this->size()-1);
    }
}