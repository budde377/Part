<?php
/**
 * User: budde
 * Date: 1/3/16
 * Time: 2:57 PM
 */

namespace ChristianBudde\Part\util\task;


use ChristianBudde\Part\StubBackendSingletonContainerImpl;

class ExecuteDelayedExecutionTaskQueueTaskImplTest extends \PHPUnit_Framework_TestCase
{
    /** @var  StubBackendSingletonContainerImpl */
    private $container;
    /** @var  ExecuteDelayedExecutionTaskQueueTaskImpl */
    private $task;
    /** @var  TaskQueue */
    private $queue;

    protected function setUp()
    {
        parent::setUp();
        $this->queue = new TaskQueueImpl();
        $this->container = new StubBackendSingletonContainerImpl();
        $this->container->setDelayedExecutionTaskQueue($this->queue);
        $this->task = new ExecuteDelayedExecutionTaskQueueTaskImpl($this->container);
    }


    public function testNotSetsIniIfQueueIsEmpty(){
        $this->task->execute();
        $this->assertEquals(0, ignore_user_abort());
    }

    public function testSetIniIfNotEmpty(){
        $this->queue->addTask(new StubTaskImpl());
        $this->task->execute();
        $this->assertEquals(1, ignore_user_abort());
    }

    public function testRunsTasks(){
        $this->queue->addTask($task1 = new StubTaskImpl());
        $this->queue->addTask($task2 = new StubTaskImpl());
        $this->task->execute();
        $this->assertEquals(1, ignore_user_abort());
        $this->assertEquals(1, $task1->getNumRuns());
        $this->assertEquals(1, $task2->getNumRuns());

    }

    protected function tearDown()
    {
        parent::tearDown();
        ignore_user_abort(false);
    }


}