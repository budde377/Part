<?php
require_once dirname(__FILE__) . '/../_interface/PageElementFactory.php';
require_once dirname(__FILE__) . '/../_exception/FileNotFoundException.php';
require_once dirname(__FILE__) . '/../_exception/ClassNotInstanceOfException.php';
require_once dirname(__FILE__) . '/../_exception/ClassNotDefinedException.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/29/12
 * Time: 11:10 AM
 * To change this template use File | Settings | File Templates.
 */
class PageElementFactoryImpl implements PageElementFactory
{

    private $config;
    private $backendSingletonFactory;

    public function __construct(Config $config, BackendSingletonContainer $backendSingletonFactory)
    {
        $this->backendSingletonFactory = $backendSingletonFactory;
        $this->config = $config;
    }

    /**
     * @param string $name The name of the PageElement
     * @throws ClassNotDefinedException
     * @throws FileNotFoundException
     * @throws ClassNotInstanceOfException
     * @return null|\PageElement Will return null if the page element is not in list, else PageElement
     */
    public function getPageElement($name)
    {

        $element = $this->config->getPageElement($name);
        if ($element === null) {
            return null;
        }


        if (!file_exists($element['link'])) {
            throw new FileNotFoundException($element['link'], 'PageElement');
        }
        require_once $element['link'];
        if (!class_exists($element['className'])) {
            throw new ClassNotDefinedException($element['className']);
        }

        $elementObject = new $element['className']($this->backendSingletonFactory);

        if (!($elementObject instanceof PageElement)) {
            throw new ClassNotInstanceOfException($element['className'], 'PageElement');
        }

        return $elementObject;


    }
}
