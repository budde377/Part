<?php
namespace ChristianBudde\Part;

use ChristianBudde\Part\exception\ClassNotDefinedException;
use ChristianBudde\Part\exception\ClassNotInstanceOfException;
use ChristianBudde\Part\exception\FileNotFoundException;
use ChristianBudde\Part\util\task\Task;
use ChristianBudde\Part\util\task\TaskQueue;
use ChristianBudde\Part\util\task\TaskQueueImpl;

/**
 * User: budde
 * Date: 5/10/12
 * Time: 11:26 AM
 */
class SiteFactoryImpl implements SiteFactory
{
    private $config;

    public function __construct(Config $config)
    {

        $this->config = $config;
    }


    /**
     * Builds a new PreScriptChain and returns it. This must contain prescripts specified
     * in some config (it must be ready to run).
     *
     *
     * @param BackendSingletonContainer $backendContainer
     * @throws ClassNotDefinedException
     * @throws FileNotFoundException
     * @throws ClassNotInstanceOfException
     * @return TaskQueue
     */
    public function buildPreTaskQueue(BackendSingletonContainer $backendContainer)
    {
        return $this->buildTaskQueue($backendContainer, $this->config->getPreTasks());

    }

    /**
     * Builds a new PostScriptChain and returns it. This must contain prescripts specified
     * in some config (it must be ready to run).
     *
     *
     * @param BackendSingletonContainer $backendContainer
     * @throws ClassNotDefinedException
     * @throws FileNotFoundException
     * @throws ClassNotInstanceOfException
     * @return TaskQueue
     */
    public function buildPostTaskQueue(BackendSingletonContainer $backendContainer)
    {

        return $this->buildTaskQueue($backendContainer, $this->config->getPostTasks());
    }


    private function buildTaskQueue(BackendSingletonContainer $container, $scriptArray)
    {
        $chain = new TaskQueueImpl();

        foreach ($scriptArray as $className => $location) {

            if ($location !== null) {
                if (!file_exists($location)) {
                    throw new FileNotFoundException($location);
                }
                /** @noinspection PhpIncludeInspection */
                require_once $location;

            }

            if (!class_exists($className)) {
                throw new ClassNotDefinedException($className);
            }

            $preScript = new $className($container);

            if (!($preScript instanceof Task)) {
                throw new ClassNotInstanceOfException($className, 'Task');
            }

            $chain->addTask($preScript);
        }

        return $chain;
    }

    /**
     * Builds and returns a new Config
     * @return Config
     */
    public function buildConfig()
    {
        return clone $this->config;
    }


    /**
     * Builds a new BackendSingletonContainer and returns it.
     * @param Config $config
     * @return BackendSingletonContainer
     */
    public function buildBackendSingletonContainer(Config $config)
    {
        return new BackendSingletonContainerImpl($config);
    }
}
