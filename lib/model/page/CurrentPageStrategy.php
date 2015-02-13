<?php
namespace ChristianBudde\Part\model\page;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/15/12
 * Time: 10:58 PM
 * To change this template use File | Settings | File Templates.
 */
interface CurrentPageStrategy
{

    /**
     * @abstract
     * Will return the path to the current page as an array of
     * Page's
     *
     * @return array
     */
    public function getCurrentPagePath();


    /**
     * @abstract
     * @return Page
     */
    public function getCurrentPage();


}
