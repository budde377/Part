<?php
namespace ChristianBudde\Part\util\file;

use ChristianBudde\Part\Config;
use ChristianBudde\Part\exception\ClassNotDefinedException;
use ChristianBudde\Part\exception\ClassNotInstanceOfException;
use ChristianBudde\Part\exception\FileNotFoundException;


/**
 * User: budde
 * Date: 6/11/12
 * Time: 9:41 AM
 */
class OptimizerFactoryImpl implements OptimizerFactory
{


    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $name
     * @throws ClassNotInstanceOfException
     * @throws FileNotFoundException
     * @throws ClassNotDefinedException
     * @return null|Optimizer Will return instance of Optimizer if $name exists, else null
     */
    public function getOptimizer($name)
    {
        $optimizer = $this->config->getOptimizer($name);
        if ($optimizer === null) {
            return null;
        }

        if (isset($optimizer['link'])) {
            if (!file_exists($location = $optimizer['link'])) {
                throw new FileNotFoundException($location, 'Optimizer');
            }
            require_once $location;
        }


        if (!class_exists($optimizer['className'])) {
            throw new ClassNotDefinedException($optimizer['className']);
        }

        $optimizerObject = new $optimizer['className']();

        if (!($optimizerObject instanceof Optimizer)) {
            throw new ClassNotInstanceOfException($optimizer['className'], 'Optimizer');
        }

        return $optimizerObject;

    }
}
