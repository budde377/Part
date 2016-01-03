<?php
namespace ChristianBudde\Part\model\page;


/**
 * User: budde
 * Date: 6/15/12
 * Time: 10:58 PM
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
