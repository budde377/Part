<?php
namespace ChristianBudde\cbweb;

use ChristianBudde\cbweb\util\traits\RequestTrait;

use ChristianBudde\cbweb\view\template\TemplateImpl;

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

        $elementFactory = new view\page_element\PageElementFactoryImpl($this->config, $this->backendContainer);
        $template = new TemplateImpl($elementFactory, $this->backendContainer);
        $pageStrategy = $this->backendContainer->getCurrentPageStrategyInstance();


        $cacheControl = $this->backendContainer->getCacheControlInstance();
        if ($this->config->isDebugMode()) {
            $cacheControl->disableCache();
            $template->setTwigDebug(true);
        }


        $currentPage = $pageStrategy->getCurrentPage();
        $template->setTemplateFromConfig($currentPage->getTemplate(), "_main");

        $ajaxServer = $this->backendContainer->getAJAXServerInstance();
        $id = null;
        if (
            (!isset($_SESSION['user-login-token']) ||
                $_SESSION['user-login-token'] == $this->GETValueOfIndexIfSetElseDefault('token', null)) &&
            ($id = $this->GETValueOfIndexIfSetElseDefault('ajax', null)) !== null
        ) {
            $template->onlyInitialize();
            $ajaxServer->registerHandlersFromConfig();
        }

        //Decide output mode
        if ($id !== null) {
            echo $ajax = $ajaxServer->handleFromFunctionString($id)->getAsJSONString();

        } else if (!$cacheControl->setUpCache()) {
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
