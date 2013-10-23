<?php
require_once dirname(__FILE__).'/../../_interface/PageElement.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 13/12/12
 * Time: 01:38
 */
class CheckInitializedPageElementImpl implements PageElement
{

    public function __construct()
    {
        if(!isset($_SESSION['initialized'])){
            $_SESSION['initialized'] = 0;
        }
        $_SESSION['initialized']++;
    }


    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent()
    {
        return '';
    }

}
