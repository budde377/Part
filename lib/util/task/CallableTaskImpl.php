<?php
/**
 * User: budde
 * Date: 1/3/16
 * Time: 2:46 PM
 */

namespace ChristianBudde\Part\util\task;


class CallableTaskImpl implements Task
{

    private $callable;

    /**
     * CallableTaskImpl constructor.
     * @param $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }


    /**
     * This function runs the script
     * @return mixed
     */
    public function execute()
    {
        $callable = $this->callable;
        return $callable();
    }
}