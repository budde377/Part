<?php
namespace ChristianBudde\Part\util\task;
use ChristianBudde\Part\exception\TaskHasRunException;

/**
 * User: budde
 * Date: 5/28/12
 * Time: 3:03 PM
 */
class ExceptionStubTaskImpl implements Task
{



    public function execute()
    {
        throw new TaskHasRunException();
    }
}
