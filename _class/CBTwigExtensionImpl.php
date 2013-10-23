<?php
require_once dirname(__FILE__).'/CBPageElementTokenParserImpl.php';
require_once dirname(__FILE__).'/CBInitPageElementTokenParserImpl.php';
require_once dirname(__FILE__).'/CBPageContentTokenParserImpl.php';
require_once dirname(__FILE__).'/CBSiteContentTokenParserImpl.php';
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 7:46 PM
 */

class CBTwigExtensionImpl extends  Twig_Extension{

    /** @var  PageElementFactory */
    private $pageElementFactory;
    /** @var  BackendSingletonContainer */
    private $backendContainer;

    function __construct(BackendSingletonContainer $backendContainer, PageElementFactory $pageElementFactory)
    {
        $this->backendContainer = $backendContainer;
        $this->pageElementFactory = $pageElementFactory;
    }


    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return "cb_extension";
    }

    public function getTokenParsers()
    {
        return array(new CBPageElementTokenParserImpl($this->pageElementFactory),
                     new CBInitPageElementTokenParserImpl($this->pageElementFactory),
                     new CBPageContentTokenParserImpl($this->backendContainer->getCurrentPageStrategyInstance()->getCurrentPage()),
                     new CBSiteContentTokenParserImpl($this->backendContainer->getSiteInstance()));
    }


}