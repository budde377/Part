<?php
namespace ChristianBudde\cbweb\test\stub;
use ChristianBudde;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 13/12/12
 * Time: 01:38
 */
class CheckInitializedPageElementImpl extends ChristianBudde\cbweb\PageElementImpl
{


    /**
     * Will set up the page element.
     * If you want to ensure that you register some files, this would be the place to do this.
     * This should always be called before generateContent, at the latest right before.
     * @return void
     */
    public function setUpElement()
    {
        parent::setUpElement();
        if (!isset($_SESSION['initialized'])) {
            $_SESSION['initialized'] = 0;
        }
        $_SESSION['initialized']++;
    }

    public function generateContent()
    {

    }


}
