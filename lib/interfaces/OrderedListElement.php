<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/5/14
 * Time: 3:00 PM
 */

interface OrderedListElement extends ArrayAccess, Iterator {

    /**
     * @return string
     */
    public function getId();

    /**
     * @return array Returns an array of string -> values
     */
    public function listAttributes();

    /**
     * Sets the attribute.
     *
     * @param string $key
     * @param string $val
     * @return void
     */
    public function setAttribute($key, $val);


    /**
     * Returns a value from a given key.
     *
     * @param string $key
     * @return string
     */
    public function getAttribute($key);

    /**
     * Clears an attribute
     * @param string $key
     * @return void
     */
    public function clearAttribute($key);

    /**
     * Deletes an node.
     * The containing list must be updated.
     * @return void
     */
    public function delete();

    /**
     * This will return the list that contains this element.
     * @return OrderedList
     */
    public function getList();

} 