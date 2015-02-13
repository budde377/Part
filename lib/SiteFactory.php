<?php
namespace ChristianBudde\Part;
use ChristianBudde\Part\util\script\ScriptChain;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 10:52 AM
 * To change this template use File | Settings | File Templates.
 */
interface SiteFactory
{

    /**
     * @abstract
     * Builds a new PreScriptChain and returns it. This must contain prescripts specified
     * in some config (it must be ready to run).
     * @param BackendSingletonContainer $backendContainer
     * @return ScriptChain
     */
    public function buildPreScriptChain(BackendSingletonContainer $backendContainer);


    /**
     * @abstract
     * Builds a new PostScriptChain and returns it. This must contain prescripts specified
     * in some config (it must be ready to run).
     * @param BackendSingletonContainer $backendContainer
     * @return ScriptChain
     */
    public function buildPostScriptChain(BackendSingletonContainer $backendContainer);

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
