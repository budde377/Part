<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:25 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\page\PageContentLibrary;

class PageContentLibraryTypeHandlerImpl extends GenericObjectTypeHandlerImpl{


    function __construct(BackendSingletonContainer $container, PageContentLibrary $library)
    {
        parent::__construct($library, 'PageContentLibrary');


    }


}