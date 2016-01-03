<?php
namespace ChristianBudde\Part;
use ChristianBudde\Part\util\task\TaskQueue;

/**
 * User: budde
 * Date: 5/10/12
 * Time: 10:52 AM
 */
interface SiteFactory
{

    /**
     * @abstract
     * Builds a new PreScriptChain and returns it. This must contain prescripts specified
     * in some config (it must be ready to run).
     * @param BackendSingletonContainer $backendContainer
     * @return TaskQueue
     */
    public function buildPreTaskQueue(BackendSingletonContainer $backendContainer);


    /**
     * @abstract
     * Builds a new PostScriptChain and returns it. This must contain prescripts specified
     * in some config (it must be ready to run).
     * @param BackendSingletonContainer $backendContainer
     * @return TaskQueue
     */
    public function buildPostTaskQueue(BackendSingletonContainer $backendContainer);

    /**
     * @abstract
     * Builds and returns a new Config
     * @return Config
     */
    public function buildConfig();


    /**
     * @abstract
     * Builds a new BackendSingletonContainer and returns it.
     * @param Config $config
     * @return BackendSingletonContainer
     */
    public function buildBackendSingletonContainer(Config $config);


}
