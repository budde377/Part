<?php
require_once dirname(__FILE__) . '/../_interface/PageElement.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 18/01/13
 * Time: 19:20
 */
class LogoutPageElementImpl implements PageElement
{
    function __construct(BackendSingletonContainer $container)
    {
        $user = $container->getUserLibraryInstance()->getUserLoggedIn();
        if($user != null){
            $user->logout();
        }
        HTTPHeaderHelper::redirectToLocation("/");
    }


    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent()
    {
        return "";
     }
}
