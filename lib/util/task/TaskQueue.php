<?php
namespace ChristianBudde\Part\util\task;

/**
 * User: budde
 * Date: 5/10/12
 * Time: 10:51 AM
 */
interface TaskQueue
{
    /**
     * @abstract
     * Runs all scripts in chain in the order submitted.
     * Returns an array containing the result of
     * executing each script. In order.
     * @return mixed[]
     */
    public function execute();

    /**
     * @abstract
     * Adds a script to the chain
     * @param Task $script
     */
    public function addTask(Task $script);


    /**
     * The current size of the queue
     * @return int
     */
    public function length();

}
