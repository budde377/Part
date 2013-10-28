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
     * @param bool $cached IF set to false, it will return a new instance, else in will return a cached if such is present The cached will be the last new element returned.
     * @return PageElement | null Will return null if the page element is not in list, else PageElement
     */
    public function getPageElement($name, $cached = true);


    /**
     * Will clear cache
     * @return void
     */
    public function clearCache();
}
