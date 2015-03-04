<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:22 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\site\SiteContentLibrary;

class SiteContentLibraryTypeHandlerImpl extends GenericObjectTypeHandlerImpl{



    function __construct(BackendSingletonContainer $container, SiteContentLibrary $library)
    {
        parent::__construct($library, "SiteContentLibrary");

    }


}