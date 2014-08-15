<?php

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
     * @return \ScriptChain
     */
    public function buildPreScriptChain(BackendSingletonContainer $backendContainer)
    {
        $chain = new ScriptChainImpl();

        $preScriptArray = $this->config->getPreScripts();
        foreach ($preScriptArray as $className => $location) {

            if($location !== null){
                if (!file_exists($location)) {
                    throw new FileNotFoundException($location);
                }
                require_once $location;

            }


            if (!class_exists($className)) {
                throw new ClassNotDefinedException($className);
            }


            $preScript = new $className($backendContainer);

            if (!($preScript instanceof Script)) {
                throw new ClassNotInstanceOfException($className, 'Script');
            }

            $chain->addScript($preScript);
        }

        return $chain;
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
     * @return \ScriptChain
     */
    public function buildPostScriptChain(BackendSingletonContainer $backendContainer)
    {
        $chain = new ScriptChainImpl();

        $postScriptArray = $this->config->getPostScripts();
        foreach ($postScriptArray as $className => $location) {

            if($location !== null){
                if (!file_exists($location)) {
                    throw new FileNotFoundException($location);
                }
                require_once $location;

            }

            if (!class_exists($className)) {
                throw new ClassNotDefinedException($className);
            }

            $preScript = new $className();

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
