<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:08 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\page\Page;
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

class PageTypeHandlerImpl extends GenericObjectTypeHandlerImpl
{

    use TypeHandlerTrait;

    private $container;

    function __construct(BackendSingletonContainer $container, Page $page)
    {
        $this->container = $container;
        parent::__construct($page, 'Page');
        $this->whitelistFunction('Page',
            'isHidden',
            'hide',
            'show',
            'getID',
            'getTitle',
            'getTemplate',
            'getAlias',
            'getContent',
            'setID',
            'setTitle',
            'setTemplate',
            'setAlias',
            'delete',
            'match',
            'isEditable',
            'isValidID',
            'isValidAlias',
            'lastModified',
            'modify',
            'getInstance'
        );

        $this->addFunctionAuthFunction('Page', 'setID', $this->wrapFunction([$this, "hasPagePrivilegesAuthFunction"]));
        $this->addFunctionAuthFunction('Page', 'setTitle', $this->wrapFunction([$this, "hasPagePrivilegesAuthFunction"]));
        $this->addFunctionAuthFunction('Page', 'setTemplate', $this->wrapFunction([$this, "hasPagePrivilegesAuthFunction"]));
        $this->addFunctionAuthFunction('Page', 'setAlias', $this->wrapFunction([$this, "hasPagePrivilegesAuthFunction"]));
        $this->addFunctionAuthFunction('Page', 'modify', $this->wrapFunction([$this, "hasPagePrivilegesAuthFunction"]));
        $this->addFunctionAuthFunction('Page', 'delete', $this->currentUserSitePrivilegesAuthFunction($this->container));
        $this->addFunctionAuthFunction('Page', 'hide', $this->currentUserSitePrivilegesAuthFunction($this->container));
        $this->addFunctionAuthFunction('Page', 'show', $this->currentUserSitePrivilegesAuthFunction($this->container));
        $this->addGetInstanceFunction("Page");
    }


    function hasPagePrivilegesAuthFunction(/** @noinspection PhpUnusedParameterInspection */
        $type, Page $instance)
    {
        $currentUser = $this->container->getUserLibraryInstance()->getUserLoggedIn();
        if ($currentUser == null) {
            return false;
        }
        return $currentUser->getUserPrivileges()->hasPagePrivileges($instance);
    }

}