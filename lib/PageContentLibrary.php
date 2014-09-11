<?php
namespace ChristianBudde\cbweb;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 5:12 PM
 */

interface PageContentLibrary extends ContentLibrary{

    /**
     * Returns the page instance of which the library is registered.
     * @return Page
     */
    public function getPage();
} 