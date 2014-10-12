<?php
namespace ChristianBudde\cbweb\model\page;
use ChristianBudde\cbweb\model\Content;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 5:08 PM
 */

interface PageContent extends Content{

    /**
     * Returns the page instance of which the content is registered.
     * @return Page
     */
    public function getPage();
} 