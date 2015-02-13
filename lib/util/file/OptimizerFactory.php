<?php
namespace ChristianBudde\Part\util\file;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/11/12
 * Time: 9:38 AM
 * To change this template use File | Settings | File Templates.
 */
interface OptimizerFactory
{
    /**
     * @abstract
     * @param string $name
     * @return Optimizer | null Will return instance of Optimizer if $name exists, else null
     */
    public function getOptimizer($name);

}
