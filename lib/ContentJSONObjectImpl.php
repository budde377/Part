<?php
namespace ChristianBudde\cbweb;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 5:38 PM
 */

class ContentJSONObjectImpl extends JSONObjectImpl {

    function __construct(Content $content)
    {
        parent::__construct("content");
        $this->setVariable('latest_time', $content->latestTime());
        $this->setVariable('latest_content', $content->latestContent());
        $this->setVariable('id', $content->getId());
    }
}