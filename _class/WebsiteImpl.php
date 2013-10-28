<?php
require_once dirname(__FILE__) . '/../_interface/Website.php';
require_once dirname(__FILE__) . '/../_trait/RequestTrait.php';
require_once dirname(__FILE__) . '/TemplateImpl.php';
require_once dirname(__FILE__) . '/BackendSingletonContainerImpl.php';
require_once dirname(__FILE__) . '/PageElementFactoryImpl.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 11:03 AM
 * To change this template use File | Settings | File Templates.
 */
class WebsiteImpl implements Website
{
    private $factory;
    private $backendContainer;
    private $config;

    use RequestTrait;

    public function __construct(SiteFactory $factory)
    {
        $this->factory = $factory;
        $this->config = $factory->buildConfig();
        $this->backendContainer = $factory->buildBackendSingletonContainer($this->config);

        // RUN PRESCRIPTS
        $preScriptChain = $factory->buildPreScriptChain($this->backendContainer);
        $preScriptChain->run(Website::WEBSITE_SCRIPT_TYPE_PRESCRIPT, null);

    }

    /**
     * Generate site and output it in browser.
     */
    public function generateSite()
    {

        $elementFactory = new PageElementFactoryImpl($this->config, $this->backendContainer);
        $template = new TemplateImpl($elementFactory, $this->backendContainer);
        $pageStrategy = $this->backendContainer->getCurrentPageStrategyInstance();

        $currentPage = $pageStrategy->getCurrentPage();
        $template->setTemplateFromConfig($currentPage->getTemplate());

        $ajaxRegister = $this->backendContainer->getAJAXRegisterInstance();
        if (($id = $this->GETValueOfIndexIfSetElseDefault('ajax', null)) !== null) {
            $template->onlyInitialize();
            $ajaxRegister->registerAJAXFromConfig($this->config);
        }

        $cacheControl = $this->backendContainer->getCacheControlInstance();
        if($this->config->isDebugMode()){
            $cacheControl->disableCache();
        }

        //Decide output mode
        if ($id !== null && ($ajax = $ajaxRegister->getAJAXFromRegisteredFromFunctionName($id)) !== null) {
            echo $ajax;
        } else if(!$cacheControl->setUpCache()){
            echo $template->render();
        }

    }


    public function __destruct()
    {

        // RUN POSTSCRIPTS

        $postScriptChain = $this->factory->buildPostScriptChain($this->backendContainer);
        $postScriptChain->run(Website::WEBSITE_SCRIPT_TYPE_POSTSCRIPT, null);

    }


}
