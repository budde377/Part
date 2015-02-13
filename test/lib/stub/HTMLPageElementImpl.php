<?php

namespace ChristianBudde\Part\test\stub;
use ChristianBudde\Part\view\page_element\PageElementImpl;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/29/12
 * Time: 11:21 AM
 * To change this template use File | Settings | File Templates.
 */
class HTMLPageElementImpl extends PageElementImpl
{

    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent()
    {
        parent::generateContent();
        return "<b>Hello World</b>";
    }

}
