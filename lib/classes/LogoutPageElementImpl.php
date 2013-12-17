<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 18/01/13
 * Time: 19:20
 */
class LogoutPageElementImpl extends PageElementImpl
{
    private $currentUser;
    function __construct(BackendSingletonContainer $container)
    {
        $this->currentUser = $container->getUserLibraryInstance()->getUserLoggedIn();

    }

    /**
     * Will set up the page element.
     * If you want to ensure that you register some files, this would be the place to do this.
     * This should always be called before generateContent, at the latest right before.
     * @return void
     */
    public function setUpElement()
    {
        parent::setUpElement();
        if($this->currentUser != null){
            $this->currentUser->logout();
        }
        HTTPHeaderHelper::redirectToLocation("/");    }
}
