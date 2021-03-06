<?php
namespace ChristianBudde\Part;

use ChristianBudde\Part\util\traits\RequestTrait;
use ChristianBudde\Part\view\template\TemplateImpl;

/**
 * User: budde
 * Date: 5/10/12
 * Time: 11:03 AM
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
        $preScriptChain = $factory->buildPreTaskQueue($this->backendContainer);
        $preScriptChain->execute();

    }

    /**
     * Generate site and output it in browser.
     */
    public function generateSite()
    {

        $template = new TemplateImpl($this->backendContainer);
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
        if (($id = $this->GETValueOfIndexIfSetElseDefault('ajax')) !== null) {
            $template->onlyInitialize();
            $ajaxServer->registerHandlersFromConfig();
        }

        //Decide output mode
        if ($id !== null) {
            echo json_encode($ajaxServer->handleFromFunctionString($id, $this->GETValueOfIndexIfSetElseDefault('token')), $this->config->isDebugMode()?JSON_PRETTY_PRINT:0);

        } else if (!$cacheControl->setUpCache()) {
            echo $template->render();
        }

    }


    public function __destruct()
    {

        // RUN POSTSCRIPTS

        $postScriptChain = $this->factory->buildPostTaskQueue($this->backendContainer);
        $postScriptChain->execute();

    }


}
