<?php
namespace ChristianBudde\cbweb\controller\json;

use ChristianBudde\cbweb\model\user\UserLibrary;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 12:36 PM
 */

class UserLibraryObjectImpl extends ObjectImpl
{


    function __construct(UserLibrary $library)
    {
        parent::__construct("user_library");
        $this->setVariable('users', $library->listUsers());
    }
}