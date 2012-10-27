<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/27/12
 * Time: 11:31 AM
 * To change this template use File | Settings | File Templates.
 */
trait RequestTrait
{

    public function URLFromGET()
    {
        return '?' . http_build_query($_GET);
    }

    public function URLWithoutVariableFromGET($variable)
    {
        $getAr = $_GET;
        unset($getAr[$variable]);
        return '?' . http_build_query($getAr);
    }


    public function GETValueOfIndexIfSetElseDefault($index, $default = null)
    {
        return isset($_GET[$index]) ? $_GET[$index] : $default;
    }

    public function POSTValueOfIndexIfSetElseDefault($index, $default = null)
    {
        return isset($_POST[$index]) ? $_POST[$index] : $default;
    }

    public function COOKIEValueOfIndexIfSetElseDefault($index, $default = null)
    {
        return isset($_COOKIE[$index]) ? $_COOKIE[$index] : $default;
    }

    public function SESSIONValueOfIndexIfSetElseDefault($index, $default = null)
    {
        return isset($_SESSION[$index]) ? $_SESSION[$index] : $default;
    }
}
