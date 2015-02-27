<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/27/15
 * Time: 6:29 PM
 */

namespace ChristianBudde\Part\controller\ajax;


class PostGetFilesArrayAccessTypeHandlerImpl extends ArrayAccessTypeHandlerImpl{


    function __construct()
    {

        $this->addArray('POST', $_POST);
        $this->addArray('GET', $_GET);
        $this->addArray('FILES', $_FILES);

    }
}