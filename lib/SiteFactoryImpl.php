<?php
namespace ChristianBudde\Part;

use ChristianBudde\Part\exception\ClassNotDefinedException;
use ChristianBudde\Part\exception\ClassNotInstanceOfException;
use ChristianBudde\Part\exception\FileNotFoundException;
use ChristianBudde\Part\util\script\Script;
use ChristianBudde\Part\util\script\ScriptChain;
use ChristianBudde\Part\util\script\ScriptChainImpl;

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
     * @return ScriptChain
     */
    public function buildPreScriptChain(BackendSingletonContainer $backendContainer)
    {
        return $this->buildScriptChain($backendContainer, $this->config->getPreScripts());

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
     * @return ScriptChain
     */
    public function buildPostScriptChain(BackendSingletonContainer $backendContainer)
    {

        return $this->buildScriptChain($backendContainer, $this->config->getPostScripts());
    }


    private function buildScriptChain(BackendSingletonContainer $container, $scriptArray)
    {
        $chain = new ScriptChainImpl();

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
