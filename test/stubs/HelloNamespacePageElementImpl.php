<?php
namespace TestNamespace\test;
use ChristianBudde\cbweb\PageElementImpl;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/29/12
 * Time: 11:21 AM
 * To change this template use File | Settings | File Templates.
 */
class HelloNamespacePageElementImpl extends PageElementImpl
{

    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent()
    {
        parent::generateContent();
        return "Hello World";
    }

}
