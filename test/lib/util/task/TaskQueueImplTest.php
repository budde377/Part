<?php
namespace ChristianBudde\Part\util\task;

use PHPUnit_Framework_TestCase;

/**
 * User: budde
 * Date: 5/16/12
 * Time: 9:02 AM
 */
class TaskQueueImplTest extends PHPUnit_Framework_TestCase
{

    public function testScriptChainWillRunAddedScriptOnce()
    {
        $script = new StubTaskImpl();
        $chain = new TaskQueueImpl();
        $before = $script->getNumRuns();
        $chain->addTask($script);
        $chain->execute();
        $after = $script->getNumRuns();
        $this->assertEquals(1, $after - $before, 'The Script did not run once');

    }

    public function testScriptChainWillRunScriptsInAddedOrder()
    {
        $script1 = new StubTaskImpl();
        $script2 = new StubTaskImpl();
        $chain = new TaskQueueImpl();
        $chain->addTask($script1);
        $chain->addTask($script2);
        $chain->execute();
        $this->assertGreaterThan($script1->lastRunAt(), $script2->lastRunAt(), "The Scripts did not run in added order.");
    }


    public function testScriptChainReturnsRight(){
        $script1 = new StubTaskImpl();
        $script1->result = 1;
        $script2 = new StubTaskImpl();
        $script2->result = 2;
        $chain = new TaskQueueImpl();
        $chain->addTask($script1);
        $chain->addTask($script2);
        $this->assertEquals([1,2], $chain->execute());
    }


    public function testLengthIsRight(){
        $this->assertEquals(0, (new TaskQueueImpl())->length());
    }

    public function testLengthIsUpdated(){
        $queue = new TaskQueueImpl();
        $queue->addTask(new StubTaskImpl());
        $this->assertEquals(1, $queue->length());
        $queue->addTask(new StubTaskImpl());
        $this->assertEquals(2, $queue->length());
    }

}
