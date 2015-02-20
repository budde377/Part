<?php
namespace ChristianBudde\Part\view\template;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\exception\EntryNotFoundException;
use ChristianBudde\Part\exception\FileNotFoundException;
use ChristianBudde\Part\exception\InvalidXMLException;
use ChristianBudde\Part\util\file\File;
use ChristianBudde\Part\util\file\FileImpl;
use ChristianBudde\Part\util\file\FolderImpl;
use ChristianBudde\Part\view\page_element\PageElementFactory;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Loader_Chain;
use Twig_Loader_Filesystem;
use Twig_Loader_String;
use Twig_LoaderInterface;

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
    /** @var  Twig_Environment */
    private $twig;
    private $renderTarget;

    /**
     * @param PageElementFactory $pageElementFactory
     * @param BackendSingletonContainer $container
     */

    public function __construct(PageElementFactory $pageElementFactory, BackendSingletonContainer $container)
    {
        $this->config = $container->getConfigInstance();
        $this->pageElementFactory = $pageElementFactory;
        $this->backendContainer = $container;
    }


    /**
     * @param File $file
     * @throws \ChristianBudde\Part\exception\FileNotFoundException
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
        $fn = uniqid("string_file_");
        $this->setUpTwig(new \Twig_Loader_Array([$fn=>$string]), $fn);
    }


    /**
     * @param string $name The name of the template as defined in the config
     * @param string $defaultIfNotFound
     * @throws \ChristianBudde\Part\exception\EntryNotFoundException
     * @return void
     */
    public function setTemplateFromConfig($name, $defaultIfNotFound = null)
    {
        $filename = $this->config->getTemplate($name);
        if ($filename == null && $defaultIfNotFound != null) {
            $name = $defaultIfNotFound;
            $filename = $this->config->getTemplate($defaultIfNotFound);
        }
        if ($filename === null) {
            throw new EntryNotFoundException($name, 'Config');
        }
        $file = new FileImpl($this->config->getTemplateFolderPath($name) . "/" . $filename);
        $this->setTemplate($file);
    }


    private function setUpTwig(Twig_LoaderInterface $loader, $renderTarget)
    {
        $fsLoader = new Twig_Loader_Filesystem();
        foreach($this->config->listTemplateFolders() as $folder){

            if(is_array($folder)){
                $fsLoader->addPath($folder['path'], $folder['namespace']);
            } else {
                $fsLoader->addPath($folder);
            }
        }
        $loaderChain = new Twig_Loader_Chain([$loader, $fsLoader]);
        $configArray = array('debug' => $this->twigDebug);
        if ($this->config->getTmpFolderPath() != null) {
            $tmpFolder = new FolderImpl($this->config->getTmpFolderPath() . '/twig/');
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
        $site = $this->backendContainer->getSiteInstance();
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
            'site' => $site,
            'debug_mode' => $this->config->isDebugMode(),
            'initialize' => $initialize,
            'last_modified' => max($currentPage->lastModified(), $site->lastModified()),
            'updater' => $this->backendContainer->getUpdaterInstance(),
            'config' => $this->config,
            'backend_container' => $this->backendContainer
        ));

    }
}
