<?php
namespace ChristianBudde\Part\view\page_element;
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\exception\ClassNotDefinedException;
use ChristianBudde\Part\exception\ClassNotInstanceOfException;
use ChristianBudde\Part\exception\FileNotFoundException;
use ChristianBudde\Part\view;


/**
 * User: budde
 * Date: 5/29/12
 * Time: 11:10 AM
 */
class PageElementFactoryImpl implements PageElementFactory
{

    private $config;
    private $cache = array();
    private $backendSingletonFactory;

    public function __construct(BackendSingletonContainer $backendSingletonFactory)
    {
        $this->backendSingletonFactory = $backendSingletonFactory;
        $this->config = $backendSingletonFactory->getConfigInstance();
    }

    /**
     * {@inheritdoc}
     */
    public function getPageElement($name, $cached = true)
    {
        if($cached && isset($this->cache[$name])){
            return $this->cache[$name];
        }

        $element = $this->config->getPageElement($name);
        if($element === null){
            if(!class_exists($name)){
                return null;
            }

            $className = $name;


        } else {
            if(isset($element['link'])){
                if (!file_exists($element['link'])) {
                    throw new FileNotFoundException($element['link'], 'PageElement');
                }
                /** @noinspection PhpIncludeInspection */
                require_once $element['link'];
            }
            if (!class_exists($element['className'])) {
                throw new ClassNotDefinedException($element['className']);
            }
            $className = $element['className'];
        }



        $elementObject = new $className($this->backendSingletonFactory);

        if (!($elementObject instanceof view\page_element\PageElement)) {
            throw new ClassNotInstanceOfException($className, 'PageElement');
        }

        return $this->cache[$name] = $elementObject;


    }

    /**
     * Will clear cache
     * @return void
     */
    public function clearCache()
    {
        $this->cache = array();
    }
}
