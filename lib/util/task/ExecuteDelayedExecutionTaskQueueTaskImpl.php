<?php
/**
 * User: budde
 * Date: 1/3/16
 * Time: 2:56 PM
 */

namespace ChristianBudde\Part\util\task;


use ChristianBudde\Part\BackendSingletonContainer;

class ExecuteDelayedExecutionTaskQueueTaskImpl implements Task
{
    private $container;

    /**
     * ExecuteDelayedExecutionTaskQueueTaskImpl constructor.
     * @param BackendSingletonContainer $container
     */
    public function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
    }


    /**
     * This function runs the script
     * @return mixed
     */
    public function execute()
    {
        $queue = $this->container->getDelayedExecutionTaskQueue();
        if($queue->length() == 0){
            return;
        }
        ignore_user_abort(true);
        $queue->execute();
    }
}