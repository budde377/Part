<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 18/01/13
 * Time: 10:24
 */
namespace ChristianBudde\Part\test\stub;

use ArrayIterator;
use ChristianBudde\Part\model\page\DefaultPageLibrary;
use ChristianBudde\Part\model\page\Page;

class StubDefaultPageLibraryImpl implements DefaultPageLibrary
{
    private $iterator;
    private $pages;

    function __construct(array $pages)
    {
        $this->iterator = new ArrayIterator($pages);
        $this->pages = $pages;
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->iterator->current();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->iterator->key();
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
        return $this->iterator->valid();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->iterator->rewind();
    }

    /**
     * Will list all default pages, as defined in given config.
     * @return array An array containing instances of Page representing the Default pages
     */
    public function listPages()
    {
        return array_values($this->pages);
    }

    /**
     * Will return a default page given an ID
     * @param string $id The id
     * @return Page | null Instance matching the ID or NULL on no such page
     */
    public function getPage($id)
    {
        return isset($this->pages[$id]) ? $this->pages[$id] : null;
    }
}
