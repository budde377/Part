<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 10:53 AM
 * To change this template use File | Settings | File Templates.
 */
interface PageElement
{

    /**
     * @abstract
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent();
}
