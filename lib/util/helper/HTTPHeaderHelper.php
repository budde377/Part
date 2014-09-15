<?php
namespace ChristianBudde\cbweb\util\helper;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/25/12
 * Time: 10:06 AM
 */
class HTTPHeaderHelper
{
    // 1xx Informational
    const HTTPHeaderStatusCode100 = "HTTP/1.0 100 Continue";
    const HTTPHeaderStatusCode101 = "HTTP/1.0 101 Switching Protocols";
    const HTTPHeaderStatusCode102 = "HTTP/1.0 102 Processing";

    //2xx Success
    const HTTPHeaderStatusCode200 = "HTTP/1.0 200 OK";
    //3xx Redirection
    //4xx Client Error
    const HTTPHeaderStatusCode404 = "HTTP/1.0 404 Not Found";

    //5xx Server Error
    const HTTPHeaderStatusCode500 = "HTTP/1.0 500 Internal Server Error";

    public static function setHeaderStatusCode($code)
    {
        @header($code);

    }

    public static function redirectToLocation($location)
    {
        header('Location:' . $location);
    }


}
