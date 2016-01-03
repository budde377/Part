<?php
namespace ChristianBudde\Part\util\task;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/11/12
 * Time: 12:46 PM
 */
class TaskQueueImpl implements TaskQueue
{

    private $queue = array();


    public function execute()
    {
        return array_map(function (Task $script){
            return $script->run();
        }, $this->queue);

    }

    /**
     * Adds a script to the chain
     * @param Task $script
     */
    public function addTask(Task $script)
    {
        $this->queue[] = $script;
    }

    /**
     * The current size of the queue
     * @return int
     */
    public function length()
    {
        return count($this->queue);
    }
}
