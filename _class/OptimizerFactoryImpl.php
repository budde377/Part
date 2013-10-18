<?php
require_once dirname(__FILE__) . '/../_interface/OptimizerFactory.php';
require_once dirname(__FILE__) . '/CSSYUICompressorOptimizerImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/11/12
 * Time: 9:41 AM
 * To change this template use File | Settings | File Templates.
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
     * @throws ClassNotDefinedException
     * @throws FileNotFoundException
     * @throws ClassNotInstanceOfException
     * @return null|\Optimizer Will return instance of Optimizer if $name exists, else null
     */
    public function getOptimizer($name)
    {
        $optimizer = $this->config->getOptimizer($name);
        if ($optimizer === null) {
            return null;
        }

        if (!file_exists($optimizer['link'])) {
            throw new FileNotFoundException($optimizer['link'], 'Optimizer');
        }

        require_once $optimizer['link'];

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
