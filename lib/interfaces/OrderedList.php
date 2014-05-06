<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/5/14
 * Time: 2:59 PM
 */

interface OrderedList extends Iterator {

    /**
     * Will return the list id.
     * @return string
     */
    public function getId();

    /**
     * @return array An ordered array of OrderedListElement.
     */
    public function listElements();

    /**
     * Moves the given element one position up in the order.
     * If the element is the first element, nothing will happen.
     * If the element isn't an instance provided by the list, behaviour is undefined.
     *
     * @param OrderedListElement $element
     * @return void
     */
    public function moveUp(OrderedListElement $element);

    /**
     * Moves the given element one position down in the order.
     * If the element is the last element, nothing happen.
     * If the element isn't an instance provided by the list, behaviour is undefined.
     *
     * @param OrderedListElement $element
     * @return mixed
     */
    public function moveDown(OrderedListElement $element);

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
    public function setElementOrder(OrderedListElement $element, $place);

    /**
     * Returns the order of an given element.
     * If the element isn't an instance provided by the list, behaviour is undefined.
     *
     * @param OrderedListElement $element
     * @return int
     */
    public function getElementOrder(OrderedListElement $element);


    /**
     * Creates a fresh element at the end of the list.
     *
     * @return OrderedListElement
     */
    public function createElement();

    /**
     * Deletes an element.
     *
     * @param OrderedListElement $element
     * @return void
     */
    public function deleteElement(OrderedListElement $element);


    /**
     * Check if element is in list.
     * @param OrderedListElement $element
     * @return bool
     */
    public function isInList(OrderedListElement $element);

    /**
     * @param $place
     * @return OrderedListElement | null Returns the element at given place,
     * if no such element; returns null.
     */
    public function getElementAt($place);

    /**
     * Returns the first element.
     * @return OrderedListElement
     */
    public function firstElement();

    /**
     * Returns the last element.
     * @return OrderedListElement
     */
    public function lastElement();

    /**
     * @return int Size of the list
     */
    public function size();
} 