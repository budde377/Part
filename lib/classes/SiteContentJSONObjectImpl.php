<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 5:38 PM
 */

class SiteContentJSONObjectImpl  extends ContentJSONObjectImpl{

    function __construct(SiteContent $content)
    {
        parent::__construct($content);
        $this->name = "site_content";
    }
}