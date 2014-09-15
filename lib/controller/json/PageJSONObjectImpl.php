<?php
namespace ChristianBudde\cbweb\controller\json;

use ChristianBudde\cbweb\model\page\Page;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 24/01/13
 * Time: 09:25
 * To change this template use File | Settings | File Templates.
 */
class PageJSONObjectImpl extends JSONObjectImpl
{
    /**
     * @param Page $page
     */
    function __construct(Page $page)
    {
        parent::__construct('page');
        $this->setVariable('id',$page->getID());
        $this->setVariable('title',$page->getTitle());
        $this->setVariable('template',$page->getTemplate());
        $this->setVariable('alias',$page->getAlias());
        $this->setVariable('hidden',$page->isHidden());
        $this->setVariable('editable', $page->isEditable());
    }


}
