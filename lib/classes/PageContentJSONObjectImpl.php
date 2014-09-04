<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 5:38 PM
 */

class PageContentJSONObjectImpl extends ContentJSONObjectImpl{

    function __construct(PageContent $pageContent)
    {
        parent::__construct($pageContent);
        $this->name = "page_content";
    }
}