<?php
require_once dirname(__FILE__) . '/../_vendor/autoload.php';
require_once dirname(__FILE__) . '/FileImpl.php';
require_once dirname(__FILE__) . '/TemplateTwigExtensionImpl.php';
require_once dirname(__FILE__) . '/../_interface/Template.php';
require_once dirname(__FILE__) . '/../_exception/FileNotFoundException.php';
require_once dirname(__FILE__) . '/../_exception/EntryNotFoundException.php';
require_once dirname(__FILE__) . '/../_helper/HTTPHeaderHelper.php';

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
        $this->setUpTwig(new Twig_Loader_Filesystem($file->getParentFolder()->getAbsolutePath()), $file->getBaseName());

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
     * @throws EntryNotFoundException
     * @return void
     */
    public function setTemplateFromConfig($name)
    {
        $filename = $this->config->getTemplate($name);
        if ($filename === null) {
            throw new EntryNotFoundException($name, 'Config');
        }
        $file = new FileImpl($this->config->getTemplateFolderPath()."/".$filename);
        $this->setTemplate($file);
    }



    private function setUpTwig(Twig_LoaderInterface $loader, $renderTarget){
        $loaderChain = new Twig_Loader_Chain(array($loader, new Twig_Loader_Filesystem(dirname(__FILE__).'/../_template/')));
        $this->twig = new Twig_Environment($loaderChain, array('debug'=>$this->twigDebug));
        if($this->twigDebug){
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

    private function privateRender($initialize = false){
        $userLib = $this->backendContainer->getUserLibraryInstance();
        $currentPageStrat = $this->backendContainer->getCurrentPageStrategyInstance();
        $this->pageElementFactory->clearCache();
        return $this->twig->render($this->renderTarget, array(
            'current_user' => $userLib->getUserLoggedIn(),
            'user_lib' => $userLib,
            'current_page' => $currentPageStrat->getCurrentPage(),
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
