<?php
namespace ChristianBudde\cbweb;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 12:36 PM
 */

class UserLibraryJSONObjectImpl extends  JSONObjectImpl{


    function __construct(UserLibrary $library)
    {
        parent::__construct("user_library");
        $this->setVariable('users', $library->listUsers());
    }
}