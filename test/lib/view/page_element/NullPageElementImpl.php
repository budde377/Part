<?php

namespace ChristianBudde\Part\view\page_element;

/**
 * User: budde
 * Date: 5/29/12
 * Time: 11:21 AM
 */
class NullPageElementImpl extends PageElementImpl
{

    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent()
    {
        parent::generateContent();
        return '';
    }
}