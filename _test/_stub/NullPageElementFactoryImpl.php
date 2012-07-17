<?php
require_once dirname(__FILE__) . '/../../_interface/PageElementFactory.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/29/12
 * Time: 10:56 AM
 * To change this template use File | Settings | File Templates.
 */
class NullPageElementFactoryImpl implements PageElementFactory
{

    /**
     * @param string $name The name of the PageElement
     * @return PageElement | null Will return null if the page element is not in list, else PageElement
     */
    public function getPageElement($name)
    {
        return null;
    }
}
