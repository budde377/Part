<?php
require_once dirname(__FILE__).'/../../_interface/PageElement.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/27/12
 * Time: 10:48 AM
 * To change this template use File | Settings | File Templates.
 */
class ReturnIncrementPageElementImpl implements PageElement
{

    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent()
    {
        if(!isset($_SESSION['inc'])){
            $_SESSION['inc'] = 0;
        }
        $_SESSION['inc']++;
        return $_SESSION['inc'];

    }
}
