<?php
namespace ChristianBudde\Part;

use ChristianBudde\Part\util\task\TaskQueue;
use ChristianBudde\Part\util\task\TaskQueueImpl;

/**
 * User: budde
 * Date: 5/28/12
 * Time: 3:55 PM
 */
class StubSiteFactoryImpl implements SiteFactory
{

    private $preScriptChain;
    private $postScriptChain;

    private $config;

    private $backendSingletonContainer;

    public function __construct()
    {
        $this->preScriptChain = new TaskQueueImpl();
        $this->postScriptChain = new TaskQueueImpl();
    }

    /**
     * Builds a new PreScriptChain and returns it. This must contain prescripts specified
     * in some config (it must be ready to run).
     * @param BackendSingletonContainer $backendContainer
     * @return \ChristianBudde\Part\util\task\TaskQueue
     */
    public function buildPreTaskQueue(BackendSingletonContainer $backendContainer)
    {
        return $this->preScriptChain;
    }

    /**
     * Builds a new PostScriptChain and returns it. This must contain prescripts specified
     * in some config (it must be ready to run).
     * @param BackendSingletonContainer $backendContainer
     * @return \ChristianBudde\Part\util\task\TaskQueue
     */
    public function buildPostTaskQueue(BackendSingletonContainer $backendContainer)
    {
        return $this->postScriptChain;
    }


    public function buildConfig()
    {
        return $this->config;
    }


    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function setPostScriptChain($postScriptChain)
    {
        $this->postScriptChain = $postScriptChain;
    }

    /**
     * @param TaskQueue $preScriptChain
     */
    public function setPreScriptChain($preScriptChain)
    {
        $this->preScriptChain = $preScriptChain;
    }


    /**
     * @param BackendSingletonContainer $backendSingletonContainer
     */
    public function setBackendSingletonContainer($backendSingletonContainer)
    {
        $this->backendSingletonContainer = $backendSingletonContainer;
    }


    /**
     * Builds a new BackendSingletonContainer and returns it.
     * @param Config $config
     * @return BackendSingletonContainer
     */
    public function buildBackendSingletonContainer(Config $config)
    {
        return $this->backendSingletonContainer;
    }
}
