<?php
namespace ChristianBudde\Part\util\traits;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/27/12
 * Time: 11:31 AM
 * To change this template use File | Settings | File Templates.
 */
trait RequestTrait
{

    protected function URLFromGET()
    {
        return '?' . http_build_query($_GET);
    }

    protected function URLWithoutVariableFromGET($variable)
    {
        $getAr = $_GET;
        unset($getAr[$variable]);
        return '?' . http_build_query($getAr);
    }


    protected function GETValueOfIndexIfSetElseDefault($index, $default = null)
    {
        return isset($_GET[$index]) ? $_GET[$index] : $default;
    }

    protected function POSTValueOfIndexIfSetElseDefault($index, $default = null)
    {
        return isset($_POST[$index]) ? $_POST[$index] : $default;
    }

    protected function COOKIEValueOfIndexIfSetElseDefault($index, $default = null)
    {
        return isset($_COOKIE[$index]) ? $_COOKIE[$index] : $default;
    }

    protected function SESSIONValueOfIndexIfSetElseDefault($index, $default = null)
    {
        return isset($_SESSION[$index]) ? $_SESSION[$index] : $default;
    }
}
