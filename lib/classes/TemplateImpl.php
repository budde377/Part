<?php

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/15/12
 * Time: 8:31 AM
 */
class TemplateImpl implements Template
{

    /** @var  boolean */
    private $twigDebug = false;

    private $config;
    private $pageElementFactory;
    private $backendContainer;
    /** @var  DOMDocument */
    /** @var  Twig_Environment */
    private $twig;
    private $renderTarget;

    /**
     * @param PageElementFactory $pageElementFactory
     * @param BackendSingletonContainer $container
     * @internal param \Config $config
     */

    public function __construct(PageElementFactory $pageElementFactory, BackendSingletonContainer $container)
    {
        $this->config = $container->getConfigInstance();
        $this->pageElementFactory = $pageElementFactory;
        $this->backendContainer = $container;
    }


    /**
     * @param File $file
     * @throws FileNotFoundException
     * @return void
     */
    public function setTemplate(File $file)
    {

        if (!$file->exists()) {
            throw new FileNotFoundException($file->getAbsoluteFilePath(), 'template file');
        }
        $this->setUpTwig(new Twig_Loader_Filesystem($file->getParentFolder()->getAbsolutePath()), $file->getFilename());

    }

    /**
     * @param string $string The template as a string
     * @throws InvalidXMLException
     * @return void
     */
    public function setTemplateFromString($string)
    {
        $this->setUpTwig(new Twig_Loader_String(), $string);
    }


    /**
     * @param string $name The name of the template as defined in the config
     * @param string $defaultIfNotFound
     * @throws EntryNotFoundException
     * @return void
     */
    public function setTemplateFromConfig($name, $defaultIfNotFound=null)
    {
        $filename = $this->config->getTemplate($name);
        if($filename == null && $defaultIfNotFound != null){
            $filename = $this->config->getTemplate($defaultIfNotFound);
        }
        if ($filename === null) {
            throw new EntryNotFoundException($name, 'Config');
        }
        $file = new FileImpl($this->config->getTemplateFolderPath() . "/" . $filename);
        $this->setTemplate($file);
    }


    private function setUpTwig(Twig_LoaderInterface $loader, $renderTarget)
    {
        $loaderChain = new Twig_Loader_Chain(array($loader, new Twig_Loader_Filesystem(dirname(__FILE__) . '/../../templates/')));
        $configArray = array('debug' => $this->twigDebug);
        if($this->config->getTmpFolderPath() != null){
            $tmpFolder = new FolderImpl($this->config->getTmpFolderPath().'/twig/');
            $tmpFolder->create(true);
            $configArray['cache'] = $tmpFolder->getAbsolutePath();
        }
        $this->twig = new Twig_Environment($loaderChain, $configArray);
        if ($this->twigDebug) {
            $this->twig->addExtension(new Twig_Extension_Debug());
        }
        $this->twig->addExtension(new TemplateTwigExtensionImpl());
        $this->renderTarget = $renderTarget;

    }


    /**
     * @param boolean $twigDebug
     */
    public function setTwigDebug($twigDebug)
    {
        $this->twigDebug = $twigDebug;
    }

    /**
     * @return boolean
     */
    public function getTwigDebug()
    {
        return $this->twigDebug;
    }


    public function render()
    {
        return $this->privateRender();
    }

    /**
     * This function will set the initialize flag in the template and not
     * return the result of render.
     * @return void
     */
    public function onlyInitialize()
    {
        $this->privateRender(true);
    }

    private function privateRender($initialize = false)
    {
        $userLib = $this->backendContainer->getUserLibraryInstance();
        $currentPageStrat = $this->backendContainer->getCurrentPageStrategyInstance();
        $this->pageElementFactory->clearCache();
        $currentUser = $userLib->getUserLoggedIn();
        $currentPage = $currentPageStrat->getCurrentPage();
        return $this->twig->render($this->renderTarget, array(
            'current_user' => $currentUser,
            'has_root_privileges' => $currentUser != null && $currentUser->getUserPrivileges()->hasRootPrivileges(),
            'has_site_privileges' => $currentUser != null && $currentUser->getUserPrivileges()->hasSitePrivileges(),
            'has_page_privileges' => $currentUser != null && $currentUser->getUserPrivileges()->hasPagePrivileges($currentPage),
            'user_lib' => $userLib,
            'current_page' => $currentPage,
            'current_page_path' => $currentPageStrat->getCurrentPagePath(),
            'page_order' => $this->backendContainer->getPageOrderInstance(),
            'css_register' => $this->backendContainer->getCSSRegisterInstance(),
            'page_element_factory' => $this->pageElementFactory,
            'js_register' => $this->backendContainer->getJSRegisterInstance(),
            'site' => $this->backendContainer->getSiteInstance(),
            'debug_mode' => $this->config->isDebugMode(),
            'initialize' => $initialize
        ));

    }
}
