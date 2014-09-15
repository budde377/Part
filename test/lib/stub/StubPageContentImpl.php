<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 6:00 PM
 */
namespace ChristianBudde\cbweb\test\stub;

use ChristianBudde\cbweb\model\page\PageContent;
use ChristianBudde\cbweb\model\page\Page;


class StubPageContentImpl extends StubContentImpl implements PageContent
{
    public $page;


    /**
     * Returns the page instance of which the content is registered.
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }


}