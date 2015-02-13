<?php
namespace ChristianBudde\Part\view\page_element;
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\util\file\CSSFile;
use ChristianBudde\Part\util\file\DartFile;
use ChristianBudde\Part\util\file\JSFile;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 26/07/12
 * Time: 11:44
 */
class HeadPageElementImpl extends PageElementImpl
{

    private $cssRegister;
    private $jsRegister;
    private $dartRegister;
    private $config;

    public function __construct(BackendSingletonContainer $container){
        $this->cssRegister = $container->getCSSRegisterInstance();
        $this->jsRegister = $container->getJSRegisterInstance();
        $this->dartRegister = $container->getDartRegisterInstance();
        $this->config = $container->getConfigInstance();
    }

    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent()
    {
        parent::generateContent();
        $output = '';
        $cssFiles = $this->cssRegister->getRegisteredFiles();
        foreach($cssFiles as $file){
            /** @var $file CSSFile */
            $path = $file->getRelativeFilePathTo(dirname(__FILE__)."/../../");
            $output .= "<link href='/$path' rel='stylesheet' type='text/css' /> \r\n";
        }
        $jsFiles = $this->jsRegister->getRegisteredFiles();
        foreach($jsFiles as $file){
            /** @var $file JSFile */
            $path = $file->getRelativeFilePathTo(dirname(__FILE__)."/../../");
            $output .= "<script type='text/javascript' src='/$path'></script> \r\n";

        }
        $dartFiles = $this->dartRegister->getRegisteredFiles();
        foreach($dartFiles as $file){
            /** @var $file DartFile */
           $path = $file->getRelativeFilePathTo(dirname(__FILE__).'/../../');
           $output .= "<script type='application/dart' src='/$path'></script>\r\n";
        }
        return $output;
    }

}
