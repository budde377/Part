<?php
require_once dirname(__FILE__) . '/../../_interface/PageElement.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/29/12
 * Time: 11:21 AM
 * To change this template use File | Settings | File Templates.
 */
class HTMLPageElementImpl implements PageElement
{

    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function getContent()
    {
        return "<b>Hello World</b>";
    }
}
