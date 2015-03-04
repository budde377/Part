<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:06 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\util\file\FileLibrary;

class FileLibraryTypeHandlerImpl extends GenericObjectTypeHandlerImpl
{

    private $container;
    private $library;
    //TODO implement this

    function __construct(BackendSingletonContainer $container, FileLibrary $library)
    {
        $this->container = $container;
        $this->library = $library;
    }


}