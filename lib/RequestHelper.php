<?php
namespace ChristianBudde\cbweb;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/13/12
 * Time: 7:24 PM
 * To change this template use File | Settings | File Templates.
 */
class RequestHelper
{


    public static function URLFromGET()
    {
        return '?' . http_build_query($_GET);
    }

    public static function URLWithoutVariableFromGET($variable)
    {
        $getAr = $_GET;
        unset($getAr[$variable]);
        return '?' . http_build_query($getAr);
    }


    public static function GETValueOfIndexIfSetElseDefault($index, $default = null)
    {
        return isset($_GET[$index]) ? $_GET[$index] : $default;
    }

    public static function POSTValueOfIndexIfSetElseDefault($index, $default = null)
    {
        return isset($_POST[$index]) ? $_POST[$index] : $default;
    }

    public static function COOKIEValueOfIndexIfSetElseDefault($index, $default = null)
    {
        return isset($_COOKIE[$index]) ? $_COOKIE[$index] : $default;
    }

    public static function SESSIONValueOfIndexIfSetElseDefault($index, $default = null)
    {
        return isset($_SESSION[$index]) ? $_SESSION[$index] : $default;
    }


}
