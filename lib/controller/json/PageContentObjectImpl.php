<?php
namespace ChristianBudde\cbweb\controller\json;

use ChristianBudde\cbweb\model\page\PageContent;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 5:38 PM
 */

class PageContentObjectImpl extends ContentObjectImpl
{

    function __construct(PageContent $domainLibraryContent)
    {
        parent::__construct($domainLibraryContent);
        $this->name = "page_content";
    }
}