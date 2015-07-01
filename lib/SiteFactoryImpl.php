<?php
namespace ChristianBudde\Part;

use ChristianBudde\Part\exception\ClassNotDefinedException;
use ChristianBudde\Part\exception\ClassNotInstanceOfException;
use ChristianBudde\Part\exception\FileNotFoundException;
use ChristianBudde\Part\util\script\Script;
use ChristianBudde\Part\util\script\ScriptChain;
use ChristianBudde\Part\util\script\ScriptChainImpl;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 11:26 AM
 * To change this template use File | Settings | File Templates.
 */
class SiteFactoryImpl implements SiteFactory
{
    private $config;
    private $preScriptChain;
    private $postScriptChain;
    private $backendContainer;

    public function __construct(Config $config)
    {

        $this->config = $config;
    }


    /**
     * Builds a new PreScriptChain and returns it. This must contain prescripts specified
     * in some config (it must be ready to run).
     *
     *
     * @throws ClassNotDefinedException
     * @throws FileNotFoundException
     * @throws ClassNotInstanceOfException
     * @return ScriptChain
     */
    public function buildPreScriptChain()
    {
        return $this->preScriptChain == null ? $this->preScriptChain = $this->buildScriptChain($this->buildBackendSingletonContainer(), $this->config->getPreScripts()) : $this->preScriptChain;

    }

    /**
     * Builds a new PostScriptChain and returns it. This must contain prescripts specified
     * in some config (it must be ready to run).
     *
     *
     * @throws ClassNotDefinedException
     * @throws FileNotFoundException
     * @throws ClassNotInstanceOfException
     * @return ScriptChain
     */
    public function buildPostScriptChain()
    {

        return $this->postScriptChain == null?$this->postScriptChain = $this->buildScriptChain($this->buildBackendSingletonContainer(), $this->config->getPostScripts()):$this->postScriptChain;
    }


    private function buildScriptChain(BackendSingletonContainer $container, $scriptArray)
    {
        $chain = new ScriptChainImpl();

        foreach ($scriptArray as $className => $location) {

            if ($location !== null) {
                if (!file_exists($location)) {
                    throw new FileNotFoundException($location);
                }
                require_once $location;

            }

            if (!class_exists($className)) {
                throw new ClassNotDefinedException($className);
            }

            $preScript = new $className($container);

            if (!($preScript instanceof Script)) {
                throw new ClassNotInstanceOfException($className, 'Script');
            }

            $chain->addScript($preScript);
        }

        return $chain;
    }

    /**
     * Builds and returns a new Config
     * @return Config
     */
    public function buildConfig()
    {
        return $this->config;
    }


    /**
     * Builds a new BackendSingletonContainer and returns it.
     * @return BackendSingletonContainer
     */
    public function buildBackendSingletonContainer()
    {
        return $this->backendContainer == null?$this->backendContainer = new BackendSingletonContainerImpl($this->buildConfig()):$this->backendContainer;
    }
}
