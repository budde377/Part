<?php
namespace ChristianBudde\Part\controller\json;

use ChristianBudde\Part\model\site\SiteContent;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 5:38 PM
 */

class SiteContentObjectImpl  extends ContentObjectImpl
{

    function __construct(SiteContent $content)
    {
        parent::__construct($content);
        $this->name = "site_content";
    }
}