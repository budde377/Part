<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 10:53 AM
 * To change this template use File | Settings | File Templates.
 */
interface PageElementFactory
{

    /**
     * @abstract
     * @param string $name The name of the PageElement
     * @return PageElement | null Will return null if the page element is not in list, else PageElement
     */
    public function getPageElement($name);

}
