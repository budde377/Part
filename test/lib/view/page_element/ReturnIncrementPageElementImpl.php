<?php
namespace ChristianBudde\Part\view\page_element;


/**
 * User: budde
 * Date: 10/27/12
 * Time: 10:48 AM
 */
class ReturnIncrementPageElementImpl extends PageElementImpl
{

    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent()
    {
        parent::generateContent();
        if (!isset($_SESSION['inc'])) {
            $_SESSION['inc'] = 0;
        }
        $_SESSION['inc']++;
        return $_SESSION['inc'];

    }

}
